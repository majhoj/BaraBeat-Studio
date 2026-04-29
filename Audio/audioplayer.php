<?php
$myObject = $_POST["myObj"] ?? "[]";
$playerJs = @filemtime(__DIR__ . '/js/instrument_2.js') ?: 1;
$playerCss = @filemtime(__DIR__ . '/css/audio_style.css') ?: 1;
?>
<script>
  const obj = <?php echo $myObject; ?>;
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
  color: black;
}
</style>

<body>

<div class="loading">
  <p>Loading...</p>
</div>

<div id="sequencer">
  <section class="controls-main">
    <div class="player-title">
      <script>
        document.write('<a href="javascript:close_window();">X</a>' + obj[0].Name);
      </script>
    </div>
    <div class="player-controls">
      <label for="bpm">BPM</label>
      <input name="bpm" id="bpm" type="range" min="30" max="180" value="120" step="1" />
      <span id="bpmval">120</span>
      <label for="soloTrack">Solo</label>
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
      <button data-playing="false">&nbsp;</button>
    </div>
  </section>
</div>

<script type="text/javascript">
console.clear();

const loadingEl = document.querySelector('.loading');
window.loadingEl = loadingEl;
const playButton = document.querySelector('[data-playing]');
let isPlaying = false;
let audioIsReady = false;
let hasWaitedAfterFirstResume = false;
let hasPrimedAudioOutput = false;

playButton.disabled = true;

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

const djembe_1_mp3Files = ['snd/Silence.mp3','snd/DjembeOne_Open.mp3','snd/DjembeOne_Bass.mp3','snd/DjembeOne_Slap.mp3','snd/DjembeOne_Mute.mp3'];
const djembe_2_mp3Files = ['snd/Silence.mp3','snd/DjembeTwo_Open.mp3','snd/DjembeTwo_Bass.mp3','snd/DjembeTwo_Slap.mp3','snd/DjembeTwo_Mute.mp3'];
const djembe_3_mp3Files = ['snd/Silence.mp3','snd/DjembeThree_Open.mp3','snd/DjembeThree_Bass.mp3','snd/DjembeThree_Slap.mp3','snd/DjembeThree_Mute.mp3'];
const kenkeni_mp3Files  = ['snd/Kenkeni_Open.mp3','snd/Kenkeni_Muffled.mp3','snd/Kenkeni_Bell_Open.mp3'];
const sangban_mp3Files  = ['snd/Sangban_Open.mp3','snd/Sangban_Muffled.mp3','snd/Sangban_Bell_Open.mp3'];
const doundoun_mp3Files = ['snd/Doundoun_Open.mp3','snd/Doundoun_Muffled.mp3','snd/Doundoun_Bell_Open.mp3'];
const dreierbass_mp3Files = ['snd/Silence.mp3','snd/Kenkeni_Open.mp3','snd/Kenkeni_Muffled.mp3','snd/Sangban_Open.mp3','snd/Sangban_Muffled.mp3','snd/Doundoun_Open.mp3','snd/Doundoun_Muffled.mp3'];

const djembe_1 = new Instrumente(djembe_1_mp3Files, 1, 1.4);
const djembe_2 = new Instrumente(djembe_2_mp3Files, 0.5, 1.4);
const djembe_3 = new Instrumente(djembe_3_mp3Files, 0, 1.4);
const kenkeni  = new Instrumente(kenkeni_mp3Files, -1, 1.5);
const sangban  = new Instrumente(sangban_mp3Files, -0.7, 2.5);
const doundoun = new Instrumente(doundoun_mp3Files, -0.4, 1.5);
const dreierbass = new Instrumente(dreierbass_mp3Files, -1, 1.5);
const allInstruments = [djembe_1, djembe_2, djembe_3, kenkeni, sangban, doundoun, dreierbass];
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

const repeatRanges = sanitizeRepeatRanges(obj[0].RepeatRanges);

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
  if (bar.instrumentMode === 'allUsedDjembes') {
    return usedDjembeTrackNames;
  }
  if (bar.instrument && bar.instrument.search('End') !== 0) {
    return [bar.instrument];
  }
  return [];
}

function appendBarToTracks(bar, rowIndex) {
  if (!bar || !Array.isArray(bar.notes)) {
    return;
  }

  const segmentLength = bar.notes.length;
  const targetInstruments = getTargetInstrumentsForBar(bar);

  trackInstrumentNames.forEach(function (instrumentName) {
    const instrumentEntry = ensureSteuerungEntry(instrumentName);
    const segmentStart = instrumentEntry.noten.length;
    const segmentNotes = targetInstruments.indexOf(instrumentName) !== -1
      ? bar.notes
      : new Array(segmentLength).fill('f');
    instrumentEntry.noten = instrumentEntry.noten.concat(segmentNotes);
    if (targetInstruments.indexOf(instrumentName) === -1) {
      return;
    }

    if (bar.label === "Call") {
      instrumentEntry.callNotes = instrumentEntry.callNotes.concat(bar.notes);
    }
    if (bar.label === "Intro") {
      instrumentEntry.introNotes = instrumentEntry.introNotes.concat(bar.notes);
    }
    if (bar.label === "Echauffement") {
      instrumentEntry.echauffementNotes = instrumentEntry.echauffementNotes.concat(bar.notes);
    }
    if (bar.label === "Begleitung") {
      instrumentEntry.begleitungNotes = instrumentEntry.begleitungNotes.concat(bar.notes);
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

function createOrderedSection(label) {
  return {
    label: label,
    trackNotes: createEmptyTrackNoteMap(),
    length: 0,
    startStep: 0,
    endStep: 0
  };
}

function appendBarToOrderedSection(section, bar) {
  getTargetInstrumentsForBar(bar).forEach(function (instrumentName) {
    section.trackNotes[instrumentName] = section.trackNotes[instrumentName].concat(bar.notes);
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

function expandBarsWithRepeats(flatBars, repeatRangesToApply, startBarIndex, endBarIndex) {
  const expandedBars = [];
  let currentBarIndex = startBarIndex;

  while (currentBarIndex <= endBarIndex) {
    const matchingRanges = repeatRangesToApply
      .filter(function (repeatRange) {
        return repeatRange.startBar === currentBarIndex && repeatRange.endBar <= endBarIndex;
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

const flatBars = buildFlatBarsFromRows(obj);
const usedDjembeTrackNames = trackInstrumentNames.filter(function (instrumentName) {
  return instrumentName.indexOf('Djembe_') === 0 && flatBars.some(function (bar) {
    return bar.instrument === instrumentName;
  });
});
const expandedBars = expandBarsWithRepeats(flatBars, repeatRanges, 1, flatBars.length);
const orderedSections = [];
const orderedSectionLabels = ['Call', 'Intro', 'Begleitung', 'Echauffement', 'Outro'];
let currentOrderedSection = null;
console.log('repeatRanges', repeatRanges);
console.log('expandedBars', expandedBars);

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

  if (!currentOrderedSection || currentOrderedSection.label !== bar.label) {
    currentOrderedSection = createOrderedSection(bar.label);
    orderedSections.push(currentOrderedSection);
  }

  appendBarToOrderedSection(currentOrderedSection, bar);
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
let tempo = 120.0;
const bpmControl = document.querySelector('#bpm');
const bpmValEl = document.querySelector('#bpmval');
const soloTrackControl = document.querySelector('#soloTrack');
let soloTrackName = '';

bpmControl.addEventListener('input', ev => {
  tempo = Number(ev.target.value);
  bpmValEl.innerText = tempo;
}, false);

soloTrackControl.addEventListener('change', ev => {
  soloTrackName = ev.target.value;
}, false);

const lookahead = 25;          // ms
const scheduleAheadTime = 0.25; // sec
const playerStartDelay = 0.18; // sec

let nextNoteTime = 0.0;
let timerID;
let instr;
let globalPlaybackStep = 0;

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

orderedSections.forEach(function (section, sectionIndex) {
  section.length = Math.max.apply(null, trackInstrumentNames.map(function (instrumentName) {
    return getSectionLength(section.trackNotes[instrumentName]);
  }).concat(0));
  section.startStep = sectionIndex === 0 ? 0 : orderedSections[sectionIndex - 1].endStep;
  section.endStep = section.startStep + section.length;
});

const oneShotLength = orderedSections.length > 0
  ? orderedSections[orderedSections.length - 1].endStep
  : 0;
const hasOutroSection = orderedSections.some(function (section) {
  return section.label === 'Outro';
});

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

function nextNote() {
  const secondsPerBeat = 7.5 / tempo;
  nextNoteTime += secondsPerBeat;
  globalPlaybackStep++;
}

function scheduleNote(kenkeniNote, sangbanNote, doundounNote, dreierbassNote, djembe1Note, djembe2Note, djembe3Note, time) {

  function playKenkeni() {
    switch (kenkeniNote) {
      case "f":
      case null:
      case undefined:
        break;
      case "Bell":
        kenkeni.play('Kenkeni_Bell_Open', time);
        break;
      case "Open":
        kenkeni.play('Kenkeni_Open', time);
        break;
      case "Muffled":
        kenkeni.play('Kenkeni_Muffled', time);
        break;
      case "Bell_Open":
        kenkeni.play('Kenkeni_Bell_Open', time);
        kenkeni.play('Kenkeni_Open', time);
        break;
      case "Bell_Muffled":
        kenkeni.play('Kenkeni_Bell_Open', time);
        kenkeni.play('Kenkeni_Muffled', time);
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
        sangban.play('Sangban_Bell_Open', time);
        break;
      case "Open":
        sangban.play('Sangban_Open', time);
        break;
      case "Muffled":
        sangban.play('Sangban_Muffled', time);
        break;
      case "Bell_Open":
        sangban.play('Sangban_Bell_Open', time);
        sangban.play('Sangban_Open', time);
        break;
      case "Bell_Muffled":
        sangban.play('Sangban_Bell_Open', time);
        sangban.play('Sangban_Muffled', time);
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
        doundoun.play('Doundoun_Bell_Open', time);
        break;
      case "Open":
        doundoun.play('Doundoun_Open', time);
        break;
      case "Muffled":
        doundoun.play('Doundoun_Muffled', time);
        break;
      case "Bell_Open":
        doundoun.play('Doundoun_Bell_Open', time);
        doundoun.play('Doundoun_Open', time);
        break;
      case "Bell_Muffled":
        doundoun.play('Doundoun_Bell_Open', time);
        doundoun.play('Doundoun_Muffled', time);
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
        dreierbass.play('Sangban_Open', time);
        break;
      case "doundoun":
        dreierbass.play('Doundoun_Open', time);
        break;
      case "kenkeni":
        dreierbass.play('Kenkeni_Open', time);
        break;
      case "kenkeni_muffled":
        dreierbass.play('Kenkeni_Muffled', time);
        break;
      case "sangban_muffled":
        dreierbass.play('Sangban_Muffled', time);
        break;
      case "kenkeni_sangban":
        dreierbass.play('Kenkeni_Open', time);
        dreierbass.play('Sangban_Open', time + 0.5 / tempo);
        break;
      case "kenkeni_doundoun":
        dreierbass.play('Kenkeni_Open', time);
        dreierbass.play('Doundoun_Open', time + 0.5 / tempo);
        break;
      case "sangban_doundoun":
        dreierbass.play('Sangban_Open', time);
        dreierbass.play('Doundoun_Open', time + 0.5 / tempo);
        break;
      case "kenkeni_sangban_muffled":
        dreierbass.play('Kenkeni_Open', time);
        dreierbass.play('Sangban_Muffled', time + 0.5 / tempo);
        break;
      case "kenkeni_muffled_sangban":
        dreierbass.play('Kenkeni_Muffled', time);
        dreierbass.play('Sangban_Open', time + 0.5 / tempo);
        break;
      case "kenkeni_muffled_doundoun":
        dreierbass.play('Kenkeni_Muffled', time);
        dreierbass.play('Doundoun_Open', time + 1 / tempo);
        break;
      case "sangban_muffled_doundoun":
        dreierbass.play('Sangban_Muffled', time);
        dreierbass.play('Doundoun_Open', time + 1 / tempo);
        break;
    }
  }

  function playDjembe_1() {
    switch (djembe1Note) {
      case "f":
      case null:
      case undefined:
        break;
      case "tone":
        djembe_1.play('DjembeOne_Open', time);
        break;
      case "bass":
        djembe_1.play('DjembeOne_Bass', time);
        break;
      case "slap":
        djembe_1.play('DjembeOne_Slap', time);
        break;
      case "slap_muffled":
        djembe_1.play('DjembeOne_Mute', time);
        break;
      case "tone_flam":
        djembe_1.play('DjembeOne_Open', time);
        djembe_1.play('DjembeOne_Open', time + 5 / tempo);
        break;
      case "slap_flam":
        djembe_1.play('DjembeOne_Slap', time);
        djembe_1.play('DjembeOne_Slap', time + 5 / tempo);
        break;
      case "bass_slap_flam":
        djembe_1.play('DjembeOne_Bass', time);
        djembe_1.play('DjembeOne_Slap', time + 5 / tempo);
        break;
    }
  }

  function playDjembe_2() {
    switch (djembe2Note) {
      case "f":
      case null:
      case undefined:
        break;
      case "tone":
        djembe_2.play('DjembeTwo_Open', time);
        break;
      case "bass":
        djembe_2.play('DjembeTwo_Bass', time);
        break;
      case "slap":
        djembe_2.play('DjembeTwo_Slap', time);
        break;
      case "slap_muffled":
        djembe_2.play('DjembeTwo_Mute', time);
        break;
      case "tone_flam":
        djembe_2.play('DjembeTwo_Open', time);
        djembe_2.play('DjembeTwo_Open', time + 5 / tempo);
        break;
      case "slap_flam":
        djembe_2.play('DjembeTwo_Slap', time);
        djembe_2.play('DjembeTwo_Slap', time + 5 / tempo);
        break;
      case "bass_slap_flam":
        djembe_2.play('DjembeTwo_Bass', time);
        djembe_2.play('DjembeTwo_Slap', time + 5 / tempo);
        break;
    }
  }

  function playDjembe_3() {
    switch (djembe3Note) {
      case "f":
      case null:
      case undefined:
        break;
      case "tone":
        djembe_3.play('DjembeThree_Open', time);
        break;
      case "bass":
        djembe_3.play('DjembeThree_Bass', time);
        break;
      case "slap":
        djembe_3.play('DjembeThree_Slap', time);
        break;
      case "slap_muffled":
        djembe_3.play('DjembeThree_Mute', time);
        break;
      case "tone_flam":
        djembe_3.play('DjembeThree_Open', time);
        djembe_3.play('DjembeThree_Open', time + 5 / tempo);
        break;
      case "slap_flam":
        djembe_3.play('DjembeThree_Slap', time);
        djembe_3.play('DjembeThree_Slap', time + 5 / tempo);
        break;
      case "bass_slap_flam":
        djembe_3.play('DjembeThree_Bass', time);
        djembe_3.play('DjembeThree_Slap', time + 5 / tempo);
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

function getTrackNoteAtStep(trackName, trackState, playbackStep) {
  for (let sectionIndex = 0; sectionIndex < orderedSections.length; sectionIndex++) {
    const section = orderedSections[sectionIndex];
    if (playbackStep >= section.startStep && playbackStep < section.endStep) {
      const sectionStep = playbackStep - section.startStep;
      return section.trackNotes[trackName][sectionStep] || null;
    }
  }

  if (hasOutroSection) {
    return null;
  }

  const begleitungLength = getSectionLength(trackState.begleitungNotes);
  if (begleitungLength > 0) {
    const loopStep = playbackStep - oneShotLength;
    return trackState.begleitungNotes[loopStep % begleitungLength];
  }

  return null;
}

function scheduler() {
  const dTime = instr._audioCtx.currentTime;

  while (nextNoteTime < dTime + scheduleAheadTime) {
    if (hasOutroSection && globalPlaybackStep >= oneShotLength) {
      isPlaying = false;
      playButton.dataset.playing = 'false';
      timerID = null;
      return;
    }
    scheduleCurrentStep(nextNoteTime);
    nextNote();
  }

  timerID = window.setTimeout(scheduler, lookahead);
}

function scheduleCurrentStep(time) {
  function maybeTrackBeat(trackName, trackState) {
    if (soloTrackName && soloTrackName !== trackName) {
      return null;
    }
    return getTrackNoteAtStep(trackName, trackState, globalPlaybackStep);
  }

  scheduleNote(
    maybeTrackBeat('Kenkeni', trackStates.Kenkeni),
    maybeTrackBeat('Sangban', trackStates.Sangban),
    maybeTrackBeat('Doundoun', trackStates.Doundoun),
    maybeTrackBeat('Dreierbass', trackStates.Dreierbass),
    maybeTrackBeat('Djembe_1', trackStates.Djembe_1),
    maybeTrackBeat('Djembe_2', trackStates.Djembe_2),
    maybeTrackBeat('Djembe_3', trackStates.Djembe_3),
    time
  );
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

    const dTime = instr._audioCtx.currentTime;
    nextNoteTime = dTime + playerStartDelay;

    ev.target.dataset.playing = 'true';
    scheduler();
  } else {
    window.clearTimeout(timerID);
    stopAllActiveSources(instr._audioCtx.currentTime);
    ev.target.dataset.playing = 'false';
  }
});
</script>
</body>
</html>
