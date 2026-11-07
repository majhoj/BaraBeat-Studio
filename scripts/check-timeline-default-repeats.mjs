import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, installRepeatPickup } from './helpers/music-context.mjs';

const context = vm.createContext({ window: { addEventListener() {} }, rhythm: 'binaer' });
installRepeatPickup(context);
vm.runInContext(source('JS/timeline.js'), context);
const state = vm.runInContext('timelineState', context);

function fixture(lengths) {
  const bars = [];
  const patterns = lengths.map((length, index) => {
    const key = String.fromCharCode(65 + index);
    const pattern = { id: key, sourceKey: key, instrument: 'Djembe', labelType: 'Solo',
      labelName: key, defaultTargets: ['Djembe_3'], bars: [] };
    for (let i = 0; i < length; i++) {
      const bar = { index: bars.length + 1, sourceBarIndex: bars.length + 1, patternSourceKey: key, patternBarIndex: i,
        notes: [key + i, ...Array(31).fill('f')], controls: [], repeat: { start: [], end: [] } };
      pattern.bars.push(bar);
      bars.push(bar);
    }
    return pattern;
  });
  return { bars, patterns };
}
function entries(f, ranges) {
  const before = JSON.stringify(f);
  const result = context.buildDefaultTimelineEntriesFromRhythmBars(f.bars, ranges, f.patterns);
  assert.equal(JSON.stringify(f), before, 'No source notes or repeat markers are changed');
  return Array.from(result, entry => entry.patternId).join('');
}
for (const count of [1, 2, 8, 'loop']) {
  const f = fixture([1, 2, 1]);
  f.patterns[1].bars[0].controls = [{ type: 'in', stepIndex: 20 }];
  f.patterns[1].bars[1].controls = [{ type: 'out', stepIndex: 16 }];
  f.patterns[1].bars[1].repeat = { start: ['continue'], end: [count] };
  assert.equal(entries(f, [{ startBar: 3, endBar: 3, count }]), 'ABC',
    'Solo 2: repeating the second bar must not create whole-pattern repeats');
  const expanded = context.getTimelineExpandedPatternBars(f.patterns[1]);
  const passes = count === 'loop' ? 2 : count + 1;
  assert.equal(expanded.length, 1 + passes, 'Only the pattern expands its written repeats');
  assert.deepEqual(Array.from(expanded, bar => bar.notes[0]), ['B0', ...Array(passes).fill('B1')]);
}
assert.equal(entries(fixture([3]), [{ startBar: 1, endBar: 2, count: 2 }]), 'A', 'Repeated opening segment stays internal');
assert.equal(entries(fixture([3]), [{ startBar: 1, endBar: 3, count: 2 }, { startBar: 2, endBar: 2, count: 1 }]), 'A',
  'Nested repeats inside a complete pattern are not also expanded by the default timeline');
assert.equal(entries(fixture([4]), [{ startBar: 1, endBar: 2, count: 2 }, { startBar: 3, endBar: 4, count: 1 }]), 'A');
assert.equal(entries(fixture([1, 2, 1]), [{ startBar: 1, endBar: 3, count: 2 }, { startBar: 3, endBar: 3, count: 1 }]), 'ABABABC',
  'An outer repeat across patterns stays intact while an inner repeat remains inside its pattern');
assert.equal(entries(fixture([1, 1, 1]), [{ startBar: 1, endBar: 2, count: 1 }]), 'ABABC');
assert.equal(entries(fixture([1, 1, 1, 1]), [{ startBar: 1, endBar: 3, count: 1 }, { startBar: 2, endBar: 3, count: 1 }]),
  'ABCBCABCBCD', 'Nested multi-pattern repeats keep their order and count');
assert.equal(entries(fixture([1, 1]), [{ startBar: 1, endBar: 2, count: 2 }]), 'AB',
  'The existing whole-sheet loop policy stays separate from timeline entries');
assert.equal(entries(fixture([1, 1]), []), 'AB');
assert.equal(entries(fixture([]), []), '');

// Identical notated pattern instances can share a canonical key, but a repeat over both is still external.
const aliases = fixture([2, 2, 1]);
aliases.bars.slice(2, 4).forEach(bar => { bar.patternSourceKey = 'A'; });
aliases.patterns = [aliases.patterns[0], aliases.patterns[2]];
assert.equal(entries(aliases, [{ startBar: 1, endBar: 4, count: 1 }]), 'AAAAC');

// Existing user arrangements are never deduplicated by the default-generation fix.
const saved = fixture([2]);
state.sourcePatterns = saved.patterns;
const userEntries = Array.from({ length: 3 }, (_, i) => context.cloneTimelineEntryFromPattern(saved.patterns[0],
  { id: 'entry-' + i, blockId: 'chosen-repeats', startBar: 11 }));
const restored = context.syncTimelineEntriesWithPatternLibrary(saved.patterns, userEntries, { appendMissingPatterns: false });
assert.equal(restored.length, 3);
assert(restored.every(entry => entry.startBar === 11));
console.log('Default timeline: internal opening/middle/final/nested repeats expanded only by patterns; outer repeats and saved user repetitions preserved.');
