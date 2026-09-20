import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, near } from './helpers/music-context.mjs';

const player = source('Audio/player.html');
const hits = [];
const context = vm.createContext({
  initialTempo: 80, tempo: 80, currentBaseTempo: 80, rhythmType: 'tenaer',
  getPracticeInstrumentVolume: () => 1, getPracticeInstrumentToneVolume: () => 1,
  getFeelOffsetSeconds: () => 0, sangbanStrokeGainMultiplier: 2.2,
  instr: { _audioCtx: { currentTime: 0 } },
  djembeHandStates: { Djembe_1: {}, Djembe_2: {}, Djembe_3: {} },
  playSampleToDestination: (instrument, sample, time) => hits.push({ sample, time })
});
for (const name of ['kenkeni', 'sangban', 'doundoun', 'dreierbass', 'djembe_1', 'djembe_2', 'djembe_3']) {
  context[name] = { play: (sample, time) => hits.push({ sample, time }) };
}
installTiming(context);
loadFunctions(context, source('index.php'), ['mergePercussionNote']);
loadFunctions(context, player, [
  'scheduleNote', 'scheduleNoteToDestination', 'parseTupletNoteValue', 'clonePlaybackWithNote',
  'resolveDjembeSampleName', 'playDjembeStroke', 'playDjembeStrokeToDestination',
  'getTupletNoteOffsets', 'getTupletBeatDurationSeconds', 'getBaseStepDuration', 'getStepsPerBeatForRhythm'
]);
function play(note, bpm = 80, exportAudio = false, bassNote = null) {
  hits.length = 0;
  const args = [null, null, null, bassNote, note ? { note, handMode: 'auto' } : null, null, null, 1, 1];
  if (exportAudio) context.scheduleNoteToDestination(...args, {}, {}, context.djembeHandStates, bpm);
  else context.scheduleNote(...args, bpm);
  return hits.slice();
}

for (const bpm of [60, 80, 120]) {
  for (const flam of ['tone_flam', 'slap_flam', 'bass_slap_flam']) {
    const live = play(flam, bpm);
    assert.equal(live.length, 2);
    near(live[0].time, 1, 'Flam first stroke');
    near(live[1].time, 1 + 5 / bpm, 'Flam second stroke');
    assert.deepEqual(play(flam, bpm, true), live, 'Live/export Flam timing and samples');
  }
}
for (const rhythm of ['binaer', 'tenaer', 'neunaer']) {
  context.rhythmType = rhythm;
  const tuplet = play('tuplet:triplet:tone|slap|bass');
  tuplet.forEach((hit, i) => near(hit.time, 1 + i * 0.25, 'Three strokes within one beat'));
  assert.deepEqual(play('tuplet:triplet:tone|slap|bass', 80, true), tuplet);
}

// INTENTIONAL MUSICAL RULE: one Ballet-Dunun player has two hands.
// Preserve at most two strokes per position, including when three symbols were entered.
// Retaining the first supported pair is current compatibility behavior, not a new priority rule.
for (const [symbols, expected] of [
  [['slap', 'tone', 'bass'], 'kenkeni_sangban'],
  [['tone', 'slap', 'bass'], 'kenkeni_sangban'],
  [['slap', 'bass', 'tone'], 'kenkeni_doundoun'],
  [['bass', 'slap', 'tone'], 'kenkeni_doundoun'],
  [['tone', 'bass', 'slap'], 'sangban_doundoun'],
  [['bass', 'tone', 'slap'], 'sangban_doundoun'],
  [['slap_muffled', 'tone', 'bass'], 'kenkeni_muffled_sangban']
]) {
  const encoded = symbols.reduce((value, symbol) => context.mergePercussionNote(value, symbol, 'Dreierbass'), 'f');
  assert.equal(encoded, expected);
  const live = play(null, 80, false, encoded);
  assert.equal(live.length, 2, 'Ballet Dununs must sound exactly the retained pair');
  assert.deepEqual(play(null, 80, true, encoded), live, 'Export retains the same two strokes');
}

// Execute the real WAV export loop; only WebAudio rendering/download are test doubles.
let renderedFrames = 0;
let downloaded = '';
Object.assign(context, {
  audioIsReady: true, exportWavButton: { disabled: false, textContent: 'WAV' },
  playerText: key => key, obj: [{ Name: 'Timing fixture' }],
  soloTrackName: '', trackStates: { Djembe_1: 'djembe' },
  getTrackPlaybackAtStep: track => track === 'Djembe_1' ? { note: 'slap', handMode: 'auto' } : null,
  getAccentMultiplier: () => 1, advanceSilentH2HStep: () => {}, getH2HRestMuteSampleName: () => '',
  h2hRestMuteGainMultipliers: { Djembe_1: 1, Djembe_2: 1, Djembe_3: 1 },
  getEffectiveTempoForStep: () => 80,
  OfflineAudioContext: class {
    constructor(channels, frames, sampleRate) {
      assert.equal(channels, 2); assert.equal(sampleRate, 48000);
      renderedFrames = frames; this.destination = {};
    }
    async startRendering() { return {}; }
  },
  encodeWavFromAudioBuffer: () => ({}), downloadBlob: (blob, name) => { downloaded = name; }
});
loadFunctions(context, player, ['buildSwingStepOffsets', 'getBinaerSwingStepOffsets',
  'getTenaerSwingStepOffsets', 'getNeunaerSwingStepOffsets', 'getPlaybackRhythmStep',
  'getStepInterval', 'exportCurrentArrangementAsWav']);
const fixtures = JSON.parse(source('scripts/fixtures/timing-cases.json'));
for (const fixture of fixtures.cases) {
  for (const quick of [false, true]) {
    context.rhythmType = fixture.rhythm;
    context.swingProfile = { [fixture.rhythm]: fixture.swing };
    context.isSheetQuickPlayMode = quick;
    context.orderedSections = [{ isLeadIn: true, playbackLength: 2 }];
    context.getExportStepCount = () => fixture.stepsPerBeat + 2;
    hits.length = 0;
    await context.exportCurrentArrangementAsWav();
    let time = 0;
    assert.equal(hits.length, fixture.stepsPerBeat + 2);
    hits.forEach((hit, step) => {
      near(hit.time, time, 'WAV event position');
      const phase = (step - (quick ? 2 : 0) + fixture.stepsPerBeat) % fixture.stepsPerBeat;
      time += fixture.beatIntervals[phase];
    });
    assert(Math.abs(renderedFrames - Math.ceil((time + 3.5) * 48000)) <= 1, 'WAV duration includes the existing tail');
    assert.equal(downloaded, 'Timing fixture.wav');
    assert.equal(context.exportWavButton.disabled, false);
  }
}
console.log('Audio: Flam/tuplets, intentional two-stroke Ballet-Dunun limit and actual WAV scheduling/duration checked.');
