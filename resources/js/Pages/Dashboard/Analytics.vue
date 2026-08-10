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
    top_posts: {
        type: Array,
        required: true,
    },
});

const statCards = [
    { key: 'posts', label: 'Posts', color: 'from-primary-500 to-primary-600' },
    { key: 'published_posts', label: 'Published', color: 'from-emerald-500 to-teal-500' },
    { key: 'views', label: 'Total views', color: 'from-violet-500 to-purple-600' },
    { key: 'comments', label: 'Comments', color: 'from-amber-500 to-orange-500' },
    { key: 'reactions', label: 'Reactions', color: 'from-rose-500 to-red-500' },
];
</script>

<template>
    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Analytics</h2>
        </template>

        <div class="mx-auto max-w-5xl">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
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

            <section class="card mt-6 overflow-hidden">
                <header class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Views — last 30 days</h3>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Daily reads across all your posts</p>
                </header>
                <div class="p-5">
                    <AreaChart
                        :labels="views_series.labels"
                        :values="views_series.values"
                        name="Views"
                    />
                </div>
            </section>

            <section class="card mt-6 overflow-hidden">
                <header class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Top posts</h3>
                    <Link
                        :href="route('dashboard.posts.index')"
                        class="text-xs font-semibold text-primary-700 hover:text-primary-600 dark:text-primary-500"
                    >
                        All posts
                    </Link>
                </header>

                <div v-if="top_posts.length === 0" class="px-5 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    No posts yet. Write or generate your first post to see stats here.
                </div>

                <ul v-else class="divide-y divide-zinc-100 dark:divide-zinc-800">
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
                                :href="route('dashboard.posts.edit', post.id)"
                                class="block truncate text-sm font-bold text-zinc-900 transition hover:text-primary-700 dark:text-zinc-100 dark:hover:text-primary-500"
                            >
                                {{ post.title }}
                            </Link>
                            <span
                                :class="post.status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'"
                                class="chip mt-1"
                            >
                                {{ post.status }}
                            </span>
                        </div>
                        <div class="flex shrink-0 gap-5 text-right">
                            <div>
                                <p class="text-sm font-black text-zinc-900 dark:text-zinc-100">{{ post.views.toLocaleString() }}</p>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">views</p>
                            </div>
                            <div>
                                <p class="text-sm font-black text-zinc-900 dark:text-zinc-100">{{ post.comments }}</p>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">comments</p>
                            </div>
                            <div>
                                <p class="text-sm font-black text-zinc-900 dark:text-zinc-100">{{ post.reactions }}</p>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">reactions</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
