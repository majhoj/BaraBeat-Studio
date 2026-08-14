<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="arrangement">
        <h2>9. Arrangement</h2>
        <p>The Arrangement is the timeline for complete sequences. Patterns from the Pattern library are dragged into sections and played there in sequence or in parallel.</p>
        <h3>Pattern library</h3>
        <p>All Patterns found in the score appear on the left. Drag a Pattern into the timeline or use the plus button to append it.</p>
        <h3>Sections</h3>
        <p>A section contains one or more Patterns. Patterns in the same row play in parallel. The repeat count on the left determines how often the section runs. Shorter Accompaniments can continue cycling when a longer Accompaniment determines the number of passes.</p>
        <h3>Adding in parallel</h3>
        <p>When you drag a Pattern into an existing section, a parallel drop zone appears only when the Pattern can be used there musically. An instrument cannot play two different main Patterns of the same type at once, except when a Solo deliberately replaces an Accompaniment.</p>
        <h3>Solo cells</h3>
        <p>Accompaniment sections with several repeats show a Solo grid. Each cell represents one pass. A Solo dropped into a cell plays in parallel with the Accompaniment only during that pass.</p>
        <ul>
          <li>If the Solo is shorter than the Accompaniment pass, the remainder is silent.</li>
          <li>If the Solo is longer, the section continues for the required duration.</li>
          <li>If the same instrument plays Accompaniment and Solo, the Accompaniment pauses during the Solo and resumes afterwards.</li>
          <li><strong>In</strong> and <strong>Out</strong> are also observed for Solos.</li>
        </ul>
        <h3>Moving sections</h3>
        <p>Sections can be moved up or down in the timeline, allowing an Arrangement to be reorganised without rebuilding its Patterns.</p>
        <h3>BPM in the Arrangement</h3>
        <p>Each section can have its own BPM. A tempo change glides to the new BPM over about two measures and remains in effect for subsequent sections until another change is set.</p>
        <h3>Shekere and volumes</h3>
        <p>Shekere beat is also available in the Arrangement. Swing profile, Feel and volumes open from the controls above the timeline. Instrument volumes are shared by practice mode and Arrangement and saved with the score.</p>
        <h3>Undo and redo</h3>
        <p>Timeline changes are recorded in the same history as editor and practice-setting changes. On the desktop, <kbd>Cmd</kbd> + <kbd>Z</kbd> undoes the last step; <kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> redoes it.</p>
      </section>
