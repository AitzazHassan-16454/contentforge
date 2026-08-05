<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user ?? null);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <header class="sticky top-0 z-20 border-b border-zinc-100 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link href="/" class="flex items-center gap-2.5">
                    <ApplicationLogo class="h-8 w-8" />
                    <span class="text-lg font-bold tracking-tight text-zinc-900">ContentForge</span>
                </Link>

                <nav class="flex items-center gap-2 sm:gap-4">
                    <template v-if="user">
                        <Link
                            :href="route('dashboard.posts.index')"
                            class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 sm:inline-flex"
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
                            class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900"
                            @click="router.post(route('logout'))"
                        >
                            Log Out
                        </button>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900"
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
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-zinc-100 bg-zinc-50">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-2.5">
                        <ApplicationLogo class="h-7 w-7" />
                        <span class="text-base font-bold tracking-tight text-zinc-900">ContentForge</span>
                    </div>
                    <nav class="flex items-center gap-6 text-sm font-medium text-zinc-500">
                        <Link href="/" class="transition hover:text-zinc-900">Home</Link>
                        <Link
                            :href="route('dashboard.posts.index')"
                            class="transition hover:text-zinc-900"
                        >
                            Dashboard
                        </Link>
                        <Link
                            :href="route('dashboard.posts.create')"
                            class="transition hover:text-zinc-900"
                        >
                            New Post
                        </Link>
                    </nav>
                </div>
                <div class="mt-8 border-t border-zinc-200 pt-6 text-center text-sm text-zinc-500">
                    AI-powered blogging, built with Laravel, Inertia &amp; Vue.
                </div>
            </div>
        </footer>
    </div>
</template>
