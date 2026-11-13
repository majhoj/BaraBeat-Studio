import assert from 'node:assert/strict';
import vm from 'node:vm';
import { createRequire } from 'node:module';
import { source, loadFunctions } from './helpers/music-context.mjs';

function element(id = '') {
  const classes = new Set();
  return { dataset: { id }, hidden: false, value: '', handlers: {},
    classList: { add: (...names) => names.forEach(n => classes.add(n)),
      remove: (...names) => names.forEach(n => classes.delete(n)), contains: n => classes.has(n) },
    contains: value => value === id,
    addEventListener(name, handler) { this.handlers[name] = handler; }
  };
}
const dialog = element(), nameInput = element(), search = element();
const row = element('score'), target = element('folder');
const state = { source: 'local', mode: 'open', serverNoticeRequest: 0, entries: [], selectedId: null, filter: 'all' };
let refreshes = 0, writes = 0, finishMove;
const messages = [], alerts = [];
const context = vm.createContext({
  fileDialogDragState: null, fileDialogMoveInProgress: false,
  fileDialogScoreDragType: 'application/x-barabeat-local-score', fileDialogState: state,
  document: { querySelector: selector => ({ '#fileDialog': dialog, '#fileDialogName': nameInput, '#fileDialogSearch': search })[selector],
    querySelectorAll: () => [row, target] },
  localLibrary: { rootFolderId: 'root', moveScore: async () => { writes++; return new Promise(resolve => { finishMove = resolve; }); } },
  updateFileDialogControls() {}, updateFileDialogRowSelection() {}, setFileDialogServerNotice() {},
  refreshFileDialogEntries: async () => { refreshes++; },
  showAutoDismissMessage: value => messages.push(value), alert: value => alerts.push(value),
  uiText: (key, values) => key + JSON.stringify(values)
});
const names = ['clearFileDialogDrag', 'canDropFileDialogScore', 'bindFileDialogDropTarget',
  'bindFileDialogScoreDrag', 'moveFileDialogScore', 'getFilteredFileDialogEntries'];
loadFunctions(context, source('index.php'), names);
const offline = vm.createContext({});
loadFunctions(offline, source('app-shell.html'), names);
for (const name of names) assert.equal(context[name].toString(), offline[name].toString(), 'Offline parity: ' + name);
function event() {
  const data = new Map();
  return { prevented: false, stopped: false, relatedTarget: null,
    preventDefault() { this.prevented = true; }, stopPropagation() { this.stopped = true; },
    dataTransfer: { setData: (type, value) => data.set(type, value), getData: type => data.get(type) || '' } };
}
context.bindFileDialogScoreDrag(row, { id: 'score', folderId: 'root', title: 'Djaa' });
context.bindFileDialogDropTarget(target, 'folder');
const dragged = event();
row.handlers.dragstart(dragged);
assert.equal(row.draggable, true);
assert.equal(state.selectedId, 'score');
assert.equal(dragged.dataTransfer.effectAllowed, 'move');
assert.equal(dragged.dataTransfer.getData(context.fileDialogScoreDragType), 'score');
assert(context.canDropFileDialogScore('folder'));
assert(!context.canDropFileDialogScore('root'), 'Same-folder drop is not offered');
target.handlers.dragover(dragged);
assert(dragged.prevented && target.classList.contains('is-file-drop-target'));
target.handlers.dragleave(event());
assert(!target.classList.contains('is-file-drop-target'));
const pending = context.moveFileDialogScore('score', 'folder');
assert(context.fileDialogMoveInProgress);
assert(!context.canDropFileDialogScore('folder'));
await context.moveFileDialogScore('score', 'other');
assert.equal(writes, 1, 'Do not start competing UI moves');
finishMove({ id: 'score', title: 'Djaa' });
await pending;
assert.equal(context.fileDialogMoveInProgress, false);
assert.equal(state.selectedId, null);
assert.equal(refreshes, 1);
assert.equal(messages.length, 1);
row.handlers.dragend();
assert.equal(context.fileDialogDragState, null);
assert(!row.classList.contains('is-file-dragging'));
const rejectedDrop = event();
target.handlers.drop(rejectedDrop);
assert.equal(rejectedDrop.prevented, false, 'External drags without the local source state are ignored');
row.handlers.dragstart(event());
target.handlers.drop(event());
assert.equal(context.fileDialogDragState, null, 'Mismatched transferred IDs are cleared without writing');
assert.equal(writes, 1);
for (const changes of [{ source: 'server' }, { mode: 'export' }]) {
  Object.assign(state, { source: 'local', mode: 'open' }, changes);
  const forbidden = event();
  row.handlers.dragstart(forbidden);
  assert(forbidden.prevented);
  assert(!context.canDropFileDialogScore('folder'));
  await context.moveFileDialogScore('score', 'folder');
  assert.equal(writes, 1);
}
Object.assign(state, { source: 'local', mode: 'open', selectedId: 'score' });
context.localLibrary.moveScore = async () => { throw new Error('Folder disappeared'); };
await context.moveFileDialogScore('score', 'missing');
assert.equal(state.selectedId, 'score', 'Errors keep the source selection');
assert.equal(context.fileDialogMoveInProgress, false);
assert.equal(alerts.length, 1);
state.entries = [{ entryType: 'parent-folder', title: '..' }, { entryType: 'score', title: 'Djaa' }];
search.value = 'djaa';
assert.equal(context.getFilteredFileDialogEntries().length, 2, 'Search keeps the parent-folder drop target');
for (const language of ['de', 'en', 'fr', 'es', 'pt']) {
  const file = JSON.parse(source('languages/' + language + '.json')).file;
  assert(file.message.movedLocal.includes('{title}'));
  assert(file.error.localMove.includes('{message}'));
}
console.log('File drag: local-only source/targets, feedback, cancellation, same-folder guard, busy lock, failure handling, search, five languages and offline parity checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    const errors = [], warnings = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', async d => {
      warnings.push(d.message());
      if (d.type() === 'beforeunload') await d.accept();
      else await d.dismiss();
    });
    const base = process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/';
    await page.goto(new URL('app-shell.html', base).href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    const fixture = await page.evaluate(async content => {
      const folder = await localLibrary.createFolder('Proben');
      const nested = await localLibrary.createFolder('Unterordner', folder.id);
      const other = await localLibrary.createFolder('Andere Rhythmen');
      const score = await localLibrary.saveScore({ title: 'Djaa zum Verschieben', content,
        tags: ['probe'], customMetadata: { keep: true } });
      const published = await localLibrary.markPublished(score.id, 'Djaa.bbs', 'test-token');
      if (!loadRhythmContent(published.title, content, score.id, { remember: false })) throw new Error('Fixture load failed');
      setRhythmTitle('Ungespeicherte Aenderung');
      practiceState.repeatCount += 1;
      const snapshot = scoreSnapshotSignature(getCurrentScoreSnapshot());
      openFileDialog('open');
      return { folder, nested, other, score: published, snapshot };
    }, source('Noten/Balakulania SMF.bbs'));
    const rowFor = id => page.locator('#fileDialogList tr[data-id="' + id + '"]');
    async function waitForFolder(folderId) {
      await page.waitForFunction(id => fileDialogState.folderId === id && !fileDialogMoveInProgress, folderId);
    }
    async function dragTo(scoreId, destination, targetId) {
      await rowFor(scoreId).dragTo(destination);
      await page.waitForFunction(async ({ scoreId, targetId }) => !fileDialogMoveInProgress &&
        (await localLibrary.getScore(scoreId)).folderId === targetId, { scoreId, targetId });
      assert.equal(await page.locator('#fileDialog .is-file-dragging, #fileDialog .is-file-drop-target').count(), 0);
    }
    async function assertPreserved(folderId) {
      const actual = await page.evaluate(async id => ({ score: await localLibrary.getScore(id), id: currentScoreId,
        snapshot: scoreSnapshotSignature(getCurrentScoreSnapshot()), dirty: hasUnsavedScoreChanges() }), fixture.score.id);
      assert.deepEqual(actual.score, { ...fixture.score, folderId }, 'Only folderId changes, including for published files');
      assert.equal(actual.id, fixture.score.id, 'The active editor identity is unchanged');
      assert.equal(actual.snapshot, fixture.snapshot, 'Unsaved score, practice and arrangement stay unchanged');
      assert(actual.dirty);
    }
    await rowFor(fixture.score.id).waitFor();
    assert.equal(await rowFor(fixture.folder.id).getAttribute('draggable'), null, 'Folders are targets, not movable files');
    await dragTo(fixture.score.id, rowFor(fixture.folder.id), fixture.folder.id);
    await assertPreserved(fixture.folder.id);
    await rowFor(fixture.folder.id).dblclick();
    await waitForFolder(fixture.folder.id);
    await dragTo(fixture.score.id, rowFor('__parent__'), 'root');
    await assertPreserved('root');
    await rowFor('__parent__').dblclick();
    await waitForFolder('root');
    await dragTo(fixture.score.id, rowFor(fixture.folder.id), fixture.folder.id);
    await rowFor(fixture.folder.id).dblclick();
    await waitForFolder(fixture.folder.id);
    await dragTo(fixture.score.id, rowFor(fixture.nested.id), fixture.nested.id);
    await rowFor(fixture.nested.id).dblclick();
    await waitForFolder(fixture.nested.id);
    await dragTo(fixture.score.id, page.locator('.file-dialog-source[data-source="local"]'), 'root');
    await assertPreserved('root');
    await page.evaluate(async () => navigateFileDialogFolder(localLibrary.rootFolderId));

    const duplicate = await page.evaluate(async ({ folder, score }) => localLibrary.saveScore({
      title: score.title.toUpperCase(), folderId: folder.id, content: 'must not overwrite'
    }), fixture);
    await rowFor(fixture.score.id).dragTo(rowFor(fixture.folder.id));
    await page.waitForFunction(() => !fileDialogMoveInProgress);
    assert.equal(warnings.length, 1, 'A name conflict is reported');
    await assertPreserved('root');
    assert.equal(await page.evaluate(async id => (await localLibrary.getScore(id)).content, duplicate.id), 'must not overwrite');
    assert.equal(await page.locator('#fileDialogRefreshButton').isEnabled(), true);
    assert.equal(await page.locator('#fileDialogServerNotice button').isEnabled(), true);

    // Exercise real IndexedDB failure and concurrent-write cases in the same disposable profile.
    const storage = await page.evaluate(async ({ score, other }) => {
      const check = (value, message) => { if (!value) throw new Error(message); };
      const rejects = async (task, expected) => {
        try { await task(); } catch (error) { check(error.message.includes(expected) || error.code === expected, 'Wrong error: ' + error); return; }
        throw new Error('Expected failure: ' + expected);
      };
      await rejects(() => localLibrary.moveScore(score.id, 'missing'), uiText('error.targetFolderNotFound', { id: 'missing' }));
      await rejects(() => localLibrary.moveScore('missing', other.id), uiText('error.scoreNotFound', { id: 'missing' }));
      const before = await localLibrary.getScore(score.id);
      check(JSON.stringify(await localLibrary.moveScore(score.id, 'root')) === JSON.stringify(before), 'Same folder must be a no-op');
      const left = await localLibrary.saveScore({ title: 'Race', content: 'left', folderId: other.id });
      const right = await localLibrary.saveScore({ title: 'race', content: 'right' });
      const destination = await localLibrary.createFolder('Race target');
      const race = await Promise.allSettled([localLibrary.moveScore(left.id, destination.id), localLibrary.moveScore(right.id, destination.id)]);
      check(race.filter(r => r.status === 'fulfilled').length === 1, 'Concurrent moves must not create duplicates');
      check(race.find(r => r.status === 'rejected').reason.code === 'LOCAL_SCORE_NAME_EXISTS', 'Concurrent conflict code');
      for (const deleteFirst of [false, true]) {
        const doomed = await localLibrary.createFolder('Delete race ' + deleteFirst);
        const item = await localLibrary.saveScore({ title: 'Delete race ' + deleteFirst, content: 'keep' });
        const actions = deleteFirst
          ? [localLibrary.deleteFolder(doomed.id), localLibrary.moveScore(item.id, doomed.id)]
          : [localLibrary.moveScore(item.id, doomed.id), localLibrary.deleteFolder(doomed.id)];
        const results = await Promise.allSettled(actions);
        check(results.filter(r => r.status === 'fulfilled').length === 1, 'Only move or folder delete can win');
        const moved = await localLibrary.getScore(item.id);
        check(moved.content === 'keep', 'Concurrent delete must never lose the file');
        check(moved.folderId === 'root' || Boolean(await localLibrary.getFolder(moved.folderId)), 'No orphaned files');
      }
      const doomedScore = await localLibrary.saveScore({ title: 'Removed score', content: 'do not resurrect' });
      const deleted = await Promise.allSettled([localLibrary.deleteScore(doomedScore.id), localLibrary.moveScore(doomedScore.id, other.id)]);
      check(deleted[0].status === 'fulfilled' && deleted[1].status === 'rejected', 'Moving a deleted file must not recreate it');
      check(!await localLibrary.getScore(doomedScore.id), 'Deleted score stays deleted');
      return true;
    }, fixture);
    assert(storage);

    await page.context().setOffline(true);
    await page.evaluate(async () => refreshFileDialogEntries());
    await dragTo(fixture.score.id, rowFor(fixture.other.id), fixture.other.id);
    await assertPreserved(fixture.other.id);
    await page.context().setOffline(false);
    await page.reload();
    assert.equal(await page.evaluate(async id => (await localLibrary.getScore(id)).folderId, fixture.score.id), fixture.other.id,
      'The moved location survives reloading');
    await page.evaluate(async () => { openFileDialog('export'); await refreshFileDialogEntries(); });
    assert.equal(await page.locator('#fileDialogList tr[draggable="true"]').count(), 0, 'Export does not rearrange local files');
    assert.deepEqual(errors, []);
    console.log('Chromium: native folder/parent/root dragging, nested folders, current unsaved score, metadata preservation, conflicts, atomic move/delete races, offline and reload checked.');
  } finally {
    await browser.close();
  }
}
