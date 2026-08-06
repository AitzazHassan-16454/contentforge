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
        class="prose prose-zinc prose-headings:scroll-mt-24 prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:underline prose-pre:rounded-xl prose-pre:bg-zinc-900 dark:prose-invert dark:prose-a:text-indigo-400 max-w-none"
        v-html="html"
    />
</template>
