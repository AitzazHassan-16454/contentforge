<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import { useReveal } from '@/composables/useReveal';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

useReveal();

const page = usePage();

const success = computed(() => page.props.flash?.success ?? null);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const channels = [
    {
        title: 'Sales & plans',
        body: 'Questions about pricing, trials, or teams. We typically reply within one business day.',
        icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'Support & help',
        body: 'Stuck on something? Send us the details and we will get you unblocked.',
        icon: 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
    },
    {
        title: 'Press & partnerships',
        body: 'Collaborations, features, and press enquiries — we would love to hear from you.',
        icon: 'M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z',
    },
];
</script>

<template>
    <Head title="Contact" />

    <BlogLayout>
        <section class="relative overflow-hidden border-b border-zinc-100 dark:border-zinc-800">
            <div
                class="pointer-events-none absolute inset-0 dark:opacity-20"
                style="background-image: radial-gradient(circle at 1px 1px, rgb(228 228 231 / 0.6) 1px, transparent 0); background-size: 28px 28px;"
            ></div>
            <div class="animate-glow-pulse pointer-events-none absolute -top-32 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-indigo-100/70 blur-3xl dark:bg-indigo-500/20"></div>

            <div class="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6">
                <p class="animate-fade-in-down text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Contact</p>
                <h1 class="animate-fade-in-up mx-auto mt-4 max-w-2xl text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 anim-delay-1 sm:text-5xl">
                    We would love to hear from you
                </h1>
                <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-400 anim-delay-2">
                    Questions, feedback, or just want to say hi? Drop us a message and we will get back to you.
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="grid gap-10 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <p data-reveal class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Ways to reach us</p>
                    <h2 data-reveal class="mt-3 text-2xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">
                        Choose a topic
                    </h2>
                    <div class="mt-8 space-y-4">
                        <div v-for="(channel, index) in channels" :key="channel.title" data-reveal :style="{ '--reveal-delay': `${index * 80}ms` }" class="card flex items-start gap-4 p-5">
                            <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="channel.icon" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ channel.title }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ channel.body }}</p>
                            </div>
                        </div>
                    </div>
                    <p data-reveal class="mt-8 text-sm text-zinc-500 dark:text-zinc-400">
                        Prefer email? Write to us directly at
                        <a href="mailto:hello@contentforge.dev" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">hello@contentforge.dev</a>.
                    </p>
                </div>

                <div data-reveal class="card p-8 lg:col-span-3">
                    <div v-if="success" class="animate-fade-in-down mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ success }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="contact-name" class="label">Name</label>
                                <input
                                    id="contact-name"
                                    v-model="form.name"
                                    type="text"
                                    class="input mt-1.5"
                                    placeholder="Your name"
                                    autocomplete="name"
                                />
                                <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label for="contact-email" class="label">Email</label>
                                <input
                                    id="contact-email"
                                    v-model="form.email"
                                    type="email"
                                    class="input mt-1.5"
                                    placeholder="you@example.com"
                                    autocomplete="email"
                                />
                                <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div>
                            <label for="contact-subject" class="label">Subject</label>
                            <input
                                id="contact-subject"
                                v-model="form.subject"
                                type="text"
                                class="input mt-1.5"
                                placeholder="What is this about?"
                            />
                            <p v-if="form.errors.subject" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.subject }}</p>
                        </div>

                        <div>
                            <label for="contact-message" class="label">Message</label>
                            <textarea
                                id="contact-message"
                                v-model="form.message"
                                rows="6"
                                class="input mt-1.5 resize-none"
                                placeholder="Tell us everything…"
                            ></textarea>
                            <p v-if="form.errors.message" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.message }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                We only use this to reply to you. See our
                                <Link href="/privacy" class="font-semibold text-indigo-600 dark:text-indigo-400">privacy policy</Link>.
                            </p>
                            <button type="submit" class="btn-primary shrink-0" :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Send message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </BlogLayout>
</template>
