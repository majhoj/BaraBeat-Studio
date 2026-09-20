import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming } from './helpers/music-context.mjs';

const editor = source('index.php');
const noOp = () => {};
const overlays = [];
const context = vm.createContext({
  rhythm: 'tenaer', uiText: (key, args) => key + (args?.number || ''),
  sheetQuickPlayState: { patternLibrary: [], selectedPatternIds: [] }, sheetPatternMoveState: {},
  timelineState: { sourcePatterns: [] }, window: { requestAnimationFrame: noOp },
  stopSheetQuickPlay: noOp, removeCanvasElements: noOp, buildCurrentTimelineSyncOptions: () => ({}),
  getSheetPatternMoveRanges: () => [], syncTimelineStateFromReadResultIfNeeded: noOp,
  getChooserDisplayText: value => value, cloneTimelineRepeatMarkers: value => value,
  getSheetBarBounds: () => ({ x: 0, y: 0, width: 100, height: 100 }),
  rebuildSheetQuickPlayNoteElementMap: noOp, updateSheetQuickPlaySelectionClasses: noOp,
  scheduleSheetQuickPlayPreparation: noOp, positionSheetQuickPlayControls: noOp,
  s: { rect: () => ({ attr(value) { overlays.push(value); return this; }, click: noOp }) }
});
installTiming(context);
loadFunctions(context, editor, ['renderSheetQuickPlaySelectors', 'isSheetQuickPlayPlayableNote',
  'normalizeSheetQuickPlayTargetInstrument', 'mapLabelForPlayer', 'getPlayerLabelInfo', 'getReadRhythmConfig']);
const notes = new Array(24).fill('f');
notes[6] = 'tone';
const draft = { index: 1, instrument: 'Djembe 1', notes, controls: [], repeat: {} };
const input = { rhythmBars: [draft, { ...draft, index: 2, notes: new Array(24).fill('f') },
  { ...draft, index: 3, instrument: '' }] };
const original = JSON.stringify(input);
context.renderSheetQuickPlaySelectors(input);
const patterns = context.sheetQuickPlayState.patternLibrary;
assert.equal(patterns.length, 1, 'Only a bar with an instrument AND notes can play');
assert.equal(patterns[0].isQuickPlayDraft, true);
assert.equal(patterns[0].bars.length, 1, 'Unnamed bar stays a single-bar pattern');
assert.equal(patterns[0].bars[0].notes[6], 'tone');
assert.deepEqual(Array.from(patterns[0].defaultTargets), ['Djembe_1']);
assert.equal(overlays.filter(item => item['aria-disabled'] === 'true').length, 2);
assert.equal(JSON.stringify(input), original, 'Draft creation must not mutate the score');
context.sheetQuickPlayState.selectedPatternIds = [patterns[0].id];
context.renderSheetQuickPlaySelectors(input);
assert.deepEqual(Array.from(context.sheetQuickPlayState.selectedPatternIds), [patterns[0].id]);

// Feed the draft through the real quick-play preparation and section builder as well.
vm.runInContext(editor.slice(editor.indexOf('function buildSheetQuickPlayRepeatRanges('),
  editor.indexOf('function createSheetPatternMoveOverlayButton(')), context);
loadFunctions(context, editor, ['getSheetQuickPlayPositionKey']);
const practice = source('JS/practice.js');
context.practiceTrackInstrumentNames = context.getSheetQuickPlayTrackNames();
vm.runInContext(practice.slice(practice.indexOf('function createEmptyPracticeTrackNotes('),
  practice.indexOf('function notifyPracticeHandModeChanged(')), context);
const sections = context.buildSheetQuickPlayConfiguredSections(patterns.map(context.buildSheetQuickPlayPreparedPattern));
assert.equal(sections.length, 1);
assert.equal(sections[0].trackNotes.Djembe_1.length, 24);
assert.equal(sections[0].trackNotes.Djembe_1[6], 'tone');
console.log('Quick-play drafts: playable unnamed single bar, disabled incomplete bars, selection retention and exact note position checked.');
