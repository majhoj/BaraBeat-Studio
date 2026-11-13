import assert from 'node:assert/strict';
import vm from 'node:vm';
import { createRequire } from 'node:module';
import { source, loadFunctions } from './helpers/music-context.mjs';

const ui = vm.createContext({ timelineText: key => key });
loadFunctions(ui, source('JS/timeline.js'), [
  'createTimelineMetadataError', 'migrateTimelineInstrumentReferences', 'validateTimelinePatternReferences'
]);
const plain = value => JSON.parse(JSON.stringify(value));
const library = [{ id: 'P8', sourceKey: 'Doundoun|Begleitung|1' }];
for (const oldName of ['Dununba', 'Dundunba']) {
  const entries = [{ id: 'entry', patternId: 'obsolete-id', patternSourceKey: oldName + '|Begleitung|1',
    startBar: 41, durationBars: 11, blockId: 'block-8', parallelGroupId: 'parallel',
    overlayRepeatIndex: 2, overlayBarOffset: 1, handMode: '', sectionTempo: 110,
    targetInstruments: ['Sangban', oldName] }];
  const original = JSON.stringify(entries);
  const migrated = ui.migrateTimelineInstrumentReferences(library, entries);
  assert.deepEqual(plain(migrated), [{ ...entries[0], patternSourceKey: 'Doundoun|Begleitung|1',
    targetInstruments: ['Sangban', 'Doundoun'] }]);
  ui.validateTimelinePatternReferences(library, migrated, []);
  assert.equal(JSON.stringify(entries), original, 'Do not alter the saved source metadata');
  assert.notEqual(migrated[0], entries[0]);
  assert.notEqual(migrated[0].targetInstruments, entries[0].targetInstruments);
  assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences(library, migrated)), plain(migrated), 'Migration is idempotent');
  const segment = { patternSourceKey: oldName + '|Begleitung|1', targetInstrument: oldName, startBar: 12, barCount: 18 };
  assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences(library, [segment])), [
    { ...segment, patternSourceKey: 'Doundoun|Begleitung|1', targetInstrument: 'Doundoun' }
  ]);
}
assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences(library, undefined)), []);
const known = [{ patternSourceKey: 'Doundoun|Begleitung|1', targetInstruments: ['Doundoun'] }];
assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences(library, known)), known);
const untouched = [{ patternSourceKey: 'Djembe 1|Dununba|2', targetInstruments: ['Djembe_1', 'Djembe_3'] }];
assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences([], untouched)), untouched, 'No pattern-label or Djembe-number normalization');
for (const key of ['Dununba|Begleitung|2', 'Dununba|Different label|1']) {
  assert.throws(() => ui.migrateTimelineInstrumentReferences(library, [{ patternId: 'P8', patternSourceKey: key }]),
    /savedPatternsMissing/, 'Never guess a label/occurrence or reuse a numeric ID');
}
const unknown = ui.migrateTimelineInstrumentReferences(library, [{ patternId: 'P8', patternSourceKey: 'Unknown|Begleitung|1' }]);
assert.throws(() => ui.validateTimelinePatternReferences(library, unknown, []), /savedPatternsMissing/);
assert.throws(() => ui.migrateTimelineInstrumentReferences([
  ...library, { id: 'P9', sourceKey: 'Other|1', aliasSourceKeys: ['Doundoun|Begleitung|1'] }
], [{ patternSourceKey: 'Dununba|Begleitung|1' }]), /savedPatternsMissing/, 'Ambiguous aliases must not choose a pattern');
const explicitAlias = [{ id: 'P8', sourceKey: 'Doundoun|Begleitung|1', aliasSourceKeys: ['Dununba|Begleitung|1'] }];
const oldReference = [{ patternSourceKey: 'Dununba|Begleitung|1' }];
assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences(explicitAlias, oldReference)), oldReference,
  'An existing exact reference takes precedence');
ui.validateTimelinePatternReferences(explicitAlias, oldReference, []);
const aliasOnly = [{ id: 'P8', sourceKey: 'Doundoun|Merged|1', aliasSourceKeys: ['Doundoun|Begleitung|1'] }];
ui.validateTimelinePatternReferences(aliasOnly, ui.migrateTimelineInstrumentReferences(aliasOnly, oldReference), []);

const soli = source('Noten/Soli 06.txt');
const savedMetadata = JSON.parse(Buffer.from(soli.match(/<desc[^>]*id="timeline_metadata"[^>]*>([^<]*)<\/desc>/)[1], 'base64').toString());
assert.equal(savedMetadata.entries.length, 8);
assert.equal(savedMetadata.entries.at(-1).patternSourceKey, 'Dununba|Begleitung|1');
const expectedEntries = savedMetadata.entries.map(entry => ({ ...entry,
  patternSourceKey: entry.patternSourceKey === 'Dununba|Begleitung|1' ? 'Doundoun|Begleitung|1' : entry.patternSourceKey }));
assert.deepEqual(plain(ui.migrateTimelineInstrumentReferences(expectedEntries.map(entry => ({
  id: entry.patternId, sourceKey: entry.patternSourceKey
})), savedMetadata.entries)), expectedEntries, 'Soli 06: exactly one reference changes; eight entries and all settings survive');
console.log('Instrument migration: two explicit aliases, exact labels/occurrences, unique matching, targets/segments, immutable metadata and actual Soli 06 checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [], prompts = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', async dialog => { prompts.push(dialog.message()); await dialog.dismiss(); });
    await page.goto(new URL('app-shell.html', process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/').href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    const snapshot = () => page.evaluate(() => ({
      entries: timelineState.entries,
      segments: timelineState.accompanimentSegments,
      layout: getCurrentTimelineLayout().clips.map(clip => ({ key: clip.pattern.sourceKey,
        target: clip.targetInstrument, startBar: clip.startBar, barCount: clip.barCount })),
      totalBars: getCurrentTimelineLayout().playbackTotalBars,
      payload: buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries),
      notes: callPHPScript_lesen(zeilenAnzahl, { showAlert: false, updateQuickPlaySelectors: false }).rhythmBars
    }));
    const canonical = await page.evaluate(content => {
      const data = Snap.parseStr(content);
      const metadata = readTimelineMetadata(data);
      metadata.entries.at(-1).patternSourceKey = 'Doundoun|Begleitung|1';
      data.select('#timeline_metadata').node.textContent = btoa(unescape(encodeURIComponent(JSON.stringify(metadata))));
      return new XMLSerializer().serializeToString(data.node);
    }, soli);
    assert(await page.evaluate(content => loadRhythmContent('Soli 06', content, null, { remember: false }), canonical));
    const expected = await snapshot();
    assert.equal(expected.entries.length, 8);
    assert.equal(expected.payload[0].Tempo, 110);
    assert.deepEqual(expected.payload[0].SwingProfile.binaer, [6, -5, 6, 10]);
    const stored = await page.evaluate(async content => localLibrary.saveScore({ title: 'Soli 06', content }), soli);
    assert(await page.evaluate(score => loadRhythmContent(score.title, score.content, score.id, { remember: false }), stored));
    assert.deepEqual(await snapshot(), expected, 'The original file gives the exact same notes, positions, repeats and player payload as the canonical reference');
    assert.deepEqual(await page.evaluate(() => timelineState.entries.map(e => e.id)), savedMetadata.entries.map(e => e.id));
    assert.deepEqual(await page.evaluate(() => timelineState.entries.map(e => e.targetInstruments)), savedMetadata.entries.map(e => e.targetInstruments));
    assert.equal(await page.evaluate(async id => (await localLibrary.getScore(id)).content, stored.id), soli,
      'Opening does not overwrite the original in IndexedDB');
    assert.deepEqual(prompts, [], 'A known unambiguous rename does not ask to discard the arrangement');
    await page.evaluate(() => saveCurrentScoreLocal());
    const saved = await page.evaluate(async id => localLibrary.getScore(id), stored.id);
    assert.notEqual(saved.content, soli, 'Only the explicit save updates stored content');
    assert(await page.evaluate(score => loadRhythmContent(score.title, score.content, score.id, { remember: false }), saved));
    assert.deepEqual(await snapshot(), expected, 'Save/reopen preserves the migrated arrangement');
    await page.context().setOffline(true);
    assert(await page.evaluate(content => loadRhythmContent('Soli 06', content, null, { remember: false }), soli));
    assert.deepEqual(await snapshot(), expected, 'The same migration also works offline');
    assert.deepEqual(prompts, []);
    assert.deepEqual(errors, []);
    assert.equal(source('Noten/Soli 06.txt'), soli);
    console.log('Chromium: Soli 06 retains all eight entries, instrument assignments, positions, lengths, repeats, source notes and player payload; original untouched, save/reopen and offline passed.');
    console.log(JSON.stringify({ totalBars: expected.totalBars, clips: expected.layout }));
  } finally {
    await browser.close();
  }
}
