<?php
$dateiname = basename($_POST["b"] ?? "");
if ($dateiname !== "" && file_exists(__DIR__ . "/../Noten/SVG/" . $dateiname)) {
    echo "true";
} else {
    echo "false";
}

?>
