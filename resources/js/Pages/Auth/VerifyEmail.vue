<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Verify your email</h1>
            <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                Thanks for signing up! Before getting started, could you verify your email address by clicking
                on the link we just emailed to you? If you didn&rsquo;t receive the email, we will gladly send
                you another.
            </p>
        </div>

        <div
            class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
            v-if="verificationLinkSent"
        >
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <PrimaryButton
                class="w-full"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Sending…' : 'Resend Verification Email' }}
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full text-center text-sm font-semibold text-zinc-500 transition hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100"
            >
                Log Out
            </Link>
        </form>
    </GuestLayout>
</template>
