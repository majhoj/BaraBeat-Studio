<?php

$projectRoot = dirname(__DIR__, 2);
$manualRoot = $projectRoot . '/manual';
$contentRoot = $manualRoot . '/content';
$languages = ['de', 'en', 'fr', 'es', 'pt'];
$chapterFiles = [
    '01-quickstart.php',
    '02-interface.php',
    '03-files.php',
    '04-editor.php',
    '05-symbols.php',
    '06-selection.php',
    '07-player.php',
    '08-practice.php',
    '09-arrangement.php',
    '10-sound.php',
    '11-mobile.php',
    '12-workflows.php',
    '13-troubleshooting.php',
    '14-glossary.php',
];
$chapterIds = [
    'quickstart',
    'interface',
    'files',
    'editor',
    'symbols',
    'selection',
    'player',
    'practice',
    'arrangement',
    'sound',
    'mobile',
    'workflows',
    'troubleshooting',
    'glossary',
];
$masterHashes = [
    '01-quickstart.php' => 'e1bb3d478057035c2ac4b19e6cf019ed2b5544270baa527c1327799f45079395',
    '02-interface.php' => '55313faa2b72c7df333e86bdb68ca88fbebd2a9a7756d66c5be5ff7583f7e26f',
    '03-files.php' => 'f029263c87ca30f32d14a5019c6ad98df1b1b22d71bb97351f585f39ce759a30',
    '04-editor.php' => 'b5c18fcabd9d15ee1c71103fd5dbc37d43b569a8ff5325a305fba379daacc1c4',
    '05-symbols.php' => '1079341733f9af992d28b35c6e04630867374c9b18d141c0290abc89fd7130ba',
    '06-selection.php' => '2b52b28a0cdbc023ac1da4ec7d12330d7c97c75a7424e133fe15c0f4684678cc',
    '07-player.php' => '8c1106c591b1b89aa30fcc082b1449d653a3ae4646f15b9bee8ce1f3a9ac6d3a',
    '08-practice.php' => '871cb31c86df11c52ac43840e5c694638f65baa43d8d1d82fd087f26a2f9b956',
    '09-arrangement.php' => '0a76481909adf7df0d61b7925735dd824c595798ed00b730e3be5b99e542e6f7',
    '10-sound.php' => '62c6d18bf63339707f6c4974886724dcedeb582d0bc379fca0e8f946ea24435c',
    '11-mobile.php' => '1ac47c5789301ac065c75fbbce9228504e33da435fd1783d1a4a5900b898159f',
    '12-workflows.php' => '3092259cc067e41cd93f97c0f587798282377a066cb13e275ff02c5447387660',
    '13-troubleshooting.php' => 'fdd75d49a347ddd3ed965a5293808da71d9ad4e76276ae6cf383582b54c22a32',
    '14-glossary.php' => '2203b39fc5d91a161441d2e6ddf9c659358384496d359b38015f47ed187b8f2d',
    'navigation.php' => '866e438d1185cd874e2a9703381a9c8c5713db99f029603de67c08636fd70da6',
];
$errors = [];

function manual_test_fail(&$errors, $message)
{
    $errors[] = $message;
}

function manual_test_count_tag($source, $tag)
{
    preg_match_all('/<' . preg_quote($tag, '/') . '(?:\s|>)/i', $source, $matches);
    return count($matches[0]);
}

function manual_test_values($source, $pattern)
{
    preg_match_all($pattern, $source, $matches);
    return isset($matches[1]) ? $matches[1] : [];
}

foreach ($masterHashes as $file => $expectedHash) {
    $path = $contentRoot . '/de/' . $file;
    if (!is_file($path) || hash_file('sha256', $path) !== $expectedHash) {
        manual_test_fail($errors, 'German master changed: ' . $file);
    }
}

$masterStructures = [];
foreach ($chapterFiles as $index => $file) {
    $source = file_get_contents($contentRoot . '/de/' . $file);
    $masterStructures[$file] = [
        'h2' => manual_test_count_tag($source, 'h2'),
        'h3' => manual_test_count_tag($source, 'h3'),
        'p' => manual_test_count_tag($source, 'p'),
        'ul' => manual_test_count_tag($source, 'ul'),
        'ol' => manual_test_count_tag($source, 'ol'),
        'li' => manual_test_count_tag($source, 'li'),
        'table' => manual_test_count_tag($source, 'table'),
        'thead' => manual_test_count_tag($source, 'thead'),
        'tbody' => manual_test_count_tag($source, 'tbody'),
        'tr' => manual_test_count_tag($source, 'tr'),
        'th' => manual_test_count_tag($source, 'th'),
        'td' => manual_test_count_tag($source, 'td'),
        'code' => manual_test_count_tag($source, 'code'),
        'kbd' => manual_test_count_tag($source, 'kbd'),
        'links' => manual_test_values($source, '/href=["\']#([^"\']+)["\']/i'),
    ];
}

define('BARABEAT_MANUAL_RENDER', true);
foreach ($languages as $language) {
    $languageRoot = $contentRoot . '/' . $language;
    $navigationPath = $languageRoot . '/navigation.php';
    if (!is_file($navigationPath)) {
        manual_test_fail($errors, $language . ': navigation.php missing');
        continue;
    }

    $navigation = require $navigationPath;
    if (!isset($navigation['sections']) || array_keys($navigation['sections']) !== $chapterIds) {
        manual_test_fail($errors, $language . ': navigation section order differs');
    }

    $seenIds = [];
    foreach ($chapterFiles as $index => $file) {
        $path = $languageRoot . '/' . $file;
        if (!is_file($path)) {
            manual_test_fail($errors, $language . ': missing ' . $file);
            continue;
        }

        $source = file_get_contents($path);
        if (strpos($source, 'BARABEAT_MANUAL_RENDER') === false) {
            manual_test_fail($errors, $language . ': fragment guard missing in ' . $file);
        }

        $sectionIds = manual_test_values($source, '/<section\s+id=["\']([^"\']+)["\']/i');
        if ($sectionIds !== [$chapterIds[$index]]) {
            manual_test_fail($errors, $language . ': wrong section ID in ' . $file);
        }

        foreach (manual_test_values($source, '/\sid=["\']([^"\']+)["\']/i') as $id) {
            if (isset($seenIds[$id])) {
                manual_test_fail($errors, $language . ': duplicate ID ' . $id);
            }
            $seenIds[$id] = true;
        }

        $structure = [];
        foreach (array_keys($masterStructures[$file]) as $key) {
            $structure[$key] = $key === 'links'
                ? manual_test_values($source, '/href=["\']#([^"\']+)["\']/i')
                : manual_test_count_tag($source, $key);
        }
        if ($structure !== $masterStructures[$file]) {
            manual_test_fail($errors, $language . ': structure differs in ' . $file);
        }
    }

    $quickstart = file_get_contents($languageRoot . '/01-quickstart.php');
    foreach (['controls', 'playsinline', 'preload="metadata"', '/poster.png', '/barabeat-quickstart.mp4', 'type="video/mp4"'] as $needle) {
        if (strpos($quickstart, $needle) === false) {
            manual_test_fail($errors, $language . ': video setting missing: ' . $needle);
        }
    }
    if (stripos($quickstart, 'autoplay') !== false) {
        manual_test_fail($errors, $language . ': video must not autoplay');
    }
}

$translatedSource = '';
foreach (['en', 'fr', 'es', 'pt'] as $language) {
    foreach (glob($contentRoot . '/' . $language . '/*.php') as $path) {
        $translatedSource .= "\n" . file_get_contents($path);
    }
}
foreach (['Doundoun', 'Dreierbass', 'Three bass drums', 'Trois tambours graves', 'Tres tambores graves', 'Três tambores graves', 'Warm-up', 'Calentamiento', 'Aquecimento'] as $forbiddenTerm) {
    if (stripos($translatedSource, $forbiddenTerm) !== false) {
        manual_test_fail($errors, 'Forbidden terminology found: ' . $forbiddenTerm);
    }
}

$requiredTerms = [
    'en' => ['Accompaniment Pattern', 'Accompaniment', 'Sticking', 'Triplet', 'Quadruplet'],
    'fr' => ['Pattern d’accompagnement', 'Accompagnement', 'Alternance des mains', 'Triolet', 'Quartolet'],
    'es' => ['Pattern de acompañamiento', 'Acompañamiento', 'Patrón de manos', 'Tresillo', 'Cuatrillo'],
    'pt' => ['Pattern de acompanhamento', 'Acompanhamento', 'Padrão de mãos', 'Tercina', 'Quartina'],
];
foreach ($requiredTerms as $language => $terms) {
    $languageSource = '';
    foreach (glob($contentRoot . '/' . $language . '/*.php') as $path) {
        $languageSource .= "\n" . file_get_contents($path);
    }
    foreach ($terms as $requiredTerm) {
        if (strpos($languageSource, $requiredTerm) === false) {
            manual_test_fail($errors, $language . ': required terminology missing: ' . $requiredTerm);
        }
    }
}

require_once $projectRoot . '/PHP/i18n.php';
$browserCases = [
    'de-DE' => 'de',
    'en-US' => 'en',
    'fr-FR' => 'fr',
    'fr-CA' => 'fr',
    'es-ES' => 'es',
    'es-MX' => 'es',
    'pt-BR' => 'pt',
    'pt-PT' => 'pt',
    'it-IT' => 'en',
];
foreach ($browserCases as $acceptLanguage => $expectedLanguage) {
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $acceptLanguage;
    if (barabeat_browser_language() !== $expectedLanguage) {
        manual_test_fail($errors, 'Browser language failed: ' . $acceptLanguage);
    }
}
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
$_COOKIE[BARABEAT_LANGUAGE_COOKIE] = 'fr';
if (barabeat_requested_language() !== 'fr') {
    manual_test_fail($errors, 'Language cookie is not preferred over the browser language');
}
if (barabeat_requested_language('es') !== 'es') {
    manual_test_fail($errors, 'Explicit language is not preferred over the cookie');
}

if ($errors) {
    fwrite(STDERR, "Manual translation validation failed:\n- " . implode("\n- ", $errors) . "\n");
    exit(1);
}

echo "Manual translation validation passed for de, en, fr, es and pt.\n";
