import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const playerSource = fs.readFileSync(path.join(projectRoot, 'Audio/player.html'), 'utf8');

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

assert(
  playerSource.includes('if (shouldLimitSectionAtOut) {\n              limitTimelineSectionLengthAtOut(repeatedSection, effectiveNotes.length);'),
  'Parallele Arrangement-Abschnitte werden nicht am Out beendet.'
);
assert(
  playerSource.includes('if (currentCycleHasAppliedOut) {\n          limitTimelineSectionLengthAtOut('),
  'Arrangement-Abschnitte werden nach einem Out nicht auf die gekürzte Dauer festgelegt.'
);
assert(
  playerSource.includes('section.trackNotes[instrumentName].slice(0, section.length)'),
  'Die flache Player-Spur übernimmt noch Noten hinter der gekürzten Abschnittsgrenze.'
);

const context = vm.createContext({
  trackInstrumentNames: ['Djembe_1', 'Djembe_2'],
  isPracticeMode: false,
  isSheetQuickPlayMode: false
});

context.getSectionLength = function (notes) {
  return Array.isArray(notes) ? notes.length : 0;
};
context.getTimelineStepsPerBar = function () {
  return 24;
};

[
  'limitTimelineSectionLengthAtOut',
  'trimTimelineSectionTracksToFixedLength',
  'getPatternNotesLength',
  'hasTimelineEntryLeadingPickup',
  'getTimelineEntryPickupEndStep',
  'getTimelineEntrySkippedStartStep',
  'getTimelineEntryOverlapTailLength'
].forEach(function (functionName) {
  vm.runInContext(extractFunction(playerSource, functionName), context);
});

const oneBarAfterOut = new Array(24).fill('f');
const section = {
  trackNotes: {
    Djembe_1: oneBarAfterOut,
    Djembe_2: []
  },
  fixedLength: 0
};

context.section = section;
vm.runInContext('limitTimelineSectionLengthAtOut(section, 24);', context);

assert(section.fixedLength === 24, 'Der stumme zweite Takt wurde nicht aus der Abschnittsdauer entfernt.');

context.longerParallelSection = {
  trackNotes: {
    Djembe_1: new Array(24).fill('f'),
    Djembe_2: new Array(48).fill('f')
  },
  fixedLength: 0
};
vm.runInContext('limitTimelineSectionLengthAtOut(longerParallelSection, 24);', context);
vm.runInContext('trimTimelineSectionTracksToFixedLength(longerParallelSection);', context);
assert(
  context.longerParallelSection.fixedLength === 24,
  'Eine längere parallele Spur hält den Abschnitt nach dem Out weiterhin offen.'
);
assert(
  context.longerParallelSection.trackNotes.Djembe_1.length === 24 &&
    context.longerParallelSection.trackNotes.Djembe_2.length === 24,
  'Noten hinter dem Out bleiben intern erhalten und verschieben den folgenden Abschnitt.'
);

context.leadingPickup = {
  label: 'Solo',
  patternNotes: new Array(48).fill('f'),
  patternInStep: 22,
  patternOutStep: null,
  patternOutBarEndStep: null,
  patternOverlapStep: null
};
assert(
  vm.runInContext('hasTimelineEntryLeadingPickup(leadingPickup);', context) === true,
  'Ein echter Auftakt am Patternanfang wird nicht mehr erkannt.'
);

context.internalTransition = {
  label: 'Solo',
  patternNotes: new Array(1560).fill('f'),
  patternInStep: 1288,
  patternOutStep: 1248,
  patternOutBarEndStep: 1272,
  patternOverlapStep: 1248
};
assert(
  vm.runInContext('hasTimelineEntryLeadingPickup(internalTransition);', context) === false,
  'Ein In hinter einem früheren Out wird weiterhin als Patternauftakt behandelt.'
);
assert(
  vm.runInContext('getTimelineEntrySkippedStartStep(internalTransition);', context) === 0,
  'Der Patternanfang vor einer internen Out/In-Übergabe wird weiterhin abgeschnitten.'
);
assert(
  vm.runInContext('getTimelineEntryOverlapTailLength(internalTransition, 1272);', context) === 24,
  'Die Überlappung reicht weiterhin über den markierten Out-Takt hinaus.'
);

console.log('Arrangement-Out: gekürzte Abschnittsdauer erfolgreich geprüft.');
