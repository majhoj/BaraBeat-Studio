<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="quickstart">
        <h2>1. Quick start</h2>
        <p>BaraBeat Studio is an editor and player for Djembe and Dunun notation. You can notate Patterns such as Call, Intro, Accompaniment Pattern, Solo or Échauffement and listen to them immediately.</p>

        <h3>BaraBeat in under 3 minutes</h3>
        <p>The video shows the essential steps from the first entry in the score to playing and saving a rhythm. You can then follow the individual quick-start steps at your own pace.</p>
        <video
          class="manual-quickstart-video"
          controls
          playsinline
          preload="metadata"
          poster="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/poster.png'); ?>">
          <source
            src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/barabeat-quickstart.mp4'); ?>"
            type="video/mp4">
          Your browser does not support video playback.
        </video>

        <div class="workflow">
          <h3>Fast workflow</h3>
          <ol>
            <li>Use <strong>File</strong> to open an existing score, or use <strong>Score</strong> to create a new one.</li>
            <li>Enter the <strong>rhythm name</strong> at the top of the score.</li>
            <li>Label the part with the instrument and function choosers, for example <em>Djembe 1</em> and <em>Accompaniment Pattern</em>.</li>
            <li>Place notes and, if required, control marks from the palette on the grid.</li>
            <li>Activate the selection box in front of the Pattern you want to hear.</li>
            <li>Set the BPM and start <strong>instant playback</strong> with the Play button.</li>
            <li>Use <strong>File → Save</strong> to store your work; use <strong>Save as</strong> for a new name or a copy.</li>
          </ol>
        </div>

        <p>For systematic training, switch to <strong>practice mode</strong>; build longer sequences in the <strong>Arrangement</strong>. On smartphones, portrait orientation is mainly for reading and playback, while landscape orientation is for editing.</p>
      </section>
