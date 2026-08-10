<script setup>
import { ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingSidebar = ref(false);

watch(
    () => usePage().url,
    () => {
        showingSidebar.value = false;
    },
);

const isMyPostsActive = () =>
    ['dashboard.posts.index', 'dashboard.posts.edit'].includes(route().current());

const isNewPostActive = () => route().current() === 'dashboard.posts.create';

const isAnalyticsActive = () => route().current() === 'dashboard.analytics';

const isProfileActive = () => route().current() === 'profile.edit';

const isAdminActive = () =>
    ['admin.dashboard', 'admin.analytics', 'admin.categories.*', 'admin.tags.*', 'admin.comments.*'].some((pattern) => route().current(pattern));

const isAdminUsersActive = () =>
    ['admin.users.*', 'admin.roles.*'].some((pattern) => route().current(pattern));

const initials = (name) =>
    name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');

const navActive = 'bg-gradient-to-r from-primary-600 to-violet-600 text-white shadow-glow';
const navIdle = 'text-zinc-400 hover:bg-zinc-800/70 hover:text-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800/70 dark:hover:text-zinc-100';
const navBase = 'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200';
</script>

<template>
    <div class="min-h-screen bg-zinc-100 dark:bg-zinc-950">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-zinc-800 bg-zinc-950 transition-smooth duration-300 dark:border-zinc-800 dark:bg-zinc-950 lg:translate-x-0"
            :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center border-b border-zinc-800 px-5">
                <Link href="/" class="flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8" />
                    <span class="text-lg font-bold tracking-tight text-white">ContentForge</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-7 overflow-y-auto px-3 py-6">
                <div>
                    <p class="px-3 text-xs font-bold uppercase tracking-widest text-zinc-500">
                        Workspace
                    </p>
                    <div class="mt-2 space-y-1">
                        <Link
                            :href="route('dashboard.posts.index')"
                            :class="[isMyPostsActive() ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            My Posts
                        </Link>
                        <Link
                            :href="route('dashboard.posts.create')"
                            :class="[isNewPostActive() ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                            </svg>
                            New Post
                        </Link>
                        <Link
                            :href="route('dashboard.analytics')"
                            :class="[isAnalyticsActive() ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            Analytics
                        </Link>
                    </div>
                </div>

                <div v-if="$page.props.auth.user?.is_staff">
                    <p class="px-3 text-xs font-bold uppercase tracking-widest text-zinc-500">
                        Admin
                    </p>
                    <div class="mt-2 space-y-1">
                        <Link
                            :href="route('admin.dashboard')"
                            :class="[isAdminActive() ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                            </svg>
                            Admin Panel
                        </Link>
                        <Link
                            :href="route('admin.analytics')"
                            :class="[route().current('admin.analytics') ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            Analytics
                        </Link>
                        <Link
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('admin.users.index')"
                            :class="[isAdminUsersActive() ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Users & Roles
                        </Link>
                        <Link
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('admin.roles.index')"
                            :class="[route().current('admin.roles.*') ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            Roles
                        </Link>
                        <Link
                            v-if="$page.props.auth.user?.is_admin"
                            :href="route('admin.settings.edit')"
                            :class="[route().current('admin.settings.*') ? navActive : navIdle, navBase]"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </Link>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-xs font-bold uppercase tracking-widest text-zinc-500">
                        Account
                    </p>
                    <div class="mt-2 space-y-1">
                        <Link
                            :href="route('profile.edit')"
                            :class="[isProfileActive() ? navActive : navIdle, navBase]"
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
            <div class="shrink-0 border-t border-zinc-800 p-3">
                <Dropdown align="left" width="48" up>
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-xl p-2 text-start transition-all duration-200 hover:bg-zinc-800/70"
                        >
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-violet-600 text-xs font-bold text-white shadow-md shadow-primary-500/30">
                                {{ initials($page.props.auth.user.name) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-white">
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <span class="block truncate text-xs text-zinc-400">
                                    {{ $page.props.auth.user.email }}
                                </span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
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
                class="fixed inset-0 z-40 bg-zinc-950/60 backdrop-blur-sm lg:hidden"
                @click="showingSidebar = false"
            ></div>
        </Transition>

        <!-- Main column -->
        <div class="flex min-h-screen flex-col lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-zinc-200 bg-white/80 px-4 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/70 sm:px-6 lg:px-8">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-zinc-500 transition-all duration-200 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 lg:hidden"
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
                    class="hidden shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-500 transition-all duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:inline-flex"
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
