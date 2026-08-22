<?php
require_once __DIR__ . '/PHP/i18n.php';
require_once __DIR__ . '/PHP/edition_config.php';

$barabeatOfflineShellBuild = defined('BARABEAT_OFFLINE_SHELL_BUILD') && BARABEAT_OFFLINE_SHELL_BUILD === true;
if ($barabeatOfflineShellBuild) {
    $_GET = [];
    $_POST = [];
    $_COOKIE[BARABEAT_LANGUAGE_COOKIE] = 'de';
} else {
    require_once __DIR__ . '/PHP/access_control.php';
    barabeat_require_access('page');
}

$jsAppBootstrap = @filemtime(__DIR__ . '/JS/app-bootstrap.js') ?: 1;
$jsI18n = @filemtime(__DIR__ . '/JS/i18n.js') ?: 1;
$jsEdition = @filemtime(__DIR__ . '/JS/edition.js') ?: 1;
$jsSnap = @filemtime(__DIR__ . '/JS/snapNEU.svg.js') ?: 1;
$jsJq = @filemtime(__DIR__ . '/JS/jquery.min.js') ?: 1;
$jsLocalLibrary = @filemtime(__DIR__ . '/JS/localLibrary.js') ?: 1;
$jsServerLibrary = @filemtime(__DIR__ . '/JS/serverLibrary.js') ?: 1;
$jsSel = @filemtime(__DIR__ . '/JS/selection_drag_7.js') ?: 1;
$jsFn = @filemtime(__DIR__ . '/JS/functions.js') ?: 1;
$jsTimeline = @filemtime(__DIR__ . '/JS/timeline.js') ?: 1;
$jsPractice = @filemtime(__DIR__ . '/JS/practice.js') ?: 1;
$jsOffline = @filemtime(__DIR__ . '/JS/offline.js') ?: 1;
$jsLegalNavigation = @filemtime(__DIR__ . '/legal/navigation.js') ?: 1;
$cssIndex = @filemtime(__DIR__ . '/CSS/index_style.css') ?: 1;
$faviconSvg = @filemtime(__DIR__ . '/Assets/favicon.svg') ?: 1;
$faviconPng = @filemtime(__DIR__ . '/Assets/favicon-32.png') ?: 1;
$appleTouchIcon = @filemtime(__DIR__ . '/apple-touch-icon.png') ?: 1;
if ($barabeatOfflineShellBuild) {
    $canManageTemporaryAccess = false;
    $temporaryAccessRemaining = 0;
    $accessCsrfToken = '';
    $activeLanguage = barabeat_language('de');
} else {
    $accessConfig = barabeat_access_config();
    $canManageTemporaryAccess = !empty($accessConfig['enabled']) && barabeat_access_is_authenticated();
    $temporaryAccessUntil = barabeat_access_window_until();
    $temporaryAccessRemaining = max(0, $temporaryAccessUntil - time());
    $accessCsrfToken = $canManageTemporaryAccess ? barabeat_access_csrf_token() : '';
    $activeLanguage = barabeat_language();
}
$offlineFallbackEditionConfig = [
    'edition' => 'demo',
    'features' => barabeat_edition_features('demo'),
    'content' => [
        'demoStartScore' => null,
        'demoArrangement' => null,
    ],
    'messages' => [
        'featureUnavailable' => barabeat_t('edition.featureUnavailable'),
    ],
    'debug' => false,
];
$offlineFallbackEditionConfigJson = json_encode(
    $offlineFallbackEditionConfig,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($activeLanguage, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#745332">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="BaraBeat">
    <title>BaraBeat-Studio</title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png?v=<?php echo $appleTouchIcon; ?>">
    <link rel="apple-touch-icon-precomposed" sizes="180x180" href="apple-touch-icon.png?v=<?php echo $appleTouchIcon; ?>">
    <link rel="icon" href="Assets/favicon.svg?v=<?php echo $faviconSvg; ?>" type="image/svg+xml">
    <link rel="icon" href="Assets/favicon-32.png?v=<?php echo $faviconPng; ?>" type="image/png" sizes="32x32">
    <script>
        window.BARABEAT_OFFLINE_BOOT = <?php echo $barabeatOfflineShellBuild ? 'true' : 'false'; ?>;
        window.BaraBeatOfflineFallbackEditionConfig = <?php echo $offlineFallbackEditionConfigJson ?: '{}'; ?>;
    </script>
    <script src="JS/app-bootstrap.js?v=<?php echo $jsAppBootstrap; ?>"></script>
    <script>window.BaraBeatI18nConfig = <?php echo barabeat_i18n_config_json(); ?>;</script>
    <script>window.BaraBeatAccessConfig = <?php echo json_encode([
        'csrfToken' => $accessCsrfToken,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
    <script src="JS/i18n.js?v=<?php echo $jsI18n; ?>"></script>
<?php if ($barabeatOfflineShellBuild): ?>
    <script>window.BaraBeatEditionConfig = window.BaraBeatAppBootstrap.getOfflineEditionConfig(window.BaraBeatOfflineFallbackEditionConfig);</script>
<?php else: ?>
    <script>
        window.BaraBeatEditionConfig = <?php echo barabeat_edition_config_json(); ?>;
        window.BaraBeatAppBootstrap.rememberEditionConfig(window.BaraBeatEditionConfig);
    </script>
<?php endif; ?>
    <script src="JS/edition.js?v=<?php echo $jsEdition; ?>"></script>
    <script src="JS/snapNEU.svg.js?v=<?php echo $jsSnap; ?>"></script>
    <script src="JS/jquery.min.js?v=<?php echo $jsJq; ?>"></script>
    <script src="JS/localLibrary.js?v=<?php echo $jsLocalLibrary; ?>"></script>
    <script src="JS/serverLibrary.js?v=<?php echo $jsServerLibrary; ?>"></script>
    <script src="JS/selection_drag_7.js?v=<?php echo $jsSel; ?>"></script>
    <script src="JS/functions.js?v=<?php echo $jsFn; ?>"></script>
    <script src="JS/timeline.js?v=<?php echo $jsTimeline; ?>"></script>
    <script src="JS/practice.js?v=<?php echo $jsPractice; ?>"></script>
    <script src="JS/offline.js?v=<?php echo $jsOffline; ?>" defer></script>
    <script src="legal/navigation.js?v=<?php echo $jsLegalNavigation; ?>" defer></script>
    <link rel="stylesheet" href="CSS/index_style.css?v=<?php echo $cssIndex; ?>">
</head>

<body class="app-body" data-offline-boot="<?php echo $barabeatOfflineShellBuild ? 'true' : 'false'; ?>">
    <?php
    $file_name = $barabeatOfflineShellBuild ? '' : ($_GET["file"] ?? "");
    echo "<script>datei_name = " . json_encode($file_name) . ";</script>";
    ?>

    <nav id="appMenuBar" aria-label="<?php echo htmlspecialchars(barabeat_t('navigation.main'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="navigation.main">
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
            <summary data-i18n="navigation.file"><?php echo htmlspecialchars(barabeat_t('navigation.file'), ENT_QUOTES, 'UTF-8'); ?></summary>
            <div class="app-menu-panel">
                <button type="button" id="openFileDialogButton" data-i18n="file.open"><?php echo htmlspecialchars(barabeat_t('file.open'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="saveFileDialogButton" data-i18n="file.save"><?php echo htmlspecialchars(barabeat_t('file.save'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="saveAsFileDialogButton" data-i18n="file.saveAs"><?php echo htmlspecialchars(barabeat_t('file.saveAs'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="exportFileDialogButton" data-i18n="file.export"><?php echo htmlspecialchars(barabeat_t('file.export'), ENT_QUOTES, 'UTF-8'); ?></button>
                <a class="app-menu-link mobile-manual-link" href="manual/offline/<?php echo htmlspecialchars($activeLanguage, ENT_QUOTES, 'UTF-8'); ?>.html" target="barabeatManual" rel="opener" data-barabeat-open-window data-barabeat-manual-link data-i18n="navigation.manual"><?php echo htmlspecialchars(barabeat_t('navigation.manual'), ENT_QUOTES, 'UTF-8'); ?></a>
                <label class="app-menu-language-control" for="languageSelect">
                    <span data-i18n="language.label"><?php echo htmlspecialchars(barabeat_t('language.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <select id="languageSelect" data-barabeat-language-select aria-label="<?php echo htmlspecialchars(barabeat_t('language.label'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="language.label">
                        <option value="de" data-i18n="language.option.de"<?php echo $activeLanguage === 'de' ? ' selected' : ''; ?>><?php echo htmlspecialchars(barabeat_t('language.option.de'), ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="en" data-i18n="language.option.en"<?php echo $activeLanguage === 'en' ? ' selected' : ''; ?>><?php echo htmlspecialchars(barabeat_t('language.option.en'), ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="fr"<?php echo $activeLanguage === 'fr' ? ' selected' : ''; ?>>Français</option>
                        <option value="es"<?php echo $activeLanguage === 'es' ? ' selected' : ''; ?>>Español</option>
                        <option value="pt"<?php echo $activeLanguage === 'pt' ? ' selected' : ''; ?>>Português</option>
                    </select>
                </label>
                <?php if ($canManageTemporaryAccess): ?>
                    <button type="button" id="temporaryAccessButton" data-online-only data-i18n="auth.temporaryOpen"><?php echo htmlspecialchars(barabeat_t('auth.temporaryOpen'), ENT_QUOTES, 'UTF-8'); ?></button>
                <?php endif; ?>
                <button type="button" id="accessLogoutButton" data-online-only data-i18n="auth.logout"><?php echo htmlspecialchars(barabeat_t('auth.logout'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </details>
        <details class="app-menu" id="scoreSheetMenu">
            <summary data-i18n="navigation.score"><?php echo htmlspecialchars(barabeat_t('navigation.score'), ENT_QUOTES, 'UTF-8'); ?></summary>
            <div class="app-menu-panel">
                <button type="button" id="button4" data-i18n="editor.binaryScore"><?php echo htmlspecialchars(barabeat_t('editor.binaryScore'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="button5" data-i18n="editor.ternaryScore"><?php echo htmlspecialchars(barabeat_t('editor.ternaryScore'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="button8" data-i18n="editor.nineEightScore"><?php echo htmlspecialchars(barabeat_t('editor.nineEightScore'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="addSheetPageButton" data-i18n="editor.addPage"><?php echo htmlspecialchars(barabeat_t('editor.addPage'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="deleteSheetPageButton" data-i18n="editor.deletePage"><?php echo htmlspecialchars(barabeat_t('editor.deletePage'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="button3" data-i18n="editor.readScore"><?php echo htmlspecialchars(barabeat_t('editor.readScore'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </details>
        <details class="app-menu">
            <summary data-i18n="navigation.insert"><?php echo htmlspecialchars(barabeat_t('navigation.insert'), ENT_QUOTES, 'UTF-8'); ?></summary>
            <div class="app-menu-panel">
                <button type="button" id="button7" data-i18n="editor.instrumentChooser"><?php echo htmlspecialchars(barabeat_t('editor.instrumentChooser'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="button9" data-i18n="editor.functionChooser"><?php echo htmlspecialchars(barabeat_t('editor.functionChooser'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="resetPaletteButton" data-i18n="editor.resetPalette"><?php echo htmlspecialchars(barabeat_t('editor.resetPalette'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </details>
        <details class="app-menu" data-mobile-practice-menu="true">
            <summary><span class="desktop-menu-label" data-i18n="navigation.tools"><?php echo htmlspecialchars(barabeat_t('navigation.tools'), ENT_QUOTES, 'UTF-8'); ?></span><span class="mobile-menu-label" data-i18n="navigation.practice"><?php echo htmlspecialchars(barabeat_t('navigation.practice'), ENT_QUOTES, 'UTF-8'); ?></span></summary>
            <div class="app-menu-panel">
                <button type="button" id="practiceButton" data-i18n="navigation.practice"><?php echo htmlspecialchars(barabeat_t('navigation.practice'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="button11" data-i18n="navigation.arrange"><?php echo htmlspecialchars(barabeat_t('navigation.arrange'), ENT_QUOTES, 'UTF-8'); ?></button>
                <a class="app-menu-link" href="manual/offline/<?php echo htmlspecialchars($activeLanguage, ENT_QUOTES, 'UTF-8'); ?>.html" target="barabeatManual" rel="opener" data-barabeat-open-window data-barabeat-manual-link data-i18n="navigation.manual"><?php echo htmlspecialchars(barabeat_t('navigation.manual'), ENT_QUOTES, 'UTF-8'); ?></a>
                <details class="app-submenu">
                    <summary data-i18n="navigation.template"><?php echo htmlspecialchars(barabeat_t('navigation.template'), ENT_QUOTES, 'UTF-8'); ?></summary>
                    <div class="app-submenu-panel">
                        <button type="button" id="themeClearButton" data-i18n="editor.theme.clear"><?php echo htmlspecialchars(barabeat_t('editor.theme.clear'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="themePlayfulButton" data-i18n="editor.theme.playful"><?php echo htmlspecialchars(barabeat_t('editor.theme.playful'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="themeEarthButton" data-i18n="editor.theme.earth"><?php echo htmlspecialchars(barabeat_t('editor.theme.earth'), ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </details>
            </div>
        </details>
        <button type="button" id="mobileArrangementPlayerButton" class="mobile-menu-action" hidden data-i18n="arrangement.mobilePlay"><?php echo htmlspecialchars(barabeat_t('arrangement.mobilePlay'), ENT_QUOTES, 'UTF-8'); ?></button>
        <button type="button" id="mobilePatternChooserButton" class="mobile-menu-action" hidden data-i18n="practice.patternSelectionOpen"><?php echo htmlspecialchars(barabeat_t('practice.patternSelectionOpen'), ENT_QUOTES, 'UTF-8'); ?></button>
        <button type="button" id="mobileBluetoothLatencyButton" class="mobile-menu-action" hidden aria-label="<?php echo htmlspecialchars(barabeat_t('practice.dialog.bluetoothLatency'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n="practice.controls.mobileLatency" data-i18n-aria-label="practice.dialog.bluetoothLatency"><?php echo htmlspecialchars(barabeat_t('practice.controls.mobileLatency'), ENT_QUOTES, 'UTF-8'); ?></button>
        <form action="" name="uploadForm" class="hidden-upload-form">
            <input type="hidden" size="40" id="iofield" name="iofield" />
        </form>
    </nav>

    <div id="offlineStatus" class="offline-status" role="status" aria-live="polite" hidden></div>

    <section id="mobileStartInfo" class="mobile-start-info" aria-live="polite">
        <h1>BaraBeat Studio</h1>
        <p data-i18n="editor.mobileStartText"><?php echo htmlspecialchars(barabeat_t('editor.mobileStartText'), ENT_QUOTES, 'UTF-8'); ?></p>
    </section>

    <section id="mobileSheetView" class="mobile-sheet-view" hidden aria-label="<?php echo htmlspecialchars(barabeat_t('editor.mobileSheetView'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="editor.mobileSheetView"></section>

    <div id="mobileArrangementOverlay" class="mobile-arrangement-overlay" hidden>
        <section class="mobile-arrangement-player" role="dialog" aria-modal="true" aria-labelledby="mobileArrangementTitle">
            <header class="mobile-arrangement-header">
                <h2 id="mobileArrangementTitle" data-i18n="arrangement.title"><?php echo htmlspecialchars(barabeat_t('arrangement.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <button type="button" id="mobileArrangementCloseButton" data-i18n="common.close"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
            </header>
            <iframe id="mobileArrangementAudioFrame" name="mobileArrangementAudioFrame" title="<?php echo htmlspecialchars(barabeat_t('arrangement.mobileAudioFrameTitle'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-title="arrangement.mobileAudioFrameTitle" allow="autoplay"></iframe>
            <footer class="app-legal-footer view-legal-footer" aria-label="Rechtliche Informationen">
                <a href="legal/offline/impressum.html" target="_blank" rel="opener" data-online-href="impressum.php">Impressum</a>
                <span aria-hidden="true">·</span>
                <a href="legal/offline/datenschutz.html" target="_blank" rel="opener" data-online-href="datenschutz.php">Datenschutz</a>
            </footer>
        </section>
    </div>

    <section id="mobileOrientationNotice" class="mobile-orientation-notice" aria-live="polite" aria-hidden="true">
        <div>
            <h1 data-i18n="editor.portraitRequired"><?php echo htmlspecialchars(barabeat_t('editor.portraitRequired'), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p data-i18n="editor.portraitRequiredText"><?php echo htmlspecialchars(barabeat_t('editor.portraitRequiredText'), ENT_QUOTES, 'UTF-8'); ?></p>
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
                <h2 id="fileDialogTitle" data-i18n="file.dialog.title"><?php echo htmlspecialchars(barabeat_t('file.dialog.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            </header>
            <div class="file-dialog-main">
                <aside class="file-dialog-sidebar" aria-label="<?php echo htmlspecialchars(barabeat_t('file.dialog.sources'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="file.dialog.sources">
                    <div class="file-dialog-sidebar-section" data-i18n="file.dialog.sources"><?php echo htmlspecialchars(barabeat_t('file.dialog.sources'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <button type="button" class="file-dialog-source is-active" data-source="local" data-i18n="common.local"><?php echo htmlspecialchars(barabeat_t('common.local'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" class="file-dialog-source" data-source="server" data-online-only data-i18n="common.server"><?php echo htmlspecialchars(barabeat_t('common.server'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <div class="file-dialog-sidebar-section" data-i18n="file.dialog.collections"><?php echo htmlspecialchars(barabeat_t('file.dialog.collections'), ENT_QUOTES, 'UTF-8'); ?></div>
                    <button type="button" class="file-dialog-filter is-active" data-filter="all" data-i18n="file.dialog.filterAll"><?php echo htmlspecialchars(barabeat_t('file.dialog.filterAll'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" class="file-dialog-filter" data-filter="published" data-i18n="file.dialog.filterPublished"><?php echo htmlspecialchars(barabeat_t('file.dialog.filterPublished'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" class="file-dialog-filter" data-filter="local-only" data-i18n="file.dialog.filterLocalOnly"><?php echo htmlspecialchars(barabeat_t('file.dialog.filterLocalOnly'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" class="file-dialog-filter" data-filter="modified" data-i18n="file.dialog.filterModified"><?php echo htmlspecialchars(barabeat_t('file.dialog.filterModified'), ENT_QUOTES, 'UTF-8'); ?></button>
                </aside>
                <div class="file-dialog-content">
                    <div class="file-dialog-fields">
                        <label for="fileDialogName"><span data-i18n="common.name"><?php echo htmlspecialchars(barabeat_t('common.name'), ENT_QUOTES, 'UTF-8'); ?></span>:</label>
                        <input type="text" id="fileDialogName" autocomplete="off" />
                        <label for="fileDialogTags"><span data-i18n="file.dialog.tags"><?php echo htmlspecialchars(barabeat_t('file.dialog.tags'), ENT_QUOTES, 'UTF-8'); ?></span>:</label>
                        <input type="text" id="fileDialogTags" autocomplete="off" />
                    </div>
                    <div class="file-dialog-toolbar">
                        <button type="button" id="fileDialogRefreshButton" title="<?php echo htmlspecialchars(barabeat_t('common.refresh'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-title="common.refresh">↻</button>
                        <span id="fileDialogFolderName" class="file-dialog-folder-name" data-i18n="common.local"><?php echo htmlspecialchars(barabeat_t('common.local'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="search" id="fileDialogSearch" placeholder="<?php echo htmlspecialchars(barabeat_t('common.search'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-placeholder="common.search" />
                    </div>
                    <div id="fileDialogServerNoticeSlot" class="file-dialog-server-notice-slot">
                        <div id="fileDialogServerNotice" class="file-dialog-server-notice" hidden>
                            <span></span>
                            <button type="button" data-online-only data-i18n="file.dialog.serverVersionLoad"><?php echo htmlspecialchars(barabeat_t('file.dialog.serverVersionLoad'), ENT_QUOTES, 'UTF-8'); ?></button>
                        </div>
                    </div>
                    <div class="file-dialog-table-wrap">
                        <table class="file-dialog-table">
                            <thead>
                                <tr>
                                    <th data-i18n="common.name"><?php echo htmlspecialchars(barabeat_t('common.name'), ENT_QUOTES, 'UTF-8'); ?></th>
                                    <th data-i18n="common.status"><?php echo htmlspecialchars(barabeat_t('common.status'), ENT_QUOTES, 'UTF-8'); ?></th>
                                    <th data-i18n="common.modified"><?php echo htmlspecialchars(barabeat_t('common.modified'), ENT_QUOTES, 'UTF-8'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="fileDialogList"></tbody>
                        </table>
                        <div id="fileDialogEmpty" class="file-dialog-empty" hidden data-i18n="file.dialog.empty"><?php echo htmlspecialchars(barabeat_t('file.dialog.empty'), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>
            <footer class="file-dialog-footer">
                <div class="file-dialog-left-actions">
                    <button type="button" id="fileDialogNewFolderButton" data-i18n="file.dialog.newFolder"><?php echo htmlspecialchars(barabeat_t('file.dialog.newFolder'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="fileDialogRenameButton" data-i18n="common.rename"><?php echo htmlspecialchars(barabeat_t('common.rename'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="fileDialogDeleteButton" data-i18n="common.delete"><?php echo htmlspecialchars(barabeat_t('common.delete'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="fileDialogUnpublishButton" data-online-only data-i18n="file.dialog.deletePublication"><?php echo htmlspecialchars(barabeat_t('file.dialog.deletePublication'), ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
                <label class="file-dialog-format" for="fileDialogFormat">
                    <span data-i18n="common.format"><?php echo htmlspecialchars(barabeat_t('common.format'), ENT_QUOTES, 'UTF-8'); ?></span>:
                    <select id="fileDialogFormat">
                        <option value="svg">SVG</option>
                        <option value="pdf">PDF</option>
                    </select>
                </label>
                <div class="file-dialog-actions">
                    <button type="button" id="fileDialogCancelButton" data-i18n="common.cancel"><?php echo htmlspecialchars(barabeat_t('common.cancel'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="fileDialogConfirmButton" class="primary" data-i18n="common.open"><?php echo htmlspecialchars(barabeat_t('common.open'), ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </footer>
        </section>
    </div>

    <div id="sheetQuickPlayControls" class="sheet-quick-play-controls" aria-label="<?php echo htmlspecialchars(barabeat_t('editor.quickPlay.aria'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="editor.quickPlay.aria">
        <label for="sheetQuickPlayTempo" data-i18n="editor.quickPlay.tempo"><?php echo htmlspecialchars(barabeat_t('editor.quickPlay.tempo'), ENT_QUOTES, 'UTF-8'); ?></label>
        <input type="number" id="sheetQuickPlayTempo" min="30" max="180" step="1" value="100" />
        <button type="button" id="sheetQuickPlayButton" aria-pressed="false" title="<?php echo htmlspecialchars(barabeat_t('editor.quickPlay.playSelected'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-title="editor.quickPlay.playSelected">▶</button>
    </div>
    <iframe id="sheetQuickPlayFrame" name="sheetQuickPlayFrame" class="sheet-quick-play-frame" title="<?php echo htmlspecialchars(barabeat_t('editor.quickPlay.frameTitle'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-title="editor.quickPlay.frameTitle" allow="autoplay"></iframe>

    <div id="timelinePanel" hidden>
        <div class="timeline-sticky-region">
            <div class="timeline-panel-header">
                <div>
                    <div id="timelineTitle" class="timeline-panel-title" data-i18n="arrangement.title"><?php echo htmlspecialchars(barabeat_t('arrangement.title'), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div class="timeline-panel-actions">
                    <label class="timeline-tempo-control" for="timelineTempo">
                        <span data-i18n="arrangement.tempo"><?php echo htmlspecialchars(barabeat_t('arrangement.tempo'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <input type="number" id="timelineTempo" min="30" max="180" step="1" value="100" />
                    </label>
                    <button type="button" id="timelineSwingProfileButton" data-i18n="arrangement.swingProfile"><?php echo htmlspecialchars(barabeat_t('arrangement.swingProfile'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="timelineFeelProfileButton" data-i18n="arrangement.feel"><?php echo htmlspecialchars(barabeat_t('arrangement.feel'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="timelineVolumeButton" data-i18n="arrangement.volume"><?php echo htmlspecialchars(barabeat_t('arrangement.volume'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="timelineShekereBeat" class="shekere-beat-toggle" aria-pressed="false" data-i18n="arrangement.shekereBeat"><?php echo htmlspecialchars(barabeat_t('arrangement.shekereBeat'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="timelineRefreshButton" data-i18n="arrangement.refreshFromScore"><?php echo htmlspecialchars(barabeat_t('arrangement.refreshFromScore'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <button type="button" id="timelineCloseButton" data-i18n="common.close"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
            </div>
            <section class="timeline-player-panel" hidden>
                <iframe id="timelineAudioFrame" name="timelineAudioFrame" title="<?php echo htmlspecialchars(barabeat_t('arrangement.audioFrameTitle'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-title="arrangement.audioFrameTitle" allow="autoplay"></iframe>
            </section>
        </div>
        <div class="timeline-panel-body">
            <section class="timeline-column">
                <h3 data-i18n="arrangement.patternLibrary"><?php echo htmlspecialchars(barabeat_t('arrangement.patternLibrary'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="timeline-column-note" data-i18n="arrangement.patternLibraryNote"><?php echo htmlspecialchars(barabeat_t('arrangement.patternLibraryNote'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div id="timelinePatternList" class="timeline-pattern-list"></div>
            </section>
            <section class="timeline-column">
                <h3 data-i18n="arrangement.timelineTitle"><?php echo htmlspecialchars(barabeat_t('arrangement.timelineTitle'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="timeline-column-note" data-i18n="arrangement.timelineNote"><?php echo htmlspecialchars(barabeat_t('arrangement.timelineNote'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div id="timelineSequence" class="timeline-sequence-list"></div>
            </section>
        </div>
        <footer class="app-legal-footer view-legal-footer" aria-label="Rechtliche Informationen">
            <a href="legal/offline/impressum.html" target="_blank" rel="opener" data-online-href="impressum.php">Impressum</a>
            <span aria-hidden="true">·</span>
            <a href="legal/offline/datenschutz.html" target="_blank" rel="opener" data-online-href="datenschutz.php">Datenschutz</a>
        </footer>
    </div>

    <div id="practicePanel" hidden>
        <div class="timeline-panel-header practice-panel-header">
            <div class="practice-header-main">
                <div id="practiceTitle" class="timeline-panel-title practice-panel-title" data-i18n="practice.title"><?php echo htmlspecialchars(barabeat_t('practice.title'), ENT_QUOTES, 'UTF-8'); ?></div>
                <label class="practice-header-scenario" for="practiceScenarioHeaderSelect">
                    <span data-i18n="practice.scenario.label"><?php echo htmlspecialchars(barabeat_t('practice.scenario.label'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <select id="practiceScenarioHeaderSelect">
                        <option value="" data-i18n="practice.scenario.current"><?php echo htmlspecialchars(barabeat_t('practice.scenario.current'), ENT_QUOTES, 'UTF-8'); ?></option>
                    </select>
                </label>
            </div>
            <div class="timeline-panel-actions">
                <button type="button" id="practicePatternChooserToggle" aria-expanded="false" aria-controls="practicePatternChooser">
                    <?php echo htmlspecialchars(barabeat_t('practice.patternSelectionOpen'), ENT_QUOTES, 'UTF-8'); ?>
                </button>
                <button type="button" id="practiceCloseButton" data-i18n="common.close"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
        <div id="practicePatternChooser" class="timeline-panel-body practice-panel-body" hidden>
            <section class="practice-settings-column is-collapsed">
                <h3 class="practice-column-heading practice-options-title">
                    <button type="button" class="practice-column-toggle" aria-expanded="false" aria-controls="practiceSettingsContent">
                        <span data-i18n="practice.settings"><?php echo htmlspecialchars(barabeat_t('practice.settings'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                </h3>
                <div id="practiceSettingsContent" class="practice-column-content">
                    <div class="practice-scenario-options">
                        <label class="timeline-tempo-control" for="practiceScenarioSelect">
                            <span data-i18n="practice.scenario.practiceLabel"><?php echo htmlspecialchars(barabeat_t('practice.scenario.practiceLabel'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <select id="practiceScenarioSelect">
                                <option value="" data-i18n="practice.scenario.current"><?php echo htmlspecialchars(barabeat_t('practice.scenario.current'), ENT_QUOTES, 'UTF-8'); ?></option>
                            </select>
                        </label>
                        <button type="button" id="practiceScenarioSaveButton" data-i18n="practice.scenario.save"><?php echo htmlspecialchars(barabeat_t('practice.scenario.save'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="practiceScenarioNewButton" data-i18n="practice.scenario.new"><?php echo htmlspecialchars(barabeat_t('practice.scenario.new'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="practiceScenarioDeleteButton" data-i18n="practice.scenario.delete"><?php echo htmlspecialchars(barabeat_t('practice.scenario.delete'), ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                    <div class="practice-timing-options">
                        <label class="timeline-tempo-control" for="practiceTempo">
                            <span data-i18n="practice.controls.tempo"><?php echo htmlspecialchars(barabeat_t('practice.controls.tempo'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <input type="number" id="practiceTempo" min="30" max="180" step="1" value="100" />
                        </label>
                        <button type="button" id="practiceSwingProfileButton" data-i18n="practice.controls.swingProfile"><?php echo htmlspecialchars(barabeat_t('practice.controls.swingProfile'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="practiceFeelProfileButton" data-i18n="practice.controls.feel"><?php echo htmlspecialchars(barabeat_t('practice.controls.feel'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="practiceTempoRampButton" data-i18n="practice.controls.tempoRamp"><?php echo htmlspecialchars(barabeat_t('practice.controls.tempoRamp'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="practiceVolumeButton" data-i18n="practice.controls.volume"><?php echo htmlspecialchars(barabeat_t('practice.controls.volume'), ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" id="practiceShekereBeat" class="shekere-beat-toggle" aria-pressed="false" data-i18n="practice.controls.shekereBeat"><?php echo htmlspecialchars(barabeat_t('practice.controls.shekereBeat'), ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                    <div class="practice-pattern-options">
                        <label class="timeline-tempo-control" for="practiceAccompanimentStart">
                            <span data-i18n="practice.controls.accompanimentStarts"><?php echo htmlspecialchars(barabeat_t('practice.controls.accompanimentStarts'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <select id="practiceAccompanimentStart">
                                <option value="immediate" data-i18n="practice.controls.startImmediate"><?php echo htmlspecialchars(barabeat_t('practice.controls.startImmediate'), ENT_QUOTES, 'UTF-8'); ?></option>
                                <option value="afterCall" data-i18n="practice.controls.startAfterCall"><?php echo htmlspecialchars(barabeat_t('practice.controls.startAfterCall'), ENT_QUOTES, 'UTF-8'); ?></option>
                                <option value="afterIntro" data-i18n="practice.controls.startAfterIntro"><?php echo htmlspecialchars(barabeat_t('practice.controls.startAfterIntro'), ENT_QUOTES, 'UTF-8'); ?></option>
                                <option value="afterCallIntro" data-i18n="practice.controls.startAfterCallIntro"><?php echo htmlspecialchars(barabeat_t('practice.controls.startAfterCallIntro'), ENT_QUOTES, 'UTF-8'); ?></option>
                            </select>
                        </label>
                        <label class="timeline-tempo-control" for="practiceWithoutSoloLoops">
                            <span data-i18n="practice.controls.withoutPracticePart"><?php echo htmlspecialchars(barabeat_t('practice.controls.withoutPracticePart'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="practice-stepper">
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithoutSoloLoops" data-practice-step-delta="-1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.decreaseWithoutPracticePart'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.decreaseWithoutPracticePart">-</button>
                                <input type="number" id="practiceWithoutSoloLoops" min="0" max="32" step="1" value="1" />
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithoutSoloLoops" data-practice-step-delta="1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.increaseWithoutPracticePart'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.increaseWithoutPracticePart">+</button>
                            </span>
                        </label>
                        <label class="timeline-tempo-control" for="practiceWithSoloLoops">
                            <span data-i18n="practice.controls.withPracticePart"><?php echo htmlspecialchars(barabeat_t('practice.controls.withPracticePart'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="practice-stepper">
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithSoloLoops" data-practice-step-delta="-1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.decreaseWithPracticePart'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.decreaseWithPracticePart">-</button>
                                <input type="number" id="practiceWithSoloLoops" min="1" max="32" step="1" value="1" />
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceWithSoloLoops" data-practice-step-delta="1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.increaseWithPracticePart'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.increaseWithPracticePart">+</button>
                            </span>
                        </label>
                        <label class="timeline-tempo-control" for="practiceAccompanimentBetweenPatterns">
                            <span data-i18n="practice.controls.betweenPracticeParts"><?php echo htmlspecialchars(barabeat_t('practice.controls.betweenPracticeParts'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <input type="checkbox" id="practiceAccompanimentBetweenPatterns" />
                        </label>
                        <label class="timeline-tempo-control" for="practicePauseAccompanimentForLeadInPatterns">
                            <span data-i18n="practice.controls.stopAccompanimentAtCallIntro"><?php echo htmlspecialchars(barabeat_t('practice.controls.stopAccompanimentAtCallIntro'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <input type="checkbox" id="practicePauseAccompanimentForLeadInPatterns" />
                        </label>
                        <label class="timeline-tempo-control" for="practiceRepeatCount" id="practiceRepeatCountControl">
                            <span data-i18n="practice.controls.repeat"><?php echo htmlspecialchars(barabeat_t('practice.controls.repeat'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="practice-stepper">
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceRepeatCount" data-practice-step-delta="-1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.decreaseRepeats'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.decreaseRepeats">-</button>
                                <input type="number" id="practiceRepeatCount" min="1" max="999" step="1" value="4" />
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceRepeatCount" data-practice-step-delta="1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.increaseRepeats'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.increaseRepeats">+</button>
                            </span>
                        </label>
                        <label class="timeline-tempo-control" for="practiceTimerMinutes">
                            <span data-i18n="practice.controls.timerMinutes"><?php echo htmlspecialchars(barabeat_t('practice.controls.timerMinutes'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="practice-stepper">
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceTimerMinutes" data-practice-step-delta="-1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.decreaseTimer'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.decreaseTimer">-</button>
                                <input type="number" id="practiceTimerMinutes" min="0" max="240" step="1" value="0" />
                                <button type="button" class="practice-stepper-button" data-practice-step-target="practiceTimerMinutes" data-practice-step-delta="1" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.controls.increaseTimer'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-aria-label="practice.controls.increaseTimer">+</button>
                            </span>
                        </label>
                        <label class="timeline-tempo-control" for="practiceAudioLatency">
                            <span data-i18n="practice.controls.bluetoothLatencyMs"><?php echo htmlspecialchars(barabeat_t('practice.controls.bluetoothLatencyMs'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <input type="range" id="practiceAudioLatencyRange" min="0" max="1000" step="10" value="30" />
                            <input type="number" id="practiceAudioLatency" min="0" max="1000" step="10" value="30" />
                        </label>
                        <label class="timeline-tempo-control" for="practiceH2HRestMute">
                            <span data-i18n="practice.controls.h2hRestMute"><?php echo htmlspecialchars(barabeat_t('practice.controls.h2hRestMute'), ENT_QUOTES, 'UTF-8'); ?></span>
                            <input type="checkbox" id="practiceH2HRestMute" />
                        </label>
                        <button type="button" id="practiceRefreshButton" data-i18n="practice.controls.refreshFromScore"><?php echo htmlspecialchars(barabeat_t('practice.controls.refreshFromScore'), ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </div>
            </section>
            <section class="timeline-column practice-column practice-accompaniment-column is-collapsed">
                <h3 class="practice-column-heading">
                    <button type="button" class="practice-column-toggle" aria-expanded="false" aria-controls="practiceAccompanimentListWrap">
                        <span data-i18n="practice.selection.accompaniment"><?php echo htmlspecialchars(barabeat_t('practice.selection.accompaniment'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                </h3>
                <div id="practiceAccompanimentListWrap" class="practice-column-content">
                    <p class="timeline-column-note" data-i18n="practice.selection.accompanimentNote"><?php echo htmlspecialchars(barabeat_t('practice.selection.accompanimentNote'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <div id="practiceAccompanimentList" class="timeline-pattern-list"></div>
                </div>
            </section>
            <section class="timeline-column practice-column practice-solo-column is-collapsed">
                <h3 class="practice-column-heading">
                    <button type="button" class="practice-column-toggle" aria-expanded="false" aria-controls="practiceSoloListWrap">
                        <span data-i18n="practice.selection.practiceParts"><?php echo htmlspecialchars(barabeat_t('practice.selection.practiceParts'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                </h3>
                <div id="practiceSoloListWrap" class="practice-column-content">
                    <p class="timeline-column-note" data-i18n="practice.selection.practicePartsNote"><?php echo htmlspecialchars(barabeat_t('practice.selection.practicePartsNote'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <div id="practiceSoloList" class="timeline-pattern-list"></div>
                </div>
            </section>
        </div>
        <section class="practice-player-panel">
            <iframe id="practiceAudioFrame" name="practiceAudioFrame" title="<?php echo htmlspecialchars(barabeat_t('practice.audioFrameTitle'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n-title="practice.audioFrameTitle" allow="autoplay"></iframe>
            <div id="practiceScroller" class="practice-scroller" hidden>
                <div class="practice-scroller-head">
                    <strong data-i18n="practice.runningNotes"><?php echo htmlspecialchars(barabeat_t('practice.runningNotes'), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <span id="practiceScrollerStatus" data-i18n="practice.ready"><?php echo htmlspecialchars(barabeat_t('practice.ready'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="practice-scroller-stage">
                    <div class="practice-scroller-playhead" aria-hidden="true"></div>
                    <div id="practiceScrollerRows" class="practice-scroller-rows"></div>
                </div>
            </div>
        </section>
        <footer class="app-legal-footer view-legal-footer" aria-label="Rechtliche Informationen">
            <a href="legal/offline/impressum.html" target="_blank" rel="opener" data-online-href="impressum.php">Impressum</a>
            <span aria-hidden="true">·</span>
            <a href="legal/offline/datenschutz.html" target="_blank" rel="opener" data-online-href="datenschutz.php">Datenschutz</a>
        </footer>
    </div>

    <div id="practiceSwingProfileDialog" class="swing-profile-dialog-backdrop" hidden>
        <section class="swing-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="practiceSwingProfileTitle">
            <header class="swing-profile-dialog-header">
                <h2 id="practiceSwingProfileTitle" data-i18n="practice.controls.swingProfile"><?php echo htmlspecialchars(barabeat_t('practice.controls.swingProfile'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <button type="button" id="practiceSwingProfileCloseButton" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.dialog.closeSwingProfile'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n="common.close" data-i18n-aria-label="practice.dialog.closeSwingProfile"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
            </header>
            <div class="swing-profile-preview" id="practiceSwingProfilePreview" aria-hidden="true"></div>
            <div class="swing-profile-controls" id="practiceSwingProfileControls">
                <label>S1 <input type="number" id="practiceSwingAnchor1" min="-50" max="50" step="1" value="0" /></label>
                <label>S2 <input type="number" id="practiceSwingAnchor2" min="-50" max="50" step="1" value="0" /></label>
                <label>S3 <input type="number" id="practiceSwingAnchor3" min="-50" max="50" step="1" value="0" /></label>
                <label>S4 <input type="number" id="practiceSwingAnchor4" min="-50" max="50" step="1" value="0" /></label>
            </div>
            <footer class="swing-profile-dialog-footer">
                <button type="button" id="practiceSwingProfileResetButton" data-i18n="common.reset"><?php echo htmlspecialchars(barabeat_t('common.reset'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="practiceSwingProfileDoneButton" class="primary" data-i18n="common.done"><?php echo htmlspecialchars(barabeat_t('common.done'), ENT_QUOTES, 'UTF-8'); ?></button>
            </footer>
        </section>
    </div>

    <div id="practiceFeelProfileDialog" class="swing-profile-dialog-backdrop" hidden>
        <section class="swing-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="practiceFeelProfileTitle">
            <header class="swing-profile-dialog-header">
                <h2 id="practiceFeelProfileTitle" data-i18n="practice.dialog.feelTitle"><?php echo htmlspecialchars(barabeat_t('practice.dialog.feelTitle'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <button type="button" id="practiceFeelProfileCloseButton" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.dialog.closeFeel'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n="common.close" data-i18n-aria-label="practice.dialog.closeFeel"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
            </header>
            <div class="swing-profile-controls feel-profile-controls" id="practiceFeelProfileControls">
                <label><span data-i18n="practice.instrument.kenkeni"><?php echo htmlspecialchars(barabeat_t('practice.instrument.kenkeni'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelKenkeni" step="1" value="0" /></label>
                <label><span data-i18n="practice.instrument.sangban"><?php echo htmlspecialchars(barabeat_t('practice.instrument.sangban'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelSangban" step="1" value="0" /></label>
                <label><span data-i18n="practice.instrument.doundoun"><?php echo htmlspecialchars(barabeat_t('practice.instrument.doundoun'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelDoundoun" step="1" value="0" /></label>
                <label><span data-i18n="practice.instrument.threeBass"><?php echo htmlspecialchars(barabeat_t('practice.instrument.threeBass'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelDreierbass" step="1" value="0" /></label>
                <label><span data-i18n="practice.instrument.djembe1"><?php echo htmlspecialchars(barabeat_t('practice.instrument.djembe1'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelDjembe1" step="1" value="0" /></label>
                <label><span data-i18n="practice.instrument.djembe2"><?php echo htmlspecialchars(barabeat_t('practice.instrument.djembe2'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelDjembe2" step="1" value="0" /></label>
                <label><span data-i18n="practice.instrument.djembe3"><?php echo htmlspecialchars(barabeat_t('practice.instrument.djembe3'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceFeelDjembe3" step="1" value="0" /></label>
            </div>
            <footer class="swing-profile-dialog-footer">
                <button type="button" id="practiceFeelProfileResetButton" data-i18n="common.reset"><?php echo htmlspecialchars(barabeat_t('common.reset'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="practiceFeelProfileDoneButton" class="primary" data-i18n="common.done"><?php echo htmlspecialchars(barabeat_t('common.done'), ENT_QUOTES, 'UTF-8'); ?></button>
            </footer>
        </section>
    </div>

    <div id="practiceTempoRampDialog" class="swing-profile-dialog-backdrop" hidden>
        <section class="swing-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="practiceTempoRampTitle">
            <header class="swing-profile-dialog-header">
                <h2 id="practiceTempoRampTitle" data-i18n="practice.controls.tempoRamp"><?php echo htmlspecialchars(barabeat_t('practice.controls.tempoRamp'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <button type="button" id="practiceTempoRampCloseButton" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.dialog.closeTempoRamp'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n="common.close" data-i18n-aria-label="practice.dialog.closeTempoRamp"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
            </header>
            <div class="swing-profile-controls practice-tempo-ramp-controls">
                <label class="practice-tempo-ramp-enabled">
                    <span data-i18n="practice.tempoRamp.enabled"><?php echo htmlspecialchars(barabeat_t('practice.tempoRamp.enabled'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <input type="checkbox" id="practiceTempoRampEnabled" />
                </label>
                <label><span data-i18n="practice.tempoRamp.startTempo"><?php echo htmlspecialchars(barabeat_t('practice.tempoRamp.startTempo'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceTempoRampStart" min="30" max="180" step="1" value="80" /></label>
                <label><span data-i18n="practice.tempoRamp.endTempo"><?php echo htmlspecialchars(barabeat_t('practice.tempoRamp.endTempo'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceTempoRampEnd" min="30" max="180" step="1" value="100" /></label>
                <label><span data-i18n="practice.tempoRamp.everyRepeats"><?php echo htmlspecialchars(barabeat_t('practice.tempoRamp.everyRepeats'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceTempoRampEvery" min="1" max="64" step="1" value="2" /></label>
                <label><span data-i18n="practice.tempoRamp.increaseBpm"><?php echo htmlspecialchars(barabeat_t('practice.tempoRamp.increaseBpm'), ENT_QUOTES, 'UTF-8'); ?></span> <input type="number" id="practiceTempoRampStep" min="1" max="30" step="1" value="5" /></label>
            </div>
            <p class="practice-tempo-ramp-note" data-i18n="practice.tempoRamp.note"><?php echo htmlspecialchars(barabeat_t('practice.tempoRamp.note'), ENT_QUOTES, 'UTF-8'); ?></p>
            <footer class="swing-profile-dialog-footer">
                <button type="button" id="practiceTempoRampResetButton" data-i18n="common.reset"><?php echo htmlspecialchars(barabeat_t('common.reset'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="practiceTempoRampDoneButton" class="primary" data-i18n="common.done"><?php echo htmlspecialchars(barabeat_t('common.done'), ENT_QUOTES, 'UTF-8'); ?></button>
            </footer>
        </section>
    </div>

    <div id="practiceBluetoothLatencyDialog" class="swing-profile-dialog-backdrop" hidden>
        <section class="swing-profile-dialog practice-bluetooth-latency-dialog" role="dialog" aria-modal="true" aria-labelledby="practiceBluetoothLatencyTitle">
            <header class="swing-profile-dialog-header">
                <h2 id="practiceBluetoothLatencyTitle" data-i18n="practice.dialog.bluetoothLatency"><?php echo htmlspecialchars(barabeat_t('practice.dialog.bluetoothLatency'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <button type="button" id="practiceBluetoothLatencyCloseButton" aria-label="<?php echo htmlspecialchars(barabeat_t('practice.dialog.closeBluetoothLatency'), ENT_QUOTES, 'UTF-8'); ?>" data-i18n="common.close" data-i18n-aria-label="practice.dialog.closeBluetoothLatency"><?php echo htmlspecialchars(barabeat_t('common.close'), ENT_QUOTES, 'UTF-8'); ?></button>
            </header>
            <div class="practice-bluetooth-latency-controls">
                <label for="mobilePracticeAudioLatencyRange" data-i18n="practice.dialog.latencyMilliseconds"><?php echo htmlspecialchars(barabeat_t('practice.dialog.latencyMilliseconds'), ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="range" id="mobilePracticeAudioLatencyRange" min="0" max="1000" step="10" value="30" />
                <input type="number" id="mobilePracticeAudioLatency" min="0" max="1000" step="10" value="30" inputmode="numeric" />
            </div>
            <footer class="swing-profile-dialog-footer">
                <button type="button" id="practiceBluetoothLatencyDoneButton" class="primary" data-i18n="common.done"><?php echo htmlspecialchars(barabeat_t('common.done'), ENT_QUOTES, 'UTF-8'); ?></button>
            </footer>
        </section>
    </div>

    <div id="tupletDialog" class="tuplet-dialog-backdrop" hidden>
        <section class="tuplet-dialog" role="dialog" aria-modal="true" aria-labelledby="tupletDialogTitle">
            <header class="tuplet-dialog-header">
                <h2 id="tupletDialogTitle" data-i18n="score.tuplet.triplet"><?php echo htmlspecialchars(barabeat_t('score.tuplet.triplet'), ENT_QUOTES, 'UTF-8'); ?></h2>
            </header>
            <div id="tupletDialogControls" class="tuplet-dialog-controls"></div>
            <footer class="tuplet-dialog-footer">
                <button type="button" id="tupletDialogCancelButton" data-i18n="common.cancel"><?php echo htmlspecialchars(barabeat_t('common.cancel'), ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" id="tupletDialogInsertButton" class="primary" data-i18n="common.insert"><?php echo htmlspecialchars(barabeat_t('common.insert'), ENT_QUOTES, 'UTF-8'); ?></button>
            </footer>
        </section>
    </div>

<script>
// Bearbeitungsfunktionen
var edit_title, edit_text;

function uiText(key, values) {
    return window.BaraBeatI18n.t(key, values);
}

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
var ton, bass, slap, flam_ton, flam_slap, flam_bass_slap, ton_g, slap_g, In, Out, ShortBar, Triplet, text_z_g, repeatMarkerGroup;

// Geklonte Paletten-Elemente
var ton_c, bass_c, slap_c, flam_ton_c, flam_slap_c, flam_bass_slap_c, ton_g_c, slap_g_c, In_c, Out_c, ShortBar_c, Triplet_c, repeatMarkerLegendClone;

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
    insertTripletMarker,
    captureTextTouchStart,
    handleTextTouchEnd,
    insertTextField,
    cycleRepeatCount,
    insertRepeatMarker;

const canvasElementSelector = "#edit, #tone, #bass, #slap, #tone_muffled, #slap_muffled, #tone_flam, #slap_flam, #bass_slap_flam, #in, #out, #shortbar, #triplet, #quartuplet, #edit_text, #wiederholung";
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
const checkTextFileEndpoint = "dateivorhanden.php";
const isOfflineColdStart = window.BARABEAT_OFFLINE_BOOT === true;
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
    selectedId: null,
    serverNoticeRequest: 0,
    serverNoticePath: ''
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
const defaultRhythmTitle = uiText('editor.rhythmName');
const legacyDefaultRhythmTitles = ['Rhythmusname', 'Rhythm name', 'Enter the name of the Rhythm'];

function isDefaultTitleText(titleValue) {
    const normalizedTitle = String(titleValue || '').trim();
    return !normalizedTitle ||
        normalizedTitle === defaultRhythmTitle ||
        legacyDefaultRhythmTitles.indexOf(normalizedTitle) !== -1;
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
    editorEl.setAttribute('aria-label', uiText('editor.rhythmName'));
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
    const text_i = prompt(uiText('editor.editTextPrompt'), text_a);
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
    if (isOfflineColdStart) {
        console.warn('PHP-Aufruf im Offline-Kaltstart unterbunden:', endpoint);
        alert(uiText('offline.onlineOnly'));
        return null;
    }
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
    const normalizedSource = isOfflineColdStart && source === 'server' ? 'local' : source;
    const sourceEl = document.querySelector('#fileSource');
    if (sourceEl) {
        sourceEl.value = normalizedSource;
    }
    currentFileSource = normalizedSource;
    fileDialogState.source = normalizedSource;
    document.querySelectorAll('.file-dialog-source').forEach(function (buttonEl) {
        buttonEl.classList.toggle('is-active', buttonEl.dataset.source === normalizedSource);
    });
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
}

function buildLocalFileListMarkup(scores) {
    let markup = '<select id="dateiname" onchange="get_value(this)">';
    markup += '<option value="">' + escapeHtml(uiText('file.dialog.localLoad')) + '</option>';
    scores.forEach(function (score) {
        const statusLabel = score.syncState === 'modified-local'
            ? ' *'
            : score.isPublished
                ? ' ' + uiText('file.status.published')
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
    markup += '<option value="">' + escapeHtml(uiText('file.dialog.serverLoad')) + '</option>';
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
        updateSelectionMarkup('<select id="dateiname"><option>' +
            escapeHtml(uiText('file.error.listLoad', { message: error.message || '' })) +
            '</option></select>');
    }
}

function getScoreStatusLabel(score) {
    if (!score) {
        return '';
    }
    if (score.serverUpdateAvailable) {
        return score.syncState === 'modified-local'
            ? uiText('file.status.conflict')
            : uiText('file.status.serverChanged');
    }
    if (score.syncState === 'modified-local') {
        return uiText('file.status.modified');
    }
    if (score.isPublished) {
        return uiText('file.status.published');
    }
    return uiText('file.status.local');
}

function normalizeScoreContentForCompare(content) {
    return String(content || '').replace(/\r\n?/g, '\n');
}

function normalizeServerModifiedTs(value) {
    const numericValue = Number(value);
    return Number.isFinite(numericValue) && numericValue > 0 ? numericValue : 0;
}

function getScoreServerModifiedTs(score) {
    return normalizeServerModifiedTs(score && (score.serverModifiedTs || score.serverVersionTs));
}

function getServerInfoModifiedTs(serverInfo) {
    return normalizeServerModifiedTs(serverInfo && serverInfo.serverModifiedTs);
}

function isServerInfoNewerThanLocal(serverInfo, localScore) {
    const serverModifiedTs = getServerInfoModifiedTs(serverInfo);
    const localServerModifiedTs = getScoreServerModifiedTs(localScore);
    return serverModifiedTs > 0 && localServerModifiedTs > 0 && serverModifiedTs > localServerModifiedTs;
}

function formatServerVersionDate(serverInfo) {
    const modifiedTs = getServerInfoModifiedTs(serverInfo);
    if (modifiedTs > 0) {
        return formatFileDialogDate(new Date(modifiedTs * 1000).toISOString());
    }
    const rawDate = serverInfo && serverInfo.serverUpdatedAt;
    return rawDate ? formatFileDialogDate(rawDate) : '';
}

function formatFileDialogEntryDate(entry) {
    return formatFileDialogDate(
        entry && (entry.localUpdatedAt || entry.updatedAt || entry.publishedAt || entry.serverUpdatedAt)
    );
}

function formatScoreServerDownloadDate(score) {
    return formatFileDialogDate(score && score.serverDownloadedAt);
}

function formatScoreLocalChangeDate(score) {
    if (!score || score.syncState !== 'modified-local') {
        return '';
    }
    return formatFileDialogDate(score.localUpdatedAt || score.updatedAt);
}

function buildServerVersionNotice(score, serverInfo, serverChanged) {
    const noticeParts = [];
    const downloadedDateText = formatScoreServerDownloadDate(score);
    const serverDateText = formatServerVersionDate(serverInfo);
    const localDateText = formatScoreLocalChangeDate(score);

    if (downloadedDateText) {
        noticeParts.push(uiText('file.notice.downloadedFromServer', { date: downloadedDateText }));
    }
    if (serverChanged) {
        noticeParts.push(
            uiText(
                downloadedDateText ? 'file.notice.serverChangedSince' : 'file.notice.serverChanged',
                { date: serverDateText ? ': ' + serverDateText : '' }
            )
        );
    } else {
        noticeParts.push(uiText(
            downloadedDateText ? 'file.notice.serverUnchangedSince' : 'file.notice.serverVersionUnchanged'
        ));
    }
    if (localDateText) {
        noticeParts.push(uiText('file.notice.locallyChanged', { date: localDateText }));
    }

    return noticeParts.join(' · ');
}

async function findServerScoreInfo(serverPath) {
    const normalizedServerPath = String(serverPath || '').trim();
    if (!normalizedServerPath) {
        return null;
    }
    const serverScores = await serverLibrary.listScores();
    return serverScores.find(function (score) {
        return score.serverPath === normalizedServerPath || score.fileName === normalizedServerPath;
    }) || null;
}

function createServerScoreInfoMap(serverScores) {
    return (Array.isArray(serverScores) ? serverScores : []).reduce(function (scoreMap, serverScore) {
        const key = serverScore && (serverScore.serverPath || serverScore.fileName);
        if (key) {
            scoreMap[key] = serverScore;
        }
        return scoreMap;
    }, {});
}

function applyServerUpdateStateToLocalScore(score, serverScoreMap) {
    if (!score || !score.serverPath || !serverScoreMap) {
        return score;
    }
    const serverInfo = serverScoreMap[score.serverPath];
    if (!serverInfo) {
        return Object.assign({}, score, {
            serverUpdateAvailable: false
        });
    }
    return Object.assign({}, score, {
        serverUpdateAvailable: isServerInfoNewerThanLocal(serverInfo, score),
        latestServerUpdatedAt: serverInfo.serverUpdatedAt || '',
        latestServerModifiedTs: getServerInfoModifiedTs(serverInfo)
    });
}

function setFileDialogServerNotice(message, serverPath, actionLabel) {
    const noticeEl = document.querySelector('#fileDialogServerNotice');
    if (!noticeEl) {
        return;
    }
    const textEl = noticeEl.querySelector('span');
    const actionButton = noticeEl.querySelector('button');
    if (!message) {
        noticeEl.hidden = true;
        fileDialogState.serverNoticePath = '';
        return;
    }
    if (textEl) {
        textEl.textContent = message;
    }
    fileDialogState.serverNoticePath = serverPath || '';
    if (actionButton) {
        actionButton.textContent = actionLabel || uiText('file.dialog.serverVersionLoad');
        actionButton.hidden = !serverPath;
    }
    noticeEl.hidden = false;
}

async function updateFileDialogServerNotice() {
    const requestId = fileDialogState.serverNoticeRequest + 1;
    fileDialogState.serverNoticeRequest = requestId;
    setFileDialogServerNotice('', '');

    if (isOfflineColdStart) {
        return;
    }

    try {
        if (fileDialogState.mode !== 'open') {
            return;
        }

        const entry = getSelectedFileDialogEntry();
        if (!entry || entry.entryType !== 'score') {
            return;
        }

        const serverPath = entry.serverPath || entry.fileName || '';
        if (!serverPath) {
            return;
        }

        if (fileDialogState.source === 'server') {
            const localScore = await localLibrary.findScoreByServerPath(serverPath);
            if (fileDialogState.serverNoticeRequest !== requestId) {
                return;
            }
            if (!localScore) {
                setFileDialogServerNotice(uiText('file.notice.serverImportOnOpen'), '');
                return;
            }
            const hasTimestampComparison = getServerInfoModifiedTs(entry) > 0 && getScoreServerModifiedTs(localScore) > 0;
            let differs = isServerInfoNewerThanLocal(entry, localScore);
            if (!hasTimestampComparison) {
                setFileDialogServerNotice(uiText('file.notice.checkingLocalCopy'), '');
                const serverScore = await serverLibrary.importScore(serverPath);
                if (fileDialogState.serverNoticeRequest !== requestId) {
                    return;
                }
                differs = normalizeScoreContentForCompare(localScore.content || localScore.data) !==
                    normalizeScoreContentForCompare(serverScore.content);
            }
            if (differs) {
                setFileDialogServerNotice(
                    buildServerVersionNotice(localScore, entry, true) +
                        ' · ' + uiText('file.notice.localCopyWillUpdate'),
                    ''
                );
            } else {
                setFileDialogServerNotice(buildServerVersionNotice(localScore, entry, false), '');
            }
            return;
        }

        if (!entry.serverPath) {
            return;
        }

        setFileDialogServerNotice(uiText('file.notice.checkingServer'), '');
        const serverInfo = entry.latestServerModifiedTs
            ? {
                serverPath: entry.serverPath,
                serverUpdatedAt: entry.latestServerUpdatedAt,
                serverModifiedTs: entry.latestServerModifiedTs
            }
            : await findServerScoreInfo(entry.serverPath);
        if (fileDialogState.serverNoticeRequest !== requestId) {
            return;
        }
        const hasTimestampComparison = serverInfo &&
            getServerInfoModifiedTs(serverInfo) > 0 &&
            getScoreServerModifiedTs(entry) > 0;
        let differs = Boolean(serverInfo && isServerInfoNewerThanLocal(serverInfo, entry));
        if (!hasTimestampComparison) {
            const serverScore = await serverLibrary.importScore(entry.serverPath);
            if (fileDialogState.serverNoticeRequest !== requestId) {
                return;
            }
            differs = normalizeScoreContentForCompare(entry.content || entry.data) !==
                normalizeScoreContentForCompare(serverScore.content);
        }
        if (differs) {
            entry.serverUpdateAvailable = true;
            entry.latestServerUpdatedAt = serverInfo && serverInfo.serverUpdatedAt
                ? serverInfo.serverUpdatedAt
                : '';
            entry.latestServerModifiedTs = getServerInfoModifiedTs(serverInfo);
            const selectedRow = document.querySelector('#fileDialogList tr.is-selected td:nth-child(2)');
            if (selectedRow) {
                selectedRow.textContent = getScoreStatusLabel(entry);
            }
            const selectedDateCell = document.querySelector('#fileDialogList tr.is-selected td:nth-child(3)');
            if (selectedDateCell) {
                selectedDateCell.textContent = formatFileDialogEntryDate(entry);
            }
            setFileDialogServerNotice(
                buildServerVersionNotice(entry, serverInfo, true),
                entry.serverPath,
                uiText('file.dialog.serverVersionLoad')
            );
        } else {
            setFileDialogServerNotice(buildServerVersionNotice(entry, serverInfo, false), '');
        }
    } catch (error) {
        if (fileDialogState.serverNoticeRequest === requestId) {
            console.warn('Serverversion konnte nicht geprüft werden', error);
            setFileDialogServerNotice(uiText('file.notice.checkUnavailable'), '');
        }
    }
}

function formatFileDialogDate(value) {
    if (!value) {
        return '';
    }
    const dateValue = new Date(value);
    if (Number.isNaN(dateValue.getTime())) {
        return '';
    }
    return dateValue.toLocaleDateString(window.BaraBeatI18n.getLocale(), {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getFileDialogTitle(mode) {
    if (mode === 'saveAs') {
        return uiText('file.dialog.saveAs');
    }
    if (mode === 'save') {
        return uiText('file.dialog.saveUnder');
    }
    if (mode === 'export') {
        return uiText('file.dialog.export');
    }
    return uiText('file.dialog.open');
}

function getFileDialogConfirmLabel(mode, format) {
    if (mode === 'save' || mode === 'saveAs') {
        return uiText('common.save');
    }
    if (mode === 'export') {
        return uiText('common.export');
    }
    return uiText('common.open');
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
    const serverNoticeSlotEl = document.querySelector('#fileDialogServerNoticeSlot');
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
        unpublishButton.disabled = isOfflineColdStart || !canDeletePublishedFileDialogScore();
    }
    if (nameEl) {
        nameEl.disabled = fileDialogState.mode === 'open';
    }
    if (fieldsEl) {
        fieldsEl.hidden = fileDialogState.mode === 'open';
    }
    if (serverNoticeSlotEl) {
        serverNoticeSlotEl.hidden = isOfflineColdStart || fileDialogState.mode !== 'open';
    }
    if (folderNameEl) {
        folderNameEl.textContent = fileDialogState.source === 'server'
            ? uiText(navigator.onLine === false ? 'file.status.serverOffline' : 'file.status.server')
            : (fileDialogState.folderId === localLibrary.rootFolderId
                ? uiText('file.status.local')
                : fileDialogState.folderName);
    }
    sourceButtons.forEach(function (buttonEl) {
        buttonEl.disabled = isExportMode || (isOfflineColdStart && buttonEl.dataset.source === 'server');
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
            statusCell.textContent = uiText('file.status.back');
        } else if (entry.entryType === 'folder') {
            statusCell.textContent = uiText('file.status.folder');
        } else {
            statusCell.textContent = fileDialogState.source === 'server'
                ? uiText('file.status.server')
                : getScoreStatusLabel(entry);
        }
        const dateCell = document.createElement('td');
        dateCell.textContent = fileDialogState.source === 'server'
            ? formatServerVersionDate(entry)
            : formatFileDialogEntryDate(entry);

        rowEl.append(nameCell, statusCell, dateCell);
        rowEl.addEventListener('click', function () {
            fileDialogState.selectedId = rowEl.dataset.id;
            if (fileDialogState.mode === 'open' && fileDialogState.source === 'local' && entry.entryType === 'score') {
                document.querySelector('#fileDialogName').value = entry.title || '';
            }
            updateFileDialogRowSelection();
            updateFileDialogControls();
            updateFileDialogServerNotice();
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
            if (isOfflineColdStart || navigator.onLine === false) {
                fileDialogState.entries = [];
                fileDialogState.selectedId = null;
                fileDialogState.filter = 'all';
                fileDialogState.folderName = 'Server (offline)';
                setFileDialogServerNotice(uiText('file.notice.offlineLocalAvailable'), '');
                renderFileDialogList();
                return;
            }
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
            let serverScoreMap = {};
            if (fileDialogState.mode === 'open' && !isOfflineColdStart) {
                try {
                    serverScoreMap = createServerScoreInfoMap(await serverLibrary.listScores());
                } catch (serverError) {
                    console.warn('Server-Zeitstempel konnten nicht geladen werden', serverError);
                }
            }
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
                return applyServerUpdateStateToLocalScore(Object.assign({ entryType: 'score' }, score), serverScoreMap);
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
        alert(uiText('file.error.listLoad', { message: error.message || '' }));
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
    setFileDialogServerNotice('', '');
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
    loadedTitle = title || uiText('editor.untitled');
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
        elementMarkup.push(serializeEditorElementForStorage(el));
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

function localizeTupletElement(element) {
    const elementId = element && typeof element.attr === 'function' ? element.attr('id') : '';
    if (elementId !== 'triplet' && elementId !== 'quartuplet') {
        return;
    }
    const label = uiText(elementId === 'quartuplet' ? 'score.tuplet.quartuplet' : 'score.tuplet.triplet');
    element.selectAll('text').forEach(function (textNode) {
        textNode.attr({ text: label });
    });
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
        localizeTupletElement(el);
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
        let readResult = callPHPScript_lesen(zeilenAnzahl, {
            showAlert: false,
            updateQuickPlaySelectors: false
        });
        if (normalizeLegacyMobileSheetNotePositions(readResult)) {
            readResult = callPHPScript_lesen(zeilenAnzahl, {
                showAlert: false,
                updateQuickPlaySelectors: false
            });
        }
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
    setRhythmTitle(snapshot.title || uiText('editor.untitled'));
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
        const resolvedElementId = typeof elementId === "function" ? elementId() : elementId;
        recordHistorySnapshot();
        insertedElement = createPaletteClone(resolvedTemplateElement, resolvedElementId, offsetX, offsetY);
        if (afterCreate) {
            afterCreate(insertedElement);
        }
    };
    sourceElement.click(insertElement);
    sourceElement.touchstart(insertElement);
    return insertElement;
}

const tupletNoteOptions = [
    { value: "tone", labelKey: "score.note.tone" },
    { value: "bass", labelKey: "score.note.bass" },
    { value: "slap", labelKey: "score.note.slapSlashBell" },
    { value: "tone_muffled", labelKey: "score.note.muffledTone" },
    { value: "slap_muffled", labelKey: "score.note.muffledSlapSlashClick" }
];

function closeTupletDialog() {
    const dialogEl = document.querySelector("#tupletDialog");
    if (dialogEl) {
        dialogEl.hidden = true;
    }
    if (typeof mobileSheetEditorState !== 'undefined') {
        mobileSheetEditorState.pendingTupletTarget = null;
    }
}

function renderTupletDialogControls(display) {
    const controlsEl = document.querySelector("#tupletDialogControls");
    if (!controlsEl) {
        return;
    }
    const count = display.type === "quartuplet" ? 4 : 3;
    const defaultValues = Array.from({ length: count }, function () { return "tone"; });
    controlsEl.innerHTML = "";

    for (let index = 0; index < count; index += 1) {
        const labelEl = document.createElement("label");
        labelEl.textContent = uiText('score.tuplet.noteNumber', { number: index + 1 });
        const selectEl = document.createElement("select");
        selectEl.className = "tuplet-note-select";
        selectEl.setAttribute("data-note-index", String(index));
        tupletNoteOptions.forEach(function (option) {
            const optionEl = document.createElement("option");
            optionEl.value = option.value;
            optionEl.textContent = uiText(option.labelKey);
            if (option.value === defaultValues[index]) {
                optionEl.selected = true;
            }
            selectEl.appendChild(optionEl);
        });
        labelEl.appendChild(selectEl);
        controlsEl.appendChild(labelEl);
    }
}

function openTupletDialog(event) {
    if (event && typeof event.preventDefault === "function") {
        event.preventDefault();
    }
    const display = getCurrentTupletDisplay();
    const dialogEl = document.querySelector("#tupletDialog");
    const titleEl = document.querySelector("#tupletDialogTitle");
    if (!dialogEl) {
        return;
    }
    if (titleEl) {
        titleEl.textContent = uiText('score.tuplet.insertTitle', { name: display.label });
    }
    dialogEl.setAttribute("data-tuplet-type", display.type);
    renderTupletDialogControls(display);
    dialogEl.hidden = false;
}

function insertTupletFromDialog() {
    const dialogEl = document.querySelector("#tupletDialog");
    const type = dialogEl ? (dialogEl.getAttribute("data-tuplet-type") || getCurrentTupletDisplay().type) : getCurrentTupletDisplay().type;
    const noteTypes = Array.prototype.map.call(
        document.querySelectorAll("#tupletDialogControls .tuplet-note-select"),
        function (selectEl) {
            return selectEl.value || "tone";
        }
    );
    if (!noteTypes.length) {
        return;
    }
    recordHistorySnapshot();
    insertedElement = createTupletElementFromPalette(noteTypes, type);
    const mobileTarget = typeof mobileSheetEditorState !== 'undefined'
        ? mobileSheetEditorState.pendingTupletTarget
        : null;
    if (mobileTarget) {
        const targetPosition = getMobileSheetSourcePosition(
            mobileTarget.sourceBarIndex,
            mobileTarget.sourceStepIndex,
            type,
            mobileTarget.instrumentName
        );
        moveSheetElementAnchorTo(insertedElement, targetPosition.x, targetPosition.y, false);
        closeTupletDialog();
        refreshMobileSheetEditorView();
        return;
    }
    closeTupletDialog();
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
    stopSheetQuickPlay();
    resetSelectionArtifacts();
    removeCanvasElements("#notenlinien, .sheet-quick-play-overlay, .shp, " + chooserSelector + ", " + timelineMetadataSelector);
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
    timelineState.shekereBeatEnabled = false;
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
    renderSheetQuickPlaySelectors();

    if (shouldResetTitle) {
        document.body.classList.add('has-loaded-score');
        currentScoreId = null;
        setSelectedFileSource('local');
        rememberLastLoadedScore('');
        setRhythmTitle(defaultRhythmTitle);
    }
}

var titel = s.text(100, y - 100, defaultRhythmTitle).attr({ id: 'basis', 'font-size': 24, 'font-family': 'sans-serif', 'font-weight': 'bold', fill: '#8a8a8a', cursor: 'text' });
titel.click(edit_title);
titel.dblclick(edit_title);

const sheetQuickPlayState = {
    selectedPatternIds: [],
    patternLibrary: [],
    isPlaying: false,
    activeHighlightTimers: [],
    highlightSectionsByRuntimeKey: {},
    noteElementsByPosition: {},
    mobileNoteElementsByPosition: {},
    preparedSignature: '',
    frameReady: false,
    preparationTimer: null,
    schedulerTimer: null
};
const sheetPatternMoveState = {
    ranges: []
};
let sheetQuickPlayRefreshTimer = null;
let sheetQuickPlayMutationObserver = null;
let sheetQuickPlayEditorPointerActive = false;
let sheetQuickPlayRefreshPending = false;
let sheetQuickPlayLiveRefreshInitialized = false;

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

function getSheetBarBounds(sourceBarIndex) {
    const zeroBasedBarIndex = Math.max(0, Number(sourceBarIndex) - 1);
    const lineIndex = Math.floor(zeroBasedBarIndex / 2);
    const isRightBar = zeroBasedBarIndex % 2 === 1;
    const lineBaseY = getSheetLineBaseY(lineIndex);
    return {
        x: isRightBar ? 528 : 103,
        y: lineBaseY - 70,
        width: 422,
        height: 126
    };
}

function sheetBarHasOwnContent(bar) {
    if (!bar) {
        return false;
    }
    return Boolean(
        String(bar.instrument || '').trim() ||
        String(bar.label || '').trim() ||
        (Array.isArray(bar.notes) && bar.notes.some(function (noteValue) {
            return noteValue && noteValue !== 'f';
        })) ||
        (Array.isArray(bar.controls) && bar.controls.length > 0) ||
        (bar.repeat && (
            (Array.isArray(bar.repeat.start) && bar.repeat.start.length > 0) ||
            (Array.isArray(bar.repeat.end) && bar.repeat.end.length > 0)
        ))
    );
}

function getSheetPatternMoveRanges(readResult) {
    const bars = Array.isArray(readResult && readResult.rhythmBars)
        ? readResult.rhythmBars
        : [];
    const patternStarts = [];
    let lastContentBarIndex = 0;

    bars.forEach(function (bar) {
        const barIndex = Number(bar && bar.index) || 0;
        if (sheetBarHasOwnContent(bar)) {
            lastContentBarIndex = Math.max(lastContentBarIndex, barIndex);
        }
        const instrument = String(bar && bar.instrument || '').trim();
        const label = String(bar && bar.label || '').trim();
        if (instrument || label) {
            patternStarts.push({
                barIndex: barIndex,
                instrument: instrument,
                label: label,
                isMovable: Boolean(
                    (instrument && instrument !== 'Leer' && instrument !== 'Instrument') ||
                    (label && label !== 'Leer' && label !== 'Funktion')
                )
            });
        }
    });

    return patternStarts.reduce(function (ranges, patternStart, startIndex) {
        if (!patternStart.isMovable || patternStart.barIndex < 1) {
            return ranges;
        }
        const nextStart = patternStarts[startIndex + 1];
        const endBarIndex = nextStart
            ? nextStart.barIndex - 1
            : lastContentBarIndex;
        if (endBarIndex < patternStart.barIndex) {
            return ranges;
        }
        ranges.push({
            id: 'sheet-pattern-range-' + patternStart.barIndex,
            startBarIndex: patternStart.barIndex,
            endBarIndex: endBarIndex,
            instrument: patternStart.instrument,
            label: patternStart.label
        });
        return ranges;
    }, []);
}

function getSheetPatternMoveRangeForBar(sourceBarIndex) {
    const normalizedBarIndex = Number(sourceBarIndex);
    return sheetPatternMoveState.ranges.find(function (range) {
        return normalizedBarIndex >= range.startBarIndex && normalizedBarIndex <= range.endBarIndex;
    }) || null;
}

function getSheetPatternMoveRangeIndex(rangeId) {
    return sheetPatternMoveState.ranges.findIndex(function (range) {
        return range.id === rangeId;
    });
}

function getSheetElementMoveBarIndex(element, readConfig, totalBarCount, headerSubtitleElement) {
    if (!element || typeof element.attr !== 'function') {
        return null;
    }
    if (headerSubtitleElement && headerSubtitleElement.node === element.node) {
        return null;
    }

    const elementId = String(element.attr('id') || '');
    const position = getElementReadPosition(element);
    let barIndex;

    if (elementId === 'wiederholung') {
        const repeatTarget = getRepeatTarget(position.x, position.y, zeilenAnzahl);
        if (!repeatTarget) {
            return null;
        }
        barIndex = repeatTarget.repeatSide === 'start'
            ? repeatTarget.boundaryIndex + 1
            : repeatTarget.boundaryIndex;
    } else if (isInstrumentChooserNode(element) || isFunctionChooserNode(element)) {
        barIndex = getBarIndexForMetaElement(
            position.x,
            position.y,
            readConfig,
            zeilenAnzahl
        ).barIndex + 1;
    } else {
        const positionInfo = getBarIndexFromPosition(
            position.x,
            position.y,
            readConfig,
            zeilenAnzahl
        );
        if (elementId === 'shortbar' || elementId === 'in' || elementId === 'out') {
            positionInfo.rawLineSlotIndex = getControlLineSlotIndex(position.x, readConfig, elementId);
            positionInfo.lineSlotIndex = positionInfo.rawLineSlotIndex;
            if (positionInfo.lineSlotIndex > readConfig.stepsPerBar) {
                positionInfo.lineSlotIndex -= Number(readConfig.gapSlotCount) || 2;
            }
            positionInfo.barIndex = positionInfo.lineIndex * 2 + (
                positionInfo.rawLineSlotIndex > readConfig.stepsPerBar + (Number(readConfig.gapSlotCount) || 2)
                    ? 1
                    : 0
            );
        }
        barIndex = positionInfo.barIndex + 1;
    }

    return barIndex >= 1 && barIndex <= totalBarCount ? barIndex : null;
}

function translateSheetElementBetweenBars(element, sourceBarIndex, targetBarIndex) {
    if (!element || sourceBarIndex === targetBarIndex) {
        return;
    }
    const sourceBounds = getSheetBarBounds(sourceBarIndex);
    const targetBounds = getSheetBarBounds(targetBarIndex);
    const currentTranslate = typeof getElementTranslate === 'function'
        ? getElementTranslate(element)
        : { x: 0, y: 0 };
    element.transform(
        't' + (currentTranslate.x + targetBounds.x - sourceBounds.x) + ',' +
        (currentTranslate.y + targetBounds.y - sourceBounds.y)
    );
}

function moveSheetBarBlock(sourceStartBarIndex, sourceEndBarIndex, targetBoundaryBarIndex) {
    const totalBarCount = normalizeSheetLineCount(zeilenAnzahl) * 2;
    const startBarIndex = Math.max(1, Math.round(Number(sourceStartBarIndex) || 1));
    const endBarIndex = Math.min(
        totalBarCount,
        Math.max(startBarIndex, Math.round(Number(sourceEndBarIndex) || startBarIndex))
    );
    const targetBoundary = Math.max(
        1,
        Math.min(totalBarCount + 1, Math.round(Number(targetBoundaryBarIndex) || 1))
    );
    if (targetBoundary >= startBarIndex && targetBoundary <= endBarIndex + 1) {
        return false;
    }

    if (typeof resetSelectionArtifacts === 'function') {
        resetSelectionArtifacts();
    }
    const barOrder = Array.from({ length: totalBarCount }, function (_, barOffset) {
        return barOffset + 1;
    });
    const movedBars = barOrder.splice(startBarIndex - 1, endBarIndex - startBarIndex + 1);
    let insertionIndex = targetBoundary - 1;
    if (targetBoundary > endBarIndex + 1) {
        insertionIndex -= movedBars.length;
    }
    barOrder.splice.apply(barOrder, [insertionIndex, 0].concat(movedBars));

    const targetBarBySourceBar = {};
    barOrder.forEach(function (sourceBarIndex, targetOffset) {
        targetBarBySourceBar[sourceBarIndex] = targetOffset + 1;
    });

    const readConfig = getReadRhythmConfig();
    const headerSubtitleEntry = typeof getMobileSheetHeaderSubtitleEntry === 'function'
        ? getMobileSheetHeaderSubtitleEntry()
        : null;
    const placements = [];
    s.selectAll('.shp').forEach(function (element) {
        const sourceBarIndex = getSheetElementMoveBarIndex(
            element,
            readConfig,
            totalBarCount,
            headerSubtitleEntry ? headerSubtitleEntry.element : null
        );
        if (!sourceBarIndex || targetBarBySourceBar[sourceBarIndex] === sourceBarIndex) {
            return;
        }
        placements.push({
            element: element,
            sourceBarIndex: sourceBarIndex,
            targetBarIndex: targetBarBySourceBar[sourceBarIndex]
        });
    });

    if (placements.length === 0) {
        return false;
    }
    recordHistorySnapshot();
    placements.forEach(function (placement) {
        translateSheetElementBetweenBars(
            placement.element,
            placement.sourceBarIndex,
            placement.targetBarIndex
        );
    });
    return true;
}

function moveSheetPatternByDirection(rangeId, direction) {
    const rangeIndex = getSheetPatternMoveRangeIndex(rangeId);
    const directionOffset = direction === 'up' ? -1 : 1;
    const targetRange = sheetPatternMoveState.ranges[rangeIndex + directionOffset];
    const sourceRange = sheetPatternMoveState.ranges[rangeIndex];
    if (!sourceRange || !targetRange) {
        return false;
    }

    const targetBoundary = direction === 'up'
        ? targetRange.startBarIndex
        : targetRange.endBarIndex + 1;
    if (!moveSheetBarBlock(
        sourceRange.startBarIndex,
        sourceRange.endBarIndex,
        targetBoundary
    )) {
        return false;
    }

    const readResult = callPHPScript_lesen(zeilenAnzahl, {
        showAlert: false,
        updateQuickPlaySelectors: false,
        logResults: false
    });
    renderSheetQuickPlaySelectors(readResult);
    if (isMobileSheetReaderViewport()) {
        renderMobileSheetView(readResult);
    }
    return true;
}

function positionSheetQuickPlayControls() {
    const controlsEl = document.getElementById('sheetQuickPlayControls');
    if (!controlsEl || !s || !s.node) {
        return;
    }
    if (isMobilePracticeViewport()) {
        return;
    }

    const svgBounds = s.node.getBoundingClientRect();
    const scrollX = window.pageXOffset || document.documentElement.scrollLeft || 0;
    const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
    controlsEl.style.left = (svgBounds.left + scrollX + svgBounds.width - controlsEl.offsetWidth - 40) + 'px';
    controlsEl.style.top = (svgBounds.top + scrollY + 44) + 'px';
}

function setSheetQuickPlayTempo(tempoValue) {
    const tempo = typeof normalizeTimelineTempo === 'function'
        ? normalizeTimelineTempo(tempoValue)
        : Math.max(30, Math.min(180, Math.round(Number(tempoValue) || 100)));
    ['sheetQuickPlayTempo', 'mobileSheetQuickPlayTempo'].forEach(function (inputId) {
        const inputEl = document.getElementById(inputId);
        if (inputEl) {
            inputEl.value = tempo;
        }
    });
    return tempo;
}

function getSheetQuickPlayTempo() {
    const desktopTempoEl = document.getElementById('sheetQuickPlayTempo');
    const mobileTempoEl = document.getElementById('mobileSheetQuickPlayTempo');
    const preferredTempoEl = isMobilePracticeViewport() && mobileTempoEl
        ? mobileTempoEl
        : desktopTempoEl;
    return setSheetQuickPlayTempo(preferredTempoEl ? preferredTempoEl.value : timelineState.tempo);
}

function setSheetQuickPlayButtonState(isPlaying) {
    sheetQuickPlayState.isPlaying = Boolean(isPlaying);
    ['sheetQuickPlayButton', 'mobileSheetQuickPlayButton'].forEach(function (buttonId) {
        const buttonEl = document.getElementById(buttonId);
        if (!buttonEl) {
            return;
        }
        buttonEl.textContent = sheetQuickPlayState.isPlaying ? '■' : '▶';
        buttonEl.setAttribute('aria-pressed', sheetQuickPlayState.isPlaying ? 'true' : 'false');
        buttonEl.classList.toggle('is-playing', sheetQuickPlayState.isPlaying);
        buttonEl.title = sheetQuickPlayState.isPlaying
            ? uiText('editor.quickPlay.stop')
            : uiText('editor.quickPlay.playSelected');
    });
    updateSheetQuickPlayButtonAvailability();
}

function updateSheetQuickPlayButtonAvailability() {
    const mobileButtonEl = document.getElementById('mobileSheetQuickPlayButton');
    if (!mobileButtonEl) {
        positionMobileSheetQuickPlayFrame();
        return;
    }
    const hasSelection = sheetQuickPlayState.selectedPatternIds.length > 0;
    const isLoading = hasSelection && !sheetQuickPlayState.frameReady && !sheetQuickPlayState.isPlaying;
    mobileButtonEl.disabled = !sheetQuickPlayState.isPlaying && (!hasSelection || !sheetQuickPlayState.frameReady);
    mobileButtonEl.classList.toggle('is-loading', isLoading);
    if (isLoading) {
        mobileButtonEl.textContent = '…';
        mobileButtonEl.title = uiText('editor.quickPlay.loadingSounds');
    } else {
        mobileButtonEl.textContent = sheetQuickPlayState.isPlaying ? '■' : '▶';
        mobileButtonEl.title = sheetQuickPlayState.isPlaying
            ? uiText('editor.quickPlay.stop')
            : uiText('editor.quickPlay.playSelected');
    }
    positionMobileSheetQuickPlayFrame();
}

function positionMobileSheetQuickPlayFrame() {
    const frameEl = document.getElementById('sheetQuickPlayFrame');
    const buttonEl = document.getElementById('mobileSheetQuickPlayButton');
    const tempoInputEl = document.getElementById('mobileSheetQuickPlayTempo');
    const isTempoInputFocused = Boolean(tempoInputEl && document.activeElement === tempoInputEl);
    const shouldKeepFrameInViewport = Boolean(
        frameEl &&
        buttonEl &&
        !isTempoInputFocused &&
        isMobilePracticeViewport() &&
        sheetQuickPlayState.selectedPatternIds.length > 0
    );

    if (!frameEl || !shouldKeepFrameInViewport) {
        if (frameEl) {
            frameEl.classList.remove('is-mobile-active');
            frameEl.classList.remove('is-mobile-control');
            frameEl.style.removeProperty('left');
            frameEl.style.removeProperty('top');
            frameEl.style.removeProperty('width');
            frameEl.style.removeProperty('height');
        }
        return;
    }

    const buttonBounds = buttonEl.getBoundingClientRect();
    frameEl.style.left = buttonBounds.left + 'px';
    frameEl.style.top = buttonBounds.top + 'px';
    frameEl.style.width = buttonBounds.width + 'px';
    frameEl.style.height = buttonBounds.height + 'px';
    frameEl.classList.add('is-mobile-active');
    frameEl.classList.toggle('is-mobile-control', sheetQuickPlayState.frameReady);
}

function getSheetQuickPlaySelectedPatterns() {
    const patternById = {};
    sheetQuickPlayState.patternLibrary.forEach(function (pattern) {
        if (pattern && pattern.id) {
            patternById[pattern.id] = pattern;
        }
    });
    return sheetQuickPlayState.selectedPatternIds
        .map(function (patternId) {
            return patternById[patternId] || null;
        })
        .filter(Boolean);
}

function clearSheetQuickPlayHighlights() {
    sheetQuickPlayState.activeHighlightTimers.forEach(function (timerId) {
        window.clearTimeout(timerId);
    });
    sheetQuickPlayState.activeHighlightTimers = [];
    s.selectAll('.sheet-quick-play-highlight').forEach(function (highlightEl) {
        highlightEl.removeClass('is-active');
    });
    s.selectAll('.sheet-quick-play-note-active').forEach(function (noteEl) {
        noteEl.removeClass('sheet-quick-play-note-active');
        if (noteEl.node) {
            noteEl.node.__sheetQuickPlayHighlightTimer = null;
        }
    });
    document.querySelectorAll('.mobile-sheet-note.sheet-quick-play-note-active').forEach(function (noteEl) {
        noteEl.classList.remove('sheet-quick-play-note-active');
        noteEl.__sheetQuickPlayHighlightTimer = null;
    });
}

function getSheetQuickPlayPositionKey(sourceBarIndex, sourceStepIndex) {
    return String(Math.max(1, Math.round(Number(sourceBarIndex) || 1))) + ':' +
        String(Math.max(0, Math.round(Number(sourceStepIndex) || 0)));
}

function rebuildSheetQuickPlayNoteElementMap() {
    const readConfig = getReadRhythmConfig();
    const elementMap = {};
    s.selectAll('.shp').forEach(function (noteEl) {
        const elementId = noteEl.attr('id');
        if (!noteElementIds.includes(elementId) && !tupletElementIds.includes(elementId)) {
            return;
        }
        const elementPosition = getElementReadPosition(noteEl);
        const positionInfo = getBarIndexFromPosition(
            elementPosition.x,
            elementPosition.y,
            readConfig,
            zeilenAnzahl
        );
        const sourceStepIndex = getStepIndexWithinBar(
            positionInfo.lineSlotIndex,
            readConfig.stepsPerBar
        );
        if (sourceStepIndex === null || sourceStepIndex < 0 || sourceStepIndex >= readConfig.stepsPerBar) {
            return;
        }
        const key = getSheetQuickPlayPositionKey(positionInfo.barIndex + 1, sourceStepIndex);
        if (!elementMap[key]) {
            elementMap[key] = [];
        }
        elementMap[key].push(noteEl);
    });
    sheetQuickPlayState.noteElementsByPosition = elementMap;
}

function rebuildMobileSheetQuickPlayNoteElementMap() {
    const elementMap = {};
    document.querySelectorAll('.mobile-sheet-note[data-source-bar-index][data-source-step-index]').forEach(function (noteEl) {
        const key = getSheetQuickPlayPositionKey(
            noteEl.dataset.sourceBarIndex,
            noteEl.dataset.sourceStepIndex
        );
        if (!elementMap[key]) {
            elementMap[key] = [];
        }
        elementMap[key].push(noteEl);
    });
    sheetQuickPlayState.mobileNoteElementsByPosition = elementMap;
}

function scheduleSheetQuickPlayNoteHighlights(message) {
    const runtimeKey = String(message && message.runtimeKey || '');
    const section = runtimeKey
        ? sheetQuickPlayState.highlightSectionsByRuntimeKey[runtimeKey]
        : null;
    const localStep = Math.max(0, Math.round(Number(message && message.localStep) || 0));
    const refs = section && Array.isArray(section.highlightSteps)
        ? (section.highlightSteps[localStep] || [])
        : [];
    if (refs.length === 0) {
        return;
    }

    const startTimerId = window.setTimeout(function () {
        refs.forEach(function (ref) {
            const key = getSheetQuickPlayPositionKey(ref.sourceBarIndex, ref.sourceStepIndex);
            const noteElements = sheetQuickPlayState.noteElementsByPosition[key] || [];
            noteElements.forEach(function (noteEl) {
                if (!noteEl || !noteEl.node) {
                    return;
                }
                if (noteEl.node.__sheetQuickPlayHighlightTimer) {
                    window.clearTimeout(noteEl.node.__sheetQuickPlayHighlightTimer);
                }
                noteEl.addClass('sheet-quick-play-note-active');
                const clearTimerId = window.setTimeout(function () {
                    noteEl.removeClass('sheet-quick-play-note-active');
                    noteEl.node.__sheetQuickPlayHighlightTimer = null;
                }, 150);
                noteEl.node.__sheetQuickPlayHighlightTimer = clearTimerId;
                sheetQuickPlayState.activeHighlightTimers.push(clearTimerId);
            });
            const mobileNoteElements = sheetQuickPlayState.mobileNoteElementsByPosition[key] || [];
            mobileNoteElements.forEach(function (noteEl) {
                if (noteEl.__sheetQuickPlayHighlightTimer) {
                    window.clearTimeout(noteEl.__sheetQuickPlayHighlightTimer);
                }
                noteEl.classList.add('sheet-quick-play-note-active');
                const clearTimerId = window.setTimeout(function () {
                    noteEl.classList.remove('sheet-quick-play-note-active');
                    noteEl.__sheetQuickPlayHighlightTimer = null;
                }, 150);
                noteEl.__sheetQuickPlayHighlightTimer = clearTimerId;
                sheetQuickPlayState.activeHighlightTimers.push(clearTimerId);
            });
        });
    }, Math.max(0, Number(message && message.delayMs) || 0));
    sheetQuickPlayState.activeHighlightTimers.push(startTimerId);
}

function updateSheetQuickPlaySelectionClasses() {
    const selectedSet = new Set(sheetQuickPlayState.selectedPatternIds);
    s.selectAll('.sheet-quick-play-overlay').forEach(function (overlayEl) {
        const patternId = overlayEl.attr('data-pattern-id');
        const isSelected = selectedSet.has(patternId);
        if (isSelected) {
            overlayEl.addClass('is-selected');
        } else {
            overlayEl.removeClass('is-selected');
        }
    });
    document.querySelectorAll('.mobile-sheet-bar[data-pattern-id]').forEach(function (cardEl) {
        cardEl.classList.toggle('is-quick-play-selected', selectedSet.has(cardEl.dataset.patternId));
    });
    document.querySelectorAll('.mobile-sheet-pattern-toggle[data-pattern-id]').forEach(function (buttonEl) {
        const isSelected = selectedSet.has(buttonEl.dataset.patternId);
        buttonEl.classList.toggle('is-selected', isSelected);
        buttonEl.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        buttonEl.textContent = isSelected ? '✓' : '';
    });
}

function selectSheetQuickPlayPattern(patternId) {
    if (!patternId) {
        return;
    }
    const selectedIds = sheetQuickPlayState.selectedPatternIds.slice();
    const existingIndex = selectedIds.indexOf(patternId);
    if (existingIndex >= 0) {
        selectedIds.splice(existingIndex, 1);
    } else {
        selectedIds.push(patternId);
    }
    sheetQuickPlayState.selectedPatternIds = selectedIds;
    updateSheetQuickPlaySelectionClasses();
    if (isMobilePracticeViewport()) {
        if (sheetQuickPlayState.isPlaying) {
            stopSheetQuickPlay();
        }
        scheduleSheetQuickPlayPreparation();
    }
}

function buildSheetQuickPlayRepeatRanges(patternBars) {
    const bars = Array.isArray(patternBars) ? patternBars : [];
    const boundaries = new Array(bars.length + 1).fill(null).map(function (_, boundaryIndex) {
        return {
            index: boundaryIndex,
            startMarkers: [],
            endMarkers: []
        };
    });
    const ranges = [];
    const startStack = [];

    bars.forEach(function (bar, barIndex) {
        const repeatInfo = bar && bar.repeat ? bar.repeat : {};
        const startMarkers = Array.isArray(repeatInfo.start) ? repeatInfo.start : (repeatInfo.start ? [repeatInfo.start] : []);
        const endMarkers = Array.isArray(repeatInfo.end) ? repeatInfo.end : (repeatInfo.end ? [repeatInfo.end] : []);
        startMarkers.forEach(function (marker) {
            if (marker !== false && marker !== null && marker !== undefined && marker !== '') {
                boundaries[barIndex].startMarkers.push(marker);
            }
        });
        endMarkers.forEach(function (marker) {
            if (marker !== false && marker !== null && marker !== undefined && marker !== '') {
                boundaries[barIndex + 1].endMarkers.push(marker);
            }
        });
    });

    boundaries.forEach(function (boundary) {
        boundary.endMarkers.forEach(function (endMarker) {
            if (endMarker === 'continue') {
                return;
            }
            const startBoundary = startStack.pop();
            const repeatCount = endMarker === 'loop'
                ? 1
                : Math.max(1, Math.round(Number(endMarker) || 1));
            if (startBoundary === undefined || startBoundary === null) {
                return;
            }
            ranges.push({
                startBar: startBoundary + 1,
                endBar: boundary.index,
                count: repeatCount
            });
        });
        boundary.startMarkers.forEach(function () {
            startStack.push(boundary.index);
        });
    });

    return ranges;
}

function expandSheetQuickPlayBars(patternBars, repeatRangesToApply, startBarIndex, endBarIndex) {
    const bars = Array.isArray(patternBars) ? patternBars : [];
    const ranges = Array.isArray(repeatRangesToApply) ? repeatRangesToApply : [];
    const expandedBars = [];
    let currentBarIndex = startBarIndex;

    while (currentBarIndex <= endBarIndex) {
        const matchingRange = ranges
            .filter(function (repeatRange) {
                return repeatRange &&
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

        const nestedRanges = ranges.filter(function (repeatRange) {
            return repeatRange.startBar >= matchingRange.startBar &&
                repeatRange.endBar <= matchingRange.endBar &&
                !(repeatRange.startBar === matchingRange.startBar && repeatRange.endBar === matchingRange.endBar);
        });
        const repeatedSegment = expandSheetQuickPlayBars(
            bars,
            nestedRanges,
            matchingRange.startBar,
            matchingRange.endBar
        );
        expandedBars.push.apply(expandedBars, repeatedSegment);
        for (let repeatIndex = 0; repeatIndex < Math.max(0, Number(matchingRange.count) || 0); repeatIndex++) {
            expandedBars.push.apply(expandedBars, repeatedSegment);
        }
        currentBarIndex = matchingRange.endBar + 1;
    }

    return expandedBars.filter(Boolean);
}

function buildSheetQuickPlayPreparedPattern(pattern, patternIndex) {
    const sourceBars = Array.isArray(pattern && pattern.bars) ? pattern.bars : [];
    const repeatRanges = buildSheetQuickPlayRepeatRanges(sourceBars);
    const hasInControl = sourceBars.some(function (bar) {
        return (Array.isArray(bar && bar.controls) ? bar.controls : []).some(function (control) {
            return control && control.type === 'in';
        });
    });
    const fullPatternRepeatRanges = hasInControl ? [] : repeatRanges.filter(function (repeatRange) {
        return repeatRange.startBar === 1 && repeatRange.endBar === sourceBars.length;
    });
    const expandedBars = expandSheetQuickPlayBars(
        sourceBars,
        repeatRanges.filter(function (repeatRange) {
            return fullPatternRepeatRanges.indexOf(repeatRange) === -1;
        }),
        1,
        sourceBars.length
    );
    const sectionRepeatCount = fullPatternRepeatRanges.reduce(function (repeatCount, repeatRange) {
        return repeatCount * (Math.max(0, Math.round(Number(repeatRange.count) || 0)) + 1);
    }, 1);
    let keptInControl = false;
    let inStep = null;
    let stepOffset = 0;

    const preparedBars = expandedBars.map(function (bar, barIndex) {
        const barNotes = Array.isArray(bar && bar.notes) ? bar.notes.slice() : [];
        const barControls = Array.isArray(bar && bar.controls) ? bar.controls : [];
        const preparedControls = [];

        barControls.forEach(function (control) {
            if (!control || !control.type) {
                return;
            }
            if (control.type === 'in') {
                if (keptInControl) {
                    return;
                }
                keptInControl = true;
            }
            preparedControls.push(Object.assign({}, control));
        });

        if (inStep === null) {
            const inControl = preparedControls
                .filter(function (control) {
                    return control && control.type === 'in';
                })
                .sort(function (controlA, controlB) {
                    return Number(controlA.stepIndex) - Number(controlB.stepIndex);
                })[0];
            if (inControl) {
                inStep = stepOffset + Math.max(0, Math.min(barNotes.length, Number(inControl.stepIndex) || 0));
            }
        }
        stepOffset += barNotes.length;

        return {
            sourceBarIndex: Number(bar && bar.sourceBarIndex) || (barIndex + 1),
            label: bar && bar.label ? bar.label : pattern.labelType,
            repeat: {
                start: [],
                end: []
            },
            controls: preparedControls,
            notes: barNotes
        };
    });

    return Object.assign({}, pattern, {
        id: 'sheet-quick-play-pattern-' + patternIndex,
        sourceKey: 'sheet-quick-play|' + (pattern.sourceKey || pattern.id || patternIndex),
        quickPlayDebug: {
            originalId: pattern.id,
            originalSourceKey: pattern.sourceKey,
            name: pattern.name || pattern.labelName || pattern.labelType || '',
            foundInStep: inStep,
            originalBarCount: sourceBars.length,
            preparedBarCount: preparedBars.length
        },
        quickPlaySectionRepeatCount: sectionRepeatCount,
        bars: preparedBars
    });
}

function createSheetQuickPlayTrackMap() {
    return {
        Kenkeni: [],
        Sangban: [],
        Doundoun: [],
        Dreierbass: [],
        Djembe_1: [],
        Djembe_2: [],
        Djembe_3: [],
        Shekere: []
    };
}

function mergeSheetQuickPlayNotes(targetNotes, sourceNotes, offset) {
    const mergedNotes = Array.isArray(targetNotes) ? targetNotes.slice() : [];
    const safeSourceNotes = Array.isArray(sourceNotes) ? sourceNotes : [];
    const writeOffset = Math.max(0, Number(offset) || 0);
    while (mergedNotes.length < writeOffset) {
        mergedNotes.push('f');
    }
    safeSourceNotes.forEach(function (noteValue, noteIndex) {
        const writeIndex = writeOffset + noteIndex;
        while (mergedNotes.length <= writeIndex) {
            mergedNotes.push('f');
        }
        if (noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '') {
            mergedNotes[writeIndex] = noteValue;
        } else if (mergedNotes[writeIndex] === undefined) {
            mergedNotes[writeIndex] = 'f';
        }
    });
    return mergedNotes;
}

function mergeSheetQuickPlayHighlightRefs(targetSteps, sourceRefs, offset) {
    const mergedSteps = Array.isArray(targetSteps)
        ? targetSteps.map(function (stepRefs) {
            return Array.isArray(stepRefs) ? stepRefs.slice() : [];
        })
        : [];
    const safeSourceRefs = Array.isArray(sourceRefs) ? sourceRefs : [];
    const writeOffset = Math.max(0, Math.round(Number(offset) || 0));

    safeSourceRefs.forEach(function (sourceRefValue, sourceIndex) {
        const refsForStep = Array.isArray(sourceRefValue)
            ? sourceRefValue.filter(Boolean)
            : (sourceRefValue ? [sourceRefValue] : []);
        if (refsForStep.length === 0) {
            return;
        }
        const writeIndex = writeOffset + sourceIndex;
        while (mergedSteps.length <= writeIndex) {
            mergedSteps.push([]);
        }
        refsForStep.forEach(function (sourceRef) {
            const refKey = getSheetQuickPlayPositionKey(
                sourceRef.sourceBarIndex,
                sourceRef.sourceStepIndex
            );
            const alreadyPresent = mergedSteps[writeIndex].some(function (existingRef) {
                return getSheetQuickPlayPositionKey(
                    existingRef.sourceBarIndex,
                    existingRef.sourceStepIndex
                ) === refKey;
            });
            if (!alreadyPresent) {
                mergedSteps[writeIndex].push(sourceRef);
            }
        });
    });
    return mergedSteps;
}

function getSheetQuickPlayPatternHighlightRefs(pattern) {
    return (Array.isArray(pattern && pattern.bars) ? pattern.bars : []).reduce(function (allRefs, bar) {
        const sourceBarIndex = Number(bar && bar.sourceBarIndex) || 1;
        const barNotes = Array.isArray(bar && bar.notes) ? bar.notes : [];
        return allRefs.concat(barNotes.map(function (noteValue, sourceStepIndex) {
            return isSheetQuickPlayPlayableNote(noteValue)
                ? {
                    sourceBarIndex: sourceBarIndex,
                    sourceStepIndex: sourceStepIndex
                }
                : null;
        }));
    }, []);
}

function repeatSheetQuickPlayHighlightRefsToLength(sourceRefs, targetLength) {
    const refs = Array.isArray(sourceRefs) ? sourceRefs : [];
    const safeTargetLength = Math.max(0, Math.round(Number(targetLength) || 0));
    if (refs.length === 0 || safeTargetLength <= 0) {
        return refs.slice();
    }
    const repeatedRefs = [];
    while (repeatedRefs.length < safeTargetLength) {
        const remainingLength = safeTargetLength - repeatedRefs.length;
        repeatedRefs.push.apply(repeatedRefs, refs.slice(0, Math.min(refs.length, remainingLength)));
    }
    return repeatedRefs;
}

function repeatSheetQuickPlayValues(sourceValues, repeatCount) {
    const values = Array.isArray(sourceValues) ? sourceValues : [];
    const safeRepeatCount = Math.max(1, Math.round(Number(repeatCount) || 1));
    if (values.length === 0 || safeRepeatCount === 1) {
        return values.slice();
    }
    const repeatedValues = [];
    for (let repeatIndex = 0; repeatIndex < safeRepeatCount; repeatIndex++) {
        repeatedValues.push.apply(repeatedValues, values);
    }
    return repeatedValues;
}

function getSheetQuickPlayPatternNotes(pattern) {
    return (Array.isArray(pattern && pattern.bars) ? pattern.bars : []).reduce(function (allNotes, bar) {
        const barNotes = Array.isArray(bar && bar.notes) ? bar.notes : [];
        return allNotes.concat(barNotes);
    }, []);
}

function getSheetQuickPlayPatternInStep(pattern) {
    let stepOffset = 0;
    const bars = Array.isArray(pattern && pattern.bars) ? pattern.bars : [];
    for (let barIndex = 0; barIndex < bars.length; barIndex++) {
        const bar = bars[barIndex];
        const barNotes = Array.isArray(bar && bar.notes) ? bar.notes : [];
        const inControl = (Array.isArray(bar && bar.controls) ? bar.controls : [])
            .filter(function (control) {
                return control && control.type === 'in';
            })
            .sort(function (controlA, controlB) {
                return Number(controlA.stepIndex) - Number(controlB.stepIndex);
            })[0];
        if (inControl) {
            return stepOffset + Math.max(0, Math.min(barNotes.length, Number(inControl.stepIndex) || 0));
        }
        stepOffset += barNotes.length;
    }
    return null;
}

function getSheetQuickPlayPatternOutStep(pattern) {
    let stepOffset = 0;
    let matchedOutStep = null;
    const bars = Array.isArray(pattern && pattern.bars) ? pattern.bars : [];
    for (let barIndex = 0; barIndex < bars.length; barIndex++) {
        const bar = bars[barIndex];
        const barNotes = Array.isArray(bar && bar.notes) ? bar.notes : [];
        const outControl = (Array.isArray(bar && bar.controls) ? bar.controls : [])
            .filter(function (control) {
                return control && control.type === 'out';
            })
            .sort(function (controlA, controlB) {
                return Number(controlA.stepIndex) - Number(controlB.stepIndex);
            })[0];
        if (outControl) {
            matchedOutStep = stepOffset + Math.max(0, Math.min(barNotes.length - 1, Number(outControl.stepIndex) || 0));
        }
        stepOffset += barNotes.length;
    }
    return matchedOutStep;
}

function getSheetQuickPlayPickupEndStep(patternNotes, inStep, stepsPerBar) {
    const sourceLength = Array.isArray(patternNotes) ? patternNotes.length : 0;
    if (inStep === null || inStep === undefined || sourceLength <= 0 || stepsPerBar <= 0) {
        return 0;
    }
    const safeInStep = Math.max(0, Math.min(sourceLength - 1, Number(inStep) || 0));
    const pickupStartStep = Math.floor(safeInStep / stepsPerBar) * stepsPerBar;
    if (safeInStep === pickupStartStep) {
        return 0;
    }
    return Math.min(sourceLength, (Math.floor(safeInStep / stepsPerBar) + 1) * stepsPerBar);
}

function buildSheetQuickPlayPickupNotes(patternNotes, inStep, stepsPerBar) {
    const sourceNotes = Array.isArray(patternNotes) ? patternNotes : [];
    const pickupEndStep = getSheetQuickPlayPickupEndStep(sourceNotes, inStep, stepsPerBar);
    if (pickupEndStep <= 0) {
        return [];
    }
    const pickupStartStep = Math.max(0, pickupEndStep - stepsPerBar);
    const safeInStep = Math.max(0, Math.min(sourceNotes.length - 1, Number(inStep) || 0));
    return sourceNotes.slice(pickupStartStep, pickupEndStep).map(function (noteValue, noteIndex) {
        return pickupStartStep + noteIndex < safeInStep ? 'f' : noteValue;
    });
}

function buildSheetQuickPlayPickupHighlightRefs(patternRefs, inStep, stepsPerBar) {
    const sourceRefs = Array.isArray(patternRefs) ? patternRefs : [];
    const pickupEndStep = getSheetQuickPlayPickupEndStep(sourceRefs, inStep, stepsPerBar);
    if (pickupEndStep <= 0) {
        return [];
    }
    const pickupStartStep = Math.max(0, pickupEndStep - stepsPerBar);
    const safeInStep = Math.max(0, Math.min(sourceRefs.length - 1, Number(inStep) || 0));
    return sourceRefs.slice(pickupStartStep, pickupEndStep).map(function (sourceRef, noteIndex) {
        return pickupStartStep + noteIndex < safeInStep ? null : sourceRef;
    });
}

function normalizeSheetQuickPlayTargetInstrument(instrumentName) {
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
        Dundunba: ['Doundoun'],
        Dreierbass: ['Dreierbass'],
        'Bässe': ['Kenkeni', 'Sangban', 'Doundoun'],
        Shekere: ['Shekere']
    };
    return instrumentMap[instrumentName] || [];
}

function normalizeSheetQuickPlayTargetInstruments(targetInstruments) {
    return (Array.isArray(targetInstruments) ? targetInstruments : []).reduce(function (targets, targetName) {
        normalizeSheetQuickPlayTargetInstrument(targetName).forEach(function (instrumentName) {
            if (targets.indexOf(instrumentName) === -1) {
                targets.push(instrumentName);
            }
        });
        return targets;
    }, []);
}

function getSheetQuickPlayTrackNames() {
    return Object.keys(createSheetQuickPlayTrackMap());
}

function createSheetQuickPlayTrackValueMap(defaultValue) {
    return getSheetQuickPlayTrackNames().reduce(function (trackMap, instrumentName) {
        trackMap[instrumentName] = defaultValue;
        return trackMap;
    }, {});
}

function isSheetQuickPlayPlayableNote(noteValue) {
    return noteValue !== 'f' && noteValue !== null && noteValue !== undefined && noteValue !== '';
}

function sheetQuickPlaySectionHasNotes(section) {
    const trackNotes = section && section.trackNotes ? section.trackNotes : {};
    return getSheetQuickPlayTrackNames().some(function (instrumentName) {
        return Array.isArray(trackNotes[instrumentName]) &&
            trackNotes[instrumentName].some(isSheetQuickPlayPlayableNote);
    });
}

function getSheetQuickPlaySectionLength(section) {
    const trackNotes = section && section.trackNotes ? section.trackNotes : {};
    return Math.max.apply(null, getSheetQuickPlayTrackNames().map(function (instrumentName) {
        return Array.isArray(trackNotes[instrumentName]) ? trackNotes[instrumentName].length : 0;
    }).concat(0));
}

function getSheetQuickPlayFullBarLength(noteLength, stepsPerBar) {
    const safeLength = Math.max(0, Math.round(Number(noteLength) || 0));
    const safeStepsPerBar = Math.max(0, Math.round(Number(stepsPerBar) || 0));
    if (safeLength <= 0 || safeStepsPerBar <= 0) {
        return safeLength;
    }
    return Math.ceil(safeLength / safeStepsPerBar) * safeStepsPerBar;
}

function repeatSheetQuickPlayNotesToLength(notes, targetLength) {
    const sourceNotes = Array.isArray(notes) ? notes : [];
    const safeTargetLength = Math.max(0, Math.round(Number(targetLength) || 0));
    if (sourceNotes.length === 0 || safeTargetLength <= 0) {
        return sourceNotes.slice();
    }

    const repeatedNotes = [];
    while (repeatedNotes.length < safeTargetLength) {
        const remainingLength = safeTargetLength - repeatedNotes.length;
        repeatedNotes.push.apply(
            repeatedNotes,
            sourceNotes.slice(0, Math.min(sourceNotes.length, remainingLength))
        );
    }
    return repeatedNotes;
}

function getSheetQuickPlaySectionPlaybackLength(section) {
    const noteLength = getSheetQuickPlaySectionLength(section);
    const fixedLength = Math.max(0, Math.round(Number(section && section.fixedLength) || 0));
    return Math.max(noteLength, fixedLength);
}

function getSheetQuickPlayPatternSectionLength(patternLength, outStep, sectionStartStep, stepsPerBar) {
    const safePatternLength = Math.max(0, Math.round(Number(patternLength) || 0));
    const safeSectionStartStep = Math.max(0, Math.round(Number(sectionStartStep) || 0));
    const outIsInSection = outStep !== null && outStep !== undefined && Number(outStep) >= safeSectionStartStep;
    const lengthThroughOut = outIsInSection
        ? Math.max(0, Math.round(Number(outStep) || 0) - safeSectionStartStep + 1)
        : 0;
    const remainingPatternLength = Math.max(0, safePatternLength - safeSectionStartStep);
    return getSheetQuickPlayFullBarLength(
        Math.max(remainingPatternLength, lengthThroughOut),
        stepsPerBar
    );
}

function createSheetQuickPlaySection(label, labelName, runtimeKey, options) {
    const sectionOptions = options || {};
    return {
        label: label || 'Begleitung',
        labelName: labelName || label || uiText('editor.quickPlay.preview'),
        runtimeKey: runtimeKey || ('sheet-quick-play-' + Date.now()),
        isLeadIn: Boolean(sectionOptions.isLeadIn),
        fixedLength: Math.max(0, Math.round(Number(sectionOptions.fixedLength) || 0)),
        repeatCount: 1,
        trackNotes: createSheetQuickPlayTrackMap(),
        highlightSteps: [],
        trackHandModes: {},
        finalRepeatOutSteps: createSheetQuickPlayTrackValueMap(null),
        finalRepeatOutStepTypes: createSheetQuickPlayTrackValueMap(''),
        forceFinalOutAtSectionEnd: false,
        practiceTargetInstruments: []
    };
}

function doSheetQuickPlayTargetsOverlap(targetsA, targetsB) {
    const safeTargetsA = Array.isArray(targetsA) ? targetsA : [];
    const safeTargetsB = Array.isArray(targetsB) ? targetsB : [];
    return safeTargetsA.some(function (targetName) {
        return safeTargetsB.indexOf(targetName) !== -1;
    });
}

function buildSheetQuickPlayPatternGroups(preparedPatterns) {
    const groups = [];
    let currentGroup = [];
    let currentTargets = [];

    preparedPatterns.forEach(function (pattern) {
        const targetInstruments = normalizeSheetQuickPlayTargetInstruments(pattern.defaultTargets);
        if (currentGroup.length > 0 && doSheetQuickPlayTargetsOverlap(currentTargets, targetInstruments)) {
            groups.push(currentGroup);
            currentGroup = [];
            currentTargets = [];
        }
        currentGroup.push({
            pattern: pattern,
            targetInstruments: targetInstruments
        });
        targetInstruments.forEach(function (instrumentName) {
            if (currentTargets.indexOf(instrumentName) === -1) {
                currentTargets.push(instrumentName);
            }
        });
    });

    if (currentGroup.length > 0) {
        groups.push(currentGroup);
    }

    return groups;
}

function trimSheetQuickPlayStandalonePickupNotes(pickupNotes) {
    const notes = Array.isArray(pickupNotes) ? pickupNotes : [];
    const firstPlayableIndex = notes.findIndex(isSheetQuickPlayPlayableNote);
    return firstPlayableIndex >= 0 ? notes.slice(firstPlayableIndex) : [];
}

function mergeSheetQuickPlayPickupIntoHostSection(hostSection, pickupSection) {
    if (!hostSection || !pickupSection || !sheetQuickPlaySectionHasNotes(pickupSection)) {
        return;
    }

    const hostLength = getSheetQuickPlaySectionPlaybackLength(hostSection);
    const pickupLength = getSheetQuickPlaySectionLength(pickupSection);
    const stepsPerBar = getReadRhythmConfig().stepsPerBar;
    const pickupSpan = Math.max(stepsPerBar, pickupLength);
    const pickupOffset = Math.max(0, hostLength - pickupSpan);
    const pickupTrimStart = Math.max(0, pickupSpan - hostLength);

    getSheetQuickPlayTrackNames().forEach(function (instrumentName) {
        const pickupNotes = pickupSection.trackNotes[instrumentName];
        if (!Array.isArray(pickupNotes) || pickupNotes.length === 0) {
            return;
        }
        const alignedPickupNotes = pickupTrimStart > 0
            ? pickupNotes.slice(pickupTrimStart)
            : pickupNotes;
        if (alignedPickupNotes.length === 0) {
            return;
        }
        hostSection.trackNotes[instrumentName] = mergeSheetQuickPlayNotes(
            hostSection.trackNotes[instrumentName],
            alignedPickupNotes,
            pickupOffset
        );
        if (pickupSection.trackHandModes[instrumentName]) {
            hostSection.trackHandModes[instrumentName] = pickupSection.trackHandModes[instrumentName];
        }
    });
    const alignedPickupRefs = pickupTrimStart > 0
        ? pickupSection.highlightSteps.slice(pickupTrimStart)
        : pickupSection.highlightSteps;
    hostSection.highlightSteps = mergeSheetQuickPlayHighlightRefs(
        hostSection.highlightSteps,
        alignedPickupRefs,
        pickupOffset
    );
    hostSection.fixedLength = getSheetQuickPlayFullBarLength(
        getSheetQuickPlaySectionPlaybackLength(hostSection),
        stepsPerBar
    );
}

function buildSheetQuickPlayConfiguredSections(preparedPatterns) {
    const stepsPerBar = getReadRhythmConfig().stepsPerBar;
    const sections = [];
    const groups = buildSheetQuickPlayPatternGroups(preparedPatterns);

    groups.forEach(function (group, groupIndex) {
        const labels = [];
        const labelNames = [];
        const parallelAccompanimentLoops = [];
        const hasParallelPatterns = group.length > 1;
        const section = createSheetQuickPlaySection(
            'Begleitung',
            '',
            'sheet-quick-play-main-' + groupIndex
        );
        const pickupSection = createSheetQuickPlaySection(
            'Auftakt',
            '',
            'sheet-quick-play-pickup-' + groupIndex,
            { isLeadIn: true, fixedLength: stepsPerBar }
        );
        const hostSection = sections.slice().reverse().find(sheetQuickPlaySectionHasNotes);
        const hasHostSection = Boolean(hostSection);
        group.forEach(function (groupEntry) {
            const pattern = groupEntry.pattern;
            const targetInstruments = groupEntry.targetInstruments;
            const patternRepeatCount = Math.max(
                1,
                Math.round(Number(pattern.quickPlaySectionRepeatCount) || 1)
            );
            if (!hasParallelPatterns) {
                section.repeatCount = Math.max(section.repeatCount, patternRepeatCount);
            }
            let patternNotes = getSheetQuickPlayPatternNotes(pattern);
            let patternHighlightRefs = getSheetQuickPlayPatternHighlightRefs(pattern);
            const patternUnitLength = patternNotes.length;
            if (hasParallelPatterns && patternRepeatCount > 1) {
                patternNotes = repeatSheetQuickPlayValues(patternNotes, patternRepeatCount);
                patternHighlightRefs = repeatSheetQuickPlayValues(patternHighlightRefs, patternRepeatCount);
            }
            const inStep = getSheetQuickPlayPatternInStep(pattern);
            const sourceOutStep = getSheetQuickPlayPatternOutStep(pattern);
            const outStep = sourceOutStep !== null &&
                    sourceOutStep !== undefined &&
                    hasParallelPatterns &&
                    patternRepeatCount > 1
                ? sourceOutStep + (patternUnitLength * (patternRepeatCount - 1))
                : sourceOutStep;
            const pickupEndStep = getSheetQuickPlayPickupEndStep(patternNotes, inStep, stepsPerBar);
            let pickupNotes = buildSheetQuickPlayPickupNotes(patternNotes, inStep, stepsPerBar);
            let mainNotes = pickupEndStep > 0 ? patternNotes.slice(pickupEndStep) : patternNotes.slice();
            let pickupHighlightRefs = buildSheetQuickPlayPickupHighlightRefs(
                patternHighlightRefs,
                inStep,
                stepsPerBar
            );
            let mainHighlightRefs = pickupEndStep > 0
                ? patternHighlightRefs.slice(pickupEndStep)
                : patternHighlightRefs.slice();
            let sectionStartStep = pickupEndStep > 0 ? pickupEndStep : 0;
            const label = pattern.labelType || pattern.label || 'Begleitung';
            const labelName = pattern.labelName || pattern.name || label;

            if (pickupEndStep > 0 && !hasHostSection && group.length === 1) {
                const safeInStep = Math.max(0, Math.min(patternNotes.length - 1, Number(inStep) || 0));
                pickupNotes = [];
                mainNotes = patternNotes.slice(safeInStep);
                pickupHighlightRefs = [];
                mainHighlightRefs = patternHighlightRefs.slice(safeInStep);
                sectionStartStep = safeInStep;
            } else if (pickupEndStep > 0 && !hasHostSection) {
                sectionStartStep = Math.max(0, pickupEndStep - stepsPerBar);
                mainNotes = pickupNotes.concat(mainNotes);
                mainHighlightRefs = pickupHighlightRefs.concat(mainHighlightRefs);
                pickupNotes = [];
                pickupHighlightRefs = [];
            }

            section.fixedLength = Math.max(
                section.fixedLength,
                getSheetQuickPlayPatternSectionLength(
                    patternNotes.length,
                    outStep,
                    sectionStartStep,
                    stepsPerBar
                )
            );

            if (label && labels.indexOf(label) === -1) {
                labels.push(label);
            }
            if (labelName && labelNames.indexOf(labelName) === -1) {
                labelNames.push(labelName);
            }
            if (label === 'Begleitung' && mainNotes.length > 0) {
                parallelAccompanimentLoops.push({
                    targetInstruments: targetInstruments.slice(),
                    notes: mainNotes.slice(),
                    highlightRefs: mainHighlightRefs.slice()
                });
            }

            if (pickupHighlightRefs.length > 0) {
                pickupSection.highlightSteps = mergeSheetQuickPlayHighlightRefs(
                    pickupSection.highlightSteps,
                    pickupHighlightRefs,
                    0
                );
            }
            if (mainHighlightRefs.length > 0) {
                section.highlightSteps = mergeSheetQuickPlayHighlightRefs(
                    section.highlightSteps,
                    mainHighlightRefs,
                    0
                );
            }

            targetInstruments.forEach(function (instrumentName) {
                if (!section.trackNotes[instrumentName]) {
                    return;
                }
                const shouldIgnoreOutForAccompanimentLoop = label === 'Begleitung';
                if (!shouldIgnoreOutForAccompanimentLoop &&
                        outStep !== null &&
                        outStep !== undefined &&
                        Number(outStep) >= sectionStartStep) {
                    section.finalRepeatOutSteps[instrumentName] = Math.max(
                        0,
                        Math.round(Number(outStep) || 0) - sectionStartStep
                    );
                    section.finalRepeatOutStepTypes[instrumentName] = label || '';
                    section.forceFinalOutAtSectionEnd = true;
                }
                if (pickupNotes.length > 0) {
                    pickupSection.trackNotes[instrumentName] = mergeSheetQuickPlayNotes(
                        pickupSection.trackNotes[instrumentName],
                        pickupNotes,
                        0
                    );
                }
                if (mainNotes.length > 0) {
                    section.trackNotes[instrumentName] = mergeSheetQuickPlayNotes(
                        section.trackNotes[instrumentName],
                        mainNotes,
                        0
                    );
                }
            });
        });

        section.label = labels.indexOf('Begleitung') !== -1 ? 'Begleitung' : (labels[0] || 'Begleitung');
        section.labelName = labelNames.join(' + ') || uiText('editor.quickPlay.preview');
        pickupSection.label = section.label;
        pickupSection.labelName = uiText('editor.quickPlay.pickupLabel', {
            name: section.labelName
        });

        if (sheetQuickPlaySectionHasNotes(pickupSection)) {
            if (hostSection) {
                mergeSheetQuickPlayPickupIntoHostSection(hostSection, pickupSection);
            } else {
                getSheetQuickPlayTrackNames().forEach(function (instrumentName) {
                    pickupSection.trackNotes[instrumentName] = trimSheetQuickPlayStandalonePickupNotes(
                        pickupSection.trackNotes[instrumentName]
                    );
                });
                pickupSection.fixedLength = 0;
                if (sheetQuickPlaySectionHasNotes(pickupSection)) {
                    sections.push(pickupSection);
                }
            }
        }

        if (sheetQuickPlaySectionHasNotes(section)) {
            section.fixedLength = Math.max(
                section.fixedLength,
                getSheetQuickPlayFullBarLength(
                    getSheetQuickPlaySectionLength(section),
                    stepsPerBar
                )
            );
            parallelAccompanimentLoops.forEach(function (loopEntry) {
                const repeatedNotes = repeatSheetQuickPlayNotesToLength(
                    loopEntry.notes,
                    section.fixedLength
                );
                const repeatedHighlightRefs = repeatSheetQuickPlayHighlightRefsToLength(
                    loopEntry.highlightRefs,
                    section.fixedLength
                );
                section.highlightSteps = mergeSheetQuickPlayHighlightRefs(
                    section.highlightSteps,
                    repeatedHighlightRefs,
                    0
                );
                loopEntry.targetInstruments.forEach(function (instrumentName) {
                    if (section.trackNotes[instrumentName]) {
                        section.trackNotes[instrumentName] = repeatedNotes.slice();
                    }
                });
            });
            sections.push(section);
        }
    });

    return sections;
}

function createSheetPatternMoveOverlayButton(range, direction, disabled) {
    const bounds = getSheetBarBounds(range.startBarIndex);
    const isUp = direction === 'up';
    const buttonX = bounds.x + (isUp ? 1 : 16);
    const buttonY = bounds.y + 7;
    const groupEl = s.g().attr({
        class: 'sheet-quick-play-overlay sheet-pattern-move-overlay' + (disabled ? ' is-disabled' : ''),
        cursor: disabled ? 'default' : 'pointer',
        'aria-label': uiText(isUp ? 'editor.movePatternForward' : 'editor.movePatternBackward'),
        'aria-disabled': disabled ? 'true' : 'false'
    });
    groupEl.add(s.rect(buttonX, buttonY, 13, 14).attr({
        fill: disabled ? '#f2f0eb' : '#fffaf0',
        stroke: disabled ? '#aaa49a' : '#b77d43',
        strokeWidth: 1,
        rx: 3,
        ry: 3
    }));
    groupEl.add(s.text(buttonX + 6.5, buttonY + 10.5, isUp ? '←' : '→').attr({
        fill: disabled ? '#aaa49a' : '#70431f',
        fontSize: 11,
        fontFamily: 'sans-serif',
        fontWeight: 'bold',
        textAnchor: 'middle',
        pointerEvents: 'none'
    }));
    if (!disabled) {
        groupEl.click(function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            moveSheetPatternByDirection(range.id, direction);
        });
    }
    return groupEl;
}

function renderSheetQuickPlaySelectors(readResult) {
    const previouslySelectedBarIndexes = new Set();
    const previouslySelectedIds = new Set(sheetQuickPlayState.selectedPatternIds);
    sheetQuickPlayState.patternLibrary.forEach(function (pattern) {
        if (!pattern || !previouslySelectedIds.has(pattern.id) || !Array.isArray(pattern.bars)) {
            return;
        }
        pattern.bars.forEach(function (bar) {
            if (bar && Number.isFinite(Number(bar.sourceBarIndex))) {
                previouslySelectedBarIndexes.add(Number(bar.sourceBarIndex));
            }
        });
    });

    stopSheetQuickPlay();
    removeCanvasElements('.sheet-quick-play-overlay');
    const syncOptions = buildCurrentTimelineSyncOptions();
    const resolvedReadResult = readResult || callPHPScript_lesen(zeilenAnzahl, {
        showAlert: false,
        updateQuickPlaySelectors: false,
        logResults: false
    });
    sheetPatternMoveState.ranges = getSheetPatternMoveRanges(resolvedReadResult);
    syncTimelineStateFromReadResultIfNeeded(resolvedReadResult, syncOptions);
    const sourcePatterns = Array.isArray(timelineState.sourcePatterns)
        ? timelineState.sourcePatterns.slice()
        : [];
    const coveredBarIndexes = new Set();
    sourcePatterns.forEach(function (pattern) {
        (Array.isArray(pattern && pattern.bars) ? pattern.bars : []).forEach(function (bar) {
            if (bar && Number.isFinite(Number(bar.sourceBarIndex))) {
                coveredBarIndexes.add(Number(bar.sourceBarIndex));
            }
        });
    });

    const draftBarStates = [];
    const draftPatterns = [];
    const rhythmBars = Array.isArray(resolvedReadResult && resolvedReadResult.rhythmBars)
        ? resolvedReadResult.rhythmBars
        : [];
    rhythmBars.forEach(function (bar) {
        if (!bar || coveredBarIndexes.has(Number(bar.index))) {
            return;
        }
        const barNotes = Array.isArray(bar.notes) ? bar.notes.slice() : [];
        const hasNotes = barNotes.some(isSheetQuickPlayPlayableNote);
        const hasContent = hasNotes ||
            Boolean(String(bar.instrument || '').trim()) ||
            Boolean(String(bar.label || '').trim()) ||
            (Array.isArray(bar.controls) && bar.controls.length > 0);
        if (!hasContent) {
            return;
        }

        const sourceInstrumentName = String(bar.effectiveInstrument || bar.instrument || '').trim();
        const targetInstruments = normalizeSheetQuickPlayTargetInstrument(sourceInstrumentName);
        const canPlayDraft = hasNotes && targetInstruments.length > 0;
        draftBarStates.push({
            sourceBarIndex: Number(bar.index),
            canPlay: canPlayDraft
        });
        if (!canPlayDraft) {
            return;
        }

        const draftId = 'sheet-quick-play-draft-bar-' + Number(bar.index);
        const labelText = String(bar.effectiveLabel || bar.label || '').trim();
        const labelInfo = typeof getPlayerLabelInfo === 'function'
            ? getPlayerLabelInfo(labelText)
            : { type: '', raw: '' };
        draftPatterns.push({
            id: draftId,
            sourceKey: draftId,
            instrument: typeof normalizePatternInstrumentName === 'function'
                ? normalizePatternInstrumentName(sourceInstrumentName)
                : sourceInstrumentName,
            sourceInstrument: sourceInstrumentName,
            labelType: labelInfo.type || 'Begleitung',
            labelName: labelInfo.raw || uiText('arrangement.bar', { number: Number(bar.index) }),
            name: getChooserDisplayText(sourceInstrumentName, 'instrument') + ' / ' +
                uiText('arrangement.bar', { number: Number(bar.index) }),
            defaultTargets: targetInstruments.slice(),
            isQuickPlayDraft: true,
            bars: [{
                sourceBarIndex: Number(bar.index),
                patternSourceKey: draftId,
                patternBarIndex: 0,
                label: labelInfo.type || 'Begleitung',
                repeat: {
                    start: cloneTimelineRepeatMarkers(bar.repeat && bar.repeat.start),
                    end: cloneTimelineRepeatMarkers(bar.repeat && bar.repeat.end)
                },
                controls: Array.isArray(bar.controls) ? bar.controls.map(function (control) {
                    return Object.assign({}, control);
                }) : [],
                notes: barNotes
            }]
        });
    });

    sheetQuickPlayState.patternLibrary = sourcePatterns.concat(draftPatterns);

    const availablePatternIds = new Set(sheetQuickPlayState.patternLibrary.map(function (pattern) {
        return pattern.id;
    }));
    sheetQuickPlayState.selectedPatternIds = sheetQuickPlayState.patternLibrary
        .filter(function (pattern) {
            if (!pattern) {
                return false;
            }
            if (availablePatternIds.has(pattern.id) && previouslySelectedIds.has(pattern.id)) {
                return true;
            }
            return Array.isArray(pattern.bars) && pattern.bars.some(function (bar) {
                return bar && previouslySelectedBarIndexes.has(Number(bar.sourceBarIndex));
            });
        })
        .map(function (pattern) {
            return pattern.id;
        });

    const playableDraftBarIndexes = new Set(draftPatterns.map(function (pattern) {
        return Number(pattern.bars[0].sourceBarIndex);
    }));
    draftBarStates.forEach(function (draftBarState) {
        if (playableDraftBarIndexes.has(draftBarState.sourceBarIndex)) {
            return;
        }
        const bounds = getSheetBarBounds(draftBarState.sourceBarIndex);
        s.rect(bounds.x + 1, bounds.y + 25, 14, 14).attr({
            class: 'sheet-quick-play-overlay sheet-quick-play-hitarea is-disabled',
            fill: '#f2f0eb',
            opacity: 0.62,
            stroke: '#aaa49a',
            strokeWidth: 1,
            rx: 3,
            ry: 3,
            cursor: 'not-allowed',
            'aria-disabled': 'true'
        });
    });

    sheetQuickPlayState.patternLibrary.forEach(function (pattern) {
        if (!pattern || !Array.isArray(pattern.bars)) {
            return;
        }
        pattern.bars.forEach(function (bar) {
            const bounds = getSheetBarBounds(bar.sourceBarIndex);
            const highlightEl = s.rect(bounds.x, bounds.y, bounds.width, bounds.height).attr({
                class: 'sheet-quick-play-overlay sheet-quick-play-highlight',
                fill: '#f6b35f',
                opacity: 0,
                stroke: '#c5761e',
                strokeWidth: 1.2,
                rx: 8,
                ry: 8,
                pointerEvents: 'none',
                'data-pattern-id': pattern.id
            });
            const hitEl = s.rect(bounds.x + 1, bounds.y + 25, 14, 14).attr({
                class: 'sheet-quick-play-overlay sheet-quick-play-hitarea',
                fill: '#fffaf0',
                opacity: 0.92,
                stroke: '#b77d43',
                strokeWidth: 1.2,
                rx: 3,
                ry: 3,
                cursor: 'pointer',
                'data-pattern-id': pattern.id
            });
            hitEl.click(function (event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                selectSheetQuickPlayPattern(pattern.id);
            });
            if (typeof highlightEl.insertBefore === 'function') {
                highlightEl.insertBefore(hitEl);
            }
        });
    });

    sheetPatternMoveState.ranges.forEach(function (range, rangeIndex) {
        createSheetPatternMoveOverlayButton(range, 'up', rangeIndex === 0);
        createSheetPatternMoveOverlayButton(
            range,
            'down',
            rangeIndex === sheetPatternMoveState.ranges.length - 1
        );
    });

    rebuildSheetQuickPlayNoteElementMap();
    updateSheetQuickPlaySelectionClasses();
    window.requestAnimationFrame(positionSheetQuickPlayControls);
}

function isSheetQuickPlayOverlayNode(node) {
    const element = node && node.nodeType === 1 ? node : (node ? node.parentElement : null);
    return Boolean(element && (
        element.matches('.sheet-quick-play-overlay') ||
        element.closest('.sheet-quick-play-overlay')
    ));
}

function isSheetQuickPlayEditorNode(node, includeDescendants) {
    const element = node && node.nodeType === 1 ? node : (node ? node.parentElement : null);
    if (!element || isSheetQuickPlayOverlayNode(element)) {
        return false;
    }
    const editorSelector = '.shp, .instrument-chooser, .function-chooser';
    if (element.matches(editorSelector) || element.closest(editorSelector)) {
        return true;
    }
    return Boolean(includeDescendants && element.querySelector && element.querySelector(editorSelector));
}

function sheetQuickPlayMutationAffectsEditor(mutation) {
    if (!mutation) {
        return false;
    }
    if (mutation.type === 'attributes' || mutation.type === 'characterData') {
        return isSheetQuickPlayEditorNode(mutation.target, mutation.type === 'attributes');
    }
    if (mutation.type !== 'childList') {
        return false;
    }
    if (isSheetQuickPlayEditorNode(mutation.target, false)) {
        return true;
    }
    return Array.prototype.some.call(mutation.addedNodes || [], function (node) {
        return isSheetQuickPlayEditorNode(node, true);
    }) || Array.prototype.some.call(mutation.removedNodes || [], function (node) {
        return isSheetQuickPlayEditorNode(node, true);
    });
}

function scheduleSheetQuickPlaySelectorRefresh(delay) {
    sheetQuickPlayRefreshPending = true;
    if (sheetQuickPlayEditorPointerActive) {
        return;
    }
    if (sheetQuickPlayRefreshTimer !== null) {
        window.clearTimeout(sheetQuickPlayRefreshTimer);
    }
    sheetQuickPlayRefreshTimer = window.setTimeout(function () {
        sheetQuickPlayRefreshTimer = null;
        if (sheetQuickPlayEditorPointerActive || !sheetQuickPlayRefreshPending) {
            return;
        }
        sheetQuickPlayRefreshPending = false;
        renderSheetQuickPlaySelectors();
    }, Math.max(0, Number(delay) || 0));
}

function initializeSheetQuickPlayLiveRefresh() {
    if (sheetQuickPlayLiveRefreshInitialized || !s || !s.node || typeof MutationObserver === 'undefined') {
        return;
    }
    sheetQuickPlayLiveRefreshInitialized = true;
    sheetQuickPlayMutationObserver = new MutationObserver(function (mutations) {
        if (!mutations.some(sheetQuickPlayMutationAffectsEditor)) {
            return;
        }
        scheduleSheetQuickPlaySelectorRefresh(120);
    });
    sheetQuickPlayMutationObserver.observe(s.node, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['transform', 'text', 'data-notes', 'display']
    });

    const beginEditorPointerInteraction = function (event) {
        if (isSheetQuickPlayOverlayNode(event && event.target)) {
            return;
        }
        sheetQuickPlayEditorPointerActive = true;
        if (sheetQuickPlayRefreshTimer !== null) {
            window.clearTimeout(sheetQuickPlayRefreshTimer);
            sheetQuickPlayRefreshTimer = null;
            sheetQuickPlayRefreshPending = true;
        }
    };
    const finishEditorPointerInteraction = function () {
        if (!sheetQuickPlayEditorPointerActive) {
            return;
        }
        sheetQuickPlayEditorPointerActive = false;
        if (sheetQuickPlayRefreshPending) {
            scheduleSheetQuickPlaySelectorRefresh(60);
        }
    };

    s.node.addEventListener('pointerdown', beginEditorPointerInteraction, true);
    s.node.addEventListener('mousedown', beginEditorPointerInteraction, true);
    s.node.addEventListener('touchstart', beginEditorPointerInteraction, true);
    window.addEventListener('pointerup', finishEditorPointerInteraction, true);
    window.addEventListener('pointercancel', finishEditorPointerInteraction, true);
    window.addEventListener('mouseup', finishEditorPointerInteraction, true);
    window.addEventListener('touchend', finishEditorPointerInteraction, true);
    window.addEventListener('touchcancel', finishEditorPointerInteraction, true);
}

function buildSheetQuickPlayPayload() {
    const selectedPatterns = getSheetQuickPlaySelectedPatterns();
    if (selectedPatterns.length === 0) {
        return null;
    }
    const preparedPatterns = selectedPatterns.map(buildSheetQuickPlayPreparedPattern);
    const entries = preparedPatterns.map(function (pattern, patternIndex) {
        return {
            id: 'sheet-quick-play-' + patternIndex,
            blockId: 'sheet-quick-play-block-' + patternIndex,
            parallelGroupId: '',
            overlayRepeatIndex: null,
            patternId: pattern.id,
            patternSourceKey: pattern.sourceKey,
            handMode: '',
            sectionTempo: null,
            targetInstruments: Array.isArray(pattern.defaultTargets) ? pattern.defaultTargets.slice() : []
        };
    });
    const previousTempo = timelineState.tempo;
    timelineState.tempo = getSheetQuickPlayTempo();
    const payload = buildTimelinePlayerPayload(preparedPatterns, entries);
    timelineState.tempo = previousTempo;
    if (Array.isArray(payload) && payload[0]) {
        const configuredSections = buildSheetQuickPlayConfiguredSections(preparedPatterns);
        sheetQuickPlayState.highlightSectionsByRuntimeKey = configuredSections.reduce(function (sectionsByKey, section) {
            if (section && section.runtimeKey) {
                sectionsByKey[String(section.runtimeKey)] = section;
            }
            return sectionsByKey;
        }, {});
        payload[0].PracticeMode = true;
        payload[0].TimelineLoop = true;
        payload[0].TimelineLoopCount = 'loop';
        payload[0].SheetQuickPlayMode = true;
        payload[0].SheetQuickPlayExternalScheduler = isMobilePracticeViewport();
        payload[0].Tempo = getSheetQuickPlayTempo();
        payload[0].PracticeSections = configuredSections.map(function (section) {
            const playerSection = Object.assign({}, section);
            delete playerSection.highlightSteps;
            return playerSection;
        });
    } else {
        sheetQuickPlayState.highlightSectionsByRuntimeKey = {};
    }
    window.lastSheetQuickPlayPayload = payload;
    return payload;
}

function getSheetQuickPlayPreparationSignature() {
    return sheetQuickPlayState.selectedPatternIds.join('|') + '@' + String(getSheetQuickPlayTempo());
}

function prepareSheetQuickPlayPlayer() {
    sheetQuickPlayState.preparationTimer = null;
    if (!isMobilePracticeViewport()) {
        return;
    }
    const frameEl = document.getElementById('sheetQuickPlayFrame');
    const signature = getSheetQuickPlayPreparationSignature();
    if (!frameEl || sheetQuickPlayState.selectedPatternIds.length === 0) {
        if (frameEl) {
            frameEl.src = 'about:blank';
        }
        sheetQuickPlayState.preparedSignature = '';
        sheetQuickPlayState.frameReady = false;
        updateSheetQuickPlayButtonAvailability();
        return;
    }

    const payload = buildSheetQuickPlayPayload();
    if (!payload) {
        sheetQuickPlayState.preparedSignature = '';
        sheetQuickPlayState.frameReady = false;
        updateSheetQuickPlayButtonAvailability();
        return;
    }

    sheetQuickPlayState.preparedSignature = signature;
    sheetQuickPlayState.frameReady = false;
    frameEl.onload = null;
    updateSheetQuickPlayButtonAvailability();
    openAudioTestFrame(payload, frameEl.name || 'sheetQuickPlayFrame');
}

function scheduleSheetQuickPlayPreparation(delay) {
    if (!isMobilePracticeViewport()) {
        return;
    }
    if (sheetQuickPlayState.preparationTimer !== null) {
        window.clearTimeout(sheetQuickPlayState.preparationTimer);
    }
    sheetQuickPlayState.frameReady = false;
    sheetQuickPlayState.preparedSignature = '';
    updateSheetQuickPlayButtonAvailability();
    sheetQuickPlayState.preparationTimer = window.setTimeout(
        prepareSheetQuickPlayPlayer,
        Math.max(0, Number(delay) || 120)
    );
}

function requestSheetQuickPlayStart(frameEl) {
    let attemptsLeft = 80;
    function tryStart() {
        if (!sheetQuickPlayState.isPlaying) {
            return;
        }
        const frameWindow = frameEl && frameEl.contentWindow;
        const frameDocument = frameEl && frameEl.contentDocument;
        const playButtonEl = frameDocument ? frameDocument.querySelector('[data-playing]') : null;
        if (playButtonEl && !playButtonEl.disabled) {
            if (playButtonEl.dataset.playing !== 'true') {
                playButtonEl.click();
            }
            return;
        }
        attemptsLeft -= 1;
        if (frameWindow && attemptsLeft > 0) {
            window.setTimeout(tryStart, 100);
        }
    }
    tryStart();
}

function clearSheetQuickPlaySchedulerPump() {
    if (sheetQuickPlayState.schedulerTimer !== null) {
        window.clearInterval(sheetQuickPlayState.schedulerTimer);
        sheetQuickPlayState.schedulerTimer = null;
    }
}

function startSheetQuickPlaySchedulerPump() {
    clearSheetQuickPlaySchedulerPump();
    if (!isMobilePracticeViewport()) {
        return;
    }

    const pumpScheduler = function () {
        const frameEl = document.getElementById('sheetQuickPlayFrame');
        const pumpFromParent = frameEl && frameEl.contentWindow
            ? frameEl.contentWindow.pumpEmbeddedPlaybackSchedulerFromParent
            : null;
        if (typeof pumpFromParent !== 'function' || pumpFromParent() === false) {
            clearSheetQuickPlaySchedulerPump();
        }
    };

    pumpScheduler();
    if (sheetQuickPlayState.isPlaying) {
        sheetQuickPlayState.schedulerTimer = window.setInterval(pumpScheduler, 20);
    }
}

function stopSheetQuickPlay(options) {
    const stopOptions = options && typeof options === 'object' ? options : {};
    const preservePreparedPlayer = Boolean(stopOptions.preservePreparedPlayer);
    clearSheetQuickPlaySchedulerPump();
    const frameEl = document.getElementById('sheetQuickPlayFrame');
    if (frameEl && frameEl.contentDocument) {
        const stopFromParent = frameEl.contentWindow && frameEl.contentWindow.stopEmbeddedPlaybackFromParent;
        if (typeof stopFromParent === 'function') {
            stopFromParent();
        } else {
            const playButtonEl = frameEl.contentDocument.querySelector('[data-playing]');
            if (playButtonEl && playButtonEl.dataset.playing === 'true') {
                playButtonEl.click();
            }
        }
        if (!preservePreparedPlayer) {
            frameEl.src = 'about:blank';
        }
    }
    if (sheetQuickPlayState.preparationTimer !== null) {
        window.clearTimeout(sheetQuickPlayState.preparationTimer);
        sheetQuickPlayState.preparationTimer = null;
    }
    clearSheetQuickPlayHighlights();
    if (!preservePreparedPlayer) {
        sheetQuickPlayState.highlightSectionsByRuntimeKey = {};
        sheetQuickPlayState.preparedSignature = '';
        sheetQuickPlayState.frameReady = false;
    }
    setSheetQuickPlayButtonState(false);
}

function startSheetQuickPlay() {
    if (isMobilePracticeViewport()) {
        const frameEl = document.getElementById('sheetQuickPlayFrame');
        const signature = getSheetQuickPlayPreparationSignature();
        const startFromParent = frameEl && frameEl.contentWindow
            ? frameEl.contentWindow.startEmbeddedPlaybackFromParent
            : null;
        if (sheetQuickPlayState.frameReady &&
                sheetQuickPlayState.preparedSignature === signature &&
                typeof startFromParent === 'function' &&
                startFromParent()) {
            setSheetQuickPlayButtonState(true);
            return;
        }
        scheduleSheetQuickPlayPreparation(0);
        return;
    }
    renderSheetQuickPlaySelectors();
    const payload = buildSheetQuickPlayPayload();
    const frameEl = document.getElementById('sheetQuickPlayFrame');
    if (!payload || !frameEl) {
        alert(uiText('editor.quickPlay.selectPattern'));
        return;
    }
    setSheetQuickPlayButtonState(true);
    frameEl.onload = function () {
        requestSheetQuickPlayStart(frameEl);
    };
    openAudioTestFrame(payload, frameEl.name || 'sheetQuickPlayFrame');
}

function toggleSheetQuickPlay() {
    if (sheetQuickPlayState.isPlaying) {
        stopSheetQuickPlay({ preservePreparedPlayer: isMobilePracticeViewport() });
        return;
    }
    startSheetQuickPlay();
}

initializeSheetQuickPlayLiveRefresh();

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
        const mobileReadOnlyViewport = typeof isMobilePracticeViewport === 'function' &&
            isMobilePracticeViewport() &&
            !(typeof isMobileLandscapeViewport === 'function' && isMobileLandscapeViewport());
        deleteButtonEl.disabled = mobileReadOnlyViewport || getSheetPageCount(zeilenAnzahl) <= 1;
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
    setRhythmTitle(snapshot.title || uiText('editor.untitled'));
    syncStateAfterHistoryRestore(syncOptions || buildCurrentTimelineSyncOptions());
    renderSheetQuickPlaySelectors();
    if (isMobilePracticeViewport()) {
        renderMobileSheetView();
    }
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
    const shouldDelete = confirm(uiText('editor.deleteLastPageConfirm'));
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
        s.text(sheetWidth - 34, pageOffsetY + sheetPageHeight - 34, uiText('score.pageNumber', {
            current: pageIndex + 1,
            total: pageCount
        })).attr({
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

function getCurrentTupletDisplay() {
    return rhythm === "binaer"
        ? { letter: "T", label: uiText('score.tuplet.triplet'), type: "triplet" }
        : { letter: "Q", label: uiText('score.tuplet.quartuplet'), type: "quartuplet" };
}

function updateTupletPaletteSymbol(symbol) {
    if (!symbol || typeof symbol.select !== "function") {
        return;
    }
    const display = getCurrentTupletDisplay();
    const label = symbol.select(".tuplet-palette-letter");
    if (label) {
        label.attr({ text: display.letter });
    }
    symbol.attr({ "data-tuplet": display.type });
}

function createTripletPaletteSymbol(paper, centerX, centerY) {
    const dotY = centerY;
    const display = getCurrentTupletDisplay();
    const groupParts = [
        paper.circle(centerX - 6, dotY, 2.5),
        paper.circle(centerX, dotY, 2.5),
        paper.circle(centerX + 6, dotY, 2.5),
        paper.text(centerX, dotY + 15, display.letter).attr({
            class: "tuplet-palette-letter",
            'font-size': 11,
            'font-family': 'sans-serif',
            'font-weight': 'bold',
            'text-anchor': 'middle'
        }),
        paper.rect(centerX - 10, dotY - 6, 20, 26).attr({ opacity: 0.001 })
    ];

    return paper.g.apply(paper, groupParts).attr({
        id: "triplet_palette",
        'data-tuplet': display.type,
        'data-notes': "tone,tone,slap"
    });
}

function createTupletNoteShape(paper, noteType, centerX, centerY) {
    const parts = [];
    const type = noteType || "tone";

    if (type === "bass") {
        parts.push(paper.rect(centerX - 6, centerY - 6, 12, 12));
    } else if (type === "slap" || type === "slap_muffled") {
        parts.push(paper.rect(centerX - 7, centerY - 7, 14, 14).attr({ opacity: 0.001 }));
        parts.push(paper.line(centerX - 7, centerY + 7, centerX + 7, centerY - 7).attr({ stroke: "black", strokeWidth: 2 }));
        parts.push(paper.line(centerX - 7, centerY - 7, centerX + 7, centerY + 7).attr({ stroke: "black", strokeWidth: 2 }));
        if (type === "slap_muffled") {
            parts.push(paper.line(centerX - 8, centerY + 11, centerX + 8, centerY + 11).attr({ stroke: "black", strokeWidth: 2 }));
        }
    } else {
        parts.push(paper.circle(centerX, centerY, 7));
        if (type === "tone_muffled") {
            parts.push(paper.line(centerX - 8, centerY + 11, centerX + 8, centerY + 11).attr({ stroke: "black", strokeWidth: 2 }));
        }
    }

    return paper.g.apply(paper, parts);
}

function createTripletSymbol(paper, centerX, centerY, options) {
    const settings = options || {};
    const spacing = Number(settings.spacing) || 24;
    const tupletType = settings.type || "triplet";
    const noteTypes = Array.isArray(settings.notes) && settings.notes.length
        ? settings.notes
        : ["tone", "tone", "slap"];
    const labelText = settings.label === false
        ? ""
        : (settings.label || uiText(tupletType === "quartuplet" ? 'score.tuplet.quartuplet' : 'score.tuplet.triplet'));
    const groupParts = [];
    const noteY = centerY;
    const positionOffsets = Array.isArray(settings.positionOffsets) && settings.positionOffsets.length
        ? settings.positionOffsets
        : noteTypes.map(function (_, index) { return spacing * index; });
    const firstX = settings.anchor === "start"
        ? centerX
        : centerX - ((noteTypes.length - 1) * spacing) / 2;
    const lastOffset = positionOffsets[positionOffsets.length - 1] || 0;
    const labelX = firstX + lastOffset / 2;

    noteTypes.forEach(function (noteType, index) {
        groupParts.push(createTupletNoteShape(paper, noteType, firstX + (positionOffsets[index] || 0), noteY));
    });

    if (labelText) {
        groupParts.push(paper.text(labelX, noteY + 24, labelText).attr({
            'font-size': 10,
            'font-family': 'sans-serif',
            'text-anchor': 'middle'
        }));
    }

    const hitbox = paper.rect(firstX - 11, noteY - 14, Math.max(20, lastOffset + 22), 44).attr({ opacity: 0.001 });
    groupParts.push(hitbox);

    return paper.g.apply(paper, groupParts).attr({
        id: tupletType,
        'data-tuplet': tupletType,
        'data-notes': noteTypes.join(",")
    });
}

function getTupletSymbolSpacing(tupletType) {
    const lineStep = getTupletNotationStepX();
    const snapStep = Number(gridSizeX) || lineStep || 24;
    if (tupletType === "quartuplet") {
        return lineStep * 0.75;
    }
    return lineStep * (4 / 3);
}

function getTupletNotationStepX() {
    if (rhythm === "binaer") {
        return 850 / 34;
    }
    if (rhythm === "neunaer") {
        return 850 / 20;
    }
    return 850 / 26;
}

function getTupletPositionOffsets(tupletType, noteCount, spacing) {
    const count = Math.max(1, Number(noteCount) || 1);
    if (tupletType === "quartuplet") {
        const step = getTupletNotationStepX() || spacing || 24;
        return Array.from({ length: count }, function (_, index) {
            return (step * 0.75) * index;
        });
    }
    return Array.from({ length: count }, function (_, index) {
        return spacing * index;
    });
}

function createTupletElementFromPalette(noteTypes, tupletType) {
    const type = tupletType || getCurrentTupletDisplay().type;
    const display = type === "quartuplet"
        ? { label: uiText('score.tuplet.quartuplet'), spacing: getTupletSymbolSpacing(type) }
        : { label: uiText('score.tuplet.triplet'), spacing: getTupletSymbolSpacing(type) };
    const insertX = getPaletteInsertReferenceX();
    const insertY = paletteOriginY + 292 + paletteOffsetY;
    const element = createTripletSymbol(s, insertX, insertY, {
        type: type,
        notes: noteTypes,
        label: display.label,
        spacing: display.spacing,
        positionOffsets: getTupletPositionOffsets(type, noteTypes.length, display.spacing),
        anchor: "start"
    }).attr({
        class: "shp",
        id: type
    });
    element.drag(move, sel_start, stop_m);
    return element;
}

// Kartusche
paletteFrame = s.rect(paletteOriginX - 12, paletteOriginY - 14, 26, 330, 3, 3).attr({ fill: "lightgrey", stroke: "black", strokeWidth: 0.5 });

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

// Triole
x = paletteOriginX + 1;
y = paletteOriginY + 292;
Triplet = createTripletPaletteSymbol(s, x, y);

// Legende schreiben
function addLegendEntry(symbol, label, symbolX, symbolY, labelOffsetX, labelOffsetY, legendOffsetX, legendOffsetY) {
    const shiftedSymbolX = symbolX + legendOffsetX;
    const shiftedSymbolY = symbolY + (Number(legendOffsetY) || 0);
    const legendClone = symbol.clone();
    s.append(legendClone);
    legendClone.attr({ id: "basis", transform: "t" + shiftedSymbolX + "," + shiftedSymbolY });
    legendClone.addClass("legend-entry");
    if (label) {
        s.text(shiftedSymbolX + labelOffsetX, shiftedSymbolY + labelOffsetY, label).attr({
            id: "basis",
            class: "legend-entry",
            'font-size': 15,
            'font-family': 'sans-serif'
        });
    }
    return legendClone;
}

function renderLegend(initialChooserX) {
    const legendAnchorX = Number.isFinite(initialChooserX) ? initialChooserX : 125;
    const toneLegendReferenceLeft = 92 + ton.getBBox().x;
    const legendOffsetX = legendAnchorX - toneLegendReferenceLeft;
    const legendOffsetY = getSheetPageOffsetY(getSheetPageCount(zeilenAnzahl) - 1);
    const tupletDisplay = getCurrentTupletDisplay();

    removeCanvasElements(".legend-entry");
    updateTupletPaletteSymbol(Triplet);

    ton_c = addLegendEntry(ton, uiText('score.note.tone'), 92, 1166, 45, 178, legendOffsetX, legendOffsetY);
    bass_c = addLegendEntry(bass, uiText('score.note.bass'), 157, 1146, 46, 198, legendOffsetX, legendOffsetY);
    slap_c = addLegendEntry(slap, uiText('score.note.slapSlashBell'), 222, 1126, 45, 218, legendOffsetX, legendOffsetY);
    flam_ton_c = addLegendEntry(flam_ton, uiText('score.note.toneFlam'), 337, 1105, 49, 240, legendOffsetX, legendOffsetY);
    flam_slap_c = addLegendEntry(flam_slap, uiText('score.note.slapFlam'), 475, 1087, 49, 259, legendOffsetX, legendOffsetY);
    flam_bass_slap_c = addLegendEntry(flam_bass_slap, uiText('score.note.bassSlapFlamPlural'), 613, 1069, 49, 279, legendOffsetX, legendOffsetY);
    ton_g_c = addLegendEntry(ton_g, uiText('score.note.muffledTone'), 92, 1078, 48, 299, legendOffsetX, legendOffsetY);
    slap_g_c = addLegendEntry(slap_g, uiText('score.note.muffledSlapSlashClick'), 240, 1058, 48, 319, legendOffsetX, legendOffsetY);
    In_c = addLegendEntry(In, 'In', 428, 1034, 44, 343, legendOffsetX, legendOffsetY);
    Out_c = addLegendEntry(Out, 'Out', 470, 1011, 44, 366, legendOffsetX, legendOffsetY);
    ShortBar_c = addLegendEntry(ShortBar, 'ShortBar', 521, 938, 44, 439, legendOffsetX, legendOffsetY);
    repeatMarkerLegendClone = addLegendEntry(repeatMarkerGroup, uiText('score.legend.repeat'), 605, 968, 44, 409, legendOffsetX, legendOffsetY);
    Triplet_c = addLegendEntry(Triplet, tupletDisplay.label, 730, 900, 46, 477, legendOffsetX, legendOffsetY);
}

renderLegend(125);


// Funktionen zum Verschieben
function getPaletteBoundsForOffset(offsetX, offsetY) {
    const fallbackBounds = {
        x: paletteOriginX - 14,
        y: paletteOriginY - 16,
        x2: paletteOriginX + 48,
        y2: paletteOriginY + 318,
        width: 62,
        height: 334
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
paletteGroup = s.g(paletteFrame, ton, bass, slap, ton_g, slap_g, flam_ton, flam_slap, flam_bass_slap, In, Out, ShortBar, Triplet, text_z_g, repeatMarkerGroup)
    .addClass('editor-palette');
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
insertTripletMarker = openTupletDialog;
Triplet.click(openTupletDialog);
Triplet.touchstart(openTupletDialog);

document.querySelector("#tupletDialogCancelButton").addEventListener("click", closeTupletDialog);
document.querySelector("#tupletDialogInsertButton").addEventListener("click", insertTupletFromDialog);
document.querySelector("#tupletDialog").addEventListener("click", function (event) {
    if (event.target && event.target.id === "tupletDialog") {
        closeTupletDialog();
    }
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
        const text_i = prompt(uiText('editor.editTextPrompt'), text_a);
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
    const text_i = prompt(uiText('editor.editTextPrompt'), '');
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
    return String(value || fallback || uiText('editor.defaultExportName'))
        .trim()
        .replace(/[\\/:*?"<>|]+/g, '-')
        .replace(/\s+/g, ' ')
        || fallback || uiText('editor.defaultExportName');
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
    const baseName = sanitizeDownloadFileName(nameOverride || titel.attr('text'), uiText('editor.defaultExportName'));
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
        serializedRhythm += serializeEditorElementForStorage(el);
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
            reject(new Error(uiText('error.pdfRenderFailed')));
        };
        image.src = url;
    });
}

async function exportCurrentSheetAsPdf() {
    const svgContent = buildExportSvgContent();
    const documentTitle = titel.attr('text') || uiText('editor.defaultExportName');
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
        const baseName = sanitizeDownloadFileName(documentTitle, uiText('editor.defaultExportName'));
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
        alert(uiText('error.pdfExportFailed', { message: error.message || '' }));
    } finally {
        URL.revokeObjectURL(svgUrl);
    }
}


// Auslesen


const noteElementIds = ['tone', 'bass', 'slap', 'tone_muffled', 'slap_muffled', 'slap_muffled', 'tone_flam', 'slap_flam', 'bass_slap_flam'];
const controlElementIds = ['in', 'out', 'shortbar', 'wiederholung'];
const tupletElementIds = ['triplet', 'quartuplet'];

let notenText = "eee";

function getReadRhythmConfig() {
    if (rhythm == 'binaer') {
        return {
            rhythmLabel: uiText('score.readout.binary'),
            stepsPerBar: 32,
            totalStepsPerLine: 64,
            gapSlotCount: 2,
            getLineSlotIndex: function (centerX) {
                return Math.round(((centerX - 25) / 12.5) - 7);
            }
        };
    }
    if (rhythm == 'tenaer') {
        const gridLineStepX = 850 / 26;
        const noteStepX = gridLineStepX / 2;
        const firstNoteX = 100 + gridLineStepX + 1;
        return {
            rhythmLabel: uiText('score.readout.ternary'),
            stepsPerBar: 24,
            totalStepsPerLine: 48,
            gapSlotCount: 2,
            getLineSlotIndex: function (centerX) {
                return Math.round((centerX - firstNoteX) / noteStepX) + 1;
            }
        };
    }
    return {
        rhythmLabel: uiText('score.readout.nineEight'),
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
    if (tupletElementIds.includes(element.attr('id'))) {
        const tupletBounds = element.getBBox();
        return {
            x: tupletBounds.x + 11,
            y: tupletBounds.cy
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
        const chooserText = getChooserInternalValue(element);
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
            startBar.repeat.start = boundary.startMarkers.map(function (marker) {
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
    if (instrumentName == 'Kenkeni' || instrumentName == 'Sangban' || instrumentName == 'Doundoun' || instrumentName == 'Dununba' || instrumentName == 'Dundunba' || instrumentName == 'Bässe') {
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

function createTupletNoteValue(elementId, noteIds, instrumentName) {
    const mappedNotes = (Array.isArray(noteIds) ? noteIds : [])
        .map(function (noteId) {
            return mergePercussionNote('f', noteId, instrumentName);
        })
        .filter(function (noteValue) {
            return noteValue && noteValue !== 'f';
        });

    if (mappedNotes.length === 0) {
        return 'f';
    }

    return 'tuplet:' + elementId + ':' + mappedNotes.join('|');
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
            return marker === 'loop' ? uiText('score.readout.untilStop') : marker;
        });
        const displayEndMarkers = endMarkers.map(function (marker) {
            return marker === 'loop' ? uiText('score.readout.untilStop') : marker;
        });
        const controlSummary = bar.controls.length === 0
            ? uiText('score.readout.none')
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
        summaryText += uiText('score.readout.bar', {
            bar: bar.index,
            instrument: getChooserDisplayText(bar.effectiveInstrument || '', 'instrument'),
            label: getChooserDisplayText(bar.effectiveLabel || '', 'function'),
            start: displayStartMarkers.join(', '),
            end: displayEndMarkers.join(', ')
        }) + '\n' +
            uiText('score.readout.controls', {
                index: bar.index - 1,
                controls: controlSummary
            }) + '\n' +
            uiText('score.readout.notes', {
                index: bar.index - 1,
                notes: bar.notes.join(',')
            }) + '\n';
    });
    return summaryText;
}

function buildRepeatRangeSummary(repeatRanges) {
    if (repeatRanges.length === 0) {
        return uiText('score.readout.repeatRangesNone');
    }

    let rangeSummaryText = uiText('score.readout.repeatRangesTitle');
    repeatRanges.forEach(function (repeatRange, rangeIndex) {
        rangeSummaryText += uiText('score.readout.repeatRange', {
            index: rangeIndex + 1,
            start: repeatRange.startBar,
            end: repeatRange.endBar,
            count: repeatRange.count === 'loop'
                ? uiText('score.readout.repeatUntilStop')
                : uiText('score.readout.repeatCount', { count: repeatRange.count })
        });
    });
    return rangeSummaryText;
}

function mapInstrumentNameForPlayer(instrumentName) {
    const instrumentMap = {
        'Djembe 1': 'Djembe_1',
        'Djembe 2': 'Djembe_2',
        'Djembe 3': 'Djembe_3',
        'Dununba': 'Doundoun',
        'Dundunba': 'Doundoun'
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
    const launchKey = 'barabeat-player-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2);
    const launchData = {
        playerRows: playerRows,
        embedded: Boolean(embedded),
        uiTheme: document.body.dataset.uiTheme || ''
    };

    window.barabeatAudioLaunchPayloads = window.barabeatAudioLaunchPayloads || {};
    window.barabeatAudioLaunchPayloads[launchKey] = launchData;
    window.consumeBarabeatAudioLaunchPayload = function (key) {
        const payload = window.barabeatAudioLaunchPayloads && window.barabeatAudioLaunchPayloads[key];
        if (payload) {
            delete window.barabeatAudioLaunchPayloads[key];
        }
        return payload || null;
    };

    try {
        localStorage.setItem(launchKey, JSON.stringify(launchData));
    } catch (error) {
        console.warn('Playerdaten konnten nicht zwischengespeichert werden', error);
    }

    // A changing fragment alone does not reload an existing iframe. Keep the
    // launch key in the fragment, but also vary the query so every payload gets
    // a fresh player document (the service worker ignores this query offline).
    const encodedLaunchKey = encodeURIComponent(launchKey);
    const launchUrl = 'Audio/player.html?launchReload=' + encodedLaunchKey + '#launch=' + encodedLaunchKey;
    const frameEl = Array.from(document.querySelectorAll('iframe[name]')).find(function (candidateEl) {
        return candidateEl.name === targetName;
    });
    if (frameEl) {
        frameEl.dataset.audioLaunchKey = launchKey;
        frameEl.src = launchUrl;
        return;
    }

    const openedWindow = window.open(launchUrl, targetName || '_blank');
    if (openedWindow) {
        return;
    }

    // A form submission can still open the static player when window.open() is
    // blocked. The payload remains in localStorage for player.html to consume.
    const form = document.createElement('form');
    form.action = 'Audio/player.html#launch=' + encodedLaunchKey;
    form.method = 'GET';
    form.target = targetName || '_blank';
    form.innerHTML = '<input type="hidden" name="launchReload" />';
    document.body.appendChild(form);
    form.querySelector('input[name="launchReload"]').value = launchKey;
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

function isMobileSheetReaderViewport() {
    return isMobilePracticeViewport();
}

function updateMobilePracticeModeAvailability() {
    const mobilePracticeViewport = isMobilePracticeViewport();
    const mobileLandscapeViewport = isMobileLandscapeViewport();
    const mobileReadOnlyViewport = mobilePracticeViewport && !mobileLandscapeViewport;
    const wasMobileLandscapeEditor = document.body.classList.contains('is-mobile-landscape-edit');
    const orientationNoticeEl = document.getElementById('mobileOrientationNotice');
    document.body.classList.toggle('is-mobile-practice-viewport', mobilePracticeViewport);
    document.body.classList.toggle('is-mobile-landscape-edit', mobileLandscapeViewport);
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
        buttonEl.disabled = buttonId === 'button11'
            ? mobilePracticeViewport
            : mobileReadOnlyViewport;
        if (buttonEl.disabled) {
            buttonEl.title = uiText('editor.mobilePracticeOnly');
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
    let currentMobileReadResult = null;
    const shouldRefreshMobileSheet = (wasMobileLandscapeEditor && mobileReadOnlyViewport) ||
        (!wasMobileLandscapeEditor && mobileLandscapeViewport);
    if (shouldRefreshMobileSheet && document.body.classList.contains('has-loaded-score')) {
        try {
            currentMobileReadResult = callPHPScript_lesen(zeilenAnzahl, {
                showAlert: false,
                logResults: false
            });
        } catch (error) {
            console.warn('Mobile Notenansicht konnte nach dem Bearbeiten nicht aktualisiert werden', error);
        }
    }
    renderMobileSheetView(currentMobileReadResult);
    updateMobileArrangementButtonVisibility();
    if (mobileLandscapeViewport && typeof schedulePaletteViewportFollow === 'function') {
        window.requestAnimationFrame(schedulePaletteViewportFollow);
    }
}

function getCurrentTimelineArrangementPayload() {
    const readResult = callPHPScript_lesen(zeilenAnzahl, { showAlert: false });
    syncTimelineStateFromReadResultIfNeeded(readResult, buildCurrentTimelineSyncOptions());
    const playerPayload = buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries);
    return timelinePayloadHasPlayableEntries(playerPayload) ? playerPayload : null;
}

function updateMobileArrangementButtonVisibility() {
    const buttonEl = document.getElementById('mobileArrangementPlayerButton');
    if (!buttonEl) {
        return;
    }

    let hasArrangement = false;
    if (isMobilePracticeViewport() &&
            !practiceState.visible &&
            Array.isArray(timelineState.sourcePatterns) &&
            timelineState.sourcePatterns.length > 0 &&
            Array.isArray(timelineState.entries) &&
            timelineState.entries.length > 0) {
        try {
            hasArrangement = timelinePayloadHasPlayableEntries(
                buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries)
            );
        } catch (error) {
            hasArrangement = false;
        }
    }

    buttonEl.hidden = !hasArrangement;
}

function openMobileArrangementPlayer() {
    if (!isMobilePracticeViewport()) {
        return;
    }

    try {
        const playerPayload = getCurrentTimelineArrangementPayload();
        if (!playerPayload) {
            alert(uiText('arrangement.error.noPlayableArrangement'));
            updateMobileArrangementButtonVisibility();
            return;
        }

        const overlayEl = document.getElementById('mobileArrangementOverlay');
        const frameEl = document.getElementById('mobileArrangementAudioFrame');
        if (!overlayEl || !frameEl) {
            openAudioTestWindow(playerPayload);
            return;
        }

        practiceState.visible = false;
        clearPracticeAudioPlayer();
        timelineState.visible = false;
        clearTimelineAudioPlayer();
        renderPracticePanel();
        renderTimelinePanel();
        overlayEl.hidden = false;
        openAudioTestFrame(playerPayload, frameEl.name || 'mobileArrangementAudioFrame');
    } catch (error) {
        console.error('Mobiler Arrangement-Player konnte nicht gestartet werden', error);
        alert(uiText('arrangement.error.start', { message: error.message || '' }));
    }
}

function closeMobileArrangementPlayer() {
    const overlayEl = document.getElementById('mobileArrangementOverlay');
    const frameEl = document.getElementById('mobileArrangementAudioFrame');
    if (overlayEl) {
        overlayEl.hidden = true;
    }
    if (frameEl) {
        frameEl.src = 'about:blank';
    }
    timelineAudioPlaybackState = 'stopped';
}

function getMobileSheetStepsPerBeat() {
    if (rhythm === 'binaer') {
        return 4;
    }
    return 3;
}

function getMobileSheetStepsPerBar() {
    if (rhythm === 'binaer') {
        return 32;
    }
    if (rhythm === 'neunaer') {
        return 18;
    }
    return 24;
}

function getMobileSheetBeatCount() {
    const stepsPerBar = getMobileSheetStepsPerBar();
    const stepsPerBeat = rhythm === 'binaer' ? 8 : 6;
    return Math.max(1, Math.round(stepsPerBar / stepsPerBeat));
}

function getMobileSheetLayoutConfig() {
    if (rhythm === 'binaer') {
        return {
            subdivisionCount: 34,
            centerDividerIndex: 17,
            beatStartIndices: [1, 5, 9, 13],
            beatNumberOffset: 4,
            beatDivisor: 4,
            beatWrapAt: 4,
            noteStartRel: 25,
            noteStepRel: 12.5,
            syllables: ['Ja', 'Pi', 'Du', 'Pa']
        };
    }
    if (rhythm === 'neunaer') {
        return {
            subdivisionCount: 20,
            centerDividerIndex: 10,
            beatStartIndices: [1, 4, 7],
            beatNumberOffset: 3,
            beatDivisor: 3,
            beatWrapAt: 3,
            noteStartRel: 42.5,
            noteStepRel: 21.25,
            syllables: ['Ja', 'Pi', 'Du']
        };
    }
    const ternaryGridLineStepRel = 850 / 26;
    return {
        subdivisionCount: 26,
        centerDividerIndex: 13,
        beatStartIndices: [1, 4, 7, 10],
        beatNumberOffset: 3,
        beatDivisor: 3,
        beatWrapAt: 4,
        noteStartRel: ternaryGridLineStepRel,
        noteStepRel: ternaryGridLineStepRel / 2,
        syllables: ['Ja', 'Pi', 'Du']
    };
}

function getMobileSheetScaledX(leftX, rightX, desktopRelativeX) {
    const desktopBarWidth = 425;
    return leftX + ((rightX - leftX) * (desktopRelativeX / desktopBarWidth));
}

function parseMobileSheetTuplet(noteValue) {
    if (typeof noteValue !== 'string' || noteValue.indexOf('tuplet:') !== 0) {
        return null;
    }
    const parts = noteValue.split(':');
    const tupletType = parts[1] || 'triplet';
    const notePart = parts.slice(2).join(':');
    const notes = notePart.split('|').map(function (note) {
        return note.trim();
    }).filter(function (note) {
        return note && note !== 'f';
    });
    return notes.length ? { type: tupletType, notes: notes } : null;
}

function getMobileSheetNoteParts(noteValue) {
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
        Klick: [{ type: 'cross', muted: true }],
        kenkeni_muffled: [{ type: 'cross', muted: true, lane: 'top' }],
        tone_flam: [{ type: 'circle', offset: -3 }, { type: 'circle', ghost: true, offset: 3 }],
        Flam: [{ type: 'circle', offset: -3 }, { type: 'circle', ghost: true, offset: 3 }],
        'T-Flam': [{ type: 'circle', offset: -3 }, { type: 'circle', ghost: true, offset: 3 }],
        slap_flam: [{ type: 'cross' }, { type: 'cross' }],
        'S-Flam': [{ type: 'cross' }, { type: 'cross' }],
        bass_slap_flam: [{ type: 'square' }, { type: 'cross' }],
        Bell_Open: [{ type: 'cross', lane: 'top', offset: 0 }, { type: 'circle', lane: 'bottom', offset: 0 }],
        Bell_Muffled: [{ type: 'cross', lane: 'top', offset: 0 }, { type: 'circle', muted: true, lane: 'bottom', offset: 0 }],
        Bell_Klick: [{ type: 'cross', lane: 'top', offset: 0 }, { type: 'cross', muted: true, lane: 'bottom', offset: 0 }],
        kenkeni_sangban: [{ type: 'cross', lane: 'top', offset: 0 }, { type: 'circle', lane: 'middle', offset: 0 }],
        kenkeni_doundoun: [{ type: 'cross', lane: 'top', offset: 0 }, { type: 'square', lane: 'bottom', offset: 0 }],
        sangban_doundoun: [{ type: 'circle', lane: 'middle', offset: 0 }, { type: 'square', lane: 'bottom', offset: 0 }],
        kenkeni_muffled_sangban: [{ type: 'cross', muted: true, lane: 'top', offset: 0 }, { type: 'circle', lane: 'middle', offset: 0 }],
        kenkeni_sangban_muffled: [{ type: 'cross', lane: 'top', offset: 0 }, { type: 'circle', muted: true, lane: 'middle', offset: 0 }],
        sangban_muffled_doundoun: [{ type: 'circle', muted: true, lane: 'middle', offset: 0 }, { type: 'square', lane: 'bottom', offset: 0 }],
        kenkeni_muffled_doundoun: [{ type: 'cross', muted: true, lane: 'top', offset: 0 }, { type: 'square', lane: 'bottom', offset: 0 }]
    };
    return symbolMap[noteValue] || [];
}

function createMobileSheetSvgElement(name, attributes) {
    const element = document.createElementNS('http://www.w3.org/2000/svg', name);
    Object.keys(attributes || {}).forEach(function (attributeName) {
        element.setAttribute(attributeName, String(attributes[attributeName]));
    });
    return element;
}

function appendMobileSheetNotePart(svgEl, part, x, y, index, count) {
    const offset = Number.isFinite(Number(part.offset))
        ? Number(part.offset)
        : (count > 1 ? (index - (count - 1) / 2) * 5 : 0);
    const noteX = x + offset;
    const laneOffsetY = part.lane === 'top'
        ? -28
        : (part.lane === 'middle' ? -12 : 4);
    const noteY = y + laneOffsetY;
    if (part.type === 'square') {
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: noteX - 5,
            y: noteY - 5,
            width: 10,
            height: 10,
            fill: '#111'
        }));
    } else if (part.type === 'cross') {
        svgEl.appendChild(createMobileSheetSvgElement('line', {
            x1: noteX - 6,
            y1: noteY + 6,
            x2: noteX + 6,
            y2: noteY - 6,
            stroke: '#111',
            'stroke-width': 2
        }));
        svgEl.appendChild(createMobileSheetSvgElement('line', {
            x1: noteX - 6,
            y1: noteY - 6,
            x2: noteX + 6,
            y2: noteY + 6,
            stroke: '#111',
            'stroke-width': 2
        }));
    } else {
        svgEl.appendChild(createMobileSheetSvgElement('circle', {
            cx: noteX,
            cy: noteY,
            r: 6,
            fill: part.ghost ? '#fff' : '#111',
            stroke: '#111',
            'stroke-width': part.ghost ? 2 : 0
        }));
    }
    if (part.muted) {
        svgEl.appendChild(createMobileSheetSvgElement('line', {
            x1: noteX - 7,
            y1: noteY + 11,
            x2: noteX + 7,
            y2: noteY + 11,
            stroke: '#111',
            'stroke-width': 2
        }));
    }
}

function appendMobileSheetNote(svgEl, noteValue, x, y, beatWidth) {
    const tuplet = parseMobileSheetTuplet(noteValue);
    if (tuplet) {
        const subdivisionCount = tuplet.type === 'quartuplet' ? 4 : 3;
        tuplet.notes.forEach(function (subNoteValue, noteIndex) {
            appendMobileSheetNote(svgEl, subNoteValue, x + beatWidth * (noteIndex / subdivisionCount), y, beatWidth);
        });
        return;
    }

    const parts = getMobileSheetNoteParts(noteValue);
    parts.forEach(function (part, partIndex) {
        appendMobileSheetNotePart(svgEl, part, x, y, partIndex, parts.length);
    });
}

function getMobileSheetRepeatText(repeatValues) {
    const values = Array.isArray(repeatValues) ? repeatValues : [repeatValues];
    for (let valueIndex = 0; valueIndex < values.length; valueIndex += 1) {
        const value = values[valueIndex];
        if (value === true || value === 'continue' || value === '' || value == null) {
            continue;
        }
        return String(value);
    }
    return '';
}

function appendMobileSheetRepeatMarker(svgEl, x, y, repeatText, includeHitbox) {
    if (includeHitbox) {
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: x - 12,
            y: y - 20,
            width: 24,
            height: 48,
            fill: '#fff',
            opacity: 0.001,
            class: 'mobile-sheet-repeat-hitbox'
        }));
    }
    svgEl.appendChild(createMobileSheetSvgElement('circle', {
        cx: x,
        cy: y - 6,
        r: 2.2,
        fill: '#111'
    }));
    svgEl.appendChild(createMobileSheetSvgElement('circle', {
        cx: x,
        cy: y + 6,
        r: 2.2,
        fill: '#111'
    }));

    if (repeatText !== '') {
        svgEl.appendChild(createMobileSheetSvgElement('text', {
            x: x,
            y: y + 20,
            'font-size': 12,
            'font-weight': 'bold',
            fill: '#111',
            'text-anchor': 'middle'
        })).textContent = String(repeatText);
    }
}

function appendMobileSheetControlMarker(svgEl, control, x, staffTopY, noteY) {
    const controlType = control && control.type;
    if (controlType === 'shortbar') {
        const shortBarTopY = staffTopY - 2;
        const shortBarBottomY = noteY + 24;
        svgEl.appendChild(createMobileSheetSvgElement('line', {
            x1: x,
            y1: shortBarTopY,
            x2: x,
            y2: shortBarBottomY,
            stroke: '#111',
            'stroke-width': 2,
            'stroke-dasharray': '2 3',
            'stroke-linecap': 'round'
        }));
        return;
    }

    if (controlType !== 'in' && controlType !== 'out') {
        return;
    }

    const points = controlType === 'in'
        ? (x - 6) + ',' + (noteY + 23) + ' ' + (x + 6) + ',' + (noteY + 23) + ' ' + x + ',' + (noteY + 33)
        : (x - 6) + ',' + (noteY + 29) + ' ' + (x + 6) + ',' + (noteY + 29) + ' ' + x + ',' + (noteY + 19);
    const stemY1 = controlType === 'in' ? noteY + 12 : noteY + 35;
    const stemY2 = controlType === 'in' ? noteY + 25 : noteY + 27;

    svgEl.appendChild(createMobileSheetSvgElement('line', {
        x1: x,
        y1: stemY1,
        x2: x,
        y2: stemY2,
        stroke: '#111',
        'stroke-width': 4,
        'stroke-linecap': 'round'
    }));
    svgEl.appendChild(createMobileSheetSvgElement('polygon', {
        points: points,
        fill: '#111'
    }));
}

function hasMobileSheetBarContent(bar) {
    if (!bar) {
        return false;
    }
    const instrumentText = bar.instrument || bar.effectiveInstrument || '';
    const labelText = bar.label || bar.effectiveLabel || '';
    const hasNamedBar = Boolean(
        instrumentText &&
        instrumentText !== 'Leer' &&
        labelText &&
        labelText !== 'Leer'
    );
    const hasNotes = Array.isArray(bar.notes) && bar.notes.some(function (noteValue) {
        return noteValue && noteValue !== 'f';
    });
    const hasControls = Array.isArray(bar.controls) && bar.controls.length > 0;
    const hasRepeat = Boolean(bar.repeat) && (
        (Array.isArray(bar.repeat.start) && bar.repeat.start.length > 0) ||
        (Array.isArray(bar.repeat.end) && bar.repeat.end.length > 0)
    );
    return hasNamedBar || hasNotes || hasControls || hasRepeat;
}

function trimMobileSheetBars(rhythmBars) {
    const bars = Array.isArray(rhythmBars) ? rhythmBars : [];
    let lastContentIndex = -1;
    bars.forEach(function (bar, barIndex) {
        if (hasMobileSheetBarContent(bar)) {
            lastContentIndex = barIndex;
        }
    });
    return lastContentIndex >= 0 ? bars.slice(0, lastContentIndex + 1) : [];
}

function getSheetQuickPlayPatternForSourceBar(sourceBarIndex) {
    const normalizedBarIndex = Number(sourceBarIndex);
    return sheetQuickPlayState.patternLibrary.find(function (pattern) {
        return pattern && Array.isArray(pattern.bars) && pattern.bars.some(function (patternBar) {
            return Number(patternBar && patternBar.sourceBarIndex) === normalizedBarIndex;
        });
    }) || null;
}

function isFirstSheetQuickPlayPatternBar(pattern, sourceBarIndex) {
    if (!pattern || !Array.isArray(pattern.bars) || pattern.bars.length === 0) {
        return false;
    }
    const firstSourceBarIndex = pattern.bars.reduce(function (lowestIndex, patternBar) {
        const patternBarIndex = Number(patternBar && patternBar.sourceBarIndex);
        return Number.isFinite(patternBarIndex) ? Math.min(lowestIndex, patternBarIndex) : lowestIndex;
    }, Infinity);
    return Number(sourceBarIndex) === firstSourceBarIndex;
}

const mobileSheetEditorState = {
    activeTool: '',
    pendingTupletTarget: null,
    selectedSourceElements: [],
    movingChooserSourceBarIndex: null,
    clipboardItems: []
};

const mobileSheetEditorClipboardStorageKey = 'barabeat.mobileEditorClipboard';

const mobileSheetEditorTools = [
    { id: 'tone', labelKey: 'score.note.tone' },
    { id: 'bass', labelKey: 'score.note.bass' },
    { id: 'slap', labelKey: 'score.note.slapOrBell' },
    { id: 'tone_muffled', labelKey: 'score.note.muffledTone' },
    { id: 'slap_muffled', labelKey: 'score.note.muffledSlapOrClick' },
    { id: 'tone_flam', labelKey: 'score.note.toneFlam' },
    { id: 'slap_flam', labelKey: 'score.note.slapFlam' },
    { id: 'bass_slap_flam', labelKey: 'score.note.bassSlapFlam' },
    { id: 'in', label: 'In' },
    { id: 'out', label: 'Out' },
    { id: 'shortbar', label: 'ShortBar' },
    { id: 'wiederholung', labelKey: 'score.legend.repeat' },
    { id: 'tuplet', labelKey: 'score.tuplet.tripletOrQuartuplet' },
    { id: 'edit_text', labelKey: 'editor.insertTextField' },
    { id: 'chooser', labelKey: 'editor.insertInstrumentAndFunction' },
    { id: 'select', labelKey: 'editor.selectNotes' },
    { id: 'clipboard', labelKey: 'editor.copySelection' },
    { id: 'duplicate', labelKey: 'editor.duplicateSelection' },
    { id: 'delete', labelKey: 'editor.deleteElement' }
];

function createMobileSheetEditorToolIcon(toolId, iconMode) {
    const svgEl = createMobileSheetSvgElement('svg', {
        viewBox: '0 0 30 30',
        width: 30,
        height: 30,
        'aria-hidden': 'true',
        focusable: 'false'
    });
    svgEl.classList.add('mobile-sheet-editor-tool-icon');

    if (['tone', 'bass', 'slap', 'tone_muffled', 'slap_muffled', 'tone_flam', 'slap_flam', 'bass_slap_flam'].indexOf(toolId) !== -1) {
        appendMobileSheetNote(svgEl, toolId, 15, 10, 20);
        return svgEl;
    }
    if (toolId === 'in' || toolId === 'out') {
        appendMobileSheetControlMarker(svgEl, { type: toolId }, 15, 5, -5);
        return svgEl;
    }
    if (toolId === 'shortbar') {
        svgEl.appendChild(createMobileSheetSvgElement('line', {
            x1: 15,
            y1: 3,
            x2: 15,
            y2: 27,
            stroke: '#111',
            'stroke-width': 4,
            'stroke-dasharray': '1 5',
            'stroke-linecap': 'round'
        }));
        return svgEl;
    }
    if (toolId === 'wiederholung') {
        appendMobileSheetRepeatMarker(svgEl, 15, 15, '');
        return svgEl;
    }
    if (toolId === 'tuplet') {
        [9, 15, 21].forEach(function (dotX) {
            svgEl.appendChild(createMobileSheetSvgElement('circle', {
                cx: dotX,
                cy: 9,
                r: 2.5,
                fill: '#111'
            }));
        });
        svgEl.appendChild(createMobileSheetSvgElement('text', {
            x: 15,
            y: 25,
            'font-size': 11,
            'font-family': 'sans-serif',
            'font-weight': 'bold',
            'text-anchor': 'middle',
            fill: '#111'
        })).textContent = getCurrentTupletDisplay().letter;
        return svgEl;
    }

    if (toolId === 'chooser') {
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: 4,
            y: 4,
            width: 22,
            height: 22,
            rx: 3,
            fill: '#fff',
            stroke: '#111',
            'stroke-width': 1.4
        }));
        svgEl.appendChild(createMobileSheetSvgElement('text', {
            x: 8,
            y: 13,
            'font-size': 8,
            'font-family': 'sans-serif',
            'font-weight': 'bold',
            fill: '#111'
        })).textContent = 'I';
        svgEl.appendChild(createMobileSheetSvgElement('text', {
            x: 8,
            y: 23,
            'font-size': 8,
            'font-family': 'sans-serif',
            'font-weight': 'bold',
            fill: '#111'
        })).textContent = 'F';
        [11, 21].forEach(function (triangleY) {
            svgEl.appendChild(createMobileSheetSvgElement('path', {
                d: 'M18 ' + (triangleY - 2) + ' L23 ' + (triangleY - 2) + ' L20.5 ' + (triangleY + 1) + ' Z',
                fill: '#111'
            }));
        });
        return svgEl;
    }

    if (toolId === 'edit_text') {
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: 6,
            y: 5,
            width: 18,
            height: 20,
            rx: 1,
            fill: '#fff',
            stroke: '#111',
            'stroke-width': 1.4
        }));
        svgEl.appendChild(createMobileSheetSvgElement('text', {
            x: 15,
            y: 21,
            'font-size': 15,
            'font-family': 'sans-serif',
            'font-weight': 'bold',
            'text-anchor': 'middle',
            fill: '#111'
        })).textContent = 'T';
        return svgEl;
    }

    if (toolId === 'select') {
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: 5,
            y: 6,
            width: 20,
            height: 18,
            rx: 1,
            fill: 'none',
            stroke: '#111',
            'stroke-width': 1.6,
            'stroke-dasharray': '3 2'
        }));
        return svgEl;
    }

    if (toolId === 'duplicate') {
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: 10,
            y: 6,
            width: 14,
            height: 14,
            rx: 1,
            fill: '#fff',
            stroke: '#111',
            'stroke-width': 1.7
        }));
        svgEl.appendChild(createMobileSheetSvgElement('rect', {
            x: 6,
            y: 10,
            width: 14,
            height: 14,
            rx: 1,
            fill: '#fff',
            stroke: '#111',
            'stroke-width': 1.7
        }));
        return svgEl;
    }

    if (toolId === 'clipboard') {
        if (iconMode === 'paste') {
            svgEl.appendChild(createMobileSheetSvgElement('path', {
                d: 'M10 7h3c0-2 4-2 4 0h3v4H10V7zm-2 3h2v3h10v-3h2v16H8V10zm4 7h6m-3-3 3 3-3 3',
                fill: '#fff',
                stroke: '#111',
                'stroke-width': 1.7,
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round'
            }));
        } else {
            svgEl.appendChild(createMobileSheetSvgElement('rect', {
                x: 10,
                y: 6,
                width: 14,
                height: 14,
                rx: 1,
                fill: '#fff',
                stroke: '#111',
                'stroke-width': 1.7
            }));
            svgEl.appendChild(createMobileSheetSvgElement('rect', {
                x: 6,
                y: 10,
                width: 14,
                height: 14,
                rx: 1,
                fill: '#fff',
                stroke: '#111',
                'stroke-width': 1.7
            }));
        }
        return svgEl;
    }

    svgEl.appendChild(createMobileSheetSvgElement('path', {
        d: 'M10 9h10l-1 16h-8L10 9zm2-4h6l1 2h4v2H7V7h4l1-2zm1 7v10m4-10v10',
        fill: 'none',
        stroke: '#111',
        'stroke-width': 1.8,
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round'
    }));
    return svgEl;
}

function setMobileSheetEditorTool(toolId) {
    mobileSheetEditorState.activeTool = mobileSheetEditorState.activeTool === toolId ? '' : toolId;
    updateMobileSheetEditorPaletteState();
}

function getMobileSheetSelectedSourceElements() {
    mobileSheetEditorState.selectedSourceElements = mobileSheetEditorState.selectedSourceElements.filter(function (element) {
        return element && element.node && element.node.parentNode;
    });
    return mobileSheetEditorState.selectedSourceElements;
}

function normalizeMobileSheetClipboardItems(items) {
    return (Array.isArray(items) ? items : []).filter(function (item) {
        return item &&
            typeof item.markup === 'string' &&
            item.markup &&
            Number.isFinite(Number(item.sourceBarIndex)) &&
            Number.isFinite(Number(item.sourceStepIndex));
    }).map(function (item) {
        return {
            markup: item.markup,
            sourceBarIndex: Math.max(1, Math.round(Number(item.sourceBarIndex))),
            sourceStepIndex: Math.max(0, Math.round(Number(item.sourceStepIndex))),
            offsetX: Number(item.offsetX) || 0,
            lineOffsetY: Number(item.lineOffsetY) || 0
        };
    });
}

function readStoredMobileSheetClipboardItems() {
    try {
        const storedPayload = JSON.parse(
            window.localStorage.getItem(mobileSheetEditorClipboardStorageKey) || 'null'
        );
        return normalizeMobileSheetClipboardItems(storedPayload && storedPayload.items);
    } catch (error) {
        return [];
    }
}

function getMobileSheetClipboardItems() {
    if (mobileSheetEditorState.clipboardItems.length === 0) {
        mobileSheetEditorState.clipboardItems = readStoredMobileSheetClipboardItems();
    }
    return mobileSheetEditorState.clipboardItems;
}

function getMobileSheetClipboardButtonMode() {
    if (getMobileSheetSelectedSourceElements().length > 0) {
        return 'copy';
    }
    return getMobileSheetClipboardItems().length > 0 ? 'paste' : 'copy';
}

function updateMobileSheetClipboardButton(buttonEl) {
    if (!buttonEl) {
        return;
    }
    const mode = getMobileSheetClipboardButtonMode();
    const hasSelection = getMobileSheetSelectedSourceElements().length > 0;
    const hasClipboard = getMobileSheetClipboardItems().length > 0;
    const label = uiText(mode === 'copy' ? 'editor.copySelection' : 'editor.pasteSelection');
    buttonEl.dataset.clipboardMode = mode;
    buttonEl.disabled = mode === 'copy' ? !hasSelection : !hasClipboard;
    buttonEl.replaceChildren(createMobileSheetEditorToolIcon('clipboard', mode));
    buttonEl.title = label;
    buttonEl.setAttribute('aria-label', label);
}

function updateMobileSheetEditorPaletteState() {
    const hasSelection = getMobileSheetSelectedSourceElements().length > 0;
    const movingChooserSourceBarIndex = Number(mobileSheetEditorState.movingChooserSourceBarIndex);
    const isChooserMoveActive = Number.isFinite(movingChooserSourceBarIndex) && movingChooserSourceBarIndex > 0;
    document.body.classList.toggle(
        'is-mobile-selection-tool-active',
        isMobileLandscapeViewport() && mobileSheetEditorState.activeTool === 'select'
    );
    document.body.classList.toggle(
        'is-mobile-chooser-tool-active',
        isMobileLandscapeViewport() && mobileSheetEditorState.activeTool === 'chooser'
    );
    document.body.classList.toggle(
        'is-mobile-chooser-move-active',
        isMobileLandscapeViewport() && isChooserMoveActive
    );
    document.querySelectorAll('.mobile-sheet-bar[data-source-bar-index]').forEach(function (barEl) {
        barEl.classList.toggle(
            'is-mobile-chooser-move-source',
            isChooserMoveActive && Number(barEl.dataset.sourceBarIndex) === movingChooserSourceBarIndex
        );
    });
    document.querySelectorAll('.mobile-sheet-chooser-target').forEach(function (targetEl) {
        targetEl.textContent = isChooserMoveActive
            ? uiText('editor.moveChooserHere')
            : uiText('editor.insertInstrumentAndFunction');
    });
    document.querySelectorAll('.mobile-sheet-editor-tool').forEach(function (buttonEl) {
        if (buttonEl.dataset.toolId === 'clipboard') {
            updateMobileSheetClipboardButton(buttonEl);
        }
        const isActive = buttonEl.dataset.toolId === 'clipboard'
            ? mobileSheetEditorState.activeTool === 'paste'
            : buttonEl.dataset.toolId === mobileSheetEditorState.activeTool;
        buttonEl.classList.toggle('is-active', isActive);
        buttonEl.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        if (buttonEl.dataset.toolId === 'duplicate') {
            buttonEl.disabled = !hasSelection;
        }
    });
}

function createMobileSheetEditorPalette() {
    const paletteEl = document.createElement('div');
    paletteEl.className = 'mobile-sheet-editor-palette';
    paletteEl.setAttribute('role', 'toolbar');
    paletteEl.setAttribute('aria-label', uiText('editor.notePalette'));

    mobileSheetEditorTools.forEach(function (tool) {
        const buttonEl = document.createElement('button');
        buttonEl.type = 'button';
        buttonEl.className = 'mobile-sheet-editor-tool';
        buttonEl.dataset.toolId = tool.id;
        const clipboardMode = tool.id === 'clipboard' ? getMobileSheetClipboardButtonMode() : '';
        buttonEl.appendChild(createMobileSheetEditorToolIcon(tool.id, clipboardMode));
        const toolLabel = tool.labelKey ? uiText(tool.labelKey) : tool.label;
        buttonEl.title = toolLabel;
        buttonEl.setAttribute('aria-label', toolLabel);
        const toolIsActive = tool.id === 'clipboard'
            ? mobileSheetEditorState.activeTool === 'paste'
            : mobileSheetEditorState.activeTool === tool.id;
        buttonEl.setAttribute('aria-pressed', toolIsActive ? 'true' : 'false');
        buttonEl.classList.toggle('is-active', toolIsActive);
        if (tool.id === 'duplicate') {
            buttonEl.disabled = getMobileSheetSelectedSourceElements().length === 0;
        }
        if (tool.id === 'clipboard') {
            updateMobileSheetClipboardButton(buttonEl);
        }
        buttonEl.addEventListener('click', function () {
            if (tool.id === 'delete' && getMobileSheetSelectedSourceElements().length > 0) {
                deleteMobileSheetEditorSelection();
                return;
            }
            if (tool.id === 'duplicate') {
                duplicateMobileSheetEditorSelection();
                return;
            }
            if (tool.id === 'clipboard') {
                if (buttonEl.dataset.clipboardMode === 'copy') {
                    copyMobileSheetEditorSelection();
                } else {
                    setMobileSheetEditorTool('paste');
                }
                return;
            }
            setMobileSheetEditorTool(tool.id);
        });
        paletteEl.appendChild(buttonEl);
    });
    return paletteEl;
}

function getMobileSheetLegacyNoteOffsetY(elementId, instrumentName) {
    const normalizedInstrument = String(instrumentName || '');
    if (normalizedInstrument === 'Dreierbass') {
        return elementId === 'slap' || elementId === 'slap_muffled'
            ? 37
            : (elementId === 'tone' || elementId === 'tone_muffled' ? 53 : 69);
    }
    if (['Kenkeni', 'Sangban', 'Doundoun', 'Dununba', 'Dundunba', 'Bässe'].indexOf(normalizedInstrument) !== -1) {
        return elementId === 'slap' || elementId === 'slap_muffled' ? 47 : 69;
    }
    return 65;
}

function getMobileSheetCanonicalNoteOffsetY(elementId, instrumentName) {
    const normalizedInstrument = String(instrumentName || '');
    if (tupletElementIds.includes(elementId)) {
        return 32;
    }
    if (normalizedInstrument === 'Dreierbass') {
        if (elementId === 'slap' || elementId === 'slap_muffled' || elementId === 'slap_flam') {
            return 17;
        }
        if (elementId === 'bass' || elementId === 'bass_slap_flam') {
            return 47;
        }
        return 32;
    }
    if (['Kenkeni', 'Sangban', 'Doundoun', 'Dununba', 'Dundunba', 'Bässe'].indexOf(normalizedInstrument) !== -1) {
        return elementId === 'slap' || elementId === 'slap_flam' ? 17 : 47;
    }
    return 32;
}

function isMobileSheetPositionedNote(elementId) {
    return noteElementIds.includes(elementId) || tupletElementIds.includes(elementId);
}

function getMobileSheetSourcePosition(sourceBarIndex, sourceStepIndex, elementId, instrumentName) {
    const zeroBasedBarIndex = Math.max(0, Math.round(Number(sourceBarIndex) || 1) - 1);
    const lineIndex = Math.floor(zeroBasedBarIndex / 2);
    const isRightBar = zeroBasedBarIndex % 2 === 1;
    const stepIndex = Math.max(0, Math.min(getMobileSheetStepsPerBar() - 1, Math.round(Number(sourceStepIndex) || 0)));
    let firstStepX;
    let stepX;
    if (rhythm === 'binaer') {
        firstStepX = isRightBar ? 551 : 126;
        stepX = 12.5;
    } else if (rhythm === 'neunaer') {
        firstStepX = isRightBar ? 568.5 : 143.5;
        stepX = 21.25;
    } else {
        const ternaryGridLineStepX = 850 / 26;
        firstStepX = (isRightBar ? 525 : 100) + ternaryGridLineStepX + 1;
        stepX = ternaryGridLineStepX / 2;
    }

    const sourceOffsetY = isMobileSheetPositionedNote(elementId)
        ? getMobileSheetCanonicalNoteOffsetY(elementId, instrumentName)
        : 65;

    return {
        x: firstStepX + stepIndex * stepX,
        y: getSheetLineBaseY(lineIndex) + sourceOffsetY
    };
}

function normalizeLegacyMobileSheetNotePositions(readResult) {
    const rhythmBars = readResult && Array.isArray(readResult.rhythmBars)
        ? readResult.rhythmBars
        : [];
    if (!rhythmBars.length || !s) {
        return false;
    }

    const readConfig = getReadRhythmConfig();
    const candidates = [];
    s.selectAll('.shp').forEach(function (element) {
        const elementId = element.attr('id');
        if (!isMobileSheetPositionedNote(elementId)) {
            return;
        }
        const position = getElementReadPosition(element);
        const positionInfo = getBarIndexFromPosition(position.x, position.y, readConfig, zeilenAnzahl);
        const rhythmBar = rhythmBars[positionInfo.barIndex];
        if (!rhythmBar) {
            return;
        }
        const instrumentName = rhythmBar.effectiveInstrument || rhythmBar.instrument || '';
        const lineBaseY = getSheetLineBaseY(positionInfo.lineIndex);
        const currentOffsetY = position.y - lineBaseY;
        const legacyOffsetY = getMobileSheetLegacyNoteOffsetY(elementId, instrumentName);
        const canonicalOffsetY = getMobileSheetCanonicalNoteOffsetY(elementId, instrumentName);
        const stepIndex = getStepIndexWithinBar(positionInfo.lineSlotIndex, readConfig.stepsPerBar);
        if (stepIndex === null) {
            return;
        }
        const targetPosition = getMobileSheetSourcePosition(
            positionInfo.barIndex + 1,
            stepIndex,
            elementId,
            instrumentName
        );
        const canonicalXDistance = Math.abs(position.x - targetPosition.x);
        const horizontalSnapTolerance = Math.max(1, (Number(gridSize) || 2) / 2 - 0.01);
        const matchesLegacyY = Math.abs(currentOffsetY - legacyOffsetY) <= 1.5;
        const isNearCanonicalGridX = canonicalXDistance <= horizontalSnapTolerance;
        if (matchesLegacyY || isNearCanonicalGridX) {
            candidates.push({
                element: element,
                position: position,
                targetX: isNearCanonicalGridX ? targetPosition.x : position.x,
                targetY: targetPosition.y,
                legacyOffsetY: legacyOffsetY,
                canonicalOffsetY: canonicalOffsetY,
                matchesLegacyY: matchesLegacyY,
                hasHorizontalMisalignment: isNearCanonicalGridX && canonicalXDistance > 0.05
            });
        }
    });

    const canonicalOffsets = [17, 32, 47, 62];
    const hasDistinctLegacyVerticalPosition = candidates.some(function (candidate) {
        if (!candidate.matchesLegacyY) {
            return false;
        }
        return !canonicalOffsets.some(function (canonicalOffsetY) {
            return Math.abs(candidate.legacyOffsetY - canonicalOffsetY) <= 1.5;
        });
    });
    const hasHorizontalMisalignment = candidates.some(function (candidate) {
        return candidate.hasHorizontalMisalignment;
    });
    if (!hasDistinctLegacyVerticalPosition && !hasHorizontalMisalignment) {
        return false;
    }

    candidates.forEach(function (candidate) {
        moveSheetElementAnchorTo(
            candidate.element,
            candidate.targetX,
            hasDistinctLegacyVerticalPosition && candidate.matchesLegacyY
                ? candidate.targetY
                : candidate.position.y,
            false
        );
    });
    return candidates.length > 0;
}

function moveSheetElementAnchorTo(element, targetX, targetY, preserveY) {
    if (!element || typeof element.transform !== 'function') {
        return;
    }
    const sourcePosition = getElementReadPosition(element);
    const transformState = element.transform();
    const localMatrix = transformState && transformState.localMatrix ? transformState.localMatrix : null;
    const currentX = localMatrix ? localMatrix.e : 0;
    const currentY = localMatrix ? localMatrix.f : 0;
    const deltaX = Number(targetX) - Number(sourcePosition.x);
    const deltaY = preserveY ? 0 : Number(targetY) - Number(sourcePosition.y);
    element.transform('t' + (currentX + deltaX) + ',' + (currentY + deltaY));
}

function getMobileSheetSourceElements(sourceBarIndex, sourceStepIndex, elementType) {
    const normalizedBarIndex = Math.max(1, Math.round(Number(sourceBarIndex) || 1));
    const normalizedStepIndex = Math.max(0, Math.round(Number(sourceStepIndex) || 0));
    if (elementType === 'note') {
        rebuildSheetQuickPlayNoteElementMap();
        return (sheetQuickPlayState.noteElementsByPosition[
            getSheetQuickPlayPositionKey(normalizedBarIndex, normalizedStepIndex)
        ] || []).slice();
    }

    const readConfig = getReadRhythmConfig();
    const result = [];
    s.selectAll('.shp').forEach(function (element) {
        if (element.attr('id') !== elementType) {
            return;
        }
        const elementPosition = getElementReadPosition(element);
        const positionInfo = getBarIndexFromPosition(elementPosition.x, elementPosition.y, readConfig, zeilenAnzahl);
        if (controlElementIds.includes(elementType) && elementType !== 'wiederholung') {
            positionInfo.rawLineSlotIndex = getControlLineSlotIndex(elementPosition.x, readConfig, elementType);
            positionInfo.lineSlotIndex = positionInfo.rawLineSlotIndex;
            if (positionInfo.lineSlotIndex > readConfig.stepsPerBar) {
                positionInfo.lineSlotIndex -= Number(readConfig.gapSlotCount) || 2;
            }
            positionInfo.barIndex = positionInfo.lineIndex * 2 +
                (positionInfo.rawLineSlotIndex > readConfig.stepsPerBar + (Number(readConfig.gapSlotCount) || 2) ? 1 : 0);
        }
        const stepIndex = getStepIndexWithinBar(positionInfo.lineSlotIndex, readConfig.stepsPerBar);
        if (positionInfo.barIndex + 1 === normalizedBarIndex && stepIndex === normalizedStepIndex) {
            result.push(element);
        }
    });
    return result;
}

function getMobileSheetSourceNoteDescriptor(element) {
    if (!element || typeof element.attr !== 'function') {
        return null;
    }
    const elementId = element.attr('id');
    if (!noteElementIds.includes(elementId) && !tupletElementIds.includes(elementId)) {
        return null;
    }
    const readConfig = getReadRhythmConfig();
    const elementPosition = getElementReadPosition(element);
    const positionInfo = getBarIndexFromPosition(
        elementPosition.x,
        elementPosition.y,
        readConfig,
        zeilenAnzahl
    );
    const stepIndex = getStepIndexWithinBar(positionInfo.lineSlotIndex, readConfig.stepsPerBar);
    if (stepIndex === null || stepIndex < 0 || stepIndex >= readConfig.stepsPerBar) {
        return null;
    }
    return {
        sourceBarIndex: positionInfo.barIndex + 1,
        sourceStepIndex: stepIndex
    };
}

function getMobileSheetSelectionPositionKeys() {
    const keys = new Set();
    getMobileSheetSelectedSourceElements().forEach(function (element) {
        const descriptor = getMobileSheetSourceNoteDescriptor(element);
        if (descriptor) {
            keys.add(getSheetQuickPlayPositionKey(descriptor.sourceBarIndex, descriptor.sourceStepIndex));
        }
    });
    return keys;
}

function updateMobileSheetSelectionClasses() {
    const selectedKeys = getMobileSheetSelectionPositionKeys();
    document.querySelectorAll('.mobile-sheet-note[data-source-bar-index][data-source-step-index]').forEach(function (noteEl) {
        const key = getSheetQuickPlayPositionKey(noteEl.dataset.sourceBarIndex, noteEl.dataset.sourceStepIndex);
        noteEl.classList.toggle('is-mobile-editor-selected', selectedKeys.has(key));
    });
    updateMobileSheetEditorPaletteState();
}

function setMobileSheetSelectionFromNoteElements(noteElements) {
    rebuildSheetQuickPlayNoteElementMap();
    const selectedElements = [];
    const selectedNodes = new Set();
    noteElements.forEach(function (noteEl) {
        const key = getSheetQuickPlayPositionKey(noteEl.dataset.sourceBarIndex, noteEl.dataset.sourceStepIndex);
        (sheetQuickPlayState.noteElementsByPosition[key] || []).forEach(function (sourceElement) {
            if (!sourceElement || !sourceElement.node || selectedNodes.has(sourceElement.node)) {
                return;
            }
            selectedNodes.add(sourceElement.node);
            selectedElements.push(sourceElement);
        });
    });
    mobileSheetEditorState.selectedSourceElements = selectedElements;
    updateMobileSheetSelectionClasses();
}

function copyMobileSheetEditorSelection() {
    const selectedElements = getMobileSheetSelectedSourceElements().slice();
    const clipboardItems = [];
    selectedElements.forEach(function (element) {
        const descriptor = getMobileSheetSourceNoteDescriptor(element);
        if (!descriptor) {
            return;
        }
        const sourcePosition = getElementReadPosition(element);
        const sourceLineIndex = Math.floor((descriptor.sourceBarIndex - 1) / 2);
        const canonicalPosition = getMobileSheetSourcePosition(
            descriptor.sourceBarIndex,
            descriptor.sourceStepIndex,
            element.attr('id'),
            ''
        );
        clipboardItems.push({
            markup: typeof serializeEditorElementForStorage === 'function'
                ? serializeEditorElementForStorage(element)
                : element.toString(),
            sourceBarIndex: descriptor.sourceBarIndex,
            sourceStepIndex: descriptor.sourceStepIndex,
            offsetX: sourcePosition.x - canonicalPosition.x,
            lineOffsetY: sourcePosition.y - getSheetLineBaseY(sourceLineIndex)
        });
    });
    if (clipboardItems.length === 0) {
        return false;
    }

    mobileSheetEditorState.clipboardItems = clipboardItems;
    try {
        window.localStorage.setItem(mobileSheetEditorClipboardStorageKey, JSON.stringify({
            version: 1,
            createdAt: Date.now(),
            items: clipboardItems
        }));
    } catch (error) {
        // The in-memory clipboard remains available in restricted browser contexts.
    }
    if (typeof writeEditorClipboard === 'function') {
        writeEditorClipboard(clipboardItems.map(function (item) { return item.markup; }).join(''), 'copy');
    }

    mobileSheetEditorState.activeTool = '';
    mobileSheetEditorState.selectedSourceElements = [];
    updateMobileSheetSelectionClasses();
    return true;
}

function pasteMobileSheetEditorSelection(targetBarIndex, targetStepIndex) {
    const clipboardItems = getMobileSheetClipboardItems().slice();
    if (clipboardItems.length === 0 || typeof Snap === 'undefined' || !s) {
        return false;
    }

    const stepsPerBar = getMobileSheetStepsPerBar();
    const sourceAnchor = Math.min.apply(Math, clipboardItems.map(function (item) {
        return (item.sourceBarIndex - 1) * stepsPerBar + item.sourceStepIndex;
    }));
    const targetAnchor = (Math.max(1, Number(targetBarIndex)) - 1) * stepsPerBar +
        Math.max(0, Number(targetStepIndex));
    const maximumBarCount = Math.max(1, Number(zeilenAnzahl) || 1) * 2;
    const targetItems = clipboardItems.map(function (item) {
        const sourceAbsoluteStep = (item.sourceBarIndex - 1) * stepsPerBar + item.sourceStepIndex;
        const targetAbsoluteStep = targetAnchor + sourceAbsoluteStep - sourceAnchor;
        return {
            barIndex: Math.floor(targetAbsoluteStep / stepsPerBar) + 1,
            stepIndex: ((targetAbsoluteStep % stepsPerBar) + stepsPerBar) % stepsPerBar,
            source: item
        };
    });
    if (targetItems.some(function (item) {
        return item.barIndex < 1 || item.barIndex > maximumBarCount;
    })) {
        alert(uiText('editor.pasteOutsideScore'));
        return false;
    }

    let parsedElements;
    try {
        parsedElements = Snap.parseStr(clipboardItems.map(function (item) {
            return item.markup;
        }).join(''));
    } catch (error) {
        return false;
    }
    const pastedElements = [];
    if (parsedElements && typeof parsedElements.selectAll === 'function') {
        parsedElements.selectAll(selectableElementSelector).forEach(function (element) {
            pastedElements.push(element);
        });
    }
    if (pastedElements.length !== targetItems.length) {
        return false;
    }

    recordHistorySnapshot();
    s.append(parsedElements);
    pastedElements.forEach(function (element, elementIndex) {
        const targetItem = targetItems[elementIndex];
        const targetPosition = getMobileSheetSourcePosition(
            targetItem.barIndex,
            targetItem.stepIndex,
            element.attr('id'),
            ''
        );
        const targetLineIndex = Math.floor((targetItem.barIndex - 1) / 2);
        moveSheetElementAnchorTo(
            element,
            targetPosition.x + targetItem.source.offsetX,
            getSheetLineBaseY(targetLineIndex) + targetItem.source.lineOffsetY,
            false
        );
        if (typeof bindClonedElement === 'function') {
            bindClonedElement(element);
        } else {
            element.drag(move, sel_start, stop_m);
        }
    });

    mobileSheetEditorState.activeTool = 'select';
    mobileSheetEditorState.selectedSourceElements = pastedElements;
    refreshMobileSheetEditorView();
    return true;
}

function deleteMobileSheetEditorSelection() {
    const selectedElements = getMobileSheetSelectedSourceElements().slice();
    if (selectedElements.length === 0) {
        return;
    }
    recordHistorySnapshot();
    selectedElements.forEach(function (element) {
        element.remove();
    });
    mobileSheetEditorState.selectedSourceElements = [];
    refreshMobileSheetEditorView();
}

function duplicateMobileSheetEditorSelection() {
    const selectedElements = getMobileSheetSelectedSourceElements().slice();
    const descriptors = selectedElements.map(getMobileSheetSourceNoteDescriptor);
    if (selectedElements.length === 0 || descriptors.some(function (descriptor) { return !descriptor; })) {
        return;
    }

    const selectedSteps = descriptors.map(function (descriptor) { return descriptor.sourceStepIndex; });
    const minimumStep = Math.min.apply(Math, selectedSteps);
    const maximumStep = Math.max.apply(Math, selectedSteps);
    const lastStep = getMobileSheetStepsPerBar() - 1;
    const stepOffset = maximumStep < lastStep ? 1 : (minimumStep > 0 ? -1 : 0);
    const clonedElements = [];

    recordHistorySnapshot();
    selectedElements.forEach(function (element, elementIndex) {
        const descriptor = descriptors[elementIndex];
        const clone = element.clone().attr({
            class: 'shp',
            id: element.attr('id')
        });
        s.append(clone);
        clone.drag(move, sel_start, stop_m);
        const targetPosition = getMobileSheetSourcePosition(
            descriptor.sourceBarIndex,
            descriptor.sourceStepIndex + stepOffset,
            element.attr('id'),
            ''
        );
        moveSheetElementAnchorTo(clone, targetPosition.x, targetPosition.y, true);
        clonedElements.push(clone);
    });

    mobileSheetEditorState.selectedSourceElements = clonedElements;
    refreshMobileSheetEditorView();
}

function getMobileSheetRepeatSourceElements(sourceBarIndex, repeatSide) {
    const expectedBoundaryIndex = repeatSide === 'start'
        ? Math.max(0, Number(sourceBarIndex) - 1)
        : Math.max(1, Number(sourceBarIndex));
    const result = [];
    s.selectAll('#wiederholung').forEach(function (element) {
        const position = getElementReadPosition(element);
        const target = getRepeatTarget(position.x, position.y, zeilenAnzahl);
        if (target && target.boundaryIndex === expectedBoundaryIndex && target.repeatSide === repeatSide) {
            result.push(element);
        }
    });
    return result;
}

function refreshMobileSheetEditorView() {
    const scrollPosition = {
        x: window.scrollX || 0,
        y: window.scrollY || 0,
        anchorBarIndex: '',
        anchorTop: 0
    };
    const visibleBarEl = Array.from(document.querySelectorAll('.mobile-sheet-bar[data-source-bar-index]'))
        .find(function (barEl) {
            const bounds = barEl.getBoundingClientRect();
            return bounds.bottom > 0 && bounds.top < window.innerHeight;
        });
    if (visibleBarEl) {
        scrollPosition.anchorBarIndex = visibleBarEl.dataset.sourceBarIndex || '';
        scrollPosition.anchorTop = visibleBarEl.getBoundingClientRect().top;
    }

    const readResult = callPHPScript_lesen(zeilenAnzahl, {
        showAlert: false,
        updateQuickPlaySelectors: false,
        logResults: false
    });
    renderSheetQuickPlaySelectors(readResult);
    renderMobileSheetView(readResult);

    window.requestAnimationFrame(function () {
        window.scrollTo(scrollPosition.x, scrollPosition.y);
        window.requestAnimationFrame(function () {
            if (!scrollPosition.anchorBarIndex) {
                return;
            }
            const restoredBarEl = document.querySelector(
                '.mobile-sheet-bar[data-source-bar-index="' + scrollPosition.anchorBarIndex + '"]'
            );
            if (!restoredBarEl) {
                return;
            }
            const topDifference = restoredBarEl.getBoundingClientRect().top - scrollPosition.anchorTop;
            if (Math.abs(topDifference) > 0.5) {
                window.scrollBy(0, topDifference);
            }
        });
    });
}

function getMobileSheetStepFromPointer(svgEl, clientX) {
    const metrics = svgEl && svgEl.__mobileSheetEditorMetrics;
    if (!metrics || !svgEl.createSVGPoint || !svgEl.getScreenCTM()) {
        return 0;
    }
    const point = svgEl.createSVGPoint();
    point.x = clientX;
    point.y = 0;
    const localPoint = point.matrixTransform(svgEl.getScreenCTM().inverse());
    return Math.max(0, Math.min(
        getMobileSheetStepsPerBar() - 1,
        Math.round((localPoint.x - metrics.firstNoteX) / metrics.noteStepX)
    ));
}

function getMobileSheetChooserText(chooserElement) {
    return chooserElement && typeof getChooserInternalValue === 'function'
        ? String(getChooserInternalValue(chooserElement) || '').trim()
        : '';
}

function isMobileSheetChooserPlaceholder(chooserElement, placeholderText) {
    const chooserText = getMobileSheetChooserText(chooserElement);
    return !chooserText || chooserText === placeholderText;
}

function setMobileSheetChooserMoveSource(sourceBarIndex) {
    const normalizedSourceBarIndex = Number(sourceBarIndex);
    mobileSheetEditorState.activeTool = '';
    mobileSheetEditorState.movingChooserSourceBarIndex =
        Number(mobileSheetEditorState.movingChooserSourceBarIndex) === normalizedSourceBarIndex
            ? null
            : normalizedSourceBarIndex;
    updateMobileSheetEditorPaletteState();
}

function deleteMobileSheetBarChoosers(sourceBarIndex) {
    const chooserByType = getMobileSheetBarChoosers(sourceBarIndex);
    const chooserElements = [chooserByType.instrument, chooserByType.label].filter(Boolean);
    if (chooserElements.length === 0) {
        return;
    }
    recordHistorySnapshot();
    chooserElements.forEach(function (chooserElement) {
        chooserElement.remove();
    });
    mobileSheetEditorState.movingChooserSourceBarIndex = null;
    refreshMobileSheetEditorView();
}

function moveMobileSheetBarChoosers(targetBarIndex) {
    const sourceBarIndex = Number(mobileSheetEditorState.movingChooserSourceBarIndex);
    const normalizedTargetBarIndex = Number(targetBarIndex);
    if (!Number.isFinite(sourceBarIndex) || sourceBarIndex < 1 || sourceBarIndex === normalizedTargetBarIndex) {
        mobileSheetEditorState.movingChooserSourceBarIndex = null;
        updateMobileSheetEditorPaletteState();
        return;
    }

    const sourceChoosers = getMobileSheetBarChoosers(sourceBarIndex);
    if (!sourceChoosers.instrument && !sourceChoosers.label) {
        mobileSheetEditorState.movingChooserSourceBarIndex = null;
        updateMobileSheetEditorPaletteState();
        return;
    }

    const targetChoosers = getMobileSheetBarChoosers(normalizedTargetBarIndex);
    const targetHasInstrument = targetChoosers.instrument &&
        !isMobileSheetChooserPlaceholder(targetChoosers.instrument, 'Instrument');
    const targetHasLabel = targetChoosers.label &&
        !isMobileSheetChooserPlaceholder(targetChoosers.label, 'Funktion');
    if (targetHasInstrument || targetHasLabel) {
        return;
    }

    recordHistorySnapshot();
    [targetChoosers.instrument, targetChoosers.label].filter(Boolean).forEach(function (chooserElement) {
        chooserElement.remove();
    });

    const zeroBasedTargetBarIndex = Math.max(0, normalizedTargetBarIndex - 1);
    const targetBounds = getSheetBarBounds(normalizedTargetBarIndex);
    const chooserY = getSheetLineBaseY(Math.floor(zeroBasedTargetBarIndex / 2)) - 32;
    if (sourceChoosers.instrument) {
        sourceChoosers.instrument.transform('t' + (targetBounds.x + 24) + ',' + chooserY);
    }
    if (sourceChoosers.label) {
        sourceChoosers.label.transform('t' + (targetBounds.x + 165) + ',' + chooserY);
    }
    mobileSheetEditorState.movingChooserSourceBarIndex = null;
    refreshMobileSheetEditorView();
}

function insertMobileSheetBarChoosers(sourceBarIndex) {
    const chooserByType = getMobileSheetBarChoosers(sourceBarIndex);
    const instrumentValue = getMobileSheetChooserText(chooserByType.instrument);
    const labelValue = getMobileSheetChooserText(chooserByType.label);
    const needsInstrument = !chooserByType.instrument || !instrumentValue || instrumentValue === 'Instrument';
    const needsLabel = !chooserByType.label || !labelValue || labelValue === 'Funktion';

    if (!needsInstrument && !needsLabel) {
        mobileSheetEditorState.activeTool = '';
        updateMobileSheetEditorPaletteState();
        return;
    }

    recordHistorySnapshot();
    const zeroBasedBarIndex = Math.max(0, Number(sourceBarIndex) - 1);
    const bounds = getSheetBarBounds(sourceBarIndex);
    const chooserY = getSheetLineBaseY(Math.floor(zeroBasedBarIndex / 2)) - 32;
    if (!chooserByType.instrument) {
        createInstrumentChooser(s, bounds.x + 24, chooserY, 'Leer', 'gray')
            .addClass('shp')
            .attr({ id: nextInstrumentChooserId() });
    } else if (needsInstrument) {
        setChooserText(chooserByType.instrument, 'Leer', 'gray');
    }
    if (!chooserByType.label) {
        createFunctionChooser(s, bounds.x + 165, chooserY, 'Leer', 'gray')
            .addClass('shp')
            .attr({ id: nextFunctionChooserId() });
    } else if (needsLabel) {
        setChooserText(chooserByType.label, 'Leer', 'gray');
    }
    mobileSheetEditorState.activeTool = '';
    refreshMobileSheetEditorView();
}

function insertMobileSheetEditorElement(toolId, sourceBarIndex, sourceStepIndex, instrumentName, localX, staffMetrics) {
    if (toolId === 'chooser') {
        insertMobileSheetBarChoosers(sourceBarIndex);
        return;
    }
    const targetPosition = getMobileSheetSourcePosition(sourceBarIndex, sourceStepIndex, toolId, instrumentName);
    const templateMap = {
        tone: ton_c,
        bass: bass_c,
        slap: slap_c,
        tone_muffled: ton_g_c,
        slap_muffled: slap_g_c,
        tone_flam: flam_ton_c,
        slap_flam: flam_slap_c,
        bass_slap_flam: flam_bass_slap_c,
        in: In_c,
        out: Out_c,
        shortbar: ShortBar_c
    };

    if (toolId === 'tuplet') {
        mobileSheetEditorState.pendingTupletTarget = {
            sourceBarIndex: sourceBarIndex,
            sourceStepIndex: sourceStepIndex,
            instrumentName: instrumentName
        };
        openTupletDialog();
        return;
    }

    if (toolId === 'edit_text') {
        const textValue = prompt(uiText('editor.scoreTextPrompt'), '');
        if (textValue != null && String(textValue).trim()) {
            recordHistorySnapshot();
            createEditableTextElement(targetPosition.x, targetPosition.y, String(textValue).trim());
            refreshMobileSheetEditorView();
        }
        return;
    }

    recordHistorySnapshot();
    if (toolId === 'wiederholung') {
        const zeroBasedBarIndex = Math.max(0, Number(sourceBarIndex) - 1);
        const isRightBar = zeroBasedBarIndex % 2 === 1;
        const useEndBoundary = localX >= (staffMetrics.staffStartX + staffMetrics.staffEndX) / 2;
        const startBoundaryLineX = isRightBar ? 525 : 100;
        const endBoundaryLineX = isRightBar ? 950 : 525;
        const boundaryX = useEndBoundary
            ? endBoundaryLineX - 6
            : startBoundaryLineX + 8;
        const repeatElement = createPaletteClone(repeatMarkerLegendClone, 'wiederholung', repeatMarkerGridOffsetX, 2);
        moveSheetElementAnchorTo(repeatElement, boundaryX, getSheetLineBaseY(Math.floor(zeroBasedBarIndex / 2)) + 57, false);
        repeatElement.dblclick(cycleRepeatCount);
        refreshMobileSheetEditorView();
        return;
    }

    const templateElement = templateMap[toolId];
    if (!templateElement) {
        return;
    }
    const element = createPaletteClone(templateElement, toolId, gridSizeX, toolId === 'in' ? -2 : 0);
    moveSheetElementAnchorTo(element, targetPosition.x, targetPosition.y, false);
    if (toolId === 'shortbar') {
        updateShortBarMarkerVisual(element);
        snapElementToVerticalTarget(element);
    }
    refreshMobileSheetEditorView();
}

function getMobileSheetBarChoosers(sourceBarIndex) {
    const readConfig = getReadRhythmConfig();
    const chooserByType = { instrument: null, label: null };
    s.selectAll(chooserSelector).forEach(function (chooserElement) {
        const position = getElementReadPosition(chooserElement);
        const info = getBarIndexForMetaElement(position.x, position.y, readConfig, zeilenAnzahl);
        if (info.barIndex + 1 !== Number(sourceBarIndex)) {
            return;
        }
        chooserByType[isInstrumentChooserNode(chooserElement) ? 'instrument' : 'label'] = chooserElement;
    });
    return chooserByType;
}

async function setMobileSheetBarChooserValue(sourceBarIndex, chooserType, rawValue) {
    const chooserByType = getMobileSheetBarChoosers(sourceBarIndex);
    let nextValue = String(rawValue || '').trim();
    if (!nextValue) {
        return false;
    }

    if (chooserType === 'label' && (nextValue === 'Solo' || nextValue === 'Begleitpattern')) {
        const currentChooser = chooserByType.label;
        const currentTextNode = currentChooser ? currentChooser.select('text') : null;
        const promptText = uiText('chooser.dialog.customizeLabel', {
            name: getChooserDisplayText(nextValue, 'function')
        });
        const configuredValue = await requestChooserLabel(
            getChooserDisplayText(getChooserLabelSeed(nextValue, currentTextNode), 'function'),
            promptText
        );
        if (configuredValue == null) {
            return false;
        }
        nextValue = normalizeChooserInternalText(configuredValue, 'function');
    }

    recordHistorySnapshot();
    const zeroBasedBarIndex = Math.max(0, Number(sourceBarIndex) - 1);
    const bounds = getSheetBarBounds(sourceBarIndex);
    const chooserY = getSheetLineBaseY(Math.floor(zeroBasedBarIndex / 2)) - 32;

    if (chooserType === 'instrument') {
        if (chooserByType.instrument) {
            setChooserText(chooserByType.instrument, normalizeDoundounInstrumentName(nextValue));
        } else {
            createInstrumentChooser(s, bounds.x + 24, chooserY, nextValue, 'black')
                .addClass('shp')
                .attr({ id: nextInstrumentChooserId() });
        }
        if (nextValue === 'Leer' && chooserByType.label) {
            setChooserText(chooserByType.label, 'Leer');
        }
    } else if (chooserByType.label) {
        setChooserText(chooserByType.label, nextValue);
    } else {
        createFunctionChooser(s, bounds.x + 165, chooserY, nextValue, 'black')
            .addClass('shp')
            .attr({ id: nextFunctionChooserId() });
    }
    refreshMobileSheetEditorView();
    return true;
}

function createMobileSheetChooserSelect(sourceBarIndex, chooserType, currentValue) {
    const options = chooserType === 'instrument'
        ? getInstrumentChooserOptions()
        : getFunctionChooserOptions();
    const selectEl = document.createElement('select');
    selectEl.className = 'mobile-sheet-chooser-select mobile-sheet-chooser-' + chooserType;
    selectEl.setAttribute('aria-label', uiText(
        chooserType === 'instrument' ? 'editor.selectInstrument' : 'editor.selectPattern'
    ));

    const selectedValue = chooserType === 'instrument'
        ? String(normalizeDoundounInstrumentName(currentValue) || '').trim()
        : String(currentValue || '').trim();
    if (selectedValue && options.indexOf(selectedValue) === -1) {
        const currentOptionEl = document.createElement('option');
        currentOptionEl.value = selectedValue;
        currentOptionEl.textContent = getChooserDisplayText(selectedValue, chooserType);
        selectEl.appendChild(currentOptionEl);
    }
    options.forEach(function (optionValue) {
        const optionEl = document.createElement('option');
        optionEl.value = optionValue;
        optionEl.textContent = getChooserDisplayText(optionValue, chooserType);
        selectEl.appendChild(optionEl);
    });
    selectEl.value = selectedValue || 'Leer';

    selectEl.addEventListener('click', function (event) {
        event.stopPropagation();
    });
    selectEl.addEventListener('change', async function (event) {
        event.stopPropagation();
        const previousValue = selectedValue;
        const changed = await setMobileSheetBarChooserValue(sourceBarIndex, chooserType, selectEl.value);
        if (!changed) {
            selectEl.value = previousValue;
        }
    });
    return selectEl;
}

function bindMobileSheetNoteEditor(noteGroupEl, svgEl, sourceBarIndex, sourceStepIndex) {
    if (!isMobileLandscapeViewport()) {
        return;
    }
    let startClientX = 0;
    let currentClientX = 0;
    let startLocalX = 0;
    let dragging = false;

    function getLocalX(clientX) {
        const point = svgEl.createSVGPoint();
        point.x = clientX;
        point.y = 0;
        return point.matrixTransform(svgEl.getScreenCTM().inverse()).x;
    }

    noteGroupEl.addEventListener('pointerdown', function (event) {
        if (mobileSheetEditorState.activeTool) {
            return;
        }
        startClientX = event.clientX;
        currentClientX = event.clientX;
        startLocalX = getLocalX(event.clientX);
        dragging = false;
        noteGroupEl.setPointerCapture(event.pointerId);
        event.preventDefault();
    });
    noteGroupEl.addEventListener('pointermove', function (event) {
        if (!noteGroupEl.hasPointerCapture(event.pointerId)) {
            return;
        }
        currentClientX = event.clientX;
        const clientDeltaX = currentClientX - startClientX;
        const localDeltaX = getLocalX(currentClientX) - startLocalX;
        if (Math.abs(clientDeltaX) > 3) {
            dragging = true;
            noteGroupEl.classList.add('is-mobile-editor-dragging');
            noteGroupEl.setAttribute('transform', 'translate(' + localDeltaX + ' 0)');
        }
        event.preventDefault();
    });
    noteGroupEl.addEventListener('pointerup', function (event) {
        if (noteGroupEl.hasPointerCapture(event.pointerId)) {
            noteGroupEl.releasePointerCapture(event.pointerId);
        }
        noteGroupEl.classList.remove('is-mobile-editor-dragging');
        noteGroupEl.removeAttribute('transform');
        if (!dragging) {
            return;
        }
        const targetStepIndex = getMobileSheetStepFromPointer(svgEl, currentClientX);
        if (targetStepIndex !== Number(sourceStepIndex)) {
            const sourceElements = getMobileSheetSourceElements(sourceBarIndex, sourceStepIndex, 'note');
            if (sourceElements.length > 0) {
                recordHistorySnapshot();
                const targetPosition = getMobileSheetSourcePosition(sourceBarIndex, targetStepIndex, '', '');
                sourceElements.forEach(function (element) {
                    moveSheetElementAnchorTo(element, targetPosition.x, targetPosition.y, true);
                });
                refreshMobileSheetEditorView();
            }
        }
        event.preventDefault();
    });
}

function bindMobileSheetSelectionEditor(svgEl) {
    if (!isMobileLandscapeViewport()) {
        return;
    }
    let pointerId = null;
    let interactionMode = '';
    let startPoint = null;
    let currentPoint = null;
    let selectionRectEl = null;
    let startTargetNoteEl = null;

    function getLocalPoint(event) {
        const point = svgEl.createSVGPoint();
        point.x = event.clientX;
        point.y = event.clientY;
        return point.matrixTransform(svgEl.getScreenCTM().inverse());
    }

    function removeSelectionPreview() {
        if (selectionRectEl) {
            selectionRectEl.remove();
            selectionRectEl = null;
        }
        svgEl.querySelectorAll('.mobile-sheet-note.is-mobile-editor-selected').forEach(function (noteEl) {
            noteEl.removeAttribute('transform');
        });
    }

    function finishSelectionInteraction(event, cancelled) {
        if (pointerId === null || event.pointerId !== pointerId) {
            return;
        }
        if (svgEl.hasPointerCapture(pointerId)) {
            svgEl.releasePointerCapture(pointerId);
        }
        const deltaX = currentPoint && startPoint ? currentPoint.x - startPoint.x : 0;
        const deltaY = currentPoint && startPoint ? currentPoint.y - startPoint.y : 0;
        const wasDragged = Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3;

        if (!cancelled && interactionMode === 'select') {
            const selectedNoteEls = [];
            if (!wasDragged && startTargetNoteEl) {
                selectedNoteEls.push(startTargetNoteEl);
            } else if (startPoint && currentPoint) {
                const selectionBounds = {
                    left: Math.min(startPoint.x, currentPoint.x),
                    right: Math.max(startPoint.x, currentPoint.x),
                    top: Math.min(startPoint.y, currentPoint.y),
                    bottom: Math.max(startPoint.y, currentPoint.y)
                };
                svgEl.querySelectorAll('.mobile-sheet-note').forEach(function (noteEl) {
                    const noteBounds = noteEl.getBBox();
                    const intersects = noteBounds.x + noteBounds.width >= selectionBounds.left &&
                        noteBounds.x <= selectionBounds.right &&
                        noteBounds.y + noteBounds.height >= selectionBounds.top &&
                        noteBounds.y <= selectionBounds.bottom;
                    if (intersects) {
                        selectedNoteEls.push(noteEl);
                    }
                });
            }
            setMobileSheetSelectionFromNoteElements(selectedNoteEls);
        } else if (!cancelled && interactionMode === 'move' && wasDragged) {
            const selectedElements = getMobileSheetSelectedSourceElements().slice();
            const descriptors = selectedElements.map(getMobileSheetSourceNoteDescriptor);
            if (selectedElements.length > 0 && !descriptors.some(function (descriptor) { return !descriptor; })) {
                const requestedStepOffset = Math.round(deltaX / svgEl.__mobileSheetEditorMetrics.noteStepX);
                const selectedSteps = descriptors.map(function (descriptor) { return descriptor.sourceStepIndex; });
                const minimumStep = Math.min.apply(Math, selectedSteps);
                const maximumStep = Math.max.apply(Math, selectedSteps);
                const stepOffset = Math.max(
                    -minimumStep,
                    Math.min(getMobileSheetStepsPerBar() - 1 - maximumStep, requestedStepOffset)
                );
                if (stepOffset !== 0) {
                    recordHistorySnapshot();
                    selectedElements.forEach(function (element, elementIndex) {
                        const descriptor = descriptors[elementIndex];
                        const targetPosition = getMobileSheetSourcePosition(
                            descriptor.sourceBarIndex,
                            descriptor.sourceStepIndex + stepOffset,
                            element.attr('id'),
                            ''
                        );
                        moveSheetElementAnchorTo(element, targetPosition.x, targetPosition.y, true);
                    });
                    removeSelectionPreview();
                    refreshMobileSheetEditorView();
                }
            }
        }

        removeSelectionPreview();
        pointerId = null;
        interactionMode = '';
        startPoint = null;
        currentPoint = null;
        startTargetNoteEl = null;
        event.preventDefault();
        event.stopPropagation();
    }

    svgEl.addEventListener('pointerdown', function (event) {
        if (mobileSheetEditorState.activeTool !== 'select') {
            return;
        }
        const targetNoteEl = event.target && event.target.closest
            ? event.target.closest('.mobile-sheet-note')
            : null;
        const moveExistingSelection = Boolean(
            targetNoteEl && targetNoteEl.classList.contains('is-mobile-editor-selected')
        );
        pointerId = event.pointerId;
        interactionMode = moveExistingSelection ? 'move' : 'select';
        startTargetNoteEl = targetNoteEl;
        startPoint = getLocalPoint(event);
        currentPoint = startPoint;
        svgEl.setPointerCapture(pointerId);

        if (!moveExistingSelection) {
            mobileSheetEditorState.selectedSourceElements = [];
            updateMobileSheetSelectionClasses();
            selectionRectEl = createMobileSheetSvgElement('rect', {
                x: startPoint.x,
                y: startPoint.y,
                width: 0,
                height: 0,
                class: 'mobile-sheet-selection-box'
            });
            svgEl.appendChild(selectionRectEl);
        }
        event.preventDefault();
        event.stopPropagation();
    });

    svgEl.addEventListener('pointermove', function (event) {
        if (pointerId === null || event.pointerId !== pointerId) {
            return;
        }
        currentPoint = getLocalPoint(event);
        const deltaX = currentPoint.x - startPoint.x;
        if (interactionMode === 'select' && selectionRectEl) {
            selectionRectEl.setAttribute('x', Math.min(startPoint.x, currentPoint.x));
            selectionRectEl.setAttribute('y', Math.min(startPoint.y, currentPoint.y));
            selectionRectEl.setAttribute('width', Math.abs(deltaX));
            selectionRectEl.setAttribute('height', Math.abs(currentPoint.y - startPoint.y));
        } else if (interactionMode === 'move') {
            svgEl.querySelectorAll('.mobile-sheet-note.is-mobile-editor-selected').forEach(function (noteEl) {
                noteEl.setAttribute('transform', 'translate(' + deltaX + ' 0)');
            });
        }
        event.preventDefault();
        event.stopPropagation();
    });

    svgEl.addEventListener('pointerup', function (event) {
        finishSelectionInteraction(event, false);
    });
    svgEl.addEventListener('pointercancel', function (event) {
        finishSelectionInteraction(event, true);
    });
}

function bindMobileSheetRepeatEditor(repeatGroupEl, sourceBarIndex, repeatSide) {
    if (!isMobileLandscapeViewport()) {
        return;
    }
    let lastTapTime = 0;

    repeatGroupEl.addEventListener('pointerup', function (event) {
        if (mobileSheetEditorState.activeTool) {
            return;
        }
        const currentTapTime = Date.now();
        const isDoubleTap = currentTapTime - lastTapTime > 0 && currentTapTime - lastTapTime < 380;
        lastTapTime = currentTapTime;
        if (!isDoubleTap) {
            return;
        }

        const sourceElements = getMobileSheetRepeatSourceElements(sourceBarIndex, repeatSide);
        if (sourceElements.length > 0) {
            cycleRepeatCount.call(sourceElements[0]);
            refreshMobileSheetEditorView();
        }
        event.preventDefault();
        event.stopPropagation();
    });
    repeatGroupEl.addEventListener('click', function (event) {
        if (!mobileSheetEditorState.activeTool) {
            event.stopPropagation();
        }
    });
}

function createMobileSheetBarElement(bar, barIndex, previousBar, nextBar) {
    const cardEl = document.createElement('article');
    cardEl.className = 'mobile-sheet-bar';

    const sourceBarIndex = Number(bar && bar.index) || (barIndex + 1);
    const isLandscapeEditor = isMobileLandscapeViewport();
    cardEl.dataset.sourceBarIndex = String(sourceBarIndex);
    cardEl.classList.toggle('is-mobile-sheet-editable', isLandscapeEditor);
    const quickPlayPattern = getSheetQuickPlayPatternForSourceBar(sourceBarIndex);
    const quickPlayPatternId = quickPlayPattern && quickPlayPattern.id ? String(quickPlayPattern.id) : '';
    const patternMoveRange = getSheetPatternMoveRangeForBar(sourceBarIndex);
    const patternMoveRangeIndex = patternMoveRange
        ? getSheetPatternMoveRangeIndex(patternMoveRange.id)
        : -1;
    const isPatternMoveStart = Boolean(
        patternMoveRange && patternMoveRange.startBarIndex === sourceBarIndex
    );
    if (quickPlayPatternId) {
        cardEl.dataset.patternId = quickPlayPatternId;
    }

    const titleEl = document.createElement('div');
    titleEl.className = 'mobile-sheet-bar-title';
    const instrumentText = bar.effectiveInstrument || bar.instrument || 'Leer';
    const labelText = bar.effectiveLabel || bar.label || '';
    const displayInstrumentText = getChooserDisplayText(instrumentText, 'instrument');
    const displayLabelText = getChooserDisplayText(labelText, 'function');
    const isContinuationBar = previousBar &&
        !bar.instrument &&
        !bar.label &&
        (bar.effectiveInstrument || '') === (previousBar.effectiveInstrument || '') &&
        (bar.effectiveLabel || '') === (previousBar.effectiveLabel || '');
    const useCompactContinuationLayout = isContinuationBar && !isLandscapeEditor;
    if (useCompactContinuationLayout) {
        cardEl.classList.add('is-continuation-bar');
        const isFirstContinuationBar = previousBar &&
            (previousBar.instrument || previousBar.label);
        if (isFirstContinuationBar) {
            cardEl.classList.add('is-first-continuation-bar');
        }
        const isFollowedBySamePattern = nextBar &&
            !nextBar.instrument &&
            !nextBar.label &&
            (nextBar.effectiveInstrument || '') === (bar.effectiveInstrument || '') &&
            (nextBar.effectiveLabel || '') === (bar.effectiveLabel || '');
        if (!isFollowedBySamePattern) {
            cardEl.classList.add('is-last-continuation-bar');
        }
    }
    titleEl.textContent = isContinuationBar
        ? ''
        : (displayInstrumentText + (displayLabelText ? ' / ' + displayLabelText : ''));
    if (
        titleEl.textContent ||
        isLandscapeEditor ||
        (quickPlayPatternId && isFirstSheetQuickPlayPatternBar(quickPlayPattern, sourceBarIndex))
    ) {
        const titleRowEl = document.createElement('div');
        titleRowEl.className = 'mobile-sheet-bar-heading';
        if (quickPlayPatternId && isFirstSheetQuickPlayPatternBar(quickPlayPattern, sourceBarIndex)) {
            const selectButtonEl = document.createElement('button');
            selectButtonEl.type = 'button';
            selectButtonEl.className = 'mobile-sheet-pattern-toggle';
            selectButtonEl.dataset.patternId = quickPlayPatternId;
            selectButtonEl.setAttribute('aria-label', uiText('editor.selectNamedPattern', {
                name: titleEl.textContent || uiText('score.patternFallback')
            }));
            selectButtonEl.setAttribute('aria-pressed', 'false');
            selectButtonEl.addEventListener('click', function () {
                selectSheetQuickPlayPattern(quickPlayPatternId);
            });
            titleRowEl.appendChild(selectButtonEl);
        }
        if (titleEl.textContent && !isLandscapeEditor) {
            titleRowEl.appendChild(titleEl);
        } else if (!isContinuationBar && isLandscapeEditor) {
            const chooserRowEl = document.createElement('div');
            chooserRowEl.className = 'mobile-sheet-chooser-row';
            chooserRowEl.appendChild(createMobileSheetChooserSelect(
                sourceBarIndex,
                'instrument',
                bar.instrument || instrumentText || 'Leer'
            ));
            chooserRowEl.appendChild(createMobileSheetChooserSelect(
                sourceBarIndex,
                'label',
                bar.label || labelText || 'Leer'
            ));
            const chooserActionsEl = document.createElement('div');
            chooserActionsEl.className = 'mobile-sheet-chooser-actions';
            if (isPatternMoveStart) {
                ['up', 'down'].forEach(function (direction) {
                    const isUp = direction === 'up';
                    const isDisabled = isUp
                        ? patternMoveRangeIndex <= 0
                        : patternMoveRangeIndex >= sheetPatternMoveState.ranges.length - 1;
                    const movePatternButtonEl = document.createElement('button');
                    movePatternButtonEl.type = 'button';
                    movePatternButtonEl.className = 'mobile-sheet-chooser-action mobile-sheet-pattern-move-action';
                    movePatternButtonEl.textContent = isUp ? '↑' : '↓';
                    movePatternButtonEl.disabled = isDisabled;
                    const movePatternLabel = uiText(
                        isUp ? 'editor.moveWholePatternUp' : 'editor.moveWholePatternDown'
                    );
                    movePatternButtonEl.title = movePatternLabel;
                    movePatternButtonEl.setAttribute('aria-label', movePatternLabel);
                    movePatternButtonEl.addEventListener('click', function (event) {
                        event.stopPropagation();
                        moveSheetPatternByDirection(patternMoveRange.id, direction);
                    });
                    chooserActionsEl.appendChild(movePatternButtonEl);
                });
            }
            const moveChooserButtonEl = document.createElement('button');
            moveChooserButtonEl.type = 'button';
            moveChooserButtonEl.className = 'mobile-sheet-chooser-action';
            moveChooserButtonEl.textContent = '↕';
            moveChooserButtonEl.title = uiText('editor.moveChooser');
            moveChooserButtonEl.setAttribute('aria-label', uiText('editor.moveChooserFromBar', {
                bar: sourceBarIndex
            }));
            moveChooserButtonEl.addEventListener('click', function (event) {
                event.stopPropagation();
                setMobileSheetChooserMoveSource(sourceBarIndex);
            });
            const deleteChooserButtonEl = document.createElement('button');
            deleteChooserButtonEl.type = 'button';
            deleteChooserButtonEl.className = 'mobile-sheet-chooser-action';
            deleteChooserButtonEl.textContent = '×';
            deleteChooserButtonEl.title = uiText('editor.deleteChooser');
            deleteChooserButtonEl.setAttribute('aria-label', uiText('editor.deleteChooserFromBar', {
                bar: sourceBarIndex
            }));
            deleteChooserButtonEl.addEventListener('click', function (event) {
                event.stopPropagation();
                deleteMobileSheetBarChoosers(sourceBarIndex);
            });
            chooserActionsEl.append(moveChooserButtonEl, deleteChooserButtonEl);
            chooserRowEl.appendChild(chooserActionsEl);
            titleRowEl.appendChild(chooserRowEl);
        } else if (isLandscapeEditor) {
            const chooserTargetEl = document.createElement('button');
            chooserTargetEl.type = 'button';
            chooserTargetEl.className = 'mobile-sheet-chooser-target';
            chooserTargetEl.textContent = uiText('editor.insertInstrumentAndFunction');
            chooserTargetEl.setAttribute('aria-label', uiText('editor.insertChooserInBar', {
                bar: sourceBarIndex
            }));
            chooserTargetEl.addEventListener('click', function (event) {
                event.stopPropagation();
                if (mobileSheetEditorState.activeTool === 'chooser') {
                    insertMobileSheetBarChoosers(sourceBarIndex);
                } else if (mobileSheetEditorState.movingChooserSourceBarIndex) {
                    moveMobileSheetBarChoosers(sourceBarIndex);
                }
            });
            titleRowEl.appendChild(chooserTargetEl);
        }
        cardEl.appendChild(titleRowEl);
    }

    const barTextEntries = getMobileSheetBarTextEntries(sourceBarIndex);
    if (barTextEntries.length > 0) {
        const commentsEl = document.createElement('div');
        commentsEl.className = 'mobile-sheet-bar-comments';
        barTextEntries.forEach(function (textEntry) {
            if (isLandscapeEditor) {
                const commentInputEl = document.createElement('input');
                commentInputEl.type = 'text';
                commentInputEl.className = 'mobile-sheet-bar-comment-input';
                commentInputEl.value = textEntry.text;
                commentInputEl.setAttribute('aria-label', uiText('editor.barComment', { bar: sourceBarIndex }));
                commentInputEl.autocomplete = 'off';
                commentInputEl.spellcheck = false;
                let commentHistoryRecorded = false;
                commentInputEl.addEventListener('input', function () {
                    if (!commentHistoryRecorded && commentInputEl.value !== textEntry.text) {
                        recordHistorySnapshot();
                        commentHistoryRecorded = true;
                    }
                    textEntry.element.attr({ text: commentInputEl.value });
                });
                commentInputEl.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        commentInputEl.blur();
                    }
                });
                commentInputEl.addEventListener('blur', function () {
                    if (!commentInputEl.value.trim()) {
                        textEntry.element.remove();
                        refreshMobileSheetEditorView();
                    }
                });
                commentsEl.appendChild(commentInputEl);
            } else {
                const commentEl = document.createElement('div');
                commentEl.className = 'mobile-sheet-bar-comment';
                commentEl.textContent = textEntry.text;
                commentsEl.appendChild(commentEl);
            }
        });
        cardEl.appendChild(commentsEl);
    }

    const svgEl = createMobileSheetSvgElement('svg', {
        viewBox: useCompactContinuationLayout ? '-20 6 400 112' : '-20 -8 400 120',
        role: 'img',
        'aria-label': titleEl.textContent
    });
    const visualStaffStartX = 28;
    const visualStaffEndX = 356;
    const topY = 34;
    const noteY = 75;
    const layoutConfig = getMobileSheetLayoutConfig();
    const gridLineStepRel = 850 / layoutConfig.subdivisionCount;
    const firstStaffLineRel = gridLineStepRel;
    const lastStaffLineRel = (layoutConfig.centerDividerIndex - 1) * gridLineStepRel;
    const mobileStaffScale = (visualStaffEndX - visualStaffStartX) / (lastStaffLineRel - firstStaffLineRel);
    const leftX = visualStaffStartX - mobileStaffScale * firstStaffLineRel;
    const rightX = leftX + mobileStaffScale * 425;
    const beatWidth = getMobileSheetScaledX(leftX, rightX, layoutConfig.noteStepRel * (rhythm === 'binaer' ? 8 : 6)) - leftX;
    const noteCenterOffsetX = 0;
    const syllables = layoutConfig.syllables;
    const staffStartX = getMobileSheetScaledX(leftX, rightX, gridLineStepRel);
    const staffEndX = getMobileSheetScaledX(leftX, rightX, (layoutConfig.centerDividerIndex - 1) * gridLineStepRel);
    const lineColor = '#111';
    const firstNoteX = getMobileSheetScaledX(leftX, rightX, layoutConfig.noteStartRel) + noteCenterOffsetX;
    const secondNoteX = getMobileSheetScaledX(leftX, rightX, layoutConfig.noteStartRel + layoutConfig.noteStepRel) + noteCenterOffsetX;
    svgEl.__mobileSheetEditorMetrics = {
        firstNoteX: firstNoteX,
        noteStepX: secondNoteX - firstNoteX,
        staffStartX: staffStartX,
        staffEndX: staffEndX
    };

    svgEl.appendChild(createMobileSheetSvgElement('text', {
        x: staffStartX - 22,
        y: noteY - 4,
        'font-size': 20,
        'font-weight': 'bold',
        fill: '#8b948d',
        'text-anchor': 'end'
    })).textContent = String(barIndex + 1);

    svgEl.appendChild(createMobileSheetSvgElement('line', { x1: staffStartX, y1: topY, x2: staffEndX, y2: topY, stroke: lineColor, 'stroke-width': 2 }));
    svgEl.appendChild(createMobileSheetSvgElement('line', { x1: staffStartX, y1: topY + 5, x2: staffEndX, y2: topY + 5, stroke: lineColor, 'stroke-width': 2 }));

    for (let lineIndex = 1; lineIndex < layoutConfig.centerDividerIndex; lineIndex += 1) {
        const syllableX = getMobileSheetScaledX(leftX, rightX, lineIndex * gridLineStepRel);
        const syllable = syllables[(lineIndex - 1) % syllables.length];
        svgEl.appendChild(createMobileSheetSvgElement('line', {
            x1: syllableX,
            y1: topY,
            x2: syllableX,
            y2: noteY + 12,
            stroke: lineColor,
            'stroke-width': layoutConfig.beatStartIndices.indexOf(lineIndex) !== -1 ? 2.2 : 1
        }));
        svgEl.appendChild(createMobileSheetSvgElement('text', { x: syllableX - 3, y: 28, 'font-size': 10, fill: lineColor })).textContent = syllable;
    }
    layoutConfig.beatStartIndices.forEach(function (beatStartIndex) {
        const beatX = getMobileSheetScaledX(leftX, rightX, beatStartIndex * gridLineStepRel);
        let beatNumber = Math.trunc((beatStartIndex + layoutConfig.beatNumberOffset) / layoutConfig.beatDivisor);
        if (beatNumber > layoutConfig.beatWrapAt) {
            beatNumber -= layoutConfig.beatWrapAt;
        }
        svgEl.appendChild(createMobileSheetSvgElement('text', { x: beatX - 3, y: 14, 'font-size': 10, fill: lineColor })).textContent = String(beatNumber);
    });

    if (bar.repeat && Array.isArray(bar.repeat.start) && bar.repeat.start.length > 0) {
        const repeatStartGroupEl = createMobileSheetSvgElement('g', {
            class: 'mobile-sheet-control',
            'data-source-bar-index': sourceBarIndex,
            'data-source-step-index': 0,
            'data-source-element-type': 'wiederholung'
        });
        appendMobileSheetRepeatMarker(repeatStartGroupEl, staffStartX - 11, noteY - 8, getMobileSheetRepeatText(bar.repeat.start), true);
        svgEl.appendChild(repeatStartGroupEl);
        bindMobileSheetRepeatEditor(repeatStartGroupEl, sourceBarIndex, 'start');
    }
    if (bar.repeat && Array.isArray(bar.repeat.end) && bar.repeat.end.length > 0) {
        const repeatEndGroupEl = createMobileSheetSvgElement('g', {
            class: 'mobile-sheet-control',
            'data-source-bar-index': sourceBarIndex,
            'data-source-step-index': Math.max(0, getMobileSheetStepsPerBar() - 1),
            'data-source-element-type': 'wiederholung'
        });
        appendMobileSheetRepeatMarker(repeatEndGroupEl, staffEndX + 11, noteY - 8, getMobileSheetRepeatText(bar.repeat.end), true);
        svgEl.appendChild(repeatEndGroupEl);
        bindMobileSheetRepeatEditor(repeatEndGroupEl, sourceBarIndex, 'end');
    }

    const notes = Array.isArray(bar.notes) ? bar.notes : [];
    notes.forEach(function (noteValue, stepIndex) {
        if (!noteValue || noteValue === 'f') {
            return;
        }
        const noteX = getMobileSheetScaledX(
            leftX,
            rightX,
            layoutConfig.noteStartRel + stepIndex * layoutConfig.noteStepRel
        );
        const noteGroupEl = createMobileSheetSvgElement('g', {
            class: 'mobile-sheet-note',
            'data-source-bar-index': sourceBarIndex,
            'data-source-step-index': stepIndex
        });
        appendMobileSheetNote(noteGroupEl, noteValue, noteX + noteCenterOffsetX, noteY, beatWidth);
        svgEl.appendChild(noteGroupEl);
        bindMobileSheetNoteEditor(noteGroupEl, svgEl, sourceBarIndex, stepIndex);
    });

    (Array.isArray(bar.controls) ? bar.controls : []).forEach(function (control) {
        const controlX = getMobileSheetScaledX(
            leftX,
            rightX,
            layoutConfig.noteStartRel + (Number(control.stepIndex) || 0) * layoutConfig.noteStepRel
        ) + noteCenterOffsetX;
        const controlGroupEl = createMobileSheetSvgElement('g', {
            class: 'mobile-sheet-control',
            'data-source-bar-index': sourceBarIndex,
            'data-source-step-index': Number(control.stepIndex) || 0,
            'data-source-element-type': control.type || ''
        });
        appendMobileSheetControlMarker(controlGroupEl, control, controlX, topY, noteY);
        svgEl.appendChild(controlGroupEl);
    });

    if (isLandscapeEditor) {
        bindMobileSheetSelectionEditor(svgEl);
        svgEl.addEventListener('click', function (event) {
            const activeTool = mobileSheetEditorState.activeTool;
            if (!activeTool) {
                return;
            }
            if (activeTool === 'select') {
                return;
            }
            if (activeTool === 'paste') {
                const pasteStepIndex = getMobileSheetStepFromPointer(svgEl, event.clientX);
                if (pasteMobileSheetEditorSelection(sourceBarIndex, pasteStepIndex)) {
                    event.preventDefault();
                }
                return;
            }
            const sourceNode = event.target && event.target.closest
                ? event.target.closest('.mobile-sheet-note, .mobile-sheet-control')
                : null;
            if (activeTool === 'delete') {
                if (!sourceNode) {
                    return;
                }
                const sourceType = sourceNode.classList.contains('mobile-sheet-note')
                    ? 'note'
                    : String(sourceNode.dataset.sourceElementType || '');
                if (!sourceType) {
                    return;
                }
                const sourceElements = sourceType === 'wiederholung'
                    ? getMobileSheetRepeatSourceElements(
                        sourceNode.dataset.sourceBarIndex,
                        Number(sourceNode.dataset.sourceStepIndex) === 0 ? 'start' : 'end'
                    )
                    : getMobileSheetSourceElements(
                        sourceNode.dataset.sourceBarIndex,
                        sourceNode.dataset.sourceStepIndex,
                        sourceType
                    );
                if (sourceElements.length > 0) {
                    recordHistorySnapshot();
                    sourceElements.forEach(function (element) { element.remove(); });
                    refreshMobileSheetEditorView();
                }
                event.preventDefault();
                return;
            }

            const stepIndex = getMobileSheetStepFromPointer(svgEl, event.clientX);
            const svgPoint = svgEl.createSVGPoint();
            svgPoint.x = event.clientX;
            svgPoint.y = event.clientY;
            const localPoint = svgPoint.matrixTransform(svgEl.getScreenCTM().inverse());
            insertMobileSheetEditorElement(
                activeTool,
                sourceBarIndex,
                stepIndex,
                instrumentText,
                localPoint.x,
                svgEl.__mobileSheetEditorMetrics
            );
            event.preventDefault();
        });
    }

    cardEl.appendChild(svgEl);
    return cardEl;
}

function getMobileSheetHeaderSubtitleEntry() {
    if (!s || !titel || typeof s.selectAll !== 'function') {
        return null;
    }

    const titleBounds = titel.getBBox();
    const titleCenterY = Number(titleBounds.cy) || Number(titel.attr('y')) || 0;
    const titleStartX = Number(titleBounds.x) || Number(titel.attr('x')) || 0;
    const candidates = [];

    s.selectAll('#edit_text').forEach(function (textEl) {
        const text = String(textEl.attr('text') || '').trim();
        if (!text) {
            return;
        }
        const bounds = textEl.getBBox();
        const centerX = Number(bounds.cx) || 0;
        const centerY = Number(bounds.cy) || 0;
        if (Math.abs(centerY - titleCenterY) > 48 || centerX <= titleStartX + 80) {
            return;
        }
        candidates.push({
            element: textEl,
            text: text,
            x: Number(bounds.x) || centerX
        });
    });

    candidates.sort(function (left, right) {
        return left.x - right.x;
    });
    return candidates.length > 0 ? candidates[0] : null;
}

function getMobileSheetHeaderSubtitle() {
    const subtitleEntry = getMobileSheetHeaderSubtitleEntry();
    return subtitleEntry ? subtitleEntry.text : '';
}

function getMobileSheetEditableTextAnchor(textElement) {
    const translate = typeof getElementTranslate === 'function'
        ? getElementTranslate(textElement)
        : { x: 0, y: 0 };
    const bounds = textElement && typeof textElement.getBBox === 'function'
        ? textElement.getBBox()
        : { x: 0, y: 0 };
    const attributeX = Number(textElement && textElement.attr('x'));
    const attributeY = Number(textElement && textElement.attr('y'));
    return {
        x: (Number.isFinite(attributeX) ? attributeX : Number(bounds.x) || 0) + (Number(translate.x) || 0),
        y: (Number.isFinite(attributeY) ? attributeY : Number(bounds.y) || 0) + (Number(translate.y) || 0)
    };
}

function getMobileSheetBarTextEntries(sourceBarIndex) {
    if (!s || typeof s.selectAll !== 'function') {
        return [];
    }
    const headerSubtitleEntry = getMobileSheetHeaderSubtitleEntry();
    const readConfig = getReadRhythmConfig();
    const normalizedBarIndex = Number(sourceBarIndex);
    const entries = [];
    s.selectAll('#edit_text').forEach(function (textElement) {
        if (
            headerSubtitleEntry &&
            headerSubtitleEntry.element &&
            headerSubtitleEntry.element.node === textElement.node
        ) {
            return;
        }
        const text = String(textElement.attr('text') || '').trim();
        if (!text) {
            return;
        }
        const anchor = getMobileSheetEditableTextAnchor(textElement);
        const positionInfo = getBarIndexFromPosition(anchor.x, anchor.y, readConfig, zeilenAnzahl);
        if (positionInfo.barIndex + 1 === normalizedBarIndex) {
            entries.push({ element: textElement, text: text, x: anchor.x });
        }
    });
    entries.sort(function (left, right) {
        return left.x - right.x;
    });
    return entries;
}

function renderMobileSheetView(readResult) {
    const viewEl = document.getElementById('mobileSheetView');
    if (!viewEl) {
        return;
    }
    sheetQuickPlayState.mobileNoteElementsByPosition = {};
    if (!isMobileSheetReaderViewport() || !document.body.classList.contains('has-loaded-score')) {
        document.body.classList.remove('has-mobile-sheet-view');
        viewEl.hidden = true;
        viewEl.innerHTML = '';
        return;
    }

    const sourceReadResult = readResult || (window.lastReadRhythmBars ? { rhythmBars: window.lastReadRhythmBars } : null);
    const sourceRhythmBars = sourceReadResult && Array.isArray(sourceReadResult.rhythmBars)
        ? sourceReadResult.rhythmBars
        : [];
    const trimmedRhythmBars = trimMobileSheetBars(sourceRhythmBars);
    const rhythmBars = isMobileLandscapeViewport()
        ? sourceRhythmBars
        : (trimmedRhythmBars.length > 0 ? trimmedRhythmBars : sourceRhythmBars.slice(0, 1));
    viewEl.innerHTML = '';
    viewEl.hidden = rhythmBars.length === 0;
    if (rhythmBars.length === 0) {
        document.body.classList.remove('has-mobile-sheet-view');
        return;
    }
    document.body.classList.add('has-mobile-sheet-view');

    const headerEl = document.createElement('div');
    headerEl.className = 'mobile-sheet-header';
    const currentRhythmTitle = titel && typeof titel.attr === 'function'
        ? String(titel.attr('text') || '')
        : '';
    if (isMobileLandscapeViewport()) {
        const titleInputEl = document.createElement('input');
        titleInputEl.type = 'text';
        titleInputEl.className = 'mobile-sheet-title-input';
        titleInputEl.value = isDefaultTitleText(currentRhythmTitle) ? '' : currentRhythmTitle;
        titleInputEl.placeholder = uiText('editor.rhythmName');
        titleInputEl.setAttribute('aria-label', uiText('editor.rhythmName'));
        titleInputEl.autocomplete = 'off';
        titleInputEl.spellcheck = false;
        let titleHistoryRecorded = false;
        titleInputEl.addEventListener('input', function () {
            const nextTitle = titleInputEl.value.trim();
            if (!titleHistoryRecorded && nextTitle !== currentRhythmTitle) {
                recordHistorySnapshot();
                titleHistoryRecorded = true;
            }
            setRhythmTitle(nextTitle);
        });
        titleInputEl.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                titleInputEl.blur();
            }
        });
        headerEl.appendChild(titleInputEl);
    } else {
        const headingEl = document.createElement('h2');
        headingEl.textContent = currentRhythmTitle || uiText('editor.defaultScoreTitle');
        headerEl.appendChild(headingEl);
    }

    const controlsEl = document.createElement('div');
    controlsEl.className = 'mobile-sheet-quick-play-controls';
    controlsEl.setAttribute('aria-label', uiText('editor.quickPlay.aria'));
    const tempoLabelEl = document.createElement('label');
    tempoLabelEl.setAttribute('for', 'mobileSheetQuickPlayTempo');
    tempoLabelEl.textContent = 'BPM';
    const tempoInputEl = document.createElement('input');
    tempoInputEl.type = 'number';
    tempoInputEl.id = 'mobileSheetQuickPlayTempo';
    tempoInputEl.min = '30';
    tempoInputEl.max = '180';
    tempoInputEl.step = '1';
    tempoInputEl.inputMode = 'numeric';
    tempoInputEl.value = String(getSheetQuickPlayTempo());
    tempoInputEl.addEventListener('focus', function () {
        positionMobileSheetQuickPlayFrame();
    });
    tempoInputEl.addEventListener('blur', function () {
        window.requestAnimationFrame(positionMobileSheetQuickPlayFrame);
        window.setTimeout(positionMobileSheetQuickPlayFrame, 300);
    });
    tempoInputEl.addEventListener('change', function () {
        setSheetQuickPlayTempo(tempoInputEl.value);
        if (sheetQuickPlayState.isPlaying) {
            stopSheetQuickPlay();
        }
        scheduleSheetQuickPlayPreparation(0);
    });
    const playButtonEl = document.createElement('button');
    playButtonEl.type = 'button';
    playButtonEl.id = 'mobileSheetQuickPlayButton';
    playButtonEl.setAttribute('aria-pressed', 'false');
    playButtonEl.addEventListener('click', toggleSheetQuickPlay);
    controlsEl.appendChild(tempoLabelEl);
    controlsEl.appendChild(tempoInputEl);
    controlsEl.appendChild(playButtonEl);
    headerEl.appendChild(controlsEl);
    const subtitleEntry = getMobileSheetHeaderSubtitleEntry();
    const subtitle = subtitleEntry ? subtitleEntry.text : '';
    if (isMobileLandscapeViewport()) {
        const subtitleInputEl = document.createElement('input');
        subtitleInputEl.type = 'text';
        subtitleInputEl.className = 'mobile-sheet-comment-input';
        subtitleInputEl.value = subtitle;
        subtitleInputEl.placeholder = uiText('editor.rhythmComment');
        subtitleInputEl.setAttribute('aria-label', uiText('editor.rhythmComment'));
        subtitleInputEl.autocomplete = 'off';
        subtitleInputEl.spellcheck = false;
        let subtitleElement = subtitleEntry ? subtitleEntry.element : null;
        let subtitleHistoryRecorded = false;
        subtitleInputEl.addEventListener('input', function () {
            const nextSubtitle = subtitleInputEl.value;
            if (!subtitleHistoryRecorded && nextSubtitle !== subtitle) {
                recordHistorySnapshot();
                subtitleHistoryRecorded = true;
            }
            if (!subtitleElement && nextSubtitle.trim()) {
                const titleBounds = titel.getBBox();
                const titleY = Number(titel.attr('y')) || Number(titleBounds.cy) || 48;
                const titleStartX = Number(titleBounds.x) || 40;
                const titleEndX = Number(titleBounds.x2) || (titleStartX + (Number(titleBounds.width) || 0));
                const commentX = Math.min(
                    sheetWidth - 220,
                    Math.max(titleEndX + 24, titleStartX + 240)
                );
                subtitleElement = createEditableTextElement(commentX, titleY, nextSubtitle);
            } else if (subtitleElement) {
                subtitleElement.attr({ text: nextSubtitle });
            }
        });
        subtitleInputEl.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                subtitleInputEl.blur();
            }
        });
        subtitleInputEl.addEventListener('blur', function () {
            if (subtitleElement && !subtitleInputEl.value.trim()) {
                subtitleElement.remove();
                subtitleElement = null;
            }
        });
        headerEl.appendChild(subtitleInputEl);
    } else if (subtitle) {
        const subtitleEl = document.createElement('div');
        subtitleEl.className = 'mobile-sheet-subtitle';
        subtitleEl.textContent = subtitle;
        headerEl.appendChild(subtitleEl);
    }
    viewEl.appendChild(headerEl);
    if (isMobileLandscapeViewport()) {
        viewEl.appendChild(createMobileSheetEditorPalette());
    }

    rhythmBars.forEach(function (bar, barIndex) {
        if (!bar) {
            return;
        }
        viewEl.appendChild(createMobileSheetBarElement(bar, barIndex, rhythmBars[barIndex - 1], rhythmBars[barIndex + 1]));
    });
    rebuildMobileSheetQuickPlayNoteElementMap();
    updateSheetQuickPlaySelectionClasses();
    updateMobileSheetSelectionClasses();
    setSheetQuickPlayButtonState(sheetQuickPlayState.isPlaying);
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
            try {
                renderPracticeScrollerFromPayload(playerPayload);
            } catch (scrollerError) {
                console.warn('Laufende Noten konnten nicht aufgebaut werden', scrollerError);
            }
        }
    } else {
        playerPayload = buildTimelinePlayerPayload(timelineState.sourcePatterns, timelineState.entries);
    }

    if (!practiceIsActive && !timelinePayloadHasPlayableEntries(playerPayload)) {
        console.warn('Timeline-Payload ist leer oder nicht spielbar, verwende direkten Notenblatt-Payload.');
        playerPayload = buildPlayerRowsFromRhythmBars(readResult.rhythmBars, readResult.repeatRanges);
    }

    window.lastPlayerRows = playerPayload;
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
        if (!noteElementIds.includes(elementId) && !controlElementIds.includes(elementId) && !tupletElementIds.includes(elementId)) {
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

        if (tupletElementIds.includes(elementId)) {
            const tupletNotes = String(el.attr('data-notes') || '')
                .split(',')
                .map(function (noteId) { return noteId.trim(); })
                .filter(Boolean);
            rhythmBar.notes[stepIndex] = createTupletNoteValue(elementId, tupletNotes, rhythmBar.effectiveInstrument);
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
    if (shouldShowAlert) {
        alert(notenText);
    }

    if (readOptions.updateQuickPlaySelectors !== false) {
        window.setTimeout(function () {
            renderSheetQuickPlaySelectors({
                rhythmBars: rhythmBars,
                repeatBoundaries: repeatBoundaries,
                repeatRanges: repeatRanges,
                summaryText: notenText
            });
        }, 0);
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
        alert(uiText('error.scoreReadFailed', { message: error.message || '' }));
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
    if (typeof refreshPracticeScrollerLayout === 'function') {
        window.requestAnimationFrame(refreshPracticeScrollerLayout);
    }
}

function refreshPracticeAudioPlayer() {
    if (!isPracticeAudioModeActive()) {
        return;
    }
    if (typeof hasPracticePatternSelection === 'function' && !hasPracticePatternSelection()) {
        clearPracticeAudioPlayer();
        return;
    }

    try {
        const playerPanelEl = document.querySelector('.practice-player-panel');
        if (playerPanelEl) {
            playerPanelEl.hidden = false;
        }
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
        delete signatureConfig.PracticeInstrumentToneVolumes;
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

function preparePracticeAudioPlayerReload() {
    const playerPanelEl = document.querySelector('.practice-player-panel');
    window.clearTimeout(practiceAudioRefreshTimer);
    practiceAudioPlaybackState = 'stopped';
    if (playerPanelEl) {
        playerPanelEl.hidden = true;
    }
}

function notifyPracticeSelectionChanged(options) {
    const changeOptions = options && typeof options === 'object' ? options : {};
    if (typeof updateTimelineMetadataNode === 'function') {
        updateTimelineMetadataNode();
    }

    if (typeof hasPracticePatternSelection === 'function' && !hasPracticePatternSelection()) {
        clearPracticeAudioPlayer();
        return;
    }

    if (changeOptions.forcePlayerReload) {
        preparePracticeAudioPlayerReload();
        schedulePracticeAudioRefresh(0);
        return;
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

    // Never leave a stopped player with the previous pattern selection
    // clickable while its replacement is being prepared.
    preparePracticeAudioPlayerReload();
    schedulePracticeAudioRefresh(0);
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
    const mobileArrangementFrameEl = document.getElementById('mobileArrangementAudioFrame');
    const sheetQuickPlayFrameEl = document.getElementById('sheetQuickPlayFrame');
    const isPracticeFrame = practiceFrameEl && event.source === practiceFrameEl.contentWindow;
    const isTimelineFrame = timelineFrameEl && event.source === timelineFrameEl.contentWindow;
    const isMobileArrangementFrame = mobileArrangementFrameEl && event.source === mobileArrangementFrameEl.contentWindow;
    const isSheetQuickPlayFrame = sheetQuickPlayFrameEl && event.source === sheetQuickPlayFrameEl.contentWindow;
    const isArrangementFrame = isTimelineFrame || isMobileArrangementFrame;
    if (!isPracticeFrame && !isArrangementFrame && !isSheetQuickPlayFrame) {
        return;
    }

    const sourceFrameEl = isPracticeFrame
        ? practiceFrameEl
        : isTimelineFrame
            ? timelineFrameEl
            : isMobileArrangementFrame
                ? mobileArrangementFrameEl
                : sheetQuickPlayFrameEl;
    const expectedLaunchKey = sourceFrameEl && sourceFrameEl.dataset
        ? String(sourceFrameEl.dataset.audioLaunchKey || '')
        : '';
    if (expectedLaunchKey && String(message.launchKey || '') !== expectedLaunchKey) {
        return;
    }

    if (isSheetQuickPlayFrame) {
        if (message.type === 'barabeat-audio-step') {
            scheduleSheetQuickPlayNoteHighlights(message);
            return;
        }
        if (message.type === 'barabeat-audio-state') {
            if (message.state === 'ready') {
                sheetQuickPlayState.frameReady = true;
                updateSheetQuickPlayButtonAvailability();
                return;
            }
            if (message.state === 'playing') {
                setSheetQuickPlayButtonState(true);
                startSheetQuickPlaySchedulerPump();
                return;
            }
            if (message.state !== 'playing') {
                clearSheetQuickPlaySchedulerPump();
                setSheetQuickPlayButtonState(false);
                clearSheetQuickPlayHighlights();
            }
            return;
        }
        return;
    }

    if (message.type === 'barabeat-audio-tempo-change') {
        const nextTempo = normalizeTimelineTempo(message.tempo);
        if (timelineState.tempo !== nextTempo) {
            recordArrangementHistorySnapshot();
        }
        timelineState.tempo = nextTempo;
        window.suppressNextTimelineAudioRefresh = isArrangementFrame;
        updateTimelineMetadataNode();
        renderTimelinePanel();
        return;
    }

    if (isPracticeFrame && message.type === 'barabeat-audio-step' && typeof updatePracticeScrollerPlayback === 'function') {
        updatePracticeScrollerPlayback(message.playbackStep, message.delayMs);
        return;
    }

    if (message.type === 'barabeat-audio-state') {
        if (isArrangementFrame) {
            timelineAudioPlaybackState = message.state || 'stopped';
            return;
        }
        practiceAudioPlaybackState = message.state || 'stopped';
        if (isPracticeFrame && typeof updatePracticeScrollerState === 'function') {
            updatePracticeScrollerState(message.state, message.leadInMs, message.delayMs, message.countInMs);
        }
    }
}

function clearPracticeAudioPlayer() {
    const playerPanelEl = document.querySelector('.practice-player-panel');
    const playerFrameEl = document.getElementById('practiceAudioFrame');
    window.clearTimeout(practiceAudioRefreshTimer);
    practiceAudioPlaybackState = 'stopped';
    if (playerPanelEl) {
        playerPanelEl.hidden = true;
    }
    if (playerFrameEl) {
        playerFrameEl.src = 'about:blank';
    }
    if (typeof renderPracticeScrollerFromPayload === 'function') {
        renderPracticeScrollerFromPayload([{ PracticeSections: [] }]);
    } else if (typeof clearPracticeScrollerPlayback === 'function') {
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
    const name = (nameOverride || titel.attr('text') || uiText('editor.untitled')).trim();
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
        showAutoDismissMessage(uiText('file.message.savedLocal', { title: savedScore.title }));
        closeAppMenus();
    } catch (error) {
        console.error('Lokales Speichern fehlgeschlagen', error);
        alert(uiText('file.error.localSave', { message: error.message || '' }));
    }
}

function callPHPScript() {
    saveCurrentScoreFromMenu();
}

async function renameLocalScore() {
    try {
        const scoreId = getSelectedLocalScoreId();
        if (!scoreId) {
            alert(uiText('file.error.selectLocalToRename'));
            return;
        }

        const score = await localLibrary.getScore(scoreId);
        if (!score) {
            alert(uiText('file.error.localNotFound'));
            return;
        }

        const nextTitle = prompt(uiText('file.prompt.newName'), score.title || '');
        if (nextTitle == null) {
            return;
        }

        const trimmedTitle = nextTitle.trim();
        if (!trimmedTitle) {
            alert(uiText('file.error.nameEmpty'));
            return;
        }

        const renamedScore = await localLibrary.saveScore(Object.assign({}, score, {
            title: trimmedTitle
        }));

        currentScoreId = renamedScore.id;
        setRhythmTitle(renamedScore.title);
        setSelectedFileSource('local');
        await refreshFileList();
        alert(uiText('file.message.renamedLocal', { title: renamedScore.title }));
    } catch (error) {
        console.error('Lokales Umbenennen fehlgeschlagen', error);
        alert(uiText('file.error.localRename', { message: error.message || '' }));
    }
}

async function deleteLocalScore() {
    try {
        const scoreId = getSelectedLocalScoreId();
        if (!scoreId) {
            alert(uiText('file.error.selectLocal'));
            return;
        }

        const score = await localLibrary.getScore(scoreId);
        if (!score) {
            alert(uiText('file.error.localNotFound'));
            return;
        }

        const shouldDelete = confirm(uiText('file.confirm.deleteLocal', { title: score.title }));
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
        alert(uiText('file.message.deletedLocal', { title: score.title }));
    } catch (error) {
        console.error('Lokales Löschen fehlgeschlagen', error);
        alert(uiText('file.error.localDelete', { message: error.message || '' }));
    }
}

async function publishCurrentScoreToServer(nameOverride) {
    if (isOfflineColdStart) {
        throw new Error(uiText('offline.onlineOnly'));
    }
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
        alert(uiText('file.message.published', { title: publishedScore.title }));
    } catch (error) {
        console.error('Veröffentlichen fehlgeschlagen', error);
        alert(uiText('file.error.publish', { message: error.message || '' }));
    }
}

function applyDialogNameToTitle() {
    const nameValue = String(document.querySelector('#fileDialogName')?.value || '').trim();
    if (nameValue) {
        setRhythmTitle(nameValue);
    }
    return nameValue || titel.attr('text') || uiText('editor.untitled');
}

async function openLocalScore(scoreId) {
    const score = await localLibrary.getScore(scoreId);
    if (!score) {
        throw new Error(uiText('file.error.localNotFound'));
    }
    loadRhythmContent(score.title, score.content || score.data, score.id);
    return score;
}

async function importServerScore(serverPath, serverInfo) {
    const resolvedServerInfo = serverInfo || await findServerScoreInfo(serverPath);
    const serverScore = await serverLibrary.importScore(serverPath);
    const serverDownloadedAt = new Date().toISOString();
    const serverUpdatedAt = resolvedServerInfo && resolvedServerInfo.serverUpdatedAt
        ? resolvedServerInfo.serverUpdatedAt
        : serverScore.serverUpdatedAt;
    const serverModifiedTs = getServerInfoModifiedTs(resolvedServerInfo || serverScore);
    const savedScore = await localLibrary.findScoreByServerPath(serverScore.serverPath).then(function (existingScore) {
        if (existingScore) {
            const contentDiffers = normalizeScoreContentForCompare(existingScore.content || existingScore.data) !==
                normalizeScoreContentForCompare(serverScore.content);
            const timestampIsNewer = serverModifiedTs > 0 &&
                getScoreServerModifiedTs(existingScore) > 0 &&
                serverModifiedTs > getScoreServerModifiedTs(existingScore);
            if (!contentDiffers && !timestampIsNewer) {
                return localLibrary.updateScoreMetadata(existingScore.id, {
                    serverDownloadedAt: serverDownloadedAt,
                    serverUpdatedAt: serverUpdatedAt || existingScore.serverUpdatedAt || '',
                    serverModifiedTs: serverModifiedTs || getScoreServerModifiedTs(existingScore),
                    serverVersion: serverUpdatedAt || existingScore.serverVersion || '',
                    serverUpdateAvailable: false
                });
            }
            if (existingScore.syncState === 'modified-local') {
                const shouldReplace = confirm(uiText('file.confirm.replaceLocalChanges', {
                    title: existingScore.title
                }));
                if (!shouldReplace) {
                    const cancelError = new Error(uiText('file.error.serverVersionCancelled'));
                    cancelError.isUserCancel = true;
                    throw cancelError;
                }
            }
            return localLibrary.saveScore(Object.assign({}, existingScore, {
                title: serverScore.title,
                format: serverScore.format,
                content: serverScore.content,
                data: serverScore.content,
                isPublished: true,
                serverPath: serverScore.serverPath,
                syncState: 'published',
                serverDownloadedAt: serverDownloadedAt,
                serverUpdatedAt: serverUpdatedAt || '',
                serverModifiedTs: serverModifiedTs,
                serverVersion: serverUpdatedAt || '',
                serverUpdateAvailable: false
            }));
        }

        return localLibrary.saveScore({
            title: serverScore.title,
            folderId: localLibrary.rootFolderId,
            format: serverScore.format,
            content: serverScore.content,
            data: serverScore.content,
            isPublished: true,
            serverPath: serverScore.serverPath,
            syncState: 'published',
            serverDownloadedAt: serverDownloadedAt,
            serverUpdatedAt: serverUpdatedAt || '',
            serverModifiedTs: serverModifiedTs,
            serverVersion: serverUpdatedAt || ''
        });
    });

    loadRhythmContent(savedScore.title, savedScore.content, savedScore.id);
    setSelectedFileSource('local');
    await refreshFileList();
    return savedScore;
}

async function loadFileDialogServerNoticeVersion() {
    const serverPath = fileDialogState.serverNoticePath;
    if (!serverPath) {
        return;
    }
    try {
        const serverInfo = await findServerScoreInfo(serverPath);
        const savedScore = await importServerScore(serverPath, serverInfo);
        closeFileDialog();
        showAutoDismissMessage(uiText('file.message.loadedFromServer', { title: savedScore.title }));
    } catch (error) {
        if (error && error.isUserCancel) {
            return;
        }
        console.error('Serverversion konnte nicht geladen werden', error);
        alert(uiText('file.error.serverVersionLoad', { message: error.message || '' }));
    }
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
                await importServerScore(entry.serverPath || entry.fileName, entry);
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
            alert(uiText('file.message.published', { title: publishedScore.title }));
            return;
        }

        const savedScore = await saveCurrentScoreLocal(chosenName, fileDialogState.folderId, {
            asCopy: fileDialogState.mode === 'saveAs'
        });
        closeFileDialog();
        showAutoDismissMessage(uiText('file.message.savedLocal', { title: savedScore.title }));
    } catch (error) {
        if (error && error.isUserCancel) {
            return;
        }
        console.error('Dateidialog-Aktion fehlgeschlagen', error);
        alert(uiText('file.error.generic', { message: error.message || '' }));
    }
}

async function createFileDialogFolder() {
    if (fileDialogState.source !== 'local' || fileDialogState.mode === 'export') {
        return;
    }

    const folderName = prompt(uiText('file.prompt.newFolderName'), uiText('file.dialog.newFolder'));
    if (folderName == null) {
        return;
    }

    const trimmedName = folderName.trim();
    if (!trimmedName) {
        alert(uiText('file.error.folderNameEmpty'));
        return;
    }

    try {
        const folder = await localLibrary.createFolder(trimmedName, fileDialogState.folderId);
        await navigateFileDialogFolder(folder.id);
    } catch (error) {
        console.error('Ordner konnte nicht erstellt werden', error);
        alert(uiText('file.error.folderCreate', { message: error.message || '' }));
    }
}

async function renameSelectedFileDialogScore() {
    const entry = getSelectedFileDialogEntry();
    if (!entry || fileDialogState.source !== 'local') {
        return;
    }

    if (entry.entryType === 'folder') {
        const nextName = prompt(uiText('file.prompt.renameFolder'), entry.name || entry.title || '');
        if (nextName == null) {
            return;
        }

        const trimmedName = nextName.trim();
        if (!trimmedName) {
            alert(uiText('file.error.folderNameEmpty'));
            return;
        }

        try {
            const renamedFolder = await localLibrary.renameFolder(entry.id, trimmedName);
            fileDialogState.selectedId = renamedFolder.id;
            await refreshFileDialogEntries();
        } catch (error) {
            console.error('Lokaler Ordner konnte nicht umbenannt werden', error);
            alert(uiText('file.error.folderRename', { message: error.message || '' }));
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
            alert(uiText('file.error.folderNotEmpty'));
            return;
        }

        const shouldDeleteFolder = confirm(uiText('file.confirm.deleteEmptyFolder', {
            name: entry.name || entry.title
        }));
        if (!shouldDeleteFolder) {
            return;
        }

        try {
            await localLibrary.deleteFolder(entry.id);
            fileDialogState.selectedId = null;
            await refreshFileDialogEntries();
        } catch (error) {
            console.error('Lokaler Ordner konnte nicht gelöscht werden', error);
            alert(uiText('file.error.folderDelete', { message: error.message || '' }));
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
    if (isOfflineColdStart) {
        alert(uiText('offline.onlineOnly'));
        return;
    }
    const entry = getSelectedFileDialogEntry();
    if (!entry || fileDialogState.source !== 'local') {
        return;
    }

    if (!entry.serverPath || !entry.publishToken) {
        alert(uiText('file.error.publishTokenMissing'));
        return;
    }

    const shouldDelete = confirm(uiText('file.confirm.deleteFromServer', {
        title: entry.title || entry.serverPath
    }));
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
        alert(uiText('file.message.publicationDeleted'));
    } catch (error) {
        console.error('Veröffentlichung konnte nicht gelöscht werden', error);
        alert(uiText('file.error.publicationDelete', { message: error.message || '' }));
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

async function logoutBarabeat() {
    if (isOfflineColdStart) {
        alert(uiText('offline.onlineOnly'));
        return;
    }
    const logoutButtonEl = document.getElementById('accessLogoutButton');
    if (logoutButtonEl) {
        logoutButtonEl.disabled = true;
        logoutButtonEl.textContent = uiText('auth.loggingOut');
    }

    try {
        if ('caches' in window) {
            const cacheNames = await window.caches.keys();
            await Promise.all(cacheNames
                .filter(function (cacheName) {
                    return cacheName.indexOf('barabeat-studio-offline-') === 0;
                })
                .map(function (cacheName) {
                    return window.caches.delete(cacheName);
                }));
        }
    } catch (error) {
        console.warn('Offline-Cache konnte beim Abmelden nicht geleert werden', error);
    }

    window.location.assign('index.php?barabeat_logout=1');
}

const temporaryAccessControlState = {
    csrfToken: String(window.BaraBeatAccessConfig && window.BaraBeatAccessConfig.csrfToken || ''),
    deadlineMs: Date.now() + (<?php echo (int) $temporaryAccessRemaining; ?> * 1000),
    busy: false
};

function getTemporaryAccessRemainingSeconds() {
    return Math.max(0, Math.ceil((temporaryAccessControlState.deadlineMs - Date.now()) / 1000));
}

function formatTemporaryAccessRemaining(seconds) {
    const safeSeconds = Math.max(0, Number(seconds) || 0);
    const minutes = Math.floor(safeSeconds / 60);
    const restSeconds = Math.floor(safeSeconds % 60);
    return minutes + ':' + String(restSeconds).padStart(2, '0');
}

function updateTemporaryAccessButton() {
    const buttonEl = document.getElementById('temporaryAccessButton');
    if (!buttonEl) {
        return;
    }

    const remainingSeconds = getTemporaryAccessRemainingSeconds();
    buttonEl.classList.toggle('is-open', remainingSeconds > 0);
    buttonEl.disabled = temporaryAccessControlState.busy;
    if (temporaryAccessControlState.busy) {
        buttonEl.textContent = uiText('auth.temporaryChanging');
    } else if (remainingSeconds > 0) {
        buttonEl.textContent = uiText('auth.temporaryClose', {
            time: formatTemporaryAccessRemaining(remainingSeconds)
        });
    } else {
        buttonEl.textContent = uiText('auth.temporaryOpen');
    }
}

async function toggleTemporaryAccessWindow() {
    if (isOfflineColdStart) {
        alert(uiText('offline.onlineOnly'));
        return;
    }
    if (temporaryAccessControlState.busy) {
        return;
    }

    const isOpen = getTemporaryAccessRemainingSeconds() > 0;
    if (!isOpen && !window.confirm(uiText('auth.temporaryConfirm'))) {
        return;
    }

    temporaryAccessControlState.busy = true;
    updateTemporaryAccessButton();
    try {
        const formData = new URLSearchParams();
        formData.set('action', isOpen ? 'close' : 'open');
        formData.set('csrf', temporaryAccessControlState.csrfToken);
        const response = await fetch('PHP/access_window.php', {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: formData.toString()
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || uiText('auth.changeFailed'));
        }

        temporaryAccessControlState.deadlineMs = Date.now() + (Math.max(0, Number(result.remainingSeconds) || 0) * 1000);
        showAutoDismissMessage(result.message || uiText('auth.changed'), 3500);
    } catch (error) {
        window.alert(error && error.message ? error.message : uiText('auth.changeFailed'));
    } finally {
        temporaryAccessControlState.busy = false;
        updateTemporaryAccessButton();
    }
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
    document.querySelector('#accessLogoutButton').addEventListener('click', logoutBarabeat);
    const temporaryAccessButtonEl = document.getElementById('temporaryAccessButton');
    if (temporaryAccessButtonEl) {
        temporaryAccessButtonEl.addEventListener('click', toggleTemporaryAccessWindow);
        updateTemporaryAccessButton();
        window.setInterval(updateTemporaryAccessButton, 1000);
    }
    document.querySelector('#fileDialogCancelButton').addEventListener('click', closeFileDialog);
    document.querySelector('#fileDialogConfirmButton').addEventListener('click', confirmFileDialog);
    document.querySelector('#fileDialogRefreshButton').addEventListener('click', refreshFileDialogEntries);
    document.querySelector('#fileDialogServerNotice button').addEventListener('click', loadFileDialogServerNoticeVersion);
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
            setFileDialogServerNotice('', '');
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
    document.querySelector('#button4').addEventListener('click', function () {
        recordHistorySnapshot();
        viererNoten();
        if (isMobileLandscapeViewport()) {
            refreshMobileSheetEditorView();
        }
    });
    document.querySelector('#button5').addEventListener('click', function () {
        recordHistorySnapshot();
        dreierNoten();
        if (isMobileLandscapeViewport()) {
            refreshMobileSheetEditorView();
        }
    });
    document.querySelector('#button8').addEventListener('click', function () {
        recordHistorySnapshot();
        neunerNoten();
        if (isMobileLandscapeViewport()) {
            refreshMobileSheetEditorView();
        }
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
            alert(uiText('arrangement.error.build', { message: error.message || '' }));
        }
    });
    document.querySelector('#sheetQuickPlayButton').addEventListener('click', function () {
        toggleSheetQuickPlay();
    });
    document.querySelector('#sheetQuickPlayTempo').addEventListener('change', function () {
        getSheetQuickPlayTempo();
        if (sheetQuickPlayState.isPlaying) {
            stopSheetQuickPlay();
            startSheetQuickPlay();
        }
    });
    window.addEventListener('resize', positionSheetQuickPlayControls);
    window.addEventListener('scroll', positionSheetQuickPlayControls, { passive: true });
    window.addEventListener('resize', positionMobileSheetQuickPlayFrame);
    window.addEventListener('scroll', positionMobileSheetQuickPlayFrame, { passive: true });
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', positionMobileSheetQuickPlayFrame);
        window.visualViewport.addEventListener('scroll', positionMobileSheetQuickPlayFrame, { passive: true });
    }
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
            alert(uiText('practice.error.build', { message: error.message || '' }));
        }
    });
    document.querySelector('#practiceRefreshButton').addEventListener('click', function () {
        try {
            refreshPracticeFromSheet(true);
            schedulePracticeAudioRefresh(0);
        } catch (error) {
            console.error('Übungsmodus-Refresh fehlgeschlagen', error);
            alert(uiText('practice.error.refresh', { message: error.message || '' }));
        }
    });
    document.querySelector('#practiceCloseButton').addEventListener('click', function () {
        practiceState.visible = false;
        clearPracticeAudioPlayer();
        renderPracticePanel();
    });
    function setInitialPracticePatternColumnStates() {
        const isMobileViewport = isMobilePracticeViewport();
        document.querySelectorAll('#practicePatternChooser .practice-column, #practicePatternChooser .practice-settings-column').forEach(function (columnEl) {
            const toggleEl = columnEl.querySelector('.practice-column-toggle');
            const collapsed = isMobileViewport || columnEl.classList.contains('practice-settings-column');
            columnEl.classList.toggle('is-collapsed', collapsed);
            if (toggleEl) {
                toggleEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }
        });
    }

    function togglePracticePatternChooser() {
        const nextExpanded = !practiceState.patternChooserExpanded;
        practiceState.patternChooserExpanded = nextExpanded;
        if (nextExpanded) {
            setInitialPracticePatternColumnStates();
        }
        renderPracticePanel();
    }

    document.querySelector('#practicePatternChooserToggle').addEventListener('click', function () {
        togglePracticePatternChooser();
    });
    document.querySelector('#mobilePatternChooserButton').addEventListener('click', function () {
        togglePracticePatternChooser();
        if (practiceState.patternChooserExpanded && isMobilePracticeViewport()) {
            const panelEl = document.getElementById('practicePanel');
            const chooserEl = document.getElementById('practicePatternChooser');
            if (panelEl && chooserEl) {
                window.setTimeout(function () {
                    panelEl.scrollTo({ top: Math.max(0, chooserEl.offsetTop - 8), behavior: 'smooth' });
                }, 0);
            }
        }
    });
    document.querySelector('#mobileArrangementPlayerButton').addEventListener('click', function () {
        openMobileArrangementPlayer();
    });
    document.querySelector('#mobileArrangementCloseButton').addEventListener('click', function () {
        closeMobileArrangementPlayer();
    });
    document.querySelector('#mobileArrangementOverlay').addEventListener('click', function (event) {
        if (event.target === event.currentTarget) {
            closeMobileArrangementPlayer();
        }
    });
    document.querySelectorAll('.practice-column-toggle').forEach(function (toggleEl) {
        toggleEl.addEventListener('click', function () {
            const columnEl = toggleEl.closest('.practice-column, .practice-settings-column');
            if (!columnEl) {
                return;
            }
            const nextCollapsed = !columnEl.classList.contains('is-collapsed');
            columnEl.classList.toggle('is-collapsed', nextCollapsed);
            toggleEl.setAttribute('aria-expanded', nextCollapsed ? 'false' : 'true');
        });
    });
    const practiceScenarioSelectEl = document.querySelector('#practiceScenarioSelect');
    if (practiceScenarioSelectEl) {
        practiceScenarioSelectEl.addEventListener('change', function (event) {
            if (typeof applyPracticeScenario === 'function') {
                applyPracticeScenario(event.target.value);
            }
        });
    }
    const practiceScenarioHeaderSelectEl = document.querySelector('#practiceScenarioHeaderSelect');
    if (practiceScenarioHeaderSelectEl) {
        practiceScenarioHeaderSelectEl.addEventListener('change', function (event) {
            if (typeof applyPracticeScenario === 'function') {
                applyPracticeScenario(event.target.value);
            }
        });
    }
    const practiceScenarioSaveButtonEl = document.querySelector('#practiceScenarioSaveButton');
    if (practiceScenarioSaveButtonEl) {
        practiceScenarioSaveButtonEl.addEventListener('click', function () {
            if (typeof saveActivePracticeScenario === 'function') {
                saveActivePracticeScenario();
            }
        });
    }
    const practiceScenarioNewButtonEl = document.querySelector('#practiceScenarioNewButton');
    if (practiceScenarioNewButtonEl) {
        practiceScenarioNewButtonEl.addEventListener('click', function () {
            if (typeof createPracticeScenarioFromCurrent === 'function') {
                createPracticeScenarioFromCurrent();
            }
        });
    }
    const practiceScenarioDeleteButtonEl = document.querySelector('#practiceScenarioDeleteButton');
    if (practiceScenarioDeleteButtonEl) {
        practiceScenarioDeleteButtonEl.addEventListener('click', function () {
            if (typeof deleteActivePracticeScenario === 'function') {
                deleteActivePracticeScenario();
            }
        });
    }
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
        if (isMobilePracticeViewport() && typeof storePracticeAudioLatency === 'function') {
            storePracticeAudioLatency(normalizedValue);
        }
        const audioLatencyEl = document.querySelector('#practiceAudioLatency');
        const audioLatencyRangeEl = document.querySelector('#practiceAudioLatencyRange');
        const mobileAudioLatencyEl = document.querySelector('#mobilePracticeAudioLatency');
        const mobileAudioLatencyRangeEl = document.querySelector('#mobilePracticeAudioLatencyRange');
        if (audioLatencyEl) {
            audioLatencyEl.value = practiceState.audioLatencyMs;
        }
        if (audioLatencyRangeEl) {
            audioLatencyRangeEl.value = practiceState.audioLatencyMs;
        }
        if (mobileAudioLatencyEl) {
            mobileAudioLatencyEl.value = practiceState.audioLatencyMs;
        }
        if (mobileAudioLatencyRangeEl) {
            mobileAudioLatencyRangeEl.value = practiceState.audioLatencyMs;
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
    document.querySelector('#mobilePracticeAudioLatency').addEventListener('input', function (event) {
        updatePracticeAudioLatencyControl(event.target.value);
    });
    document.querySelector('#mobilePracticeAudioLatencyRange').addEventListener('input', function (event) {
        updatePracticeAudioLatencyControl(event.target.value);
    });
    function openPracticeBluetoothLatencyDialog() {
        updatePracticeAudioLatencyControl(practiceState.audioLatencyMs);
        document.querySelector('#practiceBluetoothLatencyDialog').hidden = false;
    }
    function closePracticeBluetoothLatencyDialog() {
        document.querySelector('#practiceBluetoothLatencyDialog').hidden = true;
    }
    document.querySelector('#mobileBluetoothLatencyButton').addEventListener('click', openPracticeBluetoothLatencyDialog);
    document.querySelector('#practiceBluetoothLatencyCloseButton').addEventListener('click', closePracticeBluetoothLatencyDialog);
    document.querySelector('#practiceBluetoothLatencyDoneButton').addEventListener('click', closePracticeBluetoothLatencyDialog);
    document.querySelector('#practiceBluetoothLatencyDialog').addEventListener('click', function (event) {
        if (event.target && event.target.id === 'practiceBluetoothLatencyDialog') {
            closePracticeBluetoothLatencyDialog();
        }
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
            alert(uiText('arrangement.error.refresh', { message: error.message || '' }));
        }
    });
    document.querySelector('#timelineCloseButton').addEventListener('click', function () {
        timelineState.visible = false;
        clearTimelineAudioPlayer();
        renderTimelinePanel();
    });
    function getSwingProfileTitle() {
        const currentProfileKey = getCurrentTimelineSwingProfileKey();
        const meter = currentProfileKey === 'binaer'
            ? '16/8'
            : (currentProfileKey === 'tenaer' ? '12/8' : '9/8');
        return uiText('practice.dialog.swingProfileMeter', { meter: meter });
    }
    function getSwingProfileInputIds(profileIndex) {
        return [
            'timelineSwingAnchor' + (profileIndex + 1),
            'practiceSwingAnchor' + (profileIndex + 1)
        ];
    }
    function setSwingProfileAnchorValue(profileIndex, rawValue) {
        const currentProfileKey = getCurrentTimelineSwingProfileKey();
        const nextProfiles = normalizeAllTimelineSwingProfiles(timelineState.swingProfile);
        const currentProfile = normalizeTimelineSwingProfile(
            nextProfiles[currentProfileKey],
            currentProfileKey
        );
        if (profileIndex < 0 || profileIndex >= currentProfile.length) {
            return {
                changed: false,
                value: 0
            };
        }

        const nextValue = normalizeSwingProfileValue(rawValue);
        const changed = currentProfile[profileIndex] !== nextValue;
        currentProfile[profileIndex] = nextValue;
        nextProfiles[currentProfileKey] = currentProfile;
        timelineState.swingProfile = nextProfiles;
        getSwingProfileInputIds(profileIndex).forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (inputEl) {
                inputEl.value = nextValue;
            }
        });
        return {
            changed: changed,
            value: nextValue
        };
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
            titleEl.textContent = profileTitle;
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
            const shiftedLineEl = addSvgElement('line', {
                x1: shiftedX,
                y1: noteY - 22,
                x2: shiftedX,
                y2: noteY + 28,
                stroke: '#9ab9a7',
                'stroke-width': 2
            });
            const noteCircleEl = addSvgElement('circle', {
                cx: shiftedX,
                cy: noteY,
                r: 7,
                fill: '#111',
                stroke: 'transparent',
                'stroke-width': 14,
                'paint-order': 'stroke',
                class: 'swing-profile-note'
            });
            const noteLabelEl = addSvgElement('text', {
                x: shiftedX,
                y: noteY - 30,
                'text-anchor': 'middle',
                'font-size': 12,
                fill: '#333'
            });
            noteLabelEl.textContent = 'S' + (profileIndex + 1);

            let activePointerId = null;
            let dragChanged = false;
            let historyRecorded = false;

            function getPreviewPointerX(event) {
                const screenMatrix = svgEl.getScreenCTM();
                if (!screenMatrix || typeof svgEl.createSVGPoint !== 'function') {
                    return neutralX;
                }
                const point = svgEl.createSVGPoint();
                point.x = Number(event.clientX) || 0;
                point.y = Number(event.clientY) || 0;
                return point.matrixTransform(screenMatrix.inverse()).x;
            }

            function updateDraggedNote(event) {
                if (activePointerId === null || event.pointerId !== activePointerId) {
                    return;
                }
                event.preventDefault();
                const maxShift = stepWidth * 0.5;
                const pointerX = getPreviewPointerX(event);
                const clampedX = Math.max(
                    neutralX - maxShift,
                    Math.min(neutralX + maxShift, pointerX)
                );
                const rawValue = Math.round(((clampedX - neutralX) / stepWidth) * 100);
                const previousValue = normalizeTimelineSwingProfile(
                    timelineState.swingProfile && timelineState.swingProfile[currentProfileKey],
                    currentProfileKey
                )[profileIndex];
                if (previousValue !== rawValue && !historyRecorded) {
                    recordArrangementHistorySnapshot();
                    historyRecorded = true;
                }
                const updateResult = setSwingProfileAnchorValue(profileIndex, rawValue);
                if (!updateResult.changed) {
                    return;
                }
                dragChanged = true;
                const renderedX = neutralX + (updateResult.value / 100) * stepWidth;
                shiftedLineEl.setAttribute('x1', renderedX);
                shiftedLineEl.setAttribute('x2', renderedX);
                noteCircleEl.setAttribute('cx', renderedX);
                noteLabelEl.setAttribute('x', renderedX);
            }

            function finishDraggedNote(event) {
                if (activePointerId === null || event.pointerId !== activePointerId) {
                    return;
                }
                if (typeof noteCircleEl.releasePointerCapture === 'function' &&
                        typeof noteCircleEl.hasPointerCapture === 'function' &&
                        noteCircleEl.hasPointerCapture(activePointerId)) {
                    noteCircleEl.releasePointerCapture(activePointerId);
                }
                activePointerId = null;
                noteCircleEl.classList.remove('is-dragging');
                if (dragChanged) {
                    notifyTimingControlsChanged();
                }
            }

            noteCircleEl.addEventListener('pointerdown', function (event) {
                if (activePointerId !== null) {
                    return;
                }
                event.preventDefault();
                activePointerId = event.pointerId;
                dragChanged = false;
                historyRecorded = false;
                noteCircleEl.classList.add('is-dragging');
                if (typeof noteCircleEl.setPointerCapture === 'function') {
                    noteCircleEl.setPointerCapture(activePointerId);
                }
            });
            noteCircleEl.addEventListener('pointermove', updateDraggedNote);
            noteCircleEl.addEventListener('pointerup', finishDraggedNote);
            noteCircleEl.addEventListener('pointercancel', finishDraggedNote);
        });

        addSvgElement('text', {
            x: left,
            y: 28,
            'font-size': 13,
            fill: '#333'
        }).textContent = uiText('practice.dialog.profilePreview', { profile: profileTitle });
        addSvgElement('text', {
            x: left,
            y: 136,
            'font-size': 12,
            fill: '#666'
        }).textContent = uiText('practice.dialog.profilePreviewLegend');

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
    function syncPracticeTempoRampControls() {
        const enabledEl = document.querySelector('#practiceTempoRampEnabled');
        const startEl = document.querySelector('#practiceTempoRampStart');
        const endEl = document.querySelector('#practiceTempoRampEnd');
        const everyEl = document.querySelector('#practiceTempoRampEvery');
        const stepEl = document.querySelector('#practiceTempoRampStep');
        if (enabledEl) {
            enabledEl.checked = Boolean(practiceState.tempoRampEnabled);
        }
        if (startEl) {
            startEl.value = normalizePracticeTempo(practiceState.tempoRampStart, timelineState.tempo);
        }
        if (endEl) {
            endEl.value = normalizePracticeTempo(practiceState.tempoRampEnd, timelineState.tempo);
        }
        if (everyEl) {
            everyEl.value = normalizePracticeTempoRampEvery(practiceState.tempoRampEvery);
        }
        if (stepEl) {
            stepEl.value = normalizePracticeTempoRampStep(practiceState.tempoRampStep);
        }
        const rampButtonEl = document.querySelector('#practiceTempoRampButton');
        if (rampButtonEl) {
            const rampConfig = getPracticeTempoRampConfig();
            rampButtonEl.classList.toggle('is-active', rampConfig.enabled);
            rampButtonEl.textContent = rampConfig.enabled
                ? uiText('practice.tempoRamp.activeButton', {
                    start: rampConfig.startTempo,
                    end: rampConfig.endTempo
                })
                : uiText('practice.controls.tempoRamp');
        }
    }
    function openPracticeTempoRampDialog() {
        const dialogEl = document.querySelector('#practiceTempoRampDialog');
        if (!dialogEl) {
            return;
        }
        syncPracticeTempoRampControls();
        dialogEl.hidden = false;
    }
    function closePracticeTempoRampDialog() {
        const dialogEl = document.querySelector('#practiceTempoRampDialog');
        if (dialogEl) {
            dialogEl.hidden = true;
        }
    }
    function notifyPracticeTempoRampChanged() {
        syncPracticeTempoRampControls();
        if (typeof updatePracticeInputs === 'function') {
            updatePracticeInputs();
        }
        updateTimelineMetadataNode();
        notifyPracticeSelectionChanged();
    }
    function syncTimingControlValues() {
        ['timelineTempo', 'practiceTempo'].forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (inputEl) {
                inputEl.value = normalizeTimelineTempo(timelineState.tempo);
            }
        });
        ['timelineShekereBeat', 'practiceShekereBeat'].forEach(function (inputId) {
            const inputEl = document.querySelector('#' + inputId);
            if (inputEl) {
                inputEl.setAttribute('aria-pressed', timelineState.shekereBeatEnabled ? 'true' : 'false');
                inputEl.classList.toggle('is-active', Boolean(timelineState.shekereBeatEnabled));
            }
        });
        syncPracticeTempoRampControls();
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
        if (timelineState.visible && typeof renderTimelineSequence === 'function') {
            renderTimelineSequence();
        }
        renderPracticeSwingProfilePreview();
        updateTimelineMetadataNode();
        if (isPracticeAudioModeActive() && practiceAudioPlaybackState !== 'playing') {
            schedulePracticeAudioRefresh(250);
        }
    }
    function notifyShekereBeatChanged() {
        syncTimingControlValues();
        window.suppressNextTimelineAudioRefresh = true;
        updateTimelineMetadataNode();
        const message = {
            type: 'barabeat-shekere-beat',
            enabled: Boolean(timelineState.shekereBeatEnabled)
        };
        sendPracticeAudioMessage(message);
        sendTimelineAudioMessage(message);
    }
    function inputHasCompleteNumberValue(inputEl) {
        if (!inputEl || inputEl.value === '') {
            return false;
        }
        const numericValue = Number(inputEl.value);
        if (!Number.isFinite(numericValue)) {
            return false;
        }
        const minValue = inputEl.min === '' ? -Infinity : Number(inputEl.min);
        const maxValue = inputEl.max === '' ? Infinity : Number(inputEl.max);
        if (Number.isFinite(minValue) && numericValue < minValue) {
            return false;
        }
        if (Number.isFinite(maxValue) && numericValue > maxValue) {
            return false;
        }
        return true;
    }
    ['timelineTempo', 'practiceTempo'].forEach(function (inputId) {
        const inputEl = document.querySelector('#' + inputId);
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('input', function (event) {
            if (!inputHasCompleteNumberValue(event.target)) {
                return;
            }
            const nextValue = normalizeTimelineTempo(event.target.value);
            if (timelineState.tempo !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            timelineState.tempo = nextValue;
            notifyTimingControlsChanged();
        });
        inputEl.addEventListener('change', function (event) {
            const nextValue = normalizeTimelineTempo(event.target.value);
            if (timelineState.tempo !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            timelineState.tempo = nextValue;
            event.target.value = String(nextValue);
            notifyTimingControlsChanged();
        });
    });
    ['timelineShekereBeat', 'practiceShekereBeat'].forEach(function (inputId) {
        const inputEl = document.querySelector('#' + inputId);
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('click', function () {
            const nextValue = !Boolean(timelineState.shekereBeatEnabled);
            if (timelineState.shekereBeatEnabled !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            timelineState.shekereBeatEnabled = nextValue;
            notifyShekereBeatChanged();
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
                const currentProfile = normalizeTimelineSwingProfile(
                    timelineState.swingProfile && timelineState.swingProfile[currentProfileKey],
                    currentProfileKey
                );
                if (inputIndex >= currentProfile.length) {
                    return;
                }
                const nextValue = normalizeSwingProfileValue(event.target.value);
                if (currentProfile[inputIndex] !== nextValue) {
                    recordArrangementHistorySnapshot();
                }
                setSwingProfileAnchorValue(inputIndex, nextValue);
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
    const practiceVolumeButtonEl = document.querySelector('#practiceVolumeButton');
    if (practiceVolumeButtonEl) {
        practiceVolumeButtonEl.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof openTimelineInstrumentVolumesPopover === 'function') {
                openTimelineInstrumentVolumesPopover(practiceVolumeButtonEl);
            }
        });
    }
    document.querySelector('#practiceTempoRampButton').addEventListener('click', openPracticeTempoRampDialog);
    document.querySelector('#practiceTempoRampCloseButton').addEventListener('click', closePracticeTempoRampDialog);
    document.querySelector('#practiceTempoRampDoneButton').addEventListener('click', closePracticeTempoRampDialog);
    document.querySelector('#practiceTempoRampDialog').addEventListener('click', function (event) {
        if (event.target && event.target.id === 'practiceTempoRampDialog') {
            closePracticeTempoRampDialog();
        }
    });
    document.querySelector('#practiceTempoRampResetButton').addEventListener('click', function () {
        recordArrangementHistorySnapshot();
        practiceState.tempoRampEnabled = false;
        practiceState.tempoRampStart = normalizePracticeTempo(timelineState.tempo, 100);
        practiceState.tempoRampEnd = normalizePracticeTempo(timelineState.tempo, 100);
        practiceState.tempoRampEvery = 2;
        practiceState.tempoRampStep = 5;
        notifyPracticeTempoRampChanged();
    });
    [
        ['practiceTempoRampEnabled', 'tempoRampEnabled', function (value) { return Boolean(value); }, 'checked'],
        ['practiceTempoRampStart', 'tempoRampStart', function (value) { return normalizePracticeTempo(value, timelineState.tempo); }, 'value'],
        ['practiceTempoRampEnd', 'tempoRampEnd', function (value) { return normalizePracticeTempo(value, timelineState.tempo); }, 'value'],
        ['practiceTempoRampEvery', 'tempoRampEvery', normalizePracticeTempoRampEvery, 'value'],
        ['practiceTempoRampStep', 'tempoRampStep', normalizePracticeTempoRampStep, 'value']
    ].forEach(function (rampConfig) {
        const inputEl = document.querySelector('#' + rampConfig[0]);
        if (!inputEl) {
            return;
        }
        inputEl.addEventListener('input', function (event) {
            if (event.target.type === 'number' && !inputHasCompleteNumberValue(event.target)) {
                return;
            }
            const nextValue = rampConfig[2](event.target[rampConfig[3]]);
            if (practiceState[rampConfig[1]] !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            practiceState[rampConfig[1]] = nextValue;
            notifyPracticeTempoRampChanged();
        });
        inputEl.addEventListener('change', function (event) {
            const nextValue = rampConfig[2](event.target[rampConfig[3]]);
            if (practiceState[rampConfig[1]] !== nextValue) {
                recordArrangementHistorySnapshot();
            }
            practiceState[rampConfig[1]] = nextValue;
            notifyPracticeTempoRampChanged();
            syncPracticeTempoRampControls();
        });
    });
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
        '#accessLogoutButton',
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

window.addEventListener('barabeat-language-change', function (event) {
    const language = event.detail && event.detail.language ? event.detail.language : '';
    ['practiceAudioFrame', 'timelineAudioFrame', 'mobileArrangementAudioFrame', 'sheetQuickPlayFrame'].forEach(function (frameId) {
        const frameEl = document.getElementById(frameId);
        if (frameEl && frameEl.contentWindow) {
            frameEl.contentWindow.postMessage({
                type: 'barabeat-language-change',
                language: language
            }, window.location.origin);
        }
    });

    if (typeof renderTimelinePanel === 'function') {
        renderTimelinePanel();
    }
    if (typeof renderPracticePanel === 'function') {
        renderPracticePanel();
    }
    if (typeof updateMobilePracticeModeAvailability === 'function') {
        updateMobilePracticeModeAvailability();
    }
    if (typeof updateMobileArrangementButtonVisibility === 'function') {
        updateMobileArrangementButtonVisibility();
    }
    if (typeof renderLegend === 'function') {
        renderLegend(125);
    }
    if (typeof localizeTupletElement === 'function' && typeof s !== 'undefined' && s) {
        s.selectAll('#triplet, #quartuplet').forEach(localizeTupletElement);
    }
    if (typeof renderMobileSheetView === 'function') {
        renderMobileSheetView();
    }
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
        let readResult = callPHPScript_lesen(zeilenAnzahl, {
            showAlert: false,
            updateQuickPlaySelectors: false
        });
        if (normalizeLegacyMobileSheetNotePositions(readResult)) {
            readResult = callPHPScript_lesen(zeilenAnzahl, {
                showAlert: false,
                updateQuickPlaySelectors: false
            });
        }
        const persistedEntries = persistedTimelineMetadata && Array.isArray(persistedTimelineMetadata.entries)
            ? persistedTimelineMetadata.entries
            : [];
        timelineState.tempo = normalizeTimelineTempo(
            persistedTimelineMetadata ? persistedTimelineMetadata.tempo : 100
        );
        timelineState.shekereBeatEnabled = Boolean(
            persistedTimelineMetadata && persistedTimelineMetadata.shekereBeatEnabled
        );
        timelineState.swingProfile = normalizeAllTimelineSwingProfiles(
            persistedTimelineMetadata ? persistedTimelineMetadata.swingProfile : null
        );
        timelineState.feelOffsets = normalizeTimelineFeelOffsets(
            persistedTimelineMetadata ? persistedTimelineMetadata.feelOffsets : null
        );
        syncTimelineStateFromReadResult(readResult, {
            tempo: timelineState.tempo,
            shekereBeatEnabled: timelineState.shekereBeatEnabled,
            swingProfile: timelineState.swingProfile,
            feelOffsets: timelineState.feelOffsets,
            persistedPractice: persistedTimelineMetadata ? persistedTimelineMetadata.practice : null,
            persistedEntries: persistedEntries,
            persistedVersion: persistedTimelineMetadata ? persistedTimelineMetadata.version : null,
            persistedSourceHash: persistedTimelineMetadata ? persistedTimelineMetadata.sourceHash : ''
        });
        const sheetQuickPlayTempoEl = document.getElementById('sheetQuickPlayTempo');
        if (sheetQuickPlayTempoEl) {
            sheetQuickPlayTempoEl.value = normalizeTimelineTempo(timelineState.tempo);
        }
        renderSheetQuickPlaySelectors(readResult);
        renderMobileSheetView(readResult);
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
        importServerScore(selectedFileName).catch(function (error) {
            console.error('Serverdatei konnte nicht importiert werden', error);
            alert(uiText('file.error.serverLoad', { message: error.message || '' }));
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
        alert(uiText('file.error.localLoad', { message: error.message || '' }));
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
    <footer class="app-legal-footer" aria-label="Rechtliche Informationen">
        <span class="app-copyright">© 2020–<?php echo date('Y'); ?> Art &amp; Werbeteam GmbH · BaraBeat</span>
        <span aria-hidden="true">·</span>
        <a href="legal/offline/impressum.html" target="_blank" rel="opener" data-online-href="impressum.php">Impressum</a>
        <span aria-hidden="true">·</span>
        <a href="legal/offline/datenschutz.html" target="_blank" rel="opener" data-online-href="datenschutz.php">Datenschutz</a>
    </footer>
</body>
</html>
