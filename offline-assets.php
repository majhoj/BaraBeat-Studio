<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

$root = __DIR__;
$assets = [];
$directories = [
    'Audio/snd',
    'Audio/snd alt'
];

foreach ($directories as $relativeDirectory) {
    $absoluteDirectory = $root . '/' . $relativeDirectory;
    if (!is_dir($absoluteDirectory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absoluteDirectory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'mp3') {
            continue;
        }
        $absolutePath = str_replace('\\', '/', $fileInfo->getPathname());
        $relativePath = ltrim(substr($absolutePath, strlen(str_replace('\\', '/', $root))), '/');
        $assets[] = $relativePath;
    }
}

sort($assets, SORT_NATURAL | SORT_FLAG_CASE);
$versionParts = array_map(static function ($relativePath) use ($root) {
    $absolutePath = $root . '/' . $relativePath;
    return $relativePath . ':' . (@filemtime($absolutePath) ?: 0) . ':' . (@filesize($absolutePath) ?: 0);
}, $assets);

echo json_encode([
    'version' => hash('sha256', implode('|', $versionParts)),
    'assets' => $assets
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
