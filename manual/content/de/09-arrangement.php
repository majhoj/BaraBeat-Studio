<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="arrangement">
        <h2>9. Arrangement</h2>
        <p>Das Arrangement ist die Timeline für vollständige Abläufe. Pattern aus der Pattern-Bibliothek werden in Abschnitte gezogen und dort nacheinander oder parallel abgespielt.</p>

        <h3>Pattern-Bibliothek</h3>
        <p>Links stehen alle im Notenblatt gefundenen Pattern. Ein Pattern kann per Drag-and-drop in die Timeline gezogen oder über den Plus-Button ans Ende angefügt werden.</p>

        <h3>Abschnitte</h3>
        <p>Ein Abschnitt enthält ein oder mehrere Pattern. Mehrere Pattern in derselben Zeile laufen parallel. Die Wiederholungszahl links bestimmt, wie oft der Abschnitt läuft. Kürzere Begleitungen können mitgeführt werden, wenn eine längere Begleitung die Anzahl der Durchläufe vorgibt.</p>

        <h3>Parallel einfügen</h3>
        <p>Wenn du ein Pattern in einen bestehenden Abschnitt ziehst, erscheint eine parallele Dropzone nur dann, wenn das Pattern dort musikalisch sinnvoll eingesetzt werden kann. Ein Instrument kann nicht gleichzeitig zwei unterschiedliche Hauptpattern derselben Art spielen, außer ein Solo ersetzt gezielt eine Begleitung.</p>

        <h3>Solo-Zellen</h3>
        <p>Bei Begleitabschnitten mit mehreren Wiederholungen erscheint ein Solo-Raster. Jede Zelle entspricht einem Durchlauf. Ziehst du ein Solo in eine Zelle, wird dieses Solo nur in diesem Durchlauf parallel zur Begleitung gespielt.</p>

        <ul>
          <li>Ist das Solo kürzer als der Begleitdurchlauf, läuft der Rest als Stille.</li>
          <li>Ist das Solo länger, wird der Abschnitt entsprechend mitgeführt.</li>
          <li>Spielt dasselbe Instrument Begleitung und Solo, setzt die Begleitung während des Solos aus und danach wieder ein.</li>
          <li><strong>In</strong> und <strong>Out</strong> werden auch bei Soli berücksichtigt.</li>
        </ul>

        <h3>Abschnitte verschieben</h3>
        <p>Abschnitte können in der Timeline nach oben oder unten verschoben werden. So lässt sich ein Arrangement schrittweise umstellen, ohne die Pattern neu aufzubauen.</p>

        <h3>Tempo im Arrangement</h3>
        <p>Jeder Abschnitt kann ein eigenes Tempo erhalten. Ein Tempowechsel gleitet über etwa zwei Takte in das neue Tempo und bleibt für die folgenden Abschnitte gültig, bis ein weiterer Tempowechsel gesetzt wird.</p>

        <h3>Shekere und Lautstärken</h3>
        <p>Auch im Arrangement kann der Shekere-Beat zugeschaltet werden. Swing-Profil, Feel und die Lautstärken lassen sich über die Bedienelemente oberhalb der Timeline öffnen. Die Instrumentenlautstärken gelten gemeinsam für Üben und Arrangement und werden mit dem Notenblatt gespeichert.</p>

        <h3>Rückgängig und Wiederherstellen</h3>
        <p>Änderungen an der Timeline werden in derselben Verlaufshistorie wie Editor- und Übungseinstellungen erfasst. Auf dem Desktop macht <kbd>Cmd</kbd> + <kbd>Z</kbd> den letzten Schritt rückgängig; <kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> stellt ihn wieder her.</p>
      </section>
