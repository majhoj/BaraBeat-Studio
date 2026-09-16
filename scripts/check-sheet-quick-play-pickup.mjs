import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const editorSource = fs.readFileSync(new URL('../index.php', import.meta.url), 'utf8');
const playerSource = fs.readFileSync(new URL('../Audio/player.html', import.meta.url), 'utf8');
function extractFunction(source, name) {
  const start = source.indexOf('function ' + name + '(');
  const end = source.indexOf('\nfunction ', start + 1);
  assert(start >= 0 && end > start, name + ' missing');
  return source.slice(start, end).split('\nrecalculateOrderedSectionTiming();')[0];
}

const context = vm.createContext({
  getReadRhythmConfig: () => ({ stepsPerBar: 24 }),
  getTimelineStepsPerBar: () => 24,
  uiText: key => key,
  isPracticeMode: true,
  isSheetQuickPlayMode: true,
  isTimelineMode: true,
  timelineLoopCount: 'loop',
  timelineLoop: true,
  currentBaseTempo: 80,
  initialTempo: 80,
  practiceTimerFinalLoopStartStep: null,
  globalPlaybackStep: 0
});
vm.runInContext(editorSource.slice(
  editorSource.indexOf('function buildSheetQuickPlayRepeatRanges('),
  editorSource.indexOf('function createSheetPatternMoveOverlayButton(')
), context);
vm.runInContext(extractFunction(editorSource, 'getSheetQuickPlayPositionKey'), context);
vm.runInContext(extractFunction(editorSource, 'mapLabelForPlayer'), context);
vm.runInContext(extractFunction(editorSource, 'getPlayerLabelInfo'), context);
context.trackInstrumentNames = Object.keys(context.createSheetQuickPlayTrackMap());
for (const name of [
  'createEmptyTrackNoteMap', 'createEmptyTrackHandModeMap', 'createEmptyTrackStepMap',
  'createEmptyTrackTextMap', 'createOrderedSection', 'normalizeSectionRepeatCount',
  'normalizeSectionTempo', 'normalizeSectionOutStep', 'getSectionLength',
  'padNotesToLength', 'loopNotesToLength', 'greatestCommonDivisor', 'leastCommonMultiple',
  'normalizeSectionTrackLoops', 'finalizeSectionLengths', 'applyOrderedSectionTempoTargets',
  'buildConfiguredPracticeSections', 'isSectionLoopEligible', 'recalculateOrderedSectionTiming',
  'getPlaybackSectionContext', 'isFinalTimelinePlaybackContext', 'getTrackPlaybackAtStep'
]) {
  vm.runInContext(extractFunction(playerSource, name), context);
}

function bar(sourceBarIndex, values, controls = [], repeat = { start: [], end: [] }) {
  const notes = new Array(24).fill('f');
  for (const [step, note] of Object.entries(values)) notes[Number(step)] = note;
  return { sourceBarIndex, notes, controls, repeat };
}
function pattern(id, target, bars, labelType = 'Begleitung') {
  const labelInfo = context.getPlayerLabelInfo(labelType);
  return { id, sourceKey: id, defaultTargets: [target], labelType: labelInfo.type,
    labelName: labelInfo.raw, bars };
}

// Djia Billy Konate 02: Sangban / Begleitpattern 2, source bars 38 and 39.
const sangban = pattern('sangban-2', 'Sangban', [
  bar(38, { 0: 'Bell_Open', 4: 'Bell_Open', 8: 'Bell_Open', 12: 'Bell',
    14: 'Bell_Open', 18: 'Bell_Open', 22: 'Bell' }),
  bar(39, { 0: 'Bell', 4: 'Bell', 8: 'Bell_Muffled', 12: 'Bell',
    14: 'Bell_Muffled', 18: 'Bell', 22: 'Bell' }, [
    { type: 'out', stepIndex: 18 }, { type: 'in', stepIndex: 22 },
    { type: 'in', stepIndex: 22 }
  ])
]);
const original = JSON.stringify(sangban);
const fullSangbanNotes = sangban.bars.flatMap(item => item.notes);
const djembe = pattern('djembe', 'Djembe_1', [bar(3, { 0: 'bass', 8: 'tone', 16: 'slap' })]);
const call = pattern('call', 'Sangban', [bar(2, { 0: 'Open', 6: 'Open' })], 'Call');

function prepare(patterns) {
  const prepared = patterns.map(context.buildSheetQuickPlayPreparedPattern);
  return context.buildSheetQuickPlayConfiguredSections(prepared);
}
function loadPlayer(sections) {
  context.orderedSections = context.buildConfiguredPracticeSections({ PracticeSections: sections });
  context.recalculateOrderedSectionTiming();
}
function notesAt(target, start, length) {
  return Array.from({ length }, (_, step) =>
    context.getTrackPlaybackAtStep(target, { begleitungNotes: [] }, start + step).note || 'f');
}
function verifyHighlights(sections, start, length, target = 'Sangban', sourcePattern = sangban) {
  for (let step = start; step < start + length; step++) {
    const playback = context.getPlaybackSectionContext(step);
    const source = sections.find(section => section.runtimeKey === playback.section.runtimeKey);
    const isFinalRepeat = playback.loopCycleIndex === playback.section.repeatCount - 1;
    const refs = (source.highlightSteps[playback.localStep] || [])
      .filter(ref => !(isFinalRepeat && ref.mutedOnFinalRepeat));
    const note = notesAt(target, step, 1)[0];
    const sourceRefs = refs.filter(ref => sourcePattern.bars.some(item => item.sourceBarIndex === ref.sourceBarIndex));
    assert.equal(sourceRefs.length, note === 'f' ? 0 : 1,
      'Each audible ' + target + ' note must highlight exactly its source position');
    for (const ref of sourceRefs) {
      assert.equal(sourcePattern.bars.find(item => item.sourceBarIndex === ref.sourceBarIndex)
        .notes[ref.sourceStepIndex], note);
    }
  }
}

const standalone = prepare([sangban]);
assert.equal(standalone.length, 2, 'A cyclic IN needs one pickup followed by the complete pattern');
assert(standalone[0].isLeadIn);
assert.equal(standalone[1].fixedLength, 48, 'The loop must keep both bars');
loadPlayer(standalone);
assert.deepEqual(notesAt('Sangban', 0, 2), ['Bell', 'f'], 'IN plays once before the first downbeat');
for (let loop = 0; loop < 4; loop++) {
  assert.deepEqual(notesAt('Sangban', 2 + loop * 48, 48), fullSangbanNotes,
    'The complete two-bar accompaniment must loop without an OUT or an extra pickup');
}
verifyHighlights(standalone, 0, 2 + 4 * 48);

for (const selection of [[sangban, djembe], [djembe, sangban]]) {
  const sections = prepare(selection);
  loadPlayer(sections);
  assert.deepEqual(notesAt('Djembe_1', 0, 2), ['f', 'f'], 'Parallel accompaniment waits for the downbeat');
  assert.deepEqual(notesAt('Sangban', 2, 96), fullSangbanNotes.concat(fullSangbanNotes));
  assert.deepEqual(notesAt('Djembe_1', 2, 96), Array.from({ length: 4 }, () => djembe.bars[0].notes).flat());
  verifyHighlights(sections, 0, 98);
}

const afterCall = prepare([call, sangban]);
assert.equal(afterCall.length, 2, 'Pickup after a call must merge into its last bar');
assert.equal(afterCall[0].trackNotes.Sangban[22], 'Bell');
assert.equal(afterCall[1].fixedLength, 48);
assert.equal(afterCall[1].finalRepeatOutSteps.Sangban, 42, 'OUT must still apply before a following pattern');
loadPlayer(afterCall);
assert.deepEqual(notesAt('Sangban', 24, 43), fullSangbanNotes.slice(0, 43));
assert.deepEqual(notesAt('Sangban', 24 + 43, 5), new Array(5).fill('f'));

const solo = pattern('solo', 'Djembe_3', [
  bar(5, { 22: 'tone' }, [{ type: 'in', stepIndex: 22 }], { start: [1], end: [2] }),
  bar(6, { 0: 'bass', 8: 'slap' })
], 'Solo');
const soloSections = prepare([solo]);
assert.equal(soloSections.length, 1, 'Existing leading solo pickups keep their established layout');
assert.equal(soloSections[0].trackNotes.Djembe_3[0], 'tone');
assert.equal(soloSections[0].trackNotes.Djembe_3[2], 'f');
assert.equal(soloSections[0].trackNotes.Djembe_3[50], 'bass', 'Internal repeats remain intact');

const parallelPickups = prepare([sangban, solo]);
loadPlayer(parallelPickups);
assert.deepEqual(notesAt('Sangban', 0, 2), ['Bell', 'f']);
assert.deepEqual(notesAt('Djembe_3', 0, 2), ['tone', 'f']);
assert.deepEqual(notesAt('Sangban', 2, 48), fullSangbanNotes);
assert.equal(notesAt('Djembe_3', 2 + 48, 1)[0], 'bass');

const earlyPickup = JSON.parse(JSON.stringify(sangban));
earlyPickup.id = 'early-pickup';
earlyPickup.defaultTargets = ['Kenkeni'];
earlyPickup.bars[1].controls = [{ type: 'out', stepIndex: 18 }, { type: 'in', stepIndex: 20 }];
earlyPickup.bars[1].notes[20] = 'Open';
const staggered = prepare([sangban, earlyPickup]);
loadPlayer(staggered);
assert.deepEqual(notesAt('Kenkeni', 0, 4), ['Open', 'f', 'Bell', 'f']);
assert.deepEqual(notesAt('Sangban', 0, 4), ['f', 'f', 'Bell', 'f'], 'Different pickups share one downbeat');

const repeated = JSON.parse(JSON.stringify(sangban));
repeated.bars[0].repeat.start = [1];
repeated.bars[1].repeat.end = [2];
const repeatedSections = prepare([repeated]);
loadPlayer(repeatedSections);
assert.equal(repeatedSections[1].fixedLength, 144, 'Internal repeats do not hide the cyclic OUT/IN relationship');
assert.deepEqual(notesAt('Sangban', 2, 144), Array.from({ length: 3 }, () => fullSangbanNotes).flat());
assert.equal(JSON.stringify(sangban), original, 'Playback preparation must not change the source pattern');

// Same score: Djembe 1 / Begleitpattern 1 Variation (bar 5), IN without OUT.
const variation = pattern('variation', 'Djembe_1', [
  bar(5, { 0: 'slap', 4: 'tone', 6: 'slap', 10: 'bass', 12: 'slap',
    14: 'tone', 18: 'slap', 22: 'bass' }, [{ type: 'in', stepIndex: 22 }])
], 'Begleitpattern 1 Variation');
const variationSections = prepare([variation]);
loadPlayer(variationSections);
assert.deepEqual(notesAt('Djembe_1', 0, 2), ['bass', 'f']);
assert.deepEqual(notesAt('Djembe_1', 2, 72), Array.from({ length: 3 }, () => variation.bars[0].notes).flat(),
  'An accompaniment IN without OUT must not reduce the loop to its final note');
verifyHighlights(variationSections, 0, 74, 'Djembe_1', variation);
for (const selection of [[variation, sangban], [sangban, variation]]) {
  const sections = prepare(selection);
  loadPlayer(sections);
  assert.deepEqual(notesAt('Djembe_1', 2, 96), Array.from({ length: 4 }, () => variation.bars[0].notes).flat());
  assert.deepEqual(notesAt('Sangban', 2, 96), fullSangbanNotes.concat(fullSangbanNotes));
  verifyHighlights(sections, 0, 98, 'Djembe_1', variation);
}
const djembeCall = { ...call, defaultTargets: ['Djembe_1'] };
const variationAfterCall = prepare([djembeCall, variation]);
loadPlayer(variationAfterCall);
assert.equal(notesAt('Djembe_1', 22, 1)[0], 'bass', 'IN still merges into the preceding call');
assert.deepEqual(notesAt('Djembe_1', 24, 24), variation.bars[0].notes);

// Djaa Djembe: the stored label is "Begkeitpattern 1", with IN at the end of bar 3.
const djaaBars = [bar(3, { 0: 'slap', 4: 'tone', 6: 'slap', 10: 'bass',
  12: 'slap', 14: 'tone', 16: 'tone', 18: 'slap', 22: 'bass' }, [{ type: 'in', stepIndex: 22 }])];
for (const label of ['Begkeitpattern 1', 'Bekleitpattern 1', 'Eigener Rhythmus', 'Begleitpattern 1']) {
  const customPattern = pattern('djaa-custom', 'Djembe_1', djaaBars, label);
  const originalCustomPattern = JSON.stringify(customPattern);
  const customSections = prepare([customPattern]);
  loadPlayer(customSections);
  assert.deepEqual(notesAt('Djembe_1', 0, 2), ['bass', 'f']);
  assert.deepEqual(notesAt('Djembe_1', 2, 72), Array.from({ length: 3 }, () => djaaBars[0].notes).flat(),
    'Terminal IN must retain the complete loop for the name: ' + label);
  verifyHighlights(customSections, 0, 74, 'Djembe_1', customPattern);
  assert.equal(customSections[1].labelName, label, 'Quick play must not rename the pattern');
  for (const selection of [[customPattern, sangban], [sangban, customPattern]]) {
    const sections = prepare(selection);
    loadPlayer(sections);
    assert.deepEqual(notesAt('Djembe_1', 2, 96), Array.from({ length: 4 }, () => djaaBars[0].notes).flat(),
      'A freely named cycle must repeat alongside a longer accompaniment');
    verifyHighlights(sections, 0, 98, 'Djembe_1', customPattern);
  }
  loadPlayer(prepare([djembeCall, customPattern]));
  assert.equal(notesAt('Djembe_1', 22, 1)[0], 'bass');
  assert.deepEqual(notesAt('Djembe_1', 24, 24), djaaBars[0].notes);
  assert.equal(JSON.stringify(customPattern), originalCustomPattern);
}
const repeatedCustomBars = JSON.parse(JSON.stringify(djaaBars));
repeatedCustomBars[0].repeat = { start: [1], end: [2] };
const repeatedCustom = prepare([pattern('djaa-repeated', 'Djembe_1', repeatedCustomBars, 'Eigener Rhythmus')]);
loadPlayer(repeatedCustom);
assert.equal(repeatedCustom[1].fixedLength, 72, 'Detect terminal IN before expanding written repeats');
assert.deepEqual(notesAt('Djembe_1', 2, 144), Array.from({ length: 6 }, () => djaaBars[0].notes).flat());
const multiBarCustom = pattern('custom-two-bars', 'Djembe_1', [
  bar(12, { 0: 'bass', 8: 'tone' }), ...djaaBars
], 'Eigener Rhythmus');
const multiBarSections = prepare([multiBarCustom]);
loadPlayer(multiBarSections);
const multiBarNotes = multiBarCustom.bars.flatMap(item => item.notes);
assert.equal(multiBarSections[1].fixedLength, 48, 'A terminal IN must preserve earlier bars as well');
assert.deepEqual(notesAt('Djembe_1', 2, 96), multiBarNotes.concat(multiBarNotes));
verifyHighlights(multiBarSections, 0, 98, 'Djembe_1', multiBarCustom);
const customLeadingPickup = prepare([pattern('custom-leading', 'Djembe_1', [
  bar(10, { 22: 'slap' }, [{ type: 'in', stepIndex: 22 }]),
  bar(11, { 0: 'bass', 8: 'tone' })
], 'Eigener Rhythmus')]);
assert.equal(customLeadingPickup.length, 1, 'A genuine leading pickup keeps its existing behavior');
assert.deepEqual(Array.from(customLeadingPickup[0].trackNotes.Djembe_1.slice(0, 3)), ['slap', 'f', 'bass']);

// Djembe 1 / Solo 1 (bar 7): the final slap follows the OUT on the bass.
const soloOne = pattern('solo-1', 'Djembe_1', [
  bar(7, { 0: 'bass', 2: 'slap', 4: 'tone', 8: 'slap', 14: 'slap',
    18: 'bass', 20: 'slap' }, [{ type: 'out', stepIndex: 18 }])
], 'Solo 1');
assert.equal(soloOne.labelType, 'Solo 1', 'The score reader preserves numbered solo names');
for (const selection of [[soloOne], [soloOne, { ...djembe, defaultTargets: ['Djembe_2'] }]]) {
  const sections = prepare(selection);
  loadPlayer(sections);
  assert.deepEqual(notesAt('Djembe_1', 0, 72), Array.from({ length: 3 }, () => soloOne.bars[0].notes).flat(),
    'The final slap must remain audible when Solo 1 loops without a pattern change');
  verifyHighlights(sections, 0, 72, 'Djembe_1', soloOne);
}
for (const label of ['Solo', 'Solo 2', 'Solo 2a', 'solo 3', 'Solo Eigener Name']) {
  const namedSolo = pattern(label, 'Djembe_1', soloOne.bars, label);
  loadPlayer(prepare([namedSolo]));
  assert.deepEqual(notesAt('Djembe_1', 0, 48), soloOne.bars[0].notes.concat(soloOne.bars[0].notes),
    label + ' must retain all notes during its continuous loop');
}
const soloBeforeCall = prepare([soloOne, djembeCall]);
loadPlayer(soloBeforeCall);
assert.deepEqual(notesAt('Djembe_1', 19, 5), new Array(5).fill('f'), 'OUT still applies when Solo 1 hands over to a call');
assert.equal(notesAt('Djembe_1', 24, 1)[0], 'Open', 'The next call starts on the next full bar');
verifyHighlights(soloBeforeCall, 0, 24, 'Djembe_1', soloOne);

const echauffement = pattern('echauffement', 'Djembe_1', [
  bar(8, { 0: 'bass', 18: 'tone', 20: 'slap' }, [{ type: 'out', stepIndex: 18 }], { start: [1], end: [1] })
], 'Echauffement');
const echauffementSections = prepare([echauffement]);
loadPlayer(echauffementSections);
assert.equal(notesAt('Djembe_1', 20, 1)[0], 'slap');
assert.equal(notesAt('Djembe_1', 24 + 20, 1)[0], 'f', 'Echauffement keeps its OUT in the final written repeat');
verifyHighlights(echauffementSections, 0, 48, 'Djembe_1', echauffement);

for (const name of ['getStepsPerBeatForRhythm', 'getBaseStepDuration', 'getPlaybackRhythmStep', 'scheduleShekereBeatIfNeeded', 'getStepInterval']) {
  vm.runInContext(extractFunction(playerSource, name), context);
}
context.getEffectiveTempoForStep = () => 80;
context.rhythmType = 'tenaer';
context.shekereBeatEnabled = true;
context.practiceCountInBuffer = {};
context.shekereBeatAnticipationSeconds = 0;
context.getTenaerSwingStepOffsets = () => [0, 0.05, 0.15, 0.3, 0.5, 0.7, 1];
loadPlayer(variationSections);
const beatTimes = [];
context.scheduleShekereHit = time => beatTimes.push(time);
for (let step = 0; step < 14; step++) context.scheduleShekereBeatIfNeeded(step, step);
assert.deepEqual(beatTimes, [2, 8], 'Shekere starts on the downbeat after the pickup, not on the IN');
assert(Math.abs(context.getStepInterval(0) - 0.2 * 0.75) < 1e-9, 'Pickup uses the end of the swung beat');
assert(Math.abs(context.getStepInterval(2) - 0.05 * 0.75) < 1e-9, 'Pattern downbeat restarts the swing profile');
loadPlayer(prepare([soloOne]));
assert(Math.abs(context.getStepInterval(0) - 0.05 * 0.75) < 1e-9, 'Playback without a pickup keeps its swing phase');
for (const rhythmType of ['binaer', 'neunaer']) {
  context.rhythmType = rhythmType;
  const offsets = rhythmType === 'binaer'
    ? [0, 0.04, 0.1, 0.2, 0.4, 0.5, 0.65, 0.8, 1]
    : [0, 0.05, 0.15, 0.3, 0.5, 0.7, 1];
  context.getBinaerSwingStepOffsets = () => offsets;
  context.getNeunaerSwingStepOffsets = () => offsets;
  loadPlayer(variationSections);
  const pickupPhase = offsets.length - 3;
  assert(Math.abs(context.getStepInterval(0) - (offsets[pickupPhase + 1] - offsets[pickupPhase]) * 0.75) < 1e-9,
    rhythmType + ' pickup uses the correct swing phase');
  assert(Math.abs(context.getStepInterval(2) - offsets[1] * 0.75) < 1e-9);
}
context.isSheetQuickPlayMode = false;
assert.equal(context.getPlaybackRhythmStep(0), 0, 'Practice and arrangement rhythm grids remain unchanged');
console.log('Quick play: freely named cyclic pickups, accompaniment/solo loops, pattern changes, repeats, highlights and beat/swing alignment checked.');
