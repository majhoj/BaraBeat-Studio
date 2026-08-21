import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');

function read(relativePath) {
  return fs.readFileSync(path.join(projectRoot, relativePath), 'utf8');
}

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

const shell = read('app-shell.html');
const serviceWorker = read('service-worker.js');
const indexSource = read('index.php');
const serverLibrarySource = read('JS/serverLibrary.js');

assert(shell.includes('window.BARABEAT_OFFLINE_BOOT = true;'), 'Offline-Markierung fehlt in app-shell.html.');
assert(shell.includes('data-offline-boot="true"'), 'Offline-Datenattribut fehlt in app-shell.html.');
assert(shell.includes('window.BaraBeatAppBootstrap.getOfflineEditionConfig'), 'Offline-Editionskonfiguration fehlt.');
assert(!/<\?php/i.test(shell), 'app-shell.html enthält nicht aufgelöstes PHP.');
assert(!/"csrfToken"\s*:\s*"[^"]+"/.test(shell), 'app-shell.html enthält einen CSRF-Wert.');
assert(!shell.includes('BARABEAT_ACCESS_PASSWORD'), 'app-shell.html enthält einen Zugangsbezeichner.');

assert(serviceWorker.includes("const CACHE_NAME = 'barabeat-studio-offline-v20-mobile-practice-scroll';"), 'Cache-Version v20 fehlt.');
assert(serviceWorker.includes("'./app-shell.html'"), 'app-shell.html fehlt im Precache.');
assert(serviceWorker.includes("'./JS/app-bootstrap.js'"), 'app-bootstrap.js fehlt im Precache.');
assert(serviceWorker.includes('isAppEntryNavigation(request, relativePath)'), 'App-Einstiegsnavigation wird nicht separat behandelt.');
assert(serviceWorker.includes('networkAppEntryOrShell(request)'), 'Statischer Kaltstart-Fallback fehlt.');
assert(serviceWorker.includes("relativePath === 'index.php'"), 'index.php wird nicht als App-Einstieg erkannt.');
assert(serviceWorker.includes('/\\.php$/i.test(relativePath)'), 'Allgemeiner PHP-Cache-Ausschluss fehlt.');
assert(serviceWorker.includes("relativePath.indexOf('PHP/') === 0"), 'PHP-Endpunkt-Ausschluss fehlt.');
const fetchHandlerSource = serviceWorker.slice(serviceWorker.indexOf("self.addEventListener('fetch'"));
assert(fetchHandlerSource.indexOf('if (isAppEntryNavigation(request, relativePath))') < fetchHandlerSource.indexOf('isSessionBoundDocumentRequest(relativePath)'), 'App-Einstiegsfallback liegt hinter dem allgemeinen PHP-Ausschluss.');

assert(indexSource.includes("barabeat_require_access('page');"), 'Online-Zugangskontrolle wurde aus index.php entfernt.');
assert(indexSource.includes('window.BARABEAT_OFFLINE_BOOT = <?php echo'), 'Online-/Offline-Bootmarkierung fehlt in index.php.');
assert(serverLibrarySource.includes('assertOnlineAvailable();'), 'Serverbibliothek besitzt keine Offline-Sperre.');
assert(serverLibrarySource.includes('!isOfflineBoot() && response'), '401 darf im Offline-Kaltstart nicht zur Loginseite umleiten.');

let fetchCalls = 0;
const context = {
  window: {
    BARABEAT_OFFLINE_BOOT: true,
    BaraBeatI18n: { t: (key) => key },
    location: { assign: () => { throw new Error('Unerwartete Navigation'); } }
  },
  FormData: class FormData {},
  DOMParser: class DOMParser {},
  fetch: async () => {
    fetchCalls += 1;
    throw new Error('Unerwarteter Netzwerkaufruf');
  },
  console
};
vm.createContext(context);
vm.runInContext(serverLibrarySource, context);

await context.window.serverLibrary.listScores().then(
  () => { throw new Error('Serverzugriff wurde im Offline-Kaltstart nicht blockiert.'); },
  (error) => assert(error.message === 'offline.onlineOnly', 'Unerwartete Offline-Fehlermeldung der Serverbibliothek.')
);
assert(fetchCalls === 0, 'Serverbibliothek hat trotz Offline-Kaltstart fetch() aufgerufen.');

console.log('Offline-Kaltstart: Struktur- und Sicherheitsprüfung erfolgreich.');
