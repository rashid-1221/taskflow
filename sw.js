// ── TaskFlow Service Worker ──────────────────────────────────────────────────
const CACHE_NAME = 'taskflow-v1';
const STATIC_ASSETS = [
  '/taskflow/',
  '/taskflow/index.html',
  '/taskflow/manifest.json',
  '/taskflow/icons/icon-192.png',
  '/taskflow/icons/icon-512.png',
];

// Installation : mise en cache des assets statiques
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

// Activation : nettoyage des anciens caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch : stratégie Network-First pour l'API, Cache-First pour le reste
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // L'API de sync doit toujours passer par le réseau
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

  // Pour tout le reste : cache d'abord, réseau en fallback
  event.respondWith(
    caches.match(event.request).then(cached => {
      if (cached) return cached;
      return fetch(event.request).then(response => {
        // Ne mettre en cache que les réponses valides
        if (response && response.status === 200 && response.type === 'basic') {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      });
    })
  );
});
