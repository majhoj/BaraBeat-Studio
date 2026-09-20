import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { source } from './helpers/music-context.mjs';

const origin = 'https://barabeat.test/';
const handlers = {};
const stores = new Map();
let online = true;
const keyOf = request => typeof request === 'string' ? request : request.url;
const withoutQuery = value => { const url = new URL(value); url.search = ''; return url.href; };
const context = vm.createContext({
  URL, Request, Response, console,
  self: {
    registration: { scope: origin },
    addEventListener: (name, handler) => { handlers[name] = handler; },
    skipWaiting: async () => {}, clients: { claim: async () => {} }
  },
  caches: {
    async open(name) {
      if (!stores.has(name)) stores.set(name, new Map());
      const store = stores.get(name);
      return {
        async put(request, response) {
          assert(!/\.php$/i.test(new URL(keyOf(request)).pathname), 'Never cache PHP responses');
          store.set(keyOf(request), response.clone());
        },
        async match(request, options = {}) {
          const key = keyOf(request);
          const storedKey = [...store.keys()].find(candidate => options.ignoreSearch
            ? withoutQuery(candidate) === withoutQuery(key) : candidate === key);
          return store.get(storedKey)?.clone();
        }
      };
    },
    async keys() { return [...stores.keys()]; },
    async delete(name) { return stores.delete(name); }
  },
  fetch: async request => {
    if (!online) throw new Error('Offline');
    const path = new URL(keyOf(request)).pathname.slice(1);
    if (path === '' || path === 'index.php') return new Response('PRIVATE_SESSION_CSRF_RESPONSE');
    return new Response(fs.readFileSync(new URL('../' + path, import.meta.url)));
  }
});
vm.runInContext(source('service-worker.js'), context);
let installPromise;
handlers.install({ waitUntil: promise => { installPromise = promise; } });
await installPromise;
assert.equal(stores.size, 1);

async function request(path, mode = 'navigate', method = 'GET') {
  let response;
  const url = new URL(path, origin);
  url.hash = ''; // Fragments belong to the document, never to a network request.
  handlers.fetch({
    request: { url: url.href, mode, method },
    respondWith: promise => { response = promise; }, waitUntil: () => {}
  });
  return response ? await response : undefined;
}
assert.equal(await (await request('index.php')).text(), 'PRIVATE_SESSION_CSRF_RESPONSE');
online = false;
for (const path of ['', 'index.php']) {
  const response = await request(path);
  assert.equal(response.status, 200);
  const html = await response.text();
  assert(html.includes('window.BARABEAT_OFFLINE_BOOT = true;'));
  assert(!html.includes('PRIVATE_SESSION_CSRF_RESPONSE'));
  assert(!/"csrfToken"\s*:\s*"[^"]+"/.test(html));
  assert(html.indexOf('src="JS/timing.js') < html.indexOf('src="JS/practice.js'));
  assert(html.includes('const stepsPerBar = BaraBeatTiming.getGrid('), 'Generated shell must contain the current reader');
}
for (const path of ['JS/timing.js?v=changed', 'Audio/player.html?launchReload=offline#launch=test']) {
  const response = await request(path);
  assert.equal(response.status, 200);
  const text = await response.text();
  assert(text.includes(path.startsWith('JS/') ? 'var BaraBeatTiming' : 'src="../JS/timing.js"'));
}
for (const path of ['Audio/audioplayer.php', 'PHP/dateiladen.php', 'impressum.php', 'offline-assets.php', 'Noten/example.bbs']) {
  assert.equal(await request(path), undefined, path + ' must remain network-only');
}
assert.equal(await request('index.php', 'navigate', 'POST'), undefined, 'POST must not enter the cache');
console.log('Timing offline: real worker routing with simulated cache/network; cold start, static player/module and PHP/session/CSRF exclusions checked.');
