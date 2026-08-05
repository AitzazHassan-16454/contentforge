<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    posts: {
        type: Object,
        required: true,
    },
    heading: {
        type: String,
        default: '',
    },
});

const categories = computed(() => usePage().props.categories ?? []);
</script>

<template>
    <Head :title="heading || 'Home'" />

    <BlogLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden border-b border-zinc-100">
            <div
                class="pointer-events-none absolute inset-0"
                style="background-image: radial-gradient(circle at 1px 1px, rgb(228 228 231 / 0.6) 1px, transparent 0); background-size: 28px 28px;"
            ></div>
            <div class="pointer-events-none absolute -top-32 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-indigo-100/70 blur-3xl"></div>

            <div class="relative mx-auto max-w-6xl px-4 py-16 text-center sm:px-6 sm:py-20">
                <p class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-white px-3.5 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    Powered by the Laravel AI SDK
                </p>

                <template v-if="heading">
                    <p class="mt-6 text-sm font-semibold uppercase tracking-widest text-indigo-600">
                        <Link href="/" class="transition hover:text-indigo-500">Browse</Link>
                        <span class="mx-2 text-zinc-300">&middot;</span>
                        <Link :href="route('posts.index')" class="transition hover:text-indigo-500">All posts</Link>
                    </p>
                    <h1 class="mx-auto mt-3 max-w-3xl text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl">
                        {{ heading }}
                    </h1>
                </template>
                <template v-else>
                    <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl">
                        Ideas, written by AI.
                        <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">
                            Refined by you.
                        </span>
                    </h1>
                    <p class="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-zinc-600">
                        Generate complete, publication-ready blog posts from a single topic &mdash; then shape every
                        word in your own editor before hitting publish.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <Link
                            :href="route('dashboard.posts.create')"
                            class="btn-primary"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                            </svg>
                            Start writing
                        </Link>
                        <a
                            href="#posts"
                            class="btn-secondary"
                        >
                            Browse posts
                        </a>
                    </div>
                </template>

                <nav v-if="categories.length" class="mx-auto mt-10 flex max-w-3xl flex-wrap items-center justify-center gap-2">
                    <Link
                        href="/"
                        class="rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 text-sm font-medium text-zinc-600 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        All
                    </Link>
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="route('posts.category', category.slug)"
                        class="rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 text-sm font-medium text-zinc-600 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        {{ category.name }}
                    </Link>
                </nav>
            </div>
        </section>

        <!-- Posts -->
        <section id="posts" class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">
                    {{ heading ? `${heading} posts` : 'Latest posts' }}
                </h2>
                <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600">
                    {{ posts.total }}
                </span>
            </div>

            <div v-if="posts.data.length === 0" class="mt-8 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-14 text-center">
                <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <p class="mt-4 text-lg font-semibold text-zinc-800">No posts yet</p>
                <p class="mt-1 text-sm text-zinc-500">
                    The first AI-generated post is one click away.
                </p>
                <Link
                    :href="route('dashboard.posts.create')"
                    class="btn-primary mt-6"
                >
                    Write the first post
                </Link>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="post in posts.data"
                    :key="post.id"
                    :href="route('posts.show', post.slug)"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white p-6 shadow-soft transition duration-200 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lift"
                >
                    <span class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-500 to-violet-500 opacity-0 transition group-hover:opacity-100"></span>
                    <div class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                        <span class="inline-flex items-center gap-1.5 text-indigo-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ post.author }}
                        </span>
                        <span class="text-zinc-300">&middot;</span>
                        <time>{{ post.published_at }}</time>
                    </div>
                    <h3 class="mt-3 text-lg font-bold leading-snug text-zinc-900 transition group-hover:text-indigo-600">
                        {{ post.title }}
                    </h3>
                    <p v-if="post.excerpt" class="mt-2 line-clamp-3 text-sm leading-relaxed text-zinc-600">
                        {{ post.excerpt }}
                    </p>
                    <div v-if="post.categories.length || post.tags.length" class="mt-4 flex flex-wrap gap-1.5">
                        <span
                            v-for="category in post.categories"
                            :key="category.id"
                            class="chip bg-indigo-50 text-indigo-700"
                        >
                            {{ category.name }}
                        </span>
                        <span
                            v-for="tag in post.tags"
                            :key="tag.id"
                            class="chip bg-zinc-100 text-zinc-600"
                        >
                            #{{ tag.name }}
                        </span>
                    </div>
                    <span class="mt-5 inline-flex items-center gap-1 pt-1 text-sm font-semibold text-indigo-600">
                        Read more
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </span>
                </Link>
            </div>

            <div v-if="posts.links.length > 3" class="mt-10 flex flex-wrap items-center justify-center gap-2">
                <Link
                    v-for="(link, index) in posts.links"
                    :key="index"
                    :href="link.url"
                    :class="[
                        'inline-flex items-center rounded-xl border px-3 py-2 text-sm font-semibold transition',
                        link.active
                            ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-sm hover:border-zinc-300 hover:bg-zinc-50',
                        !link.url && 'pointer-events-none opacity-50',
                    ]"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </div>
        </section>
    </BlogLayout>
</template>
