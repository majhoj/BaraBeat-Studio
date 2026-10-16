import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {} }, rhythm: 'tenaer',
  titel: { attr: () => 'Independent tracks' }, normalizePracticeInstrumentVolumes: () => ({}), practiceState: {} });
vm.runInContext(source('JS/timeline.js'), ui);
ui.updateTimelineMetadataNode = () => {};
ui.renderTimelinePanel = () => {};
const state = vm.runInContext('timelineState', ui);
let undo;
ui.recordArrangementHistorySnapshot = () => { undo = JSON.stringify(state.entries); };
const bar = (note, controls = []) => ({ notes: [note, ...Array(23).fill('f')], controls, repeat: { start: [], end: [] } });
const pattern = (id, label, target, bars) => ({ id, sourceKey: id, name: id, labelName: id, labelType: label,
  instrument: target.startsWith('Djembe') ? 'Djembe' : target, sourceInstrument: target, defaultTargets: [target], bars });
const prefix = pattern('prefix', 'Call', 'Djembe_1', [bar('tone')]);
const call = pattern('call', 'Call', 'Djembe_1', [bar('slap')]);
const sangban = pattern('sangban', 'Begleitung', 'Sangban', [bar('Open'), bar('Bell')]);
const bass = pattern('bass', 'Begleitung', 'Doundoun', [bar('Open'), bar('Bell')]);
const solo = pattern('solo', 'Solo', 'Djembe_1', [bar('tone', [{ type: 'in', stepIndex: 22 }])]);
solo.bars[0].notes[22] = 'bass';
const flam = pattern('flam', 'Call', 'Djembe_1', [bar('slap_flam')]);
const inSolo = pattern('in-solo', 'Solo', 'Djembe_1', [bar('f', [{ type: 'in', stepIndex: 8 }]), bar('f')]);
inSolo.bars[0].notes[8] = 'slap_flam';
inSolo.bars[0].notes[12] = 'tone';
state.sourcePatterns = [prefix, call, sangban, bass, solo, flam, inSolo];
function add(p, count = 1, overrides = {}) {
  const blockId = ui.nextTimelineBlockId();
  for (let i = 0; i < count; i++) state.entries.push(ui.cloneTimelineEntryFromPattern(p, { blockId, ...overrides }));
}
add(prefix); add(call);
add(sangban, 2, { parallelGroupId: 'bass-section' });
add(bass, 2, { parallelGroupId: 'bass-section' });
add(call, 1, { parallelGroupId: 'bass-section', overlayRepeatIndex: 0, overlayBarOffset: 3 });
add(solo, 4); add(call); add(flam); add(inSolo, 3);
const layout = () => ui.getCurrentTimelineLayout();
const clip = id => layout().clips.find(c => c.pattern.id === id);
const positions = () => Array.from(layout().clips, c => [c.pattern.id, c.targetInstrument, c.startBar, c.barCount]);
const legacyPositions = positions();
const legacyPayload = ui.buildTimelinePlayerPayload(state.sourcePatterns, state.entries)[0];
ui.migrateTimelinePositions();
assert.deepEqual(positions(), legacyPositions, 'Migration preserves all displayed positions and durations');
const saved = JSON.stringify(ui.buildCurrentTimelineSyncOptions().persistedEntries);
state.entries = ui.syncTimelineEntriesWithPatternLibrary(state.sourcePatterns, JSON.parse(saved), { appendMissingPatterns: false });
assert.deepEqual(positions(), legacyPositions, 'Positions survive save/reload');
const player = vm.createContext({ isPracticeMode: false, isSheetQuickPlayMode: false, rhythmType: 'tenaer', MAX_EXPANDED_BARS: 2000,
  trackInstrumentNames: ['Djembe_1', 'Djembe_2', 'Djembe_3', 'Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'] });
installTiming(player); installRepeatPickup(player);
const text = source('Audio/player.html');
vm.runInContext(text.slice(text.indexOf('function sanitizeRepeatRanges('), text.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(text.slice(text.indexOf('function getFeelOffsetSeconds('), text.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, text, ['getSectionLength']);
function audio(config = ui.buildTimelinePlayerPayload(state.sourcePatterns, state.entries)[0]) {
  const sections = player.buildTimelineSections(config);
  return Object.fromEntries(['Djembe_1', 'Sangban', 'Doundoun'].map(target => [target,
    Array.from(sections).flatMap(s => Array.from(player.materializeOrderedSectionTracks(s)[target]))]));
}
assert.deepEqual(audio(), audio(legacyPayload), 'Migration preserves IN, repetitions, silent bars and Call audio');
const before = audio();
const sangbanClip = clip('sangban');
ui.setTimelineGroupRepeatCount(sangbanClip.group, ui.getTimelineGroupRepeatInfo(sangbanClip.group), 3);
assert.equal(clip('sangban').barCount, 6);
assert.equal(clip('solo').startBar, 7);
assert.equal(clip('solo').barCount, 4);
assert.equal(layout().clips.filter(c => c.pattern.id === 'call').at(-1).startBar, 11);
assert.deepEqual(audio().Djembe_1, before.Djembe_1, 'Extending Sangban leaves all Djembe audio unchanged');
assert.deepEqual(audio().Sangban.slice(48, 192), Array.from({ length: 3 },
  () => ['Open', ...Array(23).fill('f'), 'Bell', ...Array(23).fill('f')]).flat());
const moving = clip('solo').group;
assert.equal(ui.placeTimelinePayload({ type: 'timeline-entry-group', startIndex: moving.startIndex, count: moving.count }, 7), true);
assert.equal(clip('solo').group.count, 4);
assert.equal(clip('solo').isOverlay, false);
assert.equal(ui.getTimelineRulerBarDropAction({ type: 'timeline-entry-group', startIndex: moving.startIndex, count: moving.count }, layout(), 6), null,
  'A Call already occupies bar 6 on the same instrument');
const preConflict = JSON.stringify(state.entries);
assert.equal(ui.setTimelineGroupRepeatCount(clip('solo').group, ui.getTimelineGroupRepeatInfo(clip('solo').group), 5), false);
assert.equal(JSON.stringify(state.entries), preConflict, 'Rejected resize does not modify entries');
ui.setTimelineGroupRepeatCount(clip('solo').group, ui.getTimelineGroupRepeatInfo(clip('solo').group), 2);
assert.equal(clip('solo').barCount, 2);
state.entries = JSON.parse(undo);
assert.equal(clip('solo').barCount, 4, 'Undo restores all repetitions');
assert.equal(clip('in-solo').startBar, 12);
assert.equal(clip('in-solo').barCount, 6);
assert.equal(audio().Djembe_1[11 * 24 + 8], 'slap_flam');
assert.equal(audio().Djembe_1[13 * 24 + 8], 'slap_flam');

// A final OUT cuts only the final pass; the next block keeps its absolute start.
state.entries = [];
const outSolo = pattern('out-solo', 'Solo', 'Djembe_1', [bar('tone', [{ type: 'out', stepIndex: 8 }]), bar('bass')]);
outSolo.bars[0].notes[8] = 'slap';
outSolo.bars[0].notes[12] = 'bass';
state.sourcePatterns.push(outSolo);
add(outSolo, 4, { startBar: 3 }); add(call, 1, { startBar: 10 });
assert.equal(clip('out-solo').barCount, 7);
assert.equal(audio().Djembe_1[8 * 24 + 8], 'slap');
assert.equal(audio().Djembe_1[8 * 24 + 12], 'f');
assert.equal(audio().Djembe_1[9 * 24], 'slap');
assert(audio().Djembe_1.slice(0, 48).every(n => n === 'f'), 'Empty opening bars remain empty');

// A one-bar overlap keeps its visible clip but shares the successor's first bar.
state.entries = [];
flam.bars[0].controls = [{ type: 'overlap', stepIndex: 0 }];
add(call, 1, { startBar: 1 }); add(flam, 1, { startBar: 2 }); add(solo, 4, { startBar: 2 });
assert.equal(clip('flam').barCount, 1);
assert.equal(clip('flam').shouldHandOffFinalOverlap, true);
assert.equal(layout().playbackTotalBars, 5);
assert.equal(audio().Djembe_1[24], 'slap_flam');
assert.equal(audio().Djembe_1[48], 'tone');
assert.equal(ui.placeTimelinePayload({ type: 'timeline-entry-group', startIndex: clip('flam').group.startIndex, count: 1 }, 2), true,
  'Moving an overlapping clip to its existing bar is safe');

// Internal and final overlaps of a repeated two-bar pattern are both retained.
state.entries = [];
const overlapSolo = pattern('overlap-solo', 'Solo', 'Djembe_1', [bar('tone'), bar('slap', [{ type: 'out', stepIndex: 0 }, { type: 'overlap', stepIndex: 0 }])]);
state.sourcePatterns.push(overlapSolo);
add(overlapSolo, 3, { startBar: 1 }); add(call, 1, { startBar: 4 });
assert.equal(clip('overlap-solo').barCount, 3);
assert.equal(layout().playbackTotalBars, 4);
assert.deepEqual([0,24,48,72].map(i=>audio().Djembe_1[i]), ['tone','slap','slap','slap']);

// Djaa Solo 5: its final overlap is intrinsic, not conditional on an already aligned successor.
state.entries = [];
const solo5 = pattern('solo-5', 'Solo', 'Djembe_1', [bar('tone'), bar('bass'),
  bar('slap', [{ type: 'out', stepIndex: 0 }, { type: 'overlap', stepIndex: 0 }])]);
const solo6 = pattern('solo-6', 'Solo', 'Djembe_1', [bar('tone'), bar('bass')]);
state.sourcePatterns.push(solo5, solo6);
add(solo5, 3, { startBar: 24 }); add(solo6, 3, { startBar: 31 }); add(call, 1, { startBar: 37 });
add(sangban, 3, { startBar: 24 });
assert.equal(clip('solo-5').barCount, 6, 'A later successor must not make the overlap count as a seventh Solo bar');
assert.equal(clip('solo-5').shouldHandOffFinalOverlap, true);
assert.equal(clip('solo-6').startBar, 31, 'Saved absolute positions are not silently shifted');
assert.equal(audio().Djembe_1[29 * 24], 'slap', 'Outgoing note is retained in bar 30 even with a gap before Solo 6');
state.entries = ui.syncTimelineEntriesWithPatternLibrary(state.sourcePatterns,
  JSON.parse(JSON.stringify(ui.buildCurrentTimelineSyncOptions().persistedEntries)), { appendMissingPatterns: false });
assert.equal(clip('solo-5').barCount, 6, 'Overlap duration survives save/reload');
const solo6Group = clip('solo-6').group;
assert.equal(ui.placeTimelinePayload({ type: 'timeline-entry-group', startIndex: solo6Group.startIndex, count: solo6Group.count }, 30), true);
assert.equal(clip('solo-5').barCount, 6);
assert.equal(clip('solo-6').group.count, 3);
assert.equal(clip('solo-6').barCount, 6);
assert.equal(clip('call').startBar, 37);
assert.equal(clip('sangban').startBar, 24);
assert.equal(audio().Djembe_1[29 * 24], 'slap', 'Final overlap sounds in Solo 6 first bar');
assert.equal(audio().Djembe_1[30 * 24], 'bass');
state.entries = state.entries.filter(entry => entry.patternId === solo5.id || entry.patternId === sangban.id);
assert.equal(clip('solo-5').barCount, 7, 'Without a successor on its own track the final sounding bar remains part of Solo 5');
assert.equal(audio().Djembe_1.at(-24), 'slap');

// Explicit accompaniment segments reject collisions without moving neighbours.
state.entries = []; state.accompanimentSegments = [];
add(call, 1, { startBar: 5 });
const accompaniment = pattern('djembe-loop', 'Begleitung', 'Djembe_1', [bar('bass')]);
state.sourcePatterns.push(accompaniment);
ui.addTimelineAccompanimentSegment({ type: 'pattern', patternId: accompaniment.id }, 'Djembe_1', 2);
const segment = state.accompanimentSegments[0];
ui.updateTimelineAccompanimentSegment(segment, { barCount: '3' });
assert.equal(segment.barCount, 3);
assert.equal(ui.updateTimelineAccompanimentSegment(segment, { barCount: '4' }), false);
assert.equal(segment.barCount, 3);
assert.equal(clip('call').startBar, 5);
assert.equal(ui.getTimelineAppendBar({ type: 'pattern', patternId: call.id }, layout()), 6);

// Starting on a Solo retains the IN immediately preceding it.
state.entries = []; state.accompanimentSegments = [];
add(call, 1, { startBar: 6 }); add(solo, 4, { startBar: 7 });
const sections = player.buildTimelineSections(ui.buildTimelinePlayerPayload(state.sourcePatterns, state.entries)[0]);
let offset = 0;
sections.forEach(s => { s.startStep = offset; offset += s.playbackLength; s.endStep = offset; });
loadFunctions(player, text, ['getTimelinePlaybackStartStep', 'getTimelinePlaybackBarForStep']);
assert.equal(player.getTimelinePlaybackStartStep(sections, 7), 142);
assert.equal(player.getTimelinePlaybackBarForStep(sections, 144), 7);
assert.equal(offset, 240, 'Playback ends with the last occupied bar');

state.entries = [];
flam.bars[0].controls = [];
add(flam, 1, { startBar: 2 });
const incoming = { type: 'pattern', patternId: inSolo.id };
assert.equal(ui.getTimelineAppendBar(incoming, layout()), 2);
assert.equal(ui.placeTimelinePayload(incoming, 2), true, 'A longer IN clip can share the shorter preceding clip');
assert.equal(audio().Djembe_1[24], 'slap_flam');
assert.equal(audio().Djembe_1[32], 'slap_flam');

state.entries = [];
const short = pattern('short', 'Call', 'Djembe_1', [bar('tone', [{ type: 'shortbar', stepIndex: 6 }])]);
short.bars[0].notes[6] = 'bass';
state.sourcePatterns.push(short);
add(short, 2, { startBar: 1 }); add(call, 1, { startBar: 3 });
assert.equal(clip('call').startBar, 3);
assert.equal(audio().Djembe_1.length, 36, 'Two shortened bars precede the full Call without extra silence');
assert.equal(audio().Djembe_1[6], 'tone');
assert.equal(audio().Djembe_1[12], 'slap');
console.log('Independent tracks: legacy migration/audio, persistence, Sangban resize, Solo move/repeats, collisions, undo and shared IN checked.');
