<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import MarkdownContent from '@/Components/MarkdownContent.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    post: {
        type: Object,
        required: true,
    },
});

const initials = (name) =>
    name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');
</script>

<template>
    <Head :title="post.title" />

    <BlogLayout>
        <article class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
            <Link
                href="/"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to all posts
            </Link>

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-2 text-sm font-medium text-zinc-500">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-[10px] font-bold text-white">
                        {{ initials(post.author) }}
                    </span>
                    <span class="text-zinc-900">{{ post.author }}</span>
                    <span class="text-zinc-300">&middot;</span>
                    <time>{{ post.published_at }}</time>
                </div>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl sm:leading-tight">
                    {{ post.title }}
                </h1>
                <div v-if="post.categories.length || post.tags.length" class="mt-6 flex flex-wrap gap-2">
                    <Link
                        v-for="category in post.categories"
                        :key="category.id"
                        :href="route('posts.category', category.slug)"
                        class="chip bg-indigo-50 px-3 py-1 text-indigo-700 transition hover:bg-indigo-100"
                    >
                        {{ category.name }}
                    </Link>
                    <Link
                        v-for="tag in post.tags"
                        :key="tag.id"
                        :href="route('posts.tag', tag.slug)"
                        class="chip bg-zinc-100 px-3 py-1 font-medium text-zinc-600 transition hover:bg-zinc-200"
                    >
                        #{{ tag.name }}
                    </Link>
                </div>
            </header>

            <div class="mt-10 border-t border-zinc-100 pt-10">
                <MarkdownContent :content="post.content" />
            </div>

            <div class="mt-14 rounded-2xl border border-zinc-100 bg-zinc-50 p-6 sm:flex sm:items-center sm:gap-4">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-sm font-bold text-white">
                    {{ initials(post.author) }}
                </span>
                <div class="mt-3 sm:mt-0">
                    <p class="text-sm font-semibold text-zinc-900">{{ post.author }}</p>
                    <p class="text-sm text-zinc-500">Author of this post</p>
                </div>
                <Link
                    href="/"
                    class="btn-secondary mt-4 w-full sm:ms-auto sm:mt-0 sm:w-auto"
                >
                    More posts
                </Link>
            </div>
        </article>
    </BlogLayout>
</template>
