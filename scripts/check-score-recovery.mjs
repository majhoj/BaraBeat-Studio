import assert from 'node:assert/strict';
import vm from 'node:vm';
import { createRequire } from 'node:module';
import { source, loadFunctions } from './helpers/music-context.mjs';

const context = vm.createContext({ timelineMetadataVersion: 11, timelineText: key => key });
loadFunctions(context, source('JS/timeline.js'), ['createTimelineMetadataError', 'buildRecoveredTimelineMetadata']);
const old = { version: 6, entries: [{ patternSourceKey: 'old' }], accompanimentSegments: [{ id: 'old' }],
  minimumBarCount: 100, playbackStartBar: 80, sourceHash: 'old', tempo: 110,
  swingProfile: { binaer: [6, -5, 6, 10] }, feelOffsets: { Sangban: 3 },
  practice: { repeatCount: 3 }, quickPlayInstrumentVolumes: { Sangban: 0.5 } };
const original = JSON.stringify(old);
const recovered = context.buildRecoveredTimelineMetadata(old);
assert.equal(recovered.version, 11);
assert.equal(recovered.entries.length, 0);
assert.equal(recovered.accompanimentSegments.length, 0);
assert.equal(recovered.minimumBarCount, 0);
assert.equal(recovered.playbackStartBar, 1);
assert.equal(recovered.sourceHash, undefined);
for (const key of ['tempo', 'swingProfile', 'feelOffsets', 'practice', 'quickPlayInstrumentVolumes']) {
  assert.deepEqual(recovered[key], old[key], 'Keep readable non-arrangement settings: ' + key);
}
assert.equal(JSON.stringify(old), original, 'Recovery never mutates original metadata');
assert.equal(context.buildRecoveredTimelineMetadata(null).entries.length, 0);
assert.equal(context.createTimelineMetadataError('test').code, 'TIMELINE_METADATA_INVALID');
const offers = [], alerts = [];
const ui = vm.createContext({ uiText: key => key, window: { confirm: text => { offers.push(text); return true; } },
  alert: text => alerts.push(text) });
loadFunctions(ui, source('index.php'), ['offerTimelineRecovery']);
assert(ui.offerTimelineRecovery(context.createTimelineMetadataError('test')));
assert(!ui.offerTimelineRecovery(new Error('Unrelated rendering failure')), 'Never discard metadata to hide an unrelated bug');
assert.equal(offers.length, 1);
assert.equal(alerts.length, 1);
const shell = vm.createContext({});
loadFunctions(shell, source('app-shell.html'), ['offerTimelineRecovery', 'loadRhythmContent']);
loadFunctions(ui, source('index.php'), ['loadRhythmContent']);
assert.equal(shell.loadRhythmContent.toString(), ui.loadRhythmContent.toString());
assert.equal(shell.offerTimelineRecovery.toString(), ui.offerTimelineRecovery.toString());
console.log('Score recovery: explicit classified errors only, empty arrangement, retained settings, immutable metadata and offline parity checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [], prompts = [];
    let approveRecovery = false;
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', async dialog => {
      prompts.push(dialog.message());
      if (dialog.type() === 'confirm' && (!dialog.message().includes('Notenblatt trotzdem öffnen') || approveRecovery)) {
        await dialog.accept();
      } else await dialog.dismiss();
    });
    await page.goto(new URL('app-shell.html', process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/').href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    const soli = source('Noten/Soli 06.txt');
    // Known instrument renames now migrate. Use a genuinely missing source for the recovery path.
    const unresolved = await page.evaluate(content => {
      const data = Snap.parseStr(content);
      const metadata = readTimelineMetadata(data);
      metadata.entries.at(-1).patternSourceKey = 'Unknown instrument|Begleitung|1';
      data.select('#timeline_metadata').node.textContent = btoa(unescape(encodeURIComponent(JSON.stringify(metadata))));
      return new XMLSerializer().serializeToString(data.node);
    }, soli);
    // The score without metadata establishes the expected source notes, independent of its saved arrangement.
    const expected = await page.evaluate(content => {
      const data = Snap.parseStr(content);
      data.selectAll('#timeline_metadata').forEach(el => el.remove());
      const noMetadata = new XMLSerializer().serializeToString(data.node);
      if (!loadRhythmContent('Soli 06 reference', noMetadata, null, { remember: false })) throw new Error('Reference failed');
      return JSON.stringify(callPHPScript_lesen(zeilenAnzahl, { showAlert: false, updateQuickPlaySelectors: false }).rhythmBars);
    }, soli);
    assert(await page.evaluate(content => loadRhythmContent('Previous score', content, null, { remember: false }),
      source('Noten/Balakulania SMF.bbs')));
    const previous = await page.evaluate(() => {
      recordArrangementHistorySnapshot();
      practiceState.repeatCount += 1;
      return { snapshot: scoreSnapshotSignature(getCurrentScoreSnapshot()), undo: JSON.stringify(undoHistory),
        generation: scoreDocumentGeneration };
    });
    const stored = await page.evaluate(async content => localLibrary.saveScore({ title: 'Soli 06 recovery test', content }), unresolved);
    assert.equal(await page.evaluate(score => loadRhythmContent(score.title, score.content, score.id, { remember: false }), stored), false);
    assert.deepEqual(await page.evaluate(() => ({ snapshot: scoreSnapshotSignature(getCurrentScoreSnapshot()),
      undo: JSON.stringify(undoHistory), generation: scoreDocumentGeneration })), previous, 'Cancel retains the previous unsaved score and undo');
    assert(prompts.some(message => message.includes('Notenblatt trotzdem öffnen')));
    approveRecovery = true;
    assert(await page.evaluate(score => loadRhythmContent(score.title, score.content, score.id, { remember: false }), stored));
    const actual = await page.evaluate(async id => ({
      entries: timelineState.entries.length, segments: timelineState.accompanimentSegments.length,
      tempo: timelineState.tempo, swing: timelineState.swingProfile.binaer,
      dirty: hasUnsavedScoreChanges(), id: currentScoreId, generation: scoreDocumentGeneration,
      originalContent: (await localLibrary.getScore(id)).content,
      bars: JSON.stringify(callPHPScript_lesen(zeilenAnzahl, { showAlert: false, updateQuickPlaySelectors: false }).rhythmBars)
    }), stored.id);
    assert.equal(actual.entries, 0);
    assert.equal(actual.segments, 0);
    assert.equal(actual.tempo, 110);
    assert.deepEqual(actual.swing, [6, -5, 6, 10]);
    assert.equal(actual.bars, expected, 'All source notes, instrument labels, repeat and IN/OUT markers are retained');
    assert(actual.dirty, 'Discarding an arrangement is an unsaved change');
    assert.equal(actual.id, stored.id);
    assert.equal(actual.generation, previous.generation + 1, 'A retry commits one document change');
    assert.equal(actual.originalContent, unresolved, 'Opening never writes the recovered document to storage');
    await page.evaluate(() => {
      const read = callPHPScript_lesen(zeilenAnzahl, { showAlert: false, updateQuickPlaySelectors: false });
      syncTimelineStateFromReadResultIfNeeded(read, buildCurrentTimelineSyncOptions());
    });
    assert.equal(await page.evaluate(() => timelineState.entries.length), 0, 'Refreshing the library does not invent a replacement arrangement');
    await page.evaluate(() => saveCurrentScoreLocal());
    const saved = await page.evaluate(async id => localLibrary.getScore(id), stored.id);
    assert.notEqual(saved.content, unresolved);
    const promptCount = prompts.length;
    assert(await page.evaluate(score => loadRhythmContent(score.title, score.content, score.id, { remember: false }), saved));
    assert.equal(prompts.length, promptCount, 'Reopening the saved recovery needs no warning');
    assert.equal(await page.evaluate(() => hasUnsavedScoreChanges()), false);
    assert.equal(await page.evaluate(() => timelineState.entries.length), 0);
    // Preflight problems can also be recovered, but only after the same explicit confirmation.
    for (const variant of ['future', 'invalidEncoding', 'duplicate']) {
      assert(await page.evaluate(({ content, variant }) => {
        const doc = new DOMParser().parseFromString('<svg>' + content + '</svg>', 'application/xml');
        const node = doc.querySelector('#timeline_metadata');
        if (variant === 'future') {
          const metadata = JSON.parse(decodeURIComponent(escape(atob(node.textContent))));
          metadata.version = 99;
          node.textContent = btoa(unescape(encodeURIComponent(JSON.stringify(metadata))));
        } else if (variant === 'duplicate') node.after(node.cloneNode(true));
        else node.textContent = 'broken!';
        const opened = loadRhythmContent('Recovery ' + variant, new XMLSerializer().serializeToString(doc.documentElement), null, { remember: false });
        return opened && timelineState.entries.length === 0 && hasUnsavedScoreChanges();
      }, { content: soli, variant }));
    }
    await page.context().setOffline(true);
    assert(await page.evaluate(content => loadRhythmContent('Soli 06 recovery offline', content, null, { remember: false }), unresolved));
    assert.equal(await page.evaluate(() => timelineState.entries.length), 0);
    assert.deepEqual(errors, []);
    assert.equal(source('Noten/Soli 06.txt'), soli, 'The real source file is never edited');
    console.log('Chromium: Soli 06 with an unresolved reference, cancel/confirm, untouched notes and stored original, empty arrangement, settings, unsaved state, save/reopen, preflight errors and offline checked.');
  } finally {
    await browser.close();
  }
}
