import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

const context = vm.createContext({
  console,
  window: { addEventListener() {} },
  rhythm: 'tenaer',
  titel: { attr: () => 'Ballet Dununs test' },
  getResolvedTimelineLoopCount: () => false
});
vm.runInContext(source('JS/timeline.js'), context);
loadFunctions(context, source('index.php'), ['mapInstrumentNameForPlayer']);
context.updateTimelineMetadataNode = () => {};
context.renderTimelinePanel = () => {};
const state = vm.runInContext('timelineState', context);
const pattern = {
  id: 'three-bass', sourceKey: 'Dreierbass|Begleitpattern|1',
  instrument: 'Dreierbass', sourceInstrument: 'Dreierbass',
  labelType: 'Begleitung', labelName: 'Begleitpattern', name: 'Ballet Dununs',
  bars: [{ sourceBarIndex: 6, notes: ['kenkeni_sangban', ...new Array(23).fill('f')],
    repeat: { start: [], end: [] }, controls: [] }]
};
pattern.defaultTargets = context.getDefaultTargetsForPattern(pattern, 'Dreierbass');
assert.deepEqual(Array.from(pattern.defaultTargets), ['Dreierbass']);
assert.deepEqual(Array.from(context.getDefaultTargetsForPattern({ instrument: 'Bässe' }, 'Bässe')),
  ['Kenkeni', 'Sangban', 'Doundoun'], 'Basses still means three separate drums, not an extra combined part');
state.sourcePatterns = [pattern];
state.entries = [context.cloneTimelineEntryFromPattern(pattern)];
function layout() {
  return context.buildTimelineHorizontalLayout(context.buildTimelineVisualRows(
    context.buildTimelineDisplayGroups(state.entries, state.sourcePatterns), state.sourcePatterns));
}
let currentLayout = layout();
assert.equal(currentLayout.clips.length, 1, 'An added Ballet Dununs entry must produce a visible clip');
assert.deepEqual(Array.from(currentLayout.usedTargets), ['Dreierbass'], 'Show the combined drum lane');
assert.equal(currentLayout.clips[0].targetInstrument, 'Dreierbass');

const payload = { type: 'pattern-group', entries: [{ patternId: pattern.id, targetInstruments: ['Dreierbass'] }] };
const drop = context.getTimelineRulerBarDropAction(payload, currentLayout, 3);
assert.equal(drop.type, 'accompaniment', 'Dropping on the ruler creates an accompaniment segment');
assert.equal(drop.targetInstrument, 'Dreierbass');
assert.equal(drop.startBar, 3);
context.addTimelineAccompanimentSegment(payload, drop.targetInstrument, drop.startBar);
assert.equal(state.accompanimentSegments.length, 1);
assert.equal(state.accompanimentSegments[0].targetInstrument, 'Dreierbass', 'Serialization must retain the instrument');
context.updateTimelineAccompanimentSegment(state.accompanimentSegments[0], { startBar: 4, barCount: 6 });
currentLayout = layout();
const segmentClip = currentLayout.clips.find(clip => clip.kind === 'segment');
assert.equal(segmentClip.targetInstrument, 'Dreierbass');
assert.equal(segmentClip.startBar, 4);
assert.equal(segmentClip.barCount, 6);

const storedSegments = JSON.parse(JSON.stringify(state.accompanimentSegments.map(context.serializeTimelineAccompanimentSegment)));
state.accompanimentSegments = context.syncTimelineAccompanimentSegmentsWithPatternLibrary(state.sourcePatterns, storedSegments);
assert.equal(state.accompanimentSegments.length, 1, 'Saved Ballet Dununs segments survive reopening');
assert.equal(state.accompanimentSegments[0].targetInstrument, 'Dreierbass');
const playerPayload = context.buildTimelinePlayerPayload(state.sourcePatterns, state.entries)[0];
assert.equal(playerPayload.TimelineEntries[0].targetInstruments[0], 'Dreierbass');
assert.equal(playerPayload.AccompanimentSegments[0].targetInstrument, 'Dreierbass');
assert.equal(playerPayload.PatternLibrary[0].bars[0].notes[0], 'kenkeni_sangban', 'Combined sound notes remain unchanged');
assert.equal(playerPayload.TimelineTotalBars, 9);

for (const target of ['Kenkeni', 'Sangban', 'Doundoun']) {
  const bassPattern = { ...pattern, id: target, sourceKey: target, instrument: target,
    sourceInstrument: target, defaultTargets: [target] };
  state.sourcePatterns.push(bassPattern);
  state.entries.push(context.cloneTimelineEntryFromPattern(bassPattern));
}
assert.deepEqual(Array.from(layout().usedTargets), ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass'],
  'Separate bass lanes coexist with the combined Ballet Dununs lane');
console.log('Ballet Dununs timeline: visible entries, ruler drops, segment move/resize, persistence, player payload and separate bass lanes checked.');
