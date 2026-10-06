import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const player = source('Audio/player.html');
const editor = source('index.php');
const practice = source('JS/practice.js');
const context = vm.createContext({
  isPracticeMode: false, isSheetQuickPlayMode: false, MAX_EXPANDED_BARS: 2000,
  rhythm: 'tenaer', rhythmType: 'tenaer', uiText: key => key,
  trackInstrumentNames: ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  timelineState: { tempo: 100, sourcePatterns: [] },
  practiceState: { repeatCount: 1, timerMinutes: 0, accompanimentStart: 'immediate' }
});
installTiming(context);
installRepeatPickup(context);
vm.runInContext(player.slice(player.indexOf('function sanitizeRepeatRanges('),
  player.indexOf('const repeatRanges = sanitizeRepeatRanges(')), context);
vm.runInContext(player.slice(player.indexOf('function getFeelOffsetSeconds('),
  player.indexOf('const flatBars = isTimelineMode')), context);
loadFunctions(context, player, ['getSectionLength']);
loadFunctions(context, editor, ['getReadRhythmConfig', 'getSheetQuickPlayPositionKey']);
vm.runInContext(editor.slice(editor.indexOf('function buildSheetQuickPlayRepeatRanges('),
  editor.indexOf('function createSheetPatternMoveOverlayButton(')), context);
context.practiceTrackInstrumentNames = context.trackInstrumentNames;
vm.runInContext(practice.slice(practice.indexOf('function createEmptyPracticeTrackNotes('),
  practice.indexOf('function notifyPracticeHandModeChanged(')), context);
loadFunctions(context, practice, ['normalizePracticeCount', 'normalizePracticeTempo',
  'buildPracticeBlocksFromEntries', 'shouldPausePracticeAccompanimentForPattern']);

function bar(sourceBarIndex, hits, controls = [], repeats = 0) {
  const notes = new Array(24).fill('f');
  for (const [step, note] of Object.entries(hits)) notes[step] = note;
  return { sourceBarIndex, notes, controls,
    repeat: repeats ? { start: ['continue'], end: [repeats] } : { start: [], end: [] } };
}

// Server score Soli nach Okas, 2026-09-24: six written Sangban bars, ten played bars.
const base = { 0: 'Bell_Open', 4: 'Bell', 8: 'Bell_Muffled', 12: 'Bell_Muffled',
  16: 'Bell', 18: 'Bell_Open', 22: 'Bell' };
const variation = { 0: 'Bell_Open', 4: 'Bell_Open', 8: 'Bell_Open', 12: 'Bell_Open',
  16: 'Bell', 18: 'Bell_Open', 22: 'Bell' };
const finalBar = { 2: 'Bell_Open', 4: 'Bell_Open', 8: 'Bell_Open', 12: 'Bell_Open',
  16: 'Bell', 18: 'Bell_Open', 22: 'Bell_Open' };
const sangban = { id: 'sangban', sourceKey: 'sangban', instrument: 'Sangban',
  label: 'Begleitung', labelType: 'Begleitung', labelName: 'Begleitung', defaultTargets: ['Sangban'], bars: [
    bar(3, base, [{ type: 'in', stepIndex: 0 }], 2), bar(4, variation),
    bar(5, base, [], 1), bar(6, variation), bar(7, base),
    bar(8, finalBar, [{ type: 'out', stepIndex: 18 }, { type: 'in', stepIndex: 22 }], 1)
  ] };
const original = JSON.stringify(sangban);
const order = [0, 0, 0, 1, 2, 2, 3, 4, 5, 5];
const expected = order.flatMap(index => sangban.bars[index].notes);
expected[7 * 24 + 22] = 'Bell_Open';

assert.deepEqual(Array.from(context.flattenPatternNotes(sangban)), expected,
  'Arrangement must play the final repeat IN at the end of its preceding bar');
assert.deepEqual(Array.from(context.flattenPracticePatternNotes(sangban)), expected,
  'Practice must use the same internal pickup without adding or skipping a bar');
const prepared = context.buildSheetQuickPlayPreparedPattern(sangban, 0);
const quickExpected = expected.map((note, step) => step > 234 ? 'f' : note);
assert.deepEqual(Array.from(context.getSheetQuickPlayPatternNotes(prepared)), quickExpected,
  'Quick Play keeps the internal IN, but closes the last written repeat at OUT');
const refs = context.getSheetQuickPlayPatternHighlightRefs(prepared);
assert.equal(refs[7 * 24 + 22].sourceBarIndex, 8, 'Highlight the IN source, not the replaced bell in bar 7');
assert.equal(refs[7 * 24 + 22].sourceStepIndex, 22);
assert.equal(refs[7 * 24 + 18].sourceBarIndex, 7, 'Unchanged notes keep their own highlight');
const sections = context.buildSheetQuickPlayConfiguredSections([prepared]);
assert.equal(sections.length, 1);
assert.equal(sections[0].fixedLength, 240);
assert.equal(sections[0].trackNotes.Sangban[190], 'Bell_Open');
assert.equal(sections[0].trackNotes.Sangban[214], 'Bell_Open', 'Earlier final-bar pass retains the IN');
assert.equal(sections[0].trackNotes.Sangban[234], 'Bell_Open', 'The OUT note itself still sounds');
assert.equal(sections[0].trackNotes.Sangban[238], 'f', 'No new pickup follows the final OUT');
assert.equal((sections[0].highlightSteps[238] || []).length, 0, 'The suppressed IN must not turn red');
assert.equal(context.getPatternOutStep(sangban), 234, 'Final OUT remains on the last written repeat');
assert.equal(context.getPracticePatternOutStep(sangban), 234);
assert.equal(JSON.stringify(sangban), original, 'The saved notation must not change');

// Updated Okas score: Variation 2 has only an IN, no repeat or OUT, and another bar follows.
const unrepeatedIn = { ...sangban, bars: [
  ...sangban.bars.slice(0, 5),
  bar(8, finalBar, [{ type: 'in', stepIndex: 22 }]),
  bar(9, { ...finalBar, 22: 'Bell' })
] };
const unrepeatedOriginal = JSON.stringify(unrepeatedIn);
const unrepeatedOrder = [0, 0, 0, 1, 2, 2, 3, 4, 5, 6];
const unrepeatedExpected = unrepeatedOrder.flatMap(index => unrepeatedIn.bars[index].notes);
unrepeatedExpected[190] = 'Bell_Open';
for (const flatten of [p => context.getSheetQuickPlayPatternNotes(context.buildSheetQuickPlayPreparedPattern(p, 0)),
  p => context.flattenPracticePatternNotes(p), p => context.flattenPatternNotes(p)]) {
  assert.deepEqual(Array.from(flatten(unrepeatedIn)), unrepeatedExpected,
    'An internal IN without a repeat must sound in the preceding bar without changing the rest');
  const noRepeats = { ...unrepeatedIn, bars: [
    bar(1, base, [{ type: 'in', stepIndex: 0 }]), bar(2, finalBar, [{ type: 'in', stepIndex: 22 }])
  ] };
  assert.equal(flatten(noRepeats)[22], 'Bell_Open', 'Internal IN works even without any written repeats');
  const repeatedHostOnly = { ...noRepeats, bars: [sangban.bars[0], noRepeats.bars[1]] };
  const notes = flatten(repeatedHostOnly);
  assert.deepEqual([notes[22], notes[46], notes[70]], ['Bell', 'Bell', 'Bell_Open'],
    'Only the last occurrence of the preceding bar receives the unrepeated IN');
  const globalPickup = { ...noRepeats, bars: [bar(1, base), noRepeats.bars[1]] };
  assert.deepEqual(Array.from(flatten(globalPickup)), globalPickup.bars.flatMap(b => b.notes),
    'The first IN of a pattern remains a whole-pattern pickup, not an internal one');
  const repeatGroup = { ...noRepeats, bars: [noRepeats.bars[0],
    { ...bar(2, base), repeat: { start: ['continue'], end: [] } },
    { ...noRepeats.bars[1], repeat: { start: [], end: [1] } }
  ] };
  const groupNotes = flatten(repeatGroup);
  assert.deepEqual([groupNotes[22], groupNotes[46], groupNotes[94]], ['Bell_Open', 'Bell', 'Bell'],
    'A repeat group owns its cyclic IN; do not also inject it into bars inside the group');
}
const unrepeatedPrepared = context.buildSheetQuickPlayPreparedPattern(unrepeatedIn, 0);
const unrepeatedRefs = context.getSheetQuickPlayPatternHighlightRefs(unrepeatedPrepared);
assert.equal(unrepeatedRefs[190].sourceBarIndex, 8);
assert.equal(unrepeatedRefs[190].sourceStepIndex, 22);
assert.equal(unrepeatedRefs[186].sourceBarIndex, 7);
assert.equal(unrepeatedRefs[214].sourceBarIndex, 8);
assert.equal(JSON.stringify(unrepeatedIn), unrepeatedOriginal);

const call = { id: 'call', label: 'Call', labelType: 'Call', defaultTargets: ['Djembe_1'],
  bars: [bar(1, { 0: 'tone' })] };
const arrangement = context.buildTimelineSections({
  PatternLibrary: [call, sangban],
  TimelineEntries: [
    { patternId: 'call', blockId: 'call', targetInstruments: ['Djembe_1'] },
    { patternId: 'sangban', blockId: 'sangban', targetInstruments: ['Sangban'] }
  ]
});
assert.equal(arrangement.length, 2);
assert.equal(arrangement[1].playbackLength, 240);
assert.equal(arrangement[1].trackNotes.Sangban[190], 'Bell_Open');
assert.equal(arrangement[1].trackNotes.Sangban[214], 'Bell_Open', 'First final-bar pass keeps its IN');
assert.equal(arrangement[1].trackNotes.Sangban[238], 'f', 'Last final-bar pass respects OUT');

context.findPatternById = id => id === sangban.id ? sangban : null;
const practiceSections = context.buildPracticeSectionsFromEntries([{
  patternId: sangban.id, patternSourceKey: sangban.sourceKey, targetInstruments: ['Sangban'],
  blockId: 'practice', repeatCount: 1, patternRepeatCount: 1, isPracticeTarget: true
}]);
assert.equal(practiceSections.length, 1);
assert.equal(practiceSections[0].trackNotes.Sangban.length, 240);
assert.equal(practiceSections[0].trackNotes.Sangban[190], 'Bell_Open');
assert.equal(practiceSections[0].finalRepeatOutSteps.Sangban, null,
  'A standalone practice accompaniment retains the existing uninterrupted-loop policy');
context.practiceRepeatCountMax = 999;
context.getPracticeScrollerVisualLoopCopies = () => 4;
context.getPracticeScrollerSectionVisualRepeatCopies = () => 4;
context.getPracticeScrollerOuterVisualLoopCopies = (length, count) => count;
loadFunctions(context, practice, ['flattenPracticeScrollerSections']);
const movingNotes = context.flattenPracticeScrollerSections(practiceSections);
assert.equal(movingNotes.trackNotes.Sangban[190], 'Bell_Open', 'Moving notes include the same pickup');
assert.equal(movingNotes.trackNotes.Sangban[238], 'Bell_Open', 'Moving notes preserve the accompaniment loop');

// The host can itself repeat: change only its last occurrence before the new segment.
const repeatedHost = { ...sangban, bars: [bar(1, base, [{ type: 'in', stepIndex: 0 }], 2), sangban.bars[5]] };
for (const expand of [p => context.expandPatternBars(p), p => context.expandPracticePatternBars(p),
  p => context.buildSheetQuickPlayPreparedPattern(p, 0).bars]) {
  const bars = expand(repeatedHost);
  assert.equal(bars.length, 5);
  assert.deepEqual(Array.from(bars.slice(0, 3), b => b.notes[22]), ['Bell', 'Bell', 'Bell_Open']);
  assert.equal(repeatedHost.bars[0].notes[22], 'Bell');
  const noPickup = JSON.parse(JSON.stringify(repeatedHost));
  noPickup.bars[1].controls = [{ type: 'out', stepIndex: 18 }];
  assert.deepEqual(Array.from(expand(noPickup).slice(0, 3), b => b.notes[22]), ['Bell', 'Bell', 'Bell']);
}

// Exercise the actual player across outer loop boundaries, not only prepared note arrays.
Object.assign(context, {
  isPracticeMode: true, isSheetQuickPlayMode: true, isTimelineMode: true,
  timelineLoopCount: 'loop', timelineLoop: true, currentBaseTempo: 100, initialTempo: 100,
  practiceTimerFinalLoopStartStep: null, globalPlaybackStep: 0
});
loadFunctions(context, player, ['recalculateOrderedSectionTiming', 'getPlaybackSectionContext',
  'isFinalTimelinePlaybackContext', 'getTrackPlaybackAtStep', 'getFinalOverlapTailStartStep']);
function quickSections(patterns) {
  return context.buildSheetQuickPlayConfiguredSections(patterns.map(context.buildSheetQuickPlayPreparedPattern));
}
function loadPlayer(configured) {
  context.orderedSections = context.buildConfiguredPracticeSections({ PracticeSections: configured });
  context.recalculateOrderedSectionTiming();
}
function noteAt(step, instrument = 'Sangban') {
  return context.getTrackPlaybackAtStep(instrument, { begleitungNotes: [] }, step).note || 'f';
}
function checkCycle(start) {
  assert.equal(noteAt(start + 190), 'Bell_Open');
  assert.equal(noteAt(start + 214), 'Bell_Open');
  assert.equal(noteAt(start + 234), 'Bell_Open');
  for (let step = 235; step < 240; step++) assert.equal(noteAt(start + step), 'f');
}
loadPlayer(sections);
assert.equal(context.oneShotLength, 240, 'No extra bar or shortened final bar');
for (let cycle = 0; cycle < 4; cycle++) {
  checkCycle(cycle * 240);
  assert.equal(noteAt((cycle + 1) * 240), 'Bell_Open', 'Restart exactly on the next downbeat');
}

loadPlayer(quickSections([unrepeatedIn]));
assert.equal(context.oneShotLength, 240);
for (let cycle = 0; cycle < 4; cycle++) {
  for (let step = 0; step < 240; step++) {
    assert.equal(noteAt(cycle * 240 + step), unrepeatedExpected[step],
      'The unrepeated internal pickup and all other notes remain correct across outer loops');
  }
}

const longCompanion = { id: 'parallel', labelType: 'Begleitung', defaultTargets: ['Kenkeni'],
  bars: Array.from({ length: 21 }, (_, index) => bar(20 + index, { 0: 'Open', 22: 'Bell' })) };
for (const selection of [[sangban, longCompanion], [longCompanion, sangban]]) {
  loadPlayer(quickSections(selection));
  assert.equal(context.oneShotLength, 21 * 24);
  checkCycle(0);
  checkCycle(240);
  assert.equal(noteAt(240), 'Bell_Open', 'Sangban continues after its own closing OUT');
  assert.equal(noteAt(480), 'Bell_Open');
  assert.equal(noteAt(238, 'Kenkeni'), 'Bell', 'Parallel accompaniment is not muted by Sangban OUT');
  assert.equal(noteAt(478, 'Kenkeni'), 'Bell');
}

const withEmptyBar = { ...sangban, bars: [...sangban.bars, bar(9, {})] };
loadPlayer(quickSections([withEmptyBar]));
checkCycle(0);
assert.equal(context.oneShotLength, 264, 'An explicitly written empty bar remains intact');
assert.equal(noteAt(238), 'f');
assert.equal(noteAt(240), 'f');
assert.equal(noteAt(264), 'Bell_Open');

// Existing continuous accompaniment loops must keep ignoring OUT.
const ordinary = { ...sangban, bars: [sangban.bars[0], { ...sangban.bars[5], repeat: { start: [], end: [] } }] };
const wholePatternRepeat = { ...sangban, bars: [sangban.bars[5]] };
const notClosing = { ...sangban, bars: [...sangban.bars, bar(9, { 0: 'Open' })] };
const unboundedClosing = JSON.parse(JSON.stringify(sangban));
unboundedClosing.bars[5].repeat.end = ['loop'];
for (const unchanged of [ordinary, wholePatternRepeat, notClosing, unboundedClosing,
  { ...sangban, label: 'Solo', labelType: 'Solo' }]) {
  const p = context.buildSheetQuickPlayPreparedPattern(unchanged, 0);
  const notes = context.getSheetQuickPlayPatternNotes(p);
  const out = context.getSheetQuickPlayPatternOutStep(p);
  assert.equal(notes[out + 4], 'Bell_Open', 'Only a finite internal accompaniment closing repeat applies OUT');
}
assert.equal(JSON.stringify(sangban), original);
console.log('Internal IN/OUT: repeated and unrepeated Sangban variation, first-IN and repeat scopes, four outer loops, longer parallel track, highlights, explicit empty bar and unchanged ordinary loops checked.');
