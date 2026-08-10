<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const flash = computed(() => usePage().props.flash ?? {});

const form = useForm({
    site_name: props.settings.site_name,
    tagline: props.settings.tagline ?? '',
    twitter_url: props.settings.twitter_url ?? '',
    github_url: props.settings.github_url ?? '',
    linkedin_url: props.settings.linkedin_url ?? '',
});

function submit() {
    form.patch(route('admin.settings.update'));
}
</script>

<template>
    <Head title="Site Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Site Settings</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <div
                v-if="flash.success"
                class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
            >
                {{ flash.success }}
            </div>

            <form class="card space-y-6 p-6" @submit.prevent="submit">
                <div>
                    <label class="label" for="site_name">Site name</label>
                    <input id="site_name" v-model="form.site_name" type="text" class="input" />
                    <InputError class="mt-2" :message="form.errors.site_name" />
                </div>

                <div>
                    <label class="label" for="tagline">Tagline</label>
                    <input id="tagline" v-model="form.tagline" type="text" class="input" />
                    <InputError class="mt-2" :message="form.errors.tagline" />
                </div>

                <div class="border-t border-zinc-200 pt-6 dark:border-zinc-800">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Social links</h3>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        Shown in the site footer.
                    </p>
                </div>

                <div>
                    <label class="label" for="twitter_url">X / Twitter URL</label>
                    <input id="twitter_url" v-model="form.twitter_url" type="url" class="input" />
                    <InputError class="mt-2" :message="form.errors.twitter_url" />
                </div>

                <div>
                    <label class="label" for="github_url">GitHub URL</label>
                    <input id="github_url" v-model="form.github_url" type="url" class="input" />
                    <InputError class="mt-2" :message="form.errors.github_url" />
                </div>

                <div>
                    <label class="label" for="linkedin_url">LinkedIn URL</label>
                    <input id="linkedin_url" v-model="form.linkedin_url" type="url" class="input" />
                    <InputError class="mt-2" :message="form.errors.linkedin_url" />
                </div>

                <div class="flex items-center justify-end border-t border-zinc-200 pt-6 dark:border-zinc-800">
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        Save settings
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
