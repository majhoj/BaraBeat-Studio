// JavaScript Document
class Instrumente {
  constructor(sounds, pan, vol) {
    this._sounds = sounds;
    this._pan = pan;
    this._vol = vol;

    if (!window.sharedAudioContext) {
      window.sharedAudioContext = window.AudioContext ? new AudioContext() : new webkitAudioContext();
    }

    this._audioCtx = window.sharedAudioContext;
    this._snd = {};
    this._activeSources = [];
    this._outputNode = this._audioCtx.createGain();
    this._outputNode.connect(this._audioCtx.destination);
    this.readyPromise = this.loadSounds();
  }

  async getFile(filepath) {
    const response = await fetch(filepath);
    const arrayBuffer = await response.arrayBuffer();
    return this._audioCtx.decodeAudioData(arrayBuffer);
  }

  getSoundName(filepath) {
    return filepath.substring(filepath.lastIndexOf('/') + 1, filepath.lastIndexOf('.'));
  }

  createPannedBuffer(audioBuffer) {
    const pannedBuffer = this._audioCtx.createBuffer(2, audioBuffer.length, audioBuffer.sampleRate);
    const leftOutput = pannedBuffer.getChannelData(0);
    const rightOutput = pannedBuffer.getChannelData(1);
    const leftGain = Math.max(0, 1 - this._pan) / 2;
    const rightGain = Math.max(0, 1 + this._pan) / 2;
    const channelScale = audioBuffer.numberOfChannels > 1 ? 1 / audioBuffer.numberOfChannels : 1;

    for (let channelIndex = 0; channelIndex < audioBuffer.numberOfChannels; channelIndex++) {
      const input = audioBuffer.getChannelData(channelIndex);
      for (let sampleIndex = 0; sampleIndex < audioBuffer.length; sampleIndex++) {
        const monoSample = input[sampleIndex] * channelScale;
        leftOutput[sampleIndex] += monoSample * leftGain;
        rightOutput[sampleIndex] += monoSample * rightGain;
      }
    }

    return pannedBuffer;
  }

  async loadSounds() {
    const loadingIndicator = window.loadingEl;

    try {
      for (let i = 0; i < this._sounds.length; i++) {
        const filepath = this._sounds[i];
        const name = this.getSoundName(filepath);
        const audioBuffer = await this.getFile(filepath);
        this._snd[name] = this.createPannedBuffer(audioBuffer);
      }
    } catch (error) {
      console.error('Fehler beim Laden der Audiodateien:', error);
      if (loadingIndicator) {
        loadingIndicator.innerHTML = '<p>Ladefehler: ' + error.message + '</p>';
      }
      throw error;
    }

    if (loadingIndicator) {
      loadingIndicator.style.display = 'none';
    }
  }

  play(name, time_i) {
    if (Object.prototype.hasOwnProperty.call(this._snd, name)) {
      const sampleSource = this._audioCtx.createBufferSource();
      const vol_tone = this._audioCtx.createGain();

      sampleSource.buffer = this._snd[name];
      vol_tone.gain.value = this._vol;

      sampleSource.connect(vol_tone).connect(this._outputNode);

      sampleSource.onended = () => {
        this._activeSources = this._activeSources.filter((source) => source !== sampleSource);
      };
      this._activeSources.push(sampleSource);
      sampleSource.start(Math.max(time_i, this._audioCtx.currentTime + 0.02));
    }
  }

  stopActiveSources(stopTime) {
    this._activeSources.forEach((sampleSource) => {
      try {
        sampleSource.stop(Math.max(stopTime, this._audioCtx.currentTime));
      } catch (error) {
        // The source may already be stopped; that is harmless here.
      }
    });
    this._activeSources = [];
  }

  warmUpSamples() {
    const warmUpTime = this._audioCtx.currentTime + 0.02;

    Object.keys(this._snd).forEach((name) => {
      const sampleSource = this._audioCtx.createBufferSource();
      const mutedGain = this._audioCtx.createGain();

      sampleSource.buffer = this._snd[name];
      mutedGain.gain.value = 0.0001;
      sampleSource.connect(mutedGain).connect(this._audioCtx.destination);
      sampleSource.start(warmUpTime);
      sampleSource.stop(warmUpTime + 0.08);
    });
  }
}
