<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('BARABEAT_MANUAL_RENDER', true);

$manualRoot = __DIR__;
$outputRoot = $manualRoot . '/offline';
$languages = ['de', 'en', 'fr', 'es', 'pt'];

function validateManualLegalLinks($html, $language)
{
    $expectedLinks = [
        'href="../../legal/offline/impressum.html" target="_self" data-online-href="../../impressum.php"',
        'href="../../legal/offline/datenschutz.html" target="_self" data-online-href="../../datenschutz.php"',
    ];

    foreach ($expectedLinks as $expectedLink) {
        if (strpos($html, $expectedLink) === false) {
            fwrite(STDERR, "Offline-Handbuch {$language}: statisch-first Legal-Link fehlt.\n");
            exit(1);
        }
    }

    if (preg_match('/<a\b[^>]*\shref="\.\.\/\.\.\/(?:impressum|datenschutz)\.php"/i', $html)) {
        fwrite(STDERR, "Offline-Handbuch {$language}: direkter PHP-Legal-Link gefunden.\n");
        exit(1);
    }
}

if (!is_dir($outputRoot) && !mkdir($outputRoot, 0775, true) && !is_dir($outputRoot)) {
    fwrite(STDERR, "Offline-Handbuchverzeichnis konnte nicht erstellt werden.\n");
    exit(1);
}

foreach ($languages as $manualBuildLanguage) {
    $manualLanguage = $manualBuildLanguage;
    $manualAssetBaseUrl = '../assets';
    $manualBackUrl = '../../index.php';
    $manualLegalBaseUrl = '../../';
    $manualLegalOfflineBaseUrl = '../../legal/offline/';
    $manualStaticBuild = true;
    $_GET = [];

    ob_start();
    require $manualRoot . '/index.php';
    $html = ob_get_clean();
    validateManualLegalLinks($html, $manualBuildLanguage);
    $targetPath = $outputRoot . '/' . $manualBuildLanguage . '.html';

    if (file_put_contents($targetPath, $html) === false) {
        fwrite(STDERR, "Offline-Handbuch konnte nicht geschrieben werden: {$targetPath}\n");
        exit(1);
    }
}

fwrite(STDOUT, "Offline-Handbücher erzeugt: " . implode(', ', $languages) . "\n");
