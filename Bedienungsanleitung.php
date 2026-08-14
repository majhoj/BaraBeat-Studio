<?php
require_once __DIR__ . '/PHP/access_control.php';
barabeat_require_access('page');

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: private, no-cache, must-revalidate');

define('BARABEAT_MANUAL_RENDER', true);
$manualExplicitLanguage = null;
if (isset($_GET['lang']) && is_string($_GET['lang'])) {
    $manualRequestedLanguage = strtolower(trim(str_replace('_', '-', $_GET['lang'])));
    $manualRequestedPrimary = explode('-', $manualRequestedLanguage, 2)[0];
    if (in_array($manualRequestedPrimary, barabeat_supported_languages(), true)) {
        $manualExplicitLanguage = $manualRequestedPrimary;
        $_COOKIE[BARABEAT_LANGUAGE_COOKIE] = $manualExplicitLanguage;
        $manualCookieOptions = [
            'expires' => time() + 365 * 24 * 60 * 60,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => false,
            'samesite' => 'Lax',
        ];
        setcookie(BARABEAT_LANGUAGE_COOKIE, $manualExplicitLanguage, $manualCookieOptions);
    }
}
$manualLanguage = barabeat_language($manualExplicitLanguage);
$manualAssetBaseUrl = 'manual/assets';
$manualBackUrl = 'index.php';
$manualLegalBaseUrl = '';
$manualLegalOfflineBaseUrl = 'legal/offline/';

require __DIR__ . '/manual/index.php';
