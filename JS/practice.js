// Übungsmodus: deterministischer Loop-Trainer aus der Pattern-Bibliothek
const practiceState = {
    visible: false,
    accompanimentPatternIds: [],
    soloPatternIds: [],
    loopsWithoutSolo: 1,
    loopsWithSolo: 1,
    repeatCount: 4,
    timerMinutes: 0,
    accompanimentStart: 'immediate',
    accompanimentBetweenPatterns: false,
    pauseAccompanimentForLeadInPatterns: false,
    audioLatencyMs: 30,
    h2hRestMute: false,
    instrumentVolumes: {},
    patternHandModes: {},
    patternSwingFactors: {},
    patternRepeatCounts: {},
    patternTargetInstruments: {},
    patternChooserExpanded: false,
    defaultsApplied: false,
    defaultSelectionSourceHash: ''
};

const practiceRepeatCountMax = 999;
const practiceTimerMinutesMax = 240;

const practiceTrackInstrumentNames = ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'];
const practiceScrollerInstrumentLabels = {
    Kenkeni: 'Kenkeni',
    Sangban: 'Sangban',
    Doundoun: 'Doundoun',
    Dreierbass: 'Dreierbass',
    Djembe_1: 'Djembe 1',
    Djembe_2: 'Djembe 2',
    Djembe_3: 'Djembe 3'
};
const practiceScrollerNoteLabels = {
    tone: 'To',
    slap: 'Sl',
    bass: 'Ba',
    muff: 'Mu',
    open: 'Op',
    Flam: 'Fl',
    'T-Flam': 'TF',
    'S-Flam': 'SF',
    Open: 'Op',
    Muffled: 'Mu',
    Bell: 'Be',
    Klick: 'Kl',
    Bell_Open: 'B+O',
    Bell_Muffled: 'B+M',
    Bell_Klick: 'B+K'
};
const practiceScrollerState = {
    totalSteps: 0,
    currentStep: 0,
    activeStep: -1,
    loopSegments: [],
    loopLength: 0,
    loopStartStep: 0,
    visualLoopLength: 0,
    playbackSegments: [],
    playbackTotalSteps: 0,
    playbackLoopStart: 0,
    playbackLoopLength: 0,
    visualCycleSteps: 0,
    visualTotalSteps: 0,
    visualTailSteps: 0,
    visualLoopCopies: 4,
    stepsPerBar: 32,
    stepWidth: 18,
    playheadRatio: 0.3,
    leadInSteps: 8,
    visualLeadInSteps: 8,
    preRollLineSteps: 24,
    playbackTimers: [],
    animationFrameId: null,
    playbackEvents: [],
    playbackAnchor: null,
    activeCells: []
};

function isPracticeScrollerCompactViewport() {
    return typeof window !== 'undefined' &&
        window.matchMedia &&
        window.matchMedia('(max-width: 760px), (hover: none) and (pointer: coarse)').matches;
}

function getPracticeScrollerVisualLoopCopies() {
    if (isPracticeScrollerCompactViewport()) {
        return 1;
    }
    return practiceScrollerState.visualLoopCopies;
}

function getPracticeScrollerTailSteps(visualLoopLength) {
    if (!isPracticeScrollerCompactViewport()) {
        return 0;
    }
    const loopLength = Math.max(0, Number(visualLoopLength) || 0);
    if (loopLength <= 0) {
        return 0;
    }
    return Math.min(loopLength, getPracticeScrollerStepsPerBar() * 3);
}

function normalizePracticeCount(rawValue, fallback, minValue, maxValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return fallback;
    }
    return Math.max(minValue, Math.min(maxValue, Math.round(numericValue)));
}

function normalizePracticeStartMode(rawValue) {
    return rawValue === 'afterCall' ||
        rawValue === 'afterIntro' ||
        rawValue === 'afterCallIntro'
        ? rawValue
        : 'immediate';
}

function normalizePracticeAudioLatency(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 0;
    }
    return Math.max(0, Math.min(1000, Math.round(numericValue)));
}

function normalizePracticeInstrumentVolume(rawValue) {
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return 1;
    }
    return Math.max(0, Math.min(2, numericValue));
}

function normalizePracticeInstrumentVolumes(rawVolumes) {
    const sourceVolumes = rawVolumes && typeof rawVolumes === 'object' ? rawVolumes : {};
    return practiceTrackInstrumentNames.reduce(function (volumes, instrumentName) {
        const normalizedVolume = normalizePracticeInstrumentVolume(sourceVolumes[instrumentName]);
        if (normalizedVolume !== 1) {
            volumes[instrumentName] = normalizedVolume;
        }
        return volumes;
    }, {});
}

function getPracticeInstrumentVolume(instrumentName) {
    if (Object.prototype.hasOwnProperty.call(practiceState.instrumentVolumes, instrumentName)) {
        return normalizePracticeInstrumentVolume(practiceState.instrumentVolumes[instrumentName]);
    }
    return 1;
}

function normalizePracticeTimerMinutes(rawValue) {
    return normalizePracticeCount(rawValue, 0, 0, practiceTimerMinutesMax);
}

function normalizePracticeHandMode(rawValue) {
    return rawValue === 'h2h' || rawValue === 'hoh' ? rawValue : 'auto';
}

function normalizePracticePatternSwingFactor(rawValue) {
    if (rawValue === null || rawValue === undefined || rawValue === '') {
        return null;
    }
    if (typeof normalizeTimelineSwingFactor === 'function') {
        return normalizeTimelineSwingFactor(rawValue);
    }
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return null;
    }
    return Math.max(0, Math.min(100, Math.round(numericValue)));
}

function normalizePracticePatternRepeatCount(rawValue) {
    if (rawValue === null || rawValue === undefined || rawValue === '') {
        return null;
    }
    const numericValue = Number(rawValue);
    if (!Number.isFinite(numericValue)) {
        return null;
    }
    return Math.max(1, Math.min(32, Math.round(numericValue)));
}

function getPracticeSoloPatternRepeatCount(patternId) {
    const normalizedCount = normalizePracticePatternRepeatCount(practiceState.patternRepeatCounts[patternId]);
    return normalizedCount === null
        ? normalizePracticeCount(practiceState.loopsWithSolo, 1, 1, 32)
        : normalizedCount;
}

function getPracticePatternsByIds(patternIds) {
    const ids = Array.isArray(patternIds) ? patternIds : [];
    return ids
        .map(function (patternId) {
            return findPatternById(patternId);
        })
        .filter(Boolean);
}

function getPracticePatternSourceKeys(patternIds) {
    return getPracticePatternsByIds(patternIds).map(function (pattern) {
        return pattern.sourceKey;
    }).filter(Boolean);
}

function getPracticePatternHandModesBySourceKey(patternHandModes) {
    return Object.keys(patternHandModes || {}).reduce(function (modeBySourceKey, patternId) {
        const pattern = findPatternById(patternId);
        if (pattern && pattern.sourceKey) {
            modeBySourceKey[pattern.sourceKey] = normalizePracticeHandMode(patternHandModes[patternId]);
        }
        return modeBySourceKey;
    }, {});
}

function getPracticePatternTargetsBySourceKey(patternTargets) {
    return Object.keys(patternTargets || {}).reduce(function (targetBySourceKey, patternId) {
        const pattern = findPatternById(patternId);
        if (pattern && pattern.sourceKey) {
            targetBySourceKey[pattern.sourceKey] = patternTargets[patternId];
        }
        return targetBySourceKey;
    }, {});
}

function getPracticePatternSwingFactorsBySourceKey(patternSwingFactors) {
    return Object.keys(patternSwingFactors || {}).reduce(function (factorBySourceKey, patternId) {
        const pattern = findPatternById(patternId);
        const normalizedFactor = normalizePracticePatternSwingFactor(patternSwingFactors[patternId]);
        if (pattern && pattern.sourceKey && normalizedFactor !== null) {
            factorBySourceKey[pattern.sourceKey] = normalizedFactor;
        }
        return factorBySourceKey;
    }, {});
}

function getPracticePatternRepeatCountsBySourceKey(patternRepeatCounts) {
    return Object.keys(patternRepeatCounts || {}).reduce(function (countBySourceKey, patternId) {
        const pattern = findPatternById(patternId);
        const normalizedCount = normalizePracticePatternRepeatCount(patternRepeatCounts[patternId]);
        if (pattern && pattern.sourceKey && normalizedCount !== null) {
            countBySourceKey[pattern.sourceKey] = normalizedCount;
        }
        return countBySourceKey;
    }, {});
}

function getPersistedPracticePatternTargets() {
    const persistedTargets = Object.assign({}, practiceState.patternTargetInstruments);
    practiceState.accompanimentPatternIds.concat(practiceState.soloPatternIds).forEach(function (patternId) {
        const pattern = findPatternById(patternId);
        if (pattern && getPracticeTargetOptionsForPattern(pattern).length > 0) {
            persistedTargets[patternId] = getPracticeTargetForPattern(pattern);
        }
    });
    return persistedTargets;
}

function getPracticePatternHandModesFromMetadata(patternHandModes, patternHandModesBySourceKey, patternLibrary) {
    const patterns = Array.isArray(patternLibrary) ? patternLibrary : timelineState.sourcePatterns;
    const modeById = patternHandModes && typeof patternHandModes === 'object' ? patternHandModes : {};
    const modeBySourceKey = patternHandModesBySourceKey && typeof patternHandModesBySourceKey === 'object'
        ? patternHandModesBySourceKey
        : {};

    return patterns.reduce(function (resolvedModes, pattern) {
        if (!pattern || !pattern.id || pattern.instrument !== 'Djembe') {
            return resolvedModes;
        }
        const rawMode = modeBySourceKey[pattern.sourceKey] || modeById[pattern.id];
        const normalizedMode = normalizePracticeHandMode(rawMode);
        if (normalizedMode !== 'auto') {
            resolvedModes[pattern.id] = normalizedMode;
        }
        return resolvedModes;
    }, {});
}

function getPracticePatternTargetsFromMetadata(patternTargets, patternTargetsBySourceKey, patternLibrary) {
    const patterns = Array.isArray(patternLibrary) ? patternLibrary : timelineState.sourcePatterns;
    const targetById = patternTargets && typeof patternTargets === 'object' ? patternTargets : {};
    const targetBySourceKey = patternTargetsBySourceKey && typeof patternTargetsBySourceKey === 'object'
        ? patternTargetsBySourceKey
        : {};

    return patterns.reduce(function (resolvedTargets, pattern) {
        if (!pattern || !pattern.id) {
            return resolvedTargets;
        }
        const allowedTargets = getPracticeTargetOptionsForPattern(pattern).map(function (optionData) {
            return optionData.value;
        });
        const rawTarget = targetBySourceKey[pattern.sourceKey] || targetById[pattern.id];
        if (allowedTargets.indexOf(rawTarget) !== -1) {
            resolvedTargets[pattern.id] = rawTarget;
        }
        return resolvedTargets;
    }, {});
}

function getPracticePatternSwingFactorsFromMetadata(patternSwingFactors, patternSwingFactorsBySourceKey, patternLibrary) {
    const patterns = Array.isArray(patternLibrary) ? patternLibrary : timelineState.sourcePatterns;
    const factorById = patternSwingFactors && typeof patternSwingFactors === 'object' ? patternSwingFactors : {};
    const factorBySourceKey = patternSwingFactorsBySourceKey && typeof patternSwingFactorsBySourceKey === 'object'
        ? patternSwingFactorsBySourceKey
        : {};

    return patterns.reduce(function (resolvedFactors, pattern) {
        if (!pattern || !pattern.id) {
            return resolvedFactors;
        }
        const rawFactor = factorBySourceKey[pattern.sourceKey] !== undefined
            ? factorBySourceKey[pattern.sourceKey]
            : factorById[pattern.id];
        const normalizedFactor = normalizePracticePatternSwingFactor(rawFactor);
        if (normalizedFactor !== null) {
            resolvedFactors[pattern.id] = normalizedFactor;
        }
        return resolvedFactors;
    }, {});
}

function getPracticePatternRepeatCountsFromMetadata(patternRepeatCounts, patternRepeatCountsBySourceKey, patternLibrary) {
    const patterns = Array.isArray(patternLibrary) ? patternLibrary : timelineState.sourcePatterns;
    const countById = patternRepeatCounts && typeof patternRepeatCounts === 'object' ? patternRepeatCounts : {};
    const countBySourceKey = patternRepeatCountsBySourceKey && typeof patternRepeatCountsBySourceKey === 'object'
        ? patternRepeatCountsBySourceKey
        : {};

    return patterns.reduce(function (resolvedCounts, pattern) {
        if (!pattern || !pattern.id) {
            return resolvedCounts;
        }
        const rawCount = countBySourceKey[pattern.sourceKey] !== undefined
            ? countBySourceKey[pattern.sourceKey]
            : countById[pattern.id];
        const normalizedCount = normalizePracticePatternRepeatCount(rawCount);
        if (normalizedCount !== null) {
            resolvedCounts[pattern.id] = normalizedCount;
        }
        return resolvedCounts;
    }, {});
}

function getPracticePatternIdsFromMetadata(sourceKeys, patternIds, patternLibrary) {
    const patterns = Array.isArray(patternLibrary) ? patternLibrary : timelineState.sourcePatterns;
    const sourceKeyList = Array.isArray(sourceKeys) ? sourceKeys : [];
    const patternIdList = Array.isArray(patternIds) ? patternIds : [];
    const selectedIds = [];

    sourceKeyList.forEach(function (sourceKey) {
        const matchedPattern = patterns.find(function (pattern) {
            return pattern && pattern.sourceKey === sourceKey;
        });
        if (matchedPattern && selectedIds.indexOf(matchedPattern.id) === -1) {
            selectedIds.push(matchedPattern.id);
        }
    });

    patternIdList.forEach(function (patternId) {
        const matchedPattern = patterns.find(function (pattern) {
            return pattern && pattern.id === patternId;
        });
        if (matchedPattern && selectedIds.indexOf(matchedPattern.id) === -1) {
            selectedIds.push(matchedPattern.id);
        }
    });

    return selectedIds;
}

function buildPracticeMetadata() {
    const persistedPatternTargets = getPersistedPracticePatternTargets();
    return {
        version: 1,
        sourceHash: timelineState.sourceHash,
        accompanimentStart: practiceState.accompanimentStart,
        accompanimentBetweenPatterns: practiceState.accompanimentBetweenPatterns,
        pauseAccompanimentForLeadInPatterns: practiceState.pauseAccompanimentForLeadInPatterns,
        audioLatencyMs: practiceState.audioLatencyMs,
        h2hRestMute: practiceState.h2hRestMute,
        instrumentVolumes: normalizePracticeInstrumentVolumes(practiceState.instrumentVolumes),
        loopsWithoutSolo: practiceState.loopsWithoutSolo,
        loopsWithSolo: practiceState.loopsWithSolo,
        repeatCount: practiceState.repeatCount,
        timerMinutes: practiceState.timerMinutes,
        accompanimentPatternIds: practiceState.accompanimentPatternIds.slice(),
        soloPatternIds: practiceState.soloPatternIds.slice(),
        accompanimentPatternSourceKeys: getPracticePatternSourceKeys(practiceState.accompanimentPatternIds),
        soloPatternSourceKeys: getPracticePatternSourceKeys(practiceState.soloPatternIds),
        patternHandModes: Object.assign({}, practiceState.patternHandModes),
        patternHandModesBySourceKey: getPracticePatternHandModesBySourceKey(practiceState.patternHandModes),
        patternSwingFactors: Object.assign({}, practiceState.patternSwingFactors),
        patternSwingFactorsBySourceKey: getPracticePatternSwingFactorsBySourceKey(practiceState.patternSwingFactors),
        patternRepeatCounts: Object.assign({}, practiceState.patternRepeatCounts),
        patternRepeatCountsBySourceKey: getPracticePatternRepeatCountsBySourceKey(practiceState.patternRepeatCounts),
        patternTargetInstruments: persistedPatternTargets,
        patternTargetInstrumentsBySourceKey: getPracticePatternTargetsBySourceKey(persistedPatternTargets)
    };
}

function resetPracticeForSource(sourceHash) {
    practiceState.accompanimentPatternIds = [];
    practiceState.soloPatternIds = [];
    practiceState.patternTargetInstruments = {};
    practiceState.patternSwingFactors = {};
    practiceState.patternRepeatCounts = {};
    practiceState.instrumentVolumes = {};
    practiceState.defaultsApplied = false;
    practiceState.defaultSelectionSourceHash = sourceHash || timelineState.sourceHash || '';
}

function applyPracticeMetadata(metadata, patternLibrary, sourceHash) {
    if (!metadata || typeof metadata !== 'object') {
        resetPracticeForSource(sourceHash);
        return;
    }

    practiceState.accompanimentStart = normalizePracticeStartMode(metadata.accompanimentStart);
    practiceState.accompanimentBetweenPatterns = Boolean(metadata.accompanimentBetweenPatterns);
    practiceState.pauseAccompanimentForLeadInPatterns = Boolean(metadata.pauseAccompanimentForLeadInPatterns);
    practiceState.audioLatencyMs = normalizePracticeAudioLatency(metadata.audioLatencyMs);
    practiceState.h2hRestMute = Boolean(metadata.h2hRestMute);
    practiceState.instrumentVolumes = normalizePracticeInstrumentVolumes(metadata.instrumentVolumes);
    practiceState.loopsWithoutSolo = normalizePracticeCount(metadata.loopsWithoutSolo, 1, 0, 32);
    practiceState.loopsWithSolo = normalizePracticeCount(metadata.loopsWithSolo, 1, 1, 32);
    practiceState.repeatCount = normalizePracticeCount(metadata.repeatCount, 4, 1, practiceRepeatCountMax);
    practiceState.timerMinutes = normalizePracticeTimerMinutes(metadata.timerMinutes);
    practiceState.accompanimentPatternIds = getPracticePatternIdsFromMetadata(
        metadata.accompanimentPatternSourceKeys,
        metadata.accompanimentPatternIds,
        patternLibrary
    );
    practiceState.soloPatternIds = getPracticePatternIdsFromMetadata(
        metadata.soloPatternSourceKeys,
        metadata.soloPatternIds,
        patternLibrary
    );
    practiceState.patternHandModes = getPracticePatternHandModesFromMetadata(
        metadata.patternHandModes,
        metadata.patternHandModesBySourceKey,
        patternLibrary
    );
    practiceState.patternTargetInstruments = getPracticePatternTargetsFromMetadata(
        metadata.patternTargetInstruments,
        metadata.patternTargetInstrumentsBySourceKey,
        patternLibrary
    );
    practiceState.patternSwingFactors = getPracticePatternSwingFactorsFromMetadata(
        metadata.patternSwingFactors,
        metadata.patternSwingFactorsBySourceKey,
        patternLibrary
    );
    practiceState.patternRepeatCounts = getPracticePatternRepeatCountsFromMetadata(
        metadata.patternRepeatCounts,
        metadata.patternRepeatCountsBySourceKey,
        patternLibrary
    );
    const hasPersistedPatternSelection =
        practiceState.accompanimentPatternIds.length > 0 ||
        practiceState.soloPatternIds.length > 0;
    practiceState.defaultsApplied = hasPersistedPatternSelection;
    practiceState.defaultSelectionSourceHash = sourceHash || timelineState.sourceHash || '';
}

function syncPracticeSelectionsWithPatternLibrary() {
    if (practiceState.defaultSelectionSourceHash !== timelineState.sourceHash) {
        practiceState.defaultsApplied = false;
        practiceState.defaultSelectionSourceHash = timelineState.sourceHash;
    }

    const availableIds = timelineState.sourcePatterns.map(function (pattern) {
        return pattern.id;
    });
    practiceState.accompanimentPatternIds = practiceState.accompanimentPatternIds.filter(function (patternId) {
        return availableIds.indexOf(patternId) !== -1;
    });
    practiceState.soloPatternIds = practiceState.soloPatternIds.filter(function (patternId) {
        return availableIds.indexOf(patternId) !== -1;
    });
    Object.keys(practiceState.patternHandModes).forEach(function (patternId) {
        if (availableIds.indexOf(patternId) === -1) {
            delete practiceState.patternHandModes[patternId];
        }
    });
    Object.keys(practiceState.patternSwingFactors).forEach(function (patternId) {
        const normalizedFactor = normalizePracticePatternSwingFactor(practiceState.patternSwingFactors[patternId]);
        if (availableIds.indexOf(patternId) === -1 || normalizedFactor === null) {
            delete practiceState.patternSwingFactors[patternId];
        } else {
            practiceState.patternSwingFactors[patternId] = normalizedFactor;
        }
    });
    Object.keys(practiceState.patternRepeatCounts).forEach(function (patternId) {
        const normalizedCount = normalizePracticePatternRepeatCount(practiceState.patternRepeatCounts[patternId]);
        if (availableIds.indexOf(patternId) === -1 || normalizedCount === null) {
            delete practiceState.patternRepeatCounts[patternId];
        } else {
            practiceState.patternRepeatCounts[patternId] = normalizedCount;
        }
    });
    Object.keys(practiceState.patternTargetInstruments).forEach(function (patternId) {
        const pattern = findPatternById(patternId);
        const allowedTargets = pattern
            ? getPracticeTargetOptionsForPattern(pattern).map(function (optionData) {
                return optionData.value;
            })
            : [];
        if (availableIds.indexOf(patternId) === -1 ||
                allowedTargets.indexOf(practiceState.patternTargetInstruments[patternId]) === -1) {
            delete practiceState.patternTargetInstruments[patternId];
        }
    });
}

function ensurePracticeDefaultSelections() {
    if (practiceState.defaultsApplied ||
            practiceState.accompanimentPatternIds.length > 0 ||
            practiceState.soloPatternIds.length > 0) {
        return;
    }

    const accompanimentPatternIds = timelineState.sourcePatterns
        .filter(function (pattern) {
            return pattern && pattern.labelType === 'Begleitung';
        })
        .map(function (pattern) {
            return pattern.id;
        });

    if (accompanimentPatternIds.length > 0) {
        practiceState.accompanimentPatternIds = accompanimentPatternIds;
    } else {
        practiceState.soloPatternIds = timelineState.sourcePatterns
            .filter(function (pattern) {
                return pattern && pattern.id;
            })
            .map(function (pattern) {
                return pattern.id;
            });
    }
    practiceState.defaultsApplied = true;
}

function togglePracticePatternSelection(listName, patternId, selected) {
    const selectedIds = practiceState[listName];
    const currentIndex = selectedIds.indexOf(patternId);
    if (selected && currentIndex === -1) {
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        selectedIds.push(patternId);
    }
    if (!selected && currentIndex !== -1) {
        if (typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        selectedIds.splice(currentIndex, 1);
    }
}

function movePracticePatternSelection(listName, patternId, direction) {
    const selectedIds = practiceState[listName];
    const currentIndex = selectedIds.indexOf(patternId);
    const targetIndex = currentIndex + direction;
    if (currentIndex === -1 || targetIndex < 0 || targetIndex >= selectedIds.length) {
        return false;
    }

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    selectedIds.splice(currentIndex, 1);
    selectedIds.splice(targetIndex, 0, patternId);
    return true;
}

function reorderPracticePatternSelection(listName, draggedPatternId, targetPatternId) {
    const selectedIds = practiceState[listName];
    const currentIndex = selectedIds.indexOf(draggedPatternId);
    const targetIndex = selectedIds.indexOf(targetPatternId);
    if (currentIndex === -1 || targetIndex === -1 || currentIndex === targetIndex) {
        return false;
    }

    if (typeof recordArrangementHistorySnapshot === 'function') {
        recordArrangementHistorySnapshot();
    }
    selectedIds.splice(currentIndex, 1);
    selectedIds.splice(targetIndex, 0, draggedPatternId);
    return true;
}

function notifyPracticePatternOrderChanged() {
    renderPracticePanel();
    if (typeof notifyPracticeSelectionChanged === 'function') {
        notifyPracticeSelectionChanged();
    }
}

function getPracticeTargetOptionsForPattern(pattern) {
    const sourceInstrument = String(pattern && (pattern.sourceInstrument || pattern.instrument) || '').trim();
    const defaultTargets = Array.isArray(pattern && pattern.defaultTargets) ? pattern.defaultTargets : [];
    const normalizedDefaultTargets = normalizePracticeTargetInstruments(defaultTargets);
    const isGenericDjembe = sourceInstrument === 'Djembe' ||
        (pattern && pattern.instrument === 'Djembe' && normalizedDefaultTargets.length > 1);
    const isGenericBass = sourceInstrument === 'Bässe' || sourceInstrument === 'Baesse';

    if (isGenericDjembe) {
        return [
            { value: 'Djembe_1', label: 'Djembe 1' },
            { value: 'Djembe_2', label: 'Djembe 2' },
            { value: 'Djembe_3', label: 'Djembe 3' }
        ];
    }

    if (isGenericBass) {
        return [
            { value: 'Kenkeni', label: 'Kenkeni' },
            { value: 'Sangban', label: 'Sangban' },
            { value: 'Doundoun', label: 'Doundoun' },
            { value: 'Dreierbass', label: 'Dreierbass' }
        ];
    }

    return [];
}

function getPracticeTargetForPattern(pattern) {
    const targetOptions = getPracticeTargetOptionsForPattern(pattern);
    if (targetOptions.length === 0) {
        return '';
    }
    const selectedTarget = practiceState.patternTargetInstruments[pattern.id];
    if (targetOptions.some(function (optionData) {
        return optionData.value === selectedTarget;
    })) {
        return selectedTarget;
    }
    return targetOptions[0].value;
}

function getPracticePatternPlaybackTargets(pattern) {
    const selectedTarget = getPracticeTargetForPattern(pattern);
    if (selectedTarget) {
        return [selectedTarget];
    }
    return Array.isArray(pattern.defaultTargets) ? pattern.defaultTargets.slice() : [];
}

function getPracticePatternDisplayInstrument(pattern) {
    const selectedTarget = getPracticeTargetForPattern(pattern);
    if (selectedTarget && practiceScrollerInstrumentLabels[selectedTarget]) {
        return practiceScrollerInstrumentLabels[selectedTarget];
    }
    return pattern.sourceInstrument || pattern.instrument || '';
}

function getPracticePatternDisplayTitle(pattern) {
    const rawName = String(pattern && (pattern.name || pattern.id) || '');
    const prefixMatch = rawName.match(/^(P\d+)\s*-/i);
    const prefix = prefixMatch ? prefixMatch[1] : rawName;
    const instrumentText = getPracticePatternDisplayInstrument(pattern);
    const labelText = pattern.labelName || pattern.labelType || '';
    return [prefix, instrumentText].filter(Boolean).join(' - ') +
        (labelText ? ' / ' + labelText : '');
}

function createPracticePatternRow(pattern, listName) {
    const rowEl = document.createElement('label');
    rowEl.className = 'practice-pattern-row';
    rowEl.classList.add(listName === 'accompanimentPatternIds' ? 'is-accompaniment-pattern' : 'is-solo-pattern');
    rowEl.dataset.patternId = pattern.id;

    const inputEl = document.createElement('input');
    inputEl.type = 'checkbox';
    inputEl.checked = practiceState[listName].indexOf(pattern.id) !== -1;
    if (inputEl.checked) {
        rowEl.classList.add('is-selected');
    }
    inputEl.addEventListener('change', function () {
        togglePracticePatternSelection(listName, pattern.id, inputEl.checked);
        renderPracticePanel();
        if (typeof notifyPracticeSelectionChanged === 'function') {
            notifyPracticeSelectionChanged();
        }
    });

    const titleEl = document.createElement('span');
    titleEl.className = 'practice-pattern-title';
    titleEl.textContent = getPracticePatternDisplayTitle(pattern);

    const metaEl = document.createElement('span');
    metaEl.className = 'practice-pattern-meta';
    metaEl.textContent = '';

    rowEl.append(inputEl, titleEl, metaEl);
    const patternControlsEl = document.createElement('span');
    patternControlsEl.className = 'practice-pattern-controls';

    if (listName === 'soloPatternIds' && inputEl.checked) {
        rowEl.classList.add('has-order-controls');
        const orderControlsEl = document.createElement('span');
        orderControlsEl.className = 'practice-pattern-order-controls';

        const dragHandleEl = document.createElement('span');
        dragHandleEl.className = 'practice-pattern-drag-handle';
        dragHandleEl.textContent = '↕';
        dragHandleEl.setAttribute('aria-hidden', 'true');

        const upButtonEl = document.createElement('button');
        upButtonEl.type = 'button';
        upButtonEl.className = 'practice-pattern-order-button';
        upButtonEl.textContent = '↑';
        upButtonEl.setAttribute('aria-label', 'Übungsteil nach oben verschieben');
        upButtonEl.disabled = practiceState[listName].indexOf(pattern.id) === 0;

        const downButtonEl = document.createElement('button');
        downButtonEl.type = 'button';
        downButtonEl.className = 'practice-pattern-order-button';
        downButtonEl.textContent = '↓';
        downButtonEl.setAttribute('aria-label', 'Übungsteil nach unten verschieben');
        downButtonEl.disabled = practiceState[listName].indexOf(pattern.id) === practiceState[listName].length - 1;

        [upButtonEl, downButtonEl].forEach(function (buttonEl) {
            buttonEl.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const direction = buttonEl === upButtonEl ? -1 : 1;
                if (movePracticePatternSelection(listName, pattern.id, direction)) {
                    notifyPracticePatternOrderChanged();
                }
            });
        });

        orderControlsEl.append(dragHandleEl, upButtonEl, downButtonEl);
        rowEl.appendChild(orderControlsEl);
        rowEl.draggable = true;
        rowEl.addEventListener('dragstart', function (event) {
            rowEl.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', pattern.id);
        });
        rowEl.addEventListener('dragend', function () {
            rowEl.classList.remove('is-dragging');
        });
        rowEl.addEventListener('dragover', function (event) {
            event.preventDefault();
            rowEl.classList.add('is-drop-target');
            event.dataTransfer.dropEffect = 'move';
        });
        rowEl.addEventListener('dragleave', function () {
            rowEl.classList.remove('is-drop-target');
        });
        rowEl.addEventListener('drop', function (event) {
            event.preventDefault();
            rowEl.classList.remove('is-drop-target');
            const draggedPatternId = event.dataTransfer.getData('text/plain');
            if (reorderPracticePatternSelection(listName, draggedPatternId, pattern.id)) {
                notifyPracticePatternOrderChanged();
            }
        });
    }

    if (pattern.instrument === 'Djembe') {
        const handModeWrapEl = document.createElement('label');
        handModeWrapEl.className = 'practice-pattern-hand-mode-wrap';
        handModeWrapEl.appendChild(document.createTextNode('Handsatz'));
        const handModeEl = document.createElement('select');
        handModeEl.className = 'practice-pattern-hand-mode';
        [
            { value: 'auto', label: 'Auto' },
            { value: 'h2h', label: 'H2H' },
            { value: 'hoh', label: 'HOH' }
        ].forEach(function (optionData) {
            const optionEl = document.createElement('option');
            optionEl.value = optionData.value;
            optionEl.textContent = optionData.label;
            handModeEl.appendChild(optionEl);
        });
        handModeEl.value = normalizePracticeHandMode(practiceState.patternHandModes[pattern.id]);
        handModeEl.addEventListener('click', function (event) {
            event.stopPropagation();
        });
        handModeEl.addEventListener('change', function () {
            const selectedMode = normalizePracticeHandMode(handModeEl.value);
            const previousMode = normalizePracticeHandMode(practiceState.patternHandModes[pattern.id]);
            if (previousMode !== selectedMode && typeof recordArrangementHistorySnapshot === 'function') {
                recordArrangementHistorySnapshot();
            }
            if (selectedMode === 'auto') {
                delete practiceState.patternHandModes[pattern.id];
            } else {
                practiceState.patternHandModes[pattern.id] = selectedMode;
            }
            notifyPracticeHandModeChanged();
        });
        handModeWrapEl.appendChild(handModeEl);
        patternControlsEl.appendChild(handModeWrapEl);
    }
    if (inputEl.checked) {
        if (listName === 'soloPatternIds') {
            const repeatCountEl = document.createElement('label');
            repeatCountEl.className = 'practice-pattern-repeat-count';
            repeatCountEl.appendChild(document.createTextNode('Wdh.'));
            const repeatInputEl = document.createElement('input');
            repeatInputEl.type = 'number';
            repeatInputEl.min = '1';
            repeatInputEl.max = '32';
            repeatInputEl.step = '1';
            repeatInputEl.placeholder = String(normalizePracticeCount(practiceState.loopsWithSolo, 1, 1, 32));
            const patternRepeatCount = normalizePracticePatternRepeatCount(practiceState.patternRepeatCounts[pattern.id]);
            repeatInputEl.value = patternRepeatCount === null ? '' : String(patternRepeatCount);
            repeatInputEl.addEventListener('click', function (event) {
                event.stopPropagation();
            });
            repeatInputEl.addEventListener('change', function () {
                const normalizedCount = normalizePracticePatternRepeatCount(repeatInputEl.value);
                const previousCount = normalizePracticePatternRepeatCount(practiceState.patternRepeatCounts[pattern.id]);
                if (previousCount !== normalizedCount && typeof recordArrangementHistorySnapshot === 'function') {
                    recordArrangementHistorySnapshot();
                }
                if (normalizedCount === null) {
                    delete practiceState.patternRepeatCounts[pattern.id];
                    repeatInputEl.value = '';
                } else {
                    practiceState.patternRepeatCounts[pattern.id] = normalizedCount;
                    repeatInputEl.value = String(normalizedCount);
                }
                notifyPracticePatternOrderChanged();
            });
            repeatCountEl.appendChild(repeatInputEl);
            patternControlsEl.appendChild(repeatCountEl);
        }

        const swingFactorEl = document.createElement('label');
        swingFactorEl.className = 'practice-pattern-swing-factor';
        swingFactorEl.appendChild(document.createTextNode('Swing'));
        const swingInputEl = document.createElement('input');
        swingInputEl.type = 'number';
        swingInputEl.min = '0';
        swingInputEl.max = '100';
        swingInputEl.step = '1';
        swingInputEl.placeholder = typeof normalizeTimelineSwingFactor === 'function'
            ? String(normalizeTimelineSwingFactor(timelineState.swingFactor))
            : '0';
        const patternSwingFactor = normalizePracticePatternSwingFactor(practiceState.patternSwingFactors[pattern.id]);
        swingInputEl.value = patternSwingFactor === null ? '' : String(patternSwingFactor);
        swingInputEl.addEventListener('click', function (event) {
            event.stopPropagation();
        });
        swingInputEl.addEventListener('change', function () {
            const normalizedFactor = normalizePracticePatternSwingFactor(swingInputEl.value);
            const previousFactor = normalizePracticePatternSwingFactor(practiceState.patternSwingFactors[pattern.id]);
            if (previousFactor !== normalizedFactor && typeof recordArrangementHistorySnapshot === 'function') {
                recordArrangementHistorySnapshot();
            }
            if (normalizedFactor === null) {
                delete practiceState.patternSwingFactors[pattern.id];
                swingInputEl.value = '';
            } else {
                practiceState.patternSwingFactors[pattern.id] = normalizedFactor;
                swingInputEl.value = String(normalizedFactor);
            }
            notifyPracticePatternOrderChanged();
        });
        swingFactorEl.appendChild(swingInputEl);
        patternControlsEl.appendChild(swingFactorEl);

        const targetOptions = getPracticeTargetOptionsForPattern(pattern);
        if (targetOptions.length > 0) {
            const targetEl = document.createElement('select');
            targetEl.className = 'practice-pattern-target';
            targetOptions.forEach(function (optionData) {
                const optionEl = document.createElement('option');
                optionEl.value = optionData.value;
                optionEl.textContent = optionData.label;
                targetEl.appendChild(optionEl);
            });
            targetEl.value = getPracticeTargetForPattern(pattern);
            targetEl.addEventListener('click', function (event) {
                event.stopPropagation();
            });
            targetEl.addEventListener('change', function () {
                if (practiceState.patternTargetInstruments[pattern.id] !== targetEl.value &&
                        typeof recordArrangementHistorySnapshot === 'function') {
                    recordArrangementHistorySnapshot();
                }
                practiceState.patternTargetInstruments[pattern.id] = targetEl.value;
                notifyPracticePatternOrderChanged();
            });
            patternControlsEl.appendChild(targetEl);
        }
    }
    if (patternControlsEl.childNodes.length > 0) {
        rowEl.appendChild(patternControlsEl);
    }
    return rowEl;
}

function isPracticePatternVisibleInList(pattern, listName) {
    if (!pattern) {
        return false;
    }
    if (listName === 'accompanimentPatternIds') {
        return pattern.labelType === 'Begleitung';
    }
    return true;
}

function renderPracticePatternList(containerId, listName) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) {
        return;
    }

    containerEl.innerHTML = '';
    let visiblePatterns = timelineState.sourcePatterns.filter(function (pattern) {
        return isPracticePatternVisibleInList(pattern, listName);
    });
    if (listName === 'soloPatternIds') {
        const selectedPatternIds = practiceState.soloPatternIds;
        const selectedPatterns = selectedPatternIds
            .map(function (patternId) {
                return visiblePatterns.find(function (pattern) {
                    return pattern && pattern.id === patternId;
                });
            })
            .filter(Boolean);
        const unselectedPatterns = visiblePatterns.filter(function (pattern) {
            return selectedPatternIds.indexOf(pattern.id) === -1;
        });
        visiblePatterns = selectedPatterns.concat(unselectedPatterns);
    }

    visiblePatterns.forEach(function (pattern) {
        containerEl.appendChild(createPracticePatternRow(pattern, listName));
    });
}

function updatePracticeInputs() {
    const withoutSoloEl = document.getElementById('practiceWithoutSoloLoops');
    const withSoloEl = document.getElementById('practiceWithSoloLoops');
    const repeatEl = document.getElementById('practiceRepeatCount');
    const timerEl = document.getElementById('practiceTimerMinutes');
    const accompanimentStartEl = document.getElementById('practiceAccompanimentStart');
    const accompanimentBetweenPatternsEl = document.getElementById('practiceAccompanimentBetweenPatterns');
    const pauseAccompanimentForLeadInPatternsEl = document.getElementById('practicePauseAccompanimentForLeadInPatterns');
    const audioLatencyEl = document.getElementById('practiceAudioLatency');
    const audioLatencyRangeEl = document.getElementById('practiceAudioLatencyRange');
    const h2hRestMuteEl = document.getElementById('practiceH2HRestMute');
    const repeatControlEl = document.getElementById('practiceRepeatCountControl');
    const repeatIsDisabled = practiceState.timerMinutes > 0;

    if (withoutSoloEl) {
        withoutSoloEl.value = practiceState.loopsWithoutSolo;
    }
    if (withSoloEl) {
        withSoloEl.value = practiceState.loopsWithSolo;
    }
    if (repeatEl) {
        repeatEl.value = practiceState.repeatCount;
        repeatEl.disabled = repeatIsDisabled;
    }
    if (repeatControlEl) {
        repeatControlEl.classList.toggle('is-disabled', repeatIsDisabled);
        repeatControlEl.querySelectorAll('.practice-stepper-button').forEach(function (buttonEl) {
            buttonEl.disabled = repeatIsDisabled;
        });
    }
    if (timerEl) {
        timerEl.value = practiceState.timerMinutes;
    }
    if (accompanimentStartEl) {
        accompanimentStartEl.value = practiceState.accompanimentStart;
    }
    if (accompanimentBetweenPatternsEl) {
        accompanimentBetweenPatternsEl.checked = Boolean(practiceState.accompanimentBetweenPatterns);
    }
    if (pauseAccompanimentForLeadInPatternsEl) {
        pauseAccompanimentForLeadInPatternsEl.checked = Boolean(practiceState.pauseAccompanimentForLeadInPatterns);
    }
    if (audioLatencyEl) {
        audioLatencyEl.value = practiceState.audioLatencyMs;
    }
    if (audioLatencyRangeEl) {
        audioLatencyRangeEl.value = practiceState.audioLatencyMs;
    }
    if (h2hRestMuteEl) {
        h2hRestMuteEl.checked = Boolean(practiceState.h2hRestMute);
    }
}

function getPracticeRhythmDisplayName() {
    const rawTitle = typeof titel !== 'undefined' && titel && typeof titel.attr === 'function'
        ? String(titel.attr('text') || '').trim()
        : '';
    if (rawTitle && rawTitle !== 'Enter the name of the Rhythm' && rawTitle !== 'Rhythmusname') {
        return rawTitle;
    }

    const rhythmNames = {
        binaer: 'Binärer Rhythmus',
        tenaer: 'Tenärer Rhythmus',
        neunaer: '9/8 Rhythmus'
    };
    return rhythmNames[rhythm] || 'Unbenannter Rhythmus';
}

function renderPracticePanel() {
    const panelEl = document.getElementById('practicePanel');
    const titleEl = document.getElementById('practiceTitle');
    const chooserEl = document.getElementById('practicePatternChooser');
    const chooserToggleEl = document.getElementById('practicePatternChooserToggle');
    const mobileChooserToggleEl = document.getElementById('mobilePatternChooserButton');
    if (!panelEl || !titleEl) {
        return;
    }

    syncPracticeSelectionsWithPatternLibrary();
    ensurePracticeDefaultSelections();
    panelEl.hidden = !practiceState.visible;
    document.body.classList.toggle('is-practice-mode-visible', practiceState.visible);
    titleEl.innerHTML = '<span class="practice-title-label">Übungsmodus:</span> ' +
        '<span class="practice-title-rhythm"></span>';
    const rhythmTitleEl = titleEl.querySelector('.practice-title-rhythm');
    if (rhythmTitleEl) {
        rhythmTitleEl.textContent = getPracticeRhythmDisplayName();
    }

    updatePracticeInputs();
    if (typeof window.syncTimingControlValues === 'function') {
        window.syncTimingControlValues();
    }
    if (chooserEl) {
        chooserEl.hidden = !practiceState.patternChooserExpanded;
    }
    if (chooserToggleEl) {
        chooserToggleEl.textContent = practiceState.patternChooserExpanded
            ? 'Patternauswahl schließen'
            : 'Patternauswahl öffnen';
        chooserToggleEl.setAttribute('aria-expanded', practiceState.patternChooserExpanded ? 'true' : 'false');
        chooserToggleEl.classList.toggle('is-active', practiceState.patternChooserExpanded);
    }
    if (mobileChooserToggleEl) {
        mobileChooserToggleEl.hidden = !practiceState.visible;
        mobileChooserToggleEl.textContent = practiceState.patternChooserExpanded
            ? 'Patternauswahl schließen'
            : 'Patternauswahl öffnen';
        mobileChooserToggleEl.classList.toggle('is-active', practiceState.patternChooserExpanded);
    }

    if (panelEl.hidden) {
        return;
    }

    if (practiceState.patternChooserExpanded) {
        renderPracticePatternList('practiceAccompanimentList', 'accompanimentPatternIds');
        renderPracticePatternList('practiceSoloList', 'soloPatternIds');
    }
}

function createPracticeEntry(pattern, parallelGroupId, blockId, repeatCount, isLeadIn) {
    return {
        id: 'practice-entry-' + blockId + '-' + pattern.id,
        blockId: blockId,
        parallelGroupId: parallelGroupId,
        repeatCount: normalizePracticeCount(repeatCount, 1, 1, 32),
        isLeadIn: Boolean(isLeadIn),
        patternId: pattern.id,
        patternSourceKey: pattern.sourceKey,
        isPracticeTarget: practiceState.soloPatternIds.indexOf(pattern.id) !== -1,
        handMode: pattern.instrument === 'Djembe'
            ? normalizePracticeHandMode(practiceState.patternHandModes[pattern.id])
            : '',
        swingFactor: normalizePracticePatternSwingFactor(practiceState.patternSwingFactors[pattern.id]),
        targetInstruments: getPracticePatternPlaybackTargets(pattern)
    };
}

function addPracticeParallelGroup(entries, patterns, blockIndex, repeatCount, isLeadIn) {
    const safePatterns = patterns.filter(Boolean);
    if (safePatterns.length === 0) {
        return;
    }

    const parallelGroupId = 'practice-group-' + blockIndex;
    const blockId = 'practice-block-' + blockIndex;
    safePatterns.forEach(function (pattern) {
        entries.push(createPracticeEntry(pattern, parallelGroupId, blockId, repeatCount, isLeadIn));
    });
}

function getPracticeLeadInLabelTypes() {
    const allowedLabelsByStartMode = {
        afterCall: ['Call'],
        afterIntro: ['Intro'],
        afterCallIntro: ['Call', 'Intro']
    };
    return allowedLabelsByStartMode[practiceState.accompanimentStart] || [];
}

function getPracticeLeadInPatterns() {
    const allowedLabels = getPracticeLeadInLabelTypes();
    const selectedLeadInPatterns = (practiceState.soloPatternIds || [])
        .map(function (patternId) {
            return findPatternById(patternId);
        })
        .filter(function (pattern) {
            return pattern && allowedLabels.indexOf(pattern.labelType) !== -1;
        });
    const selectedLeadInIds = selectedLeadInPatterns.map(function (pattern) {
        return pattern.id;
    });
    const sheetLeadInPatterns = timelineState.sourcePatterns.filter(function (pattern) {
        return pattern &&
            allowedLabels.indexOf(pattern.labelType) !== -1 &&
            selectedLeadInIds.indexOf(pattern.id) === -1;
    });
    return selectedLeadInPatterns.concat(sheetLeadInPatterns);
}

function isPracticePatternInLeadIn(pattern, leadInPatternIds) {
    return pattern && Array.isArray(leadInPatternIds) && leadInPatternIds.indexOf(pattern.id) !== -1;
}

function shouldPausePracticeAccompanimentForPattern(pattern) {
    return Boolean(practiceState.pauseAccompanimentForLeadInPatterns) &&
        pattern &&
        (pattern.labelType === 'Call' || pattern.labelType === 'Intro');
}

function buildPracticeEntries(options) {
    const buildOptions = options || {};
    const repeatCycles = buildOptions.singleCycle
        ? 1
        : practiceState.repeatCount;
    const accompanimentPatterns = getPracticePatternsByIds(practiceState.accompanimentPatternIds);
    const soloPatterns = getPracticePatternsByIds(practiceState.soloPatternIds);
    const startsAfterLeadIn = practiceState.accompanimentStart !== 'immediate';
    const leadInPatterns = startsAfterLeadIn ? getPracticeLeadInPatterns() : [];
    const leadInPatternIds = leadInPatterns.map(function (pattern) {
        return pattern.id;
    });
    const cycleSoloPatterns = soloPatterns.filter(function (pattern) {
        return !isPracticePatternInLeadIn(pattern, leadInPatternIds);
    });
    const entries = [];
    const initialLoopsWithoutSolo = startsAfterLeadIn && cycleSoloPatterns.length > 0
        ? Math.max(0, practiceState.loopsWithoutSolo - 1)
        : practiceState.loopsWithoutSolo;
    const trailingLoopsWithoutSolo = startsAfterLeadIn && cycleSoloPatterns.length > 0
        ? practiceState.loopsWithoutSolo - initialLoopsWithoutSolo
        : 0;
    let blockIndex = 1;

    if (startsAfterLeadIn) {
        leadInPatterns.forEach(function (leadInPattern) {
            addPracticeParallelGroup(entries, [leadInPattern], blockIndex, 1, true);
            blockIndex += 1;
        });
    }

    for (let cycleIndex = 0; cycleIndex < repeatCycles; cycleIndex += 1) {
        const loopsWithoutSolo = cycleIndex === 0 ? initialLoopsWithoutSolo : practiceState.loopsWithoutSolo;
        if (loopsWithoutSolo > 0) {
            addPracticeParallelGroup(entries, accompanimentPatterns, blockIndex, loopsWithoutSolo);
            blockIndex += 1;
        }

        if (cycleSoloPatterns.length === 0) {
            continue;
        }

        cycleSoloPatterns.forEach(function (soloPattern, soloPatternIndex) {
            const groupPatterns = shouldPausePracticeAccompanimentForPattern(soloPattern)
                ? [soloPattern]
                : accompanimentPatterns.concat([soloPattern]);
            addPracticeParallelGroup(entries, groupPatterns, blockIndex, getPracticeSoloPatternRepeatCount(soloPattern.id));
            blockIndex += 1;
            if (practiceState.accompanimentBetweenPatterns && soloPatternIndex < cycleSoloPatterns.length - 1) {
                if (practiceState.loopsWithoutSolo > 0) {
                    addPracticeParallelGroup(entries, accompanimentPatterns, blockIndex, practiceState.loopsWithoutSolo);
                    blockIndex += 1;
                }
            }
        });

        if (trailingLoopsWithoutSolo > 0) {
            addPracticeParallelGroup(entries, accompanimentPatterns, blockIndex, trailingLoopsWithoutSolo);
            blockIndex += 1;
        }
    }

    return entries;
}

function buildPracticeBlocksFromEntries(entries) {
    const blocks = [];
    const blockById = {};
    (Array.isArray(entries) ? entries : []).forEach(function (entry) {
        const blockId = String(entry.blockId || entry.parallelGroupId || '');
        if (!blockId) {
            return;
        }
        if (!blockById[blockId]) {
            blockById[blockId] = {
                id: blockId,
                repeatCount: normalizePracticeCount(entry.repeatCount, 1, 1, 32),
                isLeadIn: Boolean(entry.isLeadIn),
                entries: []
            };
            blocks.push(blockById[blockId]);
        }
        blockById[blockId].entries.push({
            patternId: entry.patternId,
            patternSourceKey: entry.patternSourceKey,
            handMode: entry.handMode || '',
            swingFactor: entry.swingFactor === null || entry.swingFactor === undefined
                ? null
                : entry.swingFactor,
            isPracticeTarget: Boolean(entry.isPracticeTarget),
            repeatCount: normalizePracticeCount(entry.repeatCount, 1, 1, 32),
            isLeadIn: Boolean(entry.isLeadIn),
            targetInstruments: Array.isArray(entry.targetInstruments) ? entry.targetInstruments.slice() : []
        });
    });
    return blocks;
}

function createEmptyPracticeTrackNotes() {
    return practiceTrackInstrumentNames.reduce(function (trackNotes, instrumentName) {
        trackNotes[instrumentName] = [];
        return trackNotes;
    }, {});
}

function createEmptyPracticeTrackHandModes() {
    return practiceTrackInstrumentNames.reduce(function (trackHandModes, instrumentName) {
        trackHandModes[instrumentName] = '';
        return trackHandModes;
    }, {});
}

function createEmptyPracticeTrackStepMap() {
    return practiceTrackInstrumentNames.reduce(function (trackMap, instrumentName) {
        trackMap[instrumentName] = null;
        return trackMap;
    }, {});
}

function createEmptyPracticeTrackFlags() {
    return practiceTrackInstrumentNames.reduce(function (trackFlags, instrumentName) {
        trackFlags[instrumentName] = [];
        return trackFlags;
    }, {});
}

function mergePracticeFlagsIntoTrackAtOffset(targetFlags, sourceFlags, offset) {
    const mergedFlags = Array.isArray(targetFlags) ? targetFlags.slice() : [];
    const safeSourceFlags = Array.isArray(sourceFlags) ? sourceFlags : [];
    const safeOffset = Math.max(0, Number(offset) || 0);
    safeSourceFlags.forEach(function (flagValue, flagIndex) {
        const targetIndex = safeOffset + flagIndex;
        while (mergedFlags.length <= targetIndex) {
            mergedFlags.push(false);
        }
        if (flagValue) {
            mergedFlags[targetIndex] = true;
        }
    });
    return mergedFlags;
}

function expandPracticeTargetFlags(targetFlags, paddingSteps) {
    const safeTargetFlags = Array.isArray(targetFlags) ? targetFlags : [];
    const safePaddingSteps = Math.max(0, Number(paddingSteps) || 0);
    if (safePaddingSteps === 0 || safeTargetFlags.length === 0) {
        return safeTargetFlags.slice();
    }

    const expandedFlags = safeTargetFlags.slice();
    safeTargetFlags.forEach(function (isTarget, stepIndex) {
        if (!isTarget) {
            return;
        }
        for (let offset = 1; offset <= safePaddingSteps; offset += 1) {
            if (stepIndex - offset >= 0) {
                expandedFlags[stepIndex - offset] = true;
            }
            if (stepIndex + offset < expandedFlags.length) {
                expandedFlags[stepIndex + offset] = true;
            }
        }
    });
    return expandedFlags;
}

function normalizePracticeTargetInstrument(instrumentName) {
    const instrumentMap = {
        Djembe_1: ['Djembe_1'],
        Djembe_2: ['Djembe_2'],
        Djembe_3: ['Djembe_3'],
        'Djembe 1': ['Djembe_1'],
        'Djembe 2': ['Djembe_2'],
        'Djembe 3': ['Djembe_3'],
        Kenkeni: ['Kenkeni'],
        Sangban: ['Sangban'],
        Doundoun: ['Doundoun'],
        Dununba: ['Doundoun'],
        Dreierbass: ['Dreierbass'],
        'Bässe': ['Kenkeni', 'Sangban', 'Doundoun']
    };
    return instrumentMap[instrumentName] || [];
}

function normalizePracticeTargetInstruments(targetInstruments) {
    return (Array.isArray(targetInstruments) ? targetInstruments : [])
        .reduce(function (normalizedTargets, targetName) {
            normalizePracticeTargetInstrument(targetName).forEach(function (mappedTarget) {
                if (normalizedTargets.indexOf(mappedTarget) === -1) {
                    normalizedTargets.push(mappedTarget);
                }
            });
            return normalizedTargets;
        }, []);
}

function flattenPracticePatternNotes(pattern) {
    return expandPracticePatternBars(pattern).reduce(function (allNotes, bar) {
        const notes = getPracticeBarPlayableNotes(bar);
        if (notes.length > 0) {
            return allNotes.concat(notes);
        }
        return allNotes;
    }, []);
}

function getPracticeBarShortLength(bar) {
    const notes = bar && Array.isArray(bar.notes) ? bar.notes : [];
    const controls = bar && Array.isArray(bar.controls) ? bar.controls : [];
    const shortBarControl = controls
        .filter(function (control) {
            return control && control.type === 'shortbar';
        })
        .sort(function (controlA, controlB) {
            return Number(controlA.stepIndex) - Number(controlB.stepIndex);
        })[0];

    if (!shortBarControl || notes.length === 0) {
        return notes.length;
    }

    const shortStep = Math.round(Number(shortBarControl.stepIndex));
    if (!Number.isFinite(shortStep)) {
        return notes.length;
    }
    return Math.max(0, Math.min(notes.length, shortStep));
}

function getPracticeBarPlayableNotes(bar) {
    const notes = bar && Array.isArray(bar.notes) ? bar.notes : [];
    return notes.slice(0, getPracticeBarShortLength(bar));
}

function practiceBarHasShortBar(bar) {
    return getPracticeBarShortLength(bar) < (bar && Array.isArray(bar.notes) ? bar.notes.length : 0);
}

function isPlayablePracticeNote(noteValue) {
    return noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '';
}

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

function isPracticeContinuationMarker(markerValue) {
    return markerValue === 'continue' || markerValue === 'loop';
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

function expandPracticeBarsWithRepeats(bars, repeatRangesToApply, startBarIndex, endBarIndex) {
    const expandedBars = [];
    let currentBarIndex = startBarIndex;
    while (currentBarIndex <= endBarIndex) {
        const matchingRange = repeatRangesToApply
            .filter(function (repeatRange) {
                return !isPracticeContinuationMarker(repeatRange.count) &&
                    repeatRange.startBar === currentBarIndex &&
                    repeatRange.endBar <= endBarIndex;
            })
            .sort(function (rangeA, rangeB) {
                return rangeB.endBar - rangeA.endBar;
            })[0];

        if (!matchingRange) {
            expandedBars.push(bars[currentBarIndex - 1]);
            currentBarIndex += 1;
            continue;
        }

        const repeatedSegment = expandPracticeBarsWithRepeats(
            bars,
            repeatRangesToApply.filter(function (repeatRange) {
                return repeatRange.startBar >= matchingRange.startBar &&
                    repeatRange.endBar <= matchingRange.endBar &&
                    !(repeatRange.startBar === matchingRange.startBar && repeatRange.endBar === matchingRange.endBar);
            }),
            matchingRange.startBar,
            matchingRange.endBar
        );
        expandedBars.push.apply(expandedBars, repeatedSegment);
        const repeatCount = Number(matchingRange.count) || 0;
        for (let repeatIndex = 0; repeatIndex < repeatCount; repeatIndex += 1) {
            expandedBars.push.apply(expandedBars, repeatedSegment);
        }
        currentBarIndex = matchingRange.endBar + 1;
    }
    return expandedBars;
}

function expandPracticePatternBars(pattern) {
    const bars = pattern && Array.isArray(pattern.bars) ? pattern.bars : [];
    if (bars.length === 0) {
        return [];
    }
    const repeatRanges = buildPracticeRepeatRangesFromBars(bars);
    if (repeatRanges.length === 0) {
        return bars;
    }
    return expandPracticeBarsWithRepeats(bars, repeatRanges, 1, bars.length);
}

function getPracticePatternControlStep(pattern, controlType) {
    const bars = expandPracticePatternBars(pattern);
    let stepOffset = 0;
    let matchedControlStep = null;

    for (let barIndex = 0; barIndex < bars.length; barIndex += 1) {
        const bar = bars[barIndex];
        const notes = getPracticeBarPlayableNotes(bar);
        const controls = bar && Array.isArray(bar.controls) ? bar.controls : [];
        const matchingControl = controls
            .filter(function (control) {
                return control && control.type === controlType;
            })
            .sort(function (controlA, controlB) {
                return Number(controlA.stepIndex) - Number(controlB.stepIndex);
            })[0];

        if (matchingControl) {
            const controlStep = Math.max(0, Number(matchingControl.stepIndex) || 0);
            matchedControlStep = stepOffset + Math.min(controlStep, notes.length);
            if (controlType !== 'out') {
                return matchedControlStep;
            }
        }

        stepOffset += notes.length;
    }

    return matchedControlStep;
}

function practicePatternEndsWithShortBar(pattern) {
    const bars = expandPracticePatternBars(pattern);
    if (bars.length === 0) {
        return false;
    }
    return practiceBarHasShortBar(bars[bars.length - 1]);
}

function getPracticePatternInStep(pattern) {
    return getPracticePatternControlStep(pattern, 'in');
}

function getPracticePatternOutStep(pattern) {
    return getPracticePatternControlStep(pattern, 'out');
}

function getPracticeStepsPerBar() {
    if (rhythm === 'tenaer') {
        return 24;
    }
    if (rhythm === 'neunaer') {
        return 18;
    }
    return 32;
}

function buildPracticePickupNotes(notes, inStep) {
    const safeNotes = Array.isArray(notes) ? notes : [];
    if (safeNotes.length === 0 || inStep === null || inStep === undefined) {
        return [];
    }

    const stepsPerBar = getPracticeStepsPerBar();
    const safeInStep = Math.max(0, Math.min(safeNotes.length - 1, Number(inStep) || 0));
    const pickupStartStep = stepsPerBar > 0
        ? Math.floor(safeInStep / stepsPerBar) * stepsPerBar
        : 0;
    const pickupLength = Math.max(stepsPerBar, safeNotes.length - pickupStartStep);
    const pickupNotes = Array(pickupLength).fill('f');
    for (let sourceStep = safeInStep; sourceStep < safeNotes.length; sourceStep += 1) {
        pickupNotes[sourceStep - pickupStartStep] = safeNotes[sourceStep] || 'f';
    }
    return pickupNotes;
}

function mergePracticeNotesIntoTrack(targetNotes, sourceNotes) {
    const mergedNotes = Array.isArray(targetNotes) ? targetNotes.slice() : [];
    const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes : [];
    safeSourceNotes.forEach(function (noteValue, noteIndex) {
        while (mergedNotes.length <= noteIndex) {
            mergedNotes.push('f');
        }
        if (isPlayablePracticeNote(noteValue)) {
            mergedNotes[noteIndex] = noteValue;
        }
    });
    return mergedNotes;
}

function mergePracticeNotesIntoTrackAtOffset(targetNotes, sourceNotes, offset) {
    const mergedNotes = Array.isArray(targetNotes) ? targetNotes.slice() : [];
    const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes : [];
    const safeOffset = Math.max(0, Number(offset) || 0);
    safeSourceNotes.forEach(function (noteValue, noteIndex) {
        const targetIndex = safeOffset + noteIndex;
        while (mergedNotes.length <= targetIndex) {
            mergedNotes.push('f');
        }
        if (isPlayablePracticeNote(noteValue)) {
            mergedNotes[targetIndex] = noteValue;
        }
    });
    return mergedNotes;
}

function createPracticeSection(block, blockIndex, sectionSuffix) {
    const safeSuffix = sectionSuffix || '';
    return {
            id: block.id,
            label: 'Begleitung',
            labelName: '',
            runtimeKey: 'practice-js::' + block.id + safeSuffix + '::' + blockIndex,
            swingFactor: null,
            trackNotes: createEmptyPracticeTrackNotes(),
            trackHandModes: createEmptyPracticeTrackHandModes(),
            trackTargetFlags: createEmptyPracticeTrackFlags(),
            finalRepeatOutSteps: createEmptyPracticeTrackStepMap(),
            forceFinalOutAtSectionEnd: false,
            barStartSteps: [],
            practiceTargetInstruments: []
    };
}

function clonePracticeSection(section, sectionSuffix) {
    const safeSuffix = sectionSuffix || '';
    const clonedSection = {
        id: section.id + safeSuffix,
        label: section.label,
        labelName: section.labelName,
        runtimeKey: section.runtimeKey + safeSuffix,
        swingFactor: section.swingFactor,
        trackNotes: createEmptyPracticeTrackNotes(),
        trackHandModes: createEmptyPracticeTrackHandModes(),
        trackTargetFlags: createEmptyPracticeTrackFlags(),
        finalRepeatOutSteps: createEmptyPracticeTrackStepMap(),
        forceFinalOutAtSectionEnd: Boolean(section.forceFinalOutAtSectionEnd),
        barStartSteps: Array.isArray(section.barStartSteps) ? section.barStartSteps.slice() : [],
        practiceTargetInstruments: Array.isArray(section.practiceTargetInstruments)
            ? section.practiceTargetInstruments.slice()
            : []
    };
    clonedSection.repeatCount = 1;
    clonedSection.isLeadIn = Boolean(section.isLeadIn);
    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        clonedSection.trackNotes[instrumentName] = Array.isArray(section.trackNotes[instrumentName])
            ? section.trackNotes[instrumentName].slice()
            : [];
        clonedSection.trackHandModes[instrumentName] = section.trackHandModes[instrumentName] || '';
        clonedSection.trackTargetFlags[instrumentName] = section.trackTargetFlags &&
            Array.isArray(section.trackTargetFlags[instrumentName])
            ? section.trackTargetFlags[instrumentName].slice()
            : [];
        clonedSection.finalRepeatOutSteps[instrumentName] = section.finalRepeatOutSteps[instrumentName];
    });
    return clonedSection;
}

function getPracticeSectionLength(section) {
    return Math.max.apply(null, practiceTrackInstrumentNames.map(function (instrumentName) {
        return section && section.trackNotes && Array.isArray(section.trackNotes[instrumentName])
            ? section.trackNotes[instrumentName].length
            : 0;
    }).concat(0));
}

function mergePracticeBarStartSteps(targetSteps, sourceSteps) {
    const mergedSteps = Array.isArray(targetSteps) ? targetSteps.slice() : [];
    (Array.isArray(sourceSteps) ? sourceSteps : []).forEach(function (stepValue) {
        const step = Math.max(0, Math.round(Number(stepValue) || 0));
        if (mergedSteps.indexOf(step) === -1) {
            mergedSteps.push(step);
        }
    });
    mergedSteps.sort(function (stepA, stepB) {
        return stepA - stepB;
    });
    return mergedSteps;
}

function getPracticePatternBarStartSteps(pattern) {
    const bars = expandPracticePatternBars(pattern);
    const barStartSteps = [];
    let stepOffset = 0;
    bars.forEach(function (bar) {
        barStartSteps.push(stepOffset);
        stepOffset += getPracticeBarPlayableNotes(bar).length;
    });
    return barStartSteps;
}

function sectionHasPracticeNotes(section) {
    return practiceTrackInstrumentNames.some(function (instrumentName) {
        return Array.isArray(section.trackNotes[instrumentName]) && section.trackNotes[instrumentName].length > 0;
    });
}

function mergePracticePickupIntoHostSection(hostSection, pickupSection) {
    const hostLength = getPracticeSectionLength(hostSection);
    const stepsPerBar = getPracticeStepsPerBar();
    const pickupLength = getPracticeSectionLength(pickupSection);
    const pickupOffset = Math.max(0, hostLength - Math.max(stepsPerBar, pickupLength));

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        const pickupNotes = pickupSection.trackNotes[instrumentName];
        if (!Array.isArray(pickupNotes) || pickupNotes.length === 0) {
            return;
        }

        hostSection.trackNotes[instrumentName] = mergePracticeNotesIntoTrackAtOffset(
            hostSection.trackNotes[instrumentName],
            pickupNotes,
            pickupOffset
        );
        if (pickupSection.trackHandModes[instrumentName]) {
            hostSection.trackHandModes[instrumentName] = pickupSection.trackHandModes[instrumentName];
        }
        if (pickupSection.trackTargetFlags && Array.isArray(pickupSection.trackTargetFlags[instrumentName])) {
            hostSection.trackTargetFlags[instrumentName] = mergePracticeFlagsIntoTrackAtOffset(
                hostSection.trackTargetFlags[instrumentName],
                pickupSection.trackTargetFlags[instrumentName],
                pickupOffset
            );
        }
    });
}

function clearPracticeSectionOutSteps(section) {
    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        section.finalRepeatOutSteps[instrumentName] = null;
    });
}

function getPracticeSectionFinalOutEndStep(section) {
    const outSteps = section && section.finalRepeatOutSteps ? section.finalRepeatOutSteps : {};
    const sectionLength = getPracticeSectionLength(section);
    const safeOutSteps = practiceTrackInstrumentNames
        .map(function (instrumentName) {
            const rawOutStep = outSteps[instrumentName];
            if (rawOutStep === null || rawOutStep === undefined || rawOutStep === '') {
                return null;
            }
            const numericOutStep = Number(rawOutStep);
            return Number.isFinite(numericOutStep) && numericOutStep >= 0
                ? Math.round(numericOutStep)
                : null;
        })
        .filter(function (outStep) {
            return outStep !== null;
        });

    if (safeOutSteps.length === 0 || sectionLength <= 0) {
        return null;
    }
    const outEndStep = Math.max.apply(null, safeOutSteps) + 1;
    const stepsPerBar = getPracticeStepsPerBar();
    const sectionEndStep = stepsPerBar > 0
        ? Math.ceil(outEndStep / stepsPerBar) * stepsPerBar
        : outEndStep;
    return Math.max(1, Math.min(sectionLength, sectionEndStep));
}

function trimPracticeSectionToFinalOut(section) {
    const endStep = getPracticeSectionFinalOutEndStep(section);
    if (endStep === null) {
        return;
    }

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        if (Array.isArray(section.trackNotes[instrumentName])) {
            section.trackNotes[instrumentName] = section.trackNotes[instrumentName].slice(0, endStep);
        }
        if (section.trackTargetFlags && Array.isArray(section.trackTargetFlags[instrumentName])) {
            section.trackTargetFlags[instrumentName] = section.trackTargetFlags[instrumentName].slice(0, endStep);
        }
    });
}

function normalizePracticeSectionTrackLoops(section) {
    const sectionLength = getPracticeSectionLength(section);
    if (sectionLength <= 0) {
        return;
    }

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        const notes = section.trackNotes[instrumentName];
        const sourceLength = Array.isArray(notes) ? notes.length : 0;
        if (!Array.isArray(notes) || notes.length === 0 || notes.length >= sectionLength) {
            return;
        }

        const loopedNotes = [];
        for (let stepIndex = 0; stepIndex < sectionLength; stepIndex += 1) {
            loopedNotes.push(notes[stepIndex % notes.length] || 'f');
        }
        section.trackNotes[instrumentName] = loopedNotes;

        if (section.finalRepeatOutSteps && section.finalRepeatOutSteps[instrumentName] !== null &&
                section.finalRepeatOutSteps[instrumentName] !== undefined) {
            const rawOutStep = Number(section.finalRepeatOutSteps[instrumentName]);
            if (Number.isFinite(rawOutStep) && rawOutStep >= 0 && sourceLength > 0) {
                const lastLoopStart = Math.floor((sectionLength - 1) / sourceLength) * sourceLength;
                const outStepInSource = Math.max(0, Math.min(
                    sourceLength - 1,
                    Math.round(rawOutStep) >= sourceLength
                        ? sourceLength - 1
                        : Math.round(rawOutStep)
                ));
                section.finalRepeatOutSteps[instrumentName] = Math.min(
                    sectionLength - 1,
                    lastLoopStart + outStepInSource
                );
            }
        }

        if (section.trackTargetFlags && Array.isArray(section.trackTargetFlags[instrumentName])) {
            const flags = section.trackTargetFlags[instrumentName];
            const loopedFlags = [];
            for (let stepIndex = 0; stepIndex < sectionLength; stepIndex += 1) {
                loopedFlags.push(Boolean(flags[stepIndex % Math.max(1, flags.length)]));
            }
            section.trackTargetFlags[instrumentName] = loopedFlags;
        }
    });
}

function loopPracticeArrayToLength(values, targetLength, fallbackValue) {
    const safeValues = Array.isArray(values) ? values : [];
    const safeTargetLength = Math.max(0, Number(targetLength) || 0);
    if (safeTargetLength <= 0 || safeValues.length === 0) {
        return [];
    }
    if (safeValues.length >= safeTargetLength) {
        return safeValues.slice(0, safeTargetLength);
    }

    const loopedValues = [];
    for (let stepIndex = 0; stepIndex < safeTargetLength; stepIndex += 1) {
        const sourceValue = safeValues[stepIndex % safeValues.length];
        loopedValues.push(sourceValue === undefined ? fallbackValue : sourceValue);
    }
    return loopedValues;
}

function appendPracticeSectionWithFinalOut(sections, section) {
    if (getPracticeSectionFinalOutEndStep(section) !== null && section.repeatCount > 1) {
        const repeatedSection = clonePracticeSection(section, '::before-final-out');
        repeatedSection.repeatCount = section.repeatCount - 1;
        clearPracticeSectionOutSteps(repeatedSection);
        normalizePracticeSectionTrackLoops(repeatedSection);
        sections.push(repeatedSection);
        section.repeatCount = 1;
    }
    normalizePracticeSectionTrackLoops(section);
    trimPracticeSectionToFinalOut(section);
    sections.push(section);
}

function appendPracticeSectionWithPickup(sections, section, pickupSection, hasPickup) {
    if (!hasPickup || !sectionHasPracticeNotes(pickupSection)) {
        appendPracticeSectionWithFinalOut(sections, section);
        return null;
    }

    const reversedSections = sections.slice().reverse();
    let hostIndex = reversedSections.findIndex(function (previousSection) {
        return previousSection && !previousSection.isLeadIn && sectionHasPracticeNotes(previousSection);
    });

    if (hostIndex === -1) {
        hostIndex = reversedSections.findIndex(function (previousSection) {
            return previousSection && sectionHasPracticeNotes(previousSection);
        });
    }

    if (hostIndex === -1) {
        sections.push(pickupSection);
        appendPracticeSectionWithFinalOut(sections, section);
        return pickupSection;
    }

    const realHostIndex = sections.length - 1 - hostIndex;
    let hostSection = sections[realHostIndex];
    const pickupWasPlacedInLeadIn = Boolean(hostSection.isLeadIn);
    if (hostSection.repeatCount > 1) {
        hostSection.repeatCount -= 1;
        hostSection = clonePracticeSection(hostSection, '::pickup-host');
        hostSection.labelName = hostSection.labelName ? hostSection.labelName + ' Auftakt' : 'Auftakt';
        sections.push(hostSection);
    }

    mergePracticePickupIntoHostSection(hostSection, pickupSection);
    if (section.repeatCount > 1) {
        const repeatedSection = clonePracticeSection(section, '::with-pickup');
        repeatedSection.repeatCount = section.repeatCount - 1;
        clearPracticeSectionOutSteps(repeatedSection);
        mergePracticePickupIntoHostSection(repeatedSection, pickupSection);
        normalizePracticeSectionTrackLoops(repeatedSection);
        section.repeatCount = 1;
        sections.push(repeatedSection);
    }
    normalizePracticeSectionTrackLoops(section);
    trimPracticeSectionToFinalOut(section);
    sections.push(section);
    return pickupWasPlacedInLeadIn ? pickupSection : null;
}

function practiceBlockHasAccompanimentWithOut(block) {
    return Boolean(block && Array.isArray(block.entries) && block.entries.some(function (entry) {
        const pattern = findPatternById(entry.patternId);
        return pattern && pattern.labelType === 'Begleitung' && getPracticePatternOutStep(pattern) !== null;
    }));
}

function practiceBlockPausesAccompaniment(block) {
    return Boolean(block && Array.isArray(block.entries) && block.entries.some(function (entry) {
        return shouldPausePracticeAccompanimentForPattern(findPatternById(entry.patternId));
    }));
}

function practiceSectionPausesAccompaniment(section) {
    return Boolean(practiceState.pauseAccompanimentForLeadInPatterns) &&
        section &&
        (section.label === 'Call' || section.label === 'Intro');
}

function practiceSectionHasAccompanimentOut(section) {
    if (!section || !section.finalRepeatOutSteps) {
        return false;
    }
    return practiceTrackInstrumentNames.some(function (instrumentName) {
        const outStep = section.finalRepeatOutSteps[instrumentName];
        return outStep !== null && outStep !== undefined && outStep !== '';
    });
}

function mutePracticeSectionAfterFinalOut(section) {
    if (!section || !section.finalRepeatOutSteps) {
        return;
    }

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        const outStep = section.finalRepeatOutSteps[instrumentName];
        if (outStep === null || outStep === undefined || outStep === '') {
            return;
        }
        const safeOutStep = Math.max(0, Math.round(Number(outStep) || 0));
        const notes = section.trackNotes && Array.isArray(section.trackNotes[instrumentName])
            ? section.trackNotes[instrumentName]
            : [];
        for (let stepIndex = safeOutStep + 1; stepIndex < notes.length; stepIndex += 1) {
            notes[stepIndex] = 'f';
        }
        if (section.trackTargetFlags && Array.isArray(section.trackTargetFlags[instrumentName])) {
            for (let stepIndex = safeOutStep + 1; stepIndex < section.trackTargetFlags[instrumentName].length; stepIndex += 1) {
                section.trackTargetFlags[instrumentName][stepIndex] = false;
            }
        }
    });
}

function markPracticeSectionsBeforePausedAccompaniment(sections) {
    const safeSections = Array.isArray(sections) ? sections : [];
    if (!practiceState.pauseAccompanimentForLeadInPatterns || safeSections.length === 0) {
        return;
    }

    const hasOuterPracticeLoop = practiceState.repeatCount > 1 || practiceState.timerMinutes > 0;
    const firstLoopSection = safeSections.find(function (section) {
        return section && !section.isLeadIn && sectionHasPracticeNotes(section);
    });

    safeSections.forEach(function (section, sectionIndex) {
        if (!practiceSectionHasAccompanimentOut(section)) {
            return;
        }
        const nextSection = safeSections[sectionIndex + 1] || (hasOuterPracticeLoop ? firstLoopSection : null);
        if (practiceSectionPausesAccompaniment(nextSection)) {
            section.forceFinalOutAtSectionEnd = true;
            mutePracticeSectionAfterFinalOut(section);
        }
    });
}

function practiceBlockEndsWithShortBar(block) {
    return Boolean(block && Array.isArray(block.entries) && block.entries.some(function (entry) {
        return practicePatternEndsWithShortBar(findPatternById(entry.patternId));
    }));
}

function buildPracticeSectionsFromEntries(entries) {
    const sections = [];
    let loopStartPickupSection = null;
    const blocks = buildPracticeBlocksFromEntries(entries);
    const hasOuterPracticeLoop = practiceState.repeatCount > 1 || practiceState.timerMinutes > 0;
    const firstRepeatedBlock = blocks.find(function (block) {
        return block && !block.isLeadIn;
    });
    let previousBlockEndedWithShortBar = false;
    blocks.forEach(function (block, blockIndex) {
        const nextBlock = blocks[blockIndex + 1] || (hasOuterPracticeLoop ? firstRepeatedBlock : null);
        const section = createPracticeSection(block, blockIndex, '');
        section.repeatCount = normalizePracticeCount(block.repeatCount, 1, 1, 32);
        section.isLeadIn = Boolean(block.isLeadIn);
        section.forceFinalOutAtSectionEnd = practiceBlockHasAccompanimentWithOut(block) &&
            practiceBlockPausesAccompaniment(nextBlock);
        const pickupSection = createPracticeSection(block, blockIndex, '::pickup');
        pickupSection.id = block.id + '-pickup';
        pickupSection.repeatCount = 1;
        pickupSection.isLeadIn = true;
        const labels = [];
        const labelNames = [];
        const blockHasPickup = !section.isLeadIn && !previousBlockEndedWithShortBar && block.entries.some(function (entry) {
            const pattern = findPatternById(entry.patternId);
            return pattern && getPracticePatternInStep(pattern) !== null;
        });

        block.entries.forEach(function (entry) {
            const pattern = findPatternById(entry.patternId);
            if (!pattern) {
                return;
            }

            const rawPatternNotes = flattenPracticePatternNotes(pattern);
            const patternInStep = getPracticePatternInStep(pattern);
            const patternNotes = rawPatternNotes.slice();
            const patternOutStep = getPracticePatternOutStep(pattern);
            const patternBarStartSteps = getPracticePatternBarStartSteps(pattern);
            const targetInstruments = normalizePracticeTargetInstruments(entry.targetInstruments);
            const label = pattern.labelType || pattern.label || '';
            const labelName = pattern.labelName || pattern.name || label;

            if (label && labels.indexOf(label) === -1) {
                labels.push(label);
            }
            if (labelName && labelNames.indexOf(labelName) === -1) {
                labelNames.push(labelName);
            }

            if (blockHasPickup && patternInStep !== null) {
                const pickupNotes = buildPracticePickupNotes(rawPatternNotes, patternInStep);
                targetInstruments.forEach(function (instrumentName) {
                    pickupSection.trackNotes[instrumentName] = mergePracticeNotesIntoTrack(
                        pickupSection.trackNotes[instrumentName],
                        pickupNotes
                    );
                    if (entry.isPracticeTarget) {
                        pickupSection.trackTargetFlags[instrumentName] = mergePracticeFlagsIntoTrackAtOffset(
                            pickupSection.trackTargetFlags[instrumentName],
                            pickupNotes.map(function (noteValue) {
                                return isPlayablePracticeNote(noteValue);
                            }),
                            0
                        );
                    }
                    if (instrumentName.indexOf('Djembe_') === 0) {
                        pickupSection.trackHandModes[instrumentName] = entry.handMode || '';
                    }
                });
                if (pickupSection.swingFactor === null && entry.swingFactor !== null && entry.swingFactor !== undefined) {
                    pickupSection.swingFactor = entry.swingFactor;
                }
            }

            targetInstruments.forEach(function (instrumentName) {
                section.trackNotes[instrumentName] = mergePracticeNotesIntoTrack(
                    section.trackNotes[instrumentName],
                    patternNotes
                );
                section.barStartSteps = mergePracticeBarStartSteps(section.barStartSteps, patternBarStartSteps);
                if (entry.isPracticeTarget && section.practiceTargetInstruments.indexOf(instrumentName) === -1) {
                    section.practiceTargetInstruments.push(instrumentName);
                }
                if (instrumentName.indexOf('Djembe_') === 0) {
                    section.trackHandModes[instrumentName] = entry.handMode || '';
                }
                if (patternOutStep !== null && patternOutStep !== undefined) {
                    section.finalRepeatOutSteps[instrumentName] = patternOutStep;
                }
            });

            if (section.swingFactor === null && entry.swingFactor !== null && entry.swingFactor !== undefined) {
                section.swingFactor = entry.swingFactor;
            }
        });

        section.label = labels.indexOf('Begleitung') !== -1 ? 'Begleitung' : (labels[0] || 'Begleitung');
        section.labelName = labelNames.join(' + ') || section.label;
        pickupSection.label = section.label;
        pickupSection.labelName = section.labelName ? section.labelName + ' Auftakt' : 'Auftakt';
        const loopPickupSection = appendPracticeSectionWithPickup(sections, section, pickupSection, blockHasPickup);
        if (!loopStartPickupSection && loopPickupSection) {
            loopStartPickupSection = loopPickupSection;
        }
        previousBlockEndedWithShortBar = practiceBlockEndsWithShortBar(block);
    });

    if (loopStartPickupSection && (practiceState.repeatCount > 1 || practiceState.timerMinutes > 0)) {
        for (let sectionIndex = sections.length - 1; sectionIndex >= 0; sectionIndex -= 1) {
            if (!sections[sectionIndex].isLeadIn && sectionHasPracticeNotes(sections[sectionIndex])) {
                mergePracticePickupIntoHostSection(sections[sectionIndex], loopStartPickupSection);
                break;
            }
        }
    }

    markPracticeSectionsBeforePausedAccompaniment(sections);

    return sections.filter(function (section) {
        return sectionHasPracticeNotes(section);
    });
}

function notifyPracticeHandModeChanged() {
    if (typeof updateTimelineMetadataNode === 'function') {
        updateTimelineMetadataNode();
    }

    if (typeof sendPracticeAudioMessage !== 'function') {
        return;
    }

    try {
        const entries = buildPracticeEntries({ singleCycle: true });
        sendPracticeAudioMessage({
            type: 'barabeat-practice-hand-modes-update',
            sections: buildPracticeSectionsFromEntries(entries)
        });
    } catch (error) {
        console.warn('Handsatz konnte nicht live aktualisiert werden', error);
    }
}

function notifyPracticeInstrumentVolumesChanged() {
    if (typeof updateTimelineMetadataNode === 'function') {
        updateTimelineMetadataNode();
    }

    const volumeMessage = {
        type: 'barabeat-practice-instrument-volumes',
        volumes: normalizePracticeInstrumentVolumes(practiceState.instrumentVolumes)
    };

    if (typeof sendTimelineAudioMessage === 'function') {
        sendTimelineAudioMessage(volumeMessage);
    }

    if (typeof sendPracticeAudioMessage !== 'function') {
        return;
    }

    sendPracticeAudioMessage(volumeMessage);
}

function buildPracticePlayerPayload() {
    if (!Array.isArray(timelineState.sourcePatterns) || timelineState.sourcePatterns.length === 0) {
        throw new Error('Es wurden noch keine Pattern aus dem Notenblatt gelesen.');
    }
    if (practiceState.accompanimentPatternIds.length === 0 && practiceState.soloPatternIds.length === 0) {
        throw new Error('Bitte mindestens ein Begleit- oder Übungsteil-Pattern für den Übungsmodus auswählen.');
    }

    const entries = buildPracticeEntries({ singleCycle: true });
    if (entries.length === 0) {
        throw new Error('Aus den Übungseinstellungen konnte keine Abspielfolge erzeugt werden.');
    }

    const payload = buildTimelinePlayerPayload(timelineState.sourcePatterns, entries);
    if (payload[0]) {
        payload[0].PracticeMode = true;
        payload[0].PracticeBlocks = buildPracticeBlocksFromEntries(entries);
        payload[0].PracticeSections = buildPracticeSectionsFromEntries(entries);
        payload[0].PracticeH2HRestMute = Boolean(practiceState.h2hRestMute);
        payload[0].PracticeInstrumentVolumes = normalizePracticeInstrumentVolumes(practiceState.instrumentVolumes);
        payload[0].TimelineLoop = false;
        payload[0].TimelineLoopCount = practiceState.timerMinutes > 0
            ? 'loop'
            : Math.max(0, practiceState.repeatCount - 1);
        payload[0].PracticeDurationSeconds = practiceState.timerMinutes > 0
            ? practiceState.timerMinutes * 60
            : 0;
    }
    return payload;
}

function getPracticeScrollerStepsPerBar() {
    if (rhythm === 'tenaer') {
        return 24;
    }
    if (rhythm === 'neunaer') {
        return 18;
    }
    return 32;
}

function getPracticeScrollerStepsPerBeat() {
    if (rhythm === 'tenaer') {
        return 6;
    }
    if (rhythm === 'neunaer') {
        return 6;
    }
    return 8;
}

function getPracticeScrollerBaseStepMs() {
    const tempo = typeof normalizeTimelineTempo === 'function'
        ? normalizeTimelineTempo(timelineState.tempo)
        : 100;
    const safeTempo = Math.max(1, Number(tempo) || 100);
    const stepSeconds = 60 / safeTempo / getPracticeScrollerStepsPerBeat();
    return Math.max(1, stepSeconds * 1000);
}

function getPracticeScrollerLeadInSteps(leadInMs) {
    const safeLeadInMs = Math.max(0, Number(leadInMs) || 0);
    if (safeLeadInMs <= 0) {
        return practiceScrollerState.leadInSteps;
    }
    return Math.max(1, safeLeadInMs / getPracticeScrollerBaseStepMs());
}

function getPracticeScrollerNoteLabel(noteValue) {
    if (!noteValue || noteValue === 'f') {
        return '';
    }
    return practiceScrollerNoteLabels[noteValue] || String(noteValue).slice(0, 3);
}

function getPracticeScrollerNoteClass(noteValue) {
    if (!noteValue || noteValue === 'f') {
        return 'is-rest';
    }
    return 'is-note note-' + String(noteValue).replace(/[^a-z0-9_-]/gi, '-').toLowerCase();
}

function getPracticeScrollerSymbolParts(noteValue) {
    const symbolMap = {
        tone: [{ type: 'circle' }],
        Open: [{ type: 'circle', lane: 'bottom' }],
        sangban: [{ type: 'circle', lane: 'middle' }],
        bass: [{ type: 'square' }],
        doundoun: [{ type: 'square', lane: 'bottom' }],
        slap: [{ type: 'cross' }],
        Bell: [{ type: 'cross', lane: 'top' }],
        kenkeni: [{ type: 'cross', lane: 'top' }],
        tone_muffled: [{ type: 'circle', muted: true }],
        Muffled: [{ type: 'circle', muted: true, lane: 'bottom' }],
        sangban_muffled: [{ type: 'circle', muted: true, lane: 'middle' }],
        slap_muffled: [{ type: 'cross', muted: true }],
        Klick: [{ type: 'cross', muted: true, lane: 'bottom' }],
        kenkeni_muffled: [{ type: 'cross', muted: true, lane: 'top' }],
        tone_flam: [{ type: 'circle', ghost: true }, { type: 'circle' }],
        Flam: [{ type: 'circle', ghost: true }, { type: 'circle' }],
        'T-Flam': [{ type: 'circle', ghost: true }, { type: 'circle' }],
        slap_flam: [{ type: 'cross' }, { type: 'cross' }],
        'S-Flam': [{ type: 'cross' }, { type: 'cross' }],
        bass_slap_flam: [{ type: 'square' }, { type: 'cross' }],
        Bell_Open: [{ type: 'cross', lane: 'top' }, { type: 'circle', lane: 'bottom' }],
        Bell_Muffled: [{ type: 'cross', lane: 'top' }, { type: 'circle', muted: true, lane: 'bottom' }],
        Bell_Klick: [{ type: 'cross', lane: 'top' }, { type: 'cross', muted: true, lane: 'bottom' }],
        kenkeni_sangban: [{ type: 'cross', lane: 'top' }, { type: 'circle', lane: 'middle' }],
        kenkeni_doundoun: [{ type: 'cross', lane: 'top' }, { type: 'square', lane: 'bottom' }],
        sangban_doundoun: [{ type: 'circle', lane: 'middle' }, { type: 'square', lane: 'bottom' }],
        kenkeni_muffled_sangban: [{ type: 'cross', muted: true, lane: 'top' }, { type: 'circle', lane: 'middle' }],
        kenkeni_sangban_muffled: [{ type: 'cross', lane: 'top' }, { type: 'circle', muted: true, lane: 'middle' }],
        sangban_muffled_doundoun: [{ type: 'circle', muted: true, lane: 'middle' }, { type: 'square', lane: 'bottom' }],
        kenkeni_muffled_doundoun: [{ type: 'cross', muted: true, lane: 'top' }, { type: 'square', lane: 'bottom' }]
    };
    return symbolMap[noteValue] || [];
}

function createPracticeScrollerSymbolPart(partConfig) {
    const partWrapEl = document.createElement('span');
    partWrapEl.className = 'practice-symbol-part-wrap';
    if (partConfig.lane) {
        partWrapEl.classList.add('is-lane-' + partConfig.lane);
    }

    const partEl = document.createElement('span');
    partEl.className = 'practice-symbol-part is-' + partConfig.type;
    if (partConfig.ghost) {
        partEl.classList.add('is-ghost');
    }
    partWrapEl.appendChild(partEl);

    if (partConfig.muted) {
        const muteLineEl = document.createElement('span');
        muteLineEl.className = 'practice-symbol-mute-line';
        partWrapEl.appendChild(muteLineEl);
    }

    return partWrapEl;
}

function createPracticeScrollerNoteSymbol(noteValue) {
    const parts = getPracticeScrollerSymbolParts(noteValue);
    if (parts.length === 0) {
        return null;
    }

    const symbolEl = document.createElement('span');
    const usesVerticalLanes = parts.some(function (partConfig) {
        return Boolean(partConfig.lane);
    });
    symbolEl.className = parts.length > 1
        ? 'practice-note-symbol is-combo'
        : 'practice-note-symbol';
    if (usesVerticalLanes) {
        const usesMiddleLane = parts.some(function (partConfig) {
            return partConfig.lane === 'middle';
        });
        symbolEl.classList.add(usesMiddleLane ? 'is-dreierbass-stack' : 'is-bass-stack');
    }
    parts.forEach(function (partConfig) {
        symbolEl.appendChild(createPracticeScrollerSymbolPart(partConfig));
    });
    return symbolEl;
}

function flattenPracticeScrollerSections(sections) {
    const safeSections = Array.isArray(sections) ? sections : [];
    const visualLoopCopies = getPracticeScrollerVisualLoopCopies();
    const hasOuterPracticeLoop = practiceState.repeatCount > 1 || practiceState.timerMinutes > 0;
    const extraVisualLoopCopies = hasOuterPracticeLoop ? visualLoopCopies : 0;
    const trackNotes = createEmptyPracticeTrackNotes();
    const targetSteps = createEmptyPracticeTrackFlags();
    const sectionBoundaries = [];
    const barStartSteps = [];
    const loopSegments = [];
    const playbackSegments = [];
    let stepOffset = 0;
    let playbackOffset = 0;
    let loopStartStep = null;
    let playbackLoopStart = null;

    safeSections.forEach(function (section) {
        const sectionLength = Math.max.apply(null, practiceTrackInstrumentNames.map(function (instrumentName) {
            const notes = section && section.trackNotes && Array.isArray(section.trackNotes[instrumentName])
                ? section.trackNotes[instrumentName]
                : [];
            return notes.length;
        }).concat(0));

        if (sectionLength <= 0) {
            return;
        }
        const repeatCount = normalizePracticeCount(section.repeatCount, 1, 1, 32);
        const renderRepeatCount = Math.min(repeatCount, visualLoopCopies);
        const isLeadIn = Boolean(section.isLeadIn);
        const finalRepeatOutSteps = section && section.finalRepeatOutSteps ? section.finalRepeatOutSteps : {};
        if (!isLeadIn && loopStartStep === null) {
            loopStartStep = stepOffset;
            playbackLoopStart = playbackOffset;
        }

        sectionBoundaries.push({
            step: stepOffset,
            label: section.labelName || section.label || ''
        });
        const sectionBarStartSteps = Array.isArray(section.barStartSteps) && section.barStartSteps.length > 0
            ? section.barStartSteps
            : [0];
        for (let repeatIndex = 0; repeatIndex < renderRepeatCount; repeatIndex += 1) {
            sectionBarStartSteps.forEach(function (barStep) {
                const safeBarStep = Math.max(0, Math.round(Number(barStep) || 0));
                if (safeBarStep < sectionLength) {
                    barStartSteps.push(stepOffset + repeatIndex * sectionLength + safeBarStep);
                }
            });
        }
        if (!isLeadIn) {
            loopSegments.push({
                start: stepOffset,
                length: sectionLength
            });
        }
        playbackSegments.push({
            playbackStart: playbackOffset,
            playbackLength: sectionLength * repeatCount,
            visualStart: stepOffset,
            visualLength: sectionLength * renderRepeatCount
        });

        practiceTrackInstrumentNames.forEach(function (instrumentName) {
            const rawNotes = section && section.trackNotes && Array.isArray(section.trackNotes[instrumentName])
                ? section.trackNotes[instrumentName]
                : [];
            const notes = loopPracticeArrayToLength(rawNotes, sectionLength, 'f');
            const isPracticeTarget = section &&
                Array.isArray(section.practiceTargetInstruments) &&
                section.practiceTargetInstruments.indexOf(instrumentName) !== -1;
            const rawStepTargetFlags = section && section.trackTargetFlags &&
                Array.isArray(section.trackTargetFlags[instrumentName])
                ? section.trackTargetFlags[instrumentName]
                : [];
            const stepTargetFlags = loopPracticeArrayToLength(rawStepTargetFlags, sectionLength, false);
            const outStep = finalRepeatOutSteps[instrumentName];
            const safeOutStep = outStep === null || outStep === undefined
                ? null
                : Math.max(0, Number(outStep) || 0);
            const forceFinalOutAtSectionEnd = Boolean(section && section.forceFinalOutAtSectionEnd);
            for (let repeatIndex = 0; repeatIndex < renderRepeatCount; repeatIndex += 1) {
                for (let stepIndex = 0; stepIndex < sectionLength; stepIndex += 1) {
                    const shouldMuteForOut = safeOutStep !== null &&
                        (isPracticeTarget || !hasOuterPracticeLoop || forceFinalOutAtSectionEnd) &&
                        repeatIndex === repeatCount - 1 &&
                        stepIndex > safeOutStep;
                    trackNotes[instrumentName].push(shouldMuteForOut ? 'f' : (notes[stepIndex] || 'f'));
                    const isStepTarget = Boolean(isPracticeTarget) || Boolean(stepTargetFlags[stepIndex]);
                    targetSteps[instrumentName].push(isStepTarget && !shouldMuteForOut);
                }
            }
        });

        stepOffset += sectionLength * renderRepeatCount;
        playbackOffset += sectionLength * repeatCount;
    });

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        targetSteps[instrumentName] = expandPracticeTargetFlags(targetSteps[instrumentName], 1);
    });

    const visualCycleSteps = stepOffset;
    const safeLoopStartStep = loopStartStep === null ? 0 : loopStartStep;
    const safePlaybackLoopStart = playbackLoopStart === null ? 0 : playbackLoopStart;
    const visualLoopLength = Math.max(0, visualCycleSteps - safeLoopStartStep);
    const playbackLoopLength = Math.max(0, playbackOffset - safePlaybackLoopStart);
    const baseTrackNotes = {};
    const baseTargetSteps = {};
    const baseBarStartSteps = barStartSteps
        .filter(function (step) {
            return step >= safeLoopStartStep;
        })
        .map(function (step) {
            return step - safeLoopStartStep;
        });
    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        baseTrackNotes[instrumentName] = trackNotes[instrumentName].slice(safeLoopStartStep);
        baseTargetSteps[instrumentName] = targetSteps[instrumentName].slice(safeLoopStartStep);
    });

    for (let copyIndex = 0; copyIndex < extraVisualLoopCopies; copyIndex += 1) {
        const copyStartStep = visualCycleSteps + copyIndex * visualLoopLength;
        baseBarStartSteps.forEach(function (step) {
            barStartSteps.push(copyStartStep + step);
        });
        practiceTrackInstrumentNames.forEach(function (instrumentName) {
            trackNotes[instrumentName] = trackNotes[instrumentName].concat(baseTrackNotes[instrumentName]);
            targetSteps[instrumentName] = targetSteps[instrumentName].concat(baseTargetSteps[instrumentName]);
        });
    }
    const visualTailSteps = extraVisualLoopCopies > 0
        ? getPracticeScrollerTailSteps(visualLoopLength)
        : 0;
    if (visualTailSteps > 0) {
        const tailStartStep = visualCycleSteps + extraVisualLoopCopies * visualLoopLength;
        baseBarStartSteps.forEach(function (step) {
            if (step < visualTailSteps) {
                barStartSteps.push(tailStartStep + step);
            }
        });
        practiceTrackInstrumentNames.forEach(function (instrumentName) {
            trackNotes[instrumentName] = trackNotes[instrumentName].concat(
                baseTrackNotes[instrumentName].slice(0, visualTailSteps)
            );
            targetSteps[instrumentName] = targetSteps[instrumentName].concat(
                baseTargetSteps[instrumentName].slice(0, visualTailSteps)
            );
        });
    }

    return {
        trackNotes: trackNotes,
        targetSteps: targetSteps,
        sectionBoundaries: sectionBoundaries,
        barStartSteps: barStartSteps,
        loopSegments: loopSegments,
        loopLength: loopSegments.reduce(function (sum, segment) {
            return sum + segment.length;
        }, 0),
        loopStartStep: safeLoopStartStep,
        visualLoopLength: visualLoopLength,
        playbackSegments: playbackSegments,
        playbackTotalSteps: playbackOffset,
        playbackLoopStart: safePlaybackLoopStart,
        playbackLoopLength: playbackLoopLength,
        visualCycleSteps: visualCycleSteps,
        visualLoopCopies: extraVisualLoopCopies,
        visualTailSteps: visualTailSteps,
        totalSteps: visualCycleSteps + (visualLoopLength * extraVisualLoopCopies) + visualTailSteps
    };
}

function createPracticeScrollerCell(noteValue, stepIndex, stepsPerBar, isPracticeTarget, barStartInfo) {
    const cellEl = document.createElement('span');
    const safeBarStartInfo = barStartInfo || {};
    cellEl.className = 'practice-scroller-cell ' + getPracticeScrollerNoteClass(noteValue);
    if (isPracticeTarget) {
        cellEl.classList.add('is-practice-target');
    }
    const symbolEl = createPracticeScrollerNoteSymbol(noteValue);
    if (symbolEl) {
        cellEl.appendChild(symbolEl);
    } else {
        cellEl.textContent = getPracticeScrollerNoteLabel(noteValue);
    }
    const isBarStart = safeBarStartInfo.barStartSet
        ? safeBarStartInfo.barStartSet.has(stepIndex)
        : (stepsPerBar > 0 && stepIndex % stepsPerBar === 0);
    if (isBarStart) {
        cellEl.classList.add('is-bar-start');
        const barNumberEl = document.createElement('span');
        barNumberEl.className = 'practice-bar-number';
        barNumberEl.textContent = String(
            safeBarStartInfo.barNumberByStep && safeBarStartInfo.barNumberByStep[stepIndex]
                ? safeBarStartInfo.barNumberByStep[stepIndex]
                : Math.floor(stepIndex / stepsPerBar) + 1
        );
        cellEl.appendChild(barNumberEl);
    }
    return cellEl;
}

function getPracticeScrollerPreRollLineSteps() {
    return Math.max(practiceScrollerState.preRollLineSteps, practiceScrollerState.stepsPerBar);
}

function appendPracticeScrollerCells(laneEl, notes, targetSteps, visualStepOffset, stepsPerBar, barStartInfo) {
    const safeNotes = Array.isArray(notes) ? notes : [];
    const safeTargetSteps = Array.isArray(targetSteps) ? targetSteps : [];
    safeNotes.forEach(function (noteValue, stepIndex) {
        laneEl.appendChild(createPracticeScrollerCell(
            noteValue,
            visualStepOffset + stepIndex,
            stepsPerBar,
            safeTargetSteps[stepIndex],
            barStartInfo
        ));
    });
}

function arePracticeArraysEqual(firstArray, secondArray) {
    const safeFirstArray = Array.isArray(firstArray) ? firstArray : [];
    const safeSecondArray = Array.isArray(secondArray) ? secondArray : [];
    if (safeFirstArray.length !== safeSecondArray.length) {
        return false;
    }
    return safeFirstArray.every(function (entry, entryIndex) {
        return entry === safeSecondArray[entryIndex];
    });
}

function getCollapsedPracticeDjembeRows(flattened) {
    const djembeRows = ['Djembe_1', 'Djembe_2', 'Djembe_3'];
    const firstInstrument = djembeRows[0];
    const firstNotes = flattened.trackNotes[firstInstrument] || [];
    const firstTargets = flattened.targetSteps[firstInstrument] || [];
    const hasTarget = firstTargets.some(Boolean);
    const canCollapse = hasTarget && djembeRows.every(function (instrumentName) {
        return arePracticeArraysEqual(flattened.trackNotes[instrumentName], firstNotes) &&
            arePracticeArraysEqual(flattened.targetSteps[instrumentName], firstTargets);
    });

    if (!canCollapse) {
        return {
            hiddenRows: [],
            labels: {}
        };
    }

    return {
        hiddenRows: ['Djembe_2', 'Djembe_3'],
        labels: {
            Djembe_1: 'Djembe'
        }
    };
}

function closePracticeInstrumentVolumePopover() {
    const popoverEl = document.getElementById('practiceInstrumentVolumePopover');
    if (popoverEl) {
        popoverEl.remove();
    }
}

function openPracticeInstrumentVolumePopover(instrumentNames, anchorEl, labelText) {
    const targetInstruments = (Array.isArray(instrumentNames) ? instrumentNames : [instrumentNames])
        .filter(function (instrumentName) {
            return practiceTrackInstrumentNames.indexOf(instrumentName) !== -1;
        });
    if (targetInstruments.length === 0 || !anchorEl) {
        return;
    }

    closePracticeInstrumentVolumePopover();

    const label = labelText || practiceScrollerInstrumentLabels[targetInstruments[0]] || targetInstruments[0];
    const volume = targetInstruments.reduce(function (sum, instrumentName) {
        return sum + getPracticeInstrumentVolume(instrumentName);
    }, 0) / targetInstruments.length;
    const popoverEl = document.createElement('div');
    popoverEl.id = 'practiceInstrumentVolumePopover';
    popoverEl.className = 'practice-volume-popover';
    popoverEl.dataset.instrumentName = targetInstruments.join(',');

    const titleEl = document.createElement('div');
    titleEl.className = 'practice-volume-title';
    titleEl.textContent = label;

    const valueEl = document.createElement('output');
    valueEl.className = 'practice-volume-value';
    valueEl.value = Math.round(volume * 100) + '%';
    valueEl.textContent = valueEl.value;

    const rangeEl = document.createElement('input');
    rangeEl.type = 'range';
    rangeEl.min = '0';
    rangeEl.max = '200';
    rangeEl.step = '5';
    rangeEl.value = Math.round(volume * 100);
    rangeEl.setAttribute('aria-label', label + ' Lautstärke');

    const actionsEl = document.createElement('div');
    actionsEl.className = 'practice-volume-actions';

    const muteButtonEl = document.createElement('button');
    muteButtonEl.type = 'button';
    muteButtonEl.textContent = 'Stumm';

    const resetButtonEl = document.createElement('button');
    resetButtonEl.type = 'button';
    resetButtonEl.textContent = '100%';

    function applyVolume(percentValue) {
        const normalizedVolume = normalizePracticeInstrumentVolume(Number(percentValue) / 100);
        const previousVolumes = normalizePracticeInstrumentVolumes(practiceState.instrumentVolumes);
        const volumeWillChange = targetInstruments.some(function (instrumentName) {
            return normalizePracticeInstrumentVolume(previousVolumes[instrumentName]) !== normalizedVolume;
        });
        if (volumeWillChange && typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        if (normalizedVolume === 1) {
            targetInstruments.forEach(function (instrumentName) {
                delete practiceState.instrumentVolumes[instrumentName];
            });
        } else {
            targetInstruments.forEach(function (instrumentName) {
                practiceState.instrumentVolumes[instrumentName] = normalizedVolume;
            });
        }
        rangeEl.value = String(Math.round(normalizedVolume * 100));
        valueEl.value = Math.round(normalizedVolume * 100) + '%';
        valueEl.textContent = valueEl.value;
        notifyPracticeInstrumentVolumesChanged();
    }

    rangeEl.addEventListener('input', function (event) {
        applyVolume(event.target.value);
    });
    muteButtonEl.addEventListener('click', function () {
        applyVolume(0);
    });
    resetButtonEl.addEventListener('click', function () {
        applyVolume(100);
    });

    actionsEl.append(muteButtonEl, resetButtonEl);
    popoverEl.append(titleEl, rangeEl, valueEl, actionsEl);
    document.body.appendChild(popoverEl);

    const anchorRect = anchorEl.getBoundingClientRect();
    const popoverRect = popoverEl.getBoundingClientRect();
    const left = Math.max(8, Math.min(
        window.innerWidth - popoverRect.width - 8,
        anchorRect.left + (anchorRect.width / 2) - (popoverRect.width / 2)
    ));
    const top = Math.max(8, anchorRect.top - popoverRect.height - 8);
    popoverEl.style.left = left + 'px';
    popoverEl.style.top = top + 'px';
}

function openTimelineInstrumentVolumesPopover(anchorEl) {
    if (!anchorEl) {
        return;
    }

    closePracticeInstrumentVolumePopover();

    const popoverEl = document.createElement('div');
    popoverEl.id = 'practiceInstrumentVolumePopover';
    popoverEl.className = 'practice-volume-popover practice-volume-popover-wide';

    const titleEl = document.createElement('div');
    titleEl.className = 'practice-volume-title';
    titleEl.textContent = 'Instrumentlautstärken';
    popoverEl.appendChild(titleEl);

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        const labelText = practiceScrollerInstrumentLabels[instrumentName] || instrumentName.replace('_', ' ');
        const rowEl = document.createElement('label');
        rowEl.className = 'practice-volume-row';

        const nameEl = document.createElement('span');
        nameEl.className = 'practice-volume-row-name';
        nameEl.textContent = labelText;

        const rangeEl = document.createElement('input');
        rangeEl.type = 'range';
        rangeEl.min = '0';
        rangeEl.max = '200';
        rangeEl.step = '5';
        rangeEl.value = Math.round(getPracticeInstrumentVolume(instrumentName) * 100);
        rangeEl.setAttribute('aria-label', labelText + ' Lautstärke');

        const valueEl = document.createElement('output');
        valueEl.className = 'practice-volume-row-value';
        valueEl.value = rangeEl.value + '%';
        valueEl.textContent = valueEl.value;

        rangeEl.addEventListener('input', function (event) {
            const normalizedVolume = normalizePracticeInstrumentVolume(Number(event.target.value) / 100);
            const previousVolume = getPracticeInstrumentVolume(instrumentName);
            if (previousVolume !== normalizedVolume && typeof recordArrangementHistorySnapshot === 'function') {
                recordArrangementHistorySnapshot();
            }
            if (normalizedVolume === 1) {
                delete practiceState.instrumentVolumes[instrumentName];
            } else {
                practiceState.instrumentVolumes[instrumentName] = normalizedVolume;
            }
            valueEl.value = Math.round(normalizedVolume * 100) + '%';
            valueEl.textContent = valueEl.value;
            notifyPracticeInstrumentVolumesChanged();
        });

        rowEl.append(nameEl, rangeEl, valueEl);
        popoverEl.appendChild(rowEl);
    });

    const actionsEl = document.createElement('div');
    actionsEl.className = 'practice-volume-actions';

    const resetButtonEl = document.createElement('button');
    resetButtonEl.type = 'button';
    resetButtonEl.textContent = 'Alle 100%';
    resetButtonEl.addEventListener('click', function () {
        if (Object.keys(normalizePracticeInstrumentVolumes(practiceState.instrumentVolumes)).length > 0 &&
                typeof recordArrangementHistorySnapshot === 'function') {
            recordArrangementHistorySnapshot();
        }
        practiceState.instrumentVolumes = {};
        notifyPracticeInstrumentVolumesChanged();
        openTimelineInstrumentVolumesPopover(anchorEl);
    });

    actionsEl.appendChild(resetButtonEl);
    popoverEl.appendChild(actionsEl);
    document.body.appendChild(popoverEl);

    const anchorRect = anchorEl.getBoundingClientRect();
    const popoverRect = popoverEl.getBoundingClientRect();
    const left = Math.max(8, Math.min(
        window.innerWidth - popoverRect.width - 8,
        anchorRect.right - popoverRect.width
    ));
    const top = Math.max(8, Math.min(
        window.innerHeight - popoverRect.height - 8,
        anchorRect.bottom + 8
    ));
    popoverEl.style.left = left + 'px';
    popoverEl.style.top = top + 'px';
}

document.addEventListener('click', function (event) {
    const popoverEl = document.getElementById('practiceInstrumentVolumePopover');
    if (!popoverEl) {
        return;
    }
    if (popoverEl.contains(event.target) ||
            (event.target && event.target.closest && event.target.closest('.practice-scroller-label'))) {
        return;
    }
    closePracticeInstrumentVolumePopover();
});

function renderPracticeScrollerFromPayload(playerPayload) {
    const scrollerEl = document.getElementById('practiceScroller');
    const rowsEl = document.getElementById('practiceScrollerRows');
    const statusEl = document.getElementById('practiceScrollerStatus');
    const config = Array.isArray(playerPayload) ? playerPayload[0] : null;
    const sections = config && Array.isArray(config.PracticeSections) ? config.PracticeSections : [];

    if (!scrollerEl || !rowsEl || !statusEl) {
        return;
    }

    practiceScrollerState.playbackTimers.forEach(function (playbackTimer) {
        window.clearTimeout(playbackTimer);
    });
    practiceScrollerState.playbackTimers = [];
    stopPracticeScrollerAnimation();
    practiceScrollerState.playbackEvents = [];
    practiceScrollerState.playbackAnchor = null;
    practiceScrollerState.activeCells = [];
    closePracticeInstrumentVolumePopover();

    const flattened = flattenPracticeScrollerSections(sections);
    const stepsPerBar = getPracticeScrollerStepsPerBar();
    practiceScrollerState.totalSteps = flattened.totalSteps;
    practiceScrollerState.loopSegments = flattened.loopSegments;
    practiceScrollerState.loopLength = flattened.loopLength;
    practiceScrollerState.loopStartStep = flattened.loopStartStep;
    practiceScrollerState.visualLoopLength = flattened.visualLoopLength;
    practiceScrollerState.activeVisualLoopCopies = flattened.visualLoopCopies;
    practiceScrollerState.playbackSegments = flattened.playbackSegments;
    practiceScrollerState.playbackTotalSteps = flattened.playbackTotalSteps;
    practiceScrollerState.playbackLoopStart = flattened.playbackLoopStart;
    practiceScrollerState.playbackLoopLength = flattened.playbackLoopLength;
    practiceScrollerState.visualCycleSteps = flattened.visualCycleSteps;
    practiceScrollerState.visualTotalSteps = flattened.totalSteps;
    practiceScrollerState.visualTailSteps = flattened.visualTailSteps;
    practiceScrollerState.stepsPerBar = stepsPerBar;
    practiceScrollerState.currentStep = 0;
    practiceScrollerState.activeStep = -1;

    rowsEl.innerHTML = '';
    scrollerEl.hidden = flattened.totalSteps === 0;

    if (flattened.totalSteps === 0) {
        statusEl.textContent = 'Keine Noten';
        return;
    }

    const collapsedDjembeRows = getCollapsedPracticeDjembeRows(flattened);
    const preRollLineSteps = getPracticeScrollerPreRollLineSteps();
    const barStartSteps = (Array.isArray(flattened.barStartSteps) ? flattened.barStartSteps : [])
        .map(function (step) {
            return Math.max(0, Math.round(Number(step) || 0)) + preRollLineSteps;
        })
        .filter(function (step, index, steps) {
            return steps.indexOf(step) === index;
        })
        .sort(function (stepA, stepB) {
            return stepA - stepB;
        });
    const barNumberByStep = {};
    barStartSteps.forEach(function (step, index) {
        barNumberByStep[step] = index + 1;
    });
    const barStartInfo = {
        barStartSet: new Set(barStartSteps),
        barNumberByStep: barNumberByStep
    };
    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        if (collapsedDjembeRows.hiddenRows.indexOf(instrumentName) !== -1) {
            return;
        }
        const notes = flattened.trackNotes[instrumentName];
        const targetSteps = flattened.targetSteps[instrumentName] || [];
        const hasNotes = notes.some(function (noteValue) {
            return noteValue && noteValue !== 'f';
        });
        if (!hasNotes) {
            return;
        }

        const rowEl = document.createElement('div');
        rowEl.className = 'practice-scroller-row';
        if (instrumentName === 'Dreierbass') {
            rowEl.classList.add('is-dreierbass-row');
        } else if (instrumentName === 'Kenkeni' || instrumentName === 'Sangban' || instrumentName === 'Doundoun') {
            rowEl.classList.add('is-bass-row');
        }
        if (targetSteps.some(Boolean)) {
            rowEl.classList.add('has-practice-target');
        }

        const labelEl = document.createElement('div');
        labelEl.className = 'practice-scroller-label';
        labelEl.tabIndex = 0;
        labelEl.setAttribute('role', 'button');
        labelEl.dataset.instrumentName = instrumentName;
        const displayLabel = collapsedDjembeRows.labels[instrumentName] ||
            practiceScrollerInstrumentLabels[instrumentName] ||
            instrumentName;
        labelEl.setAttribute('aria-label', 'Lautstärke für ' + displayLabel + ' einstellen');
        const volumeInstruments = collapsedDjembeRows.labels[instrumentName] === 'Djembe'
            ? ['Djembe_1', 'Djembe_2', 'Djembe_3']
            : [instrumentName];
        labelEl.textContent = displayLabel;
        labelEl.addEventListener('click', function (event) {
            event.stopPropagation();
            openPracticeInstrumentVolumePopover(volumeInstruments, labelEl, displayLabel);
        });
        labelEl.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPracticeInstrumentVolumePopover(volumeInstruments, labelEl, displayLabel);
            }
        });

        const laneWrapEl = document.createElement('div');
        laneWrapEl.className = 'practice-scroller-lane-wrap';

        const laneEl = document.createElement('div');
        laneEl.className = 'practice-scroller-lane';

        for (let leadInIndex = 0; leadInIndex < preRollLineSteps; leadInIndex += 1) {
            laneEl.appendChild(createPracticeScrollerCell('f', leadInIndex, stepsPerBar, false, barStartInfo));
        }

        appendPracticeScrollerCells(laneEl, notes, targetSteps, preRollLineSteps, stepsPerBar, barStartInfo);

        laneWrapEl.appendChild(laneEl);
        rowEl.append(labelEl, laneWrapEl);
        rowsEl.appendChild(rowEl);
    });

    updatePracticeScrollerPosition(-practiceScrollerState.visualLeadInSteps);
    statusEl.textContent = Math.ceil(flattened.totalSteps / stepsPerBar) + ' Takte';
}

function normalizePracticeScrollerPlaybackStep(playbackStep) {
    const rawStep = Math.max(0, Math.floor(Number(playbackStep) || 0));
    const playbackSegments = practiceScrollerState.playbackSegments || [];
    const playbackTotalSteps = practiceScrollerState.playbackTotalSteps || 0;
    if (practiceScrollerState.totalSteps <= 0 || playbackSegments.length === 0 || playbackTotalSteps <= 0) {
        return rawStep;
    }
    const playbackLoopStart = practiceScrollerState.playbackLoopStart || 0;
    const playbackLoopLength = practiceScrollerState.playbackLoopLength || 0;
    const visualLoopStart = practiceScrollerState.visualCycleSteps || 0;
    const visualLoopLength = practiceScrollerState.visualLoopLength || 0;
    const normalizedPlaybackStep = rawStep < playbackTotalSteps || playbackLoopLength <= 0
        ? rawStep
        : playbackLoopStart + ((rawStep - playbackTotalSteps) % playbackLoopLength);
    const matchedSegment = playbackSegments.find(function (segment) {
        return normalizedPlaybackStep >= segment.playbackStart &&
            normalizedPlaybackStep < segment.playbackStart + segment.playbackLength;
    });
    if (!matchedSegment || matchedSegment.visualLength <= 0) {
        return Math.min(rawStep, practiceScrollerState.totalSteps - 1);
    }

    const localPlaybackStep = normalizedPlaybackStep - matchedSegment.playbackStart;
    const cycleIndex = rawStep >= playbackTotalSteps && playbackLoopLength > 0 && visualLoopLength > 0
        ? Math.floor((rawStep - playbackTotalSteps) / playbackLoopLength)
        : 0;
    const baseVisualStart = rawStep >= playbackTotalSteps && visualLoopLength > 0
        ? visualLoopStart + (cycleIndex * visualLoopLength)
        : 0;
    const segmentVisualStart = rawStep >= playbackTotalSteps
        ? Math.max(0, matchedSegment.visualStart - practiceScrollerState.loopStartStep)
        : matchedSegment.visualStart;
    return baseVisualStart + segmentVisualStart + (localPlaybackStep % matchedSegment.visualLength);
}

function getRenderedPracticeScrollerStep(visualStep) {
    const rawStep = Number(visualStep) || 0;
    const visualLoopLength = practiceScrollerState.visualLoopLength || 0;
    const visualCycleSteps = practiceScrollerState.visualCycleSteps || 0;

    if (visualLoopLength <= 0 || rawStep < visualCycleSteps) {
        return rawStep;
    }

    return visualCycleSteps + ((rawStep - visualCycleSteps) % visualLoopLength);
}

function updatePracticeScrollerPosition(playbackStep) {
    const scrollerEl = document.getElementById('practiceScroller');
    if (!scrollerEl || scrollerEl.hidden || practiceScrollerState.totalSteps <= 0) {
        return;
    }

    const stageEl = scrollerEl.querySelector('.practice-scroller-stage');
    const firstLaneWrapEl = scrollerEl.querySelector('.practice-scroller-lane-wrap');
    const firstLaneEl = scrollerEl.querySelector('.practice-scroller-lane');
    const firstCellEl = scrollerEl.querySelector('.practice-scroller-cell');
    const statusEl = document.getElementById('practiceScrollerStatus');
    const minVisualStep = -practiceScrollerState.visualLeadInSteps;
    const maxVisualStep = Math.max(0, practiceScrollerState.visualTotalSteps - 1);
    const rawStep = Math.max(minVisualStep, Number(playbackStep) || 0);
    const renderedStep = getRenderedPracticeScrollerStep(rawStep);
    const safeStep = Math.max(minVisualStep, Math.min(maxVisualStep, renderedStep));
    const activeStep = Math.max(0, Math.min(maxVisualStep, Math.round(safeStep)));
    const cellRect = firstCellEl ? firstCellEl.getBoundingClientRect() : null;
    const stepWidth = cellRect && cellRect.width > 0 ? cellRect.width : practiceScrollerState.stepWidth;
    const activePlayheadRatio = window.matchMedia('(max-width: 760px)').matches
        ? 0.24
        : practiceScrollerState.playheadRatio;
    const playheadX = stageEl ? stageEl.clientWidth * activePlayheadRatio : 0;
    const laneStartX = firstLaneWrapEl ? firstLaneWrapEl.offsetLeft : 0;
    const visualStep = safeStep + getPracticeScrollerPreRollLineSteps();
    const laneOffset = playheadX - laneStartX - (visualStep * stepWidth) - (stepWidth / 2);

    practiceScrollerState.currentStep = rawStep;
    practiceScrollerState.stepWidth = stepWidth;
    scrollerEl.style.setProperty('--practice-scroller-offset', laneOffset + 'px');

    if (practiceScrollerState.activeStep !== activeStep) {
        if (isPracticeScrollerCompactViewport()) {
            practiceScrollerState.activeCells.forEach(function (cellEl) {
                cellEl.classList.remove('is-current');
            });
            practiceScrollerState.activeCells = [];
        } else {
            practiceScrollerState.activeCells.forEach(function (cellEl) {
                cellEl.classList.remove('is-current');
            });
            practiceScrollerState.activeCells = [];
            scrollerEl.querySelectorAll('.practice-scroller-lane').forEach(function (laneEl) {
                const activeChildIndex = activeStep + getPracticeScrollerPreRollLineSteps();
                if (laneEl.children[activeChildIndex]) {
                    const activeCellEl = laneEl.children[activeChildIndex];
                    activeCellEl.classList.add('is-current');
                    practiceScrollerState.activeCells.push(activeCellEl);
                }
            });
        }
        practiceScrollerState.activeStep = activeStep;

        if (statusEl && (!isPracticeScrollerCompactViewport() || activeStep % practiceScrollerState.stepsPerBar === 0)) {
            const displayBase = practiceScrollerState.visualCycleSteps || practiceScrollerState.totalSteps;
            const displayStep = displayBase > 0
                ? activeStep % displayBase
                : activeStep;
            statusEl.textContent = 'Takt ' + (Math.floor(displayStep / practiceScrollerState.stepsPerBar) + 1) +
                ', Schritt ' + ((displayStep % practiceScrollerState.stepsPerBar) + 1);
        }
    }
}

function stopPracticeScrollerAnimation() {
    if (practiceScrollerState.animationFrameId !== null) {
        window.cancelAnimationFrame(practiceScrollerState.animationFrameId);
        practiceScrollerState.animationFrameId = null;
    }
}

function runPracticeScrollerAnimation() {
    const now = window.performance.now();
    const events = practiceScrollerState.playbackEvents;

    while (events.length > 0 && events[0].time <= now) {
        practiceScrollerState.playbackAnchor = events.shift();
        updatePracticeScrollerPosition(practiceScrollerState.playbackAnchor.step);
    }

    const anchor = practiceScrollerState.playbackAnchor;
    const nextEvent = events[0];

    if (anchor && nextEvent && nextEvent.time > anchor.time && nextEvent.step >= anchor.step) {
        const progress = Math.max(0, Math.min(1, (now - anchor.time) / (nextEvent.time - anchor.time)));
        updatePracticeScrollerPosition(anchor.step + ((nextEvent.step - anchor.step) * progress));
    } else if (anchor) {
        updatePracticeScrollerPosition(anchor.step);
    }

    if (events.length > 0) {
        practiceScrollerState.animationFrameId = window.requestAnimationFrame(runPracticeScrollerAnimation);
    } else {
        practiceScrollerState.animationFrameId = null;
    }
}

function recyclePracticeScrollerVisualLoop(nextStep) {
    const visualLoopLength = practiceScrollerState.visualLoopLength || 0;
    if (visualLoopLength <= 0 || !Number.isFinite(nextStep)) {
        return;
    }

    function recycleStep(referenceStep) {
        if (!Number.isFinite(referenceStep)) {
            return referenceStep;
        }
        if (referenceStep > nextStep) {
            const loopDistance = referenceStep - nextStep;
            return referenceStep - (Math.ceil(loopDistance / visualLoopLength) * visualLoopLength);
        }
        const loopDistance = nextStep - referenceStep;
        if (loopDistance >= visualLoopLength) {
            return referenceStep + (Math.floor(loopDistance / visualLoopLength) * visualLoopLength);
        }
        return referenceStep;
    }

    if (practiceScrollerState.playbackAnchor) {
        practiceScrollerState.playbackAnchor.step = recycleStep(practiceScrollerState.playbackAnchor.step);
    }
    practiceScrollerState.playbackEvents.forEach(function (playbackEvent) {
        playbackEvent.step = recycleStep(playbackEvent.step);
    });
}

function updatePracticeScrollerPlayback(playbackStep, delayMs) {
    const stepNumber = normalizePracticeScrollerPlaybackStep(playbackStep);
    const eventTime = window.performance.now() + Math.max(0, Number(delayMs) || 0) + practiceState.audioLatencyMs;
    const matchingEvent = practiceScrollerState.playbackEvents.find(function (playbackEvent) {
        return playbackEvent.step === stepNumber && Math.abs(playbackEvent.time - eventTime) < 40;
    });

    if (matchingEvent) {
        matchingEvent.time = eventTime;
        return;
    }

    if (!practiceScrollerState.playbackAnchor) {
        practiceScrollerState.playbackAnchor = {
            step: practiceScrollerState.currentStep,
            time: window.performance.now()
        };
    }

    practiceScrollerState.playbackEvents.push({
        step: stepNumber,
        time: eventTime
    });
    practiceScrollerState.playbackEvents.sort(function (a, b) {
        return a.time - b.time;
    });

    if (practiceScrollerState.animationFrameId === null) {
        practiceScrollerState.animationFrameId = window.requestAnimationFrame(runPracticeScrollerAnimation);
    }
}

function startPracticeScrollerLeadIn(leadInMs) {
    const safeLeadInMs = Math.max(0, Number(leadInMs) || 0) + practiceState.audioLatencyMs;
    stopPracticeScrollerAnimation();
    practiceScrollerState.playbackEvents = [];
    practiceScrollerState.visualLeadInSteps = getPracticeScrollerLeadInSteps(safeLeadInMs);
    practiceScrollerState.playbackAnchor = {
        step: -practiceScrollerState.visualLeadInSteps,
        time: window.performance.now()
    };
    updatePracticeScrollerPosition(practiceScrollerState.playbackAnchor.step);

    if (safeLeadInMs <= 0) {
        return;
    }

    practiceScrollerState.playbackEvents.push({
        step: 0,
        time: practiceScrollerState.playbackAnchor.time + safeLeadInMs
    });
    practiceScrollerState.animationFrameId = window.requestAnimationFrame(runPracticeScrollerAnimation);
}

function updatePracticeScrollerState(nextState, leadInMs) {
    const scrollerEl = document.getElementById('practiceScroller');
    if (!scrollerEl) {
        return;
    }
    scrollerEl.dataset.playbackState = nextState || '';
    if (nextState === 'playing') {
        startPracticeScrollerLeadIn(leadInMs);
        return;
    }

    if (nextState === 'ended') {
        if (practiceScrollerState.playbackEvents.length > 0 &&
                practiceScrollerState.animationFrameId === null) {
            practiceScrollerState.animationFrameId = window.requestAnimationFrame(runPracticeScrollerAnimation);
        }
        return;
    }

    if (nextState === 'stopped') {
        practiceScrollerState.playbackTimers.forEach(function (playbackTimer) {
            window.clearTimeout(playbackTimer);
        });
        practiceScrollerState.playbackTimers = [];
        stopPracticeScrollerAnimation();
        practiceScrollerState.playbackEvents = [];
        practiceScrollerState.playbackAnchor = null;
        closePracticeInstrumentVolumePopover();
        updatePracticeScrollerPosition(-practiceScrollerState.visualLeadInSteps);
    }
}

function clearPracticeScrollerPlayback() {
    practiceScrollerState.playbackTimers.forEach(function (playbackTimer) {
        window.clearTimeout(playbackTimer);
    });
    practiceScrollerState.playbackTimers = [];
    stopPracticeScrollerAnimation();
    practiceScrollerState.playbackEvents = [];
    practiceScrollerState.playbackAnchor = null;
    closePracticeInstrumentVolumePopover();
    updatePracticeScrollerState('stopped');
}

function refreshPracticeFromSheet(forceReset) {
    const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
    if (forceReset) {
        syncTimelineStateFromReadResult(readResult);
    } else {
        syncTimelineStateFromReadResultIfNeeded(readResult, buildCurrentTimelineSyncOptions());
    }
    syncPracticeSelectionsWithPatternLibrary();
    renderPracticePanel();
}
