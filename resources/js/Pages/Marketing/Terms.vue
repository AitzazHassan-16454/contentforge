<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link } from '@inertiajs/vue3';

useReveal();

const sections = [
    {
        title: 'Acceptance of terms',
        body: 'By accessing or using ContentForge, you agree to be bound by these Terms of Service. If you do not agree with any part of these terms, please do not use the service.',
    },
    {
        title: 'Your account',
        body: 'You are responsible for safeguarding your account credentials and for all activity that happens under your account. You must provide accurate information when you register, and you must be at least 13 years old to use ContentForge.',
    },
    {
        title: 'Your content',
        body: 'You retain ownership of all content you write, generate, and publish. You are solely responsible for the content you publish, including ensuring it does not violate any laws or third-party rights. By publishing, you grant ContentForge a limited license to display and distribute your content on the platform.',
    },
    {
        title: 'Acceptable use',
        body: 'You agree not to use ContentForge to publish illegal content, spam, malware, or content that infringes the rights of others. We may remove content or suspend accounts that violate these terms.',
    },
    {
        title: 'Service availability',
        body: 'We aim to keep ContentForge reliable, but the service is provided "as is" and we do not guarantee uninterrupted availability. We may occasionally perform maintenance that results in brief downtime.',
    },
    {
        title: 'Limitation of liability',
        body: 'To the maximum extent permitted by law, ContentForge is not liable for indirect, incidental, or consequential damages arising from your use of the service. Our total liability is limited to the amount you paid us in the twelve months before the claim.',
    },
    {
        title: 'Changes to these terms',
        body: 'We may update these terms from time to time. Significant changes will be announced on the site. Continued use of ContentForge after changes take effect means you accept the updated terms.',
    },
];
</script>

<template>
    <Head title="Terms of Service" />

    <BlogLayout>
        <section class="relative overflow-hidden border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-600 via-primary-500 to-primary-300"></div>

            <div class="relative mx-auto max-w-3xl px-4 py-16 text-center sm:px-6">
                <p class="animate-fade-in-down text-sm font-semibold uppercase tracking-widest text-primary-700 dark:text-primary-500">Legal</p>
                <h1 class="animate-fade-in-up mx-auto mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 anim-delay-1 sm:text-5xl">
                    Terms of Service
                </h1>
                <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2">
                    Last updated: August 2026.
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <p data-reveal class="text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
                These Terms of Service govern your use of ContentForge. Please read them carefully before using the service.
            </p>

            <div class="mt-12 space-y-10">
                <div v-for="(section, index) in sections" :key="section.title" data-reveal :style="{ '--reveal-delay': `${Math.min(index, 4) * 60}ms` }">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ section.title }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ section.body }}</p>
                </div>
            </div>

            <div data-reveal class="card mt-14 p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Questions about these terms? <Link :href="route('contact.show')" class="font-semibold text-primary-700 hover:text-primary-600 dark:text-primary-500">Contact us</Link>.
                </p>
            </div>
        </section>
    </BlogLayout>
</template>
