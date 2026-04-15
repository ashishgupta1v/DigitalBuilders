import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Toast from './Components/Toast.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const spotlightSelector = '.db-mini, [data-stagger-item]';

const updateSpotlight = (event: MouseEvent) => {
    const rawTarget = event.target as Element | null;
    const target = rawTarget?.closest(spotlightSelector) as HTMLElement | null;

    if (!target) return;

    const rect = target.getBoundingClientRect();

    target.style.setProperty('--db-x', `${event.clientX - rect.left}px`);
    target.style.setProperty('--db-y', `${event.clientY - rect.top}px`);
};

const clearSpotlight = (element: HTMLElement | null) => {
    if (!element) return;

    element.style.removeProperty('--db-x');
    element.style.removeProperty('--db-y');
};

// Scroll progress bar
const updateScrollProgress = () => {
    const scrollTop = document.documentElement.scrollTop;
    const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const pct = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
    document.documentElement.style.setProperty('--db-scroll', `${pct}%`);
};

// Theme persistence — apply before first paint to avoid flash
const savedTheme = typeof localStorage !== 'undefined' ? localStorage.getItem('db-theme') : null;
if (savedTheme === 'light') {
    document.documentElement.setAttribute('data-theme', 'light');
}

if (typeof document !== 'undefined') {
    document.addEventListener('mousemove', updateSpotlight, { passive: true });

    document.addEventListener('mouseout', (event) => {
        const rawFrom = event.target as Element | null;
        const rawTo = event.relatedTarget as Element | null;
        const fromElement = rawFrom?.closest(spotlightSelector) as HTMLElement | null;
        const toElement = rawTo?.closest(spotlightSelector) as HTMLElement | null;

        if (fromElement && fromElement !== toElement) {
            clearSpotlight(fromElement);
        }
    });

    document.addEventListener('scroll', updateScrollProgress, { passive: true });
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Mount global Toast after the app
        vueApp.mount(el);

        const toastMount = document.createElement('div');
        document.body.appendChild(toastMount);
        createApp(Toast).mount(toastMount);
    },
    progress: {
        color: '#9BA7FF',
    },
});
