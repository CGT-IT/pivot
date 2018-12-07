<?php

/*
 * Plugin Name: Pivot Lodging Widget
 * Description: Un plugin d'introduction pour le développement sous WordPress
 * Version: 0.1
 * Author: Maxime Degembe
 * License: GPL2
 */

// register jquery and style on initialization
add_action('init', 'pivot_register_script');

add_action('init', 'pivot_start_session', 1);
add_action('end_session_action', 'pivot_end_session');

add_action('admin_enqueue_scripts', 'pivot_enqueue_admin_script');
add_action('wp_enqueue_scripts', 'pivot_enqueue_script');

add_action( 'widgets_init', function(){
	register_widget('pivot_lodging_widget');
});

add_shortcode('pivot_shortcode', 'pivot_custom_shortcode');

add_filter('template_include', 'pivot_template_include');

/**
 * Init Session
 */
function pivot_start_session() {
  if(!session_id()) {
    session_start();
  }
}

/**
 * End Session
 */
function pivot_end_session() {
  session_destroy ();
}

/**
 * Add script on Admin part (on condition)
 */
function pivot_enqueue_admin_script() {
//  wp_enqueue_script('my_custom_script_map', plugin_dir_url(__FILE__) . '/js/map.js',array('jquery'), '2.0', true);
  // Add script only in this case
  // page is "pivot-filters" and "edit" is set to true
  if(isset($_GET['page']) && $_GET['page'] === "pivot-filters" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('my_custom_script', plugin_dir_url(__FILE__) . '/js/filters.js',array('jquery'), '1.6', true);
  }
  
  if(isset($_GET['page']) && $_GET['page'] === "pivot-pages" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_pages_script', plugin_dir_url(__FILE__) . '/js/pages.js',array('jquery'), '1.0', true);
  }
  
  if(isset($_GET['page']) && $_GET['page'] === "pivot-offer-types" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_typeofr_script', plugin_dir_url(__FILE__) . '/js/typeofr.js',array('jquery'), '1.4', true);
  }
  
  if(isset($_GET['page']) && $_GET['page'] === "pivot-admin"){
    wp_enqueue_script('pivot_config_test', plugin_dir_url(__FILE__) . '/js/pivot-config-test.js',array('jquery'), '1.0', true);
  }
}

/**
 * Register Scripts
 */
function pivot_register_script() {
  wp_register_style('lodging_style', plugins_url('/pivot_lodging.css', __FILE__), array(), '3.1', false);
  wp_register_style('event_style', plugins_url('/pivot_event.css', __FILE__), array(), '1.2.9', false);
  wp_register_style('fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '1.0.0', false);
  wp_register_style('bootstrapexternal', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', array(), '1.0.0', false);
  wp_register_script('slimmin', 'https://code.jquery.com/jquery-3.3.1.min.js', array(), null, false);
  wp_register_script('poppermin', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), null, true);
  wp_register_script('bootstrapmin', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array(), null, true);
  wp_register_script('dataTablesmin', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array(), null, true);
}

/**
 * Add the registered jquery and style above
 */
function pivot_enqueue_script(){
  wp_enqueue_style('lodging_style');
  wp_enqueue_style('event_style');
  // Add only if Boostrap is set.
  if(get_option('pivot_bootstrap') == 'on'){
    wp_enqueue_script('pivot_config_test', plugin_dir_url(__FILE__) . '/js/cgtvarious.js',array('jquery'), '1.0', true);
    wp_enqueue_style('fontawesome');
    wp_enqueue_style('bootstrapexternal');
    wp_enqueue_script('slimmin');
    wp_enqueue_script('poppermin');
    wp_enqueue_script('bootstrapmin');
    wp_enqueue_script('dataTablesmin');
  }
}

/**
 * Define shortcode content
 * Should look like this [pivot_lodging query='QRY-01-0000-000D' path-details='event' nboffers='6' type='activite']
 * @param array $atts attributes 
 * @return string HTML content
 */
function pivot_custom_shortcode($atts) {
  $output = '';
  $find_type = false;
  $field_params = array();
  
	// Attributes
	$atts = shortcode_atts(
		array(
      'query' => '',
      'type' => '',
      'nboffers' => '3',
      'filterurn' => '',
      'operator' => '',
      'filtervalue' => '',
      'sort' => '',
		),
		$atts,
		'pivot_shortcode'
	);
  
  // Check if attribute "query" is not empty
  if(empty($atts['query'])){
    $text = __('The <strong>query</strong> argument is missing', 'pivot');
    print _show_warning($text, 'danger');
  }else{
    $types = pivot_get_offer_type_categories();
    foreach($types as $type){
      if($atts['type'] == $type->parent){
        $find_type = true;
      }
    }
      
    if($find_type == true){
      if(!empty($atts['filterurn'])){
        $urn = $atts['filterurn'];
        $urnDoc= _get_urn_documentation_full_spec($urn);
        $type_urn = $urnDoc->spec->type->__toString();
        
        $filter = new stdClass();
        $filter->urn = $urn;
        $filter->type = $type_urn;
        $filter->operator = '';
        $filter->filter_name = '';

        switch($type_urn){
          case 'Choice':
          case 'MultiChoice':
          case 'Object':
          case 'Panel':
          case 'Type de champ':
          case 'HMultiChoice':
            $text = __("It's not possible to add this type of filter: ".$type_urn, 'pivot');
            print _show_warning($text, 'danger');
            break;
          case 'Boolean':
            $filter->operator = 'exist';
            break;
          case 'Type':
          case 'Value':
            $filter->operator = 'in';
            $filter->filter_name = substr(strrchr($urn, ":"), 1);
            break;
          default:

            if(empty($atts['operator'])){
              $text = __('The attribute "operator" is required for this kind of filter', 'pivot');
              print _show_warning($text, 'danger');
            }else{
              $valid_operator = array("equal", "notequal", "like", "notlike", "lesser", "lesserequal", "greater", "greaterequal");
              if(in_array($atts['operator'], $valid_operator)){
                $filter->operator = $atts['operator'];
              }else{
                $operator_list = '<ul>';
                foreach($valid_operator as $operator){
                  $operator_list .= '<li>'.$operator.'</li>';
                }
                $operator_list .= '</ul>';
                $text = __('The attribute "operator" is not valid, it should be one of these: '.$operator_list, 'pivot');
                
                print _show_warning($text, 'danger');
              }
            }
            if(empty($atts['filtervalue'])){
              $text = __('The attribute "filtervalue" is required for this kind of filter', 'pivot');
              print _show_warning($text, 'danger');
            }else{
              $filter->filter_name = $atts['filtervalue'];
            }
            break;
        }
        _construct_filters_array($field_params,$filter);
      }
      if(!empty($atts['sort']) && $atts['sort'] == 'shuffle'){
        $field_params['sortMode'] = 'shuffle';
        $field_params['sortField'] = 'urn:fld:codecgt';
      }
      $xml_query = _xml_query_construction($atts['query'], $field_params);
      
      // Get template name depending of query type
      $template_name = 'pivot-'.$atts['type'].'-details-part-template';

      $offres = _construct_output('offer-search', $atts['nboffers'], $xml_query);
      if($atts['type'] == 'guide'){
        $output = '<div class="container">
                    <div class="row">
                    <div class="col-md-12">
                    <table id="cgt-table-search-paging" class="table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th>Nom</th>
                          <th>Province</th>
                          <th>Contact</th>
                          <th>Adresse</th>
                          <th>Email</th>
                          <th>Salary</th>
                        </tr>
                      </thead>
                      <tbody>';
      }else{
        $output = '<div class="container pivot-list">'
                 .'<div class="row row-eq-height pivot-row">';
      }

      foreach($offres as $offre){
        $offre->path = 'details';
        $output.= _template($template_name, $offre);
      }

      if($atts['type'] == 'guide'){
        $output .= '</tbody></table></div></div></div>';
      }else{
        $output .= '</div></div>';
      }

    }else{
      $text = __('The <strong>type</strong> argument for the query is wrong or missing ', 'pivot');
      print _show_warning($text, 'danger');      
    }
  }
  return $output;
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
		$default_path = plugin_dir_path( __FILE__ ) . 'templates/'; 
  }
	// Search template file in theme folder.
	$template = locate_template(array($template_path.$template_name, $template_name));
	// Get plugins template file.
	if(!$template){
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
    add_filters();
  }

	// output the option form field in admin Widgets screen
	public function form($instance) {}

	// save options
	public function update($new_instance, $old_instance) {}
}

function add_filters(){
  global $wp_query;
  if(isset($wp_query->query['pagename'])){
   $query_page = $wp_query->query['pagename'];
  }else{
    if(isset($wp_query->query['name'])){
      $query_page = $wp_query->query['name'];
    }
  }

  if(isset($query_page)){
    $pivot_page = pivot_get_page_path($query_page);
  }else{
    $pivot_page = pivot_get_page_path(key($wp_query->query));
  }

  if(isset($pivot_page->id) && $pivot_page->id != null){
    pivot_reset_filters($pivot_page->id);
    // Get filters attach to current page
    $filters = pivot_get_filters($pivot_page->id);
    if(empty($filters)){
      return;
    }

    // Print head section and HTML Form
    echo '<section id="block-pivot-lodging-pivot-lodging-filter" class="block block-pivot-lodging clearfix">'
         . '<form action="'.$pivot_page->path.'" method="post" id="pivot-lodging-form" accept-charset="UTF-8">'
         .   '<div  id="edit-equipment-body">';

    foreach($filters as $filter){
      // if not first iteration and filter is member of a group already inserted, we do not recreate this group
      if(isset($last_filter_group) && $last_filter_group == $filter->filter_group){
        echo pivot_add_filter_to_form($pivot_page->id, $filter);
      }else{
        echo pivot_add_filter_to_form($pivot_page->id, $filter, $filter->filter_group);
      }
      // to remember filter_group of this iteration
      $last_filter_group = $filter->filter_group; 
    }

    // Print footer section and close HTML form
    echo     '</div>'
         .   '<button type="submit" id="filter-submit" name="op" value="Submit" class="btn btn-primary form-submit">'.esc_html("Search").'</button>'
         .   '<input type="hidden" name="filter-submit" value="1" />'
         . '</form>'
        .'</section>';
  }
}

function pivot_reset_filters($page_id){
  // If filter form is well submited
  if(isset($_POST['filter-submit'])){
    // Unset everything on filters
    unset($_SESSION['pivot']['filters']);
    // Loop on each parameters
    foreach($_POST as $key => $value){
      // Except 'op' and 'filter-submit' parameters
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
function pivot_add_filter_to_form($page_id, $filter, $group = NULL){
  $field_params = array();
  $output = '';
  if($filter->operator == 'exist'){
    $field_params['filters'][$filter->filter_name]['name'] = $filter->urn;
    $field_params['filters'][$filter->filter_name]['operator'] = 'equal';
    $field_params['filters'][$filter->filter_name]['searched_value'][] = 'true';
  }

  if(isset($group)){
    $output .= '<h2>'.$group.'</h2>';
  }
  switch($filter->type){
    case 'Boolean':
      $number_of_offers = _get_number_of_offers($field_params, $page_id);
      if($number_of_offers > 0){
        $output .= '<div class="form-item form-item-'.$filter->filter_name.'">'
                  .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                  .    '<input type="checkbox" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?'checked':'').'> '
                  .    '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$filter->urn.';h=12"> '.$filter->filter_title.' <span class="badge">'.$number_of_offers.'</span>'
                  .  '</label>'
                  .'</div>';

        return $output;
      }
      break;
    case 'Type':
    case 'Value':
      $output .= '<div class="form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                .    '<input type="checkbox" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?'checked':'').'> '
                .    '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$filter->urn.';h=12"> '.$filter->filter_title
                .  '</label>'
                .'</div>';
      return $output;
    case 'Date':
      $output .= '<div class="form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                .    $filter->filter_title      
                .  '<input type="date" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                .'</div>';
      return $output;
    case 'UInt':
      $output .= '<div class="form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                .    $filter->filter_title
                .  '<input type="number" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" min="1" max="30" placeholder="'.$filter->filter_name.' 0 à 30"  value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                .'</div>';
      return $output;
    case 'String':
      if($filter->urn == 'urn:fld:adrcom'){
        $output .= '<div class="form-item form-item-'.$filter->filter_name.' form-type-select select">'
                  .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                  .  '<select id="edit-'.$filter->filter_name.'" name="'.$filter->id.'">'
                  .    _get_commune_from_pivot('mdt', get_option('pivot_mdt'))
                  .  '</select>'
                  .'</div>';
      }else{
        $output .= '<div class="form-item form-item-'.$filter->filter_name.'">'
                  .  '<label title="'.$filter->filter_title.'" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                  .  '<input type="text" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                  .'</div>';
      }
      return $output;
    default:
      $output .= '<div class="form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="'.$filter->filter_title.'" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$filter->filter_title.'">'
                .  '<input placeholder="'.$filter->filter_title.'" type="text" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                .'</div>';
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
      
      _construct_filters_array($field_params, $filter, $key, $page_id);
      
      // Reset var
      $parent_urn = '';
    }
  }

  // Get current page details
  $pivot_page = pivot_get_page_path($_SESSION['pivot'][$page_id]['path']);
  // Check if there if a sort is defined
  if(isset($pivot_page->sortMode) && $pivot_page->sortMode != NULL && $pivot_page->sortMode != ''){
    $field_params['sortField'] = $pivot_page->sortField;
    $field_params['sortMode'] = $pivot_page->sortMode;
  }
  $xml_query = _xml_query_construction($_SESSION['pivot'][$page_id]['query'], $field_params);

  $output = _construct_output('offer-search', $offers_per_page, $xml_query, $page_id);

  return $output;
}

/**
 * 
 * @param string $case Define request case
 * @param int $offers_per_page Number of offers per page to display
 * @param Object $xml_query XML file with request to Pivot (filter on specific fields)
 * @return string part of HTML to display
 */
function _construct_output($case, $offers_per_page, $xml_query = NULL, $page_id = NULL){
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
    $params['content_details'] = ';content=2';
    
    // Get offers
    $xml_object = _pivot_request($case, 2, $params, $xml_query);
    // Store number of offers
    $_SESSION['pivot'][$page_id]['nb_offres'] = str_replace(',', '', $xml_object->attributes()->count->__toString());
    // Store the token to get next x items
    $_SESSION['pivot'][$page_id]['token'] = $xml_object->attributes()->token->__toString();

    $offres = $xml_object->offre;
  }else{
    // Get token + current page (set +1 to current page as it start with 0 but with 1 in Pivot)
    $params['token'] = '/'.$_SESSION['pivot'][$page_id]['token'].'/'.++$current_page;

    // Get offers
    $xml_object = _pivot_request('offer-pager', 2, $params);
    $offres = $xml_object->offre;
  }
  
  return $offres;
}