import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

const names = ['saveLocalScoreWithNameCheck', 'saveCurrentScoreLocal', 'saveCurrentScoreFromMenu'];
const shell = vm.createContext({});
loadFunctions(shell, source('app-shell.html'), names);
const original = { id: 'existing', title: 'Djaa', folderId: 'root', content: 'old',
  isPublished: true, serverPath: 'Djaa.bbs', publishToken: 'keep-token' };
const conflict = Object.assign(new Error('Name exists'), {
  code: 'LOCAL_SCORE_NAME_EXISTS', conflictingScoreIds: ['existing']
});
let failure = conflict;
let approved = false;
let currentTitle = 'Djaa';
const writes = [];
const prompts = [];
const alerts = [];
const remembered = [];
const context = vm.createContext({
  console: { error() {} },
  scoreDocumentGeneration: 1, currentScoreId: null, savedScoreSnapshot: 'unsaved',
  titel: { attr: () => currentTitle },
  buildSerializedRhythm: () => '<svg>current notes</svg>',
  getCurrentRhythmTitle: () => currentTitle,
  setRhythmTitle: value => { currentTitle = value; },
  getCurrentScoreSnapshot: () => ({ title: currentTitle, notes: 'current' }),
  scoreSnapshotSignature: JSON.stringify, isDefaultRhythmTitle: value => !value,
  rememberLastLoadedScore: id => remembered.push(id), setSelectedFileSource() {},
  refreshFileList: async () => {},
  uiText: (key, values) => key + (values ? ':' + values.title : ''),
  confirm: message => { prompts.push(message); return approved; },
  alert: message => alerts.push(message),
  getSaveDialogModeForDirectSave: async () => '', closeAppMenus() {}, showAutoDismissMessage() {},
  localLibrary: {
    rootFolderId: 'root', getScore: async () => ({ ...original }),
    async saveScore(value) {
      writes.push({ ...value });
      if (!value.id && failure) throw failure;
      return { ...original, ...value, id: value.id || 'new' };
    }
  }
});
loadFunctions(context, source('index.php'), names);
for (const name of names) assert.equal(context[name].toString(), shell[name].toString(), 'Offline parity: ' + name);

await assert.rejects(context.saveCurrentScoreLocal(), error => error.isUserCancel === true);
assert.equal(context.currentScoreId, null);
assert.equal(context.savedScoreSnapshot, 'unsaved');
assert.deepEqual(remembered, []);
assert.equal(writes.length, 1, 'Cancelled replacement never writes the target');
await context.saveCurrentScoreFromMenu();
assert.deepEqual(alerts, [], 'Cancellation is not reported as a storage failure');

approved = true;
writes.length = 0;
const replaced = await context.saveCurrentScoreLocal();
assert.equal(replaced.id, original.id);
assert.equal(replaced.publishToken, original.publishToken);
assert.equal(writes.length, 2);
assert.equal(writes[1].id, original.id, 'Only the confirmed ID is reused');
assert.equal(context.currentScoreId, original.id);
assert.deepEqual(remembered, ['existing'], 'Reopening the app retains the saved identity');
const promptCount = prompts.length;
await context.saveCurrentScoreLocal();
assert.equal(prompts.length, promptCount, 'Normal save of an open file does not prompt');
await context.saveCurrentScoreLocal(undefined, undefined, { asCopy: true });
assert.equal(prompts.length, promptCount + 1, 'Save As with the same name requires confirmation');

failure = Object.assign(new Error('Ambiguous'), { code: conflict.code, conflictingScoreIds: ['a', 'b'] });
context.currentScoreId = null;
writes.length = 0;
const beforeAmbiguous = prompts.length;
await assert.rejects(context.saveCurrentScoreLocal(), /file.error.localNameAmbiguous/);
assert.equal(writes.length, 1);
assert.equal(prompts.length, beforeAmbiguous, 'Never choose arbitrarily among existing duplicates');

failure = conflict;
await assert.rejects(context.saveLocalScoreWithNameCheck({ title: 'Djaa', serverPath: 'Other.bbs' }),
  error => error === conflict);
assert.equal(prompts.length, beforeAmbiguous, 'Do not replace a different publication association');
failure = new Error('Quota exceeded');
await assert.rejects(context.saveCurrentScoreLocal(), /Quota exceeded/);
assert.equal(context.currentScoreId, null);
failure = null;
const fresh = await context.saveCurrentScoreLocal('New name');
assert.equal(fresh.id, 'new');
assert.equal(currentTitle, 'New name');
assert.equal(remembered.at(-1), 'new');

for (const language of ['de', 'en', 'fr', 'es', 'pt']) {
  const file = JSON.parse(source(`languages/${language}.json`)).file;
  for (const message of [file.confirm.replaceSameName, file.error.localNameExists, file.error.localNameAmbiguous]) {
    assert(message && message.includes('{title}'), 'Missing conflict translation: ' + language);
  }
}
console.log('Local save: confirm/cancel, Save As, ambiguous duplicates, publication identity, failures, remembered ID and offline parity checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  const base = process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/';
  try {
    // All databases here belong to isolated, disposable browser contexts, never the user's app.
    const storage = await browser.newContext({ serviceWorkers: 'block' });
    await storage.route(new URL('storage-test', base).href, route => route.fulfill({
      contentType: 'text/html', body: '<!doctype html><title>Storage regression</title>'
    }));
    const page = await storage.newPage();
    const peer = await storage.newPage();
    for (const tab of [page, peer]) {
      await tab.goto(new URL('storage-test', base).href);
      await tab.addScriptTag({ content: source('JS/localLibrary.js') });
    }
    const result = await page.evaluate(async () => {
      const check = (value, message) => { if (!value) throw new Error(message); };
      async function rejectsName(callback) {
        try { await callback(); } catch (error) {
          check(error.code === 'LOCAL_SCORE_NAME_EXISTS', 'Expected name conflict: ' + error.message);
          return;
        }
        throw new Error('Duplicate unexpectedly saved');
      }
      const first = await localLibrary.saveScore({ title: 'Djaa', content: 'old' });
      await rejectsName(() => localLibrary.saveScore({ title: 'Djaa', content: 'duplicate' }));
      await rejectsName(() => localLibrary.saveScore({ title: '  DJAA  ', content: 'case duplicate' }));
      const unicode = await localLibrary.saveScore({ title: 'R\u00e9sum\u00e9', content: 'unicode' });
      await rejectsName(() => localLibrary.saveScore({ title: unicode.title.normalize('NFD'), content: 'unicode duplicate' }));
      const folder = await localLibrary.createFolder('Other');
      const other = await localLibrary.saveScore({ title: 'Djaa', folderId: folder.id, content: 'other folder' });
      await rejectsName(() => localLibrary.moveScore(other.id, 'root'));
      check((await localLibrary.getScore(other.id)).folderId === folder.id, 'Failed move changed the original');
      const rename = await localLibrary.saveScore({ title: 'Rename me', content: 'keep' });
      await rejectsName(() => localLibrary.saveScore({ ...rename, title: 'Djaa' }));
      check((await localLibrary.getScore(rename.id)).title === 'Rename me', 'Failed rename changed the original');
      await localLibrary.markPublished(first.id, 'Djaa.bbs', 'keep-token');
      const updated = await localLibrary.saveScore({ id: first.id, title: 'Djaa', content: 'new' });
      check(updated.publishToken === 'keep-token' && updated.serverPath === 'Djaa.bbs', 'Publication link lost');
      check(updated.syncState === 'modified-local' && updated.createdAt === first.createdAt, 'Update metadata changed');
      // Simulate a database created by previous releases, without a schema migration or cleanup.
      const db = await localLibrary.openDb();
      await new Promise((resolve, reject) => {
        const tx = db.transaction('scores', 'readwrite');
        tx.objectStore('scores').put({ id: 'legacy-a', title: 'Legacy', folderId: 'root', content: 'one' });
        tx.objectStore('scores').put({ id: 'legacy-b', title: 'Legacy', folderId: 'root', content: 'two' });
        tx.oncomplete = resolve;
        tx.onabort = () => reject(tx.error);
      });
      await localLibrary.saveScore({ id: 'legacy-a', content: 'edited' });
      check((await localLibrary.getScore('legacy-b')).content === 'two', 'Existing duplicate was overwritten');
      await rejectsName(() => localLibrary.saveScore({ title: 'Legacy', content: 'third' }));
      await localLibrary.saveScore({ id: 'legacy-a', title: 'Legacy renamed', content: 'edited' });
      check((await localLibrary.getScore('legacy-b')).content === 'two', 'Renaming merged existing duplicates');
      const race = await Promise.allSettled([
        localLibrary.saveScore({ title: 'Race', content: 'one' }),
        localLibrary.saveScore({ title: 'Race', content: 'two' })
      ]);
      check(race.filter(r => r.status === 'fulfilled').length === 1, 'Concurrent save made a duplicate');
      check(race.find(r => r.status === 'rejected').reason.code === 'LOCAL_SCORE_NAME_EXISTS', 'Wrong race error');
      return { count: (await localLibrary.listScores()).length, version: db.version };
    });
    assert.equal(result.count, 7);
    assert.equal(result.version, 1, 'No destructive schema migration');
    const crossWindow = await Promise.all([page, peer].map(tab => tab.evaluate(async () => {
      try { await localLibrary.saveScore({ title: 'Two windows', content: 'notes' }); return 'saved'; }
      catch (error) { return error.code; }
    })));
    assert.deepEqual(crossWindow.sort(), ['LOCAL_SCORE_NAME_EXISTS', 'saved']);
    assert.equal(await page.evaluate(async () => (await localLibrary.listScores()).filter(s => s.title === 'Two windows').length), 1);
    await storage.setOffline(true);
    assert.equal(await peer.evaluate(async () => {
      try { await localLibrary.saveScore({ title: 'Two windows', content: 'offline duplicate' }); }
      catch (error) { return error.code; }
    }), 'LOCAL_SCORE_NAME_EXISTS');
    await storage.close();
    console.log('Chromium IndexedDB: names/folders, Unicode, rename/move, old duplicates, concurrent connections, metadata and offline saves checked.');

    const app = await browser.newContext({ serviceWorkers: 'block' });
    const editor = await app.newPage();
    let allow = false;
    const dialogs = [];
    const errors = [];
    editor.on('pageerror', error => errors.push(error.message));
    editor.on('dialog', async dialog => {
      dialogs.push({ type: dialog.type(), message: dialog.message() });
      if (dialog.type() === 'beforeunload' || (allow && dialog.type() === 'confirm')) await dialog.accept();
      else await dialog.dismiss();
    });
    await editor.goto(new URL('app-shell.html', base).href);
    await editor.waitForFunction(() => typeof saveCurrentScoreLocal === 'function' && typeof s !== 'undefined');
    const saved = await editor.evaluate(async content => {
      loadRhythmContent('Djaa test', content, null, { remember: false });
      return await saveCurrentScoreLocal();
    }, source('Noten/Djaa Djembe, Sangban, Dundunba.bbs'));
    assert.equal(await editor.evaluate(() => getRememberedLastLoadedScoreId()), saved.id);
    const sourceContent = saved.content;
    await editor.evaluate(() => openFileDialog('saveAs'));
    const cancelPrompt = editor.waitForEvent('dialog', { predicate: dialog => dialog.type() === 'confirm' });
    await editor.locator('#fileDialogConfirmButton').click();
    await cancelPrompt;
    await editor.waitForFunction(() => document.getElementById('fileDialog').hidden === false);
    assert.equal(await editor.evaluate(async () => (await localLibrary.listScores()).length), 1);
    assert(dialogs.some(d => d.type === 'confirm' && d.message.includes('Djaa test')));
    assert.equal(await editor.evaluate(async id => (await localLibrary.getScore(id)).content, saved.id), sourceContent);
    allow = true;
    await editor.locator('#fileDialogConfirmButton').click();
    await editor.waitForFunction(() => document.getElementById('fileDialog').hidden);
    assert.equal(await editor.evaluate(() => currentScoreId), saved.id);
    assert.equal(await editor.evaluate(async () => (await localLibrary.listScores()).length), 1);
    // Server import of a same-named, previously local-only score must not silently create another record.
    allow = false;
    const cancelledImport = await editor.evaluate(async () => {
      const originalImport = serverLibrary.importScore;
      serverLibrary.importScore = async () => ({ title: 'Djaa test', serverPath: 'Djaa test.bbs', format: 'bbs', content: buildSerializedRhythm() });
      try { await importServerScore('Djaa test.bbs', { serverModifiedTs: 1 }); }
      catch (error) { return error.isUserCancel === true; }
      finally { serverLibrary.importScore = originalImport; }
    });
    assert.equal(cancelledImport, true);
    assert.equal(await editor.evaluate(async () => (await localLibrary.listScores()).length), 1);
    allow = true;
    const imported = await editor.evaluate(async () => {
      const originalImport = serverLibrary.importScore;
      serverLibrary.importScore = async () => ({ title: 'Djaa test', serverPath: 'Djaa test.bbs', format: 'bbs', content: buildSerializedRhythm() });
      try { return await importServerScore('Djaa test.bbs', { serverModifiedTs: 1 }); }
      finally { serverLibrary.importScore = originalImport; }
    });
    assert.equal(imported.id, saved.id);
    assert.equal(imported.isPublished, true);
    await app.setOffline(true);
    const offline = await editor.evaluate(async () => {
      await saveCurrentScoreLocal();
      await saveCurrentScoreLocal('Djaa test copy', undefined, { asCopy: true });
      return { scores: await localLibrary.listScores(), current: currentScoreId, remembered: getRememberedLastLoadedScoreId() };
    });
    assert.equal(offline.scores.length, 2);
    assert.notEqual(offline.current, saved.id);
    assert.equal(offline.current, offline.remembered);
    assert.equal(offline.scores.find(s => s.id === saved.id).syncState, 'modified-local');
    assert.equal(offline.scores.find(s => s.id === offline.current).isPublished, false, 'A real copy does not inherit publication credentials');
    assert(!dialogs.some(d => d.type === 'alert'), 'No unexpected storage errors');
    await app.setOffline(false);
    await editor.reload();
    await editor.waitForFunction(id => currentScoreId === id, offline.current);
    assert.equal(await editor.evaluate(async () => (await localLibrary.listScores()).length), 2, 'Reload reopens the same record');
    await editor.evaluate(async () => {
      const original = await localLibrary.getScore(currentScoreId);
      const db = await localLibrary.openDb();
      await new Promise((resolve, reject) => {
        const tx = db.transaction('scores', 'readwrite');
        tx.objectStore('scores').put({ ...original, id: 'older-duplicate', content: '<svg>keep this older version</svg>' });
        tx.oncomplete = resolve;
        tx.onabort = () => reject(tx.error);
      });
      openFileDialog('saveAs');
    });
    const ambiguityAlert = editor.waitForEvent('dialog', { predicate: dialog => dialog.type() === 'alert' });
    await editor.locator('#fileDialogConfirmButton').click();
    const ambiguity = await ambiguityAlert;
    assert(ambiguity.message().includes('Djaa test copy'));
    assert(await editor.locator('#fileDialog').isVisible(), 'Ambiguous save leaves the dialog open for a different name');
    assert.equal(await editor.evaluate(async () => (await localLibrary.listScores()).length), 3);
    assert.equal(await editor.evaluate(async () => (await localLibrary.getScore('older-duplicate')).content), '<svg>keep this older version</svg>');
    assert.equal(await editor.evaluate(() => currentScoreId), offline.current);
    assert.deepEqual(errors, []);
    await app.close();
    console.log('Chromium editor: real Save As confirm/cancel, server import collision, stable IDs after reload, offline copies, existing duplicates and source preservation checked.');
  } finally {
    await browser.close();
  }
}
