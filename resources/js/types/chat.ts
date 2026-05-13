// Chat domain TypeScript types

export interface Conversation {
    id: number;
    user_id: number;
    title: string;
    model: string | null;
    last_message_at: string | null;
    created_at: string;
    updated_at: string;
    messages?: Message[];
}

export interface Message {
    id: number;
    conversation_id: number;
    role: 'user' | 'assistant';
    content: string;
    model: string;
    prompt_tokens: number | null;
    completion_tokens: number | null;
    status: 'queued' | 'complete' | 'error' | null;
    metadata: Record<string, unknown> | null;
    created_at: string;
    updated_at: string;
}

export interface OpenRouterModel {
    id: string;
    name: string;
    description?: string;
    pricing?: {
        prompt: string;
        completion: string;
    };
}

export interface ConversationListMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export type ChatState = 'no_conversation' | 'ready' | 'streaming' | 'error';

export interface SseChunkEvent {
    type: 'chunk';
    content: string;
}

export interface SseDoneEvent {
    type: 'done';
    message_id: number;
}

export interface SseErrorEvent {
    type: 'error';
    message: string;
}

export type SseEvent = SseChunkEvent | SseDoneEvent | SseErrorEvent;
