<script setup lang="ts">
import { nextTick, onUpdated, ref } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';
import type { Message } from '@/types/chat';

defineProps<{
    messages: Message[];
    streamingContent: string;
    isStreaming: boolean;
}>();

const listEl = ref<HTMLDivElement | null>(null);

// Auto-scroll to bottom whenever new content arrives.
onUpdated(() => {
    nextTick(() => {
        if (listEl.value) {
            listEl.value.scrollTop = listEl.value.scrollHeight;
        }
    });
});
</script>

<template>
    <div ref="listEl" class="flex flex-1 flex-col gap-4 overflow-y-auto p-4">
        <!-- Settled messages -->
        <div
            v-for="msg in messages"
            :key="msg.id"
            class="flex w-full"
            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
        >
            <div
                class="max-w-[80%] rounded-xl px-4 py-2 text-sm leading-relaxed"
                :class="
                    msg.role === 'user'
                        ? 'bg-primary text-primary-foreground'
                        : 'assistant-bubble text-foreground'
                "
            >
                <pre class="font-sans whitespace-pre-wrap">{{
                    msg.content
                }}</pre>
                <span
                    v-if="msg.status === 'error'"
                    class="mt-1 block text-xs text-destructive"
                >
                    ⚠ Failed to send
                </span>
            </div>
        </div>

        <!-- Streaming assistant message (live) -->
        <div v-if="isStreaming" class="flex w-full justify-start">
            <div
                class="assistant-bubble max-w-[80%] rounded-xl px-4 py-2 text-sm leading-relaxed text-foreground"
            >
                <template v-if="streamingContent">
                    <pre class="font-sans whitespace-pre-wrap">{{
                        streamingContent
                    }}</pre>
                </template>
                <template v-else>
                    <!-- Waiting for first token -->
                    <div class="flex items-center gap-1.5 py-1">
                        <Skeleton class="h-2 w-2 rounded-full" />
                        <Skeleton class="h-2 w-2 rounded-full" />
                        <Skeleton class="h-2 w-2 rounded-full" />
                    </div>
                </template>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-if="!messages.length && !isStreaming"
            class="flex flex-1 items-center justify-center text-center text-sm text-muted-foreground"
        >
            Send a message to start the conversation.
        </div>
    </div>
</template>
