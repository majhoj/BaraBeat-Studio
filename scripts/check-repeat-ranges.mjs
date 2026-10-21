import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installRepeatPickup } from './helpers/music-context.mjs';

const context = vm.createContext({ MAX_EXPANDED_BARS: 2000, playerText: key => key });
installRepeatPickup(context);
const shared = context.BaraBeatRepeatPickup;
loadFunctions(context, source('Audio/player.html'), [
  'sanitizeRepeatRanges', 'normalizeRepeatMarkerList', 'buildRepeatRangesFromPatternBars',
  'isTimelineContinuationMarker', 'expandBarsWithRepeats'
]);
loadFunctions(context, source('JS/timeline.js'), [
  'getTimelineRepeatMarkerList', 'buildTimelinePatternRepeatRanges', 'expandTimelineBarsWithRepeats'
]);
loadFunctions(context, source('JS/practice.js'), [
  'getPracticeRepeatMarkerList', 'buildPracticeRepeatRangesFromBars'
]);
loadFunctions(context, source('index.php'), ['buildSheetQuickPlayRepeatRanges']);

function freeze(value) {
  if (value && typeof value === 'object') {
    Object.values(value).forEach(freeze);
    Object.freeze(value);
  }
  return value;
}
const plainRanges = ranges => Array.from(ranges, ({ startBar, endBar, count }) => ({ startBar, endBar, count }));
const range = (startBar, endBar, count) => ({ startBar, endBar, count });
const bar = (id, start = false, end = false) => ({
  id, sourceBarIndex: id.charCodeAt(0) - 64, notes: ['bass', 'f', 'tone', 'slap'],
  repeat: { start, end }, controls: [],
  playbackNoteSources: { 0: { sourceBarIndex: id.charCodeAt(0) - 64, sourceStepIndex: 0 } }
});

function check(label, bars, expectedRanges, expectedOrder) {
  const before = structuredClone(bars);
  freeze(bars);
  const readers = [
    ['module', shared.buildRepeatRanges],
    ['player', context.buildRepeatRangesFromPatternBars],
    ['timeline', value => context.buildTimelinePatternRepeatRanges({ bars: value })]
  ];
  for (const [name, read] of readers) {
    assert.deepEqual(plainRanges(read(bars)), expectedRanges, label + ': ' + name + ' ranges');
  }
  if (expectedOrder !== undefined) {
    for (const [name, read, expand] of [
      ['player', context.buildRepeatRangesFromPatternBars, context.expandBarsWithRepeats],
      ['timeline', value => context.buildTimelinePatternRepeatRanges({ bars: value }), context.expandTimelineBarsWithRepeats]
    ]) {
      const expanded = expand(bars, read(bars), 1, bars.length);
      assert.equal(Array.from(expanded, value => value.id).join(''), expectedOrder, label + ': ' + name + ' order');
      const expectedBars = Array.from(expectedOrder, id => bars.find(value => value.id === id));
      assert.deepEqual(Array.from(expanded, value => Array.from(value.notes)), expectedBars.map(value => value.notes),
        label + ': ' + name + ' notes');
      expanded.forEach((value, index) => {
        assert.equal(value, expectedBars[index], label + ': source bar identity');
        assert.equal(value.playbackNoteSources, expectedBars[index].playbackNoteSources, label + ': note sources');
      });
    }
  }
  assert.deepEqual(bars, before, label + ': source data unchanged');
}

check('Two additional repeats', [bar('A', true), bar('B', false, 2)], [range(1, 2, 2)], 'ABABAB');
check('Adjacent ranges close before the next one opens', [
  bar('A', true), bar('B', false, 1), bar('C', true), bar('D', false, 2)
], [range(1, 2, 1), range(3, 4, 2)], 'ABABCDCDCD');
check('Nested ranges', [bar('A', true), bar('B', true), bar('C', false, 1), bar('D', false, 1)],
  [range(1, 4, 1), range(2, 3, 1)], 'ABCBCDABCBCD');
check('Shared start, longest range first', [bar('A', [true, true]), bar('B', false, 1), bar('C', false, 1)],
  [range(1, 3, 1), range(1, 2, 1)], 'ABABCABABC');
check('Duplicate markers do not expand twice', [bar('A', [true, true]), bar('B', false, [2, '2'])],
  [range(1, 2, 2)], 'ABABAB');
check('Same bounds with different counts keep their stable order', [bar('A', [true, true], [2, 1])],
  [range(1, 1, 2), range(1, 1, 1)], 'AAA');
check('Loop stays symbolic and keeps its existing extra pass', [bar('A', true), bar('B', false, 'loop')],
  [range(1, 2, 'loop')], 'ABAB');
check('Continue consumes its paired start before being rejected', [
  bar('A', true), bar('B', true, 'continue'), bar('C', false, 1)
], [range(1, 3, 1)], 'ABCABC');
check('Only the end marker supplies the count', [bar('A', 8), bar('B', false, '2')], [range(1, 2, 2)], 'ABABAB');
check('Fractional counts are not rounded by normalization', [bar('A', true, 1.4)], [range(1, 1, 1.4)], 'AAA');
check('Numeric zero still counts as a start marker', [bar('A', 0, 1)], [range(1, 1, 1)], 'AA');
check('Single bar', [bar('A', true, true)], [range(1, 1, 1)], 'AA');
check('Unclosed start', [bar('A', true), bar('B')], [], 'AB');
check('Unmatched end', [bar('A', false, 2)], [], 'A');
check('Sparse or unfinished bars', [null, undefined, {}, { repeat: null }, { notes: [] }], []);
check('Sparse array', new Array(3), []);
for (const value of [undefined, null, false, 0, '', {}, 'bars', []]) {
  check('Empty/non-array input ' + String(value), value, []);
}
for (const pattern of [undefined, null, {}, { bars: null }, { bars: 'bars' }]) {
  assert.deepEqual(plainRanges(context.buildTimelinePatternRepeatRanges(pattern)), []);
}
for (const count of [0, -1, NaN, Infinity, -Infinity, 'bad', 'NaN', 'Infinity', 'continue', ' ']) {
  check('Invalid count ' + String(count), [bar('A', true, [count])], [], 'A');
}

const controlBar = bar('A', true, 1);
controlBar.controls = [{ type: 'in', stepIndex: 2 }, { type: 'out', stepIndex: 3 },
  { type: 'shortbar', stepIndex: 3 }, { type: 'overlap', stepIndex: 3 }];
check('Range parsing does not interpret IN/OUT or other controls', [controlBar], [range(1, 1, 1)]);

const rawRanges = freeze([
  range(3, 4, 1), range('1', '2', '2'), range(1, 4, 'loop'), range(1, 2, 2), range(1, 2, 1),
  range(1.5, 2.5, 1.4), null, {}, range(0, 2, 1), range(2, 1, 1),
  range(NaN, 2, 1), range(1, Infinity, 1), range(1, 2, 0), range(1, 2, 'continue')
]);
const rangesBefore = structuredClone(rawRanges);
const expectedNormalized = [range(1, 4, 'loop'), range(1, 2, 2), range(1, 2, 1), range(1.5, 2.5, 1.4), range(3, 4, 1)];
for (const normalize of [shared.normalizeRanges, context.sanitizeRepeatRanges]) {
  assert.deepEqual(plainRanges(normalize(rawRanges)), expectedNormalized);
  for (const empty of [null, undefined, {}, 'ranges']) assert.deepEqual(plainRanges(normalize(empty)), []);
}
assert.deepEqual(rawRanges, rangesBefore, 'Range input remains untouched, including invalid values');

const markerList = freeze([false, null, undefined, '', 0, true, 'loop', 'continue', -1, NaN, 1.4]);
for (const normalize of [shared.normalizeMarkerList, context.normalizeRepeatMarkerList, context.getTimelineRepeatMarkerList]) {
  assert.deepEqual(Array.from(normalize(markerList)), [0, true, 'loop', 'continue', -1, NaN, 1.4]);
  for (const empty of [false, null, undefined, '']) assert.deepEqual(Array.from(normalize(empty)), []);
  for (const scalar of [0, true, 'loop', 'continue', 1.4]) assert.deepEqual(Array.from(normalize(scalar)), [scalar]);
}

// These mode-specific parsers deliberately remain separate in this migration step.
for (const [count, playerCount, practiceCount, quickCount] of [
  ['loop', 'loop', 'loop', 1], [0, null, 0, 1], [-1, null, -1, 1], [1.4, 1.4, 1.4, 1], ['bad', null, null, 1]
]) {
  const bars = freeze([bar('A', true, [count])]);
  for (const [reader, expected] of [
    [context.buildRepeatRangesFromPatternBars, playerCount],
    [context.buildPracticeRepeatRangesFromBars, practiceCount],
    [context.buildSheetQuickPlayRepeatRanges, quickCount]
  ]) {
    assert.deepEqual(plainRanges(reader(bars)), expected === null ? [] : [range(1, 1, expected)]);
  }
}
const duplicate = freeze([bar('A', [true, true], [2, 2])]);
assert.equal(shared.buildRepeatRanges(duplicate).length, 1);
assert.equal(context.buildPracticeRepeatRangesFromBars(duplicate).length, 2);
assert.equal(context.buildSheetQuickPlayRepeatRanges(duplicate).length, 2);

for (const file of ['index.php', 'app-shell.html']) {
  const html = source(file);
  const moduleIndex = html.indexOf('src="JS/repeat-pickup.js');
  assert(moduleIndex >= 0, file + ': shared module included');
  for (const consumer of ['src="JS/timeline.js', 'src="JS/practice.js', 'function buildSheetQuickPlayRepeatRanges(']) {
    assert(html.indexOf(consumer) > moduleIndex, file + ': module before ' + consumer);
  }
  const tag = html.slice(html.lastIndexOf('<script', moduleIndex), html.indexOf('>', moduleIndex));
  assert(!/\b(?:async|defer)\b/.test(tag), file + ': module must load synchronously');
}
const player = source('Audio/player.html');
assert(player.indexOf('src="../JS/repeat-pickup.js') >= 0);
assert(player.indexOf('src="../JS/repeat-pickup.js') < player.indexOf('function sanitizeRepeatRanges('));

console.log('Repeat ranges: exact sequences, adjacent/nested/duplicate markers, special/invalid values, immutable sources, mode differences and load order checked.');
