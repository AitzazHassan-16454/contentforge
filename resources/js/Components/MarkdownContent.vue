<script setup>
import { computed } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

const props = defineProps({
    content: {
        type: String,
        default: '',
    },
});

const html = computed(() =>
    DOMPurify.sanitize(
        marked.parse(props.content, {
            gfm: true,
            breaks: true,
        }),
    ),
);
</script>

<template>
    <div
        class="prose prose-zinc font-serif prose-headings:scroll-mt-24 prose-headings:font-display prose-h1:tracking-tight prose-h2:tracking-tight prose-a:text-primary-700 prose-a:no-underline hover:prose-a:underline prose-pre:rounded-xl prose-pre:bg-zinc-900 prose-pre:font-sans dark:prose-invert dark:prose-a:text-primary-500 max-w-none"
        v-html="html"
    />
</template>
