<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>    <nav aria-label="<?php echo barabeat_manual_escape($manualStrings['navigationAriaLabel']); ?>">
      <h2><?php echo barabeat_manual_escape($manualStrings['navigationTitle']); ?></h2>
<?php foreach ($manualSections as $manualSection): ?>
<?php if (!empty($manualSection['navigation'])): ?>
      <a href="#<?php echo barabeat_manual_escape($manualSection['id']); ?>"><?php echo barabeat_manual_escape($manualStrings['sections'][$manualSection['id']]); ?></a>
<?php endif; ?>
<?php endforeach; ?>
      <p class="toc-note"><?php echo barabeat_manual_escape($manualStrings['navigationNote']); ?></p>
    </nav>
