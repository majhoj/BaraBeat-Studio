# Musikalische Regressionstests: Migrationsschritt 1

Die Tests verwenden die aktuellen Funktionen aus Editor, Ueben, Timeline und
Player. Browser-DOM, Audiogeraet und Download werden bei den Node-Tests ersetzt;
musikalische Berechnungen werden nicht als Test-Doubles nachgebaut.
`fixtures/timing-cases.json` (Version 1) enthaelt kleine, von den Formeln
unabhaengige Sollwerte. Der `.bbs`-Container bleibt unveraendert; Timeline-Metadaten
ab Version 11 speichern Starttakte und explizite Dauern unabhaengiger Spurbloecke.

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
| IN am Ende eines Anfangs-Wiederholungsbereichs | `sheet-quick-play-pickup`: Djaa Dununba Echauffement, Zahlen 1/2/8 ergeben 2/3/9 volle Durchlaeufe plus zwei Schlusstakte; allein, parallel und nach Call, inklusive Quellmarkierungen und aeusserem Loop; `timeline-independent-tracks`: unveraendertes Arrangement-Verhalten bei 1/2 |
| Gemeinsame Wiederholungsbereiche | `repeat-ranges`: konkrete Takt-/Notenfolgen fuer normale, benachbarte, verschachtelte und doppelte Bereiche; stabile Sortierung, Sonderwerte, unvollstaendige Eingaben, unveraenderte Quellnoten und getrennte Regeln von Ueben/Quick Play |
| Gemeinsame rohe Marker-Paarung | `repeat-pairing`: Abschlussreihenfolge ohne Sortierung/Normalisierung, feste Sollfolgen und 9.261 Alt-/Neu-Kombinationen, fertige Uebungs-/Quick-Play-Abschnitte, Quellmarkierungen, bekannte Dubletten-/Shortbar-Abweichungen; nicht endliche Zaehler ausschliesslich einlesen |
| IN, Auftakt nach Call/Intro | `musical-structure`: IN zwei Schritte vor dem neuen Takt; kein zusaetzlicher Auftakttakt |
| Mehrtaktiges Solo mit IN | `arrangement-cyclic-pickup`, `timeline-horizontal-tracks`: erster Solo-Takt wird mit dem vorherigen Flam geteilt, zwei weitere Durchlaeufe bleiben vollstaendig; drei zweitaktige Durchlaeufe belegen gemeinsam sechs Takte, auch mit stummen zweiten Takten; ohne Vorgaenger kein Abzug |
| OUT / nur letzter Durchlauf | `arrangement-out-trim`, `arrangement-cyclic-pickup`, `sheet-quick-play-pickup`: Noten und Laengen vor/nach OUT, Schlussdurchlauf |
| Zyklisches IN hinter OUT | `arrangement-cyclic-pickup`, `sheet-quick-play-pickup`: vollstaendige Pattern, letzter OUT, Variation 2, Echauffement |
| IN in internem Wiederholungsabschnitt | `internal-repeat-pickup`: Soli nach Okas, sechs notierte / zehn gespielte Sangban-Takte; IN von Takt 6 am Ende von Takt 5, gleiche Audionoten in allen Modi, passende rote Markierung und laufende Noten, unveraenderte Quelldaten |
| Internes IN ohne Wiederholung | `internal-repeat-pickup`: aktualisierte Okas-Variation 2 nur mit IN; Uebernahme in den Vortakt auch ohne Wiederholungszeichen, nur letzter Durchlauf des Vortakts veraendert, passende rote Markierung, vier aeussere Loops; erstes Pattern-IN und Auftakte ganzer Wiederholungsgruppen bleiben unveraendert |
| Sofort-Spielen: rote Noten synchron | `quick-play-highlight-timing`: absolute Audiozeit und Geraete-Ausgabezeit statt reinem Browser-Timer; wartende Ausgabe, verspaetete Meldungen, Desktop/Mobil, IN-Quellnote, Stopp, alte Player und begrenzte Timerliste |
| OUT am Ende einer Begleitsequenz | `internal-repeat-pickup`: Sofort-Spielen beachtet OUT im letzten Durchlauf eines separat wiederholten Schlussabschnitts; Taktende, aeusserer Loop, laengere Parallelbegleitung und expliziter Leertakt bleiben erhalten; normale Begleitloops bleiben unveraendert |
| Verkuerzte Takte | `musical-structure`: genau ein Beat, Note auf der Verkuerzungsgrenze entfaellt |
| Ueberlappungen | `overlap-control`, `sheet-quick-play-pickup`, `timeline-horizontal-tracks`: Uebergaben, Taktzahl, Schlussnote, begrenzte Sichtkopien; eintaktiger Flam teilt Takt 12 mit Solo 2 (dreimal zwei Takte bis Takt 17), beide Bloecke bleiben sichtbar; letzter Flam ohne Nachfolger behaelt seinen Takt |
| Flam, Triolen | `musical-audio`: zwei Schlaege mit Abstand 5/BPM; drei gleichmaessige Schlaege pro Beat, Live und Export |
| Parallele Instrumente | `musical-structure`, `timeline-accompaniment-lanes`: ein- und zweitaktige Begleitung zusammen, kurze Spur wiederholt sich |
| Ballet Dununs in der Timeline | `timeline-three-bass`: eigene sichtbare Dreierbass-Spur, Einsetzen per Taktleiste, Begleitsegment verschieben/verlaengern, Speichern/Neuladen und Player-Uebergabe; separate Bassspuren bleiben unveraendert |
| Taktgenaue Calls | `timeline-positioned-call`: Call in Takt 6 innerhalb zweitaktiger Basswiederholungen; Hinzufuegen/Verschieben, unveraenderte Begleitung, einzelne Durchlaeufe, mehrere Calls, Undo-Snapshot, Speichern/Neuladen und IN/OUT; IN des direkt folgenden Solos im Call-Takt, einschliesslich Pausen und separatem Auftakttakt, ohne verfruehten Auftakt in der Bassspur |
| Unabhaengige Instrumentenspuren | `timeline-independent-tracks`: Migration, Speichern/Neuladen, Begleitung verlaengern ohne andere Instrumentenspuren zu verschieben, Solo mit allen Wiederholungen verschieben, Kollisionen, Undo, IN/OUT, verkuerzte Takte und Ueberlappungen; Solo 5 dreimal belegt sechs eigene Takte auch bei einem spaeter platzierten Solo 6, Schlussnote bleibt im Uebergang erhalten; ohne Nachfolger bleibt der klingende Schlusstakt erhalten |
| Freie Djembe-Spuren beim Einfuegen | `timeline-free-djembe-targets`: generisches Djembe-Pattern trotz belegter Standardspur einfuegen; gleiche Entscheidung in Vorschau und Drop, alle freien Stimmen, eigener Block, unveraenderte Originale/Quelldaten, vollstaendige Dauer inklusive Wiederholungen/Spursegmenten, Undo, Speichern/Neuladen und Player-Noten; keine Umbelegung expliziter oder verschobener Pattern |
| Nachruecken innerhalb einer Spur | `timeline-track-ripple`: Verlaengern, Verkuerzen und Verschieben bewegen die nachfolgenden Bloecke nur auf derselben Instrumentenspur; Abstaende, gemeinsam genutzte IN-/Ueberlappungstakte, Schluss-OUT, Wiederholungen und explizite Begleitsegmente bleiben erhalten; Mehrfachzuordnungen werden bei Bedarf spurweise getrennt; atomisches Undo, Speichern/Neuladen, unveraenderte Vorschau und Ablehnung unzulaessiger Kollisionen/Taktgrenzen |
| Einzelne/unfertige Takte | `quick-play-drafts`: Instrument + Note ohne Funktion spielbar; nur Instrument oder Note ohne Instrument nicht spielbar |
| Start im Arrangement | `musical-structure`, `timeline-accompaniment-lanes`: Starttakt inklusive Auftakt, Start-/Endgrenzen |
| Leere Auswahl | `musical-structure`: Quick Play liefert null, Ueben verlangt Auswahl |
| Einzaehlen | `musical-timing`: vier Schlaege bei 0,18 / 0,93 / 1,68 / 2,43 s; Auftakt ab 2,93 s bei BPM 80 |
| Swing und Auftaktphase | `musical-timing`: exakte Intervalle ueber ganze Takte in allen drei Modi; Shekere bleibt auf Eins |
| Sofort-Spielen ab internem IN | `sheet-quick-play-pickup`: Djaa Solo 2, IN an Schritt 8; Shekere bei Wiedergabeschritt 4/10 statt 0/6, korrekte Swingphase ueber vier Loops, unveraenderte Noten/Markierungen/Laenge und kein Versatz bei folgendem Call oder Parallelbegleitung; `musical-audio`: gleiche Phase im WAV fuer alle drei Raster |
| Tempoaufbau | `musical-structure`: 60,60,70,70,75 BPM; `musical-timing`: Abschnittsinterpolation 60/90/120 BPM und Schrittzeiten |
| Ballet Dununs | `musical-audio`: hoechstens zwei klingende Schlaege pro Position, alle sechs Eingabereihenfolgen der drei offenen Trommeln und gedaempfte Kombination |
| WAV-Zeitpunkte und Dauer | `musical-audio`: echter Export-Schleifenaufruf, alle drei Raster mit/ohne Quick-Play-Auftakt; bestehender 3,5-s-Ausklang |
| WAV-Pegelabsicherung | `wav-levels`: gemeinsamer Faktor 0.98/Peak nur oberhalb 0.98, keine Anhebung, unveraenderte PCM-Positionen/Stereo-Balance/Quelldaten, Schwellenwerte und spaeter Peak in langem Buffer; `--browser`: 19 echte Sample-Konstellationen und vollstaendiges Djaa-Arrangement |
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

## Migrationsschritt 2a: Wiederholungsbereiche

`JS/repeat-pickup.js` enthaelt jetzt auch `normalizeMarkerList`, `normalizeRanges`
und `buildRepeatRanges`, unveraendert aus der bisherigen Player-Logik uebernommen.
Die bisherigen Player-/Timeline-Funktionsnamen delegieren an dieses Modul.
Auftakthelfer, Wiederholungsexpansion, IN/OUT und Abschnittssteuerung bleiben
unveraendert; Ueben und Quick Play behalten ihre eigenen Bereichsleser.

`check-repeat-ranges.mjs` prueft unter anderem `ABABAB` bei zwei zusaetzlichen
Wiederholungen, `ABABCDCDCD` bei benachbarten Bereichen und `ABCBCDABCBCD` bei
verschachtelten Bereichen. Doppelte Bereiche werden nur in Player/Timeline
entfernt; gleiche Grenzen mit unterschiedlichen Zaehlern behalten ihre
Reihenfolge. `loop` bleibt dort symbolisch und ergibt in der bestehenden
Expansion einen zusaetzlichen Durchlauf. Bruchzahlen werden nicht gerundet;
ungueltige Zaehler werden weiterhin verworfen. Abweichende Regeln von Ueben
und Quick Play sind durch eigene Sollwerte abgesichert, nicht vereinheitlicht.
Eingaben werden eingefroren; Quellnoten und ihre Markierungszuordnungen bleiben
auch nach der Expansion unveraendert.

Editor, App-Shell und Player laden das vorhandene Modul vor den Verbrauchern.
Fuenf Timeline-Testkontexte laden es jetzt ebenfalls, ohne ihre bisherigen
Sollwerte zu aendern. `check-timing-offline.mjs` prueft zusaetzlich die Entfernung
des alten v90-Caches bei Aktivierung sowie die Ausfuehrung der neuen
Weiterleitungen mit dem tatsaechlich gecachten Modul bei abgeschaltetem Netz.
Fuer Schritt 2a wurde Cacheversion v91 eingefuehrt; die Cache-Strategie blieb unveraendert.
`app-shell.html` wird mit `php scripts/build-app-shell.php` neu erzeugt.

Beim Hochladen Modul, Timeline, Player, App-Shell und Service Worker gemeinsam
aktualisieren. Vor dem Offline-Test die Online-Aktualisierung vollstaendig
abschliessen und die App neu laden. Danach in Safari/Mac-Web-App und auf dem
iPhone Wiederholungen, IN/OUT, Ueberlappungen, Markierungen und den Einstieg
mitten im Arrangement hoeren/pruefen; auch WAV mit aktivierter Shekere testen.

## Nachtest: Sofort-Spielen ab IN innerhalb eines Takts

Bei Djaa Solo 2 wird der Beginn bis zum IN (Schritt 8) beim Sofort-Spielen
abgeschnitten. Der Player behandelte diesen Einstieg bisher als Beat-Eins.
Die nur zur Laufzeit uebergebene Abschnittseigenschaft `rhythmStepOffset`
bewahrt jetzt dessen urspruengliche Position fuer Shekere und Swing. Noten,
Hervorhebungen, Looplaenge, nachfolgende Calls und gespeicherte Daten bleiben
unveraendert. Diese Korrektur ist unabhaengig von der Bereichsermittlung.
Die ergaenzten Quick-Play-/WAV-Tests sichern den Fall ab; der Service Worker
verwendet dafuer v92, und die App-Shell wurde erneut generiert.

## Nachtest: Dununba Echauffement mit Anfangswiederholung

Das IN am Ende der ersten vier wiederholten Takte wurde beim Sofort-Spielen
als Beginn des gesamten Patterns behandelt. Dadurch fehlte der erste
Durchlauf trotz korrekt ausgelesener Wiederholungszahl. Liegt vor diesem IN
bereits Musik und endet dort der am Patternanfang beginnende
Wiederholungsbereich, wird das IN jetzt als zyklischer Auftakt behandelt.
Die vollstaendige expandierte Folge bleibt erhalten. Ein echter Auftakt
ohne vorherige Noten und IN ausserhalb solcher Bereiche bleiben unveraendert.

Mit einer 1 am Zeichen sind es zweimal vier Takte plus zwei Schlusstakte,
mit einer 2 dreimal vier Takte plus zwei Schlusstakte. Vorab erklingt das IN
einmal als Auftakt bzw. im vorherigen Call. Die Browserpruefung verwendet
die aktuelle Datei aus `Noten/`; manuell noch in Safari/Web-App gegenhoeren.
Cacheversion v93 und die neu erzeugte App-Shell liefern die Korrektur offline.

## WAV-Export: bedarfsabhaengige Pegelanpassung

Nur `encodeWavFromAudioBuffer` wurde fachlich geaendert. Nach dem vollstaendigen
Offline-Rendering liest der Encoder alle Kanaele einmal als Referenzen und
ermittelt den groessten absoluten Samplewert. Bei Peak > 0.98 wird ausschliesslich
beim Schreiben der PCM-Werte der gemeinsame Faktor `0.98 / peak` angewendet.
Andernfalls bleibt der Faktor 1. Keine Anhebung, Kompression oder neue Master-
Lautstaerke; die Float-Quelldaten bleiben unveraendert. Die bisherige PCM-Grenz-
pruefung bleibt als defensive Absicherung stehen, greift bei endlichen,
pegelkorrigierten Werten aber nicht mehr ein.

Der zusaetzliche Scan ist linear. Ausser den Kanalreferenzen und wenigen Zahlen
wird kein zusaetzlicher samplezahlabhaengiger Speicher benoetigt. WAV-ArrayBuffer
und Blob bleiben wie zuvor; es entsteht keine zweite Float-AudioBuffer-Kopie.

Numerische Tests ohne Browser:

```sh
node scripts/check-wav-levels.mjs
```

Mit laufendem PHP-Testserver und einer vorhandenen Playwright-/Chromium-Installation:

```sh
node scripts/check-wav-levels.mjs --browser
```

Optional: `BARABEAT_TEST_URL` fuer die lokale Testserver-URL und
`BARABEAT_PLAYWRIGHT_MODULE` fuer einen extern installierten Playwright-Paketpfad.
Der Browser-Test verwendet nur einen frischen Testbrowser, oeffnet die lokale
App-Shell und laedt `Noten/Djaa Djembe, Sangban, Dundunba.bbs` ohne Speicherung.
Die Helfer in `helpers/wav-level-checks.mjs` rendern echte Samples im Player und
pruefen jeden PCM-Wert gegen den gemeinsamen Sollfaktor, die Laenge, Kanalordnung,
Quell-Fingerprints und das Ausbleiben hart abgeschnittener Samples. Der echte
Arrangement-Export wird einschliesslich Kodierung ausgefuehrt; nur der Download
wird abgefangen. Die Messergebnisse werden ausgegeben, keine WAV-Datei geschrieben.

Der Service Worker verwendet Cacheversion v94. `timing-offline` prueft explizit
die Entfernung des alten v93-Player-Caches und fuehrt den neuen WAV-Encoder aus
der offline gelieferten Player-Datei aus. Cache-Strategie und Ausschluesse fuer
PHP, Sessions, CSRF und Benutzerdaten bleiben unveraendert.

Messung am 18.10.2026 mit lokalem Headless-Chromium, 48 kHz, Stereo:

| Fall | Peak vorher | Gemeinsamer Faktor | Absenkung | Hart abgeschnitten nachher |
| --- | ---: | ---: | ---: | ---: |
| Djembe Tone | 1.322711 | 0.740903 | -2.60 dB | 0 |
| Djembe Slap-Flam | 2.123862 | 0.461424 | -6.72 dB | 0 |
| Drei Basstrommeln mit Glocken | 5.230559 | 0.187360 | -14.55 dB | 0 |
| Ensemble mit Shekere | 5.287499 | 0.185343 | -14.64 dB | 0 |
| Dununba Muffled | 0.771090 | 1 | 0 dB | 0 |
| Shekere einzeln | 0.400691 | 1 | 0 dB | 0 |
| Vollstaendiges Djaa-Arrangement | 7.239211 | 0.135374 | -17.37 dB | 0 |

Alle 19 Sample-Konstellationen bestanden den Vergleich jedes PCM-Samples.
Beim vollstaendigen `Djaa Djembe, Sangban, Dundunba.bbs` waren vor der Korrektur
1.013.647 von 19.728.002 Kanal-Samples ausserhalb [-1, 1], also 5.138113 %.
Danach liegt der kodierte Peak bei 0.97998047, ohne hart abgeschnittene Samples.
Die Laenge bleibt bei 9.864.001 Stereo-Frames (205.500021 Sekunden, einschliesslich
Ausklang), die WAV-Datei bei 39.456.048 Bytes. Alle Quell-Fingerprints bleiben gleich.
Die reine Pegelermittlung und Kodierung dauerte in zwei Laeufen 282 bzw. 295 ms; das ist
kein geraeteunabhaengiges Zeitversprechen und enthaelt nicht das Offline-Rendering.
Die rund 78.9 MB Float-Quelldaten werden dabei nicht dupliziert.
Der Kontrolllauf ueber `check-wav-levels.mjs --browser` bestaetigte alle Ergebnisse;
der Rohpeak unterschied sich erst in den letzten Float-Stellen und der Zaehler
vorher um ein Kanal-Sample (1.013.648). Nachher blieb der Zaehler bei null.

Die 35 `check-*.mjs`-Skripte einschliesslich HTTP-Offline-Cache-Pruefung bestanden.
Der Browser-Test ergaenzt diese um die echten Sample- und Arrangement-Renderings.
Ein separater Alt-/Neu-Vergleich bestaetigt bytegleiche WAV-Ausgabe bei
unkritischem Pegel. Die App-Shell wurde regeneriert, blieb fuer diesen Patch
aber inhaltlich identisch. Manuell noch in Safari/Mac-Web-App und auf dem iPhone
hoeren: erwartete Pegelabsenkung, erhaltene Balance und Dynamik, unveraenderter
Ablauf. Vor dem Offline-Nachtest die Online-Aktualisierung abschliessen.

## Migrationsschritt 2b: gemeinsame Marker-Paarung

`BaraBeatRepeatPickup.collectRepeatRanges(bars)` liest die Marker mit dem
bisherigen Stapelverfahren: Enden werden vor Anfaengen derselben Grenze
bearbeitet. Die rohen Bereiche behalten Abschlussreihenfolge, Zaehlerwerte und
Dubletten. Keine Zahlenumwandlung, Sortierung, Deduplizierung oder Notenaenderung.
Das vorhandene `buildRepeatRanges` verwendet diesen Kern und danach weiterhin
die unveraenderte Funktion `normalizeRanges` fuer Player und Timeline.

`buildPracticeRepeatRangesFromBars` ersetzt nur seine bisherige Marker-Paarung
durch den gemeinsamen Kern; Filterung und Zahlenumwandlung bleiben lokal.
`getPracticeRepeatMarkerList` leitet an `normalizeMarkerList` weiter.
Quick Play verwendet ausschliesslich diesen Listenhelfer mit `value || []`:
Einzelwerte 0/NaN bleiben dort abwesend, dieselben Werte in Arrays bleiben
vorhanden. Die eigene Stapellogik, `continue`, `loop`, Rundung und Reihenfolge
bleiben unangetastet. Expansion, IN/OUT, Auftakthelfer und WAV-Encoder wurden
in diesem Schritt nicht geaendert.

### Tests und Alt-/Neu-Vergleich

Vor dem Produktionspatch wurde `check-repeat-pairing.mjs` erfolgreich gegen den
bisherigen Code ausgefuehrt. `fixtures/repeat-pairing-before-2b.mjs` enthaelt
eingefrorene, ausschliesslich in Tests verwendete Kopien der vorherigen Parser.
Diese Referenz darf nicht lediglich zum Bestehen eines geaenderten Tests an
den neuen Produktionscode angepasst werden.

Die Matrix aus 21 Markerwerten an drei Positionen umfasst 9.261 deterministische
Kombinationen. Verglichen werden Quick Play, Ueben und Player/Timeline sowie
die Player-Normalisierung. Alle Vergleiche sind ohne Abweichung. Feste Sollwerte
sichern unter anderem ABABAB, ABABCDCDCD, ABCBCDABCBCD, rohe Abschlussreihenfolge,
Einzelwerte/Arrays, falsche Zaehler und unvollstaendige Eingaben ab.

Weitere Tests vergleichen vollstaendige vorbereitete Abschnitte mit den alten
Parsern: Gesamtpattern-Wiederholungen, Call/Intro mit IN und Schluss-OUT, ungleich
lange parallele Begleitungen und leere Folgen. Quellnotenmarkierungen,
Stummschaltungsmarkierungen des Schlussdurchlaufs und unveraenderte Quelldaten
haben konkrete Sollwerte. Der neue rohe Kern wird auch mit einem Zaehlerobjekt
getestet, das bei Zahlenumwandlung absichtlich einen Fehler ausloesen wuerde.
Alle 36 `check-*.mjs`-Skripte einschliesslich HTTP-Offline-Test sind erfolgreich;
vorhandene Sollwerte wurden nicht geaendert.
Auch `check-wav-levels.mjs --browser` wurde erneut erfolgreich ausgefuehrt:
19 echte Sample-Konstellationen und das vollstaendige Djaa-Arrangement mit
2.424 Schritten / 9.864.001 Stereo-Frames. Rohpeak 7.239212, Faktor 0.135374,
kodierter Peak 0.979980, null hart abgeschnittene Samples. Der WAV-Encoder und
`Audio/player.html` sind gegenueber dem Stand vor Schritt 2b bytegleich.

### Bewusst erhaltene Abweichungen und Risiken

- Zwei identische Gesamtpattern-Bereiche mit Zaehler 2 und ohne IN ergeben bei
  Quick Play neun Patterndurchlaeufe, bei Ueben drei. Grund: Quick Play
  multipliziert die Abschnittszaehler in `buildSheetQuickPlayPreparedPattern`.
- Ein Call mit 24 Schritten und `shortbar` an Schritt 6 bleibt in Quick Play
  24 Schritte lang; Ueben und Player verwenden sechs. Dies liegt in der
  Noten-/Abschnittsaufbereitung, nicht in der Marker-Paarung.
- `buildSheetQuickPlayRepeatRanges` in `index.php` akzeptiert Infinity durch
  `Math.max(1, Math.round(Number(endMarker) || 1))`. Bei Weitergabe an die
  Expansion droht eine unbeschraenkte Wiederholung bzw. Speichererschoepfung.
  Tests lesen solche Werte nur ein; sie expandieren sie niemals.
- `loop`, `continue`, Bruchzahlen, ungueltige Werte, Dubletten und fehlende
  Taktobjekte behalten die dokumentierten Unterschiede zwischen den Modi.

Diese Punkte werden nicht mit der Zentralisierung korrigiert. Jede fachliche
Aenderung benoetigt einen getrennten Auftrag und begruendete neue Sollwerte.

### Offline und manuelle Nachtests

Cacheversion: `barabeat-studio-offline-v95-repeat-pairing`.
Die App-Shell wurde aus `index.php` neu erzeugt und enthaelt die aktualisierten
Dateiversionen. Die synchrone Ladefolge bleibt Timing, Repeat-Pickup, Timeline,
Practice und anschliessend Editor; der Audioplayer laedt das gemeinsame Modul
ebenfalls vor seinen Verbrauchern. Keine neuen Script-Dateien oder Ladepfade.

`check-timing-offline.mjs` entfernt im simulierten Update den v94-Cache ohne neue
API und prueft anschliessend den tatsaechlich gecachten Modul-, Practice-,
Timeline-, Player- und Shell-Code offline. Auch die unterschiedliche Behandlung
von Dubletten und die unveraenderte WAV-Pegelabsicherung werden dort ausgefuehrt.
Cache-Strategie und Ausschluesse fuer PHP, Sessions, CSRF und Benutzerdaten sind
unveraendert.

Beim Hochladen `JS/repeat-pickup.js`, `JS/practice.js`, `index.php`,
`app-shell.html` und `service-worker.js` gemeinsam aktualisieren. Online-Update
abschliessen und neu laden, erst dann offline testen. In Safari/Mac-Web-App und
auf dem iPhone Djaa Solo 2, Dununba Echauffement, Okas-Sangban, Call/Intro mit
Auftakt, parallele Begleitungen, Uebungswiederholungen und Tempoaufbau gegenhoeren.
Rote Quellnoten, laufende Noten, Arrangement und WAV-Export sollen sich genauso
verhalten wie vor diesem Schritt.

## Offline-Sofortspielen: veraltete ausgelieferte App-Shell

Am 21.10.2026 waren der oeffentliche Service Worker (v95) und Repeat-Pickup
aktuell, aber `app-shell.html` enthielt noch die alte zyklische IN-Erkennung.
Mit dieser ausgelieferten Shell wurde der Fehler in einem isolierten echten
Offline-Cache reproduziert: Djaa Dununba Echauffement verlor den ersten
Vier-Takt-Durchlauf trotz Wiederholungszahl 1 (146 statt 242 Schritte inklusive
zweischrittigem IN). Die aktuelle Shell liefert online und nach Offline-Kaltstart
identische Player-Daten und Quellmarkierungen: 51,52,53,54,51,52,53,54,55,56.

Nur die Cacheversion wurde auf `barabeat-studio-offline-v96-quick-play-shell`
erhoeht; musikalische Regeln und Cache-Strategie bleiben unveraendert.
`app-shell.html` wurde erneut generiert und war lokal bereits inhaltlich aktuell.
`check-timing-offline.mjs` prueft jetzt auch das Entfernen des v95-Caches und
die Gleichheit der gesamten Quick-Play-Aufbereitung in Editor und Offline-Shell.
Mit `node scripts/check-sheet-quick-play-pickup.mjs --offline-shell` laeuft die
bestehende musikalische Testsuite direkt gegen die generierte Offline-Seite.
Alle 36 Regressionstests einschliesslich HTTP-Test und der zusaetzliche
Offline-Shell-Durchlauf sind erfolgreich; kein manueller Hoertest automatisiert.

Deployment: zuerst die aktuelle `app-shell.html`, danach `service-worker.js`
hochladen. Nur die neue Cacheversion zusammen mit der alten Shell wuerde den
Fehler erneut cachen. Anschliessend BaraBeat online laden, Offline-Vorbereitung
abschliessen lassen, App vollstaendig schliessen und offline erneut starten.
Die oeffentlichen Dateien wurden bei dieser Diagnose nur gelesen, nicht geaendert.

## Arrangement: freie Djembe-Spuren beim Einfuegen

`resolveTimelineNewEntryTargets` belaesst kollisionsfreie Bibliothekszuordnungen
unveraendert. Ist die Standardspur belegt, darf ein einzelnes generisches
`Djembe`-Pattern auf alle ueber seine gesamte Dauer freien Djembe-Spuren
ausweichen. Wiederholte Eintraege desselben Patterns bleiben ein eigener Block.
Explizite Djembe-Nummern, gemischte Pattern-Gruppen, Bassinstrumente und das
Verschieben vorhandener Bloecke behalten ihre bisherigen Kollisionsregeln.
Vorschau, Taktleisten-Drop und paralleles Einfuegen nutzen dieselbe Pruefung.

Alle 37 `check-*.mjs`-Tests einschliesslich HTTP-Pruefung bestehen. Zusaetzlich
wurde die aktualisierte `Kuku Solo.bbs` vom 23.10.2026 im Browser getestet:
nativer Drag von Begleitpattern 3 auf Takt 41 selektiert Djembe 1 und 2, waehrend
alle 111 vorhandenen Eintraege unveraendert bleiben. Djembe 2 kann abgewaehlt,
der neue Block bis Takt 51 verlaengert werden. Die anderen Instrumentenspuren
behalten Position und Dauer; das bisherige Nachruecken auf der bearbeiteten
Spur bleibt erhalten. Derselbe Drag funktioniert nach echtem Offline-Kaltstart.
Die BBS-Datei selbst wurde nicht veraendert.

Offline-Cache: `barabeat-studio-offline-v97-free-djembe-targets`. Beim Deployment
`JS/timeline.js` und die neu erzeugte `app-shell.html` gemeinsam hochladen,
danach `service-worker.js`. Cache-Strategie und Ausschluesse bleiben unveraendert.
