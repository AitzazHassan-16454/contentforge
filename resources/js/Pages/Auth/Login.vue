<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-8 text-center">
            <h1 class="font-display text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50">Welcome back</h1>
            <p class="mt-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                Sign in to continue writing with AI.
            </p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-semibold text-primary-700 transition-colors hover:text-primary-600 dark:text-primary-400"
                    >
                        Forgot password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2.5">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-zinc-600 dark:text-zinc-300">Remember me</span>
            </label>

            <PrimaryButton
                class="w-full"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Signing in…' : 'Sign in' }}
            </PrimaryButton>

            <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                Don&rsquo;t have an account?
                <Link :href="route('register')" class="font-semibold text-primary-700 transition-colors hover:text-primary-600 dark:text-primary-400">
                    Create one
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
