// Übungsmodus: deterministischer Loop-Trainer aus der Pattern-Bibliothek
const practiceState = {
    visible: false,
    accompanimentPatternIds: [],
    soloPatternIds: [],
    loopsWithoutSolo: 1,
    loopsWithSolo: 1,
    repeatCount: 4,
    accompanimentStart: 'immediate',
    defaultsApplied: false,
    defaultSelectionSourceHash: ''
};

const practiceTrackInstrumentNames = ['Kenkeni', 'Sangban', 'Doundoun', 'Dreierbass', 'Djembe_1', 'Djembe_2', 'Djembe_3'];

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
        loopsWithoutSolo: practiceState.loopsWithoutSolo,
        loopsWithSolo: practiceState.loopsWithSolo,
        repeatCount: practiceState.repeatCount,
        accompanimentPatternIds: practiceState.accompanimentPatternIds.slice(),
        soloPatternIds: practiceState.soloPatternIds.slice(),
        accompanimentPatternSourceKeys: getPracticePatternSourceKeys(practiceState.accompanimentPatternIds),
        soloPatternSourceKeys: getPracticePatternSourceKeys(practiceState.soloPatternIds)
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
    practiceState.loopsWithoutSolo = normalizePracticeCount(metadata.loopsWithoutSolo, 1, 0, 32);
    practiceState.loopsWithSolo = normalizePracticeCount(metadata.loopsWithSolo, 1, 1, 32);
    practiceState.repeatCount = normalizePracticeCount(metadata.repeatCount, 4, 1, 64);
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
    practiceState.defaultsApplied = true;
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
}

function ensurePracticeDefaultSelections() {
    if (practiceState.defaultsApplied ||
            practiceState.accompanimentPatternIds.length > 0 ||
            practiceState.soloPatternIds.length > 0) {
        return;
    }

    practiceState.accompanimentPatternIds = timelineState.sourcePatterns
        .filter(function (pattern) {
            return pattern && pattern.labelType === 'Begleitung';
        })
        .map(function (pattern) {
            return pattern.id;
        });
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
    return rowEl;
}

function renderPracticePatternList(containerId, listName) {
    const containerEl = document.getElementById(containerId);
    if (!containerEl) {
        return;
    }

    containerEl.innerHTML = '';
    timelineState.sourcePatterns.forEach(function (pattern) {
        containerEl.appendChild(createPracticePatternRow(pattern, listName));
    });
}

function updatePracticeInputs() {
    const withoutSoloEl = document.getElementById('practiceWithoutSoloLoops');
    const withSoloEl = document.getElementById('practiceWithSoloLoops');
    const repeatEl = document.getElementById('practiceRepeatCount');
    const accompanimentStartEl = document.getElementById('practiceAccompanimentStart');

    if (withoutSoloEl) {
        withoutSoloEl.value = practiceState.loopsWithoutSolo;
    }
    if (withSoloEl) {
        withSoloEl.value = practiceState.loopsWithSolo;
    }
    if (repeatEl) {
        repeatEl.value = practiceState.repeatCount;
    }
    if (accompanimentStartEl) {
        accompanimentStartEl.value = practiceState.accompanimentStart;
    }
}

function renderPracticePanel() {
    const panelEl = document.getElementById('practicePanel');
    const statusEl = document.getElementById('practiceStatus');
    if (!panelEl || !statusEl) {
        return;
    }

    syncPracticeSelectionsWithPatternLibrary();
    ensurePracticeDefaultSelections();
    panelEl.hidden = !practiceState.visible;

    const accompanimentCount = practiceState.accompanimentPatternIds.length;
    const soloCount = practiceState.soloPatternIds.length;
    const startTextByMode = {
        immediate: 'Start sofort',
        afterCall: 'Start nach Call',
        afterIntro: 'Start nach Intro',
        afterCallIntro: 'Start nach Call + Intro'
    };
    const startText = startTextByMode[practiceState.accompanimentStart] || startTextByMode.immediate;
    statusEl.textContent = timelineState.sourcePatterns.length + ' Pattern aus dem Blatt, ' +
        accompanimentCount + ' Begleitung(en), ' +
        soloCount + ' Solo/Soli, ' +
        startText + '.';

    updatePracticeInputs();

    if (panelEl.hidden) {
        return;
    }

    renderPracticePatternList('practiceAccompanimentList', 'accompanimentPatternIds');
    renderPracticePatternList('practiceSoloList', 'soloPatternIds');
}

function createPracticeEntry(pattern, parallelGroupId, blockId) {
    return {
        id: 'practice-entry-' + blockId + '-' + pattern.id,
        blockId: blockId,
        parallelGroupId: parallelGroupId,
        patternId: pattern.id,
        patternSourceKey: pattern.sourceKey,
        handMode: pattern.instrument === 'Djembe' ? 'auto' : '',
        swingFactor: null,
        targetInstruments: Array.isArray(pattern.defaultTargets) ? pattern.defaultTargets.slice() : []
    };
}

function addPracticeParallelGroup(entries, patterns, blockIndex) {
    const safePatterns = patterns.filter(Boolean);
    if (safePatterns.length === 0) {
        return;
    }

    const parallelGroupId = 'practice-group-' + blockIndex;
    const blockId = 'practice-block-' + blockIndex;
    safePatterns.forEach(function (pattern) {
        entries.push(createPracticeEntry(pattern, parallelGroupId, blockId));
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

function buildPracticeEntries() {
    const accompanimentPatterns = getPracticePatternsByIds(practiceState.accompanimentPatternIds);
    const soloPatterns = getPracticePatternsByIds(practiceState.soloPatternIds);
    const entries = [];
    let blockIndex = 1;

    if (practiceState.accompanimentStart !== 'immediate') {
        getPracticeLeadInPatterns().forEach(function (leadInPattern) {
            addPracticeParallelGroup(entries, [leadInPattern], blockIndex);
            blockIndex += 1;
        });
    }

    for (let cycleIndex = 0; cycleIndex < practiceState.repeatCount; cycleIndex += 1) {
        for (let loopIndex = 0; loopIndex < practiceState.loopsWithoutSolo; loopIndex += 1) {
            addPracticeParallelGroup(entries, accompanimentPatterns, blockIndex);
            blockIndex += 1;
        }

        if (soloPatterns.length === 0) {
            continue;
        }

        soloPatterns.forEach(function (soloPattern) {
            for (let loopIndex = 0; loopIndex < practiceState.loopsWithSolo; loopIndex += 1) {
                addPracticeParallelGroup(entries, accompanimentPatterns.concat([soloPattern]), blockIndex);
                blockIndex += 1;
            }
        });
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

function buildPracticeSectionsFromEntries(entries) {
    return buildPracticeBlocksFromEntries(entries).map(function (block, blockIndex) {
        const section = {
            id: block.id,
            label: 'Begleitung',
            labelName: '',
            runtimeKey: 'practice-js::' + block.id + '::' + blockIndex,
            swingFactor: null,
            trackNotes: createEmptyPracticeTrackNotes(),
            trackHandModes: createEmptyPracticeTrackHandModes()
        };
        const labels = [];
        const labelNames = [];

        block.entries.forEach(function (entry) {
            const pattern = findPatternById(entry.patternId);
            if (!pattern) {
                return;
            }

            const patternNotes = flattenPracticePatternNotes(pattern);
            const targetInstruments = normalizePracticeTargetInstruments(entry.targetInstruments);
            const label = pattern.labelType || pattern.label || '';
            const labelName = pattern.labelName || pattern.name || label;

            if (label && labels.indexOf(label) === -1) {
                labels.push(label);
            }
            if (labelName && labelNames.indexOf(labelName) === -1) {
                labelNames.push(labelName);
            }

            targetInstruments.forEach(function (instrumentName) {
                section.trackNotes[instrumentName] = mergePracticeNotesIntoTrack(
                    section.trackNotes[instrumentName],
                    patternNotes
                );
                if (instrumentName.indexOf('Djembe_') === 0) {
                    section.trackHandModes[instrumentName] = entry.handMode || '';
                }
            });

            if (section.swingFactor === null && entry.swingFactor !== null && entry.swingFactor !== undefined) {
                section.swingFactor = entry.swingFactor;
            }
        });

        section.label = labels.indexOf('Begleitung') !== -1 ? 'Begleitung' : (labels[0] || 'Begleitung');
        section.labelName = labelNames.join(' + ') || section.label;
        return section;
    }).filter(function (section) {
        return practiceTrackInstrumentNames.some(function (instrumentName) {
            return Array.isArray(section.trackNotes[instrumentName]) && section.trackNotes[instrumentName].length > 0;
        });
    });
}

function buildPracticePlayerPayload() {
    if (!Array.isArray(timelineState.sourcePatterns) || timelineState.sourcePatterns.length === 0) {
        throw new Error('Es wurden noch keine Pattern aus dem Notenblatt gelesen.');
    }
    if (practiceState.accompanimentPatternIds.length === 0 && practiceState.soloPatternIds.length === 0) {
        throw new Error('Bitte mindestens ein Begleit- oder Solo-Pattern für den Übungsmodus auswählen.');
    }

    const entries = buildPracticeEntries();
    if (entries.length === 0) {
        throw new Error('Aus den Übungseinstellungen konnte keine Abspielfolge erzeugt werden.');
    }

    const payload = buildTimelinePlayerPayload(timelineState.sourcePatterns, entries);
    if (payload[0]) {
        payload[0].PracticeMode = true;
        payload[0].PracticeBlocks = buildPracticeBlocksFromEntries(entries);
        payload[0].PracticeSections = buildPracticeSectionsFromEntries(entries);
        payload[0].TimelineLoop = false;
        payload[0].TimelineLoopCount = false;
    }
    return payload;
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
