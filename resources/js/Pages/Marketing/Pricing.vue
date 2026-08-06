<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
});

useReveal();

const user = computed(() => usePage().props.auth?.user ?? null);
const ctaHref = computed(() => (user.value ? route('dashboard.posts.create') : route('register')));

const comparison = [
    { feature: 'Published posts', values: ['3', 'Unlimited', 'Unlimited'] },
    { feature: 'AI generations / month', values: ['25', '500', 'Unlimited'] },
    { feature: 'Markdown editor', values: [true, true, true] },
    { feature: 'Live preview', values: [true, true, true] },
    { feature: 'SEO assistant', values: [false, true, true] },
    { feature: 'Scheduled publishing', values: [false, true, true] },
    { feature: 'Analytics dashboard', values: [false, true, true] },
    { feature: 'Media library', values: [false, true, true] },
    { feature: 'Team members', values: ['1', '1', 'Up to 5'] },
    { feature: 'Roles & admin panel', values: [false, false, true] },
    { feature: 'Support', values: ['Community', 'Priority', 'Dedicated'] },
];

const faqs = [
    {
        q: 'Can I switch plans anytime?',
        a: 'Yes. Upgrade or downgrade instantly from your billing page. Changes take effect on your next billing cycle.',
    },
    {
        q: 'Is there a free trial for Pro?',
        a: 'The Pro plan includes a 14-day free trial. No credit card required to start.',
    },
    {
        q: 'Do unused AI generations roll over?',
        a: 'No — generations reset at the start of each month so everyone gets a fair allocation.',
    },
    {
        q: 'What happens if I downgrade?',
        a: 'Your published posts stay live. You just lose access to plan-exclusive features like scheduling and analytics until you upgrade again.',
    },
];

const openFaq = ref(0);

const renderValue = (value) => {
    if (value === true) {
        return {
            text: '',
            check: true,
        };
    }

    if (value === false) {
        return {
            text: '',
            check: false,
        };
    }

    return { text: value, check: null };
};
</script>

<template>
    <Head title="Pricing" />

    <BlogLayout>
        <section class="relative overflow-hidden border-b border-zinc-100 dark:border-zinc-800">
            <div
                class="pointer-events-none absolute inset-0 dark:opacity-20"
                style="background-image: radial-gradient(circle at 1px 1px, rgb(228 228 231 / 0.6) 1px, transparent 0); background-size: 28px 28px;"
            ></div>
            <div class="animate-glow-pulse pointer-events-none absolute -top-32 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-indigo-100/70 blur-3xl dark:bg-indigo-500/20"></div>

            <div class="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6">
                <p class="animate-fade-in-down text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Pricing</p>
                <h1 class="animate-fade-in-up mx-auto mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 anim-delay-1 sm:text-5xl">
                    Simple pricing that grows with your writing
                </h1>
                <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2">
                    Start free, upgrade when you need more. No hidden fees, no surprise charges — cancel anytime.
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div
                    v-for="(plan, index) in plans"
                    :key="plan.name"
                    data-reveal
                    :style="{ '--reveal-delay': `${index * 100}ms` }"
                    :class="[
                        plan.highlight
                            ? 'relative border-indigo-500 bg-white ring-2 ring-indigo-600 dark:border-indigo-500 dark:bg-zinc-900'
                            : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900',
                        'flex flex-col rounded-2xl border p-8 shadow-soft',
                    ]"
                >
                    <div v-if="plan.highlight" class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white">
                        Most popular
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ plan.name }}</h3>
                    <div class="mt-4 flex items-baseline gap-1.5">
                        <span class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">${{ plan.price }}</span>
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ plan.period }}</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ plan.description }}</p>
                    <ul class="mt-6 flex-1 space-y-3">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ feature }}
                        </li>
                    </ul>
                    <Link
                        :href="plan.price === 0 ? ctaHref : route('contact.show')"
                        :class="plan.highlight ? 'btn-primary mt-8' : 'btn-secondary mt-8'"
                    >
                        {{ plan.price === 0 ? 'Start for free' : 'Get started' }}
                    </Link>
                </div>
            </div>

            <p data-reveal class="mt-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                All prices in USD. Need something custom? <Link :href="route('contact.show')" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Talk to us</Link>.
            </p>
        </section>

        <section class="border-y border-zinc-100 bg-zinc-50/60 dark:border-zinc-800 dark:bg-zinc-900/40">
            <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
                <h2 data-reveal class="text-center text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                    Compare plans
                </h2>
                <div data-reveal class="card mt-12 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                <th class="px-6 py-4 font-semibold text-zinc-500 dark:text-zinc-400">Feature</th>
                                <th v-for="plan in plans" :key="plan.name" class="px-6 py-4 text-center font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ plan.name }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in comparison"
                                :key="row.feature"
                                class="border-b border-zinc-100 last:border-0 dark:border-zinc-800"
                            >
                                <td class="px-6 py-4 font-medium text-zinc-700 dark:text-zinc-300">{{ row.feature }}</td>
                                <td v-for="(value, index) in row.values" :key="index" class="px-6 py-4 text-center">
                                    <template v-if="renderValue(value).check === true">
                                        <svg class="mx-auto h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </template>
                                    <template v-else-if="renderValue(value).check === false">
                                        <svg class="mx-auto h-5 w-5 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                    <template v-else>{{ value }}</template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-3xl px-4 py-20 sm:px-6">
            <h2 data-reveal class="text-center text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                Billing questions
            </h2>
            <div class="mt-12 space-y-3">
                <div v-for="(faq, index) in faqs" :key="faq.q" data-reveal :style="{ '--reveal-delay': `${index * 60}ms` }" class="card overflow-hidden">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start"
                        :aria-expanded="openFaq === index"
                        @click="openFaq = openFaq === index ? -1 : index"
                    >
                        <span class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ faq.q }}</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-zinc-400 transition-smooth duration-300 dark:text-zinc-500"
                            :class="openFaq === index && 'rotate-180 text-indigo-500'"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-show="openFaq === index" class="px-6 pb-5">
                            <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ faq.a }}</p>
                        </div>
                    </Transition>
                </div>
            </div>
        </section>
    </BlogLayout>
</template>
