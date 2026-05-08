<?php
header('Content-Type: application/json; charset=UTF-8');

function respond_json($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_score_filename($rawName) {
    $baseName = basename(trim($rawName));
    $baseName = preg_replace('/\.txt$/i', '', $baseName);
    $baseName = trim($baseName);

    if ($baseName === '' || $baseName === '.' || $baseName === '..') {
        return '';
    }

    return $baseName . '.txt';
}

$title = $_POST['title'] ?? $_POST['b'] ?? '';
$content = $_POST['content'] ?? $_POST['a'] ?? '';
$fileName = normalize_score_filename($title);

if ($fileName === '') {
    respond_json(400, ['success' => false, 'message' => 'Ungültiger Dateiname.']);
}

$notesDir = realpath(__DIR__ . '/../Noten');
if ($notesDir === false || !is_dir($notesDir)) {
    respond_json(500, ['success' => false, 'message' => 'Noten-Verzeichnis wurde nicht gefunden.']);
}

$filePath = $notesDir . DIRECTORY_SEPARATOR . $fileName;
if (file_exists($filePath)) {
    respond_json(409, [
        'success' => false,
        'message' => 'Auf dem Server gibt es bereits ein Notenblatt mit diesem Namen: ' . $fileName . '. Bitte wähle einen anderen Namen.'
    ]);
}

$metaDir = $notesDir . DIRECTORY_SEPARATOR . '.meta';
if (!is_dir($metaDir) && !mkdir($metaDir, 0755, true)) {
    respond_json(500, ['success' => false, 'message' => 'Metadaten-Verzeichnis konnte nicht erstellt werden.']);
}

$publishToken = bin2hex(random_bytes(32));
$timestamp = gmdate('c');
$meta = [
    'serverPath' => $fileName,
    'publishTokenHash' => hash('sha256', $publishToken),
    'createdAt' => $timestamp,
    'updatedAt' => $timestamp
];

if (file_put_contents($filePath, $content, LOCK_EX) === false) {
    respond_json(500, ['success' => false, 'message' => 'Serverdatei konnte nicht gespeichert werden.']);
}

$metaPath = $metaDir . DIRECTORY_SEPARATOR . $fileName . '.json';
if (file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) === false) {
    @unlink($filePath);
    respond_json(500, ['success' => false, 'message' => 'Veröffentlichungs-Metadaten konnten nicht gespeichert werden.']);
}

respond_json(200, [
    'success' => true,
    'serverPath' => $fileName,
    'title' => preg_replace('/\.txt$/i', '', $fileName),
    'format' => 'txt',
    'publishToken' => $publishToken,
    'updatedAt' => $timestamp
]);
?>
