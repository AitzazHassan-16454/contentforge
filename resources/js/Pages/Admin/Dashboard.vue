<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
    latestUsers: {
        type: Array,
        required: true,
    },
    latestPosts: {
        type: Array,
        required: true,
    },
    latestComments: {
        type: Array,
        required: true,
    },
});

const statCards = [
    { key: 'users', label: 'Users', color: 'from-primary-500 to-primary-600' },
    { key: 'posts', label: 'Posts', color: 'from-emerald-500 to-teal-500' },
    { key: 'published_posts', label: 'Published', color: 'from-primary-500 to-primary-600' },
    { key: 'comments', label: 'Comments', color: 'from-amber-500 to-orange-500' },
    { key: 'pending_comments', label: 'Pending comments', color: 'from-rose-500 to-red-500' },
    { key: 'reactions', label: 'Reactions', color: 'from-primary-500 to-primary-600' },
    { key: 'contact_messages', label: 'Messages', color: 'from-zinc-600 to-zinc-500' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Admin Dashboard</h2>
        </template>

        <div class="mx-auto max-w-5xl">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <div v-for="card in statCards" :key="card.key" class="card p-4">
                    <div
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-sm"
                        :class="card.color"
                    >
                        <span class="text-lg font-bold">{{ stats[card.key] }}</span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ card.label }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <section class="card overflow-hidden">
                    <header class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Latest users</h3>
                        <Link
                            :href="route('admin.users.index')"
                            class="text-xs font-semibold text-primary-700 hover:text-primary-600 dark:text-primary-500"
                        >
                            View all
                        </Link>
                    </header>
                    <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <li v-for="user in latestUsers" :key="user.id" class="flex items-center gap-3 px-5 py-3">
                            <span
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-xs font-bold text-white"
                            >
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ user.name }}</p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ user.email }}</p>
                            </div>
                            <span
                                v-for="role in user.roles"
                                :key="role"
                                class="chip bg-primary-50 text-primary-800 dark:bg-primary-600/15 dark:text-primary-300"
                            >
                                {{ role }}
                            </span>
                        </li>
                    </ul>
                </section>

                <section class="card overflow-hidden">
                    <header class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Latest posts</h3>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Updated</span>
                    </header>
                    <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <li v-for="post in latestPosts" :key="post.id" class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="post.status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'"
                                    class="chip"
                                >
                                    {{ post.status }}
                                </span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ post.updated_at }}</span>
                            </div>
                            <Link
                                :href="route('dashboard.posts.edit', post.id)"
                                class="mt-1.5 block truncate text-sm font-bold text-zinc-900 transition hover:text-primary-700 dark:text-zinc-100 dark:hover:text-primary-500"
                            >
                                {{ post.title }}
                            </Link>
                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ post.author?.name ?? 'Unknown' }} · {{ post.views }} views
                            </p>
                        </li>
                    </ul>
                </section>
            </div>

            <section class="card mt-6 overflow-hidden">
                <header class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Latest comments</h3>
                    <Link
                        :href="route('admin.comments.index')"
                        class="text-xs font-semibold text-primary-700 hover:text-primary-600 dark:text-primary-500"
                    >
                        View all
                    </Link>
                </header>
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="comment in latestComments" :key="comment.id" class="px-5 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm text-zinc-800 dark:text-zinc-200">{{ comment.body }}</p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ comment.author?.name }}</span>
                                    on
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ comment.post?.title }}</span>
                                    · {{ comment.created_at }}
                                </p>
                            </div>
                            <span
                                :class="comment.is_approved ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'"
                                class="chip shrink-0"
                            >
                                {{ comment.is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
