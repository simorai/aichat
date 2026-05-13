<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>
<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>
        <div class="flex w-full items-center justify-center">
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
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
                        <p class="text-lg font-medium text-white/80">
                            Ai Chat in Laravel with Vue 3, Inertia.js, and
                            Tailwind CSS.
                        </p>
                        <div class="mt-2 h-px w-16 bg-red-600/60" />
                        <p
                            class="max-w-xs text-sm leading-relaxed text-white/50"
                        >
                            Your private Ai Chat solution for secure,
                            self-hosted conversations.
                        </p>
                    </div>
                </div>
                <!-- Right: main message panel -->
                <div
                    class="flex flex-1 flex-col items-center justify-center rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <div class="flex flex-col items-center gap-6">
                        <div
                            class="mb-2 text-4xl font-black tracking-tight text-red-600"
                        >
                            Welcome to ino<span
                                class="text-black dark:text-white"
                                >V</span
                            >corp
                        </div>
                        <div class="mb-2 text-center text-lg font-semibold">
                            Your Ai Chat solution built with Laravel and Vue 3.
                        </div>
                        <div class="mb-2 h-px w-16 bg-red-600/60" />
                        <div
                            class="mb-4 max-w-md text-center text-base text-muted-foreground"
                        >
                            Experience secure, self-hosted conversations with
                            inoVcorp. <br />
                            <br />
                            GPT powered Ai Chat built on Laravel and Vue 3,
                            designed for privacy and performance. <br />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
