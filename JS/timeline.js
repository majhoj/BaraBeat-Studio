// Abschnittstimeline: Zustand, Bibliothek, Rendering, Drag/Drop und Metadaten
const timelineDjembeTargets = ['Djembe_1', 'Djembe_2', 'Djembe_3'];
const timelineBassTargets = ['Kenkeni', 'Sangban', 'Doundoun'];
const timelineTrackTargets = timelineDjembeTargets.concat(timelineBassTargets);
const timelineMetadataVersion = 10;
const minimumCompatibleTimelineMetadataVersion = 7;
const defaultTimelineSwingProfiles = {
    binaer: [0, 0, 0, 0],
    tenaer: [0, 0, 0],
    neunaer: [0, 0, 0]
};
const defaultTimelineFeelOffsets = {
    Kenkeni: 0,
    Sangban: 0,
    Doundoun: 0,
    Dreierbass: 0,
    Djembe_1: 0,
    Djembe_2: 0,
    Djembe_3: 0
};
const timelineState = {
    visible: false,
    nextEntryId: 1,
    nextBlockId: 1,
    nextParallelGroupId: 1,
    nextAccompanimentSegmentId: 1,
    sourceHash: '',
    sheetHash: '',
    sourcePatterns: [],
    sourceLibraryGroups: [],
    entries: [],
    accompanimentSegments: [],
    minimumBarCount: 0,
    playbackStartBar: 1,
    sheetLoop: false,
    sheetLoopCount: false,
    tempo: 100,
    shekereBeatEnabled: false,
    swingProfile: {
        binaer: defaultTimelineSwingProfiles.binaer.slice(),
        tenaer: defaultTimelineSwingProfiles.tenaer.slice(),
        neunaer: defaultTimelineSwingProfiles.neunaer.slice()
    },
    feelOffsets: Object.assign({}, defaultTimelineFeelOffsets)
};
let timelineActiveDragPayload = null;

const timelineTypeLabelKeys = Object.freeze({
    Begleitung: 'arrangement.type.accompaniment',
    Call: 'arrangement.type.call',
    Intro: 'arrangement.type.intro',
    Outro: 'arrangement.type.outro',
    Echauffement: 'arrangement.type.warmup',
    Solo: 'arrangement.type.solo',
    Pause: 'arrangement.type.pause',
    Leer: 'arrangement.type.empty'
});

const timelineInstrumentLabelKeys = Object.freeze({
    Instrument: 'arrangement.instrument.generic',
    Kenkeni: 'arrangement.instrument.kenkeni',
    Sangban: 'arrangement.instrument.sangban',
    Doundoun: 'arrangement.instrument.doundoun',
    Dununba: 'arrangement.instrument.doundoun',
    Dundunba: 'arrangement.instrument.doundoun',
    Dreierbass: 'arrangement.instrument.threeBass',
    Djembe: 'arrangement.instrument.djembe',
    Djembe_1: 'arrangement.instrument.djembe1',
    Djembe_2: 'arrangement.instrument.djembe2',
    Djembe_3: 'arrangement.instrument.djembe3',
    Bässe: 'arrangement.instrument.basses'
});

function timelineText(key, values) {
    if (window.BaraBeatI18n && typeof window.BaraBeatI18n.t === 'function') {
        return window.BaraBeatI18n.t(key, values);
    }
    return String(key);
}

function getTimelineTypeLabel(type) {
    const internalType = String(type || '').trim();
    const labelKey = timelineTypeLabelKeys[internalType];
    return labelKey ? timelineText(labelKey) : internalType;
}

function getTimelineInstrumentLabel(instrumentName) {
    const internalName = String(instrumentName || '').trim();
    const labelKey = timelineInstrumentLabelKeys[internalName];
    return labelKey ? timelineText(labelKey) : internalName;
}

function getTimelinePatternLabel(pattern, fallbackLabel) {
    const internalType = String(pattern && pattern.labelType || '').trim();
    const rawLabel = String(
        pattern && (pattern.labelName || pattern.labelType) || fallbackLabel || ''
    ).trim();

    if (internalType && (!pattern.labelName || rawLabel === internalType)) {
        return getTimelineTypeLabel(internalType);
    }
    if (!internalType && rawLabel === 'Passage') {
        return timelineText('arrangement.passage');
    }
    return rawLabel || timelineText('arrangement.pattern');
}

function getTimelineBarCountLabel(count) {
    const normalizedCount = Math.max(0, Math.round(Number(count) || 0));
    const key = normalizedCount === 1
        ? 'arrangement.barCount.one'
        : 'arrangement.barCount.other';
    return timelineText(key, { count: normalizedCount });
}

function normalizeTimelineTempo(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 100;
    }
    return Math.max(30, Math.min(180, Math.round(numericValue)));
}

function normalizeTimelineGapBeforeBars(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue) || numericValue <= 0) {
        return 0;
    }
    return Math.max(0, Math.min(999, Math.round(numericValue)));
}

function normalizeTimelineMinimumBarCount(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue) || numericValue <= 0) {
        return 0;
    }
    return Math.max(0, Math.min(999, Math.round(numericValue)));
}

function normalizeTimelinePlaybackStartBar(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 1;
    }
    return Math.max(1, Math.min(999, Math.round(numericValue)));
}

function normalizeTimelineAccompanimentStartBar(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 1;
    }
    return Math.max(1, Math.min(999, Math.round(numericValue)));
}

function normalizeTimelineAccompanimentBarCount(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 1;
    }
    return Math.max(1, Math.min(999, Math.round(numericValue)));
}

function normalizeTimelineFeelOffset(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 0;
    }
    return Math.max(-50, Math.min(50, Math.round(numericValue)));
}

function normalizeSwingProfileValue(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 0;
    }
    return Math.max(-50, Math.min(50, Math.round(numericValue)));
}

function getCurrentTimelineSwingProfileKey() {
    if (rhythm === 'tenaer') {
        return 'tenaer';
    }
    if (rhythm === 'neunaer') {
        return 'neunaer';
    }
    return 'binaer';
}

function normalizeTimelineSwingProfile(rawProfile, profileKey) {
    const safeProfileKey = defaultTimelineSwingProfiles[profileKey] ? profileKey : 'binaer';
    const defaultProfile = defaultTimelineSwingProfiles[safeProfileKey];
    const profileValues = Array.isArray(rawProfile) ? rawProfile.slice(0, defaultProfile.length) : [];
    while (profileValues.length < defaultProfile.length) {
        profileValues.push(defaultProfile[profileValues.length]);
    }
    return profileValues.slice(0, defaultProfile.length).map(normalizeSwingProfileValue);
}

function normalizeAllTimelineSwingProfiles(rawProfiles) {
    const profileSource = rawProfiles || {};
    return {
        binaer: normalizeTimelineSwingProfile(profileSource.binaer, 'binaer'),
        tenaer: normalizeTimelineSwingProfile(profileSource.tenaer, 'tenaer'),
        neunaer: normalizeTimelineSwingProfile(profileSource.neunaer, 'neunaer')
    };
}

function normalizeTimelineFeelOffsets(rawOffsets) {
    const offsetSource = rawOffsets || {};
    return Object.keys(defaultTimelineFeelOffsets).reduce(function (normalizedOffsets, instrumentName) {
        normalizedOffsets[instrumentName] = normalizeTimelineFeelOffset(offsetSource[instrumentName]);
        return normalizedOffsets;
    }, {});
}

function normalizeTimelineLoopCountValue(rawValue) {
    if (rawValue === true || rawValue === 'loop' || rawValue === 'continue') {
        return 'loop';
    }

    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue) || numericValue < 1) {
        return false;
    }

    return Math.max(1, Math.round(numericValue));
}

function getResolvedTimelineLoopCount() {
    if (timelineState.sheetLoopCount !== undefined && timelineState.sheetLoopCount !== null && timelineState.sheetLoopCount !== '') {
        return normalizeTimelineLoopCountValue(timelineState.sheetLoopCount);
    }
    return normalizeTimelineLoopCountValue(timelineState.sheetLoop);
}

function computeTimelineSheetHash(readResult) {
    const bars = Array.isArray(readResult && readResult.rhythmBars) ? readResult.rhythmBars : [];
    const repeatRanges = Array.isArray(readResult && readResult.repeatRanges) ? readResult.repeatRanges : [];

    const barSignature = bars.map(function (bar) {
        const notesSignature = Array.isArray(bar.notes) ? bar.notes.join(',') : '';
        const controlsSignature = Array.isArray(bar.controls)
            ? bar.controls.map(function (control) {
                return String(control.type || '') + '@' + Number(control.stepIndex);
            }).join(',')
            : '';

        return [
            String(bar.instrument || ''),
            String(bar.label || ''),
            String(bar.effectiveInstrument || ''),
            String(bar.effectiveLabel || ''),
            notesSignature,
            controlsSignature
        ].join('||');
    }).join('###');

    const repeatSignature = repeatRanges.map(function (repeatRange) {
        return [
            Number(repeatRange.startBar) || 0,
            Number(repeatRange.endBar) || 0,
            String(repeatRange.count || '')
        ].join(':');
    }).join('|');

    return barSignature + '////' + repeatSignature + '////' + String(rhythm || '');
}

function hasTimelineBarContent(bar) {
    if (!bar) {
        return false;
    }
    if (bar.instrument && bar.instrument !== 'Leer') {
        return true;
    }
    if (bar.label && bar.label !== 'Leer') {
        return true;
    }
    if (Array.isArray(bar.controls) && bar.controls.length > 0) {
        return true;
    }
    return Array.isArray(bar.notes) && bar.notes.some(function (noteValue) {
        return noteValue && noteValue !== 'f';
    });
}

function getLastTimelineActiveBarIndex(rhythmBars) {
    const bars = Array.isArray(rhythmBars) ? rhythmBars : [];
    for (let barIndex = bars.length - 1; barIndex >= 0; barIndex--) {
        if (hasTimelineBarContent(bars[barIndex])) {
            return barIndex + 1;
        }
    }
    return bars.length;
}

function isTimelineAccompanimentLabel(labelText) {
    const normalizedLabel = String(labelText || '').trim();
    return normalizedLabel.indexOf('Begleitung') === 0 ||
        normalizedLabel.indexOf('Begleitpattern') === 0;
}

function isTimelineOutroLabel(labelText) {
    return String(labelText || '').trim().indexOf('Outro') === 0;
}

function getFirstTimelineAccompanimentBarIndex(rhythmBars) {
    const bars = Array.isArray(rhythmBars) ? rhythmBars : [];
    const firstIndex = bars.findIndex(function (bar) {
        return isTimelineAccompanimentLabel(bar && (bar.effectiveLabel || bar.label));
    });
    return firstIndex === -1 ? 1 : firstIndex + 1;
}

function getTimelineAccompanimentLoopEndBarIndex(rhythmBars) {
    const bars = Array.isArray(rhythmBars) ? rhythmBars : [];
    const firstAccompanimentBarIndex = getFirstTimelineAccompanimentBarIndex(bars);
    const firstOutroBarIndex = bars.findIndex(function (bar, barIndex) {
        return barIndex + 1 > firstAccompanimentBarIndex &&
            isTimelineOutroLabel(bar && (bar.effectiveLabel || bar.label));
    });

    if (firstOutroBarIndex !== -1) {
        for (let barIndex = firstOutroBarIndex - 1; barIndex >= firstAccompanimentBarIndex - 1; barIndex--) {
            if (hasTimelineBarContent(bars[barIndex])) {
                return barIndex + 1;
            }
        }
    }

    return getLastTimelineActiveBarIndex(bars);
}

function getTimelineRepeatBoundaryMarkers(repeatBoundaries, boundaryIndex, markerKey) {
    const boundaries = Array.isArray(repeatBoundaries) ? repeatBoundaries : [];
    const boundary = boundaries[boundaryIndex];
    return boundary && Array.isArray(boundary[markerKey]) ? boundary[markerKey] : [];
}

function getTimelineAccompanimentLoopCountFromPatternLibrary(patternLibrary) {
    if (!Array.isArray(patternLibrary) || patternLibrary.length === 0) {
        return false;
    }

    const accompanimentPatterns = patternLibrary.filter(function (pattern) {
        return pattern &&
            pattern.labelType === 'Begleitung' &&
            Array.isArray(pattern.bars) &&
            pattern.bars.length > 0;
    });

    if (accompanimentPatterns.length === 0) {
        return false;
    }

    const numericMarkers = [];

    accompanimentPatterns.forEach(function (pattern) {
        pattern.bars.forEach(function (bar) {
            const repeatInfo = bar && bar.repeat ? bar.repeat : {};
            const startMarkers = Array.isArray(repeatInfo.start) ? repeatInfo.start : [];
            const endMarkers = Array.isArray(repeatInfo.end) ? repeatInfo.end : [];

            startMarkers.concat(endMarkers).forEach(function (marker) {
                const normalizedMarker = normalizeTimelineLoopCountValue(marker);
                if (normalizedMarker && normalizedMarker !== 'loop') {
                    numericMarkers.push(normalizedMarker);
                }
            });
        });
    });

    if (numericMarkers.length > 0) {
        return numericMarkers[numericMarkers.length - 1];
    }

    return false;
}

function getTimelineOuterRepeatCount(readResult, patternLibrary) {
    const firstAccompanimentBarIndex = getFirstTimelineAccompanimentBarIndex(readResult && readResult.rhythmBars);
    const accompanimentLoopEndBarIndex = getTimelineAccompanimentLoopEndBarIndex(readResult && readResult.rhythmBars);
    const fallbackLoopCount = getTimelineAccompanimentLoopCountFromPatternLibrary(patternLibrary);
    const matchingRepeatRange = Array.isArray(readResult && readResult.repeatRanges)
        ? readResult.repeatRanges.find(function (repeatRange) {
            return repeatRange &&
                Number(repeatRange.startBar) === firstAccompanimentBarIndex &&
                Number(repeatRange.endBar) === accompanimentLoopEndBarIndex &&
                normalizeTimelineLoopCountValue(repeatRange.count);
        })
        : null;

    if (matchingRepeatRange) {
        return normalizeTimelineLoopCountValue(matchingRepeatRange.count);
    }

    const startMarkers = getTimelineRepeatBoundaryMarkers(
        readResult && readResult.repeatBoundaries,
        firstAccompanimentBarIndex - 1,
        'startMarkers'
    );
    const endMarkers = getTimelineRepeatBoundaryMarkers(
        readResult && readResult.repeatBoundaries,
        accompanimentLoopEndBarIndex,
        'endMarkers'
    );
    const hasMatchingStartMarker = startMarkers.some(function (marker) {
        return normalizeTimelineLoopCountValue(marker && marker.count);
    });
    if (!hasMatchingStartMarker) {
        return fallbackLoopCount;
    }

    const numericEndMarker = endMarkers
        .map(function (marker) {
            return normalizeTimelineLoopCountValue(marker && marker.count);
        })
        .find(function (markerCount) {
            return markerCount && markerCount !== 'loop';
        });

    if (numericEndMarker) {
        return numericEndMarker;
    }

    if (fallbackLoopCount && fallbackLoopCount !== 'loop') {
        return fallbackLoopCount;
    }

    const loopingEndMarker = endMarkers.some(function (marker) {
        return normalizeTimelineLoopCountValue(marker && marker.count) === 'loop';
    });

    if (loopingEndMarker && hasMatchingStartMarker && firstAccompanimentBarIndex === 1) {
        return 'loop';
    }

    return false;
}

function normalizePatternInstrumentName(instrumentName) {
    const normalizedName = String(instrumentName || '').trim();
    if (!normalizedName || normalizedName === 'Leer') {
        return '';
    }
    if (normalizedName.indexOf('Djembe') === 0) {
        return 'Djembe';
    }
    if (normalizedName === 'Bässe') {
        return 'Bässe';
    }
    if (normalizedName === 'Dununba' || normalizedName === 'Dundunba') {
        return 'Doundoun';
    }
    return normalizedName;
}

function resolvePatternSourceInstrumentName(bar, labelInfo) {
    const explicitInstrumentName = String(bar && bar.instrument || '').trim();
    const effectiveInstrumentName = String(bar && (bar.effectiveInstrument || bar.instrument) || '').trim();

    if (explicitInstrumentName && explicitInstrumentName !== 'Leer') {
        return explicitInstrumentName;
    }

    return effectiveInstrumentName;
}

function buildPatternDisplayName(pattern, occurrenceIndex) {
    const labelText = getTimelinePatternLabel(pattern);
    const instrumentText = pattern.sourceInstrument || pattern.instrument || 'Instrument';
    return 'P' + occurrenceIndex + ' - ' + instrumentText + ' / ' + labelText;
}

function buildPatternIdentitySignature(pattern) {
    if (!pattern) {
        return '';
    }

    const serializedBars = Array.isArray(pattern.bars)
        ? pattern.bars.map(function (bar) {
            const noteSignature = Array.isArray(bar.notes) ? bar.notes.join(',') : '';
            const controlSignature = Array.isArray(bar.controls)
                ? bar.controls.map(function (control) {
                    return (control.type || '') + '@' + Number(control.stepIndex);
                }).join(',')
                : '';
            const repeatStartSignature = Array.isArray(bar.repeat && bar.repeat.start)
                ? bar.repeat.start.join(',')
                : String(bar.repeat && bar.repeat.start || '');
            const repeatEndSignature = Array.isArray(bar.repeat && bar.repeat.end)
                ? bar.repeat.end.join(',')
                : String(bar.repeat && bar.repeat.end || '');
            return noteSignature + '||' + controlSignature + '||' + repeatStartSignature + '||' + repeatEndSignature;
        }).join('||')
        : '';

    return [
        pattern.instrument || '',
        pattern.sourceInstrument || '',
        pattern.labelType || '',
        pattern.labelName || '',
        serializedBars
    ].join('###');
}

function collapseDuplicatePatterns(patterns, rhythmBars) {
    const uniquePatterns = [];
    const canonicalBySignature = {};
    const canonicalBySourceKey = {};
    const canonicalByPatternId = {};

    (patterns || []).forEach(function (pattern) {
        const signature = buildPatternIdentitySignature(pattern);
        const existingPattern = canonicalBySignature[signature];

        if (!existingPattern) {
            pattern.aliasSourceKeys = [pattern.sourceKey];
            pattern.aliasPatternIds = [pattern.id];
            canonicalBySignature[signature] = pattern;
            canonicalBySourceKey[pattern.sourceKey] = pattern;
            canonicalByPatternId[pattern.id] = pattern;
            uniquePatterns.push(pattern);
            return;
        }

        existingPattern.aliasSourceKeys.push(pattern.sourceKey);
        existingPattern.aliasPatternIds.push(pattern.id);
        canonicalBySourceKey[pattern.sourceKey] = existingPattern;
        canonicalByPatternId[pattern.id] = existingPattern;
    });

    (rhythmBars || []).forEach(function (bar) {
        const canonicalPattern = canonicalBySourceKey[bar.patternSourceKey];
        if (!canonicalPattern) {
            return;
        }
        bar.patternSourceKey = canonicalPattern.sourceKey;
        if (canonicalPattern.aliasPatternIds && canonicalPattern.aliasPatternIds.length > 0) {
            bar.patternId = canonicalPattern.id;
        }
    });

    return uniquePatterns;
}

function cloneTimelineRepeatMarkers(markerValue) {
    if (Array.isArray(markerValue)) {
        return markerValue.slice();
    }
    return markerValue;
}

function isTimelineContinuationMarker(markerValue) {
    return markerValue === 'continue';
}

function getUsedGenericDjembeTargets(patterns) {
    const begleitungPatterns = patterns.filter(function (pattern) {
        return pattern.instrument === 'Djembe' && pattern.labelType === 'Begleitung';
    });
    const genericVoiceCount = Math.min(
        timelineDjembeTargets.length,
        Math.max(1, begleitungPatterns.length)
    );

    return timelineDjembeTargets.slice(0, genericVoiceCount);
}

function assignGenericDjembeDefaults(patterns) {
    const usedGenericTargets = getUsedGenericDjembeTargets(patterns);
    let nextGenericTargetIndex = 0;

    patterns.forEach(function (pattern) {
        if (pattern.instrument !== 'Djembe' || pattern.defaultTargets.length > 0) {
            return;
        }

        if (pattern.labelType === 'Intro' ||
            pattern.labelType === 'Echauffement' ||
            pattern.labelType === 'Outro') {
            pattern.defaultTargets = usedGenericTargets.slice();
            return;
        }

        if (pattern.labelType === 'Begleitung') {
            const targetName = usedGenericTargets[Math.min(nextGenericTargetIndex, usedGenericTargets.length - 1)] || 'Djembe_1';
            pattern.defaultTargets = [targetName];
            nextGenericTargetIndex += 1;
            return;
        }

        if (pattern.labelType === 'Call') {
            pattern.defaultTargets = [usedGenericTargets[0] || 'Djembe_1'];
            return;
        }

        pattern.defaultTargets = [usedGenericTargets[0] || 'Djembe_1'];
    });
}

function collectUsedSheetDjembeTargets(rhythmBars) {
    const usedTargets = [];
    const targetMap = {
        'Djembe 1': 'Djembe_1',
        'Djembe 2': 'Djembe_2',
        'Djembe 3': 'Djembe_3'
    };

    rhythmBars.forEach(function (bar) {
        const sourceInstrumentName = String(bar && (bar.effectiveInstrument || bar.instrument) || '').trim();
        const mappedTarget = targetMap[sourceInstrumentName];
        if (!mappedTarget || usedTargets.indexOf(mappedTarget) !== -1) {
            return;
        }
        usedTargets.push(mappedTarget);
    });

    return usedTargets;
}

function hasExplicitSingleDjembePattern(rhythmBars, labelType) {
    return rhythmBars.some(function (bar) {
        if (!bar) {
            return false;
        }
        const sourceInstrumentName = String(bar.effectiveInstrument || bar.instrument || '').trim();
        const labelInfo = getPlayerLabelInfo(bar.effectiveLabel || bar.label);
        return labelInfo.type === labelType &&
            (sourceInstrumentName === 'Djembe 1' ||
             sourceInstrumentName === 'Djembe 2' ||
             sourceInstrumentName === 'Djembe 3');
    });
}

function getDefaultTargetsForPattern(pattern, originalInstrumentName, timelineContext) {
    const originalName = String(originalInstrumentName || '').trim();
    const context = timelineContext || {};
    const usedSheetDjembeTargets = Array.isArray(context.usedSheetDjembeTargets)
        ? context.usedSheetDjembeTargets
        : [];
    const hasExplicitIntroDjembes = Boolean(context.hasExplicitIntroDjembes);

    if (pattern.instrument === 'Djembe') {
        if (originalName === 'Djembe 1') {
            return ['Djembe_1'];
        }
        if (originalName === 'Djembe 2') {
            return ['Djembe_2'];
        }
        if (originalName === 'Djembe 3') {
            return ['Djembe_3'];
        }
        if ((pattern.labelType === 'Echauffement' || pattern.labelType === 'Outro') && usedSheetDjembeTargets.length > 0) {
            return usedSheetDjembeTargets.slice();
        }
        if (pattern.labelType === 'Intro' && !hasExplicitIntroDjembes && usedSheetDjembeTargets.length > 0) {
            return usedSheetDjembeTargets.slice();
        }
        return [];
    }

    if (pattern.instrument === 'Bässe') {
        return timelineBassTargets.slice();
    }

    const mappedInstrument = mapInstrumentNameForPlayer(pattern.instrument);
    return mappedInstrument ? [mappedInstrument] : [];
}

function buildPatternLibraryFromRhythmBars(rhythmBars) {
    let patterns = [];
    const patternCounters = {};
    const timelineContext = {
        usedSheetDjembeTargets: collectUsedSheetDjembeTargets(rhythmBars),
        hasExplicitIntroDjembes: hasExplicitSingleDjembePattern(rhythmBars, 'Intro')
    };
    let currentPattern = null;
    rhythmBars.forEach(function (bar) {
        const labelInfo = getPlayerLabelInfo(bar.effectiveLabel || bar.label);
        const sourceInstrumentName = resolvePatternSourceInstrumentName(bar, labelInfo);
        const patternInstrument = normalizePatternInstrumentName(sourceInstrumentName);
        if (!patternInstrument || !labelInfo.type || !labelInfo.raw) {
            currentPattern = null;
            return;
        }

        if (!currentPattern ||
            currentPattern.sourceInstrument !== sourceInstrumentName ||
            currentPattern.labelName !== labelInfo.raw) {
            const counterKey = sourceInstrumentName + '|' + labelInfo.raw;
            patternCounters[counterKey] = (patternCounters[counterKey] || 0) + 1;
            const patternOccurrence = patternCounters[counterKey];
            currentPattern = {
                id: 'pattern-' + (patterns.length + 1),
                sourceKey: counterKey + '|' + patternOccurrence,
                instrument: patternInstrument,
                sourceInstrument: sourceInstrumentName,
                labelType: labelInfo.type,
                labelName: labelInfo.raw,
                name: '',
                defaultTargets: [],
                bars: []
            };
            currentPattern.name = buildPatternDisplayName(currentPattern, patterns.length + 1);
            currentPattern.defaultTargets = getDefaultTargetsForPattern(currentPattern, sourceInstrumentName, timelineContext);
            patterns.push(currentPattern);
        }

        currentPattern.bars.push({
            sourceBarIndex: bar.index,
            patternSourceKey: currentPattern.sourceKey,
            patternBarIndex: currentPattern.bars.length,
            label: labelInfo.type,
            repeat: {
                start: cloneTimelineRepeatMarkers(bar.repeat && bar.repeat.start),
                end: cloneTimelineRepeatMarkers(bar.repeat && bar.repeat.end)
            },
            controls: Array.isArray(bar.controls) ? bar.controls.map(function (control) {
                return {
                    type: control.type,
                    stepIndex: control.stepIndex
                };
            }) : [],
            notes: bar.notes.slice()
        });
        bar.patternSourceKey = currentPattern.sourceKey;
        bar.patternBarIndex = currentPattern.bars.length - 1;
    });

    assignGenericDjembeDefaults(patterns);
    patterns = collapseDuplicatePatterns(patterns, rhythmBars);
    return patterns;
}

function cloneTimelineEntryFromPattern(pattern, overrides) {
    const overrideConfig = overrides || {};
    const nextId = timelineState.nextEntryId++;
    return {
        id: overrideConfig.id || ('timeline-entry-' + nextId),
        blockId: overrideConfig.blockId || '',
        parallelGroupId: overrideConfig.parallelGroupId || '',
        gapBeforeBars: normalizeTimelineGapBeforeBars(overrideConfig.gapBeforeBars),
        overlayRepeatIndex: overrideConfig.overlayRepeatIndex === null || overrideConfig.overlayRepeatIndex === undefined
            ? null
            : Math.max(0, Math.round(Number(overrideConfig.overlayRepeatIndex) || 0)),
        patternId: pattern.id,
        patternSourceKey: pattern.sourceKey,
        handMode: pattern.instrument === 'Djembe'
            ? String(overrideConfig.handMode || 'auto')
            : '',
        sectionTempo: overrideConfig.sectionTempo === null || overrideConfig.sectionTempo === undefined || overrideConfig.sectionTempo === ''
            ? null
            : normalizeTimelineTempo(overrideConfig.sectionTempo),
        targetInstruments: Array.isArray(overrideConfig.targetInstruments) && overrideConfig.targetInstruments.length > 0
            ? overrideConfig.targetInstruments.slice()
            : pattern.defaultTargets.slice()
    };
}

function cloneTimelineEntry(entry) {
    if (!entry) {
        return null;
    }

    const sourcePattern = findPatternById(entry.patternId);
    if (!sourcePattern) {
        return null;
    }

    return cloneTimelineEntryFromPattern(sourcePattern, {
        blockId: entry.blockId || '',
        parallelGroupId: entry.parallelGroupId || '',
        gapBeforeBars: normalizeTimelineGapBeforeBars(entry.gapBeforeBars),
        overlayRepeatIndex: entry.overlayRepeatIndex === null || entry.overlayRepeatIndex === undefined
            ? null
            : Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0)),
        handMode: entry.handMode || 'auto',
        sectionTempo: entry.sectionTempo,
        targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
    });
}

function nextTimelineBlockId() {
    const nextId = timelineState.nextBlockId++;
    return 'timeline-block-' + nextId;
}

function nextTimelineParallelGroupId() {
    const nextId = timelineState.nextParallelGroupId++;
    return 'timeline-parallel-' + nextId;
}

function nextTimelineAccompanimentSegmentId() {
    const nextId = timelineState.nextAccompanimentSegmentId++;
    return 'timeline-accompaniment-segment-' + nextId;
}

function syncTimelineBlockIdSequence(entries) {
    let maxBlockId = 0;
    let maxParallelGroupId = 0;

    (entries || []).forEach(function (entry) {
        const blockIdMatch = String(entry && entry.blockId || '').match(/^timeline-block-(\d+)$/);
        if (!blockIdMatch) {
        } else {
            maxBlockId = Math.max(maxBlockId, Number(blockIdMatch[1]) || 0);
        }
        const parallelGroupMatch = String(entry && entry.parallelGroupId || '').match(/^timeline-parallel-(\d+)$/);
        if (!parallelGroupMatch) {
            return;
        }
        maxParallelGroupId = Math.max(maxParallelGroupId, Number(parallelGroupMatch[1]) || 0);
    });

    timelineState.nextBlockId = maxBlockId + 1;
    timelineState.nextParallelGroupId = maxParallelGroupId + 1;
}

function syncTimelineAccompanimentSegmentIdSequence(segments) {
    let maxSegmentId = 0;
    (Array.isArray(segments) ? segments : []).forEach(function (segment) {
        const idMatch = String(segment && segment.id || '').match(/^timeline-accompaniment-segment-(\d+)$/);
        if (idMatch) {
            maxSegmentId = Math.max(maxSegmentId, Number(idMatch[1]) || 0);
        }
    });
    timelineState.nextAccompanimentSegmentId = maxSegmentId + 1;
}

function buildDefaultTimelineEntries(patternLibrary) {
    return patternLibrary.map(function (pattern) {
        return cloneTimelineEntryFromPattern(pattern);
    });
}

function expandTimelineBarsWithRepeats(rhythmBars, repeatRangesToApply, startBarIndex, endBarIndex) {
    const expandedBars = [];
    let currentBarIndex = startBarIndex;

    while (currentBarIndex <= endBarIndex) {
        const matchingRanges = repeatRangesToApply
            .filter(function (repeatRange) {
                return !isTimelineContinuationMarker(repeatRange.count) &&
                    repeatRange.startBar === currentBarIndex &&
                    repeatRange.endBar <= endBarIndex;
            })
            .sort(function (rangeA, rangeB) {
                return rangeB.endBar - rangeA.endBar;
            });

        const matchingRange = matchingRanges[0];
        if (!matchingRange) {
            expandedBars.push(rhythmBars[currentBarIndex - 1]);
            currentBarIndex += 1;
            continue;
        }

        const repeatedSegment = expandTimelineBarsWithRepeats(
            rhythmBars,
            repeatRangesToApply.filter(function (repeatRange) {
                return repeatRange.startBar >= matchingRange.startBar &&
                    repeatRange.endBar <= matchingRange.endBar &&
                    !(repeatRange.startBar === matchingRange.startBar && repeatRange.endBar === matchingRange.endBar);
            }),
            matchingRange.startBar,
            matchingRange.endBar
        );

        expandedBars.push.apply(expandedBars, repeatedSegment);
        if (matchingRange.count === 'loop') {
            expandedBars.push.apply(expandedBars, repeatedSegment);
        } else {
            const repeatCount = Number(matchingRange.count) || 0;
            for (let repeatIndex = 0; repeatIndex < repeatCount; repeatIndex++) {
                expandedBars.push.apply(expandedBars, repeatedSegment);
            }
        }

        currentBarIndex = matchingRange.endBar + 1;
    }

    return expandedBars;
}

function buildDefaultTimelineEntriesFromRhythmBars(rhythmBars, repeatRanges, patternLibrary) {
    const patternBySourceKey = {};
    const sheetLoopRepeatRanges = (Array.isArray(repeatRanges) ? repeatRanges : []).filter(function (repeatRange) {
        const firstAccompanimentBarIndex = getFirstTimelineAccompanimentBarIndex(rhythmBars);
        const accompanimentLoopEndBarIndex = getTimelineAccompanimentLoopEndBarIndex(rhythmBars);
        return !(repeatRange &&
            Number(repeatRange.startBar) === firstAccompanimentBarIndex &&
            normalizeTimelineLoopCountValue(repeatRange.count) &&
            Number(repeatRange.endBar) === accompanimentLoopEndBarIndex);
    });
    const expandedBars = expandTimelineBarsWithRepeats(
        rhythmBars,
        sheetLoopRepeatRanges,
        1,
        rhythmBars.length
    );
    const defaultEntries = [];
    let previousPatternSourceKey = '';
    let previousPatternBarIndex = -1;
    let previousSourceBarIndex = -1;

    patternLibrary.forEach(function (pattern) {
        patternBySourceKey[pattern.sourceKey] = pattern;
    });

    expandedBars.forEach(function (bar) {
        const matchedPattern = patternBySourceKey[bar.patternSourceKey];
        if (!matchedPattern) {
            return;
        }

        const sourceBarIndex = Number(bar.sourceBarIndex || bar.index);
        const isInternalRepeatOfSameSourceBar = previousPatternSourceKey === bar.patternSourceKey &&
            Number(bar.patternBarIndex) === 0 &&
            sourceBarIndex === previousSourceBarIndex;
        const isPatternStart = previousPatternSourceKey !== bar.patternSourceKey ||
            Number(bar.patternBarIndex) === 0 ||
            Number(bar.patternBarIndex) <= previousPatternBarIndex;

        if (isPatternStart && !isInternalRepeatOfSameSourceBar) {
            defaultEntries.push(cloneTimelineEntryFromPattern(matchedPattern));
        }

        previousPatternSourceKey = bar.patternSourceKey;
        previousPatternBarIndex = Number(bar.patternBarIndex);
        previousSourceBarIndex = sourceBarIndex;
    });

    return defaultEntries;
}

function computePatternLibraryHash(patternLibrary) {
    return patternLibrary.map(function (pattern) {
        const repeatSignature = Array.isArray(pattern.bars)
            ? pattern.bars.map(function (bar) {
                const repeatStartSignature = Array.isArray(bar.repeat && bar.repeat.start)
                    ? bar.repeat.start.join(',')
                    : String(bar.repeat && bar.repeat.start || '');
                const repeatEndSignature = Array.isArray(bar.repeat && bar.repeat.end)
                    ? bar.repeat.end.join(',')
                    : String(bar.repeat && bar.repeat.end || '');
                return repeatStartSignature + '>' + repeatEndSignature;
            }).join(',')
            : '';
        return pattern.sourceKey + ':' + pattern.bars.length + ':' + repeatSignature;
    }).join('|');
}

function timelineEntriesHavePatternMatch(patternLibrary, existingEntries) {
    const patternById = {};
    const patternBySourceKey = {};
    patternLibrary.forEach(function (pattern) {
        patternById[pattern.id] = pattern;
        patternBySourceKey[pattern.sourceKey] = pattern;
        (pattern.aliasSourceKeys || []).forEach(function (aliasSourceKey) {
            patternBySourceKey[aliasSourceKey] = pattern;
        });
        (pattern.aliasPatternIds || []).forEach(function (aliasPatternId) {
            patternById[aliasPatternId] = pattern;
        });
    });

    return (existingEntries || []).some(function (entry) {
        return Boolean(entry && (patternBySourceKey[entry.patternSourceKey] || patternById[entry.patternId]));
    });
}

function syncTimelineEntriesWithPatternLibrary(patternLibrary, existingEntries, options) {
    const syncOptions = options || {};
    const appendMissingPatterns = syncOptions.appendMissingPatterns !== false;
    const patternById = {};
    const patternBySourceKey = {};
    const matchedSourceKeys = [];
    patternLibrary.forEach(function (pattern) {
        patternById[pattern.id] = pattern;
        patternBySourceKey[pattern.sourceKey] = pattern;
        (pattern.aliasSourceKeys || []).forEach(function (aliasSourceKey) {
            patternBySourceKey[aliasSourceKey] = pattern;
        });
        (pattern.aliasPatternIds || []).forEach(function (aliasPatternId) {
            patternById[aliasPatternId] = pattern;
        });
    });

    const syncedEntries = (existingEntries || []).map(function (entry) {
        const matchedPattern = patternBySourceKey[entry.patternSourceKey] || patternById[entry.patternId];
        if (!matchedPattern) {
            return null;
        }
        matchedSourceKeys.push(matchedPattern.sourceKey);

        let targetInstruments = Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : [];
        if (matchedPattern.instrument === 'Djembe') {
            targetInstruments = targetInstruments.filter(function (targetName) {
                return timelineDjembeTargets.indexOf(targetName) !== -1;
            });
            if (targetInstruments.length === 0) {
                targetInstruments = matchedPattern.defaultTargets.slice();
            }
        } else if (matchedPattern.instrument === 'Bässe') {
            targetInstruments = targetInstruments.filter(function (targetName) {
                return timelineBassTargets.indexOf(targetName) !== -1;
            });
            if (targetInstruments.length === 0) {
                targetInstruments = matchedPattern.defaultTargets.slice();
            }
        } else {
            targetInstruments = matchedPattern.defaultTargets.slice();
        }

        return {
            id: entry.id || ('timeline-entry-' + timelineState.nextEntryId++),
            blockId: entry.blockId || '',
            parallelGroupId: entry.parallelGroupId || '',
            gapBeforeBars: normalizeTimelineGapBeforeBars(entry.gapBeforeBars),
            overlayRepeatIndex: entry.overlayRepeatIndex === null || entry.overlayRepeatIndex === undefined
                ? null
                : Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0)),
            patternId: matchedPattern.id,
            patternSourceKey: matchedPattern.sourceKey,
            handMode: matchedPattern.instrument === 'Djembe'
                ? String(entry.handMode || 'auto')
                : '',
            sectionTempo: entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
                ? null
                : normalizeTimelineTempo(entry.sectionTempo),
            targetInstruments: targetInstruments
        };
    }).filter(Boolean);

    if (appendMissingPatterns) {
        patternLibrary.forEach(function (pattern) {
            if (matchedSourceKeys.indexOf(pattern.sourceKey) !== -1) {
                return;
            }
            syncedEntries.push(cloneTimelineEntryFromPattern(pattern));
        });
    }

    return syncedEntries;
}

function serializeTimelineAccompanimentSegment(segment) {
    return {
        id: String(segment && segment.id || ''),
        patternId: String(segment && segment.patternId || ''),
        patternSourceKey: String(segment && segment.patternSourceKey || ''),
        targetInstrument: timelineTrackTargets.indexOf(segment && segment.targetInstrument) !== -1
            ? segment.targetInstrument
            : '',
        startBar: normalizeTimelineAccompanimentStartBar(segment && segment.startBar),
        barCount: normalizeTimelineAccompanimentBarCount(segment && segment.barCount)
    };
}

function syncTimelineAccompanimentSegmentsWithPatternLibrary(patternLibrary, existingSegments) {
    const patternById = {};
    const patternBySourceKey = {};
    (Array.isArray(patternLibrary) ? patternLibrary : []).forEach(function (pattern) {
        patternById[pattern.id] = pattern;
        patternBySourceKey[pattern.sourceKey] = pattern;
        (pattern.aliasSourceKeys || []).forEach(function (aliasSourceKey) {
            patternBySourceKey[aliasSourceKey] = pattern;
        });
        (pattern.aliasPatternIds || []).forEach(function (aliasPatternId) {
            patternById[aliasPatternId] = pattern;
        });
    });

    return (Array.isArray(existingSegments) ? existingSegments : []).map(function (segment) {
        const matchedPattern = patternBySourceKey[segment && segment.patternSourceKey] ||
            patternById[segment && segment.patternId];
        const targetInstrument = timelineTrackTargets.indexOf(segment && segment.targetInstrument) !== -1
            ? segment.targetInstrument
            : '';
        if (!matchedPattern || matchedPattern.labelType !== 'Begleitung' || !targetInstrument) {
            return null;
        }

        return serializeTimelineAccompanimentSegment({
            id: segment.id || nextTimelineAccompanimentSegmentId(),
            patternId: matchedPattern.id,
            patternSourceKey: matchedPattern.sourceKey,
            targetInstrument: targetInstrument,
            startBar: segment.startBar,
            barCount: segment.barCount
        });
    }).filter(Boolean);
}

function timelineEntriesContainAccompaniment(patternLibrary, timelineEntries) {
    const patternById = {};
    (Array.isArray(patternLibrary) ? patternLibrary : []).forEach(function (pattern) {
        if (pattern && pattern.id) {
            patternById[pattern.id] = pattern;
        }
    });

    return (Array.isArray(timelineEntries) ? timelineEntries : []).some(function (entry) {
        const pattern = entry && patternById[entry.patternId];
        return pattern && pattern.labelType === 'Begleitung';
    });
}

function buildTimelinePlayerPayload(patternLibrary, timelineEntries) {
    const timelineLoopCount = (timelineEntriesContainAccompaniment(patternLibrary, timelineEntries) ||
        timelineState.accompanimentSegments.length > 0)
        ? getResolvedTimelineLoopCount()
        : false;

    const timelineLayout = buildTimelineHorizontalLayout(buildTimelineVisualRows(
        buildTimelineDisplayGroups(timelineEntries, patternLibrary),
        patternLibrary
    ));

    return [{
        Name: titel.attr('text'),
        Rhythmus: rhythm,
        TimelineMode: true,
        TimelineStopAtEnd: true,
        TimelineLoop: timelineLoopCount === 'loop',
        TimelineLoopCount: timelineLoopCount,
        Tempo: normalizeTimelineTempo(timelineState.tempo),
        ShekereBeatEnabled: Boolean(timelineState.shekereBeatEnabled),
        SwingProfile: normalizeAllTimelineSwingProfiles(timelineState.swingProfile),
        FeelOffsets: normalizeTimelineFeelOffsets(timelineState.feelOffsets),
        PracticeInstrumentVolumes: typeof normalizePracticeInstrumentVolumes === 'function'
            ? normalizePracticeInstrumentVolumes(practiceState.instrumentVolumes)
            : {},
        PracticeInstrumentToneVolumes: typeof normalizePracticeInstrumentToneVolumes === 'function'
            ? normalizePracticeInstrumentToneVolumes(practiceState.instrumentToneVolumes)
            : {},
        RepeatRanges: [],
        PatternLibrary: patternLibrary.map(function (pattern) {
            return {
                id: pattern.id,
                sourceKey: pattern.sourceKey,
                name: pattern.name,
                instrument: pattern.instrument,
                sourceInstrument: pattern.sourceInstrument,
                label: pattern.labelType,
                labelName: pattern.labelName,
                bars: pattern.bars.map(function (bar) {
                    return {
                        sourceBarIndex: bar.sourceBarIndex,
                        label: bar.label,
                        repeat: {
                            start: cloneTimelineRepeatMarkers(bar.repeat && bar.repeat.start),
                            end: cloneTimelineRepeatMarkers(bar.repeat && bar.repeat.end)
                        },
                        controls: Array.isArray(bar.controls) ? bar.controls.map(function (control) {
                            return {
                                type: control.type,
                                stepIndex: control.stepIndex
                            };
                        }) : [],
                        notes: bar.notes.slice()
                    };
                })
            };
        }),
        TimelineEntries: timelineEntries.map(function (entry) {
            return {
                id: entry.id,
                blockId: entry.blockId || '',
                parallelGroupId: entry.parallelGroupId || '',
                gapBeforeBars: normalizeTimelineGapBeforeBars(entry.gapBeforeBars),
                overlayRepeatIndex: entry.overlayRepeatIndex === null || entry.overlayRepeatIndex === undefined
                    ? null
                    : Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0)),
                patternId: entry.patternId,
                patternSourceKey: entry.patternSourceKey,
                handMode: entry.handMode || '',
                sectionTempo: entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
                    ? null
                    : normalizeTimelineTempo(entry.sectionTempo),
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            };
        }),
        TimelineStartBar: normalizeTimelinePlaybackStartBar(timelineState.playbackStartBar),
        TimelineTotalBars: Math.max(0, Math.round(Number(timelineLayout.playbackTotalBars) || 0)),
        TimelineTrailingBars: Math.max(0, timelineLayout.totalBars - timelineLayout.naturalTotalBars),
        AccompanimentSegments: timelineState.accompanimentSegments.map(serializeTimelineAccompanimentSegment)
    }];
}

function findPatternById(patternId) {
    return timelineState.sourcePatterns.find(function (pattern) {
        return pattern.id === patternId;
    }) || null;
}

function updateTimelineMetadataNode() {
    s.selectAll(timelineMetadataSelector).forEach(function (metadataNode) {
        metadataNode.remove();
    });

    const metadataEntries = pruneTimelineOrphanOverlayEntries(timelineState.entries);
    if (metadataEntries.length !== timelineState.entries.length) {
        timelineState.entries = metadataEntries;
    }

    const metadataPayload = JSON.stringify({
        version: timelineMetadataVersion,
        sourceHash: timelineState.sourceHash,
        sheetLoop: Boolean(timelineState.sheetLoop),
        sheetLoopCount: getResolvedTimelineLoopCount(),
        tempo: normalizeTimelineTempo(timelineState.tempo),
        shekereBeatEnabled: Boolean(timelineState.shekereBeatEnabled),
        swingProfile: normalizeAllTimelineSwingProfiles(timelineState.swingProfile),
        feelOffsets: normalizeTimelineFeelOffsets(timelineState.feelOffsets),
        practice: typeof buildPracticeMetadata === 'function' ? buildPracticeMetadata() : null,
        minimumBarCount: normalizeTimelineMinimumBarCount(timelineState.minimumBarCount),
        playbackStartBar: normalizeTimelinePlaybackStartBar(timelineState.playbackStartBar),
        accompanimentSegments: timelineState.accompanimentSegments.map(serializeTimelineAccompanimentSegment),
        entries: metadataEntries.map(function (entry) {
            return {
                id: entry.id,
                blockId: entry.blockId || '',
                parallelGroupId: entry.parallelGroupId || '',
                gapBeforeBars: normalizeTimelineGapBeforeBars(entry.gapBeforeBars),
                overlayRepeatIndex: entry.overlayRepeatIndex === null || entry.overlayRepeatIndex === undefined
                    ? null
                    : Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0)),
                patternId: entry.patternId,
                patternSourceKey: entry.patternSourceKey,
                handMode: entry.handMode || '',
                sectionTempo: entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
                    ? null
                    : normalizeTimelineTempo(entry.sectionTempo),
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            };
        })
    });
    const encodedPayload = window.btoa(unescape(encodeURIComponent(metadataPayload)));
    const metadataNode = document.createElementNS('http://www.w3.org/2000/svg', 'desc');
    metadataNode.setAttribute('id', 'timeline_metadata');
    metadataNode.textContent = encodedPayload;
    s.node.appendChild(metadataNode);

    if (window.suppressNextTimelineAudioRefresh) {
        window.suppressNextTimelineAudioRefresh = false;
        return;
    }
    if (timelineState.visible && typeof scheduleTimelineAudioRefresh === 'function') {
        scheduleTimelineAudioRefresh(250);
    }
}

function readTimelineMetadata(data) {
    const metadataElement = data && typeof data.select === 'function'
        ? data.select(timelineMetadataSelector)
        : s.select(timelineMetadataSelector);
    if (!metadataElement) {
        return null;
    }

    const metadataText = metadataElement.attr('data-timeline') || metadataElement.attr('text') || metadataElement.node.textContent || '';
    if (!metadataText) {
        return null;
    }

    try {
        const decodedText = metadataElement.node && metadataElement.node.tagName && metadataElement.node.tagName.toLowerCase() === 'desc'
            ? decodeURIComponent(escape(window.atob(metadataText)))
            : metadataText;
        return JSON.parse(decodedText);
    } catch (error) {
        console.warn('Timeline-Metadaten konnten nicht gelesen werden', error);
        return null;
    }
}

function syncTimelineStateFromReadResult(readResult, options) {
    const syncOptions = options || {};
    const patternLibrary = buildPatternLibraryFromRhythmBars(readResult.rhythmBars);
    const newSourceHash = computePatternLibraryHash(patternLibrary);
    const hasPersistedEntries = Array.isArray(syncOptions.persistedEntries);
    const persistedVersion = Number(syncOptions.persistedVersion);
    const hasCompatiblePersistedVersion = Number.isFinite(persistedVersion) &&
        persistedVersion >= minimumCompatibleTimelineMetadataVersion &&
        persistedVersion <= timelineMetadataVersion;
    const canReusePersistedEntries = hasPersistedEntries &&
        hasCompatiblePersistedVersion &&
        (syncOptions.persistedSourceHash === newSourceHash ||
            syncOptions.persistedEntries.length === 0 ||
            timelineEntriesHavePatternMatch(patternLibrary, syncOptions.persistedEntries));
    const fallbackEntries = buildDefaultTimelineEntriesFromRhythmBars(
        readResult.rhythmBars,
        readResult.repeatRanges,
        patternLibrary
    );
    const currentEntries = canReusePersistedEntries
        ? syncOptions.persistedEntries
        : fallbackEntries;
    const syncedEntries = syncTimelineEntriesWithPatternLibrary(patternLibrary, currentEntries, {
        appendMissingPatterns: !canReusePersistedEntries
    });
    const sheetLoopCount = getTimelineOuterRepeatCount(readResult, patternLibrary);

    timelineState.sourcePatterns = patternLibrary;
    timelineState.sourceLibraryGroups = buildPatternLibraryBlocks(fallbackEntries, patternLibrary);
    timelineState.sourceHash = newSourceHash;
    timelineState.sheetHash = computeTimelineSheetHash(readResult);
    timelineState.entries = canReusePersistedEntries
        ? syncedEntries
        : (syncedEntries.length > 0 ? syncedEntries : fallbackEntries);
    timelineState.accompanimentSegments = hasCompatiblePersistedVersion
        ? syncTimelineAccompanimentSegmentsWithPatternLibrary(
            patternLibrary,
            syncOptions.persistedAccompanimentSegments
        )
        : [];
    timelineState.minimumBarCount = hasCompatiblePersistedVersion
        ? normalizeTimelineMinimumBarCount(syncOptions.persistedMinimumBarCount)
        : 0;
    timelineState.playbackStartBar = hasCompatiblePersistedVersion
        ? normalizeTimelinePlaybackStartBar(syncOptions.persistedPlaybackStartBar)
        : 1;
    timelineState.sheetLoopCount = sheetLoopCount;
    timelineState.sheetLoop = sheetLoopCount === 'loop';
    syncTimelineBlockIdSequence(timelineState.entries);
    syncTimelineAccompanimentSegmentIdSequence(timelineState.accompanimentSegments);
    timelineState.tempo = normalizeTimelineTempo(syncOptions.tempo ?? timelineState.tempo);
    timelineState.shekereBeatEnabled = Boolean(syncOptions.shekereBeatEnabled);
    timelineState.swingProfile = normalizeAllTimelineSwingProfiles(syncOptions.swingProfile);
    timelineState.feelOffsets = normalizeTimelineFeelOffsets(syncOptions.feelOffsets);

    if (typeof applyPracticeMetadata === 'function' && Object.prototype.hasOwnProperty.call(syncOptions, 'persistedPractice')) {
        applyPracticeMetadata(syncOptions.persistedPractice, patternLibrary, newSourceHash);
    } else if (typeof resetPracticeForSource === 'function' &&
            practiceState.defaultSelectionSourceHash &&
            practiceState.defaultSelectionSourceHash !== newSourceHash) {
        resetPracticeForSource(newSourceHash);
    }

    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function syncTimelineStateFromReadResultIfNeeded(readResult, options) {
    const currentSheetHash = computeTimelineSheetHash(readResult);
    const hasTimelineEntries = Array.isArray(timelineState.entries) && timelineState.entries.length > 0;

    if (hasTimelineEntries && timelineState.sheetHash && timelineState.sheetHash === currentSheetHash) {
        return false;
    }

    syncTimelineStateFromReadResult(readResult, options);
    return true;
}

function buildCurrentTimelineSyncOptions() {
    return {
        tempo: timelineState.tempo,
        shekereBeatEnabled: Boolean(timelineState.shekereBeatEnabled),
        swingProfile: normalizeAllTimelineSwingProfiles(timelineState.swingProfile),
        feelOffsets: normalizeTimelineFeelOffsets(timelineState.feelOffsets),
        persistedPractice: typeof buildPracticeMetadata === 'function' ? buildPracticeMetadata() : null,
        persistedMinimumBarCount: normalizeTimelineMinimumBarCount(timelineState.minimumBarCount),
        persistedPlaybackStartBar: normalizeTimelinePlaybackStartBar(timelineState.playbackStartBar),
        persistedAccompanimentSegments: timelineState.accompanimentSegments.map(serializeTimelineAccompanimentSegment),
        persistedEntries: timelineState.entries.map(function (entry) {
            return {
                id: entry.id,
                blockId: entry.blockId || '',
                parallelGroupId: entry.parallelGroupId || '',
                gapBeforeBars: normalizeTimelineGapBeforeBars(entry.gapBeforeBars),
                patternId: entry.patternId,
                patternSourceKey: entry.patternSourceKey,
                handMode: entry.handMode || '',
                sectionTempo: entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
                    ? null
                    : normalizeTimelineTempo(entry.sectionTempo),
                overlayRepeatIndex: entry.overlayRepeatIndex === null || entry.overlayRepeatIndex === undefined
                    ? null
                    : Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0)),
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            };
        }),
        persistedVersion: timelineMetadataVersion,
        persistedSourceHash: timelineState.sourceHash
    };
}

function getTimelineDragPayload(rawPayload) {
    if (!rawPayload) {
        return null;
    }
    try {
        return JSON.parse(rawPayload);
    } catch (error) {
        return null;
    }
}

function setTimelineDragDropTargetsVisible(isVisible) {
    document.body.classList.toggle('is-timeline-dragging', Boolean(isVisible));
    if (!isVisible) {
        timelineActiveDragPayload = null;
        document.querySelectorAll([
            '.timeline-dropzone.is-drop-target',
            '.timeline-accompaniment-lane-drop.is-drop-target',
            '.timeline-track-lane-drop.is-drop-target',
            '.timeline-track-overlay-dropzone.is-drop-target',
            '.timeline-track-ruler-bar.is-drop-target'
        ].join(', ')).forEach(function (dropzoneEl) {
            dropzoneEl.classList.remove('is-drop-target');
        });
    }
}

function markTimelineDropTarget(targetEl) {
    if (!targetEl || !targetEl.classList || !targetEl.classList.contains('timeline-dropzone')) {
        return;
    }
    document.querySelectorAll('.timeline-dropzone.is-drop-target').forEach(function (dropzoneEl) {
        if (dropzoneEl !== targetEl) {
            dropzoneEl.classList.remove('is-drop-target');
        }
    });
    targetEl.classList.add('is-drop-target');
}

function unmarkTimelineDropTarget(targetEl) {
    if (targetEl && targetEl.classList) {
        targetEl.classList.remove('is-drop-target');
    }
}

function setTimelineActiveDragPayload(payload) {
    timelineActiveDragPayload = payload || null;
    setTimelineDragDropTargetsVisible(Boolean(payload));
}

function getTimelineTargetSignature(targetInstruments) {
    return (Array.isArray(targetInstruments) ? targetInstruments.slice() : [])
        .sort()
        .join('|');
}

function getTimelineRepeatMarkerList(markerValue) {
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

function buildTimelinePatternBarSummary(pattern) {
    const bars = pattern && Array.isArray(pattern.bars) ? pattern.bars : [];
    if (bars.length === 0) {
        return '';
    }

    const summaryParts = [];
    for (let barIndex = 0; barIndex < bars.length; barIndex++) {
        const bar = bars[barIndex] || {};
        const repeatStartMarkers = getTimelineRepeatMarkerList(bar.repeat && bar.repeat.start);
        const repeatEndMarkers = getTimelineRepeatMarkerList(bar.repeat && bar.repeat.end);
        let repeatCount = 1;
        const continues = repeatEndMarkers.some(isTimelineContinuationMarker);

        if (continues) {
            summaryParts.push(timelineText('arrangement.barContinues', {
                number: barIndex + 1
            }));
            continue;
        }

        if (repeatStartMarkers.length > 0 && repeatEndMarkers.length > 0) {
            const markerCount = repeatEndMarkers[0] === 'loop'
                ? 1
                : Number(repeatEndMarkers[0]) || 0;
            repeatCount = markerCount + 1;
        }

        summaryParts.push(repeatCount > 1
            ? timelineText('arrangement.barRepeated', {
                number: barIndex + 1,
                count: repeatCount
            })
            : timelineText('arrangement.bar', { number: barIndex + 1 }));
    }

    return summaryParts.join(', ');
}

function buildTimelineGroupSummary(group, patternLibrary) {
    const patternById = {};
    const summaryParts = [];
    const sequenceIndexByPatternId = {};
    let nextSequenceIndex = 1;
    let currentRun = null;
    let totalBars = 0;

    (patternLibrary || []).forEach(function (pattern) {
        patternById[pattern.id] = pattern;
    });

    (group.entries || []).forEach(function (entry) {
        const pattern = patternById[entry.patternId] || null;
        if (!sequenceIndexByPatternId[entry.patternId]) {
            sequenceIndexByPatternId[entry.patternId] = nextSequenceIndex++;
        }

        const sequenceLabel = timelineText('arrangement.bar', {
            number: sequenceIndexByPatternId[entry.patternId]
        });
        if (currentRun && currentRun.patternId === entry.patternId) {
            currentRun.count += 1;
            return;
        }

        if (currentRun) {
            summaryParts.push(currentRun);
        }

        currentRun = {
            patternId: entry.patternId,
            label: sequenceLabel,
            count: 1
        };
        totalBars += pattern && Array.isArray(pattern.bars) ? pattern.bars.length : 0;
    });

    if (currentRun) {
        summaryParts.push(currentRun);
    }

    return {
        totalBars: totalBars || 0,
        barText: summaryParts.length === 1
            ? buildTimelinePatternBarSummary(patternById[summaryParts[0].patternId])
            : '',
        text: summaryParts.length <= 1
            ? ''
            : summaryParts.map(function (part) {
                return part.count > 1
                    ? timelineText('arrangement.summaryRepeat', {
                        label: part.label,
                        count: part.count
                    })
                    : part.label;
            }).join(', ')
    };
}

function buildTimelineDisplayGroups(entries, patternLibrary) {
    const groups = [];
    const patternById = {};

    (patternLibrary || []).forEach(function (pattern) {
        patternById[pattern.id] = pattern;
    });

    (entries || []).forEach(function (entry, entryIndex) {
        const previousGroup = groups.length > 0 ? groups[groups.length - 1] : null;
        const gapBeforeBars = normalizeTimelineGapBeforeBars(entry.gapBeforeBars);
        const targetSignature = getTimelineTargetSignature(entry.targetInstruments);
        const pattern = patternById[entry.patternId] || null;
        const labelName = pattern ? (pattern.labelName || pattern.name || '') : '';
        const handSignature = pattern && pattern.instrument === 'Djembe'
            ? String(entry.handMode || 'auto')
            : '';
        const blockId = String(entry.blockId || '');
        const parallelGroupId = String(entry.parallelGroupId || '');
        const sectionTempoSignature = entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
            ? ''
            : String(normalizeTimelineTempo(entry.sectionTempo));

        if (previousGroup && gapBeforeBars === 0 &&
            previousGroup.labelName === labelName &&
            previousGroup.targetSignature === targetSignature &&
            previousGroup.handSignature === handSignature &&
            previousGroup.sectionTempoSignature === sectionTempoSignature &&
            previousGroup.blockId === blockId &&
            previousGroup.parallelGroupId === parallelGroupId) {
            previousGroup.entries.push(entry);
            previousGroup.count += 1;
            previousGroup.endIndex = entryIndex + 1;
            return;
        }

        groups.push({
            patternId: entry.patternId,
            labelName: labelName,
            targetSignature: targetSignature,
            handSignature: handSignature,
            sectionTempoSignature: sectionTempoSignature,
            blockId: blockId,
            parallelGroupId: parallelGroupId,
            gapBeforeBars: gapBeforeBars,
            entries: [entry],
            count: 1,
            startIndex: entryIndex,
            endIndex: entryIndex + 1
        });
    });

    return groups;
}

function getTimelineEntryCloneSignature(entry) {
    if (!entry) {
        return '';
    }

    const targetSignature = getTimelineTargetSignature(entry.targetInstruments);
    return [
        entry.patternId || '',
        entry.patternSourceKey || '',
        entry.handMode || '',
        targetSignature
    ].join('::');
}

function getTimelineGroupRepeatInfo(group) {
    const entries = group && Array.isArray(group.entries) ? group.entries : [];
    const totalCount = entries.length;

    if (totalCount <= 1) {
        return {
            unitLength: totalCount,
            repeatCount: totalCount > 0 ? 1 : 0
        };
    }

    const entrySignatures = entries.map(getTimelineEntryCloneSignature);

    for (let unitLength = 1; unitLength <= totalCount; unitLength++) {
        if (totalCount % unitLength !== 0) {
            continue;
        }

        let isRepeatedUnit = true;
        for (let entryIndex = 0; entryIndex < totalCount; entryIndex++) {
            if (entrySignatures[entryIndex] !== entrySignatures[entryIndex % unitLength]) {
                isRepeatedUnit = false;
                break;
            }
        }

        if (isRepeatedUnit) {
            return {
                unitLength: unitLength,
                repeatCount: totalCount / unitLength
            };
        }
    }

    return {
        unitLength: totalCount,
        repeatCount: 1
    };
}

function normalizeTimelineGroupRepeatCount(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue) || numericValue < 1) {
        return 1;
    }
    return Math.max(1, Math.min(100, Math.round(numericValue)));
}

function setTimelineGroupRepeatCount(group, repeatInfo, nextRepeatCount) {
    if (!group || !repeatInfo || repeatInfo.unitLength <= 0) {
        return;
    }

    const normalizedRepeatCount = normalizeTimelineGroupRepeatCount(nextRepeatCount);
    const gapBeforeBars = normalizeTimelineGapBeforeBars(group.gapBeforeBars);
    const sourceEntries = group.entries.slice(0, repeatInfo.unitLength);
    const replacementEntries = [];

    for (let repeatIndex = 0; repeatIndex < normalizedRepeatCount; repeatIndex++) {
        sourceEntries.forEach(function (sourceEntry) {
            const clonedEntry = cloneTimelineEntry(sourceEntry);
            if (clonedEntry) {
                clonedEntry.gapBeforeBars = 0;
                replacementEntries.push(clonedEntry);
            }
        });
    }

    if (replacementEntries.length === 0) {
        return;
    }
    replacementEntries[0].gapBeforeBars = gapBeforeBars;

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    timelineState.entries.splice.apply(timelineState.entries, [group.startIndex, group.count].concat(replacementEntries));
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function getTimelineRowRepeatInfo(rowGroups) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(function (group) {
        return group && !isTimelineOverlayGroup(group);
    }) : [];
    const groupRepeatInfos = groups.map(function (group) {
        return {
            group: group,
            repeatInfo: getTimelineGroupRepeatInfo(group)
        };
    }).filter(function (info) {
        return info.repeatInfo && info.repeatInfo.unitLength > 0;
    });

    if (groupRepeatInfos.length === 0) {
        return {
            groupRepeatInfos: [],
            repeatCount: 0,
            mixed: false,
            startIndex: 0,
            endIndex: 0
        };
    }

    const repeatCounts = groupRepeatInfos.map(function (info) {
        return normalizeTimelineGroupRepeatCount(info.repeatInfo.repeatCount);
    });
    const displayedRepeatCount = Math.max.apply(null, repeatCounts.concat(1));
    const mixed = repeatCounts.some(function (repeatCount) {
        return repeatCount !== displayedRepeatCount && repeatCount !== 1;
    });

    return {
        groupRepeatInfos: groupRepeatInfos,
        repeatCount: displayedRepeatCount,
        mixed: mixed,
        startIndex: Math.min.apply(null, groupRepeatInfos.map(function (info) {
            return info.group.startIndex;
        })),
        endIndex: Math.max.apply(null, groupRepeatInfos.map(function (info) {
            return info.group.endIndex;
        }))
    };
}

function setTimelineRowRepeatCount(rowGroups, nextRepeatCount) {
    const rowRepeatInfo = getTimelineRowRepeatInfo(rowGroups);
    if (!rowRepeatInfo.groupRepeatInfos.length) {
        return;
    }

    const normalizedRepeatCount = normalizeTimelineGroupRepeatCount(nextRepeatCount);
    const gapBeforeBars = getTimelineRowGapBeforeBars(rowGroups);
    const replacementEntries = [];

    rowRepeatInfo.groupRepeatInfos
        .slice()
        .sort(function (leftInfo, rightInfo) {
            return leftInfo.group.startIndex - rightInfo.group.startIndex;
        })
        .forEach(function (info) {
            const sourceEntries = info.group.entries.slice(0, info.repeatInfo.unitLength);
            const groupReplacementStart = replacementEntries.length;
            for (let repeatIndex = 0; repeatIndex < normalizedRepeatCount; repeatIndex++) {
                sourceEntries.forEach(function (sourceEntry) {
                    const clonedEntry = cloneTimelineEntry(sourceEntry);
                    if (clonedEntry) {
                        clonedEntry.gapBeforeBars = 0;
                        replacementEntries.push(clonedEntry);
                    }
                });
            }
            if (replacementEntries[groupReplacementStart]) {
                replacementEntries[groupReplacementStart].gapBeforeBars = gapBeforeBars;
            }
        });

    if (replacementEntries.length === 0) {
        return;
    }

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    timelineState.entries.splice.apply(
        timelineState.entries,
        [rowRepeatInfo.startIndex, rowRepeatInfo.endIndex - rowRepeatInfo.startIndex].concat(replacementEntries)
    );
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function getTimelineRowTempoInfo(rowGroups, inheritedTempo) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(function (group) {
        return group && !isTimelineOverlayGroup(group);
    }) : [];
    const tempoValues = [];
    groups.forEach(function (group) {
        (Array.isArray(group.entries) ? group.entries : []).forEach(function (entry) {
            const normalizedTempo = entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
                ? null
                : normalizeTimelineTempo(entry.sectionTempo);
            tempoValues.push(normalizedTempo);
        });
    });
    if (tempoValues.length === 0) {
        return {
            tempo: null,
            effectiveTempo: normalizeTimelineTempo(inheritedTempo),
            mixed: false
        };
    }
    const firstTempo = tempoValues[0];
    const mixed = tempoValues.some(function (tempoValue) {
        return tempoValue !== firstTempo;
    });
    return {
        tempo: firstTempo,
        effectiveTempo: firstTempo === null || mixed
            ? normalizeTimelineTempo(inheritedTempo)
            : normalizeTimelineTempo(firstTempo),
        mixed: mixed
    };
}

function setTimelineRowSectionTempo(rowGroups, nextTempoValue) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(function (group) {
        return group && !isTimelineOverlayGroup(group);
    }) : [];
    if (groups.length === 0) {
        return;
    }
    const normalizedTempo = nextTempoValue === null || nextTempoValue === undefined || nextTempoValue === ''
        ? null
        : normalizeTimelineTempo(nextTempoValue);
    const willChange = groups.some(function (group) {
        return (Array.isArray(group.entries) ? group.entries : []).some(function (entry) {
            const currentTempo = entry.sectionTempo === null || entry.sectionTempo === undefined || entry.sectionTempo === ''
                ? null
                : normalizeTimelineTempo(entry.sectionTempo);
            return currentTempo !== normalizedTempo;
        });
    });
    if (!willChange) {
        return;
    }
    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    groups.forEach(function (group) {
        (Array.isArray(group.entries) ? group.entries : []).forEach(function (entry) {
            entry.sectionTempo = normalizedTempo;
        });
    });
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function getTimelineRowEntryRange(rowGroups) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(Boolean) : [];
    if (groups.length === 0) {
        return null;
    }
    const startIndex = Math.min.apply(null, groups.map(function (group) {
        return group.startIndex;
    }));
    const endIndex = Math.max.apply(null, groups.map(function (group) {
        return group.endIndex;
    }));
    if (!Number.isFinite(startIndex) || !Number.isFinite(endIndex) || endIndex <= startIndex) {
        return null;
    }
    return {
        startIndex: startIndex,
        endIndex: endIndex,
        count: endIndex - startIndex
    };
}

function moveTimelineRow(visualRows, rowIndex, direction) {
    const rows = Array.isArray(visualRows) ? visualRows : [];
    const currentRange = getTimelineRowEntryRange(rows[rowIndex]);
    const neighborIndex = Number(rowIndex) + (direction < 0 ? -1 : 1);
    const neighborRange = getTimelineRowEntryRange(rows[neighborIndex]);
    if (!currentRange || !neighborRange) {
        return;
    }

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    const movingEntries = timelineState.entries.splice(currentRange.startIndex, currentRange.count);
    const insertIndex = direction < 0
        ? neighborRange.startIndex
        : neighborRange.endIndex - currentRange.count;
    timelineState.entries.splice.apply(timelineState.entries, [insertIndex, 0].concat(movingEntries));
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function buildPatternLibraryGroups(patternLibrary) {
    const groups = [];

    (patternLibrary || []).forEach(function (pattern) {
        const previousGroup = groups.length > 0 ? groups[groups.length - 1] : null;
        const groupKey = [
            pattern.instrument || '',
            pattern.sourceInstrument || '',
            pattern.labelType || '',
            pattern.labelName || ''
        ].join('::');

        if (previousGroup && previousGroup.groupKey === groupKey) {
            previousGroup.patterns.push(pattern);
            return;
        }

        groups.push({
            groupKey: groupKey,
            patterns: [pattern]
        });
    });

    return groups;
}

function buildPatternLibraryBlocks(defaultEntries, patternLibrary) {
    const blocks = [];
    const uniqueBlocks = [];
    const seenBlockSignatures = {};
    const patternById = {};

    (patternLibrary || []).forEach(function (pattern) {
        patternById[pattern.id] = pattern;
    });

    (defaultEntries || []).forEach(function (entry) {
        const pattern = patternById[entry.patternId] || null;
        if (!pattern) {
            return;
        }

        const groupKey = [
            pattern.instrument || '',
            pattern.sourceInstrument || '',
            pattern.labelType || '',
            pattern.labelName || ''
        ].join('::');
        const previousBlock = blocks.length > 0 ? blocks[blocks.length - 1] : null;

        if (!previousBlock || previousBlock.groupKey !== groupKey) {
            blocks.push({
                groupKey: groupKey,
                entries: [entry],
                patterns: [pattern]
            });
            return;
        }

        const entrySignature = getTimelineEntryCloneSignature(entry);
        const alreadyIncluded = previousBlock.entries.some(function (existingEntry) {
            return getTimelineEntryCloneSignature(existingEntry) === entrySignature;
        });
        if (!alreadyIncluded) {
            previousBlock.entries.push(entry);
        }
        if (!previousBlock.patterns.some(function (existingPattern) {
            return existingPattern.id === pattern.id;
        })) {
            previousBlock.patterns.push(pattern);
        }
    });

    blocks.forEach(function (block) {
        const blockSignature = (block.patterns || []).map(function (pattern) {
            return pattern && pattern.id ? pattern.id : '';
        }).join('|');

        if (seenBlockSignatures[blockSignature]) {
            return;
        }

        seenBlockSignatures[blockSignature] = true;
        uniqueBlocks.push(block);
    });

    return uniqueBlocks;
}

function buildPatternLibraryGroupSummary(patternGroup) {
    const patterns = patternGroup && Array.isArray(patternGroup.patterns) ? patternGroup.patterns : [];
    const summaryParts = [];

    patterns.forEach(function (pattern, patternIndex) {
        const barCount = Array.isArray(pattern.bars) ? pattern.bars.length : 0;
        if (barCount <= 0) {
            return;
        }
        summaryParts.push(barCount > 1
            ? timelineText('arrangement.barWithCount', {
                number: patternIndex + 1,
                bars: getTimelineBarCountLabel(barCount)
            })
            : timelineText('arrangement.bar', { number: patternIndex + 1 }));
    });

    return summaryParts.join(', ');
}

function getPatternById(patternLibrary, patternId) {
    return (patternLibrary || []).find(function (pattern) {
        return pattern.id === patternId;
    }) || null;
}

function doTimelineTargetsOverlap(targetsA, targetsB) {
    const leftTargets = Array.isArray(targetsA) ? targetsA : [];
    const rightTargets = Array.isArray(targetsB) ? targetsB : [];
    return leftTargets.some(function (targetName) {
        return rightTargets.indexOf(targetName) !== -1;
    });
}

function buildTimelineVisualRows(entryGroups, patternLibrary) {
    const rows = [];
    let rowIndex = 0;

    while (rowIndex < entryGroups.length) {
        const firstGroup = entryGroups[rowIndex];
        const firstEntry = firstGroup && firstGroup.entries ? firstGroup.entries[0] : null;
        const firstPattern = firstEntry ? getPatternById(patternLibrary, firstEntry.patternId) : null;

        if (!firstPattern) {
            rows.push([firstGroup]);
            rowIndex += 1;
            continue;
        }

        const currentRow = [firstGroup];
        const currentTargets = getTimelineTargetSignature(firstEntry.targetInstruments).split('|').filter(Boolean);
        const rowParallelGroupId = String(firstGroup.parallelGroupId || '');
        let nextIndex = rowIndex + 1;

        while (nextIndex < entryGroups.length) {
            const nextGroup = entryGroups[nextIndex];
            const nextEntry = nextGroup && nextGroup.entries ? nextGroup.entries[0] : null;
            const nextPattern = nextEntry ? getPatternById(patternLibrary, nextEntry.patternId) : null;
            if (!nextPattern) {
                break;
            }
            if (rowParallelGroupId) {
                if (String(nextGroup.parallelGroupId || '') !== rowParallelGroupId) {
                    break;
                }
            } else if (nextPattern.labelType !== firstPattern.labelType) {
                break;
            } else if ((firstGroup.blockId || nextGroup.blockId) && firstGroup.blockId !== nextGroup.blockId) {
                break;
            } else if (firstGroup.sectionTempoSignature !== nextGroup.sectionTempoSignature) {
                break;
            }
            if (!rowParallelGroupId && nextGroup.count !== firstGroup.count) {
                break;
            }
            if (rowParallelGroupId && isTimelineOverlayGroup(nextGroup)) {
                currentRow.push(nextGroup);
                nextIndex += 1;
                continue;
            }
            if (doTimelineTargetsOverlap(currentTargets, nextEntry.targetInstruments)) {
                break;
            }

            currentRow.push(nextGroup);
            nextEntry.targetInstruments.forEach(function (targetName) {
                if (currentTargets.indexOf(targetName) === -1) {
                    currentTargets.push(targetName);
                }
            });
            nextIndex += 1;
        }

        rows.push(currentRow);
        rowIndex = nextIndex;
    }

    return rows;
}

function doesTimelinePatternContinue(pattern) {
    if (!pattern || pattern.labelType !== 'Begleitung' || !Array.isArray(pattern.bars)) {
        return false;
    }
    return pattern.bars.some(function (bar) {
        return getTimelineRepeatMarkerList(bar.repeat && bar.repeat.end).some(isTimelineContinuationMarker);
    });
}

function getTimelineGroupTargets(group) {
    const firstEntry = group && Array.isArray(group.entries) ? group.entries[0] : null;
    return getTimelineTargetSignature(firstEntry && firstEntry.targetInstruments)
        .split('|')
        .filter(Boolean);
}

function doTimelineTargetListsOverlap(targetsA, targetsB) {
    return (Array.isArray(targetsA) ? targetsA : []).some(function (targetName) {
        return (Array.isArray(targetsB) ? targetsB : []).indexOf(targetName) !== -1;
    });
}

function getTimelineRowPattern(rowGroups, patternLibrary) {
    const firstGroup = Array.isArray(rowGroups) && rowGroups.length > 0 ? rowGroups[0] : null;
    return firstGroup ? getPatternById(patternLibrary, firstGroup.patternId) : null;
}

function findFirstTimelineAccompanimentRowIndex(visualRows, patternLibrary) {
    const firstIndex = (visualRows || []).findIndex(function (rowGroups) {
        return (rowGroups || []).some(function (group) {
            const pattern = getPatternById(patternLibrary, group.patternId);
            return pattern && pattern.labelType === 'Begleitung';
        });
    });
    return firstIndex === -1 ? 0 : firstIndex;
}

function buildTimelineContinuationBlocks(visualRows, patternLibrary) {
    const blocks = [];
    const continuingGroupKeys = {};
    const firstAccompanimentRowIndex = findFirstTimelineAccompanimentRowIndex(visualRows, patternLibrary);

    (visualRows || []).forEach(function (rowGroups, rowIndex) {
        (rowGroups || []).forEach(function (group) {
            const pattern = getPatternById(patternLibrary, group.patternId);
            if (!doesTimelinePatternContinue(pattern)) {
                return;
            }

            const targets = getTimelineGroupTargets(group);
            const hasPreviousAccompanimentTargetOccurrence = visualRows
                .slice(firstAccompanimentRowIndex, rowIndex)
                .some(function (previousRowGroups) {
                    const previousRowPattern = getTimelineRowPattern(previousRowGroups, patternLibrary);
                    if (!previousRowPattern || previousRowPattern.labelType !== 'Begleitung') {
                        return false;
                    }
                    return (previousRowGroups || []).some(function (previousGroup) {
                        return doTimelineTargetListsOverlap(targets, getTimelineGroupTargets(previousGroup));
                    });
                });
            const startRowIndex = hasPreviousAccompanimentTargetOccurrence
                ? rowIndex
                : firstAccompanimentRowIndex;
            let endRowIndex = visualRows.length;
            for (let nextRowIndex = startRowIndex + 1; nextRowIndex < visualRows.length; nextRowIndex++) {
                const nextRowPattern = getTimelineRowPattern(visualRows[nextRowIndex], patternLibrary);
                if (!nextRowPattern || nextRowPattern.labelType !== 'Begleitung') {
                    continue;
                }
                const hasReplacement = (visualRows[nextRowIndex] || []).some(function (nextGroup) {
                    if (nextGroup === group) {
                        return false;
                    }
                    return doTimelineTargetListsOverlap(targets, getTimelineGroupTargets(nextGroup));
                });
                if (hasReplacement) {
                    endRowIndex = nextRowIndex;
                    break;
                }
            }

            const blockKey = String(group.startIndex) + ':' + String(group.endIndex);
            continuingGroupKeys[blockKey] = true;
            blocks.push({
                group: group,
                pattern: pattern,
                laneIndex: blocks.length,
                startRowIndex: startRowIndex,
                span: Math.max(1, endRowIndex - startRowIndex),
                targets: targets
            });
        });
    });

    return {
        blocks: blocks,
        continuingGroupKeys: continuingGroupKeys
    };
}

function getTimelineGroupKey(group) {
    return String(group && group.startIndex) + ':' + String(group && group.endIndex);
}

function isTimelineOverlayEntry(entry) {
    return entry && entry.overlayRepeatIndex !== null && entry.overlayRepeatIndex !== undefined;
}

function pruneTimelineOrphanOverlayEntries(entries) {
    const sourceEntries = Array.isArray(entries) ? entries : [];
    const prunedEntries = [];
    let entryIndex = 0;

    while (entryIndex < sourceEntries.length) {
        const entry = sourceEntries[entryIndex];
        const parallelGroupId = String(entry && entry.parallelGroupId || '');

        if (!parallelGroupId) {
            if (!isTimelineOverlayEntry(entry)) {
                prunedEntries.push(entry);
            }
            entryIndex += 1;
            continue;
        }

        let blockEndIndex = entryIndex + 1;
        while (blockEndIndex < sourceEntries.length &&
            String(sourceEntries[blockEndIndex] && sourceEntries[blockEndIndex].parallelGroupId || '') === parallelGroupId) {
            blockEndIndex += 1;
        }

        const blockEntries = sourceEntries.slice(entryIndex, blockEndIndex);
        const hasBaseEntry = blockEntries.some(function (blockEntry) {
            return blockEntry && !isTimelineOverlayEntry(blockEntry);
        });

        blockEntries.forEach(function (blockEntry) {
            if (!isTimelineOverlayEntry(blockEntry) || hasBaseEntry) {
                prunedEntries.push(blockEntry);
            }
        });

        entryIndex = blockEndIndex;
    }

    return prunedEntries;
}

function isTimelineOverlayGroup(group) {
    const firstEntry = group && Array.isArray(group.entries) ? group.entries[0] : null;
    return isTimelineOverlayEntry(firstEntry);
}

function getTimelineVisibleRowGroups(rowGroups, continuationInfo) {
    const continuingGroupKeys = continuationInfo && continuationInfo.continuingGroupKeys
        ? continuationInfo.continuingGroupKeys
        : {};
    return (Array.isArray(rowGroups) ? rowGroups : []).filter(function (group) {
        return !continuingGroupKeys[getTimelineGroupKey(group)] &&
            !isTimelineOverlayGroup(group);
    });
}

function buildPatternDisplayLabelMap(patternLibrary) {
    const patternGroups = buildPatternLibraryGroups(patternLibrary);
    const displayNameByPatternId = {};

    patternGroups.forEach(function (patternGroup, groupIndex) {
        const firstPattern = patternGroup.patterns[0];
        const labelText = getTimelinePatternLabel(firstPattern);
        const instrumentText = firstPattern.sourceInstrument || firstPattern.instrument || 'Instrument';
        const displayName = 'P' + (groupIndex + 1) + ' - ' + instrumentText + ' / ' + labelText;

        patternGroup.patterns.forEach(function (pattern) {
            displayNameByPatternId[pattern.id] = displayName;
        });
    });

    return {
        groups: patternGroups,
        displayNameByPatternId: displayNameByPatternId
    };
}

function insertTimelineEntryAtIndex(payload, targetIndex) {
    const insertIndex = Math.max(0, Math.min(targetIndex, timelineState.entries.length));

    if (!payload) {
        return;
    }

    if (payload.type === 'pattern') {
        const sourcePattern = findPatternById(payload.patternId);
        if (!sourcePattern) {
            return;
        }
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        timelineState.entries.splice(insertIndex, 0, cloneTimelineEntryFromPattern(sourcePattern));
        updateTimelineMetadataNode();
        renderTimelinePanel();
        return;
    }

    if (payload.type === 'pattern-group') {
        const sourceEntries = Array.isArray(payload.entries) ? payload.entries : [];
        const newBlockId = nextTimelineBlockId();
        const entriesToInsert = sourceEntries.map(function (entry) {
            const sourcePattern = findPatternById(entry.patternId);
            if (!sourcePattern) {
                return null;
            }
            return cloneTimelineEntryFromPattern(sourcePattern, {
                blockId: newBlockId,
                handMode: entry.handMode || 'auto',
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            });
        }).filter(Boolean);
        if (entriesToInsert.length === 0) {
            return;
        }
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        timelineState.entries.splice.apply(timelineState.entries, [insertIndex, 0].concat(entriesToInsert));
        updateTimelineMetadataNode();
        renderTimelinePanel();
        return;
    }

    if (payload.type === 'timeline-entry-group') {
        const groupStartIndex = Number(payload.startIndex);
        const groupCount = Number(payload.count);
        if (!Number.isFinite(groupStartIndex) || !Number.isFinite(groupCount) || groupCount < 1) {
            return;
        }
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        const movedEntries = timelineState.entries.splice(groupStartIndex, groupCount);
        const adjustedIndex = groupStartIndex < insertIndex ? insertIndex - groupCount : insertIndex;
        movedEntries.forEach(function (entry) {
            entry.parallelGroupId = '';
        });
        timelineState.entries.splice.apply(timelineState.entries, [adjustedIndex, 0].concat(movedEntries));
        updateTimelineMetadataNode();
        renderTimelinePanel();
    }
}

function ensureParallelGroupForRow(rowGroups) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(Boolean) : [];
    if (groups.length === 0) {
        return '';
    }

    const existingParallelGroupId = String(groups[0].parallelGroupId || '');
    if (existingParallelGroupId) {
        return existingParallelGroupId;
    }

    const newParallelGroupId = nextTimelineParallelGroupId();
    groups.forEach(function (group) {
        (group.entries || []).forEach(function (entry) {
            entry.parallelGroupId = newParallelGroupId;
        });
        group.parallelGroupId = newParallelGroupId;
    });

    return newParallelGroupId;
}

function getTimelinePayloadCandidateEntries(payload) {
    if (!payload) {
        return [];
    }

    if (payload.type === 'pattern') {
        const sourcePattern = findPatternById(payload.patternId);
        return sourcePattern ? [{
            patternId: sourcePattern.id,
            targetInstruments: sourcePattern.defaultTargets.slice()
        }] : [];
    }

    if (payload.type === 'pattern-group') {
        return (Array.isArray(payload.entries) ? payload.entries : []).map(function (entry) {
            const sourcePattern = findPatternById(entry.patternId);
            if (!sourcePattern) {
                return null;
            }
            return {
                patternId: sourcePattern.id,
                targetInstruments: Array.isArray(entry.targetInstruments) && entry.targetInstruments.length > 0
                    ? entry.targetInstruments.slice()
                    : sourcePattern.defaultTargets.slice()
            };
        }).filter(Boolean);
    }

    if (payload.type === 'timeline-entry-group') {
        const groupStartIndex = Number(payload.startIndex);
        const groupCount = Number(payload.count);
        if (!Number.isFinite(groupStartIndex) || !Number.isFinite(groupCount) || groupCount < 1) {
            return [];
        }
        return timelineState.entries.slice(groupStartIndex, groupStartIndex + groupCount).map(function (entry) {
            return {
                id: entry.id,
                patternId: entry.patternId,
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            };
        });
    }

    return [];
}

function canInsertTimelinePayloadParallelToRow(payload, rowGroups) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(Boolean) : [];
    const candidateEntries = getTimelinePayloadCandidateEntries(payload);
    if (!payload || groups.length === 0 || candidateEntries.length === 0) {
        return false;
    }

    const candidateIds = candidateEntries.map(function (entry) {
        return entry.id;
    }).filter(Boolean);

    return !groups.some(function (group) {
        return (group.entries || []).some(function (rowEntry) {
            if (candidateIds.indexOf(rowEntry.id) !== -1) {
                return false;
            }
            return candidateEntries.some(function (candidateEntry) {
                return doTimelineTargetsOverlap(rowEntry.targetInstruments, candidateEntry.targetInstruments);
            });
        });
    });
}

function insertTimelineEntryParallelToRow(payload, rowGroups) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(Boolean) : [];
    if (groups.length === 0 || !canInsertTimelinePayloadParallelToRow(payload, rowGroups)) {
        return;
    }

    const parallelGroupId = ensureParallelGroupForRow(groups);
    const insertIndex = groups[groups.length - 1].endIndex;

    if (payload.type === 'pattern-group') {
        const sourceEntries = Array.isArray(payload.entries) ? payload.entries : [];
        const newBlockId = nextTimelineBlockId();
        const entriesToInsert = sourceEntries.map(function (entry) {
            const sourcePattern = findPatternById(entry.patternId);
            if (!sourcePattern) {
                return null;
            }
            return cloneTimelineEntryFromPattern(sourcePattern, {
                blockId: newBlockId,
                parallelGroupId: parallelGroupId,
                handMode: entry.handMode || 'auto',
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            });
        }).filter(Boolean);
        if (entriesToInsert.length === 0) {
            return;
        }
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        timelineState.entries.splice.apply(timelineState.entries, [insertIndex, 0].concat(entriesToInsert));
        updateTimelineMetadataNode();
        renderTimelinePanel();
        return;
    }

    if (payload.type === 'timeline-entry-group') {
        const groupStartIndex = Number(payload.startIndex);
        const groupCount = Number(payload.count);
        if (!Number.isFinite(groupStartIndex) || !Number.isFinite(groupCount) || groupCount < 1) {
            return;
        }
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        const movedEntries = timelineState.entries.splice(groupStartIndex, groupCount);
        const adjustedIndex = groupStartIndex < insertIndex ? insertIndex - groupCount : insertIndex;
        movedEntries.forEach(function (entry) {
            entry.parallelGroupId = parallelGroupId;
        });
        timelineState.entries.splice.apply(timelineState.entries, [adjustedIndex, 0].concat(movedEntries));
        updateTimelineMetadataNode();
        renderTimelinePanel();
    }
}

function insertTimelineOverlayIntoRepeatSlot(payload, rowGroups, repeatIndex) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(Boolean) : [];
    if (!payload || groups.length === 0) {
        return;
    }

    const normalizedRepeatIndex = Math.max(0, Math.round(Number(repeatIndex) || 0));
    const parallelGroupId = ensureParallelGroupForRow(groups);
    const insertIndex = groups.reduce(function (maxIndex, group) {
        return Math.max(maxIndex, group.endIndex);
    }, 0);

    function cloneOverlayEntry(sourceEntry) {
        const sourcePattern = findPatternById(sourceEntry.patternId);
        if (!sourcePattern || sourcePattern.labelType === 'Begleitung') {
            return null;
        }
        return cloneTimelineEntryFromPattern(sourcePattern, {
            blockId: nextTimelineBlockId(),
            parallelGroupId: parallelGroupId,
            overlayRepeatIndex: normalizedRepeatIndex,
            handMode: sourceEntry.handMode || 'auto',
            targetInstruments: Array.isArray(sourceEntry.targetInstruments) ? sourceEntry.targetInstruments.slice() : []
        });
    }

    let entriesToInsert = [];
    if (payload.type === 'pattern-group') {
        entriesToInsert = (Array.isArray(payload.entries) ? payload.entries : [])
            .map(cloneOverlayEntry)
            .filter(Boolean)
            .slice(0, 1);
    } else if (payload.type === 'timeline-entry-group') {
        const groupStartIndex = Number(payload.startIndex);
        const groupCount = Number(payload.count);
        if (!Number.isFinite(groupStartIndex) || !Number.isFinite(groupCount) || groupCount < 1) {
            return;
        }
        const movedEntries = timelineState.entries.splice(groupStartIndex, groupCount);
        entriesToInsert = movedEntries.map(function (entry) {
            const sourcePattern = findPatternById(entry.patternId);
            if (!sourcePattern || sourcePattern.labelType === 'Begleitung') {
                return null;
            }
            entry.parallelGroupId = parallelGroupId;
            entry.overlayRepeatIndex = normalizedRepeatIndex;
            return entry;
        }).filter(Boolean).slice(0, 1);
    }

    if (entriesToInsert.length === 0) {
        return;
    }

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    const existingOverlayGroup = groups.find(function (group) {
        const entry = group && group.entries && group.entries[0];
        return isTimelineOverlayEntry(entry) && Number(entry.overlayRepeatIndex) === normalizedRepeatIndex;
    });
    if (existingOverlayGroup) {
        timelineState.entries.splice(existingOverlayGroup.startIndex, existingOverlayGroup.count);
    }

    timelineState.entries.splice.apply(timelineState.entries, [insertIndex, 0].concat(entriesToInsert));
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function createTimelineDropzone(targetIndex) {
    const dropzone = document.createElement('div');
    dropzone.className = 'timeline-dropzone';
    dropzone.textContent = targetIndex === timelineState.entries.length
        ? timelineText('arrangement.drop.append')
        : timelineText('arrangement.drop.insert');
    dropzone.addEventListener('dragover', function (event) {
        event.preventDefault();
        markTimelineDropTarget(dropzone);
    });
    dropzone.addEventListener('dragleave', function () {
        unmarkTimelineDropTarget(dropzone);
    });
    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const payload = timelineActiveDragPayload || getTimelineDragPayload(
            event.dataTransfer.getData('text/plain')
        );
        setTimelineDragDropTargetsVisible(false);
        insertTimelineEntryAtIndex(payload, targetIndex);
    });
    return dropzone;
}

function createTimelineParallelDropzone(rowGroups) {
    const dropzone = document.createElement('div');
    dropzone.className = 'timeline-dropzone timeline-dropzone-inline';
    dropzone.textContent = timelineText('arrangement.drop.parallel');
    bindParallelDropTarget(dropzone, rowGroups);
    return dropzone;
}

function setTimelineCompactDragImage(event, labelText) {
    if (!event || !event.dataTransfer || typeof event.dataTransfer.setDragImage !== 'function') {
        return;
    }
    const dragImageEl = document.createElement('div');
    dragImageEl.className = 'timeline-drag-preview';
    dragImageEl.textContent = String(labelText || 'Pattern');
    document.body.appendChild(dragImageEl);
    event.dataTransfer.setDragImage(dragImageEl, 12, 12);
    window.requestAnimationFrame(function () {
        dragImageEl.remove();
    });
}

function bindParallelDropTarget(targetEl, rowGroups) {
    if (!targetEl) {
        return;
    }

    targetEl.addEventListener('dragover', function (event) {
        const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        if (!canInsertTimelinePayloadParallelToRow(payload, rowGroups)) {
            event.dataTransfer.dropEffect = 'none';
            unmarkTimelineDropTarget(targetEl);
            return;
        }
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        markTimelineDropTarget(targetEl);
    });
    targetEl.addEventListener('dragleave', function () {
        unmarkTimelineDropTarget(targetEl);
    });
    targetEl.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        setTimelineDragDropTargetsVisible(false);
        insertTimelineEntryParallelToRow(payload, rowGroups);
    });
}

function renderTimelinePatternLibrary() {
    const listEl = document.getElementById('timelinePatternList');
    const patternDisplayInfo = buildPatternDisplayLabelMap(timelineState.sourcePatterns);
    const patternGroups = Array.isArray(timelineState.sourceLibraryGroups) && timelineState.sourceLibraryGroups.length > 0
        ? timelineState.sourceLibraryGroups
        : patternDisplayInfo.groups;
    listEl.innerHTML = '';

    patternGroups.forEach(function (patternGroup) {
        const firstPattern = patternGroup.patterns[0];
        const blockSummary = buildTimelineGroupSummary({
            entries: patternGroup.entries || []
        }, timelineState.sourcePatterns);
        const totalBars = blockSummary.totalBars || patternGroup.patterns.reduce(function (sum, pattern) {
            return sum + (Array.isArray(pattern.bars) ? pattern.bars.length : 0);
        }, 0);

        const card = document.createElement('div');
        card.className = 'timeline-card';
        card.draggable = true;
        card.addEventListener('dragstart', function (event) {
            const payload = {
                type: 'pattern-group',
                entries: (patternGroup.entries || []).map(function (entry) {
                    return {
                        patternId: entry.patternId,
                        handMode: entry.handMode || 'auto',
                        targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
                    };
                })
            };
            setTimelineActiveDragPayload(payload);
            event.dataTransfer.setData('text/plain', JSON.stringify(payload));
            setTimelineCompactDragImage(event, patternTitle.textContent);
        });
        card.addEventListener('dragend', function () {
            setTimelineDragDropTargetsVisible(false);
        });

        const patternTitle = document.createElement('strong');
        const libraryDisplayParts = getTimelineDisplayParts(firstPattern, patternDisplayInfo);
        patternTitle.textContent = libraryDisplayParts.sub
            ? libraryDisplayParts.main + ' · ' + libraryDisplayParts.sub
            : libraryDisplayParts.main;
        const actionWrap = document.createElement('div');
        actionWrap.className = 'timeline-card-actions';
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.textContent = '+';
        addButton.setAttribute('aria-label', timelineText('arrangement.addPatternAtEnd'));
        addButton.addEventListener('click', function () {
            const newBlockId = nextTimelineBlockId();
            const newEntries = (patternGroup.entries || []).map(function (entry) {
                const sourcePattern = findPatternById(entry.patternId);
                if (!sourcePattern) {
                    return null;
                }
                return cloneTimelineEntryFromPattern(sourcePattern, {
                    blockId: newBlockId,
                    handMode: entry.handMode || 'auto',
                    targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
                });
            });
            if (typeof recordArrangementHistorySnapshot === 'function') {
                recordArrangementHistorySnapshot();
            }
            timelineState.entries.push.apply(timelineState.entries, newEntries.filter(Boolean));
            updateTimelineMetadataNode();
            renderTimelinePanel();
        });
        actionWrap.appendChild(addButton);
        card.appendChild(patternTitle);
        card.appendChild(actionWrap);
        listEl.appendChild(card);
    });
}

function createTimelineSectionHeader(rowGroups, rowIndex, visualRows, rowTempoInfo) {
    const rowRepeatInfo = getTimelineRowRepeatInfo(rowGroups);
    const tempoInfo = rowTempoInfo || getTimelineRowTempoInfo(rowGroups, timelineState.tempo);
    const sectionHeaderEl = document.createElement('div');
    sectionHeaderEl.className = 'timeline-section-header';

    const sectionActionWrap = document.createElement('div');
    sectionActionWrap.className = 'timeline-entry-actions timeline-section-actions';
    const sectionRepeatEl = document.createElement('label');
    sectionRepeatEl.className = 'timeline-entry-repeat-count';
    sectionRepeatEl.appendChild(document.createTextNode(timelineText('arrangement.repeatShort')));
    const sectionRepeatInputEl = document.createElement('input');
    sectionRepeatInputEl.type = 'number';
    sectionRepeatInputEl.min = '1';
    sectionRepeatInputEl.max = '100';
    sectionRepeatInputEl.step = '1';
    sectionRepeatInputEl.placeholder = rowRepeatInfo.mixed
        ? timelineText('arrangement.mixed')
        : String(normalizeTimelineGroupRepeatCount(rowRepeatInfo.repeatCount || 1));
    sectionRepeatInputEl.value = rowRepeatInfo.mixed
        ? ''
        : String(normalizeTimelineGroupRepeatCount(rowRepeatInfo.repeatCount || 1));
    sectionRepeatInputEl.addEventListener('click', function (event) {
        event.stopPropagation();
    });
    sectionRepeatInputEl.addEventListener('change', function () {
        const normalizedCount = normalizeTimelineGroupRepeatCount(sectionRepeatInputEl.value);
        sectionRepeatInputEl.value = String(normalizedCount);
        setTimelineRowRepeatCount(rowGroups, normalizedCount);
    });
    sectionRepeatEl.appendChild(sectionRepeatInputEl);

    sectionActionWrap.appendChild(sectionRepeatEl);

    const sectionTempoEl = document.createElement('label');
    sectionTempoEl.className = 'timeline-entry-repeat-count timeline-section-tempo-control';
    sectionTempoEl.appendChild(document.createTextNode(timelineText('arrangement.tempo')));
    const sectionTempoInputEl = document.createElement('input');
    sectionTempoInputEl.type = 'number';
    sectionTempoInputEl.min = '30';
    sectionTempoInputEl.max = '180';
    sectionTempoInputEl.step = '1';
    sectionTempoInputEl.placeholder = tempoInfo.mixed
        ? timelineText('arrangement.mixed')
        : String(normalizeTimelineTempo(tempoInfo.effectiveTempo));
    sectionTempoInputEl.value = tempoInfo.mixed
        ? ''
        : String(normalizeTimelineTempo(tempoInfo.effectiveTempo));
    sectionTempoInputEl.addEventListener('click', function (event) {
        event.stopPropagation();
    });
    sectionTempoInputEl.addEventListener('change', function () {
        setTimelineRowSectionTempo(rowGroups, sectionTempoInputEl.value);
    });
    sectionTempoEl.appendChild(sectionTempoInputEl);
    sectionActionWrap.appendChild(sectionTempoEl);

    const moveWrapEl = document.createElement('div');
    moveWrapEl.className = 'timeline-section-move-actions';

    const moveUpButtonEl = document.createElement('button');
    moveUpButtonEl.type = 'button';
    moveUpButtonEl.className = 'timeline-section-move-button';
    moveUpButtonEl.textContent = '↑';
    moveUpButtonEl.setAttribute('aria-label', timelineText('arrangement.moveSectionUp'));
    moveUpButtonEl.disabled = rowIndex <= 0;
    moveUpButtonEl.addEventListener('click', function (event) {
        event.stopPropagation();
        moveTimelineRow(visualRows, rowIndex, -1);
    });

    const moveDownButtonEl = document.createElement('button');
    moveDownButtonEl.type = 'button';
    moveDownButtonEl.className = 'timeline-section-move-button';
    moveDownButtonEl.textContent = '↓';
    moveDownButtonEl.setAttribute('aria-label', timelineText('arrangement.moveSectionDown'));
    moveDownButtonEl.disabled = !Array.isArray(visualRows) || rowIndex >= visualRows.length - 1;
    moveDownButtonEl.addEventListener('click', function (event) {
        event.stopPropagation();
        moveTimelineRow(visualRows, rowIndex, 1);
    });

    moveWrapEl.append(moveUpButtonEl, moveDownButtonEl);
    sectionActionWrap.appendChild(moveWrapEl);
    sectionHeaderEl.appendChild(sectionActionWrap);
    return sectionHeaderEl;
}

function getTimelineDisplayParts(pattern, patternDisplayInfo) {
    const fallbackName = pattern ? pattern.name || '' : timelineText('arrangement.pattern');
    const displayName = pattern
        ? (patternDisplayInfo.displayNameByPatternId[pattern.id] || fallbackName)
        : fallbackName;
    const splitMatch = String(displayName).match(/^(P\d+)\s*-\s*([^/]+?)\s*\/\s*(.+)$/);
    if (splitMatch) {
        return {
            main: getTimelineInstrumentLabel(splitMatch[2].trim()),
            sub: getTimelinePatternLabel(pattern, splitMatch[3].trim())
        };
    }

    return {
        main: displayName,
        sub: fallbackName && fallbackName !== displayName ? fallbackName : ''
    };
}

function appendTimelineChipLabel(parentEl, pattern, patternDisplayInfo) {
    const displayParts = getTimelineDisplayParts(pattern, patternDisplayInfo);
    const mainEl = document.createElement('strong');
    mainEl.textContent = displayParts.main;
    parentEl.appendChild(mainEl);
    if (displayParts.sub) {
        const subEl = document.createElement('small');
        subEl.textContent = displayParts.sub;
        subEl.className = 'timeline-chip-subtitle';
        parentEl.appendChild(subEl);
    }
}

function updateTimelineTrackDetailsClearance(entryCard) {
    const scrollEl = entryCard && entryCard.closest('.timeline-track-scroll');
    if (!scrollEl) {
        return;
    }

    scrollEl.style.removeProperty('--timeline-track-details-clearance');
    scrollEl.classList.remove('has-open-track-details');
    const openDetails = Array.from(scrollEl.querySelectorAll('.timeline-entry-details[open]'));
    if (openDetails.length === 0) {
        return;
    }

    window.requestAnimationFrame(function () {
        const scrollBounds = scrollEl.getBoundingClientRect();
        const detailBottom = Math.max.apply(null, openDetails.map(function (detailsEl) {
            return detailsEl.getBoundingClientRect().bottom;
        }));
        const requiredClearance = Math.max(0, Math.ceil(detailBottom - scrollBounds.bottom + 12));
        if (requiredClearance > 0) {
            scrollEl.style.setProperty('--timeline-track-details-clearance', requiredClearance + 'px');
            scrollEl.classList.add('has-open-track-details');
        }
    });
}

function getTimelineFullDisplayLabel(pattern, patternDisplayInfo) {
    const fallbackName = pattern ? pattern.name || '' : timelineText('arrangement.pattern');
    const displayName = pattern
        ? (patternDisplayInfo.displayNameByPatternId[pattern.id] || fallbackName)
        : fallbackName;
    const splitMatch = String(displayName).match(/^(P\d+)\s*-\s*([^/]+?)\s*\/\s*(.+)$/);
    if (!splitMatch) {
        return displayName;
    }

    return splitMatch[1] + ' - ' + getTimelineInstrumentLabel(splitMatch[2].trim()) +
        ' / ' + getTimelinePatternLabel(pattern, splitMatch[3].trim());
}

function createTimelineEntryChip(group, rowGroups, patternDisplayInfo) {
    const entry = group.entries[0];
    const pattern = findPatternById(group.patternId);
    if (!pattern) {
        return null;
    }

    const entryCard = document.createElement('div');
    entryCard.className = 'timeline-entry timeline-entry-chip';
    entryCard.draggable = true;
    bindParallelDropTarget(entryCard, rowGroups);
    entryCard.addEventListener('dblclick', function (event) {
        if (event.target && event.target.closest('input, select, button')) {
            return;
        }
        const detailsEl = entryCard.querySelector('.timeline-entry-details');
        if (detailsEl) {
            detailsEl.open = !detailsEl.open;
        }
    });
    entryCard.addEventListener('dragstart', function (event) {
        if (event.target && event.target.closest('input, select, button')) {
            event.preventDefault();
            return;
        }
        const payload = {
            type: 'timeline-entry-group',
            startIndex: group.startIndex,
            count: group.count
        };
        setTimelineActiveDragPayload(payload);
        event.dataTransfer.setData('text/plain', JSON.stringify(payload));
        setTimelineCompactDragImage(event, getTimelinePatternLabel(pattern));
    });
    entryCard.addEventListener('dragend', function () {
        setTimelineDragDropTargetsVisible(false);
    });

    const chipHeadEl = document.createElement('div');
    chipHeadEl.className = 'timeline-chip-head';
    const chipLabelEl = document.createElement('div');
    chipLabelEl.className = 'timeline-chip-label';
    appendTimelineChipLabel(chipLabelEl, pattern, patternDisplayInfo);
    const detailsEl = document.createElement('details');
    detailsEl.className = 'timeline-entry-details';
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'timeline-chip-remove';
    removeButton.textContent = 'x';
    removeButton.setAttribute('aria-label', timelineText('arrangement.removePattern'));
    removeButton.addEventListener('click', function (event) {
        event.stopPropagation();
        if (detailsEl.open) {
            detailsEl.open = false;
            removeButton.setAttribute('aria-label', timelineText('arrangement.removePattern'));
            return;
        }
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        timelineState.entries.splice(group.startIndex, group.count);
        updateTimelineMetadataNode();
        renderTimelinePanel();
    });
    chipHeadEl.appendChild(chipLabelEl);
    chipHeadEl.appendChild(removeButton);
    const groupSummary = buildTimelineGroupSummary(group, timelineState.sourcePatterns);
    const metaEl = document.createElement('small');
    const displayedBarCount = groupSummary.totalBars || (Array.isArray(pattern.bars) ? pattern.bars.length : 0);
    metaEl.textContent = groupSummary.barText || getTimelineBarCountLabel(displayedBarCount);
    entryCard.appendChild(chipHeadEl);

    detailsEl.addEventListener('toggle', function () {
        entryCard.classList.toggle('has-open-details', detailsEl.open);
        updateTimelineTrackDetailsClearance(entryCard);
        removeButton.setAttribute(
            'aria-label',
            detailsEl.open
                ? timelineText('arrangement.collapsePattern')
                : timelineText('arrangement.removePattern')
        );
    });
    const detailsSummaryEl = document.createElement('summary');
    detailsSummaryEl.textContent = timelineText('arrangement.settings');
    detailsEl.appendChild(detailsSummaryEl);

    const detailBodyEl = document.createElement('div');
    detailBodyEl.className = 'timeline-entry-detail-body';
    metaEl.className = 'timeline-chip-meta';
    detailBodyEl.appendChild(metaEl);

    if (groupSummary.text) {
        const summaryEl = document.createElement('small');
        summaryEl.textContent = groupSummary.text;
        summaryEl.className = 'timeline-card-summary';
        detailBodyEl.appendChild(summaryEl);
    }

    if (pattern.instrument === 'Djembe') {
        const handWrap = document.createElement('div');
        handWrap.className = 'timeline-entry-targets';
        const handLabelEl = document.createElement('label');
        handLabelEl.appendChild(document.createTextNode(timelineText('arrangement.handMode')));
        const handSelectEl = document.createElement('select');
        [
            { value: 'auto', label: timelineText('arrangement.handModeAutomatic') },
            { value: 'h2h', label: 'H2H' },
            { value: 'hoh', label: 'HOH' }
        ].forEach(function (optionData) {
            const optionEl = document.createElement('option');
            optionEl.value = optionData.value;
            optionEl.textContent = optionData.label;
            handSelectEl.appendChild(optionEl);
        });
        handSelectEl.value = entry.handMode || 'auto';
        handSelectEl.addEventListener('change', function () {
            if (group.entries.some(function (groupEntry) {
                return (groupEntry.handMode || 'auto') !== handSelectEl.value;
            }) && typeof recordArrangementHistorySnapshot === 'function') {
                recordArrangementHistorySnapshot();
            }
            group.entries.forEach(function (groupEntry) {
                groupEntry.handMode = handSelectEl.value;
            });
            updateTimelineMetadataNode();
        });
        handLabelEl.appendChild(handSelectEl);
        handWrap.appendChild(handLabelEl);
        detailBodyEl.appendChild(handWrap);
    }

    if (pattern.instrument === 'Djembe' || pattern.instrument === 'Bässe') {
        const targetWrap = document.createElement('div');
        targetWrap.className = 'timeline-entry-targets';
        const selectableTargets = pattern.instrument === 'Djembe'
            ? timelineDjembeTargets
            : timelineBassTargets;
        selectableTargets.forEach(function (targetName) {
            const labelEl = document.createElement('label');
            const checkboxEl = document.createElement('input');
            checkboxEl.type = 'checkbox';
            checkboxEl.checked = entry.targetInstruments.indexOf(targetName) !== -1;
            checkboxEl.addEventListener('change', function () {
                if (typeof recordArrangementHistorySnapshot === 'function') {
                    recordArrangementHistorySnapshot();
                }
                group.entries.forEach(function (groupEntry) {
                    if (checkboxEl.checked) {
                        if (groupEntry.targetInstruments.indexOf(targetName) === -1) {
                            groupEntry.targetInstruments.push(targetName);
                        }
                    } else {
                        groupEntry.targetInstruments = groupEntry.targetInstruments.filter(function (name) {
                            return name !== targetName;
                        });
                        if (groupEntry.targetInstruments.length === 0) {
                            groupEntry.targetInstruments = [targetName];
                            checkboxEl.checked = true;
                        }
                    }
                });
                updateTimelineMetadataNode();
            });
            labelEl.appendChild(checkboxEl);
            labelEl.appendChild(document.createTextNode(getTimelineInstrumentLabel(targetName)));
            targetWrap.appendChild(labelEl);
        });
        detailBodyEl.appendChild(targetWrap);
    } else {
        const fixedTargetEl = document.createElement('div');
        fixedTargetEl.className = 'timeline-entry-targets';
        fixedTargetEl.textContent = timelineText('arrangement.instrumentLabel', {
            instrument: getTimelineInstrumentLabel(pattern.instrument)
        });
        detailBodyEl.appendChild(fixedTargetEl);
    }

    detailsEl.appendChild(detailBodyEl);
    entryCard.appendChild(detailsEl);
    return entryCard;
}

function timelineRowHasAccompaniment(rowGroups) {
    return (Array.isArray(rowGroups) ? rowGroups : []).some(function (group) {
        if (!group || isTimelineOverlayGroup(group)) {
            return false;
        }
        const pattern = findPatternById(group.patternId);
        return pattern && pattern.labelType === 'Begleitung';
    });
}

function createTimelineOverlayGrid(rowGroups, patternDisplayInfo) {
    const rowRepeatInfo = getTimelineRowRepeatInfo(rowGroups);
    const repeatCount = normalizeTimelineGroupRepeatCount(rowRepeatInfo.repeatCount || 1);
    if (!timelineRowHasAccompaniment(rowGroups) || repeatCount <= 1) {
        return null;
    }

    const overlayGroupsByRepeat = {};
    (Array.isArray(rowGroups) ? rowGroups : []).forEach(function (group) {
        const entry = group && Array.isArray(group.entries) ? group.entries[0] : null;
        if (!isTimelineOverlayEntry(entry)) {
            return;
        }
        const repeatIndex = Math.max(0, Math.round(Number(entry.overlayRepeatIndex) || 0));
        if (repeatIndex < repeatCount && !overlayGroupsByRepeat[repeatIndex]) {
            overlayGroupsByRepeat[repeatIndex] = group;
        }
    });

    const gridEl = document.createElement('div');
    gridEl.className = 'timeline-overlay-grid';
    gridEl.style.gridTemplateColumns = 'repeat(' + Math.min(20, repeatCount) + ', minmax(130px, 1fr))';

    for (let repeatIndex = 0; repeatIndex < repeatCount; repeatIndex++) {
        const cellEl = document.createElement('div');
        cellEl.className = 'timeline-overlay-cell';
        cellEl.title = timelineText('arrangement.soloForRepeat', {
            count: repeatIndex + 1
        });
        cellEl.addEventListener('dragover', function (event) {
            event.preventDefault();
        });
        cellEl.addEventListener('drop', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
            setTimelineDragDropTargetsVisible(false);
            insertTimelineOverlayIntoRepeatSlot(payload, rowGroups, repeatIndex);
        });

        const overlayGroup = overlayGroupsByRepeat[repeatIndex];
        if (overlayGroup) {
            const entry = overlayGroup.entries[0];
            const pattern = findPatternById(overlayGroup.patternId);
            const chipEl = document.createElement('div');
            chipEl.className = 'timeline-overlay-chip';
            chipEl.draggable = true;
            chipEl.addEventListener('dragstart', function (event) {
                const payload = {
                    type: 'timeline-entry-group',
                    startIndex: overlayGroup.startIndex,
                    count: overlayGroup.count
                };
                setTimelineActiveDragPayload(payload);
                event.dataTransfer.setData('text/plain', JSON.stringify(payload));
                setTimelineCompactDragImage(event, pattern
                    ? getTimelinePatternLabel(pattern)
                    : timelineText('arrangement.pattern'));
            });
            chipEl.addEventListener('dragend', function () {
                setTimelineDragDropTargetsVisible(false);
            });
            const titleEl = document.createElement('span');
            titleEl.className = 'timeline-overlay-chip-label';
            if (pattern) {
                appendTimelineChipLabel(titleEl, pattern, patternDisplayInfo);
            } else {
                titleEl.textContent = timelineText('arrangement.pattern');
            }
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = 'x';
            removeButton.setAttribute('aria-label', timelineText('arrangement.removeSolo'));
            removeButton.addEventListener('click', function (event) {
                event.stopPropagation();
                if (typeof recordArrangementHistorySnapshot === 'function') {
                    recordArrangementHistorySnapshot();
                }
                timelineState.entries.splice(overlayGroup.startIndex, overlayGroup.count);
                updateTimelineMetadataNode();
                renderTimelinePanel();
            });
            chipEl.appendChild(titleEl);
            chipEl.appendChild(removeButton);
            cellEl.appendChild(chipEl);
        } else {
            cellEl.textContent = String(repeatIndex + 1);
        }

        gridEl.appendChild(cellEl);
    }

    return gridEl;
}

function alignPatternLibraryCardWidths() {
    const listEl = document.getElementById('timelinePatternList');
    if (!listEl) {
        return;
    }

    const cards = Array.from(listEl.querySelectorAll('.timeline-card'));
    if (cards.length === 0) {
        return;
    }

    cards.forEach(function (cardEl) {
        cardEl.style.width = '';
    });

    const availableWidth = Math.max(180, Math.floor(listEl.clientWidth));
    const targetWidth = availableWidth;
    document.documentElement.style.setProperty('--timeline-library-card-width', targetWidth + 'px');

    cards.forEach(function (cardEl) {
        cardEl.style.width = targetWidth + 'px';
    });
}

function getTimelineAccompanimentPatternFromPayload(payload, targetInstrument) {
    return getTimelinePayloadCandidateEntries(payload).map(function (candidateEntry) {
        const pattern = findPatternById(candidateEntry.patternId);
        const candidateTargets = Array.isArray(candidateEntry.targetInstruments)
            ? candidateEntry.targetInstruments
            : [];
        if (!pattern || pattern.labelType !== 'Begleitung' ||
            candidateTargets.indexOf(targetInstrument) === -1) {
            return null;
        }
        return pattern;
    }).filter(Boolean)[0] || null;
}

function resolveTimelineAccompanimentLaneCollisions(targetInstrument) {
    let nextAvailableBar = 1;
    timelineState.accompanimentSegments
        .filter(function (segment) {
            return segment.targetInstrument === targetInstrument;
        })
        .sort(function (leftSegment, rightSegment) {
            return normalizeTimelineAccompanimentStartBar(leftSegment.startBar) -
                normalizeTimelineAccompanimentStartBar(rightSegment.startBar);
        })
        .forEach(function (segment) {
            segment.startBar = Math.max(
                nextAvailableBar,
                normalizeTimelineAccompanimentStartBar(segment.startBar)
            );
            segment.barCount = normalizeTimelineAccompanimentBarCount(segment.barCount);
            nextAvailableBar = segment.startBar + segment.barCount;
        });
}

function addTimelineAccompanimentSegment(payload, targetInstrument, requestedStartBar) {
    const pattern = getTimelineAccompanimentPatternFromPayload(payload, targetInstrument);
    if (!pattern) {
        return;
    }

    const laneSegments = timelineState.accompanimentSegments.filter(function (segment) {
        return segment.targetInstrument === targetInstrument;
    });
    const startBar = requestedStartBar === undefined || requestedStartBar === null
        ? laneSegments.reduce(function (nextBar, segment) {
            return Math.max(
                nextBar,
                normalizeTimelineAccompanimentStartBar(segment.startBar) +
                    normalizeTimelineAccompanimentBarCount(segment.barCount)
            );
        }, 1)
        : normalizeTimelineAccompanimentStartBar(requestedStartBar);

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    timelineState.accompanimentSegments.push(serializeTimelineAccompanimentSegment({
        id: nextTimelineAccompanimentSegmentId(),
        patternId: pattern.id,
        patternSourceKey: pattern.sourceKey,
        targetInstrument: targetInstrument,
        startBar: startBar,
        barCount: Math.max(1, Array.isArray(pattern.bars) ? pattern.bars.length : 1)
    }));
    resolveTimelineAccompanimentLaneCollisions(targetInstrument);
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function updateTimelineAccompanimentSegment(segment, changes) {
    if (!segment) {
        return;
    }
    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    if (Object.prototype.hasOwnProperty.call(changes, 'startBar')) {
        segment.startBar = normalizeTimelineAccompanimentStartBar(changes.startBar);
    }
    if (Object.prototype.hasOwnProperty.call(changes, 'barCount')) {
        segment.barCount = normalizeTimelineAccompanimentBarCount(changes.barCount);
    }
    resolveTimelineAccompanimentLaneCollisions(segment.targetInstrument);
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function removeTimelineAccompanimentSegment(segmentId) {
    const segmentIndex = timelineState.accompanimentSegments.findIndex(function (segment) {
        return segment.id === segmentId;
    });
    if (segmentIndex === -1) {
        return;
    }
    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    timelineState.accompanimentSegments.splice(segmentIndex, 1);
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function bindTimelineAccompanimentResizeHandle(handleEl, segment, segmentEl, countInputEl) {
    handleEl.addEventListener('pointerdown', function (event) {
        if (event.button !== 0 && event.pointerType !== 'touch') {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        const startClientX = event.clientX;
        const startBarCount = normalizeTimelineAccompanimentBarCount(segment.barCount);
        const trackEditorEl = segmentEl.closest('.timeline-track-editor');
        const barWidth = trackEditorEl
            ? Math.max(1, parseFloat(
                window.getComputedStyle(trackEditorEl)
                    .getPropertyValue('--timeline-track-bar-width')
            ) || 108)
            : 24;
        let previewBarCount = startBarCount;
        handleEl.setPointerCapture(event.pointerId);

        function updatePreview(pointerEvent) {
            const deltaBars = Math.round((pointerEvent.clientX - startClientX) / barWidth);
            previewBarCount = normalizeTimelineAccompanimentBarCount(startBarCount + deltaBars);
            countInputEl.value = String(previewBarCount);
            segmentEl.style.setProperty('--timeline-accompaniment-segment-bars', String(previewBarCount));
            segmentEl.style.setProperty('--timeline-track-bar-count', String(previewBarCount));
        }

        function finishResize(pointerEvent) {
            updatePreview(pointerEvent);
            handleEl.removeEventListener('pointermove', updatePreview);
            handleEl.removeEventListener('pointerup', finishResize);
            handleEl.removeEventListener('pointercancel', cancelResize);
            updateTimelineAccompanimentSegment(segment, { barCount: previewBarCount });
        }

        function cancelResize() {
            handleEl.removeEventListener('pointermove', updatePreview);
            handleEl.removeEventListener('pointerup', finishResize);
            handleEl.removeEventListener('pointercancel', cancelResize);
            countInputEl.value = String(startBarCount);
            segmentEl.style.setProperty('--timeline-accompaniment-segment-bars', String(startBarCount));
            segmentEl.style.setProperty('--timeline-track-bar-count', String(startBarCount));
        }

        handleEl.addEventListener('pointermove', updatePreview);
        handleEl.addEventListener('pointerup', finishResize);
        handleEl.addEventListener('pointercancel', cancelResize);
    });
}

function createTimelineAccompanimentSegmentElement(segment, patternDisplayInfo) {
    const pattern = findPatternById(segment.patternId);
    if (!pattern) {
        return null;
    }

    const segmentEl = document.createElement('div');
    segmentEl.className = 'timeline-accompaniment-segment';
    segmentEl.draggable = true;
    segmentEl.addEventListener('dragstart', function (event) {
        if (event.target && event.target.closest('input, select, button')) {
            event.preventDefault();
            return;
        }
        const payload = {
            type: 'timeline-accompaniment-segment',
            segmentId: segment.id
        };
        setTimelineActiveDragPayload(payload);
        event.dataTransfer.setData('text/plain', JSON.stringify(payload));
        setTimelineCompactDragImage(event, getTimelinePatternLabel(pattern));
    });
    segmentEl.addEventListener('dragend', function () {
        setTimelineDragDropTargetsVisible(false);
    });
    segmentEl.style.setProperty(
        '--timeline-accompaniment-segment-bars',
        String(normalizeTimelineAccompanimentBarCount(segment.barCount))
    );

    const titleEl = document.createElement('strong');
    titleEl.textContent = getTimelineFullDisplayLabel(pattern, patternDisplayInfo);

    const controlsEl = document.createElement('div');
    controlsEl.className = 'timeline-accompaniment-segment-controls';

    const startLabelEl = document.createElement('label');
    startLabelEl.appendChild(document.createTextNode(timelineText('arrangement.accompanimentStartBar')));
    const startInputEl = document.createElement('input');
    startInputEl.type = 'number';
    startInputEl.min = '1';
    startInputEl.max = '999';
    startInputEl.step = '1';
    startInputEl.value = String(normalizeTimelineAccompanimentStartBar(segment.startBar));
    startInputEl.addEventListener('change', function () {
        updateTimelineAccompanimentSegment(segment, { startBar: startInputEl.value });
    });
    startLabelEl.appendChild(startInputEl);

    const countLabelEl = document.createElement('label');
    countLabelEl.appendChild(document.createTextNode(timelineText('arrangement.accompanimentBarCount')));
    const countInputEl = document.createElement('input');
    countInputEl.type = 'number';
    countInputEl.min = '1';
    countInputEl.max = '999';
    countInputEl.step = '1';
    countInputEl.value = String(normalizeTimelineAccompanimentBarCount(segment.barCount));
    countInputEl.addEventListener('change', function () {
        updateTimelineAccompanimentSegment(segment, { barCount: countInputEl.value });
    });
    countLabelEl.appendChild(countInputEl);

    const removeButtonEl = document.createElement('button');
    removeButtonEl.type = 'button';
    removeButtonEl.className = 'timeline-accompaniment-segment-remove';
    removeButtonEl.textContent = 'x';
    removeButtonEl.setAttribute('aria-label', timelineText('arrangement.removeAccompanimentSegment'));
    removeButtonEl.addEventListener('click', function () {
        removeTimelineAccompanimentSegment(segment.id);
    });

    const resizeHandleEl = document.createElement('button');
    resizeHandleEl.type = 'button';
    resizeHandleEl.className = 'timeline-accompaniment-resize-handle';
    resizeHandleEl.setAttribute('aria-label', timelineText('arrangement.resizeAccompanimentSegment'));
    resizeHandleEl.title = timelineText('arrangement.resizeAccompanimentSegment');
    bindTimelineAccompanimentResizeHandle(resizeHandleEl, segment, segmentEl, countInputEl);

    controlsEl.append(startLabelEl, countLabelEl, removeButtonEl);
    segmentEl.append(titleEl, controlsEl, resizeHandleEl);
    return segmentEl;
}

function renderTimelineAccompanimentLanes(sequenceEl, patternDisplayInfo) {
    const lanesEl = document.createElement('section');
    lanesEl.className = 'timeline-accompaniment-lanes';

    const headingEl = document.createElement('h4');
    headingEl.textContent = timelineText('arrangement.accompanimentLanes');
    const noteEl = document.createElement('p');
    noteEl.textContent = timelineText('arrangement.accompanimentLanesNote');
    lanesEl.append(headingEl, noteEl);

    timelineBassTargets.forEach(function (targetInstrument) {
        const laneEl = document.createElement('div');
        laneEl.className = 'timeline-accompaniment-lane-row';
        const laneLabelEl = document.createElement('strong');
        laneLabelEl.className = 'timeline-accompaniment-lane-label';
        laneLabelEl.textContent = getTimelineInstrumentLabel(targetInstrument);

        const trackEl = document.createElement('div');
        trackEl.className = 'timeline-accompaniment-lane-track';
        const laneSegments = timelineState.accompanimentSegments
            .filter(function (segment) {
                return segment.targetInstrument === targetInstrument;
            })
            .sort(function (leftSegment, rightSegment) {
                return leftSegment.startBar - rightSegment.startBar;
            });
        let nextRenderedBar = 1;
        laneSegments.forEach(function (segment) {
            const gapBars = Math.max(0, normalizeTimelineAccompanimentStartBar(segment.startBar) - nextRenderedBar);
            if (gapBars > 0) {
                const gapEl = document.createElement('span');
                gapEl.className = 'timeline-accompaniment-gap';
                gapEl.style.setProperty('--timeline-accompaniment-gap-bars', String(gapBars));
                gapEl.title = timelineText('arrangement.accompanimentGap', { count: gapBars });
                trackEl.appendChild(gapEl);
            }
            const segmentEl = createTimelineAccompanimentSegmentElement(segment, patternDisplayInfo);
            if (segmentEl) {
                trackEl.appendChild(segmentEl);
            }
            nextRenderedBar = normalizeTimelineAccompanimentStartBar(segment.startBar) +
                normalizeTimelineAccompanimentBarCount(segment.barCount);
        });

        const dropEl = document.createElement('div');
        dropEl.className = 'timeline-accompaniment-lane-drop';
        dropEl.textContent = timelineText('arrangement.drop.accompanimentLane');
        dropEl.addEventListener('dragover', function (event) {
            const payload = timelineActiveDragPayload ||
                getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
            if (!getTimelineAccompanimentPatternFromPayload(payload, targetInstrument)) {
                event.dataTransfer.dropEffect = 'none';
                dropEl.classList.remove('is-drop-target');
                return;
            }
            event.preventDefault();
            event.dataTransfer.dropEffect = 'copy';
            dropEl.classList.add('is-drop-target');
        });
        dropEl.addEventListener('dragleave', function () {
            dropEl.classList.remove('is-drop-target');
        });
        dropEl.addEventListener('drop', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const payload = timelineActiveDragPayload ||
                getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
            setTimelineDragDropTargetsVisible(false);
            addTimelineAccompanimentSegment(payload, targetInstrument);
        });

        trackEl.appendChild(dropEl);
        laneEl.append(laneLabelEl, trackEl);
        lanesEl.appendChild(laneEl);
    });

    sequenceEl.appendChild(lanesEl);
}

function renderLegacyTimelineSequence() {
    const sequenceEl = document.getElementById('timelineSequence');
    const patternDisplayInfo = buildPatternDisplayLabelMap(timelineState.sourcePatterns);
    const entryGroups = buildTimelineDisplayGroups(timelineState.entries, timelineState.sourcePatterns);
    const visualRows = buildTimelineVisualRows(entryGroups, timelineState.sourcePatterns);
    const continuationInfo = buildTimelineContinuationBlocks(visualRows, timelineState.sourcePatterns);
    sequenceEl.innerHTML = '';

    if (visualRows.length === 0) {
        sequenceEl.appendChild(createTimelineDropzone(0));
        renderTimelineAccompanimentLanes(sequenceEl, patternDisplayInfo);
        return;
    }

    const gridEl = document.createElement('div');
    gridEl.className = 'timeline-continuation-grid';
    const hasContinuationBlocks = continuationInfo.blocks.length > 0;
    if (!hasContinuationBlocks) {
        gridEl.classList.add('is-plain');
    } else {
        gridEl.style.gridTemplateColumns =
            'repeat(' + continuationInfo.blocks.length + ', minmax(150px, 180px)) minmax(0, 1fr)';
    }

    continuationInfo.blocks.forEach(function (block) {
        const laneEl = document.createElement('div');
        laneEl.className = 'timeline-continuation-lane';
        laneEl.style.gridColumn = String(block.laneIndex + 1);
        laneEl.style.gridRow = String(block.startRowIndex + 1) + ' / span ' + String(block.span);

        const titleEl = document.createElement('strong');
        titleEl.textContent = getTimelineFullDisplayLabel(block.pattern, patternDisplayInfo);
        const targetEl = document.createElement('small');
        targetEl.textContent = block.targets.map(getTimelineInstrumentLabel).join(', ');
        const statusEl = document.createElement('small');
        statusEl.textContent = timelineText('arrangement.continues');
        const actionWrap = document.createElement('div');
        actionWrap.className = 'timeline-entry-actions';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = timelineText('common.remove');
        removeButton.addEventListener('click', function () {
            if (typeof recordArrangementHistorySnapshot === 'function') {
                recordArrangementHistorySnapshot();
            }
            timelineState.entries.splice(block.group.startIndex, block.group.count);
            updateTimelineMetadataNode();
            renderTimelinePanel();
        });
        actionWrap.appendChild(removeButton);

        laneEl.appendChild(titleEl);
        laneEl.appendChild(targetEl);
        laneEl.appendChild(statusEl);
        laneEl.appendChild(actionWrap);
        gridEl.appendChild(laneEl);
    });

    const renderedRows = visualRows.filter(function (rowGroups) {
        return getTimelineVisibleRowGroups(rowGroups, continuationInfo).length > 0;
    });
    const renderedRowTempoInfos = new Map();
    let inheritedRowTempo = normalizeTimelineTempo(timelineState.tempo);
    renderedRows.forEach(function (rowGroups) {
        const rowTempoInfo = getTimelineRowTempoInfo(rowGroups, inheritedRowTempo);
        renderedRowTempoInfos.set(rowGroups, rowTempoInfo);
        if (!rowTempoInfo.mixed && rowTempoInfo.tempo !== null && rowTempoInfo.tempo !== undefined) {
            inheritedRowTempo = normalizeTimelineTempo(rowTempoInfo.tempo);
        }
    });

    visualRows.forEach(function (rowGroups, rowIndex) {
        if (!rowGroups || rowGroups.length === 0) {
            return;
        }
        const visibleGroups = getTimelineVisibleRowGroups(rowGroups, continuationInfo);
        if (visibleGroups.length === 0) {
            return;
        }
        const renderedRowIndex = renderedRows.indexOf(rowGroups);

        const rowCellEl = document.createElement('div');
        rowCellEl.className = 'timeline-row-cell';
        rowCellEl.style.gridColumn = hasContinuationBlocks ? String(continuationInfo.blocks.length + 1) : '1';
        rowCellEl.style.gridRow = String(rowIndex + 1);
        rowCellEl.appendChild(createTimelineDropzone(rowGroups[0].startIndex));

        const rowEl = document.createElement('div');
        rowEl.className = 'timeline-sequence-row';
        bindParallelDropTarget(rowEl, rowGroups);
        rowEl.classList.add('is-compact-section');
        if (visibleGroups.length > 1 || String(rowGroups[0].parallelGroupId || '') !== '') {
            rowEl.classList.add('is-parallel-section');
        }
        rowEl.appendChild(createTimelineSectionHeader(
            rowGroups,
            renderedRowIndex,
            renderedRows,
            renderedRowTempoInfos.get(rowGroups)
        ));

        visibleGroups.forEach(function (group) {
            const entryChip = createTimelineEntryChip(group, rowGroups, patternDisplayInfo);
            if (entryChip) {
                rowEl.appendChild(entryChip);
            }
        });

        rowEl.appendChild(createTimelineParallelDropzone(rowGroups));
        rowCellEl.appendChild(rowEl);
        const overlayGridEl = createTimelineOverlayGrid(rowGroups, patternDisplayInfo);
        if (overlayGridEl) {
            rowCellEl.appendChild(overlayGridEl);
        }
        gridEl.appendChild(rowCellEl);
    });

    sequenceEl.appendChild(gridEl);
    sequenceEl.appendChild(createTimelineDropzone(timelineState.entries.length));
    renderTimelineAccompanimentLanes(sequenceEl, patternDisplayInfo);
}

function buildTimelinePatternRepeatRanges(pattern) {
    const bars = pattern && Array.isArray(pattern.bars) ? pattern.bars : [];
    if (bars.length === 0) {
        return [];
    }

    const repeatBoundaries = new Array(bars.length + 1).fill(null).map(function (_, boundaryIndex) {
        return {
            index: boundaryIndex,
            startMarkers: [],
            endMarkers: []
        };
    });

    bars.forEach(function (bar, barIndex) {
        const repeatInfo = bar && bar.repeat ? bar.repeat : {};
        getTimelineRepeatMarkerList(repeatInfo.start).forEach(function (marker) {
            repeatBoundaries[barIndex].startMarkers.push({
                boundaryIndex: barIndex,
                count: marker
            });
        });
        getTimelineRepeatMarkerList(repeatInfo.end).forEach(function (marker) {
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

    const seenRanges = {};
    return repeatRanges.filter(function (repeatRange) {
        const startBar = Number(repeatRange.startBar);
        const endBar = Number(repeatRange.endBar);
        const count = repeatRange.count === 'loop' ? 'loop' : Number(repeatRange.count);
        if (!Number.isFinite(startBar) || !Number.isFinite(endBar) || startBar < 1 || endBar < startBar) {
            return false;
        }
        if (count !== 'loop' && (!Number.isFinite(count) || count < 1)) {
            return false;
        }
        const rangeKey = startBar + ':' + endBar + ':' + count;
        if (seenRanges[rangeKey]) {
            return false;
        }
        seenRanges[rangeKey] = true;
        repeatRange.startBar = startBar;
        repeatRange.endBar = endBar;
        repeatRange.count = count;
        return true;
    }).sort(function (rangeA, rangeB) {
        if (rangeA.startBar !== rangeB.startBar) {
            return rangeA.startBar - rangeB.startBar;
        }
        return rangeB.endBar - rangeA.endBar;
    });
}

function getTimelineExpandedPatternBars(pattern) {
    const bars = pattern && Array.isArray(pattern.bars) ? pattern.bars : [];
    if (bars.length === 0) {
        return [];
    }
    const repeatRanges = buildTimelinePatternRepeatRanges(pattern);
    return repeatRanges.length > 0
        ? expandTimelineBarsWithRepeats(bars, repeatRanges, 1, bars.length)
        : bars;
}

function getTimelinePatternBarCountWithOverlapHandoff(pattern, bars, barCount, shouldHandOffOverlap) {
    const normalizedBarCount = Math.max(1, Math.min(
        Array.isArray(bars) ? bars.length : 0,
        Math.round(Number(barCount) || 1)
    ));
    if (!shouldHandOffOverlap || !pattern || pattern.labelType === 'Begleitung' || normalizedBarCount <= 1) {
        return normalizedBarCount;
    }

    const finalCountedBar = bars[normalizedBarCount - 1];
    const handsFinalBarToNextPattern = Array.isArray(finalCountedBar && finalCountedBar.controls) &&
        finalCountedBar.controls.some(function (control) {
            return control && control.type === 'overlap';
        });
    return handsFinalBarToNextPattern ? normalizedBarCount - 1 : normalizedBarCount;
}

function getTimelinePatternBarCountAtFinalOut(pattern, shouldHandOffOverlap) {
    const bars = getTimelineExpandedPatternBars(pattern);
    const fullBarCount = Math.max(1, bars.length);
    if (!pattern || pattern.labelType === 'Begleitung') {
        return fullBarCount;
    }

    let outBarIndex = -1;
    bars.forEach(function (bar, barIndex) {
        if (Array.isArray(bar && bar.controls) && bar.controls.some(function (control) {
            return control && control.type === 'out';
        })) {
            outBarIndex = barIndex;
        }
    });
    const barCountAtFinalOut = outBarIndex === -1 ? fullBarCount : outBarIndex + 1;
    return getTimelinePatternBarCountWithOverlapHandoff(
        pattern,
        bars,
        barCountAtFinalOut,
        shouldHandOffOverlap !== false
    );
}

function getTimelineGroupBarCount(group, shouldHandOffFinalOverlap) {
    const entries = group && Array.isArray(group.entries) ? group.entries : [];
    return entries.reduce(function (barCount, entry, entryIndex) {
        const pattern = findPatternById(entry && entry.patternId);
        const bars = getTimelineExpandedPatternBars(pattern);
        const fullBarCount = Math.max(1, bars.length);
        const isLastEntry = entryIndex === entries.length - 1;
        const entryBarCount = isLastEntry
            ? getTimelinePatternBarCountAtFinalOut(pattern, shouldHandOffFinalOverlap !== false)
            : getTimelinePatternBarCountWithOverlapHandoff(pattern, bars, fullBarCount, true);
        return barCount + entryBarCount;
    }, 0);
}

function getTimelineRowBarCount(rowGroups, shouldHandOffFinalOverlap) {
    const baseGroups = (Array.isArray(rowGroups) ? rowGroups : []).filter(function (group) {
        return group && !isTimelineOverlayGroup(group);
    });
    return Math.max.apply(null, baseGroups.map(function (group) {
        return getTimelineGroupBarCount(group, shouldHandOffFinalOverlap);
    }).concat(1));
}

function getTimelineRowGapBeforeBars(rowGroups) {
    const baseGroups = (Array.isArray(rowGroups) ? rowGroups : []).filter(function (group) {
        return group && !isTimelineOverlayGroup(group);
    });
    return Math.max.apply(null, baseGroups.map(function (group) {
        return normalizeTimelineGapBeforeBars(
            group.gapBeforeBars !== undefined
                ? group.gapBeforeBars
                : group.entries && group.entries[0] && group.entries[0].gapBeforeBars
        );
    }).concat(0));
}

function setTimelineRowGapBeforeBars(rowGroups, nextGapBeforeBars) {
    const baseGroups = (Array.isArray(rowGroups) ? rowGroups : []).filter(function (group) {
        return group && !isTimelineOverlayGroup(group);
    });
    const normalizedGap = normalizeTimelineGapBeforeBars(nextGapBeforeBars);
    if (baseGroups.length === 0 || getTimelineRowGapBeforeBars(baseGroups) === normalizedGap) {
        return;
    }

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    baseGroups.forEach(function (group) {
        (Array.isArray(group.entries) ? group.entries : []).forEach(function (entry) {
            entry.gapBeforeBars = 0;
        });
        if (group.entries && group.entries[0]) {
            group.entries[0].gapBeforeBars = normalizedGap;
        }
    });
    updateTimelineMetadataNode();
    renderTimelinePanel();
}

function buildTimelineHorizontalLayout(visualRows) {
    const rows = [];
    let nextStartBar = 1;
    const timelineRows = Array.isArray(visualRows) ? visualRows : [];

    timelineRows.forEach(function (rowGroups, rowIndex) {
        const gapBeforeBars = getTimelineRowGapBeforeBars(rowGroups);
        const shouldHandOffFinalOverlap = rowIndex < timelineRows.length - 1;
        const barCount = getTimelineRowBarCount(rowGroups, shouldHandOffFinalOverlap);
        nextStartBar += gapBeforeBars;
        rows.push({
            rowIndex: rowIndex,
            rowGroups: rowGroups,
            startBar: nextStartBar,
            gapBeforeBars: gapBeforeBars,
            barCount: barCount,
            shouldHandOffFinalOverlap: shouldHandOffFinalOverlap,
            endBar: nextStartBar + barCount
        });
        nextStartBar += barCount;
    });

    const continuationInfo = buildTimelineContinuationBlocks(visualRows, timelineState.sourcePatterns);
    const continuationByGroupKey = {};
    continuationInfo.blocks.forEach(function (block) {
        continuationByGroupKey[getTimelineGroupKey(block.group)] = block;
    });

    const clips = [];
    rows.forEach(function (rowLayout) {
        const rowRepeatInfo = getTimelineRowRepeatInfo(rowLayout.rowGroups);
        const repeatCount = Math.max(1, normalizeTimelineGroupRepeatCount(rowRepeatInfo.repeatCount || 1));
        const repeatBarCount = Math.max(1, Math.round(rowLayout.barCount / repeatCount));

        rowLayout.rowGroups.forEach(function (group) {
            const pattern = findPatternById(group && group.patternId);
            if (!pattern) {
                return;
            }

            const continuationBlock = continuationByGroupKey[getTimelineGroupKey(group)];
            let startBar = rowLayout.startBar;
            let barCount = Math.max(1, getTimelineGroupBarCount(
                group,
                rowLayout.shouldHandOffFinalOverlap
            ));

            if (continuationBlock) {
                const startRow = rows[continuationBlock.startRowIndex] || rowLayout;
                const endRow = rows[Math.min(rows.length - 1, continuationBlock.startRowIndex + continuationBlock.span - 1)] || rowLayout;
                startBar = startRow.startBar;
                barCount = Math.max(1, endRow.endBar - startRow.startBar);
            } else if (isTimelineOverlayGroup(group)) {
                const overlayEntry = group.entries && group.entries[0];
                const overlayRepeatIndex = Math.max(0, Math.round(Number(overlayEntry && overlayEntry.overlayRepeatIndex) || 0));
                startBar = rowLayout.startBar + (overlayRepeatIndex * repeatBarCount);
            }

            const targets = getTimelineGroupTargets(group);
            targets.forEach(function (targetInstrument) {
                if (timelineTrackTargets.indexOf(targetInstrument) === -1) {
                    return;
                }
                clips.push({
                    kind: 'entry',
                    targetInstrument: targetInstrument,
                    startBar: startBar,
                    barCount: barCount,
                    group: group,
                    rowGroups: rowLayout.rowGroups,
                    rowIndex: rowLayout.rowIndex,
                    pattern: pattern,
                    shouldHandOffFinalOverlap: rowLayout.shouldHandOffFinalOverlap,
                    isOverlay: isTimelineOverlayGroup(group)
                });
            });
        });
    });

    timelineState.accompanimentSegments.forEach(function (segment) {
        const pattern = findPatternById(segment.patternId);
        if (!pattern || timelineTrackTargets.indexOf(segment.targetInstrument) === -1) {
            return;
        }
        clips.push({
            kind: 'segment',
            targetInstrument: segment.targetInstrument,
            startBar: normalizeTimelineAccompanimentStartBar(segment.startBar),
            barCount: normalizeTimelineAccompanimentBarCount(segment.barCount),
            segment: segment,
            pattern: pattern,
            isOverlay: false
        });
    });

    const contentTotalBars = Math.max.apply(null, clips.map(function (clip) {
        return clip.startBar + clip.barCount - 1;
    }).concat(rows.length ? nextStartBar - 1 : 0, 0));
    const naturalTotalBars = Math.max(contentTotalBars, 8);
    const minimumBarCount = normalizeTimelineMinimumBarCount(timelineState.minimumBarCount);
    const totalBars = Math.max(
        naturalTotalBars,
        minimumBarCount
    );
    const playbackTotalBars = Math.max(contentTotalBars, minimumBarCount);
    const usedTargets = timelineTrackTargets.filter(function (targetInstrument) {
        return clips.some(function (clip) {
            return clip.targetInstrument === targetInstrument;
        });
    });

    return {
        rows: rows,
        clips: clips,
        naturalTotalBars: naturalTotalBars,
        playbackTotalBars: playbackTotalBars,
        totalBars: totalBars,
        usedTargets: usedTargets.length > 0 ? usedTargets : timelineTrackTargets.slice(0, 1)
    };
}

function addTimelineBar(layout) {
    const currentTotalBars = Math.max(1, Number(layout && layout.totalBars) || 1);
    if (currentTotalBars >= 999) {
        return;
    }
    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    timelineState.minimumBarCount = currentTotalBars + 1;
    updateTimelineMetadataNode();
    renderTimelinePanel();
    window.requestAnimationFrame(function () {
        const scrollEl = document.querySelector('#timelineSequence .timeline-track-scroll');
        if (scrollEl) {
            scrollEl.scrollLeft = Math.max(0, scrollEl.scrollWidth - scrollEl.clientWidth);
        }
    });
}

function assignTimelineTrackClipLayers(clips) {
    const layerEndBars = [];
    return (Array.isArray(clips) ? clips.slice() : [])
        .sort(function (leftClip, rightClip) {
            return leftClip.startBar - rightClip.startBar || rightClip.barCount - leftClip.barCount;
        })
        .map(function (clip) {
            let layerIndex = layerEndBars.findIndex(function (endBar) {
                return endBar <= clip.startBar;
            });
            if (layerIndex === -1) {
                layerIndex = layerEndBars.length;
            }
            layerEndBars[layerIndex] = clip.startBar + clip.barCount;
            clip.layerIndex = layerIndex;
            return clip;
        });
}

function appendTimelineTrackRepeatControl(entryCard, group) {
    if (!entryCard || !group || isTimelineOverlayGroup(group)) {
        return null;
    }
    const chipHeadEl = entryCard.querySelector('.timeline-chip-head');
    const removeButtonEl = entryCard.querySelector('.timeline-chip-remove');
    if (!chipHeadEl) {
        return null;
    }

    const repeatInfo = getTimelineGroupRepeatInfo(group);
    const repeatLabelEl = document.createElement('label');
    repeatLabelEl.className = 'timeline-track-repeat-control';
    repeatLabelEl.appendChild(document.createTextNode(timelineText('arrangement.repeatShort')));
    const repeatInputEl = document.createElement('input');
    repeatInputEl.type = 'number';
    repeatInputEl.min = '1';
    repeatInputEl.max = '100';
    repeatInputEl.step = '1';
    repeatInputEl.value = String(normalizeTimelineGroupRepeatCount(repeatInfo.repeatCount || 1));
    repeatInputEl.addEventListener('click', function (event) {
        event.stopPropagation();
    });
    repeatInputEl.addEventListener('change', function () {
        const nextRepeatCount = normalizeTimelineGroupRepeatCount(repeatInputEl.value);
        repeatInputEl.value = String(nextRepeatCount);
        setTimelineGroupRepeatCount(group, repeatInfo, nextRepeatCount);
    });
    repeatLabelEl.appendChild(repeatInputEl);
    chipHeadEl.insertBefore(repeatLabelEl, removeButtonEl || null);
    return {
        repeatInfo: repeatInfo,
        inputEl: repeatInputEl
    };
}

function bindTimelineEntryResizeHandle(handleEl, entryCard, group, repeatControl, shouldHandOffFinalOverlap) {
    if (!handleEl || !entryCard || !group || !repeatControl) {
        return;
    }
    handleEl.addEventListener('pointerdown', function (event) {
        if (event.button !== 0 && event.pointerType !== 'touch') {
            return;
        }
        event.preventDefault();
        event.stopPropagation();

        const editorEl = entryCard.closest('.timeline-track-editor');
        const barWidth = Math.max(1, parseFloat(
            window.getComputedStyle(editorEl || document.documentElement)
                .getPropertyValue('--timeline-track-bar-width')
        ) || 108);
        const startClientX = event.clientX;
        const startRepeatCount = normalizeTimelineGroupRepeatCount(
            repeatControl.repeatInfo.repeatCount || 1
        );
        const totalBarCount = Math.max(1, getTimelineGroupBarCount(group, shouldHandOffFinalOverlap));
        const unitBarCount = Math.max(1, totalBarCount / startRepeatCount);
        let previewRepeatCount = startRepeatCount;
        entryCard.draggable = false;
        handleEl.setPointerCapture(event.pointerId);

        function updatePreview(pointerEvent) {
            const deltaBars = Math.round((pointerEvent.clientX - startClientX) / barWidth);
            const deltaRepeats = Math.round(deltaBars / unitBarCount);
            previewRepeatCount = normalizeTimelineGroupRepeatCount(startRepeatCount + deltaRepeats);
            repeatControl.inputEl.value = String(previewRepeatCount);
            entryCard.style.setProperty(
                '--timeline-track-bar-count',
                String(unitBarCount * previewRepeatCount)
            );
        }

        function removePointerListeners() {
            handleEl.removeEventListener('pointermove', updatePreview);
            handleEl.removeEventListener('pointerup', finishResize);
            handleEl.removeEventListener('pointercancel', cancelResize);
            entryCard.draggable = true;
        }

        function finishResize(pointerEvent) {
            updatePreview(pointerEvent);
            removePointerListeners();
            setTimelineGroupRepeatCount(group, repeatControl.repeatInfo, previewRepeatCount);
        }

        function cancelResize() {
            removePointerListeners();
            repeatControl.inputEl.value = String(startRepeatCount);
            entryCard.style.setProperty('--timeline-track-bar-count', String(totalBarCount));
        }

        handleEl.addEventListener('pointermove', updatePreview);
        handleEl.addEventListener('pointerup', finishResize);
        handleEl.addEventListener('pointercancel', cancelResize);
    });
}

function appendTimelineTrackTempoControl(entryCard, rowGroups) {
    const detailBodyEl = entryCard && entryCard.querySelector('.timeline-entry-detail-body');
    if (!detailBodyEl || !Array.isArray(rowGroups)) {
        return;
    }
    const tempoInfo = getTimelineRowTempoInfo(rowGroups, timelineState.tempo);
    const tempoLabelEl = document.createElement('label');
    tempoLabelEl.className = 'timeline-track-tempo-control';
    tempoLabelEl.appendChild(document.createTextNode(timelineText('arrangement.tempo')));
    const tempoInputEl = document.createElement('input');
    tempoInputEl.type = 'number';
    tempoInputEl.min = '30';
    tempoInputEl.max = '180';
    tempoInputEl.step = '1';
    tempoInputEl.value = tempoInfo.mixed ? '' : String(normalizeTimelineTempo(tempoInfo.effectiveTempo));
    tempoInputEl.placeholder = tempoInfo.mixed ? timelineText('arrangement.mixed') : '';
    tempoInputEl.addEventListener('change', function () {
        setTimelineRowSectionTempo(rowGroups, tempoInputEl.value);
    });
    tempoLabelEl.appendChild(tempoInputEl);
    detailBodyEl.appendChild(tempoLabelEl);
}

function getTimelineTrackPointerBar(clipEl, layout, clientX, clientY) {
    const scrollEl = clipEl && clipEl.closest('.timeline-track-scroll');
    const rulerCanvasEl = scrollEl && scrollEl.querySelector('.timeline-track-ruler-canvas');
    if (!scrollEl || !rulerCanvasEl || !layout) {
        return null;
    }
    const scrollBounds = scrollEl.getBoundingClientRect();
    const rulerBounds = rulerCanvasEl.getBoundingClientRect();
    if (clientY < scrollBounds.top || clientY > scrollBounds.bottom ||
        clientX < rulerBounds.left || clientX > rulerBounds.right) {
        return null;
    }
    const barWidth = Math.max(1, parseFloat(
        window.getComputedStyle(scrollEl)
            .getPropertyValue('--timeline-track-bar-width')
    ) || 108);
    return Math.max(1, Math.min(
        Math.max(1, Number(layout.totalBars) || 1),
        Math.floor((clientX - rulerBounds.left) / barWidth) + 1
    ));
}

function bindTimelineTrackPointerMove(surfaceEl, clipEl, payload, layout) {
    if (!surfaceEl || !clipEl || !payload || !layout) {
        return;
    }
    surfaceEl.addEventListener('pointerdown', function (event) {
        if (event.button !== 0 && event.pointerType !== 'touch') {
            return;
        }
        event.stopPropagation();
        const startClientX = event.clientX;
        const startClientY = event.clientY;
        let pointerMoveStarted = false;
        let pendingAction = null;
        try {
            surfaceEl.setPointerCapture(event.pointerId);
        } catch (error) {
            // Pointer capture is optional; window listeners below remain the fallback.
        }

        function clearRulerTarget() {
            document.querySelectorAll('.timeline-track-ruler-bar.is-drop-target').forEach(function (barEl) {
                barEl.classList.remove('is-drop-target');
            });
        }

        function updatePointerMove(pointerEvent) {
            const deltaX = pointerEvent.clientX - startClientX;
            const deltaY = pointerEvent.clientY - startClientY;
            if (!pointerMoveStarted && Math.hypot(deltaX, deltaY) < 4) {
                return;
            }
            if (!pointerMoveStarted) {
                pointerMoveStarted = true;
                setTimelineActiveDragPayload(payload);
                clipEl.classList.add('is-pointer-moving');
            }
            pointerEvent.preventDefault();
            clipEl.style.transform = 'translateX(' + deltaX + 'px)';
            const targetBar = getTimelineTrackPointerBar(
                clipEl,
                layout,
                pointerEvent.clientX,
                pointerEvent.clientY
            );
            pendingAction = targetBar === null
                ? null
                : getTimelineRulerBarDropAction(payload, layout, targetBar);
            clearRulerTarget();
            if (pendingAction && targetBar !== null) {
                const targetEl = clipEl.closest('.timeline-track-scroll')
                    ?.querySelector('.timeline-track-ruler-bar[data-timeline-bar="' + targetBar + '"]');
                if (targetEl) {
                    targetEl.classList.add('is-drop-target');
                }
            }
        }

        function removePointerListeners() {
            window.removeEventListener('pointermove', updatePointerMove);
            window.removeEventListener('pointerup', finishPointerMove);
            window.removeEventListener('pointercancel', cancelPointerMove);
            clearRulerTarget();
            clipEl.classList.remove('is-pointer-moving');
            clipEl.style.removeProperty('transform');
            if (pointerMoveStarted) {
                setTimelineDragDropTargetsVisible(false);
            }
            try {
                if (surfaceEl.hasPointerCapture(event.pointerId)) {
                    surfaceEl.releasePointerCapture(event.pointerId);
                }
            } catch (error) {
                // The pointer can already have been released by the browser.
            }
        }

        function finishPointerMove(pointerEvent) {
            if (pointerMoveStarted) {
                updatePointerMove(pointerEvent);
            }
            const actionToExecute = pendingAction;
            removePointerListeners();
            if (pointerMoveStarted && actionToExecute) {
                executeTimelineRulerBarDrop(payload, actionToExecute);
            }
        }

        function cancelPointerMove() {
            removePointerListeners();
        }

        window.addEventListener('pointermove', updatePointerMove, { passive: false });
        window.addEventListener('pointerup', finishPointerMove);
        window.addEventListener('pointercancel', cancelPointerMove);
    });
}

function appendTimelineTrackDragSurface(clipEl, label, payload, layout) {
    if (!clipEl) {
        return null;
    }
    const dragSurfaceEl = document.createElement('span');
    dragSurfaceEl.className = 'timeline-track-drag-surface';
    dragSurfaceEl.draggable = false;
    dragSurfaceEl.setAttribute('aria-hidden', 'true');
    if (label) {
        dragSurfaceEl.title = label;
    }
    clipEl.appendChild(dragSurfaceEl);
    bindTimelineTrackPointerMove(dragSurfaceEl, clipEl, payload, layout);
    return dragSurfaceEl;
}

function createTimelineTrackEntryClip(clip, patternDisplayInfo, layout) {
    const entryCard = createTimelineEntryChip(clip.group, clip.rowGroups, patternDisplayInfo);
    if (!entryCard) {
        return null;
    }
    entryCard.draggable = false;
    entryCard.classList.add('timeline-track-clip', 'timeline-track-entry-clip');
    if (clip.barCount <= 1) {
        entryCard.classList.add('is-single-bar');
    }
    if (clip.isOverlay) {
        entryCard.classList.add('is-overlay');
    }
    entryCard.style.setProperty('--timeline-track-start-bar', String(clip.startBar));
    entryCard.style.setProperty('--timeline-track-bar-count', String(clip.barCount));
    entryCard.style.setProperty('--timeline-track-layer', String(clip.layerIndex || 0));

    const chipLabelEl = entryCard.querySelector('.timeline-chip-label');
    const patternLabel = getTimelinePatternLabel(clip.pattern);
    if (chipLabelEl) {
        chipLabelEl.textContent = patternLabel;
    }
    if (clip.barCount >= 4) {
        entryCard.classList.add('has-end-label');
        const endLabelEl = document.createElement('span');
        endLabelEl.className = 'timeline-track-clip-label-end';
        endLabelEl.textContent = patternLabel;
        entryCard.appendChild(endLabelEl);
    }
    const repeatControl = appendTimelineTrackRepeatControl(entryCard, clip.group);
    if (repeatControl) {
        const resizeHandleEl = document.createElement('button');
        resizeHandleEl.type = 'button';
        resizeHandleEl.className = 'timeline-track-entry-resize-handle';
        resizeHandleEl.setAttribute('aria-label', timelineText('arrangement.resizePattern'));
        resizeHandleEl.title = timelineText('arrangement.resizePattern');
        resizeHandleEl.draggable = false;
        bindTimelineEntryResizeHandle(
            resizeHandleEl,
            entryCard,
            clip.group,
            repeatControl,
            clip.shouldHandOffFinalOverlap
        );
        entryCard.appendChild(resizeHandleEl);
    }
    appendTimelineTrackDragSurface(entryCard, patternLabel, {
        type: 'timeline-entry-group',
        startIndex: clip.group.startIndex,
        count: clip.group.count
    }, layout);
    appendTimelineTrackTempoControl(entryCard, clip.rowGroups);
    return entryCard;
}

function createTimelineTrackSegmentClip(clip, patternDisplayInfo, layout) {
    const segmentEl = createTimelineAccompanimentSegmentElement(clip.segment, patternDisplayInfo);
    if (!segmentEl) {
        return null;
    }
    segmentEl.draggable = false;
    segmentEl.classList.add('timeline-track-clip', 'timeline-track-segment-clip');
    segmentEl.style.setProperty('--timeline-track-start-bar', String(clip.startBar));
    segmentEl.style.setProperty('--timeline-track-bar-count', String(clip.barCount));
    segmentEl.style.setProperty('--timeline-track-layer', String(clip.layerIndex || 0));
    const titleEl = segmentEl.querySelector('strong');
    if (titleEl) {
        titleEl.textContent = getTimelinePatternLabel(clip.pattern);
    }
    if (clip.barCount >= 4) {
        segmentEl.classList.add('has-end-label');
        const endLabelEl = document.createElement('span');
        endLabelEl.className = 'timeline-track-clip-label-end';
        endLabelEl.textContent = getTimelinePatternLabel(clip.pattern);
        segmentEl.appendChild(endLabelEl);
    }
    appendTimelineTrackDragSurface(segmentEl, getTimelinePatternLabel(clip.pattern), {
        type: 'timeline-accompaniment-segment',
        segmentId: clip.segment.id
    }, layout);
    return segmentEl;
}

function getTimelineRulerBarDropAction(payload, layout, barNumber) {
    if (!payload || !layout) {
        return null;
    }

    const normalizedBarNumber = Math.max(1, Math.round(Number(barNumber) || 1));
    if (payload.type === 'timeline-accompaniment-segment') {
        const segment = timelineState.accompanimentSegments.find(function (candidateSegment) {
            return candidateSegment.id === payload.segmentId;
        });
        if (!segment || normalizeTimelineAccompanimentStartBar(segment.startBar) === normalizedBarNumber) {
            return null;
        }
        return {
            type: 'move-accompaniment-segment',
            segment: segment,
            startBar: normalizedBarNumber
        };
    }

    const candidateEntries = getTimelinePayloadCandidateEntries(payload);
    if (candidateEntries.length === 0) {
        return null;
    }

    if (payload.type === 'timeline-entry-group') {
        const sourceRowIndex = (layout.rows || []).findIndex(function (candidateRow) {
            return candidateRow.rowGroups.some(function (group) {
                return group.startIndex === Number(payload.startIndex) &&
                    group.count === Number(payload.count);
            });
        });
        const sourceRow = sourceRowIndex >= 0 ? layout.rows[sourceRowIndex] : null;
        const previousRowEndBar = sourceRowIndex > 0
            ? layout.rows[sourceRowIndex - 1].endBar
            : 1;
        if (sourceRow && normalizedBarNumber >= previousRowEndBar &&
            normalizedBarNumber !== sourceRow.startBar) {
            return {
                type: 'set-row-gap',
                rowGroups: sourceRow.rowGroups,
                gapBeforeBars: normalizedBarNumber - previousRowEndBar
            };
        }
    }

    if (payload.type === 'pattern' || payload.type === 'pattern-group') {
        const accompanimentCandidates = candidateEntries.map(function (candidateEntry) {
            const pattern = findPatternById(candidateEntry.patternId);
            const targets = Array.isArray(candidateEntry.targetInstruments)
                ? candidateEntry.targetInstruments.filter(function (targetInstrument) {
                    return timelineTrackTargets.indexOf(targetInstrument) !== -1;
                })
                : [];
            return pattern && pattern.labelType === 'Begleitung' && targets.length === 1
                ? { pattern: pattern, targetInstrument: targets[0] }
                : null;
        }).filter(Boolean);
        if (accompanimentCandidates.length === 1 && candidateEntries.length === 1) {
            return {
                type: 'accompaniment',
                targetInstrument: accompanimentCandidates[0].targetInstrument,
                startBar: normalizedBarNumber
            };
        }
    }

    const rowLayout = (layout.rows || []).find(function (candidateRow) {
        return normalizedBarNumber >= candidateRow.startBar &&
            normalizedBarNumber < candidateRow.endBar;
    });
    if (!rowLayout) {
        return { type: 'insert', targetIndex: timelineState.entries.length };
    }

    if (normalizedBarNumber === rowLayout.startBar) {
        if (payload.type === 'timeline-entry-group' && rowLayout.rowGroups.some(function (group) {
            return group.startIndex === Number(payload.startIndex) && group.count === Number(payload.count);
        })) {
            return null;
        }
        if (canInsertTimelinePayloadParallelToRow(payload, rowLayout.rowGroups)) {
            return { type: 'parallel', rowGroups: rowLayout.rowGroups };
        }
        const firstGroup = rowLayout.rowGroups && rowLayout.rowGroups[0];
        return {
            type: 'insert',
            targetIndex: firstGroup ? firstGroup.startIndex : timelineState.entries.length
        };
    }

    const repeatInfo = getTimelineRowRepeatInfo(rowLayout.rowGroups);
    const repeatCount = normalizeTimelineGroupRepeatCount(repeatInfo.repeatCount || 1);
    const repeatBarCount = Math.max(1, Math.round(rowLayout.barCount / repeatCount));
    const barOffset = normalizedBarNumber - rowLayout.startBar;
    const containsOnlyExercisePatterns = candidateEntries.every(function (candidateEntry) {
        const pattern = findPatternById(candidateEntry.patternId);
        return pattern && pattern.labelType !== 'Begleitung';
    });
    if (timelineRowHasAccompaniment(rowLayout.rowGroups) && repeatCount > 1 &&
        containsOnlyExercisePatterns && barOffset % repeatBarCount === 0) {
        return {
            type: 'overlay',
            rowGroups: rowLayout.rowGroups,
            repeatIndex: Math.floor(barOffset / repeatBarCount)
        };
    }

    return null;
}

function executeTimelineRulerBarDrop(payload, action) {
    if (!payload || !action) {
        return;
    }
    if (action.type === 'accompaniment') {
        addTimelineAccompanimentSegment(payload, action.targetInstrument, action.startBar);
        return;
    }
    if (action.type === 'move-accompaniment-segment') {
        updateTimelineAccompanimentSegment(action.segment, { startBar: action.startBar });
        return;
    }
    if (action.type === 'set-row-gap') {
        setTimelineRowGapBeforeBars(action.rowGroups, action.gapBeforeBars);
        return;
    }
    if (action.type === 'parallel') {
        insertTimelineEntryParallelToRow(payload, action.rowGroups);
        return;
    }
    if (action.type === 'overlay') {
        insertTimelineOverlayIntoRepeatSlot(payload, action.rowGroups, action.repeatIndex);
        return;
    }
    insertTimelineEntryAtIndex(payload, action.targetIndex);
}

function bindTimelineRulerBarDropTarget(barEl, layout, barNumber) {
    barEl.addEventListener('dragover', function (event) {
        const payload = timelineActiveDragPayload || getTimelineDragPayload(
            event.dataTransfer.getData('text/plain')
        );
        if (!getTimelineRulerBarDropAction(payload, layout, barNumber)) {
            barEl.classList.remove('is-drop-target');
            return;
        }
        event.preventDefault();
        event.dataTransfer.dropEffect = payload && (
            payload.type === 'timeline-entry-group' ||
            payload.type === 'timeline-accompaniment-segment'
        )
            ? 'move'
            : 'copy';
        document.querySelectorAll('.timeline-track-ruler-bar.is-drop-target').forEach(function (targetEl) {
            if (targetEl !== barEl) {
                targetEl.classList.remove('is-drop-target');
            }
        });
        barEl.classList.add('is-drop-target');
    });
    barEl.addEventListener('dragleave', function () {
        barEl.classList.remove('is-drop-target');
    });
    barEl.addEventListener('drop', function (event) {
        const payload = timelineActiveDragPayload || getTimelineDragPayload(
            event.dataTransfer.getData('text/plain')
        );
        const action = getTimelineRulerBarDropAction(payload, layout, barNumber);
        if (!action) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        setTimelineDragDropTargetsVisible(false);
        executeTimelineRulerBarDrop(payload, action);
    });
}

function setTimelinePlaybackStartBar(barNumber) {
    const normalizedBarNumber = normalizeTimelinePlaybackStartBar(barNumber);
    if (timelineState.playbackStartBar === normalizedBarNumber) {
        return;
    }
    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    timelineState.playbackStartBar = normalizedBarNumber;
    updateTimelineMetadataNode();
    renderTimelineSequence();
}

function bindTimelinePlaybackStartBar(barEl, barNumber) {
    const selectStartBar = function () {
        if (timelineActiveDragPayload || document.body.classList.contains('is-timeline-dragging')) {
            return;
        }
        setTimelinePlaybackStartBar(barNumber);
    };
    barEl.setAttribute('role', 'button');
    barEl.tabIndex = 0;
    barEl.addEventListener('click', selectStartBar);
    barEl.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        event.preventDefault();
        selectStartBar();
    });
}

function bindTimelineTrackLaneDrop(dropEl, targetInstrument, totalBars) {
    dropEl.addEventListener('dragover', function (event) {
        const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        if (!getTimelineAccompanimentPatternFromPayload(payload, targetInstrument)) {
            dropEl.classList.remove('is-drop-target');
            return;
        }
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';
        dropEl.classList.add('is-drop-target');
    });
    dropEl.addEventListener('dragleave', function () {
        dropEl.classList.remove('is-drop-target');
    });
    dropEl.addEventListener('drop', function (event) {
        const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        if (!getTimelineAccompanimentPatternFromPayload(payload, targetInstrument)) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        const bounds = dropEl.getBoundingClientRect();
        const relativeX = Math.max(0, Math.min(bounds.width - 1, event.clientX - bounds.left));
        const startBar = Math.max(1, Math.min(totalBars, Math.floor((relativeX / bounds.width) * totalBars) + 1));
        setTimelineDragDropTargetsVisible(false);
        addTimelineAccompanimentSegment(payload, targetInstrument, startBar);
    });
}

function createTimelineTrackOverlayDropzone(rowLayout, targetInstrument, repeatIndex, repeatBarCount) {
    const dropEl = document.createElement('div');
    dropEl.className = 'timeline-track-overlay-dropzone';
    dropEl.style.setProperty('--timeline-track-start-bar', String(rowLayout.startBar + repeatIndex * repeatBarCount));
    dropEl.style.setProperty('--timeline-track-bar-count', String(repeatBarCount));
    dropEl.addEventListener('dragover', function (event) {
        const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        const candidateEntries = getTimelinePayloadCandidateEntries(payload);
        const matchingEntry = candidateEntries.find(function (entry) {
            const pattern = findPatternById(entry.patternId);
            return pattern && pattern.labelType !== 'Begleitung' &&
                Array.isArray(entry.targetInstruments) && entry.targetInstruments.indexOf(targetInstrument) !== -1;
        });
        const accompanimentPattern = getTimelineAccompanimentPatternFromPayload(payload, targetInstrument);
        if (!matchingEntry && !accompanimentPattern) {
            return;
        }
        event.preventDefault();
        dropEl.classList.add('is-drop-target');
    });
    dropEl.addEventListener('dragleave', function () {
        dropEl.classList.remove('is-drop-target');
    });
    dropEl.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const payload = timelineActiveDragPayload || getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        setTimelineDragDropTargetsVisible(false);
        if (getTimelineAccompanimentPatternFromPayload(payload, targetInstrument)) {
            addTimelineAccompanimentSegment(
                payload,
                targetInstrument,
                rowLayout.startBar + repeatIndex * repeatBarCount
            );
            return;
        }
        insertTimelineOverlayIntoRepeatSlot(payload, rowLayout.rowGroups, repeatIndex);
    });
    return dropEl;
}

function renderTimelineSequence() {
    const sequenceEl = document.getElementById('timelineSequence');
    const patternDisplayInfo = buildPatternDisplayLabelMap(timelineState.sourcePatterns);
    const entryGroups = buildTimelineDisplayGroups(timelineState.entries, timelineState.sourcePatterns);
    const visualRows = buildTimelineVisualRows(entryGroups, timelineState.sourcePatterns);
    const layout = buildTimelineHorizontalLayout(visualRows);
    const playbackStartBar = Math.min(
        layout.totalBars,
        normalizeTimelinePlaybackStartBar(timelineState.playbackStartBar)
    );
    timelineState.playbackStartBar = playbackStartBar;
    sequenceEl.innerHTML = '';

    const editorEl = document.createElement('section');
    editorEl.className = 'timeline-track-editor';
    const headingEl = document.createElement('h4');
    headingEl.textContent = timelineText('arrangement.tracks');
    editorEl.appendChild(headingEl);

    const scrollEl = document.createElement('div');
    scrollEl.className = 'timeline-track-scroll';
    scrollEl.style.setProperty('--timeline-track-total-bars', String(layout.totalBars));

    const rulerRowEl = document.createElement('div');
    rulerRowEl.className = 'timeline-track-ruler-row';
    const rulerLabelEl = document.createElement('strong');
    rulerLabelEl.className = 'timeline-track-label timeline-track-ruler-label';
    rulerLabelEl.textContent = timelineText('arrangement.barLabel');
    const rulerCanvasEl = document.createElement('div');
    rulerCanvasEl.className = 'timeline-track-ruler-canvas';
    for (let barNumber = 1; barNumber <= layout.totalBars; barNumber++) {
        const barEl = document.createElement('span');
        barEl.className = 'timeline-track-ruler-bar';
        barEl.dataset.timelineBar = String(barNumber);
        barEl.textContent = String(barNumber);
        const startLabel = timelineText('arrangement.startPlaybackAtBar', { number: barNumber });
        barEl.title = startLabel;
        barEl.setAttribute('aria-label', startLabel);
        barEl.setAttribute('aria-pressed', barNumber === playbackStartBar ? 'true' : 'false');
        barEl.classList.toggle('is-playback-start', barNumber === playbackStartBar);
        bindTimelineRulerBarDropTarget(barEl, layout, barNumber);
        bindTimelinePlaybackStartBar(barEl, barNumber);
        rulerCanvasEl.appendChild(barEl);
    }
    const addBarButtonEl = document.createElement('button');
    addBarButtonEl.type = 'button';
    addBarButtonEl.className = 'timeline-track-add-bar';
    addBarButtonEl.textContent = '+';
    addBarButtonEl.title = timelineText('arrangement.addBar');
    addBarButtonEl.setAttribute('aria-label', timelineText('arrangement.addBar'));
    addBarButtonEl.disabled = layout.totalBars >= 999;
    addBarButtonEl.addEventListener('click', function () {
        addTimelineBar(layout);
    });
    rulerCanvasEl.appendChild(addBarButtonEl);
    rulerRowEl.append(rulerLabelEl, rulerCanvasEl);
    scrollEl.appendChild(rulerRowEl);

    layout.usedTargets.forEach(function (targetInstrument) {
        const laneEl = document.createElement('div');
        laneEl.className = 'timeline-track-row';
        const laneLabelEl = document.createElement('strong');
        laneLabelEl.className = 'timeline-track-label';
        laneLabelEl.textContent = getTimelineInstrumentLabel(targetInstrument);
        const laneCanvasEl = document.createElement('div');
        laneCanvasEl.className = 'timeline-track-canvas';

        const laneClips = assignTimelineTrackClipLayers(layout.clips.filter(function (clip) {
            return clip.targetInstrument === targetInstrument;
        }));
        const layerCount = Math.max.apply(null, laneClips.map(function (clip) {
            return (clip.layerIndex || 0) + 1;
        }).concat(1));
        laneCanvasEl.style.setProperty('--timeline-track-layer-count', String(layerCount));

        laneClips.forEach(function (clip) {
            const clipEl = clip.kind === 'segment'
                ? createTimelineTrackSegmentClip(clip, patternDisplayInfo, layout)
                : createTimelineTrackEntryClip(clip, patternDisplayInfo, layout);
            if (clipEl) {
                laneCanvasEl.appendChild(clipEl);
            }
        });

        layout.rows.forEach(function (rowLayout) {
            const repeatInfo = getTimelineRowRepeatInfo(rowLayout.rowGroups);
            const repeatCount = normalizeTimelineGroupRepeatCount(repeatInfo.repeatCount || 1);
            if (!timelineRowHasAccompaniment(rowLayout.rowGroups) || repeatCount <= 1) {
                return;
            }
            const repeatBarCount = Math.max(1, Math.round(rowLayout.barCount / repeatCount));
            for (let repeatIndex = 0; repeatIndex < repeatCount; repeatIndex++) {
                laneCanvasEl.appendChild(createTimelineTrackOverlayDropzone(
                    rowLayout,
                    targetInstrument,
                    repeatIndex,
                    repeatBarCount
                ));
            }
        });

        const laneDropEl = document.createElement('div');
        laneDropEl.className = 'timeline-track-lane-drop';
        bindTimelineTrackLaneDrop(laneDropEl, targetInstrument, layout.totalBars);
        laneCanvasEl.appendChild(laneDropEl);
        laneEl.append(laneLabelEl, laneCanvasEl);
        scrollEl.appendChild(laneEl);
    });

    editorEl.appendChild(scrollEl);
    sequenceEl.appendChild(editorEl);
}

function renderTimelinePanel() {
    const panelEl = document.getElementById('timelinePanel');
    const titleEl = document.getElementById('timelineTitle');
    const tempoInputEl = document.getElementById('timelineTempo');
    const practiceTempoInputEl = document.getElementById('practiceTempo');
    const shekereBeatEl = document.getElementById('timelineShekereBeat');
    const practiceShekereBeatEl = document.getElementById('practiceShekereBeat');
    const feelInputMap = {
        Kenkeni: document.getElementById('timelineFeelKenkeni'),
        Sangban: document.getElementById('timelineFeelSangban'),
        Doundoun: document.getElementById('timelineFeelDoundoun'),
        Dreierbass: document.getElementById('timelineFeelDreierbass'),
        Djembe_1: document.getElementById('timelineFeelDjembe1'),
        Djembe_2: document.getElementById('timelineFeelDjembe2'),
        Djembe_3: document.getElementById('timelineFeelDjembe3')
    };
    const practiceFeelInputMap = {
        Kenkeni: document.getElementById('practiceFeelKenkeni'),
        Sangban: document.getElementById('practiceFeelSangban'),
        Doundoun: document.getElementById('practiceFeelDoundoun'),
        Dreierbass: document.getElementById('practiceFeelDreierbass'),
        Djembe_1: document.getElementById('practiceFeelDjembe1'),
        Djembe_2: document.getElementById('practiceFeelDjembe2'),
        Djembe_3: document.getElementById('practiceFeelDjembe3')
    };
    const swingProfileWrapEl = document.getElementById('timelineSwingProfile');
    const practiceSwingProfileWrapEl = document.getElementById('practiceSwingProfile');
    const swingProfileInputs = [
        document.getElementById('timelineSwingAnchor1'),
        document.getElementById('timelineSwingAnchor2'),
        document.getElementById('timelineSwingAnchor3'),
        document.getElementById('timelineSwingAnchor4')
    ];
    const practiceSwingProfileInputs = [
        document.getElementById('practiceSwingAnchor1'),
        document.getElementById('practiceSwingAnchor2'),
        document.getElementById('practiceSwingAnchor3'),
        document.getElementById('practiceSwingAnchor4')
    ];
    const patternDisplayInfo = buildPatternDisplayLabelMap(timelineState.sourcePatterns);
    const timelineDisplayGroups = buildTimelineDisplayGroups(timelineState.entries, timelineState.sourcePatterns);
    const timelineVisualRows = buildTimelineVisualRows(timelineDisplayGroups, timelineState.sourcePatterns);
    if (!panelEl || !titleEl) {
        return;
    }

    titleEl.textContent = '';
    const titleLabelEl = document.createElement('span');
    titleLabelEl.className = 'practice-title-label';
    titleLabelEl.textContent = timelineText('arrangement.title') + ':';
    const rhythmTitleEl = document.createElement('span');
    rhythmTitleEl.className = 'practice-title-rhythm';
    titleEl.append(titleLabelEl, document.createTextNode(' '), rhythmTitleEl);
    if (rhythmTitleEl) {
        rhythmTitleEl.textContent = typeof getCurrentRhythmTitle === 'function'
            ? (getCurrentRhythmTitle() || timelineText('arrangement.untitledRhythm'))
            : timelineText('arrangement.untitledRhythm');
    }

    if (tempoInputEl) {
        tempoInputEl.value = normalizeTimelineTempo(timelineState.tempo);
    }
    if (practiceTempoInputEl) {
        practiceTempoInputEl.value = normalizeTimelineTempo(timelineState.tempo);
    }
    if (shekereBeatEl) {
        shekereBeatEl.setAttribute('aria-pressed', timelineState.shekereBeatEnabled ? 'true' : 'false');
        shekereBeatEl.classList.toggle('is-active', Boolean(timelineState.shekereBeatEnabled));
    }
    if (practiceShekereBeatEl) {
        practiceShekereBeatEl.setAttribute('aria-pressed', timelineState.shekereBeatEnabled ? 'true' : 'false');
        practiceShekereBeatEl.classList.toggle('is-active', Boolean(timelineState.shekereBeatEnabled));
    }
    const currentFeelOffsets = normalizeTimelineFeelOffsets(timelineState.feelOffsets);
    Object.keys(feelInputMap).forEach(function (instrumentName) {
        if (feelInputMap[instrumentName]) {
            feelInputMap[instrumentName].value = currentFeelOffsets[instrumentName];
        }
        if (practiceFeelInputMap[instrumentName]) {
            practiceFeelInputMap[instrumentName].value = currentFeelOffsets[instrumentName];
        }
    });
    const currentProfileKey = getCurrentTimelineSwingProfileKey();
    const currentProfile = normalizeTimelineSwingProfile(
        timelineState.swingProfile && timelineState.swingProfile[currentProfileKey],
        currentProfileKey
    );
    swingProfileInputs.forEach(function (inputEl, inputIndex) {
        if (!inputEl) {
            return;
        }
        const inputLabel = inputEl.closest('label');
        const isActive = inputIndex < currentProfile.length;
        if (inputLabel) {
            inputLabel.classList.toggle('is-inline-flex', isActive);
            inputLabel.classList.toggle('is-hidden', !isActive);
        }
        if (isActive) {
            inputEl.value = currentProfile[inputIndex];
        }
    });
    practiceSwingProfileInputs.forEach(function (inputEl, inputIndex) {
        if (!inputEl) {
            return;
        }
        const inputLabel = inputEl.closest('label');
        const isActive = inputIndex < currentProfile.length;
        if (inputLabel) {
            inputLabel.classList.toggle('is-inline-flex', isActive);
            inputLabel.classList.toggle('is-hidden', !isActive);
        }
        if (isActive) {
            inputEl.value = currentProfile[inputIndex];
        }
    });
    const profileTitleEl = swingProfileWrapEl ? swingProfileWrapEl.querySelector('span') : null;
    if (profileTitleEl) {
        const meter = currentProfileKey === 'binaer'
            ? '16/8'
            : (currentProfileKey === 'tenaer' ? '12/8' : '9/8');
        profileTitleEl.textContent = timelineText('arrangement.swingProfileMeter', { meter: meter });
    }
    const practiceProfileTitleEl = practiceSwingProfileWrapEl
        ? practiceSwingProfileWrapEl.querySelector('span')
        : document.getElementById('practiceSwingProfileTitle');
    if (practiceProfileTitleEl) {
        const practiceMeter = currentProfileKey === 'binaer'
            ? '16/8'
            : (currentProfileKey === 'tenaer' ? '12/8' : '9/8');
        practiceProfileTitleEl.textContent = timelineText('practice.dialog.swingProfileMeter', {
            meter: practiceMeter
        });
    }

    panelEl.hidden = !timelineState.visible;
    if (typeof updateMobileArrangementButtonVisibility === 'function') {
        updateMobileArrangementButtonVisibility();
    }
    if (panelEl.hidden) {
        return;
    }

    renderTimelinePatternLibrary();
    renderTimelineSequence();
    window.requestAnimationFrame(alignPatternLibraryCardWidths);
}
