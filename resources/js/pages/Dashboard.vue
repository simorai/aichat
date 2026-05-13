<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Bot, Gauge, MessageSquare, Settings2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-3">
                    <CardDescription>OpenRouter Key</CardDescription>
                    <CardTitle class="text-2xl">Configured</CardTitle>
                </CardHeader>
                <CardContent class="flex items-center gap-2">
                    <Badge variant="secondary">Environment</Badge>
                    <Badge>Local</Badge>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-3">
                    <CardDescription>Streaming Mode</CardDescription>
                    <CardTitle class="text-2xl">SSE Enabled</CardTitle>
                </CardHeader>
                <CardContent class="flex items-center gap-2">
                    <MessageSquare class="size-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground"
                        >Incremental assistant responses</span
                    >
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-3">
                    <CardDescription>Chat Runtime</CardDescription>
                    <CardTitle class="text-2xl">Ready</CardTitle>
                </CardHeader>
                <CardContent class="flex items-center gap-2">
                    <Bot class="size-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground"
                        >Models, conversations, and messages</span
                    >
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-3">
                    <CardDescription>Quality Checks</CardDescription>
                    <CardTitle class="text-2xl">Passing</CardTitle>
                </CardHeader>
                <CardContent class="flex items-center gap-2">
                    <Gauge class="size-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground"
                        >Feature and type checks</span
                    >
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
                <CardDescription>
                    Jump straight into the most common workflows.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <Separator />
                <div class="grid gap-3 md:grid-cols-3">
                    <Button as-child>
                        <Link href="/chat">
                            <MessageSquare class="mr-2 size-4" />
                            Open Chat
                        </Link>
                    </Button>

                    <Button as-child variant="secondary">
                        <Link href="/about">
                            <Settings2 class="mr-2 size-4" />
                            About This App
                        </Link>
                    </Button>

                    <Button as-child variant="outline">
                        <a
                            href="https://openrouter.ai/docs"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            OpenRouter Docs
                        </a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Implementation Snapshot</CardTitle>
                <CardDescription>
                    Current architecture status for this workspace.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-2 text-sm text-muted-foreground">
                <p>- Authenticated pages: Dashboard, Chat, About.</p>
                <p>
                    - Chat API supports conversation CRUD and streaming
                    messages.
                </p>
                <p>- OpenRouter model list is proxied by backend only.</p>
                <p>
                    - Request configuration includes model, temperature, and
                    max_tokens.
                </p>
            </CardContent>
        </Card>
    </div>
</template>
