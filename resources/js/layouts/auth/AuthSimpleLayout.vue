<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';
defineProps<{
    title?: string;
    description?: string;
}>();
</script>
<template>
    <div
        class="flex min-h-svh opacity-100 transition-opacity duration-750 starting:opacity-0"
    >
        <!-- Left: gradient branding panel (hidden on small screens) -->
        <div
            class="relative hidden w-1/2 flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-black via-neutral-900 to-red-950 lg:flex"
        >
            <!-- Glow accents -->
            <div
                class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-red-700/25 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-24 -left-24 size-80 rounded-full bg-red-900/20 blur-3xl"
            />
            <div
                class="pointer-events-none absolute top-1/3 right-1/4 size-40 rounded-full bg-red-600/10 blur-2xl"
            />
            <div
                class="relative z-10 flex flex-col items-center gap-6 px-12 text-center text-white"
            >
                <!-- InovCorp wordmark -->
                <div
                    class="text-6xl font-black tracking-tighter drop-shadow-lg"
                >
                    ino<span class="text-red-500">V</span>corp
                </div>
                <p class="text-lg font-medium text-white/80">Ai Chat</p>
                <div class="mt-2 h-px w-16 bg-red-600/60" />
                <p class="max-w-xs text-sm leading-relaxed text-white/50">
                    Ai Chat in Laravel with Vue 3, Inertia.js, and Tailwind CSS.
                </p>
            </div>
        </div>
        <!-- Right: form panel -->
        <div
            class="flex flex-1 flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
        >
            <!-- Mobile logo -->
            <Link :href="home()" class="flex items-center gap-2 lg:hidden">
                <span class="text-3xl font-black tracking-tight">
                    ino<span class="text-red-500">V</span>corp
                </span>
            </Link>
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="space-y-1 text-center">
                        <h1 class="text-xl font-semibold">{{ title }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
