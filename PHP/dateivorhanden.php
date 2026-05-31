<?php
$dateiname = basename($_POST["b"] ?? "");
if ($dateiname !== "" && preg_match('/\.(bbs|txt)$/i', $dateiname) && file_exists(__DIR__ . "/../Noten/" . $dateiname)) {
    echo "true";
} else {
    echo "false";
}

?>
