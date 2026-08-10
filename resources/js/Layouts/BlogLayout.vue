<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const user = computed(() => usePage().props.auth?.user ?? null);
const settings = computed(() => usePage().props.settings ?? {});

const mobileOpen = ref(false);

watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

watch(
    () => usePage().url,
    () => {
        mobileOpen.value = false;
    },
);

const navLink =
    'rounded-full px-3.5 py-2 text-sm font-semibold text-zinc-600 transition-all duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800/70 dark:hover:text-white';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white dark:bg-zinc-950">
        <!-- ============ Header ============ -->
        <header class="glass sticky top-0 z-50 border-b border-zinc-200/60 dark:border-zinc-800/60">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
                <Link href="/" class="group flex shrink-0 items-center gap-2.5">
                    <ApplicationLogo class="h-9 w-9 transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-105" />
                    <span class="font-display text-lg font-bold tracking-tight text-zinc-900 dark:text-white">
                        {{ settings.site_name || 'ContentForge' }}
                    </span>
                </Link>

                <nav class="hidden items-center gap-1 md:flex">
                    <Link :href="route('posts.index')" :class="navLink">Blog</Link>
                    <Link :href="route('posts.search')" :class="navLink">Search</Link>
                    <Link v-if="!user" :href="route('about')" :class="navLink">About</Link>
                </nav>

                <div class="hidden items-center gap-1.5 md:flex">
                    <ThemeToggle />
                    <template v-if="user">
                        <Link :href="route('dashboard.posts.index')" :class="navLink">My Posts</Link>
                        <Link :href="route('dashboard.posts.create')" class="btn-primary !px-3.5 !py-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>New Post</span>
                        </Link>
                        <button type="button" :class="navLink" @click="router.post(route('logout'))">
                            Log out
                        </button>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" :class="navLink">Log in</Link>
                        <Link :href="route('register')" class="btn-primary !px-3.5 !py-2">
                            Get started
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </template>
                </div>

                <div class="flex items-center gap-1 md:hidden">
                    <ThemeToggle />
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        :aria-label="mobileOpen ? 'Close menu' : 'Open menu'"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <svg v-if="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <nav v-if="mobileOpen" class="border-t border-zinc-200 bg-white px-4 pb-6 pt-3 dark:border-zinc-800 dark:bg-zinc-950 md:hidden">
                    <div class="flex flex-col gap-1">
                        <Link :href="route('posts.index')" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800/70">Blog</Link>
                        <Link :href="route('posts.search')" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800/70">Search</Link>
                        <Link v-if="!user" :href="route('about')" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800/70">About</Link>
                        <template v-if="user">
                            <Link :href="route('dashboard.posts.index')" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800/70">My Posts</Link>
                            <Link :href="route('dashboard.posts.create')" class="btn-primary mt-2 w-full">
                                New Post
                            </Link>
                            <button
                                type="button"
                                class="mt-1 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800/70"
                                @click="router.post(route('logout'))"
                            >
                                Log out
                            </button>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800/70">Log in</Link>
                            <Link :href="route('register')" class="btn-primary mt-2 w-full">
                                Get started
                            </Link>
                        </template>
                    </div>
                </nav>
            </Transition>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <!-- ============ Footer ============ -->
        <footer class="relative overflow-hidden border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-500/60 to-transparent"></div>
            <div class="pointer-events-none absolute -bottom-40 left-1/2 h-80 w-[42rem] -translate-x-1/2 rounded-full bg-primary-500/10 blur-3xl"></div>

            <div class="relative mx-auto max-w-6xl px-4 pb-10 pt-16 sm:px-6">
                <div class="grid gap-12 lg:grid-cols-12">
                    <div class="lg:col-span-4">
                        <Link href="/" class="flex items-center gap-2.5">
                            <ApplicationLogo class="h-8 w-8" />
                            <span class="font-display text-base font-bold tracking-tight text-zinc-900 dark:text-white">
                                {{ settings.site_name || 'ContentForge' }}
                            </span>
                        </Link>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                            {{ settings.tagline || 'AI-powered blogging for writers who want to ship more great content, faster.' }}
                        </p>

                        <div v-if="settings.twitter_url || settings.github_url || settings.linkedin_url" class="mt-6 flex items-center gap-2.5">
                            <a
                                v-if="settings.twitter_url"
                                :href="settings.twitter_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-600 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-primary-700 dark:hover:text-primary-400"
                                aria-label="X (Twitter)"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                            <a
                                v-if="settings.github_url"
                                :href="settings.github_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-600 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-primary-700 dark:hover:text-primary-400"
                                aria-label="GitHub"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.55v-2.15c-3.2.7-3.87-1.36-3.87-1.36-.52-1.33-1.28-1.68-1.28-1.68-1.04-.71.08-.7.08-.7 1.15.08 1.76 1.19 1.76 1.19 1.03 1.76 2.7 1.25 3.36.96.1-.75.4-1.25.72-1.54-2.55-.29-5.23-1.28-5.23-5.68 0-1.26.45-2.28 1.19-3.09-.12-.29-.52-1.46.11-3.04 0 0 .97-.31 3.17 1.18a11 11 0 015.78 0c2.2-1.49 3.16-1.18 3.16-1.18.63 1.58.24 2.75.12 3.04.74.81 1.19 1.83 1.19 3.09 0 4.41-2.69 5.38-5.25 5.67.41.35.77 1.05.77 2.12v3.14c0 .3.2.66.8.55A11.51 11.51 0 0023.5 12C23.5 5.65 18.35.5 12 .5z" />
                                </svg>
                            </a>
                            <a
                                v-if="settings.linkedin_url"
                                :href="settings.linkedin_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-primary-300 hover:text-primary-600 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-primary-700 dark:hover:text-primary-400"
                                aria-label="LinkedIn"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29zM5.34 7.43a2.06 2.06 0 110-4.12 2.06 2.06 0 010 4.12zM7.12 20.45H3.55V9h3.57v11.45z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8">
                        <div>
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Product</p>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li>
                                    <Link :href="route('posts.index')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Blog</Link>
                                </li>
                                <li>
                                    <Link :href="route('posts.search')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Search</Link>
                                </li>
                                <li>
                                    <Link :href="route('dashboard.posts.create')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Write a post</Link>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Company</p>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li>
                                    <Link :href="route('about')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">About</Link>
                                </li>
                                <li>
                                    <Link :href="route('contact.show')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Contact</Link>
                                </li>
                                <li>
                                    <Link :href="route('dashboard.posts.index')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Dashboard</Link>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">Legal</p>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li>
                                    <Link :href="route('privacy')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Privacy Policy</Link>
                                </li>
                                <li>
                                    <Link :href="route('terms')" class="text-zinc-500 transition-colors hover:text-primary-600 dark:text-zinc-400 dark:hover:text-primary-400">Terms of Service</Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-zinc-200 pt-6 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400 sm:flex-row">
                    <p>&copy; {{ new Date().getFullYear() }} {{ settings.site_name || 'ContentForge' }}. All rights reserved.</p>
                    <p class="flex items-center gap-1.5">
                        Crafted with
                        <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        and AI
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
