<?php

$result = mysql_query('SELECT * FROM ".$wpdb->prefix."pivot_filter ORDER BY filter_title ASC');

if (!$result) die('Couldn\'t fetch records');

$num_fields = mysql_num_fields($result);
$headers = array();

for ($i = 0; $i < $num_fields; $i++) {
  $headers[] = mysql_field_name($result , $i);
}

$fp = fopen('php://output', 'w');

if ($fp && $result) {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="export.csv"');
  header('Pragma: no-cache');
  header('Expires: 0');

  fputcsv($fp, $headers);

  while ($row = $result->fetch_array(MYSQLI_NUM)) {
    fputcsv($fp, array_values($row));
  }
  
  die;
}

