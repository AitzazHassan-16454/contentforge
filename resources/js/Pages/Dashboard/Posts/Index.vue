<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const props = defineProps({
    posts: {
        type: Object,
        required: true,
    },
});

const flash = computed(() => usePage().props.flash ?? {});

function togglePublish(post) {
    if (confirm(post.status === 'published' ? 'Unpublish this post?' : 'Publish this post?')) {
        router.post(route(`dashboard.posts.${post.status === 'published' ? 'unpublish' : 'publish'}`, post.id));
    }
}

function destroy(post) {
    if (confirm(`Delete "${post.title}"? This cannot be undone.`)) {
        router.delete(route('dashboard.posts.destroy', post.id));
    }
}
</script>

<template>
    <Head title="My Posts" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">My Posts</h2>
                <Link
                    :href="route('dashboard.posts.create')"
                    class="btn-primary !px-3 !py-2"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">New Post</span>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-5xl">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flash.success"
                    class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                >
                    <svg class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ flash.success }}
                </div>
            </Transition>

            <div v-if="posts.data.length === 0" class="card p-14 text-center">
                <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-600 dark:from-indigo-500/20 dark:to-violet-500/20 dark:text-indigo-300">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                    </svg>
                </div>
                <p class="mt-5 text-lg font-bold text-zinc-900 dark:text-zinc-100">No posts yet</p>
                <p class="mx-auto mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
                    Generate your first post with AI, or start from a blank editor.
                </p>
                <Link
                    :href="route('dashboard.posts.create')"
                    class="btn-primary mt-6"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Generate with AI
                </Link>
            </div>

            <div v-else class="card overflow-hidden">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li
                        v-for="post in posts.data"
                        :key="post.id"
                        class="flex flex-col gap-4 p-5 transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    :class="[
                                        post.status === 'published'
                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30'
                                            : 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
                                    ]"
                                    class="chip ring-1 ring-inset"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="post.status === 'published' ? 'bg-emerald-500' : 'bg-amber-500'"
                                    ></span>
                                    {{ post.status }}
                                </span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    <template v-if="post.status === 'published'">
                                        Published {{ post.published_at }}
                                    </template>
                                    <template v-else>
                                        Updated {{ post.updated_at }}
                                    </template>
                                </span>
                            </div>
                            <Link
                                :href="route('dashboard.posts.edit', post.id)"
                                class="mt-2 block truncate text-lg font-bold text-zinc-900 transition hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400"
                            >
                                {{ post.title }}
                            </Link>
                            <div v-if="post.categories.length || post.tags.length" class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="category in post.categories"
                                    :key="category.id"
                                    class="chip bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300"
                                >
                                    {{ category.name }}
                                </span>
                                <span
                                    v-for="tag in post.tags"
                                    :key="tag.id"
                                    class="chip bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400"
                                >
                                    #{{ tag.name }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <button
                                type="button"
                                @click="togglePublish(post)"
                                class="rounded-xl px-3 py-2 text-sm font-semibold transition"
                                :class="
                                    post.status === 'published'
                                        ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20'
                                        : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20'
                                "
                            >
                                {{ post.status === 'published' ? 'Unpublish' : 'Publish' }}
                            </button>
                            <Link
                                :href="route('dashboard.posts.edit', post.id)"
                                class="rounded-xl bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                            >
                                Edit
                            </Link>
                            <Link
                                v-if="post.status === 'published'"
                                :href="route('posts.show', post.slug)"
                                class="rounded-xl bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                            >
                                View
                            </Link>
                            <button
                                type="button"
                                @click="destroy(post)"
                                class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                            >
                                Delete
                            </button>
                        </div>
                    </li>
                </ul>
            </div>

            <div v-if="posts.links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <Link
                    v-for="(link, index) in posts.links"
                    :key="index"
                    :href="link.url"
                    :class="[
                        'inline-flex items-center rounded-xl border px-3 py-2 text-sm font-semibold transition',
                        link.active
                            ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-sm hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-800',
                        !link.url && 'pointer-events-none opacity-50',
                    ]"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
