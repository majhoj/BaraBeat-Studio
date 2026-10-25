import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

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

const currentCacheName = [...stores.keys()][0];
const previousCacheName = 'barabeat-studio-offline-v90-quick-play-highlights';
assert.notEqual(currentCacheName, previousCacheName, 'The new range API requires a new offline cache');
stores.set(previousCacheName, new Map([
  [origin + 'JS/repeat-pickup.js', new Response('var BaraBeatRepeatPickup = {};')]
]));
const previousPlayerCacheName = 'barabeat-studio-offline-v93-quick-play-opening-repeat';
assert.notEqual(currentCacheName, previousPlayerCacheName, 'WAV headroom requires a new player cache');
stores.set(previousPlayerCacheName, new Map([
  [origin + 'Audio/player.html', new Response('old player without WAV headroom')]
]));
const previousPairingCacheName = 'barabeat-studio-offline-v94-wav-headroom';
assert.notEqual(currentCacheName, previousPairingCacheName, 'Shared pairing requires a new module cache');
stores.set(previousPairingCacheName, new Map([
  [origin + 'JS/repeat-pickup.js', new Response('var BaraBeatRepeatPickup = { buildRepeatRanges: function () { return []; } };')]
]));
const staleShellCacheName = 'barabeat-studio-offline-v95-repeat-pairing';
assert.notEqual(currentCacheName, staleShellCacheName, 'Replacing the deployed stale shell requires a new cache');
stores.set(staleShellCacheName, new Map([
  [origin + 'app-shell.html', new Response('old shell that discards the opening repeat at IN')]
]));
const previousTimelineCacheName = 'barabeat-studio-offline-v96-quick-play-shell';
assert.notEqual(currentCacheName, previousTimelineCacheName, 'Free Djembe assignment requires an updated timeline cache');
stores.set(previousTimelineCacheName, new Map([
  [origin + 'JS/timeline.js', new Response('old timeline without free Djembe assignment')]
]));
const previousPdfCacheName = 'barabeat-studio-offline-v97-free-djembe-targets';
assert.notEqual(currentCacheName, previousPdfCacheName, 'Multipage PDF export requires a new offline shell');
stores.set(previousPdfCacheName, new Map([
  [origin + 'app-shell.html', new Response('old shell with single-page PDF export')]
]));
const previousPdfScaleCacheName = 'barabeat-studio-offline-v98-multipage-pdf';
assert.notEqual(currentCacheName, previousPdfScaleCacheName, 'Larger PDF sheets require an updated shell');
stores.set(previousPdfScaleCacheName, new Map([
  [origin + 'app-shell.html', new Response('old shell with smaller PDF sheets')]
]));
const previousPdfMarginCacheName = 'barabeat-studio-offline-v99-pdf-scale';
assert.notEqual(currentCacheName, previousPdfMarginCacheName, 'PDF margins and named footers require a new shell');
stores.set(previousPdfMarginCacheName, new Map([
  [origin + 'app-shell.html', new Response('old shell with centered PDF sheets')]
]));
let activationPromise;
const previousLocalSaveCacheName = 'barabeat-studio-offline-v101-pdf-row-alignment';
assert.notEqual(currentCacheName, previousLocalSaveCacheName, 'Local save guards require updated shell, module and translations');
stores.set(previousLocalSaveCacheName, new Map([
  [origin + 'app-shell.html', new Response('old shell without save conflict handling')],
  [origin + 'JS/localLibrary.js', new Response('old library without atomic name checks')]
]));
const previousPdfAlignmentCacheName = 'barabeat-studio-offline-v100-pdf-margins-footer';
assert.notEqual(currentCacheName, previousPdfAlignmentCacheName, 'Aligned PDF rows require an updated shell');
stores.set(previousPdfAlignmentCacheName, new Map([
  [origin + 'app-shell.html', new Response('old shell with individually aligned PDF pages')]
]));
handlers.activate({ waitUntil: promise => { activationPromise = promise; } });
await activationPromise;
assert.equal(stores.size, 1);
assert(stores.has(currentCacheName), 'Activation keeps the fully installed new cache');
assert(!stores.has(previousCacheName), 'Activation removes the old module cache');
assert(!stores.has(previousPlayerCacheName), 'Activation removes the old player cache');
assert(!stores.has(previousPairingCacheName), 'Activation removes the module without collectRepeatRanges');
assert(!stores.has(staleShellCacheName), 'Activation removes the stale deployed offline shell');
assert(!stores.has(previousTimelineCacheName), 'Activation removes the old placement logic');
assert(!stores.has(previousPdfCacheName), 'Activation removes the single-page PDF exporter');
assert(!stores.has(previousPdfScaleCacheName), 'Activation removes the smaller PDF layout');
assert(!stores.has(previousPdfMarginCacheName), 'Activation removes the centered PDF layout');
assert(!stores.has(previousPdfAlignmentCacheName), 'Activation removes the individually aligned PDF layout');
assert(!stores.has(previousLocalSaveCacheName), 'Activation removes the unguarded local save implementation');

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
  assert(html.indexOf('src="JS/repeat-pickup.js') < html.indexOf('src="JS/practice.js'));
  assert(html.includes('const stepsPerBar = BaraBeatTiming.getGrid('), 'Generated shell must contain the current reader');
}
for (const path of ['JS/timing.js?v=changed', 'Audio/player.html?launchReload=offline#launch=test']) {
  const response = await request(path);
  assert.equal(response.status, 200);
  const text = await response.text();
  assert(text.includes(path.startsWith('JS/') ? 'var BaraBeatTiming' : 'src="../JS/timing.js"'));
}
assert((await (await request('JS/repeat-pickup.js?v=changed')).text()).includes('var BaraBeatRepeatPickup'),
  'Internal pickups must also work with the offline player');

const offlineModule = await (await request('JS/repeat-pickup.js?v=changed')).text();
const offlineTimeline = await (await request('JS/timeline.js?v=changed')).text();
const offlinePlayer = await (await request('Audio/player.html?launchReload=offline')).text();
const offlinePractice = await (await request('JS/practice.js?v=changed')).text();
const offlineShell = await (await request('index.php')).text();
assert.equal(offlineTimeline, source('JS/timeline.js'), 'Offline insertion must use the updated target assignment');
function quickPlayCode(text) {
  const start = text.indexOf('function buildSheetQuickPlayRepeatRanges(');
  const end = text.indexOf('function createSheetPatternMoveOverlayButton(', start);
  assert(start >= 0 && end > start, 'Quick Play preparation code must be present');
  return text.slice(start, end);
}
assert.equal(quickPlayCode(offlineShell), quickPlayCode(source('index.php')),
  'Offline Quick Play must match the current editor; regenerate app-shell.html before deployment');
assert.equal(offlinePlayer, source('Audio/player.html'), 'Offline player must be the updated file');
assert.equal(offlineModule, source('JS/repeat-pickup.js'), 'Offline module must expose the new pairing API');
assert.equal(offlinePractice, source('JS/practice.js'), 'Offline practice must match its shared module');
const music = vm.createContext({ Blob });
vm.runInContext(offlineModule, music);
loadFunctions(music, offlineTimeline, ['getTimelineRepeatMarkerList', 'buildTimelinePatternRepeatRanges']);
loadFunctions(music, offlinePlayer, ['sanitizeRepeatRanges', 'normalizeRepeatMarkerList', 'buildRepeatRangesFromPatternBars']);
loadFunctions(music, offlinePractice, ['getPracticeRepeatMarkerList', 'buildPracticeRepeatRangesFromBars']);
loadFunctions(music, offlineShell, ['buildSheetQuickPlayRepeatRanges']);
assert.equal(typeof music.BaraBeatRepeatPickup.collectRepeatRanges, 'function');
const bars = [{ repeat: { start: true } }, { repeat: { end: 2 } }];
for (const ranges of [music.buildTimelinePatternRepeatRanges({ bars }), music.buildRepeatRangesFromPatternBars(bars),
  music.buildPracticeRepeatRangesFromBars(bars), music.buildSheetQuickPlayRepeatRanges(bars)]) {
  assert.deepEqual(JSON.parse(JSON.stringify(ranges)), [{ startBar: 1, endBar: 2, count: 2 }],
    'Cached consumers must run with the cached range module, not an old API');
}
const duplicates = [{ repeat: { start: [true, true], end: [2, 2] } }];
assert.equal(music.BaraBeatRepeatPickup.collectRepeatRanges(duplicates).length, 2);
assert.equal(music.buildPracticeRepeatRangesFromBars(duplicates).length, 2);
assert.equal(music.buildSheetQuickPlayRepeatRanges(duplicates).length, 2);
assert.equal(music.buildRepeatRangesFromPatternBars(duplicates).length, 1);
loadFunctions(music, offlinePlayer, ['encodeWavFromAudioBuffer']);
const offlineWav = new DataView(await music.encodeWavFromAudioBuffer({
  numberOfChannels: 1, sampleRate: 48000, length: 2,
  getChannelData: () => Float32Array.of(2, -2)
}).arrayBuffer());
assert.equal(offlineWav.getInt16(44, true), 32111, 'Offline export applies the 0.98 target peak');
assert.equal(offlineWav.getInt16(46, true), -32112, 'Offline export preserves the negative half-wave');
for (const path of ['Audio/audioplayer.php', 'PHP/dateiladen.php', 'impressum.php', 'offline-assets.php', 'Noten/example.bbs']) {
  assert.equal(await request(path), undefined, path + ' must remain network-only');
}
assert.equal(await request('index.php', 'navigate', 'POST'), undefined, 'POST must not enter the cache');
console.log('Timing offline: real worker routing with simulated cache/network; cold start, static player/module and PHP/session/CSRF exclusions checked.');
