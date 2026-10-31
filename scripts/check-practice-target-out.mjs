import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {}, innerWidth: 1440 },
  document: { addEventListener() {} }, rhythm: 'binaer' });
installTiming(ui);
installRepeatPickup(ui);
vm.runInContext(source('JS/timeline.js'), ui);
vm.runInContext(source('JS/practice.js'), ui);
const state = vm.runInContext('practiceState', ui);
const timeline = vm.runInContext('timelineState', ui);
const trackNames = Array.from(vm.runInContext('practiceTrackInstrumentNames', ui));
const playerText = source('Audio/player.html');
const player = vm.createContext({
  isPracticeMode: true, isSheetQuickPlayMode: false, isTimelineMode: true,
  rhythmType: 'binaer', MAX_EXPANDED_BARS: 2000, trackInstrumentNames: trackNames,
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  currentBaseTempo: 90, initialTempo: 90, practiceTimerFinalLoopStartStep: null, globalPlaybackStep: 0
});
installTiming(player);
installRepeatPickup(player);
vm.runInContext(playerText.slice(playerText.indexOf('function sanitizeRepeatRanges('),
  playerText.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(playerText.slice(playerText.indexOf('function getFeelOffsetSeconds('),
  playerText.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, playerText, ['getSectionLength', 'recalculateOrderedSectionTiming',
  'getPlaybackSectionContext', 'isFinalTimelinePlaybackContext', 'getTrackPlaybackAtStep', 'getFinalOverlapTailStartStep']);

function bar(hits, controls = [], repeat = { start: [], end: [] }) {
  const notes = Array(32).fill('f');
  for (const [step, note] of Object.entries(hits)) notes[step] = note;
  return { notes, controls, repeat };
}
function configurePlayer(sections, timerMinutes, repeatCount) {
  player.timelineLoopCount = timerMinutes > 0 ? 'loop' : Math.max(0, repeatCount - 1);
  player.timelineLoop = timerMinutes > 0;
  player.orderedSections = player.buildConfiguredPracticeSections({ PracticeSections: sections });
  player.recalculateOrderedSectionTiming();
}

// A target is padded with rests, not looped. Its OUT must keep its original position.
for (const [rhythm, size] of [['binaer', 32], ['tenaer', 24], ['neunaer', 18]]) {
  ui.rhythm = rhythm;
  const section = ui.createPracticeSection({ id: 'normalization' }, 0, '');
  const notes = Array(size * 3).fill('tone');
  const flags = Array(size * 3).fill(true);
  section.trackNotes.Djembe_3 = notes;
  section.trackTargetFlags.Djembe_3 = flags;
  section.practiceTargetInstruments = ['Djembe_3'];
  section.finalRepeatOutSteps.Djembe_3 = size * 2 + size / 2;
  section.trackNotes.Dreierbass = Array(size).fill('kenkeni_doundoun');
  section.finalRepeatOutSteps.Dreierbass = size - 2;
  section.minLength = size * 4;
  ui.normalizePracticeSectionTrackLoops(section);
  assert.equal(section.finalRepeatOutSteps.Djembe_3, size * 2 + size / 2,
    'Padding the target must not move its OUT into a nonexistent repetition');
  assert.deepEqual(Array.from(section.trackNotes.Djembe_3), notes.concat(Array(size).fill('f')));
  assert.deepEqual(Array.from(section.trackTargetFlags.Djembe_3), flags.concat(Array(size).fill(false)));
  assert.equal(section.finalRepeatOutSteps.Dreierbass, size * 4 - 2,
    'A genuinely looped accompaniment still moves its OUT to the final loop');
  assert.equal(section.trackNotes.Dreierbass.length, size * 4);
  const once = JSON.stringify(section);
  ui.normalizePracticeSectionTrackLoops(section);
  assert.equal(JSON.stringify(section), once, 'Normalization stays idempotent');
  assert.deepEqual(notes, Array(size * 3).fill('tone'), 'Original note array stays unchanged');
  assert.deepEqual(flags, Array(size * 3).fill(true));
}
ui.rhythm = 'binaer';

// Balakulania SMF: a pickup-only bar, followed by a bar played three times with OUT on beat 3.
const call = { id: 'ruf', instrument: 'Djembe', sourceInstrument: 'Djembe 3',
  labelType: 'Komischer Ruf', labelName: 'Komischer Ruf', defaultTargets: ['Djembe_3'],
  bars: [bar({ 0: 'tone_flam', 4: 'tone', 6: 'tone', 10: 'tone', 14: 'tone', 16: 'tone' })] };
const pickupHits = { 20: 'tone', 22: 'slap', 24: 'tone', 26: 'tone', 28: 'slap', 30: 'slap' };
const bodyHits = { 0: 'bass', 4: 'slap', 6: 'slap', 8: 'bass', 12: 'slap', 14: 'slap', 16: 'bass', ...pickupHits };
const solo = { ...call, id: 'solo', labelType: 'Solo 2', labelName: 'Solo 2', bars: [
  bar(pickupHits, [{ type: 'in', stepIndex: 20 }]),
  bar(bodyHits, [{ type: 'out', stepIndex: 16 }], { start: ['continue'], end: [2] })
] };
const bass = { id: 'ballet', instrument: 'Dreierbass', sourceInstrument: 'Dreierbass',
  labelType: 'Begleitung', labelName: 'Begleitpattern 1', defaultTargets: ['Dreierbass'], bars: [
    bar({ 0: 'kenkeni_doundoun', 8: 'kenkeni', 12: 'doundoun', 16: 'kenkeni_doundoun',
      20: 'sangban', 24: 'kenkeni_sangban', 28: 'doundoun' }, [{ type: 'in', stepIndex: 28 }, { type: 'out', stepIndex: 24 }])
  ] };
timeline.sourcePatterns = [call, solo, bass];
const original = JSON.stringify(timeline.sourcePatterns);

for (const withBass of [false, true]) {
  for (const timerMinutes of [0, 4]) {
    for (const repeatCount of [1, 3]) {
      for (const loopsWithSolo of [1, 2]) {
        Object.assign(state, { soloPatternIds: [call.id, solo.id], accompanimentPatternIds: withBass ? [bass.id] : [],
          loopsWithoutSolo: 4, loopsWithSolo, timerMinutes, repeatCount });
        const sections = ui.buildPracticeSectionsFromEntries(ui.buildPracticeEntries({ singleCycle: true }));
        const lastSolo = sections.find(section => section.finalRepeatOutStepTypes.Djembe_3 === 'Solo 2');
        assert(lastSolo);
        assert.equal(lastSolo.finalRepeatOutSteps.Djembe_3, 80, 'OUT stays on beat 3 of the final written repeat');
        assert.equal(ui.getPracticeSectionLength(lastSolo), 96, 'Later pickups see the trimmed section, not the old minimum');
        configurePlayer(sections, timerMinutes, repeatCount);
        const finalSection = player.orderedSections.find(section => section.finalRepeatOutStepTypes.Djembe_3 === 'Solo 2');
        assert.equal(finalSection.length, 96,
          `No extra silent bar after OUT (bass=${withBass}, timer=${timerMinutes}, cycles=${repeatCount}, section repeats=${loopsWithSolo}; tracks=${Object.values(lastSolo.trackNotes).map(notes => notes.length)})`);
        const heard = Array.from({ length: 96 }, (_, step) =>
          player.getTrackPlaybackAtStep('Djembe_3', { begleitungNotes: [] }, finalSection.startStep + step).note || 'f');
        const expected = solo.bars[1].notes.concat(solo.bars[1].notes, solo.bars[1].notes.slice(0, 17), Array(15).fill('f'));
        assert.deepEqual(heard, expected, 'First two written passes stay complete; only the third stops at OUT');
        if (withBass) {
          assert.equal(player.getTrackPlaybackAtStep('Dreierbass', { begleitungNotes: [] }, finalSection.startStep + 92).note,
            'doundoun', 'Accompaniment continues after the Solo OUT');
        }
        const moving = ui.flattenPracticeScrollerSections(sections);
        for (const target of ['Djembe_3', 'Dreierbass']) {
          assert.deepEqual(Array.from(moving.trackNotes[target]), Array.from(moving.trackNotes[target], (_, step) =>
            player.getTrackPlaybackAtStep(target, { begleitungNotes: [] }, step).note || 'f'),
          'Moving notes and audio agree, including the return to Komischer Ruf and subsequent loops');
        }
        assert.equal(JSON.stringify(timeline.sourcePatterns), original, 'Source notes and markers stay unchanged');
      }
    }
  }
}
console.log('Practice target OUT: rests do not shift OUT; real looped accompaniment unchanged; Balakulania Solo 2, written/section/outer repeats, timer, three grids, audio/scroller parity and immutable source checked.');

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
    await page.evaluate(content => loadRhythmContent('Balakulania SMF', content, null, { remember: false }),
      source('Noten/Balakulania SMF.bbs'));
    for (const withBass of [false, true]) {
      const result = await page.evaluate(withBass => {
        const call = timelineState.sourcePatterns.find(p => p.labelName === 'Komischer Ruf');
        const solo = timelineState.sourcePatterns.find(p => p.labelName === 'Solo 2');
        const bass = timelineState.sourcePatterns.find(p => p.instrument === 'Dreierbass' && p.labelName === 'Begleitpattern 1');
        const before = JSON.stringify(timelineState.sourcePatterns);
        practiceState.soloPatternIds = [call.id, solo.id];
        practiceState.accompanimentPatternIds = withBass ? [bass.id] : [];
        const payload = buildPracticePlayerPayload();
        openPracticeAudioPlayer(payload);
        return { payload, moving: flattenPracticeScrollerSections(payload[0].PracticeSections),
          immutable: JSON.stringify(timelineState.sourcePatterns) === before };
      }, withBass);
      assert(result.immutable);
      const frame = page.frame({ name: 'practiceAudioFrame' });
      await frame.waitForFunction(withBass => typeof orderedSections !== 'undefined' &&
        typeof getTrackPlaybackAtStep === 'function' &&
        orderedSections.some(section => section.trackNotes.Dreierbass.length > 0) === withBass, withBass);
      const audio = await frame.evaluate(() => {
        const solo = orderedSections.find(section => section.finalRepeatOutStepTypes.Djembe_3 === 'Solo 2');
        return { length: solo.length, out: solo.finalRepeatOutSteps.Djembe_3,
          notes: Array.from({ length: 96 }, (_, step) =>
            getTrackPlaybackAtStep('Djembe_3', { begleitungNotes: [] }, solo.startStep + step).note || 'f') };
      });
      assert.equal(audio.out, 80);
      assert.equal(audio.length, 96);
      assert.deepEqual(audio.notes, solo.bars[1].notes.concat(solo.bars[1].notes, solo.bars[1].notes.slice(0, 17), Array(15).fill('f')));
      Object.assign(player, { rhythmType: result.payload[0].Rhythmus, currentBaseTempo: result.payload[0].Tempo,
        initialTempo: result.payload[0].Tempo });
      configurePlayer(result.payload[0].PracticeSections, result.payload[0].PracticeDurationSeconds > 0 ? 4 : 0,
        Number(result.payload[0].TimelineLoopCount) + 1);
      for (const target of ['Djembe_3', 'Dreierbass']) {
        assert.deepEqual(result.moving.trackNotes[target], result.moving.trackNotes[target].map((_, step) =>
          player.getTrackPlaybackAtStep(target, { begleitungNotes: [] }, step).note || 'f'));
      }
    }
    await page.context().setOffline(true);
    assert.equal(await page.evaluate(() => buildPracticePlayerPayload()[0].PracticeSections
      .find(section => section.finalRepeatOutStepTypes.Djembe_3 === 'Solo 2').finalRepeatOutSteps.Djembe_3), 80);
    assert.deepEqual(errors, []);
    console.log('Chromium: current Balakulania SMF, Komischer Ruf + Solo 2 with/without Ballet Dununs, actual player iframe, moving notes and offline checked.');
  } finally {
    await browser.close();
  }
}
