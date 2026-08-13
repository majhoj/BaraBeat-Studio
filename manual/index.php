<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

$manualLanguage = isset($manualLanguage) ? (string) $manualLanguage : 'de';
$manualAvailableLanguages = ['de'];
if (!in_array($manualLanguage, $manualAvailableLanguages, true)) {
    $manualLanguage = 'de';
}

$manualRoot = __DIR__;
$manualContentRoot = $manualRoot . '/content/' . $manualLanguage;
$manualSections = require $manualRoot . '/sections.php';
$manualStrings = require $manualContentRoot . '/navigation.php';
$manualAssetBaseUrl = isset($manualAssetBaseUrl) ? rtrim((string) $manualAssetBaseUrl, '/') : 'manual/assets';
$manualBackUrl = isset($manualBackUrl) ? (string) $manualBackUrl : 'index.php';

require $manualRoot . '/templates/layout.php';
