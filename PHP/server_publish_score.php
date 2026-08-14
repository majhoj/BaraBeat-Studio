<?php
require_once __DIR__ . '/access_control.php';
require_once __DIR__ . '/edition_config.php';
barabeat_require_access('json');
barabeat_require_write_csrf('json');

header('Content-Type: application/json; charset=UTF-8');

function respond_json($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_score_filename($rawName) {
    $rawName = trim((string) $rawName);
    if ($rawName === '' || preg_match('/[\\\\\/\x00-\x1F\x7F]/', $rawName)) {
        return '';
    }

    $baseName = basename($rawName);
    $baseName = preg_replace('/\.(bbs|txt)$/i', '', $baseName);
    $baseName = trim($baseName);

    if ($baseName === '' || $baseName === '.' || $baseName === '..') {
        return '';
    }

    return $baseName . '.bbs';
}

$title = $_POST['title'] ?? $_POST['b'] ?? '';
$content = $_POST['content'] ?? $_POST['a'] ?? '';
$fileName = normalize_score_filename($title);

if ($fileName === '') {
    respond_json(400, ['success' => false, 'message' => barabeat_t('file.error.invalidFileName')]);
}

$notesDir = realpath(__DIR__ . '/../Noten');
if ($notesDir === false || !is_dir($notesDir)) {
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.notesDirectoryMissing')]);
}

$filePath = $notesDir . DIRECTORY_SEPARATOR . $fileName;
$fileHandle = @fopen($filePath, 'x');
if ($fileHandle === false && file_exists($filePath)) {
    respond_json(409, [
        'success' => false,
        'message' => barabeat_t('error.serverScoreExists', ['fileName' => $fileName])
    ]);
}
if ($fileHandle === false) {
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.serverFileSaveFailed')]);
}

$metaDir = $notesDir . DIRECTORY_SEPARATOR . '.meta';
if (!is_dir($metaDir) && !mkdir($metaDir, 0755, true) && !is_dir($metaDir)) {
    fclose($fileHandle);
    @unlink($filePath);
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.metadataDirectoryCreateFailed')]);
}

$publishToken = bin2hex(random_bytes(32));
$timestamp = gmdate('c');
$meta = [
    'serverPath' => $fileName,
    'publishTokenHash' => hash('sha256', $publishToken),
    'createdAt' => $timestamp,
    'updatedAt' => $timestamp
];

if (!flock($fileHandle, LOCK_EX)) {
    fclose($fileHandle);
    @unlink($filePath);
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.serverFileSaveFailed')]);
}
$remainingContent = (string) $content;
while ($remainingContent !== '') {
    $writtenBytes = fwrite($fileHandle, $remainingContent);
    if ($writtenBytes === false || $writtenBytes === 0) {
        flock($fileHandle, LOCK_UN);
        fclose($fileHandle);
        @unlink($filePath);
        respond_json(500, ['success' => false, 'message' => barabeat_t('error.serverFileSaveFailed')]);
    }
    $remainingContent = (string) substr($remainingContent, $writtenBytes);
}
fflush($fileHandle);
flock($fileHandle, LOCK_UN);
fclose($fileHandle);

$metaPath = $metaDir . DIRECTORY_SEPARATOR . $fileName . '.json';
if (file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) === false) {
    @unlink($filePath);
    respond_json(500, ['success' => false, 'message' => barabeat_t('error.publicationMetadataSaveFailed')]);
}

respond_json(200, [
    'success' => true,
    'serverPath' => $fileName,
    'title' => preg_replace('/\.(bbs|txt)$/i', '', $fileName),
    'format' => 'bbs',
    'publishToken' => $publishToken,
    'updatedAt' => $timestamp
]);
?>
