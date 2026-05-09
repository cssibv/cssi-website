// ═══════════════════════════════════════════
// CSSI Portal 3.0 — Service Worker
// Cache-first + network fallback + offline page
// ═══════════════════════════════════════════

const CACHE_NAME = 'cssi-portal-v3.3';
const CACHE_VERSION = 3;

// Core pages to pre-cache on install
const PRECACHE_URLS = [
  '/admin.html',
  '/offline.html',
  '/manifest.json',
  '/admin/proiecte.html',
  '/admin/crm-clienti.html',
  '/admin/planificare.html',
  '/admin/executie.html',
  '/admin/proiectare.html',
  '/admin/mentenanta.html',
  '/admin/financiar.html',
  '/admin/materiale.html',
  '/admin/necesar-materiale.html',
  '/admin/calculator-pret.html',
  '/admin/calendar-social.html',
  '/admin/marketing.html',
  '/admin/documente.html'
];

// External resources to cache on first use
const CACHE_ON_USE = [
  'https://fonts.googleapis.com',
  'https://fonts.gstatic.com'
];

// ═══════ INSTALL ═══════
self.addEventListener('install', event => {
  console.log('[SW] Installing CSSI Portal v3.0...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Pre-caching core pages');
        return cache.addAll(PRECACHE_URLS);
      })
      .then(() => self.skipWaiting())
      .catch(err => {
        console.warn('[SW] Pre-cache partial fail (normal on first deploy):', err);
        return self.skipWaiting();
      })
  );
});

// ═══════ ACTIVATE ═══════
self.addEventListener('activate', event => {
  console.log('[SW] Activating...');
  event.waitUntil(
    caches.keys().then(names => {
      return Promise.all(
        names.filter(name => name !== CACHE_NAME)
             .map(name => {
               console.log('[SW] Deleting old cache:', name);
               return caches.delete(name);
             })
      );
    }).then(() => self.clients.claim())
  );
});

// ═══════ FETCH STRATEGY ═══════
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Skip non-GET requests
  if (event.request.method !== 'GET') return;

  // Skip API calls (Google Apps Script) — always network
  if (url.hostname === 'script.google.com' || 
      url.hostname === 'script.googleusercontent.com' ||
      url.searchParams.has('action')) {
    return;
  }

  // For portal HTML pages: Network-first, fallback to cache, then offline page
  if (url.pathname.endsWith('.html') || url.pathname === '/' || url.pathname === '') {
    event.respondWith(networkFirstThenCache(event.request));
    return;
  }

  // For fonts and static assets: Cache-first
  if (CACHE_ON_USE.some(origin => url.href.startsWith(origin)) ||
      url.pathname.match(/\.(css|js|woff2?|ttf|eot|png|jpg|jpeg|gif|svg|ico|webp)$/)) {
    event.respondWith(cacheFirstThenNetwork(event.request));
    return;
  }

  // Everything else: Network with cache fallback
  event.respondWith(networkFirstThenCache(event.request));
});

// ═══════ STRATEGIES ═══════

// Network first — try network, fall back to cache, then offline page
async function networkFirstThenCache(request) {
  try {
    const networkResponse = await fetch(request);
    // Cache successful responses
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (err) {
    // Network failed — try cache
    const cached = await caches.match(request);
    if (cached) {
      console.log('[SW] Serving from cache:', request.url);
      return cached;
    }
    // No cache — show offline page for HTML requests
    if (request.headers.get('accept')?.includes('text/html')) {
      const offlinePage = await caches.match('/offline.html');
      if (offlinePage) return offlinePage;
    }
    // Nothing available
    return new Response('Offline — pagina nu este disponibilă', {
      status: 503,
      headers: { 'Content-Type': 'text/plain; charset=utf-8' }
    });
  }
}

// Cache first — try cache, fall back to network
async function cacheFirstThenNetwork(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (err) {
    return new Response('', { status: 503 });
  }
}

// ═══════ BACKGROUND SYNC (future) ═══════
self.addEventListener('message', event => {
  if (event.data === 'skipWaiting') {
    self.skipWaiting();
  }
  if (event.data === 'getVersion') {
    event.ports[0].postMessage({ version: CACHE_NAME, v: CACHE_VERSION });
  }
});
