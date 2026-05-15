<script setup lang="ts">
import { PlusIcon, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatConversationDate } from '@/lib/dateFormat';
import type { Conversation } from '@/types/chat';

const props = defineProps<{
    conversations: Conversation[];
    activeConversationId: number | null;
}>();

const emit = defineEmits<{
    select: [conversation: Conversation];
    create: [];
    delete: [conversationId: number];
}>();

const search = ref('');
const hoveredConvId = ref<number | null>(null);

const filtered = computed(() =>
    search.value.trim()
        ? props.conversations.filter((c) =>
              c.title.toLowerCase().includes(search.value.toLowerCase()),
          )
        : props.conversations,
);
</script>

<template>
    <div class="chat-frame flex h-full flex-col gap-2 bg-[#ece4d9] p-3">
        <!-- Header -->
        <div class="flex items-center justify-between pb-1">
            <span class="text-sm font-semibold text-foreground"
                >Conversations</span
            >
            <Button size="sm" variant="ghost" @click="emit('create')">
                <PlusIcon class="size-4" />
                <span class="sr-only">New conversation</span>
            </Button>
        </div>

        <!-- Search - styled input with visible background and focus states -->
        <Input
            v-model="search"
            placeholder="Search…"
            class="search-input h-8 text-sm"
        />

        <!-- List -->
        <div class="flex-1 overflow-y-auto">
            <!-- Conversation item: each item has subtle color distinction and shadow; active item highlighted with blue tint and left border -->
            <div
                v-for="conv in filtered"
                :key="conv.id"
                class="conversation-item group relative mx-0.5 flex w-auto flex-col gap-0.5 rounded-md px-2 py-2 text-left text-sm transition-all"
                :class="{
                    'conversation-item-active':
                        conv.id === activeConversationId,
                }"
                @mouseenter="hoveredConvId = conv.id"
                @mouseleave="hoveredConvId = null"
            >
                <button
                    class="flex w-full items-start justify-between gap-2"
                    @click="emit('select', conv)"
                >
                    <span class="flex-1 truncate">{{ conv.title }}</span>
                    <button
                        v-if="hoveredConvId === conv.id"
                        class="shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
                        :aria-label="`Delete ${conv.title}`"
                        @click.stop="emit('delete', conv.id)"
                    >
                        <Trash2
                            class="size-3.5 text-muted-foreground hover:text-destructive"
                        />
                    </button>
                </button>
                <span class="text-xs text-muted-foreground">
                    {{ formatConversationDate(conv.updated_at) }}
                </span>
            </div>

            <p
                v-if="!filtered.length"
                class="mt-4 text-center text-xs text-muted-foreground"
            >
                {{ search ? 'No matches.' : 'No conversations yet.' }}
            </p>
        </div>
    </div>
</template>
