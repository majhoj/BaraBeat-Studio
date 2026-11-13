import assert from 'node:assert/strict';
import vm from 'node:vm';
import { createRequire } from 'node:module';
import { source, loadFunctions, installTiming, installRepeatPickup, near } from './helpers/music-context.mjs';

const ui = vm.createContext({ console, window: { addEventListener() {}, innerWidth: 1440 },
  document: { addEventListener() {} }, rhythm: 'binaer', titel: { attr: () => 'Accompaniment pickup' } });
installTiming(ui);
installRepeatPickup(ui);
vm.runInContext(source('JS/timeline.js'), ui);
vm.runInContext(source('JS/practice.js'), ui);
const state = vm.runInContext('practiceState', ui);
const timeline = vm.runInContext('timelineState', ui);
const tracks = Array.from(vm.runInContext('practiceTrackInstrumentNames', ui));
const playerText = source('Audio/player.html');
const player = vm.createContext({
  isPracticeMode: true, isSheetQuickPlayMode: false, isTimelineMode: true,
  rhythmType: 'binaer', MAX_EXPANDED_BARS: 2000, trackInstrumentNames: tracks,
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  currentBaseTempo: 84, initialTempo: 84, globalPlaybackStep: 0, practiceTimerFinalLoopStartStep: null,
  practiceCountInBuffer: {}, practiceCountInBeats: 4, playerStartDelay: 0.18, practiceLeadInDelay: 3,
  swingProfile: {}
});
installTiming(player);
installRepeatPickup(player);
vm.runInContext(playerText.slice(playerText.indexOf('function sanitizeRepeatRanges('),
  playerText.indexOf('const repeatRanges = sanitizeRepeatRanges(')), player);
vm.runInContext(playerText.slice(playerText.indexOf('function getFeelOffsetSeconds('),
  playerText.indexOf('const flatBars = isTimelineMode')), player);
loadFunctions(player, playerText, ['getSectionLength', 'recalculateOrderedSectionTiming',
  'getPlaybackSectionContext', 'isFinalTimelinePlaybackContext', 'getTrackPlaybackAtStep', 'getFinalOverlapTailStartStep',
  'getTimelineStepsPerBar', 'getStepsPerBeatForRhythm', 'getBaseStepDuration', 'buildSwingStepOffsets',
  'getBinaerSwingStepOffsets', 'getTenaerSwingStepOffsets', 'getNeunaerSwingStepOffsets',
  'getPlaybackRhythmStep', 'getEffectiveTempoForStep', 'normalizeSectionTempo', 'getStepInterval',
  'getPracticeCountInBeatDuration', 'getPracticeCountInDuration', 'getPracticeCountInOverlapDuration', 'getPracticePlaybackStartTiming']);

function configurePlayer(config) {
  Object.assign(player, { rhythmType: config.Rhythmus, currentBaseTempo: config.Tempo, initialTempo: config.Tempo,
    timelineLoop: config.TimelineLoopCount === 'loop', timelineLoopCount: config.TimelineLoopCount });
  player.orderedSections = player.buildConfiguredPracticeSections(config);
  player.recalculateOrderedSectionTiming();
}
function assertTiming(config, moving, visualTiming, pickupSteps) {
  configurePlayer(config);
  const timing = player.getPracticePlaybackStartTiming();
  const pickupSeconds = pickupSteps * player.getBaseStepDuration(config.Tempo);
  near(timing.regularLeadInSeconds - timing.playbackLeadInSeconds, pickupSeconds, 'IN precedes beat one');
  near(timing.playbackLeadInSeconds + pickupSeconds, timing.regularLeadInSeconds, 'Main pattern starts on beat one');
  near(visualTiming.leadInMs, timing.playbackLeadInSeconds * 1000, 'Audio and moving notes share the start position');
  near(visualTiming.countInMs, timing.countInDuration * 1000, 'Count-in remains four beats');
  for (const track of tracks) {
    const notes = Array.from(moving.trackNotes[track]);
    assert.deepEqual(notes.map((_, step) => player.getTrackPlaybackAtStep(track, { begleitungNotes: [] }, step).note || 'f'),
      notes, track + ': audio/scroller note parity including later loops');
  }
  return timing;
}
function pattern(id, label, target, size, hits, controls = []) {
  const notes = Array(size).fill('f');
  for (const [step, note] of Object.entries(hits)) notes[step] = note;
  return { id, sourceKey: id, labelType: label, labelName: label,
    instrument: target.startsWith('Djembe') ? 'Djembe' : target,
    sourceInstrument: target.replace('_', ' '), defaultTargets: [target],
    bars: [{ notes, controls, repeat: { start: [], end: [] } }] };
}
for (const [rhythm, size, pickupSteps] of [['binaer', 32, 4], ['tenaer', 24, 3], ['neunaer', 18, 3]]) {
  ui.rhythm = rhythm;
  const solo = pattern('solo', 'Solo 9', 'Djembe_3', size, { 0: 'tone', 2: 'slap' });
  const bass = pattern('bass', 'Begleitung', 'Dreierbass', size,
    { 0: 'kenkeni_doundoun', [size - pickupSteps]: 'doundoun' }, [{ type: 'in', stepIndex: size - pickupSteps }]);
  timeline.sourcePatterns = [solo, bass];
  const original = JSON.stringify(timeline.sourcePatterns);
  for (const bpm of [60, 84, 140]) {
    timeline.tempo = bpm;
    for (const mode of ['withSolo', 'onlyAccompaniment', 'accompanimentAsTarget']) {
      for (const timerMinutes of [0, 4]) {
        for (const repeatCount of [1, 3]) {
          Object.assign(state, { accompanimentPatternIds: mode === 'accompanimentAsTarget' ? [] : [bass.id],
            soloPatternIds: mode === 'withSolo' ? [solo.id] : mode === 'accompanimentAsTarget' ? [bass.id] : [],
            accompanimentStart: 'immediate', loopsWithSolo: 1, loopsWithoutSolo: 0, timerMinutes, repeatCount });
          const config = ui.buildPracticePlayerPayload()[0];
          const sections = config.PracticeSections;
          assert.equal(sections[0].overlapsPracticeCountIn, true, 'An accompaniment IN alone must enter during the count-in');
          assert.equal(ui.getPracticeSectionLength(sections[0]), pickupSteps);
          assert.deepEqual(Array.from(sections[0].trackNotes.Dreierbass), ['doundoun', ...Array(pickupSteps - 1).fill('f')]);
          assert.deepEqual(Array.from(sections[1].trackNotes.Dreierbass), bass.bars[0].notes, 'Do not shift or rotate the actual loop');
          if (mode === 'withSolo') assert.deepEqual(Array.from(sections[1].trackNotes.Djembe_3), solo.bars[0].notes);
          assertTiming(config, ui.flattenPracticeScrollerSections(sections), ui.getPracticeScrollerInitialLeadInTiming(config, sections), pickupSteps);
          assert.equal(JSON.stringify(timeline.sourcePatterns), original, 'Source notes and IN markers remain unchanged');
        }
      }
    }
  }
  // An IN on beat one has no pickup; Calls retain their existing start policy.
  for (const label of ['Begleitung', 'Call', 'Intro']) {
    const atOne = pattern('at-one', label, 'Djembe_1', size, { 0: 'tone' }, [{ type: 'in', stepIndex: 0 }]);
    timeline.sourcePatterns = [atOne];
    Object.assign(state, { accompanimentPatternIds: [], soloPatternIds: [atOne.id], repeatCount: 1, timerMinutes: 0 });
    const sections = ui.buildPracticePlayerPayload()[0].PracticeSections;
    assert(!sections.some(section => section.overlapsPracticeCountIn));
  }
  for (const label of ['Call', 'Intro']) {
    const lead = pattern('lead', label, 'Djembe_1', size, { 0: 'tone', [size - pickupSteps]: 'slap' },
      [{ type: 'in', stepIndex: size - pickupSteps }]);
    timeline.sourcePatterns = [lead];
    state.soloPatternIds = [lead.id];
    assert.equal(ui.buildPracticePlayerPayload()[0].PracticeSections[0].overlapsPracticeCountIn, false,
      'No unrelated change to Call/Intro pickups');
    const bassAtOne = pattern('bass-at-one', 'Begleitung', 'Dreierbass', size, { 0: 'doundoun' },
      [{ type: 'in', stepIndex: 0 }]);
    timeline.sourcePatterns = [lead, bassAtOne];
    state.accompanimentPatternIds = [bassAtOne.id];
    assert.equal(ui.buildPracticePlayerPayload()[0].PracticeSections[0].overlapsPracticeCountIn, false,
      'A bass IN on beat one must not change the parallel Call/Intro pickup policy');
    state.accompanimentPatternIds = [];
  }
}
console.log('Accompaniment count-in: pickup before beat one, solo/alone/target, three grids/tempos, finite/timer loops, audio/scroller parity, immutable source and unchanged Call/Intro checked.');

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
      const scenario = practiceState.practiceScenarios.find(s => s.name === 'Solo 10');
      if (!scenario) throw new Error('Saved Solo 10 scenario missing');
      const before = JSON.stringify(timelineState.sourcePatterns);
      applyPracticeScenario(scenario.id);
      const config = buildPracticePlayerPayload()[0];
      openPracticeAudioPlayer([config]);
      return { config, moving: flattenPracticeScrollerSections(config.PracticeSections),
        timing: getPracticeScrollerInitialLeadInTiming(config, config.PracticeSections),
        selected: getPracticePatternsByIds(practiceState.soloPatternIds).map(p => p.sourceKey),
        lengths: config.PracticeSections.map(getPracticeSectionLength),
        sourceUnchanged: before === JSON.stringify(timelineState.sourcePatterns) };
    });
    assert.deepEqual(result.selected, ['Djembe 3|Solo 9|1'], 'Test the actual stored Solo 10 scenario without changing its selection');
    assert.deepEqual(result.lengths, [4, 64]);
    assert.equal(result.config.PracticeSections[0].trackNotes.Dreierbass[0], 'doundoun');
    assert.equal(result.config.PracticeSections[1].trackNotes.Dreierbass[0], 'kenkeni_doundoun');
    assert(result.sourceUnchanged);
    const timing = assertTiming(result.config, result.moving, result.timing, 4);
    const frame = page.frame({ name: 'practiceAudioFrame' });
    await frame.waitForFunction(() => typeof orderedSections !== 'undefined' && typeof getPracticePlaybackStartTiming === 'function' && Boolean(practiceCountInBuffer));
    const actual = await frame.evaluate(() => ({ timing: getPracticePlaybackStartTiming(),
      pickup: orderedSections[0].overlapsPracticeCountIn, length: orderedSections[0].length }));
    assert(actual.pickup);
    assert.equal(actual.length, 4);
    near(actual.timing.playbackLeadInSeconds, timing.playbackLeadInSeconds, 'Actual iframe uses corrected audio start');
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 });
      await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
      const positions = await page.evaluate(config => {
        practiceState.visible = true;
        renderPracticePanel();
        renderPracticeScrollerFromPayload([config]);
        const preRoll = getPracticeScrollerPreRollLineSteps();
        const bassRow = document.querySelector('.practice-scroller-row.is-dreierbass-row');
        const cell = step => bassRow.querySelector('[data-practice-step-order="' + (preRoll + step) + '"]');
        const center = element => { const rect = element.getBoundingClientRect(); return rect.left + rect.width / 2; };
        const playhead = document.querySelector('.practice-scroller-playhead');
        const stage = document.querySelector('.practice-scroller-stage');
        // Mobile hides the line and highlights notes at the same 24% playback position.
        const playheadX = getComputedStyle(playhead).display === 'none'
          ? stage.getBoundingClientRect().left + stage.clientWidth * 0.24 : center(playhead);
        updatePracticeScrollerPosition(0);
        const pickupError = center(cell(0)) - playheadX;
        const beforeOne = center(cell(4)) - playheadX;
        updatePracticeScrollerPosition(4);
        return { pickupError, beforeOne, oneError: center(cell(4)) - playheadX,
          pickupAfterOne: center(cell(0)) - playheadX, visible: bassRow.getBoundingClientRect().height > 0 };
      }, result.config);
      assert(positions.visible);
      assert(Math.abs(positions.pickupError) < 2, width + ': IN reaches the playback position at its own start ' + JSON.stringify(positions));
      assert(positions.beforeOne > 0, width + ': beat one is still to the right during the IN');
      assert(Math.abs(positions.oneError) < 2, width + ': beat one crosses four steps later');
      assert(positions.pickupAfterOne < 0, width + ': IN is already left of the line at beat one');
      if (process.env.BARABEAT_SCREENSHOT_PREFIX) {
        await page.locator('#practiceScroller').screenshot({ path: process.env.BARABEAT_SCREENSHOT_PREFIX + '-' + width + '.png' });
      }
    }
    await page.context().setOffline(true);
    const offline = await page.evaluate(() => {
      const config = buildPracticePlayerPayload()[0];
      return { overlap: config.PracticeSections[0].overlapsPracticeCountIn,
        timing: getPracticeScrollerInitialLeadInTiming(config, config.PracticeSections) };
    });
    assert.equal(offline.overlap, true);
    assert.deepEqual(offline.timing, result.timing);
    assert.deepEqual(errors, []);
    assert.equal(source('Noten/Balakulania SMF.bbs'), file);
    console.log('Chromium: actual Solo 10 scenario, unchanged selection/source, player iframe and moving notes, desktop/mobile DOM positions, four-step IN before beat one and offline passed.');
    console.log(JSON.stringify({ bpm: result.config.Tempo, pickupBeforeBeatOneMs: (timing.regularLeadInSeconds - timing.playbackLeadInSeconds) * 1000 }));
  } finally {
    await browser.close();
  }
}
