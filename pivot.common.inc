<?php

/**
 * Return a specific urn value
 * 
 * @param Object $offre Complete offer Object
 * @param string $urn_name urn name we are looking for
 * @param string $lang current language interface
 * @return string Return string value of a urn (field)
 */
function _get_urn_value($offre, $urn_name, $lang = NULL){
  if($lang){
    $urn_name = $lang.':'.$urn_name;
  }
  foreach ($offre->spec as $spec){
    if($spec->attributes()->__toString() == $urn_name){
      return $spec->value->__toString();
    }
  }
}

/**
 * Return a field translated value
 * 
 * @global Object $language
 * @param Object $field the specific field we want the value in it
 * @return string field value
 */
function _get_translated_value($field){
  // Get current language and take only 2first characters
  $lang = substr(get_locale(), 0, 2 );

  // Loop on field translations
  foreach ($field as $item){
    // Get translation and check if there is a value  
    if($item->attributes()->__toString() == $lang && $item->value->__toString()){
      return $item->value->__toString();
    }
  }
}

/**
 * Call the "thesaurus" web service to get the translated documentation of specific URN
 * 
 * @param string $urn_name Name of the URN
 * @return string urn documentation translated
 */
function _get_urn_documentation($urn_name){
  $params['urn_name'] = $urn_name;
  $xml_object = _pivot_request('thesaurus', 0, $params);

  foreach ($xml_object->spec as $item){
    return _get_translated_value($item);
  }
}

/**
 * Call the "thesaurus" web service to get the documentation Object of a specific URN
 * 
 * @param string $urn_name Name of the URN
 * @return Object documentation of the urn
 */
function _get_urn_documentation_full_spec($urn_name){
  $params['urn_name'] = $urn_name;
  $xml_object = _pivot_request('thesaurus', 0, $params);
  
  return $xml_object;
}

function _search_specific_urn_img($offre, $urn, $height, $color, $original = FALSE){
  // Loop on each specific field
  foreach($offre->spec as $specification){
    // Check if it's the one we are looking for
    if($specification->attributes()->urn->__toString() == $urn){
      // add specific class to allow overriding. Replace : by -
      $output = '<span class="item-service '.str_replace(":", "-",$urn).'">';
      // prepare img title attribute
      $title_attribute = 'title="'._get_urn_documentation($urn).'"';
      // prepare img alt attribute
      $alt_attribute = 'alt="image '._get_urn_documentation($urn).'"';
      
      // Construct <img/> tag
      $img = '<img '.$title_attribute.' '.$alt_attribute.' class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$urn.';h='.$height;
      if($color){
        $img .= ';c='.$color;
      }
      if($original == TRUE){
        $img .= ';modifier=orig';
      }
      $img .= '"/>';
      
      $output .= $img.'</span>';
      
      return $output;
    }
  }
}

function _search_specific_urn($offre, $urn){
  // Loop on each specific field
  foreach($offre->spec as $specification){
    // Check if it's the one we are looking for
    if($specification->attributes()->urn->__toString() == $urn){
      $output = $specification->value->__toString();
      
      return $output;
    }
  }
}

function _get_ranking_picto($offre, $urn){
  // Loop on each specific field
  foreach($offre->spec as $specification){
    // Check if it's the one we are looking for
    if($specification->attributes()->urn->__toString() == $urn){
      $urn = $specification->value->__toString();
      // add specific class to allow overriding. Replace : by -
      $output = '<span class="pivot-ranking">';
      // prepare img title attribute
      $title_attribute = 'title="'._get_urn_documentation($urn).'"';
      // prepare img alt attribute
      $alt_attribute = 'alt="image '._get_urn_documentation($urn).'"';
      $height = 20;
      // Construct <img/> tag
      $img = '<img '.$title_attribute.' '.$alt_attribute.' class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$urn.';h='.$height;
      $img .= '"/>';
      
      $output .= $img.'</span>';
      
      return $output;
    }
  }
}

/**
 * Get number of offer for a specific urn
 * 
 * @param array $field_params with urn name
 * @return int Number of offer(s)
 */
function _get_number_of_offers($field_params){   
  $xml_query = _xml_query_construction($_SESSION['pivot']['query'], $field_params);

  $params['type'] = 'query';
  // Define number of offers per page
  $params['items_per_page'] = 1;
  // Define content details we want to receive from Pivot
  $params['content_details'] = ';content=1';

  // Get offers
  $xml_object = _pivot_request('offer-search', 1, $params, $xml_query);

  if($xml_object){
    $number_of_offers = $xml_object->attributes()->count->__toString();

    return $number_of_offers;
  }
  
  return 0;
}



function _get_address_one_line($offre){
  $address = '';
  if(isset($offre->adresse1->rue)){
   $address .= $offre->adresse1->rue->__toString();
  }
  if(isset($offre->adresse1->numero)){
   $address .=' '. $offre->adresse1->numero->__toString();
  }
  if(isset($offre->adresse1->cp)){
   $address .=', '. $offre->adresse1->cp->__toString();
  }
  if(isset($offre->adresse1->commune)){
   $address .=' '.$offre->adresse1->commune->value->__toString();
  }
  if(isset($offre->adresse1->pays)){
   $address .=', '.$offre->adresse1->pays->__toString();
  }
  
  return $address;
}

function _get_list_mdt(){
  $uri = 'https://pivotweb.tourismewallonie.be:8443/PivotWeb-3.1/thesaurus/tmdts;pretty=true;fmt=xml';
  
  $ssl_options=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  );  

  $xml_response = file_get_contents($uri, false, stream_context_create($ssl_options));
  $mdts = simplexml_load_string($xml_response);
  $mdt_list = '';

  foreach($mdts as $mdt){
    if(get_option('pivot_mdt') == $mdt->attributes()['idMdt']){
      $mdt_list .= '<option selected="selected" value="'.$mdt->attributes()['idMdt'].'">'.$mdt->value.'</option>';
    }else{
      $mdt_list .= '<option value="'.$mdt->attributes()['idMdt'].'">'.$mdt->value.'</option>';
    }
    
  }
  return $mdt_list;
}

function _get_list_typeofr($selected_id = NULL){
  $uri = 'https://pivotweb.tourismewallonie.be:8443/PivotWeb-3.1/thesaurus/typeofr;fmt=xml';
  
  $ssl_options=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  );  

  $xml_response = file_get_contents($uri, false, stream_context_create($ssl_options));
  $typeofr = simplexml_load_string($xml_response);
  $typeofr_list = '';

  foreach($typeofr as $type){
//    if(!in_array($type->attributes()['order'],$typeofr_list)){
      if($selected_id == $type->attributes()['order']){
        $typeofr_list .= '<option selected="selected" value="'.$type->attributes()['order'].'">'.$type->label->value.'</option>';
      }else{
        $typeofr_list .= '<option value="'.$type->label->value.'">'.$type->label->value.'</option>';
      }
//    }
    
  }
  return $typeofr_list;
}
/*
function _get_choice_options($type, $urn= NULL){
  // Construction of request uri
  $uri = 'https://pivotweb.tourismewallonie.be:8443/PivotWeb-3.1/thesaurus/typeofr/'.$type.';fmt=xml';

  $ssl_options=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  );  
$context = [
"ssl"=>[
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ]
];
libxml_set_streams_context(stream_context_create($context));

  $xml = file_get_contents($uri ,false, stream_context_create($ssl_options));;

$dom = new DOMDocument;
$dom->loadXML($xml);

foreach ($dom->getElementsByTagName('spec') as $element)
{
if ($element->getAttribute('urn') == "urn:fld:typeheb")
{
 print'<pre>';print_r($element->nodeValue);print'</pre>';
}
}
//$xpath = new DomXpath($dom);
//
//// traverse all results
//foreach ($xpath->query('//spec[@urn="urn:fld:class"]') as $rowNode) {
//    print '<pre>';print_r($rowNode);print '</pre>';
//}

$matches = $dom->getElementsByTagName('spec@urn=urn:fld:class');
foreach ($matches as $book) {
//    print '<pre>';print_r($book);print '</pre>';
}

//  $xml_response = file_get_contents($uri, false, stream_context_create($ssl_options));
//  $options = simplexml_load_string($xml_response);
//  $matches = $xml->xpath('//spec[@urn=urn:cat:classlab]');
//  print_r($matches);
//  $options = simplexml_load_string($xml_response);
  
  // Init vars
  $commune_list = array();
  $output = '<option selected disabled hidden><Select></option>';
  //$output = '';
  
  
//  foreach($options as $option){
//    if(!in_array(_get_translated_value($option->commune), $commune_list)){
//      $commune_list[] = _get_translated_value($option->commune);
//      $output .= '<option value="'._get_translated_value($option->commune).'">'._get_translated_value($option->commune).'</option>';
//    }
//  }
  return $output;
}*/

function _get_commune_from_pivot($type, $value){
  // Construction of request uri
  $uri = 'https://pivotweb.tourismewallonie.be:8443/PivotWeb-3.1/thesaurus/tins/'.$type.'/'.$value.';pretty=true;fmt=xml';

  $ssl_options=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  );  

  $xml_response = file_get_contents($uri, false, stream_context_create($ssl_options));
  $communes = simplexml_load_string($xml_response);
  
  // Init vars
  $commune_list = array();
  $output = '<option selected disabled hidden>Choisir une commune</option>';
  //$output = '';
  
  
  foreach($communes as $commune){
    if(!in_array(_get_translated_value($commune->commune), $commune_list)){
      $commune_list[] = _get_translated_value($commune->commune);
      $output .= '<option value="'._get_translated_value($commune->commune).'">'._get_translated_value($commune->commune).'</option>';
    }
  }
  return $output;
}

function _add_meta_data($offre){
  if(strpos(get_bloginfo('wpurl'), 'localhost') !== false) {
  }else{
    $bitly_params = array();
    $bitly_params['access_token'] = get_option('pivot_bitly');
    $bitly_params['domain'] = 'bit.ly';
    $bitly_params['longUrl'] = get_bloginfo('wpurl').'/'.$_SESSION['pivot']['path'].'/'.$offre->attributes()->codeCgt->__toString(); 
    $bitly_url = bitly_get('shorten', $bitly_params);
  }
  if(isset($offre) && s_object($offre)){
    echo  '<title>'.$offre->nom->__toString().' - CGT</title>'
         .'<meta property="og:url" content="'.(isset($bitly_url['data']['url'])?$bitly_url['data']['url']:'').'">'
         .'<meta property="og:type" content="article">'
         .'<meta property="og:title" content="'.$offre->nom->__toString().'">'
         .'<meta property="og:description" content="'.wp_strip_all_tags(_get_urn_value($offre, 'urn:fld:descmarket')).'">'
         .'<meta property="og:updated_time" content="'.$offre->attributes()->dateModification->__toString().'">'
  //       .'<meta property="og:image" content="'.$meta_datas['url'].'">'
  //       .'<meta property="og:image:width" content="'.$meta_datas['img_width'].'">'
  //       .'<meta property="og:image:height" content="'.$meta_datas['img_height'].'">'
         .'<meta name="twitter:card" content="summary_large_image">'
         .'<meta name="twitter:url" content="'.(isset($bitly_url['data']['url'])?$bitly_url['data']['url']:'').'">'
         .'<meta name="twitter:title" content="'.$offre->nom->__toString().'">'
         .'<meta property="article:published_time" content="'.$offre->attributes()->dateCreation->__toString().'">'
         .'<meta property="article:modified_time" content="'.$offre->attributes()->dateModification->__toString().'">';
  }
}

/**
 * Construct correct copyright for media.
 * The field  Copyright in Pivot accept everything
 * Legal form should be like "© Enterprise XYZ Year"
 * @param string $copyright from Pivot
 * @param date $date
 * @return string
 */
function _construct_media_copyright($copyright, $date){
  $date_explode = explode('/', $date);
  if((strpos($copyright, '©') !== false) || (strpos($copyright, '(c)') !== false)){
    return str_replace('copyright','',$copyright) . ' ' . $date_explode[2];
  }else{
    return '© '.str_replace('copyright','',$copyright) . ' ' . $date_explode[2];
  }
}

/**
 * 
 * @param Object $offre complete offer object
 * @param String $wanted_date urn of the wanted date (urn:fld:date:datedeb vs urn:fld:date:datedeb vs ...)
 */
function _get_event_date($offre, $wanted_date){
  foreach($offre->spec as $specification){
    if($specification->attributes()->urn->__toString() == 'urn:obj:date'){
      foreach($specification->spec as $dateObj){
        if($dateObj->attributes()->urn->__toString() == $wanted_date){
          $date = date("Y-m-d", strtotime(str_replace('/', '-', $dateObj->value->__toString())));
          return $date;
        }
      }
    }
  }
}

/**
 * Simple Templating function
 *
 * @param $name   - Name of the template file.
 * @param $args   - Associative array of variables to pass to the template file.
 * @return string - Output of the template file. Likely HTML.
 */
function _template($name, $args){
  $file = __DIR__ . '/templates/' . $name . '.php';

  // ensure the file exists
  if(!file_exists($file)){
    return '';
  }

  // buffer the output (including the file is "output")
  ob_start();
  include $file;
  
  return ob_get_clean();
}

/**
 * 
 * @param int $nb_offres
 * @return string HTML containing pagination
 */
function _add_pagination($nb_offres){
  /* Init pagination */
  $total = $nb_offres/12;

  // Check if we have more than 1 page!
  if($total > 1)  {
    // Get the current page
    $current_page = max(1, abs(get_query_var('paged')));
    // Set format
    $format = '/&paged=%#%';
    $pagination = paginate_links(array(
      'base'     => get_pagenum_link(1) . '%_%',
      'format'   => $format,
      'current'  => $current_page,
      'total'    => $total,
      'type'     => 'array'
    ));
    return _display_pagination($pagination);
  }
  // In case there is no need of pagination
  return;
}

/**
 * 
 * @param array $pagination
 * @return string HTML containing pagination
 */
function _display_pagination($pagination){
  $output = '<nav aria-label="Page navigation example">';
  $output .= '<ul class="pagination justify-content-center">';
  foreach($pagination as $key => $page_link){
    $output .= '<li class="page-item ';
    if(strpos($page_link, 'current') !== false){
      $output .=' active';
    }
    $output .='">'.str_replace("numbers","link",$page_link).'</li>';
  }
  $output .= '</ul>';
  $output .= '</nav>';
      
  return $output;
}

/**
 * Show warning message with close button based on Bootstrap framework
 * @param string $text
 * @return string
 */
function _show_warning($text, $severity = 'warning'){
  $output = '<div class="alert alert-'.$severity.' alert-dismissible fade show" role="alert">'
          .   $text
          .   '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
          .     '<span aria-hidden="true">&times;</span>'
          .   '</button>'
          . '</div>';
  
  return $output;
}

/**
 * Show notice message on wordpress admin pages
 * Available severity (updated, error, update-nag)
 * @param string $text
 * @return string
 */
function _show_admin_notice($text, $severity = 'error'){
  $output = '<div class="'.$severity.' notice">'
          .   '<p>'.$text.'</p>'
          . '</div>';
  
  return $output;
}