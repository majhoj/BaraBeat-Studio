import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../JS/timeline.js', import.meta.url), 'utf8');
function extractFunction(name) {
  const start = source.indexOf('function ' + name + '(');
  const end = source.indexOf('\nfunction ', start + 1);
  assert(start >= 0 && end > start, name + ' missing');
  return source.slice(start, end);
}

class Element {
  constructor(tagName) {
    this.tagName = tagName;
    this.className = '';
    this.children = [];
    this.dataset = {};
    this.attributes = {};
    this.listeners = {};
    this.style = { setProperty(name, value) { this[name] = value; } };
    this.classList = { toggle() {} };
    this.scrollLeft = 0;
    this.offsetHeight = 161;
  }
  set innerHTML(value) { assert.equal(value, ''); this.children = []; }
  appendChild(child) { child.parentElement = this; this.children.push(child); }
  append(...children) { children.forEach(child => this.appendChild(child)); }
  setAttribute(name, value) { this.attributes[name] = value; }
  addEventListener(name, handler) { this.listeners[name] = handler; }
  querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
  querySelectorAll(selector) {
    return this.children.flatMap(child => [
      ...(child.className.split(' ').includes(selector.slice(1)) ? [child] : []),
      ...child.querySelectorAll(selector)
    ]);
  }
}

const sequence = new Element('div');
const observers = [];
const rulerBindings = [];
const context = vm.createContext({
  timelineState: { sourcePatterns: [], entries: [], playbackStartBar: 99 },
  timelinePlaybackHighlightedBar: null,
  timelineTrackViewportObserver: null,
  document: { getElementById: () => sequence, createElement: tag => new Element(tag) },
  ResizeObserver: class {
    constructor(callback) { this.callback = callback; observers.push(this); }
    observe(element) { this.element = element; }
    disconnect() { this.disconnected = true; }
  },
  buildPatternDisplayLabelMap: () => ({}),
  buildTimelineDisplayGroups: () => [],
  buildTimelineVisualRows: () => [],
  buildTimelineHorizontalLayout: () => ({
    totalBars: 120, usedTargets: ['Djembe_1', 'Sangban'], clips: [], rows: []
  }),
  normalizeTimelinePlaybackStartBar: value => value,
  timelineText: key => key,
  getTimelineInstrumentLabel: value => value,
  assignTimelineTrackClipLayers: clips => clips,
  bindTimelineRulerBarDropTarget: (element, layout, number) => rulerBindings.push(number),
  bindTimelinePlaybackStartBar() {},
  bindTimelineTrackLaneDrop() {}
});
vm.runInContext(extractFunction('renderTimelineSequence'), context);
vm.runInContext('renderTimelineSequence()', context);
const viewport = sequence.querySelector('.timeline-track-viewport');
const scroll = sequence.querySelector('.timeline-track-scroll');
const content = sequence.querySelector('.timeline-track-content');
const playhead = sequence.querySelector('.timeline-track-playhead');
assert.equal(scroll.parentElement, viewport);
assert.equal(playhead.parentElement, viewport, 'Playhead must not be inside the scrolling content');
assert.equal(content.parentElement, scroll);
assert.equal(playhead.attributes['aria-hidden'], 'true');
assert.equal(playhead.style.height, '161px', 'Line follows actual track content height');
content.offsetHeight = 225;
observers[0].callback();
assert.equal(playhead.style.height, '225px', 'Line adapts to changed row heights');
assert.equal(content.querySelectorAll('.timeline-track-row').length, 2);
assert.equal(content.querySelectorAll('.timeline-track-ruler-bar').length, 120);
assert.deepEqual(rulerBindings, Array.from({ length: 120 }, (_, index) => index + 1),
  'The visual lead-in must not create numbered or droppable bars');
assert.equal(context.timelineState.playbackStartBar, 99);
scroll.scrollLeft = 98 * 108;
vm.runInContext('renderTimelineSequence()', context);
assert(observers[0].disconnected, 'Rebuilding must release the previous height observer');
assert.equal(sequence.querySelector('.timeline-track-scroll').scrollLeft, 98 * 108,
  'Rebuilding must preserve the current scroll position');

let scrollLeft = 0;
const ruler = {
  getBoundingClientRect() {
    const left = 112 + 2 * 108 - scrollLeft;
    return { left, right: left + 120 * 108 };
  }
};
const scrollElement = {
  getBoundingClientRect: () => ({ top: 0, bottom: 200 }),
  querySelector: () => ruler
};
context.clip = { closest: () => scrollElement };
context.layout = { totalBars: 120 };
context.window = { getComputedStyle: () => ({ getPropertyValue: () => '108px' }) };
vm.runInContext(extractFunction('getTimelineTrackPointerBar'), context);
function pointerBar(x) {
  context.x = x;
  return vm.runInContext('getTimelineTrackPointerBar(clip, layout, x, 50)', context);
}
assert.equal(pointerBar(328), 1, 'First drop target starts at the red line');
assert.equal(pointerBar(327), null, 'Empty lead-in is not an extra drop target');
assert.equal(pointerBar(436), 2);
scrollLeft = 98 * 108;
assert.equal(pointerBar(328), 99, 'Pointer mapping follows the scrolled musical grid');
assert.equal(pointerBar(436), 100);

const cards = [{ style: { width: '800px' } }];
const library = { clientWidth: 0, querySelectorAll: () => cards };
context.document = {
  getElementById: () => library,
  documentElement: { style: { setProperty() {} } }
};
vm.runInContext(extractFunction('alignPatternLibraryCardWidths'), context);
vm.runInContext('alignPatternLibraryCardWidths()', context);
assert.equal(cards[0].style.width, '', 'A collapsed library must not acquire a fixed narrow card width');
library.clientWidth = 1100;
vm.runInContext('alignPatternLibraryCardWidths()', context);
assert.equal(cards[0].style.width, '294px', 'Wide library keeps compact fixed-width cards');
library.clientWidth = 220;
vm.runInContext('alignPatternLibraryCardWidths()', context);
assert.equal(cards[0].style.width, '220px', 'Cards still fit a narrower container');

const patternList = new Element('div');
const patterns = [
  { id: 'call', instrument: 'Djembe', sourceInstrument: 'Djembe 1', name: 'Call' },
  { id: 'solo', instrument: 'Djembe', sourceInstrument: 'Djembe 1', name: 'Solo 1' },
  { id: 'solo2', instrument: 'Djembe', sourceInstrument: 'Djembe 2', name: 'Solo 2' },
  { id: 'sangban', instrument: 'Sangban', name: 'Begleitung' },
  { id: 'call2', instrument: 'Djembe', sourceInstrument: 'Djembe 1', name: 'Call 2' }
];
const groups = patterns.map(pattern => ({
  patterns: [pattern], entries: [{ patternId: pattern.id, handMode: 'auto', targetInstruments: [pattern.sourceInstrument || pattern.instrument] }]
}));
context.timelineState = { sourcePatterns: patterns, sourceLibraryGroups: groups, entries: [] };
context.document = { getElementById: () => patternList, createElement: tag => new Element(tag) };
context.buildPatternDisplayLabelMap = () => ({ groups });
context.buildTimelineGroupSummary = () => ({ totalBars: 1 });
context.getTimelineDisplayParts = pattern => ({ main: pattern.sourceInstrument || pattern.instrument, sub: pattern.name });
context.setTimelineActiveDragPayload = payload => { context.dragPayload = payload; };
context.setTimelineCompactDragImage = () => {};
context.setTimelineDragDropTargetsVisible = () => {};
context.nextTimelineBlockId = () => 'new-block';
context.findPatternById = id => patterns.find(pattern => pattern.id === id);
context.cloneTimelineEntryFromPattern = (pattern, options) => ({ patternId: pattern.id, ...options });
context.updateTimelineMetadataNode = () => {};
context.renderTimelinePanel = () => {};
vm.runInContext(extractFunction('renderTimelinePatternLibrary'), context);
const originalPatterns = JSON.stringify(patterns);
vm.runInContext('renderTimelinePatternLibrary()', context);
assert.deepEqual(patternList.children.map(row => row.dataset.instrument),
  ['Djembe 1', 'Djembe 2', 'Sangban', 'Djembe 1'],
  'Each instrument change starts a row without reordering later occurrences');
assert.deepEqual(patternList.children.map(row => row.children.length), [2, 1, 1, 1]);
const libraryCards = patternList.querySelectorAll('.timeline-card');
assert(libraryCards.every(card => card.draggable));
let transferredPayload;
libraryCards[1].listeners.dragstart({ dataTransfer: {
  setData(type, value) { assert.equal(type, 'text/plain'); transferredPayload = JSON.parse(value); }
} });
assert.equal(transferredPayload.entries[0].patternId, 'solo');
assert.equal(transferredPayload.entries[0].targetInstruments[0], 'Djembe 1');
const addButton = libraryCards[1].querySelector('.timeline-card-actions').children[0];
addButton.listeners.click();
assert.equal(context.timelineState.entries[0].patternId, 'solo', 'Plus still appends the chosen pattern');
assert.equal(JSON.stringify(patterns), originalPatterns, 'Grouping must not modify musical data');
context.timelineState.sourceLibraryGroups = [];
vm.runInContext('renderTimelinePatternLibrary()', context);
assert.equal(patternList.querySelectorAll('.timeline-card').length, 5, 'Fallback library is grouped without duplicates');

console.log('Timeline layout: playhead, bar numbering, pointer targets, compact instrument rows, drag and plus checked.');
