<?php

add_action('add_meta_boxes', 'pivot_build_shortcode_box');
add_shortcode('pivot_shortcode', 'pivot_custom_shortcode');
add_shortcode('pivot_shortcode_offer_details', 'pivot_custom_shortcode_offer_details');

/**
 * Define shortcode content
 * Should look like this [pivot_shortcode_offer_details offerid='CGT_0002_000000A3']
 * @param array $atts attributes 
 * @return string HTML content
 */
function pivot_custom_shortcode_offer_details($atts){
  $output = '';
	// Attributes
	$atts = shortcode_atts(
		array(
      'offerid' => ''
		),
		$atts,
		'pivot_shortcode_offer_details'
	);

  // Check if attribute "query" is not empty
  if(empty($atts['offerid'])){
    $text = __('The <strong>offerid</strong> argument is missing', 'pivot');
    print _show_warning($text, 'danger');
  }else{
    $offre = _get_offer_details($atts['offerid']);
    $type = pivot_get_offer_type(null, $offre->typeOffre->label->value->__toString());
    // Get template name depending of offer type
    $template_name = 'pivot-'.(isset($type->parent)?$type->parent:'default').'-details-template';

    // Add main HTML content in output
    $output .= pivot_template($template_name, $offre);
  }
  return $output;
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
      'nbcol' => '4',
      'filterurn' => '',
      'operator' => '',
      'filtervalue' => '',
      'sortmode' => '',
      'sortfield' => '',
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
        $field_params = _construct_filters_array($field_params,$filter);
      }
      // Check sorting
      if(!empty($atts['sortmode'])){
        $field_params['sortMode'] = $atts['sortmode'];
        if(!empty($atts['sortfield']) && $atts['sortmode'] != 'shuffle'){
          $field_params['sortField'] = $atts['sortfield'];
        }
      }
      if($atts['type'] == 'activite'){
        $field_params['page_type'] = 'activite';
      }
      $xml_query = _xml_query_construction($atts['query'], $field_params);
      
      // Get template name depending of query type
      $template_name = 'pivot-'.$atts['type'].'-details-part-template';

      // Get offers
      $offres = pivot_construct_output('offer-search', $atts['nboffers'], $xml_query);
      
      $output = '<div class="container-fluid pivot-list">'
                 .'<div class="row row-eq-height pivot-row d-flex flex-wrap">';

      // Add main HTML content in output
      foreach($offres as $offre){
        $offre->path = 'details';
        $offre->nb_per_row = $atts['nbcol'];
        $output.= pivot_template($template_name, $offre);
      }

      $output .= '</div></div>';

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
      <?php print _get_offer_types(); ?>
    </select>
    <p class="description"><?php esc_html_e('Type of query', 'pivot') ?></p>
  </div>

  <div class="form-item form-item-pivot-nb-offers">
    <label for="edit-pivot-nb-offers"><?php esc_html_e('Define number of offers', 'pivot') ?> </label>
    <input type="number" id="edit-pivot-nb-offers" name="nb-offers" min="1" max="30">
    <p class="description"><?php esc_html_e('It will be 3 by default', 'pivot')?></p>
  </div>

  <div class="form-item form-item-pivot-nb-col">
    <label for="edit-pivot-nb-col"><?php esc_html_e('Define number of offers per line', 'pivot') ?> </label>
    <input type="number" id="edit-pivot-nb-col" name="nb-col" min="2" max="6">
    <p class="description"><?php esc_html_e('It will be 4 by default', 'pivot')?></p>
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
        <option value="exist"><?php esc_html_e('Exist', 'pivot')?></option>
        <option value="equal"><?php esc_html_e('Equal', 'pivot')?></option>
        <option value="like"><?php esc_html_e('Like', 'pivot')?></option>
        <option value="greaterequal"><?php esc_html_e('Greater or equal', 'pivot')?></option>
        <option value="between"><?php esc_html_e('Between', 'pivot')?></option>
        <option value="in"><?php esc_html_e('in', 'pivot')?></option>
      </select>
      <p class="description"><?php esc_html_e('Type of comparison', 'pivot') ?></p>
    </div>
    <div class="form-item form-type-textfield form-item-pivot-filter-value">
      <label for="edit-pivot-filter-value"><?php esc_html_e('Filter value', 'pivot')?> </label>
      <input type="text" id="edit-pivot-filter-value" name="value" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Searched value', 'pivot')?></p>
    </div>
  </div>

  <div class="form-item form-type-textfield form-item-pivot-sortMode">
    <label for="edit-pivot-sortMode"><?php esc_html_e('Sort mode', 'pivot') ?> </label>
    <select id="edit-pivot-sortMode" name="sortMode">
      <option selected value=""><?php esc_html_e('Choose an order', 'pivot') ?></option>
      <option value="ASC"><?php esc_html_e('Ascending', 'pivot') ?></option>
      <option value="DESC"><?php esc_html_e('Descending', 'pivot') ?></option>
      <option value="shuffle"><?php esc_html_e('Shuffle', 'pivot') ?></option>
    </select>
    <p class="description"><?php esc_html_e('Choose the sort mode for the query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-sortField">
    <label for="edit-pivot-sortField"><?php esc_html_e('Sort Field', 'pivot') ?> </label>
    <input type="text" id="edit-pivot-sortField" name="sortField" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Define the field on which the sort mode will apply', 'pivot') ?></p>
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
