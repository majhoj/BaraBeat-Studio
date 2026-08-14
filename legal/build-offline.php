<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = dirname(__DIR__);
$outputRoot = __DIR__ . '/offline';
$documents = [
    'impressum.php' => 'impressum.html',
    'datenschutz.php' => 'datenschutz.html',
];

if (!is_dir($outputRoot) && !mkdir($outputRoot, 0775, true) && !is_dir($outputRoot)) {
    fwrite(STDERR, "Offline-Verzeichnis für Rechtstexte konnte nicht erstellt werden.\n");
    exit(1);
}

$replacements = [
    'href="Assets/favicon.svg"' => 'href="../../Assets/favicon.svg"',
    'href="legal/legal.css"' => 'href="../legal.css"',
    'src="Assets/favicon.svg"' => 'src="../../Assets/favicon.svg"',
    'href="index.php" data-barabeat-return' => 'href="../../index.php" data-barabeat-return',
    'href="manual/offline/de.html" data-online-href="Bedienungsanleitung.php"' => 'href="../../manual/offline/de.html" data-online-href="../../Bedienungsanleitung.php"',
    'href="legal/offline/impressum.html" data-online-href="impressum.php"' => 'href="impressum.html" data-online-href="../../impressum.php"',
    'href="legal/offline/datenschutz.html" data-online-href="datenschutz.php"' => 'href="datenschutz.html" data-online-href="../../datenschutz.php"',
    'src="legal/navigation.js"' => 'src="../navigation.js"',
];

foreach ($documents as $sourceName => $targetName) {
    ob_start();
    require $projectRoot . '/' . $sourceName;
    $html = ob_get_clean();
    $html = str_replace(array_keys($replacements), array_values($replacements), $html);

    if (file_put_contents($outputRoot . '/' . $targetName, $html) === false) {
        fwrite(STDERR, "Offline-Rechtstext konnte nicht geschrieben werden: {$targetName}\n");
        exit(1);
    }
}

fwrite(STDOUT, "Offline-Rechtstexte erzeugt: " . implode(', ', array_values($documents)) . "\n");
