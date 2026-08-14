<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="troubleshooting">
        <h2>13. Troubleshooting</h2>
        <h3>A Pattern is not recognised</h3>
        <ul><li>Check that the instrument and function choosers are positioned correctly above the Pattern.</li><li>Check that notes sit on the grid.</li><li>For 9/8 rhythms, make sure In, Out and ShortBar use suitable grid positions.</li></ul>
        <h3>Playback sounds displaced</h3>
        <ul><li>Check Bluetooth latency.</li><li>Check Feel values.</li><li>At Pattern transitions, check In/Out and ShortBar.</li></ul>
        <h3>The moving notes show gaps</h3>
        <ul><li>Check whether very long repeats or many simultaneous Patterns are active.</li><li>On iPhone/Safari, test with realistic repeat values.</li><li>For newly constructed files, save and reopen so metadata is updated cleanly.</li><li>At transitions involving Call, Intro, ShortBar or Accompaniment Out, check that the intended practice case has actually been saved as a scenario.</li></ul>
        <h3>The timeline shows “mixed”</h3>
        <p>This can appear when parallel Patterns in a row have different repeat structures. One-pass Accompaniment groups carried through a longer section should no longer be shown as “mixed”.</p>
        <h3>A sample is missing</h3>
        <p>If a sample is missing or cannot be loaded, the player should display an error. Check its spelling in the sound folder and whether the file exists on the server.</p>
        <h3>The mobile editor does not appear</h3>
        <p>The iPhone editor is enabled only in landscape. Make sure orientation lock is off and rotate the iPhone again. Portrait intentionally retains the compact reading and playback view.</p>
        <h3>There is no sound on iPhone</h3>
        <p>First check volume, audio output and Bluetooth. On supported devices, BaraBeat Studio uses a playback audio session so audio can work in silent mode. After an iOS or browser change, another tap on Play may still be required to authorise audio output.</p>
        <h3>Offline data is missing after reinstallation</h3>
        <p>If the Home Screen app was removed and added again, iOS may have created a new local storage area. Reload the required scores from the server. Purely local files can only be restored if they were backed up beforehand.</p>
        <h3>A server file does not show the expected version</h3>
        <p>Reopen the file dialog to refresh the server list. Check the status and modification columns for local edits or a changed server version. <strong>Load server version</strong> replaces the local version after confirmation.</p>
        <h3>A publication cannot be updated or deleted</h3>
        <p>These actions require the publish token in the local file. Open it in the browser or Home Screen app from which it was published. A file merely loaded from the server can be read but does not automatically have management permission.</p>
        <h3>The selected language does not change the score</h3>
        <p>This is intentional: language selection translates controls and messages only. Rhythm names, free text, Pattern labels and musical data remain unchanged.</p>
        <h3>The SVG or PDF cannot be found</h3>
        <p>The export is sent to the browser as a download. Check the download folder or, on iPhone, Files and Safari’s download display. Browser pop-up or download restrictions may require another confirmation.</p>
      </section>
