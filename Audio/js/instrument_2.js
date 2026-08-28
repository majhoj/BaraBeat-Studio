// JavaScript Document
function audioPlayerText(key, values) {
  return window.BaraBeatI18n && typeof window.BaraBeatI18n.t === 'function'
    ? window.BaraBeatI18n.t(key, values)
    : key;
}

function configureBarabeatAudioSession() {
  if (typeof navigator === 'undefined' || !navigator.audioSession) {
    return;
  }

  try {
    navigator.audioSession.type = 'playback';
  } catch (error) {
    console.warn('AudioSession konnte nicht auf playback gesetzt werden:', error);
  }
}

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
    this._activeSourceEndTimes = new Map();
    this._gainNodesByValue = new Map();
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
      throw new Error(audioPlayerText('player.error.audioFileLoad', {
        path: filepath,
        url: response.url,
        status: response.status
      }));
    }

    const arrayBuffer = await response.arrayBuffer();
    const firstBytes = new Uint8Array(arrayBuffer.slice(0, 16));
    const firstText = String.fromCharCode.apply(null, firstBytes).trim();

    if (/^(text\/html|application\/json|application\/xml|text\/xml)\b/i.test(contentType) || firstText.charAt(0) === '<') {
      throw new Error(audioPlayerText('player.error.audioFileInvalid', {
        path: filepath,
        url: response.url,
        contentType: contentType || audioPlayerText('player.error.noContentType')
      }));
    }

    try {
      return await this._audioCtx.decodeAudioData(arrayBuffer);
    } catch (error) {
      throw new Error(audioPlayerText('player.error.audioFileDecode', {
        path: filepath,
        url: response.url,
        contentType: contentType || audioPlayerText('player.error.noContentType'),
        message: error.message || String(error)
      }));
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
        const errorMessage = window.BaraBeatI18n && typeof window.BaraBeatI18n.t === 'function'
          ? window.BaraBeatI18n.t('player.error.loading', { message: error.message || '' })
          : String(error.message || '');
        const errorParagraph = document.createElement('p');
        errorParagraph.textContent = errorMessage;
        loadingIndicator.textContent = '';
        loadingIndicator.appendChild(errorParagraph);
      }
      throw error;
    }

    if (loadingIndicator) {
      loadingIndicator.style.display = 'none';
    }
  }

  async ensureSoundFiles(soundFiles) {
    const files = Array.isArray(soundFiles) ? soundFiles : [];
    for (let i = 0; i < files.length; i++) {
      const filepath = files[i];
      const name = this.getSoundName(filepath);
      if (Object.prototype.hasOwnProperty.call(this._snd, name)) {
        continue;
      }
      if (this._sounds.indexOf(filepath) === -1) {
        this._sounds.push(filepath);
      }
      const audioBuffer = await this.getFile(filepath);
      this._snd[name] = audioBuffer;
    }
  }

  play(name, time_i, gainMultiplier = 1) {
    if (Object.prototype.hasOwnProperty.call(this._snd, name)) {
      this.pruneExpiredSources();
      const numericGain = Number(gainMultiplier);
      const finalGain = this._vol * Math.max(0, Number.isFinite(numericGain) ? numericGain : 1);
      if (finalGain <= 0) {
        return;
      }
      const sampleSource = this._audioCtx.createBufferSource();
      sampleSource.buffer = this._snd[name];
      sampleSource.connect(this.getSharedGainNode(finalGain));

      sampleSource.onended = () => this.releaseActiveSource(sampleSource);
      this._activeSources.push(sampleSource);
      const startTime = Math.max(time_i, this._audioCtx.currentTime + 0.02);
      const bufferDuration = Math.max(0, Number(sampleSource.buffer && sampleSource.buffer.duration) || 0);
      this._activeSourceEndTimes.set(sampleSource, startTime + bufferDuration + 0.25);
      sampleSource.start(startTime);
    }
  }

  pruneExpiredSources() {
    const currentTime = Number(this._audioCtx && this._audioCtx.currentTime) || 0;
    this._activeSources.slice().forEach((sampleSource) => {
      const expectedEndTime = this._activeSourceEndTimes.get(sampleSource);
      if (Number.isFinite(expectedEndTime) && expectedEndTime <= currentTime) {
        this.releaseActiveSource(sampleSource);
      }
    });
  }

  getSharedGainNode(gainValue) {
    const normalizedGain = Math.max(0, Number(gainValue) || 0);
    const gainKey = normalizedGain.toFixed(6);
    let gainNode = this._gainNodesByValue.get(gainKey);
    if (gainNode) {
      return gainNode;
    }

    gainNode = this._audioCtx.createGain();
    gainNode.gain.value = normalizedGain;
    gainNode.connect(this._panNode || this._outputNode);
    this._gainNodesByValue.set(gainKey, gainNode);
    return gainNode;
  }

  releaseActiveSource(sampleSource) {
    const sourceIndex = this._activeSources.indexOf(sampleSource);
    if (sourceIndex !== -1) {
      this._activeSources.splice(sourceIndex, 1);
    }
    this._activeSourceEndTimes.delete(sampleSource);

    try {
      sampleSource.onended = null;
      sampleSource.disconnect();
    } catch (error) {
      // Web Audio nodes may already be disconnected.
    }
  }

  stopActiveSources(stopTime) {
    this._activeSources.slice().forEach((sampleSource) => {
      try {
        sampleSource.stop(Math.max(stopTime, this._audioCtx.currentTime));
      } catch (error) {
        // The source may already be stopped; that is harmless here.
      }
      this.releaseActiveSource(sampleSource);
    });
  }

  replaceAudioContext(audioContext) {
    if (!audioContext || audioContext === this._audioCtx) {
      return;
    }

    try {
      this._gainNodesByValue.forEach(function (gainNode) {
        gainNode.disconnect();
      });
      if (this._panNode) {
        this._panNode.disconnect();
      }
      if (this._outputNode) {
        this._outputNode.disconnect();
      }
    } catch (error) {
      // The old graph may already be disconnected.
    }

    this._audioCtx = audioContext;
    this._activeSources = [];
    this._activeSourceEndTimes = new Map();
    this._gainNodesByValue = new Map();
    this._outputNode = this._audioCtx.createGain();
    this._panNode = typeof this._audioCtx.createStereoPanner === 'function'
      ? this._audioCtx.createStereoPanner()
      : null;
    if (this._panNode) {
      this._panNode.pan.value = Math.max(-1, Math.min(1, this._pan));
      this._panNode.connect(this._outputNode);
    }
    this._outputNode.connect(this._audioCtx.destination);
  }

  warmUpSamples() {
    const warmUpTime = this._audioCtx.currentTime + 0.02;

    Object.keys(this._snd).forEach((name) => {
      const sampleSource = this._audioCtx.createBufferSource();
      const mutedGain = this._audioCtx.createGain();

      sampleSource.buffer = this._snd[name];
      mutedGain.gain.value = 0.0001;
      sampleSource.connect(mutedGain).connect(this._audioCtx.destination);
      sampleSource.onended = function () {
        try {
          sampleSource.disconnect();
          mutedGain.disconnect();
        } catch (error) {
          // The warm-up graph may already be disconnected.
        }
      };
      sampleSource.start(warmUpTime);
      sampleSource.stop(warmUpTime + 0.08);
    });
  }
}
