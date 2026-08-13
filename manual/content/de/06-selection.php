<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="selection">
        <h2>6. Auswahl, Kopieren und Rückgängig</h2>
        <p>Elemente können einzeln verschoben oder über einen Auswahlrahmen gemeinsam markiert werden. Nach dem Verschieben bleibt die Auswahl bestehen, bis du neben die Auswahl klickst.</p>

        <h3>Tastatur</h3>
        <table>
          <thead>
            <tr>
              <th>Taste</th>
              <th>Funktion</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><kbd>Alt</kbd> beim Ziehen</td>
              <td>Element klonen.</td>
            </tr>
            <tr>
              <td><kbd>Cmd</kbd> + <kbd>Z</kbd></td>
              <td>Rückgängig.</td>
            </tr>
            <tr>
              <td><kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd></td>
              <td>Wiederherstellen.</td>
            </tr>
            <tr>
              <td><kbd>Cmd</kbd> + <kbd>C</kbd></td>
              <td>Auswahl kopieren.</td>
            </tr>
            <tr>
              <td><kbd>Cmd</kbd> + <kbd>X</kbd></td>
              <td>Auswahl ausschneiden.</td>
            </tr>
            <tr>
              <td><kbd>Cmd</kbd> + <kbd>V</kbd></td>
              <td>Einfügen. Eingefügte Elemente bleiben ausgewählt und können sofort verschoben werden.</td>
            </tr>
          </tbody>
        </table>

        <h3>Löschen</h3>
        <p>Auf dem Desktop kannst du eine Auswahl mit <kbd>Cmd</kbd> + <kbd>X</kbd> ausschneiden und damit vom Blatt entfernen. Im mobilen Landscape-Editor löscht das Löschen-Werkzeug entweder das angetippte Element oder die gesamte aktuelle Auswahl. Auch Chooser lassen sich dort über ihre eigene Löschen-Schaltfläche entfernen.</p>

        <h3>Tab-übergreifendes Kopieren</h3>
        <p>Ausgewählte Notenelemente können über die Zwischenablage auch zwischen zwei Browser-Tabs oder Fenstern kopiert werden. Wenn der Browser den Zugriff auf die System-Zwischenablage verhindert, verwendet BaraBeat Studio zusätzlich eine lokale Zwischenablage im geöffneten Tab.</p>

        <h3>Raster beim Verschieben</h3>
        <p>Während des Ziehens folgt ein Element flüssig der Maus. Beim Loslassen rastet es an der nächstgelegenen sinnvollen Position ein. Das gilt für Noten, Wiederholungszeichen, In/Out, ShortBar und Chooser.</p>

        <h3>Auswahl auf dem iPhone</h3>
        <p>Im mobilen Landscape-Editor aktivierst du in der unteren Palette das Auswahlwerkzeug und ziehst anschließend einen Rahmen um die gewünschten Elemente. Die Auswahl kann gemeinsam verschoben, mit dem Duplizieren-Werkzeug kopiert oder mit dem Löschen-Werkzeug entfernt werden. Die normale iOS-Textauswahl ist im Notenbereich unterdrückt, damit Gesten nicht mit dem Editor kollidieren.</p>
      </section>
