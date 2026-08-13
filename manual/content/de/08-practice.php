<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="practice">
        <h2>8. Übungsmodus</h2>
        <p>Der Übungsmodus ist für das wiederholte Trainieren einzelner Pattern gedacht. Er liest die Pattern aus dem Notenblatt und baut daraus einen Übungsloop.</p>

        <h3>Patternauswahl</h3>
        <p>Die Patternauswahl kann geöffnet oder geschlossen werden. Sie enthält drei Hauptbereiche:</p>
        <ul>
          <li><strong>Einstellungen</strong> für Start der Begleitung, Wiederholungen, Timer, Swing, Feel, Mute und Latenz.</li>
          <li><strong>Begleitung auswählen</strong> für Pattern, die parallel als Grundrhythmus laufen.</li>
          <li><strong>Übungsteile auswählen</strong> für Calls, Intros, Soli, Échauffements, Pausen und Begleitpattern, die geübt werden sollen.</li>
        </ul>
        <p>Auf dem Desktop ist <strong>Einstellungen</strong> beim Öffnen zunächst eingeklappt; <strong>Begleitung auswählen</strong> und <strong>Übungsteile auswählen</strong> sind ausgeklappt. Auf Smartphones bleiben alle drei Bereiche zunächst eingeklappt, damit Player und laufende Noten im Vordergrund bleiben.</p>

        <h3>Übungsszenarien</h3>
        <p>Für ein Notenblatt können mehrere Übungsszenarien gespeichert werden. Ein Szenario enthält die aktuellen Übungseinstellungen, ausgewählte Begleitungen und Übungsteile, Reihenfolge, Wiederholungen, Timer, Tempoaufbau, Lautstärken und weitere Werte. Die Szenarien werden mit dem Notenblatt gespeichert.</p>
        <p>Das aktive Szenario kann oben neben <strong>Übungsmodus</strong> oder in der Patternauswahl gewählt werden. Die Auswahl ist alphabetisch sortiert. <strong>Aktuelle Einstellungen</strong> bedeutet, dass gerade kein gespeichertes Szenario aktiv ist.</p>
        <p>Mit <strong>Neu</strong> speicherst du die aktuelle Konfiguration unter einem neuen Namen. <strong>Speichern</strong> aktualisiert das ausgewählte Szenario, <strong>Löschen</strong> entfernt es. Diese Aktionen aktualisieren zugleich die lokal gespeicherte Fassung des Notenblatts.</p>

        <h3>Begleitung startet</h3>
        <p>Die Begleitung kann sofort, nach Call, nach Intro oder nach Call und Intro starten. Wenn Call oder Intro bereits im Übungsteil ausgewählt sind, werden sie nicht doppelt hinzugefügt. Pausen und ShortBars werden berücksichtigt, damit die Begleitungen danach wieder gemeinsam starten.</p>

        <h3>Begleitung stoppt bei Call/Intro</h3>
        <p>Diese Option ist für Rhythmen gedacht, bei denen die Begleitung bei einem Call oder Intro aussetzt und danach wieder einsetzt.</p>

        <h3>Übungsteile und Reihenfolge</h3>
        <p>Mehrere Übungsteile können ausgewählt und in die gewünschte Reihenfolge gezogen werden. Die Wiederholungszahl eines einzelnen Blocks bestimmt, wie oft genau dieses Pattern innerhalb des Übungsablaufs gespielt wird. Optional kann zwischen Übungsteilen wieder die Begleitung allein gespielt werden.</p>

        <h3>Wiederholungen und Timer</h3>
        <ul>
          <li><strong>Wiederholungen</strong> gibt an, wie oft der Übungsloop gespielt wird.</li>
          <li><strong>Timer</strong> kann stattdessen eine Übungsdauer in Minuten festlegen.</li>
          <li>Wenn der Timer aktiv ist, läuft der aktuelle Übungsloop noch bis zum Ende, statt mitten im Pattern abzubrechen.</li>
        </ul>

        <h3>Tempoaufbau</h3>
        <p>Der Tempoaufbau erlaubt ein Starttempo und ein Zieltempo. Zusätzlich kann eingestellt werden, nach wie vielen Wiederholungen das Tempo um welchen Wert steigt. Zuerst wird der Tempoaufbau vollständig gespielt. Danach laufen die normalen Wiederholungen oder die Timerzeit weiter.</p>
        <p>Der Audioplayer zeigt das jeweils aktuelle Tempo an, während der Aufbau läuft.</p>

        <h3>Handsatz und H2H Leer = Mute</h3>
        <p>Für Djembe-Pattern kann der Handsatz pro Pattern gewählt werden, zum Beispiel <strong>Auto</strong>, <strong>H2H</strong> oder andere Modi. Bei <strong>H2H Leer = Mute</strong> werden Leernoten als leise Mute-Schläge hörbar. Die Lautstärke dieser Mute-Töne kann instrumentabhängig eingestellt sein.</p>

        <h3>Lautstärke im Übungsmodus</h3>
        <p>Ein Klick auf den Instrumentennamen bei den laufenden Noten öffnet die Lautstärke für dieses Instrument. Dort kann auch ein weiteres Overlay für einzelne Tonarten geöffnet werden, damit zum Beispiel Bass, Tone, Slap oder Mute relativ zueinander angepasst werden können.</p>

        <h3>Shekere-Beat</h3>
        <p>Vor dem Start einer Übung zählt die Shekere vier Beats vor. Über <strong>Shekere Beat</strong> kann sie zusätzlich während der Wiedergabe jeden Beat betonen. Die Funktion steht in Üben und Arrangement zur Verfügung; die Shekere besitzt eine eigene Lautstärkeeinstellung.</p>
      </section>
