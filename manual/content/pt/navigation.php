<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

return [
    'pageTitle' => 'Manual do BaraBeat Studio',
    'backLabel' => '← Voltar à partitura',
    'backAriaLabel' => 'Voltar à partitura',
    'headerTitle' => 'BaraBeat Studio',
    'headerSubtitle' => 'Manual do editor de partituras, reprodução imediata, modo de prática, player de áudio e Arranjo.',
    'navigationTitle' => 'Conteúdo',
    'navigationAriaLabel' => 'Conteúdo',
    'navigationNote' => 'Este manual descreve o estado de desenvolvimento de agosto de 2026.',
    'languageLabel' => 'Idioma',
    'sections' => [
        'quickstart' => 'Início rápido',
        'interface' => 'Interface',
        'files' => 'Arquivos',
        'editor' => 'Editar a partitura',
        'symbols' => 'Símbolos e sinais especiais',
        'selection' => 'Seleção, cópia e desfazer',
        'player' => 'Player de áudio',
        'practice' => 'Modo de prática',
        'arrangement' => 'Arranjo',
        'sound' => 'Som, Swing e Feel',
        'mobile' => 'Uso em smartphone',
        'workflows' => 'Fluxos de trabalho típicos',
        'troubleshooting' => 'Solução de problemas',
        'glossary' => 'Glossário',
    ],
];
