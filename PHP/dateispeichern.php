<?php
$inhalt = $_POST["a"] ?? "";
$dateiname = basename(trim($_POST["b"] ?? ""));
$dateiname = preg_replace('/\.(bbs|txt)$/i', '', $dateiname);
$dateiname = $dateiname . ".bbs";
$pfad = __DIR__ . "/../Noten/" . $dateiname;

if ($dateiname === ".bbs" || $dateiname === "..bbs") {
    http_response_code(400);
    echo "Ungültiger Dateiname";
    exit;
}

if (file_exists($pfad)) {
    http_response_code(409);
    echo "Die Datei existiert bereits und kann über diesen Endpunkt nicht überschrieben werden.";
    exit;
}

//$zeile = "Per GET wurde der Name $name übergeben \r\n";
//header("HTTP/1.0 204 No Content");
file_put_contents($pfad, $inhalt);
echo "&nbsp&nbsp".$dateiname." wurde gesichert";

//$inhalt = $_POST["inhalt"];
//$inhalt = str_replace("'", "\"", $inhalt);
//$inhalt1 = htmlspecialchars($inhalt);
//$name = $_POST["dateiname"];

?>
