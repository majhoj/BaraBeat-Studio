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
| IN in internem Wiederholungsabschnitt | `internal-repeat-pickup`: Soli nach Okas, sechs notierte / zehn gespielte Sangban-Takte; IN von Takt 6 am Ende von Takt 5, gleiche Audionoten in allen Modi, passende rote Markierung und laufende Noten, unveraenderte Quelldaten |
| Internes IN ohne Wiederholung | `internal-repeat-pickup`: aktualisierte Okas-Variation 2 nur mit IN; Uebernahme in den Vortakt auch ohne Wiederholungszeichen, nur letzter Durchlauf des Vortakts veraendert, passende rote Markierung, vier aeussere Loops; erstes Pattern-IN und Auftakte ganzer Wiederholungsgruppen bleiben unveraendert |
| Sofort-Spielen: rote Noten synchron | `quick-play-highlight-timing`: absolute Audiozeit und Geraete-Ausgabezeit statt reinem Browser-Timer; wartende Ausgabe, verspaetete Meldungen, Desktop/Mobil, IN-Quellnote, Stopp, alte Player und begrenzte Timerliste |
| OUT am Ende einer Begleitsequenz | `internal-repeat-pickup`: Sofort-Spielen beachtet OUT im letzten Durchlauf eines separat wiederholten Schlussabschnitts; Taktende, aeusserer Loop, laengere Parallelbegleitung und expliziter Leertakt bleiben erhalten; normale Begleitloops bleiben unveraendert |
| Verkuerzte Takte | `musical-structure`: genau ein Beat, Note auf der Verkuerzungsgrenze entfaellt |
| Ueberlappungen | `overlap-control`, `sheet-quick-play-pickup`, `timeline-horizontal-tracks`: Uebergaben, Taktzahl, Schlussnote, begrenzte Sichtkopien |
| Flam, Triolen | `musical-audio`: zwei Schlaege mit Abstand 5/BPM; drei gleichmaessige Schlaege pro Beat, Live und Export |
| Parallele Instrumente | `musical-structure`, `timeline-accompaniment-lanes`: ein- und zweitaktige Begleitung zusammen, kurze Spur wiederholt sich |
| Ballet Dununs in der Timeline | `timeline-three-bass`: eigene sichtbare Dreierbass-Spur, Einsetzen per Taktleiste, Begleitsegment verschieben/verlaengern, Speichern/Neuladen und Player-Uebergabe; separate Bassspuren bleiben unveraendert |
| Einzelne/unfertige Takte | `quick-play-drafts`: Instrument + Note ohne Funktion spielbar; nur Instrument oder Note ohne Instrument nicht spielbar |
| Start im Arrangement | `musical-structure`, `timeline-accompaniment-lanes`: Starttakt inklusive Auftakt, Start-/Endgrenzen |
| Leere Auswahl | `musical-structure`: Quick Play liefert null, Ueben verlangt Auswahl |
| Einzaehlen | `musical-timing`: vier Schlaege bei 0,18 / 0,93 / 1,68 / 2,43 s; Auftakt ab 2,93 s bei BPM 80 |
| Swing und Auftaktphase | `musical-timing`: exakte Intervalle ueber ganze Takte in allen drei Modi; Shekere bleibt auf Eins |
| Tempoaufbau | `musical-structure`: 60,60,70,70,75 BPM; `musical-timing`: Abschnittsinterpolation 60/90/120 BPM und Schrittzeiten |
| Ballet Dununs | `musical-audio`: hoechstens zwei klingende Schlaege pro Position, alle sechs Eingabereihenfolgen der drei offenen Trommeln und gedaempfte Kombination |
| WAV-Zeitpunkte und Dauer | `musical-audio`: echter Export-Schleifenaufruf, alle drei Raster mit/ohne Quick-Play-Auftakt; bestehender 3,5-s-Ausklang |
| Safari-/PWA-Start | Bestehende `sheet-quick-play-start`, `standalone-audio-scheduling`, `audio-gain-node-pooling` bleiben erhalten |
| Sofort-Spielen-Lautstaerke | `sheet-quick-play-volumes`, `sheet-quick-play-start`: eigener Mixer, 100%-Vorgaben trotz stummer Uebungsspur, Live-Aenderungen ohne Player-Neustart, Reset und unveraenderter Arrangement-Mixer |
| Offline | `offline-cold-start`, `timing-offline`, `offline-cache`: Shell, Timing-Modul, Player, HTTP-Assets und Cache-Ausschluesse |
| Ungespeicherte Aenderungen | `unsaved-score-changes`: Notenblatt, Uebungseinstellungen und Arrangement; Laden/Neu abbrechen, Browserwarnung, erfolgreiche/fehlgeschlagene Speicherung, Aenderungen waehrend Speichern, Dokumentwechsel, Offline-Shell und fuenf Sprachen |
| Datei-Tastenkuerzel | `file-keyboard-shortcuts`: Cmd+S/O verwenden bestehende Menuebefehle; Texteingaben, offene Dialoge, gehaltene Tasten, parallele Speicherversuche, Player-Fokus und Offline-Shell |
| Laufende Noten | `practice-scroller-highlights`: helle Begleitung bleibt an der roten Linie unveraendert; Uebungsteil bleibt hervorgehoben, auch bei Rollenwechsel derselben Spur; direkte Verschiebung und Mobilverhalten |

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
