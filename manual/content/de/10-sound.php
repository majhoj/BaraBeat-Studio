<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="sound">
        <h2>10. Klang, Swing und Feel</h2>
        <p>BaraBeat Studio bietet mehrere Ebenen zur klanglichen Anpassung.</p>

        <h3>Instrumentenlautstärke</h3>
        <p>Die Gesamtlautstärke einzelner Instrumente kann im Übungsmodus über die laufenden Noten eingestellt werden. Dazu gehört auch die Shekere. Diese Werte werden mit dem Notenblatt gespeichert.</p>

        <h3>Tonlautstärke pro Instrument</h3>
        <p>Zusätzlich können die verschiedenen Schläge eines Instruments relativ zueinander eingestellt werden, zum Beispiel Bass, Tone, Slap, Mute oder gedämpfte Schläge. Das ist hilfreich, wenn Samples unterschiedlich laut aufgenommen wurden.</p>

        <h3>Swing</h3>
        <p>Über <strong>Swing-Profil</strong> öffnet sich ein Overlay, in dem die Verschiebung der Unterteilungen sichtbar gemacht und eingestellt werden kann. Die Notensymbole lassen sich dazu auch direkt horizontal ziehen; der maximale Versatz ist auf 50&nbsp;% des Abstands zur benachbarten Rasterposition begrenzt. Das Profil gilt gemeinsam für alle Pattern des Notenblatts; separate Swing-Werte pro Pattern gibt es nicht.</p>

        <h3>Feel</h3>
        <p>Das Feel-Overlay verschiebt einzelne Instrumente in Millisekunden nach vorne oder hinten. Die Instrumentennamen werden ausgeschrieben, damit klar ist, welche Spur angepasst wird.</p>

        <div class="hint">
          <strong>Praxis:</strong> Swing verändert die innere Verteilung innerhalb eines Pulses. Feel verschiebt ganze Instrumente minimal gegeneinander. Beides zusammen kann einen Rhythmus lebendiger machen, sollte aber vorsichtig eingesetzt werden.
        </div>
      </section>
