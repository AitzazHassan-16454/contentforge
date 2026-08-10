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
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-600 text-start text-base font-medium text-primary-800 bg-primary-50 focus:outline-none focus:text-primary-900 focus:bg-primary-100 focus:border-primary-800 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-zinc-600 hover:text-zinc-800 hover:bg-zinc-50 hover:border-zinc-300 focus:outline-none focus:text-zinc-800 focus:bg-zinc-50 focus:border-zinc-300 transition duration-150 ease-in-out dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-900 dark:hover:border-zinc-700 dark:focus:text-zinc-100 dark:focus:bg-zinc-900 dark:focus:border-zinc-700',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
