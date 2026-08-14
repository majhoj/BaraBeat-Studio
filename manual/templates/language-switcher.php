<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

$manualLanguageNames = [
    'de' => 'Deutsch',
    'en' => 'English',
    'fr' => 'Français',
    'es' => 'Español',
    'pt' => 'Português',
];
$manualLanguageLabel = isset($manualStrings['languageLabel'])
    ? (string) $manualStrings['languageLabel']
    : 'Sprache';
$manualQuery = $_GET;
?>  <div class="manual-language-switcher">
    <label for="manualLanguageSelect"><?php echo barabeat_manual_escape($manualLanguageLabel); ?></label>
    <select id="manualLanguageSelect">
<?php foreach ($manualAvailableLanguages as $language): ?>
<?php
if (!empty($manualStaticBuild)) {
    $manualLanguageUrl = $language . '.html';
} else {
    $manualQuery['lang'] = $language;
    $manualLanguageUrl = '?' . http_build_query($manualQuery);
}
?>      <option value="<?php echo barabeat_manual_escape($manualLanguageUrl); ?>" data-language="<?php echo barabeat_manual_escape($language); ?>"<?php echo $language === $manualLanguage ? ' selected' : ''; ?>><?php echo barabeat_manual_escape($manualLanguageNames[$language]); ?></option>
<?php endforeach; ?>
    </select>
  </div>
