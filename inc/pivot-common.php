<?php

/**
 * Return a specific urn value
 * 
 * @param Object $offre Complete offer Object
 * @param string $urn_name urn name we are looking for
 * @param string $lang current language interface
 * @return string Return string value of a urn (field)
 */
function _get_urn_value($offre, $urn_name){
  $lang = substr(get_locale(), 0, 2 );
  // Construct URN with language code
  if($lang && $lang != 'fr'){
    $lang_urn_name = $lang.':'.$urn_name;
  }
  
  // Search for specific URN
  foreach ($offre->spec as $spec){
    // In case language is set and different of 'fr'
    if($lang && $lang != 'fr'){
      // Reminder of default value (fr)
      if($spec->attributes()->__toString() == $urn_name){
        $french_value = $spec->value->__toString();
      }
      // Get translated value and return it
      if($spec->attributes()->__toString() == $lang_urn_name){
        return $spec->value->__toString();
      }
    }else{
      // French case, return it
      if($spec->attributes()->__toString() == $urn_name){
        return $spec->value->__toString();
      }else{
        // Specific case where we need offer title in french
        if($urn_name == 'urn:fld:nomofr'){
          return $offre->nom->__toString();
        }
      }
    }
  }
  // In case value is not translated
  if(isset($french_value)){
    return $french_value;
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

function _get_event_categories(){
  
  $uri = get_option('pivot_uri').'thesaurus/typeofr/9/urn:cat:classlab:classif;pretty=true;fmt=xml;';

  $ssl_options=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  );

  $xml_response = file_get_contents($uri, false, stream_context_create($ssl_options));
  $event_categories = simplexml_load_string($xml_response);
  print '<pre>';print_r($event_categories);print '</pre>';
  
  // une methode en accès direct pour arriver et boucler sur la spec "urn:fld:catevt"
  // Si on veut aller plus loin et essayer de rendre la fonction utilisable dans d'autre contexte, 
  // faudrait imbriquer 2 boucles jusqu'à arriver à la spec voulue ("urn:fld:catevt" dans ce cas-ci)
  foreach($event_categories->spec->spec[1] as $category){
    if($category->attributes()->urn){
      /* L'urn est un attribut voilà comment y accéder.
       * 
       * $category->attributes()->urn
       */
      /* le label en français, pcq je sais que la valeur FR en tjs en première.
       * Pour être propre faudrait faire une boucle sur les label jusqu'à trouver l'attribut lang = FR
       * 
       * $category->label[0]->value->__toString()
       */
      
      // print '<pre>';print_r($category);print '</pre>';

      print 'Option URN: '.$category->attributes()->urn.' et son label fr'.$category->label[0]->value->__toString().'<br>';
    }
  }
}

/**
 * Call the "thesaurus" web service to get the translated documentation of specific URN
 * 
 * @param string $urn Name of the URN = field ID
 * @return string urn documentation translated
 */
function _get_urn_documentation($urn){
  $params['urn_name'] = $urn;
  $xml_object = _pivot_request('thesaurus', 0, $params);

  foreach ($xml_object->spec as $item){
    return _get_translated_value($item);
  }
}

/**
 * Call the "thesaurus" web service to get the documentation Object of a specific URN
 * 
 * @param string $urn Name of the URN = field ID
 * @return Object documentation of the urn
 */
function _get_urn_documentation_full_spec($urn){
  $params['urn_name'] = $urn;
  $xml_object = _pivot_request('thesaurus', 0, $params);

  return $xml_object;
}

/**
 * return urn in default language.
 * In case you have nl:urn:...
 * @param string $urn URN name
 * @return string
 */
function _get_urn_default_language($urn){
  if(substr($urn, 0, 4) == 'urn:'){
    return $urn;
  }else{
    return substr($urn, 3);
  }
}

/**
 * Will return a span with an image inside with all parameters you have set
 * 
 * Go there to see which URN has a picto 
 * http://pivot.tourismewallonie.be/index.php/9-pivot-gest-pc/218-liste-des-pictogrammes
 * 
 * @param Object $offre the complete offer Object
 * @param string $urn Name of the URN = field ID
 * @param int $height set height in px like (20)
 * @param string $color has to be RGB hexa color code like FFFFFF for black (can be '')
 * @param boolean $original set to true if you want to original color of the picto
 * @return string
 */
function _search_specific_urn_img($offre, $urn, $height, $color = '', $original = FALSE){
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
      $img = '<img '.$title_attribute.' '.$alt_attribute.' class="pivot-picto" src="'.get_option('pivot_uri').'img/'.$urn.';h='.$height;
      if(!empty($color) && $color != ''){
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

/**
 * Will return the urn Label
 * 
 * @param Object $offre the complete offer Object
 * @param string $urn Name of the URN = field ID
 * @return string
 */
function _search_specific_urn($offre, $urn){
  // Loop on each specific field
  foreach($offre->spec as $spec){
    // Check if it's the one we are looking for
    if($spec->attributes()->urn->__toString() == $urn){
      $output = $spec->value->__toString();

      return $output;
    }
  }
}

/**
 * Will return a span with image of the ranking picto inside.
 * 
 * @param Object $offre the complete offer Object
 * @param string $color color in hexadecimal format without # (null by default)
 * @return string
 */
function _get_ranking_picto($offre, $color = null, $height = 20){
  $urn = 'urn:fld:class';
  // Loop on each specific field
  foreach($offre->spec as $specification){
    // Check if it's the one we are looking for
    if($specification->attributes()->urn->__toString() == $urn){
      $urn = $specification->value->__toString();
      $useless = array('urn:val:class:cessation', 'urn:val:class:ecc', 'urn:val:class:echue', 'urn:val:class:nc');
      if(!in_array($urn, $useless)){
        // add specific class to allow overriding. Replace : by -
        $output = '<span class="pivot-ranking">';
        $urn_doc = _get_urn_documentation($urn);
        // prepare img title attribute
        $title_attribute = 'title="'.$urn_doc.'"';
        // prepare img alt attribute
        $alt_attribute = 'alt="image '.$urn_doc.'"';
        // Construct <img/> tag
        $img = '<img height="'.$height.'" '.$title_attribute.' '.$alt_attribute.' class="pivot-picto" src="'.get_option('pivot_uri').'img/'.$urn.(($color)?';c='.$color:'').';h='.$height;
        $img .= '"/>';

        $output .= $img.'</span>';

        return $output;
      }else{
        return '';
      }
    }
  }
}

/**
 * Get number of offer for a specific urn
 * 
 * @param array $field_params with urn name
 * @return int Number of offer(s)
 */
function _get_number_of_offers($field_params, $page_id){   
  $xml_query = _xml_query_construction($_SESSION['pivot'][$page_id]['query'], $field_params);

  $params['type'] = 'query';
  // Define number of offers per page
  $params['items_per_page'] = 1;
  // Define content details we want to receive from Pivot
  $params['content_details'] = ';content=1';

  // Get offers
  $xml_object = _pivot_request('offer-init-list', 1, $params, $xml_query);

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
  $uri = get_option('pivot_uri').'thesaurus/tmdts;pretty=true;fmt=xml';

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
  $uri = get_option('pivot_uri').'thesaurus/typeofr;fmt=xml';

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
    if($selected_id == $type->attributes()['order']){
      $typeofr_list .= '<option selected="selected" value="'.$type->attributes()['order'].'">'.$type->label->value.'</option>';
    }else{
      $typeofr_list .= '<option value="'.$type->attributes()['order'].'">'.$type->label->value.'</option>';
    }
  }
  
  if($selected_id == 'custom'){
    $typeofr_list .= '<option selected="selected" value="custom">Custom (hors Pivot) spécialement utilisé pour les vignettes en shortcode</option>';
  }else{
    $typeofr_list .= '<option value="custom">Custom (hors Pivot) spécialement utilisé pour les vignettes en shortcode</option>';
  }
  return $typeofr_list;
}

/**
 * 
 * @param string $type 
 * @param string $value 
 * @param string $selected_value = selected option
 * @return string = list of HTML option
 */
function _get_commune_from_pivot($type, $value, $selected_value = NULL){
  // Construction of request uri
  $uri = get_option('pivot_uri').'thesaurus/tins/'.$type.'/'.$value.';pretty=true;fmt=xml';
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
  $output = '<option value="all" '.(isset($selected_value)?'':'selected').' >'.esc_html__('Choose a town', 'pivot').'</option>';

  // Construct list
  foreach($communes as $commune){
    $commune_translated = _get_translated_value($commune->commune);
    if(!in_array($commune_translated, $commune_list)){
      $commune_list[] = $commune_translated;
    }
  }
  // Sort list
  asort($commune_list);

  // Construct HTML options
  foreach($commune_list as $commune){
    $output .= '<option value="'.$commune.'" ';
    if($selected_value == $commune){
      $output .= 'selected';
    }
    $output .= '>'.$commune.'</option>';
  }
  return $output;
}

function _get_offer_types($edit_page= null){
  $types = pivot_get_offer_type();

  // Init vars
  $types_list = array();
  $output = '<option selected disabled hidden>'.esc_html__('Choose a type', 'pivot').'</option>';

  foreach($types as $type){
    if(!in_array($type->parent, $types_list)){
      $types_list[] = $type->parent;
      $output .= '<option ';
      if(isset($edit_page) && $edit_page->type == $type->parent){
         $output .='selected="selected" ';
      }
      $output .= 'value="'.$type->parent.'">'.$type->parent.'</option>';
    }
  }
  return $output;
}

/**
 * Override page title with offer name + site name
 * Add metadata for twitter and og (facebook, google, ...)
 * @param Object $offre Complete Offer object
 * @param String $path path to join the offer
 */
function _overide_yoast_seo_meta_data($offre, $path){
  global $offre_meta_data;
  if(isset($offre) && is_object($offre)){
    $url = get_bloginfo('wpurl').'/'.$path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString();
    $offre_meta_data['title'] = _get_urn_value($offre, 'urn:fld:nomofr');
    $offre_meta_data['type'] = 'article';
    $offre_meta_data['url'] = $url;
    $offre_meta_data['description'] = wp_strip_all_tags(_get_urn_value($offre, 'urn:fld:descmarket'));
    $offre_meta_data['updated_time'] = $offre->attributes()->dateModification->__toString();
    $offre_meta_data['published_time'] = $offre->attributes()->dateCreation->__toString();
    $offre_meta_data['modified_time'] = $offre->attributes()->dateModification->__toString();
//    $offre_meta_data['image'] = ;
  }
}

/**
 * Override page title with offer name + site name
 * Add metadata for twitter and og (facebook, google, ...)
 * @param Object $offre Complete Offer object
 * @param String $path path to join the offer
 */
function _add_meta_data($offre, $path, $default_image=null){
  $url = get_bloginfo('wpurl').'/'.$path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString();
  if(isset($offre) && is_object($offre)){
//    $descp = preg_replace("/[^A-Za-z0-9 ]/", '', wp_strip_all_tags(_get_urn_value($offre, 'urn:fld:descmarket')));
    $descp =  wp_strip_all_tags( get_the_excerpt(), true );
    return '<meta name="description" content="'.((strlen($descp)>160)?substr($descp, 0, strpos($descp, ' ', 160)):$descp).'"/>'
         .'<meta property="og:url" content="'.$url.'">'
         .'<meta property="og:type" content="article">'
         .'<meta property="og:title" content="'._get_urn_value($offre, 'urn:fld:nomofr').'">'
         .'<meta property="og:description" content="'.substr($descp, 0, strpos($descp, ' ', 160)).'">'
         .'<meta property="og:updated_time" content="'.$offre->attributes()->dateModification->__toString().'">'
         .'<meta property="og:image" content="'.$default_image.'">'
         .'<meta name="twitter:card" content="summary_large_image">'
         .'<meta name="twitter:url" content="'.$url.'">'
         .'<meta name="twitter:title" content="'._get_urn_value($offre, 'urn:fld:nomofr').'">'
         .'<meta property="article:published_time" content="'.$offre->attributes()->dateCreation->__toString().'">'
         .'<meta property="article:modified_time" content="'.$offre->attributes()->dateModification->__toString().'">';
  }
}

/**
 * Override page title with offer name + site name
 * Add metadata for twitter and og (facebook, google, ...)
 * @param Object $offre Complete Offer object
 * @param String $path path to join the offer
 */
function _add_meta_data_list_page($pivot_page){
  if(isset($pivot_page) && is_object($pivot_page)){
    $url = get_bloginfo('wpurl').'/'.$pivot_page->path;
//    $descp = preg_replace("/[^A-Za-z0-9 ]/", '', wp_strip_all_tags(_get_urn_value($offre, 'urn:fld:descmarket')));
    $descp =  wp_strip_all_tags($pivot_page->description, true );
    return '<meta name="description" content="'.((strlen($descp)>160)?substr($descp, 0, strpos($descp, ' ', 160)):$descp).'"/>'
         .'<meta property="og:url" content="'.$url.'">'
         .'<meta property="og:type" content="page">'
         .'<meta property="og:title" content="'.__($pivot_page->title, 'pivot').'">'
         .'<meta property="og:image" content="'.$pivot_page->image.'">'
         .'<meta name="twitter:card" content="summary_large_image">'
         .'<meta name="twitter:url" content="'.$url.'">'
         .'<meta name="twitter:title" content="'.__($pivot_page->title, 'pivot').'">';
  }
}

/**
 * Create alternate hreflang for each page language
 * For repertory language format only and assuming WPML is active.
 * mydomainname.com/ = NL
 * mydomainname.com/nl/ = NL
 * @return string
 */
function _pivot_create_alternate_link(){
  global $wp;
  $output = '';
  $languages = icl_get_languages();
  $current_url = home_url($wp->request);
  foreach($languages as $lang){
    $output .= '<link rel="alternate" hreflang="'.$lang['language_code'].'" href="'.apply_filters('wpml_permalink', $current_url, $lang['language_code']).'">';
  }
  return $output;
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
  if(!empty($copyright) && !empty($date)){
    $date_explode = explode('/', $date);
    if((strpos($copyright, '©') !== false) || (strpos($copyright, '(c)') !== false)){
      return str_replace('copyright','',$copyright) . ' ' . (isset($date_explode[2])?$date_explode[2]:'');
    }else{
      return '© '.str_replace('copyright','',$copyright) . ' ' . $date_explode[2];
    }
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
 * Define how many offers we display in the page depending on number of column
 * @param int $nbcol
 * @return int
 */
function _define_nb_offers_per_page($nbcol){
  switch($nbcol){
    case 2:
    case 3:
    case 4:
      $offers_per_page = 12;
      break;
    case 5:
      $offers_per_page = 15;
      break;
    case 6:
      $offers_per_page = 18;
      break;
    default:
      $offers_per_page = 12;
      break;
  }
  return $offers_per_page;
}
/**
 * 
 * @param int $nb_offres
 * @return string HTML containing pagination
 */
function _add_pagination($nb_offres, $nbcol){
  /* Init pagination */
  $total = ceil($nb_offres/_define_nb_offers_per_page($nbcol));

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
  $output = '<div class="'.$severity.' notice notice-info">'
          .   '<p>'.$text.'</p>'
          . '</div>';

  return $output;
}

function _get_path(){
  global $wp_query;
  $path = $_SERVER['REQUEST_URI'];
  if((strpos($path, "paged=")) !== FALSE){
    if((strpos($path, key($wp_query->query)) !== FALSE)){
      $path = key($wp_query->query);
    }
  }else{
    global $post;
    $path = $post->post_name;
  }
  return $path;
}

function _get_offer_details($offer_id = NULL, $details = 3){
  if($offer_id){
    $params['offer_code'] = $offer_id;
    $params['type'] = 'offer';
    $xml_object = _pivot_request('offer-details', $details, $params);
    $offre = $xml_object->offre;
  }else{
    $path = $_SERVER['REQUEST_URI'];
    if((strpos($path, "&type=")) !== FALSE){
      if (preg_match('/\/(.*?)&type=/', $path, $match) == 1) {
        $params['offer_code'] = substr($match[1], strrpos($match[1], '/' )+1);
        $params['type'] = 'offer';
        $xml_object = _pivot_request('offer-details', $details, $params);
        $offre = $xml_object->offre;
        $url = get_bloginfo('wpurl').((substr(get_locale(), 0, 2 )=='fr')?'':'/'.substr(get_locale(), 0, 2 )).'/'.$offre->path.'/'.$params['offer_code'].'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString();
        pivot_create_fake_post(_get_urn_value($offre, 'urn:fld:nomofr'), $url, _get_urn_value($offre, 'urn:fld:descmarket'), 'post');
      }
    }
  }
  
  return $offre;
}

function _check_is_offer_active($offre){
  // Check if offer is publishable, if not redirect to 404 page.
  if($offre->estActive != 30){
    $warning_text = __("This offer doesn't exist or is not publishable anymore", "pivot");
    echo _show_warning($warning_text);
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
  }
}

/**
 * This function will construct the filter array
 * @param array $field_params
 * @param Object $filter
 * @param String $key
 * @param int $page_id
 */
function _construct_filters_array($field_params,$filter, $key = 'shortcode', $page_id = NULL){
  if($filter->urn == 'urn:fld:adrcom' && $_SESSION['pivot']['filters'][$page_id][$key] == 'all'){
    return $field_params;
  }else{
    switch($filter->type){
      case 'Type':
        $field_params['filters']['urn:fld:typeofr']['name'] = 'urn:fld:typeofr';
        $field_params['filters']['urn:fld:typeofr']['operator'] = $filter->operator;
        $field_params['filters']['urn:fld:typeofr']['searched_value'][] = $filter->filter_name;
        break;
      case 'Value':
          $parent_urn = preg_replace("'\:.*?:'" ,':fld:',substr($filter->urn, 0, strripos($filter->urn, ':')));
          $field_params['filters'][$parent_urn]['name'] = $parent_urn;
          $field_params['filters'][$parent_urn]['operator'] = $filter->operator;
          $field_params['filters'][$parent_urn]['searched_value'][] = $filter->urn;
        break;
      default:
        $field_params['filters'][$key]['name'] = $filter->urn;
        $field_params['filters'][$key]['operator'] = $filter->operator;
        break;
    }

    // If operator is no "exist", we need the field comparison
    if($filter->operator != 'exist' && (!isset($parent_urn) || $parent_urn == '') && !isset($field_params['filters']['urn:fld:typeofr'])){
      // Set value by default
      if(!empty($_SESSION['pivot']['filters'][$page_id])){
        $value = $_SESSION['pivot']['filters'][$page_id][$key];
      }else{
        $value = $filter->filter_name;
      }
      // If the filter is a Date
      if($filter->type === 'Date'){
        // Override value with the requested date format
        $value = date("d/m/Y", strtotime($value));
        $field_params['filters'][$key]['searched_value'][] = $value;
      }else{
        $field_params['filters'][$key]['searched_value'][] = $value;
      }
    }
    if($filter->operator == 'exist'){
      $field_params['filters'][$key]['operator'] = 'equal';
      $field_params['filters'][$key]['searched_value'][] = 'true';
    }
    return $field_params;
  }
}

function _get_urnValue_translated($offre, $specification){
  switch($specification->type->__toString()){
    case 'Boolean':
      $output = _get_urn_documentation($specification->attributes()->__toString());
      break;
    case 'Currency':
      $output = $specification->value->__toString().' €';
      break;
    case 'Choice':
      $output = _get_urn_documentation($specification->value->__toString());
      break;
    case 'TextML':
    case 'FirstUpperStringML':
      $output = _get_urn_value($offre, $specification->attributes()->__toString());
      break;
    default:
      $output = $specification->value->__toString();
      break;
  }
  return $output;
}

/**
 * Check if a key exist in a multidimensional array
 * @param array $array
 * @param type $key
 * @return boolean
 */
function _multiKeyExists( Array $array, $key ) {
    if (array_key_exists($key, $array)) {
        return true;
    }
    foreach ($array as $k=>$v) {
        if (!is_array($v)) {
            continue;
        }
        if (array_key_exists($key, $v)) {
            return true;
        }
    }
    return false;
}