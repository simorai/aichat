<script setup lang="ts">
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { OpenRouterModel } from '@/types/chat';

const props = defineProps<{
    models: OpenRouterModel[];
    selectedModel: string;
    temperature: number;
    maxTokens: number;
    disabled: boolean;
}>();

const emit = defineEmits<{
    'update:selectedModel': [value: string];
    'update:temperature': [value: number];
    'update:maxTokens': [value: number];
}>();

const modelSearch = ref('');

const selectedModelInfo = computed(
    () =>
        props.models.find((model) => model.id === props.selectedModel) ?? null,
);

const filteredModels = computed(() =>
    modelSearch.value.trim()
        ? props.models.filter(
              (m) =>
                  m.name
                      .toLowerCase()
                      .includes(modelSearch.value.toLowerCase()) ||
                  m.id.toLowerCase().includes(modelSearch.value.toLowerCase()),
          )
        : props.models,
);

function onTemperature(event: Event): void {
    const v = parseFloat((event.target as HTMLInputElement).value);

    if (!Number.isNaN(v)) {
        emit('update:temperature', Math.min(2, Math.max(0, v)));
    }
}

function onMaxTokens(event: Event): void {
    const v = parseInt((event.target as HTMLInputElement).value, 10);

    if (!Number.isNaN(v) && v > 0) {
        emit('update:maxTokens', v);
    }
}

function onModelValueChange(value: unknown): void {
    emit('update:selectedModel', typeof value === 'string' ? value : '');
}
</script>

<template>
    <div class="flex flex-col gap-4 p-4 text-sm">
        <div class="rounded-lg border bg-muted/30 p-3">
            <div
                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Current model
            </div>
            <div v-if="selectedModelInfo" class="mt-1 space-y-1">
                <div class="font-medium text-foreground">
                    {{ selectedModelInfo.name }}
                </div>
                <div class="text-xs break-all text-muted-foreground">
                    {{ selectedModelInfo.id }}
                </div>
                <div
                    v-if="selectedModelInfo.description"
                    class="text-xs text-muted-foreground"
                >
                    {{ selectedModelInfo.description }}
                </div>
            </div>
            <div v-else class="mt-1 text-xs text-muted-foreground">
                No model selected yet.
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label
                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
            >
                Model
            </label>
            <Input
                v-model="modelSearch"
                placeholder="Filter models…"
                class="h-8 text-xs"
                :disabled="disabled"
            />
            <Select
                :model-value="selectedModel"
                :disabled="disabled || !models.length"
                @update:model-value="onModelValueChange"
            >
                <SelectTrigger class="h-8 text-xs">
                    <SelectValue placeholder="Select a model" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Available models</SelectLabel>
                        <SelectItem
                            v-for="model in filteredModels"
                            :key="model.id"
                            :value="model.id"
                        >
                            {{ model.name }}
                        </SelectItem>
                        <p
                            v-if="!filteredModels.length"
                            class="px-2 py-1.5 text-xs text-muted-foreground"
                        >
                            No models match.
                        </p>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="flex flex-col gap-1.5">
            <label
                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
            >
                Temperature
                <span class="ml-1 font-normal normal-case">{{
                    temperature
                }}</span>
            </label>
            <input
                type="range"
                min="0"
                max="2"
                step="0.05"
                :value="temperature"
                :disabled="disabled"
                class="w-full accent-primary disabled:opacity-50"
                @input="onTemperature"
            />
            <div class="flex justify-between text-xs text-muted-foreground">
                <span>Precise (0)</span>
                <span>Creative (2)</span>
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label
                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
            >
                Max tokens
            </label>
            <p class="text-xs text-muted-foreground">
                Controls the maximum response length for this conversation.
            </p>
            <Input
                type="number"
                min="1"
                max="128000"
                :value="maxTokens"
                :disabled="disabled"
                class="h-8 text-xs"
                @input="onMaxTokens"
            />
        </div>
    </div>
</template>
