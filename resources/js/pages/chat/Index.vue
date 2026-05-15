<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { AlertCircle, MessageSquare } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import ChatConversationList from '@/components/chat/ChatConversationList.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import ChatMessageList from '@/components/chat/ChatMessageList.vue';
import ChatModelPanel from '@/components/chat/ChatModelPanel.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { useChat } from '@/composables/useChat';
import { chat } from '@/routes';
import type { Conversation } from '@/types/chat';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Chat',
                href: chat(),
            },
        ],
    },
});

const {
    conversations,
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
} = useChat();

const showDeleteDialog = ref(false);
const deleteConvId = ref<number | null>(null);

const isStreaming = computed(() => chatState.value === 'streaming');
const isDisabled = computed(() => isStreaming.value);

onMounted(async () => {
    await Promise.all([fetchConversations(), fetchModels()]);
});

function onSelectConversation(conversation: Conversation): Promise<void> {
    return selectConversation(conversation);
}

async function onCreateConversation(): Promise<void> {
    await createConversation();
}

async function onSend(content: string): Promise<void> {
    await sendMessage(content);
}

function onDeleteConversation(conversationId: number): void {
    deleteConvId.value = conversationId;
    showDeleteDialog.value = true;
}

async function confirmDelete(): Promise<void> {
    if (deleteConvId.value !== null) {
        await deleteConversation(deleteConvId.value);
    }

    showDeleteDialog.value = false;
    deleteConvId.value = null;
}

function onSelectedModelChange(value: string): void {
    selectedModel.value = value;
}

function onTemperatureChange(value: number): void {
    temperature.value = value;
}

function onMaxTokensChange(value: number): void {
    maxTokens.value = value;
}
</script>

<template>
    <Head title="Chat" />

    <div class="flex h-full min-h-0 flex-1 overflow-hidden">
        <aside
            class="chat-frame flex w-64 shrink-0 flex-col overflow-hidden border-r"
        >
            <ChatConversationList
                :conversations="conversations"
                :active-conversation-id="activeConversation?.id ?? null"
                @select="onSelectConversation"
                @create="onCreateConversation"
                @delete="onDeleteConversation"
            />
        </aside>

        <div
            class="chat-frame flex min-w-0 flex-1 flex-col overflow-hidden bg-[#f7f5fa]"
        >
            <div class="flex h-12 shrink-0 items-center border-b px-4">
                <template v-if="activeConversation">
                    <MessageSquare class="mr-2 size-4 text-muted-foreground" />
                    <span class="truncate text-sm font-medium">
                        {{ activeConversation.title }}
                    </span>
                </template>
                <template v-else>
                    <span class="text-sm text-muted-foreground">
                        Select or start a conversation
                    </span>
                </template>
            </div>

            <div
                v-if="error"
                class="flex items-center gap-2 border-b bg-destructive/10 px-4 py-2 text-sm text-destructive"
            >
                <AlertCircle class="size-4 shrink-0" />
                {{ error }}
            </div>

            <template v-if="!activeConversation">
                <div
                    class="flex flex-1 flex-col items-center justify-center gap-3 text-muted-foreground"
                >
                    <MessageSquare class="size-12 opacity-30" />
                    <p class="text-sm">
                        Create or select a conversation to begin.
                    </p>
                </div>
            </template>

            <template v-else>
                <ChatMessageList
                    :messages="messages"
                    :streaming-content="streamingContent"
                    :is-streaming="isStreaming"
                />
                <ChatInput :disabled="isDisabled" @send="onSend" />
            </template>
        </div>

        <Separator orientation="vertical" />

        <aside
            class="chat-frame flex w-64 shrink-0 flex-col overflow-y-auto border-l bg-background"
        >
            <div
                class="border-b px-4 py-2.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
            >
                Configuration
            </div>
            <ChatModelPanel
                :models="models"
                :selected-model="selectedModel"
                :temperature="temperature"
                :max-tokens="maxTokens"
                :disabled="isDisabled"
                @update:selected-model="onSelectedModelChange"
                @update:temperature="onTemperatureChange"
                @update:max-tokens="onMaxTokensChange"
            />
        </aside>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="showDeleteDialog">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete conversation?</DialogTitle>
                <DialogDescription>
                    This will permanently delete the conversation and all its
                    messages. This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false">
                    Cancel
                </Button>
                <Button variant="destructive" @click="confirmDelete">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
