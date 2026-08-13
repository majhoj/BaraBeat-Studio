<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

function barabeat_manual_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?><!doctype html>
<html lang="<?php echo barabeat_manual_escape($manualLanguage); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo barabeat_manual_escape($manualStrings['pageTitle']); ?></title>
  <link rel="stylesheet" href="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/manual.css'); ?>">
</head>
<body>
  <a id="manualBackLink" class="manual-back-link" href="<?php echo barabeat_manual_escape($manualBackUrl); ?>" aria-label="<?php echo barabeat_manual_escape($manualStrings['backAriaLabel']); ?>"><?php echo barabeat_manual_escape($manualStrings['backLabel']); ?></a>
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
<?php if ($manualSection['legacyId'] !== $manualSection['id']): ?>
      <span id="<?php echo barabeat_manual_escape($manualSection['legacyId']); ?>" aria-hidden="true" style="display:block;height:0;overflow:hidden"></span>
<?php endif; ?>
<?php require $manualContentRoot . '/' . $manualSection['file']; ?>
<?php endforeach; ?>
    </article>
  </main>
<?php require __DIR__ . '/language-switcher.php'; ?>
  <script src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/manual.js'); ?>"></script>
</body>
</html>
