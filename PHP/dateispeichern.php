<?php
$inhalt = $_POST["a"];
$dateiname = $_POST["b"];
$dateiname = $dateiname.".txt";
//$zeile = "Per GET wurde der Name $name übergeben \r\n";
//header("HTTP/1.0 204 No Content");
//file_put_contents("../Noten/".$dateiname, $inhalt);
file_put_contents(".http://192.168.168.24:8808/BaraBeat-Studio/Noten".$dateiname, $inhalt);
echo "&nbsp&nbsp".$dateiname." wurde gesichert";

//$inhalt = $_POST["inhalt"];
//$inhalt = str_replace("'", "\"", $inhalt);
//$inhalt1 = htmlspecialchars($inhalt);
//$name = $_POST["dateiname"];

?>
