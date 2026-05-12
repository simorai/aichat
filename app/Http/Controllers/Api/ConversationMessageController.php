<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StoreMessageRequest;
use App\Http\Responses\ChatSseResponder;
use App\Models\Conversation;
use App\Services\ConversationService;
use App\Services\OpenRouterService;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversationMessageController extends Controller
{
    public function __construct(
        protected readonly ConversationService $conversationService,
        protected readonly OpenRouterService $openRouterService,
        protected readonly ChatSseResponder $chatSseResponder,
    ) {
    }

    /**
     * Store the user message and stream the assistant response via SSE.
     */
    public function store(StoreMessageRequest $request, Conversation $conversation): JsonResponse|StreamedResponse
    {
        $this->conversationService->assertOwnership($request->user(), $conversation);

        // Pre-flight: catch missing key before starting the stream so the error
        // can be returned as a regular JSON response instead of inside SSE.
        try {
            $this->openRouterService->ensureApiKey();
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }

        $data = $request->validated();

        $userMessage = $this->conversationService->storeUserMessageAndSyncTitle($conversation, $data);

        // Load full history including the just-stored user message.
        $history = $this->conversationService->getOrderedHistory($conversation);

        $payload = $this->openRouterService->buildChatPayload($data, $history);

        return $this->chatSseResponder->stream(
            $this->conversationService,
            $conversation,
            $payload,
            $data['model'],
            $userMessage,
        );
    }
}
