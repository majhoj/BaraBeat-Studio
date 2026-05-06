<?php
$jsSnap = @filemtime(__DIR__ . '/JS/snapNEU.svg.js') ?: 1;
$jsJq = @filemtime(__DIR__ . '/JS/jquery.min.js') ?: 1;
$jsSel = @filemtime(__DIR__ . '/JS/selection_drag_7.js') ?: 1;
$jsFn = @filemtime(__DIR__ . '/JS/functions.js') ?: 1;
$jsTimeline = @filemtime(__DIR__ . '/JS/timeline.js') ?: 1;
$cssIndex = @filemtime(__DIR__ . '/CSS/index_style.css') ?: 1;
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
    <script src="JS/timeline.js?v=<?php echo $jsTimeline; ?>"></script>
    <link rel="stylesheet" href="CSS/index_style.css?v=<?php echo $cssIndex; ?>">
</head>

<body class="app-body">
    <?php
    $file_name = $_GET["file"] ?? "";
    echo "<script>datei_name = " . json_encode($file_name) . ";</script>";
    ?>

    <nav id="appMenuBar" aria-label="Hauptmenü">
        <details class="app-menu">
            <summary>Datei</summary>
            <div class="app-menu-panel">
                <div class="menu-file-picker">
                    <span>Datei laden</span>
                    <span id="auswahl" name="auswahl"></span>
                </div>
                <button type="button" id="button">Datei speichern</button>
                <button type="button" id="button2">Als SVG speichern</button>
                <button type="button" id="button12">Als PDF speichern</button>
            </div>
        </details>
        <details class="app-menu">
            <summary>Notenblatt</summary>
            <div class="app-menu-panel">
                <button type="button" id="button4">Binäres Notenblatt</button>
                <button type="button" id="button5">Tenäres Notenblatt</button>
                <button type="button" id="button8">Tenäres 9/8 Notenblatt</button>
                <button type="button" id="button3">Noten lesen</button>
            </div>
        </details>
        <details class="app-menu">
            <summary>Einfügen</summary>
            <div class="app-menu-panel">
                <button type="button" id="button7">Instrument-Chooser</button>
                <button type="button" id="button9">Funktions-Chooser</button>
            </div>
        </details>
        <details class="app-menu">
            <summary>Werkzeuge</summary>
            <div class="app-menu-panel">
                <button type="button" id="button10">Audiotest</button>
                <button type="button" id="button11">Abschnittstimeline</button>
                <button type="button" id="button6">Scroll</button>
            </div>
        </details>
        <form action="" name="uploadForm" class="hidden-upload-form">
            <input type="hidden" size="40" id="iofield" name="iofield" />
        </form>
    </nav>

    <div id="timelinePanel" hidden>
        <div class="timeline-panel-header">
            <div>
                <div class="timeline-panel-title">Abschnittstimeline</div>
                <div id="timelineStatus" class="timeline-status">Pattern werden aus dem Notenblatt gelesen.</div>
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
                <p class="timeline-column-note">Jede Passage aus dem Notenblatt erscheint hier einmal. Ziehen oder ueber <code>+</code> ans Ende der Timeline anfuegen.</p>
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

function resolveInsertTemplate(templateValue) {
    return typeof templateValue === 'function' ? templateValue() : templateValue;
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
        const resolvedTemplateElement = resolveInsertTemplate(templateElement);
        if (!resolvedTemplateElement) {
            return;
        }
        insertedElement = createPaletteClone(resolvedTemplateElement, elementId, offsetX, offsetY);
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
    timelineState.sheetHash = '';
    timelineState.sheetLoop = false;
    timelineState.sheetLoopCount = false;
    timelineState.tempo = 100;
    timelineState.swingFactor = 0;
    timelineState.swingProfile = normalizeAllTimelineSwingProfiles();
    timelineState.feelOffsets = normalizeTimelineFeelOffsets();
    renderTimelinePanel();
}

function snapElementToVerticalTarget(element) {
    if (!element || typeof element.getBBox !== 'function' || typeof element.transform !== 'function') {
        return element;
    }

    const bbox = element.getBBox();
    const referenceY = typeof getElementSnapReferenceY === 'function'
        ? getElementSnapReferenceY(element, bbox)
        : bbox.cy;
    const snappedY = typeof snapToVerticalTargets === 'function'
        ? snapToVerticalTargets(referenceY)
        : referenceY;
    const deltaY = snappedY - referenceY;

    if (Math.abs(deltaY) < 0.001) {
        return element;
    }

    const transformState = element.transform();
    const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
    const nextX = localMatrix ? localMatrix.e : 0;
    const nextY = (localMatrix ? localMatrix.f : 0) + deltaY;
    element.transform("translate(" + nextX + "," + nextY + ")");
    return element;
}

function addInitialInstrumentChooser(x, y) {
    const chooserElement = createInstrumentChooser(s, x, y).addClass("shp").attr({ id: nextInstrumentChooserId() });
    return snapElementToVerticalTarget(chooserElement);
}

function addInitialFunctionChooser(x, y) {
    const chooserElement = createFunctionChooser(s, x, y).addClass("shp").attr({ id: nextFunctionChooserId() });
    return snapElementToVerticalTarget(chooserElement);
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

    renderLegend(initialChooserX);

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
function addLegendEntry(symbol, label, symbolX, symbolY, labelOffsetX, labelOffsetY, legendOffsetX) {
    const shiftedSymbolX = symbolX + legendOffsetX;
    const legendClone = symbol.clone();
    s.append(legendClone);
    legendClone.attr({ id: "basis", transform: "t" + shiftedSymbolX + "," + symbolY });
    legendClone.addClass("legend-entry");
    s.text(shiftedSymbolX + labelOffsetX, symbolY + labelOffsetY, label).attr({
        id: "basis",
        class: "legend-entry",
        'font-size': 15,
        'font-family': 'sans-serif'
    });
    return legendClone;
}

function renderLegend(initialChooserX) {
    const legendAnchorX = Number.isFinite(initialChooserX) ? initialChooserX : 125;
    const toneLegendReferenceLeft = 92 + ton.getBBox().x;
    const legendOffsetX = legendAnchorX - toneLegendReferenceLeft;

    removeCanvasElements(".legend-entry");

    ton_c = addLegendEntry(ton, "Tone", 92, 1166, 45, 178, legendOffsetX);
    bass_c = addLegendEntry(bass, "Bass", 157, 1146, 46, 198, legendOffsetX);
    slap_c = addLegendEntry(slap, "Slap/Glocke", 222, 1126, 45, 218, legendOffsetX);
    flam_ton_c = addLegendEntry(flam_ton, "Flam mit Tones", 337, 1105, 49, 240, legendOffsetX);
    flam_slap_c = addLegendEntry(flam_slap, "Flam mit Slaps", 475, 1087, 49, 259, legendOffsetX);
    flam_bass_slap_c = addLegendEntry(flam_bass_slap, "Flam mit Bass und Slaps", 613, 1069, 49, 279, legendOffsetX);
    ton_g_c = addLegendEntry(ton_g, "gedämpfter Tone", 92, 1078, 48, 299, legendOffsetX);
    slap_g_c = addLegendEntry(slap_g, "gedämpfter Slap / Klick", 240, 1058, 48, 319, legendOffsetX);
    In_c = addLegendEntry(In, "In", 428, 1034, 44, 343, legendOffsetX);
    Out_c = addLegendEntry(Out, "Out", 470, 1011, 44, 366, legendOffsetX);
    repeatMarkerLegendClone = addLegendEntry(repeatMarkerGroup, "Wiederholung", 521, 968, 44, 409, legendOffsetX);
}

renderLegend(125);


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
	insertTone = bindPaletteInsert(ton, function () { return ton_c; }, "tone", function () { return gridSizeX; }, 0);
	insertBass = bindPaletteInsert(bass, function () { return bass_c; }, "bass", function () { return gridSizeX; }, 0);
	insertSlap = bindPaletteInsert(slap, function () { return slap_c; }, "slap", function () { return gridSizeX; }, 0);
	insertMuffledTone = bindPaletteInsert(ton_g, function () { return ton_g_c; }, "tone_muffled", function () { return gridSizeX; }, 0);
	insertMuffledSlap = bindPaletteInsert(slap_g, function () { return slap_g_c; }, "slap_muffled", function () { return gridSizeX; }, 0);
	insertFlamTone = bindPaletteInsert(flam_ton, function () { return flam_ton_c; }, "tone_flam", function () { return gridSizeX; }, 0);
	insertFlamSlap = bindPaletteInsert(flam_slap, function () { return flam_slap_c; }, "slap_flam", function () { return gridSizeX; }, 0);
	insertFlamBassSlap = bindPaletteInsert(flam_bass_slap, function () { return flam_bass_slap_c; }, "bass_slap_flam", function () { return gridSizeX; }, 0);
	insertInMarker = bindPaletteInsert(In, function () { return In_c; }, "in", function () { return gridSizeX; }, -2);
insertOutMarker = bindPaletteInsert(Out, function () { return Out_c; }, "out", function () { return gridSizeX; }, 0);

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
        zahl = 1;
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
    function () { return repeatMarkerLegendClone; },
    "wiederholung",
    function () { return repeatMarkerGridOffsetX; },
    2,
    function (repeatMarkerElement) {
        repeatMarkerElement.dblclick(cycleRepeatCount);
    }
);


// Als SVG speichern

function buildExportSvgContent() {
    let svgContent = "";
    let elementsToExport = s.selectAll(exportableElementSelector);
    let contentBounds = {
        minX: Infinity,
        minY: Infinity,
        maxX: -Infinity,
        maxY: -Infinity
    };
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
        if (el.attr('id') != 'timeline_metadata') {
            const bbox = el.getBBox();
            if (bbox && Number.isFinite(bbox.x) && Number.isFinite(bbox.y)) {
                contentBounds.minX = Math.min(contentBounds.minX, bbox.x);
                contentBounds.minY = Math.min(contentBounds.minY, bbox.y);
                contentBounds.maxX = Math.max(contentBounds.maxX, bbox.x + bbox.width);
                contentBounds.maxY = Math.max(contentBounds.maxY, bbox.y + bbox.height);
            }
        }
        svgContent += el.toString();
    });

    const hasFiniteBounds = Number.isFinite(contentBounds.minX) &&
        Number.isFinite(contentBounds.minY) &&
        Number.isFinite(contentBounds.maxX) &&
        Number.isFinite(contentBounds.maxY);
    const exportPadding = 18;
    const viewBoxX = hasFiniteBounds ? Math.max(0, contentBounds.minX - exportPadding) : 0;
    const viewBoxY = hasFiniteBounds ? Math.max(0, contentBounds.minY - exportPadding) : 0;
    const viewBoxWidth = hasFiniteBounds
        ? Math.max(1, (contentBounds.maxX - contentBounds.minX) + exportPadding * 2)
        : 1050;
    const viewBoxHeight = hasFiniteBounds
        ? Math.max(1, (contentBounds.maxY - contentBounds.minY) + exportPadding * 2)
        : 1480;

    return '<svg height="' + viewBoxHeight + '" version="1.1" width="' + viewBoxWidth + '" viewBox="' +
        [viewBoxX, viewBoxY, viewBoxWidth, viewBoxHeight].join(' ') +
        '" preserveAspectRatio="xMidYMin meet" xmlns="http://www.w3.org/2000/svg" id="myRect1"><desc>Created with Snap</desc><defs></defs>' +
        svgContent +
        '</svg>';
}

function callPHPScript2() {
    const svgContent = buildExportSvgContent();

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

function exportCurrentSheetAsPdf() {
    const svgContent = buildExportSvgContent();
    const documentTitle = titel.attr('text') || 'Notenblatt';
    const printWindow = window.open('', '_blank');

    if (!printWindow) {
        alert('Das PDF-Fenster konnte nicht geöffnet werden. Bitte Pop-up-Blocker prüfen.');
        return;
    }

    const html = `<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>${String(documentTitle).replace(/[&<>"]/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[char];
  })}</title>
  <style>
    @page { size: A4 portrait; margin: 8mm; }
    html, body {
      margin: 0;
      padding: 0;
      background: white;
    }
    body {
      display: block;
      padding: 0;
      box-sizing: border-box;
    }
    svg {
      width: 188mm;
      max-width: 100%;
      max-height: 279mm;
      height: auto;
      display: block;
      margin: 0 auto;
      page-break-inside: avoid;
      break-inside: avoid;
    }
  </style>
</head>
<body>
${svgContent}
<script>
  window.addEventListener('load', function () {
    setTimeout(function () {
      window.focus();
      window.print();
    }, 150);
  });
<\/script>
</body>
</html>`;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
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
        return 'continue';
    }
    if (!isNaN(Number(trimmedRepeatText))) {
        return Number(trimmedRepeatText);
    }
    return trimmedRepeatText;
}

function hasBarContentForRepeatLoop(bar) {
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

function getLastActiveBarIndex(rhythmBars) {
    for (let barIndex = rhythmBars.length - 1; barIndex >= 0; barIndex--) {
        if (hasBarContentForRepeatLoop(rhythmBars[barIndex])) {
            return barIndex + 1;
        }
    }
    return rhythmBars.length;
}

function buildRepeatRanges(repeatBoundaries, rhythmBars) {
    const repeatRanges = [];
    const repeatStartStack = [];
    const lastActiveBarIndex = Array.isArray(rhythmBars)
        ? getLastActiveBarIndex(rhythmBars)
        : repeatBoundaries.length - 1;

    repeatBoundaries.forEach(function (boundary) {
        const sortedStartMarkers = boundary.startMarkers.slice().sort(function (markerA, markerB) {
            return Math.abs(markerB.x - markerB.boundaryLineX) - Math.abs(markerA.x - markerA.boundaryLineX);
        });
        const sortedEndMarkers = boundary.endMarkers.slice().sort(function (markerA, markerB) {
            return Math.abs(markerA.x - markerA.boundaryLineX) - Math.abs(markerB.x - markerB.boundaryLineX);
        });

        sortedEndMarkers.forEach(function (endMarker) {
            if (endMarker.count === 'continue' && endMarker.boundaryIndex !== lastActiveBarIndex) {
                return;
            }
            let matchingStartMarker = repeatStartStack.pop();
            if (endMarker.count === 'continue' && endMarker.boundaryIndex === lastActiveBarIndex) {
                const sheetLoopStartIndex = repeatStartStack.findIndex(function (startMarker) {
                    return startMarker.boundaryIndex === 0 && startMarker.count === 'continue';
                });
                if (sheetLoopStartIndex !== -1) {
                    repeatRanges.push({
                        startBoundary: 0,
                        endBoundary: endMarker.boundaryIndex,
                        startBar: 1,
                        endBar: endMarker.boundaryIndex,
                        count: 'loop'
                    });
                    repeatStartStack.splice(sheetLoopStartIndex, 1);
                }
            }
            if (!matchingStartMarker) {
                return;
            }
            repeatRanges.push({
                startBoundary: matchingStartMarker.boundaryIndex,
                endBoundary: endMarker.boundaryIndex,
                startBar: matchingStartMarker.boundaryIndex + 1,
                endBar: endMarker.boundaryIndex,
                count: matchingStartMarker.boundaryIndex === 0 &&
                    endMarker.boundaryIndex === lastActiveBarIndex &&
                    endMarker.count === 'continue'
                    ? 'loop'
                    : endMarker.count
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
    const lastActiveBarIndex = getLastActiveBarIndex(rhythmBars);

    repeatBoundaries.forEach(function (boundary) {
        const startBar = rhythmBars[boundary.index];
        const endBar = rhythmBars[boundary.index - 1];

        if (startBar && boundary.startMarkers.length > 0) {
            startBar.repeat.start = boundary.startMarkers.filter(function (marker) {
                return !(boundary.index === 0 && marker.count === 'continue');
            }).map(function (marker) {
                return marker.count;
            });
        }
        if (endBar && boundary.endMarkers.length > 0) {
            endBar.repeat.end = boundary.endMarkers.filter(function (marker) {
                const previousBoundary = repeatBoundaries[boundary.index - 1];
                const hasLocalStartForFinalContinue = boundary.index === lastActiveBarIndex &&
                    marker.count === 'continue' &&
                    previousBoundary &&
                    previousBoundary.startMarkers.some(function (startMarker) {
                        return startMarker.count === 'continue';
                    });
                return hasLocalStartForFinalContinue ||
                    !(boundary.index === lastActiveBarIndex && marker.count === 'continue');
            }).map(function (marker) {
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
    const repeatRanges = buildRepeatRanges(repeatBoundaries, rhythmBars);
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
        syncTimelineStateFromReadResultIfNeeded(readResult, buildCurrentTimelineSyncOptions());
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

function closeAppMenus() {
    document.querySelectorAll('#appMenuBar details.app-menu[open]').forEach(function (menuEl) {
        menuEl.open = false;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#appMenuBar details.app-menu').forEach(function (menuEl) {
        menuEl.addEventListener('toggle', function () {
            if (!menuEl.open) {
                return;
            }
            document.querySelectorAll('#appMenuBar details.app-menu').forEach(function (otherMenu) {
                if (otherMenu !== menuEl) {
                    otherMenu.open = false;
                }
            });
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#appMenuBar')) {
            closeAppMenus();
        }
    });

    document.querySelector('#button').addEventListener('click', callPHPScript);
    document.querySelector('#button2').addEventListener('click', callPHPScript2);
    document.querySelector('#button12').addEventListener('click', exportCurrentSheetAsPdf);
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
            syncTimelineStateFromReadResultIfNeeded(readResult, buildCurrentTimelineSyncOptions());
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

    [
        '#button',
        '#button2',
        '#button12',
        '#button3',
        '#button4',
        '#button5',
        '#button6',
        '#button7',
        '#button8',
        '#button9',
        '#button10',
        '#button11'
    ].forEach(function (selector) {
        const buttonEl = document.querySelector(selector);
        if (!buttonEl) {
            return;
        }
        buttonEl.addEventListener('click', function () {
            closeAppMenus();
        });
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
    closeAppMenus();

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
