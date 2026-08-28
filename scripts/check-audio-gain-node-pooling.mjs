import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const instrumentSource = fs.readFileSync(path.join(projectRoot, 'Audio/js/instrument_2.js'), 'utf8');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

let gainNodeCount = 0;
const createdSources = [];
const audioContext = {
  currentTime: 0,
  destination: {},
  createGain() {
    gainNodeCount += 1;
    return {
      gain: { value: 0 },
      connect() {},
      disconnect() {}
    };
  },
  createStereoPanner() {
    return {
      pan: { value: 0 },
      connect() {},
      disconnect() {}
    };
  },
  createBufferSource() {
    const sampleSource = {
      buffer: null,
      onended: null,
      connect() {},
      disconnect() {},
      start() {},
      stop() {}
    };
    createdSources.push(sampleSource);
    return sampleSource;
  }
};

const context = vm.createContext({
  console,
  navigator: {},
  document: {
    createElement() {
      return {};
    }
  },
  window: {
    sharedAudioContext: audioContext,
    loadingEl: null
  }
});
vm.runInContext(instrumentSource + '\nthis.Instrumente = Instrumente;', context);

const instrument = vm.runInContext('new Instrumente([], 0, 1.5);', context);
instrument._snd.Test = { duration: 0.1 };
for (let noteIndex = 0; noteIndex < 100; noteIndex++) {
  instrument.play('Test', noteIndex * 0.1, 0.8);
}

assert(gainNodeCount === 2,
  'Hundert Schlaege gleicher Lautstaerke erzeugen weiterhin mehr als einen gemeinsamen Klang-Gain-Knoten.');
assert(instrument._gainNodesByValue.size === 1,
  'Der gemeinsame Gain-Knoten wird nicht nach Lautstaerkewert wiederverwendet.');

instrument.play('Test', 10.1, 0.4);
assert(gainNodeCount === 3 && instrument._gainNodesByValue.size === 2,
  'Ein neuer Lautstaerkewert erhaelt keinen eigenen wiederverwendbaren Gain-Knoten.');

assert(instrument._activeSources.length === 101,
  'Der Testaufbau enthaelt nicht alle Audioquellen ohne onended-Rueckmeldung.');
audioContext.currentTime = 20;
instrument.play('Test', 20.1, 0.8);
assert(instrument._activeSources.length === 1,
  'Zeitlich beendete Audioquellen bleiben ohne WebKit-onended-Rueckmeldung erhalten.');
assert(instrument._activeSourceEndTimes.size === 1,
  'Die Ablaufzeiten beendeter Audioquellen werden nicht bereinigt.');

console.log('Audio-Knoten: Gain-Wiederverwendung und zeitbasierte Quellenbereinigung geprueft.');
