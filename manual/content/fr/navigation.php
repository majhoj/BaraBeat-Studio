<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

return [
    'pageTitle' => 'Mode d’emploi de BaraBeat Studio',
    'backLabel' => '← Retour à la partition',
    'backAriaLabel' => 'Retour à la partition',
    'headerTitle' => 'BaraBeat Studio',
    'headerSubtitle' => 'Mode d’emploi de l’éditeur de partition, de la lecture immédiate, du mode entraînement, du lecteur audio et de l’Arrangement.',
    'navigationTitle' => 'Sommaire',
    'navigationAriaLabel' => 'Sommaire',
    'navigationNote' => 'Ce mode d’emploi décrit l’état du logiciel en août 2026.',
    'languageLabel' => 'Langue',
    'sections' => [
        'quickstart' => 'Prise en main',
        'interface' => 'Interface',
        'files' => 'Fichiers',
        'editor' => 'Modifier la partition',
        'symbols' => 'Symboles et signes spéciaux',
        'selection' => 'Sélection, copie et annulation',
        'player' => 'Lecteur audio',
        'practice' => 'Mode entraînement',
        'arrangement' => 'Arrangement',
        'sound' => 'Son, Swing et Feel',
        'mobile' => 'Utilisation sur smartphone',
        'workflows' => 'Procédures courantes',
        'troubleshooting' => 'Dépannage',
        'glossary' => 'Glossaire',
    ],
];
