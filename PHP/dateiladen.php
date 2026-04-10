<?php
$ein = $_POST["b"];

einlesen($ein);
function einlesen($arg1)
    {
    $zitate = file_get_contents($arg1);
    echo $zitate;
 }
?>
