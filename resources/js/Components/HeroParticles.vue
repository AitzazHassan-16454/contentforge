<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const canvas = ref(null);

const PALETTES = {
    light: [
        [14, 165, 233],
        [59, 130, 246],
        [217, 70, 239],
        [56, 189, 248],
    ],
    dark: [
        [129, 140, 248],
        [167, 139, 250],
        [232, 121, 249],
        [125, 211, 252],
    ],
};

let ctx = null;
let width = 0;
let height = 0;
let dpr = 1;
let particles = [];
let ripples = [];
let raf = 0;
let time = 0;
let started = false;

const mouse = { x: -9999, y: -9999, px: -9999, py: -9999, active: false };
const ring = { x: -9999, y: -9999 };

const finePointer = () => window.matchMedia('(pointer: fine)').matches;
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function currentPalette() {
    return document.documentElement.classList.contains('dark')
        ? PALETTES.dark
        : PALETTES.light;
}

function resize() {
    if (!canvas.value) return;

    const rect = canvas.value.getBoundingClientRect();
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    width = Math.max(1, Math.round(rect.width));
    height = Math.max(1, Math.round(rect.height));
    canvas.value.width = width * dpr;
    canvas.value.height = height * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    build();
}

function build() {
    const count = Math.min(130, Math.max(40, Math.floor((width * height) / 14000)));

    particles = [];
    for (let i = 0; i < count; i++) {
        const x = Math.random() * width;
        const y = Math.random() * height;

        particles.push({
            x,
            y,
            baseX: x,
            baseY: y,
            vx: 0,
            vy: 0,
            size: 1.5 + Math.random() * 2.2,
            amp: 0.4 + Math.random() * 0.8,
            freq: 0.4 + Math.random() * 0.8,
            phase: Math.random() * Math.PI * 2,
            color: (Math.random() * 4) | 0,
        });
    }

    ripples = [];
}

function onMouseMove(e) {
    const rect = canvas.value.getBoundingClientRect();

    mouse.px = mouse.x;
    mouse.py = mouse.y;
    mouse.x = e.clientX - rect.left;
    mouse.y = e.clientY - rect.top;
    mouse.active = true;

    if (ring.x === -9999) {
        ring.x = mouse.x;
        ring.y = mouse.y;
    }

    const dx = mouse.x - mouse.px;
    const dy = mouse.y - mouse.py;

    if (Math.hypot(dx, dy) > 10 && Math.random() < 0.4) {
        ripples.push({ x: mouse.x, y: mouse.y, r: 6, alpha: 0.5 });
    }
}

function onMouseLeave() {
    mouse.active = false;
    mouse.x = -9999;
    mouse.y = -9999;
}

function frame() {
    time += 1 / 60;

    ctx.clearRect(0, 0, width, height);

    const palette = currentPalette();
    const ringRadius = 120 + Math.sin(time * 1.3) * 14;

    if (mouse.active) {
        ring.x += (mouse.x - ring.x) * 0.08;
        ring.y += (mouse.y - ring.y) * 0.08;
    } else {
        ring.x += (width / 2 - ring.x) * 0.008;
        ring.y += (height / 2 - ring.y) * 0.008;
    }

    if (mouse.active) {
        const glow = ctx.createRadialGradient(
            ring.x,
            ring.y,
            ringRadius * 0.5,
            ring.x,
            ring.y,
            ringRadius,
        );
        glow.addColorStop(0, 'rgba(14, 165, 233, 0)');
        glow.addColorStop(0.9, 'rgba(14, 165, 233, 0.06)');
        glow.addColorStop(1, 'rgba(14, 165, 233, 0)');

        ctx.beginPath();
        ctx.arc(ring.x, ring.y, ringRadius, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(14, 165, 233, 0.35)';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(ring.x, ring.y, ringRadius * 0.5, 0, Math.PI * 2);
        ctx.fillStyle = glow;
        ctx.fill();
    }

    for (let i = ripples.length - 1; i >= 0; i--) {
        const r = ripples[i];

        r.r += 3.5;
        r.alpha -= 0.018;

        if (r.alpha <= 0) {
            ripples.splice(i, 1);
            continue;
        }

        ctx.beginPath();
        ctx.arc(r.x, r.y, r.r, 0, Math.PI * 2);
        ctx.strokeStyle = `rgba(59, 130, 246, ${r.alpha})`;
        ctx.lineWidth = 1.5;
        ctx.stroke();
    }

    for (const p of particles) {
        const nx = Math.sin(time * p.freq + p.phase);
        const ny = Math.cos(time * p.freq * 0.8 + p.phase);

        p.vx += (p.baseX - p.x) * 0.003 + nx * p.amp * 0.02;
        p.vy += (p.baseY - p.y) * 0.003 + ny * p.amp * 0.02;
        p.vx *= 0.94;
        p.vy *= 0.94;
        p.x += p.vx;
        p.y += p.vy;

        let scaleBoost = 0;

        if (mouse.active) {
            const dx = p.x - mouse.x;
            const dy = p.y - mouse.y;
            const dist = Math.hypot(dx, dy) || 1;

            if (dist < 95) {
                const force = (1 - dist / 95) * 1.2;
                p.vx += (dx / dist) * force;
                p.vy += (dy / dist) * force;
            }

            const band = 1 - Math.min(1, Math.abs(dist - ringRadius) / 22);
            scaleBoost = band * 1.6;
        }

        const r = p.size * (1 + scaleBoost);
        const c = palette[p.color];

        ctx.beginPath();
        ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${c[0]}, ${c[1]}, ${c[2]}, 0.2)`;
        ctx.fill();

        ctx.beginPath();
        ctx.arc(p.x, p.y, r * 0.55, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${c[0]}, ${c[1]}, ${c[2]}, 0.55)`;
        ctx.fill();
    }

    raf = requestAnimationFrame(frame);
}

let observer = null;
let resizeObserver = null;

onMounted(() => {
    if (!finePointer() || reducedMotion()) return;

    ctx = canvas.value.getContext('2d');

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(canvas.value);
    resize();

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            if (!started) {
                started = true;
                raf = requestAnimationFrame(frame);
            }
        } else {
            cancelAnimationFrame(raf);
            raf = 0;
            started = false;
        }
    }, { threshold: 0 });
    observer.observe(canvas.value);

    window.addEventListener('mousemove', onMouseMove, { passive: true });
    document.addEventListener('mouseleave', onMouseLeave);
});

onBeforeUnmount(() => {
    cancelAnimationFrame(raf);
    observer?.disconnect();
    resizeObserver?.disconnect();
    window.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseleave', onMouseLeave);
});
</script>

<template>
    <canvas ref="canvas" class="pointer-events-none absolute inset-0 h-full w-full"></canvas>
</template>
