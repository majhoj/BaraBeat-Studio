<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

if (!function_exists('barabeat_manual_escape')) {
    function barabeat_manual_escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$manualLegalBaseUrl = isset($manualLegalBaseUrl) ? (string) $manualLegalBaseUrl : '';
$manualLegalOfflineBaseUrl = isset($manualLegalOfflineBaseUrl) ? (string) $manualLegalOfflineBaseUrl : 'legal/offline/';

?><!doctype html>
<html lang="<?php echo barabeat_manual_escape($manualLanguage); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo barabeat_manual_escape($manualStrings['pageTitle']); ?></title>
  <link rel="stylesheet" href="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/manual.css'); ?>">
</head>
<body<?php echo !empty($manualStaticBuild) ? ' data-manual-static="true"' : ''; ?>>
  <a id="manualBackLink" class="manual-back-link" href="<?php echo barabeat_manual_escape($manualBackUrl); ?>" data-barabeat-return aria-label="<?php echo barabeat_manual_escape($manualStrings['backAriaLabel']); ?>"><?php echo barabeat_manual_escape($manualStrings['backLabel']); ?></a>
  <header>
    <div class="wrap">
      <h1><?php echo barabeat_manual_escape($manualStrings['headerTitle']); ?></h1>
      <p><?php echo barabeat_manual_escape($manualStrings['headerSubtitle']); ?></p>
    </div>
  </header>

  <main>
<?php require __DIR__ . '/navigation.php'; ?>

    <article>
<?php foreach ($manualSections as $manualSection): ?>
<?php if ($manualLanguage === 'de' && $manualSection['legacyId'] !== $manualSection['id']): ?>
      <span id="<?php echo barabeat_manual_escape($manualSection['legacyId']); ?>" aria-hidden="true" style="display:block;height:0;overflow:hidden"></span>
<?php endif; ?>
<?php require $manualContentRoot . '/' . $manualSection['file']; ?>
<?php endforeach; ?>
    </article>
  </main>
<?php require __DIR__ . '/language-switcher.php'; ?>
  <footer class="manual-legal-footer" aria-label="Rechtliche Informationen">
    <span class="manual-copyright">© 2020–<?php echo date('Y'); ?> Art &amp; Werbeteam GmbH · BaraBeat</span>
    <span aria-hidden="true">·</span>
    <a href="<?php echo barabeat_manual_escape($manualLegalOfflineBaseUrl . 'impressum.html'); ?>" target="_self" data-online-href="<?php echo barabeat_manual_escape($manualLegalBaseUrl . 'impressum.php'); ?>">Impressum</a>
    <span aria-hidden="true">·</span>
    <a href="<?php echo barabeat_manual_escape($manualLegalOfflineBaseUrl . 'datenschutz.html'); ?>" target="_self" data-online-href="<?php echo barabeat_manual_escape($manualLegalBaseUrl . 'datenschutz.php'); ?>">Datenschutz</a>
  </footer>
  <script src="<?php echo barabeat_manual_escape($manualLegalBaseUrl . 'legal/navigation.js'); ?>"></script>
  <script src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/manual.js'); ?>"></script>
</body>
</html>
