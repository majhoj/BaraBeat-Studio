<?php


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
echo '<option value="--">Datei laden:</option>';
$i = 0;



foreach($dat_array as $file) {
//while ($file = readDir($verzeichnis)) {
    $i++;
    $flag = "false";
    if(substr( $file, 0, 1 ) === "."){
      $flag = "true";
    }

     // Höhere Verzeichnisse nicht anzeigen!
     if ($file != "." && $file != ".." && $file != ".DS_Store" && $flag == "false") {
     // Link erstellen
     //echo "<a href=\"Noten/$file\">$file</a><br>\n";

  echo  '<option value="p'.$i.'">' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</option>';

 }
}
echo '</select>';
echo '</form>';
 // Verzeichnis schließen
closeDir($verzeichnis);
?>
