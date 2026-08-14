<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

return [
    'pageTitle' => 'BaraBeat Studio User Guide',
    'backLabel' => '← Back to the score',
    'backAriaLabel' => 'Back to the score',
    'headerTitle' => 'BaraBeat Studio',
    'headerSubtitle' => 'User guide for the score editor, instant playback, practice mode, audio player and Arrangement.',
    'navigationTitle' => 'Contents',
    'navigationAriaLabel' => 'Contents',
    'navigationNote' => 'This guide describes the development status of August 2026.',
    'languageLabel' => 'Language',
    'sections' => [
        'quickstart' => 'Quick start',
        'interface' => 'Interface',
        'files' => 'Files',
        'editor' => 'Editing the score',
        'symbols' => 'Symbols and special marks',
        'selection' => 'Selection, copying and undo',
        'player' => 'Audio player',
        'practice' => 'Practice mode',
        'arrangement' => 'Arrangement',
        'sound' => 'Sound, Swing and Feel',
        'mobile' => 'Smartphone use',
        'workflows' => 'Typical workflows',
        'troubleshooting' => 'Troubleshooting',
        'glossary' => 'Glossary',
    ],
];
