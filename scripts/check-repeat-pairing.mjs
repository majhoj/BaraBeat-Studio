import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';
import { legacyShared, legacyPractice, legacyQuickPlay } from './fixtures/repeat-pairing-before-2b.mjs';

const editor = source('index.php');
const practice = source('JS/practice.js');
const player = source('Audio/player.html');
const context = vm.createContext({
  rhythm: 'tenaer', rhythmType: 'tenaer', MAX_EXPANDED_BARS: 2000,
  isPracticeMode: false, isSheetQuickPlayMode: false, uiText: key => key, playerText: key => key,
  trackInstrumentNames: ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  timelineState: { tempo: 100 },
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
loadFunctions(context, source('JS/timeline.js'), ['expandTimelineBarsWithRepeats']);

function freeze(value) {
  if (value && typeof value === 'object') {
    Object.values(value).forEach(freeze);
    Object.freeze(value);
  }
  return value;
}
const range = (startBar, endBar, count) => ({ startBar, endBar, count });
const plainRanges = ranges => Array.from(ranges, ({ startBar, endBar, count }) => ({ startBar, endBar, count }));
const plain = value => JSON.parse(JSON.stringify(value));
const bar = (id, start = [], end = [], controls = [], hits = { 0: 'tone' }) => ({
  id, sourceBarIndex: id.charCodeAt(0) - 64,
  notes: Array.from({ length: 24 }, (_, step) => hits[step] || 'f'),
  repeat: { start, end }, controls
});
const currentReaders = {
  quick: context.buildSheetQuickPlayRepeatRanges,
  practice: context.buildPracticeRepeatRangesFromBars,
  shared: context.BaraBeatRepeatPickup.buildRepeatRanges
};
const oldReaders = {
  quick: legacyQuickPlay.buildSheetQuickPlayRepeatRanges,
  practice: legacyPractice.buildPracticeRepeatRangesFromBars,
  shared: legacyShared.buildRepeatRanges
};

function checkRanges(label, input, expected, orders) {
  const before = structuredClone(input);
  freeze(input);
  const results = {};
  for (const name of Object.keys(currentReaders)) {
    results[name] = currentReaders[name](input);
    assert.deepEqual(plainRanges(results[name]), expected[name], label + ': ' + name + ' fixed ranges');
    assert.deepEqual(plainRanges(results[name]), plainRanges(oldReaders[name](input)), label + ': ' + name + ' old/new');
  }
  if (orders) {
    for (const [name, expand, ranges] of [
      ['quick', context.expandSheetQuickPlayBars, results.quick],
      ['practice', context.expandPracticeBarsWithRepeats, results.practice],
      ['player', context.expandBarsWithRepeats, results.shared],
      ['timeline', context.expandTimelineBarsWithRepeats, results.shared]
    ]) {
      const expanded = expand(input, ranges, 1, input.length, false);
      assert.equal(Array.from(expanded, item => item?.id || '_').join(''), orders[name], label + ': ' + name + ' order');
    }
  }
  assert.deepEqual(input, before, label + ': immutable source');
}
const allRanges = ranges => ({ quick: ranges, practice: ranges, shared: ranges });
const allOrders = order => ({ quick: order, practice: order, player: order, timeline: order });
const collect = context.BaraBeatRepeatPickup.collectRepeatRanges;
assert.equal(typeof collect, 'function', 'Shared raw pairing API');
const rawBars = freeze([bar('A', [true, true]), bar('B', [], ['2', 2]),
  bar('C', true), bar('D', true, 'continue'), bar('E', [], 'loop')]);
const rawBefore = structuredClone(rawBars);
assert.deepEqual(plainRanges(collect(rawBars)), [
  range(1, 2, '2'), range(1, 2, 2), range(4, 4, 'continue'), range(3, 5, 'loop')
], 'Raw values, duplicates, closing order and stack consumption stay untouched');
assert.deepEqual(rawBars, rawBefore);
for (const count of [0, -1, 1.4, 'bad', NaN, Infinity, 'continue', 'loop']) {
  assert.deepEqual(plainRanges(collect(freeze([bar('A', [0], [count])]))), [range(1, 1, count)],
    'Raw pairing must not interpret count ' + String(count));
}
const opaqueCount = Object.freeze({ valueOf() { throw new Error('Raw pairing must not coerce counts'); } });
assert.equal(collect(freeze([bar('A', true, [opaqueCount])]))[0].count, opaqueCount);
for (const input of [null, undefined, false, '', {}, [], new Array(3), [null, undefined, {}],
  [bar('A', true)], [bar('A', [], 2)]]) {
  assert.deepEqual(plainRanges(collect(freeze(input))), [], 'No fabricated ranges for incomplete input');
}
checkRanges('Simple', [bar('A', true), bar('B', [], 2)], allRanges([range(1, 2, 2)]), allOrders('ABABAB'));
checkRanges('Adjacent, close before open', [bar('A', true), bar('B', [], 1), bar('C', true), bar('D', [], 2)],
  allRanges([range(1, 2, 1), range(3, 4, 2)]), allOrders('ABABCDCDCD'));
checkRanges('Nested closing order', [bar('A', true), bar('B', true), bar('C', [], 1), bar('D', [], 1)], {
  quick: [range(2, 3, 1), range(1, 4, 1)], practice: [range(2, 3, 1), range(1, 4, 1)],
  shared: [range(1, 4, 1), range(2, 3, 1)]
}, allOrders('ABCBCDABCBCD'));
checkRanges('Duplicate bounds', [bar('A', [true, true]), bar('B', [], [2, 2])], {
  quick: [range(1, 2, 2), range(1, 2, 2)], practice: [range(1, 2, 2), range(1, 2, 2)],
  shared: [range(1, 2, 2)]
}, allOrders('ABABAB'));
checkRanges('Same bounds, different counts', [bar('A', [true, true], [2, 1])],
  allRanges([range(1, 1, 2), range(1, 1, 1)]), allOrders('AAA'));
checkRanges('Loop', [bar('A', true), bar('B', [], 'loop')], {
  quick: [range(1, 2, 1)], practice: [range(1, 2, 'loop')], shared: [range(1, 2, 'loop')]
}, { quick: 'ABAB', practice: 'AB', player: 'ABAB', timeline: 'ABAB' });
checkRanges('Continue consumes a start only in practice/shared', [bar('A', true), bar('B', [], 'continue'), bar('C', [], 1)], {
  quick: [range(1, 3, 1)], practice: [], shared: []
}, { quick: 'ABCABC', practice: 'ABC', player: 'ABC', timeline: 'ABC' });
checkRanges('Fraction', [bar('A', true, 1.4)], {
  quick: [range(1, 1, 1)], practice: [range(1, 1, 1.4)], shared: [range(1, 1, 1.4)]
}, { quick: 'AA', practice: 'AAA', player: 'AAA', timeline: 'AAA' });
for (const count of [-1, [0], ' ']) {
  checkRanges('Nonpositive ' + String(count), [bar('A', true, count)], {
    quick: [range(1, 1, 1)], practice: [range(1, 1, Number(count))], shared: []
  }, { quick: 'AA', practice: 'A', player: 'A', timeline: 'A' });
}
checkRanges('Invalid text', [bar('A', true, 'bad')], {
  quick: [range(1, 1, 1)], practice: [], shared: []
}, { quick: 'AA', practice: 'A', player: 'A', timeline: 'A' });
checkRanges('Scalar zero end', [bar('A', true, 0)], {
  quick: [], practice: [range(1, 1, 0)], shared: []
}, allOrders('A'));
for (const start of [0, NaN]) {
  checkRanges('Falsy scalar start ' + String(start), [bar('A', start, 1)], {
    quick: [], practice: [range(1, 1, 1)], shared: [range(1, 1, 1)]
  }, { quick: 'A', practice: 'AA', player: 'AA', timeline: 'AA' });
}
checkRanges('Array zero start', [bar('A', [0], 1)], allRanges([range(1, 1, 1)]), allOrders('AA'));
checkRanges('Numeric string and ignored start count', [bar('A', 8, '2')], allRanges([range(1, 1, 2)]), allOrders('AAA'));
checkRanges('Unclosed', [bar('A', true), bar('B')], allRanges([]), allOrders('AB'));
checkRanges('Unmatched end', [bar('A', [], 2)], allRanges([]), allOrders('A'));
checkRanges('Missing bars', [bar('A'), null, undefined, {}], allRanges([]), {
  quick: 'A_', practice: 'A___', player: 'A___', timeline: 'A___'
});
for (const input of [null, undefined, false, '', {}, [], new Array(3)]) {
  checkRanges('Empty/sparse input', input, allRanges([]));
}
// Parsing only: Infinity must never be passed to any expander in this test.
for (const count of [Infinity, 'Infinity', [Infinity]]) {
  checkRanges('Nonfinite count, no expansion', [bar('A', true, count)], {
    quick: [range(1, 1, Infinity)], practice: [], shared: []
  });
}

const markers = freeze([undefined, null, false, '', 0, NaN, true, 1, 2, 1.4, -1, Infinity,
  'loop', 'continue', 'bad', ' ', [], [false, null, '', 0], [true, true], [1, 2], ['continue', 1]]);
let combinations = 0;
for (const start of markers) for (const end of markers) for (const middle of markers) {
  const bars = freeze([bar('A', start), bar('B', middle, end), bar('C', [], middle)]);
  const before = structuredClone(bars);
  for (const name of Object.keys(currentReaders)) {
    assert.deepEqual(plainRanges(currentReaders[name](bars)), plainRanges(oldReaders[name](bars)),
      'Parser old/new combination ' + combinations + ': ' + name);
  }
  assert.deepEqual(Array.from(context.getPracticeRepeatMarkerList(end)),
    legacyPractice.getPracticeRepeatMarkerList(end), 'Practice marker helper');
  const rawRanges = freeze([range(1, 2, end), range(1, 2, end), range(2, 3, middle), range(1, 3, start)]);
  assert.deepEqual(plainRanges(context.BaraBeatRepeatPickup.normalizeRanges(rawRanges)),
    plainRanges(legacyShared.normalizeRanges(rawRanges)), 'Player normalization old/new');
  assert.deepEqual(bars, before, 'Combination leaves source untouched');
  combinations++;
}
assert.equal(combinations, 9261);

const pattern = (id, bars, labelType = 'Call', instrument = 'Djembe_1') => ({
  id, sourceKey: id, labelType, labelName: id, defaultTargets: [instrument], bars
});
function configured(patterns, parallel = false) {
  context.findPatternById = id => patterns.find(p => p.id === id);
  const prepared = patterns.map(context.buildSheetQuickPlayPreparedPattern);
  const quick = context.buildSheetQuickPlayConfiguredSections(prepared);
  const entries = patterns.map((p, index) => ({
    patternId: p.id, patternSourceKey: p.sourceKey, targetInstruments: p.defaultTargets,
    blockId: parallel ? 'parallel' : 'block-' + index,
    repeatCount: 1, patternRepeatCount: 1, isPracticeTarget: true
  }));
  return { prepared, quick, practice: context.buildPracticeSectionsFromEntries(entries) };
}
function checkConfigured(label, patterns, assertExpected, parallel = false) {
  const before = structuredClone(patterns);
  freeze(patterns);
  const actual = configured(patterns, parallel);
  assertExpected(actual);
  for (const section of actual.quick) {
    for (const refs of section.highlightSteps) {
      for (const ref of refs || []) {
        const sourceBar = patterns.flatMap(p => p.bars).find(b => b.sourceBarIndex === ref.sourceBarIndex);
        assert(sourceBar && sourceBar.notes[ref.sourceStepIndex] !== 'f', label + ': valid highlight source');
      }
    }
  }
  const saved = {
    BaraBeatRepeatPickup: context.BaraBeatRepeatPickup,
    buildSheetQuickPlayRepeatRanges: context.buildSheetQuickPlayRepeatRanges,
    buildPracticeRepeatRangesFromBars: context.buildPracticeRepeatRangesFromBars,
    getPracticeRepeatMarkerList: context.getPracticeRepeatMarkerList
  };
  try {
    Object.assign(context, {
      BaraBeatRepeatPickup: Object.freeze({ ...saved.BaraBeatRepeatPickup, ...legacyShared }),
      buildSheetQuickPlayRepeatRanges: legacyQuickPlay.buildSheetQuickPlayRepeatRanges,
      ...legacyPractice
    });
    assert.deepEqual(plain(actual), plain(configured(patterns, parallel)), label + ': complete sections old/new');
  } finally {
    Object.assign(context, saved);
  }
  assert.deepEqual(patterns, before, label + ': immutable notes and markers');
}
for (const duplicate of [false, true]) {
  const bars = [bar('A', duplicate ? [true, true] : true), bar('B', [], duplicate ? [2, 2] : 2, [], { 8: 'bass' })];
  checkConfigured(duplicate ? 'Known duplicate whole-pattern discrepancy' : 'Whole-pattern repeat',
    [pattern('repeat', bars)], result => {
      assert.equal(result.prepared[0].quickPlaySectionRepeatCount, duplicate ? 9 : 3);
      assert.equal(result.quick[0].repeatCount, duplicate ? 9 : 3);
      assert.deepEqual(Array.from(result.quick[0].trackNotes.Djembe_1), bars.flatMap(b => b.notes));
      assert.deepEqual(Array.from(result.practice[0].trackNotes.Djembe_1),
        Array.from({ length: 3 }, () => bars.flatMap(b => b.notes)).flat());
      assert.equal(result.practice[0].repeatCount, 1);
      assert.deepEqual(plain(result.quick[0].highlightSteps[0]), [{ sourceBarIndex: 1, sourceStepIndex: 0 }]);
      assert.deepEqual(plain(result.quick[0].highlightSteps[32]), [{ sourceBarIndex: 2, sourceStepIndex: 8 }]);
      assert(result.prepared[0].bars.every(b => b.repeat.start.length === 0 && b.repeat.end.length === 0),
        'Written repeats must not be applied twice by the player');
    });
}
const shortBar = bar('A', [], [], [{ type: 'shortbar', stepIndex: 6 }], { 0: 'tone', 8: 'bass' });
checkConfigured('Known short Call discrepancy', [pattern('short', [shortBar])], result => {
  assert.equal(result.quick[0].fixedLength, 24);
  assert.deepEqual(Array.from(result.quick[0].trackNotes.Djembe_1), shortBar.notes);
  assert.deepEqual(Array.from(result.practice[0].trackNotes.Djembe_1), shortBar.notes.slice(0, 6));
  assert.equal(context.flattenPatternNotes(pattern('short', [shortBar])).length, 6);
});
const call = pattern('call', [bar('A')]);
const intro = pattern('intro', [bar('B')], 'Intro');
const solo = pattern('solo', [
  bar('C', [], [], [{ type: 'in', stepIndex: 22 }], { 22: 'slap' }),
  bar('D', [], [], [{ type: 'out', stepIndex: 8 }], { 0: 'bass', 8: 'tone', 16: 'slap' })
], 'Solo');
checkConfigured('Call/Intro, pickup and final OUT', [call, intro, solo], result => {
  for (const sections of [result.quick, result.practice]) {
    assert.equal(sections.length, 3);
    assert.equal(sections[1].trackNotes.Djembe_1[22], 'slap');
    assert.equal(sections[2].trackNotes.Djembe_1[0], 'bass');
    assert.equal(sections[2].finalRepeatOutSteps.Djembe_1, 8);
  }
  assert.deepEqual(plain(result.quick[1].highlightSteps[22]), [{ sourceBarIndex: 3, sourceStepIndex: 22 }]);
  assert.deepEqual(plain(result.quick[2].highlightSteps[16]),
    [{ sourceBarIndex: 4, sourceStepIndex: 16, mutedOnFinalRepeat: true }]);
});
const kenkeni = pattern('kenkeni', [bar('A', [], [], [], { 0: 'Open' })], 'Begleitung', 'Kenkeni');
const sangban = pattern('sangban', [bar('B', [], [], [], { 0: 'Bell' }),
  bar('C', [], [], [], { 0: 'Muffled' })], 'Begleitung', 'Sangban');
checkConfigured('Parallel accompaniment lengths', [kenkeni, sangban], result => {
  for (const sections of [result.quick, result.practice]) {
    assert.equal(sections.length, 1);
    assert.deepEqual(Array.from(sections[0].trackNotes.Kenkeni), kenkeni.bars[0].notes.concat(kenkeni.bars[0].notes));
    assert.deepEqual(Array.from(sections[0].trackNotes.Sangban), sangban.bars.flatMap(b => b.notes));
  }
}, true);
checkConfigured('Empty sections', [], result => {
  assert.equal(result.quick.length, 0);
  assert.equal(result.practice.length, 0);
});
console.log('Repeat pairing: fixed ranges/sequences, 9261 old/new combinations, unchanged normalization, complete sections, source highlights and known discrepancies checked; nonfinite counts parsed only.');
