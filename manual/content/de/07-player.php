<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="player">
        <h2>7. Audioplayer</h2>
        <p>Der Audioplayer spielt eine Übung oder ein Arrangement ab. In seiner eigenständigen Ansicht kann er außerdem ausgewählte Stimmen eines Notenblatts wiedergeben.</p>

        <h3>Bedienelemente</h3>
        <ul>
          <li><strong>BPM</strong> steuert das Tempo in Beats pro Minute.</li>
          <li><strong>Play</strong> startet oder stoppt die Wiedergabe.</li>
          <li><strong>Stimme</strong> wählt im eigenständig geöffneten Player einzelne Instrumente oder Instrumentengruppen aus.</li>
          <li><strong>Export WAV</strong> rendert die aktuelle Übung oder das Arrangement als WAV-Datei, sofern die Schaltfläche in der verwendeten Konfiguration angeboten wird. Auf Smartphones kann sie ausgeblendet sein, weil mobile Browser Downloads unterschiedlich behandeln.</li>
        </ul>

        <h3>Instrumente und Lautstärken</h3>
        <p>Im Übungsmodus und Arrangement öffnet <strong>Lautstärke</strong> den Instrumentenmixer. Dort lassen sich Djembe-Stimmen, Kenkeni, Sangban, Dununba, Ballet Dununs und Shekere getrennt regeln oder stummschalten. Für einzelne Instrumente können zusätzlich die Lautstärken ihrer Schläge, etwa Bass, Tone, Slap oder Mute, relativ zueinander eingestellt werden.</p>

        <h3>Bluetooth-Latenz</h3>
        <p>Im Übungsmodus gibt es den Regler <strong>Latenz für Bluetooth ms</strong>. Damit kann die Anzeige gegenüber dem Ton verschoben werden, damit rote Linie und hörbarer Schlag auch bei Bluetooth-Kopfhörern zusammenpassen. Auf dem iPhone ist die Einstellung über den Menüpunkt <strong>Latenz</strong> erreichbar und wird auf dem Gerät gespeichert.</p>

        <h3>Laufende Noten</h3>
        <p>Unter dem Player laufen die gerade gespielten Noten von rechts nach links unter einer festen roten Linie hindurch. Der Schlag erklingt, wenn das Symbol die rote Linie erreicht. Taktlinien, Patternhinterlegung, Instrumentenname und Patternname helfen bei der Orientierung.</p>
        <p>Rechts in der Kopfzeile der laufenden Noten wird je nach Modus angezeigt, wie viele Wiederholungen noch offen sind oder wie lange die Übung noch läuft. Bei aktivem Tempoaufbau wird zunächst die verbleibende Anzahl der Aufbau-Wiederholungen angezeigt.</p>
      </section>
