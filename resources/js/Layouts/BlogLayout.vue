<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user ?? null);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white dark:bg-zinc-950">
        <header class="sticky top-0 z-20 border-b border-zinc-100 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link href="/" class="animate-fade-in flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8" />
                    <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">ContentForge</span>
                </Link>

                <nav class="animate-fade-in flex items-center gap-1 sm:gap-2 anim-delay-1">
                    <Link
                        :href="route('posts.search')"
                        title="Search posts"
                        aria-label="Search posts"
                        class="rounded-xl px-2.5 py-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </Link>
                    <template v-if="user">
                        <Link
                            :href="route('posts.index')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:inline-flex"
                        >
                            Blog
                        </Link>
                        <Link
                            :href="route('pricing')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:inline-flex"
                        >
                            Pricing
                        </Link>
                        <Link
                            :href="route('dashboard.posts.index')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 md:inline-flex"
                        >
                            My Posts
                        </Link>
                        <Link
                            :href="route('dashboard.posts.create')"
                            class="btn-primary !px-3.5 !py-2"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New Post
                        </Link>
                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                            @click="router.post(route('logout'))"
                        >
                            Log Out
                        </button>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('posts.index')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:inline-flex"
                        >
                            Blog
                        </Link>
                        <Link
                            :href="route('pricing')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 sm:inline-flex"
                        >
                            Pricing
                        </Link>
                        <Link
                            :href="route('about')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 lg:inline-flex"
                        >
                            About
                        </Link>
                        <Link
                            :href="route('login')"
                            class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="btn-primary !px-3.5 !py-2"
                        >
                            Get started
                        </Link>
                    </template>
                    <ThemeToggle />
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="animate-fade-in border-t border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-1">
                        <div class="flex items-center gap-2.5">
                            <ApplicationLogo class="h-7 w-7" />
                            <span class="text-base font-bold tracking-tight text-zinc-900 dark:text-zinc-100">ContentForge</span>
                        </div>
                        <p class="mt-4 max-w-xs text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                            AI-powered blogging for writers who want to ship more great content, faster.
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Product</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li>
                                <Link :href="route('posts.index')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Blog</Link>
                            </li>
                            <li>
                                <Link :href="route('pricing')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Pricing</Link>
                            </li>
                            <li>
                                <Link :href="route('dashboard.posts.create')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">New Post</Link>
                            </li>
                            <li>
                                <Link :href="route('posts.search')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Search</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Company</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li>
                                <Link :href="route('about')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">About</Link>
                            </li>
                            <li>
                                <Link :href="route('contact.show')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Contact</Link>
                            </li>
                            <li>
                                <Link :href="route('dashboard.posts.index')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Dashboard</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Legal</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li>
                                <Link :href="route('privacy')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Privacy Policy</Link>
                            </li>
                            <li>
                                <Link :href="route('terms')" class="text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Terms of Service</Link>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mt-10 border-t border-zinc-200 pt-6 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-500">
                    &copy; {{ new Date().getFullYear() }} ContentForge. AI-powered blogging, built with Laravel, Inertia &amp; Vue.
                </div>
            </div>
        </footer>
    </div>
</template>
