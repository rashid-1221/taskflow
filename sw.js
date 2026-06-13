// ── TaskFlow Service Worker ──────────────────────────────────────────────────
const CACHE_VERSION = '2026-06-13-v3';
const CACHE_NAME    = 'taskflow-' + CACHE_VERSION;

const STATIC_ASSETS = [
  '/taskflow/manifest.json',
  '/taskflow/icons/icon-192.png',
  '/taskflow/icons/icon-512.png',
];

// Installation
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

// Activation : supprime tous les anciens caches et notifie les clients de recharger
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
      .then(() => {
        // Notifie tous les onglets ouverts qu'une nouvelle version est disponible
        self.clients.matchAll({ type: 'window' }).then(clients => {
          clients.forEach(client => client.postMessage({ type: 'SW_UPDATED' }));
        });
      })
  );
});

// Fetch
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // API → toujours réseau
  if (url.pathname.includes('/api/')) {
    event.respondWith(
      fetch(event.request).catch(() =>
        new Response(JSON.stringify({ ok: false, error: 'Hors ligne' }), {
          headers: { 'Content-Type': 'application/json' }
        })
      )
    );
    return;
  }

  // index.html → toujours réseau (jamais de cache)
  if (url.pathname === '/taskflow/' || url.pathname === '/taskflow/index.html' || url.pathname.endsWith('/taskflow/')) {
    event.respondWith(
      fetch(event.request, { cache: 'no-store' })
        .catch(() => caches.match('/taskflow/index.html'))
    );
    return;
  }

  // Icônes et manifest → cache
  event.respondWith(
    caches.match(event.request).then(cached => {
      if (cached) return cached;
      return fetch(event.request).then(response => {
        if (response && response.status === 200 && response.type === 'basic') {
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, response.clone()));
        }
        return response;
      });
    })
  );
});
