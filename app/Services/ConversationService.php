<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

class ConversationService
{
    public function __construct(
        protected readonly OpenRouterService $openRouterService,
    ) {
    }

    /**
     * Get paginated conversations for the authenticated user.
     */
    public function listForUser(User $user): LengthAwarePaginator
    {
        return Conversation::query()
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->paginate(20);
    }

    /**
     * Create a new conversation for the authenticated user.
     *
     * @param array<string, mixed> $data
     */
    public function createForUser(User $user, array $data): Conversation
    {
        return Conversation::query()->create([
            'user_id' => $user->id,
            'title' => $data['title'] ?? 'New conversation',
            'model' => $data['model'] ?? null,
        ]);
    }

    /**
     * Ensure user can access the conversation.
     *
     * @throws AuthorizationException
     */
    public function assertOwnership(User $user, Conversation $conversation): void
    {
        if ((int) $conversation->user_id !== (int) $user->id) {
            throw new AuthorizationException('You are not allowed to access this conversation.');
        }
    }

    /**
     * Get a conversation and eager-load ordered messages.
     */
    public function getWithMessages(Conversation $conversation): Conversation
    {
        return $conversation->load([
            'messages' => fn ($query) => $query->orderBy('created_at'),
        ]);
    }

    /**
     * Store a user message and update conversation metadata.
     *
     * @param array<string, mixed> $data
     */
    public function storeUserMessage(Conversation $conversation, array $data): Message
    {
        $message = $conversation->messages()->create([
            'role' => 'user',
            'content' => $data['content'],
            'model' => $data['model'],
            'status' => 'queued',
        ]);

        $conversation->forceFill([
            'model' => $data['model'],
            'last_message_at' => now(),
        ])->save();

        return $message;
    }

    /**
     * Store a user message and update conversation title if this is the first message.
     *
     * @param array<string, mixed> $data
     */
    public function storeUserMessageAndSyncTitle(Conversation $conversation, array $data): Message
    {
        $isFirstMessage = $conversation->messages()->doesntExist();

        $userMessage = $this->storeUserMessage($conversation, $data);

        if ($isFirstMessage) {
            $conversation->update([
                'title' => $this->generateTitle($data['content']),
            ]);
        }

        return $userMessage;
    }

    /**
     * Get ordered message history for a conversation.
     *
     * @return \Illuminate\Support\Collection<int, Message>
     */
    public function getOrderedHistory(Conversation $conversation): \Illuminate\Support\Collection
    {
        return $conversation->messages()->orderBy('created_at')->get();
    }

    /**
     * Persist the fully assembled assistant message after stream completion.
     *
     * @param array<string, mixed> $metadata Optional token usage metadata.
     */
    public function storeAssistantMessage(
        Conversation $conversation,
        string $content,
        string $model,
        array $metadata = [],
    ): Message {
        return $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $content,
            'model' => $model,
            'status' => 'complete',
            'prompt_tokens' => $metadata['prompt_tokens'] ?? null,
            'completion_tokens' => $metadata['completion_tokens'] ?? null,
        ]);
    }

    /**
     * Mark a queued user message as errored when streaming fails.
     */
    public function markMessageAsError(Message $message): void
    {
        $message->forceFill(['status' => 'error'])->save();
    }

    /**
     * Stream assistant content, persist the final assistant message, and report lifecycle callbacks.
     *
     * @param array<string, mixed> $payload
     * @param callable(string):void $onChunk
     * @param callable(int):void    $onDone
     * @param callable(string):void $onError
     */
    public function streamAssistantResponse(
        Conversation $conversation,
        array $payload,
        string $model,
        Message $userMessage,
        callable $onChunk,
        callable $onDone,
        callable $onError,
    ): void {
        try {
            $assembled = $this->openRouterService->streamChatCompletion($payload, $onChunk);

            $assistantMessage = $this->storeAssistantMessage(
                $conversation,
                $assembled,
                $model,
            );

            $onDone($assistantMessage->id);
        } catch (RuntimeException $exception) {
            $this->markMessageAsError($userMessage);
            $onError($exception->getMessage());
        }
    }

    /**
     * Derive a short title from the user's first message.
     * Truncates at 60 characters with an ellipsis if needed.
     */
    public function generateTitle(string $firstMessage): string
    {
        $cleaned = trim((string) preg_replace('/\s+/', ' ', $firstMessage));

        if (mb_strlen($cleaned) <= 60) {
            return $cleaned;
        }

        return mb_substr($cleaned, 0, 57) . '...';
    }
}
