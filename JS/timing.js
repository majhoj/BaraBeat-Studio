var BaraBeatTiming = (function () {
  'use strict';

  const grids = Object.freeze({
    binaer: Object.freeze({ stepsPerBar: 32, stepsPerBeat: 8 }),
    tenaer: Object.freeze({ stepsPerBar: 24, stepsPerBeat: 6 }),
    neunaer: Object.freeze({ stepsPerBar: 18, stepsPerBeat: 6 })
  });

  function getGrid(rhythmType) {
    return grids[rhythmType] || grids.binaer;
  }

  function getBaseStepDuration(rhythmType, tempo) {
    return 60 / Math.max(1, Number(tempo) || 100) / getGrid(rhythmType).stepsPerBeat;
  }

  function buildSwingStepOffsets(profileValues) {
    const anchors = Array.isArray(profileValues) ? profileValues : [];
    if (anchors.length === 0) {
      return [0, 1];
    }
    const anchorStep = 1 / anchors.length;
    const anchorFractions = anchors.map(function (value, index) {
      return index * anchorStep + (Number(value) || 0) / 100 * anchorStep;
    });
    anchorFractions.push(1 + (Number(anchors[0]) || 0) / 100 * anchorStep);
    const offsets = [];
    for (let index = 0; index < anchorFractions.length - 1; index++) {
      offsets.push(anchorFractions[index]);
      offsets.push(anchorFractions[index] + (anchorFractions[index + 1] - anchorFractions[index]) / 2);
    }
    offsets.push(anchorFractions[anchorFractions.length - 1]);
    return offsets;
  }

  function getStepInterval(rhythmType, tempo, rhythmStep, swingOffsets) {
    const stepsPerBeat = getGrid(rhythmType).stepsPerBeat;
    const baseDuration = getBaseStepDuration(rhythmType, tempo);
    if (!swingOffsets) {
      return Math.max(0.001, baseDuration);
    }
    // Negative positions are pickups in the preceding beat, not a new beat one.
    const phase = ((rhythmStep % stepsPerBeat) + stepsPerBeat) % stepsPerBeat;
    return Math.max(0.001, baseDuration * stepsPerBeat * (swingOffsets[phase + 1] - swingOffsets[phase]));
  }

  return Object.freeze({ getGrid, getBaseStepDuration, buildSwingStepOffsets, getStepInterval });
}());
