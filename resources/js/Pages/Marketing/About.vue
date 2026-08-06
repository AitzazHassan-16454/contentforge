<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
});

useReveal();

const values = [
    {
        title: 'Write first, edit later',
        body: 'Momentum beats perfection. Our tools get a complete first draft in front of you so the hard part — the blank page — disappears.',
        icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125',
    },
    {
        title: 'Human in the loop',
        body: 'AI is a co-writer, never the author. Every draft is yours to shape, edit, and approve before a single word goes live.',
        icon: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
    },
    {
        title: 'Own your words',
        body: 'Your content is your content. Drafts are private, exports are clean, and nothing is locked behind a walled garden.',
        icon: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
    },
];
</script>

<template>
    <Head title="About" />

    <BlogLayout>
        <section class="relative overflow-hidden border-b border-zinc-100 dark:border-zinc-800">
            <div
                class="pointer-events-none absolute inset-0 dark:opacity-20"
                style="background-image: radial-gradient(circle at 1px 1px, rgb(228 228 231 / 0.6) 1px, transparent 0); background-size: 28px 28px;"
            ></div>
            <div class="animate-glow-pulse pointer-events-none absolute -top-32 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-indigo-100/70 blur-3xl dark:bg-indigo-500/20"></div>

            <div class="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6">
                <p class="animate-fade-in-down text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">About us</p>
                <h1 class="animate-fade-in-up mx-auto mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 anim-delay-1 sm:text-5xl">
                    We build tools that make writing <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent dark:from-indigo-400 dark:to-violet-400">feel effortless</span>
                </h1>
                <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2">
                    ContentForge started with a simple observation: the hardest part of blogging is never the writing —
                    it is starting. We set out to pair the raw power of modern AI with the control of a real editor,
                    so ideas become published posts instead of abandoned drafts.
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <p data-reveal class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Our mission</p>
                    <h2 data-reveal class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                        Give every writer a fair shot at being read
                    </h2>
                    <p data-reveal class="mt-5 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
                        Great writing should not depend on a content agency budget or a team of tools held together with duct tape.
                        ContentForge packs generation, editing, SEO, scheduling, and analytics into one workflow, so individuals
                        and small teams can produce the same quality as big publishers — without the overhead.
                    </p>
                    <p data-reveal class="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
                        We keep the human at the center. The AI proposes; you dispose. Our job is to remove friction, not
                        to replace your voice.
                    </p>
                    <div data-reveal class="mt-8 flex flex-wrap gap-3">
                        <Link :href="route('register')" class="btn-primary">Start writing free</Link>
                        <Link :href="route('posts.index')" class="btn-secondary">Read the blog</Link>
                    </div>
                </div>

                <div data-reveal class="grid gap-5 sm:grid-cols-2">
                    <div v-for="(value, index) in values" :key="value.title" :style="{ '--reveal-delay': `${index * 100}ms` }" class="card p-6">
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="value.icon" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-zinc-900 dark:text-zinc-100">{{ value.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ value.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-zinc-100 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-900/40">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 px-4 py-16 text-center sm:grid-cols-3 sm:px-6">
                <div data-reveal>
                    <p class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">{{ stats.posts || 0 }}</p>
                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Published posts</p>
                </div>
                <div data-reveal style="--reveal-delay: 100ms">
                    <p class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">{{ stats.authors || 0 }}</p>
                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Active authors</p>
                </div>
                <div data-reveal style="--reveal-delay: 200ms">
                    <p class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">100%</p>
                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Human-controlled publishing</p>
                </div>
            </div>
        </section>
    </BlogLayout>
</template>
