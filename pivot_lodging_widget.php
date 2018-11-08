<?php

/*
Plugin Name: Pivot Lodging Widget
Description: Un plugin d'introduction pour le développement sous WordPress
Version: 0.1
Author: Maxime Degembe
License: GPL2
*/

add_action('init', 'start_session', 1);
function start_session() {
  if(!session_id()) {
    session_start();
  }
}

add_action('end_session_action', 'end_session');
function end_session() {
  session_destroy ();
}

add_action('admin_enqueue_scripts', 'add_admin_script');
function add_admin_script() {
//  wp_enqueue_script('my_custom_script_map', plugin_dir_url(__FILE__) . '/js/map.js',array('jquery'), '2.0', true);
  // Add script only in this case
  // page is "pivot-filters" and "edit" is set to true
  if(isset($_GET['page']) && $_GET['page'] === "pivot-filters" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . '/js/filters.js',array('jquery'), '1.5', true);
  }
  
  if(isset($_GET['page']) && $_GET['page'] === "pivot-pages" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_pages_script', plugin_dir_url(__FILE__) . '/js/pages.js',array('jquery'), '1.0', true);
  }
  
  if(isset($_GET['page']) && $_GET['page'] === "pivot-offer-types" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_typeofr_script', plugin_dir_url(__FILE__) . '/js/typeofr.js',array('jquery'), '1.0', true);
  }
  
  if(isset($_GET['page']) && $_GET['page'] === "pivot-admin"){
    wp_enqueue_script('pivot_config_test', plugin_dir_url(__FILE__) . '/js/pivot-config-test.js',array('jquery'), '1.0', true);
  }
}

// register jquery and style on initialization
add_action('init', 'pivot_register_script');
function pivot_register_script() {
  wp_register_style('bootstrapexternal', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', array(), '1.0.0', false);
  wp_register_script('slimmin', 'https://code.jquery.com/jquery-3.2.1.slim.min.js', array(), null, true);
  wp_register_script('poppermin', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js', array(), null, true);
  wp_register_script('bootstrapmin', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array(), null, true);
}

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'pivot_enqueue_script');
function pivot_enqueue_script(){
  if(get_option('pivot_bootstrap') == 'on'){
    wp_enqueue_style('bootstrapexternal');
    wp_enqueue_script('slimmin');
    wp_enqueue_script('poppermin');
    wp_enqueue_script('bootstrapmin');
  }
}

/**
 * Define shortcode content
 * Should look like this [pivot_lodging query='QRY-01-0000-000D' path-details='event' nboffers='6' type='activite']
 * @param array $atts attributes 
 * @return string HTML content
 */
function pivot_lodging_shortcode($atts) {
  $output = '';
  
	// Attributes
	$atts = shortcode_atts(
		array(
      'query' => '',
      'type' => '',
      'nboffers' => '3',
		),
		$atts,
		'pivot_lodging'
	);
  
  // Check if attribute "query" is not empty
  if(empty($atts['query'])){
    $text = __('The <strong>query</strong> argument is missing', 'pivot');
    print _show_warning($text);
  }else{
    $xml_query = _xml_query_construction($atts['query']);
  
    // Get template name depending of query type
    switch($atts['type']){
      case 'hebergement':
        $name = 'pivot-lodging-details-part-template';
        break;
      case 'activite':
        $name = 'pivot-event-details-part-template';
        break;
      case 'guide':
        $name = 'pivot-guide-details-part-template';
        break;
      default:
        break;
    }
    
    /*
     * Check if name is set.
     * If not it means attribute "type" is wrong or missing
     */
    if(!isset($name)){
      $text = __('The <strong>type</strong> argument for the query is wrong or missing ', 'pivot');
      print _show_warning($text);
    }else{
      $offres = _construct_output('offer-init-list', $atts['nboffers'], $xml_query);
      
      $output = '<div class="container pivot-list">'
               .'<div class="row row-eq-height pivot-row">';
      
      foreach($offres as $offre){
        $offre->path = 'details';
        $output.= _template($name, $offre);
      }
      
      $output .= '</div></div>';
    }
  }
  return $output;

}

add_shortcode('pivot_lodging', 'pivot_lodging_shortcode');

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

  foreach($pages as $page){
    add_rewrite_tag('%'.$page->path.'%', '([^&]+)');

    add_rewrite_rule(
      '^'.$page->path.'/([^/]*)/?',
      'index.php?'.$page->path.'=$matches[1]',
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
    //$current_page = substr($_SERVER['REQUEST_URI'], $pos+strlen("paged=")); 
    $current_page = (int) filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_NUMBER_INT);
  }else{
    $current_page = 0;
  }
  
  return $current_page;
}

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
    $page = pivot_get_page_path($wp_query->query['pagename']);

    /*
     * Basic case
     * = we are listing offers
     */
    if($wp_query->query['pagename'] == $page->path){
      // Search template file in plugins folder depending on query type
      switch($page->type){
        case 'hebergement':
          $new_template = pivot_locate_template('pivot-lodging-list-template.php');
          break;
        case 'activite':
          $new_template = pivot_locate_template('pivot-event-list-template.php');
          break;
        case 'guide':
          $new_template = pivot_locate_template('pivot-guide-list-template.php');
          break;
      }
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
        global $pivot_offer_type;
        $page = new stdClass();
        // Get number after paged= (position of first letter + length of "paged="
        $type = substr($_SERVER['REQUEST_URI'], $pos+strlen("&type=")); 
        $key = array_search($type, array_column($pivot_offer_type, 'id'));
        
        $page->type = $pivot_offer_type[$key]['parent'];
        $page->path = 'details';
        print $page->path;

      }
    }else{
      $page = pivot_get_page_path($path);
    }
  }
  if($new_template == ''){
    if(isset($page->path) && strpos($wp_query->query[$page->path], 'paged') !== false){
      $current_page = pivot_get_current_page();
      $wp_query->set('paged', $current_page);
      // Search template file in plugins folder depending on query type
      switch($page->type){
        case 'hebergement':
          $new_template = pivot_locate_template('pivot-lodging-list-template.php');
          break;
        case 'activite':
          $new_template = pivot_locate_template('pivot-event-list-template.php');
          break;
        case 'guide':
          $new_template = pivot_locate_template('pivot-guide-list-template.php');
          break;
      }
    }
  }
  
  /*
   * Details case
   * = we want to show an entire offer in its single page
   */
  if($new_template == ''){
    if(isset($page->type)){
      switch($page->type){
        // Search template file in plugins folder depending on query type
        case 'hebergement':
          $new_template = pivot_locate_template('pivot-lodging-details-template.php');
          break;
        case 'activite':
          $new_template = pivot_locate_template('pivot-event-details-template.php');
          break;  
        case 'guide':
          $new_template = pivot_locate_template('pivot-guide-details-template.php');
          break;
      }
    }
  }

  if(isset($page->map)){
    $_SESSION['pivot']['map'] = $page->map;
  }
  if(isset($page->path)){
    $_SESSION['pivot']['path'] = $page->path;
  }
  if(isset($page->query)){
    $_SESSION['pivot']['query'] = $page->query;
  }

  if ($new_template != '') {
    return $new_template;
  } else {
    // This is not a virtualpage, so return initial template
    return $template;
  }
}
add_filter('template_include', 'pivot_template_include');

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
		$default_path = plugin_dir_path( __FILE__ ) . 'templates/'; 
  }
	// Search template file in theme folder.
	$template = locate_template(array($template_path.$template_name, $template_name));
	// Get plugins template file.
	if(!$template ){
		$template = $default_path.$template_name;
  }
	return apply_filters('pivot_locate_template', $template, $template_name, $template_path, $default_path);
}

/**
 * Get template.
 *
 * Search for the template and include the file.
 *
 * @see pivot_locate_template()
 *
 * @param string $template_name	Template to load.
 * @param array $args	Args passed for the template file.
 * @param string $string $template_path	Path to templates.
 * @param string $default_path Default path to template files.
 */
function pivot_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {
	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;
	$template_file = pivot_locate_template( $template_name, $tempate_path, $default_path );
	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		return;
	endif;
	include $template_file;
}

// register My_Widget
add_action( 'widgets_init', function(){
	register_widget('pivot_lodging_widget');
});

// register css files on initialization
add_action('init', 'register_script');
function register_script() {
  wp_register_style('lodging_style', plugins_url('/pivot_lodging.css', __FILE__), false, '1.1.3', 'all');
  wp_register_style('event_style', plugins_url('/pivot_event.css', __FILE__), false, '1.2.9', 'all');
}

// use the css files registered above
add_action('wp_enqueue_scripts', 'enqueue_style');
function enqueue_style(){
  wp_enqueue_style('lodging_style');
  wp_enqueue_style('event_style');
}

class pivot_lodging_widget extends WP_Widget {
  
	// class constructor
  public function __construct() {
    $widget_ops = array( 
      'classname' => 'lodging_widget',
      'description' => 'A plugin to show all lodging from pivot',
    );
    parent::__construct(
      // Base ID of your widget
      'lodging_widget', 
      // Widget name will appear in UI
      'Lodging Widget', 
      // array of options
      $widget_ops
    );
    include_once plugin_dir_path( __FILE__ ).'pivot.common.inc';
  }
	
	// output the widget content on the front-end
	public function widget($args, $instance) {
    
    global $wp_query;
    if(isset($wp_query->query['pagename'])){
      $page = pivot_get_page_path($wp_query->query['pagename']);
    }else{
      $page = pivot_get_page_path(key($wp_query->query));
    }
    
    if(isset($page->id) && $page->id != null){
      pivot_reset_filters($page->id);

      // Print head section and HTML Form
      echo '<section id="block-pivot-lodging-pivot-lodging-filter" class="block block-pivot-lodging clearfix">'
           . '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="pivot-lodging-form" accept-charset="UTF-8">'
           .   '<div  id="edit-equipment-body">';


      // Get filters attach to current page
      $filters = pivot_get_filters($page->id);

      foreach($filters as $filter){
        // if not first iteration and filter is member of a group already inserted, we do not recreate this group
        if(isset($last_filter_group) && $last_filter_group == $filter->filter_group){
          echo pivot_add_filter_to_form($page->id, $filter->filter_name, $filter->id, $filter->filter_title, $filter->urn, $filter->operator, $filter->type);
        }else{
          echo pivot_add_filter_to_form($page->id, $filter->filter_name, $filter->id, $filter->filter_title, $filter->urn, $filter->operator, $filter->type, $filter->filter_group);
        }
        // to remember filter_group of this iteration
        $last_filter_group = $filter->filter_group; 
      }

      // Print footer section and close HTML form
      echo     '</div>'
           .   '<button type="submit" id="filter-submit" name="op" value="Submit" class="btn btn-primary form-submit">'.esc_html("Submit").'</button>'
           .   '<input type="hidden" name="filter-submit" value="1" />'
           . '</form>'
          .'</section>';
    }
  }

	// output the option form field in admin Widgets screen
	public function form($instance) {}

	// save options
	public function update($new_instance, $old_instance) {}
}

function pivot_reset_filters($page_id){
  if(isset($_POST['filter-submit'])){
    unset($_SESSION['pivot']['filters']);
    foreach($_POST as $key => $value){
      if($key != 'op' && $key != 'filter-submit'){
        if(!empty($value)){
          if($value != 'on'){
            $_SESSION['pivot']['filters'][$page_id][$key] = $value;
          }else{
            $_SESSION['pivot']['filters'][$page_id][$key] = TRUE;
          }
        }
      }
    }
  }
}

/**
 * 
 * @param string $filter_name Will be used in class or id in html content
 * @param string $filter_title Will be used in front-end
 * @param string $urn Pivot URN of the field.
 * @param string $operator exist/in
 * @return string HTML output (div containing filter)
 */
function pivot_add_filter_to_form($page_id, $filter_name, $filter_id, $filter_title, $urn, $operator, $type, $group = NULL){
  $field_params = array();
  $output = '';
  $field_params['filters'][$filter_name]['name'] = $urn;
  $field_params['filters'][$filter_name]['operator'] = $operator;
  
  if(isset($group)){
    $output .= '<h2>'.$group.'</h2>';
  }
  switch($type){
    case 'Boolean':
      $number_of_offers = _get_number_of_offers($field_params);
      if($number_of_offers > 0){
        $output .= '<div class="form-item form-item-'.$filter_name.'">'
                .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
                .     '<input type="checkbox" id="edit-'.$filter_name.'" name="'.$filter_id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter_id])?'checked':'').'> '
                .     '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$urn.';h=12"> '.$filter_title.' <span class="badge">'.$number_of_offers.'</span>'
                .   '</label>'
                . '</div>';

        return $output;
      }
      break;
    case 'Type':
      $output .= '<div class="form-item form-item-'.$filter_name.'">'
              .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
              .     '<input type="checkbox" id="edit-'.$filter_name.'" name="'.$filter_id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter_id])?'checked':'').'> '
              .     '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$urn.';h=12"> '.$filter_title
              .   '</label>'
              . '</div>';

      return $output;
    case 'Date':
      $output .= '<div class="form-item form-item-'.$filter_name.'">'
                .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
                .     $filter_title      
                .   '<input type="date" id="edit-'.$filter_name.'" name="'.$filter_id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter_id])?$_SESSION['pivot']['filters'][$page_id][$filter_id]:'').'">'
                . '</div>';
      return $output;
    case 'UInt':
      $output .= '<div class="form-item form-item-'.$filter_name.'">'
                .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
                .     $filter_title
                .   '<input type="number" id="edit-'.$filter_name.'" name="'.$filter_id.'" min="1" max="30" placeholder="'.$filter_name.' 0 à 30"  value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter_id])?$_SESSION['pivot']['filters'][$page_id][$filter_id]:'').'">'
                . '</div>';
      return $output;
    case 'String':
      if($urn == 'urn:fld:adrcom'){
        $output .= '<div class="form-item form-item-'.$filter_name.' form-type-select select">'
                .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
                .   '<select id="edit-'.$filter_name.'" name="'.$filter_id.'">'
                .     _get_commune_from_pivot('mdt', get_option('pivot_mdt'))
                .   '</select>'
                . '</div>';
      }else{
        $output .= '<div class="form-item form-item-'.$filter_name.'">'
                .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
                .   '<input type="text" id="edit-'.$filter_name.'" name="'.$filter_id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter_id])?$_SESSION['pivot']['filters'][$page_id][$filter_id]:'').'">'
                . '</div>';
      }
      return $output;
    default:
      $output .= '<div class="form-item form-item-'.$filter_name.'">'
              .   '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter_name.'" data-original-title="Filter on '.$filter_title.'">'
              .   '<input type="text" id="edit-'.$filter_name.'" name="'.$filter_id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter_id])?$_SESSION['pivot']['filters'][$page_id][$filter_id]:'').'">'
              . '</div>';
      return $output;
  }
  return;
}

/**
 * Implementation of hook_page()
 */
function pivot_lodging_page($page_id) {
  $field_params = array();
  // Define how many offers per page
  $offers_per_page = 12;

  // Check if there is at least ONE active filter
  if(isset($_SESSION['pivot']['filters'][$page_id]) && count($_SESSION['pivot']['filters'][$page_id]) > 0){
    foreach($_SESSION['pivot']['filters'][$page_id] as $key => $value){
      // Get details of filter based on his ID
      $filter = pivot_get_filter($key);

      if(substr($filter->urn, 0, 8) == 'urn:typ:'){
        $field_params['filters']['urn:fld:typeofr']['name'] = 'urn:fld:typeofr';
        $field_params['filters']['urn:fld:typeofr']['operator'] = 'in';
        $field_params['filters']['urn:fld:typeofr']['searched_value'][] = $filter->filter_name;
      }else{
        $field_params['filters'][$key]['name'] = $filter->urn;
        $field_params['filters'][$key]['operator'] = $filter->operator;
      }

      // If operator is no exist, we need the field comparison
      if($filter->operator != 'exist' && !isset($field_params['filters']['urn:fld:typeofr'])){
        // Set value by default
        $value = $_SESSION['pivot']['filters'][$page_id][$key];
        // If the filter is a Date
        if($filter->type === 'Date'){
          // Override value with the requested date format
          $value = date("d/m/Y", strtotime($value));
        }        
        $field_params['filters'][$key]['searched_value'][] = $value;
      }
    }
  }
  
  print '<pre>'; print_r($field_params['filters']); print '</pre>';
  
  // Get current page details
  $page = pivot_get_page_path($_SESSION['pivot']['path']);
  // Check if there if a sort is defined
  if(isset($page->sortMode) && $page->sortMode != NULL && $page->sortMode != ''){
    $field_params['sortField'] = $page->sortField;
    $field_params['sortMode'] = $page->sortMode;
  }
  $xml_query = _xml_query_construction($_SESSION['pivot']['query'], $field_params);

  $output = _construct_output('offer-search', $offers_per_page, $xml_query);

  return $output;
}

/**
 * 
 * @param string $case Define request case
 * @param int $offers_per_page Number of offers per page to display
 * @param Object $xml_query XML file with request to Pivot (filter on specific fields)
 * @return string part of HTML to display
 */
function _construct_output($case, $offers_per_page, $xml_query = NULL){
  // Get current page number (start with 0)
  if(($pos = strpos($_SERVER['REQUEST_URI'], "paged=")) !== FALSE){ 
    $page_number = substr($_SERVER['REQUEST_URI'], $pos+6); 
    $current_page =  (int) filter_var($page_number, FILTER_SANITIZE_NUMBER_INT);
  }else{
    $current_page = 0;
  }
  // Define query type
  $params['type'] = 'query';
//  $params['shuffle'] = TRUE;
  
  // Check current page.
  // If 0 we need to define params to get all offers (depending on filters)
  if($current_page == 0){
    // Define number of offers per page
    $params['items_per_page'] = $offers_per_page;
    // Define content details we want to receive from Pivot
    $params['content_details'] = ';content=1';
    
    // Get offers
    $xml_object = _pivot_request($case, 2, $params, $xml_query);

    // Store number of offers
    $_SESSION['pivot']['nb_offres'] = str_replace(',', '', $xml_object->attributes()->count->__toString());
    // Store the token to get next x items
    $_SESSION['pivot']['token'] = $xml_object->attributes()->token->__toString();

    $offres = $xml_object->offre;
  }else{
    // Get token + current page (set +1 to current page as it start with 0 but with 1 in Pivot)
    $params['token'] = '/'.$_SESSION['pivot']['token'].'/'.++$current_page;

    // Get offers
    $xml_object = _pivot_request('offer-pager', 2, $params);
    $offres = $xml_object->offre;
  }
  
  return $offres;
}