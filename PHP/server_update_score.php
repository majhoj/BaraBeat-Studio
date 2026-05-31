<?php
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
$content = $_POST['content'] ?? $_POST['a'] ?? '';
$publishToken = trim($_POST['publishToken'] ?? '');

if ($serverPath === '') {
    respond_json(400, ['success' => false, 'message' => 'Ungültiger Serverpfad.']);
}

if ($publishToken === '') {
    respond_json(403, ['success' => false, 'message' => 'Diese Veröffentlichung kann ohne Publish-Token nicht aktualisiert werden.']);
}

$notesDir = realpath(__DIR__ . '/../Noten');
if ($notesDir === false || !is_dir($notesDir)) {
    respond_json(500, ['success' => false, 'message' => 'Noten-Verzeichnis wurde nicht gefunden.']);
}

$filePath = $notesDir . DIRECTORY_SEPARATOR . $serverPath;
if (!is_file($filePath)) {
    respond_json(404, ['success' => false, 'message' => 'Die Serverdatei wurde nicht gefunden: ' . $serverPath]);
}

$metaPath = $notesDir . DIRECTORY_SEPARATOR . '.meta' . DIRECTORY_SEPARATOR . $serverPath . '.json';
if (!is_file($metaPath)) {
    respond_json(403, ['success' => false, 'message' => 'Diese Serverdatei hat kein Publish-Token und kann nicht aktualisiert werden.']);
}

$meta = json_decode(file_get_contents($metaPath), true);
if (!is_array($meta) || empty($meta['publishTokenHash'])) {
    respond_json(403, ['success' => false, 'message' => 'Die Veröffentlichungs-Metadaten sind ungültig.']);
}

$providedHash = hash('sha256', $publishToken);
if (!hash_equals($meta['publishTokenHash'], $providedHash)) {
    respond_json(403, ['success' => false, 'message' => 'Das Publish-Token passt nicht zu dieser Serverdatei.']);
}

$timestamp = gmdate('c');
$meta['updatedAt'] = $timestamp;

if (file_put_contents($filePath, $content, LOCK_EX) === false) {
    respond_json(500, ['success' => false, 'message' => 'Serverdatei konnte nicht aktualisiert werden.']);
}

if (file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) === false) {
    respond_json(500, ['success' => false, 'message' => 'Veröffentlichungs-Metadaten konnten nicht aktualisiert werden.']);
}

respond_json(200, [
    'success' => true,
    'serverPath' => $serverPath,
    'title' => preg_replace('/\.(bbs|txt)$/i', '', $serverPath),
    'format' => preg_match('/\.txt$/i', $serverPath) ? 'txt' : 'bbs',
    'updatedAt' => $timestamp
]);
?>
