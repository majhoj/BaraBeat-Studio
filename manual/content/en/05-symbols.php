<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="symbols">
        <h2>5. Symbols and special marks</h2>
        <p>The principal symbols correspond to the legend in the score.</p>
        <table>
          <thead><tr><th>Symbol</th><th>Meaning</th></tr></thead>
          <tbody>
            <tr><td>Tone</td><td>Open Djembe Tone.</td></tr>
            <tr><td>Bass</td><td>Djembe Bass stroke or Dununba stroke, depending on the instrument part.</td></tr>
            <tr><td>Slap / bell</td><td>Slap on Djembe; bell on Kenkeni, Sangban, Dununba or Ballet Dununs.</td></tr>
            <tr><td>Flam</td><td>Two closely spaced strokes. Flams overlap in the moving-note display.</td></tr>
            <tr><td>Muffled Tone / muffled Slap</td><td>Muffled strokes, marked by an underline in the score.</td></tr>
            <tr><td>In</td><td>Pickup or entry point. The note below the In may occur before the Pattern proper.</td></tr>
            <tr><td>Out</td><td>Exit point. The note above the Out is still played, after which the voice ends.</td></tr>
            <tr><td>Repeat</td><td>Internal repeat within the Pattern. The number by the mark gives the repeat count.</td></tr>
            <tr><td>ShortBar</td><td>Shortens a measure without making it visually narrower. Omitted units are shown with dashed lines.</td></tr>
            <tr><td>Triplet / quadruplet</td><td>Additional symbols for even subdivisions. Binary scores show a <strong>T</strong> for triplets; ternary and 9/8 scores show a <strong>Q</strong> for quadruplets.</td></tr>
          </tbody>
        </table>
        <div class="warning"><strong>ShortBar:</strong> In binary rhythms, ShortBar omits the final four units; in ternary and 9/8 rhythms, it omits the final three. The visual structure remains stable while audio and moving notes observe the shortened length.</div>
        <div class="hint"><strong>Out in an Accompaniment Pattern:</strong> In an Arrangement, an Out can mark a deliberate exit in an Accompaniment. In practice mode, an Out in an Accompaniment Pattern is normally ignored so that loops continue. It may apply when the Accompaniment is explicitly set to stop before Call/Intro, or when an Accompaniment Pattern used as a practice part should exit on its final pass before the next Call/Intro.</div>
        <p>The triplet/quadruplet symbol changes with the score type in the palette and legend. Its compact form uses three dots and a letter: <strong>T</strong> for triplet or <strong>Q</strong> for quadruplet. After clicking the symbol, select the stroke for each individual note. The completed compound symbol is created beside the palette and can then be dragged onto the score.</p>
      </section>
