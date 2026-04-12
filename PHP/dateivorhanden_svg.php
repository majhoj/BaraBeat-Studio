<?php
$dateiname = $_POST[b];
if (file_exists("Noten/SVG/".$dateiname)) {
    echo "true";
} else {
    echo "false";
}

?>
