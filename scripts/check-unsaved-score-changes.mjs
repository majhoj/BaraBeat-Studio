import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

const text = source('index.php');
const clone = value => JSON.parse(JSON.stringify(value));
const initial = { title: 'Test', rhythm: 'binaer', lineCount: 10, elements: ['note'],
  settings: { tempo: 100, persistedPractice: { repeatCount: 2 }, persistedEntries: [] } };
let score = clone(initial);
let approved = false;
let confirmations = 0;
let loads = 0;
let historyCount = 0;
let write;
let failSave = false;
const listeners = {};
const context = vm.createContext({
  console,
  savedScoreSnapshot: null,
  scoreDocumentGeneration: 0,
  currentScoreId: 'score-1',
  loadedTitle: '',
  window: {
    confirm() { confirmations++; return approved; },
    addEventListener(name, handler) { listeners[name] = handler; }
  },
  uiText: key => key,
  getCurrentScoreSnapshot: () => clone(score),
  getCurrentRhythmTitle: () => score.title,
  isDefaultRhythmTitle: title => !title,
  setRhythmTitle: title => { score.title = title; },
  titel: { attr: () => score.title },
  recordHistorySnapshot: () => { historyCount++; },
  drawRhythmSheet: config => { score = { ...clone(initial), title: '', elements: [], rhythm: config.rhythmName }; },
  buildSerializedRhythm: () => JSON.stringify(score),
  setIoFieldValue() {},
  rememberLastLoadedScore() {},
  setSelectedFileSource() {},
  refreshFileList: async () => {},
  localLibrary: {
    rootFolderId: 'root',
    async getScore() { return { id: 'score-1', folderId: 'root' }; },
    async saveScore(value) {
      if (failSave) throw new Error('Storage failure');
      return write ? write(value) : { ...value, id: value.id || 'copy-1' };
    }
  },
  Snap: { loadStr: (content, callback) => { loads++; score = JSON.parse(content); callback(); } },
  onSVGLoaded: () => { context.rememberSavedScoreSnapshot(); }
});
loadFunctions(context, text, [
  'scoreSnapshotSignature', 'rememberSavedScoreSnapshot', 'hasUnsavedScoreChanges',
  'confirmDiscardScoreChanges', 'loadRhythmContent', 'saveCurrentScoreLocal',
  'viererNoten', 'dreierNoten', 'neunerNoten', 'openLocalScore', 'importServerScore'
]);
const unloadStart = text.indexOf("window.addEventListener('beforeunload'");
const unloadEnd = text.indexOf('\n});', unloadStart) + 4;
assert(unloadStart >= 0 && unloadEnd > unloadStart);
vm.runInContext(text.slice(unloadStart, unloadEnd), context);

assert.equal(context.hasUnsavedScoreChanges(), false, 'No document baseline at startup');
context.rememberSavedScoreSnapshot();
assert.equal(context.hasUnsavedScoreChanges(), false);
score.settings.persistedPractice.repeatCount++;
assert.equal(context.hasUnsavedScoreChanges(), true, 'Practice changes are part of the score');
score.settings.persistedPractice.repeatCount--;
assert.equal(context.hasUnsavedScoreChanges(), false, 'Undo to baseline is clean');
score.settings.persistedEntries.push({ patternId: 'P1' });
assert.equal(context.hasUnsavedScoreChanges(), true, 'Arrangement changes are part of the score');
assert.equal(context.loadRhythmContent('Other', JSON.stringify(initial), 'score-2'), false);
assert.equal(context.currentScoreId, 'score-1');
assert.equal(loads, 0, 'Cancelled load must not touch the canvas');
for (const name of ['viererNoten', 'dreierNoten', 'neunerNoten']) {
  assert.equal(context[name](), false);
}
assert.equal(historyCount, 0, 'Cancelled new score must not change undo history');
assert.equal(await context.openLocalScore('score-2'), null);
assert.equal(await context.importServerScore('server.bbs'), null, 'Cancel before network or local writes');
let prevented = false;
const unloadEvent = { preventDefault() { prevented = true; }, returnValue: null };
listeners.beforeunload(unloadEvent);
assert(prevented);
assert.equal(unloadEvent.returnValue, '');

approved = true;
assert.equal(context.loadRhythmContent('Other', JSON.stringify(initial), 'score-2'), true);
assert.equal(context.currentScoreId, 'score-2');
assert.equal(context.hasUnsavedScoreChanges(), false);
const baselineSignature = context.scoreSnapshotSignature(score);
score.settings.tempo = 105;
const approvedSignature = context.scoreSnapshotSignature(score);
approved = false;
let confirmationCount = confirmations;
assert.equal(context.confirmDiscardScoreChanges(approvedSignature), true);
assert.equal(confirmations, confirmationCount, 'No duplicate confirmation for the same approved contents');
score.settings.tempo = 110;
assert.equal(context.confirmDiscardScoreChanges(approvedSignature), false, 'New edits invalidate previous approval');
assert.notEqual(context.scoreSnapshotSignature(score), baselineSignature);

failSave = true;
await assert.rejects(context.saveCurrentScoreLocal(), /Storage failure/);
assert.equal(context.hasUnsavedScoreChanges(), true, 'Failed save never clears changes');
failSave = false;
await context.saveCurrentScoreLocal('Renamed');
assert.equal(score.title, 'Renamed');
assert.equal(context.hasUnsavedScoreChanges(), false, 'Successful local save updates baseline');
prevented = false;
listeners.beforeunload(unloadEvent);
assert.equal(prevented, false, 'No unload warning for saved contents');

let finishWrite;
write = value => new Promise(resolve => { finishWrite = () => resolve({ ...value, id: 'saved-id' }); });
score.title = 'Before save';
const saving = context.saveCurrentScoreLocal();
await new Promise(resolve => setImmediate(resolve));
score.title = 'Edited during save';
score.settings.persistedPractice.repeatCount = 9;
finishWrite();
await saving;
assert.equal(score.title, 'Edited during save', 'Do not overwrite a newer title after async save');
assert.equal(context.hasUnsavedScoreChanges(), true, 'Edits during an async save remain unsaved');

const savingPreviousDocument = context.saveCurrentScoreLocal();
await new Promise(resolve => setImmediate(resolve));
approved = true;
context.loadRhythmContent('New document', JSON.stringify(initial), 'new-id');
finishWrite();
await savingPreviousDocument;
assert.equal(context.currentScoreId, 'new-id', 'A completed old save must not replace the current document identity');
assert.equal(context.hasUnsavedScoreChanges(), false, 'A completed old save must not overwrite the new baseline');
write = null;
assert.equal(context.dreierNoten(), true);
assert.equal(context.hasUnsavedScoreChanges(), false, 'Untouched new sheet starts clean');

assert.equal(context.scoreSnapshotSignature({ a: 1, b: { c: 2, sourceHash: 'old' } }),
  context.scoreSnapshotSignature({ b: { sourceHash: 'new', c: 2 }, a: 1 }),
  'Readout hashes and property order are not edits');
assert(!text.slice(text.indexOf('function get_value('), text.indexOf('(async function initializeInitialScore'))
  .includes('removeCanvasElements('), 'Legacy file selector may not erase the canvas before confirmation');
assert(source('app-shell.html').includes('function confirmDiscardScoreChanges('), 'Offline shell contains the same guard');
for (const language of ['de', 'en', 'fr', 'es', 'pt']) {
  assert(JSON.parse(source('languages/' + language + '.json')).file.confirm.discardUnsaved,
    'Missing warning translation: ' + language);
}
console.log('Unsaved score changes: load/new cancellation, all modes, unload, save failures, concurrent edits, document changes and translations checked.');
