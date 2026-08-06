<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';

const showingSidebar = ref(false);

const isMyPostsActive = () =>
    ['dashboard.posts.index', 'dashboard.posts.edit'].includes(route().current());

const isNewPostActive = () => route().current() === 'dashboard.posts.create';

const isProfileActive = () => route().current() === 'profile.edit';

const initials = (name) =>
    name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-zinc-200 bg-white transition-smooth duration-300 dark:border-zinc-800 dark:bg-zinc-900 lg:translate-x-0"
            :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center border-b border-zinc-100 px-5 dark:border-zinc-800">
                <Link href="/" class="flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8" />
                    <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">ContentForge</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-7 overflow-y-auto px-3 py-6">
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Workspace
                    </p>
                    <div class="mt-2 space-y-1">
                        <Link
                            :href="route('dashboard.posts.index')"
                            :class="[
                                isMyPostsActive()
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'
                                    : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100',
                                'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition',
                            ]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            My Posts
                        </Link>
                        <Link
                            :href="route('dashboard.posts.create')"
                            :class="[
                                route().current('dashboard.posts.create')
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'
                                    : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100',
                                'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition',
                            ]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                            </svg>
                            New Post
                        </Link>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        Account
                    </p>
                    <div class="mt-2 space-y-1">
                        <Link
                            :href="route('profile.edit')"
                            :class="[
                                route().current('profile.edit')
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'
                                    : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100',
                                'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition',
                            ]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Profile
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- User -->
            <div class="shrink-0 border-t border-zinc-100 p-3 dark:border-zinc-800">
                <Dropdown align="left" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-xl p-2 text-start transition hover:bg-zinc-50 dark:hover:bg-zinc-800"
                        >
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-xs font-bold text-white">
                                {{ initials($page.props.auth.user.name) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $page.props.auth.user.email }}
                                </span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                            <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $page.props.auth.user.name }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $page.props.auth.user.email }}</p>
                        </div>
                        <div class="py-1">
                            <DropdownLink :href="route('profile.edit')">Profile settings</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </div>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showingSidebar"
                class="fixed inset-0 z-40 bg-zinc-900/50 backdrop-blur-sm lg:hidden"
                @click="showingSidebar = false"
            ></div>
        </Transition>

        <!-- Main column -->
        <div class="flex min-h-screen flex-col lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-zinc-200 bg-white/85 px-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/85 sm:px-6 lg:px-8">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 lg:hidden"
                    @click="showingSidebar = true"
                    aria-label="Open sidebar"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <slot name="header" />
                </div>

                <ThemeToggle />

                <Link
                    href="/"
                    class="hidden shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:inline-flex"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    View site
                </Link>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
