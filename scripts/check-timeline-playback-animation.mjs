import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const playerSource = fs.readFileSync(new URL('../Audio/player.html', import.meta.url), 'utf8');
const timelineSource = fs.readFileSync(new URL('../JS/timeline.js', import.meta.url), 'utf8');
function extractFunction(source, name) {
  const start = source.indexOf('function ' + name + '(');
  const end = source.indexOf('\nfunction ', start + 1);
  assert(start >= 0 && end > start, name + ' missing');
  return source.slice(start, end);
}
function near(actual, expected, message) {
  assert(Math.abs(actual - expected) < 0.00001, `${message}: ${actual} != ${expected}`);
}

const player = vm.createContext({
  stepsPerBar: 24,
  interval: 0.1,
  getTimelineStepsPerBar() { return player.stepsPerBar; },
  getStepInterval() { return player.interval; },
  sections: [{ startStep: 0, playbackLength: 9 }, { startStep: 9, playbackLength: 72 }]
});
vm.runInContext(extractFunction(playerSource, 'getTimelinePlaybackBarTiming'), player);
function timing(step) {
  player.step = step;
  return vm.runInContext('getTimelinePlaybackBarTiming(sections, step)', player);
}
near(timing(0).durationMs, 900, 'Shortened first bar');
near(timing(6).durationMs, 300, 'Pickup remaining duration');
near(timing(6).progress, 2 / 3, 'Pickup starts within the preceding bar');
near(timing(9).durationMs, 2400, 'Following full bar');
near(timing(21).progress, 0.5, 'Midbar restart');
assert.equal(timing(81), null, 'No motion beyond the final bar');
player.interval = 0.05;
near(timing(21).durationMs, 600, 'Tempo change adjusts remaining duration');
for (const steps of [18, 24, 32]) {
  player.stepsPerBar = steps;
  player.sections = [{ startStep: 0, playbackLength: steps * 120 }];
  player.getStepInterval = step => step % 2 ? 0.075 : 0.125;
  near(timing(steps * 98).durationMs, steps * 100, 'Bar duration includes swing intervals');
  near(timing(steps * 98 + 1).progress, 0.125 / (steps * 0.1), 'Swing pickup phase');
}

let wallTime = 0;
let audioTime = 10;
let nextId = 1;
let geometryReads = 0;
let reducedMotion = false;
const timers = new Map();
const frames = new Map();
const listeners = new Map();
const classes = new Set();
const track = { isConnected: true, scrollLeft: 0, classList: { remove() {} } };
const bar = {
  dataset: { timelineBar: '1' },
  classList: {
    add(name) { classes.add(name); },
    remove(name) { classes.delete(name); },
    toggle(name, enabled) { if (enabled) classes.add(name); else classes.delete(name); }
  },
  setAttribute() {}, removeAttribute() {}
};
const frame = {
  dataset: { audioLaunchKey: 'current' },
  contentWindow: { getEmbeddedTimelinePlaybackTime: () => audioTime }
};
const context = vm.createContext({
  performance: { now: () => wallTime },
  window: {
    location: { origin: 'https://barabeat.test' },
    addEventListener(type, handler) { listeners.set(type, handler); },
    matchMedia: () => ({ matches: reducedMotion }),
    setTimeout(handler, delay) { const id = nextId++; timers.set(id, { handler, due: wallTime + delay }); return id; },
    clearTimeout(id) { timers.delete(id); },
    requestAnimationFrame(handler) { const id = nextId++; frames.set(id, handler); return id; },
    cancelAnimationFrame(id) { frames.delete(id); }
  },
  document: {
    getElementById: () => frame,
    querySelectorAll: selector => selector.includes('ruler-bar') ? [bar] : [track],
    querySelector(selector) {
      const match = selector.match(/data-timeline-bar="(\d+)"/);
      if (match) bar.dataset.timelineBar = match[1];
      return bar;
    }
  },
  measureScroll(barEl, progress) {
    geometryReads++;
    const geometry = { scrollEl: track, baseLeft: (Number(barEl.dataset.timelineBar) - 1) * 108,
      barWidth: 108, maximumScrollLeft: 120 * 108 };
    track.scrollLeft = geometry.baseLeft + 108 * progress;
    return geometry;
  }
});
const start = timelineSource.indexOf('let timelinePlaybackHighlightedBar');
const end = timelineSource.indexOf('const timelineTypeLabelKeys');
vm.runInContext(timelineSource.slice(start, end), context);
vm.runInContext('scrollTimelinePlaybackBarIntoView = function(bar, progress = 0) { return measureScroll(bar, progress); };', context);

function send(data) {
  listeners.get('message')({ origin: 'https://barabeat.test', source: frame.contentWindow,
    data: { launchKey: 'current', ...data } });
}
function sendBar(number, options = {}) {
  send({ type: 'barabeat-audio-step', timelineBar: number, timelineBarDurationMs: 2000,
    timelineScheduledTime: audioTime, timelineBarProgress: 0, delayMs: 0, ...options });
}
function advance(wallDelta, audioDelta = wallDelta / 1000) {
  wallTime += wallDelta;
  audioTime += audioDelta;
  for (const [id, timer] of [...timers]) {
    if (timers.has(id) && timer.due <= wallTime) { timers.delete(id); timer.handler(); }
  }
  for (const [id, handler] of [...frames]) {
    frames.delete(id);
    handler();
  }
  assert(frames.size <= 1, 'Only one animation loop may run');
}

send({ type: 'barabeat-audio-state', state: 'playing' });
sendBar(1, { delayMs: 500, timelineScheduledTime: audioTime + 0.5 });
advance(250);
assert.equal(frames.size, 0, 'No animation before the scheduled audio start');
advance(250);
near(track.scrollLeft, 0, 'First bar begins at the anchor');
const readsAtStart = geometryReads;
advance(500);
near(track.scrollLeft, 27, 'Quarter bar');
advance(500);
near(track.scrollLeft, 54, 'Half bar');
advance(1000, 0);
near(track.scrollLeft, 54, 'Suspended audio freezes the scroll despite wall time advancing');
advance(1000);
near(track.scrollLeft, 108, 'Full bar');
assert.equal(geometryReads, readsAtStart, 'No per-frame layout measurement');
sendBar(2);
advance(0);
near(track.scrollLeft, 108, 'Next bar connects without a jump');
advance(500);
near(track.scrollLeft, 135, 'Next bar moves continuously');
sendBar(2, { timelineBarDurationMs: 750, timelineBarProgress: 0.25 });
advance(0);
near(track.scrollLeft, 135, 'Midbar tempo update preserves position');
advance(375);
near(track.scrollLeft, 175.5, 'New tempo applies to the remainder');
listeners.get('resize')();
near(track.scrollLeft, 175.5, 'Resize preserves current audio phase');
advance(375);
near(track.scrollLeft, 216, 'Tempo-adjusted bar finishes exactly');

sendBar(99, { delayMs: 500, timelineScheduledTime: audioTime + 0.5 });
advance(1000);
near(track.scrollLeft, 98 * 108 + 27, 'Late timer catches up to audio, not its callback time');
sendBar(120, { timelineBarProgress: 0.75, timelineBarDurationMs: 500 });
advance(0);
near(track.scrollLeft, 119.75 * 108, 'Pickup start in final bar');
advance(500);
near(track.scrollLeft, 120 * 108, 'Final bar runs to its end');
sendBar(1);
advance(0);
send({ type: 'barabeat-audio-state', state: 'ended', delayMs: 2000 });
advance(1000);
assert(classes.has('is-playing'), 'Scheduled end must not clear still-audible playback');
advance(1000);
assert(!classes.has('is-playing'));
assert.equal(frames.size, 0, 'Natural end cancels animation');
sendBar(2, { delayMs: 100 });
send({ type: 'barabeat-audio-state', state: 'stopped' });
advance(500);
assert.equal(timers.size, 0, 'Stop clears pending bar notifications');
assert.equal(frames.size, 0, 'Stop clears animation');
sendBar(3, { launchKey: 'obsolete' });
advance(0);
assert.equal(frames.size, 0, 'Obsolete player cannot restart animation');

reducedMotion = true;
sendBar(5);
advance(0);
near(track.scrollLeft, 4 * 108, 'Reduced motion retains per-bar following');
assert.equal(frames.size, 0);
reducedMotion = false;
delete frame.contentWindow.getEmbeddedTimelinePlaybackTime;
sendBar(6);
advance(0);
advance(500);
near(track.scrollLeft, 5.25 * 108, 'Wall clock fallback when an older player has no clock accessor');
sendBar(7, { timelineBarDurationMs: undefined });
advance(0);
near(track.scrollLeft, 6 * 108, 'Older messages retain bar-wise following');
assert.equal(frames.size, 0);

console.log('Timeline animation: audio timing, swing, pickup, late timers, tempo, resize, stop and fallback checked.');
