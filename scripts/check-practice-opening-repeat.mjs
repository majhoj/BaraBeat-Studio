import assert from 'node:assert/strict';
import vm from 'node:vm';
import { createRequire } from 'node:module';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {}, innerWidth: 1440 },
  document: { addEventListener() {} }, rhythm: 'binaer', titel: { attr: () => 'Opening repeat' } });
installTiming(ui);
installRepeatPickup(ui);
vm.runInContext(source('JS/timeline.js'), ui);
vm.runInContext(source('JS/practice.js'), ui);
const state = vm.runInContext('practiceState', ui);
const timeline = vm.runInContext('timelineState', ui);
const tracks = Array.from(vm.runInContext('practiceTrackInstrumentNames', ui));
const playerSource = source('Audio/player.html');
const player = vm.createContext({ isPracticeMode: true, isSheetQuickPlayMode: false, isTimelineMode: true,
  rhythmType: 'binaer', MAX_EXPANDED_BARS: 2000, trackInstrumentNames: tracks,
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'], currentBaseTempo: 84, initialTempo: 84,
  practiceTimerFinalLoopStartStep: null, globalPlaybackStep: 0 });
installTiming(player);
installRepeatPickup(player);
vm.runInContext(playerSource.slice(playerSource.indexOf('function sanitizeRepeatRanges('),
  playerSource.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(playerSource.slice(playerSource.indexOf('function getFeelOffsetSeconds('),
  playerSource.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, playerSource, ['getSectionLength', 'recalculateOrderedSectionTiming',
  'getPlaybackSectionContext', 'isFinalTimelinePlaybackContext', 'getTrackPlaybackAtStep', 'getFinalOverlapTailStartStep']);

function bar(size, hits, controls = [], repeat = { start: [], end: [] }) {
  const notes = Array(size).fill('f');
  for (const [step, note] of Object.entries(hits)) notes[step] = note;
  return { notes, controls, repeat };
}
function pattern(id, label, target, bars) {
  return { id, sourceKey: id, instrument: target.startsWith('Djembe') ? 'Djembe' : target,
    sourceInstrument: target.replace('_', ' '), defaultTargets: [target], labelType: label, labelName: label, bars };
}
function assertPlayback(config, moving) {
  Object.assign(player, { rhythmType: config.Rhythmus, currentBaseTempo: config.Tempo, initialTempo: config.Tempo,
    timelineLoopCount: config.TimelineLoopCount, timelineLoop: config.TimelineLoopCount === 'loop' });
  player.orderedSections = player.buildConfiguredPracticeSections(config);
  player.recalculateOrderedSectionTiming();
  for (const target of ['Djembe_3', 'Dreierbass']) {
    const notes = Array.from(moving.trackNotes[target]);
    assert.deepEqual(notes.map((_, step) => player.getTrackPlaybackAtStep(target, { begleitungNotes: [] }, step).note || 'f'),
      notes, 'Player and moving notes agree: ' + target);
  }
}

for (const [rhythm, size] of [['binaer', 32], ['tenaer', 24], ['neunaer', 18]]) {
  ui.rhythm = rhythm;
  for (const repeatCount of [1, 2, 8]) {
    for (const repeatBars of [1, 2]) {
      const opening = [bar(size, { 0: 'slap', 4: 'tone' })];
      if (repeatBars === 2) opening.push(bar(size, { 0: 'bass', 4: 'slap' }));
      opening[0].repeat.start = ['continue'];
      opening.at(-1).notes[size - 2] = 'tone';
      opening.at(-1).controls = [{ type: 'in', stepIndex: size - 2 }];
      opening.at(-1).repeat.end = [repeatCount];
      const tail = [bar(size, { 0: 'tone', 2: 'slap' }), bar(size, { 0: 'bass' })];
      const solo = pattern('solo', 'Solo 7', 'Djembe_3', opening.concat(tail));
      const bass = pattern('bass', 'Begleitung', 'Dreierbass', [bar(size, { 0: 'kenkeni_doundoun', [size - 4]: 'doundoun' },
        [{ type: 'in', stepIndex: size - 4 }])]);
      timeline.sourcePatterns = [solo, bass];
      const original = JSON.stringify(timeline.sourcePatterns);
      const body = Array.from({ length: repeatCount + 1 }, () => opening).flat().concat(tail).flatMap(b => b.notes);
      for (const withBass of [false, true]) {
        const expected = body.slice();
        // Without an accompaniment-only host, the existing outer-loop IN lands in the last solo bar.
        if (!withBass) expected[expected.length - 2] = 'tone';
        for (const timerMinutes of [0, 4]) {
          for (const loopsWithSolo of [1, 2]) {
            Object.assign(state, { soloPatternIds: [solo.id], accompanimentPatternIds: withBass ? [bass.id] : [],
              accompanimentStart: 'immediate', loopsWithoutSolo: 1, loopsWithSolo, timerMinutes, repeatCount: 3 });
            const config = ui.buildPracticePlayerPayload()[0];
            const sections = config.PracticeSections;
            const main = sections.find(s => !s.isLeadIn);
            assert.deepEqual(Array.from(main.trackNotes.Djembe_3), expected,
              'IN must not consume the first full pass: ' + JSON.stringify({ rhythm, repeatCount, repeatBars, withBass }));
            assert.equal(ui.getPracticeSectionLength(main), expected.length);
            assert.equal(ui.getPracticeSectionLength(sections[0]), 2, 'The short count-in pickup stays unchanged');
            assert.equal(sections[0].overlapsPracticeCountIn, true);
            assertPlayback(config, ui.flattenPracticeScrollerSections(sections));
            assert.equal(JSON.stringify(timeline.sourcePatterns), original, 'Source notes and repeat markers stay untouched');
          }
        }
      }
    }
  }
}

// A real pickup-only opening still leads straight into the body, not through a silent bar.
ui.rhythm = 'binaer';
const pickupOnly = pattern('pickup-only', 'Solo 2', 'Djembe_3', [
  bar(32, { 30: 'tone' }, [{ type: 'in', stepIndex: 30 }], { start: ['continue'], end: [1] }),
  bar(32, { 0: 'slap', 8: 'tone' })
]);
timeline.sourcePatterns = [pickupOnly];
Object.assign(state, { soloPatternIds: [pickupOnly.id], accompanimentPatternIds: [], loopsWithSolo: 1,
  loopsWithoutSolo: 0, timerMinutes: 0, repeatCount: 1 });
const leadingSections = ui.buildPracticePlayerPayload()[0].PracticeSections;
assert.deepEqual(Array.from(leadingSections[1].trackNotes.Djembe_3), pickupOnly.bars.flatMap(b => b.notes),
  'The first pickup-only occurrence keeps its previous trimming behavior');
const guards = pattern('guards', 'Solo', 'Djembe_3', [
  bar(32, { 0: 'slap', 30: 'tone' }, [{ type: 'in', stepIndex: 30 }], { start: ['continue'], end: [1] }),
  bar(32, { 0: 'tone' }), bar(32, { 0: 'bass' }, [{ type: 'out', stepIndex: 0 }])
]);
timeline.sourcePatterns = [guards];
state.soloPatternIds = [guards.id];
const ending = ui.buildPracticePlayerPayload()[0].PracticeSections.find(section => !section.isLeadIn);
assert.equal(ending.finalRepeatOutSteps.Djembe_3, 96, 'OUT keeps its position after both opening passes');
assert.deepEqual(Array.from(ending.trackNotes.Djembe_3.slice(0, 64)), guards.bars[0].notes.concat(guards.bars[0].notes));
for (const kind of ['no-repeat', 'later-repeat', 'in-outside-repeat', 'continuation', 'zero-count']) {
  const changed = JSON.parse(JSON.stringify(guards));
  if (kind === 'no-repeat') changed.bars[0].repeat = { start: [], end: [] };
  if (kind === 'later-repeat') {
    changed.bars[0].controls = [];
    changed.bars[0].repeat = { start: [], end: [] };
    changed.bars[1].controls = [{ type: 'in', stepIndex: 30 }];
    changed.bars[1].repeat = { start: ['continue'], end: [1] };
  }
  if (kind === 'in-outside-repeat') {
    changed.bars[0].controls = [];
    changed.bars[1].controls = [{ type: 'in', stepIndex: 30 }];
  }
  if (kind === 'continuation') changed.bars[0].repeat.end = ['continue'];
  if (kind === 'zero-count') changed.bars[0].repeat.end = [0];
  assert.equal(ui.hasPracticeOpeningRepeatPickup(changed, ui.flattenPracticePatternNotes(changed), ui.getPracticePatternInStep(changed)),
    false, 'Do not reinterpret a different pickup case: ' + kind);
}
console.log('Practice opening repeat: concrete full sequences for counts 1/2/8, one/two-bar ranges, three grids, accompaniment, section/outer/timer loops, source immutability and audio/scroller parity checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', dialog => dialog.dismiss());
    await page.goto(new URL('app-shell.html', process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/').href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    const file = source('Noten/Balakulania SMF.bbs');
    assert(await page.evaluate(content => loadRhythmContent('Balakulania SMF', content, null, { remember: false }), file));
    const result = await page.evaluate(() => {
      const scenario = practiceState.practiceScenarios.find(s => s.name === 'Solo 7');
      if (!scenario) throw new Error('Solo 7 scenario missing');
      const original = JSON.stringify(timelineState.sourcePatterns);
      applyPracticeScenario(scenario.id);
      const solo = getPracticePatternsByIds(practiceState.soloPatternIds)[0];
      const config = buildPracticePlayerPayload()[0];
      openPracticeAudioPlayer([config]);
      return { config, moving: flattenPracticeScrollerSections(config.PracticeSections),
        solo, ranges: buildPracticeRepeatRangesFromBars(solo.bars),
        immutable: original === JSON.stringify(timelineState.sourcePatterns) };
    });
    assert.equal(result.solo.sourceKey, 'Djembe 3|Solo 7|1');
    assert.deepEqual(result.ranges, [{ startBar: 1, endBar: 1, count: 1 }]);
    const expected = [result.solo.bars[0], result.solo.bars[0], result.solo.bars[1], result.solo.bars[2]].flatMap(b => b.notes);
    const main = result.config.PracticeSections.find(s => !s.isLeadIn);
    assert.equal(expected.length, 128);
    assert.deepEqual(main.trackNotes.Djembe_3, expected, 'Solo 7: first bar twice, then the two following bars');
    assert.equal(result.config.PracticeSections[0].trackNotes.Djembe_3[0], 'tone');
    assert.equal(result.config.PracticeSections[0].trackNotes.Djembe_3.length, 2);
    assert(result.immutable);
    assertPlayback(result.config, result.moving);
    const frame = page.frame({ name: 'practiceAudioFrame' });
    await frame.waitForFunction(() => typeof orderedSections !== 'undefined' && typeof getTrackPlaybackAtStep === 'function');
    const heard = await frame.evaluate(() => {
      const main = orderedSections.find(s => !s.isLeadIn);
      return Array.from({ length: main.length }, (_, step) =>
        getTrackPlaybackAtStep('Djembe_3', { begleitungNotes: [] }, main.startStep + step).note || 'f');
    });
    assert.deepEqual(heard, expected, 'The actual player iframe receives both full passes');
    await page.context().setOffline(true);
    assert.deepEqual(await page.evaluate(() => buildPracticePlayerPayload()[0].PracticeSections.find(s => !s.isLeadIn).trackNotes.Djembe_3), expected);
    assert.deepEqual(errors, []);
    assert.equal(source('Noten/Balakulania SMF.bbs'), file);
    console.log('Chromium: saved Solo 7 scenario, A-A-B-C note sequence, unchanged pickup, actual player, moving notes and offline passed; original file untouched.');
  } finally {
    await browser.close();
  }
}
