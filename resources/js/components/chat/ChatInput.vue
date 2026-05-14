<script setup lang="ts">
import { SendHorizonal } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

defineProps<{
    disabled: boolean;
}>();

const emit = defineEmits<{
    send: [content: string];
}>();

const content = ref('');

function submit(): void {
    const trimmed = content.value.trim();

    if (!trimmed) {
return;
}

    emit('send', trimmed);
    content.value = '';
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        submit();
    }
}
</script>

<template>
    <div class="border-t bg-background p-3">
        <div
            class="flex items-end gap-2 rounded-lg border bg-muted/30 px-3 py-2"
        >
            <textarea
                v-model="content"
                rows="1"
                placeholder="Send a message… (Shift+Enter for new line)"
                :disabled="disabled"
                class="max-h-48 min-h-[2rem] flex-1 resize-none bg-transparent text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                @keydown="onKeydown"
            />
            <Button
                size="icon"
                :disabled="disabled || !content.trim()"
                @click="submit"
            >
                <SendHorizonal class="size-4" />
                <span class="sr-only">Send</span>
            </Button>
        </div>
    </div>
</template>
