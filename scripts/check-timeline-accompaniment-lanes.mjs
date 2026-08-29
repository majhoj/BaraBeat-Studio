import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const playerSource = fs.readFileSync(path.join(projectRoot, 'Audio/player.html'), 'utf8');
const timelineSource = fs.readFileSync(path.join(projectRoot, 'JS/timeline.js'), 'utf8');

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
  timelineSource.includes('AccompanimentSegments: timelineState.accompanimentSegments.map'),
  'Die Begleitspuren werden nicht an den Player übergeben.'
);
assert(
  timelineSource.includes('accompanimentSegments: timelineState.accompanimentSegments.map'),
  'Die Begleitspuren werden nicht im Notenblatt gespeichert.'
);
assert(
  timelineSource.includes('TimelineTrailingBars: Math.max(0, timelineLayout.totalBars - timelineLayout.naturalTotalBars)'),
  'Die manuell ergänzten Leertakte werden nicht als Player-Differenz berechnet.'
);
assert(
  playerSource.includes('ensureTimelinePlaybackBarCapacity(sections, config)'),
  'Der Player erweitert das Arrangement nicht bis zur übergebenen Gesamttaktzahl.'
);
const resizeHandleSource = extractFunction(timelineSource, 'bindTimelineAccompanimentResizeHandle');
assert(
  resizeHandleSource.includes("getPropertyValue('--timeline-track-bar-width')"),
  'Der Begleitspur-Anfasser verwendet nicht die sichtbare Taktbreite der horizontalen Timeline.'
);
assert(
  resizeHandleSource.includes('(pointerEvent.clientX - startClientX) / barWidth'),
  'Der Mausweg wird nicht anhand der tatsächlichen Taktbreite in Takte umgerechnet.'
);

const trackInstrumentNames = ['Djembe_1', 'Kenkeni', 'Sangban', 'Doundoun'];
const context = vm.createContext({
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  trackInstrumentNames
});

context.normalizeSectionRepeatCount = function (value) {
  return Math.max(1, Math.round(Number(value) || 1));
};
context.materializeOrderedSectionTracks = function (section) {
  const result = {};
  trackInstrumentNames.forEach(function (instrumentName) {
    const unitNotes = (section.trackNotes[instrumentName] || []).slice();
    result[instrumentName] = [];
    for (let repeatIndex = 0; repeatIndex < section.repeatCount; repeatIndex++) {
      result[instrumentName].push(...unitNotes);
    }
  });
  return result;
};
context.getTimelineStepsPerBar = function () { return 4; };
context.normalizeTimelineTargetInstrument = function (value) { return value; };
context.flattenPatternNotes = function (pattern) { return pattern.testNotes.slice(); };
context.getPatternInStep = function (pattern) {
  return pattern.testInStep === undefined ? null : pattern.testInStep;
};
context.getPatternOutStep = function (pattern) {
  return pattern.testOutStep === undefined ? null : pattern.testOutStep;
};
context.getPatternOutBarEndStep = function (pattern) {
  return pattern.testOutBarEndStep === undefined ? null : pattern.testOutBarEndStep;
};
context.getPatternOverlapStep = function () { return null; };
context.padNotesToLength = function (sourceNotes, targetLength) {
  const result = sourceNotes.slice();
  while (result.length < targetLength) {
    result.push('f');
  }
  return result;
};
context.normalizeSectionTempo = function (value) { return value === null ? null : Number(value); };
context.createOrderedSection = function (label) {
  const trackNotes = {};
  trackInstrumentNames.forEach(function (instrumentName) {
    trackNotes[instrumentName] = [];
  });
  return {
    label,
    labelName: label,
    runtimeKey: '',
    sectionTempo: null,
    fixedLength: 0,
    trackNotes
  };
};

vm.runInContext(extractFunction(playerSource, 'normalizeTimelineGapBeforeBars'), context);
vm.runInContext(extractFunction(playerSource, 'createTimelineGapSection'), context);
vm.runInContext(extractFunction(playerSource, 'applyOutToPatternNotes'), context);
vm.runInContext(extractFunction(playerSource, 'getPatternNotesLength'), context);
vm.runInContext(extractFunction(playerSource, 'hasTimelineEntryLeadingPickup'), context);
vm.runInContext(extractFunction(playerSource, 'getTimelineEntryPickupEndStep'), context);
vm.runInContext(extractFunction(playerSource, 'getTimelineEntrySkippedStartStep'), context);
vm.runInContext(extractFunction(playerSource, 'getTimelineEntryOutStep'), context);
vm.runInContext(extractFunction(playerSource, 'getTimelineEntryOutBarEndStep'), context);
vm.runInContext(extractFunction(playerSource, 'loopNotesToLength'), context);
vm.runInContext(extractFunction(playerSource, 'getTimelineEntryEffectiveNotes'), context);
const gapSection = vm.runInContext('createTimelineGapSection(2, 90, 1);', context);
assert(gapSection.fixedLength === 8, 'Zwei Leertakte erhalten nicht die korrekte Wiedergabelänge.');
assert(trackInstrumentNames.every(instrumentName =>
  gapSection.trackNotes[instrumentName].length === 8 &&
  gapSection.trackNotes[instrumentName].every(note => note === 'f')
), 'Ein Timeline-Leertakt enthält unerwartete Noten.');

vm.runInContext(extractFunction(playerSource, 'buildTimelinePickupNotes'), context);
vm.runInContext(extractFunction(playerSource, 'writeTimelineTrackRange'), context);
vm.runInContext(extractFunction(playerSource, 'registerTimelinePickupLeadAtStep'), context);
vm.runInContext(extractFunction(playerSource, 'ensureTimelinePlaybackBarCapacity'), context);
vm.runInContext(extractFunction(playerSource, 'applyExplicitTimelineAccompanimentSegments'), context);

function createSection(notesByInstrument, repeatCount = 1) {
  const trackNotes = {};
  trackInstrumentNames.forEach(function (instrumentName) {
    trackNotes[instrumentName] = (notesByInstrument[instrumentName] || new Array(4).fill('f')).slice();
  });
  return {
    trackNotes,
    sectionTargets: [],
    length: 4,
    playbackLength: 4 * repeatCount,
    repeatCount
  };
}

context.capacitySections = [createSection({})];
context.capacityConfig = {
  TimelineTotalBars: 3,
  AccompanimentSegments: []
};
vm.runInContext(
  'ensureTimelinePlaybackBarCapacity(capacitySections, capacityConfig);',
  context
);
assert(
  context.capacitySections.length === 2 && context.capacitySections[1].fixedLength === 8,
  'Die Player-Grundtimeline wird nicht bis zur sichtbaren Gesamtlänge erweitert.'
);

const timelineBuildSource = extractFunction(playerSource, 'buildTimelineSections');
assert(
  timelineBuildSource.indexOf('ensureTimelinePlaybackBarCapacity(sections, config)') <
    timelineBuildSource.indexOf('applyExplicitTimelineAccompanimentSegments(sections, config, patternById)'),
  'Die Begleitspuren werden eingesetzt, bevor die benötigten Endtakte angelegt sind.'
);

const sections = [
  createSection({ Djembe_1: ['d', 'f', 'f', 'f'] }),
  createSection({}),
  createSection({})
];
const config = {
  AccompanimentSegments: [{
    patternId: 'sangban-pattern',
    targetInstrument: 'Sangban',
    startBar: 2,
    barCount: 2
  }]
};
const patternById = {
  'sangban-pattern': {
    id: 'sangban-pattern',
    label: 'Begleitung',
    testNotes: ['s', 'f', 's', 'f']
  }
};

context.sections = sections;
context.config = config;
context.patternById = patternById;
vm.runInContext('applyExplicitTimelineAccompanimentSegments(sections, config, patternById);', context);

assert(sections[0].trackNotes.Sangban.every(note => note === 'f'), 'Die Begleitspur beginnt vor ihrem Starttakt.');
assert(sections[1].trackNotes.Sangban.join('') === 'sfsf', 'Die Begleitspur fehlt im ersten Zielabschnitt.');
assert(sections[2].trackNotes.Sangban.join('') === 'sfsf', 'Die Begleitspur läuft nicht über Abschnittsgrenzen weiter.');
assert(sections[0].trackNotes.Djembe_1[0] === 'd', 'Eine bestehende Instrumentenspur wurde überschrieben.');

const repeatedSection = createSection({ Sangban: ['a', 'a', 'a', 'a'] }, 2);
context.repeatedSections = [repeatedSection];
context.repeatedConfig = {
  AccompanimentSegments: [{
    patternId: 'sangban-pattern',
    targetInstrument: 'Sangban',
    startBar: 2,
    barCount: 1
  }]
};
vm.runInContext(
  'applyExplicitTimelineAccompanimentSegments(repeatedSections, repeatedConfig, patternById);',
  context
);
assert(repeatedSection.repeatCount === 1 && repeatedSection.length === 8, 'Wiederholte Abschnitte wurden nicht materialisiert.');
assert(repeatedSection.trackNotes.Sangban.slice(0, 4).every(note => note === 'a'), 'Die Begleitspur beginnt im falschen Wiederholungstakt.');
assert(repeatedSection.trackNotes.Sangban.slice(4).join('') === 'sfsf', 'Die Begleitspur liegt nicht auf dem gewählten Wiederholungstakt.');

const shortBarSection = createSection({});
shortBarSection.length = 3;
shortBarSection.playbackLength = 3;
trackInstrumentNames.forEach(function (instrumentName) {
  shortBarSection.trackNotes[instrumentName] = shortBarSection.trackNotes[instrumentName].slice(0, 3);
});
const sectionAfterShortBar = createSection({});
context.shortBarSections = [shortBarSection, sectionAfterShortBar];
context.shortBarConfig = {
  AccompanimentSegments: [{
    patternId: 'sangban-pattern',
    targetInstrument: 'Sangban',
    startBar: 2,
    barCount: 1
  }]
};
vm.runInContext(
  'applyExplicitTimelineAccompanimentSegments(shortBarSections, shortBarConfig, patternById);',
  context
);
assert(shortBarSection.trackNotes.Sangban.every(note => note === 'f'), 'Eine ShortBar verschiebt den Begleitstart in den Vortakt.');
assert(sectionAfterShortBar.trackNotes.Sangban.join('') === 'sfsf', 'Der Begleitstart folgt nicht unmittelbar auf eine ShortBar.');

const boundedSections = [createSection({}), createSection({}), createSection({})];
const boundedPatternById = {
  'doundoun-bounded-pattern': {
    id: 'doundoun-bounded-pattern',
    label: 'Begleitung',
    testNotes: ['d', 'f', 'o', 'i'],
    testInStep: 3,
    testOutStep: 2,
    testOutBarEndStep: 4
  }
};
context.boundedSections = boundedSections;
context.boundedConfig = {
  AccompanimentSegments: [{
    patternId: 'doundoun-bounded-pattern',
    targetInstrument: 'Doundoun',
    startBar: 2,
    barCount: 2
  }]
};
context.boundedPatternById = boundedPatternById;
vm.runInContext(
  'applyExplicitTimelineAccompanimentSegments(boundedSections, boundedConfig, boundedPatternById);',
  context
);
assert(
  boundedSections[0].trackNotes.Doundoun.join('') === 'fffi',
  'Das In der Begleitspur wird nicht als Auftakt vor dem Segmentstart eingesetzt.'
);
assert(
  boundedSections.some((section, sectionIndex) =>
    Array.isArray(section.timelinePickupLeads) &&
      section.timelinePickupLeads.some(pickupData =>
        sectionIndex * 4 + pickupData.targetOffset === 4 && pickupData.leadSteps === 1
      )
  ),
  'Der Auftakt der Begleitspur wird nicht für einen Start am Segmentanfang vorgemerkt.'
);
assert(
  boundedSections[1].trackNotes.Doundoun.join('') === 'dfoi',
  'Die Begleitspur wird zwischen In und Out nicht vollständig geloopt.'
);
assert(
  boundedSections[2].trackNotes.Doundoun.join('') === 'dfof',
  'Das Out beendet nicht den letzten Durchlauf der Begleitspur.'
);

console.log('Timeline-Begleitspuren: Start, Länge, In/Out und abschnittsübergreifende Wiedergabe geprüft.');
