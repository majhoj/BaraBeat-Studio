'use strict';

const CACHE_NAME = 'barabeat-studio-offline-v3-access';
const APP_SHELL = [
  './',
  './index.php',
  './manifest.webmanifest',
  './CSS/index_style.css',
  './JS/snapNEU.svg.js',
  './JS/jquery.min.js',
  './JS/localLibrary.js',
  './JS/serverLibrary.js',
  './JS/selection_drag_7.js',
  './JS/functions.js',
  './JS/timeline.js',
  './JS/practice.js',
  './JS/offline.js',
  './Audio/audioplayer.php',
  './Audio/css/audio_style.css',
  './Audio/js/instrument_2.js',
  './Assets/favicon.svg',
  './Assets/favicon-32.png',
  './apple-touch-icon.png',
  './Assets/apple-touch-icon.png',
  './Assets/pwa-icon-192.png',
  './Assets/pwa-icon-512.png',
  './Bedienungsanleitung.php'
];

function scopedUrl(relativePath) {
  return new URL(relativePath, self.registration.scope).toString();
}

async function cacheResponse(cache, request, options) {
  const response = await fetch(request, options);
  if (response && response.ok) {
    await cache.put(request, response.clone());
  }
  return response;
}

async function cacheAppShell() {
  const cache = await caches.open(CACHE_NAME);
  await Promise.all(APP_SHELL.map(function (relativePath) {
    const request = new Request(scopedUrl(relativePath), { cache: 'reload' });
    return cacheResponse(cache, request, { cache: 'reload' });
  }));
}

async function cacheOfflineAudioAssets() {
  const manifestResponse = await fetch(scopedUrl('./offline-assets.php'), { cache: 'no-store' });
  if (!manifestResponse.ok) {
    throw new Error('Offline-Audioliste konnte nicht geladen werden.');
  }
  const manifest = await manifestResponse.json();
  const assets = Array.isArray(manifest.assets) ? manifest.assets : [];
  const cache = await caches.open(CACHE_NAME);
  const versionCacheKey = scopedUrl('./.barabeat-audio-cache-version');
  const cachedVersionResponse = await cache.match(versionCacheKey);
  const cachedVersion = cachedVersionResponse ? await cachedVersionResponse.text() : '';
  if (cachedVersion && cachedVersion === manifest.version) {
    return;
  }
  const results = await Promise.allSettled(assets.map(function (relativePath) {
    const request = new Request(scopedUrl('./' + relativePath), { cache: 'reload' });
    return cacheResponse(cache, request, { cache: 'reload' });
  }));
  const rejected = results.filter(function (result) {
    return result.status === 'rejected';
  });
  if (rejected.length > 0) {
    throw new Error(rejected.length + ' Audiodateien konnten nicht gespeichert werden.');
  }
  await cache.put(versionCacheKey, new Response(manifest.version || '', {
    headers: { 'Content-Type': 'text/plain; charset=utf-8' }
  }));
}

async function notifyClient(clientId, message) {
  if (clientId) {
    const client = await self.clients.get(clientId);
    if (client) {
      client.postMessage(message);
      return;
    }
  }
  const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
  clients.forEach(function (client) {
    client.postMessage(message);
  });
}

self.addEventListener('install', function (event) {
  event.waitUntil(cacheAppShell().then(function () {
    return self.skipWaiting();
  }));
});

self.addEventListener('activate', function (event) {
  event.waitUntil(caches.keys().then(function (cacheNames) {
    return Promise.all(cacheNames.map(function (cacheName) {
      if (cacheName.indexOf('barabeat-studio-offline-') === 0 && cacheName !== CACHE_NAME) {
        return caches.delete(cacheName);
      }
      return Promise.resolve(false);
    }));
  }).then(function () {
    return self.clients.claim();
  }));
});

self.addEventListener('message', function (event) {
  const message = event.data || {};
  if (message.type !== 'barabeat-prepare-offline') {
    return;
  }
  event.waitUntil(Promise.all([
    cacheAppShell(),
    cacheOfflineAudioAssets()
  ]).then(function () {
    return notifyClient(event.source && event.source.id, { type: 'barabeat-offline-ready' });
  }).catch(function (error) {
    console.error('Offline-Vorbereitung fehlgeschlagen', error);
    return notifyClient(event.source && event.source.id, {
      type: 'barabeat-offline-error',
      message: error && error.message ? error.message : String(error)
    });
  }));
});

function getRelativePath(url) {
  const scopeUrl = new URL(self.registration.scope);
  if (url.origin !== scopeUrl.origin || url.pathname.indexOf(scopeUrl.pathname) !== 0) {
    return null;
  }
  return decodeURIComponent(url.pathname.slice(scopeUrl.pathname.length));
}

function isServerOnlyRequest(relativePath) {
  return relativePath.indexOf('PHP/') === 0 ||
    relativePath.indexOf('Noten/') === 0 ||
    relativePath === 'offline-assets.php';
}

async function networkFirst(request, fallbackPath) {
  const cache = await caches.open(CACHE_NAME);
  try {
    return await cacheResponse(cache, request, { cache: 'no-store' });
  } catch (error) {
    const cachedResponse = await cache.match(request, { ignoreSearch: true });
    if (cachedResponse) {
      return cachedResponse;
    }
    if (fallbackPath) {
      const fallbackResponse = await cache.match(scopedUrl(fallbackPath), { ignoreSearch: true });
      if (fallbackResponse) {
        return fallbackResponse;
      }
    }
    throw error;
  }
}

async function cacheFirstWithRefresh(event) {
  const cache = await caches.open(CACHE_NAME);
  const cachedResponse = await cache.match(event.request, { ignoreSearch: true });
  const refreshPromise = cacheResponse(cache, event.request).catch(function () {
    return null;
  });
  if (cachedResponse) {
    event.waitUntil(refreshPromise);
    return cachedResponse;
  }
  const networkResponse = await refreshPromise;
  if (networkResponse) {
    return networkResponse;
  }
  throw new Error('Ressource ist offline nicht verfügbar.');
}

self.addEventListener('fetch', function (event) {
  const request = event.request;
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);
  const relativePath = getRelativePath(url);
  if (relativePath === null || isServerOnlyRequest(relativePath)) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(networkFirst(request, './index.php'));
    return;
  }

  if (relativePath.indexOf('Audio/snd/') === 0 || relativePath.indexOf('Audio/snd alt/') === 0) {
    event.respondWith(cacheFirstWithRefresh(event));
    return;
  }

  event.respondWith(networkFirst(request));
});
