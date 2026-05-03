// Abschnittstimeline: Zustand, Bibliothek, Rendering, Drag/Drop und Metadaten
const timelineDjembeTargets = ['Djembe_1', 'Djembe_2', 'Djembe_3'];
const timelineBassTargets = ['Kenkeni', 'Sangban', 'Doundoun'];
const timelineMetadataVersion = 3;
const defaultTimelineSwingProfiles = {
    binaer: [6, -5, 6, 10],
    tenaer: [0, -15, -10],
    neunaer: [0, 15, 10]
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
    sourceHash: '',
    sheetHash: '',
    sourcePatterns: [],
    sourceLibraryGroups: [],
    entries: [],
    tempo: 100,
    swingFactor: 0,
    swingProfile: {
        binaer: defaultTimelineSwingProfiles.binaer.slice(),
        tenaer: defaultTimelineSwingProfiles.tenaer.slice(),
        neunaer: defaultTimelineSwingProfiles.neunaer.slice()
    },
    feelOffsets: Object.assign({}, defaultTimelineFeelOffsets)
};

function normalizeTimelineSwingFactor(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 0;
    }
    return Math.max(0, Math.min(100, Math.round(numericValue)));
}

function normalizeTimelineTempo(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 100;
    }
    return Math.max(30, Math.min(180, Math.round(numericValue)));
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
    return Math.max(-100, Math.min(100, Math.round(numericValue)));
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
    if (normalizedName === 'Dununba') {
        return 'Doundoun';
    }
    return normalizedName;
}

function isSpecificDjembeName(instrumentName) {
    return instrumentName === 'Djembe 1' ||
        instrumentName === 'Djembe 2' ||
        instrumentName === 'Djembe 3';
}

function resolvePatternSourceInstrumentName(bar, labelInfo) {
    const explicitInstrumentName = String(bar && bar.instrument || '').trim();
    const effectiveInstrumentName = String(bar && (bar.effectiveInstrument || bar.instrument) || '').trim();

    if (explicitInstrumentName && explicitInstrumentName !== 'Leer') {
        return explicitInstrumentName;
    }

    if ((labelInfo.type === 'Intro' || labelInfo.type === 'Echauffement' || labelInfo.type === 'Outro') &&
        isSpecificDjembeName(effectiveInstrumentName)) {
        return 'Djembe';
    }

    return effectiveInstrumentName;
}

function buildPatternDisplayName(pattern, occurrenceIndex) {
    const labelText = pattern.labelName || pattern.labelType || 'Passage';
    const instrumentText = pattern.instrument || 'Instrument';
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
            return noteSignature + '||' + controlSignature;
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
    let previousBar = null;

    function hasRepeatMarker(markerValue) {
        if (Array.isArray(markerValue)) {
            return markerValue.length > 0;
        }
        return Boolean(markerValue);
    }

    rhythmBars.forEach(function (bar) {
        const labelInfo = getPlayerLabelInfo(bar.effectiveLabel || bar.label);
        const sourceInstrumentName = resolvePatternSourceInstrumentName(bar, labelInfo);
        const patternInstrument = normalizePatternInstrumentName(sourceInstrumentName);
        if (!patternInstrument || !labelInfo.type || !labelInfo.raw) {
            currentPattern = null;
            previousBar = bar;
            return;
        }

        const shouldSplitAtRepeatBoundary = previousBar &&
            currentPattern &&
            currentPattern.sourceInstrument === sourceInstrumentName &&
            currentPattern.labelName === labelInfo.raw &&
            (hasRepeatMarker(previousBar.repeat && previousBar.repeat.end) ||
             hasRepeatMarker(bar.repeat && bar.repeat.start));

        if (!currentPattern ||
            currentPattern.sourceInstrument !== sourceInstrumentName ||
            currentPattern.labelName !== labelInfo.raw ||
            shouldSplitAtRepeatBoundary) {
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
        previousBar = bar;
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
        patternId: pattern.id,
        patternSourceKey: pattern.sourceKey,
        handMode: pattern.instrument === 'Djembe'
            ? String(overrideConfig.handMode || 'auto')
            : '',
        swingFactor: overrideConfig.swingFactor === null || overrideConfig.swingFactor === undefined
            ? null
            : normalizeTimelineSwingFactor(overrideConfig.swingFactor),
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
        handMode: entry.handMode || 'auto',
        swingFactor: entry.swingFactor,
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
                return repeatRange.startBar === currentBarIndex && repeatRange.endBar <= endBarIndex;
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
    const expandedBars = expandTimelineBarsWithRepeats(
        rhythmBars,
        Array.isArray(repeatRanges) ? repeatRanges : [],
        1,
        rhythmBars.length
    );
    const defaultEntries = [];
    let previousPatternSourceKey = '';
    let previousPatternBarIndex = -1;

    patternLibrary.forEach(function (pattern) {
        patternBySourceKey[pattern.sourceKey] = pattern;
    });

    expandedBars.forEach(function (bar) {
        const matchedPattern = patternBySourceKey[bar.patternSourceKey];
        if (!matchedPattern) {
            return;
        }

        const isPatternStart = previousPatternSourceKey !== bar.patternSourceKey ||
            Number(bar.patternBarIndex) === 0 ||
            Number(bar.patternBarIndex) <= previousPatternBarIndex;

        if (isPatternStart) {
            defaultEntries.push(cloneTimelineEntryFromPattern(matchedPattern));
        }

        previousPatternSourceKey = bar.patternSourceKey;
        previousPatternBarIndex = Number(bar.patternBarIndex);
    });

    return defaultEntries;
}

function computePatternLibraryHash(patternLibrary) {
    return patternLibrary.map(function (pattern) {
        return pattern.sourceKey + ':' + pattern.bars.length;
    }).join('|');
}

function syncTimelineEntriesWithPatternLibrary(patternLibrary, existingEntries) {
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
            patternId: matchedPattern.id,
            patternSourceKey: matchedPattern.sourceKey,
            handMode: matchedPattern.instrument === 'Djembe'
                ? String(entry.handMode || 'auto')
                : '',
            swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
                ? null
                : normalizeTimelineSwingFactor(entry.swingFactor),
            targetInstruments: targetInstruments
        };
    }).filter(Boolean);

    patternLibrary.forEach(function (pattern) {
        if (matchedSourceKeys.indexOf(pattern.sourceKey) !== -1) {
            return;
        }
        syncedEntries.push(cloneTimelineEntryFromPattern(pattern));
    });

    return syncedEntries;
}

function buildTimelinePlayerPayload(patternLibrary, timelineEntries) {
    return [{
        Name: titel.attr('text'),
        Rhythmus: rhythm,
        TimelineMode: true,
        Tempo: normalizeTimelineTempo(timelineState.tempo),
        SwingFactor: normalizeTimelineSwingFactor(timelineState.swingFactor),
        SwingProfile: normalizeAllTimelineSwingProfiles(timelineState.swingProfile),
        FeelOffsets: normalizeTimelineFeelOffsets(timelineState.feelOffsets),
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
                patternId: entry.patternId,
                patternSourceKey: entry.patternSourceKey,
                handMode: entry.handMode || '',
                swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
                    ? null
                    : normalizeTimelineSwingFactor(entry.swingFactor),
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            };
        })
    }];
}

function findPatternById(patternId) {
    return timelineState.sourcePatterns.find(function (pattern) {
        return pattern.id === patternId;
    }) || null;
}

function updateTimelineMetadataNode() {
    const existingMetadataNode = s.select(timelineMetadataSelector);
    if (existingMetadataNode) {
        existingMetadataNode.remove();
    }

    const metadataPayload = JSON.stringify({
        version: timelineMetadataVersion,
        sourceHash: timelineState.sourceHash,
        tempo: normalizeTimelineTempo(timelineState.tempo),
        swingFactor: normalizeTimelineSwingFactor(timelineState.swingFactor),
        swingProfile: normalizeAllTimelineSwingProfiles(timelineState.swingProfile),
        feelOffsets: normalizeTimelineFeelOffsets(timelineState.feelOffsets),
        entries: timelineState.entries.map(function (entry) {
            return {
                id: entry.id,
                blockId: entry.blockId || '',
                parallelGroupId: entry.parallelGroupId || '',
                patternId: entry.patternId,
                patternSourceKey: entry.patternSourceKey,
                handMode: entry.handMode || '',
                swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
                    ? null
                    : normalizeTimelineSwingFactor(entry.swingFactor),
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            };
        })
    });
    const encodedPayload = window.btoa(unescape(encodeURIComponent(metadataPayload)));
    const metadataNode = document.createElementNS('http://www.w3.org/2000/svg', 'desc');
    metadataNode.setAttribute('id', 'timeline_metadata');
    metadataNode.textContent = encodedPayload;
    s.node.appendChild(metadataNode);
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
    const canReusePersistedEntries = Array.isArray(syncOptions.persistedEntries) &&
        syncOptions.persistedVersion === timelineMetadataVersion &&
        syncOptions.persistedSourceHash === newSourceHash;
    const fallbackEntries = buildDefaultTimelineEntriesFromRhythmBars(
        readResult.rhythmBars,
        readResult.repeatRanges,
        patternLibrary
    );
    const currentEntries = canReusePersistedEntries
        ? syncOptions.persistedEntries
        : fallbackEntries;
    const syncedEntries = syncTimelineEntriesWithPatternLibrary(patternLibrary, currentEntries);

    timelineState.sourcePatterns = patternLibrary;
    timelineState.sourceLibraryGroups = buildPatternLibraryBlocks(fallbackEntries, patternLibrary);
    timelineState.sourceHash = newSourceHash;
    timelineState.sheetHash = computeTimelineSheetHash(readResult);
    timelineState.entries = syncedEntries.length > 0 ? syncedEntries : fallbackEntries;
    syncTimelineBlockIdSequence(timelineState.entries);
    timelineState.tempo = normalizeTimelineTempo(syncOptions.tempo ?? timelineState.tempo);
    timelineState.swingFactor = normalizeTimelineSwingFactor(syncOptions.swingFactor ?? timelineState.swingFactor);
    timelineState.swingProfile = normalizeAllTimelineSwingProfiles(syncOptions.swingProfile);
    timelineState.feelOffsets = normalizeTimelineFeelOffsets(syncOptions.feelOffsets);

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
        swingFactor: timelineState.swingFactor,
        swingProfile: normalizeAllTimelineSwingProfiles(timelineState.swingProfile),
        feelOffsets: normalizeTimelineFeelOffsets(timelineState.feelOffsets),
        persistedEntries: timelineState.entries.map(function (entry) {
            return {
                id: entry.id,
                blockId: entry.blockId || '',
                parallelGroupId: entry.parallelGroupId || '',
                patternId: entry.patternId,
                patternSourceKey: entry.patternSourceKey,
                handMode: entry.handMode || '',
                swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
                    ? null
                    : normalizeTimelineSwingFactor(entry.swingFactor),
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

function getTimelineTargetSignature(targetInstruments) {
    return (Array.isArray(targetInstruments) ? targetInstruments.slice() : [])
        .sort()
        .join('|');
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

        const sequenceLabel = 'Takt ' + sequenceIndexByPatternId[entry.patternId];
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
        text: summaryParts.length <= 1
            ? ''
            : summaryParts.map(function (part) {
                return part.label + (part.count > 1 ? ' | Wiederholung x' + part.count : '');
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
        const targetSignature = getTimelineTargetSignature(entry.targetInstruments);
        const pattern = patternById[entry.patternId] || null;
        const labelName = pattern ? (pattern.labelName || pattern.name || '') : '';
        const swingSignature = entry.swingFactor === null || entry.swingFactor === undefined
            ? ''
            : String(normalizeTimelineSwingFactor(entry.swingFactor));
        const handSignature = pattern && pattern.instrument === 'Djembe'
            ? String(entry.handMode || 'auto')
            : '';
        const blockId = String(entry.blockId || '');
        const parallelGroupId = String(entry.parallelGroupId || '');

        if (previousGroup &&
            previousGroup.labelName === labelName &&
            previousGroup.targetSignature === targetSignature &&
            previousGroup.swingSignature === swingSignature &&
            previousGroup.handSignature === handSignature &&
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
            swingSignature: swingSignature,
            handSignature: handSignature,
            blockId: blockId,
            parallelGroupId: parallelGroupId,
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
    const swingSignature = entry.swingFactor === null || entry.swingFactor === undefined
        ? ''
        : String(normalizeTimelineSwingFactor(entry.swingFactor));

    return [
        entry.patternId || '',
        entry.patternSourceKey || '',
        entry.handMode || '',
        swingSignature,
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

        previousBlock.entries.push(entry);
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
        summaryParts.push('Takt ' + (patternIndex + 1) + (barCount > 1 ? ' (' + barCount + ' Takte)' : ''));
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
            }
            if (!rowParallelGroupId && nextGroup.count !== firstGroup.count) {
                break;
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

function buildPatternDisplayLabelMap(patternLibrary) {
    const patternGroups = buildPatternLibraryGroups(patternLibrary);
    const displayNameByPatternId = {};

    patternGroups.forEach(function (patternGroup, groupIndex) {
        const firstPattern = patternGroup.patterns[0];
        const labelText = firstPattern.labelName || firstPattern.labelType || 'Passage';
        const instrumentText = firstPattern.instrument || 'Instrument';
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
                swingFactor: entry.swingFactor,
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            });
        }).filter(Boolean);
        if (entriesToInsert.length === 0) {
            return;
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

function insertTimelineEntryParallelToRow(payload, rowGroups) {
    const groups = Array.isArray(rowGroups) ? rowGroups.filter(Boolean) : [];
    if (groups.length === 0) {
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
                swingFactor: entry.swingFactor,
                targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
            });
        }).filter(Boolean);
        if (entriesToInsert.length === 0) {
            return;
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

function createTimelineDropzone(targetIndex) {
    const dropzone = document.createElement('div');
    dropzone.className = 'timeline-dropzone';
    dropzone.textContent = targetIndex === timelineState.entries.length
        ? 'Pattern hier ablegen'
        : 'Hier einfügen';
    dropzone.addEventListener('dragover', function (event) {
        event.preventDefault();
    });
    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        const payload = getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
        insertTimelineEntryAtIndex(payload, targetIndex);
    });
    return dropzone;
}

function createTimelineParallelDropzone(rowGroups) {
    const dropzone = document.createElement('div');
    dropzone.className = 'timeline-dropzone timeline-dropzone-inline';
    dropzone.textContent = 'Parallel hier';
    bindParallelDropTarget(dropzone, rowGroups);
    return dropzone;
}

function bindParallelDropTarget(targetEl, rowGroups) {
    if (!targetEl) {
        return;
    }

    targetEl.addEventListener('dragover', function (event) {
        event.preventDefault();
    });
    targetEl.addEventListener('drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const payload = getTimelineDragPayload(event.dataTransfer.getData('text/plain'));
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
            event.dataTransfer.setData('text/plain', JSON.stringify({
                type: 'pattern-group',
                entries: (patternGroup.entries || []).map(function (entry) {
                    return {
                        patternId: entry.patternId,
                        handMode: entry.handMode || 'auto',
                        swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
                            ? null
                            : normalizeTimelineSwingFactor(entry.swingFactor),
                        targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
                    };
                })
            }));
        });

        const patternTitle = document.createElement('strong');
        patternTitle.textContent = patternDisplayInfo.displayNameByPatternId[firstPattern.id] || firstPattern.name;
        const actionWrap = document.createElement('div');
        actionWrap.className = 'timeline-card-actions';
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.textContent = '+';
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
                    swingFactor: entry.swingFactor,
                    targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
                });
            });
            timelineState.entries.push.apply(timelineState.entries, newEntries.filter(Boolean));
            updateTimelineMetadataNode();
            renderTimelinePanel();
        });
        const addHint = document.createElement('small');
        addHint.textContent = 'Ans Ende der Timeline hinzufuegen';

        actionWrap.appendChild(addButton);
        actionWrap.appendChild(addHint);
        card.appendChild(patternTitle);
        card.appendChild(actionWrap);
        listEl.appendChild(card);
    });
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

    const widestCardWidth = cards.reduce(function (maxWidth, cardEl) {
        const computedStyle = window.getComputedStyle(cardEl);
        const horizontalPadding = (parseFloat(computedStyle.paddingLeft) || 0) +
            (parseFloat(computedStyle.paddingRight) || 0) +
            (parseFloat(computedStyle.borderLeftWidth) || 0) +
            (parseFloat(computedStyle.borderRightWidth) || 0);
        const contentWidth = Array.from(cardEl.children).reduce(function (childMaxWidth, childEl) {
            return Math.max(childMaxWidth, Math.ceil(childEl.scrollWidth || childEl.getBoundingClientRect().width || 0));
        }, 0);
        return Math.max(maxWidth, Math.ceil(contentWidth + horizontalPadding));
    }, 0);
    const availableWidth = Math.max(180, Math.floor(listEl.clientWidth));
    const targetWidth = Math.max(180, Math.min(widestCardWidth, availableWidth));
    document.documentElement.style.setProperty('--timeline-library-card-width', targetWidth + 'px');

    cards.forEach(function (cardEl) {
        cardEl.style.width = targetWidth + 'px';
    });
}

function renderTimelineSequence() {
    const sequenceEl = document.getElementById('timelineSequence');
    const patternDisplayInfo = buildPatternDisplayLabelMap(timelineState.sourcePatterns);
    const entryGroups = buildTimelineDisplayGroups(timelineState.entries, timelineState.sourcePatterns);
    const visualRows = buildTimelineVisualRows(entryGroups, timelineState.sourcePatterns);
    sequenceEl.innerHTML = '';

    if (visualRows.length === 0) {
        sequenceEl.appendChild(createTimelineDropzone(0));
        return;
    }

    visualRows.forEach(function (rowGroups) {
        if (!rowGroups || rowGroups.length === 0) {
            return;
        }

        sequenceEl.appendChild(createTimelineDropzone(rowGroups[0].startIndex));
        const rowEl = document.createElement('div');
        rowEl.className = 'timeline-sequence-row';
        bindParallelDropTarget(rowEl, rowGroups);

        rowGroups.forEach(function (group) {
            const entry = group.entries[0];
            const pattern = findPatternById(group.patternId);
            if (!pattern) {
                return;
            }
            const repeatInfo = getTimelineGroupRepeatInfo(group);

            const entryCard = document.createElement('div');
            entryCard.className = 'timeline-entry';
            entryCard.draggable = true;
            bindParallelDropTarget(entryCard, rowGroups);
            entryCard.addEventListener('dragstart', function (event) {
                event.dataTransfer.setData('text/plain', JSON.stringify({
                    type: 'timeline-entry-group',
                    startIndex: group.startIndex,
                    count: group.count
                }));
            });

            const mainWrap = document.createElement('div');
            mainWrap.className = 'timeline-entry-main';
            const titleEl = document.createElement('strong');
            titleEl.textContent = patternDisplayInfo.displayNameByPatternId[pattern.id] || pattern.name;
            const groupSummary = buildTimelineGroupSummary(group, timelineState.sourcePatterns);
            const metaEl = document.createElement('small');
            const displayedBarCount = groupSummary.totalBars || (Array.isArray(pattern.bars) ? pattern.bars.length : 0);
            metaEl.textContent = 'Takte: ' + displayedBarCount + (!groupSummary.text && group.count > 1 ? ' | Wiederholung x' + group.count : '');
            mainWrap.appendChild(titleEl);
            mainWrap.appendChild(metaEl);
            if (groupSummary.text) {
                const summaryEl = document.createElement('small');
                summaryEl.textContent = groupSummary.text;
                summaryEl.className = 'timeline-card-summary';
                mainWrap.appendChild(summaryEl);
            }

            const swingWrap = document.createElement('div');
            swingWrap.className = 'timeline-entry-targets';
            const swingLabelEl = document.createElement('label');
            swingLabelEl.appendChild(document.createTextNode('Swing'));
            const swingInputEl = document.createElement('input');
            swingInputEl.type = 'number';
            swingInputEl.min = '0';
            swingInputEl.max = '100';
            swingInputEl.step = '1';
            swingInputEl.placeholder = String(normalizeTimelineSwingFactor(timelineState.swingFactor));
            swingInputEl.value = entry.swingFactor === null || entry.swingFactor === undefined
                ? ''
                : String(normalizeTimelineSwingFactor(entry.swingFactor));
            swingInputEl.classList.add('timeline-input-compact');
            swingInputEl.addEventListener('change', function () {
                const normalizedValue = swingInputEl.value === ''
                    ? null
                    : normalizeTimelineSwingFactor(swingInputEl.value);
                group.entries.forEach(function (groupEntry) {
                    groupEntry.swingFactor = normalizedValue;
                });
                swingInputEl.value = normalizedValue === null ? '' : String(normalizedValue);
                updateTimelineMetadataNode();
                renderTimelinePanel();
            });
            swingLabelEl.appendChild(swingInputEl);
            swingWrap.appendChild(swingLabelEl);
            mainWrap.appendChild(swingWrap);

            if (pattern.instrument === 'Djembe') {
                const handWrap = document.createElement('div');
                handWrap.className = 'timeline-entry-targets';
                const handLabelEl = document.createElement('label');
                handLabelEl.appendChild(document.createTextNode('Handsatz'));
                const handSelectEl = document.createElement('select');
                [
                    { value: 'auto', label: 'Auto' },
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
                    group.entries.forEach(function (groupEntry) {
                        groupEntry.handMode = handSelectEl.value;
                    });
                    updateTimelineMetadataNode();
                    renderTimelinePanel();
                });
                handLabelEl.appendChild(handSelectEl);
                handWrap.appendChild(handLabelEl);
                mainWrap.appendChild(handWrap);
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
                    labelEl.appendChild(document.createTextNode(targetName.replace('_', ' ')));
                    targetWrap.appendChild(labelEl);
                });
                mainWrap.appendChild(targetWrap);
            } else {
                const fixedTargetEl = document.createElement('div');
                fixedTargetEl.className = 'timeline-entry-targets';
                fixedTargetEl.textContent = 'Instrument: ' + pattern.instrument;
                mainWrap.appendChild(fixedTargetEl);
            }

            const actionWrap = document.createElement('div');
            actionWrap.className = 'timeline-entry-actions';
            const cloneButton = document.createElement('button');
            cloneButton.type = 'button';
            cloneButton.textContent = 'Klon';
            cloneButton.addEventListener('click', function () {
                const sourceEntries = group.entries.slice(0, repeatInfo.unitLength);
                const clonedEntries = sourceEntries.map(function (groupEntry) {
                    return cloneTimelineEntry(groupEntry);
                }).filter(Boolean);
                if (clonedEntries.length === 0) {
                    return;
                }
                timelineState.entries.splice.apply(timelineState.entries, [group.endIndex, 0].concat(clonedEntries));
                updateTimelineMetadataNode();
                renderTimelinePanel();
            });

            const reduceButton = document.createElement('button');
            reduceButton.type = 'button';
            reduceButton.textContent = '-';
            reduceButton.disabled = repeatInfo.repeatCount <= 1;
            reduceButton.addEventListener('click', function () {
                if (repeatInfo.repeatCount <= 1 || repeatInfo.unitLength <= 0) {
                    return;
                }
                timelineState.entries.splice(group.endIndex - repeatInfo.unitLength, repeatInfo.unitLength);
                updateTimelineMetadataNode();
                renderTimelinePanel();
            });

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = 'Entfernen';
            removeButton.addEventListener('click', function () {
                timelineState.entries.splice(group.startIndex, group.count);
                updateTimelineMetadataNode();
                renderTimelinePanel();
            });

            actionWrap.appendChild(cloneButton);
            actionWrap.appendChild(reduceButton);
            actionWrap.appendChild(removeButton);

            entryCard.appendChild(mainWrap);
            entryCard.appendChild(actionWrap);
            rowEl.appendChild(entryCard);
        });

        rowEl.appendChild(createTimelineParallelDropzone(rowGroups));
        sequenceEl.appendChild(rowEl);
    });

    sequenceEl.appendChild(createTimelineDropzone(timelineState.entries.length));
}

function renderTimelinePanel() {
    const panelEl = document.getElementById('timelinePanel');
    const statusEl = document.getElementById('timelineStatus');
    const tempoInputEl = document.getElementById('timelineTempo');
    const swingInputEl = document.getElementById('timelineSwingFactor');
    const feelInputMap = {
        Kenkeni: document.getElementById('timelineFeelKenkeni'),
        Sangban: document.getElementById('timelineFeelSangban'),
        Doundoun: document.getElementById('timelineFeelDoundoun'),
        Dreierbass: document.getElementById('timelineFeelDreierbass'),
        Djembe_1: document.getElementById('timelineFeelDjembe1'),
        Djembe_2: document.getElementById('timelineFeelDjembe2'),
        Djembe_3: document.getElementById('timelineFeelDjembe3')
    };
    const swingProfileWrapEl = document.getElementById('timelineSwingProfile');
    const swingProfileInputs = [
        document.getElementById('timelineSwingAnchor1'),
        document.getElementById('timelineSwingAnchor2'),
        document.getElementById('timelineSwingAnchor3'),
        document.getElementById('timelineSwingAnchor4')
    ];
    const patternDisplayInfo = buildPatternDisplayLabelMap(timelineState.sourcePatterns);
    const timelineDisplayGroups = buildTimelineDisplayGroups(timelineState.entries, timelineState.sourcePatterns);
    const timelineVisualRows = buildTimelineVisualRows(timelineDisplayGroups, timelineState.sourcePatterns);
    if (!panelEl || !statusEl) {
        return;
    }

    if (tempoInputEl) {
        tempoInputEl.value = normalizeTimelineTempo(timelineState.tempo);
    }
    if (swingInputEl) {
        swingInputEl.value = normalizeTimelineSwingFactor(timelineState.swingFactor);
    }
    const currentFeelOffsets = normalizeTimelineFeelOffsets(timelineState.feelOffsets);
    Object.keys(feelInputMap).forEach(function (instrumentName) {
        if (feelInputMap[instrumentName]) {
            feelInputMap[instrumentName].value = currentFeelOffsets[instrumentName];
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
    const profileTitleEl = swingProfileWrapEl ? swingProfileWrapEl.querySelector('span') : null;
    if (profileTitleEl) {
        profileTitleEl.textContent = currentProfileKey === 'binaer'
            ? 'Profil 16/8'
            : (currentProfileKey === 'tenaer' ? 'Profil 12/8' : 'Profil 9/8');
    }

    panelEl.hidden = !timelineState.visible;
    const sourceLibraryGroupCount = Array.isArray(timelineState.sourceLibraryGroups) && timelineState.sourceLibraryGroups.length > 0
        ? timelineState.sourceLibraryGroups.length
        : patternDisplayInfo.groups.length;
    statusEl.textContent = sourceLibraryGroupCount + ' Pattern aus dem Blatt, ' +
        timelineVisualRows.length + ' Eintrag/Eintaege in der Timeline.';

    if (panelEl.hidden) {
        return;
    }

    renderTimelinePatternLibrary();
    renderTimelineSequence();
    window.requestAnimationFrame(alignPatternLibraryCardWidths);
}
