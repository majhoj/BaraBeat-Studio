<?php
require_once __DIR__ . '/access_control.php';
barabeat_require_access('text');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Öffnet ein Unterverzeichnis mit dem Namen "daten"
$verzeichnis = openDir(__DIR__ . "/../Noten");

// In Array einlesen und sortieren

while($file=readdir($verzeichnis)){
$dat_array[] = $file;
}
sort($dat_array, SORT_NATURAL | SORT_FLAG_CASE);



// Verzeichnis lesen
echo '<form name="dateiauswahl">';
//echo '<label id="professorLabel" for="professor"></label>';
echo '<select id="dateiname" onchange="get_value(this)">';
echo '<option value="--">' . htmlspecialchars(barabeat_t('file.dialog.load'), ENT_QUOTES, 'UTF-8') . '</option>';
$i = 0;



foreach($dat_array as $file) {
//while ($file = readDir($verzeichnis)) {
    $i++;
    $flag = "false";
    if(substr( $file, 0, 1 ) === "."){
      $flag = "true";
    }

     // Höhere Verzeichnisse nicht anzeigen!
     if ($file != "." && $file != ".." && $file != ".DS_Store" && $flag == "false" && preg_match('/\.(bbs|txt)$/i', $file)) {
     // Link erstellen
     //echo "<a href=\"Noten/$file\">$file</a><br>\n";

  $pfad = __DIR__ . "/../Noten/" . $file;
  $geaendert = is_file($pfad) ? filemtime($pfad) : 0;
  $geaendertIso = $geaendert > 0 ? date(DATE_ATOM, $geaendert) : "";

  echo  '<option value="' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '"' .
        ' data-modified="' . htmlspecialchars($geaendertIso, ENT_QUOTES, 'UTF-8') . '"' .
        ' data-modified-ts="' . htmlspecialchars((string)$geaendert, ENT_QUOTES, 'UTF-8') . '">' .
        htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</option>';

 }
}
echo '</select>';
echo '</form>';
 // Verzeichnis schließen
closeDir($verzeichnis);
?>
