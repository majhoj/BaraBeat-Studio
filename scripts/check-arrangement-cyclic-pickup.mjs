import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const player = fs.readFileSync(new URL('../Audio/player.html', import.meta.url), 'utf8');
const context = vm.createContext({
  isPracticeMode: false,
  isSheetQuickPlayMode: false,
  rhythmType: 'tenaer',
  MAX_EXPANDED_BARS: 2000,
  trackInstrumentNames: ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'],
  timelineBassTargets: ['Kenkeni', 'Sangban', 'Doundoun'],
  steuerung: {},
  instrument: ''
});
// Execute the actual pattern/section builders without the browser and audio bootstrap.
vm.runInContext(player.slice(player.indexOf('function sanitizeRepeatRanges('),
  player.indexOf('const repeatRanges = sanitizeRepeatRanges(')), context);
vm.runInContext(player.slice(player.indexOf('function getFeelOffsetSeconds('),
  player.indexOf('const flatBars = isTimelineMode')), context);
const lengthStart = player.indexOf('function getSectionLength(');
vm.runInContext(player.slice(lengthStart, player.indexOf('\nfunction ', lengthStart + 1)), context);

function bar(values, controls = []) {
  const notes = new Array(24).fill('f');
  for (const [step, note] of Object.entries(values)) notes[Number(step)] = note;
  return { notes, controls, repeat: { start: [], end: [] } };
}
function pattern(id, label, bars) {
  return { id, sourceKey: id, label, labelName: id, bars };
}
function entry(patternId, blockId = patternId, extra = {}) {
  return { patternId, blockId, targetInstruments: ['Djembe_1'], ...extra };
}
function notes(section, target = 'Djembe_1') {
  return Array.from(section.trackNotes[target]);
}
function build(patterns, entries, extra = {}) {
  return context.buildTimelineSections({ PatternLibrary: patterns, TimelineEntries: entries, ...extra });
}

const call = pattern('Call', 'Call', [bar({ 0: 'tone', 2: 'tone', 6: 'slap', 18: 'slap' })]);
const flam = pattern('Flam auf Eins', 'Solo', [bar({ 0: 'slap_flam' })]);
// Djaa Djembe, source bar 18: one full cyclic bar, IN on step 8, six timeline entries.
const warmupBar = bar({
  0: 'tone', 2: 'slap', 4: 'slap', 8: 'slap', 10: 'slap', 12: 'tone',
  14: 'slap', 16: 'slap', 18: 'bass', 20: 'slap', 22: 'slap'
}, [{ type: 'in', stepIndex: 8 }]);
const warmup = pattern('Echauffement', 'Echauffement', [warmupBar]);
const original = JSON.stringify([call, flam, warmup]);

for (const repeatCount of [1, 6]) {
  const sections = build([call, flam, warmup], [entry(call.id), entry(flam.id),
    ...Array.from({ length: repeatCount }, () => entry(warmup.id))],
  { TimelineTotalBars: repeatCount + 2 });
  const warmupSections = sections.filter(section => section.label === 'Echauffement');
  assert.equal(warmupSections.length, repeatCount);
  assert(warmupSections.every(section => section.length === 24),
    'Every Echauffement occurrence must keep its full bar after the pickup');
  assert.deepEqual(notes(sections[0]), call.bars[0].notes, 'Call must remain unchanged');
  const expectedFlam = flam.bars[0].notes.slice();
  expectedFlam.splice(8, 16, ...warmupBar.notes.slice(8));
  assert.deepEqual(notes(sections[1]), expectedFlam, 'The IN must follow the Flam in its bar');
  for (const section of warmupSections) assert.deepEqual(notes(section), warmupBar.notes);
  assert.equal(sections.length, repeatCount + 2, 'No silent filler may replace the missing repetitions');
  assert.equal(sections.reduce((total, section) => total + section.playbackLength, 0), (repeatCount + 2) * 24);
  context.steuerung = {};
  sections.forEach(context.appendSectionToSteuerung);
  assert.deepEqual(Array.from(context.steuerung.Djembe_1.noten), [
    ...call.bars[0].notes, ...expectedFlam, ...Array(repeatCount).fill(warmupBar.notes).flat()
  ], 'The final player track must contain every repetition');
}
assert.equal(JSON.stringify([call, flam, warmup]), original, 'Source patterns must not be modified');

// The same notation must work for a freely named solo, not only for Echauffement.
const renamed = { ...warmup, id: 'Freier Name', label: 'Solo' };
const renamedSections = build([flam, renamed], [entry(flam.id), entry(renamed.id)]);
assert.deepEqual(notes(renamedSections[1]), warmupBar.notes);

// Parallel and repeated parallel groups take different branches of the section builder.
const bass = pattern('Sangban', 'Begleitung', [bar({ 0: 'Open', 12: 'Open' })]);
for (const repeatCount of [1, 3]) {
  const entries = [entry(flam.id)];
  for (let i = 0; i < repeatCount; i++) {
    entries.push(entry(warmup.id, 'parallel', { parallelGroupId: 'parallel' }),
      entry(bass.id, 'parallel', { parallelGroupId: 'parallel', targetInstruments: ['Sangban'] }));
  }
  const sections = build([flam, warmup, bass], entries);
  assert.equal(sections.length, repeatCount + 1);
  for (const section of sections.slice(1)) {
    assert.deepEqual(notes(section), warmupBar.notes);
    assert.deepEqual(notes(section, 'Sangban'), bass.bars[0].notes);
  }
}

const withOut = pattern('Echauffement mit OUT', 'Echauffement', [
  { ...warmupBar, controls: [...warmupBar.controls, { type: 'out', stepIndex: 18 }] }
]);
const outSections = build([flam, withOut], [entry(flam.id), entry(withOut.id), entry(withOut.id)]);
assert.deepEqual(notes(outSections[1]), warmupBar.notes, 'OUT must not shorten earlier repetitions');
assert.deepEqual(notes(outSections[2]), warmupBar.notes.map((note, step) => step > 18 ? 'f' : note),
  'The final OUT still applies, without discarding the whole bar');

// A separate pickup bar before a main bar must still be consumed exactly once.
const separatePickup = pattern('Solo mit Auftakttakt', 'Solo', [
  bar({ 22: 'slap' }, [{ type: 'in', stepIndex: 22 }]), bar({ 0: 'bass', 12: 'tone' })
]);
const pickupSections = build([flam, separatePickup], [entry(flam.id), entry(separatePickup.id)]);
assert.equal(pickupSections[0].trackNotes.Djembe_1[22], 'slap');
assert.deepEqual(notes(pickupSections[1]), separatePickup.bars[1].notes);

const noIn = pattern('Ohne IN', 'Echauffement', [{ ...warmupBar, controls: [] }]);
const noInSections = build([flam, noIn], [entry(flam.id), entry(noIn.id)]);
assert.deepEqual(notes(noInSections[0]), flam.bars[0].notes);
assert.deepEqual(notes(noInSections[1]), warmupBar.notes);

// Soli nach Okas: Sangban / Variation 2 has OUT at 18 and a cyclic IN at 22.
const sangbanBase = pattern('Sangban Begleitung', 'Begleitung', [bar({
  0: 'Bell_Open', 4: 'Bell', 8: 'Bell_Muffled', 12: 'Bell_Muffled',
  16: 'Bell', 18: 'Bell_Open', 22: 'Bell'
})]);
const variation2 = pattern('Variation 2', 'Variation 2', [bar({
  2: 'Bell_Open', 4: 'Bell_Open', 8: 'Bell_Open', 12: 'Bell_Open',
  16: 'Bell', 18: 'Bell_Open', 22: 'Bell_Open'
}, [{ type: 'out', stepIndex: 18 }, { type: 'in', stepIndex: 22 }])]);
const sangbanEntry = (source, blockId = source.id) => entry(source.id, blockId, { targetInstruments: ['Sangban'] });
const variationSections = build([call, sangbanBase, variation2], [
  entry(call.id), sangbanEntry(sangbanBase, ''),
  sangbanEntry(variation2), sangbanEntry(variation2),
  sangbanEntry(sangbanBase, 'return'), sangbanEntry(sangbanBase, 'return')
]);
assert.equal(variationSections.length, 6);
const baseWithIn = sangbanBase.bars[0].notes.slice();
baseWithIn[22] = 'Bell_Open';
assert.deepEqual(notes(variationSections[1], 'Sangban'), baseWithIn,
  'Variation 2 IN must sound on the last beat of the preceding accompaniment, even after its own OUT');
assert.deepEqual(notes(variationSections[2], 'Sangban'), variation2.bars[0].notes,
  'The first variation cycle must keep its pickup into the second');
assert.deepEqual(notes(variationSections[3], 'Sangban'),
  variation2.bars[0].notes.map((note, step) => step > 18 ? 'f' : note),
  'The final variation cycle must end at OUT without another pickup');
for (const section of variationSections.slice(4)) assert.deepEqual(notes(section, 'Sangban'), sangbanBase.bars[0].notes);
assert.deepEqual(notes(variationSections[0]), call.bars[0].notes);

console.log('Arrangement: cyclic IN after Call/Flam, Variation 2 OUT/IN, full repetitions, parallel tracks and separate pickup bars checked.');
