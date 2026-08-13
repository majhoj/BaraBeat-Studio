<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="mobile">
        <h2>11. Nutzung auf Smartphones</h2>
        <p>Auf Smartphones kann BaraBeat Studio Notenblätter öffnen, anzeigen, sofort abspielen, üben und bearbeiten. Die Anzeige- und Bearbeitungsfunktionen wechseln automatisch mit der Ausrichtung des Geräts. Eine vorhandene Arrangement-Timeline kann mobil abgespielt, aber noch nicht bearbeitet werden.</p>

        <h3>Mobile Bedienung</h3>
        <p>Die Menüleiste zeigt nur die für den aktuellen Modus wichtigen Befehle. Unter <strong>Datei</strong> findest du Öffnen, Speichern, Speichern als, die Bedienungsanleitung und Abmelden. In der mobilen Bedienungsanleitung führt der dauerhaft sichtbare Button <strong>Zurück zum Notenblatt</strong> wieder zur Anwendung. Im Übungsmodus sind zusätzlich <strong>Patternauswahl</strong> und <strong>Latenz</strong> erreichbar. Enthält das geladene Notenblatt bereits ein Arrangement, erscheint außerhalb des Übungsmodus der Befehl <strong>Arrangement abspielen</strong>; er öffnet den Player in einem Overlay.</p>

        <h3>Portrait und Landscape</h3>
        <ul>
          <li><strong>Portrait:</strong> kompakte Leseansicht mit einem Takt pro Zeile, Sofort-Spielen, Übungsmodus und Wiedergabe vorhandener Arrangements.</li>
          <li><strong>Landscape:</strong> mobiler Noteneditor mit größerem Taktraster, Notenblatt-Menü und horizontaler Palette.</li>
        </ul>

        <h3>Mobile Notenblattansicht</h3>
        <p>Mehrseitige Notenblätter werden seitenweise dargestellt, nicht als gesamter Stapel auf eine Displayhöhe gedrückt. Dadurch bleiben mehrseitige Blätter lesbar. Jeder Takt steht in einer eigenen Zeile. Mehrtaktige Pattern werden in der Portrait-Ansicht kompakt zusammengefasst; Instrument und Patternname stehen nur am Beginn des Patterns.</p>
        <p>Bei den laufenden Noten stehen Instrument und Patternname platzsparend in einer Zeile. Die rote Spiellinie wird nur über den eigentlichen Notenzeilen gezeichnet, damit längere Namen lesbar bleiben.</p>

        <h3>Noten im Landscape-Modus bearbeiten</h3>
        <ol>
          <li>Das iPhone quer halten. Der Landscape-Editor erscheint automatisch.</li>
          <li>In der unteren Palette ein Noten- oder Steuerzeichen wählen und anschließend auf die gewünschte Rasterposition tippen.</li>
          <li>Vorhandene Noten und Markierungen können direkt angefasst, verschoben und beim Loslassen am Raster ausgerichtet werden.</li>
          <li>Mit dem Auswahlwerkzeug einen Rahmen aufziehen. Danach kann die Auswahl gemeinsam verschoben, dupliziert oder gelöscht werden.</li>
          <li>Mit <strong>Instrument und Funktion einsetzen</strong> erscheinen in einem freien Takt die beiden Chooser. Vorhandene Chooser können über ihre Auswahlfelder geändert, verschoben oder gelöscht werden.</li>
          <li>Die Pfeile in der Chooser-Zeile verschieben ein vollständiges Pattern mit allen zugehörigen Elementen nach oben oder unten.</li>
          <li>Der Rhythmusname kann oben direkt eingegeben werden. Das Textwerkzeug setzt zusätzliche Kommentare in einen Takt; vorhandene Texte können im eingeblendeten Eingabefeld geändert werden.</li>
          <li>Über <strong>Datei → Speichern</strong> oder <strong>Speichern als</strong> die Änderungen sichern.</li>
        </ol>

        <div class="hint">
          <strong>Touch-Bedienung:</strong> Die Palette bleibt am unteren Rand sichtbar und kann horizontal gescrollt werden. Ein Doppeltipp auf ein Wiederholungszeichen öffnet wie auf dem Desktop dessen Einstellung. Für Eingabefelder darf die iOS-Tastatur erscheinen; nach dem Schließen bleibt die Notenblattgröße erhalten.
        </div>

        <h3>Sofort-Spielen auf dem iPhone</h3>
        <p>Die Auswahlfelder vor den Pattern, BPM und Play stehen auch mobil zur Verfügung. Mehrere Instrumente können parallel laufen; Pattern desselben Instruments werden nacheinander behandelt. Die klingenden Noten werden kurz hervorgehoben. Die Kopfzeile bleibt beim Scrollen sichtbar, damit die Wiedergabe jederzeit gestoppt werden kann.</p>

        <h3>Offline verwenden</h3>
        <ol>
          <li>BaraBeat Studio auf dem iPhone einmal vollständig über eine <strong>HTTPS-Verbindung</strong> öffnen.</li>
          <li>Warten, bis die Meldung <strong>Offline bereit</strong> erscheint. Dabei werden Oberfläche, Audioplayer und Sounds auf dem Gerät vorbereitet.</li>
          <li>Gewünschte Notenblätter vorher einmal vom Server laden. Sie liegen anschließend in der lokalen Bibliothek.</li>
          <li>Optional in Safari über <strong>Teilen &gt; Zum Home-Bildschirm</strong> als Web-App hinzufügen.</li>
        </ol>
        <p>Ohne Verbindung können lokale Notenblätter geöffnet, angezeigt, bearbeitet und abgespielt werden. Lokales Speichern ist ebenfalls möglich. Die Serverliste, das erneute Laden vom Server und das Speichern auf dem Server benötigen weiterhin eine Internetverbindung.</p>

        <h3>Hintergrund und Sperrbildschirm</h3>
        <p>Auf unterstützten iPhones und Browsern verwendet BaraBeat Studio die Media Session und eine Wiedergabe-Audiositzung. Dadurch kann Audio auch bei gesperrtem Bildschirm weiterlaufen. Der Sperrbildschirm zeigt Titel, BaraBeat-Logo und Wiedergabesteuerung; Play, Pause und Stopp lassen sich dort auslösen. Welche Bedienelemente sichtbar sind, hängt von iOS, Browser und Ausgabegerät ab.</p>

        <div class="warning">
          <strong>Lokale Daten vor dem Löschen sichern:</strong> Safari und eine zum Home-Bildschirm hinzugefügte BaraBeat-App können getrennte lokale Speicherbereiche verwenden. Wenn du den Home-Screen-Eintrag löschst, können die darin gespeicherten lokalen Notenblätter, Übungsszenarien, Einstellungen und Offline-Daten verloren gehen. Ein erneutes Hinzufügen stellt diese lokalen Daten nicht automatisch wieder her. Speichere wichtige Notenblätter deshalb vorher auf dem Server; darin enthaltene Übungsszenarien werden zusammen mit der Datei gesichert. Serverdateien bleiben vom Löschen des Home-Screen-Eintrags unberührt.
        </div>

        <h3>Leistung und Speicher</h3>
        <p>iOS Safari ist bei Audio und vielen DOM-Elementen empfindlich. Der Player arbeitet deshalb speicherschonend: Wiederholungen werden nicht unnötig vollständig vorgerendert, laufende Noten werden begrenzt aufgebaut und Samples werden möglichst nicht mehrfach gehalten.</p>
      </section>
