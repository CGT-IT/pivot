<?php

add_action('add_meta_boxes', 'pivot_build_shortcode_box');
add_shortcode('pivot_shortcode', 'pivot_custom_shortcode');

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
    // Check if type is valid
    $type = pivot_get_offer_type_categories($atts['type']);
    // If type is valid (and query is set), the minimum is set to build the shortcode
    if($type){
      // Construct filter if set
      if(!empty($atts['filterurn'])){
        $urn = $atts['filterurn'];
        // Get specification of the filtered URN
        $urnDoc= _get_urn_documentation_full_spec($urn);
        // Get type of the filtered URN
        $type_urn = $urnDoc->spec->type->__toString();
        
        $filter = new stdClass();
        $filter->urn = $urn;
        $filter->type = $type_urn;
        $filter->operator = '';
        $filter->filter_name = '';

        // Check the type of the filter URN and do some tricks depending the case
        switch($type_urn){
          case 'Choice':
          case 'MultiChoice':
          case 'Object':
          case 'Panel':
          case 'Type de champ':
          case 'HMultiChoice':
            // Can't handle those URN types
            $text = __("It's not possible to add this type of filter: ".$type_urn, 'pivot');
            print _show_warning($text, 'danger');
            break;
          case 'Boolean':
            // By default for boolean operator is "exist"
            $filter->operator = 'exist';
            break;
          case 'Type':
          case 'Value':
            // Operator and filtered value can be guess for thoses types
            $filter->operator = 'in';
            $filter->filter_name = substr(strrchr($urn, ":"), 1);
            break;
          default:
            // Default case, operator is required so check if it is well set
            if(empty($atts['operator'])){
              $text = __('The attribute "operator" is required for this kind of filter', 'pivot');
              print _show_warning($text, 'danger');
            }else{
              $valid_operator = array("equal", "notequal", "like", "notlike", "lesser", "lesserequal", "greater", "greaterequal");
              // Check if operator is valid
              if(in_array($atts['operator'], $valid_operator)){
                $filter->operator = $atts['operator'];
              }else{
                // If operator not valid, construction of error message
                $operator_list = '<ul>';
                foreach($valid_operator as $operator){
                  $operator_list .= '<li>'.$operator.'</li>';
                }
                $operator_list .= '</ul>';
                $text = __('The attribute "operator" is not valid, it should be one of these: '.$operator_list, 'pivot');
                
                print _show_warning($text, 'danger');
              }
            }
            // Check if filtered value is well set
            if(empty($atts['filtervalue'])){
              // If filtered value is not set, construction of error message
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

      // Get offers
      $offres = pivot_construct_output('offer-search', $atts['nboffers'], $xml_query);
      
      // Start and open HTML balise in output
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

      // Add main HTML content in output
      foreach($offres as $offre){
        $offre->path = 'details';
        $output.= pivot_template($template_name, $offre);
      }

      // Close HTML balise in output
      if($atts['type'] == 'guide'){
        $output .= '</tbody></table></div></div></div>';
      }else{
        $output .= '</div></div>';
      }

    }else{
      $text = __('The <strong>type</strong> attributes for the query is wrong or missing ', 'pivot');
      print _show_warning($text, 'danger');      
    }
  }
  return $output;
}

/**
 * Add custom meta box on all post / page types
 * This box will help to build pivot shortcode
 */
function pivot_build_shortcode_box(){
  $screens = get_post_types();
  foreach ($screens as $screen) {
    add_meta_box(
      'pivot_build_shortcode_box',          // Unique ID
      __('Build Pivot Shortcode', 'pivot'), // Box title
      'pivot_build_shortcode_box_html',     // Content callback, must be of type callable
      $screen                               // Post type
    );
  }
}

/**
 * HTML content for the custom meta box
 */
function pivot_build_shortcode_box_html(){
  ?>
  <div class="form-item form-type-textfield form-item-pivot-query">
    <label for="edit-pivot-query"><?php esc_html_e('Query', 'pivot') ?></label>
    <input type="text" id="edit-pivot-query" name="query" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot predefined query', 'pivot') ?></p>
  </div>

  <div class="form-item form-type-textfield form-item-pivot-type">
    <label for="edit-pivot-type"><?php esc_html_e('Type', 'pivot') ?> </label>
    <select id="edit-pivot-type" name="type">
      <?php print _get_offer_types($edit_page); ?>
    </select>
    <p class="description"><?php esc_html_e('Type of query', 'pivot') ?></p>
  </div>

  <div class="form-item form-item-pivot-nb-offers">
    <label for="edit-pivot-nb-offers"><?php esc_html_e('Define number of offers', 'pivot') ?> </label>
    <input type="number" id="edit-pivot-nb-offers" name="nb-offers" min="1" max="30">
    <p class="description"><?php esc_html_e('It will be 3 by default', 'pivot')?></p>
  </div>

  <div class="form-item form-type-textfield form-item-pivot-urn">
    <label for="edit-pivot-urn"><?php esc_html_e('Filter URN', 'pivot')?> </label>
    <input type="text" id="edit-pivot-urn" name="urn" maxlength="128" class="form-text">
    <span><input id="load-urn-info" type="button" class="button" value="<?php esc_html_e('Load URN Infos', 'pivot')?>"> </input></span>
    <p class="description"><?php esc_html_e('URN or ID of the field you want to filter', 'pivot')?></p>
  </div>

  <div id="filter-urn-infos">
    <div class="form-item form-type-textfield form-item-pivot-operator">
      <label for="edit-pivot-operator"><?php esc_html_e('Operator', 'pivot')?> </label>
      <select id="edit-pivot-operator" name="operator">
        <option selected disabled hidden><?php esc_html_e('Choose an operator', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'exist') echo 'selected="selected"';?>value="exist"><?php esc_html_e('Exist', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'equal') echo 'selected="selected"';?>value="equal"><?php esc_html_e('Equal', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'like') echo 'selected="selected"';?>value="like"><?php esc_html_e('Like', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'greaterequal') echo 'selected="selected"';?>value="greaterequal"><?php esc_html_e('Greater or equal', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'between') echo 'selected="selected"';?>value="between"><?php esc_html_e('Between', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'in') echo 'selected="selected"';?>value="in"><?php esc_html_e('in', 'pivot')?></option>
      </select>
      <p class="description">Type of comparison</p>
    </div>
    <div class="form-item form-type-textfield form-item-pivot-filter-value">
      <label for="edit-pivot-filter-value"><?php esc_html_e('Filter value', 'pivot')?> </label>
      <input type="text" id="edit-pivot-filter-value" name="value" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Searched value', 'pivot')?></p>
    </div>
  </div>

  <div><input id="build-shortcode" type="button" class="button" value="<?php esc_html_e('Build shortcode', 'pivot')?>"> </button></div>
  <br>
  <div id="major-publishing-actions" class="form-item form-type-textfield form-item-pivot-shortcode">
    <label class="bold" for="edit-pivot-shortcode"><?php esc_html_e('Shortcode to insert', 'pivot')?> </label>
    <input id="pivot-shortcode-insertion" size="120" style="text-align: left;" value="">
    <div class="button" id="clipboard-btn" data-clipboard-target="#pivot-shortcode-insertion"><span class="dashicons dashicons-editor-paste-text"></span></div>
    <p class="description"><?php esc_html_e('Copy and paste this text in your post, page, ... body', 'pivot')?></p>
  </div>

  <?php
}
