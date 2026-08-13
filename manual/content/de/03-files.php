<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="files">
        <h2>3. Dateien öffnen und speichern</h2>
        <p>Notenblätter werden im Projekt als <code>.bbs</code>-Dateien gespeichert. Ältere <code>.txt</code>-Notenblätter können weiterhin geöffnet werden.</p>

        <h3>Öffnen</h3>
        <ul>
          <li><strong>Lokal</strong> zeigt Notenblätter, die in diesem Browser oder in der Home-Screen-App gespeichert wurden. Sie können in Ordnern und Unterordnern organisiert werden.</li>
          <li><strong>Server</strong> zeigt Notenblätter aus dem Ordner <code>Noten/</code>. Die Serverliste wird beim Öffnen des Dialogs neu eingelesen.</li>
          <li>Beim Laden vom Server merkt sich BaraBeat Studio den Ladezeitpunkt und die Version der Serverdatei. Wurde sie danach auf dem Server verändert, erscheint <strong>Server seitdem geändert</strong>. Eine eigene lokale Bearbeitung wird zusätzlich mit <strong>Lokal geändert</strong> und ihrem Zeitpunkt ausgewiesen.</li>
          <li>Der Hinweis oberhalb der Dateiliste bietet bei Bedarf <strong>Serverversion laden</strong>.</li>
          <li>Auf Smartphones ist die Dateiansicht vereinfacht, damit Öffnen und Löschen lokaler Dateien gut erreichbar bleiben.</li>
        </ul>

        <h3>Speichern</h3>
        <ul>
          <li><strong>Speichern</strong> überschreibt das aktuell geöffnete Notenblatt.</li>
          <li><strong>Speichern als</strong> ist für neue Namen oder Kopien gedacht.</li>
          <li>Auf dem iPhone steht <strong>Speichern als</strong> ebenfalls zur Verfügung. Damit können im mobilen Editor erstellte oder geänderte Notenblätter lokal beziehungsweise auf dem Server gesichert werden.</li>
          <li>In der lokalen Bibliothek kannst du Ordner erstellen und umbenennen. Ein Ordner lässt sich erst löschen, wenn er keine Notenblätter oder Unterordner mehr enthält.</li>
          <li>Bei erfolgreichem Speichern erscheint nur eine kurze Statusmeldung. Fehlermeldungen bleiben sichtbar.</li>
        </ul>

        <h3>Auf dem Server veröffentlichen</h3>
        <p>Beim Speichern auf dem Server wird das Notenblatt veröffentlicht und zugleich als lokale Datei geführt. BaraBeat Studio hinterlegt in dieser lokalen Fassung ein Publish-Token. Es weist beim späteren Aktualisieren oder Zurückziehen nach, dass diese Veröffentlichung von dir verwaltet werden darf.</p>
        <ul>
          <li>Speicherst du dieselbe veröffentlichte Datei erneut auf dem Server, wird ihre Veröffentlichung aktualisiert.</li>
          <li>Wählst du einen neuen Namen, entsteht eine neue Veröffentlichung.</li>
          <li><strong>Veröffentlichung löschen</strong> entfernt die Serverfassung; die lokale Datei bleibt erhalten.</li>
          <li>Fehlt das Publish-Token, kann die Serverdatei weiterhin gelesen werden, aber nicht als deine Veröffentlichung aktualisiert oder gelöscht werden. Verwende dafür den Browser beziehungsweise die Home-Screen-App, mit der sie veröffentlicht wurde.</li>
        </ul>
        <p>Die Statusspalte unterscheidet unter anderem lokale Dateien, Veröffentlichungen und lokal geänderte Fassungen. Bei Serverdateien zeigt BaraBeat Studio außerdem an, wenn sich die Veröffentlichung seit dem letzten Laden geändert hat.</p>

        <h3>SVG, PDF und Drucken</h3>
        <p>Über <strong>Datei → Exportieren</strong> gibst du einen Dateinamen ein und wählst das Format:</p>
        <ul>
          <li><strong>SVG</strong> ist eine skalierbare Vektordatei und eignet sich für Weiterverarbeitung oder hochwertige Darstellung.</li>
          <li><strong>PDF</strong> eignet sich zum Weitergeben und Drucken mit gleichbleibendem Seitenbild.</li>
        </ul>
        <p>Zum Drucken öffnest du die exportierte PDF-Datei und verwendest den Druckbefehl des Browsers oder Betriebssystems. Einen eigenen Druckdialog besitzt BaraBeat Studio nicht.</p>

        <div class="hint">
          <strong>Hinweis:</strong> Im Notenblatt werden nicht nur die gezeichneten Noten gespeichert, sondern auch Übungseinstellungen, gespeicherte Übungsszenarien, Patternauswahl, Arrangement-Timeline, Lautstärken, Swing, Feel und weitere Metadaten.
        </div>
      </section>
