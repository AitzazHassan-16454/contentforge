<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    roles: {
        type: Array,
        required: true,
    },
    permissions: {
        type: Array,
        required: true,
    },
});

const flash = computed(() => usePage().props.flash ?? {});

const createForm = useForm({
    name: '',
    permissions: [],
});
const editing = ref(null);
const updateForm = useForm({
    name: '',
    permissions: [],
});

const protectedRoles = ['admin', 'moderator', 'author'];

function togglePermission(form, permission) {
    const index = form.permissions.indexOf(permission);
    if (index === -1) {
        form.permissions.push(permission);
    } else {
        form.permissions.splice(index, 1);
    }
}

function startEdit(role) {
    editing.value = role;
    updateForm.name = role.name;
    updateForm.permissions = [...role.permissions];
}

function submitCreate() {
    createForm.post(route('admin.roles.store'), { onSuccess: () => createForm.reset() });
}

function submitUpdate() {
    updateForm.patch(route('admin.roles.update', editing.value.id), {
        onSuccess: () => (editing.value = null),
    });
}

function destroy(role) {
    if (confirm(`Delete role "${role.name}"?`)) {
        router.delete(route('admin.roles.destroy', role.id));
    }
}
</script>

<template>
    <Head title="Manage Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Roles & Permissions</h2>
        </template>

        <div class="mx-auto max-w-4xl">
            <div
                v-if="flash.success"
                class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="mb-6 flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"
            >
                {{ flash.error }}
            </div>

            <form class="card p-5" @submit.prevent="submitCreate">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Create a role</h3>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="Role name, e.g. editor"
                        class="input flex-1"
                    />
                    <button type="submit" class="btn-primary" :disabled="createForm.processing">
                        Create role
                    </button>
                </div>
                <InputError class="mt-2" :message="createForm.errors.name" />

                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-400">
                    Permissions
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <label
                        v-for="permission in permissions"
                        :key="permission"
                        class="cursor-pointer rounded-xl border border-zinc-200 px-3 py-2 text-sm font-semibold transition dark:border-zinc-700"
                        :class="createForm.permissions.includes(permission)
                            ? 'border-primary-700 bg-primary-50 text-primary-800 dark:bg-primary-600/15 dark:text-primary-300'
                            : 'bg-white text-zinc-600 hover:border-zinc-300 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:border-zinc-600'"
                    >
                        <input
                            type="checkbox"
                            class="sr-only"
                            :checked="createForm.permissions.includes(permission)"
                            @change="togglePermission(createForm, permission)"
                        />
                        {{ permission }}
                    </label>
                    <p v-if="permissions.length === 0" class="text-sm text-zinc-500 dark:text-zinc-400">
                        No permissions available.
                    </p>
                </div>
            </form>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <section
                    v-for="role in roles"
                    :key="role.id"
                    class="card overflow-hidden"
                >
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <div class="flex min-w-0 items-center gap-2">
                            <h3 class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ role.name }}</h3>
                            <span
                                v-if="protectedRoles.includes(role.name)"
                                class="chip bg-zinc-100 text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400"
                                title="Built-in role"
                            >
                                built-in
                            </span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ role.users_count }} users</span>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <button
                                type="button"
                                class="rounded-xl bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                @click="startEdit(role)"
                            >
                                Edit
                            </button>
                            <button
                                v-if="!protectedRoles.includes(role.name)"
                                type="button"
                                class="rounded-xl bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                                @click="destroy(role)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <div v-if="editing?.id === role.id" class="p-5">
                        <form @submit.prevent="submitUpdate">
                            <input v-model="updateForm.name" type="text" class="input" />
                            <InputError class="mt-2" :message="updateForm.errors.name" />
                            <div class="mt-3 flex flex-wrap gap-2">
                                <label
                                    v-for="permission in permissions"
                                    :key="permission"
                                    class="cursor-pointer rounded-xl border border-zinc-200 px-3 py-1.5 text-sm font-semibold transition dark:border-zinc-700"
                                    :class="updateForm.permissions.includes(permission)
                                        ? 'border-primary-700 bg-primary-50 text-primary-800 dark:bg-primary-600/15 dark:text-primary-300'
                                        : 'bg-white text-zinc-600 hover:border-zinc-300 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:border-zinc-600'"
                                >
                                    <input
                                        type="checkbox"
                                        class="sr-only"
                                        :checked="updateForm.permissions.includes(permission)"
                                        @change="togglePermission(updateForm, permission)"
                                    />
                                    {{ permission }}
                                </label>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button type="submit" class="btn-primary !px-3 !py-2" :disabled="updateForm.processing">
                                    Save
                                </button>
                                <button
                                    type="button"
                                    class="btn-secondary !px-3 !py-2"
                                    @click="editing = null"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <div v-else class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-400">
                            Permissions
                        </p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span
                                v-for="permission in role.permissions"
                                :key="permission"
                                class="chip bg-primary-50 text-primary-800 dark:bg-primary-600/15 dark:text-primary-300"
                            >
                                {{ permission }}
                            </span>
                            <span v-if="role.permissions.length === 0" class="text-sm text-zinc-500 dark:text-zinc-400">
                                No permissions assigned.
                            </span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
