import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const playerSource = fs.readFileSync(path.join(projectRoot, 'Audio/player.html'), 'utf8');
const editorSource = fs.readFileSync(path.join(projectRoot, 'index.php'), 'utf8');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function extractFunction(source, functionName) {
  const start = source.indexOf('function ' + functionName + '(');
  if (start === -1) {
    throw new Error('Funktion fehlt: ' + functionName);
  }
  const nextFunction = source.indexOf('\nfunction ', start + 1);
  return source.slice(start, nextFunction === -1 ? source.length : nextFunction);
}

assert(editorSource.includes("#overlap"), 'Überlappung fehlt in den Editor-Selektoren.');
assert(editorSource.includes("{ id: 'overlap', labelKey: 'score.legend.overlap' }"), 'Mobiles Überlappungswerkzeug fehlt.');
assert(playerSource.includes("getPatternControlStep(pattern, 'overlap')"), 'Player liest die Überlappungsmarkierung nicht.');

const context = vm.createContext({
  console,
  isPracticeMode: false,
  isSheetQuickPlayMode: false,
  trackInstrumentNames: ['Djembe_1', 'Djembe_2'],
  normalizeSectionRepeatCount(value) {
    return Math.max(1, Math.round(Number(value) || 1));
  }
});

[
  'mergeNotesIntoTrack',
  'padNotesToLength',
  'materializeOrderedSectionTracks',
  'applyTimelineSectionOverlaps'
].forEach(function (functionName) {
  vm.runInContext(extractFunction(playerSource, functionName), context);
});

const silentBar = new Array(12).fill('f');
const currentUnit = silentBar.slice();
currentUnit[8] = 'slap';
const originalSections = [
  {
    trackNotes: {
      Djembe_1: currentUnit,
      Djembe_2: silentBar.slice()
    },
    length: 12,
    playbackLength: 24,
    repeatCount: 2,
    overlapTailLength: 4
  },
  {
    trackNotes: {
      Djembe_1: ['tone', 'f', 'f', 'f'],
      Djembe_2: ['bass', 'f', 'f', 'f']
    },
    length: 4,
    playbackLength: 4,
    repeatCount: 1,
    overlapTailLength: 0
  }
];
const sections = JSON.parse(JSON.stringify(originalSections));

context.sections = sections;
vm.runInContext('applyTimelineSectionOverlaps(sections);', context);

assert(sections[0].fixedLength === 20, 'Nur der letzte markierte Takt darf überlappen.');
assert(sections[0].trackNotes.Djembe_1[8] === 'slap', 'Der frühere Durchlauf wurde verändert.');
assert(sections[0].trackNotes.Djembe_1.length === 20, 'Der überlappende Schlussteil wurde nicht abgetrennt.');
assert(sections[1].trackNotes.Djembe_1[0] === 'slap', 'Die markierte Schlussnote liegt nicht auf dem nächsten Abschnitt.');
assert(sections[1].trackNotes.Djembe_2[0] === 'bass', 'Parallele Instrumente des nächsten Abschnitts gingen verloren.');

const practiceSections = JSON.parse(JSON.stringify(originalSections));
const practiceSectionsBefore = JSON.stringify(practiceSections);
context.practiceSections = practiceSections;
context.isPracticeMode = true;
vm.runInContext('applyTimelineSectionOverlaps(practiceSections);', context);
assert(JSON.stringify(practiceSections) === practiceSectionsBefore, 'Übungsmodus darf durch die Arrangement-Markierung nicht verändert werden.');

console.log('Überlappungs-Steuerelement: Struktur- und Wiedergabetest erfolgreich.');
