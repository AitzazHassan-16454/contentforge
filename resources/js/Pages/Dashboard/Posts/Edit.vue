<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import MarkdownContent from '@/Components/MarkdownContent.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

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
    scheduled_at: props.post.scheduled_at ?? '',
    cover_image: null,
    remove_cover: false,
    new_gallery_images: [],
    remove_gallery_images: [],
});

const coverPreview = ref(props.post.cover_image_url ?? null);
const coverInput = ref(null);

function onCoverChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.cover_image = file;
        form.remove_cover = false;
        coverPreview.value = URL.createObjectURL(file);
    }
}

function removeCover() {
    form.cover_image = null;
    form.remove_cover = true;
    coverPreview.value = null;
    if (coverInput.value) {
        coverInput.value.value = '';
    }
}

const existingGalleryImages = ref(
    (props.post.gallery_images ?? []).map((path, index) => ({
        path,
        url: props.post.gallery_image_urls[index],
    }))
);
const newGalleryPreviews = ref([]);
const galleryInput = ref(null);

function onGalleryChange(event) {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        form.new_gallery_images.push(file);
        newGalleryPreviews.value.push({
            file,
            url: URL.createObjectURL(file),
        });
    });
    if (galleryInput.value) {
        galleryInput.value.value = '';
    }
}

function removeExistingGalleryImage(index) {
    const img = existingGalleryImages.value[index];
    form.remove_gallery_images.push(img.path);
    existingGalleryImages.value.splice(index, 1);
}

function removeNewGalleryImage(index) {
    form.new_gallery_images.splice(index, 1);
    newGalleryPreviews.value.splice(index, 1);
}

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

function unquote(value) {
    return value.trim().replace(/^["']|["']$/g, '').trim();
}

function parseFrontMatterLine(line, meta) {
    const trimmed = line.trim();

    const titleMatch = trimmed.match(/^title:\s*(.*)$/i);
    if (titleMatch) {
        meta.title = unquote(titleMatch[1]);
        return;
    }

    const excerptMatch = trimmed.match(/^excerpt:\s*(.*)$/i);
    if (excerptMatch) {
        meta.excerpt = unquote(excerptMatch[1]);
        return;
    }

    const tagsMatch = trimmed.match(/^tags:\s*(.*)$/i);
    if (tagsMatch) {
        const rest = tagsMatch[1].trim();
        if (rest) {
            meta.tags.push(
                ...rest
                    .replace(/^\[|\]$/g, '')
                    .split(',')
                    .map((tag) => unquote(tag))
                    .filter(Boolean),
            );
        }
        return;
    }

    const itemMatch = trimmed.match(/^-\s+(.+)$/);
    if (itemMatch) {
        meta.tags.push(unquote(itemMatch[1]));
    }
}

function finalizeGeneratedPost(meta) {
    let content = form.content
        .replace(/^```[a-z]*\s*\n?/i, '')
        .replace(/\s*```\s*$/, '')
        .trim();

    const heading = content.match(/^#\s+(.+?)\s*$/m);
    if (heading) {
        if (!meta.title) {
            meta.title = heading[1].trim();
        }
        content = content.replace(heading[0], '').trim();
    }

    form.content = content ? `${content}\n` : '';

    if (meta.title) {
        form.title = meta.title;
    }

    if (meta.excerpt) {
        form.excerpt = meta.excerpt;
    }

    if (meta.tags.length) {
        form.tags = [...meta.tags];
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

    const meta = { title: '', excerpt: '', tags: [] };
    let pending = '';
    let inFrontMatter = false;
    let frontMatterClosed = false;

    const appendContent = (chunk) => {
        form.content += chunk;
        streamedWords.value = form.content
            .trim()
            .split(/\s+/)
            .filter((word) => word.length > 0).length;
    };

    const handleLine = (line) => {
        if (!frontMatterClosed) {
            if (!inFrontMatter) {
                if (line.trim() === '' || line.trim().startsWith('```')) {
                    return;
                }

                if (line.trim() === '---') {
                    inFrontMatter = true;
                    return;
                }

                frontMatterClosed = true;
            } else {
                if (line.trim() === '---') {
                    frontMatterClosed = true;
                    return;
                }

                parseFrontMatterLine(line, meta);
                return;
            }
        }

        appendContent(`${line}\n`);
    };

    const handleDelta = (delta) => {
        pending += delta;

        let index;
        while ((index = pending.indexOf('\n')) !== -1) {
            handleLine(pending.slice(0, index));
            pending = pending.slice(index + 1);
        }
    };

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
            const lines = buffer.split('\n');
            buffer = lines.pop();

            for (const line of lines) {
                const part = line.trim();
                if (part.startsWith('data:')) {
                    const data = part.slice(5).trim();
                    if (data === '[DONE]') continue;

                    try {
                        const event = JSON.parse(data);
                        if (event.type === 'text-delta') {
                            handleDelta(event.delta);
                        }
                    } catch {
                        // Ignore malformed frames.
                    }
                }
            }
        }

        if (buffer.trim()) {
            const part = buffer.trim();
            if (part.startsWith('data:')) {
                const data = part.slice(5).trim();
                if (data !== '[DONE]') {
                    try {
                        const event = JSON.parse(data);
                        if (event.type === 'text-delta') {
                            handleDelta(event.delta);
                        }
                    } catch {
                        // Ignore malformed frames.
                    }
                }
            }
        }

        finalizeGeneratedPost(meta);
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
        form.transform((data) => ({
            ...data,
            _method: 'patch',
        })).post(route('dashboard.posts.update', props.post.id), { preserveScroll: true, preserveState: true });
        return;
    }

    form.post(route('dashboard.posts.store'), { preserveScroll: true });
}

const saveStatus = computed(() => {
    if (form.processing) {
        return { label: 'Saving&hellip;', busy: true };
    }

    if (form.isDirty) {
        return { label: 'Unsaved changes', busy: false };
    }

    return { label: 'All changes saved', busy: false };
});

function onKeydown(event) {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's') {
        event.preventDefault();
        saveDraft();
    }
}

const handleBeforeUnload = (event) => {
    if (form.isDirty && !form.processing) {
        event.preventDefault();
        event.returnValue = '';
    }
};

const handleNavigateBefore = (event) => {
    if (event.detail.visit.method === 'get' && form.isDirty && !form.processing) {
        if (!window.confirm('You have unsaved changes. Leave anyway?')) {
            return false;
        }
    }
};

const unsubscribeBefore = router.on('before', handleNavigateBefore);

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onBeforeUnmount(() => {
    abortController.value?.abort();
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('beforeunload', handleBeforeUnload);
    unsubscribeBefore();
});
</script>

<template>
    <Head :title="isEditing ? `Edit ${post.title}` : 'New Post'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex min-w-0 items-center justify-between gap-3">
                <h2 class="truncate text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ isEditing ? 'Edit Post' : 'New Post' }}
                </h2>
                <Link
                    :href="route('dashboard.posts.index')"
                    class="hidden shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-900 dark:hover:text-zinc-100 sm:inline-flex"
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
                    class="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                >
                    <svg class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                    class="input mt-1.5 !px-0 !py-2 !text-2xl !font-bold !shadow-none !ring-0 placeholder:!text-zinc-300 focus:!ring-0 dark:bg-transparent dark:placeholder:!text-zinc-600"
                                    placeholder="An irresistible title..."
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div>
                                <label class="label">Cover Image</label>
                                <div class="mt-1.5">
                                    <div v-if="coverPreview" class="relative overflow-hidden rounded-xl bg-zinc-100 border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700">
                                        <img :src="coverPreview" alt="Cover preview" class="w-full object-cover max-h-64" />
                                        <button
                                            type="button"
                                            @click="removeCover"
                                            class="absolute top-2 right-2 rounded-full bg-white/90 p-1.5 text-zinc-900 shadow-sm backdrop-blur-sm transition hover:bg-red-50 hover:text-red-600 dark:bg-zinc-950/90 dark:text-zinc-100 dark:hover:bg-red-500/20 dark:hover:text-red-400"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-else class="flex justify-center rounded-xl border border-dashed border-zinc-300 px-6 py-8 dark:border-zinc-700">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-zinc-300 dark:text-zinc-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="mt-4 flex justify-center text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                                <label for="cover_image" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-700 focus-within:ring-offset-2 hover:text-primary-600 dark:bg-transparent dark:text-primary-500 dark:focus-within:ring-offset-zinc-900 dark:hover:text-primary-300">
                                                    <span>Upload a file</span>
                                                    <input id="cover_image" ref="coverInput" name="cover_image" type="file" class="sr-only" accept="image/*" @change="onCoverChange" />
                                                </label>
                                            </div>
                                            <p class="text-xs leading-5 text-zinc-500 mt-1">PNG, JPG, GIF up to 10MB</p>
                                        </div>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.cover_image" />
                            </div>

                            <div>
                                <label class="label">Gallery Images (Carousel)</label>
                                <div class="mt-1.5 space-y-4">
                                    <div v-if="existingGalleryImages.length > 0 || newGalleryPreviews.length > 0" class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                        <div v-for="(img, index) in existingGalleryImages" :key="'existing-'+index" class="relative overflow-hidden rounded-xl bg-zinc-100 border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700">
                                            <img :src="img.url" class="h-24 w-full object-cover" />
                                            <button type="button" @click="removeExistingGalleryImage(index)" class="absolute top-1 right-1 rounded-full bg-white/90 p-1 text-zinc-900 shadow-sm backdrop-blur-sm transition hover:bg-red-50 hover:text-red-600 dark:bg-zinc-950/90 dark:text-zinc-100">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                        <div v-for="(img, index) in newGalleryPreviews" :key="'new-'+index" class="relative overflow-hidden rounded-xl bg-zinc-100 border border-zinc-200 dark:bg-zinc-900 dark:border-zinc-700">
                                            <img :src="img.url" class="h-24 w-full object-cover" />
                                            <button type="button" @click="removeNewGalleryImage(index)" class="absolute top-1 right-1 rounded-full bg-white/90 p-1 text-zinc-900 shadow-sm backdrop-blur-sm transition hover:bg-red-50 hover:text-red-600 dark:bg-zinc-950/90 dark:text-zinc-100">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex justify-center rounded-xl border border-dashed border-zinc-300 px-6 py-4 dark:border-zinc-700">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="mt-2 flex justify-center text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                                <label for="gallery_images" class="relative cursor-pointer rounded-md bg-white font-semibold text-primary-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-700 hover:text-primary-600 dark:bg-transparent dark:text-primary-500 dark:hover:text-primary-300">
                                                    <span>Upload images</span>
                                                    <input id="gallery_images" ref="galleryInput" name="gallery_images" type="file" multiple class="sr-only" accept="image/*" @change="onGalleryChange" />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.new_gallery_images" />
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
                                    <div class="mt-1.5 flex min-h-[2.75rem] flex-wrap items-center gap-1.5 rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-inset ring-zinc-300 transition focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary-600 dark:bg-zinc-950 dark:ring-zinc-700 dark:focus-within:ring-primary-600">
                                        <span
                                            v-for="(tag, index) in form.tags"
                                            :key="tag"
                                            class="chip bg-primary-50 text-primary-800 dark:bg-primary-600/15 dark:text-primary-300"
                                        >
                                            {{ tag }}
                                            <button
                                                type="button"
                                                class="-me-0.5 text-primary-300 transition hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-200"
                                                @click="removeTag(index)"
                                            >
                                                &times;
                                            </button>
                                        </span>
                                        <input
                                            id="tags"
                                            v-model="tagInput"
                                            type="text"
                                            class="min-w-[8rem] flex-1 border-0 bg-transparent p-0 text-sm text-zinc-900 placeholder:text-zinc-400 focus:ring-0 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                            placeholder="Add a tag, press Enter"
                                            @keydown="onTagKeydown"
                                            @blur="addTag"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.tags" />
                                </div>
                            </div>

                            <div>
                                <label for="scheduled_at" class="label">Schedule publish</label>
                                <input
                                    id="scheduled_at"
                                    v-model="form.scheduled_at"
                                    type="datetime-local"
                                    class="input mt-1.5"
                                />
                                <p class="mt-1.5 text-xs text-zinc-400 dark:text-zinc-400">
                                    Post stays a draft and publishes automatically at this time. Leave empty to publish manually.
                                </p>
                                <InputError class="mt-2" :message="form.errors.scheduled_at" />
                            </div>
                        </div>
                    </div>

                    <!-- Editor -->
                    <div class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-3 sm:px-6 dark:border-zinc-800">
                            <div class="flex gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-900">
                                <button
                                    type="button"
                                    @click="previewMode = false"
                                    :class="previewMode ? 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100' : 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-50'"
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
                                    :class="previewMode ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-50' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100'"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-sm font-semibold transition"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Preview
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    v-if="!generating"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="saveStatus.busy ? 'text-primary-700 dark:text-primary-500' : saveStatus.label === 'Unsaved changes' ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-400 dark:text-zinc-400'"
                                >
                                    <svg v-if="saveStatus.busy" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <svg v-else-if="saveStatus.label === 'Unsaved changes'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <svg v-else class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <span v-html="saveStatus.label"></span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-400 dark:text-zinc-400">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    </svg>
                                    {{ wordCount.toLocaleString() }} words
                                </span>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6">
                            <textarea
                                v-if="!previewMode"
                                v-model="form.content"
                                rows="24"
                                class="block w-full resize-y rounded-xl border-zinc-200 bg-white font-mono text-sm leading-relaxed text-zinc-900 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                placeholder="Write in Markdown, or generate a first draft with AI..."
                            ></textarea>
                            <div v-else class="min-h-[36rem] rounded-xl border border-zinc-200 bg-zinc-50/50 p-6 dark:border-zinc-800 dark:bg-zinc-950/50">
                                <MarkdownContent v-if="form.content" :content="form.content" />
                                <p v-else class="text-sm text-zinc-400 dark:text-zinc-400">Nothing to preview yet.</p>
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
                        <span v-if="form.processing" class="inline-flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <svg class="h-4 w-4 animate-spin text-primary-700 dark:text-primary-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Saving&hellip;
                        </span>
                        <span v-else class="hidden text-xs font-medium text-zinc-400 dark:text-zinc-400 sm:inline-flex">
                            Tip: press <kbd class="rounded-md bg-zinc-100 px-1.5 py-0.5 font-sans text-[10px] font-semibold text-zinc-600 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700">Ctrl</kbd> + <kbd class="rounded-md bg-zinc-100 px-1.5 py-0.5 font-sans text-[10px] font-semibold text-zinc-600 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-700">S</kbd> to save as draft
                        </span>
                        <Link
                            v-if="isEditing && post.status === 'published'"
                            :href="route('posts.show', post.slug)"
                            class="ms-auto inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700 transition hover:text-primary-600 dark:text-primary-500 dark:hover:text-primary-300"
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
                    <div class="card border-primary-100 p-6 dark:border-primary-600/20">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">AI Assistant</h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Generates a title, excerpt, tags &amp; a full draft</p>
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

                            <div v-else class="rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-600/30 dark:bg-primary-600/10">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="inline-flex items-center gap-2 text-sm font-semibold text-primary-800 dark:text-primary-300">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Generating draft&hellip;
                                    </p>
                                    <span class="shrink-0 text-xs font-medium text-primary-600 dark:text-primary-500">{{ streamedWords.toLocaleString() }} words</span>
                                </div>
                                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-primary-100 dark:bg-primary-600/20">
                                    <div class="h-full w-full animate-pulse rounded-full bg-gradient-to-r from-primary-600 to-primary-700"></div>
                                </div>
                                <button
                                    type="button"
                                    @click="stopGenerating"
                                    class="mt-3 w-full rounded-xl bg-white px-3 py-2 text-sm font-semibold text-primary-800 shadow-sm ring-1 ring-inset ring-primary-200 transition hover:bg-primary-100 dark:bg-zinc-950 dark:text-primary-300 dark:ring-primary-600/30 dark:hover:bg-zinc-900"
                                >
                                    Stop generating
                                </button>
                            </div>

                            <p v-if="streamError" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                                {{ streamError }}
                            </p>

                            <p class="text-xs leading-relaxed text-zinc-400 dark:text-zinc-400">
                                The generated title, excerpt, and tags are applied to your post automatically.
                                Everything stays a draft for you to review and edit — nothing is published
                                automatically.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
