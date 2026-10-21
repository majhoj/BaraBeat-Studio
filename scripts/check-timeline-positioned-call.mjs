import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {} }, rhythm: 'tenaer',
  titel: { attr: () => 'Djaa Call test' }, getResolvedTimelineLoopCount: () => false,
  normalizePracticeInstrumentVolumes: value => value || {}, practiceState: {} });
installRepeatPickup(ui);
vm.runInContext(source('JS/timeline.js'), ui);
loadFunctions(ui, source('index.php'), ['mapInstrumentNameForPlayer']);
ui.updateTimelineMetadataNode = () => {};
ui.renderTimelinePanel = () => {};
const state = vm.runInContext('timelineState', ui);
let undoEntries;
ui.recordArrangementHistorySnapshot = () => { undoEntries = JSON.stringify(state.entries); };
const bar = (value, controls = []) => ({ notes: [value, ...new Array(23).fill('f')],
  repeat: { start: [], end: [] }, controls });
const pattern = (id, label, target, bars) => ({ id, sourceKey: id, name: id, labelType: label,
  labelName: id, instrument: target.startsWith('Djembe') ? 'Djembe' : target,
  sourceInstrument: target, defaultTargets: [target], bars });
const call = pattern('call', 'Call', 'Djembe_1', [bar('slap')]);
const patterns = [pattern('prefix', 'Call', 'Djembe_1', [bar('tone')]), call,
  pattern('sangban', 'Begleitung', 'Sangban', [bar('Open'), bar('Bell')]),
  pattern('doundoun', 'Begleitung', 'Doundoun', [bar('Bell'), bar('Open')]),
  pattern('djembe', 'Begleitung', 'Djembe_1', [bar('bass')])];
state.sourcePatterns = patterns;
function setup(repeats = 2) {
  state.entries = [ui.cloneTimelineEntryFromPattern(patterns[0]), ui.cloneTimelineEntryFromPattern(call)];
  for (const p of patterns.slice(2, 4)) {
    for (let i = 0; i < repeats; i++) state.entries.push(ui.cloneTimelineEntryFromPattern(p, { parallelGroupId: 'bass-block' }));
  }
  state.entries.push(ui.cloneTimelineEntryFromPattern(call));
  state.accompanimentSegments = [{ id: 'djembe-loop', patternId: 'djembe', patternSourceKey: 'djembe',
    targetInstrument: 'Djembe_1', startBar: 3, barCount: repeats * 2 - 1 }];
}
function layout() {
  return ui.buildTimelineHorizontalLayout(ui.buildTimelineVisualRows(ui.buildTimelineDisplayGroups(state.entries, patterns), patterns));
}
function payload() { return ui.buildTimelinePlayerPayload(patterns, state.entries)[0]; }
const playerText = source('Audio/player.html');
const player = vm.createContext({ isPracticeMode: false, isSheetQuickPlayMode: false,
  rhythmType: 'tenaer', MAX_EXPANDED_BARS: 2000,
  trackInstrumentNames: ['Djembe_1', 'Djembe_2', 'Djembe_3', 'Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'] });
installTiming(player);
installRepeatPickup(player);
vm.runInContext(playerText.slice(playerText.indexOf('function sanitizeRepeatRanges('),
  playerText.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(playerText.slice(playerText.indexOf('function getFeelOffsetSeconds('),
  playerText.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, playerText, ['getSectionLength']);
function audio() {
  const sections = player.buildTimelineSections(payload());
  return Object.fromEntries(['Djembe_1', 'Sangban', 'Doundoun'].map(target => [target,
    sections.flatMap(section => Array.from(player.materializeOrderedSectionTracks(section)[target]))]));
}
function assertBassUnchanged(before, after) {
  for (const target of ['Sangban', 'Doundoun']) assert.deepEqual(after[target].slice(0, 144), before[target].slice(0, 144), target);
}

setup();
const baseline = audio();
const beforeMove = JSON.stringify(state.entries);
const groups = ui.buildTimelineDisplayGroups(state.entries, patterns);
const lastCall = groups.at(-1);
const move = { type: 'timeline-entry-group', startIndex: lastCall.startIndex, count: lastCall.count };
const action = ui.getTimelineRulerBarDropAction(move, layout(), 6);
assert.equal(action?.type, 'positioned-call', 'Call can start inside the second two-bar bass repeat');
ui.executeTimelineRulerBarDrop(move, action);
assert.equal(undoEntries, beforeMove, 'Undo snapshot is taken before changing the host or removing the Call');
let clip = layout().clips.find(clip => clip.pattern.id === 'call' && clip.isOverlay);
assert.equal(clip.startBar, 6);
assert.equal(layout().playbackTotalBars, 6, 'Moving the last Call must not leave an extra bar');
let played = audio();
assert.equal(played.Djembe_1.length, 144);
assert.equal(played.Djembe_1[120], 'slap');
assert.deepEqual(played.Djembe_1.slice(48, 120), baseline.Djembe_1.slice(48, 120), 'Djembe accompaniment stays in bars 3-5');
assertBassUnchanged(baseline, played);

const saved = JSON.parse(JSON.stringify(ui.buildCurrentTimelineSyncOptions().persistedEntries));
state.entries = ui.syncTimelineEntriesWithPatternLibrary(patterns, saved, { appendMissingPatterns: false });
assert.equal(layout().clips.find(clip => clip.pattern.id === 'call' && clip.isOverlay).startBar, 6);
assert.deepEqual(audio(), played, 'Restoring saved entries preserves exact timing');
clip = layout().clips.find(clip => clip.pattern.id === 'call' && clip.isOverlay);
const movedAgain = { type: 'timeline-entry-group', startIndex: clip.group.startIndex, count: clip.group.count };
ui.executeTimelineRulerBarDrop(movedAgain, ui.getTimelineRulerBarDropAction(movedAgain, layout(), 4));
assert.equal(layout().clips.find(clip => clip.pattern.id === 'call' && clip.isOverlay).startBar, 4);
assert.equal(layout().rows.find(row => row.rowGroups.some(g => g.patternId === 'sangban')).startBar, 3,
  'Moving an overlay must not shift the entire bass block');
assert.equal(audio().Djembe_1[72], 'slap');
assertBassUnchanged(baseline, audio());

clip = layout().clips.find(clip => clip.pattern.id === 'call' && clip.isOverlay);
const moveOut = { type: 'timeline-entry-group', startIndex: clip.group.startIndex, count: clip.group.count };
ui.executeTimelineRulerBarDrop(moveOut, ui.getTimelineRulerBarDropAction(moveOut, layout(), 7));
assert.equal(ui.pruneTimelineOrphanOverlayEntries(state.entries).length, state.entries.length,
  'A Call moved back out of its host must not be pruned as an orphan');
assert.equal(state.entries.at(-1).overlayBarOffset, null);
assert.deepEqual(audio(), baseline);

setup();
const add = { type: 'pattern-group', entries: [{ patternId: 'call', targetInstruments: ['Djembe_1'] }] };
for (const startBar of [4, 6]) ui.executeTimelineRulerBarDrop(add, ui.getTimelineRulerBarDropAction(add, layout(), startBar));
assert.equal(payload().TimelinePositionedCalls.length, 2, 'Multiple Calls do not replace another repeat slot');
played = audio();
assert.equal(played.Djembe_1[72], 'slap');
assert.equal(played.Djembe_1[120], 'slap');
assert.equal(played.Djembe_1[144], 'slap', 'Original following Call remains when adding instead of moving');
assertBassUnchanged(baseline, played);

setup(1);
ui.executeTimelineRulerBarDrop(add, ui.getTimelineRulerBarDropAction(add, layout(), 4));
assert.equal(audio().Djembe_1[72], 'slap', 'Works inside a single unrepeated two-bar accompaniment too');

setup();
const moveEarlier = { type: 'timeline-entry-group', startIndex: 1, count: 1 };
ui.executeTimelineRulerBarDrop(moveEarlier, ui.getTimelineRulerBarDropAction(moveEarlier, layout(), 6));
assert.equal(layout().clips.find(clip => clip.pattern.id === 'call' && clip.isOverlay).startBar, 6);
assert.equal(layout().rows.find(row => row.rowGroups.some(group => group.patternId === 'sangban')).startBar, 3);
played = audio();
assert.equal(played.Djembe_1[120], 'slap');
assert(played.Djembe_1.slice(24, 48).every(note => note === 'f'), 'Moving an earlier Call leaves its bar free');
assertBassUnchanged(baseline, played);

setup();
call.bars[0].controls = [{ type: 'out', stepIndex: 8 }, { type: 'in', stepIndex: 22 }];
call.bars[0].notes[8] = 'bass';
call.bars[0].notes[12] = 'tone';
call.bars[0].notes[22] = 'slap';
ui.executeTimelineRulerBarDrop(add, ui.getTimelineRulerBarDropAction(add, layout(), 6));
played = audio();
assert.equal(played.Djembe_1[118], 'slap', 'Call IN is placed just before its start bar');
assert.equal(played.Djembe_1[128], 'bass');
assert.equal(played.Djembe_1[132], 'f', 'Call OUT is respected without shortening other instruments');
assertBassUnchanged(baseline, played);

// Djaa: Solo 1 follows a Call in the last bar of a two-bar bass repetition.
call.bars = [bar('slap')];
call.bars[0].notes[22] = 'tone';
call.bars[0].notes[23] = 'tone';
const solo = pattern('solo', 'Solo 1', 'Djembe_1', [bar('tone', [{ type: 'in', stepIndex: 22 }])]);
solo.bars[0].notes[22] = 'bass';
patterns.push(solo);
setup();
const originalCallGroup = ui.buildTimelineDisplayGroups(state.entries, patterns).at(-1);
for (let i = 0; i < 4; i++) state.entries.push(ui.cloneTimelineEntryFromPattern(solo, { blockId: 'solo-block' }));
const sequential = audio();
const moveBeforeSolo = { type: 'timeline-entry-group', startIndex: originalCallGroup.startIndex, count: originalCallGroup.count };
ui.executeTimelineRulerBarDrop(moveBeforeSolo, ui.getTimelineRulerBarDropAction(moveBeforeSolo, layout(), 6));
assert.equal(payload().TimelinePositionedCalls[0].followingPatternId, '', 'A gap is not an immediate successor');
state.entries.find(entry => entry.patternId === solo.id).gapBeforeBars = 0;
assert.equal(payload().TimelinePositionedCalls[0].followingPatternId, solo.id);
played = audio();
assert.equal(played.Djembe_1[142], 'bass', 'Solo IN belongs at the end of the Call in bar 6');
assert.equal(played.Djembe_1[143], 'f', 'Rests after IN replace remaining Call notes too');
assert.deepEqual(played.Djembe_1.slice(120, 144), sequential.Djembe_1.slice(144, 168),
  'Positioned and sequential Calls must have the same incoming pickup');
assert.deepEqual(played.Djembe_1.slice(144), sequential.Djembe_1.slice(168),
  'All four Solo repetitions must remain unchanged');
assertBassUnchanged(sequential, played);
state.entries = ui.syncTimelineEntriesWithPatternLibrary(patterns,
  JSON.parse(JSON.stringify(ui.buildCurrentTimelineSyncOptions().persistedEntries)), { appendMissingPatterns: false });
assert.deepEqual(audio(), played, 'Incoming pickup also survives save/reload');

state.accompanimentSegments = [];
played = audio();
assert.equal(played.Djembe_1[118], 'f', 'No stray Solo IN one bar early inside the bass section');
assert.equal(played.Djembe_1[142], 'bass');
solo.bars[0].controls = [];
assert.equal(audio().Djembe_1[142], 'tone', 'Without a following IN the Call must not be modified');
solo.bars = [bar('f', [{ type: 'in', stepIndex: 22 }]), bar('bass')];
solo.bars[0].notes[22] = 'slap_flam';
played = audio();
assert.equal(played.Djembe_1[142], 'slap_flam', 'Separate pickup bar also leads into the following Solo');
assert.equal(played.Djembe_1[144], 'bass', 'The separate pickup bar is consumed, not played again');
for (let i = 0; i < 4; i++) assert.equal(played.Djembe_1[144 + i * 48], 'bass', 'Only the first pickup bar is shared; later Solo repetitions stay complete');
assertBassUnchanged(sequential, played);

console.log('Positioned Calls: bar 6, add/move, continuous bass repeats, single passes, multiple Calls, undo snapshot, persistence, IN/OUT, following Solo pickup and moving out checked.');
