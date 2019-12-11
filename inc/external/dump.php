<?php 

if (isset($_GET['export']) && $_GET['export'] == 'dump'){
  $results = $wpdb->get_results("SELECT urn, operator, filter_title, filter_group FROM ".$wpdb->prefix."pivot_filter WHERE page_id = ".$_GET['page_id']." ORDER BY filter_title ASC");

  // Process report request
  if(!$results){
    $error = $wpdb->print_error();
    die("The following error was found: $error");
  }else{
    // Prepare our csv download

    // Set header row values
    $output_filename = 'filters_export_'.date("Y-m-d").'.csv';
    $output_handle = @fopen('php://output', 'w');

    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-type:application/csv;charset=UTF-8');
    header('Content-Disposition: attachment; filename=' . $output_filename);
    header('Expires: 0');
    header('Pragma: public');	

    $first = true;
    // Parse results to csv format
    foreach ($results as $row) {
      // Add table headers
      if($first){
        $titles = array();
        foreach($row as $key=>$val){
          $titles[] = $key;
        }
        fputcsv($output_handle, $titles);
        $first = false;
      }

      $leadArray = (array) $row; // Cast the Object to an array
      // Add row to file
      fputcsv( $output_handle, $leadArray );
    }

    // Close output file stream
    fclose( $output_handle ); 

    die();
  }
}