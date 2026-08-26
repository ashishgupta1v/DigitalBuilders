/**
 * DigitalBuilders Service Worker
 * Provides offline caching shell for core assets and offline fallback.
 */

const CACHE_NAME = 'db-cache-v1';
const PRECACHE_ASSETS = [
    '/',
    '/manifest.webmanifest',
    '/images/db-logo.png',
    '/favicon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Skip caching for analytics, dynamic API calls, and Inertia partial reloads
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/ajax/') ||
        event.request.headers.get('X-Inertia')
    ) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }
            return fetch(event.request).then((response) => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/')) {
                        cache.put(event.request, responseToCache);
                    }
                });
                return response;
            }).catch(() => {
                // If offline and request is HTML document, return cached root
                if (event.request.headers.get('accept')?.includes('text/html')) {
                    return caches.match('/');
                }
            });
        })
    );
});
