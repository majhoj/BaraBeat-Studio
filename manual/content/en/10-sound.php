<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="sound">
        <h2>10. Sound, Swing and Feel</h2>
        <p>BaraBeat Studio provides several levels of sound adjustment.</p>
        <h3>Instrument volume</h3>
        <p>The overall level of individual instruments, including Shekere, can be set from the moving notes in practice mode. These values are saved with the score.</p>
        <h3>Stroke volume per instrument</h3>
        <p>The different strokes of an instrument can also be balanced relative to one another, for example Bass, Tone, Slap, Mute or muffled strokes. This is useful when samples were recorded at different levels.</p>
        <h3>Swing</h3>
        <p><strong>Swing profile</strong> opens an overlay in which subdivision displacement is displayed and adjusted. Note symbols can also be dragged horizontally; the maximum offset is limited to 50&nbsp;% of the distance to the neighbouring grid position. The profile applies to all Patterns in the score; there are no separate Swing values per Pattern.</p>
        <h3>Feel</h3>
        <p>The Feel overlay moves individual instruments forward or backward by milliseconds. Instrument names are written in full so that the adjusted part is unambiguous.</p>
        <div class="hint"><strong>In practice:</strong> Swing changes the internal distribution within a pulse. Feel shifts complete instruments slightly relative to one another. Together they can make a rhythm more lively, but should be used carefully.</div>
      </section>
