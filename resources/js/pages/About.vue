<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'About',
                href: '/about',
            },
        ],
    },
});
</script>

<template>
    <Head title="About" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Card>
            <CardHeader>
                <CardTitle>About This Application</CardTitle>
                <CardDescription>
                    Internal AI Chat module built for Inovcorp.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-sm text-muted-foreground">
                    This project provides an authenticated ChatGPT-like
                    interface using OpenRouter as the LLM gateway, with
                    persistent conversation history, model selection, and
                    streaming responses.
                </p>
                <Separator />
                <div class="space-y-2">
                    <h3 class="text-sm font-semibold">Tech Stack</h3>
                    <div class="flex flex-wrap gap-2">
                        <Badge>Laravel</Badge>
                        <Badge variant="secondary">Vue 3</Badge>
                        <Badge variant="secondary">Inertia.js</Badge>
                        <Badge variant="outline">shadcn-vue</Badge>
                        <Badge variant="outline">OpenRouter API</Badge>
                        <Badge variant="outline">TypeScript</Badge>
                    </div>
                </div>
                <Separator />
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold">Author</h3>
                    <p class="text-sm text-muted-foreground">Simao morais</p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
