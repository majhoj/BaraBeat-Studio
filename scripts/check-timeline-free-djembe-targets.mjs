import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installRepeatPickup, installTiming } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {} }, rhythm: 'tenaer',
  titel: { attr: () => 'Free Djembe targets' }, practiceState: {},
  normalizePracticeInstrumentVolumes: value => value || {} });
installRepeatPickup(ui);
vm.runInContext(source('JS/timeline.js'), ui);
ui.updateTimelineMetadataNode = () => {};
ui.renderTimelinePanel = () => {};
ui.reportTimelinePlacementConflict = () => {};
const state = vm.runInContext('timelineState', ui);
let undo;
ui.recordArrangementHistorySnapshot = () => { undo = JSON.stringify(state.entries); };
const bar = note => ({ notes: [note, ...Array(23).fill('f')], controls: [], repeat: { start: [], end: [] } });
const pattern = (id, sourceInstrument, targets, bars = [bar('bass')]) => ({ id, sourceKey: id,
  name: id, labelName: id, labelType: 'Begleitung', instrument: 'Djembe', sourceInstrument,
  defaultTargets: targets, bars });
const accompaniment = pattern('begleitpattern-3', 'Djembe', ['Djembe_3']);
const second = pattern('begleitpattern-2', 'Djembe', ['Djembe_2'], [bar('tone')]);
const explicit = pattern('explicit-djembe-1', 'Djembe 1', ['Djembe_1']);
const long = pattern('long', 'Djembe', ['Djembe_3'], [bar('bass'), bar('tone')]);
const bass = { ...pattern('sangban', 'Sangban', ['Sangban']), instrument: 'Sangban' };
state.sourcePatterns = [accompaniment, second, explicit, long, bass];
function add(p, target, startBar, durationBars = 1) {
  const entry = ui.cloneTimelineEntryFromPattern(p, { blockId: ui.nextTimelineBlockId(),
    targetInstruments: [target], startBar, durationBars });
  state.entries.push(entry);
  return entry;
}
function setup() {
  state.entries = []; state.accompanimentSegments = []; undo = null;
  add(accompaniment, 'Djembe_3', 41, 11);
  add(second, 'Djembe_2', 41, 11);
}
const incoming = { type: 'pattern-group', entries: [{ patternId: accompaniment.id,
  handMode: 'h2h', targetInstruments: ['Djembe_3'] }] };
const originalPayload = JSON.stringify(incoming);
const originalPatterns = JSON.stringify(state.sourcePatterns);
const layout = () => ui.getCurrentTimelineLayout();
const newGroup = () => layout().clips.find(c => c.targetInstrument === 'Djembe_1').group;

// Kuku: Djembe 1 is free in bars 41-51; the same pattern already plays on Djembe 3.
setup();
const before = JSON.stringify(state.entries);
const idsBefore = [state.nextEntryId, state.nextBlockId];
const action = ui.getTimelineRulerBarDropAction(incoming, layout(), 41);
assert.equal(action?.type, 'positioned', 'A library drop must remain possible on a free Djembe track');
assert.equal(JSON.stringify(state.entries), before, 'Drag preview must not mutate existing entries');
assert.deepEqual([state.nextEntryId, state.nextBlockId], idsBefore, 'Preview must not consume IDs');
assert(ui.canInsertTimelinePayloadParallelToRow(incoming, [layout().clips[0].group]),
  'Parallel drop targets use the same availability as the ruler');
ui.executeTimelineRulerBarDrop(incoming, action);
assert.equal(undo, before);
assert.equal(JSON.stringify(state.entries.slice(0, 2)), before, 'Existing Djembe 2/3 clips remain untouched');
assert.deepEqual(Array.from(state.entries.at(-1).targetInstruments), ['Djembe_1']);
assert.equal(state.entries.at(-1).handMode, 'h2h');
assert.equal(state.entries.at(-1).startBar, 41);
assert.notEqual(state.entries.at(-1).blockId, state.entries[0].blockId, 'The new block is independent');
assert.notEqual(state.entries.at(-1).id, state.entries[0].id);
assert.equal(ui.setTimelineGroupRepeatCount(newGroup(), ui.getTimelineGroupRepeatInfo(newGroup()), 11), true);
assert.equal(layout().clips.find(c => c.targetInstrument === 'Djembe_1').barCount, 11);
assert.equal(JSON.stringify(state.entries.slice(0, 2)), before, 'Resizing the copy leaves its original intact');

const player = vm.createContext({ isPracticeMode: false, isSheetQuickPlayMode: false,
  rhythmType: 'tenaer', MAX_EXPANDED_BARS: 2000,
  trackInstrumentNames: ['Djembe_1', 'Djembe_2', 'Djembe_3', 'Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'] });
installTiming(player); installRepeatPickup(player);
const playerSource = source('Audio/player.html');
vm.runInContext(playerSource.slice(playerSource.indexOf('function sanitizeRepeatRanges('),
  playerSource.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(playerSource.slice(playerSource.indexOf('function getFeelOffsetSeconds('),
  playerSource.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, playerSource, ['getSectionLength']);
const sections = player.buildTimelineSections(ui.buildTimelinePlayerPayload(state.sourcePatterns, state.entries)[0]);
for (const target of ['Djembe_1', 'Djembe_3']) {
  const notes = Array.from(sections).flatMap(s => Array.from(player.materializeOrderedSectionTracks(s)[target]));
  assert.deepEqual(notes.slice(40 * 24, 51 * 24), Array.from({ length: 11 }, () => accompaniment.bars[0].notes).flat(),
    target + ' plays the complete independent eleven-bar accompaniment');
}
const saved = JSON.parse(JSON.stringify(ui.buildCurrentTimelineSyncOptions().persistedEntries));
state.entries = ui.syncTimelineEntriesWithPatternLibrary(state.sourcePatterns, saved, { appendMissingPatterns: false });
assert.equal(layout().clips.find(c => c.targetInstrument === 'Djembe_1').barCount, 11);
assert.notEqual(newGroup().entries[0].blockId, state.entries[0].blockId, 'Independent assignment survives reload');
state.entries = JSON.parse(before);
assert.equal(state.entries.length, 2, 'Undo removes only the new block');

// All free Djembes are selected on fallback, not just the first one.
setup(); state.entries.splice(1, 1);
assert.equal(ui.placeTimelinePayload({ type: 'pattern', patternId: accompaniment.id }, 41), true);
assert.deepEqual(Array.from(state.entries.at(-1).targetInstruments), ['Djembe_1', 'Djembe_2']);

// No collision: preserve the library's normal assignment.
state.entries = [];
assert.equal(ui.placeTimelinePayload(incoming, 41), true);
assert.deepEqual(Array.from(state.entries[0].targetInstruments), ['Djembe_3']);

setup();
const repeatedGroup = { type: 'pattern-group', entries: [incoming.entries[0], incoming.entries[0]] };
const groupAction = ui.getTimelineRulerBarDropAction(repeatedGroup, layout(), 41);
assert.equal(groupAction?.type, 'positioned');
ui.executeTimelineRulerBarDrop(repeatedGroup, groupAction);
assert.equal(newGroup().count, 2, 'Library repetitions are retained in one independent block');
assert.equal(ui.getTimelineGroupBarCount(newGroup()), 2);
assert(newGroup().entries.every(entry => entry.targetInstruments.length === 1 && entry.targetInstruments[0] === 'Djembe_1'));

// Availability covers the complete pattern, including written repeats and segments.
setup();
add(explicit, 'Djembe_1', 42);
const longPayload = { type: 'pattern', patternId: long.id };
assert.equal(ui.getTimelineRulerBarDropAction(longPayload, layout(), 41), null);
assert.equal(ui.placeTimelinePayload(longPayload, 41), false, 'Free start bar is not enough for a two-bar pattern');
setup();
state.accompanimentSegments = [{ id: 'occupied-1', patternId: accompaniment.id,
  targetInstrument: 'Djembe_1', startBar: 41, barCount: 11 }];
assert.equal(ui.getTimelineRulerBarDropAction(incoming, layout(), 41), null);
const blocked = JSON.stringify(state.entries);
assert.equal(ui.placeTimelinePayload(incoming, 41), false);
assert.equal(JSON.stringify(state.entries), blocked);
assert.equal(undo, null, 'A rejected insertion does not create an undo entry');
setup();
const repeated = pattern('repeated', 'Djembe', ['Djembe_3']);
repeated.bars[0].repeat = { start: [true], end: [1] };
state.sourcePatterns.push(repeated);
add(explicit, 'Djembe_1', 42);
assert.equal(ui.getTimelineRulerBarDropAction({ type: 'pattern', patternId: repeated.id }, layout(), 41), null,
  'Written repeats count toward occupied duration');
state.sourcePatterns.pop();

// Moving existing clips, explicit instruments and mixed groups are not reassigned.
setup(); add(explicit, 'Djembe_1', 41);
assert.equal(ui.placeTimelinePayload({ type: 'pattern', patternId: explicit.id }, 41), false);
state.entries = [state.entries[0]];
add(accompaniment, 'Djembe_3', 60);
const moving = { type: 'timeline-entry-group', startIndex: 1, count: 1 };
const beforeMove = JSON.stringify(state.entries);
assert.equal(ui.getTimelineRulerBarDropAction(moving, layout(), 41), null);
assert.equal(ui.placeTimelinePayload(moving, 41), false);
assert.equal(JSON.stringify(state.entries), beforeMove);
setup(); add(bass, 'Sangban', 41);
assert.equal(ui.placeTimelinePayload({ type: 'pattern', patternId: bass.id }, 41), false);
assert.equal(ui.placeTimelinePayload({ type: 'pattern-group', entries: [incoming.entries[0],
  { patternId: bass.id, targetInstruments: ['Sangban'] }] }, 41), false);
assert.equal(JSON.stringify(incoming), originalPayload, 'Library payload is immutable');
assert.equal(JSON.stringify(state.sourcePatterns), originalPatterns, 'Source patterns and defaults are immutable');
console.log('Free Djembe targets: preview/drop parity, independent copies, all free tracks, full duration, repeats, segments, undo/reload, audio and unchanged explicit/moved clips checked.');
