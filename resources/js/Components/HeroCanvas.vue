<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import * as THREE from 'three';

const props = withDefaults(
    defineProps<{
        isDarkMode?: boolean;
    }>(),
    {
        isDarkMode: true,
    },
);

const canvasRef = ref<HTMLCanvasElement | null>(null);

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let animFrameId: number | null = null;

let particleSystem: THREE.Points | null = null;
let lineSegments: THREE.LineSegments | null = null;
let centerPolyhedron: THREE.Mesh | null = null;
let polyWireframe: THREE.LineSegments | null = null;

const mouse = new THREE.Vector2(-9999, -9999);
const targetMouse = new THREE.Vector2(-9999, -9999);

const PARTICLE_COUNT = typeof window !== 'undefined' && window.innerWidth < 768 ? 70 : 130;
const CONNECTION_DIST = 3.6;

// Original particle positions and velocities
let basePositions: Float32Array;
let currentPositions: Float32Array;
let velocities: THREE.Vector3[];

function applyThemeColors() {
    if (!particleSystem || !lineSegments || !centerPolyhedron || !polyWireframe) return;
    const isDark = props.isDarkMode;

    const color1 = new THREE.Color(isDark ? '#38bdf8' : '#0284c7'); // Sky blue
    const color2 = new THREE.Color(isDark ? '#818cf8' : '#4f46e5'); // Indigo
    const color3 = new THREE.Color(isDark ? '#c084fc' : '#7c3aed'); // Purple

    // Particle colors
    const pGeo = particleSystem.geometry;
    const colors: number[] = [];
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        const t = i / PARTICLE_COUNT;
        const c = t < 0.5 ? color1.clone().lerp(color2, t * 2) : color2.clone().lerp(color3, (t - 0.5) * 2);
        colors.push(c.r, c.g, c.b);
    }
    pGeo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    (pGeo.attributes.color as THREE.BufferAttribute).needsUpdate = true;

    // Line material
    (lineSegments.material as THREE.LineBasicMaterial).color = color1;

    // Central Polyhedron material
    (centerPolyhedron.material as THREE.MeshBasicMaterial).color = color2;
    (polyWireframe.material as THREE.LineBasicMaterial).color = color1;
}

watch(() => props.isDarkMode, () => {
    applyThemeColors();
});

function initScene(w: number, h: number) {
    scene = new THREE.Scene();

    camera = new THREE.PerspectiveCamera(50, w / h, 0.1, 100);
    camera.position.set(0, 0, 13);

    // 1. Central 3D Floating Polyhedron (Icosahedron)
    const polyGeo = new THREE.IcosahedronGeometry(2.4, 1);
    const polyMat = new THREE.MeshBasicMaterial({
        wireframe: true,
        transparent: true,
        opacity: props.isDarkMode ? 0.18 : 0.12,
    });
    centerPolyhedron = new THREE.Mesh(polyGeo, polyMat);
    centerPolyhedron.position.set(3.5, 0.5, -2);
    scene.add(centerPolyhedron);

    const wireGeo = new THREE.WireframeGeometry(polyGeo);
    const wireMat = new THREE.LineBasicMaterial({
        transparent: true,
        opacity: props.isDarkMode ? 0.35 : 0.25,
    });
    polyWireframe = new THREE.LineSegments(wireGeo, wireMat);
    polyWireframe.position.copy(centerPolyhedron.position);
    scene.add(polyWireframe);

    // 2. Particle Network setup
    basePositions = new Float32Array(PARTICLE_COUNT * 3);
    currentPositions = new Float32Array(PARTICLE_COUNT * 3);
    velocities = [];

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        const x = (Math.random() - 0.5) * 22;
        const y = (Math.random() - 0.5) * 13;
        const z = (Math.random() - 0.5) * 9;

        basePositions[i * 3] = x;
        basePositions[i * 3 + 1] = y;
        basePositions[i * 3 + 2] = z;

        currentPositions[i * 3] = x;
        currentPositions[i * 3 + 1] = y;
        currentPositions[i * 3 + 2] = z;

        velocities.push(
            new THREE.Vector3(
                (Math.random() - 0.5) * 0.008,
                (Math.random() - 0.5) * 0.006,
                (Math.random() - 0.5) * 0.004,
            ),
        );
    }

    const pGeo = new THREE.BufferGeometry();
    pGeo.setAttribute('position', new THREE.Float32BufferAttribute(currentPositions, 3));

    const pMat = new THREE.PointsMaterial({
        size: 0.12,
        vertexColors: true,
        transparent: true,
        opacity: props.isDarkMode ? 0.65 : 0.45,
        sizeAttenuation: true,
    });
    particleSystem = new THREE.Points(pGeo, pMat);
    scene.add(particleSystem);

    // 3. Line Connections (subtle, non-distracting background depth)
    const maxConnections = PARTICLE_COUNT * PARTICLE_COUNT;
    const lGeo = new THREE.BufferGeometry();
    lGeo.setAttribute('position', new THREE.Float32BufferAttribute(new Float32Array(maxConnections * 6), 3));
    const lMat = new THREE.LineBasicMaterial({
        transparent: true,
        opacity: props.isDarkMode ? 0.10 : 0.07,
    });
    lineSegments = new THREE.LineSegments(lGeo, lMat);
    scene.add(lineSegments);

    applyThemeColors();
}

function updatePhysics() {
    if (!particleSystem || !lineSegments) return;

    // Smooth mouse lerp
    mouse.x += (targetMouse.x - mouse.x) * 0.08;
    mouse.y += (targetMouse.y - mouse.y) * 0.08;

    const pPosAttr = particleSystem.geometry.attributes.position as THREE.BufferAttribute;
    const pPos = pPosAttr.array as Float32Array;

    // Convert mouse screen coords to 3D world space approx at Z=0
    const mouseWorldX = (mouse.x * 12);
    const mouseWorldY = (-mouse.y * 7);

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        // Base drift
        basePositions[i * 3]     += velocities[i].x;
        basePositions[i * 3 + 1] += velocities[i].y;
        basePositions[i * 3 + 2] += velocities[i].z;

        // Bounce drift inside bounding box
        if (Math.abs(basePositions[i * 3])     > 11) velocities[i].x *= -1;
        if (Math.abs(basePositions[i * 3 + 1]) > 7.5) velocities[i].y *= -1;
        if (Math.abs(basePositions[i * 3 + 2]) > 5)   velocities[i].z *= -1;

        // Interactive mouse repulsion force
        const dx = basePositions[i * 3] - mouseWorldX;
        const dy = basePositions[i * 3 + 1] - mouseWorldY;
        const distToMouse = Math.sqrt(dx * dx + dy * dy);

        let pushX = 0;
        let pushY = 0;
        if (distToMouse < 3.2 && distToMouse > 0.01) {
            const force = (3.2 - distToMouse) / 3.2;
            pushX = (dx / distToMouse) * force * 1.8;
            pushY = (dy / distToMouse) * force * 1.8;
        }

        pPos[i * 3]     = basePositions[i * 3] + pushX;
        pPos[i * 3 + 1] = basePositions[i * 3 + 1] + pushY;
        pPos[i * 3 + 2] = basePositions[i * 3 + 2];
    }
    pPosAttr.needsUpdate = true;

    // Line connections update
    const lPosAttr = lineSegments.geometry.attributes.position as THREE.BufferAttribute;
    const lPos = lPosAttr.array as Float32Array;
    let lineIdx = 0;

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        for (let j = i + 1; j < PARTICLE_COUNT; j++) {
            const ax = pPos[i * 3], ay = pPos[i * 3 + 1], az = pPos[i * 3 + 2];
            const bx = pPos[j * 3], by = pPos[j * 3 + 1], bz = pPos[j * 3 + 2];
            const dist = Math.sqrt((ax - bx) ** 2 + (ay - by) ** 2 + (az - bz) ** 2);

            if (dist < CONNECTION_DIST) {
                lPos[lineIdx++] = ax; lPos[lineIdx++] = ay; lPos[lineIdx++] = az;
                lPos[lineIdx++] = bx; lPos[lineIdx++] = by; lPos[lineIdx++] = bz;
            }
        }
    }
    while (lineIdx < lPos.length) lPos[lineIdx++] = 0;
    lPosAttr.needsUpdate = true;
}

let clock = 0;
function animate() {
    if (!renderer || !scene || !camera) return;
    animFrameId = requestAnimationFrame(animate);

    clock += 0.01;

    // Rotate Polyhedron
    if (centerPolyhedron && polyWireframe) {
        centerPolyhedron.rotation.x = Math.sin(clock * 0.5) * 0.3;
        centerPolyhedron.rotation.y += 0.005;
        centerPolyhedron.position.y = 0.5 + Math.sin(clock * 0.8) * 0.3;

        polyWireframe.rotation.copy(centerPolyhedron.rotation);
        polyWireframe.position.copy(centerPolyhedron.position);
    }

    // Gentle camera parallax
    camera.position.x += (mouse.x * 1.8 - camera.position.x) * 0.03;
    camera.position.y += (-mouse.y * 1.2 - camera.position.y) * 0.03;
    camera.lookAt(0, 0, 0);

    updatePhysics();
    renderer.render(scene, camera);
}

function onMouseMove(e: MouseEvent) {
    const w = window.innerWidth;
    const h = window.innerHeight;
    targetMouse.x = (e.clientX / w - 0.5) * 2;
    targetMouse.y = (e.clientY / h - 0.5) * 2;
}

function onMouseLeave() {
    targetMouse.set(-9999, -9999);
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

    initScene(w, h);

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion && scene && camera) {
        // Render single static frame for reduced motion users without continuous CPU loop
        renderer.render(scene, camera);
    } else {
        animate();
    }

    window.addEventListener('mousemove', onMouseMove, { passive: true });
    window.addEventListener('mouseleave', onMouseLeave, { passive: true });
    window.addEventListener('resize', onResize, { passive: true });
});

onBeforeUnmount(() => {
    if (animFrameId !== null) cancelAnimationFrame(animFrameId);
    if (renderer) {
        renderer.dispose();
        renderer = null;
    }
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseleave', onMouseLeave);
    window.removeEventListener('resize', onResize);

    if (particleSystem) {
        particleSystem.geometry.dispose();
        (particleSystem.material as THREE.Material).dispose();
    }
    if (lineSegments) {
        lineSegments.geometry.dispose();
        (lineSegments.material as THREE.Material).dispose();
    }
    if (centerPolyhedron) {
        centerPolyhedron.geometry.dispose();
        (centerPolyhedron.material as THREE.Material).dispose();
    }
    if (polyWireframe) {
        polyWireframe.geometry.dispose();
        (polyWireframe.material as THREE.Material).dispose();
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
