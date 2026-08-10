<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    comments: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const flash = computed(() => usePage().props.flash ?? {});

const search = ref(props.filters.search ?? '');
const filter = ref(props.filters.filter ?? 'all');

let searchTimer = null;
watch([search, filter], ([searchValue, filterValue]) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('admin.comments.index'),
            { search: searchValue || undefined, filter: filterValue !== 'all' ? filterValue : undefined },
            { preserveState: true, replace: true },
        );
    }, 300);
});

function approve(comment) {
    router.post(route('admin.comments.approve', comment.id));
}

function unapprove(comment) {
    router.post(route('admin.comments.unapprove', comment.id));
}

function destroy(comment) {
    if (confirm('Delete this comment?')) {
        router.delete(route('admin.comments.destroy', comment.id));
    }
}
</script>

<template>
    <Head title="Moderate Comments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Comments</h2>
        </template>

        <div class="mx-auto max-w-4xl">
            <div
                v-if="flash.success"
                class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
            >
                {{ flash.success }}
            </div>

            <div class="card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-zinc-200 p-4 dark:border-zinc-800 sm:flex-row sm:items-center">
                    <div class="flex flex-1 items-center gap-3">
                        <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Search comments…"
                            class="input !py-2"
                        />
                    </div>
                    <select v-model="filter" class="input !py-2 sm:w-40">
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="comment in comments.data" :key="comment.id" class="px-5 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        :class="comment.is_approved
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'"
                                        class="chip"
                                    >
                                        {{ comment.is_approved ? 'Approved' : 'Pending' }}
                                    </span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ comment.created_at }}</span>
                                </div>
                                <p class="mt-2 text-sm text-zinc-800 dark:text-zinc-200">{{ comment.body }}</p>
                                <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ comment.author?.name }}</span>
                                    · on
                                    <Link
                                        :href="route('posts.show', comment.post?.slug)"
                                        class="font-semibold text-primary-700 hover:underline dark:text-primary-500"
                                    >
                                        {{ comment.post?.title }}
                                    </Link>
                                </p>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                <button
                                    v-if="!comment.is_approved"
                                    type="button"
                                    class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20"
                                    @click="approve(comment)"
                                >
                                    Approve
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="rounded-xl bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                    @click="unapprove(comment)"
                                >
                                    Unapprove
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                                    @click="destroy(comment)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </li>
                    <li v-if="comments.data.length === 0" class="px-5 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No comments found.
                    </li>
                </ul>
            </div>

            <div v-if="comments.links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <Link
                    v-for="(link, index) in comments.links"
                    :key="index"
                    :href="link.url"
                    :class="[
                        'inline-flex items-center rounded-xl border px-3 py-2 text-sm font-semibold transition',
                        link.active
                            ? 'border-primary-500 bg-primary-500 text-white shadow-sm'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-sm hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-900',
                        !link.url && 'pointer-events-none opacity-50',
                    ]"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
