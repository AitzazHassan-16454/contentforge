<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    query: {
        type: String,
        default: '',
    },
    results: {
        type: Array,
        default: () => [],
    },
});

const q = ref(props.query);
const navigating = ref(false);
let debounceTimer = null;

const examples = [
    'launching a side project while working full time',
    'how streaming AI responses work',
    'choosing an AI provider',
    'AI-powered blogging',
];

const hasQuery = computed(() => q.value.trim().length > 0);
const resultsCount = computed(() => props.results.length);

const handleStart = () => {
    navigating.value = true;
};

const handleFinish = () => {
    navigating.value = false;
};

const search = () => {
    router.get(route('posts.search'), { q: q.value.trim() }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const onInput = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(search, 350);
};

const highlight = (snippet) => {
    const terms = q.value.trim().split(/\s+/).filter(Boolean);

    if (terms.length === 0) {
        return [{ text: snippet, match: false }];
    }

    const pattern = new RegExp(`(${terms.map((term) => term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|')})`, 'ig');
    const segments = [];
    let lastIndex = 0;

    for (const match of snippet.matchAll(pattern)) {
        if (match.index > lastIndex) {
            segments.push({ text: snippet.slice(lastIndex, match.index), match: false });
        }
        segments.push({ text: match[0], match: true });
        lastIndex = match.index + match[0].length;
    }

    if (lastIndex < snippet.length) {
        segments.push({ text: snippet.slice(lastIndex), match: false });
    }

    return segments;
};

const formatScore = (score) => `${Math.round(score * 100)}%`;

watch(
    () => props.query,
    (value) => {
        q.value = value;
    },
);

const unsubscribeStart = router.on('start', handleStart);
const unsubscribeFinish = router.on('finish', handleFinish);

onBeforeUnmount(() => {
    clearTimeout(debounceTimer);
    unsubscribeStart();
    unsubscribeFinish();
});
</script>

<template>
    <Head title="Search" />

    <BlogLayout>
        <section class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800">
            <div class="bg-grid bg-mask-fade pointer-events-none absolute inset-0"></div>
            <div class="animate-blob pointer-events-none absolute -top-32 right-[-10%] h-96 w-96 rounded-full bg-primary-500/20 blur-3xl"></div>

            <div class="relative mx-auto max-w-4xl px-4 py-16 sm:px-6 sm:py-20">
                <p class="animate-slide-up text-center text-xs font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">
                    Semantic search
                </p>
                <h1 class="animate-slide-up mt-3 text-center font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-4xl anim-delay-1">
                    Search posts by meaning, not just keywords
                </h1>
                <p class="animate-slide-up mx-auto mt-3 max-w-xl text-center text-sm leading-relaxed text-zinc-500 dark:text-zinc-400 anim-delay-2">
                    Every published post is embedded with the Gemini API, so queries are matched by semantic
                    similarity against the full text.
                </p>

                <div class="animate-slide-up relative mx-auto mt-8 max-w-2xl anim-delay-3">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input
                        v-model="q"
                        type="search"
                        autofocus
                        autocomplete="off"
                        placeholder="Ask anything about the posts..."
                        class="w-full rounded-2xl border border-zinc-200 bg-white py-4 pl-12 pr-16 text-base text-zinc-900 shadow-soft outline-none transition placeholder:text-zinc-400 focus:border-primary-500 focus:shadow-glow dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:focus:border-primary-600"
                        @input="onInput"
                        @keyup.enter="search"
                    />
                    <kbd class="pointer-events-none absolute inset-y-0 right-4 my-auto hidden h-6 items-center rounded-md border border-zinc-200 bg-zinc-50 px-1.5 text-xs font-semibold text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-500 sm:flex">
                        Enter
                    </kbd>
                </div>

                <div class="animate-slide-up mx-auto mt-5 flex max-w-2xl flex-wrap items-center justify-center gap-2 anim-delay-4">
                    <button
                        v-for="example in examples"
                        :key="example"
                        type="button"
                        class="rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-zinc-600 shadow-soft transition-all duration-300 hover:-translate-y-0.5 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-800 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-primary-500/50 dark:hover:bg-primary-500/10 dark:hover:text-primary-300"
                        @click="q = example; search()"
                    >
                        {{ example }}
                    </button>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
            <template v-if="!hasQuery">
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-14 text-center dark:border-zinc-700 dark:bg-zinc-950/50">
                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-lg font-semibold text-zinc-800 dark:text-zinc-200">Search the full text of every post</p>
                    <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                        Type a question above, or try one of the suggested queries. Results are ranked by cosine
                        similarity between your query and each post's embedded content.
                    </p>
                </div>
            </template>

            <template v-else-if="navigating">
                <div class="space-y-4">
                    <div
                        v-for="index in 3"
                        :key="index"
                        class="card p-6"
                    >
                        <div class="flex items-center gap-2">
                            <div class="skeleton h-3 w-24"></div>
                            <div class="skeleton h-3 w-14"></div>
                        </div>
                        <div class="skeleton mt-4 h-5 w-3/5"></div>
                        <div class="mt-3 space-y-2">
                            <div class="skeleton h-3 w-full"></div>
                            <div class="skeleton h-3 w-4/5"></div>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
                <div v-if="resultsCount === 0" class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-14 text-center dark:border-zinc-700 dark:bg-zinc-950/50">
                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-lg font-semibold text-zinc-800 dark:text-zinc-200">No matches for &ldquo;{{ q }}&rdquo;</p>
                    <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                        Try rephrasing with different words, or browse the latest posts instead.
                    </p>
                    <Link :href="route('posts.index')" class="btn-primary mt-6">
                        Browse all posts
                    </Link>
                </div>

                <template v-else>
                    <div class="flex items-center gap-3">
                        <h2 class="font-display text-xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                            Results for &ldquo;{{ q }}&rdquo;
                        </h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-200 bg-primary-50 px-2.5 py-0.5 text-xs font-bold text-primary-700 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300">
                            {{ resultsCount }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div
                            v-for="result in results"
                            :key="result.id"
                            class="group relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-soft transition-all duration-300 hover:-translate-y-0.5 hover:border-primary-200 hover:shadow-lift dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40"
                        >
                            <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-600 to-violet-600 opacity-0 transition group-hover:opacity-100"></span>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                <span class="inline-flex items-center gap-1.5 text-primary-700 dark:text-primary-400">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ result.author }}
                                </span>
                                <span class="text-zinc-300 dark:text-zinc-600">&middot;</span>
                                <time>{{ result.published_at }}</time>
                                <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                    {{ formatScore(result.score) }} match
                                </span>
                            </div>
                            <Link :href="route('posts.show', result.slug)" class="mt-3 block text-lg font-bold leading-snug text-zinc-900 transition group-hover:text-primary-700 dark:text-zinc-100 dark:group-hover:text-primary-400">
                                {{ result.title }}
                            </Link>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                                <template v-for="(segment, index) in highlight(result.snippet)" :key="index">
                                    <mark
                                        v-if="segment.match"
                                        class="rounded bg-primary-100 px-0.5 font-semibold text-primary-900 dark:bg-primary-500/30 dark:text-primary-200"
                                    >
                                        {{ segment.text }}
                                    </mark>
                                    <template v-else>{{ segment.text }}</template>
                                </template>
                            </p>
                            <div v-if="result.categories.length || result.tags.length" class="mt-4 flex flex-wrap gap-1.5">
                                <span
                                    v-for="category in result.categories"
                                    :key="category.id"
                                    class="chip bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-200/60 dark:bg-primary-500/15 dark:text-primary-300 dark:ring-primary-500/25"
                                >
                                    {{ category.name }}
                                </span>
                                <span
                                    v-for="tag in result.tags"
                                    :key="tag.id"
                                    class="chip bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400"
                                >
                                    #{{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </section>
    </BlogLayout>
</template>
