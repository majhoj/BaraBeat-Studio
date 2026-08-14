<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="player">
        <h2>7. Audio player</h2>
        <p>The audio player plays a practice session or Arrangement. In its standalone view, it can also play selected voices from a score.</p>
        <h3>Controls</h3>
        <ul>
          <li><strong>BPM</strong> controls the tempo in beats per minute.</li>
          <li><strong>Play</strong> starts or stops playback.</li>
          <li><strong>Voice</strong> selects individual instruments or groups in the standalone player.</li>
          <li><strong>Export WAV</strong> renders the current practice session or Arrangement as a WAV file when this button is available in the current configuration. It may be hidden on smartphones because mobile browsers handle downloads differently.</li>
        </ul>
        <h3>Instruments and volumes</h3>
        <p>In practice mode and Arrangement, <strong>Volume</strong> opens the instrument mixer. Djembe voices, Kenkeni, Sangban, Dununba, Ballet Dununs and Shekere can be adjusted or muted separately. For individual instruments, the relative levels of strokes such as Bass, Tone, Slap or Mute can also be set.</p>
        <h3>Bluetooth latency</h3>
        <p>Practice mode includes <strong>Bluetooth latency ms</strong>. It offsets the display relative to the audio so that the red line and audible stroke coincide with Bluetooth headphones. On iPhone, the setting is available under <strong>Latency</strong> and is saved on the device.</p>
        <h3>Moving notes</h3>
        <p>Below the player, the notes being played move from right to left beneath a fixed red line. A stroke sounds when its symbol reaches the line. Bar lines, Pattern shading, instrument name and Pattern name aid orientation.</p>
        <p>Depending on the mode, the right side of the header shows the number of remaining repeats or the remaining practice time. With tempo progression active, it first shows the remaining progression repeats.</p>
      </section>
