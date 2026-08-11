<?php

require_once __DIR__ . '/i18n.php';

/**
 * Zentrale BaraBeat-Editionskonfiguration.
 *
 * Phase 1 stellt nur die Konfiguration bereit. Bestehende Funktionen werten die
 * Matrix noch nicht aus und bleiben deshalb im Standard "full" unverändert.
 */

function barabeat_edition_deployment_config()
{
    $config = [
        // Diese eine Vorgabe bestimmt die aktive Edition, sofern die Umgebung
        // BARABEAT_EDITION nicht explizit setzt.
        'edition' => 'full',
        // Nach Abschluss der Einführungsphase kann die Konsolenausgabe hier
        // zentral abgeschaltet werden.
        'debug' => true,
        // Die konkreten Dateien werden erst bei der Einrichtung der Demo gewählt.
        'demoStartScore' => null,
        'demoArrangement' => null,
    ];

    $environmentEdition = getenv('BARABEAT_EDITION');
    if (is_string($environmentEdition) && trim($environmentEdition) !== '') {
        $config['edition'] = strtolower(trim($environmentEdition));
    }

    $environmentDebug = getenv('BARABEAT_EDITION_DEBUG');
    if (is_string($environmentDebug) && trim($environmentDebug) !== '') {
        $normalizedDebug = strtolower(trim($environmentDebug));
        $config['debug'] = in_array($normalizedDebug, ['1', 'true', 'yes', 'on'], true);
    }

    return $config;
}

function barabeat_edition_matrix()
{
    return [
        'demo' => [
            'maxPages' => 1,
            'maxSavedWorks' => 1,
            'svgDemoMark' => true,
            'serverLibraryRead' => true,
            'serverStorage' => false,
            'serverPublish' => false,
            'serverUpdate' => false,
            'serverDelete' => false,
            'maxPracticeAccompaniments' => 1,
            'maxPracticeParts' => 1,
            'fullExercises' => false,
            'practiceScenarios' => false,
            'tempoTraining' => false,
            'maxArrangementBlocks' => 3,
            'advancedFeel' => false,
            'advancedMixer' => false,
            'wavExport' => false,
        ],
        'personal' => [
            'maxPages' => null,
            'maxSavedWorks' => null,
            'svgDemoMark' => false,
            'serverLibraryRead' => true,
            'serverStorage' => true,
            'serverPublish' => true,
            'serverUpdate' => true,
            'serverDelete' => true,
            'maxPracticeAccompaniments' => null,
            'maxPracticeParts' => null,
            'fullExercises' => true,
            'practiceScenarios' => true,
            'tempoTraining' => true,
            'maxArrangementBlocks' => null,
            'advancedFeel' => true,
            'advancedMixer' => true,
            'wavExport' => false,
        ],
        'teacher' => [
            'maxPages' => null,
            'maxSavedWorks' => null,
            'svgDemoMark' => false,
            'serverLibraryRead' => true,
            'serverStorage' => true,
            'serverPublish' => true,
            'serverUpdate' => true,
            'serverDelete' => true,
            'maxPracticeAccompaniments' => null,
            'maxPracticeParts' => null,
            'fullExercises' => true,
            'practiceScenarios' => true,
            'tempoTraining' => true,
            'maxArrangementBlocks' => null,
            'advancedFeel' => true,
            'advancedMixer' => true,
            'wavExport' => true,
        ],
        'full' => [
            'maxPages' => null,
            'maxSavedWorks' => null,
            'svgDemoMark' => false,
            'serverLibraryRead' => true,
            'serverStorage' => true,
            'serverPublish' => true,
            'serverUpdate' => true,
            'serverDelete' => true,
            'maxPracticeAccompaniments' => null,
            'maxPracticeParts' => null,
            'fullExercises' => true,
            'practiceScenarios' => true,
            'tempoTraining' => true,
            'maxArrangementBlocks' => null,
            'advancedFeel' => true,
            'advancedMixer' => true,
            'wavExport' => true,
        ],
    ];
}

function barabeat_current_edition()
{
    $deploymentConfig = barabeat_edition_deployment_config();
    $edition = strtolower(trim((string) ($deploymentConfig['edition'] ?? 'full')));
    $matrix = barabeat_edition_matrix();

    // Eine fehlende oder unbekannte Deployment-Angabe darf während der
    // Migration niemals unbemerkt Funktionen abschalten.
    return array_key_exists($edition, $matrix) ? $edition : 'full';
}

function barabeat_edition_features($edition = null)
{
    $matrix = barabeat_edition_matrix();
    $resolvedEdition = $edition === null
        ? barabeat_current_edition()
        : strtolower(trim((string) $edition));

    return $matrix[$resolvedEdition] ?? $matrix['full'];
}

function barabeat_feature($name, $default = null)
{
    $features = barabeat_edition_features();
    $featureName = (string) $name;
    return array_key_exists($featureName, $features) ? $features[$featureName] : $default;
}

function barabeat_feature_allows($name, $value = null)
{
    $features = barabeat_edition_features();
    $featureName = (string) $name;
    if (!array_key_exists($featureName, $features)) {
        return false;
    }

    $featureValue = $features[$featureName];
    if ($featureValue === null) {
        return true;
    }
    if (is_bool($featureValue)) {
        return $featureValue;
    }
    if (is_numeric($featureValue) && $value !== null) {
        return is_numeric($value) && (float) $value <= (float) $featureValue;
    }

    return (bool) $featureValue;
}

function barabeat_require_feature($name, $value = null, $message = null)
{
    if (barabeat_feature_allows($name, $value)) {
        return true;
    }

    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'feature' => (string) $name,
        'edition' => barabeat_current_edition(),
        'message' => $message ?: barabeat_t('edition.featureUnavailable'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function barabeat_public_edition_config()
{
    $deploymentConfig = barabeat_edition_deployment_config();
    return [
        'edition' => barabeat_current_edition(),
        'features' => barabeat_edition_features(),
        'content' => [
            'demoStartScore' => $deploymentConfig['demoStartScore'] ?? null,
            'demoArrangement' => $deploymentConfig['demoArrangement'] ?? null,
        ],
        'messages' => [
            'featureUnavailable' => barabeat_t('edition.featureUnavailable'),
        ],
        'debug' => !empty($deploymentConfig['debug']),
    ];
}

function barabeat_edition_config_json()
{
    $json = json_encode(
        barabeat_public_edition_config(),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT
    );

    return is_string($json) ? $json : '{}';
}

/*
 * Vorbereitung für eine spätere Phase:
 * Die Herkunft lokaler Notenblätter wird am sinnvollsten in
 * JS/localLibrary.js::saveScore() als origin = user|demo|server gespeichert.
 * Bestehende IndexedDB-Daten werden in Phase 1 ausdrücklich nicht migriert.
 * Auch die fachliche Definition eines Arrangement-Abschnitts bleibt bis zur
 * späteren Begrenzungsphase unverändert.
 */
