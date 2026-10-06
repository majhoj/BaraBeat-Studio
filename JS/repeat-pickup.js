var BaraBeatRepeatPickup = (function () {
  'use strict';

  function playableLength(bar) {
    const length = Array.isArray(bar && bar.notes) ? bar.notes.length : 0;
    return (Array.isArray(bar && bar.controls) ? bar.controls : []).reduce(function (end, control) {
      return control && control.type === 'shortbar'
        ? Math.min(end, Math.max(0, Math.round(Number(control.stepIndex) || 0)))
        : end;
    }, length);
  }

  function withPickup(previousBar, repeatedBars) {
    if (!previousBar || !Array.isArray(repeatedBars) || repeatedBars.length === 0) {
      return previousBar;
    }
    const inBarIndex = repeatedBars.findIndex(function (bar) {
      return (Array.isArray(bar && bar.controls) ? bar.controls : []).some(function (control) {
        return control && control.type === 'in';
      });
    });
    // Only a cyclic IN in the repeat's final bar leads into this internal section.
    // A separate opening pickup bar is handled by the existing mode-specific logic.
    if (inBarIndex !== repeatedBars.length - 1) {
      return previousBar;
    }
    const pickupBar = repeatedBars[inBarIndex];
    const inStep = Math.min.apply(null, pickupBar.controls.filter(function (control) {
      return control && control.type === 'in';
    }).map(function (control) { return Math.max(0, Math.round(Number(control.stepIndex) || 0)); }));
    const pickupLength = playableLength(pickupBar);
    const hostLength = playableLength(previousBar);
    if (inStep <= 0 || inStep >= pickupLength || hostLength <= 0 ||
        (previousBar.instrument && pickupBar.instrument && previousBar.instrument !== pickupBar.instrument)) {
      return previousBar;
    }
    const notes = previousBar.notes.slice();
    const noteSources = Object.assign({}, previousBar.playbackNoteSources);
    for (let step = inStep; step < pickupLength; step++) {
      const targetStep = hostLength - pickupLength + step;
      const note = pickupBar.notes[step];
      if (targetStep < 0 || !note || note === 'f') {
        continue;
      }
      notes[targetStep] = note;
      noteSources[targetStep] = pickupBar.playbackNoteSources && pickupBar.playbackNoteSources[step] || {
        sourceBarIndex: pickupBar.sourceBarIndex,
        sourceStepIndex: step
      };
    }
    // Repeated occurrences can share the same source object; never change it in place.
    return Object.assign({}, previousBar, { notes: notes, playbackNoteSources: noteSources });
  }

  function withInternalPickup(previousBar, bars, sectionStartIndex, nextBarIndex) {
    const nextBar = bars[nextBarIndex];
    const hasIn = function (bar) {
      return (Array.isArray(bar && bar.controls) ? bar.controls : []).some(function (control) {
        return control && control.type === 'in';
      });
    };
    if (!previousBar || !hasIn(nextBar)) {
      return previousBar;
    }
    // The first IN belongs to the whole section. Only subsequent INs open an internal variation.
    // Restrict the search to this repeat scope so a repeat group's pickup is not applied twice.
    const hasEarlierIn = bars.slice(sectionStartIndex, nextBarIndex).some(function (bar) {
      return bar && (!bar.patternSourceKey || !nextBar.patternSourceKey ||
        bar.patternSourceKey === nextBar.patternSourceKey) && hasIn(bar);
    });
    return hasEarlierIn ? withPickup(previousBar, [nextBar]) : previousBar;
  }

  return Object.freeze({ withPickup: withPickup, withInternalPickup: withInternalPickup });
}());
