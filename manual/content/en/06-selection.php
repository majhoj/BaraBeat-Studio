<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="selection">
        <h2>6. Selection, copying and undo</h2>
        <p>Elements can be moved individually or marked together with a selection rectangle. After moving, the selection remains active until you click outside it.</p>
        <h3>Keyboard</h3>
        <table>
          <thead><tr><th>Key</th><th>Function</th></tr></thead>
          <tbody>
            <tr><td><kbd>Alt</kbd> while dragging</td><td>Clone an element.</td></tr>
            <tr><td><kbd>Cmd</kbd> + <kbd>Z</kbd></td><td>Undo.</td></tr>
            <tr><td><kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd></td><td>Redo.</td></tr>
            <tr><td><kbd>Cmd</kbd> + <kbd>C</kbd></td><td>Copy selection.</td></tr>
            <tr><td><kbd>Cmd</kbd> + <kbd>X</kbd></td><td>Cut selection.</td></tr>
            <tr><td><kbd>Cmd</kbd> + <kbd>V</kbd></td><td>Paste. Pasted elements remain selected and can be moved immediately.</td></tr>
          </tbody>
        </table>
        <h3>Delete</h3>
        <p>On the desktop, use <kbd>Cmd</kbd> + <kbd>X</kbd> to cut and remove a selection. In the mobile landscape editor, the delete tool removes either the tapped element or the whole current selection. Choosers can also be removed with their own delete control.</p>
        <h3>Copying between tabs</h3>
        <p>Selected score elements can be copied through the clipboard between two browser tabs or windows. If the browser blocks access to the system clipboard, BaraBeat Studio also uses a local clipboard in the open tab.</p>
        <h3>Grid behaviour while moving</h3>
        <p>While dragging, an element follows the pointer smoothly. When released, it snaps to the nearest sensible position. This applies to notes, repeat marks, In/Out, ShortBar and choosers.</p>
        <h3>Selection on iPhone</h3>
        <p>In the mobile landscape editor, activate the selection tool in the lower palette and draw a rectangle around the required elements. You can move the selection together, copy it with the duplicate tool or remove it with the delete tool. Normal iOS text selection is suppressed in the score area so that its gestures do not conflict with the editor.</p>
      </section>
