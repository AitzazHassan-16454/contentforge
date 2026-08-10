<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

useReveal();

const user = computed(() => usePage().props.auth?.user ?? null);

const coverGradients = [
    'from-primary-600 via-indigo-500 to-violet-500',
    'from-violet-600 via-purple-500 to-fuchsia-500',
    'from-sky-600 via-blue-500 to-indigo-500',
    'from-indigo-600 via-primary-500 to-sky-500',
];

const coverGradient = (index) => coverGradients[index % coverGradients.length];

const steps = [
    {
        title: 'Describe your topic',
        body: 'Drop in a topic, tone, keywords, and target length. Our AI agents handle the outline and heavy lifting for you.',
        icon: 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z',
    },
    {
        title: 'AI drafts, you refine',
        body: 'Watch your post stream into a clean markdown editor with SEO titles, meta descriptions, and tag suggestions on the side.',
        icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125',
    },
    {
        title: 'Schedule, publish, grow',
        body: 'Publish instantly or schedule for the perfect moment. Track views, reactions, and comments from your dashboard.',
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
    },
];

const features = [
    {
        title: 'AI blog generator',
        body: 'Generate complete, publication-ready posts from a single topic with tone and keyword control. Nothing is published without your approval.',
        icon: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z',
        tint: 'from-primary-500 to-primary-600',
    },
    {
        title: 'Live markdown editor',
        body: 'A distraction-free editor with real-time preview, cover images, categories, tags, and draft/publish workflow built in.',
        icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125',
        tint: 'from-violet-500 to-violet-600',
    },
    {
        title: 'SEO assistant',
        body: 'Get title options, meta descriptions, and keyword suggestions before you publish — so every post has a fighting chance to rank.',
        icon: 'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75',
        tint: 'from-sky-500 to-sky-600',
    },
    {
        title: 'Scheduled publishing',
        body: 'Queue posts ahead of time and let ContentForge publish them when your audience is most likely to read.',
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        tint: 'from-emerald-500 to-teal-600',
    },
    {
        title: 'Analytics that matter',
        body: 'See views, reactions, and comments per post, plus engagement trends across your entire catalog in one dashboard.',
        icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        tint: 'from-amber-500 to-orange-600',
    },
    {
        title: 'Built-in community',
        body: 'Comments, reactions, author pages, and searchable categories turn your blog into a conversation, not a monologue.',
        icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        tint: 'from-rose-500 to-pink-600',
    },
];

const testimonials = [
    {
        name: 'Priya Sharma',
        role: 'Content lead, SaaS startup',
        quote: 'I went from a blank page to a published post in under an hour. The AI drafts are genuinely usable and the SEO suggestions save me a second tool entirely.',
    },
    {
        name: 'Daniel Osei',
        role: 'Indie blogger',
        quote: 'ContentForge is the first tool that got me writing consistently. Scheduling a month of posts in a single afternoon changed everything for my traffic.',
    },
    {
        name: 'María Fernández',
        role: 'Marketing freelancer',
        quote: 'The streaming editor is magic. I can watch the post take shape and stop it to redirect anytime. My clients think I hired a whole team.',
    },
];

const faqs = [
    {
        q: 'How does the AI generation work?',
        a: 'You give a topic, optional title, tone, keywords, and length. ContentForge streams a complete draft into your editor using the Laravel AI SDK. Everything is generated as a draft — nothing is published automatically.',
    },
    {
        q: 'Do I own the content I generate?',
        a: 'Yes. Every post you write or generate is yours, and drafts stay private to your account until you decide to publish them.',
    },
    {
        q: 'Which AI providers are supported?',
        a: 'ContentForge is provider-agnostic. It ships with OpenAI, Anthropic, and Google Gemini support, configurable from a single environment setting — so you can swap providers without touching code.',
    },
    {
        q: 'Can I schedule posts ahead of time?',
        a: 'Yes. Set a future publish date from the editor and ContentForge will publish the post automatically when the time comes.',
    },
];

const formatCount = (value) => {
    const n = Number(value ?? 0);

    if (n >= 1_000_000) {
        return `${(n / 1_000_000).toFixed(1).replace(/\.0$/, '')}M`;
    }

    if (n >= 1_000) {
        return `${(n / 1_000).toFixed(1).replace(/\.0$/, '')}K`;
    }

    return n.toLocaleString();
};

const openFaq = ref(0);

const ctaHref = computed(() => (user.value ? route('dashboard.posts.create') : route('register')));
</script>

<template>
    <Head title="Home" />

    <BlogLayout>
        <!-- ============ Hero ============ -->
        <section class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800">
            <div class="bg-grid bg-mask-fade animate-twinkle pointer-events-none absolute inset-0"></div>
            <div class="bg-grid bg-mask-fade animate-drift pointer-events-none absolute inset-0 opacity-60" style="background-size: 56px 56px"></div>
            <div class="animate-blob pointer-events-none absolute -top-32 right-[-10%] h-96 w-96 rounded-full bg-primary-500/20 blur-3xl"></div>
            <div class="animate-blob pointer-events-none absolute -bottom-24 left-[-5%] h-80 w-80 rounded-full bg-violet-500/20 blur-3xl" style="animation-delay: 3s"></div>
            <div class="animate-blob pointer-events-none absolute left-1/3 top-1/2 h-64 w-64 rounded-full bg-sky-400/10 blur-3xl" style="animation-delay: 6s"></div>

            <div class="relative mx-auto max-w-6xl px-4 pb-16 pt-16 sm:px-6 sm:pt-24 lg:pb-28">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="animate-slide-up inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50/80 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-primary-700 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-pulse-brand absolute inline-flex h-full w-full rounded-full bg-primary-500"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                        </span>
                        AI-powered blogging for modern writers
                    </p>

                    <h1 class="animate-slide-up mx-auto mt-8 font-display text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-6xl lg:text-7xl lg:leading-[1.05] anim-delay-1">
                        Ideas, written by AI.
                        <span class="text-gradient block pb-2">Refined by you.</span>
                    </h1>

                    <p class="animate-slide-up mx-auto mt-8 max-w-2xl text-lg font-medium leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2 sm:text-xl">
                        Generate complete, publication-ready blog posts from a single topic — then shape every word
                        in your own editor, polish it with AI-powered SEO suggestions, and schedule it to publish.
                    </p>

                    <div class="animate-slide-up mt-10 flex flex-wrap items-center justify-center gap-4 anim-delay-3">
                        <Link :href="ctaHref" class="btn-primary btn-sheen group !rounded-full !px-8 !py-3.5 !text-base">
                            Start writing free
                            <svg class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                        <Link :href="route('posts.index')" class="btn-secondary group !rounded-full !px-8 !py-3.5 !text-base">
                            Browse the blog
                            <svg class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>

                    <p class="animate-slide-up mt-8 text-sm font-semibold text-zinc-500 dark:text-zinc-400 anim-delay-4">
                        No credit card required &middot; Start writing in minutes
                    </p>
                </div>

                <!-- Product mockup -->
                <div class="animate-slide-up relative mx-auto mt-20 max-w-4xl anim-delay-5">
                    <div class="pointer-events-none absolute -right-4 -top-4 h-24 w-24 rounded-full bg-primary-500/30 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-6 -left-6 h-32 w-32 rounded-full bg-violet-500/25 blur-3xl"></div>

                    <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl shadow-zinc-900/10 transition-all duration-500 hover:shadow-primary-500/10 hover:shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-2 border-b border-zinc-200 bg-zinc-50/80 px-5 py-3.5 dark:border-zinc-800 dark:bg-zinc-950/50">
                            <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                            <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                            <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-4 hidden items-center gap-2 rounded-lg bg-white px-3 py-1 text-xs font-semibold text-zinc-500 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-950 dark:text-zinc-400 dark:ring-zinc-700 sm:flex">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                            app.contentforge.dev/posts/generate
                            </span>
                        </div>
                        <div class="grid gap-6 p-6 sm:grid-cols-5 sm:p-8">
                            <div class="sm:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Generate a post</p>
                                <div class="mt-4 space-y-3">
                                    <div>
                                        <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Topic</p>
                                        <div class="flex h-9 items-center rounded-lg bg-zinc-50 px-3 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-950 dark:ring-zinc-800">
                                            <span class="truncate text-xs text-zinc-600 dark:text-zinc-300">How AI is changing content marketing</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Tone</p>
                                        <div class="flex h-9 items-center rounded-lg bg-zinc-50 px-3 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-950 dark:ring-zinc-800">
                                            <span class="text-xs text-zinc-600 dark:text-zinc-300">Professional &amp; approachable</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Keywords</p>
                                        <div class="flex h-9 items-center gap-1.5 px-2">
                                            <span class="chip bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-200 dark:bg-primary-500/15 dark:text-primary-300 dark:ring-primary-500/25">ai marketing</span>
                                            <span class="chip bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">content strategy</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-primary w-full" disabled>
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Generating draft&hellip;
                                    </button>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Streaming draft</p>
                                <div class="mt-4 space-y-2.5">
                                    <div class="h-5 w-3/4 rounded bg-zinc-200/80 dark:bg-zinc-800"></div>
                                    <div class="h-3 w-full rounded bg-zinc-200/70 dark:bg-zinc-800/60"></div>
                                    <div class="h-3 w-full rounded bg-zinc-200/70 dark:bg-zinc-800/60"></div>
                                    <div class="h-3 w-5/6 rounded bg-zinc-200/70 dark:bg-zinc-800/60"></div>
                                    <div class="flex items-center gap-2 pt-2">
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-300">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                            </svg>
                                        </span>
                                        <span class="text-xs font-medium text-primary-700 dark:text-primary-400">AI is writing your outline&hellip;</span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            SEO title
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-2.5 py-1.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Meta description
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v6a2.25 2.25 0 002.25 2.25h10.5A2.25 2.25 0 0019.5 15V9a2.25 2.25 0 00-2.25-2.25M6 12h6" />
                                            </svg>
                                            Tags
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ Stats ============ -->
        <section class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto grid max-w-6xl grid-cols-3 divide-x divide-zinc-200 px-4 py-12 sm:px-6 dark:divide-zinc-800">
                <div data-reveal class="px-4 text-center sm:px-8">
                    <p class="font-display text-3xl font-extrabold tracking-tight text-gradient sm:text-4xl">
                        {{ formatCount(stats.posts) }}
                    </p>
                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Published posts</p>
                </div>
                <div data-reveal style="--reveal-delay: 100ms" class="px-4 text-center sm:px-8">
                    <p class="font-display text-3xl font-extrabold tracking-tight text-gradient sm:text-4xl">
                        {{ formatCount(stats.authors) }}
                    </p>
                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Active authors</p>
                </div>
                <div data-reveal style="--reveal-delay: 200ms" class="px-4 text-center sm:px-8">
                    <p class="font-display text-3xl font-extrabold tracking-tight text-gradient sm:text-4xl">
                        {{ formatCount(stats.views) }}
                    </p>
                    <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">Total reads</p>
                </div>
            </div>
        </section>

        <!-- ============ How it works ============ -->
        <section class="relative border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <p data-reveal class="text-sm font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">How it works</p>
                    <h2 data-reveal class="mt-3 font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-4xl">
                        From idea to published post in minutes
                    </h2>
                    <p data-reveal class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                        No more staring at a blank page. ContentForge carries the momentum so you can focus on the words that matter.
                    </p>
                </div>

                <div class="relative mt-16 grid gap-10 md:grid-cols-3 md:gap-6">
                    <div class="pointer-events-none absolute left-[16.6%] right-[16.6%] top-7 hidden border-t-2 border-dashed border-primary-200 dark:border-primary-500/20 md:block"></div>

                    <div
                        v-for="(step, index) in steps"
                        :key="step.title"
                        data-reveal
                        :style="{ '--reveal-delay': `${index * 120}ms` }"
                        class="group relative p-2 text-center"
                    >
                        <div class="relative mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 font-display text-lg font-bold text-white shadow-lg shadow-primary-500/30 transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl group-hover:shadow-primary-500/40">
                            {{ index + 1 }}
                        </div>
                        <h3 class="relative mt-5 text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ step.title }}</h3>
                        <p class="relative mx-auto mt-2 max-w-xs text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ step.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ Features ============ -->
        <section class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <p data-reveal class="text-sm font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">Features</p>
                    <h2 data-reveal class="mt-3 font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-4xl">
                        Everything you need to ship great content
                    </h2>
                    <p data-reveal class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                        A complete writing workflow — generation, editing, SEO, scheduling, analytics, and community — without the tool sprawl.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        data-reveal
                        :style="{ '--reveal-delay': `${(index % 3) * 100}ms` }"
                        class="group relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-7 shadow-soft transition-all duration-300 hover:-translate-y-1.5 hover:border-primary-200 hover:shadow-lift dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40"
                    >
                        <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br from-primary-500/10 to-violet-500/10 blur-2xl opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        <div class="relative inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3" :class="feature.tint">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="relative mt-5 text-base font-bold text-zinc-900 dark:text-zinc-100">{{ feature.title }}</h3>
                        <p class="relative mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ feature.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ Latest posts ============ -->
        <section class="relative border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-4 py-24 sm:px-6">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row sm:items-end">
                    <div class="text-center sm:text-left">
                        <p data-reveal class="inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50 px-3.5 py-1.5 text-xs font-bold uppercase tracking-widest text-primary-700 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-400">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-500 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                            </span>
                            Fresh from the blog
                        </p>
                        <h2 data-reveal class="mt-4 font-display text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-5xl">
                            What writers are publishing
                        </h2>
                    </div>
                    <Link
                        data-reveal
                        :href="route('posts.index')"
                        class="btn-secondary hidden shrink-0 !px-6 !py-3 sm:inline-flex"
                    >
                        View all posts
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>

                <div v-if="posts.length" class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="(post, index) in posts"
                        :key="post.id"
                        :href="route('posts.show', post.slug)"
                        data-reveal
                        :style="{ '--reveal-delay': `${index * 100}ms` }"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-soft transition-all duration-300 hover:-translate-y-1.5 hover:border-primary-200 hover:shadow-lift dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40"
                    >
                        <div class="relative h-44 overflow-hidden">
                            <img
                                v-if="post.cover_image"
                                :src="post.cover_image"
                                :alt="post.title"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div v-else class="h-full w-full bg-gradient-to-br transition-transform duration-500 group-hover:scale-105" :class="coverGradient(index)">
                                <span class="flex h-full w-full items-center justify-center font-display text-6xl font-extrabold text-white/40">
                                    {{ post.title.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-center gap-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-100 text-[10px] font-bold text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">{{ post.author.charAt(0).toUpperCase() }}</span>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ post.author }}</span>
                                <span class="text-zinc-300 dark:text-zinc-600">&bull;</span>
                                <time>{{ post.published_at }}</time>
                            </div>

                            <h3 class="mt-4 text-lg font-bold leading-snug text-zinc-900 transition-colors duration-300 group-hover:text-primary-700 dark:text-zinc-50 dark:group-hover:text-primary-400">
                                {{ post.title }}
                            </h3>

                            <p v-if="post.excerpt" class="mt-2 line-clamp-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                {{ post.excerpt }}
                            </p>

                            <div v-if="post.categories.length" class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-for="category in post.categories"
                                    :key="category.id"
                                    class="chip bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-200/60 dark:bg-primary-500/15 dark:text-primary-300 dark:ring-primary-500/25"
                                >
                                    {{ category.name }}
                                </span>
                            </div>

                            <div class="mt-auto flex items-center gap-2 pt-6 text-sm font-bold text-primary-700 transition-colors group-hover:text-primary-600 dark:text-primary-400 dark:group-hover:text-primary-300">
                                Read article
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="mt-10 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-14 text-center dark:border-zinc-700 dark:bg-zinc-950/50">
                    <p class="text-lg font-semibold text-zinc-800 dark:text-zinc-200">No posts published yet</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Be the first to share a story with the world.</p>
                    <Link :href="ctaHref" class="btn-primary mt-6">Write the first post</Link>
                </div>

                <div class="mt-8 text-center sm:hidden">
                    <Link :href="route('posts.index')" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary-700 dark:text-primary-400">
                        View all posts
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ============ Testimonials ============ -->
        <section class="relative border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
                <div class="mx-auto max-w-2xl text-center">
                    <p data-reveal class="text-sm font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">Loved by writers</p>
                    <h2 data-reveal class="mt-3 font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-4xl">
                        Writers who stopped staring at blank pages
                    </h2>
                </div>

                <div class="mt-14 grid gap-6 md:grid-cols-3">
                    <figure
                        v-for="(item, index) in testimonials"
                        :key="item.name"
                        data-reveal
                        :style="{ '--reveal-delay': `${index * 100}ms` }"
                        class="flex flex-col rounded-2xl border border-zinc-200/80 bg-white p-7 shadow-soft transition-all duration-300 hover:-translate-y-1.5 hover:border-primary-200 hover:shadow-lift dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40"
                    >
                        <div class="flex gap-0.5 text-amber-400">
                            <svg v-for="star in 5" :key="star" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <blockquote class="mt-4 flex-1 font-serif text-[15px] leading-relaxed text-zinc-600 dark:text-zinc-300">
                            &ldquo;{{ item.quote }}&rdquo;
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-violet-600 text-sm font-bold text-white">
                                {{ item.name.split(' ').map((part) => part[0]).join('') }}
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ item.name }}</span>
                                <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ item.role }}</span>
                            </span>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <!-- ============ FAQ ============ -->
        <section class="border-y border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-3xl px-4 py-20 sm:px-6">
                <div class="text-center">
                    <p data-reveal class="text-sm font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">FAQ</p>
                    <h2 data-reveal class="mt-3 font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-4xl">
                        Frequently asked questions
                    </h2>
                </div>

                <div class="mt-12 space-y-3">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="faq.q"
                        data-reveal
                        :style="{ '--reveal-delay': `${Math.min(index, 4) * 60}ms` }"
                        class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-soft transition-all duration-300 hover:border-primary-200 hover:shadow-lift dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start"
                            :aria-expanded="openFaq === index"
                            @click="openFaq = openFaq === index ? -1 : index"
                        >
                            <span class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ faq.q }}</span>
                            <svg
                                class="h-5 w-5 shrink-0 text-zinc-400 transition-all duration-300 dark:text-zinc-500"
                                :class="openFaq === index && 'rotate-180 text-primary-600'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div v-show="openFaq === index" class="px-6 pb-5">
                                <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ faq.a }}</p>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ Final CTA ============ -->
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 animate-gradient-x bg-gradient-to-br from-primary-700 via-primary-600 to-violet-700"></div>
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, rgb(255 255 255 / 0.12) 1px, transparent 1px), linear-gradient(to bottom, rgb(255 255 255 / 0.12) 1px, transparent 1px);"></div>
            <div class="animate-blob pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative mx-auto max-w-4xl px-4 py-24 text-center sm:px-6">
                <h2 data-reveal class="font-display text-3xl font-extrabold tracking-tight text-white sm:text-5xl">
                    Ready to write something great?
                </h2>
                <p data-reveal class="mx-auto mt-4 max-w-2xl text-lg text-primary-100">
                    Join the writers using ContentForge to turn ideas into published posts — faster than ever.
                </p>
                <div data-reveal class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        :href="ctaHref"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-3.5 text-base font-semibold text-primary-700 shadow-xl shadow-black/10 transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary-50 hover:shadow-2xl"
                    >
                        Start writing free
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('posts.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-white/40 px-8 py-3.5 text-base font-semibold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/10"
                    >
                        Browse the blog
                    </Link>
                </div>
            </div>
        </section>
    </BlogLayout>
</template>
