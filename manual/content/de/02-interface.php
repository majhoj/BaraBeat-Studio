<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="interface">
        <h2>2. Oberfläche</h2>
        <p>Die Anwendung besteht aus dem Notenblatt, einer verschiebbaren Palette, dem Hauptmenü und optionalen Arbeitsansichten für Übungsmodus und Arrangement.</p>

        <h3>Hauptmenü</h3>
        <table>
          <thead>
            <tr>
              <th>Menü</th>
              <th>Wofür es gedacht ist</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Datei</strong></td>
              <td>Notenblätter öffnen, speichern, lokal oder auf dem Server verwalten, exportieren und die Sprache wählen. Auf Smartphones ist hier auch die Bedienungsanleitung erreichbar.</td>
            </tr>
            <tr>
              <td><strong>Notenblatt</strong></td>
              <td>Binäres, tenäres oder 9/8-Blatt anlegen sowie Blätter hinzufügen oder löschen.</td>
            </tr>
            <tr>
              <td><strong>Einfügen</strong></td>
              <td>Zusätzliche Elemente oder Vorlagen einsetzen.</td>
            </tr>
            <tr>
              <td><strong>Werkzeuge</strong></td>
              <td>Auf dem Desktop Übungsmodus, Arrangement, Bedienungsanleitung und Template-Auswahl öffnen.</td>
            </tr>
          </tbody>
        </table>

        <h3>Sprache</h3>
        <p>Über <strong>Datei → Sprache</strong> kannst du <strong>Deutsch</strong>, <strong>English</strong>, <strong>Français</strong>, <strong>Español</strong> oder <strong>Português</strong> wählen. BaraBeat Studio merkt sich die Auswahl und zeigt die Oberfläche beim nächsten Aufruf wieder in dieser Sprache an. Die Sprachwahl ändert nur sichtbare Bedienelemente und Meldungen; musikalische Daten, frei eingegebene Texte und gespeicherte Notenblätter werden nicht übersetzt oder verändert.</p>

        <h3>Anmeldung</h3>
        <p>Ist die Zugangsbeschränkung aktiviert, erscheint vor BaraBeat Studio eine Passwortabfrage. Die Anmeldung bleibt auf dem jeweiligen Browser beziehungsweise in der Home-Screen-App bestehen, bis du im Menü <strong>Datei → Abmelden</strong> wählst oder die Browserdaten löschst. Auf gemeinsam genutzten Geräten solltest du dich nach der Arbeit abmelden.</p>

        <h3>Administration: temporärer Zugang</h3>
        <p>Nach einer regulären Anmeldung kannst du über <strong>Datei → Zugang 5 Min öffnen</strong> den Passwortschutz vorübergehend für alle Besucher aussetzen. Das gilt ohne Anmeldung oder Cookie auch für automatisierte Browser, API-Aufrufe und Prüfwerkzeuge. Eine Sicherheitsabfrage verhindert versehentliches Öffnen. Der Button zeigt die Restzeit und kann das Zugangsfenster vorzeitig schließen; nach fünf Minuten wird der Schutz automatisch wieder aktiv.</p>
        <p>Während dieses Fensters kennzeichnet BaraBeat Studio die Antworten mit <code>noindex</code> und <code>no-store</code>. Automatisierte Werkzeuge dürfen zugreifen, Suchmaschinen sollen die vorübergehend offene Anwendung jedoch weder indexieren noch zwischenspeichern.</p>

        <h3>Templates</h3>
        <p>Über <strong>Werkzeuge → Template</strong> kannst du zwischen visuellen Presets wechseln. Vorhanden sind unter anderem <strong>Klar</strong>, <strong>Verspielt</strong> und <strong>Erdig</strong>. Die Templates ändern die optische Stimmung der Oberfläche, nicht die musikalischen Daten.</p>
      </section>
