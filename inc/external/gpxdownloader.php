<?php
// file url
$file = $_GET['f'];

header('Content-type: application/gpx');

// filename
header('Content-Disposition: attachment; filename="'.$_GET['n'].'.gpx"');

// deliver file
readfile($file);