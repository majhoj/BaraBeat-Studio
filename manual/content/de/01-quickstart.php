<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="quickstart">
        <h2>1. Kurzstart</h2>
        <p>BaraBeat Studio ist ein Editor und Player für Djembe- und Dunun-Notation. Du kannst Pattern wie Call, Intro, Begleitpattern, Solo oder Échauffement notieren und direkt anhören.</p>

        <h3>BaraBeat in knapp 3 Minuten</h3>
        <p>Das Video zeigt die wichtigsten Schritte vom ersten Eintrag im Notenblatt bis zum Abspielen und Speichern eines Rhythmus. Anschließend kannst du die einzelnen Schritte im Kurzstart in Ruhe nachvollziehen.</p>
        <video
          class="manual-quickstart-video"
          controls
          playsinline
          preload="metadata"
          poster="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/poster.png'); ?>">
          <source
            src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/barabeat-quickstart.mp4'); ?>"
            type="video/mp4">
          Dein Browser unterstützt die Wiedergabe dieses Videos nicht.
        </video>

        <div class="workflow">
          <h3>Schneller Arbeitsablauf</h3>
          <ol>
            <li>Über <strong>Datei</strong> ein vorhandenes Notenblatt öffnen oder über <strong>Notenblatt</strong> ein neues Blatt anlegen.</li>
            <li>Oben im Blatt den <strong>Rhythmusnamen</strong> eingeben.</li>
            <li>Mit Instrumenten- und Funktions-Chooser die Spur beschriften, zum Beispiel <em>Djembe 1</em> und <em>Begleitpattern</em>.</li>
            <li>Noten und bei Bedarf Steuerzeichen aus der Palette auf das Raster setzen.</li>
            <li>Das Auswahlfeld vor dem gewünschten Pattern aktivieren.</li>
            <li>Tempo einstellen und mit dem Play-Button <strong>Sofort-Spielen</strong> starten.</li>
            <li>Über <strong>Datei → Speichern</strong> sichern; für einen neuen Namen oder eine Kopie <strong>Speichern als</strong> verwenden.</li>
          </ol>
        </div>

        <p>Für systematisches Training wechselst du anschließend in den <strong>Übungsmodus</strong>; längere Abläufe baust du im <strong>Arrangement</strong>. Auf Smartphones dient das Hochformat vor allem zum Lesen und Abspielen, das Querformat zum Bearbeiten.</p>
      </section>
