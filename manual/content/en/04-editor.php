<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="editor">
        <h2>4. Editing the score</h2>
        <p>The score is the basis of all other functions. Patterns are created from passages labelled with an instrument chooser and a function chooser.</p>

        <h3>Score types</h3>
        <table>
          <thead><tr><th>Type</th><th>Grid</th><th>Typical use</th></tr></thead>
          <tbody>
            <tr><td>Binary</td><td>Four subdivisions per pulse</td><td>Many straight Djembe and Dunun rhythms</td></tr>
            <tr><td>Ternary</td><td>Three subdivisions per pulse</td><td>Triplet-based rhythms</td></tr>
            <tr><td>9/8</td><td>Three groups of three</td><td>Special 9/8 rhythms such as Koreduga</td></tr>
          </tbody>
        </table>

        <h3>Multi-page scores</h3>
        <p>A score can have several pages. Each page displays ten lines. If more lines are needed, use <strong>Add page</strong>. The legend is placed on the last page, and page numbers such as <code>1/2</code> and <code>2/2</code> appear at the lower right.</p>

        <h3>Palette</h3>
        <p>The palette contains note symbols, choosers and control marks. On the desktop it remains visible while scrolling and can be moved when required. In the mobile landscape editor it appears as a horizontally scrollable toolbar at the bottom of the display.</p>

        <h3>Setting the instrument and function</h3>
        <p>The instrument chooser sets the voice, for example Djembe, Kenkeni, Sangban, Dununba or Ballet Dununs. The function chooser describes the Pattern’s role, such as Call, Intro, Accompaniment Pattern, Solo, Échauffement or Outro. For <strong>Accompaniment Pattern</strong> and <strong>Solo</strong>, you can enter a custom label such as <em>Accompaniment Pattern 2</em> or <em>Solo 1</em>. This custom label reappears in the input field when you edit it later.</p>
        <p>In BaraBeat Studio, <strong>Ballet Dununs</strong> denotes the combined part or jointly played set of Kenkeni, Sangban and Dununba.</p>

        <h3>Rhythm name</h3>
        <p>The rhythm name appears at the top of the score and can be edited directly. On startup, the last loaded title is restored if the browser still has that information.</p>

        <h3>Instant playback from the score</h3>
        <p>A BPM field and Play button appear next to the rhythm name. Small selection boxes before the Patterns mark what should be played directly from the score. They become active as soon as a measure is recognised as playable through an instrument, Pattern name or notes; saving and reloading first is unnecessary. On iPhone, the header with rhythm name and Play button stays visible while scrolling.</p>
        <ul>
          <li>One selected Pattern loops until you press Stop.</li>
          <li>Patterns on different instruments play in parallel.</li>
          <li>Patterns on the same instrument play in sequence.</li>
          <li>A selected Accompaniment continues while an additional Solo plays in parallel. An Out in the Accompaniment is ignored to keep the loop seamless.</li>
          <li>Notes currently sounding are briefly highlighted in the score.</li>
        </ul>

        <h3>Moving a complete Pattern</h3>
        <p>A complete Pattern, including notes, choosers and additional marks, can be moved up or down. Use the arrows at the beginning of the Pattern. This is available in both the desktop editor and the mobile landscape editor.</p>
      </section>
