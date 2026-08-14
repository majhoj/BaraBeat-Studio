<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

return [
    'pageTitle' => 'Manual de BaraBeat Studio',
    'backLabel' => '← Volver a la partitura',
    'backAriaLabel' => 'Volver a la partitura',
    'headerTitle' => 'BaraBeat Studio',
    'headerSubtitle' => 'Manual del editor de partituras, la reproducción inmediata, el modo de práctica, el reproductor de audio y el Arreglo.',
    'navigationTitle' => 'Contenido',
    'navigationAriaLabel' => 'Contenido',
    'navigationNote' => 'Este manual describe el estado de desarrollo de agosto de 2026.',
    'languageLabel' => 'Idioma',
    'sections' => [
        'quickstart' => 'Inicio rápido',
        'interface' => 'Interfaz',
        'files' => 'Archivos',
        'editor' => 'Editar la partitura',
        'symbols' => 'Símbolos y marcas especiales',
        'selection' => 'Selección, copia y deshacer',
        'player' => 'Reproductor de audio',
        'practice' => 'Modo de práctica',
        'arrangement' => 'Arreglo',
        'sound' => 'Sonido, Swing y Feel',
        'mobile' => 'Uso en smartphone',
        'workflows' => 'Flujos de trabajo habituales',
        'troubleshooting' => 'Solución de problemas',
        'glossary' => 'Glosario',
    ],
];
