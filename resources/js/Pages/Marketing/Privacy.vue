<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link } from '@inertiajs/vue3';

useReveal();

const sections = [
    {
        title: 'Information we collect',
        body: 'When you create an account we collect your name, email address, and a username. If you upload a cover image or avatar, we store that file too. We also collect limited technical data — like browser type and IP address — to keep the service secure and to diagnose problems.',
    },
    {
        title: 'How we use your information',
        body: 'We use your information to run the service: to let you write and publish posts, to keep your drafts private, to send you essential service emails, and to improve the product. We do not sell your personal information to anyone.',
    },
    {
        title: 'AI-generated content',
        body: 'Content you generate with our AI tools is processed by a third-party AI provider (such as OpenAI, Anthropic, or Google) to produce your drafts. You retain ownership of the content you create, and drafts remain private to your account.',
    },
    {
        title: 'Cookies & local storage',
        body: 'We use cookies for authentication and local storage to remember your theme preference. These are required for the core functionality of the site.',
    },
    {
        title: 'Data retention',
        body: 'We keep your account data for as long as your account is active. When you delete your account, your posts and personal information are permanently removed from our systems, subject to legal obligations that may require us to retain certain records.',
    },
    {
        title: 'Your rights',
        body: 'You may access, correct, export, or delete your personal information at any time from your profile settings or by contacting us. We respond to all requests within 30 days.',
    },
    {
        title: 'Third-party services',
        body: 'We rely on trusted third parties for hosting, email delivery, and AI processing. Each provider processes data only to the extent needed to deliver the service, under agreements that require them to protect your data.',
    },
    {
        title: 'Changes to this policy',
        body: 'We may update this policy from time to time. Material changes will be announced on the site. Continued use of ContentForge after a change means you accept the updated policy.',
    },
];
</script>

<template>
    <Head title="Privacy Policy" />

    <BlogLayout>
        <section class="relative overflow-hidden border-b border-zinc-100 dark:border-zinc-800">
            <div
                class="pointer-events-none absolute inset-0 dark:opacity-20"
                style="background-image: radial-gradient(circle at 1px 1px, rgb(228 228 231 / 0.6) 1px, transparent 0); background-size: 28px 28px;"
            ></div>
            <div class="animate-glow-pulse pointer-events-none absolute -top-32 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-indigo-100/70 blur-3xl dark:bg-indigo-500/20"></div>

            <div class="relative mx-auto max-w-3xl px-4 py-16 text-center sm:px-6">
                <p class="animate-fade-in-down text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Legal</p>
                <h1 class="animate-fade-in-up mx-auto mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 anim-delay-1 sm:text-5xl">
                    Privacy Policy
                </h1>
                <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2">
                    Last updated: August 2026. Your privacy matters to us.
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <p data-reveal class="text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
                This Privacy Policy explains what information ContentForge collects, how it is used, and the choices you have.
                By using ContentForge you agree to the practices described here.
            </p>

            <div class="mt-12 space-y-10">
                <div v-for="(section, index) in sections" :key="section.title" data-reveal :style="{ '--reveal-delay': `${Math.min(index, 4) * 60}ms` }">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ section.title }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ section.body }}</p>
                </div>
            </div>

            <div data-reveal class="card mt-14 p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Questions about this policy? <Link :href="route('contact.show')" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Contact us</Link>.
                </p>
            </div>
        </section>
    </BlogLayout>
</template>
