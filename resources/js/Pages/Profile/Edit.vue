<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Profile</h2>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="card p-6 sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="card p-6 sm:p-8">
                <UpdatePasswordForm />
            </div>

            <div class="card p-6 sm:p-8">
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
