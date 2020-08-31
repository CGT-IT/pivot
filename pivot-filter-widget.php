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
    print pivot_display_widget($instance);
  }

	// output the option form field in admin Widgets screen
	public function form($instance) {
    $defaults = array('pageid' => '0');
    if(isset($instance['pageid'])){
      $pageid = $instance['pageid'];
    }
    
    $pages = pivot_get_pages();
    
    // markup for form
    $output = '<div class="form-item form-type-textfield form-item-pivot-'.$this->get_field_id('pageid').'">'
             .'<label for="'.$this->get_field_id('pageid').'">'.esc_html__('Referenced Page ID', 'pivot').'</label>'
             .'<select id="'.$this->get_field_id('pageid').'" name="'.$this->get_field_name('pageid').'">'
             .'<option selected value="">'.esc_html__('Choose a page', 'pivot').'</option>';
    foreach($pages as $page){
      if($pageid == $page->title){
        $output .= '<option selected value="'.$page->title.'">'.$page->title.'</option>';
      }else{
        $output .= '<option value="'.$page->title.'">'.$page->title.'</option>';
      }
    }
    $output .= '</select></div>';

    echo $output;
  }

	// save options
	public function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['pageid'] = strip_tags($new_instance['pageid']);
    return $instance;
  }
}
function pivot_display_widget($instance = NULL){
  if(isset($instance['pageid'])){
    $pivot_page = pivot_get_page_path($instance['pageid']);
  }

  if(isset($pivot_page->id) && $pivot_page->id != null){
    pivot_reset_filters($pivot_page->id);
    // Get filters attach to current page
    $filters = pivot_get_filters($pivot_page->id);
    if(empty($filters)){
      return;
    }

    // Print head section and HTML Form
    $output = '<section id="block-pivot-filters" class="block block-pivot block-pivot-filter clearfix">'
         . '<form action="'.get_site_url().'/'.$pivot_page->path.'" method="post" id="pivot-filter-form" accept-charset="UTF-8">';

    foreach($filters as $filter){
      // if not first iteration and filter is member of a group already inserted, we do not recreate this group
      if(isset($last_filter_group) && $last_filter_group == $filter->filter_group){
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter);
      }else{
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter, $filter->filter_group);
      }
      // to remember filter_group of this iteration
      $last_filter_group = $filter->filter_group; 
    }

    // Print footer section and close HTML form
    $output .= '<div><button type="submit" id="filter-submit" name="filter-submit" value="'.esc_html__('Search', 'pivot').'"class="btn btn-lg form-submit" style="background-color:#f5f5f5;"><i class="fas fa-search"></i> '.esc_html__('Search', 'pivot').'</button></div>'
         .   '</div>'
         . '</form>'
        .'</section>';
    
    return $output;
  }
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
    $output = '<section id="block-pivot-filters" class="block block-pivot block-pivot-filter clearfix">'
         . '<form action="'.get_site_url().'/'.$pivot_page->path.'" method="post" id="pivot-filter-form" accept-charset="UTF-8">'
         .   '<div  id="edit-filter-body">';

    foreach($filters as $filter){
      // if not first iteration and filter is member of a group already inserted, we do not recreate this group
      if(isset($last_filter_group) && $last_filter_group == $filter->filter_group){
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter);
      }else{
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter, $filter->filter_group);
      }
      // to remember filter_group of this iteration
      $last_filter_group = $filter->filter_group; 
    }

    // Print footer section and close HTML form
    $output .= '</div>'
         .   '<div class="row mt-2 filter-buttons">'
         .     '<div class="col-xl-5 col-12">'
         .       '<button type="submit" id="filter-reset" name="filter-reset" value="'.esc_html__('Reset', 'pivot').'"class="btn text-dark btn-lg btn-block form-submit" style="background-color:#f5f5f5;"><i class="fas fa-redo-alt"></i> '.esc_html__('Reset', 'pivot').'</button>'
         .     '</div>'
         .     '<div class="col-xl-7 col-12">'
         .       '<button type="submit" id="filter-submit" name="filter-submit" value="'.esc_html__('Search', 'pivot').'" class="btn btn-lg btn-block form-submit text-white" style="background-color:#555555;"><i class="fas fa-search"></i> '.esc_html__('Search', 'pivot').'</button>'
         .     '</div>'
         .   '</div>'
         . '</form>'
        .'</section>';
    
    return $output;
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
  }else{
    if(isset($_POST['filter-reset'])){
      $_SESSION['pivot']['filters'][$page_id] = array();
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

  if(isset($group) && !empty($group)){
    $output .= '<div class="filter-group text-uppercase font-weight-bolder p-2 mb-2 mt-2 bg-light">'.__($group, 'pivot').'</div>';
  }
  // check if current language is different from fr
  if(substr(get_locale(), 0, 2 ) != 'fr'){
    // Check if filter title is translated in WPML
    if($filter->filter_title != __($filter->filter_title, 'pivot')){
      $title = __($filter->filter_title, 'pivot');
    }else{
      // Otherwise, Get translated title from Pivot
      $title = _get_urn_documentation($filter->urn);
    }
  }else{
    $title = $filter->filter_title;
  }
  switch($filter->type){
    case 'Boolean':
        $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                  .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'
                  .    '<input type="checkbox" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?'checked':'').'> '
                  .    '<img heiht="12px" width="12px" class="pivot-picto" src="'.get_option('pivot_uri').'img/'.$filter->urn.';h=12"> '.$title
                  .  '</label>'
                  .'</div>';

        return $output;
      break;
    case 'Type':
    case 'Value':
      $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'
                .    '<input type="checkbox" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?'checked':'').'> '
                .    '<img class="pivot-picto" src="'.get_option('pivot_uri').'img/'.$filter->urn.';h=12"> '.$title
                .  '</label>'
                .'</div>';
      return $output;
    case 'Date':
      $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="" data-toggle="tooltip" class="w-50 control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'
                .    $title      
                .  '</label>'
                .  '<input type="date" class="w-50" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                .'</div>';
      return $output;
    case 'UInt':
      $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="" data-toggle="tooltip" class="w-50 control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'
                .    $title
                .  '</label>'
                .  '<input type="number" class="w-50" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" min="1" max="1000" placeholder="'.$title.'"  value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                .'</div>';
      return $output;
    case 'String':
      if($filter->urn == 'urn:fld:adrcom'){
        $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.' form-type-select select">'
                  .  '<label title="" data-toggle="tooltip" class="w-50 control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'.$title.'</label>'
                  .  '<select id="edit-'.$filter->filter_name.'" class="w-50" name="'.$filter->id.'">'
                  .    _get_commune_from_pivot('mdt', get_option('pivot_mdt'), (isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:null))
                  .  '</select>'
                  .'</div>';
      }else{
        if ($filter->urn == 'urn:fld:idorc') {
          $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                  .  '<label title="" data-toggle="tooltip" class="control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'
                  .    '<input type="checkbox" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'"  class="form-checkbox"'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?'checked':'').'> '
                  .    $title
                  .  '</label>'
                  .'</div>';
        }else{
          $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                    .  '<label title="'.$title.'" data-toggle="tooltip" class="w-50 control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'.$title.'</label>'
                    .  '<input type="text" id="edit-'.$filter->filter_name.'" class="w-50" name="'.$filter->id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                    .'</div>';
        }
      }
      return $output;
    default:
      $output .= '<div class="pl-2 form-item form-item-'.$filter->filter_name.'">'
                .  '<label title="'.$title.'" data-toggle="tooltip" class="w-50 control-label" for="edit-'.$filter->filter_name.'" data-original-title="Filter on '.$title.'">'
                .    $title
                .  '</label>'
                .  '<input placeholder="'.$title.'" type="text" class="w-50" id="edit-'.$filter->filter_name.'" name="'.$filter->id.'" value="'.(isset($_SESSION['pivot']['filters'][$page_id][$filter->id])?$_SESSION['pivot']['filters'][$page_id][$filter->id]:'').'">'
                .'</div>';
      return $output;
  }
  return;
}