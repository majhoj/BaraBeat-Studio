<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="practice">
        <h2>8. Practice mode</h2>
        <p>Practice mode is designed for repeated training of individual Patterns. It reads Patterns from the score and builds a practice loop.</p>
        <h3>Pattern selection</h3>
        <p>Pattern selection can be opened or closed. It has three main areas:</p>
        <ul>
          <li><strong>Settings</strong> for Accompaniment start, repeats, timer, Swing, Feel, Mute and latency.</li>
          <li><strong>Select Accompaniment</strong> for Patterns that run in parallel as the foundation rhythm.</li>
          <li><strong>Select practice parts</strong> for Calls, Intros, Solos, Échauffements, rests and Accompaniment Patterns to be practised.</li>
        </ul>
        <p>On the desktop, <strong>Settings</strong> initially opens collapsed; <strong>Select Accompaniment</strong> and <strong>Select practice parts</strong> are expanded. On smartphones all three initially remain collapsed so that the player and moving notes stay prominent.</p>
        <h3>Practice scenarios</h3>
        <p>Several practice scenarios can be saved for one score. A scenario contains the current settings, selected Accompaniments and practice parts, order, repeats, timer, tempo progression, volumes and other values. Scenarios are saved with the score.</p>
        <p>Select the active scenario beside <strong>Practice mode</strong> at the top or in Pattern selection. The list is alphabetical. <strong>Current settings</strong> means that no saved scenario is currently active.</p>
        <p><strong>New</strong> saves the current configuration under a new name. <strong>Save</strong> updates the selected scenario and <strong>Delete</strong> removes it. These actions also update the locally saved score.</p>
        <h3>Accompaniment starts</h3>
        <p>The Accompaniment can start immediately, after Call, after Intro or after Call and Intro. If Call or Intro are already selected as practice parts, they are not added twice. Rests and ShortBars are observed so that the Accompaniments restart together afterwards.</p>
        <h3>Accompaniment stops at Call/Intro</h3>
        <p>This option is intended for rhythms in which the Accompaniment pauses for a Call or Intro and then resumes.</p>
        <h3>Practice parts and order</h3>
        <p>You can select several practice parts and drag them into the required order. The repeat count in an individual block determines how often that Pattern is played within the practice sequence. Optionally, the Accompaniment can play alone between practice parts.</p>
        <h3>Repeats and timer</h3>
        <ul>
          <li><strong>Repeats</strong> sets how often the practice loop is played.</li>
          <li><strong>Timer</strong> can instead set a practice duration in minutes.</li>
          <li>When the timer is active, the current practice loop finishes rather than stopping in the middle of a Pattern.</li>
        </ul>
        <h3>Tempo progression</h3>
        <p>Tempo progression provides a start tempo and a target tempo. You can also specify after how many repeats the tempo rises and by how much. The progression is completed first; normal repeats or timer duration continue afterwards.</p>
        <p>The audio player displays the current BPM while the progression is running.</p>
        <h3>Sticking and H2H rest = Mute</h3>
        <p>Sticking can be selected for each Djembe Pattern, for example <strong>Auto</strong>, <strong>H2H</strong> or other modes. With <strong>H2H rest = Mute</strong>, empty notes become quiet Mute strokes. Their level can be set separately for each instrument.</p>
        <h3>Volume in practice mode</h3>
        <p>Clicking an instrument name beside the moving notes opens its volume control. A further overlay for individual stroke types can be opened there, allowing Bass, Tone, Slap or Mute to be adjusted relative to one another.</p>
        <h3>Shekere beat</h3>
        <p>Before a practice session starts, Shekere counts in four beats. <strong>Shekere beat</strong> can also accent every beat during playback. This is available in practice mode and Arrangement; Shekere has its own volume setting.</p>
      </section>
