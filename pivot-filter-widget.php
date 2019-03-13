<?php

add_action( 'widgets_init', function(){
	register_widget('pivot_filter_widget');
});

// Register My_Widget
class pivot_filter_widget extends WP_Widget {
  
	// class constructor
  public function __construct() {
    $widget_ops = array( 
      'classname' => 'filter_widget',
      'description' => 'A widget fo filter on offers from pivot',
    );
    parent::__construct(
      // Base ID of your widget
      'filter_widget',
      // Widget name will appear in UI
      'Filter Widget',
      // array of options
      $widget_ops
    );
  }
	
	// output the widget content on the front-end
	public function widget($args, $instance) {
    pivot_add_filters();
  }

	// output the option form field in admin Widgets screen
	public function form($instance) {}

	// save options
	public function update($new_instance, $old_instance) {}
}

function pivot_add_filters(){
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
         .   '<button type="submit" id="filter-submit" name="op" value="Submit" class="btn btn-primary form-submit">'.esc_html('Search', 'pivot').'</button>'
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