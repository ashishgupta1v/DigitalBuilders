<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue';
import * as THREE from 'three';

const canvasRef = ref<HTMLCanvasElement | null>(null);

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let animFrameId: number | null = null;
let particles: THREE.Points | null = null;
let lines: THREE.LineSegments | null = null;
let mouse = new THREE.Vector2(0, 0);
let clock: THREE.Clock;

const PARTICLE_COUNT = typeof window !== 'undefined' && window.innerWidth < 768 ? 60 : 120;
const CONNECTION_DISTANCE = 3.5;

function buildScene(w: number, h: number) {
    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(55, w / h, 0.1, 100);
    camera.position.set(0, 0, 14);

    // ---------- Particles ----------
    const posArr: number[] = [];
    const velArr: THREE.Vector3[] = [];

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        const x = (Math.random() - 0.5) * 20;
        const y = (Math.random() - 0.5) * 12;
        const z = (Math.random() - 0.5) * 8;
        posArr.push(x, y, z);
        velArr.push(
            new THREE.Vector3(
                (Math.random() - 0.5) * 0.006,
                (Math.random() - 0.5) * 0.004,
                (Math.random() - 0.5) * 0.003,
            ),
        );
    }

    const pGeo = new THREE.BufferGeometry();
    pGeo.setAttribute('position', new THREE.Float32BufferAttribute(posArr, 3));

    // Per-particle color: blend from electric-blue → violet → purple
    const colors: number[] = [];
    const c1 = new THREE.Color('#7ac4ff');
    const c2 = new THREE.Color('#9ba7ff');
    const c3 = new THREE.Color('#c593ff');
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        const t = i / PARTICLE_COUNT;
        const c = t < 0.5 ? c1.clone().lerp(c2, t * 2) : c2.clone().lerp(c3, (t - 0.5) * 2);
        colors.push(c.r, c.g, c.b);
    }
    pGeo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

    const pMat = new THREE.PointsMaterial({
        size: 0.12,
        vertexColors: true,
        transparent: true,
        opacity: 0.85,
        sizeAttenuation: true,
    });
    particles = new THREE.Points(pGeo, pMat);
    scene.add(particles);

    // ---------- Connection lines (start empty) ----------
    const lGeo = new THREE.BufferGeometry();
    const maxLines = PARTICLE_COUNT * PARTICLE_COUNT;
    lGeo.setAttribute('position', new THREE.Float32BufferAttribute(new Float32Array(maxLines * 6), 3));
    const lMat = new THREE.LineBasicMaterial({
        color: new THREE.Color('#7ac4ff'),
        transparent: true,
        opacity: 0.18,
    });
    lines = new THREE.LineSegments(lGeo, lMat);
    scene.add(lines);

    // Store velocities for animation
    (pGeo as any)._velocities = velArr;
}

function updateConnections() {
    if (!particles || !lines) return;
    const pPos = (particles.geometry.attributes.position as THREE.BufferAttribute).array as Float32Array;
    const lPos = (lines.geometry.attributes.position as THREE.BufferAttribute).array as Float32Array;
    let lineIdx = 0;

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        for (let j = i + 1; j < PARTICLE_COUNT; j++) {
            const ax = pPos[i * 3], ay = pPos[i * 3 + 1], az = pPos[i * 3 + 2];
            const bx = pPos[j * 3], by = pPos[j * 3 + 1], bz = pPos[j * 3 + 2];
            const dist = Math.sqrt((ax - bx) ** 2 + (ay - by) ** 2 + (az - bz) ** 2);

            if (dist < CONNECTION_DISTANCE) {
                lPos[lineIdx++] = ax; lPos[lineIdx++] = ay; lPos[lineIdx++] = az;
                lPos[lineIdx++] = bx; lPos[lineIdx++] = by; lPos[lineIdx++] = bz;
            }
        }
    }
    // Fill rest with zeros
    while (lineIdx < lPos.length) lPos[lineIdx++] = 0;
    (lines.geometry.attributes.position as THREE.BufferAttribute).needsUpdate = true;

    // Adjust opacity based on total connections
    const connectedFraction = lineIdx / lPos.length;
    ((lines.material) as THREE.LineBasicMaterial).opacity = 0.1 + connectedFraction * 0.2;
}

function animate() {
    if (!renderer || !scene || !camera || !particles) return;
    animFrameId = requestAnimationFrame(animate);

    const pPos = (particles.geometry.attributes.position as THREE.BufferAttribute).array as Float32Array;
    const vels: THREE.Vector3[] = (particles.geometry as any)._velocities;

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        pPos[i * 3]     += vels[i].x;
        pPos[i * 3 + 1] += vels[i].y;
        pPos[i * 3 + 2] += vels[i].z;

        // Bounce off invisible box
        if (Math.abs(pPos[i * 3])     > 10) vels[i].x *= -1;
        if (Math.abs(pPos[i * 3 + 1]) > 6)  vels[i].y *= -1;
        if (Math.abs(pPos[i * 3 + 2]) > 4)  vels[i].z *= -1;
    }
    (particles.geometry.attributes.position as THREE.BufferAttribute).needsUpdate = true;

    // Mouse parallax: gently shift camera target
    camera.position.x += (mouse.x * 1.5 - camera.position.x) * 0.03;
    camera.position.y += (-mouse.y * 1.0 - camera.position.y) * 0.03;
    camera.lookAt(0, 0, 0);

    // Slow global rotation
    particles.rotation.y += 0.0008;
    if (lines) lines.rotation.y += 0.0008;

    updateConnections();
    renderer.render(scene, camera);
}

function onMouseMove(e: MouseEvent) {
    const w = window.innerWidth;
    const h = window.innerHeight;
    mouse.x = (e.clientX / w - 0.5) * 2;
    mouse.y = (e.clientY / h - 0.5) * 2;
}

function onResize() {
    if (!renderer || !camera || !canvasRef.value) return;
    const w = canvasRef.value.clientWidth;
    const h = canvasRef.value.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
}

onMounted(() => {
    const canvas = canvasRef.value;
    if (!canvas) return;

    const w = canvas.clientWidth;
    const h = canvas.clientHeight;

    renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(w, h, false);
    renderer.setClearColor(0x000000, 0);

    clock = new THREE.Clock();
    buildScene(w, h);
    animate();

    window.addEventListener('mousemove', onMouseMove, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
});

onBeforeUnmount(() => {
    if (animFrameId !== null) cancelAnimationFrame(animFrameId);
    if (renderer) {
        renderer.dispose();
        renderer = null;
    }
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('resize', onResize);
    // Dispose Three.js objects
    if (particles) {
        particles.geometry.dispose();
        (particles.material as THREE.Material).dispose();
    }
    if (lines) {
        lines.geometry.dispose();
        (lines.material as THREE.Material).dispose();
    }
});
</script>

<template>
    <canvas
        ref="canvasRef"
        class="hero-canvas"
        aria-hidden="true"
    />
</template>

<style scoped>
.hero-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    display: block;
}
</style>
