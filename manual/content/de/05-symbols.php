<?php

if (!defined("BARABEAT_MANUAL_RENDER")) {
    http_response_code(404);
    exit;
}

?>      <section id="symbols">
        <h2>5. Zeichen und Sonderzeichen</h2>
        <p>Die wichtigsten Zeichen entsprechen der Legende im Notenblatt.</p>

        <table>
          <thead>
            <tr>
              <th>Zeichen</th>
              <th>Bedeutung</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Tone</td>
              <td>Offener Djembe-Ton.</td>
            </tr>
            <tr>
              <td>Bass</td>
              <td>Bassschlag der Djembe oder Schlag der Dununba, je nach Instrumentenspur.</td>
            </tr>
            <tr>
              <td>Slap / Glocke</td>
              <td>Slap bei Djembe, Glocke bei Kenkeni, Sangban, Dununba oder Ballet Dununs.</td>
            </tr>
            <tr>
              <td>Flam</td>
              <td>Zwei eng zusammenliegende Schläge. In den laufenden Noten werden Flams überlagert dargestellt.</td>
            </tr>
            <tr>
              <td>Gedämpfter Tone / gedämpfter Slap</td>
              <td>Gedämpfte Schläge, im Notenblatt durch Unterstrich markiert.</td>
            </tr>
            <tr>
              <td>In</td>
              <td>Auftakt oder Einstiegspunkt. Der Ton unter dem In kann vor dem eigentlichen Pattern liegen.</td>
            </tr>
            <tr>
              <td>Out</td>
              <td>Ausstiegspunkt. Der Ton über dem Out wird noch gespielt, danach endet die Stimme.</td>
            </tr>
            <tr>
              <td>Wiederholung</td>
              <td>Interne Wiederholung im Pattern. Die Zahl am Zeichen gibt die Wiederholungsanzahl an.</td>
            </tr>
            <tr>
              <td>ShortBar</td>
              <td>Verkürzt einen Takt, ohne ihn optisch schmaler darzustellen. Die weggelassenen Einheiten werden gestrichelt markiert.</td>
            </tr>
            <tr>
              <td>Triole / Quartole</td>
              <td>Zusatzzeichen für gleichmäßig unterteilte Schläge. In binären Notenblättern zeigt die Palette ein <strong>T</strong> für Triolen, in tenären und 9/8-Notenblättern ein <strong>Q</strong> für Quartolen.</td>
            </tr>
          </tbody>
        </table>

        <div class="warning">
          <strong>ShortBar:</strong> Bei binären Rhythmen verkürzt ShortBar die letzten vier Einheiten, bei tenären und 9/8-Rhythmen die letzten drei Einheiten. Dadurch bleibt die optische Struktur stabil, während Audio und laufende Noten die verkürzte Länge beachten.
        </div>
        <div class="hint">
          <strong>Out bei Begleitpattern:</strong> Im Arrangement kann ein Out in einer Begleitung ein bewusstes Aussteigen markieren. Im Übungsmodus wird ein Out in einem Begleitpattern normalerweise ignoriert, damit Loops durchlaufen. Es kann aber greifen, wenn die Begleitung ausdrücklich vor Call/Intro stoppen soll oder wenn ein als Übungsteil gespieltes Begleitpattern im letzten Durchlauf vor dem nächsten Call/Intro aussteigen soll.
        </div>
        <p>Das Triolen-/Quartolenzeichen wird in Palette und Legende rhythmusabhängig angezeigt. Die kompakte Darstellung besteht aus drei Punkten und einem Buchstaben: <strong>T</strong> für Triole oder <strong>Q</strong> für Quartole. Nach einem Klick auf das Zeichen wählst du für jeden Einzelschlag den gewünschten Ton aus. Das fertig zusammengesetzte Zeichen wird anschließend neben der Palette erzeugt und kann auf das Notenblatt gezogen werden.</p>
      </section>
