import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

const nodes = [];
function element(tag) {
  const node = {
    tag, children: [], handlers: {}, attributes: {}, className: '',
    append(...children) { this.children.push(...children); },
    appendChild(child) { this.children.push(child); },
    setAttribute(key, value) { this.attributes[key] = value; },
    addEventListener(type, handler) { this.handlers[type] = handler; },
    remove() { this.removed = true; }
  };
  node.classList = { contains: value => node.className.split(' ').includes(value) };
  nodes.push(node);
  return node;
}
const messages = [];
let histories = 0;
let practiceUpdates = 0;
let metadataUpdates = 0;
const frame = { contentWindow: { postMessage(message, origin) {
  assert.equal(origin, 'http://localhost');
  messages.push(JSON.parse(JSON.stringify(message)));
} } };
const context = vm.createContext({
  timelineState: { quickPlayInstrumentVolumes: {} },
  practiceState: { instrumentVolumes: { Kenkeni: 0 }, instrumentToneVolumes: { Kenkeni: { open: 0 } } },
  practiceTrackInstrumentNames: ['Kenkeni', 'Sangban', 'Djembe_1'],
  practiceInstrumentToneVolumeLabels: { Kenkeni: [{ key: 'open' }] },
  practiceText: key => key,
  getPracticeInstrumentDisplayLabel: name => name,
  recordArrangementHistorySnapshot: () => { histories++; },
  notifyPracticeInstrumentVolumesChanged: () => { practiceUpdates++; },
  updateTimelineMetadataNode: () => { metadataUpdates++; },
  positionPracticeVolumePopover() {},
  window: { location: { origin: 'http://localhost' } },
  document: {
    createElement: element,
    getElementById: id => id === 'sheetQuickPlayFrame' ? frame : nodes.find(node => !node.removed && node.id === id),
    querySelectorAll: () => nodes.filter(node => node.attributes['aria-controls'] === 'practiceInstrumentVolumePopover'),
    body: element('body')
  }
});
loadFunctions(context, source('JS/practice.js'), ['normalizePracticeInstrumentVolume',
  'normalizePracticeInstrumentVolumes', 'closePracticeInstrumentVolumePopover',
  'openInstrumentVolumesPopover', 'openTimelineInstrumentVolumesPopover']);
loadFunctions(context, source('index.php'), ['openSheetQuickPlayInstrumentVolumes', 'sendSheetQuickPlayInstrumentVolumes']);
const anchor = element('button');
anchor.setAttribute('aria-controls', 'practiceInstrumentVolumePopover');
const popup = () => context.document.getElementById('practiceInstrumentVolumePopover');
const row = () => popup().children[1];
function change(value) { row().children[1].handlers.input({ target: { value } }); }

context.openSheetQuickPlayInstrumentVolumes(anchor);
assert.equal(row().children[1].value, 100, 'Practice mute must not initialize quick-play volume');
assert.equal(row().children[0].attributes.role, undefined, 'Quick-play names must not open the practice tone mixer');
assert.equal(anchor.attributes['aria-expanded'], 'true');
change('55');
assert.equal(context.timelineState.quickPlayInstrumentVolumes.Kenkeni, 0.55);
assert.equal(context.practiceState.instrumentVolumes.Kenkeni, 0);
assert.equal(context.practiceState.instrumentToneVolumes.Kenkeni.open, 0);
assert.equal(row().children[2].textContent, '55%');
assert.equal(histories, 0, 'Live mixer changes must not rewrite chooser text through a sheet snapshot');
assert.equal(practiceUpdates, 0, 'Quick play must not notify practice or arrangement players');
assert.equal(metadataUpdates, 1);
assert.deepEqual(messages.at(-1), {
  type: 'barabeat-practice-instrument-volumes', volumes: { Kenkeni: 0.55 }, toneVolumes: {}
});
for (const [value, expected] of [['0', 0], ['250', 2], ['-10', 0]]) {
  change(value);
  assert.equal(messages.at(-1).volumes.Kenkeni, expected);
}
let stoppedClick = false;
popup().children.at(-1).children[0].handlers.click({ stopPropagation() { stoppedClick = true; } });
assert(stoppedClick, 'Reset must not bubble as an outside click after the popup is rebuilt');
assert.equal(row().children[1].value, 100);
assert.deepEqual(messages.at(-1).volumes, {});
assert.equal(context.practiceState.instrumentVolumes.Kenkeni, 0);
assert.equal(histories, 0);
context.openSheetQuickPlayInstrumentVolumes(anchor);
assert.equal(popup(), undefined, 'Clicking the quick-play volume button again closes its popup');
assert.equal(anchor.attributes['aria-expanded'], 'false');

context.openTimelineInstrumentVolumesPopover(anchor);
assert.equal(row().children[1].value, 0, 'Arrangement keeps the original practice mixer');
assert.equal(row().children[0].attributes.role, 'button', 'Arrangement retains tone controls');
change('80');
assert.equal(context.practiceState.instrumentVolumes.Kenkeni, 0.8);
assert.equal(context.timelineState.quickPlayInstrumentVolumes.Kenkeni, undefined);
assert.equal(histories, 1);
assert.equal(practiceUpdates, 1);
console.log('Quick-play mixer: independent defaults, live messages, bounds, reset, click toggle and unchanged arrangement mixer checked.');
