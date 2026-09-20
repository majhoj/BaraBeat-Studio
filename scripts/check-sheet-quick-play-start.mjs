import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const editor = fs.readFileSync(new URL('../index.php', import.meta.url), 'utf8');
const player = fs.readFileSync(new URL('../Audio/player.html', import.meta.url), 'utf8');
function extract(source, name) {
  const match = new RegExp('(?:async )?function ' + name + '\\(').exec(source);
  assert(match, name + ' missing');
  const rest = source.slice(match.index);
  const end = /\n(?:async )?function |\nwindow\.|\nplayButton\.addEventListener|\ninitializeSheetQuickPlayLiveRefresh\(\);/.exec(rest);
  assert(end, name + ' end missing');
  return rest.slice(0, end.index);
}
function button() {
  return { disabled: false, textContent: '', dataset: {}, classList: { toggle() {} }, setAttribute() {} };
}

for (const mobile of [false, true]) {
  let gesture = false;
  let starts = 0;
  let loads = 0;
  let pumps = 0;
  let tempo = 90;
  let timerId = 0;
  const timeouts = new Map();
  const intervals = new Map();
  const errors = [];
  const bpm = { value: '90', dispatchEvent(event) { assert.equal(event.type, 'input'); } };
  const frame = { name: 'sheetQuickPlayFrame', src: '', dataset: {},
    contentDocument: { querySelector: selector => selector === '#bpm' ? bpm : {
      disabled: false, dataset: {}, click() { assert.fail('No synthetic Play clicks'); }
    } }, contentWindow: {} };
  const elements = { sheetQuickPlayFrame: frame, sheetQuickPlayButton: button() };
  if (mobile) elements.mobileSheetQuickPlayButton = button();
  const state = { selectedPatternIds: [], patternLibrary: [], isPlaying: false, isStarting: false,
    frameReady: false, preparedSignature: '', preparationTimer: null, schedulerTimer: null };
  const context = vm.createContext({
    sheetQuickPlayState: state, sheetQuickPlayRefreshPending: false,
    timelineState: { tempo: 100 },
    document: { getElementById: id => elements[id] || null },
    window: { location: { origin: 'http://localhost' },
      setTimeout(fn) { const id = ++timerId; timeouts.set(id, fn); return id; },
      clearTimeout(id) { timeouts.delete(id); },
      setInterval(fn) { const id = ++timerId; intervals.set(id, fn); return id; },
      clearInterval(id) { intervals.delete(id); } },
    isMobilePracticeViewport: () => mobile,
    uiText: key => key,
    alert: message => errors.push(message),
    positionMobileSheetQuickPlayFrame() {},
    updateSheetQuickPlaySelectionClasses() {}, clearSheetQuickPlayHighlights() {},
    getSheetQuickPlayTempo: () => tempo,
    getSheetQuickPlaySelectedPatterns: () => state.selectedPatternIds.map(id => ({ id, defaultTargets: ['Djembe_1'] })),
    buildSheetQuickPlayPreparedPattern: pattern => pattern,
    buildTimelinePlayerPayload: () => [{}],
    buildSheetQuickPlayConfiguredSections: () => [{ runtimeKey: 'test', highlightSteps: [] }],
    renderSheetQuickPlaySelectors() { assert.fail('Play must not discard its prepared player'); },
    openAudioTestFrame(payload) {
      loads++;
      assert.equal(payload[0].SheetQuickPlayExternalScheduler, true, 'Both layouts use the parent scheduler');
      frame.src = 'Audio/player.html?launchReload=' + loads;
      frame.dataset.audioLaunchKey = String(loads);
      frame.contentWindow = {
        Event: class { constructor(type) { this.type = type; } },
        startEmbeddedPlaybackFromParent() {
          assert(gesture, 'Start must stay in the real user gesture');
          starts++;
          return true;
        },
        stopEmbeddedPlaybackFromParent() { return true; },
        pumpEmbeddedPlaybackSchedulerFromParent() { pumps++; return true; }
      };
    }
  });
  for (const name of ['buildSheetQuickPlayPayload', 'getSheetQuickPlayPreparationSignature',
    'updateSheetQuickPlayButtonAvailability', 'setSheetQuickPlayButtonState', 'selectSheetQuickPlayPattern',
    'prepareSheetQuickPlayPlayer', 'scheduleSheetQuickPlayPreparation', 'clearSheetQuickPlaySchedulerPump',
    'startSheetQuickPlaySchedulerPump', 'stopSheetQuickPlay', 'startSheetQuickPlay', 'toggleSheetQuickPlay',
    'handleEmbeddedAudioPlayerMessage']) {
    vm.runInContext(extract(editor, name), context);
  }
  function drain() {
    const pending = [...timeouts.values()];
    timeouts.clear();
    pending.forEach(fn => fn());
  }
  function message(stateName, launchKey = frame.dataset.audioLaunchKey) {
    context.handleEmbeddedAudioPlayerMessage({ origin: 'http://localhost', source: frame.contentWindow,
      data: { type: 'barabeat-audio-state', state: stateName, launchKey } });
  }
  function click() {
    gesture = true;
    try { context.toggleSheetQuickPlay(); } finally { gesture = false; }
  }

  context.selectSheetQuickPlayPattern('solo-5');
  assert.equal(elements.sheetQuickPlayButton.disabled, true, 'Wait for loaded samples');
  drain();
  assert.equal(loads, 1, 'Selection preloads the player even on desktop');
  assert.equal(starts, 0, 'Loading must never start audio automatically');
  message('ready', 'old-launch');
  assert.equal(state.frameReady, false, 'Ignore readiness from a discarded player');
  message('ready');
  assert.equal(elements.sheetQuickPlayButton.disabled, false);
  click();
  assert.equal(starts, 1);
  assert.equal(loads, 1, 'The click must not navigate/reload the iframe');
  assert.equal(state.isPlaying, false, 'Do not announce playback before AudioContext resume completes');
  assert.equal(state.isStarting, true);
  assert.equal(elements.sheetQuickPlayButton.textContent, '…');
  click();
  assert.equal(starts, 1, 'Ignore duplicate starts during resume');
  message('playing');
  assert.equal(state.isPlaying, true);
  assert.equal(state.isStarting, false);
  assert.equal(elements.sheetQuickPlayButton.textContent, '■');
  assert.equal(pumps, 1, 'The parent pumps desktop and mobile playback');
  assert.equal(intervals.size, 1);

  if (!mobile) {
    elements.sheetQuickPlayTempo = { addEventListener(type, listener) { this.listener = listener; } };
    context.document.querySelector = selector => elements[selector.slice(1)];
    const start = editor.indexOf("document.querySelector('#sheetQuickPlayTempo').addEventListener('change'");
    vm.runInContext(editor.slice(start, editor.indexOf("    window.addEventListener('resize'", start)), context);
    tempo = 110;
    elements.sheetQuickPlayTempo.listener();
    assert.equal(bpm.value, '110');
    assert.equal(loads, 1, 'Tempo changes keep the unlocked player');
    assert.equal(state.preparedSignature, 'solo-5@110');
  }

  click();
  assert.equal(state.isPlaying, false);
  assert.equal(intervals.size, 0);
  assert.equal(state.frameReady, true, 'Stop preserves the player for the next real click');
  assert.notEqual(frame.src, 'about:blank');
  click();
  assert.equal(starts, 2);
  message('error');
  assert.equal(state.isStarting, false);
  assert.equal(state.isPlaying, false);
  assert.equal(elements.sheetQuickPlayButton.disabled, false, 'A rejected start can be retried');
  assert.equal(errors.length, 1);

  context.selectSheetQuickPlayPattern('call');
  assert.equal(state.frameReady, false);
  message('ready');
  assert.equal(state.frameReady, false, 'An old ready message cannot enable a changed selection');
  drain();
  assert.equal(loads, 2);
  assert.equal(starts, 2);
  message('ready');
  context.sheetQuickPlayRefreshPending = true;
  click();
  assert.equal(starts, 2, 'Pending score edits cannot play stale notes');
  context.sheetQuickPlayRefreshPending = false;
  context.selectSheetQuickPlayPattern('solo-5');
  context.selectSheetQuickPlayPattern('call');
  drain();
  assert.equal(frame.src, 'about:blank');
  assert.equal(elements.sheetQuickPlayButton.disabled, true);
}

function playerHarness(resumeMode = 'resolve') {
  let gesture = true;
  let resolveResume;
  const timers = new Map();
  const states = [];
  let id = 0;
  let created = 0;
  let resumed = 0;
  const sampleBuffer = {};
  const instrument = { _audioCtx: { currentTime: 0, close() {} }, _snd: { tone: sampleBuffer },
    replaceAudioContext(ctx) { this._audioCtx = ctx; }, warmUpSamples() {} };
  const context = vm.createContext({
    isSheetQuickPlayMode: true, usesSheetQuickPlayExternalScheduler: true,
    hasCreatedSheetQuickPlayGestureContext: false, playbackStartGeneration: 0,
    isPlaying: false, audioIsReady: true, hasWaitedAfterFirstResume: false, hasPrimedAudioOutput: false,
    playButton: button(), allInstruments: [instrument], instr: instrument,
    timelinePlaybackStartStep: 0, practiceDurationSeconds: 0, timerID: null,
    playerText: key => key, console: { error() {} },
    configureBarabeatAudioSession() {}, configureBarabeatMediaMetadata() {},
    warmUpAudioContext() {}, updateLoadingStatus() {}, resetDjembeHandStates() {},
    getPracticePlaybackStartTiming: () => ({ regularLeadInSeconds: 0.1, playbackLeadInSeconds: 0.1, countInDuration: 0 }),
    schedulePracticeCountIn() {}, stopAllActiveSources() {},
    scheduler() { assert.fail('The iframe must not start a second scheduler'); },
    notifyEmbeddedPlaybackState: state => states.push(state),
    window: { sharedAudioContext: instrument._audioCtx,
      AudioContext: class {
        constructor() { assert(gesture, 'AudioContext must be created synchronously in the click'); created++; this.currentTime = 1; }
        resume() {
          assert(gesture, 'resume() must be invoked synchronously in the click');
          resumed++;
          if (resumeMode === 'reject') return Promise.reject(new Error('blocked'));
          if (resumeMode === 'pending') return new Promise(resolve => { resolveResume = resolve; });
          return Promise.resolve();
        }
        close() {}
      },
      setTimeout(fn, ms) { const key = ++id; timers.set(key, { fn, ms }); return key; },
      clearTimeout(key) { timers.delete(key); }
    }
  });
  for (const name of ['createSheetQuickPlayGestureAudioContext', 'resumeAndWarmAllInstruments', 'startAudioPlayback', 'stopAudioPlayback']) {
    vm.runInContext(extract(player, name), context);
  }
  async function finishWarmup() {
    for (let count = 0; count < 10; count++) {
      await Promise.resolve();
      for (const [key, timer] of timers) {
        if (timer.ms < 5000) { timers.delete(key); timer.fn(); }
      }
    }
  }
  return { context, states, timers, instrument, sampleBuffer, finishWarmup,
    start() { gesture = true; const promise = context.startAudioPlayback(); gesture = false; return promise; },
    resolve: () => resolveResume(), counts: () => ({ created, resumed }) };
}

let h = playerHarness();
let started = h.start();
assert.deepEqual(h.counts(), { created: 1, resumed: 1 });
assert.deepEqual(h.states, ['starting']);
await h.finishWarmup();
assert.equal(await started, true);
assert.deepEqual(h.states, ['starting', 'playing']);
assert.equal(h.instrument._snd.tone, h.sampleBuffer, 'Decoded samples survive the context replacement');
h.context.stopAudioPlayback();
started = h.start();
assert.equal(await started, true);
assert.deepEqual(h.counts(), { created: 1, resumed: 2 }, 'Restart reuses the unlocked AudioContext');

h = playerHarness('pending');
started = h.start();
h.context.stopAudioPlayback();
h.resolve();
await h.finishWarmup();
assert.equal(await started, false);
assert(!h.states.includes('playing'), 'A stopped pending resume must not start later');

h = playerHarness('reject');
assert.equal(await h.start(), false);
assert.deepEqual(h.states, ['starting', 'error']);
assert.equal(h.context.isPlaying, false);

h = playerHarness('pending');
started = h.start();
[...h.timers.values()].find(timer => timer.ms === 5000).fn();
assert.equal(await started, false);
assert.deepEqual(h.states, ['starting', 'error'], 'A stuck resume must not leave a fake playing state');
assert.equal(h.timers.size, 0);
assert(!editor.includes('function requestSheetQuickPlayStart('), 'Remove the delayed synthetic-click start path');
console.log('Quick-play start: desktop/mobile preload, synchronous gesture, readiness, stop/restart, tempo, scheduler, errors and cancellation checked.');
