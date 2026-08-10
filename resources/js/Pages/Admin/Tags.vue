<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    tags: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const flash = computed(() => usePage().props.flash ?? {});

const search = ref(props.filters.search ?? '');
const createForm = useForm({ name: '' });
const editing = ref(null);
const updateForm = useForm({ name: '' });

let searchTimer = null;
watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(route('admin.tags.index'), { search: value || undefined }, { preserveState: true, replace: true });
    }, 300);
});

function startEdit(tag) {
    editing.value = tag;
    updateForm.name = tag.name;
}

function submitCreate() {
    createForm.post(route('admin.tags.store'), { onSuccess: () => createForm.reset() });
}

function submitUpdate() {
    updateForm.patch(route('admin.tags.update', editing.value.id), {
        onSuccess: () => (editing.value = null),
    });
}

function destroy(tag) {
    if (confirm(`Delete "${tag.name}"?`)) {
        router.delete(route('admin.tags.destroy', tag.id));
    }
}
</script>

<template>
    <Head title="Manage Tags" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Tags</h2>
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
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Add a tag</h3>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="Tag name"
                        class="input flex-1"
                    />
                    <button type="submit" class="btn-primary" :disabled="createForm.processing">
                        Create
                    </button>
                </div>
                <InputError class="mt-2" :message="createForm.errors.name" />
            </form>

            <div class="card mt-6 overflow-hidden">
                <div class="flex items-center gap-3 border-b border-zinc-200 p-4 dark:border-zinc-800">
                    <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search tags…"
                        class="input !py-2"
                    />
                </div>

                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="tag in tags.data" :key="tag.id" class="flex items-center gap-3 px-5 py-3.5">
                        <template v-if="editing?.id === tag.id">
                            <form class="flex flex-1 gap-2" @submit.prevent="submitUpdate">
                                <input
                                    v-model="updateForm.name"
                                    type="text"
                                    class="input flex-1 !py-2"
                                />
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
                            </form>
                        </template>
                        <template v-else>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-zinc-900 dark:text-zinc-100">#{{ tag.name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">/tags/{{ tag.slug }} · {{ tag.posts_count }} posts</p>
                            </div>
                            <button
                                type="button"
                                class="rounded-xl bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                @click="startEdit(tag)"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
                                @click="destroy(tag)"
                            >
                                Delete
                            </button>
                        </template>
                    </li>
                    <li v-if="tags.data.length === 0" class="px-5 py-10 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        No tags found.
                    </li>
                </ul>
            </div>

            <div v-if="tags.links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <Link
                    v-for="(link, index) in tags.links"
                    :key="index"
                    :href="link.url"
                    :class="[
                        'inline-flex items-center rounded-xl border px-3 py-2 text-sm font-semibold transition',
                        link.active
                            ? 'border-primary-500 bg-primary-500 text-white shadow-sm'
                            : 'border-zinc-200 bg-white text-zinc-600 shadow-sm hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-400 dark:hover:border-zinc-600 dark:hover:bg-zinc-900',
                        !link.url && 'pointer-events-none opacity-50',
                    ]"
                    v-html="link.label"
                    :preserve-scroll="true"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
