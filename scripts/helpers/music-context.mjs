import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

export function source(path) {
  return fs.readFileSync(new URL('../../' + path, import.meta.url), 'utf8');
}

export function loadFunctions(context, text, names) {
  for (const name of names) {
    let start = text.indexOf('function ' + name + '(');
    if (text.slice(start - 6, start) === 'async ') start -= 6;
    // Top-level function closing braces are unindented in these source files.
    const end = text.indexOf('\n}', start) + 2;
    assert(start >= 0 && end > start, name + ' missing');
    vm.runInContext(text.slice(start, end), context);
  }
}

export function installTiming(context) {
  const path = new URL('../../JS/timing.js', import.meta.url);
  vm.runInContext(fs.readFileSync(path, 'utf8'), context);
}

export function near(actual, expected, label) {
  assert(Math.abs(actual - expected) < 1e-9, `${label}: expected ${expected}, received ${actual}`);
}
