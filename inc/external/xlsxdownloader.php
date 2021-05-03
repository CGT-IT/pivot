<?php
// file url
$file = $_GET['f'];

if (!fopen($file,'r')) exit('File/URL not accessible'); 
else fclose($file);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Transfer-Encoding: Binary"); 
header('Content-Disposition: attachment; filename="'.$_GET['n'].'.xls"');
ob_clean();
readfile($file);
exit();