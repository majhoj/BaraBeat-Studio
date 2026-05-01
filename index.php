<?php
$jsSnap = @filemtime(__DIR__ . '/JS/snapNEU.svg.js') ?: 1;
$jsJq = @filemtime(__DIR__ . '/JS/jquery.min.js') ?: 1;
$jsSel = @filemtime(__DIR__ . '/JS/selection_drag_7.js') ?: 1;
$jsFn = @filemtime(__DIR__ . '/JS/functions.js') ?: 1;
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta name="viewport" content="initial-scale=1.0">
    <title><BaraBeat-Studio></title>
    <script src="JS/snapNEU.svg.js?v=<?php echo $jsSnap; ?>"></script>
    <script src="JS/jquery.min.js?v=<?php echo $jsJq; ?>"></script>
    <script src="JS/selection_drag_7.js?v=<?php echo $jsSel; ?>"></script>
    <script src="JS/functions.js?v=<?php echo $jsFn; ?>"></script>
    <style>
        #timelinePanel {
            position: fixed;
            top: 46px;
            left: 12px;
            right: 12px;
            z-index: 20;
            display: none;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid #777;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            padding: 14px;
            font-family: sans-serif;
            max-height: calc(100vh - 64px);
            overflow: auto;
        }

        .timeline-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .timeline-panel-title {
            font-size: 18px;
            font-weight: bold;
        }

        .timeline-panel-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .timeline-swing-control {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #333;
        }

        .timeline-swing-control input {
            width: 72px;
            padding: 4px 6px;
            font-size: 14px;
        }

        .timeline-tempo-control {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #333;
        }

        .timeline-tempo-control input {
            width: 72px;
            padding: 4px 6px;
            font-size: 14px;
        }

        .timeline-swing-profile {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #333;
            flex-wrap: wrap;
        }

        .timeline-swing-profile label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .timeline-swing-profile input {
            width: 52px;
            padding: 4px 6px;
            font-size: 13px;
        }

        .timeline-panel-body {
            display: grid;
            grid-template-columns: minmax(240px, 320px) 1fr;
            gap: 14px;
            align-items: start;
            min-height: 0;
        }

        .timeline-column {
            border: 1px solid #ccc;
            background: #fafafa;
            min-height: 180px;
            padding: 10px;
            max-height: calc(100vh - 170px);
            overflow: auto;
        }

        .timeline-column h3 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        .timeline-pattern-list,
        .timeline-sequence-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            min-height: 80px;
        }

        .timeline-card {
            border: 1px solid #666;
            background: white;
            padding: 8px;
            min-width: 180px;
            max-width: 240px;
            cursor: grab;
        }

        .timeline-card strong,
        .timeline-entry-main strong {
            display: block;
            margin-bottom: 4px;
        }

        .timeline-card small,
        .timeline-entry-main small {
            color: #444;
        }

        .timeline-card-actions,
        .timeline-entry-actions {
            display: flex;
            gap: 6px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .timeline-sequence-list {
            align-items: stretch;
            padding-bottom: 6px;
            flex-direction: column;
        }

        .timeline-entry {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            border: 2px solid #222;
            background: #f1f1f1;
            padding: 8px;
            min-width: 220px;
            max-width: 300px;
            flex: 0 0 260px;
        }

        .timeline-sequence-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            align-items: stretch;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .timeline-entry-main {
            flex: 1;
            min-width: 0;
        }

        .timeline-entry-targets {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            font-size: 13px;
        }

        .timeline-entry-targets label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .timeline-dropzone {
            border: 1px dashed #999;
            color: #666;
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            width: 100%;
            background: #fff;
        }

        .timeline-dropzone-inline {
            width: auto;
            min-width: 120px;
            flex: 0 0 auto;
        }

        .timeline-column-note {
            margin: 0 0 10px;
            color: #555;
            font-size: 13px;
        }
    </style>
</head>

<body style="margin-top: 20px;">
    <?php
    $file_name = $_GET["file"] ?? "";
    echo "<script>datei_name = " . json_encode($file_name) . ";</script>";
    ?>

    <div style="position: fixed; top: 0;">
        <form action="" name="uploadForm">
            <span id="auswahl" name="auswahl"></span>
            <input type="hidden" size="40" id="iofield" name="iofield" />
            <input type="button" id="button" value="Datei speichern" />
            <input type="button" id="button2" value="Als SVG speichern" />
            <input type="button" id="button3" value="Noten lesen" />
            <input type="button" id="button10" value="Audiotest" />
            <input type="button" id="button4" value="Binäres Notenblatt" />
            <input type="button" id="button5" value="Tenäres Notenblatt" />
            <input type="button" id="button8" value="Tenäres 9/8 Notenblatt" />
            <input type="button" id="button6" value="Scroll" />
            <input type="button" id="button7" value="Instrument" />
            <input type="button" id="button9" value="Funktion" />
            <input type="button" id="button11" value="Abschnittstimeline" />
        </form>
    </div>

    <div id="timelinePanel">
        <div class="timeline-panel-header">
            <div>
                <div class="timeline-panel-title">Abschnittstimeline</div>
                <div id="timelineStatus" style="font-size:13px; color:#555;">Pattern werden aus dem Notenblatt gelesen.</div>
            </div>
            <div class="timeline-panel-actions">
                <label class="timeline-tempo-control" for="timelineTempo">
                    Tempo
                    <input type="number" id="timelineTempo" min="30" max="180" step="1" value="100" />
                </label>
                <label class="timeline-swing-control" for="timelineSwingFactor">
                    Swing
                    <input type="number" id="timelineSwingFactor" min="0" max="100" step="1" value="0" />
                </label>
                <div class="timeline-swing-profile" id="timelineSwingProfile">
                    <span>Profil</span>
                    <label>S1 <input type="number" id="timelineSwingAnchor1" step="1" value="6" /></label>
                    <label>S2 <input type="number" id="timelineSwingAnchor2" step="1" value="-5" /></label>
                    <label>S3 <input type="number" id="timelineSwingAnchor3" step="1" value="6" /></label>
                    <label>S4 <input type="number" id="timelineSwingAnchor4" step="1" value="10" /></label>
                </div>
                <div class="timeline-swing-profile" id="timelineFeelProfile">
                    <span>Feel ms</span>
                    <label>Kk <input type="number" id="timelineFeelKenkeni" step="1" value="0" /></label>
                    <label>Sg <input type="number" id="timelineFeelSangban" step="1" value="0" /></label>
                    <label>Du <input type="number" id="timelineFeelDoundoun" step="1" value="0" /></label>
                    <label>Dr <input type="number" id="timelineFeelDreierbass" step="1" value="0" /></label>
                    <label>D1 <input type="number" id="timelineFeelDjembe1" step="1" value="0" /></label>
                    <label>D2 <input type="number" id="timelineFeelDjembe2" step="1" value="0" /></label>
                    <label>D3 <input type="number" id="timelineFeelDjembe3" step="1" value="0" /></label>
                </div>
                <button type="button" id="timelineRefreshButton">Aus Blatt aktualisieren</button>
                <button type="button" id="timelineCloseButton">Schließen</button>
            </div>
        </div>
        <div class="timeline-panel-body">
            <section class="timeline-column">
                <h3>Pattern-Bibliothek</h3>
                <p class="timeline-column-note">Jede Passage aus dem Notenblatt erscheint hier einmal. Ziehen oder mit <code>+</code> in die Timeline legen.</p>
                <div id="timelinePatternList" class="timeline-pattern-list"></div>
            </section>
            <section class="timeline-column">
                <h3>Timeline</h3>
                <p class="timeline-column-note">Die Timeline bestimmt die Wiedergabereihenfolge. Djembe-Zuordnung passiert hier.</p>
                <div id="timelineSequence" class="timeline-sequence-list"></div>
            </section>
        </div>
    </div>

    <script>
// Bearbeitungsfunktionen
var edit_title, edit_text;

// Layout- und Rasterzustand
var y = 172,
    paletteBaseY = 202,
    syllableIndex = 0,
    staffStartY = 172,
    gridSize = (850 / 34) / 2,
    gridSizeY = 2.5,
    gridSizeX = 29,
    repeatMarkerGridOffsetX = 24;

// Palette und Einfüge-Offsets
var paletteOriginX,
    paletteOriginY,
    paletteFrame,
    paletteGroup,
    paletteDragDeltaX = 0,
    paletteDragDeltaY = 0,
    paletteOffsetX = 0,
    paletteOffsetY = 0;

// Paletten-Elemente
var ton, bass, slap, flam_ton, flam_slap, flam_bass_slap, ton_g, slap_g, In, Out, text_z_g, repeatMarkerGroup;

// Geklonte Paletten-Elemente
var ton_c, bass_c, slap_c, flam_ton_c, flam_slap_c, flam_bass_slap_c, ton_g_c, slap_g_c, In_c, Out_c, repeatMarkerLegendClone;

// Touch-Status und geladener Titel
var textTouchStartX,
    textTouchStartY,
    textTouchEndX,
    textTouchEndY,
    loadedTitle = '';

// Temporäre Einfüge- und Hilfsvariablen
var x, insertedElement,
    slap_a, slap_b, flam_ton_a, flam_ton_b,
    slap_0, slap_a1, slap_a2, slap_b1, slap_b2,
    flam_bass_0, flam_bass,
    slap_a3, slap_a4, ton_g_a, ton_g_b,
    slap_a5, slap_a6, slap_g_b,
    in_c, in_a, in_b,
    out_c, out_a, out_b,
    textPaletteBox, textPaletteHorizontalLine, textPaletteVerticalLine;

// Wiederholungszeichen und Paletten-Positionen
var repeatMarkerHitbox,
    repeatMarkerDotTop,
    repeatMarkerDotBottom,
    repeatMarkerCountText,
    tx, ty, bx, by, sx, sy,
    ftx, fty, fsx, fsy,
    sgx, sgy, ix, iy, ox, oy, px, py;

// Einfüge- und Interaktionsfunktionen
var insertTone,
    insertBass,
    insertSlap,
    insertMuffledTone,
    insertMuffledSlap,
    insertFlamTone,
    insertFlamSlap,
    insertFlamBassSlap,
    insertInMarker,
    insertOutMarker,
    captureTextTouchStart,
    handleTextTouchEnd,
    insertTextField,
    cycleRepeatCount,
    insertRepeatMarker;

const canvasElementSelector = "#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #edit_text, #wiederholung";
const instrumentChooserSelector = ".instrument-chooser, #instrumentChooser";
const functionChooserSelector = ".function-chooser, #functionChooser";
const chooserSelector = instrumentChooserSelector + ", " + functionChooserSelector;
const timelineMetadataSelector = "#timeline_metadata";
const removableCanvasElementSelector = canvasElementSelector + ", " + chooserSelector + ", " + timelineMetadataSelector;
const exportableElementSelector = "#notenlinien, #basis, " + removableCanvasElementSelector;
const readableElementSelector = "#edit_text, #wiederholung, " + chooserSelector;
const phpEndpointBase = "PHP/";
const fileListEndpoint = "auswahlliste.php";
const loadFileEndpoint = "dateiladen.php";
const saveTextEndpoint = "dateispeichern.php";
const saveSvgEndpoint = "dateispeichern_svg.php";
const checkTextFileEndpoint = "dateivorhanden.php";
const checkSvgFileEndpoint = "dateivorhanden_svg.php";
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

const bodyElement = document.body;
bodyElement.addEventListener("keydown", shadow_end);
bodyElement.addEventListener("keydown", start);
bodyElement.addEventListener("keydown", entfernen);
/*
elem.addEventListener ("keydown", function (event) {
	console.log (event.key + " " + event.metaKey)
});
*/

// Funktionen
edit_title = function () {
    const text_a = this.attr('text');
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {
        return;
    }
    this.attr({ text: text_i });
};

edit_text = function () {
    const text_a = this.attr('text');
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {
        return;
    }
    this.attr({ text: text_i });
};

// Zeichenfläche und Titel festlegen
var s = Snap(1050, 1480).attr({ id: "myRect1" });
var canv = s.rect(0, 0, 1050, 1480).attr({ fill: "white", stroke: "black", strokeWidth: 0.5, opacity: 0.300, id: "myRect2" });
canv.drag(shadow_move, shadow_start, shadow_end);

function setIoFieldValue(value) {
    $('#iofield').val(value);
}

function getIoFieldValue() {
    return $('input[name=iofield]').val();
}

function postPhp(endpoint, payload, onSuccess) {
    const url = phpEndpointBase + endpoint;
    if (typeof payload === 'function') {
        $.post(url, payload);
        return;
    }
    $.post(url, payload, onSuccess);
}

function updateSelectionMarkup(markup) {
    document.getElementById('auswahl').innerHTML = markup;
}

function refreshFileList() {
    postPhp(fileListEndpoint, function (data) {
        setIoFieldValue(data);
        const fileListMarkup = getIoFieldValue();
        updateSelectionMarkup(fileListMarkup);
    });
}

function saveContentWithCheck(config) {
    checkCandidateName(config.baseName);

    function checkCandidateName(candidateBaseName) {
        const fileNameWithExtension = candidateBaseName + config.extension;
        postPhp(config.checkEndpoint, { b: fileNameWithExtension }, function (data) {
            setIoFieldValue(data);
            const fileExists = getIoFieldValue() == "true";
            if (fileExists) {
                if (config.onExistingFile) {
                    config.onExistingFile(candidateBaseName, checkCandidateName, saveCandidateName);
                }
                return;
            }
            saveCandidateName(candidateBaseName);
        });
    }

    function saveCandidateName(candidateBaseName) {
        postPhp(config.saveEndpoint, { a: config.content, b: candidateBaseName }, function (data) {
            setIoFieldValue(data);
            updateSelectionMarkup(getIoFieldValue());
            refreshFileList();
        });
    }
}

function loadRhythmFile(fileName) {
    const fileNameLengthWithoutExtension = fileName.length - 4;
    loadedTitle = fileName.substr(0, fileNameLengthWithoutExtension);
    postPhp(loadFileEndpoint, { b: fileName }, function (data) {
        setIoFieldValue(data);
        const loadedSvgMarkup = getIoFieldValue();
        Snap.loadStr(loadedSvgMarkup, onSVGLoaded);
    });
}

function removeCanvasElements(selector) {
    s.selectAll(selector).forEach(function (el) {
        el.remove();
    });
}

function resetSelectionArtifacts() {
    if (typeof box !== 'undefined' && box) {
        box.remove();
        box = null;
    }

    if (typeof selections !== 'undefined' && selections) {
        UnGroup();
        selections.remove();
        selections = null;
    }
}

function resolveInsertOffset(offsetValue) {
    return typeof offsetValue === 'function' ? offsetValue() : offsetValue;
}

function createPaletteClone(templateElement, elementId, offsetX, offsetY) {
    const resolvedOffsetX = resolveInsertOffset(offsetX);
    const resolvedOffsetY = resolveInsertOffset(offsetY);
    const clone = templateElement.clone().attr({
        class: 'shp',
        id: elementId,
        transform: "t" + (paletteOffsetX + resolvedOffsetX) + "," + (paletteOffsetY + resolvedOffsetY)
    });
    clone.drag(move, sel_start);
    return clone;
}

function bindPaletteInsert(sourceElement, templateElement, elementId, offsetX, offsetY, afterCreate) {
    const insertElement = function () {
        insertedElement = createPaletteClone(templateElement, elementId, offsetX, offsetY);
        if (afterCreate) {
            afterCreate(insertedElement);
        }
    };
    sourceElement.click(insertElement);
    sourceElement.touchstart(insertElement);
    return insertElement;
}

function bindEditableTextElement(textElement) {
    textElement.drag(move, sel_start);
    textElement.dblclick(edit_text);
    textElement.touchstart(captureTextTouchStart);
    textElement.touchend(handleTextTouchEnd);
    return textElement;
}

function createEditableTextElement(x, y, textContent) {
    const textElement = s.text(x, y, textContent).attr({
        class: 'shp',
        id: 'edit_text',
        'font-size': 14,
        'font-family': 'sans-serif'
    });
    return bindEditableTextElement(textElement);
}

function clear_all() {
    resetSelectionArtifacts();
    removeCanvasElements("#notenlinien, .shp, " + chooserSelector + ", " + timelineMetadataSelector);
    timelineState.nextBlockId = 1;
    timelineState.nextParallelGroupId = 1;
    timelineState.sourcePatterns = [];
    timelineState.sourceLibraryGroups = [];
    timelineState.entries = [];
    timelineState.sourceHash = '';
    timelineState.tempo = 100;
    timelineState.swingFactor = 0;
    timelineState.swingProfile = normalizeAllTimelineSwingProfiles();
    timelineState.feelOffsets = normalizeTimelineFeelOffsets();
    renderTimelinePanel();
}

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

function addInitialInstrumentChooser(x, y) {
    return createInstrumentChooser(s, x, y).addClass("shp").attr({ id: nextInstrumentChooserId() });
}

function addInitialFunctionChooser(x, y) {
    return createFunctionChooser(s, x, y).addClass("shp").attr({ id: nextFunctionChooserId() });
}

function drawRhythmSheet(config) {
    const gridLineStepX = 850 / config.subdivisionCount;
    const beatBarWidth = config.beatBarWidth;
    const initialChooserX = 100 + gridLineStepX * config.beatStartIndices[0];
    const shouldAddInitialChooser = config.addInitialChooser !== false;
    const shouldResetTitle = config.resetTitle !== false;

    rhythm = config.rhythmName;
    gridSize = config.gridSizeValue;
    gridSizeY = 2.5;
    gridSizeX = config.gridSizeXValue;
    repeatMarkerGridOffsetX = config.repeatMarkerOffsetXValue;

    clear_all();
    syllableIndex = 0;

    for (var j = 0; j < zeilenAnzahl; j++) {
        s.rect(100, staffStartY - 10 + j * 120, 3, 60).attr({ id: "notenlinien" });
        s.rect(525, staffStartY - 10 + j * 120, 3, 60).attr({ id: "notenlinien" });
        s.rect(950, staffStartY - 10 + j * 120, 3, 60).attr({ id: "notenlinien" });
        s.text(90, staffStartY + 30 + j * 120, j + 1).attr({
            id: "notenlinien",
            'font-size': 24,
            'font-family': 'sans-serif',
            'font-weight': 'bold',
            'fill': "#a0a0a0",
            'text-anchor': 'end'
        });

        for (var i = 1; i < config.subdivisionCount; i++) {
            const x = 100 + gridLineStepX * i;

            if (i != config.centerDividerIndex) {
                s.text(x - 3, staffStartY + j * 120 + config.syllableYOffset, config.countSyllables[syllableIndex]).attr({
                    id: "notenlinien",
                    'font-size': 10
                });
                syllableIndex++;
                if (syllableIndex == config.countSyllables.length) {
                    syllableIndex = 0;
                }
                s.rect(x, staffStartY + j * 120, 1.5, 40).attr({ id: "notenlinien" });
            }

            if (config.beatStartIndices.indexOf(i) !== -1) {
                let beatNumber = Math.trunc((i + config.beatNumberOffset) / config.beatDivisor);
                if (beatNumber > config.beatWrapAt) {
                    beatNumber -= config.beatWrapAt;
                }
                s.text(x - 3, staffStartY + j * 120 + config.beatNumberYOffset, beatNumber).attr({
                    id: "notenlinien",
                    'font-size': 10
                });
                s.rect(x, staffStartY + j * 120, beatBarWidth, 1.5).attr({ id: "notenlinien" });
                s.rect(x, staffStartY + j * 120 + 5, beatBarWidth, 1.5).attr({ id: "notenlinien" });
            }
        }
    }

    if (shouldAddInitialChooser) {
        addInitialInstrumentChooser(initialChooserX, 140);
        addInitialFunctionChooser(initialChooserX + 135, 140);
    }

    if (shouldResetTitle) {
        titel.attr({ text: "Enter the name of the Rhythm" });
    }
}

var titel = s.text(100, y - 100, "Enter the name of the Rhythm").attr({ id: 'basis', 'font-size': 24, 'font-family': 'sans-serif', 'font-weight': 'bold' });
titel.dblclick(edit_title);

let zeilenAnzahl = 10;
let rhythm = "binaer";

// Notenlinien anlegen für binären Rhythmus
function viererNoten() {
    drawRhythmSheet({
        rhythmName: 'binaer',
        subdivisionCount: 34,
        countSyllables: ["Ja", "Pi", "Du", "Pa"],
        centerDividerIndex: 17,
        beatStartIndices: [1, 5, 9, 13, 18, 22, 26, 30],
        beatDivisor: 4,
        beatNumberOffset: 4,
        beatWrapAt: 4,
        beatBarWidth: (850 / 34) * 3,
        beatNumberYOffset: -14,
        syllableYOffset: -4,
        gridSizeValue: (850 / 34) / 2,
        gridSizeXValue: 29,
        repeatMarkerOffsetXValue: 24
    });
}

function dreierNoten() {
    drawRhythmSheet({
        rhythmName: 'tenaer',
        subdivisionCount: 26,
        countSyllables: ["Ja", "Pi", "Du"],
        centerDividerIndex: 13,
        beatStartIndices: [1, 4, 7, 10, 14, 17, 20, 23],
        beatDivisor: 3,
        beatNumberOffset: 3,
        beatWrapAt: 4,
        beatBarWidth: (850 / 39) * 3,
        beatNumberYOffset: -16,
        syllableYOffset: -4,
        gridSizeValue: (850 / 26) / 2,
        gridSizeXValue: 34,
        repeatMarkerOffsetXValue: 26
    });
}

function neunerNoten() {
    drawRhythmSheet({
        rhythmName: 'neunaer',
        subdivisionCount: 20,
        countSyllables: ["Ja", "Pi", "Du"],
        centerDividerIndex: 10,
        beatStartIndices: [1, 4, 7, 11, 14, 17],
        beatDivisor: 3,
        beatNumberOffset: 3,
        beatWrapAt: 3,
        beatBarWidth: (850 / 30) * 3,
        beatNumberYOffset: -16,
        syllableYOffset: -4,
        gridSizeValue: (850 / 20) / 2,
        gridSizeXValue: 45.5,
        repeatMarkerOffsetXValue: 35
    });
}

function viererNotenOhneStartChooser() {
    drawRhythmSheet({
        rhythmName: 'binaer',
        subdivisionCount: 34,
        countSyllables: ["Ja", "Pi", "Du", "Pa"],
        centerDividerIndex: 17,
        beatStartIndices: [1, 5, 9, 13, 18, 22, 26, 30],
        beatDivisor: 4,
        beatNumberOffset: 4,
        beatWrapAt: 4,
        beatBarWidth: (850 / 34) * 3,
        beatNumberYOffset: -14,
        syllableYOffset: -4,
        gridSizeValue: (850 / 34) / 2,
        gridSizeXValue: 29,
        repeatMarkerOffsetXValue: 24,
        addInitialChooser: false,
        resetTitle: false
    });
}

function dreierNotenOhneStartChooser() {
    drawRhythmSheet({
        rhythmName: 'tenaer',
        subdivisionCount: 26,
        countSyllables: ["Ja", "Pi", "Du"],
        centerDividerIndex: 13,
        beatStartIndices: [1, 4, 7, 10, 14, 17, 20, 23],
        beatDivisor: 3,
        beatNumberOffset: 3,
        beatWrapAt: 4,
        beatBarWidth: (850 / 39) * 3,
        beatNumberYOffset: -16,
        syllableYOffset: -4,
        gridSizeValue: (850 / 26) / 2,
        gridSizeXValue: 34,
        repeatMarkerOffsetXValue: 26,
        addInitialChooser: false,
        resetTitle: false
    });
}

function neunerNotenOhneStartChooser() {
    drawRhythmSheet({
        rhythmName: 'neunaer',
        subdivisionCount: 20,
        countSyllables: ["Ja", "Pi", "Du"],
        centerDividerIndex: 10,
        beatStartIndices: [1, 4, 7, 11, 14, 17],
        beatDivisor: 3,
        beatNumberOffset: 3,
        beatWrapAt: 3,
        beatBarWidth: (850 / 30) * 3,
        beatNumberYOffset: -16,
        syllableYOffset: -4,
        gridSizeValue: (850 / 20) / 2,
        gridSizeXValue: 45.5,
        repeatMarkerOffsetXValue: 35,
        addInitialChooser: false,
        resetTitle: false
    });
}

// Noten zeichnen und initialisieren

// Anfangskoordinaten
paletteOriginX = 33;
paletteOriginY = paletteBaseY - 30;

// Kartusche
paletteFrame = s.rect(paletteOriginX - 12, paletteOriginY - 14, 26, 262, 3, 3).attr({ fill: "lightgrey", stroke: "black", strokeWidth: 0.5 });

// Tone
ton = s.circle(paletteOriginX + 1, paletteOriginY + 1, 7);

// Bass
x = paletteOriginX - 6;
y = paletteOriginY + 15;
bass = s.rect(x + 1, y, 12, 12);

// Slap
x = paletteOriginX - 5;
y = paletteOriginY + 47;
slap_c = s.rect(x, y - 12, 12, 12).attr({ opacity: 0.001 });
slap_a = s.line(x, y, x + 12, y - 12).attr({ stroke: "black", strokeWidth: 2 });
slap_b = s.line(x, y - 12, x + 12, y).attr({ stroke: "black", strokeWidth: 2 });
slap = s.g(slap_a, slap_b, slap_c);

// Flam Ton
x = paletteOriginX + 4;
y = paletteOriginY + 62;
flam_ton_a = s.circle(x, y, 6).attr({ fill: "white", stroke: "black", strokeWidth: 2 });
x = paletteOriginX - 2;
flam_ton_b = s.circle(x, y, 6).attr({ fill: "black", stroke: "black", strokeWidth: 2 });
flam_ton = s.g(flam_ton_a, flam_ton_b);

// Flam Slap
x = paletteOriginX - 8;
y = paletteOriginY + 87;
slap_0 = s.rect(x, y - 12, 20, 12).attr({ opacity: 0.001 });
slap_a1 = s.line(x, y, x + 12, y - 12).attr({ stroke: "black", strokeWidth: 2 });
slap_a2 = s.line(x, y - 12, x + 12, y).attr({ stroke: "black", strokeWidth: 2 });
x = paletteOriginX - 2;
slap_b1 = s.line(x, y, x + 12, y - 12).attr({ stroke: "black", strokeWidth: 2 });
slap_b2 = s.line(x, y - 12, x + 12, y).attr({ stroke: "black", strokeWidth: 2 });
flam_slap = s.g(slap_0, slap_a1, slap_a2, slap_b1, slap_b2);

// Flam Bass/Slap
x = paletteOriginX - 8;
y = paletteOriginY + 95;
flam_bass_0 = s.rect(x, y - 12, 12, 12).attr({ opacity: 0.001 });
flam_bass = s.rect(x + 2, y + 1, 10, 10);
x = paletteOriginX - 2;
y = paletteOriginY + 107;
slap_a3 = s.line(x, y, x + 12, y - 12).attr({ stroke: "black", strokeWidth: 2 });
slap_a4 = s.line(x, y - 12, x + 12, y).attr({ stroke: "black", strokeWidth: 2 });
flam_bass_slap = s.g(flam_bass_0, flam_bass, slap_a3, slap_a4).attr({ fill: "white", stroke: "black", strokeWidth: 2 });

// Tone gedämpft
x = paletteOriginX - 5;
y = paletteOriginY + 125;
ton_g_c = s.rect(x, y - 12, 12, 14).attr({ opacity: 0.001 });
x = paletteOriginX - 50;
y = paletteOriginY - 88;
ton_g_a = ton.clone().attr({ transform: "t0,120" });
x = paletteOriginX - 6;
y = paletteOriginY + 130;
ton_g_b = s.line(x, y, x + 15, y).attr({ stroke: "black", strokeWidth: 2 });
ton_g = s.g(ton_g_a, ton_g_b, ton_g_c);

// Slap gedämpft
x = paletteOriginX - 5;
y = paletteOriginY + 147;
slap_g_c = s.rect(x, y - 12, 12, 14).attr({ opacity: 0.001 });
slap_a5 = s.line(x, y, x + 12, y - 12).attr({ stroke: "black", strokeWidth: 2 });
slap_a6 = s.line(x, y - 12, x + 12, y).attr({ stroke: "black", strokeWidth: 2 });
x = paletteOriginX - 6;
y = paletteOriginY + 150;
slap_g_b = s.line(x, y, x + 15, y).attr({ stroke: "black", strokeWidth: 2 });
slap_g = s.g(slap_a5, slap_a6, slap_g_b, slap_g_c);

// In
x = paletteOriginX + 1;
y = paletteOriginY + 156;
in_c = s.rect(x - 6, y, 12, 20).attr({ opacity: 0.001 });
in_a = s.line(x, y, x, y + 12).attr({ stroke: "black", strokeWidth: 3 });
x = paletteOriginX - 5;
y = paletteOriginY + 168;
in_b = s.polygon(x, y, x + 6, y + 7, x + 12, y);
In = s.g(in_a, in_b, in_c);

// Out
x = paletteOriginX + 1;
y = paletteOriginY + 185;
out_c = s.rect(x - 6, y - 8, 12, 20).attr({ opacity: 0.001 });
out_a = s.line(x, y, x, y + 12).attr({ stroke: "black", strokeWidth: 3 });
x = paletteOriginX - 5;
out_b = s.polygon(x, y, x + 6, y - 7, x + 12, y);
Out = s.g(out_a, out_b, out_c);

// Text
x = paletteOriginX - 6;
y = paletteOriginY + 230;
textPaletteBox = s.rect(x, y - 26, 14, 15).attr({ fill: "white", stroke: "black", strokeWidth: 1 });
textPaletteHorizontalLine = s.line(x + 3, y - 21, x + 11, y - 21).attr({ stroke: "black", strokeWidth: 2.5 });
textPaletteVerticalLine = s.line(x + 7, y - 21, x + 7, y - 14).attr({ stroke: "black", strokeWidth: 2.5 });
text_z_g = s.g(textPaletteBox, textPaletteVerticalLine, textPaletteHorizontalLine);

y += 20;

// Wiederholungszeichen
repeatMarkerHitbox = s.rect(paletteOriginX - 4, y - 27, 10, 20).attr({ opacity: 0.001 });
repeatMarkerDotTop = s.circle(paletteOriginX + 1, paletteOriginY + 228, 2.5);
repeatMarkerDotBottom = s.circle(paletteOriginX + 1, paletteOriginY + 236, 2.5);
repeatMarkerCountText = s.text(paletteOriginX + 1, paletteOriginY + 252, " ").attr({ 'font-size': 12, 'font-family': 'sans-serif', 'font-weight': 'bold', 'text-anchor': 'middle' });
repeatMarkerGroup = s.g(repeatMarkerHitbox, repeatMarkerDotTop, repeatMarkerDotBottom, repeatMarkerCountText);

// Legende schreiben
function addLegendEntry(symbol, label, symbolX, symbolY, labelOffsetX, labelOffsetY) {
    const legendClone = symbol.clone().attr({ id: "basis", transform: "t" + symbolX + "," + symbolY });
    s.text(symbolX + labelOffsetX, symbolY + labelOffsetY, label).attr({
        id: "basis",
        'font-size': 15,
        'font-family': 'sans-serif'
    });
    return legendClone;
}

ton_c = addLegendEntry(ton, "Tone", 92, 1166, 45, 178);
bass_c = addLegendEntry(bass, "Bass", 157, 1146, 46, 198);
slap_c = addLegendEntry(slap, "Slap/Glocke", 222, 1126, 45, 218);
flam_ton_c = addLegendEntry(flam_ton, "Flam mit Tones", 337, 1105, 49, 240);
flam_slap_c = addLegendEntry(flam_slap, "Flam mit Slaps", 475, 1087, 49, 259);
flam_bass_slap_c = addLegendEntry(flam_bass_slap, "Flam mit Bass und Slaps", 613, 1069, 49, 279);
ton_g_c = addLegendEntry(ton_g, "gedämpfter Tone", 92, 1078, 48, 299);
slap_g_c = addLegendEntry(slap_g, "gedämpfter Slap / Klick", 240, 1058, 48, 319);
In_c = addLegendEntry(In, "In", 428, 1034, 44, 343);
Out_c = addLegendEntry(Out, "Out", 470, 1011, 44, 366);
repeatMarkerLegendClone = addLegendEntry(repeatMarkerGroup, "Wiederholung", 521, 968, 44, 409);


// Funktionen zum Verschieben
var move1 = function (dx, dy, x, y) {
    var dx = Snap.snapTo(gridSize, dx, 50);
    var dy = Snap.snapTo(gridSizeY, dy, 50);
    this.attr({
        transform: this.data('origTransform') + (this.data('origTransform') ? "T" : "t") + [dx, dy]
    });
    paletteDragDeltaX = dx;
    paletteDragDeltaY = dy;
};

var stop1 = function() {
    paletteOffsetX += paletteDragDeltaX;
    paletteOffsetY += paletteDragDeltaY;
    paletteDragDeltaX = 0;
    paletteDragDeltaY = 0;
};

// Kartusche zeichnen
paletteGroup = s.g(paletteFrame, ton, bass, slap, ton_g, slap_g, flam_ton, flam_slap, flam_bass_slap, In, Out, text_z_g, repeatMarkerGroup);
paletteGroup.drag(move1, sel_start, stop1);

// Duplicate der Noten erzeugen
	insertTone = bindPaletteInsert(ton, ton_c, "tone", function () { return gridSizeX; }, 0);
	insertBass = bindPaletteInsert(bass, bass_c, "bass", function () { return gridSizeX; }, 0);
	insertSlap = bindPaletteInsert(slap, slap_c, "slap", function () { return gridSizeX; }, 0);
	insertMuffledTone = bindPaletteInsert(ton_g, ton_g_c, "tone_muffled", function () { return gridSizeX; }, 0);
	insertMuffledSlap = bindPaletteInsert(slap_g, slap_g_c, "slap_muffled", function () { return gridSizeX; }, 0);
	insertFlamTone = bindPaletteInsert(flam_ton, flam_ton_c, "tone_flam", function () { return gridSizeX; }, 0);
	insertFlamSlap = bindPaletteInsert(flam_slap, flam_slap_c, "slap_flam", function () { return gridSizeX; }, 0);
	insertFlamBassSlap = bindPaletteInsert(flam_bass_slap, flam_bass_slap_c, "bass_slap_flam", function () { return gridSizeX; }, 0);
	insertInMarker = bindPaletteInsert(In, In_c, "in", function () { return gridSizeX; }, -2);
insertOutMarker = bindPaletteInsert(Out, Out_c, "out", function () { return gridSizeX; }, 0);

captureTextTouchStart = function () {
    textTouchStartX = this.getBBox().x;
    textTouchStartY = this.getBBox().y;
};

handleTextTouchEnd = function () {
    textTouchEndX = this.getBBox().x;
    textTouchEndY = this.getBBox().y;
    if (textTouchEndX == textTouchStartX && textTouchEndY == textTouchStartY) {
        const text_a = this.attr('text');
        const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
        if (text_i == null) {
            return;
        }
        this.attr({ text: text_i });
    }
};

insertTextField = function () {
    const elx = this.getBBox().cx + paletteOffsetX + 19;
    const ely = this.getBBox().y + paletteOffsetY + 12;
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', '');
    createEditableTextElement(elx + 3.5, ely, text_i);
};
text_z_g.click(insertTextField);
text_z_g.touchstart(insertTextField);

cycleRepeatCount = function () {
    let textEl = this.select('text');
    let wert = textEl.node.textContent.trim();
    let zahl = parseInt(wert, 10);

    if (isNaN(zahl)) {
        zahl = 2;
    } else {
        zahl++;
        if (zahl > 4) {
            zahl = 0;
        }
    }
    textEl.attr({ text: zahl === 0 ? '' : String(zahl) });
};

insertRepeatMarker = bindPaletteInsert(
    repeatMarkerGroup,
    repeatMarkerLegendClone,
    "wiederholung",
    function () { return repeatMarkerGridOffsetX; },
    2,
    function (repeatMarkerElement) {
        repeatMarkerElement.dblclick(cycleRepeatCount);
    }
);


// Als SVG speichern

function callPHPScript2() {
    let svgContent = "";
    let elementsToExport = s.selectAll(exportableElementSelector);
    // Noten im Abseits löschen
    elementsToExport.forEach(function (el) {
        if (el.attr('id') == 'timeline_metadata') {
            return;
        }
        const ax = el.getBBox().cx;
        const ay = el.getBBox().cy;
        if (ax < 0 || ax > 1050 || ay < 0 || ay > 1480) {
            el.remove();
        }
    });

    elementsToExport = s.selectAll(exportableElementSelector);
    elementsToExport.forEach(function (el) {
        svgContent += el.toString();
    });

    const svgWithoutStylePrefix = svgContent.replaceAll('style="f', 'f');
    const svgWithFont10 = svgWithoutStylePrefix.replaceAll('font-size: 10px;', 'font-size="10px"');
    const svgWithFont12 = svgWithFont10.replaceAll('font-size: 12px;', 'font-size="12px"');
    const svgWithFont24 = svgWithFont12.replaceAll('font-size: 24px;', 'font-size="24px"');
    const svgWithFont15 = svgWithFont24.replaceAll('font-size: 15px;', 'font-size="15px"');
    const svgWithFont14 = svgWithFont15.replaceAll('font-size: 14px;', 'font-size="14px"');
    const svgWithTahoma = svgWithFont14.replaceAll('font-family: sans-serif;', 'font-family="Tahoma"');
    const svgWithBoldText = svgWithTahoma.replaceAll('font-weight: bold;', 'font-weight="bold"');
    svgContent = '<svg height="1480" version="1.1" width="1050" xmlns="http://www.w3.org/2000/svg" id="myRect1"><desc>Created with Snap</desc><defs></defs>' + svgWithBoldText + '</svg>';

    const name = titel.attr('text');
    saveContentWithCheck({
        baseName: name,
        extension: ".svg",
        checkEndpoint: checkSvgFileEndpoint,
        saveEndpoint: saveSvgEndpoint,
        content: svgContent,
        onExistingFile: function (existingBaseName, retryCheckName) {
            const replacementName = prompt('Die Datei "' + existingBaseName + '" existiert schon!\nGib einen anderen Dateinamen ein!', '');
            if (replacementName == null) {
                return;
            }
            retryCheckName(replacementName);
        }
    });
}


// Auslesen


const noteElementIds = ['tone', 'bass', 'slap', 'tone_muffled', 'slap_muffled', 'slap_muffled', 'tone_flam', 'slap_flam', 'bass_slap_flam'];
const controlElementIds = ['in', 'out', 'wiederholung'];

let notenText = "eee";

function getReadRhythmConfig() {
    if (rhythm == 'binaer') {
        return {
            rhythmLabel: "binär",
            stepsPerBar: 32,
            totalStepsPerLine: 64,
            gapSlotCount: 2,
            getLineSlotIndex: function (centerX) {
                return Math.round(((centerX - 25) / 12.5) - 7);
            }
        };
    }
    if (rhythm == 'tenaer') {
        return {
            rhythmLabel: "tenär",
            stepsPerBar: 24,
            totalStepsPerLine: 48,
            gapSlotCount: 2,
            getLineSlotIndex: function (centerX) {
                return Math.round(((centerX - 34) / 16.5) - 5);
            }
        };
    }
    return {
        rhythmLabel: "tenär 9/8",
        stepsPerBar: 9,
        totalStepsPerLine: 18,
        gapSlotCount: 1,
        getLineSlotIndex: function (centerX) {
            return Math.round(((centerX - 58.25) / 42.5) - 1);
        }
    };
}

function createEmptyBar(barIndex, stepsPerBar) {
    return {
        index: barIndex + 1,
        lineIndex: Math.floor(barIndex / 2) + 1,
        instrument: '',
        effectiveInstrument: '',
        label: '',
        effectiveLabel: '',
        repeat: {
            start: false,
            end: false
        },
        controls: [],
        notes: new Array(stepsPerBar).fill('f')
    };
}

function createEmptyRepeatBoundary(boundaryIndex) {
    return {
        index: boundaryIndex,
        startMarkers: [],
        endMarkers: []
    };
}

function getElementReadPosition(element) {
    if (isInstrumentChooserNode(element) || isFunctionChooserNode(element)) {
        const chooserBounds = element.getBBox();
        const transformState = typeof element.transform === 'function' ? element.transform() : null;
        const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
        return {
            x: chooserBounds.cx,
            y: localMatrix ? localMatrix.f : chooserBounds.cy
        };
    }
    return {
        x: element.getBBox().cx,
        y: element.getBBox().cy
    };
}

function getBarIndexFromPosition(centerX, centerY, readConfig, lineCount) {
    const rawLineSlotIndex = readConfig.getLineSlotIndex(centerX);
    const gapSlotCount = Number(readConfig.gapSlotCount) || 2;
    let lineSlotIndex = rawLineSlotIndex;
    if (lineSlotIndex > readConfig.stepsPerBar) {
        lineSlotIndex -= gapSlotCount;
    }
    const barOffset = rawLineSlotIndex > readConfig.stepsPerBar + gapSlotCount ? 1 : 0;
    const lineIndex = Math.max(0, Math.min(lineCount - 1, Math.round((centerY - 237) / 120)));
    return {
        rawLineSlotIndex: rawLineSlotIndex,
        lineSlotIndex: lineSlotIndex,
        lineIndex: lineIndex,
        barIndex: lineIndex * 2 + barOffset
    };
}

function getBarIndexForMetaElement(centerX, centerY, readConfig, lineCount) {
    const rawLineSlotIndex = readConfig.getLineSlotIndex(centerX);
    const gapSlotCount = Number(readConfig.gapSlotCount) || 2;
    let lineSlotIndex = rawLineSlotIndex;
    if (lineSlotIndex > readConfig.stepsPerBar) {
        lineSlotIndex -= gapSlotCount;
    }
    const barOffset = rawLineSlotIndex > readConfig.stepsPerBar + gapSlotCount ? 1 : 0;
    const lineIndex = Math.max(0, Math.min(lineCount - 1, Math.round((centerY - 140) / 120)));
    return {
        rawLineSlotIndex: rawLineSlotIndex,
        lineSlotIndex: lineSlotIndex,
        lineIndex: lineIndex,
        barIndex: lineIndex * 2 + barOffset
    };
}

function getRepeatTarget(centerX, centerY, lineCount) {
    const lineIndex = Math.max(0, Math.min(lineCount - 1, Math.round((centerY - 237) / 120)));
    const leftBarLineX = 100;
    const middleBarLineX = 525;
    const rightBarLineX = 950;
    const distanceToLeft = Math.abs(centerX - leftBarLineX);
    const distanceToMiddle = Math.abs(centerX - middleBarLineX);
    const distanceToRight = Math.abs(centerX - rightBarLineX);

    if (distanceToLeft <= distanceToMiddle) {
        return {
            boundaryIndex: lineIndex * 2,
            boundaryLineX: leftBarLineX,
            repeatSide: 'start'
        };
    }
    if (distanceToMiddle <= distanceToRight) {
        return {
            boundaryIndex: lineIndex * 2 + 1,
            boundaryLineX: middleBarLineX,
            repeatSide: centerX < middleBarLineX ? 'end' : 'start'
        };
    }
    return {
        boundaryIndex: lineIndex * 2 + 2,
        boundaryLineX: rightBarLineX,
        repeatSide: 'end'
    };
}

function getStepIndexWithinBar(lineSlotIndex, stepsPerBar) {
    if (lineSlotIndex < 1) {
        return null;
    }
    if (lineSlotIndex > stepsPerBar) {
        return lineSlotIndex - stepsPerBar - 1;
    }
    return lineSlotIndex - 1;
}

function getElementLabelText(element) {
    if (isInstrumentChooserNode(element) || isFunctionChooserNode(element)) {
        const chooserLabel = element.select("text");
        const chooserText = chooserLabel ? (chooserLabel.attr('text') || chooserLabel.node.textContent || '') : '';
        if (chooserText == 'Instrument' || chooserText == 'Funktion') {
            return '';
        }
        return chooserText;
    }
    if (element.attr('id') == 'wiederholung') {
        const repeatText = element.select('text');
        return repeatText ? (repeatText.attr('text') || repeatText.node.textContent || '') : '';
    }
    return element.attr('text') || '';
}

function normalizeRepeatCount(repeatText, repeatSide) {
    const trimmedRepeatText = String(repeatText).trim();
    if (trimmedRepeatText === '') {
        return repeatSide == 'end' ? 1 : true;
    }
    if (!isNaN(Number(trimmedRepeatText))) {
        return Number(trimmedRepeatText);
    }
    return trimmedRepeatText;
}

function buildRepeatRanges(repeatBoundaries) {
    const repeatRanges = [];
    const repeatStartStack = [];

    repeatBoundaries.forEach(function (boundary) {
        const sortedStartMarkers = boundary.startMarkers.slice().sort(function (markerA, markerB) {
            return Math.abs(markerB.x - markerB.boundaryLineX) - Math.abs(markerA.x - markerA.boundaryLineX);
        });
        const sortedEndMarkers = boundary.endMarkers.slice().sort(function (markerA, markerB) {
            return Math.abs(markerA.x - markerA.boundaryLineX) - Math.abs(markerB.x - markerB.boundaryLineX);
        });

        sortedEndMarkers.forEach(function (endMarker) {
            const matchingStartMarker = repeatStartStack.pop();
            if (!matchingStartMarker) {
                return;
            }
            repeatRanges.push({
                startBoundary: matchingStartMarker.boundaryIndex,
                endBoundary: endMarker.boundaryIndex,
                startBar: matchingStartMarker.boundaryIndex + 1,
                endBar: endMarker.boundaryIndex,
                count: endMarker.count
            });
        });

        sortedStartMarkers.forEach(function (startMarker) {
            repeatStartStack.push(startMarker);
        });
    });

    repeatRanges.sort(function (rangeA, rangeB) {
        if (rangeA.startBoundary !== rangeB.startBoundary) {
            return rangeA.startBoundary - rangeB.startBoundary;
        }
        return rangeA.endBoundary - rangeB.endBoundary;
    });

    return repeatRanges;
}

function applyRepeatMarkersToBars(rhythmBars, repeatBoundaries) {
    repeatBoundaries.forEach(function (boundary) {
        const startBar = rhythmBars[boundary.index];
        const endBar = rhythmBars[boundary.index - 1];

        if (startBar && boundary.startMarkers.length > 0) {
            startBar.repeat.start = boundary.startMarkers.map(function (marker) {
                return marker.count;
            });
        }
        if (endBar && boundary.endMarkers.length > 0) {
            endBar.repeat.end = boundary.endMarkers.map(function (marker) {
                return marker.count;
            });
        }
    });
}

function mergePercussionNote(currentSymbol, noteId, instrumentName) {
    if (instrumentName == 'Kenkeni' || instrumentName == 'Sangban' || instrumentName == 'Doundoun' || instrumentName == 'Dununba' || instrumentName == 'Bässe') {
        if (noteId == 'slap' && currentSymbol == 'f') {
            return 'Bell';
        }
        if (noteId == 'tone' && currentSymbol == 'f') {
            return 'Open';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'f') {
            return 'Muffled';
        }
        if (noteId == 'slap_muffled' && currentSymbol == 'f') {
            return 'Klick';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'Bell') {
            return 'Bell_Muffled';
        }
        if (noteId == 'slap_muffled' && currentSymbol == 'Bell') {
            return 'Bell_Klick';
        }
        if (noteId == 'slap' && currentSymbol == 'Muffled') {
            return 'Bell_Muffled';
        }
        if (noteId == 'slap' && currentSymbol == 'Klick') {
            return 'Bell_Klick';
        }
        if (noteId == 'slap' && currentSymbol == 'Open') {
            return 'Bell_Open';
        }
        if (noteId == 'tone' && currentSymbol == 'Bell') {
            return 'Bell_Open';
        }
    } else if (instrumentName == 'Dreierbass') {
        if (noteId == 'slap' && currentSymbol == 'f') {
            return 'kenkeni';
        }
        if (noteId == 'tone' && currentSymbol == 'f') {
            return 'sangban';
        }
        if (noteId == 'bass' && currentSymbol == 'f') {
            return 'doundoun';
        }
        if (noteId == 'slap_muffled' && currentSymbol == 'f') {
            return 'kenkeni_muffled';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'f') {
            return 'sangban_muffled';
        }
        if (noteId == 'slap' && currentSymbol == 'sangban') {
            return 'kenkeni_sangban';
        }
        if (noteId == 'tone' && currentSymbol == 'kenkeni') {
            return 'kenkeni_sangban';
        }
        if (noteId == 'bass' && currentSymbol == 'kenkeni') {
            return 'kenkeni_doundoun';
        }
        if (noteId == 'slap' && currentSymbol == 'doundoun') {
            return 'kenkeni_doundoun';
        }
        if (noteId == 'tone' && currentSymbol == 'doundoun') {
            return 'sangban_doundoun';
        }
        if (noteId == 'bass' && currentSymbol == 'sangban') {
            return 'sangban_doundoun';
        }
        if (noteId == 'tone' && currentSymbol == 'kenkeni_muffled') {
            return 'kenkeni_muffled_sangban';
        }
        if (noteId == 'slap_muffled' && currentSymbol == 'sangban') {
            return 'kenkeni_muffled_sangban';
        }
        if (noteId == 'slap' && currentSymbol == 'sangban_muffled') {
            return 'kenkeni_sangban_muffled';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'kenkeni') {
            return 'kenkeni_sangban_muffled';
        }
        if (noteId == 'bass' && currentSymbol == 'sangban_muffled') {
            return 'sangban_muffled_doundoun';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'doundoun') {
            return 'sangban_muffled_doundoun';
        }
        if (noteId == 'bass' && currentSymbol == 'kenkeni_muffled') {
            return 'kenkeni_muffled_doundoun';
        }
        if (noteId == 'slap_muffled' && currentSymbol == 'doundoun') {
            return 'kenkeni_muffled_doundoun';
        }
    } else if (currentSymbol == 'f') {
        return noteId;
    }
    return currentSymbol;
}

function propagateBarInstruments(rhythmBars) {
    let currentInstrument = '';
    let currentLabel = '';

    rhythmBars.forEach(function (bar) {
        if (!bar) {
            return;
        }

        if (bar.label === 'Leer' && !bar.instrument) {
            bar.effectiveInstrument = 'Leer';
            bar.effectiveLabel = 'Leer';
            currentInstrument = '';
            currentLabel = '';
            return;
        }

        if (bar.instrument) {
            if (bar.instrument === 'Leer') {
                bar.effectiveInstrument = 'Leer';
                bar.effectiveLabel = 'Leer';
                currentInstrument = '';
                currentLabel = '';
                return;
            }
            currentInstrument = bar.instrument;
            bar.effectiveInstrument = bar.instrument;
        } else {
            bar.effectiveInstrument = currentInstrument || 'Leer';
        }

        if (bar.label) {
            if (bar.label === 'Leer') {
                bar.effectiveLabel = 'Leer';
                currentLabel = '';
                return;
            }
            currentLabel = bar.label;
            bar.effectiveLabel = bar.label;
        } else {
            bar.effectiveLabel = currentInstrument ? (currentLabel || 'Leer') : 'Leer';
        }
    });
}

function buildBarSummary(rhythmBars) {
    let summaryText = '';
    rhythmBars.forEach(function (bar) {
        const startMarkers = Array.isArray(bar.repeat.start) ? bar.repeat.start : [bar.repeat.start];
        const endMarkers = Array.isArray(bar.repeat.end) ? bar.repeat.end : [bar.repeat.end];
        const displayStartMarkers = startMarkers.map(function (marker) {
            return marker === 'loop' ? 'bis Stop' : marker;
        });
        const displayEndMarkers = endMarkers.map(function (marker) {
            return marker === 'loop' ? 'bis Stop' : marker;
        });
        const controlSummary = bar.controls.length === 0
            ? 'keine'
            : bar.controls
                .slice()
                .sort(function (controlA, controlB) {
                    return controlA.stepIndex - controlB.stepIndex;
                })
                .map(function (control) {
                    const controlLabel = control.type === 'in' ? 'In' : 'Out';
                    return controlLabel + '@' + (control.stepIndex + 1);
                })
                .join(', ');
        summaryText +=
            'Takt ' + bar.index + ': ' +
            (bar.effectiveInstrument || '') + ', ' +
            (bar.effectiveLabel || '') + ', Wiederholungsmarker = Start[' +
            displayStartMarkers.join(', ') + '], Ende[' + displayEndMarkers.join(', ') + ']\n' +
            'Steuerung [' + (bar.index - 1) + '] = [' + controlSummary + ']\n' +
            'Schlaege [' + (bar.index - 1) + '] = [' + bar.notes.join(',') + ']\n';
    });
    return summaryText;
}

function buildRepeatRangeSummary(repeatRanges) {
    if (repeatRanges.length === 0) {
        return 'Wiederholungsbereiche: keine\n';
    }

    let rangeSummaryText = 'Wiederholungsbereiche:\n';
    repeatRanges.forEach(function (repeatRange, rangeIndex) {
        rangeSummaryText +=
            (rangeIndex + 1) + '. Takt ' + repeatRange.startBar + '-' + repeatRange.endBar +
            (repeatRange.count === 'loop' ? ' bis Stop' : ' x ' + repeatRange.count) + '\n';
    });
    return rangeSummaryText;
}

function mapInstrumentNameForPlayer(instrumentName) {
    const instrumentMap = {
        'Djembe 1': 'Djembe_1',
        'Djembe 2': 'Djembe_2',
        'Djembe 3': 'Djembe_3',
        'Dununba': 'Doundoun'
    };
    if (!instrumentName || instrumentName === 'Leer') {
        return '';
    }
    return instrumentMap[instrumentName] || instrumentName;
}

function mapLabelForPlayer(label) {
    if (!label || label === 'Leer') {
        return '';
    }
    if (label.indexOf('Begleitpattern') === 0 || label.indexOf('Begleitung') === 0) {
        return 'Begleitung';
    }
    if (label.indexOf('Call') === 0) {
        return 'Call';
    }
    if (label.indexOf('Echauffement') === 0) {
        return 'Echauffement';
    }
    if (label.indexOf('Outro') === 0) {
        return 'Outro';
    }
    return label;
}

function getPlayerLabelInfo(label) {
    const rawLabel = String(label || '').trim();
    if (!rawLabel || rawLabel === 'Leer') {
        return {
            raw: '',
            type: ''
        };
    }

    return {
        raw: rawLabel,
        type: mapLabelForPlayer(rawLabel)
    };
}

function getPlayerRepeatValue(repeatMarkers, markerType) {
    if (!Array.isArray(repeatMarkers) || repeatMarkers.length === 0) {
        return false;
    }
    const firstMarker = repeatMarkers[0];
    if (firstMarker === 'loop') {
        return markerType === 'end' ? 'loop' : true;
    }
    return firstMarker;
}

function buildPlayerRowsFromRhythmBars(rhythmBars, repeatRanges) {
    function getExplicitPlayerInstrument(bar) {
        if (!bar) {
            return '';
        }
        if (bar.instrument && bar.instrument !== 'Leer') {
            return bar.instrument;
        }
        return '';
    }

    function getEffectivePlayerInstrument(bar) {
        if (!bar) {
            return '';
        }
        if (bar.effectiveInstrument && bar.effectiveInstrument !== 'Leer') {
            return bar.effectiveInstrument;
        }
        return '';
    }

    function getExplicitPlayerLabel(bar) {
        if (!bar) {
            return '';
        }
        if (bar.label && bar.label !== 'Leer') {
            return bar.label;
        }
        return '';
    }

    function getEffectivePlayerLabel(bar) {
        if (!bar) {
            return '';
        }
        if (bar.effectiveLabel && bar.effectiveLabel !== 'Leer') {
            return bar.effectiveLabel;
        }
        return '';
    }

    const playerRows = [{
        Name: titel.attr('text'),
        Rhythmus: rhythm,
        RepeatRanges: repeatRanges || []
    }];

    for (let rowIndex = 0; rowIndex < zeilenAnzahl; rowIndex++) {
        const leftBar = rhythmBars[rowIndex * 2];
        const rightBar = rhythmBars[rowIndex * 2 + 1];
        const leftExplicitInstrument = mapInstrumentNameForPlayer(getExplicitPlayerInstrument(leftBar));
        const rightExplicitInstrument = mapInstrumentNameForPlayer(getExplicitPlayerInstrument(rightBar));
        const leftEffectiveInstrument = mapInstrumentNameForPlayer(getEffectivePlayerInstrument(leftBar));
        const rightEffectiveInstrument = mapInstrumentNameForPlayer(getEffectivePlayerInstrument(rightBar));
        const leftExplicitLabel = mapLabelForPlayer(getExplicitPlayerLabel(leftBar));
        const rightExplicitLabel = mapLabelForPlayer(getExplicitPlayerLabel(rightBar));
        const leftEffectiveLabel = mapLabelForPlayer(getEffectivePlayerLabel(leftBar));
        const rightEffectiveLabel = mapLabelForPlayer(getEffectivePlayerLabel(rightBar));
        const leftLabel = leftExplicitLabel || leftEffectiveLabel;
        const rightLabel = rightExplicitLabel || rightEffectiveLabel;
        const leftUsesAllDjembes = !leftExplicitInstrument && (leftLabel === 'Echauffement' || leftLabel === 'Outro');
        const rightUsesAllDjembes = !rightExplicitInstrument && (rightLabel === 'Echauffement' || rightLabel === 'Outro');
        const leftUsesAllBasses = (leftExplicitInstrument || leftEffectiveInstrument) === 'Bässe';
        const rightUsesAllBasses = (rightExplicitInstrument || rightEffectiveInstrument) === 'Bässe';

        playerRows.push({
            Instrument_1: leftExplicitInstrument || leftEffectiveInstrument,
            InstrumentMode_1: leftUsesAllDjembes ? 'allUsedDjembes' : (leftUsesAllBasses ? 'allBasses' : 'single'),
            Bezeichner_1: leftLabel,
            Wiederholung_1: leftBar ? [
                getPlayerRepeatValue(leftBar.repeat.start, 'start'),
                getPlayerRepeatValue(leftBar.repeat.end, 'end')
            ] : [false, false],
            Instrument_2: rightExplicitInstrument || rightEffectiveInstrument,
            InstrumentMode_2: rightUsesAllDjembes ? 'allUsedDjembes' : (rightUsesAllBasses ? 'allBasses' : 'single'),
            Bezeichner_2: rightLabel,
            Wiederholung_2: rightBar ? [
                getPlayerRepeatValue(rightBar.repeat.start, 'start'),
                getPlayerRepeatValue(rightBar.repeat.end, 'end')
            ] : [false, false],
            Reihe: (leftBar ? leftBar.notes : []).concat(rightBar ? rightBar.notes : [])
        });
    }

    return playerRows;
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
    timelineState.entries = syncedEntries.length > 0 ? syncedEntries : fallbackEntries;
    syncTimelineBlockIdSequence(timelineState.entries);
    timelineState.tempo = normalizeTimelineTempo(syncOptions.tempo ?? timelineState.tempo);
    timelineState.swingFactor = normalizeTimelineSwingFactor(syncOptions.swingFactor ?? timelineState.swingFactor);
    timelineState.swingProfile = normalizeAllTimelineSwingProfiles(syncOptions.swingProfile);
    timelineState.feelOffsets = normalizeTimelineFeelOffsets(syncOptions.feelOffsets);

    updateTimelineMetadataNode();
    renderTimelinePanel();
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

    return blocks;
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
        const patternMeta = document.createElement('small');
        patternMeta.textContent = totalBars + ' Takt(e)';
        const patternSummary = blockSummary.text || buildPatternLibraryGroupSummary(patternGroup);
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

        actionWrap.appendChild(addButton);
        card.appendChild(patternTitle);
        card.appendChild(patternMeta);
        if (patternSummary) {
            const summaryEl = document.createElement('small');
            summaryEl.textContent = patternSummary;
            summaryEl.style.display = 'block';
            summaryEl.style.marginTop = '4px';
            card.appendChild(summaryEl);
        }
        card.appendChild(actionWrap);
        listEl.appendChild(card);
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
                summaryEl.style.display = 'block';
                summaryEl.style.marginTop = '4px';
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
            swingInputEl.style.width = '60px';
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
    if (swingProfileWrapEl) {
        swingProfileWrapEl.style.display = 'inline-flex';
    }
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
            inputLabel.style.display = isActive ? 'inline-flex' : 'none';
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

    panelEl.style.display = timelineState.visible ? 'block' : 'none';
    const sourceLibraryGroupCount = Array.isArray(timelineState.sourceLibraryGroups) && timelineState.sourceLibraryGroups.length > 0
        ? timelineState.sourceLibraryGroups.length
        : patternDisplayInfo.groups.length;
    statusEl.textContent = sourceLibraryGroupCount + ' Pattern aus dem Blatt, ' +
        timelineVisualRows.length + ' Eintrag/Eintaege in der Timeline.';

    if (!timelineState.visible) {
        return;
    }

    renderTimelinePatternLibrary();
    renderTimelineSequence();
}

function openAudioTestWindow(playerRows) {
    const form = document.createElement('form');
    form.action = 'Audio/audioplayer.php';
    form.method = 'POST';
    form.target = '_blank';
    form.innerHTML = '<input type="hidden" name="myObj" />';
    document.body.appendChild(form);
    form.querySelector('input[name="myObj"]').value = JSON.stringify(playerRows);
    form.submit();
    form.remove();
}

function callPHPScript_lesen(anzahl, options) {
    const readOptions = options || {};
    const shouldShowAlert = readOptions.showAlert !== false;
    const takteAnzahl = anzahl * 2;
    const readConfig = getReadRhythmConfig();
    const rhythmBars = [];
    const repeatBoundaries = [];

    notenText = readConfig.rhythmLabel;

    for (var i = 0; i < takteAnzahl; i++) {
        rhythmBars.push(createEmptyBar(i, readConfig.stepsPerBar));
    }
    for (var j = 0; j <= takteAnzahl; j++) {
        repeatBoundaries.push(createEmptyRepeatBoundary(j));
    }

    const readableElements = s.selectAll(readableElementSelector);
    readableElements.forEach(function (el) {
        const elementPosition = getElementReadPosition(el);
        const positionInfo = el.attr('id') == "wiederholung"
            ? getBarIndexFromPosition(elementPosition.x, elementPosition.y, readConfig, anzahl)
            : getBarIndexForMetaElement(elementPosition.x, elementPosition.y, readConfig, anzahl);
        const elementText = String(getElementLabelText(el) || '').trim();

        if (el.attr('id') == "wiederholung") {
            const repeatTarget = getRepeatTarget(elementPosition.x, elementPosition.y, anzahl);
            if (!repeatTarget || !repeatBoundaries[repeatTarget.boundaryIndex]) {
                return;
            }
            repeatBoundaries[repeatTarget.boundaryIndex][repeatTarget.repeatSide + 'Markers'].push({
                boundaryIndex: repeatTarget.boundaryIndex,
                boundaryLineX: repeatTarget.boundaryLineX,
                x: elementPosition.x,
                count: normalizeRepeatCount(elementText, repeatTarget.repeatSide)
            });
            return;
        }

        const rhythmBar = rhythmBars[positionInfo.barIndex];
        if (!rhythmBar) {
            return;
        }

        if (elementText === '') {
            return;
        }

        if (isInstrumentChooserNode(el)) {
            rhythmBar.instrument = elementText;
        } else if (isFunctionChooserNode(el)) {
            rhythmBar.label = elementText;
        } else {
            rhythmBar.label = elementText;
        }
    });

    propagateBarInstruments(rhythmBars);

    const playableElements = s.selectAll("." + "shp");
    playableElements.forEach(function (el) {
        const elementId = el.attr('id');
        if (!noteElementIds.includes(elementId) && !controlElementIds.includes(elementId)) {
            return;
        }

        const elementPosition = getElementReadPosition(el);
        const positionInfo = getBarIndexFromPosition(elementPosition.x, elementPosition.y, readConfig, anzahl);
        const rhythmBar = rhythmBars[positionInfo.barIndex];
        if (!rhythmBar) {
            return;
        }

        const stepIndex = getStepIndexWithinBar(positionInfo.lineSlotIndex, readConfig.stepsPerBar);
        if (stepIndex === null || stepIndex < 0 || stepIndex >= rhythmBar.notes.length) {
            return;
        }

        if (noteElementIds.includes(elementId)) {
            const currentSymbol = rhythmBar.notes[stepIndex];
            rhythmBar.notes[stepIndex] = mergePercussionNote(currentSymbol, elementId, rhythmBar.effectiveInstrument);
            return;
        }

        if (elementId != 'wiederholung') {
            rhythmBar.controls.push({
                type: elementId,
                stepIndex: stepIndex
            });
        }
    });

    applyRepeatMarkersToBars(rhythmBars, repeatBoundaries);
    const repeatRanges = buildRepeatRanges(repeatBoundaries);
    window.lastReadRhythmBars = rhythmBars;
    window.lastReadRepeatBoundaries = repeatBoundaries;
    window.lastReadRepeatRanges = repeatRanges;
    notenText = buildBarSummary(rhythmBars) + '\n' + buildRepeatRangeSummary(repeatRanges);
    console.log('readRhythmBars', rhythmBars);
    console.log('readRepeatBoundaries', repeatBoundaries);
    console.log('readRepeatRanges', repeatRanges);
    if (shouldShowAlert) {
        alert(notenText);
    }

    return {
        rhythmBars: rhythmBars,
        repeatBoundaries: repeatBoundaries,
        repeatRanges: repeatRanges,
        summaryText: notenText
    };
}

function runReadRhythm() {
    try {
        callPHPScript_lesen(zeilenAnzahl);
    } catch (error) {
        console.error('callPHPScript_lesen failed', error);
        alert('Fehler beim Auslesen: ' + error.message);
    }
}

function runAudioTest() {
    try {
        const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
        syncTimelineStateFromReadResult(readResult, buildCurrentTimelineSyncOptions());
        const playerPayload = buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries);
        window.lastPlayerRows = playerPayload;
        console.log('playerRows', playerPayload);
        openAudioTestWindow(playerPayload);
    } catch (error) {
        console.error('runAudioTest failed', error);
        alert('Fehler beim Audiotest: ' + error.message);
    }
}

// Speichern

function callPHPScript() {
    updateTimelineMetadataNode();

    if (rhythm == 'binaer') {
        var serializedRhythm = '<binaer id="rhythmus"/>';
    } else if (rhythm == 'neunaer') {
        var serializedRhythm = '<neunaer id="rhythmus"/>';
    } else {
        var serializedRhythm = '<tenaer id="rhythmus"/>';
    }

    let elementsToSave = s.selectAll(removableCanvasElementSelector);
    // Noten im Abseits löschen
    elementsToSave.forEach(function (el) {
        if (el.attr('id') == 'timeline_metadata') {
            return;
        }
        const ax = el.getBBox().cx;
        const ay = el.getBBox().cy;
        if (ax < 70 || ax > 1050 || ay < 0 || ay > 1480) {
            el.remove();
        }
    });

    elementsToSave = s.selectAll(removableCanvasElementSelector);
    elementsToSave.forEach(function (el) {
        serializedRhythm += el.toString();
    });

    const name = titel.attr('text');
    saveContentWithCheck({
        baseName: name,
        extension: ".txt",
        checkEndpoint: checkTextFileEndpoint,
        saveEndpoint: saveTextEndpoint,
        content: serializedRhythm,
        onExistingFile: function (existingBaseName, retryCheckName, saveConfirmedName) {
            const shouldOverwrite = confirm('Die Datei "' + existingBaseName + '" existiert schon!\nSoll die Datei überschrieben werden?', '');
            if (shouldOverwrite) {
                saveConfirmedName(existingBaseName);
            }
        }
    });
}

let scrollOn = false;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('#button').addEventListener('click', callPHPScript);
    document.querySelector('#button2').addEventListener('click', callPHPScript2);
    document.querySelector('#button3').addEventListener('click', runReadRhythm);
    document.querySelector('#button10').addEventListener('click', runAudioTest);
    document.querySelector('#button4').addEventListener('click', viererNoten);
    document.querySelector('#button5').addEventListener('click', dreierNoten);
    document.querySelector('#button8').addEventListener('click', neunerNoten);
    document.querySelector('#button7').addEventListener('click', function () {
        addInitialInstrumentChooser(125, 140);
    });
    document.querySelector('#button9').addEventListener('click', function () {
        addInitialFunctionChooser(260, 140);
    });
    document.querySelector('#button11').addEventListener('click', function () {
        try {
            const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
            syncTimelineStateFromReadResult(readResult, buildCurrentTimelineSyncOptions());
            timelineState.visible = !timelineState.visible;
            renderTimelinePanel();
        } catch (error) {
            console.error('Timeline konnte nicht aktualisiert werden', error);
            alert('Fehler beim Aufbau der Timeline: ' + error.message);
        }
    });
    document.querySelector('#timelineRefreshButton').addEventListener('click', function () {
        try {
            const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
            syncTimelineStateFromReadResult(readResult);
        } catch (error) {
            console.error('Timeline-Refresh fehlgeschlagen', error);
            alert('Fehler beim Aktualisieren der Timeline: ' + error.message);
        }
    });
    document.querySelector('#timelineCloseButton').addEventListener('click', function () {
        timelineState.visible = false;
        renderTimelinePanel();
    });
    document.querySelector('#timelineTempo').addEventListener('input', function (event) {
        timelineState.tempo = normalizeTimelineTempo(event.target.value);
        event.target.value = timelineState.tempo;
        updateTimelineMetadataNode();
    });
    document.querySelector('#timelineSwingFactor').addEventListener('input', function (event) {
        timelineState.swingFactor = normalizeTimelineSwingFactor(event.target.value);
        event.target.value = timelineState.swingFactor;
        updateTimelineMetadataNode();
    });
    [
        ['timelineFeelKenkeni', 'Kenkeni'],
        ['timelineFeelSangban', 'Sangban'],
        ['timelineFeelDoundoun', 'Doundoun'],
        ['timelineFeelDreierbass', 'Dreierbass'],
        ['timelineFeelDjembe1', 'Djembe_1'],
        ['timelineFeelDjembe2', 'Djembe_2'],
        ['timelineFeelDjembe3', 'Djembe_3']
    ].forEach(function (feelConfig) {
        const inputEl = document.querySelector('#' + feelConfig[0]);
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('input', function (event) {
            const nextFeelOffsets = normalizeTimelineFeelOffsets(timelineState.feelOffsets);
            nextFeelOffsets[feelConfig[1]] = normalizeTimelineFeelOffset(event.target.value);
            timelineState.feelOffsets = nextFeelOffsets;
            event.target.value = nextFeelOffsets[feelConfig[1]];
            updateTimelineMetadataNode();
        });
    });
    ['timelineSwingAnchor1', 'timelineSwingAnchor2', 'timelineSwingAnchor3', 'timelineSwingAnchor4'].forEach(function (inputId, inputIndex) {
        document.querySelector('#' + inputId).addEventListener('input', function (event) {
            const currentProfileKey = getCurrentTimelineSwingProfileKey();
            const nextProfiles = normalizeAllTimelineSwingProfiles(timelineState.swingProfile);
            const currentProfile = normalizeTimelineSwingProfile(nextProfiles[currentProfileKey], currentProfileKey);
            if (inputIndex >= currentProfile.length) {
                return;
            }
            currentProfile[inputIndex] = normalizeSwingProfileValue(event.target.value);
            nextProfiles[currentProfileKey] = currentProfile;
            timelineState.swingProfile = nextProfiles;
            event.target.value = currentProfile[inputIndex];
            updateTimelineMetadataNode();
        });
    });

    document.querySelector('#button6').addEventListener('click', function () {
        scrollOn = !scrollOn;
        if (scrollOn) {
            canv.attr({ fill: "none" });
        } else {
            canv.attr({ fill: "white" });
        }
    });
});

// Laden

callPHPScript1();

function callPHPScript1() {
    refreshFileList();
}

function onSVGLoaded(data) {
    const persistedTimelineMetadata = readTimelineMetadata(data);

    if (data.select("#rhythmus") == '<binaer id="rhythmus"/>') {
        viererNotenOhneStartChooser();
    } else if (data.select("#rhythmus") == '<neunaer id="rhythmus"/>') {
        neunerNotenOhneStartChooser();
    } else {
        dreierNotenOhneStartChooser();
    }

    let loadedElements = data.selectAll(removableCanvasElementSelector);
    s.append(loadedElements);
    loadedElements.forEach(function (el) {
        if (isInstrumentChooserNode(el) || isFunctionChooserNode(el)) {
            return;
        }
        if (el.attr("id") == "timeline_metadata") {
            return;
        }
        if (el.attr("id") == "edit_text") {
            return;
        }
        el.attr({ class: "shp" });
        el.drag(move, sel_start);
    });

    const loadedTextElements = s.selectAll("#edit_text");
    loadedTextElements.forEach(function (el) {
        bindEditableTextElement(el);
    });

    const loadedRepeatElements = s.selectAll("#wiederholung");
    loadedRepeatElements.forEach(function (el) {
        el.dblclick(cycleRepeatCount);
    });

    const loadedInstrumentChoosers = s.selectAll(instrumentChooserSelector);
    loadedInstrumentChoosers.forEach(function (el) {
        el.addClass("shp");
        el.addClass("instrument-chooser");
        el.attr({ id: nextInstrumentChooserId() });
        el.selectAll("g").forEach(function (sub) {
            sub.attr({ display: "none" });
        });
        rewireInstrumentChooser(el);
    });

    const loadedFunctionChoosers = s.selectAll(functionChooserSelector);
    loadedFunctionChoosers.forEach(function (el) {
        el.addClass("shp");
        el.addClass("function-chooser");
        el.attr({ id: nextFunctionChooserId() });
        el.selectAll("g").forEach(function (sub) {
            sub.attr({ display: "none" });
        });
        rewireFunctionChooser(el);
    });

    titel.attr({ text: loadedTitle });

    try {
        const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
        const persistedEntries = persistedTimelineMetadata && Array.isArray(persistedTimelineMetadata.entries)
            ? persistedTimelineMetadata.entries
            : [];
        timelineState.tempo = normalizeTimelineTempo(
            persistedTimelineMetadata ? persistedTimelineMetadata.tempo : 100
        );
        timelineState.swingFactor = normalizeTimelineSwingFactor(
            persistedTimelineMetadata ? persistedTimelineMetadata.swingFactor : 0
        );
        timelineState.swingProfile = normalizeAllTimelineSwingProfiles(
            persistedTimelineMetadata ? persistedTimelineMetadata.swingProfile : null
        );
        timelineState.feelOffsets = normalizeTimelineFeelOffsets(
            persistedTimelineMetadata ? persistedTimelineMetadata.feelOffsets : null
        );
        syncTimelineStateFromReadResult(readResult, {
            tempo: timelineState.tempo,
            swingFactor: timelineState.swingFactor,
            swingProfile: timelineState.swingProfile,
            feelOffsets: timelineState.feelOffsets,
            persistedEntries: persistedEntries,
            persistedVersion: persistedTimelineMetadata ? persistedTimelineMetadata.version : null,
            persistedSourceHash: persistedTimelineMetadata ? persistedTimelineMetadata.sourceHash : ''
        });
    } catch (error) {
        console.warn('Timeline-Zustand konnte nach dem Laden nicht rekonstruiert werden', error);
    }
}

function get_value(e) {
    removeCanvasElements(removableCanvasElementSelector);

    let selectedFileName;
    if (e) {
        selectedFileName = e.options[e.selectedIndex].text;
    }
    if (datei_name != "") {
        selectedFileName = datei_name;
    }
    loadRhythmFile(selectedFileName);
}

get_value();

    </script>
    <br>
</body>
</html>
