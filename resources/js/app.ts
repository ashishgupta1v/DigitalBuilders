import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Toast from './Components/Toast.vue';
import { trackPageView } from './utils/analytics';

// Auto-track Inertia SPA transitions
router.on('navigate', (event) => {
    trackPageView(event.detail.page.url, document.title);
});

const appName = import.meta.env.VITE_APP_NAME || 'DigitalBuilders';
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
if (savedTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    document.documentElement.classList.add('dark');
    document.documentElement.classList.remove('light');
} else {
    document.documentElement.setAttribute('data-theme', 'light');
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
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
    title: (title) => {
        if (!title) return appName;
        return title.includes(appName) ? title : `${title} — ${appName}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // Mount global Toast component
        setTimeout(() => {
            const toastMount = document.createElement('div');
            toastMount.id = 'app-toast';
            document.body.appendChild(toastMount);
            createApp(Toast).mount(toastMount);
        }, 0);
    },
    progress: {
        color: '#9BA7FF',
        delay: 750,
    },
}) as any;
