<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="editor">
        <h2>4. Notenblatt bearbeiten</h2>
        <p>Das Notenblatt ist die Grundlage für alle weiteren Funktionen. Pattern entstehen aus Abschnitten, die durch Instrumenten-Chooser und Funktions-Chooser beschriftet werden.</p>

        <h3>Blatttypen</h3>
        <table>
          <thead>
            <tr>
              <th>Typ</th>
              <th>Raster</th>
              <th>Typische Verwendung</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Binär</td>
              <td>Vier Unterteilungen pro Puls</td>
              <td>Viele gerade Djembe- und Dunun-Rhythmen</td>
            </tr>
            <tr>
              <td>Tenär</td>
              <td>Drei Unterteilungen pro Puls</td>
              <td>Triolische Rhythmen</td>
            </tr>
            <tr>
              <td>9/8</td>
              <td>Drei Dreiergruppen</td>
              <td>Spezielle 9/8-Rhythmen wie Koreduga</td>
            </tr>
          </tbody>
        </table>

        <h3>Mehrseitige Notenblätter</h3>
        <p>Ein Notenblatt kann mehrere Seiten haben. Pro Seite werden zehn Zeilen angezeigt. Wenn mehr Zeilen benötigt werden, kannst du über <strong>Blatt hinzufügen</strong> eine weitere Seite anlegen. Die Legende steht auf dem letzten Blatt, rechts unten erscheinen Seitenzahlen wie <code>1/2</code> und <code>2/2</code>.</p>

        <h3>Palette</h3>
        <p>Die Palette enthält Notensymbole, Chooser und Steuerzeichen. Auf dem Desktop bleibt sie beim Scrollen sichtbar und kann bei Bedarf verschoben werden. Im mobilen Landscape-Editor liegt sie als horizontal scrollbare Werkzeugleiste am unteren Displayrand.</p>

        <h3>Instrument und Funktion festlegen</h3>
        <p>Der Instrumenten-Chooser legt die Stimme fest, zum Beispiel Djembe, Kenkeni, Sangban, Dununba oder Ballet Dununs. Der Funktions-Chooser beschreibt die Aufgabe des Patterns, etwa Call, Intro, Begleitpattern, Solo, Échauffement oder Outro. Für <strong>Begleitpattern</strong> und <strong>Solo</strong> kannst du eine freie Bezeichnung eingeben, zum Beispiel <em>Begleitpattern 2</em> oder <em>Solo 1</em>. Beim späteren Bearbeiten erscheint diese eigene Bezeichnung wieder im Eingabefeld.</p>
        <p><strong>Ballet Dununs</strong> bezeichnet in BaraBeat Studio die gemeinsame Spur beziehungsweise das gemeinsam gespielte Set aus Kenkeni, Sangban und Dununba.</p>

        <h3>Rhythmusname</h3>
        <p>Der Rhythmusname steht oben im Notenblatt. Er kann direkt im Feld geändert werden. Beim ersten Öffnen wird der zuletzt geladene Titel wiederhergestellt, wenn der Browser diese Information noch kennt.</p>

        <h3>Sofort-Spielen im Notenblatt</h3>
        <p>Neben dem Rhythmusnamen stehen eine Tempoangabe und ein Play-Button. Kleine Auswahlfelder vor den Pattern markieren, was direkt aus dem Notenblatt vorgespielt werden soll. Die Felder werden aktiv, sobald ein Takt durch Instrument, Patternname oder Noten als spielbar erkannt wird; ein vorheriges Speichern und Neuladen ist nicht nötig. Auf dem iPhone bleibt die Kopfzeile mit Rhythmusname und Play-Button beim Scrollen sichtbar.</p>
        <ul>
          <li>Ein ausgewähltes Pattern wird als Loop gespielt, bis du auf Stopp klickst.</li>
          <li>Mehrere Pattern verschiedener Instrumente laufen parallel.</li>
          <li>Mehrere Pattern desselben Instruments laufen nacheinander.</li>
          <li>Eine ausgewählte Begleitung läuft durch, während ein zusätzliches Solo parallel gespielt wird. Ein Out der Begleitung wird dabei für den nahtlosen Loop ignoriert.</li>
          <li>Die jeweils klingenden Noten werden im Notenblatt kurz farblich hervorgehoben.</li>
        </ul>

        <h3>Ganzes Pattern verschieben</h3>
        <p>Ein vollständiges Pattern kann einschließlich Noten, Choosern und zusätzlichen Markierungen nach oben oder unten verschoben werden. Dazu dienen die Pfeile am Beginn des Patterns. Die Funktion steht im Desktop-Editor und im mobilen Landscape-Editor zur Verfügung.</p>
      </section>
