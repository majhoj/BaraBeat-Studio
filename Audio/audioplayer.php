<?php
$myObject = $_POST["myObj"] ?? "[]";
$embedded = ($_POST["embedded"] ?? "") === "1";
$uiTheme = $_POST["uiTheme"] ?? "";
$uiThemeClass = $uiTheme === "playful" ? "audio-theme-playful" : ($uiTheme === "earth" ? "audio-theme-earth" : "");
$playerJs = @filemtime(__DIR__ . '/js/instrument_2.js') ?: 1;
$playerCss = @filemtime(__DIR__ . '/css/audio_style.css') ?: 1;
?>
<script>
  const obj = <?php echo $myObject; ?>;
  const embeddedPlayer = <?php echo $embedded ? 'true' : 'false'; ?>;
  const myObjectString = JSON.stringify(obj);

  function close_window() {
      window.close();
  }
</script>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <script>
    document.write("<title>" + obj[0].Name + "</title>");
  </script>

  <meta name="description" content="Making an instrument with the Web Audio API">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <script src="js/instrument_2.js?v=<?php echo $playerJs; ?>"></script>

  <link rel="stylesheet" type="text/css" href="css/audio_style.css?v=<?php echo $playerCss; ?>">
</head>

<style type="text/css">
a:link {
  text-decoration: none;
  color: inherit;
}
</style>

<body class="<?php echo trim(($embedded ? 'embedded-player ' : '') . $uiThemeClass); ?>">

<div class="loading">
  <p>Loading...</p>
</div>

<div id="sequencer">
  <section class="controls-main">
    <?php if (!$embedded) { ?>
    <div class="player-title">
      <script>
        document.write('<a href="javascript:close_window();">X</a>' + obj[0].Name);
      </script>
    </div>
    <?php } ?>
    <div class="player-controls">
      <label for="bpm">BPM</label>
      <input name="bpm" id="bpm" type="range" min="30" max="180" value="100" step="1" />
      <span id="bpmval">100</span>
      <?php if (!$embedded) { ?>
      <label for="soloTrack">Stimme</label>
      <select id="soloTrack">
        <option value="">Alle</option>
        <option value="Kenkeni">Kenkeni</option>
        <option value="Sangban">Sangban</option>
        <option value="Doundoun">Doundoun</option>
        <option value="Dreierbass">Dreierbass</option>
        <option value="Djembe_1">Djembe 1</option>
        <option value="Djembe_2">Djembe 2</option>
        <option value="Djembe_3">Djembe 3</option>
      </select>
      <?php } ?>
      <button type="button" id="exportWavButton" class="secondary-button">Export WAV</button>
      <button data-playing="false">&nbsp;</button>
    </div>
  </section>
</div>

<script type="text/javascript">
console.clear();

const loadingEl = document.querySelector('.loading');
window.loadingEl = loadingEl;
const playButton = document.querySelector('[data-playing]');
const exportWavButton = document.querySelector('#exportWavButton');
let isPlaying = false;
let audioIsReady = false;
let hasWaitedAfterFirstResume = false;
let hasPrimedAudioOutput = false;

function applyAudioTheme(themeName) {
  document.body.classList.toggle('audio-theme-playful', themeName === 'playful');
  document.body.classList.toggle('audio-theme-earth', themeName === 'earth');
}

playButton.disabled = true;
exportWavButton.disabled = true;

function notifyEmbeddedTempoChange(nextTempo) {
  if (!embeddedPlayer || !window.parent || window.parent === window) {
    return;
  }
  window.parent.postMessage({
    type: 'barabeat-audio-tempo-change',
    tempo: nextTempo
  }, window.location.origin);
}

function notifyEmbeddedPlaybackState(nextState, extraData) {
  if (!embeddedPlayer || !window.parent || window.parent === window) {
    return;
  }
  window.parent.postMessage(Object.assign({
    type: 'barabeat-audio-state',
    state: nextState
  }, extraData || {}), window.location.origin);
}

function notifyEmbeddedPlaybackStep(playbackStep, scheduledTime) {
  if (!embeddedPlayer || !window.parent || window.parent === window || !instr || !instr._audioCtx) {
    return;
  }
  window.parent.postMessage({
    type: 'barabeat-audio-step',
    playbackStep: playbackStep,
    delayMs: Math.max(0, (scheduledTime - instr._audioCtx.currentTime) * 1000)
  }, window.location.origin);
}

function updateLoadingStatus(message) {
  console.log(message);
  if (loadingEl) {
    loadingEl.innerHTML = '<p>' + message + '</p>';
  }
}

window.addEventListener('error', function (event) {
  console.error('Player-Fehler:', event.error || event.message);
  updateLoadingStatus('Player-Fehler: ' + (event.message || 'Unbekannter Fehler'));
});

window.addEventListener('unhandledrejection', function (event) {
  const rejectionMessage = event.reason && event.reason.message ? event.reason.message : String(event.reason);
  console.error('Player Promise-Fehler:', event.reason);
  updateLoadingStatus('Ladefehler: ' + rejectionMessage);
});

updateLoadingStatus('Player startet...');

const playerConfig = Array.isArray(obj) && obj.length > 0 ? obj[0] : {};
const isPracticeMode = Boolean(playerConfig.PracticeMode);
const allPracticeTrackNames = ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'];

function normalizeInstrumentVolume(rawValue) {
  const numericValue = Number(rawValue);
  if (!Number.isFinite(numericValue)) {
    return 1;
  }
  return Math.max(0, Math.min(2, numericValue));
}

function normalizeInstrumentVolumes(rawVolumes) {
  const sourceVolumes = rawVolumes && typeof rawVolumes === 'object' ? rawVolumes : {};
  return allPracticeTrackNames.reduce(function (volumes, instrumentName) {
    volumes[instrumentName] = normalizeInstrumentVolume(sourceVolumes[instrumentName]);
    return volumes;
  }, {});
}

let practiceInstrumentVolumes = normalizeInstrumentVolumes(playerConfig.PracticeInstrumentVolumes);

function getPracticeInstrumentVolume(trackName) {
  return practiceInstrumentVolumes[trackName] === undefined
    ? 1
    : normalizeInstrumentVolume(practiceInstrumentVolumes[trackName]);
}

function hasPlayablePracticeNotes(notes) {
  return Array.isArray(notes) && notes.some(function (noteValue) {
    return isPlayableNote(noteValue);
  });
}

function isPlayableNote(noteValue) {
  return noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '';
}

function getActivePracticeTrackNames(config) {
  if (!isPracticeMode || !Array.isArray(config.PracticeSections)) {
    return allPracticeTrackNames.slice();
  }

  const activeTracks = [];
  config.PracticeSections.forEach(function (section) {
    const trackNotes = section && section.trackNotes ? section.trackNotes : {};
    allPracticeTrackNames.forEach(function (trackName) {
      if (activeTracks.indexOf(trackName) === -1 && hasPlayablePracticeNotes(trackNotes[trackName])) {
        activeTracks.push(trackName);
      }
    });
  });

  return activeTracks.length > 0 ? activeTracks : allPracticeTrackNames.slice();
}

const activePracticeTrackNames = getActivePracticeTrackNames(playerConfig);

function getActivePracticeTrackNamesFromSections(configuredSections) {
  if (!Array.isArray(configuredSections)) {
    return [];
  }

  const activeTracks = [];
  configuredSections.forEach(function (section) {
    const trackNotes = section && section.trackNotes ? section.trackNotes : {};
    allPracticeTrackNames.forEach(function (trackName) {
      if (activeTracks.indexOf(trackName) === -1 && hasPlayablePracticeNotes(trackNotes[trackName])) {
        activeTracks.push(trackName);
      }
    });
  });
  return activeTracks;
}

function getPracticeInstrumentFiles(trackName, files) {
  if (!isPracticeMode || activePracticeTrackNames.indexOf(trackName) !== -1) {
    return files;
  }
  return [];
}

const djembe_1_mp3Files = ['snd/Silence.mp3','snd/DjembeOne_Open.mp3','snd/DjembeOne_OpenL.mp3','snd/DjembeOne_Bass.mp3','snd/DjembeOne_BassL.mp3','snd/DjembeOne_Slap.mp3','snd/DjembeOne_SlapL.mp3','snd/DjembeOne_Mute.mp3','snd/DjembeOne_MuteL.mp3','snd/DjembeOne_Muffled_Open.mp3','snd/DjembeOne_Muffled_Slap.mp3'];
const djembe_2_mp3Files = ['snd/Silence.mp3','snd/DjembeTwo_Open.mp3','snd/DjembeTwo_OpenL.mp3','snd/DjembeTwo_Bass.mp3','snd/DjembeTwo_BassL.mp3','snd/DjembeTwo_Slap.mp3','snd/DjembeTwo_SlapL.mp3','snd/DjembeTwo_Mute.mp3','snd/DjembeTwo_MuteL.mp3','snd/DjembeTwo_Muffled_Open.mp3','snd/DjembeTwo_Muffled_Slap.mp3'];
const djembe_3_mp3Files = ['snd/Silence.mp3','snd/DjembeThree_Open.mp3','snd/DjembeThree_OpenL.mp3','snd/DjembeThree_Bass.mp3','snd/DjembeThree_BassL.mp3','snd/DjembeThree_Slap.mp3','snd/DjembeThree_SlapL.mp3','snd/DjembeThree_Mute.mp3','snd/DjembeThree_MuteL.mp3'];
const kenkeni_mp3Files  = ['snd/Kenkeni_Open.mp3','snd/Kenkeni_Muffled.mp3','snd/Kenkeni_Bell_Open.mp3','snd/Kenkeni_Klick.mp3'];
const sangban_mp3Files  = ['snd/Sangban_Open.mp3','snd/Sangban_Muffled.mp3','snd/Sangban_Bell_Open.mp3','snd/Sangban_Klick.mp3'];
const doundoun_mp3Files = ['snd/Doundoun_Open.mp3','snd/Doundoun_Muffled.mp3','snd/Doundoun_Bell_Open.mp3','snd/Doundoun_Klick.mp3'];
const dreierbass_mp3Files = ['snd/Silence.mp3','snd/Kenkeni_Open.mp3','snd/Kenkeni_Muffled.mp3','snd/Sangban_Open.mp3','snd/Sangban_Muffled.mp3','snd/Doundoun_Open.mp3','snd/Doundoun_Muffled.mp3'];

const djembe_1 = new Instrumente(getPracticeInstrumentFiles('Djembe_1', djembe_1_mp3Files), 1, 1.4);
const djembe_2 = new Instrumente(getPracticeInstrumentFiles('Djembe_2', djembe_2_mp3Files), 0.4, 1.4);
const djembe_3 = new Instrumente(getPracticeInstrumentFiles('Djembe_3', djembe_3_mp3Files), -0.2, 1.4);
const kenkeni  = new Instrumente(getPracticeInstrumentFiles('Kenkeni', kenkeni_mp3Files), -1, 1.8);
const sangban  = new Instrumente(getPracticeInstrumentFiles('Sangban', sangban_mp3Files), -0.8, 2.5);
const doundoun = new Instrumente(getPracticeInstrumentFiles('Doundoun', doundoun_mp3Files), -0.6, 1.5);
const dreierbass = new Instrumente(getPracticeInstrumentFiles('Dreierbass', dreierbass_mp3Files), -1, 1.5);
const allInstruments = [djembe_1, djembe_2, djembe_3, kenkeni, sangban, doundoun, dreierbass];
const practiceInstrumentSoundFiles = {
  Djembe_1: djembe_1_mp3Files,
  Djembe_2: djembe_2_mp3Files,
  Djembe_3: djembe_3_mp3Files,
  Kenkeni: kenkeni_mp3Files,
  Sangban: sangban_mp3Files,
  Doundoun: doundoun_mp3Files,
  Dreierbass: dreierbass_mp3Files
};
const practiceInstrumentInstances = {
  Djembe_1: djembe_1,
  Djembe_2: djembe_2,
  Djembe_3: djembe_3,
  Kenkeni: kenkeni,
  Sangban: sangban,
  Doundoun: doundoun,
  Dreierbass: dreierbass
};
let practiceSectionUpdateLoadToken = 0;

async function ensurePracticeTrackSoundsLoaded(trackNames) {
  const safeTrackNames = Array.isArray(trackNames) ? trackNames : [];
  const loadPromises = safeTrackNames.map(function (trackName) {
    const instrumentInstance = practiceInstrumentInstances[trackName];
    const soundFiles = practiceInstrumentSoundFiles[trackName];
    if (!instrumentInstance || typeof instrumentInstance.ensureSoundFiles !== 'function' || !Array.isArray(soundFiles)) {
      return Promise.resolve();
    }
    return instrumentInstance.ensureSoundFiles(soundFiles);
  });
  await Promise.all(loadPromises);
}
const sangbanStrokeGainMultiplier = 2.2;
const h2hRestMuteGainMultipliers = {
  Djembe_1: 0.8,
  Djembe_2: 1,
  Djembe_3: 0.8
};
const allInstrumentReadyPromises = allInstruments.map(function (instrumentInstance) {
  return instrumentInstance.readyPromise;
});

console.log("Name : " + obj[0].Name);
console.log("Rhythmus : " + obj[0].Rhythmus);
updateLoadingStatus('Audiodateien werden geladen...');

Promise.all(allInstrumentReadyPromises)
  .then(function () {
    audioIsReady = true;
    playButton.disabled = false;
    exportWavButton.disabled = false;
    if (loadingEl) {
      loadingEl.style.display = 'none';
    }
  })
  .catch(function (error) {
    console.error('Audio-Initialisierung fehlgeschlagen:', error);
    updateLoadingStatus('Ladefehler: ' + error.message);
  });

let steuerung = {};
let instrument = "";
const MAX_EXPANDED_BARS = 2000;
const trackInstrumentNames = ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'];

function sanitizeRepeatRanges(repeatRangesInput) {
  if (!Array.isArray(repeatRangesInput)) {
    return [];
  }

  const seenRanges = new Set();
  return repeatRangesInput
    .filter(function (repeatRange) {
      if (!repeatRange) {
        return false;
      }
      const startBar = Number(repeatRange.startBar);
      const endBar = Number(repeatRange.endBar);
      if (!Number.isFinite(startBar) || !Number.isFinite(endBar) || startBar < 1 || endBar < startBar) {
        return false;
      }

      const countValue = repeatRange.count === 'loop' ? 'loop' : Number(repeatRange.count);
      if (countValue !== 'loop' && (!Number.isFinite(countValue) || countValue < 1)) {
        return false;
      }

      const dedupeKey = startBar + ':' + endBar + ':' + countValue;
      if (seenRanges.has(dedupeKey)) {
        return false;
      }
      seenRanges.add(dedupeKey);
      return true;
    })
    .map(function (repeatRange) {
      return {
        startBar: Number(repeatRange.startBar),
        endBar: Number(repeatRange.endBar),
        count: repeatRange.count === 'loop' ? 'loop' : Number(repeatRange.count)
      };
    })
    .sort(function (rangeA, rangeB) {
      if (rangeA.startBar !== rangeB.startBar) {
        return rangeA.startBar - rangeB.startBar;
      }
      return rangeB.endBar - rangeA.endBar;
    });
}

function normalizeRepeatMarkerList(markerValue) {
  if (Array.isArray(markerValue)) {
    return markerValue.filter(function (marker) {
      return marker !== false && marker !== null && marker !== undefined && marker !== '';
    });
  }
  if (markerValue === false || markerValue === null || markerValue === undefined || markerValue === '') {
    return [];
  }
  return [markerValue];
}

function normalizeTimelineLoopCountValue(rawValue) {
  if (rawValue === true || rawValue === 'loop' || rawValue === 'continue') {
    return 'loop';
  }

  const numericValue = Number(rawValue);
  if (!Number.isFinite(numericValue) || numericValue < 1) {
    return false;
  }

  return Math.max(1, Math.round(numericValue));
}

function buildRepeatRangesFromPatternBars(patternBars) {
  if (!Array.isArray(patternBars) || patternBars.length === 0) {
    return [];
  }

  const repeatBoundaries = new Array(patternBars.length + 1).fill(null).map(function (_, boundaryIndex) {
    return {
      index: boundaryIndex,
      startMarkers: [],
      endMarkers: []
    };
  });

  patternBars.forEach(function (bar, barIndex) {
    const repeatInfo = bar && bar.repeat ? bar.repeat : {};
    normalizeRepeatMarkerList(repeatInfo.start).forEach(function (marker) {
      repeatBoundaries[barIndex].startMarkers.push({
        boundaryIndex: barIndex,
        count: marker
      });
    });
    normalizeRepeatMarkerList(repeatInfo.end).forEach(function (marker) {
      repeatBoundaries[barIndex + 1].endMarkers.push({
        boundaryIndex: barIndex + 1,
        count: marker
      });
    });
  });

  const repeatRanges = [];
  const repeatStartStack = [];
  repeatBoundaries.forEach(function (boundary) {
    boundary.endMarkers.forEach(function (endMarker) {
      const matchingStartMarker = repeatStartStack.pop();
      if (!matchingStartMarker) {
        return;
      }
      repeatRanges.push({
        startBar: matchingStartMarker.boundaryIndex + 1,
        endBar: endMarker.boundaryIndex,
        count: endMarker.count
      });
    });
    boundary.startMarkers.forEach(function (startMarker) {
      repeatStartStack.push(startMarker);
    });
  });

  return sanitizeRepeatRanges(repeatRanges);
}

function expandPatternBars(pattern) {
  const patternBars = pattern && Array.isArray(pattern.bars) ? pattern.bars : [];
  if (patternBars.length === 0) {
    return [];
  }

  if (patternStartsContinuingAccompaniment(pattern)) {
    return patternBars;
  }

  const patternRepeatRanges = buildRepeatRangesFromPatternBars(patternBars);
  if (patternRepeatRanges.length === 0) {
    return patternBars;
  }
  return expandBarsWithRepeats(patternBars, patternRepeatRanges, 1, patternBars.length);
}

const repeatRanges = sanitizeRepeatRanges(playerConfig.RepeatRanges);
const isTimelineMode = Boolean(playerConfig.TimelineMode);
let timelineLoopCount = normalizeTimelineLoopCountValue(
  playerConfig.TimelineLoopCount !== undefined ? playerConfig.TimelineLoopCount : playerConfig.TimelineLoop
);
let timelineLoop = timelineLoopCount === 'loop';
const initialTempo = Math.max(30, Math.min(180, Number(playerConfig.Tempo) || 100));
const swingFactor = Math.max(0, Math.min(100, Number(playerConfig.SwingFactor) || 0));
const rhythmType = String(playerConfig.Rhythmus || '');
const swingProfile = playerConfig.SwingProfile || {};
const feelOffsets = playerConfig.FeelOffsets || {};
let practiceH2HRestMute = Boolean(playerConfig.PracticeH2HRestMute);
let practiceDurationSeconds = Math.max(0, Number(playerConfig.PracticeDurationSeconds) || 0);
let pendingPracticeSections = null;
let pendingPracticeLoopCount = false;
let pendingPracticeDurationSeconds = undefined;
let pendingPracticeSectionsApplyStep = null;
const timelineBassTargets = ['Kenkeni', 'Sangban', 'Doundoun'];

window.addEventListener('message', function (event) {
  if (event.origin !== window.location.origin) {
    return;
  }
  const message = event.data || {};
  if (message.type === 'barabeat-ui-theme') {
    applyAudioTheme(message.theme || '');
    return;
  }
  if (message.type === 'barabeat-practice-h2h-rest-mute') {
    practiceH2HRestMute = Boolean(message.enabled);
    return;
  }
  if (message.type === 'barabeat-practice-instrument-volumes') {
    practiceInstrumentVolumes = normalizeInstrumentVolumes(message.volumes);
    return;
  }
  if (message.type === 'barabeat-practice-hand-modes-update') {
    updateOrderedSectionHandModes(message.sections);
    return;
  }
  if (message.type === 'barabeat-practice-sections-update') {
    queuePracticeSectionsUpdate(message.sections, message.timelineLoopCount, message.practiceDurationSeconds);
  }
});

function getFeelOffsetSeconds(trackName) {
  const offsetMilliseconds = Number(feelOffsets && feelOffsets[trackName]) || 0;
  return offsetMilliseconds / 1000;
}

function ensureSteuerungEntry(instrumentName) {
  if (!steuerung[instrumentName]) {
    steuerung[instrumentName] = {
      noten: [],
      callNotes: [],
      introNotes: [],
      echauffementNotes: [],
      begleitungNotes: []
    };
  }
  return steuerung[instrumentName];
}

function getTargetInstrumentsForBar(bar) {
  if (!bar) {
    return [];
  }
  if (Array.isArray(bar.targetInstruments) && bar.targetInstruments.length > 0) {
    return bar.targetInstruments;
  }
  if (bar.instrumentMode === 'allUsedDjembes') {
    return usedDjembeTrackNames;
  }
  if (bar.instrumentMode === 'allBasses') {
    return timelineBassTargets.slice();
  }
  if (bar.instrument === 'Bässe') {
    return timelineBassTargets.slice();
  }
  if (bar.instrument && bar.instrument.search('End') !== 0) {
    return [bar.instrument];
  }
  return [];
}

function getBarShortLength(bar) {
  const notes = bar && Array.isArray(bar.notes) ? bar.notes : [];
  const controls = bar && Array.isArray(bar.controls) ? bar.controls : [];
  const shortBarControl = controls
    .filter(function (control) {
      return control && control.type === 'shortbar';
    })
    .sort(function (controlA, controlB) {
      return Number(controlA.stepIndex) - Number(controlB.stepIndex);
    })[0];

  if (!shortBarControl || notes.length === 0) {
    return notes.length;
  }

  const shortStep = Math.round(Number(shortBarControl.stepIndex));
  if (!Number.isFinite(shortStep)) {
    return notes.length;
  }
  return Math.max(0, Math.min(notes.length, shortStep));
}

function getBarPlayableNotes(bar) {
  const notes = bar && Array.isArray(bar.notes) ? bar.notes : [];
  return notes.slice(0, getBarShortLength(bar));
}

function appendBarToTracks(bar, rowIndex) {
  if (!bar || !Array.isArray(bar.notes)) {
    return;
  }

  const playableNotes = getBarPlayableNotes(bar);
  const segmentLength = playableNotes.length;
  const targetInstruments = getTargetInstrumentsForBar(bar);

  trackInstrumentNames.forEach(function (instrumentName) {
    const instrumentEntry = ensureSteuerungEntry(instrumentName);
    const segmentStart = instrumentEntry.noten.length;
    const hasTrackNotes = bar.trackNotes &&
      Array.isArray(bar.trackNotes[instrumentName]) &&
      bar.trackNotes[instrumentName].length === segmentLength;
    const segmentNotes = hasTrackNotes
      ? bar.trackNotes[instrumentName]
      : (targetInstruments.indexOf(instrumentName) !== -1
        ? playableNotes
        : new Array(segmentLength).fill('f'));
    instrumentEntry.noten = instrumentEntry.noten.concat(segmentNotes);
    if (targetInstruments.indexOf(instrumentName) === -1 && !hasTrackNotes) {
      return;
    }

    if (bar.label === "Call") {
      instrumentEntry.callNotes = instrumentEntry.callNotes.concat(segmentNotes);
    }
    if (bar.label === "Intro") {
      instrumentEntry.introNotes = instrumentEntry.introNotes.concat(segmentNotes);
    }
    if (bar.label === "Echauffement") {
      instrumentEntry.echauffementNotes = instrumentEntry.echauffementNotes.concat(segmentNotes);
    }
    if (bar.label === "Begleitung") {
      instrumentEntry.begleitungNotes = instrumentEntry.begleitungNotes.concat(segmentNotes);
    }

    instrument += instrumentName + " von " + rowIndex + " - " + rowIndex + ", ";
  });
}

function createEmptyTrackNoteMap() {
  return trackInstrumentNames.reduce(function (trackMap, instrumentName) {
    trackMap[instrumentName] = [];
    return trackMap;
  }, {});
}

function createEmptyTrackHandModeMap() {
  return trackInstrumentNames.reduce(function (trackMap, instrumentName) {
    trackMap[instrumentName] = '';
    return trackMap;
  }, {});
}

function createEmptyTrackStepMap() {
  return trackInstrumentNames.reduce(function (trackMap, instrumentName) {
    trackMap[instrumentName] = null;
    return trackMap;
  }, {});
}

function createOrderedSection(label) {
  return {
    label: label,
    labelName: label,
    swingFactor: null,
    runtimeKey: '',
    trackNotes: createEmptyTrackNoteMap(),
    trackHandModes: createEmptyTrackHandModeMap(),
    finalRepeatOutSteps: createEmptyTrackStepMap(),
    forceFinalOutAtSectionEnd: false,
    continuingAccompaniments: {},
    practiceTargetInstruments: [],
    isLeadIn: false,
    repeatCount: 1,
    playbackLength: 0,
    length: 0,
    startStep: 0,
    endStep: 0
  };
}

function appendBarToOrderedSection(section, bar) {
  const playableNotes = getBarPlayableNotes(bar);
  getTargetInstrumentsForBar(bar).forEach(function (instrumentName) {
    section.trackNotes[instrumentName] = section.trackNotes[instrumentName].concat(playableNotes);
  });
}

function appendSectionToSteuerung(section, sectionIndex) {
  trackInstrumentNames.forEach(function (instrumentName) {
    const instrumentEntry = ensureSteuerungEntry(instrumentName);
    const sectionNotes = Array.isArray(section.trackNotes[instrumentName])
      ? section.trackNotes[instrumentName].slice()
      : [];
    const paddedSectionNotes = sectionNotes.concat(new Array(Math.max(0, section.length - sectionNotes.length)).fill('f'));

    instrumentEntry.noten = instrumentEntry.noten.concat(paddedSectionNotes);

    if (section.label === "Call") {
      instrumentEntry.callNotes = instrumentEntry.callNotes.concat(paddedSectionNotes);
    }
    if (section.label === "Intro") {
      instrumentEntry.introNotes = instrumentEntry.introNotes.concat(paddedSectionNotes);
    }
    if (section.label === "Echauffement") {
      instrumentEntry.echauffementNotes = instrumentEntry.echauffementNotes.concat(paddedSectionNotes);
    }
    if (section.label === "Begleitung") {
      instrumentEntry.begleitungNotes = instrumentEntry.begleitungNotes.concat(paddedSectionNotes);
    }
  });

  instrument += section.label + " Abschnitt " + sectionIndex + ", ";
}

function flattenPatternNotes(pattern) {
  return expandPatternBars(pattern).reduce(function (allNotes, bar) {
    const playableNotes = getBarPlayableNotes(bar);
    if (playableNotes.length === 0) {
      return allNotes;
    }
    return allNotes.concat(playableNotes);
  }, []);
}

function flattenContinuingAccompanimentNotes(pattern) {
  return getContinuingAccompanimentPatternBars(pattern).reduce(function (allNotes, bar) {
    const playableNotes = getBarPlayableNotes(bar);
    if (playableNotes.length === 0) {
      return allNotes;
    }
    return allNotes.concat(playableNotes);
  }, []);
}

function getPatternOutStep(pattern) {
  const patternBars = expandPatternBars(pattern);
  if (patternBars.length === 0) {
    return null;
  }

  let stepOffset = 0;
  let matchedOutStep = null;

  for (let barIndex = 0; barIndex < patternBars.length; barIndex++) {
    const bar = patternBars[barIndex];
    const barNotes = getBarPlayableNotes(bar);
    const barControls = bar && Array.isArray(bar.controls) ? bar.controls : [];
    const outControl = barControls
      .filter(function (control) {
        return control && control.type === 'out';
      })
      .sort(function (controlA, controlB) {
        return Number(controlA.stepIndex) - Number(controlB.stepIndex);
      })[0];

    if (outControl) {
      const controlStep = Math.max(0, Number(outControl.stepIndex) || 0);
      matchedOutStep = stepOffset + Math.min(controlStep, barNotes.length);
    }

    stepOffset += barNotes.length;
  }

  return matchedOutStep;
}

function getPatternControlStep(pattern, controlType) {
  const patternBars = expandPatternBars(pattern);
  if (patternBars.length === 0) {
    return null;
  }

  let stepOffset = 0;
  let matchedControlStep = null;

  for (let barIndex = 0; barIndex < patternBars.length; barIndex++) {
    const bar = patternBars[barIndex];
    const barNotes = getBarPlayableNotes(bar);
    const barControls = bar && Array.isArray(bar.controls) ? bar.controls : [];
    const matchingControl = barControls
      .filter(function (control) {
        return control && control.type === controlType;
      })
      .sort(function (controlA, controlB) {
        return Number(controlA.stepIndex) - Number(controlB.stepIndex);
      })[0];

    if (matchingControl) {
      const controlStep = Math.max(0, Number(matchingControl.stepIndex) || 0);
      matchedControlStep = stepOffset + Math.min(controlStep, barNotes.length);
      if (controlType !== 'out') {
        return matchedControlStep;
      }
    }

    stepOffset += barNotes.length;
  }

  return matchedControlStep;
}

function getPatternInStep(pattern) {
  return getPatternControlStep(pattern, 'in');
}

function getTimelineStepsPerBar() {
  if (rhythmType === 'tenaer') {
    return 24;
  }
  if (rhythmType === 'neunaer') {
    return 18;
  }
  return 32;
}

function buildTimelinePickupNotes(notes, inStep) {
  const safeNotes = Array.isArray(notes) ? notes : [];
  if (safeNotes.length === 0 || inStep === null || inStep === undefined) {
    return [];
  }

  const stepsPerBar = getTimelineStepsPerBar();
  const safeInStep = Math.max(0, Math.min(safeNotes.length - 1, Number(inStep) || 0));
  const pickupStartStep = stepsPerBar > 0
    ? Math.floor(safeInStep / stepsPerBar) * stepsPerBar
    : 0;
  const pickupLength = Math.max(stepsPerBar, safeNotes.length - pickupStartStep);
  const pickupNotes = new Array(pickupLength).fill('f');

  for (let sourceStep = safeInStep; sourceStep < safeNotes.length; sourceStep++) {
    pickupNotes[sourceStep - pickupStartStep] = safeNotes[sourceStep] || 'f';
  }

  return pickupNotes;
}

function sectionHasTimelineNotes(section) {
  return trackInstrumentNames.some(function (instrumentName) {
    return Array.isArray(section && section.trackNotes && section.trackNotes[instrumentName]) &&
      section.trackNotes[instrumentName].some(function (noteValue) {
        return noteValue && noteValue !== 'f';
      });
  });
}

function mergeTimelinePickupIntoHostSection(hostSection, pickupSection) {
  if (!hostSection || !pickupSection) {
    return;
  }

  const hostLength = Math.max.apply(null, trackInstrumentNames.map(function (instrumentName) {
    return getSectionLength(hostSection.trackNotes[instrumentName]);
  }).concat(0));
  const stepsPerBar = getTimelineStepsPerBar();
  const pickupLength = Math.max.apply(null, trackInstrumentNames.map(function (instrumentName) {
    return getSectionLength(pickupSection.trackNotes[instrumentName]);
  }).concat(0));
  const pickupOffset = Math.max(0, hostLength - Math.max(stepsPerBar, pickupLength));

  trackInstrumentNames.forEach(function (instrumentName) {
    const pickupNotes = pickupSection.trackNotes[instrumentName];
    if (!Array.isArray(pickupNotes) || pickupNotes.length === 0) {
      return;
    }
    hostSection.trackNotes[instrumentName] = mergeNotesIntoTrack(
      hostSection.trackNotes[instrumentName],
      pickupNotes,
      pickupOffset
    );
    if (pickupSection.trackHandModes[instrumentName]) {
      hostSection.trackHandModes[instrumentName] = pickupSection.trackHandModes[instrumentName];
    }
  });
}

function mergeTimelineEntryPickupsIntoPreviousSection(sections, entryDataList) {
  const entries = Array.isArray(entryDataList) ? entryDataList : [];
  const pickupSection = createOrderedSection('Auftakt');
  const pickupLabelNames = [];

  entries.forEach(function (entryData) {
    if (!entryData || entryData.patternInStep === null || entryData.patternInStep === undefined) {
      return;
    }
    const pickupNotes = buildTimelinePickupNotes(entryData.patternNotes, entryData.patternInStep);
    if (pickupNotes.length === 0) {
      return;
    }
    entryData.targetInstruments.forEach(function (instrumentName) {
      pickupSection.trackNotes[instrumentName] = mergeNotesIntoTrack(
        pickupSection.trackNotes[instrumentName],
        pickupNotes,
        0
      );
      if (instrumentName.indexOf('Djembe_') === 0) {
        pickupSection.trackHandModes[instrumentName] = String(entryData.handMode || '');
      }
    });
    if (entryData.labelName && pickupLabelNames.indexOf(entryData.labelName) === -1) {
      pickupLabelNames.push(entryData.labelName);
    }
    if (pickupSection.swingFactor === null && entryData.swingFactor !== null && entryData.swingFactor !== undefined) {
      pickupSection.swingFactor = entryData.swingFactor;
    }
  });

  if (!sectionHasTimelineNotes(pickupSection)) {
    return;
  }

  pickupSection.labelName = pickupLabelNames.length > 0
    ? pickupLabelNames.join(' + ') + ' Auftakt'
    : 'Auftakt';

  for (let sectionIndex = sections.length - 1; sectionIndex >= 0; sectionIndex--) {
    if (sectionHasTimelineNotes(sections[sectionIndex])) {
      mergeTimelinePickupIntoHostSection(sections[sectionIndex], pickupSection);
      return;
    }
  }

  sections.push(pickupSection);
}

function applyOutToPatternNotes(patternNotes, outStep) {
  const safeNotes = Array.isArray(patternNotes) ? patternNotes.slice() : [];
  if (outStep === null || outStep === undefined) {
    return safeNotes;
  }

  const safeOutStep = Math.max(0, Math.min(safeNotes.length - 1, Number(outStep) || 0));
  for (let stepIndex = safeOutStep + 1; stepIndex < safeNotes.length; stepIndex++) {
    safeNotes[stepIndex] = 'f';
  }

  return safeNotes;
}

function getPatternNotesLength(entryData) {
  return Array.isArray(entryData && entryData.patternNotes) ? entryData.patternNotes.length : 0;
}

function getMaxPatternNotesLength(entryDataList) {
  return (Array.isArray(entryDataList) ? entryDataList : []).reduce(function (maxLength, entryData) {
    return Math.max(maxLength, getPatternNotesLength(entryData));
  }, 0);
}

function getMaxPatternNotesLengthByLabel(entryDataList, labelName) {
  return (Array.isArray(entryDataList) ? entryDataList : [])
    .filter(function (entryData) {
      return entryData && entryData.label === labelName;
    })
    .reduce(function (maxLength, entryData) {
      return Math.max(maxLength, getPatternNotesLength(entryData));
    }, 0);
}

function getTimelineParallelTargetLength(entryDataList, containsEchauffement, containsOverlay) {
  if (containsEchauffement) {
    return getMaxPatternNotesLengthByLabel(entryDataList, 'Echauffement');
  }
  if (containsOverlay) {
    return getMaxPatternNotesLength(entryDataList);
  }
  return 0;
}

function getTimelineEntryOutStep(entryData, effectiveNotesLength) {
  if (!entryData || entryData.patternOutStep === null || entryData.patternOutStep === undefined) {
    return null;
  }

  const sourceLength = getPatternNotesLength(entryData);
  const safeEffectiveLength = Math.max(0, Number(effectiveNotesLength) || 0);
  let outStep = safeEffectiveLength > sourceLength && sourceLength > 0
    ? safeEffectiveLength - sourceLength + entryData.patternOutStep
    : entryData.patternOutStep;

  return outStep;
}

function getTimelineEntryEffectiveNotes(entryData, shouldApplyOut, loopToLength) {
  const sourceNotes = Array.isArray(entryData && entryData.patternNotes) ? entryData.patternNotes : [];
  let effectiveNotes = sourceNotes.slice();
  const safeLoopToLength = Math.max(0, Number(loopToLength) || 0);

  if (entryData && entryData.label === 'Begleitung' && safeLoopToLength > 0) {
    effectiveNotes = safeLoopToLength > effectiveNotes.length
      ? loopNotesToLength(effectiveNotes, safeLoopToLength)
      : effectiveNotes.slice(0, safeLoopToLength);
  }

  if (shouldApplyOut && entryData && entryData.patternOutStep !== null) {
    effectiveNotes = applyOutToPatternNotes(effectiveNotes, getTimelineEntryOutStep(entryData, effectiveNotes.length));
  }

  return effectiveNotes;
}

function mergeNotesIntoTrack(targetNotes, sourceNotes, offset) {
  const writeOffset = Number(offset) || 0;
  const mergedNotes = targetNotes.slice();
  const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes : [];

  if (mergedNotes.length < writeOffset) {
    mergedNotes.push.apply(mergedNotes, new Array(writeOffset - mergedNotes.length).fill('f'));
  }

  safeSourceNotes.forEach(function (noteValue, noteIndex) {
    const writeIndex = writeOffset + noteIndex;
    while (mergedNotes.length <= writeIndex) {
      mergedNotes.push('f');
    }
    if (noteValue !== 'f' && noteValue !== null && noteValue !== undefined) {
      mergedNotes[writeIndex] = noteValue;
    } else if (mergedNotes[writeIndex] === undefined) {
      mergedNotes[writeIndex] = 'f';
    }
  });

  return mergedNotes;
}

function doInstrumentSetsOverlap(instrumentNamesA, instrumentNamesB) {
  const leftSet = Array.isArray(instrumentNamesA) ? instrumentNamesA : [];
  const rightSet = Array.isArray(instrumentNamesB) ? instrumentNamesB : [];
  return leftSet.some(function (instrumentName) {
    return rightSet.indexOf(instrumentName) !== -1;
  });
}

function appendEntryToSection(section, entryData) {
  entryData.targetInstruments.forEach(function (instrumentName) {
    section.trackNotes[instrumentName] = mergeNotesIntoTrack(
      section.trackNotes[instrumentName],
      entryData.patternNotes,
      0
    );
    if (instrumentName.indexOf('Djembe_') === 0) {
      section.trackHandModes[instrumentName] = String(entryData.handMode || '');
    }
  });
}

function greatestCommonDivisor(leftValue, rightValue) {
  let a = Math.abs(Number(leftValue) || 0);
  let b = Math.abs(Number(rightValue) || 0);

  while (b !== 0) {
    const remainder = a % b;
    a = b;
    b = remainder;
  }

  return a || 1;
}

function leastCommonMultiple(leftValue, rightValue) {
  const a = Math.abs(Number(leftValue) || 0);
  const b = Math.abs(Number(rightValue) || 0);
  if (a <= 0) {
    return b;
  }
  if (b <= 0) {
    return a;
  }
  return Math.abs(a * b) / greatestCommonDivisor(a, b);
}

function loopNotesToLength(sourceNotes, targetLength) {
  const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes.slice() : [];
  const safeTargetLength = Math.max(0, Number(targetLength) || 0);
  if (safeSourceNotes.length === 0 || safeTargetLength <= safeSourceNotes.length) {
    return safeSourceNotes;
  }

  const loopedNotes = [];
  for (let noteIndex = 0; noteIndex < safeTargetLength; noteIndex++) {
    loopedNotes.push(safeSourceNotes[noteIndex % safeSourceNotes.length]);
  }
  return loopedNotes;
}

function padNotesToLength(sourceNotes, targetLength) {
  const paddedNotes = Array.isArray(sourceNotes) ? sourceNotes.slice() : [];
  const safeTargetLength = Math.max(0, Number(targetLength) || 0);
  while (paddedNotes.length < safeTargetLength) {
    paddedNotes.push('f');
  }
  return paddedNotes;
}

function loopNotesFromOffset(sourceNotes, targetLength, startOffset) {
  const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes.slice() : [];
  const safeTargetLength = Math.max(0, Number(targetLength) || 0);
  const safeStartOffset = Math.max(0, Number(startOffset) || 0);
  if (safeSourceNotes.length === 0 || safeTargetLength <= 0) {
    return [];
  }

  const loopedNotes = [];
  for (let noteIndex = 0; noteIndex < safeTargetLength; noteIndex++) {
    loopedNotes.push(safeSourceNotes[(safeStartOffset + noteIndex) % safeSourceNotes.length]);
  }
  return loopedNotes;
}

function normalizeSectionTrackLoops(section) {
  const trackLengths = trackInstrumentNames
    .map(function (instrumentName) {
      return getSectionLength(section.trackNotes[instrumentName]);
    })
    .filter(function (trackLength) {
      return trackLength > 0;
    });

  if (trackLengths.length <= 1) {
    return;
  }

  if (section && (section.padTracksToLongest || section.loopTracksToLongest)) {
    const targetLength = Math.max.apply(null, trackLengths);
    trackInstrumentNames.forEach(function (instrumentName) {
      const currentNotes = section.trackNotes[instrumentName];
      const currentLength = getSectionLength(currentNotes);
      if (currentLength <= 0 || currentLength >= targetLength) {
        return;
      }
      section.trackNotes[instrumentName] = section.loopTracksToLongest
        ? loopNotesToLength(currentNotes, targetLength)
        : padNotesToLength(currentNotes, targetLength);
    });
    return;
  }

  const targetLength = trackLengths.reduce(function (currentLength, trackLength) {
    return leastCommonMultiple(currentLength, trackLength);
  }, 0);

  if (targetLength <= 0) {
    return;
  }

  trackInstrumentNames.forEach(function (instrumentName) {
    const currentNotes = section.trackNotes[instrumentName];
    const currentLength = getSectionLength(currentNotes);
    if (currentLength <= 0 || currentLength === targetLength) {
      return;
    }
    section.trackNotes[instrumentName] = loopNotesToLength(currentNotes, targetLength);
  });
}

function getRepeatedEntryUnitLength(entryDataList) {
  const entries = Array.isArray(entryDataList) ? entryDataList : [];
  const totalCount = entries.length;
  if (totalCount <= 1) {
    return totalCount;
  }

  const signatures = entries.map(function (entryData) {
    return String(entryData && entryData.entrySignature || '');
  });

  for (let unitLength = 1; unitLength <= totalCount; unitLength++) {
    if (totalCount % unitLength !== 0) {
      continue;
    }

    let isRepeatedUnit = true;
    for (let entryIndex = 0; entryIndex < totalCount; entryIndex++) {
      if (signatures[entryIndex] !== signatures[entryIndex % unitLength]) {
        isRepeatedUnit = false;
        break;
      }
    }

    if (isRepeatedUnit) {
      return unitLength;
    }
  }

  return totalCount;
}

function buildRepeatedParallelEntryUnits(entryDataList) {
  const entries = Array.isArray(entryDataList) ? entryDataList : [];
  const overlayEntriesByRepeatIndex = {};
  const baseEntries = entries.filter(function (entryData) {
    if (entryData && entryData.overlayRepeatIndex !== null && entryData.overlayRepeatIndex !== undefined) {
      const repeatIndex = Math.max(0, Math.round(Number(entryData.overlayRepeatIndex) || 0));
      if (!overlayEntriesByRepeatIndex[repeatIndex]) {
        overlayEntriesByRepeatIndex[repeatIndex] = [];
      }
      overlayEntriesByRepeatIndex[repeatIndex].push(entryData);
      return false;
    }
    return true;
  });
  const repeatedUnitLength = getRepeatedEntryUnitLength(baseEntries);

  function attachOverlayEntries(units) {
    return units.map(function (unitEntries, repeatIndex) {
      const overlayEntries = overlayEntriesByRepeatIndex[repeatIndex] || [];
      return unitEntries.concat(overlayEntries);
    });
  }

  if (repeatedUnitLength > 0 && repeatedUnitLength < baseEntries.length) {
    const units = [];
    for (let unitStartIndex = 0; unitStartIndex < baseEntries.length; unitStartIndex += repeatedUnitLength) {
      units.push(baseEntries.slice(unitStartIndex, unitStartIndex + repeatedUnitLength));
    }
    return attachOverlayEntries(units);
  }

  const runs = [];
  baseEntries.forEach(function (entryData) {
    const signature = String(entryData && entryData.entrySignature || '');
    const previousRun = runs.length > 0 ? runs[runs.length - 1] : null;
    if (previousRun && previousRun.signature === signature) {
      previousRun.entries.push(entryData);
      return;
    }
    runs.push({
      signature: signature,
      entries: [entryData]
    });
  });

  if (runs.length <= 1) {
    return attachOverlayEntries([baseEntries]);
  }

  const runLength = runs[0].entries.length;
  const canZipRuns = runLength > 1 && runs.every(function (run) {
    return run.entries.length === runLength;
  });

  if (!canZipRuns) {
    return attachOverlayEntries([baseEntries]);
  }

  const zippedUnits = [];
  for (let repeatIndex = 0; repeatIndex < runLength; repeatIndex++) {
    zippedUnits.push(runs.map(function (run) {
      return run.entries[repeatIndex];
    }));
  }
  return attachOverlayEntries(zippedUnits);
}

function hasTimelineEntryLabel(entryDataList, labelName) {
  return (Array.isArray(entryDataList) ? entryDataList : []).some(function (entryData) {
    return entryData && entryData.label === labelName;
  });
}

function hasOnlyTimelineEntryLabel(entryDataList, labelName) {
  const entries = Array.isArray(entryDataList) ? entryDataList : [];
  return entries.length > 0 && entries.every(function (entryData) {
    return entryData && entryData.label === labelName;
  });
}

function getTimelineAccompanimentTargets(entryDataList) {
  const targetNames = [];
  (Array.isArray(entryDataList) ? entryDataList : []).forEach(function (entryData) {
    if (!entryData || entryData.label !== 'Begleitung') {
      return;
    }
    (Array.isArray(entryData.targetInstruments) ? entryData.targetInstruments : []).forEach(function (targetName) {
      if (targetNames.indexOf(targetName) === -1) {
        targetNames.push(targetName);
      }
    });
  });
  return targetNames;
}

function getTimelinePlaybackBlockEntries(playbackEntries, startIndex) {
  const entries = Array.isArray(playbackEntries) ? playbackEntries : [];
  const safeStartIndex = Math.max(0, Number(startIndex) || 0);
  if (safeStartIndex >= entries.length) {
    return [];
  }

  const firstEntry = entries[safeStartIndex];
  const blockLabel = firstEntry.label;
  const blockId = firstEntry.blockId;
  const parallelGroupId = firstEntry.parallelGroupId;
  const isPracticeBlock = isPracticeMode && blockId;
  let endIndex = safeStartIndex + 1;

  while (endIndex < entries.length) {
    const nextEntry = entries[endIndex];
    const belongsToCurrentBlock = isPracticeBlock
      ? nextEntry.blockId === blockId
      : (parallelGroupId
          ? nextEntry.parallelGroupId === parallelGroupId
          : nextEntry.label === blockLabel && nextEntry.blockId === blockId);
    if (!belongsToCurrentBlock) {
      break;
    }
    endIndex += 1;
  }

  return entries.slice(safeStartIndex, endIndex);
}

function normalizeSectionRepeatCount(rawValue) {
  const numericValue = Number(rawValue);
  if (!Number.isFinite(numericValue) || numericValue < 1) {
    return 1;
  }
  return Math.max(1, Math.min(100, Math.round(numericValue)));
}

function normalizeSectionOutStep(rawValue) {
  if (rawValue === null || rawValue === undefined || rawValue === '') {
    return null;
  }
  const numericValue = Number(rawValue);
  if (!Number.isFinite(numericValue) || numericValue < 0) {
    return null;
  }
  return Math.round(numericValue);
}

function isSectionLoopEligible(section) {
  if (!section || section.playbackLength <= 0) {
    return false;
  }
  return isPracticeMode ? !section.isLeadIn : section.label === 'Begleitung';
}

function appendNotesToSectionTrack(section, instrumentName, sourceNotes) {
  const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes : [];
  if (!Array.isArray(section.trackNotes[instrumentName])) {
    section.trackNotes[instrumentName] = [];
  }
  section.trackNotes[instrumentName] = section.trackNotes[instrumentName].concat(safeSourceNotes);
}

function createSectionFromEntry(label, labelName, entryData) {
  const section = createOrderedSection(label);
  section.labelName = labelName;
  section.runtimeKey = [label, labelName, entryData.entrySignature].join('::');
  section.swingFactor = entryData.swingFactor === null || entryData.swingFactor === undefined
    ? null
    : Math.max(0, Math.min(100, Number(entryData.swingFactor) || 0));
  section.entrySignatures = [entryData.entrySignature];
  section.sectionTargets = entryData.targetInstruments.slice();
  appendEntryToSection(section, entryData);
  if (entryData.startsContinuingAccompaniment) {
    entryData.targetInstruments.forEach(function (instrumentName) {
      section.continuingAccompaniments[instrumentName] = entryData.patternNotes.slice();
    });
  }
  return section;
}

function applyContinuingAccompanimentsToSections(sections) {
  const activeAccompanimentsByInstrument = {};

  sections.forEach(function (section) {
    const sectionTargets = Array.isArray(section.sectionTargets) ? section.sectionTargets : [];

    sectionTargets.forEach(function (instrumentName) {
      delete activeAccompanimentsByInstrument[instrumentName];
    });

    Object.keys(section.continuingAccompaniments || {}).forEach(function (instrumentName) {
      const sourceNotes = section.continuingAccompaniments[instrumentName];
      if (Array.isArray(sourceNotes) && sourceNotes.length > 0) {
        activeAccompanimentsByInstrument[instrumentName] = {
          notes: sourceNotes.slice(),
          nextStepIndex: 0
        };
      }
    });

    const sectionLength = Math.max.apply(null, trackInstrumentNames.map(function (instrumentName) {
      return getSectionLength(section.trackNotes[instrumentName]);
    }).concat(0));

    Object.keys(activeAccompanimentsByInstrument).forEach(function (instrumentName) {
      const accompaniment = activeAccompanimentsByInstrument[instrumentName];
      if (!accompaniment || !Array.isArray(accompaniment.notes) || accompaniment.notes.length === 0) {
        return;
      }

      if (sectionTargets.indexOf(instrumentName) === -1 && sectionLength > 0) {
        section.trackNotes[instrumentName] = mergeNotesIntoTrack(
          section.trackNotes[instrumentName],
          loopNotesFromOffset(accompaniment.notes, sectionLength, accompaniment.nextStepIndex),
          0
        );
      }

      accompaniment.nextStepIndex += sectionLength;
    });
  });
}

function finalizeSectionLengths(sections) {
  sections.forEach(function (section) {
    section.length = Math.max.apply(null, trackInstrumentNames.map(function (instrumentName) {
      return getSectionLength(section.trackNotes[instrumentName]);
    }).concat(0));
    section.repeatCount = normalizeSectionRepeatCount(section.repeatCount);
    section.playbackLength = section.length * section.repeatCount;
  });
}

function buildFlatBarsFromRows(playerRows) {
  const flatBars = [];

  for (let i = 1; i < playerRows.length; i++) {
    const row = playerRows[i];
    if (!row || !Array.isArray(row.Reihe)) {
      continue;
    }

    const rowNotes = row.Reihe;
    const halfLength = Math.floor(rowNotes.length / 2);
    flatBars.push({
      index: flatBars.length + 1,
      instrument: row.Instrument_1 || '',
      instrumentMode: row.InstrumentMode_1 || 'single',
      label: row.Bezeichner_1 || '',
      notes: rowNotes.slice(0, halfLength)
    });
    flatBars.push({
      index: flatBars.length + 1,
      instrument: row.Instrument_2 || '',
      instrumentMode: row.InstrumentMode_2 || 'single',
      label: row.Bezeichner_2 || '',
      notes: rowNotes.slice(halfLength)
    });
  }

  return flatBars;
}

function normalizeTimelineTargetInstrument(instrumentName) {
  if (!instrumentName) {
    return '';
  }

  const instrumentMap = {
    Djembe_1: 'Djembe_1',
    Djembe_2: 'Djembe_2',
    Djembe_3: 'Djembe_3',
    'Djembe 1': 'Djembe_1',
    'Djembe 2': 'Djembe_2',
    'Djembe 3': 'Djembe_3',
    Kenkeni: 'Kenkeni',
    Sangban: 'Sangban',
    Doundoun: 'Doundoun',
    Dununba: 'Doundoun',
    Dreierbass: 'Dreierbass',
    'Bässe': 'Bässe'
  };

  return instrumentMap[instrumentName] || '';
}

function normalizeTimelineEntryTargets(targetInstruments) {
  return Array.isArray(targetInstruments)
    ? targetInstruments
        .map(normalizeTimelineTargetInstrument)
        .reduce(function (allTargets, targetName) {
          if (targetName === 'Bässe') {
            return allTargets.concat(timelineBassTargets);
          }
          if (targetName && allTargets.indexOf(targetName) === -1) {
            allTargets.push(targetName);
          }
          return allTargets;
        }, [])
    : [];
}

function isTimelineContinuationMarker(markerValue) {
  return markerValue === 'continue';
}

function patternStartsContinuingAccompaniment(pattern) {
  if (isPracticeMode) {
    return false;
  }

  if (!pattern || pattern.label !== 'Begleitung' || !Array.isArray(pattern.bars)) {
    return false;
  }

  return pattern.bars.some(function (bar) {
    const repeatInfo = bar && bar.repeat ? bar.repeat : {};
    return normalizeRepeatMarkerList(repeatInfo.end).some(isTimelineContinuationMarker);
  });
}

function barHasPlayableContent(bar) {
  if (!bar) {
    return false;
  }

  const notes = getBarPlayableNotes(bar);
  return notes.some(function (noteValue) {
    return noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '';
  });
}

function trimTrailingSilentPatternBars(patternBars) {
  const safeBars = Array.isArray(patternBars) ? patternBars.slice() : [];
  if (safeBars.length <= 1) {
    return safeBars;
  }

  let lastPlayableBarIndex = -1;
  safeBars.forEach(function (bar, barIndex) {
    if (barHasPlayableContent(bar)) {
      lastPlayableBarIndex = barIndex;
    }
  });

  if (lastPlayableBarIndex <= 0) {
    return safeBars.slice(0, 1);
  }

  return safeBars.slice(0, lastPlayableBarIndex + 1);
}

function getContinuingAccompanimentPatternBars(pattern) {
  return trimTrailingSilentPatternBars(expandPatternBars(pattern));
}

function buildFlatBarsFromTimeline(config) {
  const patternLibrary = Array.isArray(config.PatternLibrary) ? config.PatternLibrary : [];
  const timelineEntries = Array.isArray(config.TimelineEntries) ? config.TimelineEntries : [];
  const patternById = {};
  const flatBars = [];
  const activeAccompanimentsByInstrument = {};

  patternLibrary.forEach(function (pattern) {
    if (pattern && pattern.id) {
      patternById[pattern.id] = pattern;
    }
  });

  timelineEntries.forEach(function (entry) {
    const pattern = patternById[entry.patternId];
    if (!pattern || !Array.isArray(pattern.bars)) {
      return;
    }

    const targetInstruments = normalizeTimelineEntryTargets(entry.targetInstruments);
    const startsContinuingAccompaniment = patternStartsContinuingAccompaniment(pattern);
    const expandedPatternBars = startsContinuingAccompaniment
      ? getContinuingAccompanimentPatternBars(pattern)
      : expandPatternBars(pattern);

    targetInstruments.forEach(function (instrumentName) {
      delete activeAccompanimentsByInstrument[instrumentName];
    });

    if (startsContinuingAccompaniment && expandedPatternBars.length > 0) {
      targetInstruments.forEach(function (instrumentName) {
        activeAccompanimentsByInstrument[instrumentName] = {
          bars: expandedPatternBars,
          nextBarIndex: 0
        };
      });
    }

    expandedPatternBars.forEach(function (bar) {
      const trackNotes = {};
      Object.keys(activeAccompanimentsByInstrument).forEach(function (instrumentName) {
        const accompaniment = activeAccompanimentsByInstrument[instrumentName];
        if (!accompaniment || !Array.isArray(accompaniment.bars) || accompaniment.bars.length === 0) {
          return;
        }
        const accompanimentBar = accompaniment.bars[accompaniment.nextBarIndex % accompaniment.bars.length];
        if (targetInstruments.indexOf(instrumentName) === -1 && accompanimentBar && Array.isArray(accompanimentBar.notes)) {
          trackNotes[instrumentName] = accompanimentBar.notes.slice();
        }
        accompaniment.nextBarIndex += 1;
      });

      flatBars.push({
        index: flatBars.length + 1,
        instrument: '',
        instrumentMode: 'timelineTargets',
        targetInstruments: targetInstruments.slice(),
        label: bar.label || pattern.label || '',
        labelName: pattern.labelName || pattern.label || bar.label || '',
        notes: Array.isArray(bar.notes) ? bar.notes.slice() : [],
        trackNotes: trackNotes
      });
    });
  });

  return flatBars;
}

function buildTimelineSections(config) {
  const patternLibrary = Array.isArray(config.PatternLibrary) ? config.PatternLibrary : [];
  const timelineEntries = Array.isArray(config.TimelineEntries) ? config.TimelineEntries : [];
  const patternById = {};
  const sections = [];
  const expandedEntries = [];

  patternLibrary.forEach(function (pattern) {
    if (pattern && pattern.id) {
      patternById[pattern.id] = pattern;
    }
  });

  timelineEntries.forEach(function (entry) {
    const pattern = patternById[entry.patternId];
    if (!pattern) {
      return;
    }

    const sectionLabel = pattern.label || '';
    if (!sectionLabel) {
      return;
    }

    const targetInstruments = normalizeTimelineEntryTargets(entry.targetInstruments);
    const entrySignature = [
      pattern.sourceKey || pattern.id || '',
      targetInstruments.slice().sort().join(',')
    ].join('::');
    const startsContinuingAccompaniment = patternStartsContinuingAccompaniment(pattern);
    const patternNotes = startsContinuingAccompaniment
      ? flattenContinuingAccompanimentNotes(pattern)
      : flattenPatternNotes(pattern);
    const patternInStep = getPatternInStep(pattern);
    const patternOutStep = getPatternOutStep(pattern);
    expandedEntries.push({
      label: sectionLabel,
      labelName: pattern.labelName || pattern.label || '',
      blockId: String(entry.blockId || ''),
      parallelGroupId: String(entry.parallelGroupId || ''),
      overlayRepeatIndex: entry.overlayRepeatIndex === null || entry.overlayRepeatIndex === undefined
        ? null
        : Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0)),
      patternFingerprint: pattern.sourceKey || pattern.id || '',
      entrySignature: entrySignature,
      handMode: String(entry.handMode || ''),
      targetInstruments: targetInstruments,
      swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
        ? null
        : Math.max(0, Math.min(100, Number(entry.swingFactor) || 0)),
      startsContinuingAccompaniment: startsContinuingAccompaniment,
      patternNotes: patternNotes,
      patternInStep: patternInStep,
      patternOutStep: patternOutStep
    });
  });

  const continuingAccompanimentEntries = expandedEntries.filter(function (entryData) {
    return entryData.startsContinuingAccompaniment;
  });
  const playbackEntries = expandedEntries.slice();

  let blockStartIndex = 0;
  while (blockStartIndex < playbackEntries.length) {
    const blockLabel = playbackEntries[blockStartIndex].label;
    const blockId = playbackEntries[blockStartIndex].blockId;
    const parallelGroupId = playbackEntries[blockStartIndex].parallelGroupId;
    const isPracticeBlock = isPracticeMode && blockId;
    let blockEndIndex = blockStartIndex + 1;
    while (blockEndIndex < playbackEntries.length) {
      const nextEntry = playbackEntries[blockEndIndex];
      const belongsToCurrentBlock = isPracticeBlock
        ? nextEntry.blockId === blockId
        : (parallelGroupId
            ? nextEntry.parallelGroupId === parallelGroupId
            : nextEntry.label === blockLabel && nextEntry.blockId === blockId);
      if (!belongsToCurrentBlock) {
        break;
      }
      blockEndIndex += 1;
    }

    const blockEntries = playbackEntries.slice(blockStartIndex, blockEndIndex);
    const nextBlockEntries = getTimelinePlaybackBlockEntries(playbackEntries, blockEndIndex);
    const currentBlockContainsEchauffement = hasTimelineEntryLabel(blockEntries, 'Echauffement');
    const nextAccompanimentTargets = getTimelineAccompanimentTargets(nextBlockEntries);
    function shouldApplyOutToTimelineEntry(entryData) {
      if (isPracticeMode || !entryData || entryData.patternOutStep === null) {
        return false;
      }

      if (entryData.label === 'Echauffement') {
        return true;
      }

      if (entryData.label !== 'Begleitung') {
        return true;
      }

      return entryData.label === 'Begleitung' &&
        (currentBlockContainsEchauffement ||
          !doInstrumentSetsOverlap(entryData.targetInstruments, nextAccompanimentTargets));
    }
    const blockLanes = [];

    blockEntries.forEach(function (entryData) {
      let matchingLane = blockLanes.find(function (laneData) {
        return doInstrumentSetsOverlap(laneData.targetInstruments, entryData.targetInstruments);
      });

      if (!matchingLane) {
        matchingLane = {
          targetInstruments: entryData.targetInstruments.slice(),
          entries: []
        };
        blockLanes.push(matchingLane);
      } else {
        matchingLane.targetInstruments = matchingLane.targetInstruments.concat(
          entryData.targetInstruments.filter(function (instrumentName) {
            return matchingLane.targetInstruments.indexOf(instrumentName) === -1;
          })
        );
      }

      matchingLane.entries.push(entryData);
    });

    if (parallelGroupId || isPracticeBlock) {
      const repeatedParallelUnits = parallelGroupId && !isPracticeBlock
        ? buildRepeatedParallelEntryUnits(blockEntries)
        : [blockEntries];
      if (parallelGroupId && !isPracticeBlock && repeatedParallelUnits.length > 1) {
        repeatedParallelUnits.forEach(function (repeatEntries, repeatIndex) {
          const repeatedSection = createOrderedSection(blockLabel);
          const repeatContainsEchauffement = hasTimelineEntryLabel(repeatEntries, 'Echauffement');
          const repeatContainsOnlyAccompaniment = hasOnlyTimelineEntryLabel(repeatEntries, 'Begleitung');
          const repeatContainsOverlay = repeatEntries.some(function (entryData) {
            return entryData && entryData.overlayRepeatIndex !== null && entryData.overlayRepeatIndex !== undefined;
          });
          const repeatTargetLength = getTimelineParallelTargetLength(
            repeatEntries,
            repeatContainsEchauffement,
            repeatContainsOverlay
          );
          repeatedSection.loopTracksToLongest = repeatContainsOnlyAccompaniment;
          repeatedSection.padTracksToLongest = !repeatContainsEchauffement && !repeatContainsOnlyAccompaniment;
          const repeatedSectionLabelNames = [];
          const isLastRepeatedParallelUnit = repeatIndex === repeatedParallelUnits.length - 1;

          repeatEntries.forEach(function (entryData) {
            const shouldApplyOut = !isPracticeMode &&
              isLastRepeatedParallelUnit &&
              shouldApplyOutToTimelineEntry(entryData);
            const effectiveNotes = getTimelineEntryEffectiveNotes(
              entryData,
              shouldApplyOut,
              repeatContainsEchauffement || repeatContainsOverlay ? repeatTargetLength : 0
            );

            entryData.targetInstruments.forEach(function (instrumentName) {
              repeatedSection.trackNotes[instrumentName] = mergeNotesIntoTrack(
                repeatedSection.trackNotes[instrumentName],
                effectiveNotes,
                0
              );
              if (instrumentName.indexOf('Djembe_') === 0) {
                repeatedSection.trackHandModes[instrumentName] = String(entryData.handMode || '');
              }
            });

            if (repeatedSectionLabelNames.indexOf(entryData.labelName) === -1) {
              repeatedSectionLabelNames.push(entryData.labelName);
            }
            if (repeatedSection.swingFactor === null && entryData.swingFactor !== null && entryData.swingFactor !== undefined) {
              repeatedSection.swingFactor = entryData.swingFactor;
            }
            if (entryData.startsContinuingAccompaniment) {
              entryData.targetInstruments.forEach(function (instrumentName) {
                repeatedSection.continuingAccompaniments[instrumentName] = entryData.patternNotes.slice();
              });
            }
          });

          repeatedSection.labelName = repeatedSectionLabelNames.join(' + ');
          repeatedSection.runtimeKey = [
            'parallel',
            parallelGroupId,
            repeatIndex,
            repeatedSection.labelName
          ].join('::');
          normalizeSectionTrackLoops(repeatedSection);
          mergeTimelineEntryPickupsIntoPreviousSection(sections, repeatEntries);
          sections.push(repeatedSection);
        });
        blockStartIndex = blockEndIndex;
        continue;
      }

      const mergedSection = createOrderedSection(blockLabel);
      const currentBlockContainsOverlay = blockEntries.some(function (entryData) {
        return entryData && entryData.overlayRepeatIndex !== null && entryData.overlayRepeatIndex !== undefined;
      });
      const blockTargetLength = getTimelineParallelTargetLength(
        blockEntries,
        currentBlockContainsEchauffement,
        currentBlockContainsOverlay
      );
      const currentBlockContainsOnlyAccompaniment = hasOnlyTimelineEntryLabel(blockEntries, 'Begleitung');
      mergedSection.loopTracksToLongest = Boolean(parallelGroupId && !isPracticeBlock && currentBlockContainsOnlyAccompaniment);
      mergedSection.padTracksToLongest = Boolean(
        parallelGroupId &&
        !isPracticeBlock &&
        !currentBlockContainsEchauffement &&
        !currentBlockContainsOnlyAccompaniment
      );
      const sectionLabelNames = [];

      blockEntries.forEach(function (entryData) {
        const shouldApplyOut = shouldApplyOutToTimelineEntry(entryData);
        const effectiveNotes = getTimelineEntryEffectiveNotes(
          entryData,
          shouldApplyOut,
          currentBlockContainsEchauffement || currentBlockContainsOverlay ? blockTargetLength : 0
        );

        entryData.targetInstruments.forEach(function (instrumentName) {
          mergedSection.trackNotes[instrumentName] = mergeNotesIntoTrack(
            mergedSection.trackNotes[instrumentName],
            effectiveNotes,
            0
          );
          if (instrumentName.indexOf('Djembe_') === 0) {
            mergedSection.trackHandModes[instrumentName] = String(entryData.handMode || '');
          }
        });

        if (sectionLabelNames.indexOf(entryData.labelName) === -1) {
          sectionLabelNames.push(entryData.labelName);
        }
        if (mergedSection.swingFactor === null && entryData.swingFactor !== null && entryData.swingFactor !== undefined) {
          mergedSection.swingFactor = entryData.swingFactor;
        }
        if (entryData.startsContinuingAccompaniment) {
          entryData.targetInstruments.forEach(function (instrumentName) {
            mergedSection.continuingAccompaniments[instrumentName] = entryData.patternNotes.slice();
          });
        }
      });

      mergedSection.labelName = sectionLabelNames.join(' + ');
      mergedSection.runtimeKey = [
        isPracticeBlock ? 'practice' : 'parallel',
        isPracticeBlock ? blockId : parallelGroupId,
        mergedSection.labelName
      ].join('::');
      normalizeSectionTrackLoops(mergedSection);
      mergeTimelineEntryPickupsIntoPreviousSection(sections, blockEntries);
      sections.push(mergedSection);
      blockStartIndex = blockEndIndex;
      continue;
    }

    const maxLaneLength = Math.max.apply(null, blockLanes.map(function (laneData) {
      return laneData.entries.length;
    }).concat(0));

    for (let occurrenceIndex = 0; occurrenceIndex < maxLaneLength; occurrenceIndex++) {
      let currentCycleSection = null;

      blockLanes.forEach(function (laneData) {
        const laneEntry = laneData.entries[occurrenceIndex];
        if (!laneEntry) {
          return;
        }

        const isLastLaneOccurrence = occurrenceIndex === laneData.entries.length - 1;
        const occurrenceEntry = isLastLaneOccurrence && shouldApplyOutToTimelineEntry(laneEntry)
          ? Object.assign({}, laneEntry, {
              patternNotes: getTimelineEntryEffectiveNotes(laneEntry, true, 0)
            })
          : laneEntry;

        if (!currentCycleSection) {
          currentCycleSection = createSectionFromEntry(blockLabel, occurrenceEntry.labelName, occurrenceEntry);
          return;
        }

        if (currentCycleSection.labelName !== occurrenceEntry.labelName) {
          currentCycleSection.labelName += ' + ' + occurrenceEntry.labelName;
        }
        if (currentCycleSection.swingFactor === null && occurrenceEntry.swingFactor !== null && occurrenceEntry.swingFactor !== undefined) {
          currentCycleSection.swingFactor = occurrenceEntry.swingFactor;
        }
        currentCycleSection.entrySignatures.push(occurrenceEntry.entrySignature);
        currentCycleSection.sectionTargets = currentCycleSection.sectionTargets.concat(
          occurrenceEntry.targetInstruments.filter(function (instrumentName) {
            return currentCycleSection.sectionTargets.indexOf(instrumentName) === -1;
          })
        );
        appendEntryToSection(currentCycleSection, occurrenceEntry);
        if (occurrenceEntry.startsContinuingAccompaniment) {
          occurrenceEntry.targetInstruments.forEach(function (instrumentName) {
            currentCycleSection.continuingAccompaniments[instrumentName] = occurrenceEntry.patternNotes.slice();
          });
        }
      });

      if (currentCycleSection) {
        normalizeSectionTrackLoops(currentCycleSection);
        const cycleEntries = blockLanes
          .map(function (laneData) {
            return laneData.entries[occurrenceIndex];
          })
          .filter(Boolean);
        mergeTimelineEntryPickupsIntoPreviousSection(sections, cycleEntries);
        sections.push(currentCycleSection);
      }
    }

    blockStartIndex = blockEndIndex;
  }

  if (sections.length > 0) {
    const accompanimentStartSection = sections.find(function (section) {
      return section && section.label === 'Begleitung';
    }) || sections[0];

    continuingAccompanimentEntries.forEach(function (entryData) {
      entryData.targetInstruments.forEach(function (instrumentName) {
        if (!accompanimentStartSection.continuingAccompaniments[instrumentName]) {
          accompanimentStartSection.continuingAccompaniments[instrumentName] = entryData.patternNotes.slice();
        }
      });
    });
  }

  applyContinuingAccompanimentsToSections(sections);
  finalizeSectionLengths(sections);

  return sections;
}

function buildPracticeSections(config) {
  const patternLibrary = Array.isArray(config.PatternLibrary) ? config.PatternLibrary : [];
  const practiceBlocks = Array.isArray(config.PracticeBlocks) ? config.PracticeBlocks : [];
  const patternById = {};
  const sections = [];

  patternLibrary.forEach(function (pattern) {
    if (pattern && pattern.id) {
      patternById[pattern.id] = pattern;
    }
  });

  practiceBlocks.forEach(function (block, blockIndex) {
    const blockEntries = Array.isArray(block && block.entries) ? block.entries : [];
    const mergedSection = createOrderedSection('');
    mergedSection.repeatCount = normalizeSectionRepeatCount(block && block.repeatCount);
    mergedSection.isLeadIn = Boolean(block && block.isLeadIn);
    const sectionLabels = [];
    const sectionLabelNames = [];

    blockEntries.forEach(function (entry) {
      const pattern = patternById[entry.patternId];
      if (!pattern) {
        return;
      }

      const targetInstruments = normalizeTimelineEntryTargets(entry.targetInstruments);
      const patternNotes = flattenPatternNotes(pattern);
      const patternLabel = pattern.label || '';
      const patternLabelName = pattern.labelName || pattern.label || '';

      if (patternLabel && sectionLabels.indexOf(patternLabel) === -1) {
        sectionLabels.push(patternLabel);
      }
      if (patternLabelName && sectionLabelNames.indexOf(patternLabelName) === -1) {
        sectionLabelNames.push(patternLabelName);
      }

      targetInstruments.forEach(function (instrumentName) {
        mergedSection.trackNotes[instrumentName] = mergeNotesIntoTrack(
          mergedSection.trackNotes[instrumentName],
          patternNotes,
          0
        );
        if (instrumentName.indexOf('Djembe_') === 0) {
          mergedSection.trackHandModes[instrumentName] = String(entry.handMode || '');
        }
      });

      if (mergedSection.swingFactor === null && entry.swingFactor !== null && entry.swingFactor !== undefined) {
        mergedSection.swingFactor = Math.max(0, Math.min(100, Number(entry.swingFactor) || 0));
      }
    });

    mergedSection.label = sectionLabels.indexOf('Begleitung') !== -1
      ? 'Begleitung'
      : (sectionLabels[0] || 'Begleitung');
    mergedSection.labelName = sectionLabelNames.join(' + ') || mergedSection.label;
    mergedSection.runtimeKey = ['practice', block && block.id ? block.id : blockIndex, mergedSection.labelName].join('::');
    normalizeSectionTrackLoops(mergedSection);

    if (trackInstrumentNames.some(function (instrumentName) {
      return getSectionLength(mergedSection.trackNotes[instrumentName]) > 0;
    })) {
      sections.push(mergedSection);
    }
  });

  finalizeSectionLengths(sections);
  return sections;
}

function buildConfiguredPracticeSections(config) {
  const configuredSections = Array.isArray(config.PracticeSections) ? config.PracticeSections : [];
  const sections = configuredSections.map(function (configuredSection, sectionIndex) {
    const section = createOrderedSection(configuredSection && configuredSection.label ? configuredSection.label : 'Begleitung');
    section.repeatCount = normalizeSectionRepeatCount(configuredSection && configuredSection.repeatCount);
    section.isLeadIn = Boolean(configuredSection && configuredSection.isLeadIn);
    section.labelName = configuredSection && configuredSection.labelName ? configuredSection.labelName : section.label;
    section.runtimeKey = configuredSection && configuredSection.runtimeKey
      ? configuredSection.runtimeKey
      : ['practice-config', sectionIndex, section.labelName].join('::');
    section.swingFactor = configuredSection &&
      configuredSection.swingFactor !== null &&
      configuredSection.swingFactor !== undefined
        ? Math.max(0, Math.min(100, Number(configuredSection.swingFactor) || 0))
        : null;
    section.practiceTargetInstruments = Array.isArray(configuredSection && configuredSection.practiceTargetInstruments)
      ? configuredSection.practiceTargetInstruments.filter(function (instrumentName) {
          return trackInstrumentNames.indexOf(instrumentName) !== -1;
        })
      : [];
    section.forceFinalOutAtSectionEnd = Boolean(configuredSection && configuredSection.forceFinalOutAtSectionEnd);

    const sourceTrackNotes = configuredSection && configuredSection.trackNotes ? configuredSection.trackNotes : {};
    const sourceTrackHandModes = configuredSection && configuredSection.trackHandModes ? configuredSection.trackHandModes : {};
    const sourceFinalRepeatOutSteps = configuredSection && configuredSection.finalRepeatOutSteps
      ? configuredSection.finalRepeatOutSteps
      : {};
    trackInstrumentNames.forEach(function (instrumentName) {
      section.trackNotes[instrumentName] = Array.isArray(sourceTrackNotes[instrumentName])
        ? sourceTrackNotes[instrumentName].slice()
        : [];
      section.trackHandModes[instrumentName] = String(sourceTrackHandModes[instrumentName] || '');
      section.finalRepeatOutSteps[instrumentName] = normalizeSectionOutStep(sourceFinalRepeatOutSteps[instrumentName]);
    });

    normalizeSectionTrackLoops(section);
    return section;
  }).filter(function (section) {
    return trackInstrumentNames.some(function (instrumentName) {
      return getSectionLength(section.trackNotes[instrumentName]) > 0;
    });
  });

  finalizeSectionLengths(sections);
  return sections;
}

function expandBarsWithRepeats(flatBars, repeatRangesToApply, startBarIndex, endBarIndex) {
  const expandedBars = [];
  let currentBarIndex = startBarIndex;

  while (currentBarIndex <= endBarIndex) {
    const matchingRanges = repeatRangesToApply
      .filter(function (repeatRange) {
        return !isTimelineContinuationMarker(repeatRange.count) &&
          repeatRange.startBar === currentBarIndex &&
          repeatRange.endBar <= endBarIndex;
      })
      .sort(function (rangeA, rangeB) {
        return rangeB.endBar - rangeA.endBar;
      });

    const matchingRange = matchingRanges[0];
    if (!matchingRange) {
      expandedBars.push(flatBars[currentBarIndex - 1]);
      currentBarIndex += 1;
      continue;
    }

    const repeatedSegment = expandBarsWithRepeats(
      flatBars,
      repeatRangesToApply.filter(function (repeatRange) {
        return repeatRange.startBar >= matchingRange.startBar &&
          repeatRange.endBar <= matchingRange.endBar &&
          !(repeatRange.startBar === matchingRange.startBar && repeatRange.endBar === matchingRange.endBar);
      }),
      matchingRange.startBar,
      matchingRange.endBar
    );

    expandedBars.push.apply(expandedBars, repeatedSegment);
    if (matchingRange.count === 'loop') {
      expandedBars.push.apply(expandedBars, repeatedSegment);
    } else {
      const repeatCount = Number(matchingRange.count) || 0;
      for (let repeatIndex = 0; repeatIndex < repeatCount; repeatIndex++) {
        expandedBars.push.apply(expandedBars, repeatedSegment);
      }
    }

    currentBarIndex = matchingRange.endBar + 1;

    if (expandedBars.length > MAX_EXPANDED_BARS) {
      throw new Error('Zu viele expandierte Takte durch Wiederholungen');
    }
  }

  return expandedBars;
}

const flatBars = isTimelineMode ? buildFlatBarsFromTimeline(playerConfig) : buildFlatBarsFromRows(obj);
const usedDjembeTrackNames = trackInstrumentNames.filter(function (instrumentName) {
  return instrumentName.indexOf('Djembe_') === 0 && flatBars.some(function (bar) {
    if (Array.isArray(bar.targetInstruments)) {
      return bar.targetInstruments.indexOf(instrumentName) !== -1;
    }
    return bar.instrument === instrumentName;
  });
});
const expandedBars = isTimelineMode
  ? flatBars
  : expandBarsWithRepeats(flatBars, repeatRanges, 1, flatBars.length);
let orderedSections = isTimelineMode
  ? (isPracticeMode && Array.isArray(playerConfig.PracticeSections) && playerConfig.PracticeSections.length > 0
      ? buildConfiguredPracticeSections(playerConfig)
      : (isPracticeMode && Array.isArray(playerConfig.PracticeBlocks) && playerConfig.PracticeBlocks.length > 0
          ? buildPracticeSections(playerConfig)
          : buildTimelineSections(playerConfig)))
  : [];
const orderedSectionLabels = ['Call', 'Intro', 'Begleitung', 'Echauffement', 'Outro'];
let currentOrderedSection = null;
console.log('repeatRanges', repeatRanges);
console.log('expandedBars', expandedBars);

if (isTimelineMode) {
  orderedSections.forEach(function (section, sectionIndex) {
    appendSectionToSteuerung(section, sectionIndex + 1);
  });
} else {
  for (let i = 0; i < expandedBars.length; i++) {
    const bar = expandedBars[i];
    if (!bar) {
      continue;
    }

    appendBarToTracks(bar, i + 1);

    if (orderedSectionLabels.indexOf(bar.label) === -1) {
      currentOrderedSection = null;
      continue;
    }

    const barSectionName = bar.labelName || bar.label || '';

    if (!currentOrderedSection ||
        currentOrderedSection.label !== bar.label ||
        currentOrderedSection.labelName !== barSectionName) {
      currentOrderedSection = createOrderedSection(bar.label);
      currentOrderedSection.labelName = barSectionName;
      orderedSections.push(currentOrderedSection);
    }

    appendBarToOrderedSection(currentOrderedSection, bar);
  }
}

function updateOrderedSectionHandModes(configuredSections) {
  if (!Array.isArray(configuredSections) || !Array.isArray(orderedSections)) {
    return;
  }

  configuredSections.forEach(function (configuredSection, sectionIndex) {
    if (!configuredSection || !configuredSection.trackHandModes) {
      return;
    }

    const runtimeKey = String(configuredSection.runtimeKey || '');
    const targetSection = runtimeKey
      ? orderedSections.find(function (section) {
          return section && section.runtimeKey === runtimeKey;
        })
      : orderedSections[sectionIndex];

    if (!targetSection || !targetSection.trackHandModes) {
      return;
    }

    trackInstrumentNames.forEach(function (instrumentName) {
      targetSection.trackHandModes[instrumentName] = String(configuredSection.trackHandModes[instrumentName] || '');
    });
  });
}

function resetDjembeHandStates() {
  Object.keys(djembeHandStates).forEach(function (trackName) {
    djembeHandStates[trackName].nextHand = 'R';
    djembeHandStates[trackName].leadHand = 'R';
    djembeHandStates[trackName].lastHand = '';
    djembeHandStates[trackName].lastSectionKey = '';
  });
}

function updatePracticeDurationSeconds(nextDurationSeconds) {
  if (nextDurationSeconds === undefined) {
    return;
  }

  const normalizedDurationSeconds = Math.max(0, Number(nextDurationSeconds) || 0);
  if (practiceDurationSeconds === normalizedDurationSeconds) {
    return;
  }

  practiceDurationSeconds = normalizedDurationSeconds;
  if (isPlaying && practiceDurationSeconds > 0 && instr && instr._audioCtx) {
    practiceStopAudioTime = instr._audioCtx.currentTime + practiceDurationSeconds;
    practiceTimerFinalLoopStartStep = null;
  } else if (practiceDurationSeconds <= 0) {
    practiceStopAudioTime = 0;
    practiceTimerFinalLoopStartStep = null;
  }
}

function replaceOrderedSectionsWithConfigured(configuredSections, nextLoopCount, nextDurationSeconds) {
  if (!Array.isArray(configuredSections) || configuredSections.length === 0) {
    return false;
  }

  const nextSections = buildConfiguredPracticeSections({
    PracticeSections: configuredSections
  });

  if (nextSections.length === 0) {
    return false;
  }

  orderedSections = nextSections;
  if (nextLoopCount !== undefined) {
    timelineLoopCount = normalizeTimelineLoopCountValue(nextLoopCount);
    timelineLoop = timelineLoopCount === 'loop';
  }
  updatePracticeDurationSeconds(nextDurationSeconds);
  recalculateOrderedSectionTiming();
  resetDjembeHandStates();
  return true;
}

function getNextPracticeSectionBoundaryStep(playbackStep) {
  const sectionContext = getPlaybackSectionContext(playbackStep);
  if (!sectionContext || !sectionContext.section || !sectionContext.section.playbackLength) {
    return playbackStep;
  }

  return playbackStep + Math.max(1, sectionContext.section.playbackLength - sectionContext.sectionStep);
}

function queuePracticeSectionsUpdate(configuredSections, nextLoopCount, nextDurationSeconds) {
  if (!isPracticeMode || !Array.isArray(configuredSections) || configuredSections.length === 0) {
    return;
  }

  const updateToken = ++practiceSectionUpdateLoadToken;
  const requiredTrackNames = getActivePracticeTrackNamesFromSections(configuredSections);
  ensurePracticeTrackSoundsLoaded(requiredTrackNames)
    .then(function () {
      if (updateToken !== practiceSectionUpdateLoadToken) {
        return;
      }

      if (!isPlaying) {
        replaceOrderedSectionsWithConfigured(configuredSections, nextLoopCount, nextDurationSeconds);
        globalPlaybackStep = 0;
        notifyEmbeddedPlaybackStep(globalPlaybackStep, nextNoteTime);
        return;
      }

      pendingPracticeSections = configuredSections;
      pendingPracticeLoopCount = nextLoopCount;
      pendingPracticeDurationSeconds = nextDurationSeconds;
      pendingPracticeSectionsApplyStep = getNextPracticeSectionBoundaryStep(globalPlaybackStep);
    })
    .catch(function (error) {
      console.error('Practice-Samples konnten nicht nachgeladen werden:', error);
      updateLoadingStatus('Ladefehler: ' + (error && error.message ? error.message : String(error)));
    });
}

function applyPendingPracticeSectionsIfReady() {
  if (!pendingPracticeSections || pendingPracticeSectionsApplyStep === null) {
    return;
  }

  if (globalPlaybackStep < pendingPracticeSectionsApplyStep) {
    return;
  }

  if (replaceOrderedSectionsWithConfigured(pendingPracticeSections, pendingPracticeLoopCount, pendingPracticeDurationSeconds)) {
    globalPlaybackStep = 0;
    notifyEmbeddedPlaybackStep(globalPlaybackStep, nextNoteTime);
  }

  pendingPracticeSections = null;
  pendingPracticeLoopCount = false;
  pendingPracticeDurationSeconds = undefined;
  pendingPracticeSectionsApplyStep = null;
}

console.log(instrument);
console.log(steuerung);
console.log('orderedSections', orderedSections);

let djembe_1_notes = [];
let djembe_2_notes = [];
let djembe_3_notes = [];
let kenkeni_notes = [];
let sangban_notes = [];
let doundoun_notes = [];
let dreierbass_notes = [];

for (const key in steuerung) {
  if (key === "Djembe_1") djembe_1_notes = steuerung[key].noten;
  if (key === "Djembe_2") djembe_2_notes = steuerung[key].noten;
  if (key === "Djembe_3") djembe_3_notes = steuerung[key].noten;
  if (key === "Kenkeni")  kenkeni_notes  = steuerung[key].noten;
  if (key === "Sangban")  sangban_notes  = steuerung[key].noten;
  if (key === "Doundoun") doundoun_notes = steuerung[key].noten;
  if (key === "Dreierbass") dreierbass_notes = steuerung[key].noten;
}

// Scheduling
let tempo = initialTempo;
const bpmControl = document.querySelector('#bpm');
const bpmValEl = document.querySelector('#bpmval');
let soloTrackName = '';

bpmControl.value = String(initialTempo);
bpmValEl.innerText = String(initialTempo);

bpmControl.addEventListener('input', ev => {
  tempo = Number(ev.target.value);
  bpmValEl.innerText = tempo;
  notifyEmbeddedTempoChange(tempo);
}, false);

const soloTrackControl = document.querySelector('#soloTrack');
if (soloTrackControl) {
  soloTrackControl.addEventListener('change', ev => {
    soloTrackName = ev.target.value;
  }, false);
}

const lookahead = 25;          // ms
const scheduleAheadTime = 0.25; // sec
const playerStartDelay = 0.18; // sec
const practiceLeadInDelay = isPracticeMode ? 3.0 : 0; // sec

let nextNoteTime = 0.0;
let timerID;
let instr;
let globalPlaybackStep = 0;
let practiceStopAudioTime = 0;
let practiceTimerFinalLoopStartStep = null;

function getSectionLength(sectionNotes) {
  return Array.isArray(sectionNotes) ? sectionNotes.length : 0;
}

function createTrackState(notesArray, instrumentKey) {
  const instrumentState = steuerung[instrumentKey] || {
    notes: notesArray,
    callNotes: [],
    introNotes: [],
    echauffementNotes: [],
    begleitungNotes: []
  };

  return {
    notes: notesArray,
    callNotes: instrumentState.callNotes || [],
    introNotes: instrumentState.introNotes || [],
    echauffementNotes: instrumentState.echauffementNotes || [],
    begleitungNotes: instrumentState.begleitungNotes || []
  };
}

const trackStates = {
  Kenkeni: createTrackState(kenkeni_notes, "Kenkeni"),
  Sangban: createTrackState(sangban_notes, "Sangban"),
  Doundoun: createTrackState(doundoun_notes, "Doundoun"),
  Dreierbass: createTrackState(dreierbass_notes, "Dreierbass"),
  Djembe_1: createTrackState(djembe_1_notes, "Djembe_1"),
  Djembe_2: createTrackState(djembe_2_notes, "Djembe_2"),
  Djembe_3: createTrackState(djembe_3_notes, "Djembe_3")
};

const globalBegleitungLength = Math.max(
  getSectionLength(trackStates.Kenkeni.begleitungNotes),
  getSectionLength(trackStates.Sangban.begleitungNotes),
  getSectionLength(trackStates.Doundoun.begleitungNotes),
  getSectionLength(trackStates.Dreierbass.begleitungNotes),
  getSectionLength(trackStates.Djembe_1.begleitungNotes),
  getSectionLength(trackStates.Djembe_2.begleitungNotes),
  getSectionLength(trackStates.Djembe_3.begleitungNotes)
);

let oneShotLength = 0;
let hasOutroSection = false;
let orderedBegleitungSections = [];
let orderedBegleitungLoopLength = 0;
let firstTimelineBegleitungSectionIndex = -1;
let orderedTimelineFallbackLoopSections = [];
let orderedFallbackLoopSections = [];
let orderedFallbackLoopLength = 0;
let timelinePlaybackLength = 0;

function recalculateOrderedSectionTiming() {
  orderedSections.forEach(function (section, sectionIndex) {
    section.length = Math.max.apply(null, trackInstrumentNames.map(function (instrumentName) {
      return getSectionLength(section.trackNotes[instrumentName]);
    }).concat(0));
    section.repeatCount = normalizeSectionRepeatCount(section.repeatCount);
    section.playbackLength = section.length * section.repeatCount;
    section.startStep = sectionIndex === 0 ? 0 : orderedSections[sectionIndex - 1].endStep;
    section.endStep = section.startStep + section.playbackLength;
  });

  oneShotLength = orderedSections.length > 0
    ? orderedSections[orderedSections.length - 1].endStep
    : 0;
  hasOutroSection = orderedSections.some(function (section) {
    return section.label === 'Outro';
  });
  orderedBegleitungSections = orderedSections.filter(isSectionLoopEligible);
  orderedBegleitungLoopLength = orderedBegleitungSections.reduce(function (sum, section) {
    return sum + section.playbackLength;
  }, 0);
  firstTimelineBegleitungSectionIndex = isTimelineMode
    ? orderedSections.findIndex(isSectionLoopEligible)
    : -1;
  orderedTimelineFallbackLoopSections = firstTimelineBegleitungSectionIndex === -1
    ? []
    : orderedSections.slice(firstTimelineBegleitungSectionIndex).filter(isSectionLoopEligible);
  orderedFallbackLoopSections = isTimelineMode && orderedTimelineFallbackLoopSections.length > 0
    ? orderedTimelineFallbackLoopSections
    : orderedBegleitungSections;
  orderedFallbackLoopLength = orderedFallbackLoopSections.reduce(function (sum, section) {
    return sum + section.playbackLength;
  }, 0);
  if (isTimelineMode && orderedFallbackLoopLength <= 0) {
    timelineLoopCount = false;
    timelineLoop = false;
  }
  timelinePlaybackLength = timelineLoopCount && timelineLoopCount !== 'loop' && orderedFallbackLoopLength > 0
    ? oneShotLength + (orderedFallbackLoopLength * Number(timelineLoopCount))
    : 0;
}

recalculateOrderedSectionTiming();
const djembeHandStates = {
  Djembe_1: { nextHand: 'R', leadHand: 'R', lastHand: '', lastSectionKey: '' },
  Djembe_2: { nextHand: 'R', leadHand: 'R', lastHand: '', lastSectionKey: '' },
  Djembe_3: { nextHand: 'R', leadHand: 'R', lastHand: '', lastSectionKey: '' }
};

function warmUpAudioContext(audioCtx) {
  const oscillator = audioCtx.createOscillator();
  const gainNode = audioCtx.createGain();
  gainNode.gain.value = 0.0001;
  oscillator.connect(gainNode).connect(audioCtx.destination);
  oscillator.start(audioCtx.currentTime);
  oscillator.stop(audioCtx.currentTime + 0.08);
}

async function resumeAndWarmAllInstruments() {
  const uniqueAudioContexts = Array.from(new Set(allInstruments.map(function (instrumentInstance) {
    return instrumentInstance._audioCtx;
  })));

  await Promise.all(uniqueAudioContexts.map(function (audioCtx) {
    return audioCtx.resume();
  }));

  uniqueAudioContexts.forEach(function (audioCtx) {
    warmUpAudioContext(audioCtx);
  });

  allInstruments.forEach(function (instrumentInstance) {
    instrumentInstance.warmUpSamples();
  });
}

function buildSwingStepOffsets(profileValues, activeSwingFactor) {
  const swingScale = Math.max(0, Math.min(100, activeSwingFactor)) / 100;
  const anchors = Array.isArray(profileValues) ? profileValues : [];
  if (anchors.length === 0) {
    return [0, 1];
  }

  const anchorStep = 1 / anchors.length;
  const anchorFractions = anchors.map(function (profileValue, profileIndex) {
    return (profileIndex * anchorStep) + ((Number(profileValue) || 0) / 100 * swingScale);
  });
  anchorFractions.push(1 + ((Number(anchors[0]) || 0) / 100 * swingScale));
  const stepOffsets = [];

  for (let segmentIndex = 0; segmentIndex < anchorFractions.length - 1; segmentIndex++) {
    const segmentStart = anchorFractions[segmentIndex];
    const segmentEnd = anchorFractions[segmentIndex + 1];
    stepOffsets.push(segmentStart);
    stepOffsets.push(segmentStart + ((segmentEnd - segmentStart) / 2));
  }

  stepOffsets.push(anchorFractions[anchorFractions.length - 1]);
  return stepOffsets;
}

function getBinaerSwingStepOffsets(activeSwingFactor) {
  const profileValues = Array.isArray(swingProfile.binaer) ? swingProfile.binaer : [6, -5, 6, 10];
  return buildSwingStepOffsets(profileValues, activeSwingFactor);
}

function getTenaerSwingStepOffsets(activeSwingFactor) {
  const profileValues = Array.isArray(swingProfile.tenaer) ? swingProfile.tenaer : [0, -15, -10];
  return buildSwingStepOffsets(profileValues, activeSwingFactor);
}

function getNeunaerSwingStepOffsets(activeSwingFactor) {
  const profileValues = Array.isArray(swingProfile.neunaer) ? swingProfile.neunaer : [0, 15, 10];
  const swingScale = Math.max(0, Math.min(100, activeSwingFactor)) / 100;
  const anchors = [
    0 + ((Number(profileValues[0]) || 0) / 100 * swingScale),
    (1 / 3) + ((Number(profileValues[1]) || 0) / 100 * swingScale),
    (2 / 3) + ((Number(profileValues[2]) || 0) / 100 * swingScale),
    1 + ((Number(profileValues[0]) || 0) / 100 * swingScale)
  ];
  return [
    anchors[0],
    anchors[0] + ((anchors[1] - anchors[0]) / 2),
    anchors[1],
    anchors[1] + ((anchors[2] - anchors[1]) / 2),
    anchors[2],
    anchors[2] + ((anchors[3] - anchors[2]) / 2),
    anchors[3]
  ];
}

function getPlaybackSectionContext(playbackStep) {
  for (let sectionIndex = 0; sectionIndex < orderedSections.length; sectionIndex++) {
    const section = orderedSections[sectionIndex];
    if (playbackStep >= section.startStep && playbackStep < section.endStep) {
      const sectionStep = playbackStep - section.startStep;
      return {
        section: section,
        localStep: section.length > 0 ? sectionStep % section.length : 0,
        sectionStep: sectionStep,
        loopCycleIndex: section.length > 0 ? Math.floor(sectionStep / section.length) : 0
      };
    }
  }

  if (timelineLoopCount && orderedFallbackLoopLength > 0 && playbackStep >= oneShotLength) {
    let loopStep = (playbackStep - oneShotLength) % orderedFallbackLoopLength;
    const loopCycleIndex = Math.floor((playbackStep - oneShotLength) / orderedFallbackLoopLength);
    for (let sectionIndex = 0; sectionIndex < orderedFallbackLoopSections.length; sectionIndex++) {
      const loopSection = orderedFallbackLoopSections[sectionIndex];
      const loopSectionLength = loopSection.playbackLength || loopSection.length || 0;
      if (loopStep < loopSectionLength) {
        const repeatedLocalStep = loopSection.length > 0 ? loopStep % loopSection.length : 0;
        const repeatedCycleIndex = loopSection.length > 0 ? Math.floor(loopStep / loopSection.length) : 0;
        return {
          section: loopSection,
          localStep: repeatedLocalStep,
          sectionStep: loopStep,
          loopCycleIndex: repeatedCycleIndex,
          outerLoopIndex: loopCycleIndex
        };
      }
      loopStep -= loopSectionLength;
    }
  }

  return null;
}

function isFinalTimelinePlaybackContext(sectionContext) {
  if (!sectionContext) {
    return true;
  }

  if (!timelineLoopCount) {
    return true;
  }

  if (timelineLoopCount === 'loop') {
    return practiceTimerFinalLoopStartStep !== null &&
      globalPlaybackStep >= practiceTimerFinalLoopStartStep;
  }

  const loopCount = Number(timelineLoopCount) || 0;
  if (loopCount <= 0) {
    return sectionContext.outerLoopIndex === undefined || sectionContext.outerLoopIndex === null;
  }

  if (sectionContext.outerLoopIndex === undefined || sectionContext.outerLoopIndex === null) {
    return false;
  }

  return Number(sectionContext.outerLoopIndex) >= loopCount - 1;
}

function getStepsPerBeatForRhythm() {
  if (rhythmType === 'tenaer') {
    return 6;
  }
  if (rhythmType === 'neunaer') {
    return 6;
  }
  return 8;
}

function getBaseStepDuration(tempoValue) {
  const safeTempo = Math.max(1, Number(tempoValue) || initialTempo || 100);
  return 60 / safeTempo / getStepsPerBeatForRhythm();
}

function nextNote() {
  const stepDuration = getBaseStepDuration(tempo);
  let intervalToNextStep = stepDuration;
  let activeSwingFactor = swingFactor;
  const sectionContext = getPlaybackSectionContext(globalPlaybackStep);

  if (sectionContext && sectionContext.section.swingFactor !== null && sectionContext.section.swingFactor !== undefined) {
    activeSwingFactor = sectionContext.section.swingFactor;
  }

  if (activeSwingFactor > 0) {
    if (rhythmType === 'binaer') {
      const stepsPerBeat = 8;
      const beatDuration = stepDuration * stepsPerBeat;
      const stepInBeat = globalPlaybackStep % stepsPerBeat;
      const binaerOffsets = getBinaerSwingStepOffsets(activeSwingFactor);
      intervalToNextStep = beatDuration * (binaerOffsets[stepInBeat + 1] - binaerOffsets[stepInBeat]);
    } else if (rhythmType === 'tenaer') {
      const stepsPerBeat = 6;
      const beatDuration = stepDuration * stepsPerBeat;
      const stepInBeat = globalPlaybackStep % stepsPerBeat;
      const tenaerOffsets = getTenaerSwingStepOffsets(activeSwingFactor);
      intervalToNextStep = beatDuration * (tenaerOffsets[stepInBeat + 1] - tenaerOffsets[stepInBeat]);
    } else if (rhythmType === 'neunaer') {
      const stepsPerBeat = 6;
      const beatDuration = stepDuration * stepsPerBeat;
      const stepInBeat = globalPlaybackStep % stepsPerBeat;
      const neunaerOffsets = getNeunaerSwingStepOffsets(activeSwingFactor);
      intervalToNextStep = beatDuration * (neunaerOffsets[stepInBeat + 1] - neunaerOffsets[stepInBeat]);
    } else {
      intervalToNextStep = stepDuration;
    }
  }

  nextNoteTime += Math.max(0.001, intervalToNextStep);
  globalPlaybackStep++;
}

function getAccentMultiplier(playbackStep) {
  const sectionContext = getPlaybackSectionContext(playbackStep);
  if (!sectionContext || !sectionContext.section || sectionContext.section.label !== 'Echauffement') {
    return 1;
  }

  let stepsPerBeat = 0;
  let stepsPerBar = 0;
  if (rhythmType === 'binaer') {
    stepsPerBeat = 8;
    stepsPerBar = 32;
  } else if (rhythmType === 'tenaer') {
    stepsPerBeat = 6;
    stepsPerBar = 24;
  } else if (rhythmType === 'neunaer') {
    stepsPerBeat = 6;
    stepsPerBar = 18;
  }

  if (stepsPerBeat <= 0) {
    return 1;
  }

  if (sectionContext.localStep % stepsPerBeat !== 0) {
    return 1;
  }

  if (stepsPerBar > 0 && sectionContext.localStep % stepsPerBar === 0) {
    return 1.18;
  }

  return 1.14;
}

function scheduleNote(kenkeniNote, sangbanNote, doundounNote, dreierbassNote, djembe1Playback, djembe2Playback, djembe3Playback, time, accentMultiplier) {
  const noteGain = Math.max(0, Number(accentMultiplier) || 1);
  const kenkeniGain = noteGain * getPracticeInstrumentVolume('Kenkeni');
  const sangbanGain = noteGain * getPracticeInstrumentVolume('Sangban');
  const doundounGain = noteGain * getPracticeInstrumentVolume('Doundoun');
  const dreierbassGain = noteGain * getPracticeInstrumentVolume('Dreierbass');
  const djembe1Gain = noteGain * getPracticeInstrumentVolume('Djembe_1');
  const djembe2Gain = noteGain * getPracticeInstrumentVolume('Djembe_2');
  const djembe3Gain = noteGain * getPracticeInstrumentVolume('Djembe_3');
  const kenkeniMuffledGain = kenkeniGain * 1.4;
  const kenkeniKlickGain = kenkeniGain * 0.75;
  const sangbanStrokeGain = sangbanGain * sangbanStrokeGainMultiplier;
  const sangbanMuffledGain = sangbanGain * 1.4 * sangbanStrokeGainMultiplier;
  const sangbanKlickGain = sangbanGain * 0.75 * sangbanStrokeGainMultiplier;
  const sangbanBellGain = sangbanGain * 0.7;
  const doundounMuffledGain = doundounGain * 1.4;
  const doundounKlickGain = doundounGain * 0.75;
  const doundounBellGain = doundounGain * 0.7;
  const kenkeniTime = Math.max(0, time + getFeelOffsetSeconds('Kenkeni'));
  const sangbanTime = Math.max(0, time + getFeelOffsetSeconds('Sangban'));
  const doundounTime = Math.max(0, time + getFeelOffsetSeconds('Doundoun'));
  const dreierbassTime = Math.max(0, time + getFeelOffsetSeconds('Dreierbass'));
  const djembe1Time = Math.max(0, time + getFeelOffsetSeconds('Djembe_1'));
  const djembe2Time = Math.max(0, time + getFeelOffsetSeconds('Djembe_2'));
  const djembe3Time = Math.max(0, time + getFeelOffsetSeconds('Djembe_3'));

  function playKenkeni() {
    switch (kenkeniNote) {
      case "f":
      case null:
      case undefined:
        break;
      case "Bell":
        kenkeni.play('Kenkeni_Bell_Open', kenkeniTime, kenkeniGain);
        break;
      case "Open":
        kenkeni.play('Kenkeni_Open', kenkeniTime, kenkeniGain);
        break;
      case "Muffled":
        kenkeni.play('Kenkeni_Muffled', kenkeniTime, kenkeniMuffledGain);
        break;
      case "Klick":
        kenkeni.play('Kenkeni_Klick', kenkeniTime, kenkeniKlickGain);
        break;
      case "Bell_Open":
        kenkeni.play('Kenkeni_Bell_Open', kenkeniTime, kenkeniGain);
        kenkeni.play('Kenkeni_Open', kenkeniTime, kenkeniGain);
        break;
      case "Bell_Muffled":
        kenkeni.play('Kenkeni_Bell_Open', kenkeniTime, kenkeniGain);
        kenkeni.play('Kenkeni_Muffled', kenkeniTime, kenkeniMuffledGain);
        break;
      case "Bell_Klick":
        kenkeni.play('Kenkeni_Bell_Open', kenkeniTime, kenkeniGain);
        kenkeni.play('Kenkeni_Klick', kenkeniTime, kenkeniKlickGain);
        break;
    }
  }

  function playSangban() {
    switch (sangbanNote) {
      case "f":
      case null:
      case undefined:
        break;
      case "Bell":
        sangban.play('Sangban_Bell_Open', sangbanTime, sangbanBellGain);
        break;
      case "Open":
        sangban.play('Sangban_Open', sangbanTime, sangbanStrokeGain);
        break;
      case "Muffled":
        sangban.play('Sangban_Muffled', sangbanTime, sangbanMuffledGain);
        break;
      case "Klick":
        sangban.play('Sangban_Klick', sangbanTime, sangbanKlickGain);
        break;
      case "Bell_Open":
        sangban.play('Sangban_Bell_Open', sangbanTime, sangbanBellGain);
        sangban.play('Sangban_Open', sangbanTime, sangbanStrokeGain);
        break;
      case "Bell_Muffled":
        sangban.play('Sangban_Bell_Open', sangbanTime, sangbanBellGain);
        sangban.play('Sangban_Muffled', sangbanTime, sangbanMuffledGain);
        break;
      case "Bell_Klick":
        sangban.play('Sangban_Bell_Open', sangbanTime, sangbanBellGain);
        sangban.play('Sangban_Klick', sangbanTime, sangbanKlickGain);
        break;
    }
  }

  function playDoundoun() {
    switch (doundounNote) {
      case "f":
      case null:
      case undefined:
        break;
      case "Bell":
        doundoun.play('Doundoun_Bell_Open', doundounTime, doundounBellGain);
        break;
      case "Open":
        doundoun.play('Doundoun_Open', doundounTime, doundounGain);
        break;
      case "Muffled":
        doundoun.play('Doundoun_Muffled', doundounTime, doundounMuffledGain);
        break;
      case "Klick":
        doundoun.play('Doundoun_Klick', doundounTime, doundounKlickGain);
        break;
      case "Bell_Open":
        doundoun.play('Doundoun_Bell_Open', doundounTime, doundounBellGain);
        doundoun.play('Doundoun_Open', doundounTime, doundounGain);
        break;
      case "Bell_Muffled":
        doundoun.play('Doundoun_Bell_Open', doundounTime, doundounBellGain);
        doundoun.play('Doundoun_Muffled', doundounTime, doundounMuffledGain);
        break;
      case "Bell_Klick":
        doundoun.play('Doundoun_Bell_Open', doundounTime, doundounBellGain);
        doundoun.play('Doundoun_Klick', doundounTime, doundounKlickGain);
        break;
    }
  }

  function playDreierbass() {
    switch (dreierbassNote) {
      case "f":
      case null:
      case undefined:
        break;
      case "sangban":
        dreierbass.play('Sangban_Open', dreierbassTime, dreierbassGain);
        break;
      case "doundoun":
        dreierbass.play('Doundoun_Open', dreierbassTime, dreierbassGain);
        break;
      case "kenkeni":
        dreierbass.play('Kenkeni_Open', dreierbassTime, dreierbassGain);
        break;
      case "kenkeni_muffled":
        dreierbass.play('Kenkeni_Muffled', dreierbassTime, dreierbassGain);
        break;
      case "sangban_muffled":
        dreierbass.play('Sangban_Muffled', dreierbassTime, dreierbassGain);
        break;
      case "kenkeni_sangban":
        dreierbass.play('Kenkeni_Open', dreierbassTime, dreierbassGain);
        dreierbass.play('Sangban_Open', dreierbassTime + 0.5 / tempo, dreierbassGain);
        break;
      case "kenkeni_doundoun":
        dreierbass.play('Kenkeni_Open', dreierbassTime, dreierbassGain);
        dreierbass.play('Doundoun_Open', dreierbassTime + 0.5 / tempo, dreierbassGain);
        break;
      case "sangban_doundoun":
        dreierbass.play('Sangban_Open', dreierbassTime, dreierbassGain);
        dreierbass.play('Doundoun_Open', dreierbassTime + 0.5 / tempo, dreierbassGain);
        break;
      case "kenkeni_sangban_muffled":
        dreierbass.play('Kenkeni_Open', dreierbassTime, dreierbassGain);
        dreierbass.play('Sangban_Muffled', dreierbassTime + 0.5 / tempo, dreierbassGain);
        break;
      case "kenkeni_muffled_sangban":
        dreierbass.play('Kenkeni_Muffled', dreierbassTime, dreierbassGain);
        dreierbass.play('Sangban_Open', dreierbassTime + 0.5 / tempo, dreierbassGain);
        break;
      case "kenkeni_muffled_doundoun":
        dreierbass.play('Kenkeni_Muffled', dreierbassTime, dreierbassGain);
        dreierbass.play('Doundoun_Open', dreierbassTime + 1 / tempo, dreierbassGain);
        break;
      case "sangban_muffled_doundoun":
        dreierbass.play('Sangban_Muffled', dreierbassTime, dreierbassGain);
        dreierbass.play('Doundoun_Open', dreierbassTime + 1 / tempo, dreierbassGain);
        break;
    }
  }

  function playDjembe_1() {
    const djembe1Note = djembe1Playback ? djembe1Playback.note : null;
    switch (djembe1Note) {
      case "f":
      case null:
      case undefined:
        break;
      case "tone":
        playDjembeStroke(djembe_1, 'DjembeOne_Open', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "bass":
        playDjembeStroke(djembe_1, 'DjembeOne_Bass', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "slap":
        playDjembeStroke(djembe_1, 'DjembeOne_Slap', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "tone_muffled":
        playDjembeStroke(djembe_1, 'DjembeOne_Muffled_Open', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "slap_muffled":
        playDjembeStroke(djembe_1, 'DjembeOne_Muffled_Slap', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "tone_flam":
        playDjembeStroke(djembe_1, 'DjembeOne_Open', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain, { isGrace: true });
        playDjembeStroke(djembe_1, 'DjembeOne_Open', djembe1Time + 5 / tempo, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "slap_flam":
        playDjembeStroke(djembe_1, 'DjembeOne_Slap', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain, { isGrace: true });
        playDjembeStroke(djembe_1, 'DjembeOne_Slap', djembe1Time + 5 / tempo, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
      case "bass_slap_flam":
        playDjembeStroke(djembe_1, 'DjembeOne_Bass', djembe1Time, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain, { isGrace: true });
        playDjembeStroke(djembe_1, 'DjembeOne_Slap', djembe1Time + 5 / tempo, djembe1Playback, djembeHandStates.Djembe_1, djembe1Gain);
        break;
    }
  }

  function playDjembe_2() {
    const djembe2Note = djembe2Playback ? djembe2Playback.note : null;
    switch (djembe2Note) {
      case "f":
      case null:
      case undefined:
        break;
      case "tone":
        playDjembeStroke(djembe_2, 'DjembeTwo_Open', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "tone_muffled":
        playDjembeStroke(djembe_2, 'DjembeTwo_Muffled_Open', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "bass":
        playDjembeStroke(djembe_2, 'DjembeTwo_Bass', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "slap":
        playDjembeStroke(djembe_2, 'DjembeTwo_Slap', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "slap_muffled":
        playDjembeStroke(djembe_2, 'DjembeTwo_Muffled_Slap', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "tone_flam":
        playDjembeStroke(djembe_2, 'DjembeTwo_Open', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain, { isGrace: true });
        playDjembeStroke(djembe_2, 'DjembeTwo_Open', djembe2Time + 5 / tempo, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "slap_flam":
        playDjembeStroke(djembe_2, 'DjembeTwo_Slap', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain, { isGrace: true });
        playDjembeStroke(djembe_2, 'DjembeTwo_Slap', djembe2Time + 5 / tempo, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
      case "bass_slap_flam":
        playDjembeStroke(djembe_2, 'DjembeTwo_Bass', djembe2Time, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain, { isGrace: true });
        playDjembeStroke(djembe_2, 'DjembeTwo_Slap', djembe2Time + 5 / tempo, djembe2Playback, djembeHandStates.Djembe_2, djembe2Gain);
        break;
    }
  }

  function playDjembe_3() {
    const djembe3Note = djembe3Playback ? djembe3Playback.note : null;
    switch (djembe3Note) {
      case "f":
      case null:
      case undefined:
        break;
      case "tone":
        playDjembeStroke(djembe_3, 'DjembeThree_Open', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
      case "bass":
        playDjembeStroke(djembe_3, 'DjembeThree_Bass', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
      case "slap":
        playDjembeStroke(djembe_3, 'DjembeThree_Slap', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
      case "slap_muffled":
        playDjembeStroke(djembe_3, 'DjembeThree_Mute', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
      case "tone_flam":
        playDjembeStroke(djembe_3, 'DjembeThree_Open', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain, { isGrace: true });
        playDjembeStroke(djembe_3, 'DjembeThree_Open', djembe3Time + 5 / tempo, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
      case "slap_flam":
        playDjembeStroke(djembe_3, 'DjembeThree_Slap', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain, { isGrace: true });
        playDjembeStroke(djembe_3, 'DjembeThree_Slap', djembe3Time + 5 / tempo, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
      case "bass_slap_flam":
        playDjembeStroke(djembe_3, 'DjembeThree_Bass', djembe3Time, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain, { isGrace: true });
        playDjembeStroke(djembe_3, 'DjembeThree_Slap', djembe3Time + 5 / tempo, djembe3Playback, djembeHandStates.Djembe_3, djembe3Gain);
        break;
    }
  }

  playKenkeni();
  playSangban();
  playDoundoun();
  playDreierbass();
  playDjembe_1();
  playDjembe_2();
  playDjembe_3();
}

if (dreierbass_notes.length > Math.max(djembe_1_notes.length, djembe_2_notes.length, djembe_3_notes.length)) {
  instr = dreierbass;
} else if (djembe_1_notes.length > Math.max(dreierbass_notes.length, djembe_2_notes.length, djembe_3_notes.length)) {
  instr = djembe_1;
} else if (djembe_2_notes.length > Math.max(dreierbass_notes.length, djembe_1_notes.length, djembe_3_notes.length)) {
  instr = djembe_2;
} else if (djembe_3_notes.length > Math.max(dreierbass_notes.length, djembe_1_notes.length, djembe_2_notes.length)) {
  instr = djembe_3;
} else {
  instr = djembe_1;
}

window.masterAudioContext = instr._audioCtx;

function getTrackPlaybackAtStep(trackName, trackState, playbackStep) {
  const sectionContext = getPlaybackSectionContext(playbackStep);
  if (sectionContext) {
    const finalRepeatOutStep = sectionContext.section.finalRepeatOutSteps
      ? normalizeSectionOutStep(sectionContext.section.finalRepeatOutSteps[trackName])
      : null;
    const isPracticeTargetTrack = Array.isArray(sectionContext.section.practiceTargetInstruments) &&
      sectionContext.section.practiceTargetInstruments.indexOf(trackName) !== -1;
    const isFinalSectionRepeat = sectionContext.loopCycleIndex === sectionContext.section.repeatCount - 1;
    const isFinalPlaybackContext = isFinalTimelinePlaybackContext(sectionContext);
    const forceFinalOutAtSectionEnd = Boolean(sectionContext.section.forceFinalOutAtSectionEnd);
    const shouldMuteForOut = isFinalSectionRepeat &&
      (isPracticeTargetTrack || isFinalPlaybackContext || forceFinalOutAtSectionEnd) &&
      finalRepeatOutStep !== null &&
      sectionContext.localStep > finalRepeatOutStep;
    const outerLoopIndex = Number(sectionContext.outerLoopIndex) || 0;
    const sectionKeySuffix = outerLoopIndex
      ? ':timeline-loop:' + outerLoopIndex + ':section-repeat:' + sectionContext.loopCycleIndex
      : (sectionContext.loopCycleIndex ? ':section-repeat:' + sectionContext.loopCycleIndex : '');
    return {
      note: shouldMuteForOut
        ? null
        : (sectionContext.section.trackNotes[trackName][sectionContext.localStep] || null),
      handMode: sectionContext.section.trackHandModes
        ? (sectionContext.section.trackHandModes[trackName] || '')
        : '',
      sectionKey: (sectionContext.section.runtimeKey || '') + sectionKeySuffix,
      stepIndex: sectionContext.localStep
    };
  }

  if (isTimelineMode && oneShotLength > 0 && playbackStep >= oneShotLength) {
    return {
      note: null,
      handMode: '',
      sectionKey: '',
      stepIndex: 0
    };
  }

  if (hasOutroSection) {
    return {
      note: null,
      handMode: '',
      sectionKey: '',
      stepIndex: 0
    };
  }

  const begleitungLength = getSectionLength(trackState.begleitungNotes);
  if (begleitungLength > 0) {
    const loopStep = playbackStep - oneShotLength;
    if (orderedFallbackLoopSections.length > 0 && orderedFallbackLoopLength > 0) {
      const normalizedLoopStep = ((loopStep % orderedFallbackLoopLength) + orderedFallbackLoopLength) % orderedFallbackLoopLength;
      const loopCycleIndex = Math.floor(loopStep / orderedFallbackLoopLength);
      let sectionOffset = 0;

      for (let sectionIndex = 0; sectionIndex < orderedFallbackLoopSections.length; sectionIndex++) {
        const section = orderedFallbackLoopSections[sectionIndex];
        const sectionPlaybackLength = section.playbackLength || section.length || 0;
        if (sectionPlaybackLength <= 0) {
          continue;
        }
        if (normalizedLoopStep < sectionOffset + sectionPlaybackLength) {
          const sectionStep = normalizedLoopStep - sectionOffset;
          const localStep = section.length > 0 ? sectionStep % section.length : 0;
          const repeatedCycleIndex = section.length > 0 ? Math.floor(sectionStep / section.length) : 0;
          return {
            note: section.trackNotes[trackName][localStep] || null,
            handMode: section.trackHandModes
              ? (section.trackHandModes[trackName] || '')
              : '',
            sectionKey: (section.runtimeKey || 'begleitung-loop') + ':loop:' + (loopCycleIndex + repeatedCycleIndex),
            stepIndex: localStep
          };
        }
        sectionOffset += sectionPlaybackLength;
      }
    }

    return {
      note: trackState.begleitungNotes[loopStep % begleitungLength],
      handMode: '',
      sectionKey: 'begleitung-loop',
      stepIndex: loopStep % begleitungLength
    };
  }

  return {
    note: null,
    handMode: '',
    sectionKey: '',
    stepIndex: 0
  };
}

function getOppositeHand(handName) {
  return handName === 'L' ? 'R' : 'L';
}

function resetHandStateForSection(handState, sectionKey) {
  if (handState.lastSectionKey !== sectionKey) {
    handState.lastSectionKey = sectionKey;
    handState.nextHand = 'R';
    handState.leadHand = 'R';
    handState.lastHand = '';
  }
}

function isHoHAccentNote(noteValue) {
  return noteValue === 'slap' ||
    noteValue === 'slap_flam' ||
    noteValue === 'slap_muffled' ||
    noteValue === 'bass_slap_flam';
}

function getHandPulseStride() {
  if (rhythmType === 'neunaer') {
    return 1;
  }
  return 2;
}

function isSingleHandDjembeSample(baseSampleName) {
  return baseSampleName === 'DjembeOne_Muffled_Open' ||
    baseSampleName === 'DjembeOne_Muffled_Slap' ||
    baseSampleName === 'DjembeTwo_Muffled_Open' ||
    baseSampleName === 'DjembeTwo_Muffled_Slap' ||
    baseSampleName === 'DjembeThree_Muffled_Open' ||
    baseSampleName === 'DjembeThree_Muffled_Slap';
}

function getH2HRestMuteSampleName(trackName) {
  if (trackName === 'Djembe_1') {
    return 'DjembeOne_Mute';
  }
  if (trackName === 'Djembe_2') {
    return 'DjembeTwo_Mute';
  }
  if (trackName === 'Djembe_3') {
    return 'DjembeThree_Mute';
  }
  return '';
}

function playH2HRestMuteSample(restConfig, selectedHand) {
  if (!restConfig || !restConfig.baseSampleName) {
    return;
  }
  const sampleName = selectedHand === 'L' && !isSingleHandDjembeSample(restConfig.baseSampleName)
    ? restConfig.baseSampleName + 'L'
    : restConfig.baseSampleName;

  if (restConfig.audioContext && restConfig.destinationNode) {
    playSampleToDestination(
      restConfig.instrumentInstance,
      sampleName,
      restConfig.time,
      restConfig.gainMultiplier,
      restConfig.audioContext,
      restConfig.destinationNode
    );
    return;
  }

  if (restConfig.instrumentInstance && typeof restConfig.instrumentInstance.play === 'function') {
    restConfig.instrumentInstance.play(sampleName, restConfig.time, restConfig.gainMultiplier);
  }
}

function advanceSilentH2HStep(playbackContext, handState, restConfig) {
  const noteHandMode = playbackContext && playbackContext.handMode ? playbackContext.handMode : '';
  const noteValue = playbackContext && playbackContext.note ? String(playbackContext.note) : '';
  if (noteHandMode !== 'h2h' || (noteValue && noteValue !== 'f')) {
    return;
  }

  const stepIndex = playbackContext && Number.isFinite(Number(playbackContext.stepIndex))
    ? Number(playbackContext.stepIndex)
    : 0;
  if (stepIndex % getHandPulseStride() !== 0) {
    return;
  }

  const sectionKey = playbackContext && playbackContext.sectionKey ? playbackContext.sectionKey : '';
  resetHandStateForSection(handState, sectionKey);
  const consumedHand = handState.nextHand === 'L' ? 'L' : 'R';
  handState.lastHand = consumedHand;
  handState.nextHand = getOppositeHand(consumedHand);
  if (practiceH2HRestMute) {
    playH2HRestMuteSample(restConfig, consumedHand);
  }
}

function resolveDjembeSampleName(baseSampleName, playbackContext, handState, strokeMeta) {
  const noteHandMode = playbackContext && playbackContext.handMode ? playbackContext.handMode : '';
  if (!noteHandMode || noteHandMode === 'auto') {
    return baseSampleName;
  }

  const sectionKey = playbackContext && playbackContext.sectionKey ? playbackContext.sectionKey : '';
  resetHandStateForSection(handState, sectionKey);

  if (noteHandMode === 'h2h') {
    const selectedHand = handState.nextHand === 'L' ? 'L' : 'R';
    handState.nextHand = getOppositeHand(selectedHand);
    handState.lastHand = selectedHand;
    return selectedHand === 'L' && !isSingleHandDjembeSample(baseSampleName)
      ? baseSampleName + 'L'
      : baseSampleName;
  }

  if (noteHandMode === 'hoh') {
    const noteValue = playbackContext && playbackContext.note ? String(playbackContext.note) : '';
    const leadHand = handState.leadHand || 'R';
    const supportHand = getOppositeHand(leadHand);
    const isGraceStroke = Boolean(strokeMeta && strokeMeta.isGrace);
    let selectedHand = 'R';

    if (isGraceStroke) {
      selectedHand = supportHand;
    } else if (isHoHAccentNote(noteValue)) {
      selectedHand = leadHand;
      handState.leadHand = getOppositeHand(leadHand);
    } else {
      selectedHand = handState.lastHand === supportHand ? leadHand : supportHand;
    }

    handState.nextHand = getOppositeHand(selectedHand);
    handState.lastHand = selectedHand;
    return selectedHand === 'L' && !isSingleHandDjembeSample(baseSampleName)
      ? baseSampleName + 'L'
      : baseSampleName;
  }

  return baseSampleName;
}

function playDjembeStroke(instrumentInstance, baseSampleName, time, playbackContext, handState, gainMultiplier, strokeMeta) {
  const sampleName = resolveDjembeSampleName(baseSampleName, playbackContext, handState, strokeMeta);
  instrumentInstance.play(sampleName, time, gainMultiplier);
}

function getPracticeInstrumentVolumeForInstance(instrumentInstance) {
  if (instrumentInstance === kenkeni) {
    return getPracticeInstrumentVolume('Kenkeni');
  }
  if (instrumentInstance === sangban) {
    return getPracticeInstrumentVolume('Sangban');
  }
  if (instrumentInstance === doundoun) {
    return getPracticeInstrumentVolume('Doundoun');
  }
  if (instrumentInstance === dreierbass) {
    return getPracticeInstrumentVolume('Dreierbass');
  }
  if (instrumentInstance === djembe_1) {
    return getPracticeInstrumentVolume('Djembe_1');
  }
  if (instrumentInstance === djembe_2) {
    return getPracticeInstrumentVolume('Djembe_2');
  }
  if (instrumentInstance === djembe_3) {
    return getPracticeInstrumentVolume('Djembe_3');
  }
  return 1;
}

function playSampleToDestination(instrumentInstance, sampleName, time, gainMultiplier, audioContext, destinationNode) {
  if (!instrumentInstance || !instrumentInstance._snd || !instrumentInstance._snd[sampleName]) {
    return;
  }

  const sampleSource = audioContext.createBufferSource();
  const gainNode = audioContext.createGain();
  const numericGain = Number(gainMultiplier);
  sampleSource.buffer = instrumentInstance._snd[sampleName];
  gainNode.gain.value = instrumentInstance._vol *
    getPracticeInstrumentVolumeForInstance(instrumentInstance) *
    Math.max(0, Number.isFinite(numericGain) ? numericGain : 1);
  if (typeof audioContext.createStereoPanner === 'function') {
    const panNode = audioContext.createStereoPanner();
    panNode.pan.value = Math.max(-1, Math.min(1, Number(instrumentInstance._pan) || 0));
    sampleSource.connect(gainNode).connect(panNode).connect(destinationNode);
  } else {
    sampleSource.connect(gainNode).connect(destinationNode);
  }
  sampleSource.start(Math.max(0, time));
}

function playDjembeStrokeToDestination(instrumentInstance, baseSampleName, time, playbackContext, handState, gainMultiplier, audioContext, destinationNode, strokeMeta) {
  const sampleName = resolveDjembeSampleName(baseSampleName, playbackContext, handState, strokeMeta);
  playSampleToDestination(instrumentInstance, sampleName, time, gainMultiplier, audioContext, destinationNode);
}

function shouldStopForPracticeTimer(scheduledTime) {
  if (practiceStopAudioTime <= 0 || scheduledTime < practiceStopAudioTime) {
    return false;
  }

  if (orderedFallbackLoopLength <= 0) {
    return true;
  }

  if (globalPlaybackStep < oneShotLength) {
    return globalPlaybackStep >= oneShotLength;
  }

  practiceTimerFinalLoopStartStep = oneShotLength +
    Math.floor((globalPlaybackStep - oneShotLength) / orderedFallbackLoopLength) * orderedFallbackLoopLength;

  return globalPlaybackStep === practiceTimerFinalLoopStartStep;
}

function markPracticeTimerFinalLoopIfNeeded(scheduledTime) {
  if (practiceStopAudioTime <= 0 ||
      practiceTimerFinalLoopStartStep !== null ||
      orderedFallbackLoopLength <= 0 ||
      globalPlaybackStep < oneShotLength) {
    return;
  }

  const loopOffset = (globalPlaybackStep - oneShotLength) % orderedFallbackLoopLength;
  const loopStartStep = globalPlaybackStep - loopOffset;
  const loopEndStep = loopStartStep + orderedFallbackLoopLength;
  let loopEndTime = scheduledTime;

  for (let stepIndex = globalPlaybackStep; stepIndex < loopEndStep; stepIndex++) {
    loopEndTime += getStepInterval(stepIndex, tempo);
    if (loopEndTime >= practiceStopAudioTime) {
      practiceTimerFinalLoopStartStep = loopStartStep;
      return;
    }
  }
}

function shouldStopTimelineAtOneShotEnd() {
  if (!isTimelineMode || oneShotLength <= 0 || globalPlaybackStep < oneShotLength) {
    return false;
  }

  if (!timelineLoopCount) {
    return true;
  }

  return orderedFallbackLoopLength <= 0;
}

function scheduler() {
  const dTime = instr._audioCtx.currentTime;

  while (nextNoteTime < dTime + scheduleAheadTime) {
    applyPendingPracticeSectionsIfReady();
    markPracticeTimerFinalLoopIfNeeded(nextNoteTime);

    if (shouldStopForPracticeTimer(nextNoteTime)) {
      isPlaying = false;
      playButton.dataset.playing = 'false';
      timerID = null;
      practiceStopAudioTime = 0;
      practiceTimerFinalLoopStartStep = null;
      notifyEmbeddedPlaybackState('ended');
      return;
    }
    if (timelineLoopCount && timelineLoopCount !== 'loop' && timelinePlaybackLength > 0 && globalPlaybackStep >= timelinePlaybackLength) {
      isPlaying = false;
      playButton.dataset.playing = 'false';
      timerID = null;
      practiceStopAudioTime = 0;
      practiceTimerFinalLoopStartStep = null;
      notifyEmbeddedPlaybackState('ended');
      return;
    }
    if (shouldStopTimelineAtOneShotEnd()) {
      isPlaying = false;
      playButton.dataset.playing = 'false';
      timerID = null;
      practiceStopAudioTime = 0;
      practiceTimerFinalLoopStartStep = null;
      notifyEmbeddedPlaybackState('ended');
      return;
    }
    if (!timelineLoopCount && !isTimelineMode && hasOutroSection && globalPlaybackStep >= oneShotLength) {
      isPlaying = false;
      playButton.dataset.playing = 'false';
      timerID = null;
      practiceStopAudioTime = 0;
      practiceTimerFinalLoopStartStep = null;
      notifyEmbeddedPlaybackState('ended');
      return;
    }
    scheduleCurrentStep(nextNoteTime);
    nextNote();
  }

  timerID = window.setTimeout(scheduler, lookahead);
}

function scheduleCurrentStep(time) {
  notifyEmbeddedPlaybackStep(globalPlaybackStep, time);

  function maybeTrackBeat(trackName, trackState) {
    if (soloTrackName && soloTrackName !== trackName) {
      return null;
    }
    return getTrackPlaybackAtStep(trackName, trackState, globalPlaybackStep);
  }

  const kenkeniPlayback = maybeTrackBeat('Kenkeni', trackStates.Kenkeni);
  const sangbanPlayback = maybeTrackBeat('Sangban', trackStates.Sangban);
  const doundounPlayback = maybeTrackBeat('Doundoun', trackStates.Doundoun);
  const dreierbassPlayback = maybeTrackBeat('Dreierbass', trackStates.Dreierbass);
  const djembe1Playback = maybeTrackBeat('Djembe_1', trackStates.Djembe_1);
  const djembe2Playback = maybeTrackBeat('Djembe_2', trackStates.Djembe_2);
  const djembe3Playback = maybeTrackBeat('Djembe_3', trackStates.Djembe_3);
  const accentMultiplier = getAccentMultiplier(globalPlaybackStep);

  advanceSilentH2HStep(djembe1Playback, djembeHandStates.Djembe_1, {
    instrumentInstance: djembe_1,
    baseSampleName: getH2HRestMuteSampleName('Djembe_1'),
    time: Math.max(0, time + getFeelOffsetSeconds('Djembe_1')),
    gainMultiplier: accentMultiplier * h2hRestMuteGainMultipliers.Djembe_1 * getPracticeInstrumentVolume('Djembe_1')
  });
  advanceSilentH2HStep(djembe2Playback, djembeHandStates.Djembe_2, {
    instrumentInstance: djembe_2,
    baseSampleName: getH2HRestMuteSampleName('Djembe_2'),
    time: Math.max(0, time + getFeelOffsetSeconds('Djembe_2')),
    gainMultiplier: accentMultiplier * h2hRestMuteGainMultipliers.Djembe_2 * getPracticeInstrumentVolume('Djembe_2')
  });
  advanceSilentH2HStep(djembe3Playback, djembeHandStates.Djembe_3, {
    instrumentInstance: djembe_3,
    baseSampleName: getH2HRestMuteSampleName('Djembe_3'),
    time: Math.max(0, time + getFeelOffsetSeconds('Djembe_3')),
    gainMultiplier: accentMultiplier * h2hRestMuteGainMultipliers.Djembe_3 * getPracticeInstrumentVolume('Djembe_3')
  });

  scheduleNote(
    kenkeniPlayback ? kenkeniPlayback.note : null,
    sangbanPlayback ? sangbanPlayback.note : null,
    doundounPlayback ? doundounPlayback.note : null,
    dreierbassPlayback ? dreierbassPlayback.note : null,
    djembe1Playback,
    djembe2Playback,
    djembe3Playback,
    time,
    accentMultiplier
  );
}

function getStepInterval(playbackStep, tempoValue) {
  const stepDuration = getBaseStepDuration(tempoValue);
  let intervalToNextStep = stepDuration;
  let activeSwingFactor = swingFactor;
  const sectionContext = getPlaybackSectionContext(playbackStep);

  if (sectionContext && sectionContext.section.swingFactor !== null && sectionContext.section.swingFactor !== undefined) {
    activeSwingFactor = sectionContext.section.swingFactor;
  }

  if (activeSwingFactor > 0) {
    if (rhythmType === 'binaer') {
      const stepsPerBeat = 8;
      const beatDuration = stepDuration * stepsPerBeat;
      const stepInBeat = playbackStep % stepsPerBeat;
      const binaerOffsets = getBinaerSwingStepOffsets(activeSwingFactor);
      intervalToNextStep = beatDuration * (binaerOffsets[stepInBeat + 1] - binaerOffsets[stepInBeat]);
    } else if (rhythmType === 'tenaer') {
      const stepsPerBeat = 6;
      const beatDuration = stepDuration * stepsPerBeat;
      const stepInBeat = playbackStep % stepsPerBeat;
      const tenaerOffsets = getTenaerSwingStepOffsets(activeSwingFactor);
      intervalToNextStep = beatDuration * (tenaerOffsets[stepInBeat + 1] - tenaerOffsets[stepInBeat]);
    } else if (rhythmType === 'neunaer') {
      const stepsPerBeat = 6;
      const beatDuration = stepDuration * stepsPerBeat;
      const stepInBeat = playbackStep % stepsPerBeat;
      const neunaerOffsets = getNeunaerSwingStepOffsets(activeSwingFactor);
      intervalToNextStep = beatDuration * (neunaerOffsets[stepInBeat + 1] - neunaerOffsets[stepInBeat]);
    }
  }

  return Math.max(0.001, intervalToNextStep);
}

function getExportStepCount() {
  if (practiceDurationSeconds > 0) {
    let exportDuration = 0;
    let exportStep = 0;
    const maxExportSteps = 200000;
    while (exportDuration < practiceDurationSeconds && exportStep < maxExportSteps) {
      exportDuration += getStepInterval(exportStep, tempo);
      exportStep += 1;
    }
    return Math.max(1, exportStep);
  }

  if (timelineLoopCount && timelineLoopCount !== 'loop' && timelinePlaybackLength > 0) {
    return timelinePlaybackLength;
  }

  if (timelineLoop && orderedFallbackLoopLength > 0) {
    return oneShotLength + orderedFallbackLoopLength;
  }

  if (isTimelineMode) {
    return oneShotLength;
  }

  if (hasOutroSection) {
    return oneShotLength;
  }

  const loopLength = orderedFallbackLoopLength > 0 ? orderedFallbackLoopLength : globalBegleitungLength;
  if (loopLength > 0) {
    return oneShotLength + loopLength;
  }

  return oneShotLength;
}

function scheduleNoteToDestination(kenkeniNote, sangbanNote, doundounNote, dreierbassNote, djembe1Playback, djembe2Playback, djembe3Playback, time, accentMultiplier, audioContext, destinationNode, exportHandStates, exportTempo) {
  const noteGain = Math.max(0, Number(accentMultiplier) || 1);
  const bassMuffledGain = noteGain * 1.4;
  const klickGain = noteGain * 0.75;
  const sangbanStrokeGain = noteGain * sangbanStrokeGainMultiplier;
  const sangbanMuffledGain = bassMuffledGain * sangbanStrokeGainMultiplier;
  const sangbanKlickGain = klickGain * sangbanStrokeGainMultiplier;
  const sangbanBellGain = noteGain * 0.7;
  const doundounBellGain = noteGain * 0.7;
  const kenkeniTime = Math.max(0, time + getFeelOffsetSeconds('Kenkeni'));
  const sangbanTime = Math.max(0, time + getFeelOffsetSeconds('Sangban'));
  const doundounTime = Math.max(0, time + getFeelOffsetSeconds('Doundoun'));
  const dreierbassTime = Math.max(0, time + getFeelOffsetSeconds('Dreierbass'));
  const djembeTimes = {
    Djembe_1: Math.max(0, time + getFeelOffsetSeconds('Djembe_1')),
    Djembe_2: Math.max(0, time + getFeelOffsetSeconds('Djembe_2')),
    Djembe_3: Math.max(0, time + getFeelOffsetSeconds('Djembe_3'))
  };

  switch (kenkeniNote) {
    case "Bell":
      playSampleToDestination(kenkeni, 'Kenkeni_Bell_Open', kenkeniTime, noteGain, audioContext, destinationNode);
      break;
    case "Open":
      playSampleToDestination(kenkeni, 'Kenkeni_Open', kenkeniTime, noteGain, audioContext, destinationNode);
      break;
    case "Muffled":
      playSampleToDestination(kenkeni, 'Kenkeni_Muffled', kenkeniTime, bassMuffledGain, audioContext, destinationNode);
      break;
    case "Klick":
      playSampleToDestination(kenkeni, 'Kenkeni_Klick', kenkeniTime, klickGain, audioContext, destinationNode);
      break;
    case "Bell_Open":
      playSampleToDestination(kenkeni, 'Kenkeni_Bell_Open', kenkeniTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(kenkeni, 'Kenkeni_Open', kenkeniTime, noteGain, audioContext, destinationNode);
      break;
    case "Bell_Muffled":
      playSampleToDestination(kenkeni, 'Kenkeni_Bell_Open', kenkeniTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(kenkeni, 'Kenkeni_Muffled', kenkeniTime, bassMuffledGain, audioContext, destinationNode);
      break;
    case "Bell_Klick":
      playSampleToDestination(kenkeni, 'Kenkeni_Bell_Open', kenkeniTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(kenkeni, 'Kenkeni_Klick', kenkeniTime, klickGain, audioContext, destinationNode);
      break;
  }

  switch (sangbanNote) {
    case "Bell":
      playSampleToDestination(sangban, 'Sangban_Bell_Open', sangbanTime, sangbanBellGain, audioContext, destinationNode);
      break;
    case "Open":
      playSampleToDestination(sangban, 'Sangban_Open', sangbanTime, sangbanStrokeGain, audioContext, destinationNode);
      break;
    case "Muffled":
      playSampleToDestination(sangban, 'Sangban_Muffled', sangbanTime, sangbanMuffledGain, audioContext, destinationNode);
      break;
    case "Klick":
      playSampleToDestination(sangban, 'Sangban_Klick', sangbanTime, sangbanKlickGain, audioContext, destinationNode);
      break;
    case "Bell_Open":
      playSampleToDestination(sangban, 'Sangban_Bell_Open', sangbanTime, sangbanBellGain, audioContext, destinationNode);
      playSampleToDestination(sangban, 'Sangban_Open', sangbanTime, sangbanStrokeGain, audioContext, destinationNode);
      break;
    case "Bell_Muffled":
      playSampleToDestination(sangban, 'Sangban_Bell_Open', sangbanTime, sangbanBellGain, audioContext, destinationNode);
      playSampleToDestination(sangban, 'Sangban_Muffled', sangbanTime, sangbanMuffledGain, audioContext, destinationNode);
      break;
    case "Bell_Klick":
      playSampleToDestination(sangban, 'Sangban_Bell_Open', sangbanTime, sangbanBellGain, audioContext, destinationNode);
      playSampleToDestination(sangban, 'Sangban_Klick', sangbanTime, sangbanKlickGain, audioContext, destinationNode);
      break;
  }

  switch (doundounNote) {
    case "Bell":
      playSampleToDestination(doundoun, 'Doundoun_Bell_Open', doundounTime, doundounBellGain, audioContext, destinationNode);
      break;
    case "Open":
      playSampleToDestination(doundoun, 'Doundoun_Open', doundounTime, noteGain, audioContext, destinationNode);
      break;
    case "Muffled":
      playSampleToDestination(doundoun, 'Doundoun_Muffled', doundounTime, bassMuffledGain, audioContext, destinationNode);
      break;
    case "Klick":
      playSampleToDestination(doundoun, 'Doundoun_Klick', doundounTime, klickGain, audioContext, destinationNode);
      break;
    case "Bell_Open":
      playSampleToDestination(doundoun, 'Doundoun_Bell_Open', doundounTime, doundounBellGain, audioContext, destinationNode);
      playSampleToDestination(doundoun, 'Doundoun_Open', doundounTime, noteGain, audioContext, destinationNode);
      break;
    case "Bell_Muffled":
      playSampleToDestination(doundoun, 'Doundoun_Bell_Open', doundounTime, doundounBellGain, audioContext, destinationNode);
      playSampleToDestination(doundoun, 'Doundoun_Muffled', doundounTime, bassMuffledGain, audioContext, destinationNode);
      break;
    case "Bell_Klick":
      playSampleToDestination(doundoun, 'Doundoun_Bell_Open', doundounTime, doundounBellGain, audioContext, destinationNode);
      playSampleToDestination(doundoun, 'Doundoun_Klick', doundounTime, klickGain, audioContext, destinationNode);
      break;
  }

  switch (dreierbassNote) {
    case "sangban":
      playSampleToDestination(dreierbass, 'Sangban_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      break;
    case "doundoun":
      playSampleToDestination(dreierbass, 'Doundoun_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni":
      playSampleToDestination(dreierbass, 'Kenkeni_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni_muffled":
      playSampleToDestination(dreierbass, 'Kenkeni_Muffled', dreierbassTime, noteGain, audioContext, destinationNode);
      break;
    case "sangban_muffled":
      playSampleToDestination(dreierbass, 'Sangban_Muffled', dreierbassTime, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni_sangban":
      playSampleToDestination(dreierbass, 'Kenkeni_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Sangban_Open', dreierbassTime + 0.5 / exportTempo, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni_doundoun":
      playSampleToDestination(dreierbass, 'Kenkeni_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Doundoun_Open', dreierbassTime + 0.5 / exportTempo, noteGain, audioContext, destinationNode);
      break;
    case "sangban_doundoun":
      playSampleToDestination(dreierbass, 'Sangban_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Doundoun_Open', dreierbassTime + 0.5 / exportTempo, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni_sangban_muffled":
      playSampleToDestination(dreierbass, 'Kenkeni_Open', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Sangban_Muffled', dreierbassTime + 0.5 / exportTempo, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni_muffled_sangban":
      playSampleToDestination(dreierbass, 'Kenkeni_Muffled', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Sangban_Open', dreierbassTime + 0.5 / exportTempo, noteGain, audioContext, destinationNode);
      break;
    case "kenkeni_muffled_doundoun":
      playSampleToDestination(dreierbass, 'Kenkeni_Muffled', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Doundoun_Open', dreierbassTime + 1 / exportTempo, noteGain, audioContext, destinationNode);
      break;
    case "sangban_muffled_doundoun":
      playSampleToDestination(dreierbass, 'Sangban_Muffled', dreierbassTime, noteGain, audioContext, destinationNode);
      playSampleToDestination(dreierbass, 'Doundoun_Open', dreierbassTime + 1 / exportTempo, noteGain, audioContext, destinationNode);
      break;
  }

  const djembeNotes = [
    { trackName: 'Djembe_1', playback: djembe1Playback, instrument: djembe_1, baseNames: { tone: 'DjembeOne_Open', bass: 'DjembeOne_Bass', slap: 'DjembeOne_Slap', tone_muffled: 'DjembeOne_Muffled_Open', slap_muffled: 'DjembeOne_Muffled_Slap' }, handState: exportHandStates.Djembe_1 },
    { trackName: 'Djembe_2', playback: djembe2Playback, instrument: djembe_2, baseNames: { tone: 'DjembeTwo_Open', bass: 'DjembeTwo_Bass', slap: 'DjembeTwo_Slap', tone_muffled: 'DjembeTwo_Muffled_Open', slap_muffled: 'DjembeTwo_Muffled_Slap' }, handState: exportHandStates.Djembe_2 },
    { trackName: 'Djembe_3', playback: djembe3Playback, instrument: djembe_3, baseNames: { tone: 'DjembeThree_Open', bass: 'DjembeThree_Bass', slap: 'DjembeThree_Slap', slap_muffled: 'DjembeThree_Mute' }, handState: exportHandStates.Djembe_3 }
  ];

  djembeNotes.forEach(function (djembeData) {
    const playback = djembeData.playback;
    const noteValue = playback ? playback.note : null;
    const djembeTime = djembeTimes[djembeData.trackName] ?? time;
    switch (noteValue) {
      case "tone":
      case "bass":
      case "slap":
      case "tone_muffled":
      case "slap_muffled":
        if (djembeData.baseNames[noteValue]) {
          playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames[noteValue], djembeTime, playback, djembeData.handState, noteGain, audioContext, destinationNode);
        }
        break;
      case "tone_flam":
        playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames.tone, djembeTime, playback, djembeData.handState, noteGain, audioContext, destinationNode, { isGrace: true });
        playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames.tone, djembeTime + 5 / exportTempo, playback, djembeData.handState, noteGain, audioContext, destinationNode);
        break;
      case "slap_flam":
        playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames.slap, djembeTime, playback, djembeData.handState, noteGain, audioContext, destinationNode, { isGrace: true });
        playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames.slap, djembeTime + 5 / exportTempo, playback, djembeData.handState, noteGain, audioContext, destinationNode);
        break;
      case "bass_slap_flam":
        playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames.bass, djembeTime, playback, djembeData.handState, noteGain, audioContext, destinationNode, { isGrace: true });
        playDjembeStrokeToDestination(djembeData.instrument, djembeData.baseNames.slap, djembeTime + 5 / exportTempo, playback, djembeData.handState, noteGain, audioContext, destinationNode);
        break;
    }
  });
}

function encodeWavFromAudioBuffer(audioBuffer) {
  const channelCount = audioBuffer.numberOfChannels;
  const sampleRate = audioBuffer.sampleRate;
  const frameCount = audioBuffer.length;
  const bytesPerSample = 2;
  const blockAlign = channelCount * bytesPerSample;
  const dataSize = frameCount * blockAlign;
  const buffer = new ArrayBuffer(44 + dataSize);
  const view = new DataView(buffer);
  let offset = 0;

  function writeString(value) {
    for (let charIndex = 0; charIndex < value.length; charIndex++) {
      view.setUint8(offset++, value.charCodeAt(charIndex));
    }
  }

  function writeUint32(value) {
    view.setUint32(offset, value, true);
    offset += 4;
  }

  function writeUint16(value) {
    view.setUint16(offset, value, true);
    offset += 2;
  }

  writeString('RIFF');
  writeUint32(36 + dataSize);
  writeString('WAVE');
  writeString('fmt ');
  writeUint32(16);
  writeUint16(1);
  writeUint16(channelCount);
  writeUint32(sampleRate);
  writeUint32(sampleRate * blockAlign);
  writeUint16(blockAlign);
  writeUint16(bytesPerSample * 8);
  writeString('data');
  writeUint32(dataSize);

  const channels = [];
  for (let channelIndex = 0; channelIndex < channelCount; channelIndex++) {
    channels.push(audioBuffer.getChannelData(channelIndex));
  }

  for (let sampleIndex = 0; sampleIndex < frameCount; sampleIndex++) {
    for (let channelIndex = 0; channelIndex < channelCount; channelIndex++) {
      const sampleValue = Math.max(-1, Math.min(1, channels[channelIndex][sampleIndex] || 0));
      view.setInt16(offset, sampleValue < 0 ? sampleValue * 0x8000 : sampleValue * 0x7FFF, true);
      offset += 2;
    }
  }

  return new Blob([buffer], { type: 'audio/wav' });
}

function downloadBlob(blob, filename) {
  const downloadUrl = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = downloadUrl;
  anchor.download = filename;
  document.body.appendChild(anchor);
  anchor.click();
  anchor.remove();
  window.setTimeout(function () {
    URL.revokeObjectURL(downloadUrl);
  }, 1000);
}

async function exportCurrentArrangementAsWav() {
  if (!audioIsReady) {
    updateLoadingStatus('Audiodateien werden noch geladen...');
    return;
  }

  const exportTempo = Number(tempo) || initialTempo;
  const exportStepCount = getExportStepCount();
  if (exportStepCount <= 0) {
    return;
  }

  exportWavButton.disabled = true;
  const previousButtonText = exportWavButton.textContent;
  exportWavButton.textContent = 'WAV...';

  try {
    let renderTime = 0;
    for (let stepIndex = 0; stepIndex < exportStepCount; stepIndex++) {
      renderTime += getStepInterval(stepIndex, exportTempo);
    }

    const tailTime = 3.5;
    const sampleRate = 48000;
    const offlineContext = new OfflineAudioContext(2, Math.ceil((renderTime + tailTime) * sampleRate), sampleRate);
    const exportHandStates = {
      Djembe_1: { nextHand: 'R', leadHand: 'R', lastHand: '', lastSectionKey: '' },
      Djembe_2: { nextHand: 'R', leadHand: 'R', lastHand: '', lastSectionKey: '' },
      Djembe_3: { nextHand: 'R', leadHand: 'R', lastHand: '', lastSectionKey: '' }
    };

    let currentTime = 0;
    for (let stepIndex = 0; stepIndex < exportStepCount; stepIndex++) {
      function maybeTrackBeatForExport(trackName, trackState) {
        if (soloTrackName && soloTrackName !== trackName) {
          return null;
        }
        return getTrackPlaybackAtStep(trackName, trackState, stepIndex);
      }

      const kenkeniPlayback = maybeTrackBeatForExport('Kenkeni', trackStates.Kenkeni);
      const sangbanPlayback = maybeTrackBeatForExport('Sangban', trackStates.Sangban);
      const doundounPlayback = maybeTrackBeatForExport('Doundoun', trackStates.Doundoun);
      const dreierbassPlayback = maybeTrackBeatForExport('Dreierbass', trackStates.Dreierbass);
      const djembe1Playback = maybeTrackBeatForExport('Djembe_1', trackStates.Djembe_1);
      const djembe2Playback = maybeTrackBeatForExport('Djembe_2', trackStates.Djembe_2);
      const djembe3Playback = maybeTrackBeatForExport('Djembe_3', trackStates.Djembe_3);
      const accentMultiplier = getAccentMultiplier(stepIndex);

      advanceSilentH2HStep(djembe1Playback, exportHandStates.Djembe_1, {
        instrumentInstance: djembe_1,
        baseSampleName: getH2HRestMuteSampleName('Djembe_1'),
        time: Math.max(0, currentTime + getFeelOffsetSeconds('Djembe_1')),
        gainMultiplier: accentMultiplier * h2hRestMuteGainMultipliers.Djembe_1,
        audioContext: offlineContext,
        destinationNode: offlineContext.destination
      });
      advanceSilentH2HStep(djembe2Playback, exportHandStates.Djembe_2, {
        instrumentInstance: djembe_2,
        baseSampleName: getH2HRestMuteSampleName('Djembe_2'),
        time: Math.max(0, currentTime + getFeelOffsetSeconds('Djembe_2')),
        gainMultiplier: accentMultiplier * h2hRestMuteGainMultipliers.Djembe_2,
        audioContext: offlineContext,
        destinationNode: offlineContext.destination
      });
      advanceSilentH2HStep(djembe3Playback, exportHandStates.Djembe_3, {
        instrumentInstance: djembe_3,
        baseSampleName: getH2HRestMuteSampleName('Djembe_3'),
        time: Math.max(0, currentTime + getFeelOffsetSeconds('Djembe_3')),
        gainMultiplier: accentMultiplier * h2hRestMuteGainMultipliers.Djembe_3,
        audioContext: offlineContext,
        destinationNode: offlineContext.destination
      });

      scheduleNoteToDestination(
        kenkeniPlayback ? kenkeniPlayback.note : null,
        sangbanPlayback ? sangbanPlayback.note : null,
        doundounPlayback ? doundounPlayback.note : null,
        dreierbassPlayback ? dreierbassPlayback.note : null,
        djembe1Playback,
        djembe2Playback,
        djembe3Playback,
        currentTime,
        accentMultiplier,
        offlineContext,
        offlineContext.destination,
        exportHandStates,
        exportTempo
      );

      currentTime += getStepInterval(stepIndex, exportTempo);
    }

    const renderedBuffer = await offlineContext.startRendering();
    const wavBlob = encodeWavFromAudioBuffer(renderedBuffer);
    const exportName = (obj[0] && obj[0].Name ? obj[0].Name : 'bara-export') + '.wav';
    downloadBlob(wavBlob, exportName);
  } finally {
    exportWavButton.disabled = false;
    exportWavButton.textContent = previousButtonText;
  }
}

function stopAllActiveSources(stopTime) {
  allInstruments.forEach(function (instrumentInstance) {
    instrumentInstance.stopActiveSources(stopTime);
  });
}

playButton.addEventListener('click', async (ev) => {
  if (!audioIsReady) {
    updateLoadingStatus('Audiodateien werden noch geladen...');
    return;
  }

  isPlaying = !isPlaying;

  if (isPlaying) {
    try {
      await resumeAndWarmAllInstruments();
      if (!hasWaitedAfterFirstResume) {
        await new Promise(resolve => window.setTimeout(resolve, 260));
        hasWaitedAfterFirstResume = true;
      }
      if (!hasPrimedAudioOutput) {
        await new Promise(resolve => window.setTimeout(resolve, 120));
        hasPrimedAudioOutput = true;
      }
    } catch (err) {
      console.error('AudioContext resume fehlgeschlagen:', err);
    }

    globalPlaybackStep = 0;
    resetDjembeHandStates();

    const dTime = instr._audioCtx.currentTime;
    nextNoteTime = dTime + playerStartDelay + practiceLeadInDelay;
    practiceStopAudioTime = practiceDurationSeconds > 0
      ? nextNoteTime + practiceDurationSeconds
      : 0;
    practiceTimerFinalLoopStartStep = null;

    ev.target.dataset.playing = 'true';
    notifyEmbeddedPlaybackState('playing', {
      leadInMs: Math.max(0, (playerStartDelay + practiceLeadInDelay) * 1000)
    });
    scheduler();
  } else {
    window.clearTimeout(timerID);
    stopAllActiveSources(instr._audioCtx.currentTime);
    practiceStopAudioTime = 0;
    practiceTimerFinalLoopStartStep = null;
    ev.target.dataset.playing = 'false';
    notifyEmbeddedPlaybackState('stopped');
  }
});

exportWavButton.addEventListener('click', function () {
  exportCurrentArrangementAsWav().catch(function (error) {
    console.error('WAV-Export fehlgeschlagen:', error);
    updateLoadingStatus('Exportfehler: ' + (error && error.message ? error.message : String(error)));
  });
});
</script>
</body>
</html>
