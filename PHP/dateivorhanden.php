<?php
$dateiname = $_POST["b"];
if (file_exists("../Noten/".$dateiname)) {
    echo "true";
} else {
    echo "false";
}

?>
