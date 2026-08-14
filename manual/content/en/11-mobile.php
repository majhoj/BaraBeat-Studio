<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="mobile">
        <h2>11. Using BaraBeat on smartphones</h2>
        <p>On smartphones, BaraBeat Studio can open, display, play instantly, practise and edit scores. Display and editing functions switch automatically with device orientation. An existing Arrangement timeline can be played on mobile but cannot yet be edited there.</p>
        <h3>Mobile operation</h3>
        <p>The menu bar shows only commands relevant to the current mode. Under <strong>File</strong> you will find Open, Save, Save as, the user guide and Sign out. The persistent <strong>Back to the score</strong> button returns from the mobile guide to the application. In practice mode, <strong>Pattern selection</strong> and <strong>Latency</strong> are also available. If the score contains an Arrangement, <strong>Play Arrangement</strong> appears outside practice mode and opens the player in an overlay.</p>
        <h3>Portrait and landscape</h3>
        <ul>
          <li><strong>Portrait:</strong> compact reading view with one measure per row, instant playback, practice mode and playback of existing Arrangements.</li>
          <li><strong>Landscape:</strong> mobile score editor with a larger measure grid, Score menu and horizontal palette.</li>
        </ul>
        <h3>Mobile score view</h3>
        <p>Multi-page scores are displayed page by page, rather than shrinking the entire stack to one screen height. Each measure has its own row. Multi-measure Patterns are grouped compactly in portrait view; instrument and Pattern name appear only at the start.</p>
        <p>In the moving notes, instrument and Pattern name share one compact line. The red play line is drawn only across the actual note rows so that longer names remain readable.</p>
        <h3>Editing notes in landscape</h3>
        <ol>
          <li>Turn the iPhone to landscape. The landscape editor appears automatically.</li>
          <li>Select a note or control mark in the lower palette, then tap the required grid position.</li>
          <li>Existing notes and marks can be touched directly, moved and snapped to the grid on release.</li>
          <li>Draw a rectangle with the selection tool. The selection can then be moved, duplicated or deleted together.</li>
          <li>Use <strong>Insert instrument and function</strong> to add both choosers to an empty measure. Existing choosers can be changed, moved or deleted.</li>
          <li>The arrows in the chooser row move a complete Pattern with all associated elements up or down.</li>
          <li>Edit the rhythm name directly at the top. The text tool inserts comments in a measure; existing text can be changed in the displayed input field.</li>
          <li>Use <strong>File → Save</strong> or <strong>Save as</strong> to store the changes.</li>
        </ol>
        <div class="hint"><strong>Touch operation:</strong> The palette remains at the bottom and scrolls horizontally. Double-tapping a repeat mark opens its settings as on the desktop. The iOS keyboard may appear for inputs; closing it keeps the score scale unchanged.</div>
        <h3>Instant playback on iPhone</h3>
        <p>Pattern selection boxes, BPM and Play are also available on mobile. Several instruments can play in parallel; Patterns on the same instrument are handled in sequence. Sounding notes are briefly highlighted. The header remains visible while scrolling so playback can always be stopped.</p>
        <h3>Offline use</h3>
        <ol>
          <li>Open BaraBeat Studio fully once on iPhone over an <strong>HTTPS connection</strong>.</li>
          <li>Wait for <strong>Offline ready</strong>. The interface, audio player and sounds are then prepared on the device.</li>
          <li>Load required scores from the server once in advance. They will then be in the local library.</li>
          <li>Optionally add it as a web app in Safari with <strong>Share &gt; Add to Home Screen</strong>.</li>
        </ol>
        <p>Without a connection, local scores can be opened, displayed, edited and played, and saved locally. The server list, loading again from the server and saving to the server still require an internet connection.</p>
        <h3>Background and Lock Screen</h3>
        <p>On supported iPhones and browsers, BaraBeat Studio uses Media Session and a playback audio session. Audio can therefore continue with the screen locked. The Lock Screen shows the title, BaraBeat logo and playback controls; Play, Pause and Stop can be used there. The controls available depend on iOS, browser and output device.</p>
        <div class="warning"><strong>Back up local data before deleting:</strong> Safari and an added Home Screen app may use separate local storage areas. Deleting the Home Screen item can remove its local scores, practice scenarios, settings and offline data. Adding it again does not restore them automatically. Save important scores to the server first; embedded practice scenarios are stored with the file. Server files are unaffected.</div>
        <h3>Performance and memory</h3>
        <p>iOS Safari is sensitive to audio and large numbers of DOM elements. The player therefore conserves memory: repeats are not unnecessarily prerendered in full, moving notes are built in a limited window and samples are not held more than necessary.</p>
      </section>
