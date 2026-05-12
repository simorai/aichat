<?php

namespace App\Http\Responses;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\ConversationService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatSseResponder
{
    /**
     * Create the SSE response for an assistant stream lifecycle.
     *
     * @param array<string, mixed> $payload
     */
    public function stream(
        ConversationService $conversationService,
        Conversation $conversation,
        array $payload,
        string $model,
        Message $userMessage,
    ): StreamedResponse {
        return response()->stream(
            function () use ($conversationService, $conversation, $payload, $model, $userMessage): void {
                $conversationService->streamAssistantResponse(
                    $conversation,
                    $payload,
                    $model,
                    $userMessage,
                    function (string $chunk): void {
                        $this->emit('chunk', ['content' => $chunk]);
                    },
                    function (int $messageId): void {
                        $this->emit('done', ['message_id' => $messageId]);
                    },
                    function (string $message): void {
                        $this->emit('error', ['message' => $message]);
                    },
                );
            },
            200,
            [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function emit(string $type, array $data = []): void
    {
        echo 'data: ' . json_encode(array_merge(['type' => $type], $data)) . "\n\n";
        flush();
    }
}