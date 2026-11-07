import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import vm from 'node:vm';
import { source, loadFunctions, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console: { warn() {} }, window: { addEventListener() {}, atob },
  rhythm: 'binaer', timelineMetadataSelector: '#timeline_metadata' });
installRepeatPickup(ui);
vm.runInContext(source('JS/timeline.js'), ui);
const metadata = { version: 11, entries: [], accompanimentSegments: [] };
function fragment(value, count = 1, raw = false) {
  const text = raw ? value : Buffer.from(JSON.stringify(value)).toString('base64');
  const node = { node: { tagName: 'desc', textContent: text }, attr: () => '' };
  return { select: () => count ? node : null, selectAll: () => Array(count).fill(node) };
}
assert.equal(ui.readTimelineMetadata(fragment(null, 0)), null, 'A file without metadata may get a default timeline');
for (const version of [7, 8, 9, 10, 11]) assert.equal(ui.readTimelineMetadata(fragment({ ...metadata, version })).version, version);
for (const version of [undefined, null, 0, 6, 12, 99, 7.5, 'bad']) {
  assert.throws(() => ui.readTimelineMetadata(fragment({ ...metadata, version })), /savedVersionUnsupported/);
}
for (const value of [null, [], {}, { version: 11 }, { ...metadata, entries: 'broken' },
  { ...metadata, entries: [null] }, { ...metadata, entries: [{}] }, { ...metadata, accompanimentSegments: {} }]) {
  assert.throws(() => ui.readTimelineMetadata(fragment(value)), /savedDataUnreadable/);
}
for (const raw of ['', 'not base64!', btoa('{broken JSON')]) {
  assert.throws(() => ui.readTimelineMetadata(fragment(raw, 1, true)), /savedDataUnreadable/);
}
assert.throws(() => ui.readTimelineMetadata(fragment(metadata, 2)), /savedDataUnreadable/, 'Ambiguous metadata must not be discarded');
const library = [{ id: 'P1', sourceKey: 'Solo|1', aliasSourceKeys: ['Old Solo|1'], aliasPatternIds: ['old-P1'] }];
for (const ref of [{ patternSourceKey: 'Solo|1' }, { patternSourceKey: 'Old Solo|1' }, { patternId: 'P1' }, { patternId: 'old-P1' }]) {
  ui.validateTimelinePatternReferences(library, [ref], []);
}
assert.throws(() => ui.validateTimelinePatternReferences(library, [{ patternId: 'P1', patternSourceKey: 'Missing|1' }], []),
  /savedPatternsMissing/, 'A reused numeric ID must not replace a missing source key');
assert.throws(() => ui.validateTimelinePatternReferences(library, [], [{ patternSourceKey: 'Missing|1' }]), /savedPatternsMissing/);

// Test the actual file-load transaction independently of the SVG renderer.
let contents = { title: 'Original', notes: ['tone'], arrangement: ['Solo'], practice: { repeats: 4 } };
let baseline = JSON.stringify(contents);
let restores = 0, remembers = 0, historyClears = 0;
const warnings = [];
const loader = vm.createContext({
  scoreDocumentGeneration: 8, currentScoreId: 'original-id', loadedTitle: 'Original',
  uiText: (key, values) => key + JSON.stringify(values), alert: value => warnings.push(value),
  confirmDiscardScoreChanges: () => true,
  Snap: { parseStr: data => JSON.parse(data) },
  readTimelineMetadata: data => { if (data.preflightError) throw new Error('Unreadable'); return data.metadata; },
  getCurrentHistorySnapshot: () => structuredClone(contents),
  restoreHistorySnapshot: snapshot => { restores++; contents = snapshot; },
  practiceState: {}, updateTimelineMetadataNode() {}, renderPracticePanel() {},
  onSVGLoaded: data => { contents = data.contents; if (data.readError) throw new Error('Missing pattern'); },
  rememberLastLoadedScore: () => { remembers++; }, setIoFieldValue() {},
  clearHistorySnapshots: () => { historyClears++; }, rememberSavedScoreSnapshot: () => { baseline = JSON.stringify(contents); }
});
loadFunctions(loader, source('index.php'), ['loadRhythmContent']);
for (const bad of [{ preflightError: true }, { contents: { title: 'Wrong score' }, readError: true }]) {
  const before = JSON.stringify(contents);
  assert.equal(loader.loadRhythmContent('Wrong', JSON.stringify(bad), 'wrong-id'), false);
  assert.equal(JSON.stringify(contents), before);
  assert.equal(loader.currentScoreId, 'original-id');
  assert.equal(loader.loadedTitle, 'Original');
  assert.equal(loader.scoreDocumentGeneration, 8);
  assert.equal(baseline, before);
  assert.equal(remembers, 0);
  assert.equal(historyClears, 0);
}
assert.equal(restores, 1, 'Only a post-read failure needs rollback');
assert.equal(warnings.length, 2);
assert.equal(loader.loadRhythmContent('New', JSON.stringify({ contents: { title: 'New' } }), 'new-id'), true);
assert.equal(loader.currentScoreId, 'new-id');
assert.equal(loader.scoreDocumentGeneration, 9);
assert.equal(remembers, 1);
assert.equal(historyClears, 1);
for (const language of ['de', 'en', 'fr', 'es', 'pt']) {
  const strings = JSON.parse(source('languages/' + language + '.json')).arrangement;
  assert(strings.confirm.rebuild);
  for (const key of ['savedDataUnreadable', 'savedVersionUnsupported', 'savedPatternsMissing', 'loadCancelled']) assert(strings.error[key]);
}
console.log('Arrangement load guards: version/schema/encoding/duplicate metadata, strict references, atomic load cancellation, rollback and five languages checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    let approveRebuild = false;
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', dialog => dialog.type() === 'confirm' && approveRebuild ? dialog.accept() : dialog.dismiss());
    await page.goto(new URL('app-shell.html', process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/').href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    const bbs = source('Noten/Balakulania SMF.bbs');
    assert.equal(await page.evaluate(content => loadRhythmContent('Balakulania SMF', content, 'test-id', { remember: false }), bbs), true);
    const currentPreserved = await page.evaluate(content => {
      const doc = new DOMParser().parseFromString('<svg>' + content + '</svg>', 'application/xml');
      const saved = JSON.parse(decodeURIComponent(escape(atob(doc.querySelector('#timeline_metadata').textContent))));
      return JSON.stringify(saved.entries) === JSON.stringify(timelineState.entries);
    }, bbs);
    assert.equal(currentPreserved, true, 'The existing Balakulania arrangement is not repaired or deduplicated automatically');
    const results = await page.evaluate(content => {
      const doc = new DOMParser().parseFromString('<svg>' + content + '</svg>', 'application/xml');
      const saved = JSON.parse(decodeURIComponent(escape(atob(doc.querySelector('#timeline_metadata').textContent))));
      const cases = [
        ['future', m => { m.version = 99; }], ['old', m => { m.version = 6; }],
        ['badEntries', m => { m.entries = null; }],
        ['emptyMetadata', null], ['badEncoding', null], ['duplicateMetadata', null],
        ['missingPattern', m => { m.entries[0].patternSourceKey = 'missing'; }],
        ['missingSegment', m => { m.accompanimentSegments = [{ patternSourceKey: 'missing', patternId: 'pattern-1' }]; }],
        ['invalidSegmentTarget', m => { m.accompanimentSegments = [{ patternSourceKey: 'Djembe 1|Begleitpattern 1|1', targetInstrument: 'invalid' }]; }],
        ['nonAccompanimentSegment', m => { m.accompanimentSegments = [{ patternSourceKey: m.entries[0].patternSourceKey, targetInstrument: 'Djembe_1' }]; }],
        ['orphanOverlay', m => { m.entries[0].overlayRepeatIndex = 0; m.entries[0].parallelGroupId = ''; }]
      ];
      const oldConfirm = window.confirm;
      window.confirm = () => true;
      recordArrangementHistorySnapshot();
      timelineState.tempo += 1;
      practiceState.repeatCount += 1;
      const before = scoreSnapshotSignature(getCurrentScoreSnapshot());
      const history = JSON.stringify(undoHistory);
      const oldBaseline = savedScoreSnapshot;
      const generation = scoreDocumentGeneration;
      const results = [];
      function differences(left, right, path = '') {
        if (JSON.stringify(left) === JSON.stringify(right)) return [];
        if (!left || !right || typeof left !== 'object' || typeof right !== 'object') return [{ path, left, right }];
        return [...new Set(Object.keys(left).concat(Object.keys(right)))].flatMap(key => differences(left[key], right[key], path + '.' + key));
      }
      for (const [name, change] of cases) {
        const m = JSON.parse(JSON.stringify(saved));
        const candidate = doc.cloneNode(true);
        const node = candidate.querySelector('#timeline_metadata');
        if (change) change(m);
        node.textContent = name === 'emptyMetadata' ? '' : name === 'badEncoding' ? 'invalid!' : btoa(unescape(encodeURIComponent(JSON.stringify(m))));
        if (name === 'duplicateMetadata') node.after(node.cloneNode(true));
        const opened = loadRhythmContent('Must not replace', new XMLSerializer().serializeToString(candidate.documentElement), 'wrong-id', { remember: false });
        results.push({ name, opened, same: before === scoreSnapshotSignature(getCurrentScoreSnapshot()),
          history: JSON.stringify(undoHistory) === history, baseline: savedScoreSnapshot === oldBaseline,
          id: currentScoreId, generation: scoreDocumentGeneration === generation, dirty: hasUnsavedScoreChanges(),
          differences: differences(JSON.parse(before), JSON.parse(scoreSnapshotSignature(getCurrentScoreSnapshot()))) });
      }
      window.confirm = oldConfirm;
      return results;
    }, bbs);
    for (const result of results) {
      assert.equal(result.opened, false, result.name);
      assert.equal(result.same, true, result.name + ': previous score restored ' + JSON.stringify(result.differences));
      assert.equal(result.history, true, result.name + ': undo preserved');
      assert.equal(result.baseline, true, result.name + ': saved baseline preserved');
      assert.equal(result.id, 'test-id');
      assert(result.generation && result.dirty);
    }
    // Reload the real file, then exercise the actual rebuild button: cancel, confirm and undo.
    await page.evaluate(() => { rememberSavedScoreSnapshot(); });
    assert.equal(await page.evaluate(content => loadRhythmContent('Balakulania SMF', content, 'test-id', { remember: false }), bbs), true);
    const initialEntries = await page.evaluate(() => JSON.stringify(timelineState.entries));
    await page.evaluate(() => document.getElementById('timelineRefreshButton').click());
    assert.equal(await page.evaluate(() => JSON.stringify(timelineState.entries)), initialEntries, 'Cancelling rebuild preserves the saved arrangement');
    approveRebuild = true;
    await page.evaluate(() => document.getElementById('timelineRefreshButton').click());
    const fresh = await page.evaluate(() => ({ count: timelineState.entries.length,
      solo: timelineState.entries.filter(e => e.patternSourceKey === 'Djembe 3|Solo 2|1').length }));
    assert.deepEqual(fresh, { count: 11, solo: 1 }, 'New Balakulania timeline expands Solo 2 only inside its pattern');
    await page.evaluate(() => undoLastEditorAction());
    assert.equal(await page.evaluate(() => JSON.stringify(timelineState.entries)), initialEntries, 'Rebuild can be undone');
    const empty = await page.evaluate(content => {
      const doc = new DOMParser().parseFromString('<svg>' + content + '</svg>', 'application/xml');
      const node = doc.querySelector('#timeline_metadata');
      const saved = JSON.parse(decodeURIComponent(escape(atob(node.textContent))));
      saved.entries = [];
      saved.accompanimentSegments = [];
      node.textContent = btoa(unescape(encodeURIComponent(JSON.stringify(saved))));
      return loadRhythmContent('Empty arrangement', new XMLSerializer().serializeToString(doc.documentElement), null, { remember: false }) && timelineState.entries.length === 0;
    }, bbs);
    assert.equal(empty, true, 'An intentionally empty arrangement is not replaced by defaults');
    assert.equal(await page.evaluate(content => loadRhythmContent('Legacy version 7', content, null, { remember: false }),
      source('Noten/Balakulania SMF test 1.bbs')), true, 'Supported old formats still migrate');
    const freshWithoutMetadata = await page.evaluate(content => {
      const doc = new DOMParser().parseFromString('<svg>' + content + '</svg>', 'application/xml');
      doc.querySelector('#timeline_metadata').remove();
      return loadRhythmContent('No metadata', new XMLSerializer().serializeToString(doc.documentElement), null, { remember: false }) && timelineState.entries.length === 11;
    }, bbs);
    assert.equal(freshWithoutMetadata, true, 'Only a score without an existing arrangement gets defaults automatically');
    await page.context().setOffline(true);
    assert.equal(await page.evaluate(content => loadRhythmContent('Balakulania SMF', content, 'test-id', { remember: false }), bbs), true);
    assert.deepEqual(errors, []);
    console.log('Chromium: real score, protected load, missing references, complete rollback/undo/dirty identity, confirmed rebuild and offline checked.');
  } finally {
    await browser.close();
  }
}
