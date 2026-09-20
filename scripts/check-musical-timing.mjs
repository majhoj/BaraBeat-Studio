import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, installTiming, near } from './helpers/music-context.mjs';

const player = source('Audio/player.html');
const fixtures = JSON.parse(source('scripts/fixtures/timing-cases.json'));
assert.equal(fixtures.version, 1);
const context = vm.createContext({
  initialTempo: 80, currentBaseTempo: 80, globalPlaybackStep: 0, nextNoteTime: 0,
  isPracticeMode: true, isSheetQuickPlayMode: true,
  orderedSections: [{ isLeadIn: true, playbackLength: 2 }],
  getPlaybackSectionContext: () => null,
  updateDisplayedTempo: () => {},
  shekereBeatEnabled: true, practiceCountInBuffer: {}, shekereBeatAnticipationSeconds: 0,
  practiceCountInBeats: 4, playerStartDelay: 0.18, practiceLeadInDelay: 3
});
installTiming(context);
loadFunctions(context, player, [
  'getTimelineStepsPerBar', 'getStepsPerBeatForRhythm', 'getBaseStepDuration',
  'buildSwingStepOffsets', 'getBinaerSwingStepOffsets', 'getTenaerSwingStepOffsets',
  'getNeunaerSwingStepOffsets', 'getPlaybackRhythmStep', 'getEffectiveTempoForStep',
  'normalizeSectionTempo', 'getStepInterval', 'nextNote', 'scheduleShekereBeatIfNeeded',
  'getPracticeCountInBeatDuration', 'getPracticeCountInDuration',
  'getPracticeCountInOverlapDuration', 'getPracticePlaybackStartTiming', 'schedulePracticeCountIn'
]);

for (const fixture of fixtures.cases) {
  context.rhythmType = fixture.rhythm;
  context.swingProfile = { [fixture.rhythm]: fixture.swing };
  assert.equal(context.getTimelineStepsPerBar(), fixture.stepsPerBar);
  assert.equal(context.getStepsPerBeatForRhythm(), fixture.stepsPerBeat);
  near(context.getBaseStepDuration(80), 0.75 / fixture.stepsPerBeat, 'Straight step duration');
  for (const mode of ['quick', 'practice', 'arrangement']) {
    context.isSheetQuickPlayMode = mode === 'quick';
    context.isPracticeMode = mode !== 'arrangement';
    for (const pickup of [0, 2]) {
      context.orderedSections = pickup ? [{ isLeadIn: true, playbackLength: pickup }] : [];
      context.nextNoteTime = 0;
      context.globalPlaybackStep = 0;
      const hits = [];
      context.scheduleShekereHit = time => hits.push(time);
      let expectedTime = 0;
      for (let step = 0; step < fixture.stepsPerBar + pickup; step++) {
        const phase = ((step - (mode === 'quick' ? pickup : 0)) % fixture.stepsPerBeat + fixture.stepsPerBeat) % fixture.stepsPerBeat;
        const expected = fixture.beatIntervals[phase];
        near(context.getStepInterval(step), expected, `${fixture.rhythm}/${mode}: interval ${step}`);
        context.scheduleShekereBeatIfNeeded(step, step);
        const before = context.nextNoteTime;
        context.nextNote();
        near(context.nextNoteTime - before, expected, `${fixture.rhythm}/${mode}: LIVE interval ${step}`);
        expectedTime += expected;
        near(context.nextNoteTime, expectedTime, 'Live and duration/export accumulated time');
        assert.equal(context.globalPlaybackStep, step + 1);
      }
      assert.equal(hits[0], mode === 'quick' ? pickup : 0, 'Shekere stays on beat one');
    }
  }
  context.isSheetQuickPlayMode = false;
  context.swingProfile = {};
  let barSeconds = 0;
  for (let step = 0; step < fixture.stepsPerBar; step++) barSeconds += context.getStepInterval(step);
  near(barSeconds, fixture.rhythm === 'neunaer' ? 2.25 : 3, 'Straight bar duration');
}

// Count-in is four beats; a two-step pickup occupies its end, not an extra bar.
context.rhythmType = 'tenaer';
context.isPracticeMode = true;
context.orderedSections = [{ overlapsPracticeCountIn: true, playbackLength: 2 }];
const timing = context.getPracticePlaybackStartTiming();
near(timing.countInDuration, 3, 'Four count-in beats at BPM 80');
near(timing.regularLeadInSeconds, 3.18, 'Original lead-in delay');
near(timing.playbackLeadInSeconds, 2.93, 'Pickup starts before beat one');
const countHits = [];
context.instr = { _audioCtx: {} };
context.scheduleShekereHit = time => countHits.push(time);
context.schedulePracticeCountIn(3.18);
countHits.forEach((time, i) => near(time, 0.18 + i * 0.75, 'Count-in hit'));
assert.equal(countHits.length, 4);

// The existing section tempo interpolation must drive both live and export timing.
context.getPlaybackSectionContext = step => ({
  section: { tempoStart: 60, tempoTarget: 120, tempoRampSteps: 12 }, sectionStep: step
});
for (const [step, bpm] of [[0, 60], [6, 90], [12, 120]]) {
  context.globalPlaybackStep = step;
  context.nextNoteTime = 0;
  near(context.getEffectiveTempoForStep(step), bpm, 'Tempo interpolation');
  near(context.getStepInterval(step), 60 / bpm / 6, 'Tempo-dependent export interval');
  context.nextNote();
  near(context.nextNoteTime, 60 / bpm / 6, 'Tempo-dependent live interval');
}
console.log('Musical timing: all grids, exact swing/pickup intervals, live/export parity, beat, count-in and section tempo checked.');
