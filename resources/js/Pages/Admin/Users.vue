<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const flash = computed(() => usePage().props.flash ?? {});

const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? 'all');

let searchTimer = null;
watch([search, role], ([searchValue, roleValue]) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            route('admin.users.index'),
            { search: searchValue || undefined, role: roleValue !== 'all' ? roleValue : undefined },
            { preserveState: true, replace: true },
        );
    }, 300);
});

function changeRole(user, event) {
    if (confirm(`Set ${user.name} as ${event.target.value}?`)) {
        router.patch(route('admin.users.update', user.id), { role: event.target.value });
    } else {
        event.target.value = user.roles[0] ?? '';
    }
}

function destroy(user) {
    if (confirm(`Delete ${user.name}? This will remove their account, posts, and comments.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
}
</script>

<template>
    <Head title="Manage Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Users</h2>
        </template>

        <div class="mx-auto max-w-5xl">
            <div
                v-if="flash.success"
                class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="mb-6 flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"
            >
                {{ flash.error }}
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
                            placeholder="Search by name, email, or username…"
                            class="input !py-2"
                        />
                    </div>
                    <select v-model="role" class="input !py-2 sm:w-40">
                        <option value="all">All roles</option>
                        <option v-for="option in roles" :key="option" :value="option">{{ option }}</option>
                    </select>
                </div>

                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="user in users.data" :key="user.id" class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-xs font-bold text-white"
                            >
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ user.name }}
                                    <span class="font-normal text-zinc-500 dark:text-zinc-400">@{{ user.username }}</span>
                                </p>
                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ user.email }} · {{ user.posts_count }} posts · {{ user.comments_count }} comments · joined {{ user.created_at }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <select
                                class="input !py-2"
                                :value="user.roles[0] ?? null"
                                @change="changeRole(user, $event)"
                            >
                                <option value="" disabled>Select role</option>
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                            <button
                                type="button"
                                class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                                @click="destroy(user)"
                            >
                                Delete
                            </button>
                        </div>
                    </li>
                    <li v-if="users.data.length === 0" class="px-5 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No users found.
                    </li>
                </ul>
            </div>

            <div v-if="users.links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <Link
                    v-for="(link, index) in users.links"
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
