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
    audioLatencyMs: 30,
    h2hRestMute: false,
    patternHandModes: {},
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
    playbackAnchor: null
};

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

function normalizePracticeTimerMinutes(rawValue) {
    return normalizePracticeCount(rawValue, 0, 0, practiceTimerMinutesMax);
}

function normalizePracticeHandMode(rawValue) {
    return rawValue === 'h2h' || rawValue === 'hoh' ? rawValue : 'auto';
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
    return {
        version: 1,
        sourceHash: timelineState.sourceHash,
        accompanimentStart: practiceState.accompanimentStart,
        accompanimentBetweenPatterns: practiceState.accompanimentBetweenPatterns,
        audioLatencyMs: practiceState.audioLatencyMs,
        h2hRestMute: practiceState.h2hRestMute,
        loopsWithoutSolo: practiceState.loopsWithoutSolo,
        loopsWithSolo: practiceState.loopsWithSolo,
        repeatCount: practiceState.repeatCount,
        timerMinutes: practiceState.timerMinutes,
        accompanimentPatternIds: practiceState.accompanimentPatternIds.slice(),
        soloPatternIds: practiceState.soloPatternIds.slice(),
        accompanimentPatternSourceKeys: getPracticePatternSourceKeys(practiceState.accompanimentPatternIds),
        soloPatternSourceKeys: getPracticePatternSourceKeys(practiceState.soloPatternIds),
        patternHandModes: Object.assign({}, practiceState.patternHandModes),
        patternHandModesBySourceKey: getPracticePatternHandModesBySourceKey(practiceState.patternHandModes)
    };
}

function resetPracticeForSource(sourceHash) {
    practiceState.accompanimentPatternIds = [];
    practiceState.soloPatternIds = [];
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
    practiceState.audioLatencyMs = normalizePracticeAudioLatency(metadata.audioLatencyMs);
    practiceState.h2hRestMute = Boolean(metadata.h2hRestMute);
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
        selectedIds.push(patternId);
    }
    if (!selected && currentIndex !== -1) {
        selectedIds.splice(currentIndex, 1);
    }
}

function createPracticePatternRow(pattern, listName) {
    const rowEl = document.createElement('label');
    rowEl.className = 'practice-pattern-row';

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
    titleEl.textContent = pattern.name || pattern.labelName || pattern.id;

    const metaEl = document.createElement('span');
    metaEl.className = 'practice-pattern-meta';
    metaEl.textContent = (pattern.sourceInstrument || pattern.instrument || '') + ' · ' + (pattern.labelName || pattern.labelType || '');

    rowEl.append(inputEl, titleEl, metaEl);

    if (listName === 'soloPatternIds' && pattern.instrument === 'Djembe') {
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
            if (selectedMode === 'auto') {
                delete practiceState.patternHandModes[pattern.id];
            } else {
                practiceState.patternHandModes[pattern.id] = selectedMode;
            }
            notifyPracticeHandModeChanged();
        });
        rowEl.appendChild(handModeEl);
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
    timelineState.sourcePatterns.filter(function (pattern) {
        return isPracticePatternVisibleInList(pattern, listName);
    }).forEach(function (pattern) {
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
    if (rawTitle && rawTitle !== 'Enter the name of the Rhythm') {
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
        swingFactor: null,
        targetInstruments: Array.isArray(pattern.defaultTargets) ? pattern.defaultTargets.slice() : []
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

function getPracticeLeadInPatterns() {
    const allowedLabelsByStartMode = {
        afterCall: ['Call'],
        afterIntro: ['Intro'],
        afterCallIntro: ['Call', 'Intro']
    };
    const allowedLabels = allowedLabelsByStartMode[practiceState.accompanimentStart] || [];
    return timelineState.sourcePatterns.filter(function (pattern) {
        return pattern && allowedLabels.indexOf(pattern.labelType) !== -1;
    });
}

function buildPracticeEntries(options) {
    const buildOptions = options || {};
    const repeatCycles = buildOptions.singleCycle
        ? 1
        : practiceState.repeatCount;
    const accompanimentPatterns = getPracticePatternsByIds(practiceState.accompanimentPatternIds);
    const soloPatterns = getPracticePatternsByIds(practiceState.soloPatternIds);
    const entries = [];
    const startsAfterLeadIn = practiceState.accompanimentStart !== 'immediate';
    const initialLoopsWithoutSolo = startsAfterLeadIn && soloPatterns.length > 0
        ? Math.max(0, practiceState.loopsWithoutSolo - 1)
        : practiceState.loopsWithoutSolo;
    const trailingLoopsWithoutSolo = startsAfterLeadIn && soloPatterns.length > 0
        ? practiceState.loopsWithoutSolo - initialLoopsWithoutSolo
        : 0;
    let blockIndex = 1;

    if (startsAfterLeadIn) {
        getPracticeLeadInPatterns().forEach(function (leadInPattern) {
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

        if (soloPatterns.length === 0) {
            continue;
        }

        soloPatterns.forEach(function (soloPattern, soloPatternIndex) {
            addPracticeParallelGroup(entries, accompanimentPatterns.concat([soloPattern]), blockIndex, practiceState.loopsWithSolo);
            blockIndex += 1;
            if (practiceState.accompanimentBetweenPatterns && soloPatternIndex < soloPatterns.length - 1) {
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
    return (pattern && Array.isArray(pattern.bars) ? pattern.bars : []).reduce(function (allNotes, bar) {
        if (bar && Array.isArray(bar.notes)) {
            return allNotes.concat(bar.notes);
        }
        return allNotes;
    }, []);
}

function getPracticePatternControlStep(pattern, controlType) {
    const bars = pattern && Array.isArray(pattern.bars) ? pattern.bars : [];
    let stepOffset = 0;

    for (let barIndex = 0; barIndex < bars.length; barIndex += 1) {
        const bar = bars[barIndex];
        const notes = bar && Array.isArray(bar.notes) ? bar.notes : [];
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
            return stepOffset + Math.min(controlStep, notes.length);
        }

        stepOffset += notes.length;
    }

    return null;
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
        return 9;
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
    const pickupStep = stepsPerBar > 0 ? safeInStep % stepsPerBar : safeInStep;
    const pickupNotes = Array(Math.max(stepsPerBar, pickupStep + 1)).fill('f');
    pickupNotes[pickupStep] = safeNotes[safeInStep] || 'f';
    return pickupNotes;
}

function mergePracticeNotesIntoTrack(targetNotes, sourceNotes) {
    const mergedNotes = Array.isArray(targetNotes) ? targetNotes.slice() : [];
    const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes : [];
    safeSourceNotes.forEach(function (noteValue, noteIndex) {
        while (mergedNotes.length <= noteIndex) {
            mergedNotes.push('f');
        }
        if (noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '') {
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
        if (noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '') {
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
            finalRepeatOutSteps: createEmptyPracticeTrackStepMap(),
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
        finalRepeatOutSteps: createEmptyPracticeTrackStepMap(),
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

function sectionHasPracticeNotes(section) {
    return practiceTrackInstrumentNames.some(function (instrumentName) {
        return Array.isArray(section.trackNotes[instrumentName]) && section.trackNotes[instrumentName].length > 0;
    });
}

function mergePracticePickupIntoHostSection(hostSection, pickupSection) {
    const hostLength = getPracticeSectionLength(hostSection);
    const stepsPerBar = getPracticeStepsPerBar();
    const pickupOffset = Math.max(0, hostLength - stepsPerBar);

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
    });
}

function clearPracticeSectionOutSteps(section) {
    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        section.finalRepeatOutSteps[instrumentName] = null;
    });
}

function appendPracticeSectionWithPickup(sections, section, pickupSection, hasPickup) {
    if (!hasPickup || !sectionHasPracticeNotes(pickupSection)) {
        sections.push(section);
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
        sections.push(section);
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
        section.repeatCount = 1;
        sections.push(repeatedSection);
    }
    sections.push(section);
    return pickupWasPlacedInLeadIn ? pickupSection : null;
}

function buildPracticeSectionsFromEntries(entries) {
    const sections = [];
    let loopStartPickupSection = null;
    buildPracticeBlocksFromEntries(entries).forEach(function (block, blockIndex) {
        const section = createPracticeSection(block, blockIndex, '');
        section.repeatCount = normalizePracticeCount(block.repeatCount, 1, 1, 32);
        section.isLeadIn = Boolean(block.isLeadIn);
        const pickupSection = createPracticeSection(block, blockIndex, '::pickup');
        pickupSection.id = block.id + '-pickup';
        pickupSection.repeatCount = 1;
        pickupSection.isLeadIn = true;
        const labels = [];
        const labelNames = [];
        const blockHasPickup = !section.isLeadIn && block.entries.some(function (entry) {
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
    });

    if (loopStartPickupSection && (practiceState.repeatCount > 1 || practiceState.timerMinutes > 0)) {
        for (let sectionIndex = sections.length - 1; sectionIndex >= 0; sectionIndex -= 1) {
            if (!sections[sectionIndex].isLeadIn && sectionHasPracticeNotes(sections[sectionIndex])) {
                mergePracticePickupIntoHostSection(sections[sectionIndex], loopStartPickupSection);
                break;
            }
        }
    }

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
        return 9;
    }
    return 32;
}

function getPracticeScrollerBaseStepMs() {
    const tempo = typeof normalizeTimelineTempo === 'function'
        ? normalizeTimelineTempo(timelineState.tempo)
        : 100;
    const stepSeconds = rhythm === 'neunaer' ? 15 / tempo : 7.5 / tempo;
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
    const trackNotes = createEmptyPracticeTrackNotes();
    const targetSteps = createEmptyPracticeTrackFlags();
    const sectionBoundaries = [];
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
        const renderRepeatCount = Math.min(repeatCount, practiceScrollerState.visualLoopCopies);
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
            const notes = section && section.trackNotes && Array.isArray(section.trackNotes[instrumentName])
                ? section.trackNotes[instrumentName]
                : [];
            const isPracticeTarget = section &&
                Array.isArray(section.practiceTargetInstruments) &&
                section.practiceTargetInstruments.indexOf(instrumentName) !== -1;
            const outStep = finalRepeatOutSteps[instrumentName];
            const safeOutStep = outStep === null || outStep === undefined
                ? null
                : Math.max(0, Number(outStep) || 0);
            for (let repeatIndex = 0; repeatIndex < renderRepeatCount; repeatIndex += 1) {
                for (let stepIndex = 0; stepIndex < sectionLength; stepIndex += 1) {
                    const shouldMuteForOut = safeOutStep !== null &&
                        repeatIndex === repeatCount - 1 &&
                        stepIndex > safeOutStep;
                    trackNotes[instrumentName].push(shouldMuteForOut ? 'f' : (notes[stepIndex] || 'f'));
                    targetSteps[instrumentName].push(Boolean(isPracticeTarget));
                }
            }
        });

        stepOffset += sectionLength * renderRepeatCount;
        playbackOffset += sectionLength * repeatCount;
    });

    const visualCycleSteps = stepOffset;
    const safeLoopStartStep = loopStartStep === null ? 0 : loopStartStep;
    const safePlaybackLoopStart = playbackLoopStart === null ? 0 : playbackLoopStart;
    const visualLoopLength = Math.max(0, visualCycleSteps - safeLoopStartStep);
    const playbackLoopLength = Math.max(0, playbackOffset - safePlaybackLoopStart);
    const baseTrackNotes = {};
    const baseTargetSteps = {};
    practiceTrackInstrumentNames.forEach(function (instrumentName) {
        baseTrackNotes[instrumentName] = trackNotes[instrumentName].slice(safeLoopStartStep);
        baseTargetSteps[instrumentName] = targetSteps[instrumentName].slice(safeLoopStartStep);
    });

    for (let copyIndex = 0; copyIndex < practiceScrollerState.visualLoopCopies; copyIndex += 1) {
        practiceTrackInstrumentNames.forEach(function (instrumentName) {
            trackNotes[instrumentName] = trackNotes[instrumentName].concat(baseTrackNotes[instrumentName]);
            targetSteps[instrumentName] = targetSteps[instrumentName].concat(baseTargetSteps[instrumentName]);
        });
    }

    return {
        trackNotes: trackNotes,
        targetSteps: targetSteps,
        sectionBoundaries: sectionBoundaries,
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
        totalSteps: visualCycleSteps + (visualLoopLength * practiceScrollerState.visualLoopCopies)
    };
}

function createPracticeScrollerCell(noteValue, stepIndex, stepsPerBar, isPracticeTarget) {
    const cellEl = document.createElement('span');
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
    if (stepsPerBar > 0 && stepIndex % stepsPerBar === 0) {
        cellEl.classList.add('is-bar-start');
        const barNumberEl = document.createElement('span');
        barNumberEl.className = 'practice-bar-number';
        barNumberEl.textContent = String(Math.floor(stepIndex / stepsPerBar) + 1);
        cellEl.appendChild(barNumberEl);
    }
    return cellEl;
}

function getPracticeScrollerPreRollLineSteps() {
    return Math.max(practiceScrollerState.preRollLineSteps, practiceScrollerState.stepsPerBar);
}

function appendPracticeScrollerCells(laneEl, notes, targetSteps, visualStepOffset, stepsPerBar) {
    const safeNotes = Array.isArray(notes) ? notes : [];
    const safeTargetSteps = Array.isArray(targetSteps) ? targetSteps : [];
    safeNotes.forEach(function (noteValue, stepIndex) {
        laneEl.appendChild(createPracticeScrollerCell(
            noteValue,
            visualStepOffset + stepIndex,
            stepsPerBar,
            safeTargetSteps[stepIndex]
        ));
    });
}

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

    const flattened = flattenPracticeScrollerSections(sections);
    const stepsPerBar = getPracticeScrollerStepsPerBar();
    practiceScrollerState.totalSteps = flattened.totalSteps;
    practiceScrollerState.loopSegments = flattened.loopSegments;
    practiceScrollerState.loopLength = flattened.loopLength;
    practiceScrollerState.loopStartStep = flattened.loopStartStep;
    practiceScrollerState.visualLoopLength = flattened.visualLoopLength;
    practiceScrollerState.playbackSegments = flattened.playbackSegments;
    practiceScrollerState.playbackTotalSteps = flattened.playbackTotalSteps;
    practiceScrollerState.playbackLoopStart = flattened.playbackLoopStart;
    practiceScrollerState.playbackLoopLength = flattened.playbackLoopLength;
    practiceScrollerState.visualCycleSteps = flattened.visualCycleSteps;
    practiceScrollerState.visualTotalSteps = flattened.totalSteps;
    practiceScrollerState.stepsPerBar = stepsPerBar;
    practiceScrollerState.currentStep = 0;
    practiceScrollerState.activeStep = -1;

    rowsEl.innerHTML = '';
    scrollerEl.hidden = flattened.totalSteps === 0;

    if (flattened.totalSteps === 0) {
        statusEl.textContent = 'Keine Noten';
        return;
    }

    practiceTrackInstrumentNames.forEach(function (instrumentName) {
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
        labelEl.textContent = practiceScrollerInstrumentLabels[instrumentName] || instrumentName;

        const laneWrapEl = document.createElement('div');
        laneWrapEl.className = 'practice-scroller-lane-wrap';

        const laneEl = document.createElement('div');
        laneEl.className = 'practice-scroller-lane';

        const preRollLineSteps = getPracticeScrollerPreRollLineSteps();
        for (let leadInIndex = 0; leadInIndex < preRollLineSteps; leadInIndex += 1) {
            laneEl.appendChild(createPracticeScrollerCell('f', leadInIndex, stepsPerBar, false));
        }

        appendPracticeScrollerCells(laneEl, notes, targetSteps, preRollLineSteps, stepsPerBar);

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
        ? Math.floor((rawStep - playbackTotalSteps) / playbackLoopLength) % practiceScrollerState.visualLoopCopies
        : 0;
    const baseVisualStart = rawStep >= playbackTotalSteps && visualLoopLength > 0
        ? visualLoopStart + (cycleIndex * visualLoopLength)
        : 0;
    const segmentVisualStart = rawStep >= playbackTotalSteps
        ? Math.max(0, matchedSegment.visualStart - practiceScrollerState.loopStartStep)
        : matchedSegment.visualStart;
    return baseVisualStart + segmentVisualStart + (localPlaybackStep % matchedSegment.visualLength);
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
    const safeStep = Math.max(minVisualStep, Math.min(maxVisualStep, Number(playbackStep) || 0));
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

    practiceScrollerState.currentStep = safeStep;
    practiceScrollerState.stepWidth = stepWidth;
    scrollerEl.style.setProperty('--practice-scroller-offset', laneOffset + 'px');

    if (practiceScrollerState.activeStep !== activeStep) {
        scrollerEl.querySelectorAll('.practice-scroller-cell.is-current').forEach(function (cellEl) {
            cellEl.classList.remove('is-current');
        });
        scrollerEl.querySelectorAll('.practice-scroller-lane').forEach(function (laneEl) {
            const activeChildIndex = activeStep + getPracticeScrollerPreRollLineSteps();
            if (laneEl.children[activeChildIndex]) {
                laneEl.children[activeChildIndex].classList.add('is-current');
            }
        });
        practiceScrollerState.activeStep = activeStep;

        if (statusEl) {
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
        if (!Number.isFinite(referenceStep) || referenceStep <= nextStep) {
            return referenceStep;
        }
        const loopDistance = referenceStep - nextStep;
        if (loopDistance < visualLoopLength) {
            return referenceStep;
        }
        return referenceStep - (Math.floor(loopDistance / visualLoopLength) * visualLoopLength);
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
    recyclePracticeScrollerVisualLoop(stepNumber);
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
