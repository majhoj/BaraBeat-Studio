import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming } from './helpers/music-context.mjs';

const player = source('Audio/player.html');
const editor = source('index.php');
const practice = source('JS/practice.js');
const context = vm.createContext({
  isPracticeMode: false, isSheetQuickPlayMode: false, MAX_EXPANDED_BARS: 2000,
  trackInstrumentNames: ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  steuerung: {}, instrument: '', uiText: key => key,
  timelineState: { tempo: 80, sourcePatterns: [] },
  practiceState: { repeatCount: 1, timerMinutes: 0, accompanimentStart: 'immediate' }
});
installTiming(context);
vm.runInContext(player.slice(player.indexOf('function sanitizeRepeatRanges('),
  player.indexOf('const repeatRanges = sanitizeRepeatRanges(')), context);
vm.runInContext(player.slice(player.indexOf('function getFeelOffsetSeconds('),
  player.indexOf('const flatBars = isTimelineMode')), context);
loadFunctions(context, player, ['getSectionLength', 'getTimelinePlaybackStartStep']);
loadFunctions(context, editor, ['getReadRhythmConfig', 'mapLabelForPlayer', 'getPlayerLabelInfo',
  'getSheetQuickPlayPositionKey', 'buildSheetQuickPlayPayload', 'getMobileSheetStepsPerBar']);
vm.runInContext(editor.slice(editor.indexOf('function buildSheetQuickPlayRepeatRanges('),
  editor.indexOf('function createSheetPatternMoveOverlayButton(')), context);
context.practiceTrackInstrumentNames = context.trackInstrumentNames;
vm.runInContext(practice.slice(practice.indexOf('function createEmptyPracticeTrackNotes('),
  practice.indexOf('function notifyPracticeHandModeChanged(')), context);
loadFunctions(context, practice, ['normalizePracticeCount', 'normalizePracticeTempo',
  'normalizePracticeTempoRampEvery', 'normalizePracticeTempoRampStep', 'getPracticeTempoRampConfig',
  'buildPracticeTempoRampPlan', 'buildPracticeBlocksFromEntries', 'shouldPausePracticeAccompanimentForPattern',
  'buildPracticePlayerPayload', 'getPracticeScrollerStepsPerBar', 'getPracticeScrollerStepsPerBeat']);

for (const [rhythm, size, beat] of [['binaer', 32, 8], ['tenaer', 24, 6], ['neunaer', 18, 6]]) {
  context.rhythm = context.rhythmType = rhythm;
  assert.equal(context.getReadRhythmConfig().stepsPerBar, size);
  assert.equal(context.getReadRhythmConfig().totalStepsPerLine, size * 2);
  assert.equal(context.getMobileSheetStepsPerBar(), size);
  assert.equal(context.getPracticeStepsPerBar(), size);
  assert.equal(context.getPracticeScrollerStepsPerBar(), size);
  assert.equal(context.getPracticeScrollerStepsPerBeat(), beat);
  const bar = (hits, controls = [], repeat = { start: [], end: [] }) => {
    const notes = new Array(size).fill('f');
    for (const [step, note] of Object.entries(hits)) notes[Number(step)] = note;
    return { sourceBarIndex: 1, notes, controls, repeat };
  };
  const pattern = (id, label, bars, target = 'Djembe_1') => ({
    id, sourceKey: id, label, labelType: label, labelName: id, bars, defaultTargets: [target]
  });
  const entry = (p, extra = {}) => ({ patternId: p.id, blockId: p.id, targetInstruments: p.defaultTargets, ...extra });
  const build = (patterns, entries) => context.buildTimelineSections({ PatternLibrary: patterns, TimelineEntries: entries });

  // A written repeat count is ADDITIONAL repeats: two means three complete passes.
  const repeated = pattern('repeat', 'Solo', [bar({ 0: 'bass', [size - 2]: 'slap' }, [], { start: [1], end: [2] })]);
  const expanded = context.expandPatternBars(repeated);
  assert.equal(expanded.length, 3);
  assert.equal(context.expandPracticePatternBars(repeated).length, 3);
  const quick = context.buildSheetQuickPlayPreparedPattern(repeated, 0);
  assert.equal(quick.bars.length * quick.quickPlaySectionRepeatCount, 3);
  const repeatedSections = build([repeated], [entry(repeated)]);
  assert.equal(repeatedSections.reduce((sum, s) => sum + s.playbackLength, 0), size * 3);

  const short = pattern('short', 'Call', [bar({ 0: 'tone', [beat - 1]: 'slap', [beat]: 'bass' },
    [{ type: 'shortbar', stepIndex: beat }])]);
  assert.equal(context.flattenPatternNotes(short).length, beat);
  assert.equal(context.flattenPracticePatternNotes(short).length, beat);
  assert.equal(build([short], [entry(short)])[0].playbackLength, beat);

  const call = pattern('call', 'Call', [bar({ 0: 'tone' })]);
  const intro = pattern('intro', 'Intro', [bar({ 0: 'slap_flam' })]);
  const pickup = pattern('pickup', 'Solo', [bar({ [size - 2]: 'slap' }, [{ type: 'in', stepIndex: size - 2 }]),
    bar({ 0: 'bass', [beat]: 'tone' })]);
  const sections = build([call, intro, pickup], [entry(call), entry(intro), entry(pickup)]);
  assert.equal(sections.length, 3, 'Pickup is absorbed into the preceding Intro, not another bar');
  assert.equal(sections[1].trackNotes.Djembe_1[size - 2], 'slap');
  assert.equal(sections[2].trackNotes.Djembe_1[0], 'bass');
  let start = 0;
  for (const section of sections) { section.startStep = start; start += section.playbackLength; }
  assert.equal(start, size * 3);
  assert.equal(context.getTimelinePlaybackStartStep(sections, 3), size * 2 - 2,
    'Start in the arrangement includes the two-step pickup');
  assert.equal(context.getTimelinePlaybackStartStep(sections, 1), 0);

  const one = pattern('kenkeni', 'Begleitung', [bar({ 0: 'Open' })], 'Kenkeni');
  const two = pattern('sangban', 'Begleitung', [bar({ 0: 'Bell' }), bar({ 0: 'Muffled' })], 'Sangban');
  const parallel = build([one, two], [entry(one, { parallelGroupId: 'parallel', blockId: 'parallel' }),
    entry(two, { parallelGroupId: 'parallel', blockId: 'parallel' })]);
  assert.equal(parallel.length, 1);
  assert.equal(parallel[0].playbackLength, size * 2);
  assert.equal(parallel[0].trackNotes.Kenkeni[0], 'Open');
  assert.equal(parallel[0].trackNotes.Kenkeni[size], 'Open', 'The shorter parallel accompaniment must repeat');
  assert.equal(parallel[0].trackNotes.Sangban[size], 'Muffled');
  const quickParallel = context.buildSheetQuickPlayConfiguredSections([one, two].map(context.buildSheetQuickPlayPreparedPattern));
  assert.equal(quickParallel[0].trackNotes.Kenkeni.length, size * 2);
  assert.equal(quickParallel[0].trackNotes.Kenkeni[size], 'Open');
}

context.getSheetQuickPlaySelectedPatterns = () => [];
assert.equal(context.buildSheetQuickPlayPayload(), null, 'No selection must not play another pattern');
context.timelineState.sourcePatterns = [{ id: 'present' }];
context.practiceState.accompanimentPatternIds = [];
context.practiceState.soloPatternIds = [];
context.practiceText = key => key;
assert.throws(() => context.buildPracticePlayerPayload(), /practice.error.selectPattern/);

Object.assign(context.practiceState, {
  tempoRampEnabled: true, tempoRampStart: 60, tempoRampEnd: 75, tempoRampEvery: 2, tempoRampStep: 10
});
const ramp = context.buildPracticeTempoRampPlan();
assert.deepEqual(Array.from(ramp.rampTempos), [60, 60, 70, 70, 75]);
assert.equal(ramp.targetTempo, 75);
context.practiceState.tempoRampEnabled = false;
assert.equal(context.buildPracticeTempoRampPlan(), null);
console.log('Musical structure: three grids, repeat counts, short bars, Call/Intro + IN, arrangement start, parallel lengths, empty selections and practice tempo ramp checked.');
