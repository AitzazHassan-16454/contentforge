<script setup>
import BlogLayout from '@/Layouts/BlogLayout.vue';
import MarkdownContent from '@/Components/MarkdownContent.vue';
import PostAudioPlayer from '@/Components/PostAudioPlayer.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    reactions: {
        type: Object,
        default: () => ({ like: 0, fire: 0, star: 0 }),
    },
    user_reactions: {
        type: Array,
        default: () => [],
    },
    comments: {
        type: Array,
        default: () => [],
    }
});

const initials = (name) =>
    name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');

const currentSlide = ref(0);
let slideInterval = null;

const localReactions = ref({ ...props.reactions });
const localUserReactions = ref([...props.user_reactions]);

function toggleReaction(type) {
    if (!usePage().props.auth?.user) {
        router.visit(route('login'));
        return;
    }

    const index = localUserReactions.value.indexOf(type);
    if (index > -1) {
        localUserReactions.value.splice(index, 1);
        localReactions.value[type] = Math.max(0, localReactions.value[type] - 1);
    } else {
        localUserReactions.value.push(type);
        localReactions.value[type]++;
    }

    axios.post(route('posts.reactions.toggle', props.post.slug || props.post.id), { type })
        .then(response => {
            localReactions.value = response.data.reactions;
            localUserReactions.value = response.data.user_reactions;
        })
        .catch(error => {
            console.error("Error toggling reaction", error);
            localReactions.value = { ...props.reactions };
            localUserReactions.value = [...props.user_reactions];
        });
}

onMounted(() => {
    if (props.post.gallery_images && props.post.gallery_images.length > 1) {
        slideInterval = setInterval(() => {
            currentSlide.value = (currentSlide.value + 1) % props.post.gallery_images.length;
        }, 2500);
    }
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});
</script>

<template>
    <Head :title="post.title" />

    <BlogLayout>
        <article class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
            <Link
                :href="route('posts.index')"
                class="group inline-flex items-center gap-2 text-sm font-bold text-zinc-500 transition-colors hover:text-primary-700 dark:text-zinc-400 dark:hover:text-primary-400"
            >
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to all posts
            </Link>

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-zinc-500 dark:text-zinc-400">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-violet-600 text-xs font-bold text-white shadow-md shadow-primary-500/30">
                            {{ initials(post.author) }}
                        </span>
                        <span class="text-zinc-900 dark:text-zinc-100">{{ post.author }}</span>
                    </div>
                    <span class="text-zinc-300 dark:text-zinc-600">&bull;</span>
                    <time>{{ post.published_at }}</time>
                    <span v-if="post.views" class="inline-flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ post.views }}
                    </span>
                </div>

                <h1 class="mt-6 font-display text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-5xl sm:leading-tight">
                    {{ post.title }}
                </h1>

                <div v-if="post.categories.length || post.tags.length" class="mt-6 flex flex-wrap gap-2">
                    <Link
                        v-for="category in post.categories"
                        :key="category.id"
                        :href="route('posts.category', category.slug)"
                        class="rounded-full border border-primary-200 bg-primary-50 px-3.5 py-1.5 text-xs font-bold text-primary-800 transition-all duration-300 hover:-translate-y-0.5 hover:bg-primary-100 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:bg-primary-500/20"
                    >
                        {{ category.name }}
                    </Link>
                    <Link
                        v-for="tag in post.tags"
                        :key="tag.id"
                        :href="route('posts.tag', tag.slug)"
                        class="rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 text-xs font-bold text-zinc-600 transition-all duration-300 hover:-translate-y-0.5 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800"
                    >
                        #{{ tag.name }}
                    </Link>
                </div>
            </header>

            <!-- Gallery Carousel -->
            <div v-if="post.gallery_images && post.gallery_images.length" class="relative mt-10 h-72 sm:h-[28rem] w-full overflow-hidden rounded-3xl shadow-2xl shadow-zinc-900/10 ring-1 ring-zinc-200 dark:ring-zinc-800">
                <transition-group name="fade" tag="div" class="relative h-full w-full bg-zinc-100 dark:bg-zinc-950">
                    <div
                        v-for="(img, index) in post.gallery_images"
                        v-show="currentSlide === index"
                        :key="img"
                        class="absolute inset-0 h-full w-full"
                    >
                        <img :src="img" class="h-full w-full object-cover transition-transform duration-1000 ease-out hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    </div>
                </transition-group>

                <div v-if="post.gallery_images.length > 1" class="absolute bottom-5 left-1/2 flex -translate-x-1/2 gap-2 z-10">
                    <button
                        v-for="(_, index) in post.gallery_images"
                        :key="index"
                        @click="currentSlide = index"
                        class="h-2 rounded-full transition-all duration-300"
                        :class="currentSlide === index ? 'bg-white w-6 shadow-[0_0_8px_rgba(255,255,255,0.8)]' : 'bg-white/50 w-2 hover:bg-white/80'"
                        aria-label="Go to slide"
                    ></button>
                </div>
            </div>

            <div class="mt-8">
                <PostAudioPlayer :post="post" />
            </div>

            <div class="mt-10 border-t border-zinc-200 pt-10 dark:border-zinc-800">
                <MarkdownContent :content="post.content" />
            </div>

            <!-- Reactions -->
            <div class="mt-12 flex items-center justify-center gap-4">
                <button
                    @click="toggleReaction('like')"
                    class="group flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-semibold transition-all duration-300"
                    :class="localUserReactions.includes('like') 
                        ? 'border-primary-200 bg-primary-50 text-primary-800 shadow-soft dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300' 
                        : 'border-zinc-200 bg-white text-zinc-600 hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-soft dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-primary-500/40'"
                >
                    <span class="text-xl transition-transform duration-300 group-hover:scale-125" :class="localUserReactions.includes('like') ? 'scale-110' : ''">👍</span>
                    <span>{{ localReactions.like || 0 }}</span>
                </button>

                <button
                    @click="toggleReaction('fire')"
                    class="group flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-semibold transition-all duration-300"
                    :class="localUserReactions.includes('fire') 
                        ? 'border-orange-200 bg-orange-50 text-orange-700 shadow-soft dark:border-orange-500/30 dark:bg-orange-500/10 dark:text-orange-400' 
                        : 'border-zinc-200 bg-white text-zinc-600 hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-soft dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-orange-500/40'"
                >
                    <span class="text-xl transition-transform duration-300 group-hover:scale-125" :class="localUserReactions.includes('fire') ? 'scale-110' : ''">🔥</span>
                    <span>{{ localReactions.fire || 0 }}</span>
                </button>

                <button
                    @click="toggleReaction('star')"
                    class="group flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-semibold transition-all duration-300"
                    :class="localUserReactions.includes('star') 
                        ? 'border-primary-200 bg-primary-50 text-primary-800 shadow-soft dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300' 
                        : 'border-zinc-200 bg-white text-zinc-600 hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-soft dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-primary-500/40'"
                >
                    <span class="text-xl transition-transform duration-300 group-hover:scale-125" :class="localUserReactions.includes('star') ? 'scale-110' : ''">⭐</span>
                    <span>{{ localReactions.star || 0 }}</span>
                </button>
            </div>

            <div class="relative mt-14 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-8 shadow-soft transition-all duration-300 hover:border-primary-200 hover:shadow-lift sm:flex sm:items-center sm:gap-6 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-primary-500/40">
                <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-violet-600 text-lg font-bold text-white shadow-md shadow-primary-500/30">
                    {{ initials(post.author) }}
                </span>
                <div class="mt-4 sm:mt-0">
                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ post.author }}</p>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Author of this post</p>
                </div>
                <Link
                    :href="route('posts.index')"
                    class="btn-primary mt-6 w-full sm:ms-auto sm:mt-0 sm:w-auto"
                >
                    More posts
                </Link>
            </div>
        </article>
    </BlogLayout>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.8s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
