<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary-600 text-sm font-semibold leading-5 text-zinc-900 focus:outline-none focus:border-primary-800 transition duration-150 ease-in-out dark:text-zinc-50 dark:focus:border-primary-500'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-zinc-500 hover:text-zinc-800 hover:border-zinc-300 focus:outline-none focus:text-zinc-800 focus:border-zinc-300 transition duration-150 ease-in-out dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:border-zinc-700 dark:focus:text-zinc-100 dark:focus:border-zinc-700',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
