import assert from 'node:assert/strict';
import vm from 'node:vm';
import { createRequire } from 'node:module';
import { source, loadFunctions, installTiming, near } from './helpers/music-context.mjs';
import { verifyWavEncoding } from './helpers/wav-level-checks.mjs';

const hits = [];
function node(properties = {}) {
  return { ...properties, connect(next) { this.next = next; return next; }, disconnect() {} };
}
const audio = {
  currentTime: 0, destination: {},
  createGain: () => node({ gain: { value: 1 } }),
  createStereoPanner: () => node({ pan: { value: 0 } }),
  createBufferSource() {
    return node({ start(time) {
      let gain = 1, pan = 0;
      for (let output = this.next; output; output = output.next) {
        if (output.gain) gain *= output.gain.value;
        if (output.pan) pan = output.pan.value;
      }
      hits.push({ sample: this.buffer.name, time, gain, pan });
    } });
  }
};
let volume = 0.45, toneVolume = 0.6;
const context = vm.createContext({
  console, window: { sharedAudioContext: audio }, navigator: {},
  tempo: 100, initialTempo: 100, currentBaseTempo: 100, rhythmType: 'tenaer',
  instr: { _audioCtx: audio }, sangbanStrokeGainMultiplier: 2.2,
  getPracticeInstrumentVolume: track => track === 'Dreierbass' ? volume : 1,
  getPracticeInstrumentToneVolume: (track, tone) => {
    assert.equal(track, 'Dreierbass');
    assert(['sangban', 'kenkeni', 'doundoun'].includes(tone), 'Flams use the existing drum volume controls');
    return toneVolume;
  },
  getFeelOffsetSeconds: track => track === 'Dreierbass' ? 0.013 : 0,
  kenkeni: {}, sangban: {}, doundoun: {}, djembe_1: {}, djembe_2: {}, djembe_3: {},
  djembeHandStates: { Djembe_1: {}, Djembe_2: {}, Djembe_3: {} }
});
vm.runInContext(source('Audio/js/instrument_2.js') + '\nvar dreierbass = new Instrumente([], -1, 1.5);', context);
for (const name of ['Sangban_Open', 'Kenkeni_Open', 'Doundoun_Open']) {
  context.dreierbass._snd[name] = { name, duration: 0.5 };
}
installTiming(context);
loadFunctions(context, source('index.php'), ['mergePercussionNote', 'createTupletNoteValue', 'getMobileSheetNoteParts']);
loadFunctions(context, source('JS/practice.js'), ['getPracticeScrollerSymbolParts']);
loadFunctions(context, source('Audio/player.html'), ['scheduleNote', 'scheduleNoteToDestination',
  'parseTupletNoteValue', 'playSampleToDestination', 'getPracticeInstrumentVolumeForInstance',
  'getTupletNoteOffsets', 'getTupletBeatDurationSeconds', 'getBaseStepDuration', 'getStepsPerBeatForRhythm']);
function play(note, bpm, offline = false, time = 1) {
  hits.length = 0;
  const args = [null, null, null, note, null, null, null, time, 0.75];
  if (offline) context.scheduleNoteToDestination(...args, audio, audio.destination, context.djembeHandStates, bpm);
  else context.scheduleNote(...args, bpm);
  return hits.slice();
}
function compareHits(actual, expected) {
  assert.equal(actual.length, expected.length);
  actual.forEach((hit, index) => {
    assert.equal(hit.sample, expected[index].sample);
    near(hit.time, expected[index].time, 'Live/WAV timing');
    near(hit.gain, expected[index].gain, 'Live/WAV gain');
    assert.equal(hit.pan, expected[index].pan);
  });
}
for (const [symbol, value, sample, lane] of [
  ['tone_flam', 'sangban_flam', 'Sangban_Open', 'middle'],
  ['slap_flam', 'kenkeni_flam', 'Kenkeni_Open', 'top']
]) {
  assert.equal(context.mergePercussionNote('f', symbol, 'Dreierbass'), value);
  assert.equal(context.mergePercussionNote('f', symbol, 'Djembe 1'), symbol, 'Djembe encoding is unchanged');
  for (const renderer of [context.getMobileSheetNoteParts, context.getPracticeScrollerSymbolParts]) {
    const parts = renderer(value);
    assert.equal(parts.length, 2);
    assert(parts.every(part => part.lane === lane), 'Both visible strokes stay on their drum lane');
  }
  for (const bpm of [40, 60, 90, 120, 180, 240]) {
    const live = play(value, bpm);
    assert.equal(live.length, 2);
    for (const [index, hit] of live.entries()) {
      assert.equal(hit.sample, sample);
      near(hit.time, 1.013 + index * 5 / bpm, 'BPM-dependent Flam, including feel offset');
      near(hit.gain, 1.5 * volume * toneVolume * 0.75, 'Instrument, drum and accent gain applied once');
      assert.equal(hit.pan, -1, 'Existing Ballet Dununs panorama');
    }
    compareHits(play(value, bpm, true), live);
  }
  for (const extra of ['slap', 'tone', 'bass', 'tone_flam', 'slap_flam']) {
    assert.equal(context.mergePercussionNote(value, extra, 'Dreierbass'), value, 'A Flam already uses both strokes');
  }
  assert.equal(context.mergePercussionNote('kenkeni_sangban', symbol, 'Dreierbass'), 'kenkeni_sangban', 'An existing pair is never replaced');
  for (const mutedControl of ['instrument', 'drum']) {
    const originalVolume = volume, originalToneVolume = toneVolume;
    if (mutedControl === 'instrument') volume = 0;
    else toneVolume = 0;
    assert.equal(play(value, 100).length, 0, 'Live muting covers both Flam strokes');
    assert(play(value, 100, true).every(hit => hit.gain === 0), 'Export muting covers both Flam strokes');
    volume = originalVolume;
    toneVolume = originalToneVolume;
  }
  audio.currentTime = 2;
  const late = play(value, 100, false, 0);
  near(late[0].time, 2.033, 'Late live scheduling preserves its safety margin and feel');
  near(late[1].time - late[0].time, 0.05, 'Late scheduling keeps the Flam separation');
  audio.currentTime = 0;
}
for (const rhythm of ['binaer', 'tenaer', 'neunaer']) {
  context.rhythmType = rhythm;
  const tuplet = context.createTupletNoteValue('triplet', ['tone_flam', 'slap_flam', 'bass'], 'Dreierbass');
  const live = play(tuplet, 120);
  assert.equal(live.length, 5, 'Two Flams and one bass inside a triplet');
  near(live[2].time, 1.013 + 0.5 / 3, 'Tuplet subdivision preserved');
  compareHits(play(tuplet, 120, true), live);
}
const offlineReader = vm.createContext({});
loadFunctions(offlineReader, source('app-shell.html'), ['mergePercussionNote']);
assert.equal(offlineReader.mergePercussionNote('f', 'tone_flam', 'Dreierbass'), 'sangban_flam', 'Offline shell must include the updated reader');
assert.equal(offlineReader.mergePercussionNote('f', 'slap_flam', 'Dreierbass'), 'kenkeni_flam');
console.log('Ballet Dununs Flams: reader, both drums, six BPM values, actual live/export gain nodes, panorama, mute, feel, late scheduling, tuplets, symbols and two-stroke limit checked.');

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
    assert(await page.evaluate(content => loadRhythmContent('Flam test', content, null, { remember: false }),
      source('Noten/Balakulania SMF.bbs')));
    const payloads = await page.evaluate(() => {
      // Modify only the isolated in-memory test score, never the BBS file.
      const read = callPHPScript_lesen(zeilenAnzahl, { showAlert: false, updateQuickPlaySelectors: false });
      const config = getReadRhythmConfig();
      const changed = [];
      for (const [oldNote, symbol] of [['sangban', 'tone_flam'], ['kenkeni', 'slap_flam']]) {
        const bar = read.rhythmBars.find(b => b.effectiveInstrument === 'Dreierbass' && b.notes.includes(oldNote));
        if (!bar) throw new Error('Missing Ballet Dununs test note: ' + oldNote);
        const step = bar.notes.indexOf(oldNote);
        const element = Array.from(s.selectAll('.shp')).find(el => {
          if (el.attr('id') !== (oldNote === 'sangban' ? 'tone' : 'slap')) return false;
          const pos = getElementReadPosition(el);
          const info = getBarIndexFromPosition(pos.x, pos.y, config, zeilenAnzahl);
          return info.barIndex === read.rhythmBars.indexOf(bar) && getStepIndexWithinBar(info.lineSlotIndex, config.stepsPerBar) === step;
        });
        if (!element) throw new Error('Missing SVG test note');
        element.attr({ id: symbol });
        changed.push({ bar: bar.index, step });
      }
      const updated = callPHPScript_lesen(zeilenAnzahl, { showAlert: false, updateQuickPlaySelectors: false });
      syncTimelineStateFromReadResult(updated);
      renderSheetQuickPlaySelectors(updated);
      const patterns = timelineState.sourcePatterns.filter(p => p.instrument === 'Dreierbass' &&
        p.bars.some(b => b.notes.some(n => n === 'sangban_flam' || n === 'kenkeni_flam')));
      sheetQuickPlayState.selectedPatternIds = patterns.map(p => p.id);
      Object.assign(practiceState, { soloPatternIds: patterns.map(p => p.id), accompanimentPatternIds: [],
        repeatCount: 1, timerMinutes: 0, loopsWithSolo: 1, loopsWithoutSolo: 0 });
      return {
        quick: buildSheetQuickPlayPayload(), practice: buildPracticePlayerPayload(),
        arrangement: buildTimelinePlayerPayload(patterns, patterns.map(p => cloneTimelineEntryFromPattern(p))), changed
      };
    });
    assert.equal(payloads.changed.length, 2);
    for (const mode of ['quick', 'practice', 'arrangement']) {
      for (const token of ['sangban_flam', 'kenkeni_flam']) assert(JSON.stringify(payloads[mode]).includes(token), mode + ': ' + token);
      const playerUrl = await page.evaluate(payload => {
        openTimelineAudioPlayer(payload);
        return document.getElementById('timelineAudioFrame').src;
      }, payloads[mode]);
      const frame = page.frame({ name: 'timelineAudioFrame' });
      await frame.waitForURL(playerUrl);
      await frame.waitForFunction(() => typeof audioIsReady !== 'undefined' && audioIsReady);
      const heard = await frame.evaluate(() => {
        const notes = [];
        for (let step = 0; step < oneShotLength; step++) notes.push(getTrackPlaybackAtStep('Dreierbass', trackStates.Dreierbass, step).note);
        return notes;
      });
      assert(heard.includes('sangban_flam') && heard.includes('kenkeni_flam'), mode + ': actual player retains both Flams');
    }
    const frame = page.frame({ name: 'timelineAudioFrame' });
    const rendered = await frame.evaluate(async verifySource => {
      const verify = eval('(' + verifySource + ')');
      await ensurePracticeTrackSoundsLoaded(['Dreierbass']);
      const results = [];
      for (const note of ['sangban_flam', 'kenkeni_flam']) {
        const audio = new OfflineAudioContext(2, 96000, 48000);
        scheduleNoteToDestination(null, null, null, note, null, null, null, 0.25, 1, audio, audio.destination, {}, 120);
        results.push({ note, ...await verify(await audio.startRendering(), encodeWavFromAudioBuffer) });
      }
      return results;
    }, verifyWavEncoding.toString());
    for (const result of rendered) assert(result.peak > 0 && result.clippedAfter === 0, result.note + ': real sample renders to unclipped WAV');
    for (const viewport of [{ width: 1440, height: 1000 }, { width: 390, height: 844 }]) {
      await page.setViewportSize(viewport);
      const visible = await page.evaluate(() => {
        const host = document.createElement('div');
        host.dataset.flamTest = 'true';
        host.style.cssText = 'position:fixed;top:40px;left:40px;z-index:99999;background:white;padding:20px';
        document.body.appendChild(host);
        const results = [];
        for (const [flam, single] of [['sangban_flam', 'sangban'], ['kenkeni_flam', 'kenkeni']]) {
          const symbol = createPracticeScrollerNoteSymbol(flam);
          const normal = createPracticeScrollerNoteSymbol(single);
          const row = document.createElement('div');
          row.style.cssText = 'display:flex;gap:30px;align-items:center;height:50px';
          row.append(normal, symbol);
          host.appendChild(row);
          const parts = Array.from(symbol.querySelectorAll('.practice-symbol-part')).map(p => p.getBoundingClientRect());
          const ref = normal.querySelector('.practice-symbol-part').getBoundingClientRect();
          results.push({ separated: parts[0].x < parts[1].x,
            aligned: parts.every(p => Math.abs(p.y + p.height / 2 - ref.y - ref.height / 2) < 1),
            visible: parts.every(p => p.width > 0 && p.height > 0), flam, referenceY: ref.y,
            partYs: parts.map(p => p.y), normalHeight: normal.getBoundingClientRect().height,
            flamHeight: symbol.getBoundingClientRect().height,
            transforms: Array.from(symbol.children).map(p => getComputedStyle(p).transform) });
          const svg = createMobileSheetSvgElement('svg', { width: 200, height: 60 });
          appendMobileSheetNote(svg, flam, 80, 40, 30);
          host.appendChild(svg);
          if (!svg.children.length) throw new Error('Missing mobile Flam symbol');
        }
        return results;
      });
      for (const result of visible) assert(result.separated && result.aligned && result.visible, JSON.stringify({ viewport, result }));
      if (process.env.BARABEAT_FLAM_TEST_DIR) {
        await page.locator('[data-flam-test]').screenshot({ path: process.env.BARABEAT_FLAM_TEST_DIR + '/ballet-flams-' + viewport.width + '.png' });
      }
      await page.locator('[data-flam-test]').evaluate(host => host.remove());
    }
    await page.context().setOffline(true);
    assert(await page.evaluate(() => JSON.stringify(buildSheetQuickPlayPayload()).includes('sangban_flam')));
    assert.deepEqual(errors, []);
    console.log('Chromium: real SVG reader, Quick Play/practice/arrangement player, real sample WAV rendering, desktop/mobile symbols and offline preparation checked.');
    console.table(rendered.map(({ note, peak, gain, clippedAfter }) => ({ note, peak, gain, clippedAfter })));
  } finally {
    await browser.close();
  }
}
