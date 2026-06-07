<?php
$jsSnap = @filemtime(__DIR__ . '/JS/snapNEU.svg.js') ?: 1;
$jsJq = @filemtime(__DIR__ . '/JS/jquery.min.js') ?: 1;
$jsLocalLibrary = @filemtime(__DIR__ . '/JS/localLibrary.js') ?: 1;
$jsServerLibrary = @filemtime(__DIR__ . '/JS/serverLibrary.js') ?: 1;
$jsSel = @filemtime(__DIR__ . '/JS/selection_drag_7.js') ?: 1;
$jsFn = @filemtime(__DIR__ . '/JS/functions.js') ?: 1;
$jsTimeline = @filemtime(__DIR__ . '/JS/timeline.js') ?: 1;
$jsPractice = @filemtime(__DIR__ . '/JS/practice.js') ?: 1;
$cssIndex = @filemtime(__DIR__ . '/CSS/index_style.css') ?: 1;
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><BaraBeat-Studio></title>
    <script src="JS/snapNEU.svg.js?v=<?php echo $jsSnap; ?>"></script>
    <script src="JS/jquery.min.js?v=<?php echo $jsJq; ?>"></script>
    <script src="JS/localLibrary.js?v=<?php echo $jsLocalLibrary; ?>"></script>
    <script src="JS/serverLibrary.js?v=<?php echo $jsServerLibrary; ?>"></script>
    <script src="JS/selection_drag_7.js?v=<?php echo $jsSel; ?>"></script>
    <script src="JS/functions.js?v=<?php echo $jsFn; ?>"></script>
    <script src="JS/timeline.js?v=<?php echo $jsTimeline; ?>"></script>
    <script src="JS/practice.js?v=<?php echo $jsPractice; ?>"></script>
    <link rel="stylesheet" href="CSS/index_style.css?v=<?php echo $cssIndex; ?>">
</head>

<body class="app-body">
    <?php
    $file_name = $_GET["file"] ?? "";
    echo "<script>datei_name = " . json_encode($file_name) . ";</script>";
    ?>

    <nav id="appMenuBar" aria-label="Hauptmenü">
        <div class="app-logo" aria-label="BaraBeat Studio">
            <svg viewBox="0 0 64 64" role="img" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="logoSkyGradient" x1="14" y1="6" x2="50" y2="38" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#ffe800" />
                        <stop offset="0.35" stop-color="#ff9d00" />
                        <stop offset="0.72" stop-color="#e94112" />
                        <stop offset="1" stop-color="#7b170e" />
                    </linearGradient>
                    <clipPath id="logoCircleClip">
                        <circle cx="32" cy="24" r="22" />
                    </clipPath>
                </defs>
                <circle cx="32" cy="24" r="22" fill="url(#logoSkyGradient)" />
                <g clip-path="url(#logoCircleClip)">
                    <path d="M6 16 C18 19, 25 9, 38 12 C49 14, 55 9, 62 7" class="logo-wave" />
                    <path d="M5 26 C18 29, 26 18, 39 22 C49 24, 57 18, 64 17" class="logo-wave" />
                    <path d="M4 36 C17 40, 27 27, 41 31 C51 34, 58 28, 65 27" class="logo-wave" />
                </g>
                <path d="M10 37 C20 41, 44 41, 54 37 C53 51, 44 61, 32 62 C20 61, 11 51, 10 37 Z" class="logo-drum-body" />
                <path d="M19 44 C21 49, 23 55, 21 61" class="logo-drum-cutout" />
                <path d="M45 44 C43 49, 41 55, 43 61" class="logo-drum-cutout" />
                <path d="M30 44 C27 50, 27 57, 28 62 L36 62 C37 57, 37 50, 34 44 Z" class="logo-drum-cutout" />
                <circle cx="17" cy="43" r="2.2" class="logo-drum-dot" />
                <circle cx="26" cy="46" r="2" class="logo-drum-dot" />
                <circle cx="38" cy="46" r="2" class="logo-drum-dot" />
                <circle cx="47" cy="43" r="2.2" class="logo-drum-dot" />
                <path d="M9 36 C20 42, 44 42, 55 36" class="logo-drum-rim" />
            </svg>
        </div>
        <details class="app-menu">
            <summary>Datei</summary>
            <div class="app-menu-panel">
                <button type="button" id="openFileDialogButton">Öffnen...</button>
                <button type="button" id="saveFileDialogButton">Speichern</button>
                <button type="button" id="saveAsFileDialogButton">Speichern als...</button>
                <button type="button" id="exportFileDialogButton">Exportieren...</button>
            </div>
        </details>
        <details class="app-menu">
            <summary>Notenblatt</summary>
            <div class="app-menu-panel">
                <button type="button" id="button4">Binäres Notenblatt</button>
                <button type="button" id="button5">Tenäres Notenblatt</button>
                <button type="button" id="button8">Tenäres 9/8 Notenblatt</button>
                <button type="button" id="addSheetPageButton">Blatt hinzufügen</button>
                <button type="button" id="deleteSheetPageButton">Blatt löschen</button>
                <button type="button" id="button3">Noten lesen</button>
            </div>
        </details>
        <details class="app-menu">
            <summary>Einfügen</summary>
            <div class="app-menu-panel">
                <button type="button" id="button7">Instrument-Chooser</button>
                <button type="button" id="button9">Funktions-Chooser</button>
                <button type="button" id="resetPaletteButton">Palette zurücksetzen</button>
            </div>
        </details>
        <details class="app-menu" data-mobile-practice-menu="true">
            <summary><span class="desktop-menu-label">Werkzeuge</span><span class="mobile-menu-label">Üben</span></summary>
            <div class="app-menu-panel">
                <button type="button" id="button10" hidden>Audiotest</button>
                <button type="button" id="practiceButton">Üben</button>
                <button type="button" id="button11">Arrangieren</button>
                <details class="app-submenu">
                    <summary>Template</summary>
                    <div class="app-submenu-panel">
                        <button type="button" id="themeClearButton">Klar</button>
                        <button type="button" id="themePlayfulButton">Verspielt</button>
                        <button type="button" id="themeEarthButton">Erdig</button>
                    </div>
                </details>
            </div>
        </details>
        <button type="button" id="mobilePatternChooserButton" class="mobile-menu-action" hidden>Patternauswahl öffnen</button>
        <form action="" name="uploadForm" class="hidden-upload-form">
            <input type="hidden" size="40" id="iofield" name="iofield" />
        </form>
    </nav>

    <section id="mobileStartInfo" class="mobile-start-info" aria-live="polite">
        <h1>BaraBeat Studio</h1>
        <p>Öffne ein Notenblatt und starte den Übungsmodus. Auf Smartphones ist die Ansicht zum Üben und Abspielen vorbereitet.</p>
    </section>

    <section id="mobileOrientationNotice" class="mobile-orientation-notice" aria-live="polite" aria-hidden="true">
        <div>
            <h1>Bitte hochkant drehen</h1>
            <p>Die Smartphone-Ansicht ist für den Portrait-Modus vorbereitet.</p>
        </div>
    </section>

    <div id="fileDialog" class="file-dialog-backdrop" hidden>
        <section class="file-dialog" role="dialog" aria-modal="true" aria-labelledby="fileDialogTitle">
            <header class="file-dialog-titlebar">
                <div class="file-dialog-window-controls" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <h2 id="fileDialogTitle">Datei</h2>
            </header>
            <div class="file-dialog-main">
                <aside class="file-dialog-sidebar" aria-label="Quellen">
                    <div class="file-dialog-sidebar-section">Quellen</div>
                    <button type="button" class="file-dialog-source is-active" data-source="local">Lokal</button>
                    <button type="button" class="file-dialog-source" data-source="server">Server</button>
                    <div class="file-dialog-sidebar-section">Sammlungen</div>
                    <button type="button" class="file-dialog-filter is-active" data-filter="all">Alle</button>
                    <button type="button" class="file-dialog-filter" data-filter="published">Veröffentlicht</button>
                    <button type="button" class="file-dialog-filter" data-filter="local-only">Nur lokal</button>
                    <button type="button" class="file-dialog-filter" data-filter="modified">Geändert</button>
                </aside>
                <div class="file-dialog-content">
                    <div class="file-dialog-fields">
                        <label for="fileDialogName">Name:</label>
                        <input type="text" id="fileDialogName" autocomplete="off" />
                        <label for="fileDialogTags">Tags:</label>
                        <input type="text" id="fileDialogTags" autocomplete="off" />
                    </div>
                    <div class="file-dialog-toolbar">
                        <button type="button" id="fileDialogRefreshButton" title="Aktualisieren">↻</button>
                        <span id="fileDialogFolderName" class="file-dialog-folder-name">Lokal</span>
                        <input type="search" id="fileDialogSearch" placeholder="Suchen" />
                    </div>
                    <div class="file-dialog-table-wrap">
                        <table class="file-dialog-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Geändert</th>
                                </tr>
                            </thead>
                            <tbody id="fileDialogList"></tbody>
                        </table>
                        <div id="fileDialogEmpty" class="file-dialog-empty" hidden>Keine Dateien gefunden.</div>
                    </div>
                </div>
            </div>
            <footer class="file-dialog-footer">
                <div class="file-dialog-left-actions">
                    <button type="button" id="fileDialogNewFolderButton">Neuer Ordner</button>
                    <button type="button" id="fileDialogRenameButton">Umbenennen</button>
                    <button type="button" id="fileDialogDeleteButton">Löschen</button>
                    <button type="button" id="fileDialogUnpublishButton">Veröffentlichung löschen</button>
                </div>
                <label class="file-dialog-format" for="fileDialogFormat">
                    Format:
                    <select id="fileDialogFormat">
                        <option value="svg">SVG</option>
                        <option value="pdf">PDF</option>
                    </select>
                </label>
                <div class="file-dialog-actions">
                    <button type="button" id="fileDialogCancelButton">Abbrechen</button>
                    <button type="button" id="fileDialogConfirmButton" class="primary">Öffnen</button>
                </div>
            </footer>
        </section>
    </div>

    <div id="timelinePanel" hidden>
        <div class="timeline-sticky-region">
            <div class="timeline-panel-header">
                <div>
                    <div id="timelineTitle" class="timeline-panel-title">Arrangement</div>
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
                    <button type="button" id="timelineSwingProfileButton">Swing-Profil</button>
                    <button type="button" id="timelineFeelProfileButton">Feel</button>
                    <button type="button" id="timelineVolumeButton">Lautstärke</button>
                    <button type="button" id="timelineRefreshButton">Aus Blatt aktualisieren</button>
                    <button type="button" id="timelineCloseButton">Schließen</button>
                </div>
            </div>
            <section class="timeline-player-panel" hidden>
                <iframe id="timelineAudioFrame" name="timelineAudioFrame" title="Audioplayer Arrangement"></iframe>
            </section>
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

    <div id="practicePanel" hidden>
        <div class="timeline-panel-header practice-panel-header">
            <div>
                <div id="practiceTitle" class="timeline-panel-title practice-panel-title">Übungsmodus</div>
            </div>
            <div class="timeline-panel-actions">
                <button type="button" id="practicePatternChooserToggle" aria-expanded="false" aria-controls="practicePatternChooser">
                    Patternauswahl öffnen
                </button>
                <button type="button" id="practiceCloseButton">Schließen</button>
            </div>
        </div>
        <div id="practicePatternChooser" class="timeline-panel-body practice-panel-body" hidden>
            <section class="practice-settings-column">
                <h3 class="practice-options-title">Einstellungen</h3>
                <div class="practice-timing-options">
                    <label class="timeline-tempo-control" for="practiceTempo">
                        Tempo
                        <input type="number" id="practiceTempo" min="30" max="180" step="1" value="100" />
                    </label>
                    <label class="timeline-swing-control" for="practiceSwingFactor">
                        Swing
                        <input type="number" id="practiceSwingFactor" min="0" max="100" step="1" value="0" />
                    </label>
                    <button type="button" id="practiceSwingProfileButton">Swing-Profil</button>
                    <button type="button" id="practiceFeelProfileButton">Feel</button>
                </div>
                <div class="practice-pattern-options">
                    <label class="timeline-tempo-control" for="practiceAccompanimentStart">
                        Begleitung startet
                        <select id="practiceAccompanimentStart">
                            <option value="immediate">Sofort</option>
                            <option value="afterCall">Nach Call</option>
                            <option value="afterIntro">Nach Intro</option>
                            <option value="afterCallIntro">Nach Call + Intro</option>
                        </select>
                    </label>
                    <label class="timeline-tempo-control" for="practiceWithoutSoloLoops">
                        Ohne Übungsteil
                        <span class="practice-stepper">
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithoutSoloLoops" data-practice-step-delta="-1" aria-label="Ohne Übungsteil verringern">-</button>
                            <input type="number" id="practiceWithoutSoloLoops" min="0" max="32" step="1" value="1" />
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithoutSoloLoops" data-practice-step-delta="1" aria-label="Ohne Übungsteil erhöhen">+</button>
                        </span>
                    </label>
                    <label class="timeline-tempo-control" for="practiceWithSoloLoops">
                        Mit Übungsteil
                        <span class="practice-stepper">
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithSoloLoops" data-practice-step-delta="-1" aria-label="Mit Übungsteil verringern">-</button>
                            <input type="number" id="practiceWithSoloLoops" min="1" max="32" step="1" value="1" />
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithSoloLoops" data-practice-step-delta="1" aria-label="Mit Übungsteil erhöhen">+</button>
                        </span>
                    </label>
                    <label class="timeline-tempo-control" for="practiceAccompanimentBetweenPatterns">
                        Zwischen Übungsteilen
                        <input type="checkbox" id="practiceAccompanimentBetweenPatterns" />
                    </label>
                    <label class="timeline-tempo-control" for="practicePauseAccompanimentForLeadInPatterns">
                        Begleitung stoppt bei Call/Intro
                        <input type="checkbox" id="practicePauseAccompanimentForLeadInPatterns" />
                    </label>
                    <label class="timeline-tempo-control" for="practiceRepeatCount" id="practiceRepeatCountControl">
                        Wiederholen
                        <span class="practice-stepper">
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceRepeatCount" data-practice-step-delta="-1" aria-label="Wiederholungen verringern">-</button>
                            <input type="number" id="practiceRepeatCount" min="1" max="999" step="1" value="4" />
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceRepeatCount" data-practice-step-delta="1" aria-label="Wiederholungen erhöhen">+</button>
                        </span>
                    </label>
                    <label class="timeline-tempo-control" for="practiceTimerMinutes">
                        Timer min
                        <span class="practice-stepper">
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceTimerMinutes" data-practice-step-delta="-1" aria-label="Timer verringern">-</button>
                            <input type="number" id="practiceTimerMinutes" min="0" max="240" step="1" value="0" />
                            <button type="button" class="practice-stepper-button" data-practice-step-target="practiceTimerMinutes" data-practice-step-delta="1" aria-label="Timer erhöhen">+</button>
                        </span>
                    </label>
                    <label class="timeline-tempo-control" for="practiceAudioLatency">
                        Latenz für Bluetooth ms
                        <input type="range" id="practiceAudioLatencyRange" min="0" max="1000" step="10" value="30" />
                        <input type="number" id="practiceAudioLatency" min="0" max="1000" step="10" value="30" />
                    </label>
                    <label class="timeline-tempo-control" for="practiceH2HRestMute">
                        H2H Leer = Mute
                        <input type="checkbox" id="practiceH2HRestMute" />
                    </label>
                    <button type="button" id="practiceRefreshButton">Aus Blatt aktualisieren</button>
                </div>
            </section>
            <section class="timeline-column practice-column practice-accompaniment-column">
                <h3>Begleitung auswählen</h3>
                <p class="timeline-column-note">Diese Pattern laufen parallel als Loop.</p>
                <div id="practiceAccompanimentList" class="timeline-pattern-list"></div>
            </section>
            <section class="timeline-column practice-column practice-solo-column">
                <h3>Übungsteile auswählen</h3>
                <p class="timeline-column-note">Diese Pattern werden in fester Reihenfolge zugeschaltet.</p>
                <div id="practiceSoloList" class="timeline-pattern-list"></div>
            </section>
        </div>
        <section class="practice-player-panel">
            <iframe id="practiceAudioFrame" name="practiceAudioFrame" title="Audioplayer Übungsmodus"></iframe>
            <div id="practiceScroller" class="practice-scroller" hidden>
                <div class="practice-scroller-head">
                    <strong>Laufende Noten</strong>
                    <span id="practiceScrollerStatus">Bereit</span>
                </div>
                <div class="practice-scroller-stage">
                    <div class="practice-scroller-playhead" aria-hidden="true"></div>
                    <div id="practiceScrollerRows" class="practice-scroller-rows"></div>
                </div>
            </div>
        </section>
    </div>

    <div id="practiceSwingProfileDialog" class="swing-profile-dialog-backdrop" hidden>
        <section class="swing-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="practiceSwingProfileTitle">
            <header class="swing-profile-dialog-header">
                <h2 id="practiceSwingProfileTitle">Swing-Profil</h2>
                <button type="button" id="practiceSwingProfileCloseButton" aria-label="Swing-Profil schließen">Schließen</button>
            </header>
            <div class="swing-profile-preview" id="practiceSwingProfilePreview" aria-hidden="true"></div>
            <div class="swing-profile-controls" id="practiceSwingProfileControls">
                <label>S1 <input type="number" id="practiceSwingAnchor1" step="1" value="0" /></label>
                <label>S2 <input type="number" id="practiceSwingAnchor2" step="1" value="0" /></label>
                <label>S3 <input type="number" id="practiceSwingAnchor3" step="1" value="0" /></label>
                <label>S4 <input type="number" id="practiceSwingAnchor4" step="1" value="0" /></label>
            </div>
            <footer class="swing-profile-dialog-footer">
                <button type="button" id="practiceSwingProfileResetButton">Zurücksetzen</button>
                <button type="button" id="practiceSwingProfileDoneButton" class="primary">Fertig</button>
            </footer>
        </section>
    </div>

    <div id="practiceFeelProfileDialog" class="swing-profile-dialog-backdrop" hidden>
        <section class="swing-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="practiceFeelProfileTitle">
            <header class="swing-profile-dialog-header">
                <h2 id="practiceFeelProfileTitle">Feel ms</h2>
                <button type="button" id="practiceFeelProfileCloseButton" aria-label="Feel schließen">Schließen</button>
            </header>
            <div class="swing-profile-controls feel-profile-controls" id="practiceFeelProfileControls">
                <label>Kenkeni <input type="number" id="practiceFeelKenkeni" step="1" value="0" /></label>
                <label>Sangban <input type="number" id="practiceFeelSangban" step="1" value="0" /></label>
                <label>Doundoun <input type="number" id="practiceFeelDoundoun" step="1" value="0" /></label>
                <label>Dreierbass <input type="number" id="practiceFeelDreierbass" step="1" value="0" /></label>
                <label>Djembe 1 <input type="number" id="practiceFeelDjembe1" step="1" value="0" /></label>
                <label>Djembe 2 <input type="number" id="practiceFeelDjembe2" step="1" value="0" /></label>
                <label>Djembe 3 <input type="number" id="practiceFeelDjembe3" step="1" value="0" /></label>
            </div>
            <footer class="swing-profile-dialog-footer">
                <button type="button" id="practiceFeelProfileResetButton">Zurücksetzen</button>
                <button type="button" id="practiceFeelProfileDoneButton" class="primary">Fertig</button>
            </footer>
        </section>
    </div>

    <script>
// Bearbeitungsfunktionen
var edit_title, edit_text;

// Layout- und Rasterzustand
var y = 172,
    paletteBaseY = 202,
    syllableIndex = 0,
    staffStartY = 172,
    sheetWidth = 1050,
    sheetPageHeight = 1480,
    sheetPageGapY = 70,
    sheetLineStepY = 120,
    gridSize = (850 / 34) / 2,
    gridSizeY = 2.5,
    gridSizeX = 29,
    repeatMarkerGridOffsetX = 24;

// Palette und Einfüge-Offsets
var paletteOriginX,
    paletteOriginY,
    paletteFrame,
    paletteGroup,
    paletteBaseBounds,
    paletteInsertTargetX = 125,
    paletteDragDeltaX = 0,
    paletteDragDeltaY = 0,
    paletteOffsetX = 0,
    paletteOffsetY = 0;

// Paletten-Elemente
var ton, bass, slap, flam_ton, flam_slap, flam_bass_slap, ton_g, slap_g, In, Out, ShortBar, text_z_g, repeatMarkerGroup;

// Geklonte Paletten-Elemente
var ton_c, bass_c, slap_c, flam_ton_c, flam_slap_c, flam_bass_slap_c, ton_g_c, slap_g_c, In_c, Out_c, ShortBar_c, repeatMarkerLegendClone;

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
    shortbar_c, shortbar_a, shortbar_b, shortbar_v1, shortbar_v2,
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
    insertShortBarMarker,
    captureTextTouchStart,
    handleTextTouchEnd,
    insertTextField,
    cycleRepeatCount,
    insertRepeatMarker;

const canvasElementSelector = "#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #shortbar, #edit_text, #wiederholung";
const instrumentChooserSelector = ".instrument-chooser, #instrumentChooser";
const functionChooserSelector = ".function-chooser, #functionChooser";
const chooserSelector = instrumentChooserSelector + ", " + functionChooserSelector;
const timelineMetadataSelector = "#timeline_metadata";
const scoreMetadataSelector = "#score_metadata";
const removableCanvasElementSelector = canvasElementSelector + ", " + chooserSelector + ", " + timelineMetadataSelector;
const exportableElementSelector = "#notenlinien, #basis, " + removableCanvasElementSelector;
const readableElementSelector = "#wiederholung, " + chooserSelector;
const phpEndpointBase = "PHP/";
const fileListEndpoint = "auswahlliste.php";
const loadFileEndpoint = "dateiladen.php";
const saveTextEndpoint = "dateispeichern.php";
const checkTextFileEndpoint = "dateivorhanden.php";
const historyLimit = 80;
let currentScoreId = null;
let currentFileSource = "local";
let undoHistory = [];
let redoHistory = [];
const fileDialogState = {
    mode: 'open',
    source: 'local',
    filter: 'all',
    format: 'svg',
    folderId: localLibrary.rootFolderId,
    folderName: 'Lokal',
    entries: [],
    selectedId: null
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
const defaultRhythmTitle = 'Rhythmusname';
const legacyDefaultRhythmTitle = 'Enter the name of the Rhythm';

function isDefaultTitleText(titleValue) {
    const normalizedTitle = String(titleValue || '').trim();
    return !normalizedTitle ||
        normalizedTitle === defaultRhythmTitle ||
        normalizedTitle === legacyDefaultRhythmTitle;
}

function setRhythmTitle(titleValue) {
    if (!titel) {
        return;
    }
    const isPlaceholder = isDefaultTitleText(titleValue);
    titel.attr({
        text: isPlaceholder ? defaultRhythmTitle : String(titleValue).trim(),
        fill: isPlaceholder ? '#8a8a8a' : '#111'
    });
}

function startInlineRhythmTitleEdit() {
    if (!titel || !titel.node || document.getElementById('rhythmTitleEditor')) {
        return;
    }

    const currentTitle = titel.attr('text') || '';
    const editorEl = document.createElement('input');
    const titleBounds = titel.node.getBoundingClientRect();
    const scrollX = window.pageXOffset || document.documentElement.scrollLeft || 0;
    const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

    editorEl.id = 'rhythmTitleEditor';
    editorEl.type = 'text';
    editorEl.value = isDefaultTitleText(currentTitle) ? '' : currentTitle;
    editorEl.placeholder = defaultRhythmTitle;
    editorEl.setAttribute('aria-label', 'Rhythmusname');
    editorEl.style.position = 'absolute';
    editorEl.style.left = (titleBounds.left + scrollX - 4) + 'px';
    editorEl.style.top = (titleBounds.top + scrollY - 4) + 'px';
    editorEl.style.width = Math.max(280, titleBounds.width + 80) + 'px';
    editorEl.style.height = Math.max(32, titleBounds.height + 8) + 'px';
    editorEl.style.zIndex = '10001';
    editorEl.style.boxSizing = 'border-box';
    editorEl.style.padding = '2px 4px';
    editorEl.style.border = '1px solid #8c8c8c';
    editorEl.style.borderRadius = '4px';
    editorEl.style.background = 'rgba(255, 255, 255, 0.96)';
    editorEl.style.color = '#111';
    editorEl.style.font = 'bold 24px sans-serif';
    let isFinishingTitleEdit = false;

    function finishEditing(shouldCommit) {
        if (isFinishingTitleEdit) {
            return;
        }
        isFinishingTitleEdit = true;
        const nextTitle = shouldCommit ? editorEl.value.trim() : currentTitle;
        if (shouldCommit && nextTitle !== currentTitle && !(isDefaultTitleText(nextTitle) && isDefaultTitleText(currentTitle))) {
            recordHistorySnapshot();
        }
        setRhythmTitle(shouldCommit ? nextTitle : currentTitle);
        editorEl.remove();
    }

    editorEl.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            finishEditing(true);
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            finishEditing(false);
        }
    });
    editorEl.addEventListener('blur', function () {
        finishEditing(true);
    });

    document.body.appendChild(editorEl);
    editorEl.focus();
    editorEl.select();
}

edit_title = function () {
    startInlineRhythmTitleEdit();
};

edit_text = function () {
    const text_a = this.attr('text');
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', text_a);
    if (text_i == null) {
        return;
    }
    if (text_i !== text_a) {
        recordHistorySnapshot();
    }
    this.attr({ text: text_i });
};

// Zeichenfläche und Titel festlegen
var s = Snap(sheetWidth, sheetPageHeight).attr({ id: "myRect1" });
if (s.node) {
    s.node.setAttribute('viewBox', '0 0 ' + sheetWidth + ' ' + sheetPageHeight);
    s.node.setAttribute('preserveAspectRatio', 'xMinYMin meet');
}
var canv = s.rect(0, 0, sheetWidth, sheetPageHeight).attr({ fill: "white", stroke: "none", opacity: 0.001, id: "myRect2" });
canv.drag(shadow_move, shadow_start, shadow_end);

if (s.node) {
    s.node.addEventListener('selectstart', function (event) {
        event.preventDefault();
    });
    s.node.addEventListener('dragstart', function (event) {
        event.preventDefault();
    });
}

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
    const selectionEl = document.getElementById('auswahl');
    if (selectionEl) {
        selectionEl.innerHTML = markup;
    }
}

function getSelectedFileSource() {
    const sourceEl = document.querySelector('#fileSource');
    return sourceEl ? sourceEl.value : fileDialogState.source;
}

function setSelectedFileSource(source) {
    const sourceEl = document.querySelector('#fileSource');
    if (sourceEl) {
        sourceEl.value = source;
    }
    currentFileSource = source;
    fileDialogState.source = source;
    document.querySelectorAll('.file-dialog-source').forEach(function (buttonEl) {
        buttonEl.classList.toggle('is-active', buttonEl.dataset.source === source);
    });
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
}

function buildLocalFileListMarkup(scores) {
    let markup = '<select id="dateiname" onchange="get_value(this)">';
    markup += '<option value="">Lokale Datei laden:</option>';
    scores.forEach(function (score) {
        const statusLabel = score.syncState === 'modified-local'
            ? ' *'
            : score.isPublished
                ? ' veröffentlicht'
                : '';
        markup += '<option value="' + escapeHtml(score.id) + '">' +
            escapeHtml(score.title + statusLabel) +
            '</option>';
    });
    markup += '</select>';
    return markup;
}

function buildServerFileListMarkup(scores) {
    let markup = '<select id="dateiname" onchange="get_value(this)">';
    markup += '<option value="">Server-Datei laden:</option>';
    scores.forEach(function (score) {
        markup += '<option value="' + escapeHtml(score.serverPath || score.fileName) + '">' +
            escapeHtml(score.fileName || score.serverPath || score.title) +
            '</option>';
    });
    markup += '</select>';
    return markup;
}

function getSelectedLocalScoreId() {
    if (getSelectedFileSource() !== 'local') {
        return currentScoreId;
    }

    const fileSelect = document.querySelector('#dateiname');
    return fileSelect && fileSelect.value ? fileSelect.value : currentScoreId;
}

async function refreshFileList() {
    const source = getSelectedFileSource();
    currentFileSource = source;

    try {
        if (source === 'server') {
            const serverScores = await serverLibrary.listScores();
            updateSelectionMarkup(buildServerFileListMarkup(serverScores));
            return;
        }

        const localScores = await localLibrary.listScores();
        updateSelectionMarkup(buildLocalFileListMarkup(localScores));
    } catch (error) {
        console.error('Dateiliste konnte nicht geladen werden', error);
        updateSelectionMarkup('<select id="dateiname"><option>Fehler beim Laden</option></select>');
    }
}

function getScoreStatusLabel(score) {
    if (!score) {
        return '';
    }
    if (score.syncState === 'modified-local') {
        return 'Geändert';
    }
    if (score.isPublished) {
        return 'Veröffentlicht';
    }
    return 'Lokal';
}

function formatFileDialogDate(value) {
    if (!value) {
        return '';
    }
    const dateValue = new Date(value);
    if (Number.isNaN(dateValue.getTime())) {
        return '';
    }
    return dateValue.toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getFileDialogTitle(mode) {
    if (mode === 'saveAs') {
        return 'Speichern als';
    }
    if (mode === 'save') {
        return 'Speichern unter';
    }
    if (mode === 'export') {
        return 'Exportieren';
    }
    return 'Öffnen';
}

function getFileDialogConfirmLabel(mode, format) {
    if (mode === 'save' || mode === 'saveAs') {
        return 'Speichern';
    }
    if (mode === 'export') {
        return 'Exportieren';
    }
    return 'Öffnen';
}

function getFileDialogEntryId(entry) {
    if (!entry) {
        return '';
    }
    return entry.dialogId || entry.id || entry.serverPath || entry.fileName || '';
}

function isFileDialogFolderEntry(entry) {
    return entry && (entry.entryType === 'folder' || entry.entryType === 'parent-folder');
}

function isFileDialogManagementAvailable() {
    const entry = getSelectedFileDialogEntry();
    if (!entry || fileDialogState.source !== 'local') {
        return false;
    }
    if (entry.entryType === 'score') {
        return true;
    }
    if (entry.entryType === 'folder') {
        return Boolean(entry.isEmpty);
    }
    return false;
}

function isFileDialogRenameAvailable() {
    const entry = getSelectedFileDialogEntry();
    return fileDialogState.source === 'local' &&
        entry &&
        (entry.entryType === 'score' || entry.entryType === 'folder');
}

function canDeletePublishedFileDialogScore() {
    const entry = getSelectedFileDialogEntry();
    return fileDialogState.source === 'local' &&
        entry &&
        entry.entryType === 'score' &&
        entry.serverPath &&
        entry.publishToken;
}

function updateFileDialogControls() {
    const dialogTitle = document.querySelector('#fileDialogTitle');
    const confirmButton = document.querySelector('#fileDialogConfirmButton');
    const newFolderButton = document.querySelector('#fileDialogNewFolderButton');
    const renameButton = document.querySelector('#fileDialogRenameButton');
    const deleteButton = document.querySelector('#fileDialogDeleteButton');
    const unpublishButton = document.querySelector('#fileDialogUnpublishButton');
    const formatEl = document.querySelector('#fileDialogFormat');
    const formatWrapEl = document.querySelector('.file-dialog-format');
    const nameEl = document.querySelector('#fileDialogName');
    const fieldsEl = document.querySelector('.file-dialog-fields');
    const folderNameEl = document.querySelector('#fileDialogFolderName');
    const sourceButtons = document.querySelectorAll('.file-dialog-source');
    const isExportMode = fileDialogState.mode === 'export';

    if (dialogTitle) {
        dialogTitle.textContent = getFileDialogTitle(fileDialogState.mode);
    }
    if (formatWrapEl) {
        formatWrapEl.hidden = !isExportMode;
    }
    if (formatEl) {
        formatEl.value = fileDialogState.format;
        formatEl.disabled = !isExportMode;
    }
    if (confirmButton) {
        confirmButton.textContent = getFileDialogConfirmLabel(fileDialogState.mode, fileDialogState.format);
        confirmButton.disabled = fileDialogState.mode === 'open' && !fileDialogState.selectedId;
    }
    if (newFolderButton) {
        newFolderButton.disabled = fileDialogState.source !== 'local' || isExportMode;
    }
    if (renameButton) {
        renameButton.disabled = !isFileDialogRenameAvailable();
    }
    if (deleteButton) {
        deleteButton.disabled = !isFileDialogManagementAvailable();
    }
    if (unpublishButton) {
        unpublishButton.disabled = !canDeletePublishedFileDialogScore();
    }
    if (nameEl) {
        nameEl.disabled = fileDialogState.mode === 'open';
    }
    if (fieldsEl) {
        fieldsEl.hidden = fileDialogState.mode === 'open';
    }
    if (folderNameEl) {
        folderNameEl.textContent = fileDialogState.source === 'server' ? 'Server' : fileDialogState.folderName;
    }
    sourceButtons.forEach(function (buttonEl) {
        buttonEl.disabled = isExportMode;
        buttonEl.classList.toggle('is-active', buttonEl.dataset.source === fileDialogState.source);
    });

    document.querySelectorAll('.file-dialog-filter').forEach(function (buttonEl) {
        const shouldDisable = fileDialogState.source !== 'local' || isExportMode;
        buttonEl.disabled = shouldDisable;
        buttonEl.classList.toggle('is-active', buttonEl.dataset.filter === fileDialogState.filter && !shouldDisable);
    });
}

function getFilteredFileDialogEntries() {
    const searchText = String(document.querySelector('#fileDialogSearch')?.value || '').trim().toLocaleLowerCase('de-DE');
    return fileDialogState.entries.filter(function (entry) {
        if (fileDialogState.source === 'local' && entry.entryType === 'score') {
            if (fileDialogState.filter === 'published' && !entry.isPublished) {
                return false;
            }
            if (fileDialogState.filter === 'local-only' && entry.isPublished) {
                return false;
            }
            if (fileDialogState.filter === 'modified' && entry.syncState !== 'modified-local') {
                return false;
            }
        }
        if (!searchText) {
            return true;
        }
        return String(entry.title || entry.name || entry.fileName || '').toLocaleLowerCase('de-DE').indexOf(searchText) !== -1;
    });
}

async function navigateFileDialogFolder(folderId) {
    fileDialogState.folderId = folderId || localLibrary.rootFolderId;
    fileDialogState.selectedId = null;
    await refreshFileDialogEntries();
}

function updateFileDialogRowSelection() {
    document.querySelectorAll('#fileDialogList tr').forEach(function (rowEl) {
        rowEl.classList.toggle('is-selected', rowEl.dataset.id === fileDialogState.selectedId);
    });
}

function renderFileDialogList() {
    const listEl = document.querySelector('#fileDialogList');
    const emptyEl = document.querySelector('#fileDialogEmpty');
    if (!listEl) {
        return;
    }

    const entries = getFilteredFileDialogEntries();
    listEl.innerHTML = '';

    entries.forEach(function (entry) {
        const rowEl = document.createElement('tr');
        rowEl.dataset.id = getFileDialogEntryId(entry);
        rowEl.dataset.entryType = entry.entryType || 'score';
        rowEl.className = rowEl.dataset.id === fileDialogState.selectedId ? 'is-selected' : '';
        if (isFileDialogFolderEntry(entry)) {
            rowEl.classList.add('is-folder');
        }

        const nameCell = document.createElement('td');
        nameCell.textContent = entry.title || entry.name || entry.fileName || entry.serverPath || '';
        const statusCell = document.createElement('td');
        if (entry.entryType === 'parent-folder') {
            statusCell.textContent = 'Zurück';
        } else if (entry.entryType === 'folder') {
            statusCell.textContent = 'Ordner';
        } else {
            statusCell.textContent = fileDialogState.source === 'server' ? 'Server' : getScoreStatusLabel(entry);
        }
        const dateCell = document.createElement('td');
        dateCell.textContent = formatFileDialogDate(entry.updatedAt || entry.localUpdatedAt || entry.publishedAt);

        rowEl.append(nameCell, statusCell, dateCell);
        rowEl.addEventListener('click', function () {
            fileDialogState.selectedId = rowEl.dataset.id;
            if (fileDialogState.mode === 'open' && fileDialogState.source === 'local' && entry.entryType === 'score') {
                document.querySelector('#fileDialogName').value = entry.title || '';
            }
            updateFileDialogRowSelection();
            updateFileDialogControls();
        });
        rowEl.addEventListener('dblclick', function () {
            if (fileDialogState.source === 'local' && isFileDialogFolderEntry(entry)) {
                navigateFileDialogFolder(entry.targetFolderId || entry.id);
                return;
            }
            confirmFileDialog();
        });
        listEl.appendChild(rowEl);
    });

    if (emptyEl) {
        emptyEl.hidden = entries.length > 0;
    }
    updateFileDialogControls();
}

async function refreshFileDialogEntries() {
    try {
        if (fileDialogState.source === 'server') {
            fileDialogState.entries = (await serverLibrary.listScores()).map(function (entry) {
                return Object.assign({ entryType: 'score' }, entry);
            });
            fileDialogState.filter = 'all';
            fileDialogState.folderName = 'Server';
        } else {
            const currentFolder = await localLibrary.getFolder(fileDialogState.folderId);
            if (!currentFolder) {
                fileDialogState.folderId = localLibrary.rootFolderId;
            }
            fileDialogState.folderName = currentFolder && currentFolder.name ? currentFolder.name : 'Lokal';
            const foldersInCurrentFolder = await localLibrary.listFolders(fileDialogState.folderId);
            const folders = await Promise.all(foldersInCurrentFolder.map(async function (folder) {
                const childFolders = await localLibrary.listFolders(folder.id);
                const childScores = await localLibrary.listScores(folder.id);
                return Object.assign({
                    entryType: 'folder',
                    title: folder.name,
                    isEmpty: childFolders.length === 0 && childScores.length === 0
                }, folder);
            }));
            const scores = (await localLibrary.listScores(fileDialogState.folderId)).map(function (score) {
                return Object.assign({ entryType: 'score' }, score);
            });
            fileDialogState.entries = [];
            if (fileDialogState.folderId !== localLibrary.rootFolderId && currentFolder) {
                fileDialogState.entries.push({
                    entryType: 'parent-folder',
                    dialogId: '__parent__',
                    targetFolderId: currentFolder.parentId || localLibrary.rootFolderId,
                    title: '..',
                    name: '..'
                });
            }
            fileDialogState.entries = fileDialogState.entries.concat(folders, scores);
        }

        const selectedStillExists = fileDialogState.entries.some(function (entry) {
            return getFileDialogEntryId(entry) === fileDialogState.selectedId;
        });
        if (!selectedStillExists) {
            fileDialogState.selectedId = null;
        }
        renderFileDialogList();
    } catch (error) {
        console.error('Dateidialog konnte nicht aktualisiert werden', error);
        fileDialogState.entries = [];
        fileDialogState.selectedId = null;
        renderFileDialogList();
        alert('Fehler beim Laden der Dateiliste: ' + error.message);
    }
}

function openFileDialog(mode) {
    const dialogEl = document.querySelector('#fileDialog');
    if (!dialogEl) {
        return;
    }

    fileDialogState.mode = mode;
    fileDialogState.source = 'local';
    fileDialogState.filter = 'all';
    fileDialogState.format = mode === 'export' ? 'svg' : 'score';
    fileDialogState.folderId = localLibrary.rootFolderId;
    fileDialogState.folderName = 'Lokal';
    fileDialogState.selectedId = null;

    document.querySelector('#fileDialogName').value = titel ? (titel.attr('text') || '') : '';
    document.querySelector('#fileDialogTags').value = '';
    document.querySelector('#fileDialogSearch').value = '';
    setSelectedFileSource('local');
    dialogEl.hidden = false;
    updateFileDialogControls();
    refreshFileDialogEntries();
    closeAppMenus();
}

function closeFileDialog() {
    const dialogEl = document.querySelector('#fileDialog');
    if (dialogEl) {
        dialogEl.hidden = true;
    }
}

function getSelectedFileDialogEntry() {
    return fileDialogState.entries.find(function (entry) {
        return getFileDialogEntryId(entry) === fileDialogState.selectedId;
    }) || null;
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

function loadRhythmContent(title, content, scoreId, options) {
    if (!content) {
        return;
    }
    const loadOptions = options || {};
    loadedTitle = title || 'Unbenannt';
    currentScoreId = scoreId || null;
    if (loadOptions.remember !== false) {
        rememberLastLoadedScore(currentScoreId);
    }
    setIoFieldValue(content);
    Snap.loadStr(content, onSVGLoaded);
}

function loadRhythmFile(fileName) {
    loadedTitle = String(fileName || '').replace(/\.(bbs|txt)$/i, '');
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

function getCurrentHistorySnapshot() {
    const elementMarkup = [];
    s.selectAll(removableCanvasElementSelector).forEach(function (el) {
        elementMarkup.push(el.toString());
    });
    return {
        rhythm: rhythm || 'tenaer',
        lineCount: normalizeSheetLineCount(zeilenAnzahl),
        title: titel ? (titel.attr('text') || '') : '',
        elementsMarkup: elementMarkup.join(''),
        timelineSyncOptions: typeof buildCurrentTimelineSyncOptions === 'function'
            ? buildCurrentTimelineSyncOptions()
            : null
    };
}

function stringifyHistoryState(value) {
    try {
        return JSON.stringify(value || null);
    } catch (error) {
        return '';
    }
}

function areHistorySnapshotsEqual(leftSnapshot, rightSnapshot) {
    return Boolean(leftSnapshot && rightSnapshot) &&
        leftSnapshot.rhythm === rightSnapshot.rhythm &&
        normalizeSheetLineCount(leftSnapshot.lineCount) === normalizeSheetLineCount(rightSnapshot.lineCount) &&
        leftSnapshot.title === rightSnapshot.title &&
        leftSnapshot.elementsMarkup === rightSnapshot.elementsMarkup &&
        stringifyHistoryState(leftSnapshot.timelineSyncOptions) === stringifyHistoryState(rightSnapshot.timelineSyncOptions);
}

function recordHistorySnapshot() {
    if (!s || !titel) {
        return;
    }
    pushHistorySnapshot(getCurrentHistorySnapshot());
}

function recordArrangementHistorySnapshot() {
    recordHistorySnapshot();
}

function pushHistorySnapshot(snapshot) {
    if (!snapshot) {
        return;
    }
    const previousSnapshot = undoHistory.length > 0 ? undoHistory[undoHistory.length - 1] : null;
    if (areHistorySnapshotsEqual(snapshot, previousSnapshot)) {
        return;
    }
    undoHistory.push(snapshot);
    if (undoHistory.length > historyLimit) {
        undoHistory.shift();
    }
    redoHistory = [];
}

function clearHistorySnapshots() {
    undoHistory = [];
    redoHistory = [];
}

function drawHistoryBaseSheet(rhythmName) {
    if (rhythmName === 'binaer') {
        viererNotenOhneStartChooser();
    } else if (rhythmName === 'neunaer') {
        neunerNotenOhneStartChooser();
    } else {
        dreierNotenOhneStartChooser();
    }
}

function bindLoadedScoreElements() {
    const loadedElements = s.selectAll(removableCanvasElementSelector);
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
        if (el.attr("id") == "shortbar") {
            updateShortBarMarkerVisual(el);
        }
        el.attr({ class: "shp" });
        el.drag(move, sel_start, stop_m);
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
}

function syncStateAfterHistoryRestore(syncOptions) {
    try {
        const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
        syncTimelineStateFromReadResult(readResult, syncOptions || buildCurrentTimelineSyncOptions());
        renderPracticePanel();
        if (practiceState.visible) {
            schedulePracticeAudioRefresh(0);
        }
    } catch (error) {
        console.warn('Timeline-Zustand konnte nach Undo/Redo nicht rekonstruiert werden', error);
    }
}

function restoreHistorySnapshot(snapshot) {
    if (!snapshot) {
        return;
    }
    resetSelectionArtifacts();
    const syncOptions = snapshot.timelineSyncOptions || buildCurrentTimelineSyncOptions();
    zeilenAnzahl = normalizeSheetLineCount(snapshot.lineCount || zeilenProBlatt);
    drawHistoryBaseSheet(snapshot.rhythm);
    removeCanvasElements(removableCanvasElementSelector);
    if (snapshot.elementsMarkup) {
        s.append(Snap.parseStr(snapshot.elementsMarkup));
    }
    bindLoadedScoreElements();
    setRhythmTitle(snapshot.title || 'Unbenannt');
    syncStateAfterHistoryRestore(syncOptions);
}

function undoLastEditorAction() {
    if (undoHistory.length === 0) {
        return;
    }
    const currentSnapshot = getCurrentHistorySnapshot();
    const previousSnapshot = undoHistory.pop();
    if (!areHistorySnapshotsEqual(currentSnapshot, previousSnapshot)) {
        redoHistory.push(currentSnapshot);
    }
    restoreHistorySnapshot(previousSnapshot);
}

function redoLastEditorAction() {
    if (redoHistory.length === 0) {
        return;
    }
    const currentSnapshot = getCurrentHistorySnapshot();
    const nextSnapshot = redoHistory.pop();
    if (!areHistorySnapshotsEqual(currentSnapshot, nextSnapshot)) {
        undoHistory.push(currentSnapshot);
    }
    restoreHistorySnapshot(nextSnapshot);
}

function isUndoRedoKeyEvent(event) {
    const isZKey = event &&
        (String(event.key || '').toLowerCase() === 'z' || event.code === 'KeyZ');
    if (!event || !event.metaKey || !isZKey) {
        return false;
    }
    const targetName = event.target && event.target.tagName ? event.target.tagName.toLowerCase() : '';
    return targetName !== 'input' && targetName !== 'textarea' && targetName !== 'select';
}

document.addEventListener('keydown', function (event) {
    if (!isUndoRedoKeyEvent(event)) {
        return;
    }
    event.preventDefault();
    event.stopPropagation();
    if (event.shiftKey) {
        redoLastEditorAction();
    } else {
        undoLastEditorAction();
    }
}, true);

function resetSelectionArtifacts() {
    if (typeof box !== 'undefined' && box) {
        box.remove();
        box = null;
    }

    if (typeof selections !== 'undefined' && selections) {
        UnGroup();
        if (selections) {
            selections.remove();
        }
        selections = null;
    }
}

function resolveInsertOffset(offsetValue) {
    return typeof offsetValue === 'function' ? offsetValue() : offsetValue;
}

function resolveInsertTemplate(templateValue) {
    return typeof templateValue === 'function' ? templateValue() : templateValue;
}

function getPaletteCloneLocalReferenceX(templateElement, elementId) {
    if (!templateElement || typeof templateElement.getBBox !== 'function') {
        return NaN;
    }

    if (templateElement.attr && templateElement.attr('id') === 'shortbar') {
        const markerLine = typeof templateElement.select === 'function'
            ? templateElement.select('.shortbar-marker-line')
            : null;
        const markerX = markerLine ? Number(markerLine.attr('x1')) : NaN;
        if (Number.isFinite(markerX)) {
            return markerX;
        }
    }
    if (elementId === 'wiederholung') {
        const repeatDots = typeof templateElement.selectAll === 'function'
            ? templateElement.selectAll('circle')
            : [];
        if (repeatDots && repeatDots.length) {
            const dotX = Number(repeatDots[0].attr('cx'));
            if (Number.isFinite(dotX)) {
                return dotX;
            }
        }
    }

    const transformState = typeof templateElement.transform === 'function' ? templateElement.transform() : null;
    const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
    const bbox = templateElement.getBBox();
    return bbox.cx - (localMatrix ? localMatrix.e : 0);
}

function getPaletteInsertReferenceX() {
    const firstNoteLineX = Number(paletteInsertTargetX);
    const snapStep = Number(gridSize);
    const preferredPaletteX = Number(paletteOriginX) + Number(gridSizeX);

    if (!Number.isFinite(firstNoteLineX) || !Number.isFinite(snapStep) || snapStep <= 0 || !Number.isFinite(preferredPaletteX)) {
        return NaN;
    }

    const stepsBackFromFirstLine = Math.max(1, Math.round((firstNoteLineX - preferredPaletteX) / snapStep));
    return paletteOffsetX + firstNoteLineX - stepsBackFromFirstLine * snapStep;
}

function getPaletteInsertFineTuneX(elementId) {
    const noteSymbolIds = [
        'tone',
        'bass',
        'slap',
        'tone_muffled',
        'slap_muffled',
        'tone_flam',
        'slap_flam',
        'bass_slap_flam'
    ];
    if (noteSymbolIds.indexOf(elementId) !== -1) {
        return 1;
    }
    return 0;
}

function getPaletteInsertFineTuneY(elementId) {
    return elementId === 'wiederholung' ? -10 : 0;
}

function createPaletteClone(templateElement, elementId, offsetX, offsetY) {
    const resolvedOffsetX = resolveInsertOffset(offsetX);
    const resolvedOffsetY = resolveInsertOffset(offsetY);
    const localReferenceX = getPaletteCloneLocalReferenceX(templateElement, elementId);
    const insertReferenceX = getPaletteInsertReferenceX();
    const transformX = Number.isFinite(localReferenceX) && Number.isFinite(insertReferenceX)
        ? insertReferenceX - localReferenceX + getPaletteInsertFineTuneX(elementId)
        : paletteOffsetX + resolvedOffsetX;
    const clone = templateElement.clone().attr({
        class: 'shp',
        id: elementId,
        transform: "t" + transformX + "," + (paletteOffsetY + resolvedOffsetY + getPaletteInsertFineTuneY(elementId))
    });
    clone.drag(move, sel_start, stop_m);
    return clone;
}

function bindPaletteInsert(sourceElement, templateElement, elementId, offsetX, offsetY, afterCreate) {
    const insertElement = function () {
        const resolvedTemplateElement = resolveInsertTemplate(templateElement);
        if (!resolvedTemplateElement) {
            return;
        }
        recordHistorySnapshot();
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
    textElement.drag(move, sel_start, stop_m);
    textElement.dblclick(edit_text);
    textElement.touchstart(captureTextTouchStart);
    textElement.touchend(handleTextTouchEnd);
    return textElement;
}

function updateShortBarMarkerVisual(shortBarElement) {
    if (!shortBarElement || typeof shortBarElement.select !== 'function') {
        return shortBarElement;
    }
    const tailWidth = Math.max(34, Math.round((Number(gridSizeX) || 34) * 3) - 12);
    const tail = shortBarElement.select('.shortbar-tail');
    const markerLine = shortBarElement.select('.shortbar-marker-line');
    const firstTailLine = shortBarElement.select('.shortbar-tail-line-1');
    const secondTailLine = shortBarElement.select('.shortbar-tail-line-2');
    const hitbox = shortBarElement.select('.shortbar-hitbox');
    const baseX = markerLine ? Number(markerLine.attr('x1')) || 0 : 0;
    const baseY1 = markerLine ? Number(markerLine.attr('y1')) || 0 : 0;
    const baseY2 = markerLine ? Number(markerLine.attr('y2')) || 0 : 0;
    const explicitAnchorY = Number(shortBarElement.attr('data-shortbar-anchor-y'));
    const centerY = Number.isFinite(explicitAnchorY)
        ? explicitAnchorY
        : (baseY1 + baseY2) / 2;
    const tailTop = centerY - 19;
    const tailBottom = centerY + 21;
    const tailX = baseX + 4;

    if (tail) {
        tail.attr({ display: null, x: tailX, y: tailTop, width: tailWidth, height: tailBottom - tailTop });
    }
    if (firstTailLine) {
        firstTailLine.attr({ display: null, x1: tailX + tailWidth / 3, x2: tailX + tailWidth / 3, y1: tailTop, y2: tailBottom });
    }
    if (secondTailLine) {
        secondTailLine.attr({ display: null, x1: tailX + tailWidth * 2 / 3, x2: tailX + tailWidth * 2 / 3, y1: tailTop, y2: tailBottom });
    }
    if (hitbox) {
        hitbox.attr({ x: baseX - 7, y: tailTop - 4, width: tailWidth + 18, height: tailBottom - tailTop + 8 });
    }
    return shortBarElement;
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
    if (typeof resetPracticeForSource === 'function') {
        resetPracticeForSource('');
    }
    if (typeof clearPracticeAudioPlayer === 'function') {
        clearPracticeAudioPlayer();
    }
    renderPracticePanel();
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
        ? snapToVerticalTargets(referenceY, element)
        : referenceY;
    const deltaY = snappedY - referenceY;

    if (Math.abs(deltaY) < 0.001) {
        return element;
    }

    const transformState = element.transform();
    const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
    const nextX = localMatrix ? localMatrix.e : 0;
    const nextY = (localMatrix ? localMatrix.f : 0) + deltaY;
    element.transform("t" + nextX + "," + nextY);
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
    paletteInsertTargetX = initialChooserX;

    if (shouldResetTitle && config.resetLineCount !== false) {
        zeilenAnzahl = zeilenProBlatt;
    }
    zeilenAnzahl = normalizeSheetLineCount(zeilenAnzahl);
    updateSheetCanvasDimensions();
    clear_all();
    drawSheetPageFrames();
    syllableIndex = 0;

    for (var j = 0; j < zeilenAnzahl; j++) {
        const lineBaseY = getSheetLineBaseY(j);
        s.rect(100, lineBaseY - 10, 3, 60).attr({ id: "notenlinien" });
        s.rect(525, lineBaseY - 10, 3, 60).attr({ id: "notenlinien" });
        s.rect(950, lineBaseY - 10, 3, 60).attr({ id: "notenlinien" });
        s.text(90, lineBaseY + 30, j + 1).attr({
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
                s.text(x - 3, lineBaseY + config.syllableYOffset, config.countSyllables[syllableIndex]).attr({
                    id: "notenlinien",
                    'font-size': 10
                });
                syllableIndex++;
                if (syllableIndex == config.countSyllables.length) {
                    syllableIndex = 0;
                }
                s.rect(x, lineBaseY, 1.5, 40).attr({ id: "notenlinien" });
            }

            if (config.beatStartIndices.indexOf(i) !== -1) {
                let beatNumber = Math.trunc((i + config.beatNumberOffset) / config.beatDivisor);
                if (beatNumber > config.beatWrapAt) {
                    beatNumber -= config.beatWrapAt;
                }
                s.text(x - 3, lineBaseY + config.beatNumberYOffset, beatNumber).attr({
                    id: "notenlinien",
                    'font-size': 10
                });
                s.rect(x, lineBaseY, beatBarWidth, 1.5).attr({ id: "notenlinien" });
                s.rect(x, lineBaseY + 5, beatBarWidth, 1.5).attr({ id: "notenlinien" });
            }
        }
    }

    if (shouldAddInitialChooser) {
        addInitialInstrumentChooser(initialChooserX, 140);
        addInitialFunctionChooser(initialChooserX + 135, 140);
    }

    renderLegend(initialChooserX);

    if (shouldResetTitle) {
        currentScoreId = null;
        setSelectedFileSource('local');
        rememberLastLoadedScore('');
        setRhythmTitle(defaultRhythmTitle);
    }
}

var titel = s.text(100, y - 100, defaultRhythmTitle).attr({ id: 'basis', 'font-size': 24, 'font-family': 'sans-serif', 'font-weight': 'bold', fill: '#8a8a8a', cursor: 'text' });
titel.click(edit_title);
titel.dblclick(edit_title);

const zeilenProBlatt = 10;
let zeilenAnzahl = 10;
let rhythm = "binaer";

function normalizeSheetLineCount(lineCount) {
    const normalizedLineCount = Math.ceil(Number(lineCount) || zeilenProBlatt);
    return Math.max(zeilenProBlatt, normalizedLineCount);
}

function getSheetPageCount(lineCount) {
    return Math.max(1, Math.ceil(normalizeSheetLineCount(lineCount || zeilenAnzahl) / zeilenProBlatt));
}

function getSheetPageOffsetY(pageIndex) {
    return pageIndex * (sheetPageHeight + sheetPageGapY);
}

function getSheetDocumentHeight(lineCount) {
    const pageCount = getSheetPageCount(lineCount);
    return pageCount * sheetPageHeight + (pageCount - 1) * sheetPageGapY;
}

function getSheetLinePageIndex(lineIndex) {
    return Math.floor(Math.max(0, Number(lineIndex) || 0) / zeilenProBlatt);
}

function getSheetLineLocalIndex(lineIndex) {
    return Math.max(0, Number(lineIndex) || 0) % zeilenProBlatt;
}

function getSheetLineBaseY(lineIndex) {
    const pageIndex = getSheetLinePageIndex(lineIndex);
    return staffStartY + getSheetLineLocalIndex(lineIndex) * sheetLineStepY + getSheetPageOffsetY(pageIndex);
}

function getSheetLineIndexFromY(centerY, lineCount, referenceOffsetY) {
    const resolvedLineCount = normalizeSheetLineCount(lineCount || zeilenAnzahl);
    const resolvedReferenceOffsetY = Number(referenceOffsetY) || 0;
    let closestLineIndex = 0;
    let closestDistance = Infinity;

    for (let lineIndex = 0; lineIndex < resolvedLineCount; lineIndex++) {
        const expectedY = getSheetLineBaseY(lineIndex) + resolvedReferenceOffsetY;
        const distance = Math.abs(centerY - expectedY);
        if (distance < closestDistance) {
            closestDistance = distance;
            closestLineIndex = lineIndex;
        }
    }

    return closestLineIndex;
}

function updateSheetCanvasDimensions() {
    const documentHeight = getSheetDocumentHeight(zeilenAnzahl);
    if (s && s.node) {
        s.attr({ width: sheetWidth, height: documentHeight });
        s.node.setAttribute('viewBox', '0 0 ' + sheetWidth + ' ' + documentHeight);
    }
    if (canv) {
        canv.attr({ width: sheetWidth, height: documentHeight });
    }
    updateSheetPageControls();
}

function updateSheetPageControls() {
    const deleteButtonEl = document.getElementById('deleteSheetPageButton');
    if (deleteButtonEl) {
        deleteButtonEl.disabled = getSheetPageCount(zeilenAnzahl) <= 1;
    }
}

function redrawCurrentSheetFromSnapshot(snapshot, syncOptions) {
    if (!snapshot) {
        return;
    }
    resetSelectionArtifacts();
    zeilenAnzahl = normalizeSheetLineCount(snapshot.lineCount || zeilenProBlatt);
    drawHistoryBaseSheet(snapshot.rhythm || rhythm);
    removeCanvasElements(removableCanvasElementSelector);
    if (snapshot.elementsMarkup) {
        s.append(Snap.parseStr(snapshot.elementsMarkup));
    }
    bindLoadedScoreElements();
    setRhythmTitle(snapshot.title || 'Unbenannt');
    syncStateAfterHistoryRestore(syncOptions || buildCurrentTimelineSyncOptions());
}

function removeElementsOutsideSheetLineCount(lineCount) {
    const nextSheetHeight = getSheetDocumentHeight(lineCount);
    s.selectAll(removableCanvasElementSelector).forEach(function (el) {
        if (el.attr('id') == 'timeline_metadata') {
            return;
        }
        const bbox = typeof el.getBBox === 'function' ? el.getBBox() : null;
        if (!bbox || !Number.isFinite(bbox.cy)) {
            return;
        }
        if (bbox.cy > nextSheetHeight) {
            el.remove();
        }
    });
}

function addSheetPage() {
    recordHistorySnapshot();
    const syncOptions = buildCurrentTimelineSyncOptions();
    const snapshot = getCurrentHistorySnapshot();
    snapshot.lineCount = normalizeSheetLineCount(zeilenAnzahl) + zeilenProBlatt;
    redrawCurrentSheetFromSnapshot(snapshot, syncOptions);
}

function deleteSheetPage() {
    if (getSheetPageCount(zeilenAnzahl) <= 1) {
        return;
    }
    const shouldDelete = confirm('Das letzte Blatt löschen? Inhalte auf diesem Blatt werden entfernt.');
    if (!shouldDelete) {
        return;
    }
    recordHistorySnapshot();
    const syncOptions = buildCurrentTimelineSyncOptions();
    const nextLineCount = normalizeSheetLineCount(zeilenAnzahl) - zeilenProBlatt;
    removeElementsOutsideSheetLineCount(nextLineCount);
    const snapshot = getCurrentHistorySnapshot();
    snapshot.lineCount = nextLineCount;
    redrawCurrentSheetFromSnapshot(snapshot, syncOptions);
}

function drawSheetPageFrames() {
    const pageCount = getSheetPageCount(zeilenAnzahl);
    removeCanvasElements(".sheet-page-background, .sheet-page-number");
    for (let pageIndex = 0; pageIndex < pageCount; pageIndex++) {
        const pageOffsetY = getSheetPageOffsetY(pageIndex);
        s.rect(0, pageOffsetY, sheetWidth, sheetPageHeight).attr({
            id: "basis",
            class: "sheet-page-background",
            fill: "white",
            stroke: "#d8d0c4",
            strokeWidth: 0.7,
            pointerEvents: "none"
        }).insertAfter(canv);
        s.text(sheetWidth - 34, pageOffsetY + sheetPageHeight - 34, (pageIndex + 1) + "/" + pageCount).attr({
            id: "basis",
            class: "sheet-page-number",
            'font-size': 11,
            'font-family': 'sans-serif',
            fill: "#666",
            'text-anchor': 'end',
            pointerEvents: "none"
        });
    }
}

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
paletteFrame = s.rect(paletteOriginX - 12, paletteOriginY - 14, 26, 300, 3, 3).attr({ fill: "lightgrey", stroke: "black", strokeWidth: 0.5 });

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
repeatMarkerHitbox = s.rect(paletteOriginX - 9, paletteOriginY + 220, 20, 44).attr({ opacity: 0.001 });
repeatMarkerDotTop = s.circle(paletteOriginX + 1, paletteOriginY + 228, 2.5);
repeatMarkerDotBottom = s.circle(paletteOriginX + 1, paletteOriginY + 236, 2.5);
repeatMarkerCountText = s.text(paletteOriginX + 1, paletteOriginY + 252, " ").attr({ 'font-size': 12, 'font-family': 'sans-serif', 'font-weight': 'bold', 'text-anchor': 'middle' });
repeatMarkerGroup = s.g(repeatMarkerHitbox, repeatMarkerDotTop, repeatMarkerDotBottom, repeatMarkerCountText);

// ShortBar
x = paletteOriginX + 1;
y = paletteOriginY + 257;
shortbar_c = s.rect(x - 7, y - 14, 14, 28).attr({ opacity: 0.001 }).addClass('shortbar-hitbox');
shortbar_a = s.rect(x, y - 14, 44, 38).attr({
    display: "none",
    fill: "#f4f4f4",
    opacity: 0.55,
    stroke: "#777",
    strokeWidth: 1,
    strokeDasharray: "4 4"
}).addClass('shortbar-tail');
shortbar_b = s.line(x, y - 4, x, y + 22).attr({
    stroke: "#222",
    strokeWidth: 4,
    strokeDasharray: "1 5",
    strokeLinecap: "round"
}).addClass('shortbar-marker-line');
shortbar_v1 = s.line(x + 15, y - 14, x + 15, y + 24).attr({
    display: "none",
    stroke: "#aaa",
    strokeWidth: 1,
    strokeDasharray: "4 4"
}).addClass('shortbar-tail-line-1');
shortbar_v2 = s.line(x + 30, y - 14, x + 30, y + 24).attr({
    display: "none",
    stroke: "#aaa",
    strokeWidth: 1,
    strokeDasharray: "4 4"
}).addClass('shortbar-tail-line-2');
ShortBar = s.g(shortbar_a, shortbar_b, shortbar_v1, shortbar_v2, shortbar_c).attr({
    'data-shortbar-anchor-y': y + 7
});

// Legende schreiben
function addLegendEntry(symbol, label, symbolX, symbolY, labelOffsetX, labelOffsetY, legendOffsetX, legendOffsetY) {
    const shiftedSymbolX = symbolX + legendOffsetX;
    const shiftedSymbolY = symbolY + (Number(legendOffsetY) || 0);
    const legendClone = symbol.clone();
    s.append(legendClone);
    legendClone.attr({ id: "basis", transform: "t" + shiftedSymbolX + "," + shiftedSymbolY });
    legendClone.addClass("legend-entry");
    s.text(shiftedSymbolX + labelOffsetX, shiftedSymbolY + labelOffsetY, label).attr({
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
    const legendOffsetY = getSheetPageOffsetY(getSheetPageCount(zeilenAnzahl) - 1);

    removeCanvasElements(".legend-entry");

    ton_c = addLegendEntry(ton, "Tone", 92, 1166, 45, 178, legendOffsetX, legendOffsetY);
    bass_c = addLegendEntry(bass, "Bass", 157, 1146, 46, 198, legendOffsetX, legendOffsetY);
    slap_c = addLegendEntry(slap, "Slap/Glocke", 222, 1126, 45, 218, legendOffsetX, legendOffsetY);
    flam_ton_c = addLegendEntry(flam_ton, "Flam mit Tones", 337, 1105, 49, 240, legendOffsetX, legendOffsetY);
    flam_slap_c = addLegendEntry(flam_slap, "Flam mit Slaps", 475, 1087, 49, 259, legendOffsetX, legendOffsetY);
    flam_bass_slap_c = addLegendEntry(flam_bass_slap, "Flam mit Bass und Slaps", 613, 1069, 49, 279, legendOffsetX, legendOffsetY);
    ton_g_c = addLegendEntry(ton_g, "gedämpfter Tone", 92, 1078, 48, 299, legendOffsetX, legendOffsetY);
    slap_g_c = addLegendEntry(slap_g, "gedämpfter Slap / Klick", 240, 1058, 48, 319, legendOffsetX, legendOffsetY);
    In_c = addLegendEntry(In, "In", 428, 1034, 44, 343, legendOffsetX, legendOffsetY);
    Out_c = addLegendEntry(Out, "Out", 470, 1011, 44, 366, legendOffsetX, legendOffsetY);
    ShortBar_c = addLegendEntry(ShortBar, "ShortBar", 521, 943, 44, 434, legendOffsetX, legendOffsetY);
    repeatMarkerLegendClone = addLegendEntry(repeatMarkerGroup, "Wiederholung", 605, 968, 44, 409, legendOffsetX, legendOffsetY);
}

renderLegend(125);


// Funktionen zum Verschieben
function getPaletteBoundsForOffset(offsetX, offsetY) {
    const fallbackBounds = {
        x: paletteOriginX - 14,
        y: paletteOriginY - 16,
        x2: paletteOriginX + 48,
        y2: paletteOriginY + 286,
        width: 62,
        height: 302
    };
    const bounds = paletteBaseBounds || fallbackBounds;
    return {
        x: bounds.x + offsetX,
        y: bounds.y + offsetY,
        x2: bounds.x2 + offsetX,
        y2: bounds.y2 + offsetY,
        width: bounds.width,
        height: bounds.height
    };
}

function clampPaletteOffset(offsetX, offsetY) {
    const sheetHeight = getSheetDocumentHeight(zeilenAnzahl);
    const margin = 10;
    const bounds = paletteBaseBounds || getPaletteBoundsForOffset(0, 0);
    const minX = margin - bounds.x;
    const maxX = sheetWidth - margin - bounds.x2;
    const minY = margin - bounds.y;
    const maxY = sheetHeight - margin - bounds.y2;
    return {
        x: Math.max(minX, Math.min(maxX, offsetX)),
        y: Math.max(minY, Math.min(maxY, offsetY))
    };
}

function applyPaletteOffset(offsetX, offsetY) {
    if (!paletteGroup) {
        return;
    }
    const clampedOffset = clampPaletteOffset(offsetX, offsetY);
    paletteOffsetX = clampedOffset.x;
    paletteOffsetY = clampedOffset.y;
    paletteDragDeltaX = 0;
    paletteDragDeltaY = 0;
    paletteGroup.transform(paletteOffsetX || paletteOffsetY
        ? "t" + paletteOffsetX + "," + paletteOffsetY
        : "");
}

function keepPaletteInsideSheet() {
    applyPaletteOffset(paletteOffsetX, paletteOffsetY);
}

function resetPalettePosition() {
    applyPaletteOffset(0, 0);
}

var move1 = function (dx, dy, x, y) {
    var snappedDx = Snap.snapTo(gridSize, dx, 50);
    var snappedDy = Snap.snapTo(gridSizeY, dy, 50);
    var nextOffset = clampPaletteOffset(paletteOffsetX + snappedDx, paletteOffsetY + snappedDy);
    var clampedDx = nextOffset.x - paletteOffsetX;
    var clampedDy = nextOffset.y - paletteOffsetY;
    this.attr({
        transform: this.data('origTransform') + (this.data('origTransform') ? "T" : "t") + [clampedDx, clampedDy]
    });
    paletteDragDeltaX = clampedDx;
    paletteDragDeltaY = clampedDy;
};

var stop1 = function() {
    paletteOffsetX += paletteDragDeltaX;
    paletteOffsetY += paletteDragDeltaY;
    applyPaletteOffset(paletteOffsetX, paletteOffsetY);
};

let paletteViewportFollowFrame = null;

function getClientYAsSvgY(clientY) {
    if (!s || !s.node || typeof s.node.createSVGPoint !== 'function' || !s.node.getScreenCTM()) {
        return null;
    }
    const point = s.node.createSVGPoint();
    point.x = 0;
    point.y = clientY;
    return point.matrixTransform(s.node.getScreenCTM().inverse()).y;
}

function getVisibleSheetViewportYBounds() {
    if (!s || !s.node) {
        return null;
    }
    const svgBounds = s.node.getBoundingClientRect();
    const menuBarEl = document.getElementById('appMenuBar');
    const menuBottom = menuBarEl ? menuBarEl.getBoundingClientRect().bottom : 0;
    const topClientY = Math.max(svgBounds.top, menuBottom, 0) + 12;
    const bottomClientY = Math.min(svgBounds.bottom, window.innerHeight) - 12;
    if (bottomClientY <= topClientY) {
        return null;
    }
    const topY = getClientYAsSvgY(topClientY);
    const bottomY = getClientYAsSvgY(bottomClientY);
    if (!Number.isFinite(topY) || !Number.isFinite(bottomY)) {
        return null;
    }
    return {
        top: Math.min(topY, bottomY),
        bottom: Math.max(topY, bottomY)
    };
}

function keepPaletteInVisibleViewport() {
    if (!paletteGroup) {
        return;
    }
    const visibleBounds = getVisibleSheetViewportYBounds();
    if (!visibleBounds) {
        return;
    }
    const paletteBounds = getPaletteBoundsForOffset(paletteOffsetX, paletteOffsetY);
    let nextOffsetY = paletteOffsetY;

    if (paletteBounds.height >= visibleBounds.bottom - visibleBounds.top) {
        nextOffsetY += visibleBounds.top - paletteBounds.y;
    } else if (paletteBounds.y < visibleBounds.top) {
        nextOffsetY += visibleBounds.top - paletteBounds.y;
    } else if (paletteBounds.y2 > visibleBounds.bottom) {
        nextOffsetY -= paletteBounds.y2 - visibleBounds.bottom;
    }

    if (nextOffsetY !== paletteOffsetY) {
        applyPaletteOffset(paletteOffsetX, nextOffsetY);
    }
}

function schedulePaletteViewportFollow() {
    if (paletteViewportFollowFrame !== null) {
        return;
    }
    paletteViewportFollowFrame = window.requestAnimationFrame(function () {
        paletteViewportFollowFrame = null;
        keepPaletteInVisibleViewport();
    });
}

// Kartusche zeichnen
paletteGroup = s.g(paletteFrame, ton, bass, slap, ton_g, slap_g, flam_ton, flam_slap, flam_bass_slap, In, Out, ShortBar, text_z_g, repeatMarkerGroup);
paletteBaseBounds = paletteGroup.getBBox();
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
insertShortBarMarker = bindPaletteInsert(ShortBar, function () { return ShortBar_c; }, "shortbar", function () { return gridSizeX; }, 0, function (shortBarElement) {
    updateShortBarMarkerVisual(shortBarElement);
    snapElementToVerticalTarget(shortBarElement);
});

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
        if (text_i !== text_a) {
            recordHistorySnapshot();
        }
        this.attr({ text: text_i });
    }
};

insertTextField = function () {
    const elx = this.getBBox().cx + paletteOffsetX + 19;
    const ely = this.getBBox().y + paletteOffsetY + 12;
    const text_i = prompt('Gib hier bitte den gewünschten Text ein!', '');
    if (text_i == null) {
        return;
    }
    recordHistorySnapshot();
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
    recordHistorySnapshot();
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
        if (ax < 0 || ax > sheetWidth || ay < 0 || ay > getSheetDocumentHeight(zeilenAnzahl)) {
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
        : sheetWidth;
    const viewBoxHeight = hasFiniteBounds
        ? Math.max(1, (contentBounds.maxY - contentBounds.minY) + exportPadding * 2)
        : getSheetDocumentHeight(zeilenAnzahl);

    return '<svg height="' + viewBoxHeight + '" version="1.1" width="' + viewBoxWidth + '" viewBox="' +
        [viewBoxX, viewBoxY, viewBoxWidth, viewBoxHeight].join(' ') +
        '" preserveAspectRatio="xMidYMin meet" xmlns="http://www.w3.org/2000/svg" id="myRect1"><desc>Created with Snap</desc><defs></defs>' +
        svgContent +
        '</svg>';
}

function sanitizeDownloadFileName(value, fallback) {
    return String(value || fallback || 'Notenblatt')
        .trim()
        .replace(/[\\/:*?"<>|]+/g, '-')
        .replace(/\s+/g, ' ')
        || fallback || 'Notenblatt';
}

function downloadTextFile(content, fileName, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(function () {
        URL.revokeObjectURL(url);
    }, 1000);
}

function callPHPScript2(nameOverride) {
    const svgContent = buildExportSvgContent();
    const baseName = sanitizeDownloadFileName(nameOverride || titel.attr('text'), 'Notenblatt');
    downloadTextFile(svgContent, baseName + '.svg', 'image/svg+xml;charset=utf-8');
}

function buildSerializedRhythm() {
    updateTimelineMetadataNode();

    let serializedRhythm;
    if (rhythm == 'binaer') {
        serializedRhythm = '<binaer id="rhythmus"/>';
    } else if (rhythm == 'neunaer') {
        serializedRhythm = '<neunaer id="rhythmus"/>';
    } else {
        serializedRhythm = '<tenaer id="rhythmus"/>';
    }

    if (normalizeSheetLineCount(zeilenAnzahl) !== zeilenProBlatt) {
        serializedRhythm += '<score_metadata id="score_metadata" data-line-count="' +
            normalizeSheetLineCount(zeilenAnzahl) +
            '" />';
    }

    let elementsToSave = s.selectAll(removableCanvasElementSelector);
    elementsToSave.forEach(function (el) {
        if (el.attr('id') == 'timeline_metadata') {
            return;
        }
        const ax = el.getBBox().cx;
        const ay = el.getBBox().cy;
        if (ax < 70 || ax > sheetWidth || ay < 0 || ay > getSheetDocumentHeight(zeilenAnzahl)) {
            el.remove();
        }
    });

    elementsToSave = s.selectAll(removableCanvasElementSelector);
    elementsToSave.forEach(function (el) {
        serializedRhythm += el.toString();
    });

    return serializedRhythm;
}

function base64ToUint8Array(base64Value) {
    const binaryString = atob(base64Value);
    const bytes = new Uint8Array(binaryString.length);
    for (let index = 0; index < binaryString.length; index += 1) {
        bytes[index] = binaryString.charCodeAt(index);
    }
    return bytes;
}

function createSingleImagePdf(jpegBytes, imageWidth, imageHeight) {
    const encoder = new TextEncoder();
    const pageWidth = 595.28;
    const pageHeight = 841.89;
    const margin = 22;
    const usableWidth = pageWidth - margin * 2;
    const usableHeight = pageHeight - margin * 2;
    const imageRatio = imageWidth / imageHeight;
    const pageRatio = usableWidth / usableHeight;
    const drawWidth = imageRatio > pageRatio ? usableWidth : usableHeight * imageRatio;
    const drawHeight = imageRatio > pageRatio ? usableWidth / imageRatio : usableHeight;
    const drawX = (pageWidth - drawWidth) / 2;
    const drawY = pageHeight - margin - drawHeight;
    const chunks = [];
    const offsets = [0];
    let byteLength = 0;

    function appendText(text) {
        const bytes = encoder.encode(text);
        chunks.push(bytes);
        byteLength += bytes.length;
    }

    function appendBytes(bytes) {
        chunks.push(bytes);
        byteLength += bytes.length;
    }

    function addObject(objectNumber, content) {
        offsets[objectNumber] = byteLength;
        appendText(objectNumber + ' 0 obj\n');
        if (content instanceof Uint8Array) {
            appendBytes(content);
        } else {
            appendText(content);
        }
        appendText('\nendobj\n');
    }

    appendText('%PDF-1.4\n');
    addObject(1, '<< /Type /Catalog /Pages 2 0 R >>');
    addObject(2, '<< /Type /Pages /Kids [3 0 R] /Count 1 >>');
    addObject(
        3,
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' + pageWidth + ' ' + pageHeight +
        '] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>'
    );
    offsets[4] = byteLength;
    appendText(
        '4 0 obj\n' +
        '<< /Type /XObject /Subtype /Image /Width ' + imageWidth +
        ' /Height ' + imageHeight +
        ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' +
        jpegBytes.length + ' >>\nstream\n'
    );
    appendBytes(jpegBytes);
    appendText('\nendstream\nendobj\n');

    const contentStream = 'q\n' +
        drawWidth.toFixed(2) + ' 0 0 ' + drawHeight.toFixed(2) + ' ' +
        drawX.toFixed(2) + ' ' + drawY.toFixed(2) + ' cm\n' +
        '/Im0 Do\nQ';
    addObject(5, '<< /Length ' + encoder.encode(contentStream).length + ' >>\nstream\n' + contentStream + '\nendstream');

    const xrefOffset = byteLength;
    appendText('xref\n0 6\n0000000000 65535 f \n');
    for (let objectNumber = 1; objectNumber <= 5; objectNumber += 1) {
        appendText(String(offsets[objectNumber]).padStart(10, '0') + ' 00000 n \n');
    }
    appendText('trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n' + xrefOffset + '\n%%EOF');
    return new Blob(chunks, { type: 'application/pdf' });
}

function loadImageFromObjectUrl(url) {
    return new Promise(function (resolve, reject) {
        const image = new Image();
        image.onload = function () {
            resolve(image);
        };
        image.onerror = function () {
            reject(new Error('Das Notenblatt konnte nicht für den PDF-Export gerendert werden.'));
        };
        image.src = url;
    });
}

async function exportCurrentSheetAsPdf() {
    const svgContent = buildExportSvgContent();
    const documentTitle = titel.attr('text') || 'Notenblatt';
    const svgBlob = new Blob([svgContent], { type: 'image/svg+xml;charset=utf-8' });
    const svgUrl = URL.createObjectURL(svgBlob);

    try {
        const image = await loadImageFromObjectUrl(svgUrl);
        const canvas = document.createElement('canvas');
        canvas.width = 2480;
        canvas.height = 3508;
        const context = canvas.getContext('2d');
        context.fillStyle = 'white';
        context.fillRect(0, 0, canvas.width, canvas.height);

        const padding = 94;
        const usableWidth = canvas.width - padding * 2;
        const usableHeight = canvas.height - padding * 2;
        const imageRatio = image.naturalWidth / image.naturalHeight;
        const canvasRatio = usableWidth / usableHeight;
        const drawWidth = imageRatio > canvasRatio ? usableWidth : usableHeight * imageRatio;
        const drawHeight = imageRatio > canvasRatio ? usableWidth / imageRatio : usableHeight;
        const drawX = (canvas.width - drawWidth) / 2;
        const drawY = padding;

        context.drawImage(image, drawX, drawY, drawWidth, drawHeight);

        const jpegDataUrl = canvas.toDataURL('image/jpeg', 0.94);
        const jpegBytes = base64ToUint8Array(jpegDataUrl.split(',')[1]);
        const pdfBlob = createSingleImagePdf(jpegBytes, canvas.width, canvas.height);
        const baseName = sanitizeDownloadFileName(documentTitle, 'Notenblatt');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = baseName + '.pdf';
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(function () {
            URL.revokeObjectURL(pdfUrl);
        }, 1000);
    } catch (error) {
        console.error('PDF-Export fehlgeschlagen', error);
        alert('Fehler beim PDF-Export: ' + error.message);
    } finally {
        URL.revokeObjectURL(svgUrl);
    }
}


// Auslesen


const noteElementIds = ['tone', 'bass', 'slap', 'tone_muffled', 'slap_muffled', 'slap_muffled', 'tone_flam', 'slap_flam', 'bass_slap_flam'];
const controlElementIds = ['in', 'out', 'shortbar', 'wiederholung'];

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
        stepsPerBar: 18,
        totalStepsPerLine: 36,
        gapSlotCount: 2,
        getLineSlotIndex: function (centerX) {
            return Math.round((centerX - 121.25) / 21.25);
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
    if (element.attr('id') == 'shortbar') {
        const markerLine = typeof element.select === 'function'
            ? element.select('.shortbar-marker-line')
            : null;
        const transformState = typeof element.transform === 'function' ? element.transform() : null;
        const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
        const markerX = markerLine
            ? Number(markerLine.attr('x1'))
            : NaN;
        const explicitAnchorY = Number(element.attr('data-shortbar-anchor-y'));
        const markerY1 = markerLine
            ? Number(markerLine.attr('y1'))
            : NaN;
        const markerY2 = markerLine
            ? Number(markerLine.attr('y2'))
            : NaN;
        if (Number.isFinite(markerX) && (Number.isFinite(explicitAnchorY) || (Number.isFinite(markerY1) && Number.isFinite(markerY2)))) {
            return {
                x: markerX + (localMatrix ? localMatrix.e : 0),
                y: (Number.isFinite(explicitAnchorY) ? explicitAnchorY : ((markerY1 + markerY2) / 2)) +
                    (localMatrix ? localMatrix.f : 0)
            };
        }
        const shortBarBounds = element.getBBox();
        return {
            x: shortBarBounds.cx,
            y: shortBarBounds.cy
        };
    }
    return {
        x: element.getBBox().cx,
        y: element.getBBox().cy
    };
}

function getControlLineSlotIndex(centerX, readConfig, controlType) {
    if (controlType === 'in' || controlType === 'out') {
        return readConfig.getLineSlotIndex(centerX);
    }
    if (controlType === 'shortbar') {
        if (rhythm == 'binaer') {
            return Math.ceil(((centerX - 25) / 12.5) - 7);
        }
        if (rhythm == 'tenaer') {
            return Math.ceil(((centerX - 34) / 16.5) - 5);
        }
        return Math.ceil((centerX - 121.25) / 21.25);
    }
    return readConfig.getLineSlotIndex(centerX);
}

function getBarIndexFromPosition(centerX, centerY, readConfig, lineCount) {
    const rawLineSlotIndex = readConfig.getLineSlotIndex(centerX);
    const gapSlotCount = Number(readConfig.gapSlotCount) || 2;
    let lineSlotIndex = rawLineSlotIndex;
    if (lineSlotIndex > readConfig.stepsPerBar) {
        lineSlotIndex -= gapSlotCount;
    }
    const barOffset = rawLineSlotIndex > readConfig.stepsPerBar + gapSlotCount ? 1 : 0;
    const lineIndex = getSheetLineIndexFromY(centerY, lineCount, 65);
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
    const lineIndex = getSheetLineIndexFromY(centerY, lineCount, -32);
    return {
        rawLineSlotIndex: rawLineSlotIndex,
        lineSlotIndex: lineSlotIndex,
        lineIndex: lineIndex,
        barIndex: lineIndex * 2 + barOffset
    };
}

function getRepeatTarget(centerX, centerY, lineCount) {
    const lineIndex = getSheetLineIndexFromY(centerY, lineCount, 65);
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
                    const controlLabel = control.type === 'in'
                        ? 'In'
                        : (control.type === 'shortbar' ? 'ShortBar' : 'Out');
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

function openAudioTestTarget(playerRows, targetName, embedded) {
    const form = document.createElement('form');
    form.action = 'Audio/audioplayer.php';
    form.method = 'POST';
    form.target = targetName || '_blank';
    form.innerHTML = '<input type="hidden" name="myObj" /><input type="hidden" name="embedded" /><input type="hidden" name="uiTheme" />';
    document.body.appendChild(form);
    form.querySelector('input[name="myObj"]').value = JSON.stringify(playerRows);
    form.querySelector('input[name="embedded"]').value = embedded ? '1' : '';
    form.querySelector('input[name="uiTheme"]').value = document.body.dataset.uiTheme || '';
    form.submit();
    form.remove();
}

function openAudioTestWindow(playerRows) {
    openAudioTestTarget(playerRows, '_blank', false);
}

function openAudioTestFrame(playerRows, frameName) {
    openAudioTestTarget(playerRows, frameName, true);
}

let practiceAudioRefreshTimer = null;
let practiceAudioPlaybackState = 'stopped';
let timelineAudioRefreshTimer = null;
let timelineAudioPayloadSignature = '';
let timelineAudioPlaybackState = 'stopped';
window.suppressNextTimelineAudioRefresh = false;

function isMobilePracticeViewport() {
    return window.matchMedia('(max-width: 760px)').matches ||
        (window.matchMedia('(hover: none) and (pointer: coarse)').matches &&
            Math.min(window.innerWidth || 0, window.innerHeight || 0) <= 760);
}

function isMobileLandscapeViewport() {
    return window.matchMedia('(hover: none) and (pointer: coarse) and (orientation: landscape)').matches &&
        Math.min(window.innerWidth || 0, window.innerHeight || 0) <= 760;
}

function updateMobilePracticeModeAvailability() {
    const mobilePracticeViewport = isMobilePracticeViewport();
    const mobileLandscapeViewport = isMobileLandscapeViewport();
    const orientationNoticeEl = document.getElementById('mobileOrientationNotice');
    document.body.classList.toggle('is-mobile-practice-viewport', mobilePracticeViewport);
    document.body.classList.toggle('is-mobile-landscape-blocked', mobileLandscapeViewport);
    if (orientationNoticeEl) {
        orientationNoticeEl.hidden = true;
        orientationNoticeEl.setAttribute('aria-hidden', 'true');
    }

    [
        'button4',
        'button5',
        'button8',
        'addSheetPageButton',
        'deleteSheetPageButton',
        'button7',
        'button9',
        'button11',
        'button6'
    ].forEach(function (buttonId) {
        const buttonEl = document.getElementById(buttonId);
        if (!buttonEl) {
            return;
        }
        buttonEl.disabled = mobilePracticeViewport;
        if (mobilePracticeViewport) {
            buttonEl.title = 'Auf Smartphones ist nur Üben/Abspielen aktiv.';
        } else {
            buttonEl.removeAttribute('title');
        }
    });
    updateSheetPageControls();

    if (mobilePracticeViewport && timelineState.visible) {
        timelineState.visible = false;
        clearTimelineAudioPlayer();
        renderTimelinePanel();
    }
}

function isPracticeAudioModeActive() {
    const practicePanelEl = document.getElementById('practicePanel');
    return practiceState.visible || (practicePanelEl && !practicePanelEl.hidden);
}

function buildAudioTestPayload(forcePracticeMode) {
    const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
    syncTimelineStateFromReadResultIfNeeded(readResult, buildCurrentTimelineSyncOptions());
    const practiceIsActive = forcePracticeMode || isPracticeAudioModeActive();
    let playerPayload;

    if (practiceIsActive) {
        syncPracticeSelectionsWithPatternLibrary();
        playerPayload = buildPracticePlayerPayload();
        if (typeof renderPracticeScrollerFromPayload === 'function') {
            renderPracticeScrollerFromPayload(playerPayload);
        }
    } else {
        playerPayload = buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries);
    }

    if (!practiceIsActive && !timelinePayloadHasPlayableEntries(playerPayload)) {
        console.warn('Timeline-Payload ist leer oder nicht spielbar, verwende direkten Notenblatt-Payload.');
        playerPayload = buildPlayerRowsFromRhythmBars(readResult.rhythmBars, readResult.repeatRanges);
    }

    window.lastPlayerRows = playerPayload;
    console.log('playerRows', playerPayload);
    return {
        playerPayload: playerPayload,
        practiceIsActive: practiceIsActive
    };
}

function timelinePayloadHasPlayableEntries(playerPayload) {
    const config = Array.isArray(playerPayload) ? playerPayload[0] : null;
    if (!config || !config.TimelineMode) {
        return false;
    }

    const patternLibrary = Array.isArray(config.PatternLibrary) ? config.PatternLibrary : [];
    const timelineEntries = Array.isArray(config.TimelineEntries) ? config.TimelineEntries : [];
    if (patternLibrary.length === 0 || timelineEntries.length === 0) {
        return false;
    }

    return timelineEntries.some(function (entry) {
        if (!entry || !entry.patternId) {
            return false;
        }
        const hasTarget = Array.isArray(entry.targetInstruments) && entry.targetInstruments.length > 0;
        const pattern = patternLibrary.find(function (candidatePattern) {
            return candidatePattern && candidatePattern.id === entry.patternId;
        });
        const hasNotes = pattern && Array.isArray(pattern.bars) && pattern.bars.some(function (bar) {
            return bar && Array.isArray(bar.notes) && bar.notes.some(function (noteValue) {
                return noteValue && noteValue !== 'f';
            });
        });
        return hasTarget && hasNotes;
    });
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
        const rawPositionInfo = getBarIndexFromPosition(elementPosition.x, elementPosition.y, readConfig, anzahl);
        if (elementId === 'shortbar' || elementId === 'in' || elementId === 'out') {
            rawPositionInfo.rawLineSlotIndex = getControlLineSlotIndex(elementPosition.x, readConfig, elementId);
            rawPositionInfo.lineSlotIndex = rawPositionInfo.rawLineSlotIndex;
            if (rawPositionInfo.lineSlotIndex > readConfig.stepsPerBar) {
                rawPositionInfo.lineSlotIndex -= Number(readConfig.gapSlotCount) || 2;
            }
            rawPositionInfo.barIndex = rawPositionInfo.lineIndex * 2 +
                (rawPositionInfo.rawLineSlotIndex > readConfig.stepsPerBar + (Number(readConfig.gapSlotCount) || 2) ? 1 : 0);
        }
        const positionInfo = rawPositionInfo;
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
        const audioTest = buildAudioTestPayload(false);
        if (audioTest.practiceIsActive) {
            openPracticeAudioPlayer(audioTest.playerPayload);
            return;
        }
        openAudioTestWindow(audioTest.playerPayload);
    } catch (error) {
        console.error('runAudioTest failed', error);
        alert('Fehler beim Audiotest: ' + error.message);
    }
}

function openPracticeAudioPlayer(playerPayload) {
    const playerPanelEl = document.querySelector('.practice-player-panel');
    const playerFrameEl = document.getElementById('practiceAudioFrame');
    if (!playerPanelEl || !playerFrameEl) {
        openAudioTestWindow(playerPayload);
        return;
    }

    playerPanelEl.hidden = false;
    openAudioTestFrame(playerPayload, playerFrameEl.name || 'practiceAudioFrame');
}

function refreshPracticeAudioPlayer() {
    if (!isPracticeAudioModeActive()) {
        return;
    }

    try {
        const audioTest = buildAudioTestPayload(true);
        openPracticeAudioPlayer(audioTest.playerPayload);
    } catch (error) {
        console.error('refreshPracticeAudioPlayer failed', error);
    }
}

function schedulePracticeAudioRefresh(delayMs) {
    if (!isPracticeAudioModeActive()) {
        return;
    }

    window.clearTimeout(practiceAudioRefreshTimer);
    practiceAudioRefreshTimer = window.setTimeout(refreshPracticeAudioPlayer, Math.max(0, Number(delayMs) || 0));
}

function openTimelineAudioPlayer(playerPayload) {
    const playerPanelEl = document.querySelector('.timeline-player-panel');
    const playerFrameEl = document.getElementById('timelineAudioFrame');
    if (!playerPanelEl || !playerFrameEl) {
        openAudioTestWindow(playerPayload);
        return;
    }

    playerPanelEl.hidden = false;
    openAudioTestFrame(playerPayload, playerFrameEl.name || 'timelineAudioFrame');
}

function refreshTimelineAudioPlayer() {
    if (!timelineState.visible) {
        return;
    }

    try {
        const audioTest = buildAudioTestPayload(false);
        const payloadSignature = JSON.stringify(getAudioPayloadRefreshSignature(audioTest.playerPayload));
        if (payloadSignature === timelineAudioPayloadSignature) {
            return;
        }
        timelineAudioPayloadSignature = payloadSignature;
        openTimelineAudioPlayer(audioTest.playerPayload);
    } catch (error) {
        console.error('refreshTimelineAudioPlayer failed', error);
    }
}

function getAudioPayloadRefreshSignature(playerPayload) {
    if (!Array.isArray(playerPayload)) {
        return playerPayload;
    }

    return playerPayload.map(function (config) {
        if (!config || typeof config !== 'object') {
            return config;
        }
        const signatureConfig = Object.assign({}, config);
        delete signatureConfig.PracticeInstrumentVolumes;
        return signatureConfig;
    });
}

function scheduleTimelineAudioRefresh(delayMs) {
    if (!timelineState.visible) {
        return;
    }

    window.clearTimeout(timelineAudioRefreshTimer);
    timelineAudioRefreshTimer = window.setTimeout(refreshTimelineAudioPlayer, Math.max(0, Number(delayMs) || 0));
}

function clearTimelineAudioPlayer() {
    const playerPanelEl = document.querySelector('.timeline-player-panel');
    const playerFrameEl = document.getElementById('timelineAudioFrame');
    window.clearTimeout(timelineAudioRefreshTimer);
    timelineAudioPayloadSignature = '';
    timelineAudioPlaybackState = 'stopped';
    if (playerPanelEl) {
        playerPanelEl.hidden = true;
    }
    if (playerFrameEl) {
        playerFrameEl.src = 'about:blank';
    }
}

function notifyPracticeSelectionChanged() {
    if (typeof updateTimelineMetadataNode === 'function') {
        updateTimelineMetadataNode();
    }

    if (isPracticeAudioModeActive() && practiceAudioPlaybackState === 'playing') {
        try {
            const audioTest = buildAudioTestPayload(true);
            const playerConfig = Array.isArray(audioTest.playerPayload) ? audioTest.playerPayload[0] : null;
            if (playerConfig && Array.isArray(playerConfig.PracticeSections) && sendPracticeAudioMessage({
                type: 'barabeat-practice-sections-update',
                sections: playerConfig.PracticeSections,
                timelineLoopCount: playerConfig.TimelineLoopCount,
                practiceDurationSeconds: playerConfig.PracticeDurationSeconds
            })) {
                return;
            }
        } catch (error) {
            console.error('notifyPracticeSelectionChanged live update failed', error);
        }
    }

    schedulePracticeAudioRefresh(250);
}

function sendPracticeAudioMessage(message) {
    const playerFrameEl = document.getElementById('practiceAudioFrame');
    if (!playerFrameEl || !playerFrameEl.contentWindow) {
        return false;
    }
    playerFrameEl.contentWindow.postMessage(message, window.location.origin);
    return true;
}

function sendTimelineAudioMessage(message) {
    const playerFrameEl = document.getElementById('timelineAudioFrame');
    if (!playerFrameEl || !playerFrameEl.contentWindow) {
        return false;
    }
    playerFrameEl.contentWindow.postMessage(message, window.location.origin);
    return true;
}

function handleEmbeddedAudioPlayerMessage(event) {
    if (event.origin !== window.location.origin) {
        return;
    }

    const message = event.data || {};
    if (!message || typeof message.type !== 'string') {
        return;
    }

    const practiceFrameEl = document.getElementById('practiceAudioFrame');
    const timelineFrameEl = document.getElementById('timelineAudioFrame');
    const isPracticeFrame = practiceFrameEl && event.source === practiceFrameEl.contentWindow;
    const isTimelineFrame = timelineFrameEl && event.source === timelineFrameEl.contentWindow;
    if (!isPracticeFrame && !isTimelineFrame) {
        return;
    }

    if (message.type === 'barabeat-audio-tempo-change') {
        const nextTempo = normalizeTimelineTempo(message.tempo);
        if (timelineState.tempo !== nextTempo) {
            recordArrangementHistorySnapshot();
        }
        timelineState.tempo = nextTempo;
        window.suppressNextTimelineAudioRefresh = isTimelineFrame;
        updateTimelineMetadataNode();
        renderTimelinePanel();
        return;
    }

    if (isPracticeFrame && message.type === 'barabeat-audio-step' && typeof updatePracticeScrollerPlayback === 'function') {
        updatePracticeScrollerPlayback(message.playbackStep, message.delayMs);
        return;
    }

    if (message.type === 'barabeat-audio-state') {
        if (isTimelineFrame) {
            timelineAudioPlaybackState = message.state || 'stopped';
            return;
        }
        practiceAudioPlaybackState = message.state || 'stopped';
        if (isPracticeFrame && typeof updatePracticeScrollerState === 'function') {
            updatePracticeScrollerState(message.state, message.leadInMs);
        }
    }
}

function clearPracticeAudioPlayer() {
    const playerPanelEl = document.querySelector('.practice-player-panel');
    const playerFrameEl = document.getElementById('practiceAudioFrame');
    window.clearTimeout(practiceAudioRefreshTimer);
    practiceAudioPlaybackState = 'stopped';
    if (playerPanelEl) {
        playerPanelEl.hidden = false;
    }
    if (playerFrameEl) {
        playerFrameEl.src = 'about:blank';
    }
    if (typeof clearPracticeScrollerPlayback === 'function') {
        clearPracticeScrollerPlayback();
    }
}

function showAutoDismissMessage(message, durationMs) {
    const existingEl = document.querySelector('.auto-dismiss-message');
    if (existingEl) {
        existingEl.remove();
    }

    const messageEl = document.createElement('div');
    messageEl.className = 'auto-dismiss-message';
    messageEl.textContent = message;
    document.body.appendChild(messageEl);

    window.setTimeout(function () {
        messageEl.classList.add('is-hiding');
        window.setTimeout(function () {
            messageEl.remove();
        }, 180);
    }, Math.max(500, Number(durationMs) || 900));
}

// Speichern

async function saveCurrentScoreLocal(nameOverride, folderIdOverride, options) {
    const saveOptions = options || {};
    const serializedRhythm = buildSerializedRhythm();
    const name = (nameOverride || titel.attr('text') || 'Unbenannt').trim();
    const scoreId = saveOptions.asCopy ? null : currentScoreId;
    const existingScore = scoreId ? await localLibrary.getScore(scoreId) : null;
    const folderId = folderIdOverride ||
        (existingScore && existingScore.folderId) ||
        localLibrary.rootFolderId;

    const savedScore = await localLibrary.saveScore({
        id: scoreId,
        title: name,
        folderId: folderId,
        format: 'bbs',
        content: serializedRhythm
    });

    currentScoreId = savedScore.id;
    setRhythmTitle(savedScore.title);
    setSelectedFileSource('local');
    await refreshFileList();
    return savedScore;
}

function getCurrentRhythmTitle() {
    return String(titel && typeof titel.attr === 'function' ? titel.attr('text') || '' : '').trim();
}

function isDefaultRhythmTitle(titleValue) {
    return isDefaultTitleText(titleValue);
}

async function getSaveDialogModeForDirectSave() {
    const currentTitle = getCurrentRhythmTitle();
    if (isDefaultRhythmTitle(currentTitle)) {
        return 'save';
    }
    if (!currentScoreId) {
        return '';
    }
    const existingScore = await localLibrary.getScore(currentScoreId);
    return existingScore && String(existingScore.title || '').trim() !== currentTitle ? 'saveAs' : '';
}

async function saveCurrentScoreFromMenu() {
    try {
        const saveDialogMode = await getSaveDialogModeForDirectSave();
        if (saveDialogMode) {
            openFileDialog(saveDialogMode);
            return;
        }
        const savedScore = await saveCurrentScoreLocal();
        showAutoDismissMessage('"' + savedScore.title + '" wurde lokal gespeichert.');
        closeAppMenus();
    } catch (error) {
        console.error('Lokales Speichern fehlgeschlagen', error);
        alert('Fehler beim lokalen Speichern: ' + error.message);
    }
}

function callPHPScript() {
    saveCurrentScoreFromMenu();
}

async function renameLocalScore() {
    try {
        const scoreId = getSelectedLocalScoreId();
        if (!scoreId) {
            alert('Bitte zuerst eine lokale Datei auswählen oder speichern.');
            return;
        }

        const score = await localLibrary.getScore(scoreId);
        if (!score) {
            alert('Die lokale Datei wurde nicht gefunden.');
            return;
        }

        const nextTitle = prompt('Neuer Name:', score.title || '');
        if (nextTitle == null) {
            return;
        }

        const trimmedTitle = nextTitle.trim();
        if (!trimmedTitle) {
            alert('Der Name darf nicht leer sein.');
            return;
        }

        const renamedScore = await localLibrary.saveScore(Object.assign({}, score, {
            title: trimmedTitle
        }));

        currentScoreId = renamedScore.id;
        setRhythmTitle(renamedScore.title);
        setSelectedFileSource('local');
        await refreshFileList();
        alert('"' + renamedScore.title + '" wurde lokal umbenannt.');
    } catch (error) {
        console.error('Lokales Umbenennen fehlgeschlagen', error);
        alert('Fehler beim lokalen Umbenennen: ' + error.message);
    }
}

async function deleteLocalScore() {
    try {
        const scoreId = getSelectedLocalScoreId();
        if (!scoreId) {
            alert('Bitte zuerst eine lokale Datei auswählen.');
            return;
        }

        const score = await localLibrary.getScore(scoreId);
        if (!score) {
            alert('Die lokale Datei wurde nicht gefunden.');
            return;
        }

        const shouldDelete = confirm('"' + score.title + '" lokal löschen?\nDer Server bleibt unverändert.');
        if (!shouldDelete) {
            return;
        }

        await localLibrary.deleteScore(score.id);

        setSelectedFileSource('local');
        if (score.id === getRememberedLastLoadedScoreId()) {
            rememberLastLoadedScore('');
        }
        viererNoten();
        await refreshFileList();
        alert('"' + score.title + '" wurde lokal gelöscht.');
    } catch (error) {
        console.error('Lokales Löschen fehlgeschlagen', error);
        alert('Fehler beim lokalen Löschen: ' + error.message);
    }
}

async function publishCurrentScoreToServer(nameOverride) {
    const savedScore = await saveCurrentScoreLocal(nameOverride);
    const serverBaseName = String(savedScore.serverPath || '').replace(/\.(bbs|txt)$/i, '');
    const localBaseName = String(savedScore.title || '').trim();
    const publishToken = String(savedScore.publishToken || '').trim();
    const canUpdatePublishedScore = Boolean(
        savedScore.serverPath &&
        publishToken &&
        serverBaseName === localBaseName
    );

    const scoreForNewPublication = Object.assign({}, savedScore, {
        serverPath: '',
        fileName: '',
        publishToken: ''
    });
    let publishResult;

    if (canUpdatePublishedScore) {
        try {
            publishResult = await serverLibrary.updatePublishedScore(savedScore);
        } catch (error) {
            if (!/Publish-Token|kein Publish/i.test(error.message || '')) {
                throw error;
            }
            publishResult = await serverLibrary.publishScore(scoreForNewPublication);
        }
    } else {
        publishResult = await serverLibrary.publishScore(scoreForNewPublication);
    }

    const publishedScore = await localLibrary.markPublished(
        savedScore.id,
        publishResult.serverPath,
        publishResult.publishToken
    );

    await refreshFileList();
    return publishedScore;
}

async function publishCurrentScore() {
    try {
        const publishedScore = await publishCurrentScoreToServer();
        alert('"' + publishedScore.title + '" wurde veröffentlicht.');
    } catch (error) {
        console.error('Veröffentlichen fehlgeschlagen', error);
        alert('Fehler beim Veröffentlichen: ' + error.message);
    }
}

function applyDialogNameToTitle() {
    const nameValue = String(document.querySelector('#fileDialogName')?.value || '').trim();
    if (nameValue) {
        setRhythmTitle(nameValue);
    }
    return nameValue || titel.attr('text') || 'Unbenannt';
}

async function openLocalScore(scoreId) {
    const score = await localLibrary.getScore(scoreId);
    if (!score) {
        throw new Error('Die lokale Datei wurde nicht gefunden.');
    }
    loadRhythmContent(score.title, score.content || score.data, score.id);
    return score;
}

async function importServerScore(serverPath) {
    const serverScore = await serverLibrary.importScore(serverPath);
    const savedScore = await localLibrary.findScoreByServerPath(serverScore.serverPath).then(function (existingScore) {
        if (existingScore) {
            return existingScore;
        }

        return localLibrary.saveScore({
            title: serverScore.title,
            folderId: localLibrary.rootFolderId,
            format: serverScore.format,
            content: serverScore.content,
            isPublished: true,
            serverPath: serverScore.serverPath,
            syncState: 'published'
        });
    });

    loadRhythmContent(savedScore.title, savedScore.content, savedScore.id);
    setSelectedFileSource('local');
    await refreshFileList();
    return savedScore;
}

async function confirmFileDialog() {
    try {
        if (fileDialogState.mode === 'open') {
            const entry = getSelectedFileDialogEntry();
            if (!entry) {
                return;
            }
            if (fileDialogState.source === 'local' && isFileDialogFolderEntry(entry)) {
                await navigateFileDialogFolder(entry.targetFolderId || entry.id);
                return;
            }
            if (fileDialogState.source === 'server') {
                await importServerScore(entry.serverPath || entry.fileName);
            } else {
                await openLocalScore(entry.id);
            }
            closeFileDialog();
            return;
        }

        const selectedEntry = getSelectedFileDialogEntry();
        if ((fileDialogState.mode === 'save' || fileDialogState.mode === 'saveAs') &&
            fileDialogState.source === 'local' &&
            isFileDialogFolderEntry(selectedEntry)) {
            await navigateFileDialogFolder(selectedEntry.targetFolderId || selectedEntry.id);
            return;
        }

        const chosenName = applyDialogNameToTitle();
        if (fileDialogState.mode === 'export' && fileDialogState.format === 'pdf') {
            closeFileDialog();
            exportCurrentSheetAsPdf();
            return;
        }
        if (fileDialogState.mode === 'export' && fileDialogState.format === 'svg') {
            closeFileDialog();
            callPHPScript2(chosenName);
            return;
        }

        if (fileDialogState.source === 'server') {
            const publishedScore = await publishCurrentScoreToServer(chosenName);
            closeFileDialog();
            alert('"' + publishedScore.title + '" wurde veröffentlicht.');
            return;
        }

        const savedScore = await saveCurrentScoreLocal(chosenName, fileDialogState.folderId, {
            asCopy: fileDialogState.mode === 'saveAs'
        });
        closeFileDialog();
        showAutoDismissMessage('"' + savedScore.title + '" wurde lokal gespeichert.');
    } catch (error) {
        console.error('Dateidialog-Aktion fehlgeschlagen', error);
        alert('Fehler: ' + error.message);
    }
}

async function createFileDialogFolder() {
    if (fileDialogState.source !== 'local' || fileDialogState.mode === 'export') {
        return;
    }

    const folderName = prompt('Name des neuen Ordners:', 'Neuer Ordner');
    if (folderName == null) {
        return;
    }

    const trimmedName = folderName.trim();
    if (!trimmedName) {
        alert('Der Ordnername darf nicht leer sein.');
        return;
    }

    try {
        const folder = await localLibrary.createFolder(trimmedName, fileDialogState.folderId);
        await navigateFileDialogFolder(folder.id);
    } catch (error) {
        console.error('Ordner konnte nicht erstellt werden', error);
        alert('Fehler beim Erstellen des Ordners: ' + error.message);
    }
}

async function renameSelectedFileDialogScore() {
    const entry = getSelectedFileDialogEntry();
    if (!entry || fileDialogState.source !== 'local') {
        return;
    }

    if (entry.entryType === 'folder') {
        const nextName = prompt('Neuer Ordnername:', entry.name || entry.title || '');
        if (nextName == null) {
            return;
        }

        const trimmedName = nextName.trim();
        if (!trimmedName) {
            alert('Der Ordnername darf nicht leer sein.');
            return;
        }

        try {
            const renamedFolder = await localLibrary.renameFolder(entry.id, trimmedName);
            fileDialogState.selectedId = renamedFolder.id;
            await refreshFileDialogEntries();
        } catch (error) {
            console.error('Lokaler Ordner konnte nicht umbenannt werden', error);
            alert('Fehler beim Umbenennen des Ordners: ' + error.message);
        }
        return;
    }

    if (entry.entryType === 'score') {
        currentScoreId = entry.id;
        await renameLocalScore();
        await refreshFileDialogEntries();
    }
}

async function deleteSelectedFileDialogScore() {
    const entry = getSelectedFileDialogEntry();
    if (!entry || fileDialogState.source !== 'local') {
        return;
    }

    if (entry.entryType === 'folder') {
        if (!entry.isEmpty) {
            alert('Der Ordner enthält noch Dateien oder Unterordner und kann nicht gelöscht werden.');
            return;
        }

        const shouldDeleteFolder = confirm('Den leeren Ordner "' + (entry.name || entry.title) + '" lokal löschen?');
        if (!shouldDeleteFolder) {
            return;
        }

        try {
            await localLibrary.deleteFolder(entry.id);
            fileDialogState.selectedId = null;
            await refreshFileDialogEntries();
        } catch (error) {
            console.error('Lokaler Ordner konnte nicht gelöscht werden', error);
            alert('Fehler beim Löschen des Ordners: ' + error.message);
        }
        return;
    }

    if (entry.entryType === 'score') {
        currentScoreId = entry.id;
        await deleteLocalScore();
        fileDialogState.selectedId = null;
        await refreshFileDialogEntries();
    }
}

async function deletePublishedFileDialogScore() {
    const entry = getSelectedFileDialogEntry();
    if (!entry || fileDialogState.source !== 'local') {
        return;
    }

    if (!entry.serverPath || !entry.publishToken) {
        alert('Diese lokale Datei hat kein Publish-Token für eine Serververöffentlichung.');
        return;
    }

    const shouldDelete = confirm(
        '"' + (entry.title || entry.serverPath) + '" vom Server löschen?\n' +
        'Die lokale Datei bleibt erhalten.'
    );
    if (!shouldDelete) {
        return;
    }

    try {
        await serverLibrary.deletePublishedScore(entry);
        const localScore = await localLibrary.unmarkPublished(entry.id);
        currentScoreId = localScore.id;
        setSelectedFileSource('local');
        await refreshFileList();
        await refreshFileDialogEntries();
        alert('Die Veröffentlichung wurde gelöscht. Die lokale Datei bleibt erhalten.');
    } catch (error) {
        console.error('Veröffentlichung konnte nicht gelöscht werden', error);
        alert('Fehler beim Löschen der Veröffentlichung: ' + error.message);
    }
}

let scrollOn = false;
const uiThemeStorageKey = 'barabeat-ui-theme';
const lastLoadedScoreStorageKey = 'barabeat-last-loaded-score-id';

function setUiTheme(themeName) {
    const normalizedTheme = themeName === 'playful' || themeName === 'earth' ? themeName : '';
    if (normalizedTheme) {
        document.body.dataset.uiTheme = normalizedTheme;
        try {
            window.localStorage.setItem(uiThemeStorageKey, normalizedTheme);
        } catch (error) {
            console.warn('Theme konnte nicht gespeichert werden', error);
        }
    } else {
        document.body.removeAttribute('data-ui-theme');
        try {
            window.localStorage.removeItem(uiThemeStorageKey);
        } catch (error) {
            console.warn('Theme konnte nicht zurückgesetzt werden', error);
        }
    }

    const themeClearButtonEl = document.getElementById('themeClearButton');
    const themePlayfulButtonEl = document.getElementById('themePlayfulButton');
    const themeEarthButtonEl = document.getElementById('themeEarthButton');
    if (themeClearButtonEl) {
        themeClearButtonEl.classList.toggle('is-active', !normalizedTheme);
    }
    if (themePlayfulButtonEl) {
        themePlayfulButtonEl.classList.toggle('is-active', normalizedTheme === 'playful');
    }
    if (themeEarthButtonEl) {
        themeEarthButtonEl.classList.toggle('is-active', normalizedTheme === 'earth');
    }
    sendPracticeAudioMessage({
        type: 'barabeat-ui-theme',
        theme: normalizedTheme
    });
    sendTimelineAudioMessage({
        type: 'barabeat-ui-theme',
        theme: normalizedTheme
    });
}

function initializeUiTheme() {
    let storedTheme = '';
    try {
        storedTheme = window.localStorage.getItem(uiThemeStorageKey) || '';
    } catch (error) {
        storedTheme = '';
    }
    setUiTheme(storedTheme);
}

function rememberLastLoadedScore(scoreId) {
    const normalizedScoreId = String(scoreId || '').trim();
    try {
        if (normalizedScoreId) {
            window.localStorage.setItem(lastLoadedScoreStorageKey, normalizedScoreId);
        } else {
            window.localStorage.removeItem(lastLoadedScoreStorageKey);
        }
    } catch (error) {
        console.warn('Letzter geladener Titel konnte nicht gespeichert werden', error);
    }
}

function getRememberedLastLoadedScoreId() {
    try {
        return window.localStorage.getItem(lastLoadedScoreStorageKey) || '';
    } catch (error) {
        return '';
    }
}

async function loadRememberedLastScore() {
    const rememberedScoreId = getRememberedLastLoadedScoreId();
    if (!rememberedScoreId) {
        return false;
    }

    try {
        const score = await localLibrary.getScore(rememberedScoreId);
        if (!score) {
            rememberLastLoadedScore('');
            return false;
        }
        loadRhythmContent(score.title, score.content || score.data, score.id, { remember: false });
        setSelectedFileSource('local');
        return true;
    } catch (error) {
        console.warn('Letzter geladener Titel konnte nicht geöffnet werden', error);
        return false;
    }
}

function closeAppMenus() {
    document.querySelectorAll('#appMenuBar details.app-menu[open]').forEach(function (menuEl) {
        menuEl.open = false;
    });
    document.querySelectorAll('#appMenuBar details.app-submenu[open]').forEach(function (menuEl) {
        menuEl.open = false;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initializeUiTheme();
    updateMobilePracticeModeAvailability();
    window.addEventListener('resize', updateMobilePracticeModeAvailability);
    window.addEventListener('orientationchange', function () {
        updateMobilePracticeModeAvailability();
        window.setTimeout(updateMobilePracticeModeAvailability, 250);
    });
    window.addEventListener('message', handleEmbeddedAudioPlayerMessage);

    document.querySelectorAll('#appMenuBar details.app-menu').forEach(function (menuEl) {
        const summaryEl = menuEl.querySelector('summary');
        if (summaryEl && menuEl.dataset.mobilePracticeMenu === 'true') {
            summaryEl.addEventListener('click', function (event) {
                if (!isMobilePracticeViewport()) {
                    return;
                }
                event.preventDefault();
                menuEl.open = false;
                const practiceButtonEl = document.getElementById('practiceButton');
                if (practiceButtonEl) {
                    practiceButtonEl.click();
                }
            });
        }
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

    document.querySelector('#appMenuBar').addEventListener('click', function (event) {
        if (event.target.closest('button')) {
            closeAppMenus();
        }
    });

    window.addEventListener('scroll', schedulePaletteViewportFollow, { passive: true });
    window.addEventListener('resize', schedulePaletteViewportFollow);
    schedulePaletteViewportFollow();

    document.querySelector('#openFileDialogButton').addEventListener('click', function () {
        openFileDialog('open');
    });
    document.querySelector('#saveFileDialogButton').addEventListener('click', function () {
        saveCurrentScoreFromMenu();
    });
    document.querySelector('#saveAsFileDialogButton').addEventListener('click', function () {
        openFileDialog('saveAs');
    });
    document.querySelector('#exportFileDialogButton').addEventListener('click', function () {
        openFileDialog('export');
    });
    document.querySelector('#fileDialogCancelButton').addEventListener('click', closeFileDialog);
    document.querySelector('#fileDialogConfirmButton').addEventListener('click', confirmFileDialog);
    document.querySelector('#fileDialogRefreshButton').addEventListener('click', refreshFileDialogEntries);
    document.querySelector('#fileDialogNewFolderButton').addEventListener('click', createFileDialogFolder);
    document.querySelector('#fileDialogRenameButton').addEventListener('click', renameSelectedFileDialogScore);
    document.querySelector('#fileDialogDeleteButton').addEventListener('click', deleteSelectedFileDialogScore);
    document.querySelector('#fileDialogUnpublishButton').addEventListener('click', deletePublishedFileDialogScore);
    document.querySelector('#fileDialogSearch').addEventListener('input', renderFileDialogList);
    document.querySelector('#fileDialogName').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            confirmFileDialog();
        }
    });
    document.querySelector('#fileDialogFormat').addEventListener('change', function (event) {
        fileDialogState.format = event.target.value;
        updateFileDialogControls();
    });
    document.querySelectorAll('.file-dialog-source').forEach(function (sourceButton) {
        sourceButton.addEventListener('click', function () {
            setSelectedFileSource(sourceButton.dataset.source);
            fileDialogState.selectedId = null;
            refreshFileDialogEntries();
        });
    });
    document.querySelectorAll('.file-dialog-filter').forEach(function (filterButton) {
        filterButton.addEventListener('click', function () {
            if (fileDialogState.source !== 'local') {
                return;
            }
            fileDialogState.filter = filterButton.dataset.filter;
            renderFileDialogList();
        });
    });
    document.querySelector('#fileDialog').addEventListener('click', function (event) {
        if (event.target === event.currentTarget) {
            closeFileDialog();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !document.querySelector('#fileDialog').hidden) {
            closeFileDialog();
        }
    });
    document.querySelector('#button3').addEventListener('click', runReadRhythm);
    document.querySelector('#button10').addEventListener('click', runAudioTest);
    document.querySelector('#button4').addEventListener('click', function () {
        recordHistorySnapshot();
        viererNoten();
    });
    document.querySelector('#button5').addEventListener('click', function () {
        recordHistorySnapshot();
        dreierNoten();
    });
    document.querySelector('#button8').addEventListener('click', function () {
        recordHistorySnapshot();
        neunerNoten();
    });
    document.querySelector('#addSheetPageButton').addEventListener('click', addSheetPage);
    document.querySelector('#deleteSheetPageButton').addEventListener('click', deleteSheetPage);
    document.querySelector('#button7').addEventListener('click', function () {
        recordHistorySnapshot();
        addInitialInstrumentChooser(125, 140);
    });
    document.querySelector('#button9').addEventListener('click', function () {
        recordHistorySnapshot();
        addInitialFunctionChooser(260, 140);
    });
    document.querySelector('#resetPaletteButton').addEventListener('click', function () {
        resetPalettePosition();
    });
    document.querySelector('#button11').addEventListener('click', function () {
        if (isMobilePracticeViewport()) {
            return;
        }
        try {
            const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
            syncTimelineStateFromReadResultIfNeeded(readResult, buildCurrentTimelineSyncOptions());
            practiceState.visible = false;
            clearPracticeAudioPlayer();
            timelineState.visible = !timelineState.visible;
            renderPracticePanel();
            renderTimelinePanel();
            if (timelineState.visible) {
                scheduleTimelineAudioRefresh(0);
            } else {
                clearTimelineAudioPlayer();
            }
        } catch (error) {
            console.error('Timeline konnte nicht aktualisiert werden', error);
            alert('Fehler beim Aufbau der Timeline: ' + error.message);
        }
    });
    document.querySelector('#themeClearButton').addEventListener('click', function () {
        setUiTheme('');
    });
    document.querySelector('#themePlayfulButton').addEventListener('click', function () {
        setUiTheme('playful');
    });
    document.querySelector('#themeEarthButton').addEventListener('click', function () {
        setUiTheme('earth');
    });
    document.querySelector('#practiceButton').addEventListener('click', function () {
        try {
            refreshPracticeFromSheet(false);
            timelineState.visible = false;
            clearTimelineAudioPlayer();
            practiceState.visible = !practiceState.visible;
            if (!practiceState.visible) {
                clearPracticeAudioPlayer();
            }
            renderTimelinePanel();
            renderPracticePanel();
            if (practiceState.visible) {
                schedulePracticeAudioRefresh(0);
            }
        } catch (error) {
            console.error('Übungsmodus konnte nicht aktualisiert werden', error);
            alert('Fehler beim Aufbau des Übungsmodus: ' + error.message);
        }
    });
    document.querySelector('#practiceRefreshButton').addEventListener('click', function () {
        try {
            refreshPracticeFromSheet(true);
            schedulePracticeAudioRefresh(0);
        } catch (error) {
            console.error('Übungsmodus-Refresh fehlgeschlagen', error);
            alert('Fehler beim Aktualisieren des Übungsmodus: ' + error.message);
        }
    });
    document.querySelector('#practiceCloseButton').addEventListener('click', function () {
        practiceState.visible = false;
        clearPracticeAudioPlayer();
        renderPracticePanel();
    });
    document.querySelector('#practicePatternChooserToggle').addEventListener('click', function () {
        practiceState.patternChooserExpanded = !practiceState.patternChooserExpanded;
        renderPracticePanel();
    });
    document.querySelector('#mobilePatternChooserButton').addEventListener('click', function () {
        practiceState.patternChooserExpanded = !practiceState.patternChooserExpanded;
        renderPracticePanel();
    });
    document.querySelector('#practiceWithoutSoloLoops').addEventListener('input', function (event) {
        const nextValue = normalizePracticeCount(event.target.value, 1, 0, 32);
        if (practiceState.loopsWithoutSolo !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.loopsWithoutSolo = nextValue;
        event.target.value = practiceState.loopsWithoutSolo;
        notifyPracticeSelectionChanged();
    });
    document.querySelector('#practiceWithSoloLoops').addEventListener('input', function (event) {
        const nextValue = normalizePracticeCount(event.target.value, 1, 1, 32);
        if (practiceState.loopsWithSolo !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.loopsWithSolo = nextValue;
        event.target.value = practiceState.loopsWithSolo;
        notifyPracticeSelectionChanged();
    });
    document.querySelector('#practiceAccompanimentBetweenPatterns').addEventListener('change', function (event) {
        const nextValue = Boolean(event.target.checked);
        if (practiceState.accompanimentBetweenPatterns !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.accompanimentBetweenPatterns = nextValue;
        notifyPracticeSelectionChanged();
    });
    document.querySelector('#practicePauseAccompanimentForLeadInPatterns').addEventListener('change', function (event) {
        const nextValue = Boolean(event.target.checked);
        if (practiceState.pauseAccompanimentForLeadInPatterns !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.pauseAccompanimentForLeadInPatterns = nextValue;
        notifyPracticeSelectionChanged();
    });
    document.querySelector('#practiceRepeatCount').addEventListener('input', function (event) {
        if (practiceState.timerMinutes > 0) {
            event.target.value = practiceState.repeatCount;
            return;
        }
        const nextValue = normalizePracticeCount(event.target.value, 4, 1, practiceRepeatCountMax);
        if (practiceState.repeatCount !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.repeatCount = nextValue;
        event.target.value = practiceState.repeatCount;
        notifyPracticeSelectionChanged();
    });
    document.querySelector('#practiceTimerMinutes').addEventListener('input', function (event) {
        const nextValue = normalizePracticeTimerMinutes(event.target.value);
        if (practiceState.timerMinutes !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.timerMinutes = nextValue;
        event.target.value = practiceState.timerMinutes;
        updatePracticeInputs();
        notifyPracticeSelectionChanged();
    });
    document.querySelectorAll('.practice-stepper-button').forEach(function (buttonEl) {
        buttonEl.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const inputEl = document.getElementById(buttonEl.dataset.practiceStepTarget || '');
            if (!inputEl) {
                return;
            }
            const stepValue = Math.max(1, Number(inputEl.step) || 1);
            const delta = (Number(buttonEl.dataset.practiceStepDelta) || 0) * stepValue;
            const minValue = Number.isFinite(Number(inputEl.min)) ? Number(inputEl.min) : -Infinity;
            const maxValue = Number.isFinite(Number(inputEl.max)) ? Number(inputEl.max) : Infinity;
            const fallbackValue = Number.isFinite(Number(inputEl.value)) ? Number(inputEl.value) : minValue;
            const nextValue = Math.max(minValue, Math.min(maxValue, fallbackValue + delta));
            inputEl.value = nextValue;
            inputEl.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
    function updatePracticeAudioLatencyControl(nextValue) {
        const normalizedValue = normalizePracticeAudioLatency(nextValue);
        if (practiceState.audioLatencyMs !== normalizedValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.audioLatencyMs = normalizedValue;
        const audioLatencyEl = document.querySelector('#practiceAudioLatency');
        const audioLatencyRangeEl = document.querySelector('#practiceAudioLatencyRange');
        if (audioLatencyEl) {
            audioLatencyEl.value = practiceState.audioLatencyMs;
        }
        if (audioLatencyRangeEl) {
            audioLatencyRangeEl.value = practiceState.audioLatencyMs;
        }
        if (typeof updateTimelineMetadataNode === 'function') {
            updateTimelineMetadataNode();
        }
    }
    document.querySelector('#practiceAudioLatency').addEventListener('input', function (event) {
        updatePracticeAudioLatencyControl(event.target.value);
    });
    document.querySelector('#practiceAudioLatencyRange').addEventListener('input', function (event) {
        updatePracticeAudioLatencyControl(event.target.value);
    });
    document.querySelector('#practiceH2HRestMute').addEventListener('change', function (event) {
        const nextValue = Boolean(event.target.checked);
        if (practiceState.h2hRestMute !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.h2hRestMute = nextValue;
        if (typeof updateTimelineMetadataNode === 'function') {
            updateTimelineMetadataNode();
        }
        sendPracticeAudioMessage({
            type: 'barabeat-practice-h2h-rest-mute',
            enabled: practiceState.h2hRestMute
        });
    });
    document.querySelector('#practiceAccompanimentStart').addEventListener('change', function (event) {
        const selectedStartMode = event.target.value;
        const nextValue = selectedStartMode === 'afterCall' ||
            selectedStartMode === 'afterIntro' ||
            selectedStartMode === 'afterCallIntro'
            ? selectedStartMode
            : 'immediate';
        if (practiceState.accompanimentStart !== nextValue) {
            recordArrangementHistorySnapshot();
        }
        practiceState.accompanimentStart = nextValue;
        renderPracticePanel();
        notifyPracticeSelectionChanged();
    });
    document.querySelector('#timelineRefreshButton').addEventListener('click', function () {
        try {
            const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
            syncTimelineStateFromReadResult(readResult);
            scheduleTimelineAudioRefresh(0);
        } catch (error) {
            console.error('Timeline-Refresh fehlgeschlagen', error);
            alert('Fehler beim Aktualisieren der Timeline: ' + error.message);
        }
    });
    document.querySelector('#timelineCloseButton').addEventListener('click', function () {
        timelineState.visible = false;
        clearTimelineAudioPlayer();
        renderTimelinePanel();
    });
    function getSwingProfileTitle() {
        const currentProfileKey = getCurrentTimelineSwingProfileKey();
        if (currentProfileKey === 'binaer') {
            return 'Profil 16/8';
        }
        return currentProfileKey === 'tenaer' ? 'Profil 12/8' : 'Profil 9/8';
    }
    function renderPracticeSwingProfilePreview() {
        const previewEl = document.querySelector('#practiceSwingProfilePreview');
        const titleEl = document.querySelector('#practiceSwingProfileTitle');
        const currentProfileKey = getCurrentTimelineSwingProfileKey();
        const currentProfile = normalizeTimelineSwingProfile(
            timelineState.swingProfile && timelineState.swingProfile[currentProfileKey],
            currentProfileKey
        );
        const profileTitle = getSwingProfileTitle();
        if (titleEl) {
            titleEl.textContent = 'Swing-' + profileTitle;
        }
        [
            'practiceSwingAnchor1',
            'practiceSwingAnchor2',
            'practiceSwingAnchor3',
            'practiceSwingAnchor4'
        ].forEach(function (inputId, inputIndex) {
            const inputEl = document.querySelector('#' + inputId);
            const labelEl = inputEl ? inputEl.closest('label') : null;
            if (labelEl) {
                labelEl.classList.toggle('is-hidden', inputIndex >= currentProfile.length);
            }
        });
        if (!previewEl) {
            return;
        }

        const svgNs = 'http://www.w3.org/2000/svg';
        const svgEl = document.createElementNS(svgNs, 'svg');
        const width = 620;
        const height = 150;
        const left = 58;
        const right = 562;
        const top = 46;
        const bottom = 108;
        const noteY = 78;
        const span = right - left;
        const stepWidth = span / currentProfile.length;
        svgEl.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
        previewEl.innerHTML = '';

        function addSvgElement(type, attrs) {
            const el = document.createElementNS(svgNs, type);
            Object.keys(attrs || {}).forEach(function (attrName) {
                el.setAttribute(attrName, attrs[attrName]);
            });
            svgEl.appendChild(el);
            return el;
        }

        addSvgElement('rect', {
            x: left,
            y: top,
            width: span,
            height: bottom - top,
            fill: '#fbfbfb',
            stroke: '#d0d0d0',
            'stroke-width': 1
        });
        addSvgElement('line', { x1: left, y1: noteY, x2: right, y2: noteY, stroke: '#d7d7d7', 'stroke-width': 1 });
        for (let lineIndex = 0; lineIndex <= currentProfile.length; lineIndex += 1) {
            const x = left + lineIndex * stepWidth;
            addSvgElement('line', {
                x1: x,
                y1: top,
                x2: x,
                y2: bottom,
                stroke: lineIndex === 0 || lineIndex === currentProfile.length ? '#777' : '#c8d8cf',
                'stroke-width': lineIndex === 0 || lineIndex === currentProfile.length ? 2 : 1,
                'stroke-dasharray': lineIndex === 0 || lineIndex === currentProfile.length ? '' : '4 4'
            });
        }

        currentProfile.forEach(function (profileValue, profileIndex) {
            const neutralX = left + profileIndex * stepWidth;
            const shiftedX = neutralX + ((Number(profileValue) || 0) / 100) * stepWidth;
            addSvgElement('line', {
                x1: neutralX,
                y1: profileIndex > 0 ? noteY + 11 : top - 12,
                x2: neutralX,
                y2: bottom + 12,
                stroke: profileIndex > 0 ? '#8fb39d' : '#e1e1e1',
                'stroke-width': profileIndex > 0 ? 2 : 1
            });
            addSvgElement('line', {
                x1: shiftedX,
                y1: noteY - 22,
                x2: shiftedX,
                y2: noteY + 28,
                stroke: '#9ab9a7',
                'stroke-width': 2
            });
            addSvgElement('circle', {
                cx: shiftedX,
                cy: noteY,
                r: 7,
                fill: '#111'
            });
            addSvgElement('text', {
                x: shiftedX,
                y: noteY - 30,
                'text-anchor': 'middle',
                'font-size': 12,
                fill: '#333'
            }).textContent = 'S' + (profileIndex + 1);
        });

        addSvgElement('text', {
            x: left,
            y: 28,
            'font-size': 13,
            fill: '#333'
        }).textContent = profileTitle + ' Vorschau bei Swing 100%';
        addSvgElement('text', {
            x: left,
            y: 136,
            'font-size': 12,
            fill: '#666'
        }).textContent = 'grau = Raster, schwarz = verschobene Note';

        previewEl.appendChild(svgEl);
    }
    function openPracticeSwingProfileDialog() {
        const dialogEl = document.querySelector('#practiceSwingProfileDialog');
        if (!dialogEl) {
            return;
        }
        syncTimingControlValues();
        renderPracticeSwingProfilePreview();
        dialogEl.hidden = false;
    }
    function closePracticeSwingProfileDialog() {
        const dialogEl = document.querySelector('#practiceSwingProfileDialog');
        if (dialogEl) {
            dialogEl.hidden = true;
        }
    }
    function openPracticeFeelProfileDialog() {
        const dialogEl = document.querySelector('#practiceFeelProfileDialog');
        if (!dialogEl) {
            return;
        }
        syncTimingControlValues();
        dialogEl.hidden = false;
    }
    function closePracticeFeelProfileDialog() {
        const dialogEl = document.querySelector('#practiceFeelProfileDialog');
        if (dialogEl) {
            dialogEl.hidden = true;
        }
    }
    function syncTimingControlValues() {
        ['timelineTempo', 'practiceTempo'].forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (inputEl) {
                inputEl.value = normalizeTimelineTempo(timelineState.tempo);
            }
        });
        ['timelineSwingFactor', 'practiceSwingFactor'].forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (inputEl) {
                inputEl.value = normalizeTimelineSwingFactor(timelineState.swingFactor);
            }
        });
        const currentProfileKey = getCurrentTimelineSwingProfileKey();
        const currentProfile = normalizeTimelineSwingProfile(
            timelineState.swingProfile && timelineState.swingProfile[currentProfileKey],
            currentProfileKey
        );
        [
            ['timelineSwingAnchor1', 'practiceSwingAnchor1'],
            ['timelineSwingAnchor2', 'practiceSwingAnchor2'],
            ['timelineSwingAnchor3', 'practiceSwingAnchor3'],
            ['timelineSwingAnchor4', 'practiceSwingAnchor4']
        ].forEach(function (swingConfig, inputIndex) {
            swingConfig.forEach(function (inputId) {
                const inputEl = document.querySelector('#' + inputId);
                if (inputEl && inputIndex < currentProfile.length) {
                    inputEl.value = currentProfile[inputIndex];
                }
            });
        });
        const feelOffsets = normalizeTimelineFeelOffsets(timelineState.feelOffsets);
        [
            ['timelineFeelKenkeni', 'practiceFeelKenkeni', 'Kenkeni'],
            ['timelineFeelSangban', 'practiceFeelSangban', 'Sangban'],
            ['timelineFeelDoundoun', 'practiceFeelDoundoun', 'Doundoun'],
            ['timelineFeelDreierbass', 'practiceFeelDreierbass', 'Dreierbass'],
            ['timelineFeelDjembe1', 'practiceFeelDjembe1', 'Djembe_1'],
            ['timelineFeelDjembe2', 'practiceFeelDjembe2', 'Djembe_2'],
            ['timelineFeelDjembe3', 'practiceFeelDjembe3', 'Djembe_3']
        ].forEach(function (feelConfig) {
            [feelConfig[0], feelConfig[1]].forEach(function (inputId) {
                const inputEl = document.querySelector('#' + inputId);
                if (inputEl) {
                    inputEl.value = feelOffsets[feelConfig[2]];
                }
            });
        });
    }
    window.syncTimingControlValues = syncTimingControlValues;
    function notifyTimingControlsChanged() {
        syncTimingControlValues();
        renderPracticeSwingProfilePreview();
        updateTimelineMetadataNode();
        if (isPracticeAudioModeActive() && practiceAudioPlaybackState !== 'playing') {
            schedulePracticeAudioRefresh(250);
        }
    }
    ['timelineTempo', 'practiceTempo'].forEach(function (inputId) {
        const inputEl = document.querySelector('#' + inputId);
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('input', function (event) {
            const nextValue = normalizeTimelineTempo(event.target.value);
            if (timelineState.tempo !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            timelineState.tempo = nextValue;
            notifyTimingControlsChanged();
        });
    });
    ['timelineSwingFactor', 'practiceSwingFactor'].forEach(function (inputId) {
        const inputEl = document.querySelector('#' + inputId);
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('input', function (event) {
            const nextValue = normalizeTimelineSwingFactor(event.target.value);
            if (timelineState.swingFactor !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            timelineState.swingFactor = nextValue;
            notifyTimingControlsChanged();
        });
    });
    [
        ['timelineFeelKenkeni', 'practiceFeelKenkeni', 'Kenkeni'],
        ['timelineFeelSangban', 'practiceFeelSangban', 'Sangban'],
        ['timelineFeelDoundoun', 'practiceFeelDoundoun', 'Doundoun'],
        ['timelineFeelDreierbass', 'practiceFeelDreierbass', 'Dreierbass'],
        ['timelineFeelDjembe1', 'practiceFeelDjembe1', 'Djembe_1'],
        ['timelineFeelDjembe2', 'practiceFeelDjembe2', 'Djembe_2'],
        ['timelineFeelDjembe3', 'practiceFeelDjembe3', 'Djembe_3']
    ].forEach(function (feelConfig) {
        [feelConfig[0], feelConfig[1]].forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (!inputEl) {
                return;
            }
            inputEl.addEventListener('input', function (event) {
                const nextFeelOffsets = normalizeTimelineFeelOffsets(timelineState.feelOffsets);
                const nextValue = normalizeTimelineFeelOffset(event.target.value);
                if (nextFeelOffsets[feelConfig[2]] !== nextValue) {
                    recordArrangementHistorySnapshot();
                }
                nextFeelOffsets[feelConfig[2]] = nextValue;
                timelineState.feelOffsets = nextFeelOffsets;
                notifyTimingControlsChanged();
            });
        });
    });
    [
        ['timelineSwingAnchor1', 'practiceSwingAnchor1'],
        ['timelineSwingAnchor2', 'practiceSwingAnchor2'],
        ['timelineSwingAnchor3', 'practiceSwingAnchor3'],
        ['timelineSwingAnchor4', 'practiceSwingAnchor4']
    ].forEach(function (swingConfig, inputIndex) {
        swingConfig.forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (!inputEl) {
                return;
            }
            inputEl.addEventListener('input', function (event) {
                const currentProfileKey = getCurrentTimelineSwingProfileKey();
                const nextProfiles = normalizeAllTimelineSwingProfiles(timelineState.swingProfile);
                const currentProfile = normalizeTimelineSwingProfile(nextProfiles[currentProfileKey], currentProfileKey);
                if (inputIndex >= currentProfile.length) {
                    return;
                }
                const nextValue = normalizeSwingProfileValue(event.target.value);
                if (currentProfile[inputIndex] !== nextValue) {
                    recordArrangementHistorySnapshot();
                }
                currentProfile[inputIndex] = nextValue;
                nextProfiles[currentProfileKey] = currentProfile;
                timelineState.swingProfile = nextProfiles;
                notifyTimingControlsChanged();
                if (typeof renderTimelinePanel === 'function') {
                    renderTimelinePanel();
                }
            });
        });
    });
    const timelineSwingProfileButtonEl = document.querySelector('#timelineSwingProfileButton');
    if (timelineSwingProfileButtonEl) {
        timelineSwingProfileButtonEl.addEventListener('click', openPracticeSwingProfileDialog);
    }
    document.querySelector('#practiceSwingProfileButton').addEventListener('click', openPracticeSwingProfileDialog);
    document.querySelector('#practiceSwingProfileCloseButton').addEventListener('click', closePracticeSwingProfileDialog);
    document.querySelector('#practiceSwingProfileDoneButton').addEventListener('click', closePracticeSwingProfileDialog);
    document.querySelector('#practiceSwingProfileDialog').addEventListener('click', function (event) {
        if (event.target && event.target.id === 'practiceSwingProfileDialog') {
            closePracticeSwingProfileDialog();
        }
    });
    document.querySelector('#practiceSwingProfileResetButton').addEventListener('click', function () {
        const currentProfileKey = getCurrentTimelineSwingProfileKey();
        const nextProfiles = normalizeAllTimelineSwingProfiles(timelineState.swingProfile);
        recordArrangementHistorySnapshot();
        nextProfiles[currentProfileKey] = normalizeTimelineSwingProfile(null, currentProfileKey);
        timelineState.swingProfile = nextProfiles;
        notifyTimingControlsChanged();
        if (typeof renderTimelinePanel === 'function') {
            renderTimelinePanel();
        }
    });
    const timelineFeelProfileButtonEl = document.querySelector('#timelineFeelProfileButton');
    if (timelineFeelProfileButtonEl) {
        timelineFeelProfileButtonEl.addEventListener('click', openPracticeFeelProfileDialog);
    }
    const timelineVolumeButtonEl = document.querySelector('#timelineVolumeButton');
    if (timelineVolumeButtonEl) {
        timelineVolumeButtonEl.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof openTimelineInstrumentVolumesPopover === 'function') {
                openTimelineInstrumentVolumesPopover(timelineVolumeButtonEl);
            }
        });
    }
    document.querySelector('#practiceFeelProfileButton').addEventListener('click', openPracticeFeelProfileDialog);
    document.querySelector('#practiceFeelProfileCloseButton').addEventListener('click', closePracticeFeelProfileDialog);
    document.querySelector('#practiceFeelProfileDoneButton').addEventListener('click', closePracticeFeelProfileDialog);
    document.querySelector('#practiceFeelProfileDialog').addEventListener('click', function (event) {
        if (event.target && event.target.id === 'practiceFeelProfileDialog') {
            closePracticeFeelProfileDialog();
        }
    });
    document.querySelector('#practiceFeelProfileResetButton').addEventListener('click', function () {
        recordArrangementHistorySnapshot();
        timelineState.feelOffsets = normalizeTimelineFeelOffsets(null);
        notifyTimingControlsChanged();
        if (typeof renderTimelinePanel === 'function') {
            renderTimelinePanel();
        }
    });

    const scrollButtonEl = document.querySelector('#button6');
    if (scrollButtonEl) {
        scrollButtonEl.addEventListener('click', function () {
            scrollOn = !scrollOn;
            if (scrollOn) {
                canv.attr({ fill: "none" });
            } else {
                canv.attr({ fill: "white" });
            }
        });
    }

    [
        '#openFileDialogButton',
        '#saveFileDialogButton',
        '#saveAsFileDialogButton',
        '#exportFileDialogButton',
        '#button3',
        '#button4',
        '#button5',
        '#addSheetPageButton',
        '#deleteSheetPageButton',
        '#button6',
        '#button7',
        '#button8',
        '#button9',
        '#resetPaletteButton',
        '#button10',
        '#practiceButton',
        '#button11',
        '#themeClearButton',
        '#themePlayfulButton',
        '#themeEarthButton'
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

function getLoadedSheetLineCount(data) {
    const metadataEl = data && typeof data.select === 'function'
        ? data.select(scoreMetadataSelector)
        : null;
    const metadataLineCount = metadataEl && typeof metadataEl.attr === 'function'
        ? Number(metadataEl.attr('data-line-count'))
        : NaN;
    if (Number.isFinite(metadataLineCount) && metadataLineCount > 0) {
        return normalizeSheetLineCount(metadataLineCount);
    }
    return zeilenProBlatt;
}

function onSVGLoaded(data) {
    document.body.classList.add('has-loaded-score');
    const persistedTimelineMetadata = readTimelineMetadata(data);
    zeilenAnzahl = getLoadedSheetLineCount(data);

    if (data.select("#rhythmus") == '<binaer id="rhythmus"/>') {
        viererNotenOhneStartChooser();
    } else if (data.select("#rhythmus") == '<neunaer id="rhythmus"/>') {
        neunerNotenOhneStartChooser();
    } else {
        dreierNotenOhneStartChooser();
    }
    keepPaletteInsideSheet();

    let loadedElements = data.selectAll(removableCanvasElementSelector);
    s.append(loadedElements);
    bindLoadedScoreElements();

    setRhythmTitle(loadedTitle);

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
            persistedPractice: persistedTimelineMetadata ? persistedTimelineMetadata.practice : null,
            persistedEntries: persistedEntries,
            persistedVersion: persistedTimelineMetadata ? persistedTimelineMetadata.version : null,
            persistedSourceHash: persistedTimelineMetadata ? persistedTimelineMetadata.sourceHash : ''
        });
        renderPracticePanel();
        if (practiceState.visible) {
            schedulePracticeAudioRefresh(0);
        }
    } catch (error) {
        console.warn('Timeline-Zustand konnte nach dem Laden nicht rekonstruiert werden', error);
    }
    clearHistorySnapshots();
}

function get_value(e) {
    removeCanvasElements(removableCanvasElementSelector);
    closeAppMenus();

    let selectedFileName;
    let selectedFromUrl = false;
    if (e) {
        selectedFileName = e.value || e.options[e.selectedIndex].text;
    }
    if (datei_name != "") {
        selectedFileName = datei_name;
        selectedFromUrl = true;
        datei_name = "";
    }

    if (!selectedFileName || selectedFileName === '--') {
        return;
    }

    if (getSelectedFileSource() === 'server' || selectedFromUrl) {
        serverLibrary.importScore(selectedFileName).then(function (serverScore) {
            return localLibrary.findScoreByServerPath(serverScore.serverPath).then(function (existingScore) {
                if (existingScore) {
                    return existingScore;
                }

                return localLibrary.saveScore({
                    title: serverScore.title,
                    folderId: localLibrary.rootFolderId,
                    format: serverScore.format,
                    content: serverScore.content,
                    isPublished: true,
                    serverPath: serverScore.serverPath,
                    syncState: 'published'
                });
            });
        }).then(function (savedScore) {
            loadRhythmContent(savedScore.title, savedScore.content, savedScore.id);
            setSelectedFileSource('local');
            refreshFileList();
        }).catch(function (error) {
            console.error('Serverdatei konnte nicht importiert werden', error);
            alert('Fehler beim Laden vom Server: ' + error.message);
        });
        return;
    }

    localLibrary.getScore(selectedFileName).then(function (score) {
        if (!score) {
            return;
        }
        loadRhythmContent(score.title, score.content || score.data, score.id);
    }).catch(function (error) {
        console.error('Lokale Datei konnte nicht geladen werden', error);
        alert('Fehler beim lokalen Laden: ' + error.message);
    });
}

(async function initializeInitialScore() {
    if (datei_name != "") {
        viererNoten();
        get_value();
        return;
    }
    if (await loadRememberedLastScore()) {
        return;
    }
    viererNoten();
})();

    </script>
    <br>
</body>
</html>
