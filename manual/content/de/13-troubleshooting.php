<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="troubleshooting">
        <h2>13. Fehlersuche</h2>

        <h3>Ein Pattern wird nicht erkannt</h3>
        <ul>
          <li>Prüfen, ob Instrumenten-Chooser und Funktions-Chooser korrekt über dem Pattern stehen.</li>
          <li>Prüfen, ob die Noten wirklich auf dem Raster sitzen.</li>
          <li>Bei 9/8-Rhythmen darauf achten, dass In, Out und ShortBar auf passenden Rasterpositionen stehen.</li>
        </ul>

        <h3>Die Wiedergabe klingt verschoben</h3>
        <ul>
          <li>Bluetooth-Latenz prüfen.</li>
          <li>Feel-Werte kontrollieren.</li>
          <li>Bei Patternübergängen In/Out und ShortBar prüfen.</li>
        </ul>

        <h3>Die laufenden Noten zeigen Lücken</h3>
        <ul>
          <li>Prüfen, ob sehr lange Wiederholungen oder viele Pattern gleichzeitig aktiv sind.</li>
          <li>Auf iPhone/Safari möglichst mit realistischen Wiederholungen testen.</li>
          <li>Bei neu gebauten Dateien speichern und neu öffnen, damit Metadaten sauber aktualisiert werden.</li>
          <li>Bei Übergängen mit Call, Intro, ShortBar oder Begleit-Out prüfen, ob der gewünschte Übungsfall wirklich als Szenario gespeichert wurde.</li>
        </ul>

        <h3>Die Timeline zeigt "gemischt"</h3>
        <p>Das kann erscheinen, wenn parallele Pattern in einer Zeile unterschiedliche Wiederholungsstrukturen haben. Einmalige Begleitgruppen, die in einem längeren Abschnitt mitgeführt werden, sollten inzwischen nicht mehr als "gemischt" angezeigt werden.</p>

        <h3>Ein Sample fehlt</h3>
        <p>Wenn ein Sample fehlt oder nicht geladen werden kann, sollte der Player den Fehler anzeigen. Prüfe in diesem Fall die Schreibweise der Datei im Sound-Ordner und ob die Datei auf dem Server vorhanden ist.</p>

        <h3>Der mobile Editor erscheint nicht</h3>
        <p>Der Editor wird auf dem iPhone nur im Querformat aktiviert. Prüfe, ob die Ausrichtungssperre des Geräts ausgeschaltet ist, und drehe das iPhone erneut. Im Hochformat bleibt bewusst die kompakte Lese- und Abspielansicht sichtbar.</p>

        <h3>Auf dem iPhone ist kein Ton zu hören</h3>
        <p>Prüfe zuerst Lautstärke, Audioausgabe und Bluetooth-Verbindung. BaraBeat Studio verwendet auf unterstützten Geräten eine Wiedergabe-Audiositzung, damit Audio auch im Stummmodus möglich ist. Nach einem iOS- oder Browserwechsel kann trotzdem ein erneuter Tipp auf Play nötig sein, um die Audioausgabe freizugeben.</p>

        <h3>Offline-Daten fehlen nach einer Neuinstallation</h3>
        <p>Wurde die Home-Screen-App gelöscht und neu hinzugefügt, kann iOS einen neuen lokalen Speicherbereich angelegt haben. Öffne die benötigten Notenblätter erneut vom Server. Rein lokal gespeicherte Dateien lassen sich nur wiederherstellen, wenn sie zuvor auf dem Server oder anderweitig gesichert wurden.</p>

        <h3>Eine Serverdatei zeigt nicht die erwartete Fassung</h3>
        <p>Öffne den Dateidialog erneut, damit die Serverliste neu eingelesen wird. Prüfe in der Status- und Änderungsspalte, ob die lokale Datei bearbeitet wurde oder sich die Serverfassung seit dem letzten Laden geändert hat. Mit <strong>Serverversion laden</strong> ersetzt du die lokale Fassung nach der angezeigten Sicherheitsabfrage.</p>

        <h3>Eine Veröffentlichung lässt sich nicht aktualisieren oder löschen</h3>
        <p>Für diese Aktionen benötigt die lokale Datei ihr Publish-Token. Öffne die lokale Fassung in dem Browser oder der Home-Screen-App, mit der sie veröffentlicht wurde. Eine nur vom Server geladene Datei darf gelesen werden, besitzt aber nicht automatisch die Verwaltungsberechtigung der ursprünglichen lokalen Fassung.</p>

        <h3>Die gewählte Sprache verändert das Notenblatt nicht</h3>
        <p>Das ist beabsichtigt: Die Sprachwahl übersetzt nur Bedienelemente und Meldungen. Rhythmusnamen, freie Texte, Patternbezeichnungen und musikalische Daten bleiben unverändert.</p>

        <h3>SVG oder PDF wird nicht gefunden</h3>
        <p>Der Export wird als Download an den Browser übergeben. Prüfe den Download-Ordner beziehungsweise auf dem iPhone die Dateien-App und die Downloadanzeige von Safari. Pop-up- oder Downloadbeschränkungen des Browsers können eine erneute Bestätigung erfordern.</p>
      </section>
