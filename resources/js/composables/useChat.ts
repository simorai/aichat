import { ref } from 'vue';
import type { Ref } from 'vue';
import type {
    ChatState,
    Conversation,
    ConversationListMeta,
    Message,
    OpenRouterModel,
    SseEvent,
} from '@/types/chat';

export interface UseChatReturn {
    conversations: Ref<Conversation[]>;
    conversationsMeta: Ref<ConversationListMeta | null>;
    activeConversation: Ref<Conversation | null>;
    messages: Ref<Message[]>;
    models: Ref<OpenRouterModel[]>;
    selectedModel: Ref<string>;
    temperature: Ref<number>;
    maxTokens: Ref<number>;
    streamingContent: Ref<string>;
    chatState: Ref<ChatState>;
    error: Ref<string | null>;
    fetchConversations: () => Promise<void>;
    fetchModels: () => Promise<void>;
    selectConversation: (conversation: Conversation) => Promise<void>;
    createConversation: () => Promise<void>;
    sendMessage: (content: string) => Promise<void>;
    deleteConversation: (conversationId: number) => Promise<void>;
}

export function useChat(): UseChatReturn {
    const conversations = ref<Conversation[]>([]);
    const conversationsMeta = ref<ConversationListMeta | null>(null);
    const activeConversation = ref<Conversation | null>(null);
    const messages = ref<Message[]>([]);
    const models = ref<OpenRouterModel[]>([]);
    const selectedModel = ref('');
    const temperature = ref(0.7);
    const maxTokens = ref(1024);
    const streamingContent = ref('');
    const chatState = ref<ChatState>('no_conversation');
    const error = ref<string | null>(null);

    async function readErrorMessage(
        response: Response,
        fallback: string,
    ): Promise<string> {
        const contentType = response.headers.get('content-type') ?? '';

        try {
            if (contentType.includes('application/json')) {
                const json = (await response.json()) as { message?: unknown };

                if (typeof json.message === 'string' && json.message.trim()) {
                    return json.message;
                }
            } else {
                const text = (await response.text()).trim();

                if (text) {
                    return text;
                }
            }
        } catch {
            // Fall back to the default message below.
        }

        return fallback;
    }

    function selectDefaultModel(): void {
        if (!selectedModel.value && models.value.length > 0) {
            selectedModel.value = models.value[0].id;
        }
    }

    function getCsrfToken(): string {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

        return match ? decodeURIComponent(match[1]) : '';
    }

    async function fetchConversations(): Promise<void> {
        try {
            const response = await fetch('/api/conversations', {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(
                    await readErrorMessage(
                        response,
                        'Failed to load conversations.',
                    ),
                );
            }

            const json = (await response.json()) as {
                data: Conversation[];
                meta: ConversationListMeta;
            };

            conversations.value = json.data;
            conversationsMeta.value = json.meta;
        } catch (caught: unknown) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Failed to load conversations.';
        }
    }

    async function fetchModels(): Promise<void> {
        try {
            const response = await fetch('/api/models', {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(
                    await readErrorMessage(
                        response,
                        'Failed to load models from OpenRouter.',
                    ),
                );
            }

            const json = (await response.json()) as {
                data: { data?: OpenRouterModel[] } | OpenRouterModel[];
            };

            const raw = json.data;
            const list = Array.isArray(raw)
                ? raw
                : ((raw as { data?: OpenRouterModel[] }).data ?? []);

            models.value = list;
            selectDefaultModel();
        } catch (caught: unknown) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Failed to load models from OpenRouter.';
        }
    }

    async function selectConversation(
        conversation: Conversation,
    ): Promise<void> {
        try {
            const response = await fetch(
                `/api/conversations/${conversation.id}`,
                {
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                },
            );

            if (!response.ok) {
                throw new Error(
                    await readErrorMessage(
                        response,
                        'Failed to load conversation.',
                    ),
                );
            }

            const json = (await response.json()) as {
                data: Conversation & { messages: Message[] };
            };

            activeConversation.value = json.data;
            messages.value = json.data.messages ?? [];

            if (json.data.model) {
                selectedModel.value = json.data.model;
            } else {
                selectDefaultModel();
            }

            chatState.value = 'ready';
        } catch (caught: unknown) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Failed to load conversation.';
            chatState.value = 'error';
        }
    }

    async function createConversation(): Promise<void> {
        error.value = null;

        try {
            const response = await fetch('/api/conversations', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCsrfToken(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    title: 'New conversation',
                    model: selectedModel.value || null,
                }),
            });

            if (!response.ok) {
                throw new Error(
                    await readErrorMessage(
                        response,
                        'Failed to create conversation.',
                    ),
                );
            }

            const json = (await response.json()) as { data: Conversation };
            conversations.value.unshift(json.data);
            activeConversation.value = json.data;
            messages.value = [];

            if (!selectedModel.value) {
                selectDefaultModel();
            }

            chatState.value = 'ready';
        } catch (caught: unknown) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Failed to create conversation.';
            chatState.value = 'error';
        }
    }

    async function sendMessage(content: string): Promise<void> {
        if (
            !activeConversation.value ||
            !selectedModel.value ||
            !content.trim()
        ) {
            return;
        }

        error.value = null;

        const optimisticMessage: Message = {
            id: Date.now(),
            conversation_id: activeConversation.value.id,
            role: 'user',
            content: content.trim(),
            model: selectedModel.value,
            prompt_tokens: null,
            completion_tokens: null,
            status: 'queued',
            metadata: null,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
        };

        messages.value.push(optimisticMessage);
        streamingContent.value = '';
        chatState.value = 'streaming';

        try {
            await streamAssistantResponse(content.trim());
        } catch (caught: unknown) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Failed to send message.';
            chatState.value = 'error';
            streamingContent.value = '';
            optimisticMessage.status = 'error';
        }

        void fetchConversations();
    }

    function streamAssistantResponse(content: string): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            if (!activeConversation.value) {
                reject(new Error('No active conversation'));

                return;
            }

            fetch(
                `/api/conversations/${activeConversation.value.id}/messages`,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': getCsrfToken(),
                        Accept: 'text/event-stream',
                    },
                    body: JSON.stringify({
                        content,
                        model: selectedModel.value,
                        temperature: temperature.value,
                        max_tokens: maxTokens.value,
                    }),
                },
            )
                .then(async (response) => {
                    if (!response.ok) {
                        reject(
                            new Error(
                                await readErrorMessage(
                                    response,
                                    `Request failed: ${response.status}`,
                                ),
                            ),
                        );

                        return;
                    }

                    const reader = response.body?.getReader();

                    if (!reader) {
                        reject(
                            new Error(
                                'Streaming not supported by this browser.',
                            ),
                        );

                        return;
                    }

                    const decoder = new TextDecoder();
                    let buffer = '';
                    let assembledContent = '';

                    const read = async (): Promise<void> => {
                        const { done, value } = await reader.read();

                        if (done) {
                            if (assembledContent) {
                                messages.value.push({
                                    id: Date.now() + 1,
                                    conversation_id:
                                        activeConversation.value!.id,
                                    role: 'assistant',
                                    content: assembledContent,
                                    model: selectedModel.value,
                                    prompt_tokens: null,
                                    completion_tokens: null,
                                    status: 'complete',
                                    metadata: null,
                                    created_at: new Date().toISOString(),
                                    updated_at: new Date().toISOString(),
                                });
                                streamingContent.value = '';
                                chatState.value = 'ready';
                            }

                            resolve();

                            return;
                        }

                        buffer += decoder.decode(value, { stream: true });

                        const lines = buffer.split('\n');
                        buffer = lines.pop() ?? '';

                        for (const line of lines) {
                            const trimmed = line.trimEnd();

                            if (!trimmed.startsWith('data: ')) {
                                continue;
                            }

                            const raw = trimmed.slice(6);

                            if (!raw || raw === '[DONE]') {
                                continue;
                            }

                            try {
                                const event = JSON.parse(raw) as SseEvent;

                                if (event.type === 'chunk') {
                                    assembledContent += event.content;
                                    streamingContent.value = assembledContent;
                                } else if (event.type === 'done') {
                                    streamingContent.value = '';
                                    chatState.value = 'ready';
                                } else if (event.type === 'error') {
                                    reader.cancel();
                                    reject(new Error(event.message));

                                    return;
                                }
                            } catch {
                                // Ignore malformed SSE lines.
                            }
                        }

                        return read();
                    };

                    return read();
                })
                .catch((networkError: unknown) => {
                    reject(
                        networkError instanceof Error
                            ? networkError
                            : new Error(
                                  'Network error. Check your connection.',
                              ),
                    );
                });
        });
    }

    async function deleteConversation(conversationId: number): Promise<void> {
        error.value = null;

        try {
            const response = await fetch(
                `/api/conversations/${conversationId}`,
                {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': getCsrfToken(),
                        Accept: 'application/json',
                    },
                },
            );

            if (!response.ok) {
                throw new Error(
                    await readErrorMessage(
                        response,
                        'Failed to delete conversation.',
                    ),
                );
            }

            // Remove from local list and reset active if deleted
            conversations.value = conversations.value.filter(
                (c) => c.id !== conversationId,
            );

            if (activeConversation.value?.id === conversationId) {
                activeConversation.value = null;
                messages.value = [];
                chatState.value = 'no_conversation';
            }
        } catch (caught: unknown) {
            error.value =
                caught instanceof Error
                    ? caught.message
                    : 'Failed to delete conversation.';
        }
    }

    return {
        conversations,
        conversationsMeta,
        activeConversation,
        messages,
        models,
        selectedModel,
        temperature,
        maxTokens,
        streamingContent,
        chatState,
        error,
        fetchConversations,
        fetchModels,
        selectConversation,
        createConversation,
        sendMessage,
        deleteConversation,
    };
}
