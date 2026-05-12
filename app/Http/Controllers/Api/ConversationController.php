<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function __construct(
        protected readonly ConversationService $conversationService,
    ) {
    }

    /**
     * List conversations for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversationService->listForUser($request->user());

        return ConversationResource::collection($conversations);
    }

    /**
     * Create a conversation for the authenticated user.
     */
    public function store(StoreConversationRequest $request): JsonResponse
    {
        $conversation = $this->conversationService->createForUser($request->user(), $request->validated());

        return (new ConversationResource($conversation))->response()->setStatusCode(201);
    }

    /**
     * Show a conversation with messages for the authenticated user.
     */
    public function show(Request $request, Conversation $conversation): ConversationResource
    {
        $this->conversationService->assertOwnership($request->user(), $conversation);

        return ConversationResource::make($this->conversationService->getWithMessages($conversation));
    }

    /**
     * Delete a conversation for the authenticated user.
     */
    public function destroy(Request $request, Conversation $conversation): JsonResponse
    {
        $this->conversationService->deleteForUser($request->user(), $conversation);

        return response()->json(['success' => true], 200);
    }
}
