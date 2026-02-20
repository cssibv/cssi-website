// CSSI Portal Service Worker v2.0
// Enables: Install on phone, offline caching, fast loading

const CACHE_NAME = 'cssi-portal-v2';
const PAGES = [
  '/admin.html',
  '/admin/calendar-zilnic.html',
  '/admin/agenda-echipa.html',
  '/admin/calendar-social.html',
  '/admin/resurse-media.html',
  '/admin/calculator-pret.html',
  '/admin/crm-clienti.html',
  '/admin/checklist-montaj.html',
  '/admin/necesar-materiale.html',
  '/admin/documente.html'
];

// Install: cache all portal pages
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('[SW] Caching portal pages...');
      return cache.addAll(PAGES);
    })
  );
  self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => 
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch: network first, fallback to cache (for offline)
self.addEventListener('fetch', event => {
  // Skip non-GET requests and Google embeds
  if (event.request.method !== 'GET') return;
  if (event.request.url.includes('google.com')) return;
  if (event.request.url.includes('googleapis.com')) return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful responses
        if (response.status === 200) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => {
        // Offline: serve from cache
        return caches.match(event.request).then(cached => {
          if (cached) return cached;
          // Fallback for HTML pages
          if (event.request.headers.get('accept').includes('text/html')) {
            return caches.match('/admin.html');
          }
        });
      })
  );
});
