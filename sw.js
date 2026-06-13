// ── TaskFlow Service Worker ──────────────────────────────────────────────────
// Changer ce numéro à chaque déploiement pour forcer la mise à jour
const CACHE_VERSION = '2026-06-13-v2';
const CACHE_NAME    = 'taskflow-' + CACHE_VERSION;

// Seuls les assets statiques lourds sont mis en cache (pas index.html)
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
  self.skipWaiting(); // prend effet immédiatement sans attendre la fermeture des onglets
});

// Activation : supprime tous les anciens caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim(); // contrôle immédiat de tous les onglets ouverts
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

  // index.html → toujours réseau en priorité (pour recevoir les mises à jour)
  if (url.pathname === '/taskflow/' || url.pathname === '/taskflow/index.html') {
    event.respondWith(
      fetch(event.request)
        .then(response => response)
        .catch(() => caches.match('/taskflow/index.html'))
    );
    return;
  }

  // Icônes et manifest → cache d'abord, réseau en fallback
  event.respondWith(
    caches.match(event.request).then(cached => {
      if (cached) return cached;
      return fetch(event.request).then(response => {
        if (response && response.status === 200 && response.type === 'basic') {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      });
    })
  );
});
