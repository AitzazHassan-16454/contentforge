<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <!-- Branding panel -->
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-zinc-950 p-12 lg:flex">
            <div
                class="animate-glow-pulse pointer-events-none absolute -top-40 left-1/2 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full bg-indigo-600/30 blur-3xl"
            ></div>
            <div
                class="animate-glow-pulse pointer-events-none absolute bottom-0 right-0 h-72 w-72 translate-x-1/3 translate-y-1/3 rounded-full bg-violet-600/20 blur-3xl anim-delay-2"
            ></div>

            <Link href="/" class="relative flex items-center gap-2.5">
                <ApplicationLogo class="h-9 w-9" />
                <span class="text-xl font-bold tracking-tight text-white">ContentForge</span>
            </Link>

            <div class="animate-fade-in-up relative max-w-md">
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-400">
                    AI-powered blogging
                </p>
                <h2 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight text-white">
                    Ideas, written by AI.
                    <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">
                        Refined by you.
                    </span>
                </h2>
                <p class="mt-4 text-base leading-relaxed text-zinc-400">
                    Turn a single topic into a full first draft, then shape every word before you publish.
                </p>

                <ul class="mt-10 space-y-5">
                    <li class="animate-fade-in-up flex items-start gap-3 anim-delay-1">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-500/15 text-indigo-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                            </svg>
                        </span>
                        <span class="text-sm text-zinc-300">
                            <span class="font-semibold text-white">Generate full drafts</span> from a topic, tone, and keywords — streamed straight into your editor.
                        </span>
                    </li>
                    <li class="animate-fade-in-up flex items-start gap-3 anim-delay-2">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-500/15 text-violet-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                            </svg>
                        </span>
                        <span class="text-sm text-zinc-300">
                            <span class="font-semibold text-white">AI SEO suggestions</span> for titles, meta descriptions, and tags that rank.
                        </span>
                    </li>
                    <li class="animate-fade-in-up flex items-start gap-3 anim-delay-3">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-fuchsia-500/15 text-fuchsia-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                            </svg>
                        </span>
                        <span class="text-sm text-zinc-300">
                            <span class="font-semibold text-white">A clean draft-to-publish flow</span> — nothing goes live until you say so.
                        </span>
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-zinc-500">
                Built with Laravel, Inertia &amp; Vue.
            </p>
        </div>

        <!-- Form panel -->
        <div class="flex flex-1 flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 flex justify-center lg:hidden">
                    <Link href="/" class="flex items-center gap-2.5">
                        <ApplicationLogo class="h-10 w-10" />
                        <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">ContentForge</span>
                    </Link>
                </div>

                <div class="animate-scale-in card px-6 py-8 sm:px-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
