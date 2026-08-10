<script setup>
import AreaChart from '@/Components/AreaChart.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
    views_series: {
        type: Object,
        required: true,
    },
    users_series: {
        type: Object,
        required: true,
    },
    top_posts: {
        type: Array,
        required: true,
    },
    top_authors: {
        type: Array,
        required: true,
    },
});

const statCards = [
    { key: 'users', label: 'Users', color: 'from-primary-500 to-primary-600' },
    { key: 'posts', label: 'Posts', color: 'from-emerald-500 to-teal-500' },
    { key: 'published_posts', label: 'Published', color: 'from-primary-500 to-primary-600' },
    { key: 'comments', label: 'Comments', color: 'from-amber-500 to-orange-500' },
    { key: 'reactions', label: 'Reactions', color: 'from-rose-500 to-red-500' },
    { key: 'views', label: 'Total views', color: 'from-violet-500 to-purple-600' },
    { key: 'contact_messages', label: 'Messages', color: 'from-zinc-600 to-zinc-500' },
];
</script>

<template>
    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Analytics</h2>
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

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <section class="card overflow-hidden">
                    <header class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Views — last 30 days</h3>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Platform-wide daily reads</p>
                    </header>
                    <div class="p-5">
                        <AreaChart
                            :labels="views_series.labels"
                            :values="views_series.values"
                            name="Views"
                        />
                    </div>
                </section>

                <section class="card overflow-hidden">
                    <header class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">New users — last 30 days</h3>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Daily registrations</p>
                    </header>
                    <div class="p-5">
                        <AreaChart
                            :labels="users_series.labels"
                            :values="users_series.values"
                            name="Users"
                            color="#10B981"
                        />
                    </div>
                </section>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <section class="card overflow-hidden">
                    <header class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Top posts</h3>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">All time</span>
                    </header>
                    <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <li
                            v-for="(post, index) in top_posts"
                            :key="post.id"
                            class="flex items-center gap-4 px-5 py-3.5"
                        >
                            <span class="w-6 shrink-0 text-center text-sm font-black text-zinc-300 dark:text-zinc-600">
                                {{ index + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="route('posts.show', post.slug)"
                                    class="block truncate text-sm font-bold text-zinc-900 transition hover:text-primary-700 dark:text-zinc-100 dark:hover:text-primary-500"
                                >
                                    {{ post.title }}
                                </Link>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ post.author?.name ?? 'Unknown' }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-black text-zinc-900 dark:text-zinc-100">
                                {{ post.views.toLocaleString() }}
                            </p>
                        </li>
                    </ul>
                </section>

                <section class="card overflow-hidden">
                    <header class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Top authors</h3>
                    </header>
                    <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <li
                            v-for="(author, index) in top_authors"
                            :key="author.id"
                            class="flex items-center gap-4 px-5 py-3.5"
                        >
                            <span class="w-6 shrink-0 text-center text-sm font-black text-zinc-300 dark:text-zinc-600">
                                {{ index + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ author.name }}</p>
                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ author.posts }} posts · {{ author.comments }} comments
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-black text-zinc-900 dark:text-zinc-100">
                                {{ author.views.toLocaleString() }}
                            </p>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
