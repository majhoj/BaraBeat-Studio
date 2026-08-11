<?php
require_once __DIR__ . '/access_control.php';
require_once __DIR__ . '/edition_config.php';
barabeat_require_access('text');

$inhalt = $_POST["a"] ?? "";
$dateiname = basename(trim($_POST["b"] ?? ""));
$dateiname = preg_replace('/\.(bbs|txt)$/i', '', $dateiname);
$dateiname = $dateiname . ".bbs";
$pfad = __DIR__ . "/../Noten/" . $dateiname;

if ($dateiname === ".bbs" || $dateiname === "..bbs") {
    http_response_code(400);
    echo htmlspecialchars(barabeat_t('file.error.invalidFileName'), ENT_QUOTES, 'UTF-8');
    exit;
}

if (file_exists($pfad)) {
    http_response_code(409);
    echo htmlspecialchars(barabeat_t('file.error.fileAlreadyExistsLegacy'), ENT_QUOTES, 'UTF-8');
    exit;
}

//$zeile = "Per GET wurde der Name $name übergeben \r\n";
//header("HTTP/1.0 204 No Content");
file_put_contents($pfad, $inhalt);
echo htmlspecialchars(barabeat_t('file.message.savedLegacy', ['fileName' => $dateiname]), ENT_QUOTES, 'UTF-8');

//$inhalt = $_POST["inhalt"];
//$inhalt = str_replace("'", "\"", $inhalt);
//$inhalt1 = htmlspecialchars($inhalt);
//$name = $_POST["dateiname"];

?>
