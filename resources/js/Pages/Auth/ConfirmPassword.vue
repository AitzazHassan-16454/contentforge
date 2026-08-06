<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Confirm your password</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                This is a secure area of the application. Please confirm your password before continuing.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton
                class="w-full"
                :class="{ 'opacity-60': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Confirming…' : 'Confirm' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
