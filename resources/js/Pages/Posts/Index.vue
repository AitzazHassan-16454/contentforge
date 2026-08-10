<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

defineProps({
    posts: {
        type: Object,
        required: true,
    },
    heading: {
        type: String,
        default: '',
    },
});

useReveal();

const categories = computed(() => usePage().props.categories ?? []);

const navigating = ref(false);

const handleStart = () => {
    navigating.value = true;
};

const handleFinish = () => {
    navigating.value = false;
};

const unsubscribeStart = router.on('start', handleStart);
const unsubscribeFinish = router.on('finish', handleFinish);

onBeforeUnmount(() => {
    unsubscribeStart();
    unsubscribeFinish();
});

const coverGradients = [
    'from-primary-600 via-indigo-500 to-violet-500',
    'from-violet-600 via-purple-500 to-fuchsia-500',
    'from-sky-600 via-blue-500 to-indigo-500',
    'from-indigo-600 via-primary-500 to-sky-500',
];

const coverGradient = (index) => coverGradients[index % coverGradients.length];
</script>

<template>
    <Head :title="heading || 'Blog'" />

    <BlogLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden border-b border-zinc-200 dark:border-zinc-800">
            <div class="bg-grid bg-mask-fade pointer-events-none absolute inset-0"></div>
            <div class="animate-blob pointer-events-none absolute -top-32 right-[-10%] h-96 w-96 rounded-full bg-primary-500/20 blur-3xl"></div>
            <div class="animate-blob pointer-events-none absolute -bottom-24 left-[-5%] h-80 w-80 rounded-full bg-violet-500/20 blur-3xl" style="animation-delay: 3s"></div>

            <div class="relative mx-auto max-w-6xl px-4 py-16 text-center sm:px-6 sm:py-20">
                <p class="animate-slide-up inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50/80 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-primary-700 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-pulse-brand absolute inline-flex h-full w-full rounded-full bg-primary-500"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                    </span>
                    The ContentForge blog
                </p>

                <h1 class="animate-slide-up mx-auto mt-6 max-w-3xl font-display text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-5xl anim-delay-1">
                    <template v-if="heading">{{ heading }}</template>
                    <template v-else>
                        Ideas, written by AI.
                        <span class="text-gradient block pb-2">Refined by you.</span>
                    </template>
                </h1>

                <p class="animate-slide-up mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2">
                    Explore what writers are publishing — AI-generated drafts, shaped by human hands, about the
                    future of content, AI, and modern blogging.
                </p>

                <div v-if="categories.length" class="animate-slide-up mx-auto mt-10 flex max-w-3xl flex-wrap items-center justify-center gap-2 anim-delay-3">
                    <Link
                        :href="route('posts.index')"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition-all duration-300"
                        :class="!heading
                            ? 'border-transparent bg-gradient-to-r from-primary-600 to-violet-600 text-white shadow-glow'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-soft hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-primary-500/50 dark:hover:text-primary-300'"
                    >
                        All posts
                    </Link>
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="route('posts.category', category.slug)"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition-all duration-300"
                        :class="heading === category.name
                            ? 'border-transparent bg-gradient-to-r from-primary-600 to-violet-600 text-white shadow-glow'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-soft hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-primary-500/50 dark:hover:text-primary-300'"
                    >
                        {{ category.name }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Posts -->
        <section class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h2 class="font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                    {{ heading ? `${heading} posts` : 'All posts' }}
                </h2>
                <span class="inline-flex items-center gap-2 rounded-full border border-primary-200 bg-primary-50 px-3.5 py-1.5 text-sm font-bold text-primary-700 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary-600"></span>
                    {{ posts.total }} published
                </span>
            </div>

            <div v-if="posts.data.length === 0" class="animate-scale-in relative z-10 mt-12 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-16 text-center dark:border-zinc-700 dark:bg-zinc-950/50">
                <div class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <p class="mt-5 text-xl font-bold text-zinc-900 dark:text-zinc-100">No posts yet</p>
                <p class="mt-2 text-base text-zinc-500 dark:text-zinc-400">
                    The first AI-generated post is one click away.
                </p>
                <Link
                    :href="route('dashboard.posts.create')"
                    class="btn-primary mt-8"
                >
                    Write the first post
                </Link>
            </div>

            <div v-else-if="navigating" class="relative z-10 mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
                <div
                    v-for="index in 6"
                    :key="index"
                    class="card flex flex-col overflow-hidden p-0"
                >
                    <div class="skeleton h-44 w-full rounded-none"></div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-center gap-3">
                            <div class="skeleton h-7 w-7 rounded-full"></div>
                            <div class="skeleton h-3 w-24"></div>
                        </div>
                        <div class="skeleton mt-5 h-5 w-4/5"></div>
                        <div class="mt-4 space-y-2">
                            <div class="skeleton h-3 w-full"></div>
                            <div class="skeleton h-3 w-3/4"></div>
                        </div>
                        <div class="mt-6 flex gap-2">
                            <div class="skeleton h-6 w-16 rounded-lg"></div>
                            <div class="skeleton h-6 w-12 rounded-lg"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="relative z-10 mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="(post, index) in posts.data"
                    :key="post.id"
                    :href="route('posts.show', post.slug)"
                    data-reveal
                    :style="{ '--reveal-delay': `${(index % 3) * 100}ms` }"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-soft transition-all duration-300 hover:-translate-y-1.5 hover:border-primary-200 hover:shadow-lift dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40"
                >
                    <div class="relative h-44 overflow-hidden">
                        <img
                            v-if="post.cover_image"
                            :src="post.cover_image"
                            :alt="post.cover_image_alt || post.title"
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

                        <div class="mt-auto flex items-center justify-between pt-6">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 dark:text-zinc-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ post.views ?? 0 }}
                            </span>
                            <span class="inline-flex items-center gap-2 text-sm font-bold text-primary-700 transition-colors group-hover:text-primary-600 dark:text-primary-400 dark:group-hover:text-primary-300">
                                Read article
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <nav v-if="posts.links.length > 3" class="mt-14 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
                <template v-for="(link, index) in posts.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :preserve-scroll="true"
                        class="inline-flex min-w-10 items-center justify-center rounded-xl border px-3.5 py-2 text-sm font-semibold transition-all duration-300"
                        :class="link.active
                            ? 'border-transparent bg-gradient-to-r from-primary-600 to-violet-600 text-white shadow-glow'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-soft hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-primary-500/50 dark:hover:text-primary-300'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="inline-flex min-w-10 items-center justify-center px-3.5 py-2 text-sm font-semibold text-zinc-300 dark:text-zinc-600"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </section>
    </BlogLayout>
</template>
