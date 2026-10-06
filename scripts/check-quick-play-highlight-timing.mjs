import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions, near } from './helpers/music-context.mjs';

const editor = source('index.php');
const playerSource = source('Audio/player.html');
let wallTime = 0;
let outputTime = 0;
let nextId = 0;
const timers = new Map();
function note() {
  const classes = new Set();
  const node = { classList: { add: c => classes.add(c), remove: c => classes.delete(c) } };
  return { node, addClass: c => classes.add(c), removeClass: c => classes.delete(c),
    active: () => classes.has('sheet-quick-play-note-active') };
}
const first = note();
const later = note();
const mobile = note();
const frame = { contentWindow: { getEmbeddedQuickPlayPlaybackTime: () => outputTime } };
const state = {
  isPlaying: true, activeHighlightTimers: [],
  highlightSectionsByRuntimeKey: { test: { highlightSteps: [
    [{ sourceBarIndex: 3, sourceStepIndex: 0 }],
    [{ sourceBarIndex: 3, sourceStepIndex: 12 }],
    [{ sourceBarIndex: 8, sourceStepIndex: 22 }],
    [{ sourceBarIndex: 3, sourceStepIndex: 0, mutedOnFinalRepeat: true }]
  ] } },
  noteElementsByPosition: { '3:0': [first], '3:12': [later], '8:22': [later] },
  mobileNoteElementsByPosition: { '3:0': [mobile.node], '8:22': [mobile.node] }
};
const context = vm.createContext({
  sheetQuickPlayState: state,
  window: {
    setTimeout(callback, delay) { const id = ++nextId; timers.set(id, { callback, due: wallTime + delay }); return id; },
    clearTimeout(id) { timers.delete(id); }
  },
  document: { getElementById: () => frame, querySelectorAll: () => [mobile.node] },
  s: { selectAll: selector => selector === '.sheet-quick-play-note-active' ? [first, later] : [] }
});
loadFunctions(context, editor, ['getSheetQuickPlayPositionKey', 'cancelSheetQuickPlayHighlightTimer',
  'scheduleSheetQuickPlayHighlightTimer', 'scheduleSheetQuickPlayNoteHighlights',
  'clearSheetQuickPlayHighlights']);
function send(localStep, scheduledTime, options = {}) {
  context.scheduleSheetQuickPlayNoteHighlights({ runtimeKey: 'test', localStep,
    audioScheduledTime: scheduledTime, delayMs: 100, ...options });
}
function advance(ms, audioDelta = ms / 1000) {
  wallTime += ms;
  outputTime += audioDelta;
  for (const [id, timer] of [...timers]) {
    if (timers.has(id) && timer.due <= wallTime) {
      timers.delete(id);
      timer.callback();
    }
  }
}

send(0, 1);
send(1, 1.3);
advance(1000, 0);
assert.equal(first.active(), false, 'Wall time must not highlight a note while the audio output is still waiting');
assert.equal(later.active(), false, 'Later notes must not run ahead either');
advance(1000, 1);
assert(first.active() && mobile.active(), 'Desktop and mobile highlight the note at its audible time');
assert.equal(later.active(), false);
advance(150);
assert.equal(first.active(), false);
advance(150);
assert(later.active());
context.clearSheetQuickPlayHighlights();
assert.equal(timers.size, 0, 'Stop cancels both pending and active highlights');

// A delayed message must not start its original relative timer again.
outputTime = 5.05;
send(0, 5, { delayMs: 1800 });
advance(0, 0);
assert(first.active(), 'A just-audible note is highlighted immediately despite a delayed message');
advance(150);
send(1, 4, { delayMs: 0 });
advance(0, 0);
assert.equal(later.active(), false, 'An obsolete background notification must not flash an old note');

send(2, outputTime);
advance(0, 0);
assert(later.active() && mobile.active(), 'Transferred IN highlights its original source note');
context.clearSheetQuickPlayHighlights();
send(3, outputTime);
advance(0, 0);
assert.equal(first.active(), false, 'Suppressed final-repeat notes remain unmarked');

send(0, outputTime + 2);
context.clearSheetQuickPlayHighlights();
advance(3000);
assert.equal(first.active(), false, 'No old highlight after stop or reload');
assert.equal(state.activeHighlightTimers.length, 0);

// Older cached players retain the relative-delay fallback during an update.
delete frame.contentWindow.getEmbeddedQuickPlayPlaybackTime;
send(0, undefined);
advance(50);
assert.equal(first.active(), false);
advance(50);
assert(first.active());
advance(150);
assert.equal(state.activeHighlightTimers.length, 0, 'Expired timers do not accumulate in long loops');
for (let repeat = 0; repeat < 100; repeat++) {
  send(0, undefined, { delayMs: 0 });
  advance(0);
  assert(state.activeHighlightTimers.length <= 2, 'Retriggered note timers stay bounded');
}
advance(150);
assert.equal(state.activeHighlightTimers.length, 0);
console.log('Quick-play highlights: audio-clock wait, delayed messages, desktop/mobile source notes, stop and fallback checked.');

const audio = { state: 'running', currentTime: 10, baseLatency: 0.01, outputLatency: 0.2 };
const player = vm.createContext({ instr: { _audioCtx: audio }, performance: { now: () => 2000 } });
loadFunctions(player, playerSource, ['getQuickPlayOutputTime']);
near(player.getQuickPlayOutputTime(), 9.79, 'Reported device latency fallback');
audio.getOutputTimestamp = () => ({ contextTime: 9.5, performanceTime: 1950 });
near(player.getQuickPlayOutputTime(), 9.55, 'Use audible output timestamp without double latency compensation');
audio.getOutputTimestamp = () => ({ contextTime: 9.99, performanceTime: 1950 });
near(player.getQuickPlayOutputTime(), 10, 'Do not advance beyond the audio clock');
audio.state = 'suspended';
assert.equal(player.getQuickPlayOutputTime(), null);
audio.state = 'running';
audio.getOutputTimestamp = () => ({ contextTime: 0, performanceTime: 0 });
near(player.getQuickPlayOutputTime(), 9.79, 'Ignore uninitialized output timestamps');
audio.getOutputTimestamp = () => { throw new Error('unsupported'); };
near(player.getQuickPlayOutputTime(), 9.79, 'Timestamp failures use the latency fallback');
delete audio.baseLatency;
delete audio.outputLatency;
near(player.getQuickPlayOutputTime(), 10, 'Older browsers still use the audio clock');

let sent;
Object.assign(player, {
  embeddedPlayer: true, isTimelineMode: true, isPracticeMode: true, isSheetQuickPlayMode: true,
  playerLaunchKey: 'test', lastNotifiedTimelineBar: null, lastNotifiedTimelineTempo: null,
  getPlaybackSectionContext: () => ({ section: { runtimeKey: 'test', repeatCount: 1 }, localStep: 0, loopCycleIndex: 0 }),
  window: { parent: { postMessage(message) { sent = message; } }, location: { origin: 'https://barabeat.test' } }
});
loadFunctions(player, playerSource, ['notifyEmbeddedPlaybackStep']);
player.notifyEmbeddedPlaybackStep(0, 12);
assert.equal(sent.audioScheduledTime, 12, 'Messages carry the scheduled audio time, not the scheduling cursor');
assert.equal(sent.delayMs, 2000);
console.log('Quick-play output clock: device timestamp, startup/suspend, latency fallbacks and player message checked.');
