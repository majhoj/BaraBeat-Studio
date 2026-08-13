<?php
require_once __DIR__ . '/PHP/access_control.php';
barabeat_require_access('page');

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: private, no-cache, must-revalidate');

define('BARABEAT_MANUAL_RENDER', true);
$manualLanguage = 'de';
$manualAssetBaseUrl = 'manual/assets';
$manualBackUrl = 'index.php';

require __DIR__ . '/manual/index.php';
