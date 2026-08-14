import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const serviceWorkerSource = fs.readFileSync(path.join(projectRoot, 'service-worker.js'), 'utf8');
const appShellMatch = serviceWorkerSource.match(/const APP_SHELL = \[([\s\S]*?)\n\];/);

if (!appShellMatch) {
  throw new Error('APP_SHELL wurde in service-worker.js nicht gefunden.');
}

const appShellPaths = Array.from(appShellMatch[1].matchAll(/'([^']+)'/g), function (match) {
  return match[1].replace(/^\.\//, '');
});
const baseUrl = new URL(process.argv[2] || 'http://127.0.0.1:8877/');
const simulatedCache = new Map();
let hasHttpError = false;

console.log('Precache-HTTP-Prüfung: ' + baseUrl.href);
for (const relativePath of appShellPaths) {
  const requestUrl = new URL(relativePath, baseUrl);
  try {
    const response = await fetch(requestUrl, { cache: 'no-store', redirect: 'follow' });
    const result = response.ok ? 'vorhanden' : 'FEHLER';
    console.log(`${relativePath}\tHTTP ${response.status}\t${result}`);
    if (response.ok) {
      simulatedCache.set(requestUrl.href, response.clone());
    } else {
      hasHttpError = true;
    }
  } catch (error) {
    hasHttpError = true;
    console.log(`${relativePath}\tNETZWERKFEHLER\t${error.message}`);
  }
}

function cacheMatch(requestPath) {
  const requestUrl = new URL(requestPath, baseUrl);
  requestUrl.search = '';
  requestUrl.hash = '';
  return simulatedCache.has(requestUrl.href);
}

const requiredMatches = [
  'app-shell.html?offline-cold-start=diagnostic',
  'Audio/player.html?launchReload=diagnostic',
  'Audio/css/audio_style.css',
  'Audio/js/instrument_2.js',
  'JS/i18n.js',
  'JS/edition.js',
  'JS/app-bootstrap.js',
  'Assets/pwa-icon-192.png',
  'Assets/pwa-icon-512.png',
  'manual/offline/de.html',
  'manual/offline/en.html',
  'manual/offline/fr.html',
  'manual/offline/es.html',
  'manual/offline/pt.html',
  'manual/assets/manual.css',
  'manual/assets/manual.js',
  'manual/assets/poster.png',
  'legal/legal.css',
  'legal/navigation.js',
  'legal/offline/impressum.html',
  'legal/offline/datenschutz.html'
];

console.log('\nSimulierte Cache-Matches (ignoreSearch):');
for (const requestPath of requiredMatches) {
  const matched = cacheMatch(requestPath);
  console.log(`${requestPath}\t${matched ? 'JA' : 'NEIN'}`);
  if (!matched) {
    hasHttpError = true;
  }
}

if (hasHttpError) {
  process.exitCode = 1;
}
