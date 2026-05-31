<?php
$dateiname = basename($_POST["b"] ?? "");

if ($dateiname === "" || !preg_match('/\.(bbs|txt)$/i', $dateiname)) {
    echo "";
    exit;
}

$pfad = __DIR__ . "/../Noten/" . $dateiname;
if (!is_file($pfad)) {
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
