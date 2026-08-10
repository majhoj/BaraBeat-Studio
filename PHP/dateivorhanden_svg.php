<?php
require_once __DIR__ . '/access_control.php';
barabeat_require_access('text');

$dateiname = basename($_POST["b"] ?? "");
if ($dateiname !== "" && file_exists(__DIR__ . "/../Noten/SVG/" . $dateiname)) {
    echo "true";
} else {
    echo "false";
}

?>
