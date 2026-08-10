<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const ring = ref(null);
const dot = ref(null);
const pill = ref(null);

let raf = 0;

const mouse = { x: -100, y: -100, active: false };
const ringPos = { x: -100, y: -100 };
const pillPos = { x: -100, y: -100 };

let pillActive = false;
let pillElement = null;

const lerp = (a, b, t) => a + (b - a) * t;

const enabled = () =>
    window.matchMedia('(pointer: fine)').matches &&
    !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function place(el, x, y) {
    el.style.transform = `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`;
}

function onMove(e) {
    mouse.x = e.clientX;
    mouse.y = e.clientY;

    if (!mouse.active) {
        mouse.active = true;
        ringPos.x = mouse.x;
        ringPos.y = mouse.y;
        pillPos.x = mouse.x;
        pillPos.y = mouse.y;
        dot.value.style.opacity = '1';
        ring.value.style.opacity = '1';
    }
}

function onOver(e) {
    const target = e.target.closest('[data-cursor-pill]');

    if (target !== pillElement) {
        pillElement?.style.removeProperty('cursor');
        pillElement = target;
        pillActive = !!target;
        pill.value.classList.toggle('is-active', pillActive);

        if (target) {
            target.style.cursor = 'none';
            pillPos.x = mouse.x;
            pillPos.y = mouse.y;
        }
    }

    const interactive = !!e.target.closest(
        'a, button, [role="button"], input, select, textarea, label',
    );
    dot.value.classList.toggle('is-hover', interactive);
    ring.value.classList.toggle('is-hover', interactive);
}

function frame() {
    if (mouse.active) {
        place(dot.value, mouse.x, mouse.y);

        ringPos.x = lerp(ringPos.x, mouse.x, 0.16);
        ringPos.y = lerp(ringPos.y, mouse.y, 0.16);
        place(ring.value, ringPos.x, ringPos.y);

        if (pillActive) {
            pillPos.x = lerp(pillPos.x, mouse.x, 0.28);
            pillPos.y = lerp(pillPos.y, mouse.y, 0.28);
            place(pill.value, pillPos.x, pillPos.y);
        }
    }

    raf = requestAnimationFrame(frame);
}

onMounted(() => {
    if (!enabled()) return;

    window.addEventListener('mousemove', onMove, { passive: true });
    document.addEventListener('mouseover', onOver, { passive: true });
    raf = requestAnimationFrame(frame);
});

onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    window.removeEventListener('mousemove', onMove);
    document.removeEventListener('mouseover', onOver);
    pillElement?.style.removeProperty('cursor');
});
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-[9999]" aria-hidden="true">
        <div ref="ring" class="cursor-element cursor-ring">
            <span class="cursor-ring-core"></span>
        </div>
        <div ref="dot" class="cursor-element cursor-dot">
            <span class="cursor-dot-core"></span>
        </div>
        <div ref="pill" class="cursor-element cursor-pill">
            <span class="cursor-pill-core">
                <span>Explore</span>
                <svg class="cursor-pill-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </span>
        </div>
    </div>
</template>

<style scoped>
.cursor-element {
    position: fixed;
    top: 0;
    left: 0;
    pointer-events: none;
    will-change: transform;
}

.cursor-ring {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.cursor-ring-core {
    display: block;
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    border: 1.5px solid rgb(14 165 233 / 0.8);
    background: rgb(14 165 233 / 0.05);
    transition: transform 0.35s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.cursor-ring.is-hover .cursor-ring-core {
    transform: scale(1.6);
    background: rgb(14 165 233 / 0.12);
}

.cursor-dot {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.cursor-dot-core {
    display: block;
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    background: #6366f1;
    box-shadow: 0 0 12px rgb(14 165 233 / 0.7);
    transition: transform 0.35s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.cursor-dot.is-hover .cursor-dot-core {
    transform: scale(0.5);
}

.cursor-pill {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease;
}

.cursor-pill.is-active {
    opacity: 1;
    visibility: visible;
}

.cursor-pill-core {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px 10px 14px;
    border-radius: 9999px;
    background: #fff;
    border: 1px solid #e4e4e7;
    box-shadow: 0 8px 24px -8px rgb(9 9 11 / 0.25);
    color: #18181b;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    transform: scale(0);
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.cursor-pill.is-active .cursor-pill-core {
    transform: scale(1);
}

.cursor-pill-icon {
    width: 16px;
    height: 16px;
    color: #6366f1;
}

:global(.dark) .cursor-pill-core {
    background: #27272a;
    border-color: #3f3f46;
    color: #f4f4f5;
    box-shadow: 0 8px 24px -8px rgb(0 0 0 / 0.5);
}

@media (prefers-reduced-motion: reduce) {
    .cursor-ring-core,
    .cursor-dot-core,
    .cursor-pill-core {
        transition: none;
    }
}
</style>
