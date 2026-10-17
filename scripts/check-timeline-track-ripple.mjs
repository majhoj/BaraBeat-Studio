import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {} }, rhythm: 'tenaer',
  titel: { attr: () => 'Track ripple' }, normalizePracticeInstrumentVolumes: () => ({}), practiceState: {} });
vm.runInContext(source('JS/timeline.js'), ui);
ui.updateTimelineMetadataNode = () => {};
ui.renderTimelinePanel = () => {};
const state = vm.runInContext('timelineState', ui);
const snapshot = () => JSON.stringify({ entries: state.entries, segments: state.accompanimentSegments });
const restore = json => { const value = JSON.parse(json); state.entries = value.entries; state.accompanimentSegments = value.segments; };
const history = [];
ui.recordArrangementHistorySnapshot = () => history.push(snapshot());
const bar = (note, controls = []) => ({ notes: [note, ...Array(23).fill('f')], controls, repeat: { start: [], end: [] } });
const pattern = (id, label, target, bars) => ({ id, sourceKey: id, labelName: id, name: id, labelType: label,
  instrument: target.startsWith('Djembe') ? 'Djembe' : target, sourceInstrument: target, defaultTargets: [target], bars });
const call = pattern('call', 'Call', 'Djembe_1', [bar('slap')]);
const solo = pattern('solo', 'Solo', 'Djembe_1', [bar('tone'), bar('bass')]);
const other = pattern('other', 'Solo', 'Djembe_1', [bar('bass')]);
const sangban = pattern('sangban', 'Begleitung', 'Sangban', [bar('Open'), bar('Bell')]);
state.sourcePatterns = [call, solo, other, sangban];
const layout = () => ui.getCurrentTimelineLayout();
const clip = (id, target = 'Djembe_1') => layout().clips.find(c => c.pattern.id === id && c.targetInstrument === target);
const positions = target => Array.from(layout().clips).filter(c => c.targetInstrument === target).map(c => [c.pattern.id, c.startBar, c.barCount]);
function add(p, startBar, repeats = 1, targets = p.defaultTargets) {
  const blockId = ui.nextTimelineBlockId();
  for (let i = 0; i < repeats; i++) state.entries.push(ui.cloneTimelineEntryFromPattern(p, { blockId, startBar, targetInstruments: targets }));
}
function reset() { state.entries = []; state.accompanimentSegments = []; history.length = 0; }
function repeat(id, count, target = 'Djembe_1') {
  const group = clip(id, target).group;
  return ui.setTimelineGroupRepeatCount(group, ui.getTimelineGroupRepeatInfo(group), count);
}
function movePayload(id, target = 'Djembe_1') {
  const group = clip(id, target).group;
  return { type: 'timeline-entry-group', startIndex: group.startIndex, count: group.count };
}
function move(id, start, target) { return ui.placeTimelinePayload(movePayload(id, target), start); }
function addSegment(p, startBar, barCount, targetInstrument = p.defaultTargets[0]) {
  const segment = { id: ui.nextTimelineAccompanimentSegmentId(), patternId: p.id, patternSourceKey: p.sourceKey,
    targetInstrument, startBar, barCount };
  state.accompanimentSegments.push(segment);
  return segment;
}

const player = vm.createContext({ isPracticeMode: false, isSheetQuickPlayMode: false, rhythmType: 'tenaer', MAX_EXPANDED_BARS: 2000,
  trackInstrumentNames: ['Djembe_1', 'Djembe_2', 'Djembe_3', 'Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'] });
installTiming(player); installRepeatPickup(player);
const text = source('Audio/player.html');
vm.runInContext(text.slice(text.indexOf('function sanitizeRepeatRanges('), text.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(text.slice(text.indexOf('function getFeelOffsetSeconds('), text.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, text, ['getSectionLength']);
function audio(target = 'Djembe_1') {
  const sections = player.buildTimelineSections(ui.buildTimelinePlayerPayload(state.sourcePatterns, state.entries)[0]);
  return Array.from(sections).flatMap(s => Array.from(player.materializeOrderedSectionTracks(s)[target]));
}

reset();
add(call, 1); add(solo, 2, 2); add(other, 8); add(sangban, 2, 6);
const initial = positions('Djembe_1');
const initialBass = positions('Sangban');
const initialAudio = audio();
const bassAudio = audio('Sangban');
assert.equal(repeat('solo', 3), true);
assert.deepEqual(positions('Djembe_1'), [['call', 1, 1], ['solo', 2, 6], ['other', 10, 1]]);
assert.deepEqual(positions('Sangban'), initialBass);
assert.deepEqual(audio('Sangban'), bassAudio);
assert.equal(audio()[9 * 24], 'bass', 'Following audio moves by the same number of bars');
assert.equal(history.length, 1, 'Resize plus all followers form one undo step');
const afterResize = snapshot();
restore(history.pop());
assert.deepEqual(positions('Djembe_1'), initial);
restore(afterResize);
assert.equal(repeat('solo', 2), true);
assert.deepEqual(positions('Djembe_1'), initial, 'Shrinking restores all original gaps');
assert.deepEqual(audio(), initialAudio);
const beforePreview = snapshot();
const beforeId = state.nextEntryId;
const action = ui.getTimelineRulerBarDropAction(movePayload('solo'), layout(), 7);
assert.equal(action.type, 'positioned');
assert.equal(snapshot(), beforePreview, 'Drag preview is read-only');
assert.equal(state.nextEntryId, beforeId);
ui.executeTimelineRulerBarDrop(movePayload('solo'), action);
assert.deepEqual(positions('Djembe_1'), [['call', 1, 1], ['solo', 7, 4], ['other', 13, 1]]);
assert.equal(move('solo', 2), true);
assert.deepEqual(positions('Djembe_1'), initial);
const beforeRejected = snapshot();
const historyCount = history.length;
assert.equal(move('solo', 1), false, 'Cannot cross an earlier occupied bar');
assert.equal(snapshot(), beforeRejected);
assert.equal(history.length, historyCount);
assert.equal(ui.getTimelineRulerBarDropAction(movePayload('solo'), layout(), 1), null);
assert.equal(ui.placeTimelinePayload({ type: 'pattern', patternId: call.id }, 3), false,
  'Library insertion is not an implicit ripple edit');

// Entries and explicit accompaniment segments participate in the same track sequence.
reset();
const loop = pattern('loop', 'Begleitung', 'Djembe_1', [bar('tone')]);
state.sourcePatterns.push(loop);
add(call, 1); const segment = addSegment(loop, 2, 3); add(solo, 6); const later = addSegment(loop, 10, 2);
add(sangban, 2, 2); const laterBass = addSegment(sangban, 8, 4);
assert.equal(ui.updateTimelineAccompanimentSegment(segment, { barCount: '5' }), true);
assert.equal(segment.barCount, 5);
assert.equal(clip('solo').startBar, 8);
assert.equal(later.startBar, 12);
assert.equal(laterBass.startBar, 8);
assert.equal(ui.updateTimelineAccompanimentSegment(segment, { barCount: '3' }), true);
assert.equal(clip('solo').startBar, 6);
assert.equal(later.startBar, 10);
const segmentMove = { type: 'timeline-accompaniment-segment', segmentId: segment.id };
const segmentAction = ui.getTimelineRulerBarDropAction(segmentMove, layout(), 4);
assert(segmentAction);
ui.executeTimelineRulerBarDrop(segmentMove, segmentAction);
assert.equal(segment.startBar, 4);
assert.equal(clip('solo').startBar, 8);
assert.equal(later.startBar, 12);
assert.equal(repeat('solo', 2), true);
assert.equal(later.startBar, 14, 'Resizing an entry moves a following explicit segment too');
assert.equal(repeat('sangban', 3, 'Sangban'), true);
assert.equal(laterBass.startBar, 10);
assert.equal(clip('solo').startBar, 8, 'Sangban edits still cannot move Djembe');

// Shared target assignments must not pull an unrelated instrument along.
reset();
add(solo, 2); add(other, 5, 2, ['Djembe_1', 'Djembe_2']); add(call, 9, 1, ['Djembe_2']);
const untouched = positions('Djembe_2');
assert.equal(repeat('solo', 2), true);
assert.equal(clip('other').startBar, 7);
assert.equal(clip('other').group.count, 2);
assert.deepEqual(positions('Djembe_2'), untouched);
assert.notEqual(clip('other').group.blockId, clip('other', 'Djembe_2').group.blockId);
assert.equal(new Set(state.entries.map(e => e.id)).size, state.entries.length);
const persisted = JSON.parse(JSON.stringify(ui.buildCurrentTimelineSyncOptions().persistedEntries));
state.entries = ui.syncTimelineEntriesWithPatternLibrary(state.sourcePatterns, persisted, { appendMissingPatterns: false });
assert.equal(clip('other').startBar, 7);
assert.deepEqual(positions('Djembe_2'), untouched);
reset();
add(solo, 2, 1, ['Djembe_1', 'Djembe_2']); add(other, 5, 1, ['Djembe_1', 'Djembe_2']); add(sangban, 2);
assert.equal(move('solo', 4), true);
assert.equal(clip('other').startBar, 7);
assert.equal(clip('other', 'Djembe_2').startBar, 7);
assert.equal(clip('sangban', 'Sangban').startBar, 2);

// A final OUT changes the length of the last pass, not the length of every pass.
reset();
const out = pattern('out', 'Solo', 'Djembe_1', [bar('tone', [{ type: 'out', stepIndex: 8 }]), bar('bass')]);
out.bars[0].notes[8] = 'slap'; out.bars[0].notes[12] = 'bass';
state.sourcePatterns.push(out);
add(out, 2, 3); add(call, 7);
assert.equal(repeat('out', 4), true);
assert.equal(clip('out').barCount, 7);
assert.equal(clip('call').startBar, 9);
assert.equal(audio()[7 * 24 + 8], 'slap');
assert.equal(audio()[7 * 24 + 12], 'f');
assert.equal(audio()[8 * 24], 'slap');

// Solo 5: six bars become eight, while the final slap remains shared with Solo 6.
reset();
const overlap = pattern('overlap', 'Solo', 'Djembe_1', [bar('tone'), bar('bass'),
  bar('slap', [{ type: 'out', stepIndex: 0 }, { type: 'overlap', stepIndex: 0 }])]);
state.sourcePatterns.push(overlap);
add(overlap, 24, 3); add(solo, 30, 3); add(call, 36); add(sangban, 24, 8);
assert.equal(repeat('overlap', 4), true);
assert.equal(clip('overlap').barCount, 8);
assert.equal(clip('solo').startBar, 32);
assert.equal(clip('call').startBar, 38);
assert.equal(audio()[31 * 24], 'slap');
assert.equal(audio()[32 * 24], 'bass');
assert.equal(repeat('overlap', 3), true);
assert.equal(clip('solo').startBar, 30);
assert.equal(clip('call').startBar, 36);
assert.equal(move('overlap', 26), true);
assert.equal(clip('solo').startBar, 32);
assert.equal(clip('sangban', 'Sangban').startBar, 24);

// Same-bar Flam/IN order is musical, even if the Solo is stored first.
reset();
const flam = pattern('flam', 'Call', 'Djembe_1', [bar('slap_flam')]);
const incoming = pattern('incoming', 'Solo', 'Djembe_1', [bar('f', [{ type: 'in', stepIndex: 8 }]), bar('bass')]);
incoming.bars[0].notes[8] = 'tone';
state.sourcePatterns.push(flam, incoming);
add(call, 1); add(incoming, 2, 3); add(flam, 2); add(other, 8);
assert.equal(move('flam', 4), true);
assert.equal(clip('incoming').startBar, 4);
assert.equal(clip('other').startBar, 10);
assert.equal(audio()[3 * 24], 'slap_flam');
assert.equal(audio()[3 * 24 + 8], 'tone');
assert.equal(repeat('incoming', 4), true);
assert.equal(clip('flam').startBar, 4, 'Resizing an incoming Solo does not move its preceding Flam');
assert.equal(clip('other').startBar, 12);
assert.equal(repeat('flam', 2), true);
assert.equal(clip('incoming').startBar, 5);
assert.equal(clip('other').startBar, 13);

// Handle preview uses the real final-OUT length and cancellation changes no data.
reset();
add(out, 2, 4); add(call, 9);
const listeners = new Map();
const handle = { addEventListener: (name, callback) => listeners.set(name, callback),
  removeEventListener: name => listeners.delete(name), setPointerCapture() {} };
const styleValues = {};
const card = { draggable: true, closest: () => ({}), style: { setProperty: (key, value) => { styleValues[key] = value; } } };
const outGroup = clip('out').group;
const control = { repeatInfo: ui.getTimelineGroupRepeatInfo(outGroup), inputEl: { value: '4' } };
ui.window.getComputedStyle = () => ({ getPropertyValue: () => '108' });
ui.bindTimelineEntryResizeHandle(handle, card, outGroup, control, false);
const pointer = x => ({ button: 0, pointerType: 'mouse', pointerId: 1, clientX: x, preventDefault() {}, stopPropagation() {} });
const beforeDrag = snapshot();
listeners.get('pointerdown')(pointer(0));
listeners.get('pointermove')(pointer(216));
assert.equal(control.inputEl.value, '5');
assert.equal(styleValues['--timeline-track-bar-count'], '9');
assert.equal(snapshot(), beforeDrag);
listeners.get('pointercancel')();
assert.equal(styleValues['--timeline-track-bar-count'], '7');
assert.equal(control.inputEl.value, '4');
assert.equal(card.draggable, true);
assert.equal(history.length, 0);
listeners.get('pointerdown')(pointer(0));
listeners.get('pointerup')(pointer(216));
assert.equal(clip('out').barCount, 9);
assert.equal(clip('call').startBar, 11);
assert.equal(history.length, 1);

reset();
add(call, 2); add(solo, 999);
const atLimit = snapshot();
assert.equal(move('call', 3), false, 'Reject an out-of-range follower instead of silently clamping its start');
assert.equal(snapshot(), atLimit);
assert.equal(history.length, 0);
console.log('Track ripple: resize, move, gaps, track isolation, shared assignments, segments, IN/OUT/overlap, persistence and undo passed.');
