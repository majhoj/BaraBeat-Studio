<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

return [
    ['id' => 'quickstart', 'legacyId' => 'kurzstart', 'file' => '01-quickstart.php', 'navigation' => true],
    ['id' => 'interface', 'legacyId' => 'oberflaeche', 'file' => '02-interface.php', 'navigation' => true],
    ['id' => 'files', 'legacyId' => 'dateien', 'file' => '03-files.php', 'navigation' => true],
    ['id' => 'editor', 'legacyId' => 'notenblatt', 'file' => '04-editor.php', 'navigation' => true],
    ['id' => 'symbols', 'legacyId' => 'zeichen', 'file' => '05-symbols.php', 'navigation' => true],
    ['id' => 'selection', 'legacyId' => 'auswahl', 'file' => '06-selection.php', 'navigation' => true],
    ['id' => 'player', 'legacyId' => 'audioplayer', 'file' => '07-player.php', 'navigation' => true],
    ['id' => 'practice', 'legacyId' => 'uebungsmodus', 'file' => '08-practice.php', 'navigation' => true],
    ['id' => 'arrangement', 'legacyId' => 'arrangement', 'file' => '09-arrangement.php', 'navigation' => true],
    ['id' => 'sound', 'legacyId' => 'klang', 'file' => '10-sound.php', 'navigation' => true],
    ['id' => 'mobile', 'legacyId' => 'mobil', 'file' => '11-mobile.php', 'navigation' => true],
    ['id' => 'workflows', 'legacyId' => 'tipps', 'file' => '12-workflows.php', 'navigation' => true],
    ['id' => 'troubleshooting', 'legacyId' => 'probleme', 'file' => '13-troubleshooting.php', 'navigation' => true],
    ['id' => 'glossary', 'legacyId' => 'begriffe', 'file' => '14-glossary.php', 'navigation' => true],
];
