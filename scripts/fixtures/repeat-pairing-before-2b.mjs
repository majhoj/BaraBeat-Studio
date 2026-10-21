// Frozen parser implementations before migration step 2b (2026-10-19).
// Test-only oracle: do not update these copies to accommodate production changes.

export const legacyShared = (function () {
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

  function buildRepeatRanges(patternBars) {
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

    return normalizeRanges(repeatRanges);
  }
  return Object.freeze({ normalizeRanges, normalizeMarkerList, buildRepeatRanges });
}());

export const legacyPractice = (function () {
  function getPracticeRepeatMarkerList(markerValue) {
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

  function buildPracticeRepeatRangesFromBars(bars) {
      const safeBars = Array.isArray(bars) ? bars : [];
      const repeatBoundaries = new Array(safeBars.length + 1).fill(null).map(function (_, boundaryIndex) {
          return {
              startMarkers: [],
              endMarkers: [],
              index: boundaryIndex
          };
      });

      safeBars.forEach(function (bar, barIndex) {
          const repeatInfo = bar && bar.repeat ? bar.repeat : {};
          getPracticeRepeatMarkerList(repeatInfo.start).forEach(function (marker) {
              repeatBoundaries[barIndex].startMarkers.push({
                  boundaryIndex: barIndex,
                  count: marker
              });
          });
          getPracticeRepeatMarkerList(repeatInfo.end).forEach(function (marker) {
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

      return repeatRanges.filter(function (repeatRange) {
          const startBar = Number(repeatRange.startBar);
          const endBar = Number(repeatRange.endBar);
          if (!Number.isFinite(startBar) || !Number.isFinite(endBar) || startBar < 1 || endBar < startBar) {
              return false;
          }
          return repeatRange.count === 'loop' || Number.isFinite(Number(repeatRange.count));
      }).map(function (repeatRange) {
          return {
              startBar: Number(repeatRange.startBar),
              endBar: Number(repeatRange.endBar),
              count: repeatRange.count === 'loop' ? 'loop' : Number(repeatRange.count)
          };
      });
  }
  return Object.freeze({ getPracticeRepeatMarkerList, buildPracticeRepeatRangesFromBars });
}());

export const legacyQuickPlay = (function () {
  function buildSheetQuickPlayRepeatRanges(patternBars) {
      const bars = Array.isArray(patternBars) ? patternBars : [];
      const boundaries = new Array(bars.length + 1).fill(null).map(function (_, boundaryIndex) {
          return {
              index: boundaryIndex,
              startMarkers: [],
              endMarkers: []
          };
      });
      const ranges = [];
      const startStack = [];

      bars.forEach(function (bar, barIndex) {
          const repeatInfo = bar && bar.repeat ? bar.repeat : {};
          const startMarkers = Array.isArray(repeatInfo.start) ? repeatInfo.start : (repeatInfo.start ? [repeatInfo.start] : []);
          const endMarkers = Array.isArray(repeatInfo.end) ? repeatInfo.end : (repeatInfo.end ? [repeatInfo.end] : []);
          startMarkers.forEach(function (marker) {
              if (marker !== false && marker !== null && marker !== undefined && marker !== '') {
                  boundaries[barIndex].startMarkers.push(marker);
              }
          });
          endMarkers.forEach(function (marker) {
              if (marker !== false && marker !== null && marker !== undefined && marker !== '') {
                  boundaries[barIndex + 1].endMarkers.push(marker);
              }
          });
      });

      boundaries.forEach(function (boundary) {
          boundary.endMarkers.forEach(function (endMarker) {
              if (endMarker === 'continue') {
                  return;
              }
              const startBoundary = startStack.pop();
              const repeatCount = endMarker === 'loop'
                  ? 1
                  : Math.max(1, Math.round(Number(endMarker) || 1));
              if (startBoundary === undefined || startBoundary === null) {
                  return;
              }
              ranges.push({
                  startBar: startBoundary + 1,
                  endBar: boundary.index,
                  count: repeatCount
              });
          });
          boundary.startMarkers.forEach(function () {
              startStack.push(boundary.index);
          });
      });

      return ranges;
  }
  return Object.freeze({ buildSheetQuickPlayRepeatRanges });
}());
