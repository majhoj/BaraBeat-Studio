<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="files">
        <h2>3. Opening and saving files</h2>
        <p>Scores are stored as <code>.bbs</code> files. Older <code>.txt</code> scores can still be opened.</p>

        <h3>Open</h3>
        <ul>
          <li><strong>Local</strong> shows scores stored in this browser or Home Screen app. They can be organised in folders and subfolders.</li>
          <li><strong>Server</strong> shows scores from the <code>Noten/</code> folder. The server list is refreshed whenever the dialog opens.</li>
          <li>When loading from the server, BaraBeat Studio remembers the download time and server-file version. If it subsequently changes on the server, <strong>Server changed since then</strong> appears. Local editing is shown separately as <strong>Locally changed</strong> with its time.</li>
          <li>When required, the notice above the file list offers <strong>Load server version</strong>.</li>
          <li>On smartphones, the file view is simplified so that opening and deleting local files remain easy to reach.</li>
        </ul>

        <h3>Save</h3>
        <ul>
          <li><strong>Save</strong> overwrites the currently open score.</li>
          <li><strong>Save as</strong> is intended for new names or copies.</li>
          <li><strong>Save as</strong> is also available on iPhone, allowing scores created or changed in the mobile editor to be stored locally or on the server.</li>
          <li>You can create and rename folders in the local library. A folder can only be deleted when it contains no scores or subfolders.</li>
          <li>A successful save produces a brief status message. Error messages remain visible.</li>
        </ul>

        <h3>Publish on the server</h3>
        <p>Saving to the server publishes the score and also keeps it as a local file. BaraBeat Studio stores a publish token in that local copy. The token proves later that you are allowed to update or withdraw the publication.</p>
        <ul>
          <li>Saving the same published file to the server again updates its publication.</li>
          <li>Using a new name creates a new publication.</li>
          <li><strong>Delete publication</strong> removes the server version; the local file remains.</li>
          <li>Without the publish token, the server file can still be read but cannot be updated or deleted as your publication. Use the browser or Home Screen app from which it was published.</li>
        </ul>
        <p>The status column distinguishes local files, publications and locally changed versions. For server files, BaraBeat Studio also shows whether the publication has changed since it was last loaded.</p>

        <h3>SVG, PDF and printing</h3>
        <p>Under <strong>File → Export</strong>, enter a file name and select the format:</p>
        <ul>
          <li><strong>SVG</strong> is a scalable vector file suitable for further editing or high-quality display.</li>
          <li><strong>PDF</strong> is suitable for sharing and printing with a consistent page layout.</li>
        </ul>
        <p>To print, open the exported PDF and use the browser or operating system print command. BaraBeat Studio does not have a separate print dialog.</p>

        <div class="hint"><strong>Note:</strong> The score stores not only the drawn notes but also practice settings, saved practice scenarios, Pattern selections, the Arrangement timeline, volumes, Swing, Feel and other metadata.</div>
      </section>
