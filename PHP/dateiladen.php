<?php
$dateiname = basename($_POST["b"] ?? "");
$pfad = __DIR__ . "/../Noten/" . $dateiname;

if ($dateiname === "" || !is_file($pfad)) {
    echo "";
    exit;
}

einlesen($pfad);
function einlesen($arg1)
{
    $zitate = file_get_contents($arg1);
    echo $zitate;
}
?>
