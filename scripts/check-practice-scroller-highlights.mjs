import assert from 'node:assert/strict';
import vm from 'node:vm';
import { source, loadFunctions } from './helpers/music-context.mjs';

function cell(target) {
  const classes = new Set(target ? ['is-practice-target'] : []);
  return { classList: {
    contains: name => classes.has(name),
    add: name => classes.add(name),
    remove: name => classes.delete(name)
  } };
}
const accompaniment = [cell(false), cell(false)];
const practice = [cell(true), cell(false)];
const nextPractice = [cell(false), cell(true)];
const lanes = [accompaniment, practice, nextPractice].map(children => ({ children, style: {} }));
const scroller = { hidden: false };
let compact = false;
const state = {
  totalSteps: 2, visualTotalSteps: 2, visualLeadInSteps: 0,
  currentStep: 0, activeStep: -1, activeCells: [], lastStatusUpdateAt: 0
};
const context = vm.createContext({
  window: { performance: { now: () => 10 } },
  document: { getElementById: () => scroller },
  practiceScrollerState: state,
  getPracticeScrollerDom: () => ({ laneEls: lanes }),
  getPracticeScrollerLayout: () => ({ stepWidth: 18, playheadX: 300, laneStartX: 126 }),
  getPracticeScrollerPlaybackSegmentContext: () => null,
  normalizePracticeScrollerPlaybackStep: step => step,
  getRenderedPracticeScrollerStep: step => step,
  getPracticeScrollerPreRollLineSteps: () => 0,
  isPracticeScrollerCompactViewport: () => compact
});
loadFunctions(context, source('JS/practice.js'), ['updatePracticeScrollerPosition']);
context.updatePracticeScrollerPosition(0);
assert(!accompaniment[0].classList.contains('is-current'), 'Accompaniment stays light at the playhead');
assert(practice[0].classList.contains('is-current'), 'Practice target retains its existing emphasis');
assert(!nextPractice[0].classList.contains('is-current'));
assert.equal(state.activeCells.length, 1);
const firstTransform = lanes[0].style.transform;
context.updatePracticeScrollerPosition(0.5);
assert.notEqual(lanes[0].style.transform, firstTransform, 'Sub-step animation still moves directly');
context.updatePracticeScrollerPosition(1);
assert(!accompaniment[1].classList.contains('is-current'));
assert(!practice[0].classList.contains('is-current'), 'Previous target is cleared');
assert(!practice[1].classList.contains('is-current'), 'Former practice track is now accompaniment');
assert(nextPractice[1].classList.contains('is-current'), 'Target follows the active section, not the instrument name');
compact = true;
context.updatePracticeScrollerPosition(0);
assert.equal(state.activeCells.length, 0, 'Compact view keeps its existing non-enlarging behavior');
assert(!nextPractice[1].classList.contains('is-current'));
console.log('Practice scroller: accompaniment stays light, targets remain highlighted, section changes and mobile behavior checked.');
