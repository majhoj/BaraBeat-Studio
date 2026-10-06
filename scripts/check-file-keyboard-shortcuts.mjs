import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

const text = source('index.php');
const actions = [];
const dialog = { hidden: true };
const saveButton = { disabled: false };
const openButton = { disabled: false };
const listeners = [];
const frameListeners = [];
let finishSave;
let deferSave = false;
let failSave = false;
const frameDocument = { addEventListener: (...args) => frameListeners.push(args) };
const frame = {
  contentDocument: frameDocument,
  addEventListener(name, handler) { this[name] = handler; }
};
const document = {
  activeElement: { blur() { actions.push('blur'); } },
  getElementById: id => ({ fileDialog: dialog, saveFileDialogButton: saveButton, openFileDialogButton: openButton })[id],
  addEventListener: (...args) => listeners.push(args),
  querySelectorAll: () => [frame]
};
const context = vm.createContext({
  document,
  fileShortcutInProgress: false,
  openFileDialog(mode) { actions.push(mode); dialog.hidden = false; },
  async saveCurrentScoreFromMenu() {
    actions.push('save');
    if (failSave) throw new Error('save failure');
    if (deferSave) await new Promise(resolve => { finishSave = resolve; });
  }
});
loadFunctions(context, text, ['handleFileKeyboardShortcut', 'bindFileKeyboardShortcuts']);
function event(options = {}) {
  return { key: 's', metaKey: true, target: { ownerDocument: document },
    preventDefault() { this.prevented = true; }, stopPropagation() { this.stopped = true; }, ...options };
}
for (const options of [
  { key: 'z' }, { key: 'c' }, { key: 'o', metaKey: false },
  { metaKey: false, ctrlKey: true }, { altKey: true }, { shiftKey: true },
  { ctrlKey: true }, { isComposing: true }, { defaultPrevented: true }
]) {
  const input = event(options);
  await context.handleFileKeyboardShortcut(input);
  assert.equal(input.prevented, undefined, 'Other shortcuts remain untouched');
}
assert.deepEqual(actions, []);
for (const key of ['s', 'S']) {
  const input = event({ key });
  await context.handleFileKeyboardShortcut(input);
  assert(input.prevented && input.stopped, 'Browser Save Page is suppressed');
  assert.deepEqual(actions.splice(0), ['blur', 'save'], 'Pending edits commit before the existing save command');
}
const openEvent = event({ key: 'o' });
await context.handleFileKeyboardShortcut(openEvent);
assert(openEvent.prevented && openEvent.stopped, 'Browser Open File is suppressed');
assert.deepEqual(actions.splice(0), ['blur', 'open']);
for (const key of ['o', 's']) {
  await context.handleFileKeyboardShortcut(event({ key }));
  assert.deepEqual(actions, [], 'An open file dialog is not reset or submitted by a shortcut');
}
dialog.hidden = true;
await context.handleFileKeyboardShortcut(event({ repeat: true }));
assert.deepEqual(actions, [], 'Auto-repeat must not save repeatedly');
saveButton.disabled = true;
await context.handleFileKeyboardShortcut(event());
assert.deepEqual(actions, [], 'Disabled menu command cannot be bypassed');
saveButton.disabled = false;

deferSave = true;
const saving = context.handleFileKeyboardShortcut(event());
assert.equal(context.fileShortcutInProgress, true);
await context.handleFileKeyboardShortcut(event());
assert.deepEqual(actions.splice(0), ['blur', 'save'], 'No concurrent shortcut saves');
finishSave();
await saving;
assert.equal(context.fileShortcutInProgress, false);
deferSave = false;
failSave = true;
await assert.rejects(context.handleFileKeyboardShortcut(event()), /save failure/);
assert.equal(context.fileShortcutInProgress, false, 'A failed save does not lock shortcuts');
failSave = false;
actions.length = 0;

context.bindFileKeyboardShortcuts();
assert.equal(listeners[0][0], 'keydown');
assert.equal(listeners[0][1], context.handleFileKeyboardShortcut);
assert.equal(listeners[0][2], true, 'Capture before editor note handlers');
assert.equal(frameListeners[0][1], context.handleFileKeyboardShortcut);
assert.equal(frameListeners[0][2], true);
frame.load();
assert.equal(frameListeners.length, 2, 'Rebind after player navigation');
await frameListeners[0][1](event({ target: { ownerDocument: {
  activeElement: { blur() { actions.push('player-blur'); } }
} } }));
assert.deepEqual(actions, ['player-blur', 'save'], 'Player focus uses the same editor command');
assert(text.includes('    bindFileKeyboardShortcuts();'), 'Shortcuts are wired during UI initialization');
assert(source('app-shell.html').includes('async function handleFileKeyboardShortcut('), 'Offline shell has shortcuts');
console.log('File shortcuts: Cmd+S/O, inline edits, existing dialogs, held keys, concurrent saves, player focus and offline shell checked.');
