import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const scriptDirectory = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(scriptDirectory, '..');
const timelineSource = fs.readFileSync(path.join(projectRoot, 'JS/timeline.js'), 'utf8');
const timelineStyles = fs.readFileSync(path.join(projectRoot, 'CSS/index_style.css'), 'utf8');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

function extractFunction(source, functionName) {
  const start = source.indexOf('function ' + functionName + '(');
  if (start === -1) {
    throw new Error('Funktion fehlt: ' + functionName);
  }
  const nextFunction = source.indexOf('\nfunction ', start + 1);
  return source.slice(start, nextFunction === -1 ? source.length : nextFunction);
}

const patterns = {
  call: { id: 'call', labelType: 'Call', bars: [{}] },
  solo: {
    id: 'solo',
    labelType: 'Solo',
    bars: [
      { controls: [{ type: 'out', stepIndex: 16 }] },
      { controls: [] }
    ]
  },
  solo2: {
    id: 'solo2',
    labelType: 'Solo',
    bars: Array.from({ length: 13 }, (_, barIndex) => {
      const repeatStarts = { 0: true, 4: true, 8: true, 10: true };
      const repeatEnds = { 1: 5, 5: 6, 9: 1, 11: 3 };
      return {
        repeat: {
          start: repeatStarts[barIndex] || false,
          end: repeatEnds[barIndex] || false
        },
        controls: []
      };
    })
  },
  djembe: { id: 'djembe', labelType: 'Begleitung', bars: [{}] },
  sangban: { id: 'sangban', labelType: 'Begleitung', bars: [{}, {}] },
  doundoun: { id: 'doundoun', labelType: 'Begleitung', bars: [{}] }
};
const callGroup = {
  patternId: 'call',
  count: 1,
  startIndex: 0,
  endIndex: 1,
  entries: [{ patternId: 'call', targetInstruments: ['Djembe_1'] }]
};
const djembeGroup = {
  patternId: 'djembe',
  count: 6,
  startIndex: 1,
  endIndex: 7,
  entries: Array.from({ length: 6 }, () => ({
    patternId: 'djembe',
    targetInstruments: ['Djembe_1']
  }))
};
const sangbanGroup = {
  patternId: 'sangban',
  count: 3,
  startIndex: 7,
  endIndex: 10,
  entries: Array.from({ length: 3 }, () => ({
    patternId: 'sangban',
    targetInstruments: ['Sangban']
  }))
};

const context = vm.createContext({
  timelineState: {
    sourcePatterns: Object.values(patterns),
    minimumBarCount: 0,
    accompanimentSegments: [{
      patternId: 'doundoun',
      targetInstrument: 'Doundoun',
      startBar: 2,
      barCount: 18
    }]
  },
  timelineTrackTargets: ['Djembe_1', 'Djembe_2', 'Djembe_3', 'Kenkeni', 'Sangban', 'Doundoun']
});
context.findPatternById = patternId => patterns[patternId] || null;
context.isTimelineOverlayGroup = () => false;
context.getTimelineRowRepeatInfo = () => ({ repeatCount: 1 });
context.normalizeTimelineGroupRepeatCount = value => Math.max(1, Number(value) || 1);
context.normalizeTimelineGapBeforeBars = value => Math.max(0, Math.round(Number(value) || 0));
context.normalizeTimelineMinimumBarCount = value => Math.max(0, Math.round(Number(value) || 0));
context.buildTimelineContinuationBlocks = () => ({ blocks: [] });
context.getTimelineGroupKey = group => String(group.startIndex) + ':' + String(group.endIndex);
context.getTimelineGroupTargets = group => group.entries[0].targetInstruments.slice();
context.normalizeTimelineAccompanimentStartBar = value => Math.max(1, Number(value) || 1);
context.normalizeTimelineAccompanimentBarCount = value => Math.max(1, Number(value) || 1);
context.isTimelineContinuationMarker = value => value === 'continue';

vm.runInContext(extractFunction(timelineSource, 'expandTimelineBarsWithRepeats'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineRepeatMarkerList'), context);
vm.runInContext(extractFunction(timelineSource, 'buildTimelinePatternRepeatRanges'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineExpandedPatternBars'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelinePatternBarCountAtFinalOut'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineGroupBarCount'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineRowBarCount'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineRowGapBeforeBars'), context);
vm.runInContext(extractFunction(timelineSource, 'buildTimelineHorizontalLayout'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineTargetSignature'), context);
vm.runInContext(extractFunction(timelineSource, 'getTimelineEntryCloneSignature'), context);
vm.runInContext(extractFunction(timelineSource, 'buildPatternLibraryBlocks'), context);

context.visualRows = [[callGroup], [djembeGroup, sangbanGroup]];
const layout = vm.runInContext('buildTimelineHorizontalLayout(visualRows);', context);

assert(layout.rows[0].startBar === 1 && layout.rows[0].barCount === 1,
  'Der Call belegt nicht genau Takt 1.');
assert(layout.rows[1].startBar === 2 && layout.rows[1].barCount === 6,
  'Der zweite Ablaufblock beginnt nicht bei Takt 2 oder endet nicht bei Takt 7.');

const djembeClip = layout.clips.find(clip => clip.kind === 'entry' && clip.pattern.id === 'djembe');
const sangbanClip = layout.clips.find(clip => clip.kind === 'entry' && clip.pattern.id === 'sangban');
const doundounClip = layout.clips.find(clip => clip.kind === 'segment');
assert(djembeClip && djembeClip.startBar === 2 && djembeClip.barCount === 6,
  'Sechs eintaktige Djembe-Wiederholungen werden nicht als Takt 2 bis 7 dargestellt.');
assert(sangbanClip && sangbanClip.startBar === 2 && sangbanClip.barCount === 6,
  'Drei zweitaktige Sangban-Wiederholungen werden nicht parallel über sechs Takte dargestellt.');
assert(doundounClip && doundounClip.startBar === 2 && doundounClip.barCount === 18,
  'Eine durchgehende Spur verliert ihre globale Start- oder Längenangabe.');
assert(layout.usedTargets.join(',') === 'Djembe_1,Sangban,Doundoun',
  'Die tatsächlich verwendeten Instrumentenspuren werden nicht korrekt ermittelt.');

context.timelineState.minimumBarCount = 24;
const manuallyExtendedLayout = vm.runInContext('buildTimelineHorizontalLayout(visualRows);', context);
assert(manuallyExtendedLayout.naturalTotalBars === 19 && manuallyExtendedLayout.totalBars === 24,
  'Die manuell erweiterte Timeline behält ihre natürliche Länge oder Mindestlänge nicht korrekt bei.');
context.timelineState.minimumBarCount = 0;

context.repeatedLibraryPattern = {
  id: 'repeated-library-pattern',
  instrument: 'Djembe 1',
  sourceInstrument: 'Djembe 1',
  labelType: 'Solo',
  labelName: 'Solo 2'
};
context.distinctLibraryPattern = {
  id: 'distinct-library-pattern',
  instrument: 'Djembe 1',
  sourceInstrument: 'Djembe 1',
  labelType: 'Solo',
  labelName: 'Solo 2'
};
context.repeatedLibraryEntries = Array.from({ length: 16 }, () => ({
  patternId: 'repeated-library-pattern',
  patternSourceKey: 'Djembe 1::Solo::Solo 2',
  handMode: 'auto',
  targetInstruments: ['Djembe_1']
}));
context.repeatedLibraryEntries.push({
  patternId: 'distinct-library-pattern',
  patternSourceKey: 'Djembe 1::Solo::Solo 2::part-2',
  handMode: 'auto',
  targetInstruments: ['Djembe_1']
});
const libraryBlocks = vm.runInContext(
  'buildPatternLibraryBlocks(repeatedLibraryEntries, [repeatedLibraryPattern, distinctLibraryPattern]);',
  context
);
assert(libraryBlocks.length === 1 && libraryBlocks[0].entries.length === 2,
  'Interne Pattern-Wiederholungen werden beim Ziehen weiterhin als äußere Timeline-Kopien übernommen.');

context.soloRepeatGroup = {
  patternId: 'solo',
  entries: Array.from({ length: 4 }, () => ({ patternId: 'solo' }))
};
assert(vm.runInContext('getTimelineGroupBarCount(soloRepeatGroup);', context) === 7,
  'Vier Wiederholungen eines zweitaktigen Solos mit Out im ersten Takt belegen nicht sieben Takte.');

context.solo2Group = {
  patternId: 'solo2',
  entries: [{ patternId: 'solo2' }]
};
assert(vm.runInContext('getTimelineGroupBarCount(solo2Group);', context) === 43,
  'Solo 2 belegt mit seinen inneren Wiederholungen nicht die erwarteten 43 Takte.');

djembeGroup.gapBeforeBars = 1;
sangbanGroup.gapBeforeBars = 1;
const layoutWithGap = vm.runInContext('buildTimelineHorizontalLayout(visualRows);', context);
assert(layoutWithGap.rows[1].startBar === 3 && layoutWithGap.rows[1].gapBeforeBars === 1,
  'Ein Leertakt vor einem parallelen Ablaufblock wird nicht in der Taktposition berücksichtigt.');
assert(layoutWithGap.clips.find(clip => clip.kind === 'entry' && clip.pattern.id === 'djembe').startBar === 3,
  'Die Pattern-Clips werden nach einem Leertakt nicht an die neue Position verschoben.');
djembeGroup.gapBeforeBars = 0;
sangbanGroup.gapBeforeBars = 0;

context.timelineState.entries = Array.from({ length: 10 }, () => ({}));
context.getTimelinePayloadCandidateEntries = payload => {
  if (payload.type === 'timeline-entry-group') {
    return [{ patternId: 'djembe', targetInstruments: ['Djembe_1'] }];
  }
  return payload.entries || [];
};
context.canInsertTimelinePayloadParallelToRow = () => false;
context.getTimelineRowRepeatInfo = () => ({ repeatCount: 6 });
context.timelineRowHasAccompaniment = () => true;
vm.runInContext(extractFunction(timelineSource, 'getTimelineRulerBarDropAction'), context);

context.rulerAccompanimentPayload = {
  type: 'pattern-group',
  entries: [{ patternId: 'djembe', targetInstruments: ['Djembe_1'] }]
};
context.rulerSoloPayload = {
  type: 'pattern-group',
  entries: [{ patternId: 'solo', targetInstruments: ['Djembe_2'] }]
};
context.timelineState.accompanimentSegments.push({
  id: 'segment-to-move',
  patternId: 'doundoun',
  targetInstrument: 'Doundoun',
  startBar: 2,
  barCount: 18
});
context.rulerSegmentPayload = {
  type: 'timeline-accompaniment-segment',
  segmentId: 'segment-to-move'
};
context.rulerEntryPayload = {
  type: 'timeline-entry-group',
  startIndex: 1,
  count: 6
};
context.layout = layout;
const accompanimentDropAction = vm.runInContext(
  'getTimelineRulerBarDropAction(rulerAccompanimentPayload, layout, 5);',
  context
);
const overlayDropAction = vm.runInContext(
  'getTimelineRulerBarDropAction(rulerSoloPayload, layout, 3);',
  context
);
const segmentMoveAction = vm.runInContext(
  'getTimelineRulerBarDropAction(rulerSegmentPayload, layout, 6);',
  context
);
const gapMoveAction = vm.runInContext(
  'getTimelineRulerBarDropAction(rulerEntryPayload, layout, 3);',
  context
);
assert(accompanimentDropAction && accompanimentDropAction.type === 'accompaniment' &&
  accompanimentDropAction.startBar === 5 && accompanimentDropAction.targetInstrument === 'Djembe_1',
  'Ein Begleitpattern wird über die Taktanzeige nicht taktgenau eingesetzt.');
assert(overlayDropAction && overlayDropAction.type === 'overlay' && overlayDropAction.repeatIndex === 1,
  'Ein Übungspattern wird nicht der gewählten Begleitwiederholung zugeordnet.');
assert(segmentMoveAction && segmentMoveAction.type === 'move-accompaniment-segment' &&
  segmentMoveAction.startBar === 6,
  'Ein vorhandener Begleitblock kann nicht taktgenau verschoben werden.');
assert(gapMoveAction && gapMoveAction.type === 'set-row-gap' && gapMoveAction.gapBeforeBars === 1,
  'Ein vorhandener Abschnitt kann nicht unter Freilassen eines Taktes nach rechts verschoben werden.');
assert(timelineSource.includes("headingEl.textContent = timelineText('arrangement.tracks')"),
  'Die gemeinsame Überschrift „Spuren“ wird nicht verwendet.');
assert(timelineSource.includes("resizeHandleEl.className = 'timeline-track-entry-resize-handle'"),
  'Normale Pattern-Clips besitzen keinen rechten Längenanfasser.');
assert(timelineSource.includes("endLabelEl.className = 'timeline-track-clip-label-end'"),
  'Lange Pattern-Clips erhalten keine zusätzliche rechtsbündige Beschriftung.');
assert(timelineSource.includes('bindTimelineRulerBarDropTarget(barEl, layout, barNumber)'),
  'Die Taktfelder der Zeitleiste sind keine Dropziele.');
assert(timelineSource.includes("addBarButtonEl.className = 'timeline-track-add-bar'"),
  'Hinter dem letzten Takt fehlt das Feld zum manuellen Hinzufügen eines Taktes.');
assert(timelineSource.includes('TimelineTrailingBars:'),
  'Manuell ergänzte Leertakte werden nicht an den Player übergeben.');
assert(timelineSource.includes("type: 'timeline-accompaniment-segment'"),
  'Vorhandene Begleitblöcke besitzen keinen Drag-Payload.');
assert(timelineSource.includes("dragSurfaceEl.className = 'timeline-track-drag-surface'"),
  'Vorhandene Timeline-Blöcke besitzen keine zuverlässige Ziehfläche.');
assert(timelineSource.includes("window.addEventListener('pointermove', updatePointerMove"),
  'Vorhandene Timeline-Blöcke werden nicht unabhängig vom nativen Browser-Drag verschoben.');
assert(timelineSource.includes("type: 'set-row-gap'"),
  'Das taktgenaue Verschieben erzeugt keine gespeicherten Leertakte.');
assert(timelineSource.includes("entryCard.draggable = false"),
  'Normale Timeline-Blöcke verwenden weiterhin den unzuverlässigen nativen Browser-Drag.');
assert(timelineStyles.includes('.timeline-track-drag-surface'),
  'Die Ziehfläche der Timeline-Blöcke ist nicht gestaltet.');
assert(timelineStyles.includes('.timeline-track-entry-clip .timeline-chip-label'),
  'Die eigene Namenszeile der normalen Pattern-Clips fehlt.');
assert(timelineStyles.includes('.timeline-track-segment-clip > strong'),
  'Die Namenszeile der durchgehenden Begleitsegmente fehlt.');
assert(timelineStyles.includes('.timeline-track-ruler-bar.is-drop-target'),
  'Die aktive Takt-Dropzone wird nicht sichtbar hervorgehoben.');
assert(timelineStyles.includes('.timeline-track-add-bar'),
  'Das Feld zum Hinzufügen eines Taktes ist nicht gestaltet.');

console.log('Horizontale Timeline: Taktpositionen, Wiederholungslängen und Instrumentenspuren geprüft.');
