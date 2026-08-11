<?php
require_once __DIR__ . '/access_control.php';
require_once __DIR__ . '/edition_config.php';
barabeat_require_access('json');

header('Content-Type: application/json; charset=UTF-8');

function respond_json($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_server_path($rawPath) {
    $fileName = basename(trim($rawPath));
    if ($fileName === '' || $fileName === '.' || $fileName === '..') {
        return '';
    }
    if (!preg_match('/\.(bbs|txt)$/i', $fileName)) {
        $fileName .= '.bbs';
    }
    return $fileName;
}

$serverPath = normalize_server_path($_POST['serverPath'] ?? $_POST['b'] ?? '');
$publishToken = trim($_POST['publishToken'] ?? '');

if ($serverPath === '') {
    respond_json(400, ['success' => false, 'message' => barabeat_t('error.invalidServerPath')]);
}

if ($publishToken === '') {
    respond_json(403, ['success' => false, 'message' => barabeat_t('error.publicationTokenDeleteMissing')]);
}

$notesDir = realpath(__DIR__ . '/../Noten');
if ($notesDir === false || !is_dir($notesDir)) {
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.notesDirectoryMissing')]);
}

$filePath = $notesDir . DIRECTORY_SEPARATOR . $serverPath;
if (!is_file($filePath)) {
    respond_json(404, ['success' => false, 'message' => barabeat_t('error.serverFileNotFound', ['path' => $serverPath])]);
}

$metaPath = $notesDir . DIRECTORY_SEPARATOR . '.meta' . DIRECTORY_SEPARATOR . $serverPath . '.json';
if (!is_file($metaPath)) {
    respond_json(403, ['success' => false, 'message' => barabeat_t('error.serverPublishTokenDeleteMissing')]);
}

$meta = json_decode(file_get_contents($metaPath), true);
if (!is_array($meta) || empty($meta['publishTokenHash'])) {
    respond_json(403, ['success' => false, 'message' => barabeat_t('error.publicationMetadataInvalid')]);
}

$providedHash = hash('sha256', $publishToken);
if (!hash_equals($meta['publishTokenHash'], $providedHash)) {
    respond_json(403, ['success' => false, 'message' => barabeat_t('error.publishTokenMismatch')]);
}

if (!unlink($filePath)) {
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.serverFileDeleteFailed')]);
}

if (is_file($metaPath) && !unlink($metaPath)) {
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.serverFileDeletedMetadataFailed')]);
}

respond_json(200, [
    'success' => true,
    'serverPath' => $serverPath
]);
?>
