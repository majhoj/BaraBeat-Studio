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
    this._panNode = typeof this._audioCtx.createStereoPanner === 'function'
      ? this._audioCtx.createStereoPanner()
      : null;
    if (this._panNode) {
      this._panNode.pan.value = Math.max(-1, Math.min(1, this._pan));
      this._panNode.connect(this._outputNode);
    }
    this._outputNode.connect(this._audioCtx.destination);
    this.readyPromise = this.loadSounds();
  }

  createSilentBuffer(duration = 0.08) {
    const sampleRate = this._audioCtx.sampleRate || 44100;
    return this._audioCtx.createBuffer(1, Math.max(1, Math.ceil(sampleRate * duration)), sampleRate);
  }

  async getFile(filepath) {
    if (!Instrumente.audioBufferCache) {
      Instrumente.audioBufferCache = new Map();
    }
    if (Instrumente.audioBufferCache.has(filepath)) {
      return await Instrumente.audioBufferCache.get(filepath);
    }

    const loadPromise = this.loadAudioBuffer(filepath);
    Instrumente.audioBufferCache.set(filepath, loadPromise);
    try {
      return await loadPromise;
    } catch (error) {
      Instrumente.audioBufferCache.delete(filepath);
      throw error;
    }
  }

  async loadAudioBuffer(filepath) {
    if (this.getSoundName(filepath) === 'Silence') {
      return this.createSilentBuffer();
    }

    const response = await fetch(filepath);
    const contentType = response.headers.get('content-type') || '';

    if (!response.ok) {
      throw new Error(
        'Audiodatei konnte nicht geladen werden: ' + filepath +
        ' -> ' + response.url +
        ' (HTTP ' + response.status + ')'
      );
    }

    const arrayBuffer = await response.arrayBuffer();
    const firstBytes = new Uint8Array(arrayBuffer.slice(0, 16));
    const firstText = String.fromCharCode.apply(null, firstBytes).trim();

    if (/^(text\/html|application\/json|application\/xml|text\/xml)\b/i.test(contentType) || firstText.charAt(0) === '<') {
      throw new Error(
        'Audiodatei liefert keine Audiodaten: ' + filepath +
        ' -> ' + response.url +
        ' (' + (contentType || 'kein Content-Type') + ')'
      );
    }

    try {
      return await this._audioCtx.decodeAudioData(arrayBuffer);
    } catch (error) {
      throw new Error(
        'Audiodatei konnte nicht dekodiert werden: ' + filepath +
        ' -> ' + response.url +
        ' (' + (contentType || 'kein Content-Type') + ') - ' + error.message
      );
    }
  }

  getSoundName(filepath) {
    return filepath.substring(filepath.lastIndexOf('/') + 1, filepath.lastIndexOf('.'));
  }

  async loadSounds() {
    const loadingIndicator = window.loadingEl;

    try {
      for (let i = 0; i < this._sounds.length; i++) {
        const filepath = this._sounds[i];
        const name = this.getSoundName(filepath);
        const audioBuffer = await this.getFile(filepath);
        this._snd[name] = audioBuffer;
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

  play(name, time_i, gainMultiplier = 1) {
    if (Object.prototype.hasOwnProperty.call(this._snd, name)) {
      const sampleSource = this._audioCtx.createBufferSource();
      const vol_tone = this._audioCtx.createGain();

      sampleSource.buffer = this._snd[name];
      vol_tone.gain.value = this._vol * Math.max(0, Number(gainMultiplier) || 1);

      if (this._panNode) {
        sampleSource.connect(vol_tone).connect(this._panNode);
      } else {
        sampleSource.connect(vol_tone).connect(this._outputNode);
      }

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
