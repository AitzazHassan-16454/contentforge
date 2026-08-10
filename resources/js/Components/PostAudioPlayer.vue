<script setup>
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

// The URL of the audio file (empty until we have one).
const audioUrl = ref(props.post.audio_url ?? '');

// The Gemini TTS voices readers may pick from.
const voices = ['Kore', 'Puck', 'Zephyr', 'Aoede', 'Fenrir', 'Leda', 'Orus', 'Perseus', 'Charon'];

// The voice selected for the next generation.
const voice = ref(props.post.audio_voice ?? 'Kore');

// The voice the currently loaded audio was generated with.
const activeVoice = ref(props.post.audio_voice ?? 'Kore');

// Loading and error states.
const loading = ref(false);
const error = ref('');

// Player state.
const playing = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const speed = ref(1);

// The native <audio> element.
const audio = ref(null);

function formatTime(seconds) {
    if (!seconds || isNaN(seconds)) {
        return '0:00';
    }

    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);

    return `${minutes}:${String(secs).padStart(2, '0')}`;
}

async function generate() {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch(route('posts.audio', {
            post: props.post.slug,
            voice: voice.value,
        }));

        if (!response.ok) {
            throw new Error('Could not generate audio.');
        }

        const data = await response.json();
        audioUrl.value = data.audio_url;
        activeVoice.value = voice.value;

        // Wait for the <audio> element to pick up the new source, then play.
        setTimeout(() => {
            audio.value?.play()?.catch(() => {
                error.value = 'Sorry, the audio could not be played right now.';
            });
        }, 100);
    } catch (e) {
        error.value = 'Sorry, the audio could not be generated right now.';
    } finally {
        loading.value = false;
    }
}

function togglePlay() {
    if (!audioUrl.value) {
        generate();
        return;
    }

    if (playing.value) {
        audio.value?.pause();
    } else {
        audio.value?.play()?.catch(() => {
            error.value = 'Sorry, the audio could not be played right now.';
        });
    }
}

function onVoiceChange() {
    if (voice.value === activeVoice.value) {
        return;
    }

    if (audioUrl.value) {
        generate();
    }
}

function onError() {
    playing.value = false;
    error.value = 'Sorry, the audio could not be loaded.';
}

function onTimeUpdate() {
    currentTime.value = audio.value?.currentTime ?? 0;
}

function onLoadedMetadata() {
    duration.value = audio.value?.duration ?? 0;
}

function onEnded() {
    playing.value = false;
    currentTime.value = 0;
}

function changeSpeed() {
    if (audio.value) {
        audio.value.playbackRate = Number(speed.value);
    }
}

function seek(event) {
    if (audio.value) {
        audio.value.currentTime = event.target.value;
    }
}
</script>

<template>
    <div>
        <!-- Hidden audio element that plays the narration. -->
        <audio
            ref="audio"
            :src="audioUrl || undefined"
            @timeupdate="onTimeUpdate"
            @loadedmetadata="onLoadedMetadata"
            @play="playing = true"
            @pause="playing = false"
            @ended="onEnded"
            @error="onError"
        ></audio>

        <!-- Controls shown when no audio exists yet. -->
        <div v-if="!audioUrl && !loading" class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                class="btn-secondary"
                @click="generate"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
                </svg>
                Listen to this article
            </button>

            <label class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                Voice
                <select
                    v-model="voice"
                    class="rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs font-semibold text-zinc-700 focus:ring-2 focus:ring-primary-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300"
                >
                    <option v-for="name in voices" :key="name" :value="name">{{ name }}</option>
                </select>
            </label>
        </div>

        <!-- Message shown while the AI creates the audio. -->
        <div
            v-else-if="loading"
            class="inline-flex items-center gap-2 rounded-xl bg-zinc-100 px-4 py-2.5 text-sm font-semibold text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400"
        >
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generating audio…
        </div>

        <!-- Player shown when audio is ready. -->
        <div
            v-else
            class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white p-3 shadow-soft dark:border-zinc-800 dark:bg-zinc-950"
        >
            <button
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-600 to-violet-600 text-white shadow-md shadow-primary-500/30 transition-all duration-300 hover:scale-110 hover:from-primary-500 hover:to-violet-500"
                aria-label="Play or pause"
                @click="togglePlay"
            >
                <svg v-if="!playing" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5.14v13.72a1 1 0 001.5.86l11-6.86a1 1 0 000-1.72l-11-6.86a1 1 0 00-1.5.86z" />
                </svg>
                <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 5h4v14H7zM13 5h4v14h-4z" />
                </svg>
            </button>

            <input
                type="range"
                class="flex-1 accent-primary-700"
                :max="duration || 0"
                :value="currentTime"
                step="0.1"
                @input="seek"
            />

            <span class="shrink-0 text-xs font-medium tabular-nums text-zinc-500 dark:text-zinc-400">
                {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
            </span>

            <select
                v-model="voice"
                class="shrink-0 rounded-lg border-0 bg-zinc-100 px-2 py-1.5 text-xs font-semibold text-zinc-700 focus:ring-2 focus:ring-primary-600 dark:bg-zinc-900 dark:text-zinc-300"
                @change="onVoiceChange"
            >
                <option v-for="name in voices" :key="name" :value="name">{{ name }}</option>
            </select>

            <select
                v-model="speed"
                class="shrink-0 rounded-lg border-0 bg-zinc-100 px-2 py-1.5 text-xs font-semibold text-zinc-700 focus:ring-2 focus:ring-primary-600 dark:bg-zinc-900 dark:text-zinc-300"
                @change="changeSpeed"
            >
                <option :value="0.75">0.75x</option>
                <option :value="1">1x</option>
                <option :value="1.25">1.25x</option>
                <option :value="1.5">1.5x</option>
                <option :value="2">2x</option>
            </select>
        </div>

        <p v-if="error" class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">
            {{ error }}
        </p>
    </div>
</template>
