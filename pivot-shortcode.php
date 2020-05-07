<?php

//add_action('add_meta_boxes', 'pivot_build_shortcode_box');
add_shortcode('pivot_shortcode', 'pivot_custom_shortcode');
add_shortcode('pivot_shortcode_slider', 'pivot_custom_shortcode_slider');
add_shortcode('pivot_shortcode_event', 'pivot_custom_shortcode_event');
add_shortcode('pivot_shortcode_event_slider', 'pivot_custom_shortcode_event_slider');
add_shortcode('pivot_shortcode_offer_details', 'pivot_custom_shortcode_offer_details');
add_shortcode('pivot_orc_list', 'pivot_custom_shortcode_orc_list');

function pivot_custom_shortcode_slider($atts){
  $output = '';
  $field_params = array();
	// Attributes
	$atts = shortcode_atts(
		array(
      'query' => '',
      'nboffers' => '6',
      'nbcol' => '2',
      'sortmode' => '',
      'sortfield' => '',
		),
		$atts,
		'pivot_shortcode_slider'
	);

  // Check if attribute "query" is not empty
  if(empty($atts['query'])){
    $text = __('The <strong>query</strong> argument is missing', 'pivot');
    print _show_warning($text, 'danger');
  }else{
    if(!empty($atts['sortmode'])){
      $field_params['sortMode'] = $atts['sortmode'];
      if(!empty($atts['sortfield']) && $atts['sortmode'] != 'shuffle'){
        $field_params['sortField'] = $atts['sortfield'];
      }
    }

    $xml_query = _xml_query_construction($atts['query'], $field_params);

    // Get template name depending of query type
    $template_name = 'pivot-shortcode-slider-template';

    // Get offers
    $offres = pivot_construct_output('offer-search', $atts['nboffers'], $xml_query);

    // Open HTML balises
    $output = '<div class="container-fluid">
                <div id="pivot-shortcode-carousel" class="carousel slide" data-ride="carousel">
                  <div class="carousel-inner row w-100 mx-auto nb-col-'.$atts['nbcol'].'" data-nbcol="'.$atts['nbcol'].'">';

    // Add main HTML content in output
    $i = 0;
    foreach($offres as $offre){
      $offre->path = 'details';
      $offre->nb_per_row = $atts['nbcol'];
      // Will add an 'active' class for the first element
      if($i == 0){
        $offre->first = TRUE;
      }
      $output.= pivot_template($template_name, $offre);
      $i++;
    }

    // Close HTML balises
    $output .= '<a class="carousel-control-prev" href="#pivot-shortcode-carousell" role="button" data-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="sr-only">'.__('Previous').'</span>
                </a>
                <a class="carousel-control-next" href="#pivot-shortcode-carousel" role="button" data-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="sr-only">'.__('Next').'</span>
                </a>
              </div></div></div>';
  }
  return $output;
}

/**
 * Shortcode to display a slider of event
 * @param array $atts
 * @return string HTML content
 */
function pivot_custom_shortcode_event_slider($atts){
  $output = '';
  $field_params = array();
	// Attributes
	$atts = shortcode_atts(
		array(
      'query' => '',
      'nboffers' => '6',
      'nbcol' => '2',
      'sortmode' => '',
      'sortfield' => '',
		),
		$atts,
		'pivot_shortcode_event_slider'
	);

  // Check if attribute "query" is not empty
  if(empty($atts['query'])){
    $text = __('The <strong>query</strong> argument is missing', 'pivot');
    print _show_warning($text, 'danger');
  }else{
    if(!empty($atts['sortmode'])){
      $field_params['sortMode'] = $atts['sortmode'];
      if(!empty($atts['sortfield']) && $atts['sortmode'] != 'shuffle'){
        $field_params['sortField'] = $atts['sortfield'];
      }
    }

    $field_params['page_type'] = 'activite';
    $xml_query = _xml_query_construction($atts['query'], $field_params);

    // Get template name depending of query type
    $template_name = 'pivot-eventslider-details-part-template';

    // Get offers
    $offres = pivot_construct_output('offer-search', $atts['nboffers'], $xml_query);

    // Open HTML balises
    $output = '<div class="container-fluid">
                <div id="pivot-shortcode-carousel" class="carousel slide" data-ride="carousel">
                  <div class="carousel-inner row w-100 mx-auto nb-col-'.$atts['nbcol'].'" data-nbcol="'.$atts['nbcol'].'">';

    // Add main HTML content in output
    $i = 0;
    foreach($offres as $offre){
      $offre->path = 'details';
      $offre->nb_per_row = $atts['nbcol'];
      // Will add an 'active' class for the first element
      if($i == 0){
        $offre->first = TRUE;
      }
      $output.= pivot_template($template_name, $offre);
      $i++;
    }

    // Close HTML balises
    $output .= '</div></div></div>';
  }
  return $output;
}

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
 * Should look like this [pivot_orc_list query='OTH-A0-002R-07NG']
 * @param array $atts attributes 
 * @return string HTML content
 */
function pivot_custom_shortcode_orc_list($atts){
  $output = '';
  $field_params = array();
	// Attributes
	$atts = shortcode_atts(
		array(
      'query' => ''
		),
		$atts,
		'pivot_orc_list'
	);

  // Check if attribute "query" is not empty
  if(empty($atts['query'])){
    $text = __('The <strong>query</strong> argument is missing', 'pivot');
    print _show_warning($text, 'danger');
  }else{
    $xml_query = _xml_query_construction($atts['query'], $field_params);

    // Get template name depending of query type
    $template_name = 'pivot-orc-list-template';

    // Get offers
    $offres = pivot_construct_output('offer-search', 1000, $xml_query);

    // Add main HTML content in output
    $output .= pivot_template($template_name, $offres);
  }
  return $output;
}

/**
 * Define shortcode content
 * Should look like this [pivot_lodging query='QRY-01-0000-000D' path-details='event' nboffers='6' type='activite']
 * @param array $atts attributes 
 * @return string HTML content
 */
function pivot_custom_shortcode_event($atts) {
  $output = '';
  $find_type = false;
  $field_params = array();
  
	// Attributes
	$atts = shortcode_atts(
		array(
      'query' => '', // Query Pivot
      'nboffers' => '3', // Number of offers to display
      'nbcol' => '4', // Number of column to display
      'date1' => '', // URN of the first date you want to compare
      'operator1' => '', // Comparison operator for the first date. Allowed values: equal, lesser, lesserequal, greater, greaterequal
      'value1' => '', // Relative first date value to compare
      'date2' => '', // URN of the first date you want to compare
      'operator2' => '', // Comparison operator for the second date. Allowed values: equal, lesser, lesserequal, greater, greaterequal
      'value2' => '', // Relative second date value to compare
      'sortmode' => '', // Sort mode. Allowed values: shuffle, asc, desc
      'sortfield' => '', // URN of the field you want to sort
		),
		$atts,
		'pivot_shortcode_event'
	);

  // Check if attribute "query" is not empty
  if(empty($atts['query'])){
    $text = __('The <strong>query</strong> argument is missing', 'pivot');
    return _show_warning($text, 'danger');
  }else{
    // Construct filter if set for first date
    if(!empty($atts['date1'])){
      // Operator is required so check if it is well set
      if(empty($atts['operator1'])){
        $text = __('The attribute "operator1" is required as you defined a startdate attribute', 'pivot');
        return _show_warning($text, 'danger');
      }else{
        $valid_operator = array("equal", "lesser", "lesserequal", "greater", "greaterequal");
        // Check if operator is valid
        if(in_array($atts['operator1'], $valid_operator)){
          if(!empty($atts['value1'])){
            $field_params['filters']['shortcode_date_start']['name'] = $atts['date1'];
            $field_params['filters']['shortcode_date_start']['operator'] = $atts['operator1'];
            $field_params['filters']['shortcode_date_start']['searched_value'][] = date("d/m/Y", strtotime($atts['value1']));
          }else{
            $text = __('The attribute "value1" is required as you defined a startdate attribute', 'pivot');
            return _show_warning($text, 'danger');
          }
        }else{
          // If operator not valid, construction of error message
          $operator_list = '<ul>';
          foreach($valid_operator as $operator){
            $operator_list .= '<li>'.$operator.'</li>';
          }
          $operator_list .= '</ul>';
          $text = __('The attribute "operator1" is not valid, it should be one of these: '.$operator_list, 'pivot');
          return _show_warning($text, 'danger');
        }
      }
    }
    // Construct filter if set for second date
    if(!empty($atts['date2'])){
      // Operator is required so check if it is well set
      if(empty($atts['operator2'])){
        $text = __('The attribute "operator2" is required as you defined a startdate attribute', 'pivot');
        return _show_warning($text, 'danger');
      }else{
        $valid_operator = array("equal", "lesser", "lesserequal", "greater", "greaterequal");
        // Check if operator is valid
        if(in_array($atts['operator2'], $valid_operator)){
          if(!empty($atts['value2'])){
            $field_params['filters']['shortcode_date_end']['name'] = $atts['date2'];
            $field_params['filters']['shortcode_date_end']['operator'] = $atts['operator2'];
            $field_params['filters']['shortcode_date_end']['searched_value'][] = date("d/m/Y", strtotime($atts['value2']));
          }else{
            $text = __('The attribute "value2" is required as you defined a startdate attribute', 'pivot');
            return _show_warning($text, 'danger');
          }
        }else{
          // If operator not valid, construction of error message
          $operator_list = '<ul>';
          foreach($valid_operator as $operator){
            $operator_list .= '<li>'.$operator.'</li>';
          }
          $operator_list .= '</ul>';
          $text = __('The attribute "operator2" is not valid, it should be one of these: '.$operator_list, 'pivot');
          return _show_warning($text, 'danger');
        }
      }
    }
    // Check sorting
    if(!empty($atts['sortmode'])){
      $field_params['sortMode'] = $atts['sortmode'];
      if(!empty($atts['sortfield']) && $atts['sortmode'] != 'shuffle'){
        $field_params['sortField'] = $atts['sortfield'];
      }
    }
    
    $xml_query = _xml_query_construction($atts['query'], $field_params);

    // Get template name depending of query type
    $template_name = 'pivot-activite-details-part-template';

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
      'query' => '',      // Query ID coming from PIVOT
      'type' => '',       // template type to show offers
      'nboffers' => '3',  // Number of offers to display
      'nbcol' => '4',     // Define how many column we want to display (@see bootstrap)
      'filterurn' => '',  // Can be use to filter the query on an urn of your choice
      'operator' => '',   // Comparison operator to filter
      'filtervalue' => '',// Comparison value to filter
      'sortmode' => '',   // Define sort mode (choice limited)
      'sortfield' => '',  // if sortmode is ASC or DESC, use this one to define on which field
      'details' => '2',   // Should not be used unless you want to display a simple list of title and links of offers
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
      $offres = pivot_construct_output('offer-search', $atts['nboffers'], $xml_query, NULL, $atts['details']);
      
      $output = '<div class="container-fluid pivot-list">';
      // Change display as we want to display only title and url on list
      if($atts['details'] == 1){
        $output .= '<div class="list-group">';
      }else{
        $output .='<div class="row row-eq-height pivot-row d-flex flex-wrap">';
      }

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
/*function pivot_build_shortcode_box(){
  $screens = get_post_types();
  foreach ($screens as $screen) {
    add_meta_box(
      'pivot_build_shortcode_box',          // Unique ID
      __('Build Pivot Shortcode', 'pivot'), // Box title
      'pivot_build_shortcode_box_html',     // Content callback, must be of type callable
      $screen                               // Post type
    );
  }
}*/

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

/**
 * HTML content for the custom meta box
 */
function pivot_build_shortcode_event_box_html(){
  ?>
  <h1 id="pivot-shortcodeh1"><?php _e('Create your shortcode event', 'pivot');?></h1>
  <div class="form-item form-type-textfield form-item-pivot-query">
    <label for="edit-pivot-query"><?php esc_html_e('Query', 'pivot') ?></label>
    <input type="text" id="edit-pivot-query" name="query" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot predefined query', 'pivot') ?></p>
  </div>

  <div class="form-item form-item-pivot-nb-offers">
    <label for="edit-pivot-nb-offers"><?php esc_html_e('Define number of offers to display', 'pivot') ?> </label>
    <input type="number" id="edit-pivot-nb-offers" name="nb-offers" min="1" max="30" value="3">
    <p class="description"><?php esc_html_e('It will be 3 by default', 'pivot')?></p>
  </div>

  <div class="form-item form-item-pivot-nb-col">
    <label for="edit-pivot-nb-col"><?php esc_html_e('Define number of offers to display per line', 'pivot') ?> </label>
    <input type="number" id="edit-pivot-nb-col" name="nb-col" min="2" max="6" value="4">
    <p class="description"><?php esc_html_e('It will be 4 by default', 'pivot')?></p>
  </div>

  <div class="form-item form-type-textfield form-item-pivot-date1">
    <label for="edit-pivot-date1"><?php esc_html_e('First date', 'pivot')?> </label>
    <select id="edit-pivot-date1" name="date1">
      <option selected value=""><?php esc_html_e('Choose a date', 'pivot') ?></option>
      <option value="urn:fld:datedebvalid"><?php esc_html_e('Début de publication', 'pivot') ?></option>
      <option value="urn:fld:datefinvalid"><?php esc_html_e('Fin de publication', 'pivot') ?></option>
      <option value="urn:fld:date:datedeb"><?php esc_html_e('Date de début', 'pivot') ?></option>
      <option value="urn:fld:date:datefin"><?php esc_html_e('Date de fin', 'pivot') ?></option>
    </select>
    <p class="description"><?php esc_html_e('URN or ID of the date field you want to filter', 'pivot')?></p>
  </div>
  <div id="value-date1-infos">
    <div class="form-item form-type-textfield form-item-pivot-operator1">
      <label for="edit-pivot-operator1"><?php esc_html_e('Operator', 'pivot')?> </label>
      <select id="edit-pivot-operator1" name="operator1">
        <option selected disabled hidden><?php esc_html_e('Choose an operator', 'pivot')?></option>
        <option value="equal"><?php esc_html_e('Equal', 'pivot')?></option>
        <option value="lesser"><?php esc_html_e('Lesser', 'pivot')?></option>
        <option value="lesserequal"><?php esc_html_e('Lesser or equal', 'pivot')?></option>
        <option value="greater"><?php esc_html_e('Greater', 'pivot')?></option>
        <option value="greaterequal"><?php esc_html_e('Greater or equal', 'pivot')?></option>
      </select>
      <p class="description"><?php esc_html_e('Type of comparison', 'pivot') ?></p>
    </div>
    <div class="form-item form-type-textfield form-item-pivot-value">
      <label for="edit-pivot-value1"><?php esc_html_e('Relative start date value', 'pivot')?> </label>
      <input type="number" id="edit-pivot-value1" name="value1" min="1" max="50">
    </div>
    <div class="form-item form-type-textfield form-item-pivot-format">
      <label for="edit-pivot-format1"><?php esc_html_e('Format', 'pivot') ?> </label>
      <select id="edit-pivot-format1" name="format1">
        <option selected value=""><?php esc_html_e('Choose a format', 'pivot') ?></option>
        <option value="days"><?php esc_html_e('Day(s)', 'pivot') ?></option>
        <option value="weeks"><?php esc_html_e('Week(s)', 'pivot') ?></option>
        <option value="months"><?php esc_html_e('Month(s)', 'pivot') ?></option>
      </select>
      <p class="description"><a target='_blank' href='https://www.php.net/manual/fr/datetime.formats.relative.php'><?php esc_html_e('It will use relative dates', 'pivot') ?></a></p>
    </div>
  </div>

  <div class="form-item form-type-textfield form-item-pivot-date2">
    <label for="edit-pivot-date2"><?php esc_html_e('Second date', 'pivot')?> </label>
    <select id="edit-pivot-date2" name="date2">
      <option selected value=""><?php esc_html_e('Choose a date', 'pivot') ?></option>
      <option value="urn:fld:datedebvalid"><?php esc_html_e('Début de publication', 'pivot') ?></option>
      <option value="urn:fld:datefinvalid"><?php esc_html_e('Fin de publication', 'pivot') ?></option>
      <option value="urn:fld:date:datedeb"><?php esc_html_e('Date de début', 'pivot') ?></option>
      <option value="urn:fld:date:datefin"><?php esc_html_e('Date de fin', 'pivot') ?></option>
    </select>
    <p class="description"><?php esc_html_e('URN or ID of the date field you want to filter', 'pivot')?></p>
  </div>  
  <div id="value-date2-infos">
    <div class="form-item form-type-textfield form-item-pivot-operator2">
      <label for="edit-pivot-operator2"><?php esc_html_e('Operator', 'pivot')?> </label>
      <select id="edit-pivot-operator2" name="operator2">
        <option selected disabled hidden><?php esc_html_e('Choose an operator', 'pivot')?></option>
        <option value="equal"><?php esc_html_e('Equal', 'pivot')?></option>
        <option value="lesser"><?php esc_html_e('Lesser', 'pivot')?></option>
        <option value="lesserequal"><?php esc_html_e('Lesser or equal', 'pivot')?></option>
        <option value="greater"><?php esc_html_e('Greater', 'pivot')?></option>
        <option value="greaterequal"><?php esc_html_e('Greater or equal', 'pivot')?></option>
      </select>
      <p class="description"><?php esc_html_e('Type of comparison', 'pivot') ?></p>
    </div>
    <div class="form-item form-type-textfield form-item-pivot-value">
      <label for="edit-pivot-value2"><?php esc_html_e('Relative start date value', 'pivot')?> </label>
      <input type="number" id="edit-pivot-value2" name="value2" min="1" max="50">
    </div>
    <div class="form-item form-type-textfield form-item-pivot-format">
      <label for="edit-pivot-format2"><?php esc_html_e('Format', 'pivot') ?> </label>
      <select id="edit-pivot-format2" name="format2">
        <option selected value=""><?php esc_html_e('Choose a format', 'pivot') ?></option>
        <option value="days"><?php esc_html_e('Day(s)', 'pivot') ?></option>
        <option value="weeks"><?php esc_html_e('Week(s)', 'pivot') ?></option>
        <option value="months"><?php esc_html_e('Month(s)', 'pivot') ?></option>
      </select>
      <p class="description"><a target='_blank' href='https://www.php.net/manual/fr/datetime.formats.relative.php'><?php esc_html_e('It will use relative dates', 'pivot') ?></a></p>
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

  <div><input id="build-shortcode-event" type="button" class="button" value="<?php esc_html_e('Build shortcode', 'pivot')?>"> </button></div>
  <br>
  <div id="major-publishing-actions" class="form-item form-type-textfield form-item-pivot-shortcode">
    <label class="bold" for="edit-pivot-shortcode"><?php esc_html_e('Shortcode to insert', 'pivot')?> </label>
    <input id="pivot-shortcode-insertion" size="200" style="text-align: left;" value="">
    <div class="button" id="clipboard-btn" data-clipboard-target="#pivot-shortcode-insertion"><span class="dashicons dashicons-editor-paste-text"></span></div>
    <p class="description"><?php esc_html_e('Copy and paste this text in your post, page, ... body', 'pivot')?></p>
  </div>

  <?php
}
