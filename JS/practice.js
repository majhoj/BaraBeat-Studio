// Übungsmodus: deterministischer Loop-Trainer aus der Pattern-Bibliothek
const practiceState = {
    visible: false,
    accompanimentPatternIds: [],
    soloPatternIds: [],
    loopsWithoutSolo: 1,
    loopsWithSolo: 1,
    repeatCount: 4,
    accompanimentStart: 'immediate',
    audioLatencyMs: 30,
    patternChooserExpanded: false,
    defaultsApplied: false,
    defaultSelectionSourceHash: ''
};

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
    stepsPerBar: 32,
    stepWidth: 18,
    playheadRatio: 0.3,
    leadInSteps: 8,
    visualLeadInSteps: 8,
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
        audioLatencyMs: practiceState.audioLatencyMs,
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
    practiceState.audioLatencyMs = normalizePracticeAudioLatency(metadata.audioLatencyMs);
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
    const accompanimentStartEl = document.getElementById('practiceAccompanimentStart');
    const audioLatencyEl = document.getElementById('practiceAudioLatency');
    const audioLatencyRangeEl = document.getElementById('practiceAudioLatencyRange');

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
    if (audioLatencyEl) {
        audioLatencyEl.value = practiceState.audioLatencyMs;
    }
    if (audioLatencyRangeEl) {
        audioLatencyRangeEl.value = practiceState.audioLatencyMs;
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
    if (!panelEl || !titleEl) {
        return;
    }

    syncPracticeSelectionsWithPatternLibrary();
    ensurePracticeDefaultSelections();
    panelEl.hidden = !practiceState.visible;
    document.body.classList.toggle('is-practice-mode-visible', practiceState.visible);
    titleEl.textContent = 'Übungsmodus: ' + getPracticeRhythmDisplayName();

    updatePracticeInputs();
    if (chooserEl) {
        chooserEl.hidden = !practiceState.patternChooserExpanded;
    }
    if (chooserToggleEl) {
        chooserToggleEl.textContent = 'Patternauswahl';
        chooserToggleEl.setAttribute('aria-expanded', practiceState.patternChooserExpanded ? 'true' : 'false');
        chooserToggleEl.classList.toggle('is-active', practiceState.patternChooserExpanded);
    }

    if (panelEl.hidden) {
        return;
    }

    if (practiceState.patternChooserExpanded) {
        renderPracticePatternList('practiceAccompanimentList', 'accompanimentPatternIds');
        renderPracticePatternList('practiceSoloList', 'soloPatternIds');
    }
}

function createPracticeEntry(pattern, parallelGroupId, blockId) {
    return {
        id: 'practice-entry-' + blockId + '-' + pattern.id,
        blockId: blockId,
        parallelGroupId: parallelGroupId,
        patternId: pattern.id,
        patternSourceKey: pattern.sourceKey,
        isPracticeTarget: practiceState.soloPatternIds.indexOf(pattern.id) !== -1,
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
            isPracticeTarget: Boolean(entry.isPracticeTarget),
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
            trackHandModes: createEmptyPracticeTrackHandModes(),
            practiceTargetInstruments: []
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
                if (entry.isPracticeTarget && section.practiceTargetInstruments.indexOf(instrumentName) === -1) {
                    section.practiceTargetInstruments.push(instrumentName);
                }
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
        throw new Error('Bitte mindestens ein Begleit- oder Übungsteil-Pattern für den Übungsmodus auswählen.');
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
    let stepOffset = 0;

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

        sectionBoundaries.push({
            step: stepOffset,
            label: section.labelName || section.label || ''
        });

        practiceTrackInstrumentNames.forEach(function (instrumentName) {
            const notes = section && section.trackNotes && Array.isArray(section.trackNotes[instrumentName])
                ? section.trackNotes[instrumentName]
                : [];
            const isPracticeTarget = section &&
                Array.isArray(section.practiceTargetInstruments) &&
                section.practiceTargetInstruments.indexOf(instrumentName) !== -1;
            for (let stepIndex = 0; stepIndex < sectionLength; stepIndex += 1) {
                trackNotes[instrumentName].push(notes[stepIndex] || 'f');
                targetSteps[instrumentName].push(Boolean(isPracticeTarget));
            }
        });

        stepOffset += sectionLength;
    });

    return {
        trackNotes: trackNotes,
        targetSteps: targetSteps,
        sectionBoundaries: sectionBoundaries,
        totalSteps: stepOffset
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

        notes.forEach(function (noteValue, stepIndex) {
            laneEl.appendChild(createPracticeScrollerCell(noteValue, stepIndex, stepsPerBar, targetSteps[stepIndex]));
        });

        laneWrapEl.appendChild(laneEl);
        rowEl.append(labelEl, laneWrapEl);
        rowsEl.appendChild(rowEl);
    });

    updatePracticeScrollerPosition(-practiceScrollerState.visualLeadInSteps);
    statusEl.textContent = Math.ceil(flattened.totalSteps / stepsPerBar) + ' Takte';
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
    const safeStep = Math.max(minVisualStep, Math.min(practiceScrollerState.totalSteps - 1, Number(playbackStep) || 0));
    const activeStep = Math.max(0, Math.min(practiceScrollerState.totalSteps - 1, Math.round(safeStep)));
    const cellRect = firstCellEl ? firstCellEl.getBoundingClientRect() : null;
    const stepWidth = cellRect && cellRect.width > 0 ? cellRect.width : practiceScrollerState.stepWidth;
    const playheadX = stageEl ? stageEl.clientWidth * practiceScrollerState.playheadRatio : 0;
    const laneStartX = firstLaneWrapEl ? firstLaneWrapEl.offsetLeft : 0;
    const laneOffset = playheadX - laneStartX - (safeStep * stepWidth) - (stepWidth / 2);

    practiceScrollerState.currentStep = safeStep;
    practiceScrollerState.stepWidth = stepWidth;
    scrollerEl.style.setProperty('--practice-scroller-offset', laneOffset + 'px');

    if (practiceScrollerState.activeStep !== activeStep) {
        scrollerEl.querySelectorAll('.practice-scroller-cell.is-current').forEach(function (cellEl) {
            cellEl.classList.remove('is-current');
        });
        scrollerEl.querySelectorAll('.practice-scroller-lane').forEach(function (laneEl) {
            if (laneEl.children[activeStep]) {
                laneEl.children[activeStep].classList.add('is-current');
            }
        });
        practiceScrollerState.activeStep = activeStep;

        if (statusEl) {
            statusEl.textContent = 'Takt ' + (Math.floor(activeStep / practiceScrollerState.stepsPerBar) + 1) +
                ', Schritt ' + ((activeStep % practiceScrollerState.stepsPerBar) + 1);
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

    if (anchor && nextEvent && nextEvent.time > anchor.time) {
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

function updatePracticeScrollerPlayback(playbackStep, delayMs) {
    const stepNumber = Math.max(0, Math.floor(Number(playbackStep) || 0));
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
