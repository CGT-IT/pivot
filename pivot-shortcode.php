<?php

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

      $offres = pivot_construct_output('offer-search', $atts['nboffers'], $xml_query);
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
        $output.= pivot_template($template_name, $offre);
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
