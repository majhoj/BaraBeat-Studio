// Self-contained so the same assertions can run in Node or in the real player frame.
export async function verifyWavEncoding(audioBuffer, encode) {
  function check(condition, message) {
    if (!condition) throw new Error(message);
  }
  function fingerprint(data) {
    const words = new Uint32Array(data.buffer, data.byteOffset, data.byteLength / 4);
    let hash = 2166136261;
    for (let i = 0; i < words.length; i++) hash = Math.imul(hash ^ words[i], 16777619);
    return hash;
  }
  const frameCount = audioBuffer.length;
  const channelCount = audioBuffer.numberOfChannels;
  const channels = Array.from({ length: channelCount }, (_, ch) => audioBuffer.getChannelData(ch));
  const fingerprints = channels.map(fingerprint);
  let peak = 0;
  let squaredSum = 0;
  let clippedBefore = 0;
  for (const data of channels) {
    for (let i = 0; i < frameCount; i++) {
      const value = Math.abs(data[i]);
      peak = Math.max(peak, value);
      squaredSum += value * value;
      if (value > 1) clippedBefore++;
    }
  }
  const gain = peak > 0.98 ? 0.98 / peak : 1;
  let channelReads = 0;
  const startTime = performance.now();
  const blob = encode({
    numberOfChannels: channelCount, length: frameCount, sampleRate: audioBuffer.sampleRate,
    getChannelData(ch) { channelReads++; return channels[ch]; }
  });
  const encodeMs = performance.now() - startTime;
  check(channelReads === channelCount, 'Read each source channel once, without copying it');
  const view = new DataView(await blob.arrayBuffer());
  check(blob.type === 'audio/wav', 'Keep the WAV MIME type');
  check(view.byteLength === 44 + frameCount * channelCount * 2, 'Keep all frames and channels');
  check(view.getUint32(0, false) === 0x52494646 && view.getUint32(8, false) === 0x57415645, 'RIFF/WAVE header');
  check(view.getUint32(4, true) === view.byteLength - 8, 'RIFF byte count');
  check(view.getUint16(20, true) === 1 && view.getUint16(34, true) === 16, '16-bit integer PCM');
  check(view.getUint16(22, true) === channelCount, 'Channel count');
  check(view.getUint32(24, true) === audioBuffer.sampleRate, 'Sample rate');
  check(view.getUint32(28, true) === audioBuffer.sampleRate * channelCount * 2, 'Byte rate');
  check(view.getUint16(32, true) === channelCount * 2, 'Block alignment');
  check(view.getUint32(40, true) === frameCount * channelCount * 2, 'PCM byte count');
  let encodedPeak = 0;
  let clippedAfter = 0;
  for (let ch = 0; ch < channelCount; ch++) {
    const data = channels[ch];
    for (let i = 0; i < frameCount; i++) {
      const scaled = data[i] * gain;
      const pcm = view.getInt16(44 + (i * channelCount + ch) * 2, true);
      const expected = Math.trunc(scaled * (scaled < 0 ? 32768 : 32767));
      if (pcm !== expected) throw new Error('PCM must preserve frame position and the common gain at ' + ch + ':' + i);
      encodedPeak = Math.max(encodedPeak, Math.abs(pcm / (pcm < 0 ? 32768 : 32767)));
      if (pcm === 32767 || pcm === -32768) clippedAfter++;
    }
    check(fingerprint(data) === fingerprints[ch], 'Rendered source samples must remain unchanged');
  }
  check(encodedPeak <= 0.98, 'Encoded peak must not exceed the target');
  check(clippedAfter === 0, 'No hard-clipped PCM samples');
  check(gain > 0 && gain <= 1, 'Never normalize upwards');
  return { peak, rms: Math.sqrt(squaredSum / (frameCount * channelCount || 1)),
    gain, attenuationDb: 20 * Math.log10(gain), encodedPeak, clippedBefore, clippedAfter,
    frameCount, channelCount, encodeMs };
}

// Run inside a loaded Audio/player.html frame; no real-time sound or downloads.
export async function checkPlayerWavLevels(verify) {
  const originalVolumes = practiceInstrumentVolumes;
  const originalToneVolumes = practiceInstrumentToneVolumes;
  const originalFeel = getFeelOffsetSeconds;
  const results = [];
  const cases = [
    ['Djembe tone', { Djembe_1: 'tone' }],
    ['Djembe bass', { Djembe_1: 'bass' }],
    ['Djembe slap', { Djembe_1: 'slap' }],
    ['Djembe slap flam', { Djembe_1: 'slap_flam' }],
    ['Djembe tone flam', { Djembe_1: 'tone_flam' }],
    ['Sangban open', { Sangban: 'Open' }],
    ['Sangban muffled', { Sangban: 'Muffled' }],
    ['Sangban bell + open', { Sangban: 'Bell_Open' }],
    ['Dununba open', { Doundoun: 'Open' }],
    ['Dununba muffled', { Doundoun: 'Muffled' }],
    ['Kenkeni bell + open', { Kenkeni: 'Bell_Open' }],
    ['Ballet Dununs pair', { Dreierbass: 'kenkeni_sangban' }],
    ['Three bass drums + bells', { Kenkeni: 'Bell_Open', Sangban: 'Bell_Open', Doundoun: 'Bell_Open' }],
    ['Three Djembe flams', { Djembe_1: 'slap_flam', Djembe_2: 'slap_flam', Djembe_3: 'slap_flam' }],
    ['Ensemble + Shekere', { Kenkeni: 'Bell_Open', Sangban: 'Bell_Open', Doundoun: 'Bell_Open',
      Djembe_1: 'slap_flam', Djembe_2: 'slap_flam', Djembe_3: 'slap_flam' }, 1, 0, true],
    ['Two simultaneous Djembe tones', { Djembe_1: 'tone' }, 2],
    ['Dense Sangban', { Sangban: 'Open' }, 16, 0.0625],
    ['Dense Djembe flams', { Djembe_1: 'slap_flam' }, 16, 0.125],
    ['Shekere', {}, 1, 0, true]
  ];
  try {
    practiceInstrumentVolumes = normalizeInstrumentVolumes({});
    practiceInstrumentToneVolumes = normalizeInstrumentToneVolumes({});
    getFeelOffsetSeconds = () => 0;
    await ensurePracticeTrackSoundsLoaded(Object.keys(practiceInstrumentInstances));
    if (!practiceCountInBuffer) throw new Error('Shekere sample required for the WAV tests');
    for (const [name, notes, count = 1, spacing = 0, shekere = false] of cases) {
      const context = new OfflineAudioContext(2, 48000 * 5, 48000);
      const hands = { Djembe_1: {}, Djembe_2: {}, Djembe_3: {} };
      const playback = note => note ? { note, handMode: 'auto', sectionKey: 'wav-test', stepIndex: 0 } : null;
      for (let i = 0; i < count; i++) {
        const time = 0.25 + i * spacing;
        scheduleNoteToDestination(notes.Kenkeni || null, notes.Sangban || null, notes.Doundoun || null,
          notes.Dreierbass || null, playback(notes.Djembe_1), playback(notes.Djembe_2), playback(notes.Djembe_3),
          time, 1, context, context.destination, hands, 120);
        if (shekere) scheduleShekereHit(time - 0.018, 0.55, context);
      }
      const result = await verify(await context.startRendering(), encodeWavFromAudioBuffer);
      if (result.peak === 0) throw new Error('Missing audio in case: ' + name);
      results.push({ name, ...result });
    }
  } finally {
    practiceInstrumentVolumes = originalVolumes;
    practiceInstrumentToneVolumes = originalToneVolumes;
    getFeelOffsetSeconds = originalFeel;
  }
  return results;
}

export async function checkCurrentArrangementWav(verify) {
  const originalEncode = encodeWavFromAudioBuffer;
  const originalDownload = downloadBlob;
  let verification;
  let download;
  try {
    encodeWavFromAudioBuffer = buffer => {
      let blob;
      verification = verify(buffer, input => {
        blob = originalEncode(input);
        return blob;
      });
      return blob;
    };
    downloadBlob = (blob, filename) => { download = { bytes: blob.size, filename }; };
    await exportCurrentArrangementAsWav();
    if (!verification || !download) throw new Error('Export must render and deliver a WAV');
    const result = await verification;
    if (download.bytes !== 44 + result.frameCount * result.channelCount * 2) {
      throw new Error('The exported WAV must contain all rendered frames');
    }
    return { ...result, ...download, steps: getExportStepCount(), bpm: tempo };
  } finally {
    encodeWavFromAudioBuffer = originalEncode;
    downloadBlob = originalDownload;
  }
}
