/* ============================================================
 * Hotel Ciputra Membership — Service Worker (PWA)
 * Strategi:
 *  - Halaman (navigasi)      : network-first → fallback offline.html
 *  - Aset statis (js/css/img): stale-while-revalidate
 *  - POST / admin / portal   : selalu network (tanpa cache)
 * ============================================================ */
const VERSION = 'hc-membership-v1.5';
const STATIC_CACHE = VERSION + '-static';
const OFFLINE_URL = '/offline.html';

/* Aset inti yang di-precache saat install */
const CORE_ASSETS = [
    OFFLINE_URL,
    '/favicon.ico',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/images/logo-header.png',
    '/images/logo.png',
];

/* Ekstensi aset statis yang boleh dicache */
const STATIC_EXT = /\.(?:js|css|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|webmanifest)$/i;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Hanya tangani GET, protokol http/https
    if (request.method !== 'GET' || !request.url.startsWith('http')) return;

    // Navigasi halaman → network-first, fallback offline
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(OFFLINE_URL).then((res) => res || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/html' } }))
            )
        );
        return;
    }

    // Aset same-origin → stale-while-revalidate
    const url = new URL(request.url);
    if (url.origin === self.location.origin && STATIC_EXT.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const refresh = fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                }).catch(() => cached);
                return cached || refresh;
            })
        );
        return;
    }

    // Aset CDN (tailwind, chart.js) → cache-first dengan revalidate di belakang
    if (/cdn\.|fonts\./.test(url.hostname)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                }).catch(() => cached);
            })
        );
    }
});

/* Menerima pesan "SKIP_WAITING" untuk update langsung */
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});
