import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, installRepeatPickup } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {}, innerWidth: 1440 },
  document: { addEventListener() {} }, rhythm: 'binaer', titel: { attr: () => 'Pickup regression' } });
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

function bar(size, hits, controls = [], repeat = { start: [], end: [] }) {
  const notes = Array(size).fill('f');
  for (const [step, note] of Object.entries(hits)) notes[step] = note;
  return { notes, controls, repeat };
}
function pattern(id, label, target, bars) {
  return { id, sourceKey: id, labelType: label, labelName: label, instrument: target.startsWith('Djembe') ? 'Djembe' : target,
    sourceInstrument: target.replace('_', ' '), defaultTargets: [target], bars };
}
function audible(notes) {
  return Array.from(notes).flatMap((note, step) => note && note !== 'f' ? [[step, note]] : []);
}
function checkPlayerAndScroller(sections) {
  player.timelineLoopCount = state.timerMinutes > 0 ? 'loop' : Math.max(0, state.repeatCount - 1);
  player.timelineLoop = state.timerMinutes > 0;
  player.orderedSections = player.buildConfiguredPracticeSections({ PracticeSections: sections });
  player.recalculateOrderedSectionTiming();
  const moving = ui.flattenPracticeScrollerSections(sections);
  for (const target of trackNames) {
    const expected = Array.from(moving.trackNotes[target]);
    const heard = expected.map((_, step) => player.getTrackPlaybackAtStep(target, { begleitungNotes: [] }, step).note || 'f');
    assert.deepEqual(heard, expected, target + ': actual player and moving notes agree across outer loops');
  }
  return moving;
}

for (const [rhythm, size] of [['binaer', 32], ['tenaer', 24], ['neunaer', 18]]) {
  ui.rhythm = player.rhythmType = rhythm;
  // Solo 1 shape: repeated opening bar with a final IN, then two more bars.
  const solo = pattern('solo', 'Solo 1', 'Djembe_3', [
    bar(size, { 0: 'slap', 2: 'tone', [size - 2]: 'bass' }, [{ type: 'in', stepIndex: size - 2 }],
      { start: ['continue'], end: [2] }),
    bar(size, { 0: 'slap', 4: 'tone' }), bar(size, { 0: 'tone', 2: 'slap' })
  ]);
  const bass = pattern('ballet', 'Begleitung', 'Dreierbass', [
    bar(size, { 0: 'kenkeni_doundoun', 8: 'kenkeni', [size - 4]: 'doundoun' },
      [{ type: 'in', stepIndex: size - 4 }])
  ]);
  timeline.sourcePatterns = [solo, bass];
  const original = JSON.stringify(timeline.sourcePatterns);
  for (const withBass of [true, false]) {
    for (const timerMinutes of [4, 0]) {
      for (const repeatCount of [3, 1]) {
        Object.assign(state, { soloPatternIds: [solo.id], accompanimentPatternIds: withBass ? [bass.id] : [],
          loopsWithoutSolo: 1, loopsWithSolo: 1, timerMinutes, repeatCount });
        const entries = ui.buildPracticeEntries({ singleCycle: true });
        const sections = ui.buildPracticeSectionsFromEntries(entries);
        const lead = sections[0];
        assert.equal(lead.isLeadIn, true);
        assert.equal(lead.overlapsPracticeCountIn, true);
        assert.equal(ui.getPracticeSectionLength(lead), 2, 'Initial IN stays short and overlaps the count-in');
        assert.deepEqual(audible(lead.trackNotes.Djembe_3), [[0, 'bass']]);
        assert.deepEqual(Array.from(sections.find(section => !section.isLeadIn).trackNotes.Djembe_3.slice(0, size * 3)),
          Array.from({ length: 3 }, () => solo.bars[0].notes).flat(), 'The opening repeat remains three full passes after its pickup');
        const host = sections.at(-1);
        const hostLength = ui.getPracticeSectionLength(host);
        const loops = timerMinutes > 0 || repeatCount > 1;
        if (withBass) {
          assert.equal(hostLength, size, 'Accompaniment-only bar keeps its length');
          assert.deepEqual(audible(host.trackNotes.Djembe_3), loops ? [[size - 2, 'bass']] : [],
            'Only one IN at the end of the accompaniment bar, never a repeating bass fragment');
          if (loops) assert.equal(host.trackNotes.Djembe_3.length, size, 'Full bar including rests reaches player and scroller');
          assert.deepEqual(Array.from(host.trackNotes.Dreierbass), bass.bars[0].notes);
        } else {
          assert.equal(hostLength, size * 5, 'Three opening passes plus two tail bars; the IN must not consume a pass');
          assert.equal(host.trackNotes.Djembe_3[hostLength - size], 'tone', 'Loop pickup must not overwrite the start of the last bar');
          assert.equal(host.trackNotes.Djembe_3[hostLength - 2], loops ? 'bass' : 'f');
        }
        checkPlayerAndScroller(sections);
        assert.equal(JSON.stringify(timeline.sourcePatterns), original, 'Source notes and markers stay unchanged');
      }
    }
  }

  // Only the standalone copy is shortened; the shared pickup remains at its source subdivision.
  const block = { id: 'direct', sectionTempo: null };
  const pickup = ui.createPracticeSection(block, 0, '::pickup');
  pickup.isLeadIn = true;
  pickup.trackNotes.Djembe_3 = Array(size).fill('f');
  pickup.trackNotes.Djembe_3[size - 2] = 'bass';
  pickup.trackTargetFlags.Djembe_3 = Array(size).fill(false);
  pickup.trackTargetFlags.Djembe_3[size - 2] = true;
  const unchanged = JSON.stringify(pickup);
  const main = ui.createPracticeSection(block, 0, '');
  main.trackNotes.Djembe_3 = ['tone', ...Array(size - 1).fill('f')];
  main.practiceTargetInstruments = ['Djembe_3'];
  main.repeatCount = 1;
  const sections = [];
  const returned = ui.appendPracticeSectionWithPickup(sections, main, pickup, true, true);
  assert.equal(returned, pickup);
  assert.equal(JSON.stringify(pickup), unchanged, 'Standalone trimming does not mutate the loop pickup');
  assert.notEqual(sections[0], pickup);
  assert.equal(sections[0].runtimeKey, pickup.runtimeKey, 'Playback and scroller section identity stays stable');
  assert.deepEqual(audible(sections[0].trackNotes.Djembe_3), [[0, 'bass']]);
}
console.log('Practice loop pickup: one IN at the correct subdivision, accompaniment/solo, timer/finite loops, three grids, count-in, audio/scroller parity and immutable source checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', dialog => dialog.dismiss());
    const base = process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/';
    await page.goto(new URL('app-shell.html', base).href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    await page.evaluate(content => loadRhythmContent('Balakulania SMF', content, null, { remember: false }),
      source('Noten/Balakulania SMF.bbs'));
    const results = await page.evaluate(() => {
      const patterns = timelineState.sourcePatterns;
      const solo = patterns.find(p => p.sourceInstrument === 'Djembe 3' && p.labelName === 'Solo 1');
      const bass = patterns.find(p => p.instrument === 'Dreierbass' && p.labelName === 'Begleitpattern 1');
      const sourceBefore = JSON.stringify(patterns);
      // Keep this regression's one-bar pause even when the saved exercise settings change.
      Object.assign(practiceState, { loopsWithoutSolo: 1, loopsWithSolo: 1, repeatCount: 10, timerMinutes: 4 });
      const results = [];
      for (const withBass of [false, true]) {
        practiceState.soloPatternIds = [solo.id];
        practiceState.accompanimentPatternIds = withBass ? [bass.id] : [];
        const payload = buildPracticePlayerPayload();
        const sections = payload[0].PracticeSections;
        const moving = flattenPracticeScrollerSections(sections);
        results.push({ withBass, payload, moving, sectionLengths: sections.map(getPracticeSectionLength) });
      }
      return { results, immutable: JSON.stringify(patterns) === sourceBefore };
    });
    assert.equal(results.immutable, true);
    for (const { withBass, payload, moving, sectionLengths } of results.results) {
      assert.deepEqual(sectionLengths, withBass ? [2, 160, 32] : [2, 160], 'Short count-in pickup plus all five solo bars');
      const sections = payload[0].PracticeSections;
      if (withBass) {
        assert.deepEqual(audible(sections.at(-1).trackNotes.Djembe_3), [[30, 'bass']]);
        assert.equal(moving.trackNotes.Djembe_3.slice(162, 194).filter(n => n === 'bass').length, 1,
          'Only one bass in the formerly broken accompaniment-only bar');
      }
      Object.assign(player, { rhythmType: payload[0].Rhythmus, timelineLoopCount: payload[0].TimelineLoopCount,
        timelineLoop: payload[0].TimelineLoop, currentBaseTempo: payload[0].Tempo, initialTempo: payload[0].Tempo });
      player.orderedSections = player.buildConfiguredPracticeSections(payload[0]);
      player.recalculateOrderedSectionTiming();
      for (const target of ['Djembe_3', 'Dreierbass']) {
        assert.deepEqual(moving.trackNotes[target], moving.trackNotes[target].map((_, step) =>
          player.getTrackPlaybackAtStep(target, { begleitungNotes: [] }, step).note || 'f'), 'Real BBS: audio/scroller parity');
      }
    }
    await page.context().setOffline(true);
    const offlineHost = await page.evaluate(() => buildPracticePlayerPayload()[0].PracticeSections.at(-1).trackNotes.Djembe_3);
    assert.deepEqual(audible(offlineHost), [[30, 'bass']]);
    assert.deepEqual(errors, []);
    console.log('Chromium: current Balakulania SMF read through actual editor; Solo 1 with/without Ballet Dununs, timing, audio/scroller parity and offline checked.');
  } finally {
    await browser.close();
  }
}
