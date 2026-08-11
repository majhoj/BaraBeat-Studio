<?php

require_once __DIR__ . '/access_control.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

function barabeat_access_window_response($statusCode, array $payload)
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$config = barabeat_access_config();
if (empty($config['enabled']) || !barabeat_access_is_authenticated()) {
    barabeat_access_window_response(401, [
        'success' => false,
        'message' => barabeat_t('auth.changeRequiresLogin'),
    ]);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    barabeat_access_window_response(405, [
        'success' => false,
        'message' => barabeat_t('auth.postOnly'),
    ]);
}

$csrfToken = (string) ($_POST['csrf'] ?? '');
if ($csrfToken === '' || !hash_equals(barabeat_access_csrf_token(), $csrfToken)) {
    barabeat_access_window_response(403, [
        'success' => false,
        'message' => barabeat_t('auth.sessionExpired'),
    ]);
}

$action = (string) ($_POST['action'] ?? '');
if ($action === 'open') {
    $accessUntil = barabeat_access_open_window();
    if ($accessUntil <= time()) {
        barabeat_access_window_response(500, [
            'success' => false,
            'message' => barabeat_t('auth.windowStoreFailed'),
        ]);
    }

    barabeat_access_window_response(200, [
        'success' => true,
        'open' => true,
        'remainingSeconds' => max(0, $accessUntil - time()),
        'message' => barabeat_t('auth.disabledFiveMinutes'),
    ]);
}

if ($action === 'close') {
    if (!barabeat_access_close_window()) {
        barabeat_access_window_response(500, [
            'success' => false,
            'message' => barabeat_t('auth.windowCloseFailed'),
        ]);
    }

    barabeat_access_window_response(200, [
        'success' => true,
        'open' => false,
        'remainingSeconds' => 0,
        'message' => barabeat_t('auth.activeAgain'),
    ]);
}

barabeat_access_window_response(400, [
    'success' => false,
    'message' => barabeat_t('auth.unknownAction'),
]);
