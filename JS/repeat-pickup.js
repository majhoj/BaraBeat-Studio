var BaraBeatRepeatPickup = (function () {
  'use strict';

  function normalizeRanges(repeatRangesInput) {
    if (!Array.isArray(repeatRangesInput)) {
      return [];
    }

    const seenRanges = new Set();
    return repeatRangesInput
      .filter(function (repeatRange) {
        if (!repeatRange) {
          return false;
        }
        const startBar = Number(repeatRange.startBar);
        const endBar = Number(repeatRange.endBar);
        if (!Number.isFinite(startBar) || !Number.isFinite(endBar) || startBar < 1 || endBar < startBar) {
          return false;
        }

        const countValue = repeatRange.count === 'loop' ? 'loop' : Number(repeatRange.count);
        if (countValue !== 'loop' && (!Number.isFinite(countValue) || countValue < 1)) {
          return false;
        }

        const dedupeKey = startBar + ':' + endBar + ':' + countValue;
        if (seenRanges.has(dedupeKey)) {
          return false;
        }
        seenRanges.add(dedupeKey);
        return true;
      })
      .map(function (repeatRange) {
        return {
          startBar: Number(repeatRange.startBar),
          endBar: Number(repeatRange.endBar),
          count: repeatRange.count === 'loop' ? 'loop' : Number(repeatRange.count)
        };
      })
      .sort(function (rangeA, rangeB) {
        if (rangeA.startBar !== rangeB.startBar) {
          return rangeA.startBar - rangeB.startBar;
        }
        return rangeB.endBar - rangeA.endBar;
      });
  }

  function normalizeMarkerList(markerValue) {
    if (Array.isArray(markerValue)) {
      return markerValue.filter(function (marker) {
        return marker !== false && marker !== null && marker !== undefined && marker !== '';
      });
    }
    if (markerValue === false || markerValue === null || markerValue === undefined || markerValue === '') {
      return [];
    }
    return [markerValue];
  }

  function collectRepeatRanges(patternBars) {
    if (!Array.isArray(patternBars) || patternBars.length === 0) {
      return [];
    }

    const repeatBoundaries = new Array(patternBars.length + 1).fill(null).map(function (_, boundaryIndex) {
      return {
        index: boundaryIndex,
        startMarkers: [],
        endMarkers: []
      };
    });

    patternBars.forEach(function (bar, barIndex) {
      const repeatInfo = bar && bar.repeat ? bar.repeat : {};
      normalizeMarkerList(repeatInfo.start).forEach(function (marker) {
        repeatBoundaries[barIndex].startMarkers.push({
          boundaryIndex: barIndex,
          count: marker
        });
      });
      normalizeMarkerList(repeatInfo.end).forEach(function (marker) {
        repeatBoundaries[barIndex + 1].endMarkers.push({
          boundaryIndex: barIndex + 1,
          count: marker
        });
      });
    });

    const repeatRanges = [];
    const repeatStartStack = [];
    repeatBoundaries.forEach(function (boundary) {
      boundary.endMarkers.forEach(function (endMarker) {
        const matchingStartMarker = repeatStartStack.pop();
        if (!matchingStartMarker) {
          return;
        }
        repeatRanges.push({
          startBar: matchingStartMarker.boundaryIndex + 1,
          endBar: endMarker.boundaryIndex,
          count: endMarker.count
        });
      });
      boundary.startMarkers.forEach(function (startMarker) {
        repeatStartStack.push(startMarker);
      });
    });

    return repeatRanges;
  }

  function buildRepeatRanges(patternBars) {
    return normalizeRanges(collectRepeatRanges(patternBars));
  }

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

  return Object.freeze({
    normalizeRanges: normalizeRanges,
    normalizeMarkerList: normalizeMarkerList,
    collectRepeatRanges: collectRepeatRanges,
    buildRepeatRanges: buildRepeatRanges,
    withPickup: withPickup,
    withInternalPickup: withInternalPickup
  });
}());
