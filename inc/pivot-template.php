<?php

add_filter('template_include', 'pivot_template_include');

/**
 * 
 * @global Object $wp_query
 * @param type $template
 * @return type
 */
function pivot_template_include($template) {
  // Get wp_query
  global $wp_query;
  // Init var to nothing as we can check if it's empty or not
  $new_template = '';

  if(isset($wp_query->query['pagename'])){
    $query_page = $wp_query->query['pagename'];
  }else{
    if(isset($wp_query->query['name'])){
      $query_page = $wp_query->query['name'];
    }
  }
  
  if(isset($query_page)){
    $pivot_page = pivot_get_page_path($query_page);

    /*
     * Basic case
     * = we are listing offers
     */
    if(isset($pivot_page->path) && $query_page == $pivot_page->path){
      // Search template file in plugins folder depending on query type
      $new_template = pivot_locate_template('pivot-'.$pivot_page->type.'-list-template.php');
    }
  }
  
  /* 
   * Paged case
   * = we are listing offers but url contains parameter for page number
   */
  if($new_template == ''){
    reset($wp_query->query_vars);
    $path = key($wp_query->query_vars);

    if($path == 'details'){
      if(($pos = strpos($_SERVER['REQUEST_URI'], "&type=")) !== FALSE){ 
        $pivot_page = new stdClass();
        // Get number after paged= (position of first letter + length of "paged="
        $type_id = substr($_SERVER['REQUEST_URI'], $pos+strlen("&type="));
        $type = pivot_get_offer_type($type_id);
        $pivot_page->type = $type->parent;
        $pivot_page->path = 'details';
      }
    }else{
      $pivot_page = pivot_get_page_path($path);
    }
  }
  if($new_template == ''){
    if(isset($pivot_page->path) && strpos($wp_query->query[$pivot_page->path], 'paged') !== false){
      $current_page = pivot_get_current_page();
      $wp_query->set('paged', $current_page);
      // Search template file in plugins folder depending on query type
      $new_template = pivot_locate_template('pivot-'.$pivot_page->type.'-list-template.php');
    }
  }
  
  /*
   * Details case
   * = we want to show an entire offer in its single page
   */
  if($new_template == ''){
    if(isset($pivot_page->type)){
      $new_template = pivot_locate_template('pivot-'.$pivot_page->type.'-details-template.php');
    }
  }

  if(isset($pivot_page->map)){
    $_SESSION['pivot'][$pivot_page->id]['map'] = $pivot_page->map;
  }
  if(isset($pivot_page->path) && $pivot_page->path != 'details'){
    $_SESSION['pivot'][$pivot_page->id]['path'] = $pivot_page->path;
  }
  if(isset($pivot_page->query)){
    $_SESSION['pivot'][$pivot_page->id]['query'] = $pivot_page->query;
  }
  if(isset($pivot_page->title)){
    $_SESSION['pivot'][$pivot_page->id]['page_title'] = $pivot_page->title;
  }

  if ($new_template != '') {
    return $new_template;
  } else {
    // This is not a virtualpage, so return initial template
    return $template;
  }
}

/**
 * Locate template.
 *
 * @param string $template_name	Template to load.
 * @param string $template_path	Path to templates.
 * @param string $default_path Default path to template files.
 * @return string	Path to the template file.
 */
function pivot_locate_template($template_name, $template_path = '', $default_path = '') {
	// Set variable to search in pivot-plugin-templates folder of theme.
	if(!$template_path){
		$template_path = 'pivot/';
  }
	// Set default plugin templates path.
	if(!$default_path){
    // Path to the template folder
		$default_path = plugin_dir_path(__DIR__) . 'templates/'; 
  }
	// Search template file in theme folder.
	$template = locate_template(array($template_path.$template_name, $template_name));
    
	// Get plugins template file.
	if(!$template){
		$template = $default_path.$template_name;
  }
  
  // Ensure the file exists
  if(!file_exists($template)){
    $text = __('The required template', 'pivot' );
    $text .= ' '.$template_name.' ';
    $text .= __('was not found!', 'pivot');
    print _show_warning($text, 'warning');
    return '';
  }
  
	return apply_filters('pivot_locate_template', $template, $template_name, $template_path, $default_path);
}

/**
 * Simple Templating function
 *
 * @param $name   - Name of the template file.
 * @param $args   - Associative array of variables to pass to the template file.
 * @return string - Output of the template file. Likely HTML.
 */
function pivot_template($name, $args){
  // Search template file in theme folder.
	$template = locate_template($name.'.php');
	// Get plugins template file.
	if(!$template){
    $template = MY_PLUGIN_PATH. 'templates/' . $name . '.php';
  }

  // Ensure the file exists
  if(!file_exists($template)){
    $text = __('The required template', 'pivot' );
    $text .= ' '.$name.' ';
    $text .= __('was not found!', 'pivot');
    print _show_warning($text, 'warning');
    return '';
  }

  // buffer the output (including the file is "output")
  ob_start();
  include $template;

  return ob_get_clean();
}


/**
 * Add redirects to point desired virtual page paths to the new 
 * index.php?virtualpage=name destination.
 *
 * After this code is updated, the permalink settings in the administration
 * interface must be saved before they will take effect. This can be done 
 * programmatically as well, using flush_rewrite_rules() triggered on theme
 * or plugin install, update, or removal.
 */
function pivot_add_rewrite_rules() {
  $pages = pivot_get_pages();
  $pages[] = (object) array('path' => 'details');

  foreach($pages as $pivot_page){
    add_rewrite_tag('%'.$pivot_page->path.'%', '([^&]+)');

    add_rewrite_rule(
      '^'.$pivot_page->path.'/([^/]*)/?',
      'index.php?'.$pivot_page->path.'=$matches[1]',
      'top'
    );
  }
}
add_action('init', 'pivot_add_rewrite_rules');
 
/*
 * Function to get active page number used for pagination
 * @return int current page number for pagination
 */
function pivot_get_current_page(){
  // Check position of "paged=" in current uri
  if(($pos = strpos($_SERVER['REQUEST_URI'], "paged=")) !== FALSE){
    // Get number after paged= (position of first letter + length of "paged="
    $current_page = substr($_SERVER['REQUEST_URI'], $pos+strlen("paged=")); 
    //$current_page = (int) filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_NUMBER_INT);
  }else{
    $current_page = 0;
  }

  return $current_page;
}