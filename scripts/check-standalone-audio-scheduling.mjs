import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const playerSource = fs.readFileSync(path.join(projectRoot, 'Audio/player.html'), 'utf8');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

assert(playerSource.includes('function isStandaloneAppPlayback()'),
  'Die Standalone-Erkennung fuer den Player fehlt.');
assert(playerSource.includes("matchMedia('(display-mode: standalone)').matches"),
  'Der installierte Desktop-App-Modus wird nicht erkannt.');
assert(playerSource.includes('const standaloneAppPlayback = isStandaloneAppPlayback();'),
  'Die Standalone-Erkennung wird nicht fuer den Scheduler gespeichert.');
assert(playerSource.includes('standaloneAppPlayback ? 2.0 : 0.25'),
  'Die groessere Planungssicherheit fuer die Standalone-App fehlt.');
assert(playerSource.includes('nextNoteTime < dTime - maximumSchedulerLag'),
  'Die Wiederherstellung nach einem verspaeteten Scheduler-Aufruf fehlt.');
assert(playerSource.includes('nextNoteTime = dTime + schedulerRecoveryDelay;'),
  'Verspaetete Schritte werden nicht auf einen sicheren Audiostart gesetzt.');
assert(playerSource.includes('instrumentInstance.pruneExpiredSources();'),
  'Abgelaufene Audioquellen werden nicht regelmaessig bereinigt.');
assert(playerSource.includes('const anchoredStepTime = Math.max(time, minimumNoteTime);'),
  'Verspaetete Schlaege werden nicht gemeinsam auf einen stabilen Startzeitpunkt gesetzt.');
assert(playerSource.includes("djembe1Time + 5 / strokeTempo"),
  'Der zeitliche Abstand des Djembe-1-Flams fehlt.');

console.log('Standalone-Audio: Planungsvorlauf und stabiler Flam-Abstand geprueft.');
