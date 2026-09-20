# Musikalische Regressionstests: Migrationsschritt 1

Die Tests verwenden die aktuellen Funktionen aus Editor, Ueben, Timeline und
Player. Browser-DOM, Audiogeraet und Download werden bei den Node-Tests ersetzt;
musikalische Berechnungen werden nicht als Test-Doubles nachgebaut.
`fixtures/timing-cases.json` (Version 1) enthaelt kleine, von den Formeln
unabhaengige Sollwerte. Das `.bbs`-Format bleibt unveraendert.

## Ausfuehren

Im Projektverzeichnis in einem Terminal den lokalen Testserver starten:

```sh
php -S 127.0.0.1:8877 -t .
```

In einem zweiten Terminal alle alten und neuen Tests ausfuehren:

```sh
for test in scripts/check-*.mjs; do
  node "$test" || exit 1
done
```

Die HTTP-Pruefung erwartet den Server auf Port 8877; alternativ:
`node scripts/check-offline-cache.mjs http://127.0.0.1:ANDERER_PORT/`.
Der Handbuchtest benoetigt PHP CLI. Keine Tests aendern Notenblaetter.

## Abdeckung

| Musikalischer Fall | Test (`check-*.mjs`) / konkretes Soll |
| --- | --- |
| Binaer, tenaer, neunaer | `musical-structure`, `musical-timing`: 32/8, 24/6, 18/6 Schritte; bei BPM 80 Taktlaenge 3 s, 3 s, 2,25 s |
| Normale Wiederholungen | `musical-structure`: zwei zusaetzliche Wiederholungen ergeben drei Durchlaeufe in Player, Ueben und Quick Play |
| IN, Auftakt nach Call/Intro | `musical-structure`: IN zwei Schritte vor dem neuen Takt; kein zusaetzlicher Auftakttakt |
| OUT / nur letzter Durchlauf | `arrangement-out-trim`, `arrangement-cyclic-pickup`, `sheet-quick-play-pickup`: Noten und Laengen vor/nach OUT, Schlussdurchlauf |
| Zyklisches IN hinter OUT | `arrangement-cyclic-pickup`, `sheet-quick-play-pickup`: vollstaendige Pattern, letzter OUT, Variation 2, Echauffement |
| Verkuerzte Takte | `musical-structure`: genau ein Beat, Note auf der Verkuerzungsgrenze entfaellt |
| Ueberlappungen | `overlap-control`, `sheet-quick-play-pickup`, `timeline-horizontal-tracks`: Uebergaben, Taktzahl, Schlussnote, begrenzte Sichtkopien |
| Flam, Triolen | `musical-audio`: zwei Schlaege mit Abstand 5/BPM; drei gleichmaessige Schlaege pro Beat, Live und Export |
| Parallele Instrumente | `musical-structure`, `timeline-accompaniment-lanes`: ein- und zweitaktige Begleitung zusammen, kurze Spur wiederholt sich |
| Einzelne/unfertige Takte | `quick-play-drafts`: Instrument + Note ohne Funktion spielbar; nur Instrument oder Note ohne Instrument nicht spielbar |
| Start im Arrangement | `musical-structure`, `timeline-accompaniment-lanes`: Starttakt inklusive Auftakt, Start-/Endgrenzen |
| Leere Auswahl | `musical-structure`: Quick Play liefert null, Ueben verlangt Auswahl |
| Einzaehlen | `musical-timing`: vier Schlaege bei 0,18 / 0,93 / 1,68 / 2,43 s; Auftakt ab 2,93 s bei BPM 80 |
| Swing und Auftaktphase | `musical-timing`: exakte Intervalle ueber ganze Takte in allen drei Modi; Shekere bleibt auf Eins |
| Tempoaufbau | `musical-structure`: 60,60,70,70,75 BPM; `musical-timing`: Abschnittsinterpolation 60/90/120 BPM und Schrittzeiten |
| Ballet Dununs | `musical-audio`: hoechstens zwei klingende Schlaege pro Position, alle sechs Eingabereihenfolgen der drei offenen Trommeln und gedaempfte Kombination |
| WAV-Zeitpunkte und Dauer | `musical-audio`: echter Export-Schleifenaufruf, alle drei Raster mit/ohne Quick-Play-Auftakt; bestehender 3,5-s-Ausklang |
| Safari-/PWA-Start | Bestehende `sheet-quick-play-start`, `standalone-audio-scheduling`, `audio-gain-node-pooling` bleiben erhalten |
| Offline | `offline-cold-start`, `timing-offline`, `offline-cache`: Shell, Timing-Modul, Player, HTTP-Assets und Cache-Ausschluesse |

## Bewusste Regel: Ballet Dununs

Ballet Dununs verwendet intern weiterhin `Dreierbass`. Eine Person spielt die
drei Basstrommeln mit zwei Haenden: maximal zwei gleichzeitig klingende Schlaege
sind ausdruecklich gewollt. Die aktuell zuerst erkannte unterstuetzte Kombination
bleibt erhalten, ein dritter Schlag ersetzt oder erweitert diese nicht.
Beispiel: `slap`, `tone`, `bass` wird zu `kenkeni_sangban` und spielt genau zwei
Samples. Dies ist kein zu korrigierender Datenverlust. Auch eine spaetere
Ereignisliste muss die musikalische Zweischlag-Grenze erhalten.

## Timingfehler: Ursache und Soll

Bei BPM 80 dauert ein Beat 0,75 s. Das ternaere Swingprofil `[0,30,10]`
setzt die drei Anker auf `0`, `0,433333...`, `0,7`, gefolgt von `1`.
Die sechs Schrittintervalle betragen damit:
`162,5 / 162,5 / 100 / 100 / 112,5 / 112,5 ms`.

Ein separater Quick-Play-Auftakt mit zwei Schritten liegt musikalisch VOR Eins:
Er verwendet die letzten beiden Intervalle, also `112,5 / 112,5 ms`.
Anschliessend beginnt der Haupttakt mit `162,5 / 162,5 ms`.
`getStepInterval()` beruecksichtigte diesen Versatz bereits; `nextNote()` begann
falsch bei Phase null und verwendete zuerst 162,5 ms. Der neue Test schlug vor
der Korrektur fehl (auch im binaeren Raster: 112,5 statt 93,75 ms).
Jetzt verwendet der Live-Scheduler dieselbe Funktion wie Dauerberechnung und
WAV-Export. Bestehende Test-Sollwerte wurden NICHT geaendert; lediglich die neue
Timing-Abhaengigkeit wird in zwei bestehenden Testkontexten mitgeladen.

## Bewusst nicht zusammengefuehrt

IN-/OUT-/Wiederholungs- und Abschnittspolitik bleibt in den bestehenden Modi.
Vorlaufprognose der laufenden Noten, tatsaechliches Audio-Einzaehlen und
Bluetooth-Korrektur bleiben getrennt: sie haben unterschiedliche Aufgaben.
`getMobileSheetStepsPerBeat()` zaehlt sichtbare Unterteilungen (4/3), nicht
Audiorasterschritte (8/6), und bleibt deshalb unveraendert.
Klangzuordnungen, Panorama und Sample-Offsets bleiben unveraendert.
Der Export hat weiterhin keinen Geraete-/Einzaehlvorlauf, aber einen Ausklang;
verglichen werden die musikalischen Zeitpunkte ab demselben Ursprung.

Die automatischen Tests ersetzen keinen Hoertest auf einem echten iPhone oder
in Safari bzw. der installierten Mac-Web-App. Bei diesem Schritt war die
Browseranbindung wegen einer fehlenden Plugin-Laufzeitdatei nicht verfuegbar.
