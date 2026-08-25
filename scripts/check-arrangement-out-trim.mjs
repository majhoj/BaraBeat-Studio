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
  playerSource.includes("if (shouldLimitSectionAtOut && entryData.label !== 'Begleitung') {\n              limitTimelineSectionLengthAtOut(repeatedSection, effectiveNotes.length);"),
  'Das Out einer Begleitung begrenzt weiterhin den gesamten parallelen Abschnitt.'
);
assert(
  playerSource.includes('padTimelineAccompanimentOutForParallelSection(effectiveNotes, entryData, repeatEntries)'),
  'Die gekürzte Begleitspur wird im wiederholten Parallelabschnitt nicht mit Stille aufgefüllt.'
);
assert(
  playerSource.includes('getTimelineParallelAccompanimentLoopLength(\n              entryData,\n              repeatEntries,\n              shouldApplyOut'),
  'Das Out der Begleitung wird weiterhin auf deren ersten statt auf den letzten parallelen Zyklus angewendet.'
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
  'getTimelineEntryPlaybackLength',
  'getMaxPatternNotesLength',
  'applyOutToPatternNotes',
  'getTimelineEntryOutStep',
  'getTimelineEntryOutBarEndStep',
  'getTimelineEntryEffectiveNotes',
  'loopNotesToLength',
  'padNotesToLength',
  'padTimelineAccompanimentOutForParallelSection',
  'getTimelineParallelAccompanimentLoopLength',
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

context.shortOutAccompaniment = {
  label: 'Begleitung',
  patternNotes: new Array(24).fill('x'),
  patternInStep: null,
  patternOutStep: 18,
  patternOutBarEndStep: 24,
  patternOverlapStep: null
};
context.longParallelAccompaniment = {
  label: 'Begleitung',
  patternNotes: new Array(48).fill('f')
};
const parallelAccompanimentLoopLength = vm.runInContext(
  'getTimelineParallelAccompanimentLoopLength(shortOutAccompaniment, [shortOutAccompaniment, longParallelAccompaniment], true);',
  context
);
assert(
  parallelAccompanimentLoopLength === 48,
  'Die kürzere Begleitung wird vor ihrem Out nicht bis zur gemeinsamen Abschnittslänge wiederholt.'
);
context.parallelAccompanimentLoopLength = parallelAccompanimentLoopLength;
context.shortOutNotes = vm.runInContext(
  'getTimelineEntryEffectiveNotes(shortOutAccompaniment, true, parallelAccompanimentLoopLength);',
  context
);
const paddedAccompanimentOut = vm.runInContext(
  'padTimelineAccompanimentOutForParallelSection(shortOutNotes, shortOutAccompaniment, [shortOutAccompaniment, longParallelAccompaniment]);',
  context
);
assert(
  paddedAccompanimentOut.length === 48,
  'Das Out einer Begleitspur verkürzt weiterhin eine längere parallele Begleitung.'
);
assert(
  paddedAccompanimentOut.slice(0, 43).every(function (noteValue) { return noteValue === 'x'; }) &&
    paddedAccompanimentOut.slice(43).every(function (noteValue) { return noteValue === 'f'; }),
  'Das Out greift nicht erst im letzten Zyklus oder die Begleitspur bleibt danach hörbar.'
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

context.accompanimentPickupAfterOut = {
  label: 'Begleitung',
  patternNotes: new Array(24).fill('f'),
  patternInStep: 22,
  patternOutStep: 18,
  patternOutBarEndStep: 24,
  patternOverlapStep: null
};
assert(
  vm.runInContext('hasTimelineEntryLeadingPickup(accompanimentPickupAfterOut);', context) === true,
  'Das In eines zyklischen Begleitpatterns wird hinter dessen Out nicht als Auftakt erkannt.'
);
assert(
  vm.runInContext('getTimelineEntrySkippedStartStep(accompanimentPickupAfterOut);', context) === 0,
  'Das Begleitpattern wird nach seinem Auftakt fälschlich am Anfang gekürzt.'
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
