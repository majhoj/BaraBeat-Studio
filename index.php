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
            <input type="button" id="button4" value="Binäres Notenblatt" />
            <input type="button" id="button5" value="Tenäres Notenblatt" />
            <input type="button" id="button8" value="Tenäres 9/8 Notenblatt" />
            <input type="button" id="button6" value="Scroll" />
            <input type="button" id="button7" value="Instrument" />
        </form>
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
const removableCanvasElementSelector = canvasElementSelector + ", " + instrumentChooserSelector;
const exportableElementSelector = "#notenlinien, #basis, " + removableCanvasElementSelector;
const readableElementSelector = "#edit_text, #wiederholung, " + instrumentChooserSelector;
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
    removeCanvasElements("#notenlinien, .shp, " + instrumentChooserSelector);
}

function addInitialInstrumentChooser(x, y) {
    return createInstrumentChooser(s, x, y).addClass("shp").attr({ id: nextInstrumentChooserId() });
}

function drawRhythmSheet(config) {
    const gridLineStepX = 850 / config.subdivisionCount;
    const beatBarWidth = config.beatBarWidth;
    const initialChooserX = 100 + gridLineStepX * config.beatStartIndices[0];
    const shouldAddInitialChooser = config.addInitialChooser !== false;

    rhythm = config.rhythmName;
    gridSize = config.gridSizeValue;
    gridSizeY = 2.5;
    gridSizeX = config.gridSizeXValue;
    repeatMarkerGridOffsetX = config.repeatMarkerOffsetXValue;

    clear_all();
    if (shouldAddInitialChooser) {
        addInitialInstrumentChooser(initialChooserX, 140);
    }
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
        addInitialChooser: false
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
        addInitialChooser: false
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
        addInitialChooser: false
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
slap_g_c = addLegendEntry(slap_g, "gedämpfter Slap", 240, 1058, 48, 319);
In_c = addLegendEntry(In, "In", 382, 1034, 44, 343);
Out_c = addLegendEntry(Out, "Out", 424, 1011, 44, 366);
repeatMarkerLegendClone = addLegendEntry(repeatMarkerGroup, "Wiederholung", 475, 968, 44, 409);


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
            getLineSlotIndex: function (centerX) {
                return Math.round(((centerX - 34) / 16.5) - 5);
            }
        };
    }
    return {
        rhythmLabel: "tenär 9/8",
        stepsPerBar: 18,
        totalStepsPerLine: 36,
        getLineSlotIndex: function (centerX) {
            return Math.round(((centerX - 45.5) / 21.25) - 5);
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
    if (isInstrumentChooserNode(element)) {
        const transformState = typeof element.transform === 'function' ? element.transform() : null;
        const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
        if (localMatrix) {
            return {
                x: localMatrix.e,
                y: localMatrix.f
            };
        }
        const chooserBounds = element.getBBox();
        return {
            x: chooserBounds.x,
            y: chooserBounds.y
        };
    }
    return {
        x: element.getBBox().cx,
        y: element.getBBox().cy
    };
}

function getBarIndexFromPosition(centerX, centerY, readConfig, lineCount) {
    const rawLineSlotIndex = readConfig.getLineSlotIndex(centerX);
    let lineSlotIndex = rawLineSlotIndex;
    if (lineSlotIndex > readConfig.stepsPerBar) {
        lineSlotIndex -= 2;
    }
    const barOffset = rawLineSlotIndex > readConfig.stepsPerBar + 1 ? 1 : 0;
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
    let lineSlotIndex = rawLineSlotIndex;
    if (lineSlotIndex > readConfig.stepsPerBar) {
        lineSlotIndex -= 2;
    }
    const barOffset = rawLineSlotIndex > readConfig.stepsPerBar + 1 ? 1 : 0;
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
    if (isInstrumentChooserNode(element)) {
        const chooserLabel = element.select("text");
        const chooserText = chooserLabel ? (chooserLabel.attr('text') || chooserLabel.node.textContent || '') : '';
        if (chooserText == 'Instrument') {
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
        return repeatSide == 'end' ? 'loop' : true;
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

        sortedStartMarkers.forEach(function (startMarker) {
            repeatStartStack.push(startMarker);
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
    if (instrumentName == 'Kenkeni' || instrumentName == 'Sangban' || instrumentName == 'Doundoun') {
        if (noteId == 'slap' && currentSymbol == 'f') {
            return 'Bell';
        }
        if (noteId == 'tone' && currentSymbol == 'f') {
            return 'Open';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'f') {
            return 'Muffled';
        }
        if (noteId == 'tone_muffled' && currentSymbol == 'Bell') {
            return 'Bell_Muffled';
        }
        if (noteId == 'slap' && currentSymbol == 'Muffled') {
            return 'Bell_Muffled';
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
        if (bar.instrument) {
            bar.effectiveInstrument = bar.instrument;
            if (bar.instrument.search(/^End/i) === 0) {
                currentInstrument = '';
            } else {
                currentInstrument = bar.instrument;
            }
        } else {
            bar.effectiveInstrument = currentInstrument;
        }

        if (bar.label) {
            bar.effectiveLabel = bar.label;
            currentLabel = bar.label;
        } else {
            bar.effectiveLabel = currentLabel;
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
        summaryText +=
            'Takt ' + bar.index + ': ' +
            (bar.effectiveInstrument || '') + ', ' +
            (bar.effectiveLabel || '') + ', Wiederholungsmarker = Start[' +
            displayStartMarkers.join(', ') + '], Ende[' + displayEndMarkers.join(', ') + ']\n' +
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

function callPHPScript_lesen(anzahl) {
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
    alert(notenText);
}

function runReadRhythm() {
    try {
        callPHPScript_lesen(zeilenAnzahl);
    } catch (error) {
        console.error('callPHPScript_lesen failed', error);
        alert('Fehler beim Auslesen: ' + error.message);
    }
}

// Speichern

function callPHPScript() {
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
    document.querySelector('#button4').addEventListener('click', viererNoten);
    document.querySelector('#button5').addEventListener('click', dreierNoten);
    document.querySelector('#button8').addEventListener('click', neunerNoten);
    document.querySelector('#button7').addEventListener('click', function () {
        addInitialInstrumentChooser(125, 140);
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
        if (isInstrumentChooserNode(el)) {
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

    titel.attr({ text: loadedTitle });
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
