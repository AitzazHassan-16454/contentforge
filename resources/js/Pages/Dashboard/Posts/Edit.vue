<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import MarkdownContent from '@/Components/MarkdownContent.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const isEditing = computed(() => Boolean(props.post.id));
const flash = computed(() => usePage().props.flash ?? {});

const form = useForm({
    title: props.post.title ?? '',
    content: props.post.content ?? '',
    excerpt: props.post.excerpt ?? '',
    status: props.post.status ?? 'draft',
    category_ids: [...(props.post.category_ids ?? [])],
    tags: [...(props.post.tags ?? [])],
});

const tagInput = ref('');

const categoryId = computed({
    get: () => form.category_ids[0] ?? '',
    set: (value) => {
        form.category_ids = value ? [value] : [];
    },
});

function addTag() {
    const value = tagInput.value.trim();
    if (!value) return;

    const names = value.split(',').map((name) => name.trim()).filter(Boolean);
    for (const name of names) {
        if (!form.tags.some((tag) => tag.toLowerCase() === name.toLowerCase())) {
            form.tags.push(name);
        }
    }
    tagInput.value = '';
}

function removeTag(index) {
    form.tags.splice(index, 1);
}

function onTagKeydown(event) {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addTag();
    }
}

const previewMode = ref(false);
const wordCount = computed(() =>
    form.content
        .trim()
        .split(/\s+/)
        .filter((word) => word.length > 0).length,
);

const aiForm = useForm({
    topic: '',
    title: '',
    tone: 'professional',
    keywords: '',
    length: 'medium',
});

const tones = [
    { value: 'professional', label: 'Professional' },
    { value: 'conversational', label: 'Conversational' },
    { value: 'inspirational', label: 'Inspirational' },
    { value: 'technical', label: 'Technical' },
];

const lengths = [
    { value: 'short', label: 'Short (~500 words)' },
    { value: 'medium', label: 'Medium (~1,000 words)' },
    { value: 'long', label: 'Long (~1,500 words)' },
];

const generating = ref(false);
const streamedWords = ref(0);
const streamError = ref(null);
const abortController = ref(null);

const seo = ref({
    loading: false,
    error: null,
    suggestions: null,
});

function csrfToken() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[2]) : '';
}

async function suggestSeo() {
    if (seo.value.loading) return;

    seo.value.loading = true;
    seo.value.error = null;
    seo.value.suggestions = null;

    try {
        const response = await fetch(route('dashboard.posts.seo-suggestions'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                title: form.title,
                content: form.content,
                excerpt: form.excerpt,
                tags: form.tags,
            }),
        });

        if (!response.ok) {
            throw new Error('Could not generate suggestions. Check the draft and try again.');
        }

        seo.value.suggestions = await response.json();
    } catch (error) {
        seo.value.error = error.message;
    } finally {
        seo.value.loading = false;
    }
}

function applyTitle() {
    if (seo.value.suggestions?.title) {
        form.title = seo.value.suggestions.title;
    }
}

function applyMetaDescription() {
    if (seo.value.suggestions?.meta_description) {
        form.excerpt = seo.value.suggestions.meta_description;
    }
}

function applyTags() {
    for (const tag of seo.value.suggestions?.tags ?? []) {
        if (!form.tags.some((t) => t.toLowerCase() === tag.toLowerCase())) {
            form.tags.push(tag);
        }
    }
}

async function generate() {
    if (!aiForm.topic.trim()) {
        aiForm.setError('topic', 'Please describe a topic first.');
        return;
    }

    aiForm.clearErrors();
    generating.value = true;
    streamError.value = null;
    streamedWords.value = 0;
    form.content = '';
    previewMode.value = false;

    abortController.value = new AbortController();

    try {
        const url = route('dashboard.posts.ai.generate', {
            topic: aiForm.topic,
            title: aiForm.title || undefined,
            tone: aiForm.tone,
            keywords: aiForm.keywords || undefined,
            length: aiForm.length,
        });

        const response = await fetch(url, {
            headers: { Accept: 'text/event-stream' },
            signal: abortController.value.signal,
        });

        if (!response.ok) {
            throw new Error('Generation failed. Please check your AI provider configuration.');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const parts = buffer.split('\n\n');
            buffer = parts.pop();

            for (const part of parts) {
                const line = part.trim();
                if (!line.startsWith('data:')) continue;

                const data = line.slice(5).trim();
                if (data === '[DONE]') continue;

                try {
                    const event = JSON.parse(data);
                    if (event.type === 'text-delta') {
                        form.content += event.delta;
                        streamedWords.value = form.content
                            .trim()
                            .split(/\s+/)
                            .filter((word) => word.length > 0).length;
                    }
                } catch {
                    // Ignore malformed frames.
                }
            }
        }
    } catch (error) {
        if (error.name !== 'AbortError') {
            streamError.value = error.message;
        }
    } finally {
        generating.value = false;
        abortController.value = null;
    }
}

function stopGenerating() {
    abortController.value?.abort();
}

function saveDraft() {
    submit('draft');
}

function saveAndPublish() {
    submit('published');
}

function submit(status) {
    form.status = status;

    if (isEditing.value) {
        form.patch(route('dashboard.posts.update', props.post.id), { preserveScroll: true });
        return;
    }

    form.post(route('dashboard.posts.store'), { preserveScroll: true });
}

onBeforeUnmount(() => {
    abortController.value?.abort();
});
</script>

<template>
    <Head :title="isEditing ? `Edit ${post.title}` : 'New Post'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex min-w-0 items-center justify-between gap-3">
                <h2 class="truncate text-lg font-bold tracking-tight text-zinc-900">
                    {{ isEditing ? 'Edit Post' : 'New Post' }}
                </h2>
                <Link
                    :href="route('dashboard.posts.index')"
                    class="hidden shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 sm:inline-flex"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    My posts
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flash.success"
                    class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
                >
                    <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ flash.success }}
                </div>
            </Transition>

            <div class="grid items-start gap-6 lg:grid-cols-[1fr_360px]">
                <!-- Main column -->
                <div class="min-w-0 space-y-6">
                    <!-- Details -->
                    <div class="card p-6 sm:p-8">
                        <div class="space-y-6">
                            <div>
                                <label for="title" class="label">Title</label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="input mt-1.5 !px-0 !py-2 !text-2xl !font-bold !shadow-none !ring-0 placeholder:!text-zinc-300 focus:!ring-0"
                                    placeholder="An irresistible title..."
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div>
                                <label for="excerpt" class="label">Excerpt</label>
                                <textarea
                                    id="excerpt"
                                    v-model="form.excerpt"
                                    rows="2"
                                    class="input mt-1.5 resize-none"
                                    placeholder="A short summary shown on cards and in search results."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.excerpt" />
                            </div>

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="category" class="label">Category</label>
                                    <select
                                        id="category"
                                        v-model="categoryId"
                                        class="select mt-1.5"
                                    >
                                        <option value="">No category</option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.category_ids" />
                                </div>

                                <div>
                                    <label for="tags" class="label">Tags</label>
                                    <div class="mt-1.5 flex min-h-[2.75rem] flex-wrap items-center gap-1.5 rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-inset ring-zinc-300 transition focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500">
                                        <span
                                            v-for="(tag, index) in form.tags"
                                            :key="tag"
                                            class="chip bg-indigo-50 text-indigo-700"
                                        >
                                            {{ tag }}
                                            <button
                                                type="button"
                                                class="-me-0.5 text-indigo-300 transition hover:text-indigo-600"
                                                @click="removeTag(index)"
                                            >
                                                &times;
                                            </button>
                                        </span>
                                        <input
                                            id="tags"
                                            v-model="tagInput"
                                            type="text"
                                            class="min-w-[8rem] flex-1 border-0 bg-transparent p-0 text-sm text-zinc-900 placeholder:text-zinc-400 focus:ring-0"
                                            placeholder="Add a tag, press Enter"
                                            @keydown="onTagKeydown"
                                            @blur="addTag"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.tags" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Editor -->
                    <div class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-5 py-3 sm:px-6">
                            <div class="flex gap-1 rounded-xl bg-zinc-100 p-1">
                                <button
                                    type="button"
                                    @click="previewMode = false"
                                    :class="previewMode ? 'text-zinc-500 hover:text-zinc-800' : 'bg-white text-zinc-900 shadow-sm'"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold transition"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                    </svg>
                                    Write
                                </button>
                                <button
                                    type="button"
                                    @click="previewMode = true"
                                    :class="previewMode ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-800'"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold transition"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Preview
                                </button>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                </svg>
                                {{ wordCount.toLocaleString() }} words
                            </span>
                        </div>

                        <div class="p-5 sm:p-6">
                            <textarea
                                v-if="!previewMode"
                                v-model="form.content"
                                rows="24"
                                class="block w-full resize-y rounded-xl border-zinc-200 font-mono text-sm leading-relaxed shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Write in Markdown, or generate a first draft with AI..."
                            ></textarea>
                            <div v-else class="min-h-[36rem] rounded-xl border border-zinc-100 bg-zinc-50/50 p-6">
                                <MarkdownContent v-if="form.content" :content="form.content" />
                                <p v-else class="text-sm text-zinc-400">Nothing to preview yet.</p>
                            </div>
                            <InputError class="mt-3" :message="form.errors.content" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            @click="saveAndPublish"
                            class="btn-primary"
                            :disabled="form.processing"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            Save &amp; Publish
                        </button>
                        <button
                            type="button"
                            @click="saveDraft"
                            class="btn-secondary"
                            :disabled="form.processing"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Save Draft
                        </button>
                        <span v-if="form.processing" class="inline-flex items-center gap-2 text-sm text-zinc-500">
                            <svg class="h-4 w-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Saving&hellip;
                        </span>
                        <Link
                            v-if="isEditing && post.status === 'published'"
                            :href="route('posts.show', post.slug)"
                            class="ms-auto inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 transition hover:text-indigo-500"
                        >
                            View published post
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="min-w-0 space-y-6">
                    <!-- AI Assistant -->
                    <div class="card border-indigo-100 p-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-zinc-900">AI Assistant</h3>
                                <p class="text-xs text-zinc-500">Stream a full first draft</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label for="topic" class="label">Topic <span class="text-red-500">*</span></label>
                                <input
                                    id="topic"
                                    v-model="aiForm.topic"
                                    type="text"
                                    class="input mt-1.5"
                                    placeholder="e.g. Why Laravel is perfect for AI apps"
                                />
                                <InputError class="mt-1.5" :message="aiForm.errors.topic" />
                            </div>

                            <div>
                                <label for="ai-title" class="label">Suggested title</label>
                                <input
                                    id="ai-title"
                                    v-model="aiForm.title"
                                    type="text"
                                    class="input mt-1.5"
                                    placeholder="Optional"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="tone" class="label">Tone</label>
                                    <select
                                        id="tone"
                                        v-model="aiForm.tone"
                                        class="select mt-1.5"
                                    >
                                        <option v-for="tone in tones" :key="tone.value" :value="tone.value">
                                            {{ tone.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label for="length" class="label">Length</label>
                                    <select
                                        id="length"
                                        v-model="aiForm.length"
                                        class="select mt-1.5"
                                    >
                                        <option v-for="len in lengths" :key="len.value" :value="len.value">
                                            {{ len.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="keywords" class="label">Keywords</label>
                                <input
                                    id="keywords"
                                    v-model="aiForm.keywords"
                                    type="text"
                                    class="input mt-1.5"
                                    placeholder="Optional, comma separated"
                                />
                            </div>

                            <button
                                v-if="!generating"
                                type="button"
                                @click="generate"
                                class="btn-primary w-full"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                Generate with AI
                            </button>

                            <div v-else class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Generating draft&hellip;
                                    </p>
                                    <span class="shrink-0 text-xs font-medium text-indigo-500">{{ streamedWords.toLocaleString() }} words</span>
                                </div>
                                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-indigo-100">
                                    <div class="h-full w-full animate-pulse rounded-full bg-gradient-to-r from-indigo-500 to-violet-500"></div>
                                </div>
                                <button
                                    type="button"
                                    @click="stopGenerating"
                                    class="mt-3 w-full rounded-xl bg-white px-3 py-2 text-sm font-semibold text-indigo-700 shadow-sm ring-1 ring-inset ring-indigo-200 transition hover:bg-indigo-100"
                                >
                                    Stop generating
                                </button>
                            </div>

                            <p v-if="streamError" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600">
                                {{ streamError }}
                            </p>

                            <p class="text-xs leading-relaxed text-zinc-400">
                                Generated content is a draft for you to review and edit. Nothing is published
                                automatically.
                            </p>
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="card p-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-900 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-zinc-900">SEO &amp; Tags</h3>
                                <p class="text-xs text-zinc-500">AI title, meta &amp; tag suggestions</p>
                            </div>
                        </div>

                        <button
                            v-if="!seo.loading"
                            type="button"
                            @click="suggestSeo"
                            class="btn-secondary mt-5 w-full"
                            :disabled="form.content.trim().length < 50"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                            </svg>
                            Suggest SEO &amp; tags
                        </button>
                        <div v-else class="mt-5 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-sm font-semibold text-zinc-700">Analyzing draft&hellip;</p>
                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-200">
                                <div class="h-full w-full animate-pulse rounded-full bg-zinc-900"></div>
                            </div>
                        </div>

                        <p v-if="form.content.trim().length < 50 && !seo.loading" class="mt-2 text-xs text-zinc-400">
                            Write at least 50 characters before asking for suggestions.
                        </p>

                        <p v-if="seo.error" class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600">
                            {{ seo.error }}
                        </p>

                        <div v-if="seo.suggestions" class="mt-5 space-y-5">
                            <div v-if="seo.suggestions.title">
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Suggested title</p>
                                <p class="mt-1 text-sm font-medium text-zinc-800">{{ seo.suggestions.title }}</p>
                                <button
                                    type="button"
                                    @click="applyTitle"
                                    class="mt-1.5 text-xs font-bold text-indigo-600 transition hover:text-indigo-500"
                                >
                                    Apply title
                                </button>
                            </div>

                            <div v-if="seo.suggestions.meta_description">
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Meta description</p>
                                <p class="mt-1 text-sm leading-relaxed text-zinc-600">{{ seo.suggestions.meta_description }}</p>
                                <button
                                    type="button"
                                    @click="applyMetaDescription"
                                    class="mt-1.5 text-xs font-bold text-indigo-600 transition hover:text-indigo-500"
                                >
                                    Apply to excerpt
                                </button>
                            </div>

                            <div v-if="seo.suggestions.tags.length">
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Suggested tags</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span
                                        v-for="tag in seo.suggestions.tags"
                                        :key="tag"
                                        class="chip bg-zinc-100 text-zinc-700"
                                    >
                                        #{{ tag }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="applyTags"
                                    class="mt-1.5 text-xs font-bold text-indigo-600 transition hover:text-indigo-500"
                                >
                                    Add all tags
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
