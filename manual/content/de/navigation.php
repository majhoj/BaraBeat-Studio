<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

return [
    'pageTitle' => 'BaraBeat Studio Bedienungsanleitung',
    'backLabel' => '← Zurück zum Notenblatt',
    'backAriaLabel' => 'Zurück zum Notenblatt',
    'headerTitle' => 'BaraBeat Studio',
    'headerSubtitle' => 'Bedienungsanleitung für Noteneditor, Sofort-Spielen, Übungsmodus, Audioplayer und Arrangement.',
    'navigationTitle' => 'Inhalt',
    'navigationAriaLabel' => 'Inhalt',
    'navigationNote' => 'Diese Anleitung beschreibt den Arbeitsstand von August 2026.',
    'sections' => [
        'quickstart' => 'Kurzstart',
        'interface' => 'Oberfläche',
        'files' => 'Dateien',
        'editor' => 'Notenblatt bearbeiten',
        'symbols' => 'Zeichen und Sonderzeichen',
        'selection' => 'Auswahl, Kopieren, Undo',
        'player' => 'Audioplayer',
        'practice' => 'Übungsmodus',
        'arrangement' => 'Arrangement',
        'sound' => 'Klang, Swing und Feel',
        'mobile' => 'Smartphone-Nutzung',
        'workflows' => 'Typische Abläufe',
        'troubleshooting' => 'Fehlersuche',
        'glossary' => 'Begriffe',
    ],
];
