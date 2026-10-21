import assert from 'node:assert/strict';
import fs from 'node:fs';
import { createRequire } from 'node:module';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';
import { verifyWavEncoding, checkPlayerWavLevels, checkCurrentArrangementWav } from './helpers/wav-level-checks.mjs';

const context = vm.createContext({ Blob });
loadFunctions(context, source('Audio/player.html'), ['encodeWavFromAudioBuffer']);
const encode = context.encodeWavFromAudioBuffer;
function audioBuffer(channels, sampleRate = 48000) {
  assert(channels.every(channel => channel.length === channels[0].length));
  return { numberOfChannels: channels.length, length: channels[0].length,
    sampleRate, getChannelData: index => channels[index] };
}
async function verify(channels, expectedGain, sampleRate = 48000) {
  const before = channels.map(channel => channel.slice());
  const result = await verifyWavEncoding(audioBuffer(channels, sampleRate), encode);
  assert.equal(result.gain, expectedGain);
  channels.forEach((channel, index) => assert.deepEqual(channel, before[index], 'Source samples unchanged'));
  return result;
}

await verify([Float32Array.of(0, 0.25, -0.5, 0.125, 0)], 1, 44100);
await verify([Float32Array.of(0, 0.75, -0.125), Float32Array.of(0.5, -0.25, 0)], 1);
await verify([new Float32Array(8), new Float32Array(8)], 1);
await verify([new Float32Array(0), new Float32Array(0)], 1);
// Float64 fixtures represent the exact threshold; real AudioBuffers use Float32.
await verify([Float64Array.of(-0.98, 0.98), Float64Array.of(0.49, -0.49)], 1);
await verify([Float64Array.of(-0.99, 0.99), Float64Array.of(0.495, -0.495)], 0.98 / 0.99);
await verify([Float32Array.of(-1, 1), Float32Array.of(0.5, -0.5)], 0.98);
await verify([Float32Array.of(0, 2, -4, 1), Float32Array.of(0, 0.25, -0.5, 0.125)], 0.98 / 4);
await verify([Float32Array.of(0.25, -0.5, 0), Float32Array.of(7.2392120361328125, -2, 0)],
  0.98 / 7.2392120361328125);
await verify([Float32Array.of(0, -32, 8, 0)], 0.98 / 32);

// A late peak in the other channel must scale earlier samples too, without moving them.
const left = new Float32Array(48000 * 30);
const right = new Float32Array(left.length);
for (let i = 0; i < left.length; i += 24000) {
  left[i] = 0.5;
  right[i] = -0.125;
}
right[right.length - 1] = -8;
const longResult = await verifyWavEncoding(audioBuffer([left, right]), encode);
assert.equal(longResult.gain, 0.98 / 8);
assert.equal(left[0], 0.5);
assert.equal(right[right.length - 1], -8);
console.log('WAV levels: threshold, no upward normalization, exact PCM positions, shared stereo gain, headers, immutable sources and long buffers checked.');

if (process.argv.includes('--browser')) {
  const { chromium } = createRequire(import.meta.url)(process.env.BARABEAT_PLAYWRIGHT_MODULE || 'playwright');
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('dialog', dialog => dialog.dismiss());
    const baseUrl = process.env.BARABEAT_TEST_URL || 'http://127.0.0.1:8877/';
    await page.goto(new URL('app-shell.html', baseUrl).href);
    await page.waitForFunction(() => typeof loadRhythmContent === 'function' && typeof s !== 'undefined');
    const score = fs.readFileSync(new URL('../Noten/Djaa Djembe, Sangban, Dundunba.bbs', import.meta.url), 'utf8');
    await page.evaluate(content => loadRhythmContent('Djaa Djembe, Sangban, Dundunba', content, null,
      { remember: false }), score);
    await page.evaluate(() => openTimelineAudioPlayer(
      buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries)));
    const frame = page.frame({ name: 'timelineAudioFrame' });
    await frame.waitForFunction(() => typeof audioIsReady !== 'undefined' && audioIsReady);
    async function runInPlayer(run) {
      // Test functions execute in the player's lexical scope, using its actual samples and encoder.
      return frame.evaluate(async ({ verifySource, runSource }) => {
        const verify = eval('(' + verifySource + ')');
        const runCheck = eval('(' + runSource + ')');
        return runCheck(verify);
      }, { verifySource: verifyWavEncoding.toString(), runSource: run.toString() });
    }
    const cases = await runInPlayer(checkPlayerWavLevels);
    assert.equal(cases.length, 19);
    for (const name of ['Shekere', 'Dununba muffled']) {
      assert.equal(cases.find(result => result.name === name).gain, 1, name + ' stays unchanged');
    }
    console.table(cases.map(({ name, peak, gain, encodedPeak, clippedBefore, clippedAfter }) =>
      ({ name, peak, gain, encodedPeak, clippedBefore, clippedAfter })));
    const arrangement = await runInPlayer(checkCurrentArrangementWav);
    assert(arrangement.steps > 0 && arrangement.frameCount > 0, 'The complete saved arrangement must render');
    assert.deepEqual(errors, []);
    console.log('Complete Djaa arrangement:', JSON.stringify(arrangement, null, 2));
  } finally {
    await browser.close();
  }
}
