import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../index.php', import.meta.url), 'utf8');
const styles = fs.readFileSync(new URL('../CSS/index_style.css', import.meta.url), 'utf8');
function extractFunction(name) {
  const start = source.indexOf('function ' + name + '(');
  const end = source.indexOf('\nfunction ', start + 1);
  assert(start >= 0 && end > start, name + ' missing');
  return source.slice(start, end);
}

let mobile = false;
let scoreTitle = 'Djia Billy Konate 01';
let svgBounds = { left: 398, right: 1522, top: 70, width: 1124 };
let menuBottom = 58;
let editingInput = null;
let historyCount = 0;
const classes = new Set();
const controls = {
  style: {}, offsetWidth: 206, clientLeft: 1,
  classList: {
    remove(name) { classes.delete(name); },
    toggle(name, on) { if (on) classes.add(name); else classes.delete(name); }
  }
};
const title = {
  id: 'sheetQuickPlayTitle', textContent: '', title: '',
  getBoundingClientRect: () => ({ left: 438, top: 74, width: 650, height: 24 })
};
const desktopTempo = { value: '81' };
const desktopPlay = { dataset: { playing: 'true' } };
const elements = { sheetQuickPlayControls: controls, sheetQuickPlayTitle: title,
  sheetQuickPlayTempo: desktopTempo, sheetQuickPlayButton: desktopPlay,
  appMenuBar: { getBoundingClientRect: () => ({ bottom: menuBottom }) } };
const context = vm.createContext({
  window: { pageXOffset: 0, pageYOffset: 0, getComputedStyle: () => ({ paddingLeft: '8px' }) },
  document: {
    documentElement: { clientWidth: 1920, scrollLeft: 0, scrollTop: 0 },
    getElementById: id => id === 'rhythmTitleEditor' ? editingInput : elements[id],
    createElement: () => ({
      style: {}, handlers: {}, setAttribute() {}, focus() {}, select() {},
      addEventListener(type, handler) { this.handlers[type] = handler; },
      remove() { editingInput = null; }
    }),
    body: { appendChild: input => { editingInput = input; } }
  },
  s: { node: { getBoundingClientRect: () => svgBounds } },
  titel: {
    attr(value) {
      if (typeof value === 'string') return scoreTitle;
      scoreTitle = value.text;
    },
    node: { getBoundingClientRect: () => ({ left: svgBounds.left + 100, top: svgBounds.top + 44, width: 280, height: 28 }) }
  },
  isMobilePracticeViewport: () => mobile,
  defaultRhythmTitle: 'Rhythmusname',
  isDefaultTitleText: value => !value || value === 'Rhythmusname',
  recordHistorySnapshot: () => { historyCount++; },
  uiText: () => 'Rhythmusname'
});
for (const name of ['setRhythmTitle', 'updateSheetQuickPlayTitle', 'positionSheetQuickPlayControls']) {
  vm.runInContext(extractFunction(name), context);
}
const editorStart = source.indexOf('function startInlineRhythmTitleEdit(');
vm.runInContext(source.slice(editorStart, source.indexOf('\nedit_title =', editorStart)), context);
function position() { vm.runInContext('positionSheetQuickPlayControls()', context); }

position();
assert(!classes.has('is-sticky'), 'Initial score header keeps its existing position');
assert.equal(controls.style.top, '114px');
assert.equal(controls.style.left, '1276px');
assert.equal(title.textContent, scoreTitle);

for (const pageScroll of [49, 50, 100, 1200, 2400, 3600]) {
  context.window.pageYOffset = pageScroll;
  svgBounds.top = 70 - pageScroll;
  position();
  assert(classes.has('is-sticky'), 'Header must remain pinned across all score pages');
  assert.equal(controls.style.top, '66px', 'Pinned header stays below the menu');
  assert.equal(controls.style.left, '489px');
  assert.equal(controls.style.width, '993px');
  assert.equal(parseFloat(controls.style.left) + controls.clientLeft + 8,
    context.titel.node.getBoundingClientRect().left,
    'Pinned title keeps the horizontal position of the original SVG title');
}
assert.equal(elements.sheetQuickPlayTempo, desktopTempo);
assert.equal(elements.sheetQuickPlayButton, desktopPlay);
assert.equal(desktopTempo.value, '81');
assert.equal(desktopPlay.dataset.playing, 'true', 'Scrolling must not reset playback');

context.document.documentElement.clientWidth = 800;
svgBounds = { left: -100, right: 1024, top: -1800, width: 1124 };
position();
assert.equal(controls.style.left, '8px');
assert.equal(controls.style.width, '784px', 'Horizontal scrolling must keep the controls within the window');
menuBottom = 94;
position();
assert.equal(controls.style.top, '102px', 'Header follows the actual menu height');

mobile = true;
position();
assert(!classes.has('is-sticky'), 'Mobile retains its separate existing header');
assert.equal(controls.style.width, '');
mobile = false;
context.window.pageYOffset = 0;
context.document.documentElement.clientWidth = 1920;
menuBottom = 58;
svgBounds = { left: 398, right: 1522, top: 70, width: 1124 };
position();
assert(!classes.has('is-sticky'), 'Returning to the top restores the original header');
assert.equal(controls.style.top, '114px');
assert.equal(controls.style.left, '1276px');
assert.equal(controls.style.width, '');

context.anchor = title;
context.window.pageYOffset = 2400;
vm.runInContext('startInlineRhythmTitleEdit(anchor)', context);
assert.equal(editingInput.style.position, 'fixed');
assert.equal(editingInput.style.top, '70px', 'Pinned title editor must not be moved by the document scroll offset');
assert.equal(editingInput.style.width, '658px', 'Title editor stays within the title area');
editingInput.value = ' Neuer Rhythmus ';
editingInput.handlers.keydown({ key: 'Enter', preventDefault() {} });
assert.equal(title.textContent, 'Neuer Rhythmus');
assert.equal(scoreTitle, 'Neuer Rhythmus');
assert.equal(historyCount, 1);
assert.equal(editingInput, null);
vm.runInContext('startInlineRhythmTitleEdit(anchor)', context);
editingInput.value = 'Verwerfen';
editingInput.handlers.keydown({ key: 'Escape', preventDefault() {} });
assert.equal(scoreTitle, 'Neuer Rhythmus');
assert.equal(historyCount, 1);
context.window.pageYOffset = 0;
vm.runInContext('startInlineRhythmTitleEdit()', context);
assert.equal(editingInput.style.position, 'absolute', 'Original SVG title editing remains unchanged');
assert.equal(editingInput.style.top, '110px');
editingInput.handlers.blur();

assert.equal((source.match(/id="sheetQuickPlayButton"/g) || []).length, 1, 'Only one desktop Play button');
assert.equal((source.match(/id="sheetQuickPlayTempo"/g) || []).length, 1, 'Only one desktop BPM input');
assert(styles.includes('.sheet-quick-play-controls.is-sticky'));
assert(styles.includes('.sheet-quick-play-controls.is-sticky .sheet-quick-play-title'));
const svgTitleSize = source.match(/class: 'sheet-rhythm-title', 'font-size': (\d+)/);
const stickyTitleRule = styles.match(/\.sheet-quick-play-controls \.sheet-quick-play-title\s*\{([^}]+)\}/);
assert(svgTitleSize && stickyTitleRule, 'Both title styles must exist');
assert(stickyTitleRule[1].includes('font: 700 ' + svgTitleSize[1] + 'px var(--ui-font);'),
  'Pinned title must retain the bold font and size of the original SVG title');
assert.match(styles, /--ui-font:\s*sans-serif;/, 'Both titles use the same font family');
assert(source.includes("class: 'sheet-rhythm-title'"), 'Only the rhythm title receives the dedicated marker, not legend elements sharing the basis ID');
assert.match(styles, /@media screen\s*\{\s*\.sheet-quick-play-controls\.is-sticky ~ #myRect1 \.sheet-rhythm-title\s*\{\s*visibility: hidden;/,
  'Hide the original title only on screen while its sticky counterpart is shown');
assert(!source.includes("titel.attr({ visibility:"), 'Hiding the original must not write an invisible title into saved SVG content');
console.log('Sheet quick play: matching title fonts, aligned title, screen-only title hiding, sticky transition, viewport, mobile reset, editing and control identity checked.');
