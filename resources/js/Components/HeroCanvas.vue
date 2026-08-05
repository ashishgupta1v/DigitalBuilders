<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{ isDarkMode: boolean }>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animFrameId: number | null = null;
let cleanupFn: (() => void) | null = null;

function initThree(canvas: HTMLCanvasElement, dark: boolean) {
    const W = canvas.offsetWidth || window.innerWidth;
    const H = canvas.offsetHeight || window.innerHeight;
    canvas.width = W * Math.min(window.devicePixelRatio, 2);
    canvas.height = H * Math.min(window.devicePixelRatio, 2);
    const ctx = canvas.getContext('2d')!;
    ctx.scale(Math.min(window.devicePixelRatio, 2), Math.min(window.devicePixelRatio, 2));

    const COUNT = Math.min(80, Math.floor((W * H) / 12000));
    const LINK_DIST = 150;
    const COLORS_DARK = ['rgba(56,189,248,', 'rgba(129,140,248,', 'rgba(192,132,252,', 'rgba(244,114,182,'];
    const COLORS_LIGHT = ['rgba(2,132,199,', 'rgba(79,70,229,', 'rgba(124,58,237,', 'rgba(219,39,119,'];

    const particles: { x: number; y: number; vx: number; vy: number; r: number; ci: number; opacity: number }[] = [];
    for (let i = 0; i < COUNT; i++) {
        particles.push({
            x: Math.random() * W,
            y: Math.random() * H,
            vx: (Math.random() - 0.5) * 0.35,
            vy: (Math.random() - 0.5) * 0.35,
            r: Math.random() * 2.2 + 0.8,
            ci: Math.floor(Math.random() * 4),
            opacity: Math.random() * 0.5 + 0.3,
        });
    }

    let running = true;

    function draw() {
        if (!running) return;
        ctx.clearRect(0, 0, W, H);
        const colors = dark ? COLORS_DARK : COLORS_LIGHT;

        // Update & draw particles
        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;
            if (p.x < 0) p.x = W;
            if (p.x > W) p.x = 0;
            if (p.y < 0) p.y = H;
            if (p.y > H) p.y = 0;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = `${colors[p.ci]}${p.opacity})`;
            ctx.fill();
        }

        // Draw connections
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < LINK_DIST) {
                    const alpha = (1 - dist / LINK_DIST) * (dark ? 0.18 : 0.1);
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `${colors[particles[i].ci]}${alpha})`;
                    ctx.lineWidth = 0.6;
                    ctx.stroke();
                }
            }
        }

        animFrameId = requestAnimationFrame(draw);
    }

    draw();

    function onResize() {
        const nW = canvas.offsetWidth || window.innerWidth;
        const nH = canvas.offsetHeight || window.innerHeight;
        canvas.width = nW * Math.min(window.devicePixelRatio, 2);
        canvas.height = nH * Math.min(window.devicePixelRatio, 2);
        ctx.scale(Math.min(window.devicePixelRatio, 2), Math.min(window.devicePixelRatio, 2));
    }
    window.addEventListener('resize', onResize, { passive: true });

    cleanupFn = () => {
        running = false;
        if (animFrameId) cancelAnimationFrame(animFrameId);
        window.removeEventListener('resize', onResize);
    };
}

function restart() {
    if (cleanupFn) { cleanupFn(); cleanupFn = null; }
    if (canvasRef.value) {
        initThree(canvasRef.value, props.isDarkMode);
    }
}

onMounted(() => {
    if (canvasRef.value) initThree(canvasRef.value, props.isDarkMode);
});

watch(() => props.isDarkMode, () => {
    restart();
});

onBeforeUnmount(() => {
    if (cleanupFn) cleanupFn();
});
</script>

<template>
    <canvas
        ref="canvasRef"
        class="absolute inset-0 h-full w-full"
        style="pointer-events: none; z-index: 0;"
        aria-hidden="true"
    />
</template>
