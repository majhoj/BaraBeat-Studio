<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BARABEAT_OFFLINE_SHELL_BUILD', true);

$projectRoot = dirname(__DIR__);
$targetPath = $projectRoot . '/app-shell.html';

ob_start();
require $projectRoot . '/index.php';
$html = ob_get_clean();

$requiredFragments = [
    'window.BARABEAT_OFFLINE_BOOT = true;',
    'data-offline-boot="true"',
    'window.BaraBeatEditionConfig = window.BaraBeatAppBootstrap.getOfflineEditionConfig',
    'src="JS/app-bootstrap.js?',
];

foreach ($requiredFragments as $fragment) {
    if (strpos($html, $fragment) === false) {
        fwrite(STDERR, "App-Shell konnte nicht erzeugt werden: Pflichtmerkmal fehlt: {$fragment}\n");
        exit(1);
    }
}

if (preg_match('/"csrfToken"\s*:\s*"[^"]+"/', $html)) {
    fwrite(STDERR, "App-Shell enthält einen nicht leeren CSRF-Wert.\n");
    exit(1);
}

if (strpos($html, '<?php') !== false || strpos($html, 'BARABEAT_ACCESS_PASSWORD') !== false) {
    fwrite(STDERR, "App-Shell enthält nicht aufgelösten oder geheimen Serverinhalt.\n");
    exit(1);
}

if (file_put_contents($targetPath, $html) === false) {
    fwrite(STDERR, "App-Shell konnte nicht geschrieben werden: {$targetPath}\n");
    exit(1);
}

fwrite(STDOUT, "Statische App-Shell erzeugt: app-shell.html\n");
