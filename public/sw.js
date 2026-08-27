/**
 * DigitalBuilders Service Worker - Purge & De-registration
 * Automatically clears all legacy cached shells and unregisters to prevent stale asset hashes.
 */

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(keys.map((key) => caches.delete(key)));
        }).then(() => {
            return self.clients.claim();
        }).then(() => {
            return self.registration.unregister();
        })
    );
});

self.addEventListener('fetch', (event) => {
    // Direct network pass-through to ensure fresh builds and dynamic assets are always served
    event.respondWith(fetch(event.request));
});

