<?php
$dateiname = basename($_POST["b"] ?? "");
if ($dateiname !== "" && file_exists(__DIR__ . "/../Noten/" . $dateiname)) {
    echo "true";
} else {
    echo "false";
}

?>
