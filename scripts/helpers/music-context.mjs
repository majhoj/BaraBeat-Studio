import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

export function source(path) {
  return fs.readFileSync(new URL('../../' + path, import.meta.url), 'utf8');
}

export function loadFunctions(context, text, names) {
  for (const name of names) {
    const start = text.indexOf('function ' + name + '(');
    const end = text.indexOf('\nfunction ', start + 1);
    assert(start >= 0 && end > start, name + ' missing');
    vm.runInContext(text.slice(start, end).split('\nrecalculateOrderedSectionTiming();')[0], context);
  }
}

export function installTiming(context) {
  const path = new URL('../../JS/timing.js', import.meta.url);
  if (fs.existsSync(path)) vm.runInContext(fs.readFileSync(path, 'utf8'), context);
}

export function near(actual, expected, label) {
  assert(Math.abs(actual - expected) < 1e-9, `${label}: expected ${expected}, received ${actual}`);
}
