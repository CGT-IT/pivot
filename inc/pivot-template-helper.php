<?php 

/**
 * Return a HTML section with fields inside a group of info (category or sub category) 
 * like urn:cat:eqpsrv or urn:cat:tarif or urn:cat:visite or urn:cat:accueil:langpar, ...
 * @param Object $offre complete offer object
 * @param string $urnCat URN of type Category like urn:cat:eqpsrv or urn:cat:tarif or urn:cat:visite or urn:cat:accueil:langpar, ...
 * @param string $title H5 title  above the section
 * @param string $faIcon Font Awesome icon class (without .)
 * @param boolean $urnSubCat to true if you want to display field of a sub category or false (by default) if you want to display the entire category
 * @see https://fontawesome.bootstrapcheatsheets.com/
 * @return string
 */
function _add_section($offre, $urnCat, $title, $faIcon='', $urnSubCat=0){
  $excludedUrn = array('urn:cat:accueil:attest','urn:val:attestincendie:asi','urn:val:attestincendie:acs','urn:val:attestincendie:defaut','urn:fld:attestincendie:dateech','urn:fld:dateech', 'urn:fld:idaccessi', 'urn:fld:accessi', 'urn:fld:accessi:url', 'urn:fld:accessi:perfautroul', 'urn:fld:accessi:permardif', 'urn:fld:accessi:perave', 'urn:fld:accessi:permalvoy', 'urn:fld:accessi:persou', 'urn:fld:accessi:permalent', 'urn:fld:accessi:perdifcomp');
  // Define if sub category or category
  $cat_or_subcat = ($urnSubCat?'urnSubCat':'urnCat');
  // Get 2 letter language code
  $lang = substr(get_locale(), 0, 2 );
  // Get cat name taking word after last :
  $cat = substr($urnCat, strrpos($urnCat, ':') + 1);
  // Init var, will be used to check if there is well content or not
  $content = '';

  $open_balise = '<h5 class="lis-font-weight-500"><i class="fas fas-align-right pr-2 f0fc '.$faIcon.'"></i>'. __($title, 'pivot') .'</h5>'
           .'<section class="pivot-'.$cat.' card lis-brd-light mb-4">'
           .'<div class="card-body p-4">'
           .'<ul class="list-unstyled lis-line-height-2 m-0">';
  foreach($offre->spec as $specification){
    // If iteration is on an URN of the cat or subcat we are looking for
    if($specification->$cat_or_subcat->__toString() == $urnCat && !empty(_get_urn_documentation($specification->attributes()->urn->__toString()))){
      if(!in_array($specification->attributes()->urn->__toString(),$excludedUrn)){
        // Case FR
        if($lang == 'fr' && 'urn' == substr($specification->attributes()->urn->__toString(), 0, 3)){
          $content .= '<li class="p-1 '. str_replace(":", "-", $specification->attributes()->urn->__toString()) .'">';
          if($specification->type != 'Boolean'){
            $content .= '<span class="'.$cat.'-label">'. _get_urn_documentation($specification->attributes()->urn->__toString()) .'</span>';
          }
          $content .=    '<span class="'.$cat.'-value"> ';
          if($specification->attributes()->urn->__toString() == 'urn:fld:signal'){
            $content .= '<img class="pivot-img" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/'._get_urn_value($offre, 'urn:fld:signal').';w=20"/>';
          }else{
            $content .= _get_urnValue_translated($offre, $specification);
          }
          $content  .=  '</span> '
                    .    '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'. $specification->attributes()->urn->__toString() .';h=16"/>'
                    .  '</li>';
        }else{
          // Case other language than french
          if($lang != 'fr'){
          $content .= '<li class="p-1 '. str_replace(":", "-", $specification->attributes()->urn->__toString()) .'">';
          if($specification->type != 'Boolean'){
            $content .=    '<span class="'.$cat.'-label">'. _get_urn_documentation($specification->attributes()->urn->__toString()) .'</span>';
          }
          $content .=     '<span class="'.$cat.'-value"> '
                  .      _get_urnValue_translated($offre, $specification)
                  .    '</span> '
                  .    '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'. $specification->attributes()->urn->__toString() .';h=16"/>'
                  .  '</li>';
           }
        }
      }
    }
  }
  $close_balise = '</ul></div></section>';

  // Check if there is well something to display
  if($content != ''){
    $output = $open_balise.$content.$close_balise;
  }else{
    $output = '';
  }
  
  return $output;
}

function _add_section_themes($offre){
  $excludedUrn = array('urn:fld:dateech', 'urn:fld:class');
  // Init var, will be used to check if there is well content or not
  $content = '';

  foreach($offre->spec as $specification){
    if(!in_array($specification->attributes()->urn->__toString(),$excludedUrn)){
      if($specification->urnCat->__toString() == 'urn:cat:classlab'){
        $content .= '<span class="ml-1 badge badge-dark"> '
                 .  _get_urnValue_translated($offre, $specification)
                 .  '<span class="m-1 badge badge-light"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'. $specification->attributes()->urn->__toString() .';h=16"/></span>'
                 .  '</span>';
      }
    }
  }
  // Check if there is well something to display
  if($content != ''){
    $output = $content;
  }
  
  return $output;
}

/**
 * Return a HTML section with facebook and twitter share link
 * @param Object $offre
 * @return string
 */
function _add_section_share($offre){
  $url_offer_details = get_bloginfo('wpurl').'/details/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString();
  /*$output = '<div class="fb-share-button" 
    data-href="'.$url_offer_details.'" 
    data-layout="button_count">cccc
  </div><div class="pivot-offer-share">'
          .'<img class="pivot-picto fb-share-button" data-href="'.$url_offer_details.'" data-layout="button_count" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlfacebook;h=35" alt="Facebook '.esc_attr__('Share button').'" title="Facebook '.esc_attr__('Share button').'"/>'
//          .   '<span class="pr-3"><a class="social-icon" href="https://www.facebook.com/sharer.php?u='.$url_offer_details.'&amp;t='._get_urn_value($offre, 'urn:fld:nomofr').'" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlfacebook;h=35" alt="Facebook '.esc_attr__('Share button').'" title="Facebook '.esc_attr__('Share button').'"/></a></span>'
          .   '<span><a class="social-icon" href="https://twitter.com/share?text='._get_urn_value($offre, 'urn:fld:nomofr').'&amp;url='.$url_offer_details.'" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urltwitter;h=35" alt="Twitter '.esc_attr__('Share button').'" title="Twitter '.esc_attr__('Share button').'"/></a></span>'
          . '</div>';*/
  $output = '<div class="pivot-offer-share">'
          .   '<span class="pr-3"><a class="social-icon" href="https://www.facebook.com/sharer.php?u='.$url_offer_details.'&amp;t='._get_urn_value($offre, 'urn:fld:nomofr').'" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlfacebook;h=35" alt="Facebook '.esc_attr__('Share button').'" title="Facebook '.esc_attr__('Share button').'"/></a></span>'
          .   '<span><a class="social-icon" href="https://twitter.com/share?text='._get_urn_value($offre, 'urn:fld:nomofr').'&amp;url='.$url_offer_details.'" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urltwitter;h=35" alt="Twitter '.esc_attr__('Share button').'" title="Twitter '.esc_attr__('Share button').'"/></a></span>'
          . '</div>';
  
  return $output;
}

/**
 * Return a HTML section with all contact infos
 * @param Object $offre complete offer object
 * @return string
 */
function _add_section_contact($offre){
  $output = '<h5 class="lis-font-weight-500"><i class="fas fas-align-right pr-2 fa-id-card"></i>'.esc_html('Contact', 'pivot').'</h5>'
          . '<section class="pivot-contacts card lis-brd-light wow fadeInUp mb-4">'
          .   '<div class="card-body p-4">'
          .     '<h6 class="pivo-title">'._get_urn_value($offre, 'urn:fld:nomofr').'</h6>'
          .     '<ul class="list-unstyled lis-line-height-2 m-0">';
  foreach($offre->spec as $specification){
    if($specification->urnCat->__toString() == 'urn:cat:moycom' && $specification->urnSubCat->__toString() != 'urn:cat:moycom:sitereservation'){
      $output .= '<li>'
              . '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'.$specification->attributes()->urn->__toString().';h=16"/>';
      switch ($specification->type->__toString()){
        case 'EMail':
          $output .= '<a class="'.$specification->type->__toString().'" href="mailto:'.$specification->value->__toString().'">'.$specification->value->__toString().'</a>';
          break;
        case 'URL':
          $output .= '<a class="'.$specification->type->__toString().'" target="_blank" href="'.esc_url($specification->value->__toString()).'">'.esc_url($specification->value->__toString()).'</a>';
          break;
        case 'GSM':
          $output .= '<a class="'.$specification->type->__toString().'" href="tel:'.$specification->value->__toString().'">'.$specification->value->__toString().'</a>';
          break;
        case 'Phone':
          $output .= '<a class="'.$specification->type->__toString().'" href="tel:'.$specification->value->__toString().'">'.$specification->value->__toString().'</a>';
          break;
        default:
          if (esc_url($specification->value->__toString())){
            $output .= '<a class="'.$specification->type->__toString().'" target="_blank" href="'.esc_url($specification->value->__toString()).'">'.esc_url($specification->value->__toString()).'</a>';
          }else{
            $output .= $specification->value->__toString();
          }
          break;
      }

      $output .= '</li>';
    }
  }
  $output .= '</ul>';
            
  $output .= '<ul class="adr list-unstyled lis-line-height-2 m-0">'
          .    '<li class="street-address"><i class="fas fa-map"></i> '.$offre->adresse1->rue->__toString().', '.$offre->adresse1->numero->__toString().'</li>'
          .      '<span class="postal-code">'.$offre->adresse1->cp->__toString().' </span>'
          .      '<span class="locality">'.(isset($offre->adresse1->localite)?$offre->adresse1->localite->value->__toString():'').'</span>'
          .    '<li class="country-name">'.$offre->adresse1->pays->__toString().'</li>'
          .    '<li class="pivot-latitude d-none">'.$offre->adresse1->latitude->__toString().'</li>'
          .    '<li class="pivot-longitude d-none">'.$offre->adresse1->longitude->__toString().'</li>'
          .  '</ul></div></section>';
  
  return $output;
}

/**
 * Return a HTML section with all booking infos
 * @param Object $offre complete offer object
 * @return string
 */
function _add_section_booking($offre){
  $booking = FALSE;
  $output = '<h5 class="lis-font-weight-500"><i class="fas fas-align-right pr-2 fa-credit-card"></i>'.esc_html('Booking', 'pivot').'</h5>'
          . '<section class="pivot-booking card lis-brd-light wow fadeInUp mb-4">'
          .   '<div class="card-body p-4">'
          .     '<ul class="list-unstyled list-inline lis-line-height-2 m-0">';
  foreach($offre->spec as $specification){
    if($specification->urnSubCat->__toString() == 'urn:cat:moycom:sitereservation'){
      $booking = TRUE;
      $output .= '<li class="list-inline-item pr-3">';
      if (esc_url($specification->value->__toString())){
        $output .= '<a title="'._get_urn_documentation($specification->attributes()->urn->__toString()).'" class="'.$specification->type->__toString().'" target="_blank" href="'.esc_url($specification->value->__toString()).'"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'._get_urn_default_language($specification->attributes()->urn->__toString()).';h=40"/></a>';
      }
      $output .= '</li>';
    }
  }
  $output .= '</ul>';
            
  $output .= '</div></section>';
  
  if($booking === TRUE){
    return $output;
  }else{
    return '';
  }
}
/**
 * Return a HTML section with all booking infos
 * @param Object $offre complete offer object
 * @return string
 */
function _add_section_booking2($offre){
  $booking = FALSE;
  $output = '<h5 class="lis-font-weight-500"><i class="fas fas-align-right pr-2 fa-credit-card"></i>'.esc_html('Réservation', 'pivot').'</h5>'
          . '<section class="pivot-booking card lis-brd-light wow fadeInUp mb-4">'
          .   '<div class="card-body p-4">';
  
  foreach($offre->spec as $specification){
    if($specification->urnSubCat->__toString() == 'urn:cat:moycom:sitereservation'){
      $booking = TRUE;
      if(esc_url($specification->value->__toString()) && $specification->attributes()->urn->__toString() == 'urn:fld:urlresa:default'){
        $output .= '<a target="_blank" href="'.$specification->value->__toString().'" class="elementor-button-link elementor-button elementor-size-md">'.esc_html('Réserver', 'pivot').'</a>';
      }
    }
  }  
            
  $output .= '</div></section>';
  
  if($booking === TRUE){
    return $output;
  }else{
    return '';
  }
}

/**
 * Return a carousel of linked offers
 * @param Object $offre complete offer object
 * @return string
 */
function _add_section_linked_offers($offre){
  $output = '<h5 class="lis-font-weight-500"><i class="fas fas-align-right pr-2 fa-paperclip"></i>'.__('Linked offers', 'pivot').'</h5>'
          .  '<div class="carousel slide" data-ride="carousel" id="quote-carousel">'
                // Carousel Slides
          .     '<div class="carousel-inner">';
  $i= 0;
  foreach($offre->relOffre as $relation){
    // The linked offer shouldn't be a contact or a media
    if(!(in_array($relation->offre->typeOffre->attributes()->idTypeOffre->__toString(), array('268', '23')))){
      // The linked offer type should exist in "pivot offer type" otherwise no template will be used
      if(pivot_get_offer_type($relation->offre->typeOffre->attributes()->idTypeOffre->__toString())){
        $url = get_bloginfo('wpurl').'/details/'.$relation->offre->attributes()->codeCgt->__toString().'&type='.$relation->offre->typeOffre->attributes()->idTypeOffre->__toString();
        $output .= '<div class="carousel-item ';
        if($i++ == 0)
          $output .= 'active';
        $output .= '"><blockquote>'
                .      '<a class="text-dark" title="'.esc_attr('Link to', 'pivot').' '._get_urn_value($relation->offre, 'urn:fld:nomofr').'" href="'.$url.'">'
                .        '<div class="row">'
                .          '<div class="col-sm-3 text-center">'
                .            '<img class="pivot-img zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/'.$relation->offre->attributes()->codeCgt->__toString().';w=256;h=170"/>'
                .          '</div>'
                .          '<div class="col-sm-9">'
                .            '<p>'._get_urn_value($relation->offre, 'urn:fld:descmarket').'</p>'
                .            '<small><b>'._get_urn_value($relation->offre, 'urn:fld:nomofr').'</b></small>'
                .          '</div>'
                .        '</div>'
                .      '</a>'
                .    '</blockquote>'
                .  '</div>';
      }
    }
  }

  // Bottom Carousel Indicators
  $output .= '<ol class="carousel-indicators">';
  for($x = 0; $x < $i; $x++){
    $output .= '<li data-target="#quote-carousel" data-slide-to="'.$x.'" ';
    if($x == 0)
      $output .= 'class="active"';
    $output .= '></li>';
  }
  $output .= '</ol></div>';

  // Carousel Buttons Next/Prev
  $output .= '<a class="carousel-control-prev" href="#quote-carousel" role="button" data-slide="prev">'
            . '<i class="fa fa-chevron-left"></i>'
            .'</a>'
            .'<a class="carousel-control-next" href="#quote-carousel" role="button" data-slide="next">'
            . '<i class="fa fa-chevron-right"></i>'
            .'</a></div>';
  
  // There is minimum one offer
  if($i > 0){
    return $output;
  }
  
  return '';
}

function _add_section_mice_rooms($offre, $title, $faIcon=''){
  $output = '';

  $open_balise = '<link href="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.css" rel="stylesheet">'
                . '<script src="https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.js"></script>'
                . '<h5 class="lis-font-weight-500"><i class="fas fas-align-right pr-2 f0fc '.$faIcon.'"></i>'. __($title, 'pivot') .'</h5>'
                . '<div class="table-responsive-xl">'
                  . '<table  data-toggle="table" data-sort-name="name" data-sort-order="asc" data-pagination="true" data-page-size="25" data-toggle="table" data-search="true"class="table table-striped">';
                    
  $close_balise = '</tbody></table></div>';
  // Construction of rooms array to be able to check if an attribute is not present
  $i = 0;
  foreach($offre->relOffre as $relation){
    if((in_array($relation->offre->typeOffre->attributes()->idTypeOffre->__toString(), array('24')))){
      $offer_id = $relation->offre->attributes()->codeCgt->__toString();
      $rooms[$i]['name'] = $relation->offre->nom->__toString();
      $rooms[$i]['lumjour'] = (_get_urn_value($relation->offre, 'urn:fld:eqsrvsalle:lumjour')?'&#10004;':'&#10008;');
      foreach($relation->offre->spec as $spec_relation){
        switch($spec_relation->attributes()->urn->__toString()){
          case 'urn:fld:ident:superf':
            $rooms[$i]['superf'] = $spec_relation->value->__toString();
            break;
          case 'urn:fld:ident:captheat':
            $rooms[$i]['captheat'] = $spec_relation->value->__toString();
            break;
          case 'urn:fld:ident:capcockt':
            $rooms[$i]['capcockt'] = $spec_relation->value->__toString();
            break;
          case 'urn:fld:ident:capbanq':
            $rooms[$i]['capbanq'] = $spec_relation->value->__toString();
            break;
          case 'urn:fld:ident:capaudit':
            $rooms[$i]['capaudit'] = $spec_relation->value->__toString();
            break;
          case 'urn:fld:ident:capsemin':
            $rooms[$i]['capsemin'] = $spec_relation->value->__toString();
            break;
        }
      }
    }
    $i++;
  }
  
  if(isset($rooms)){
    $content = '<thead>'
              . '<tr>'
              .'<th data-field="name" data-sortable="true">'.__('Names of Rooms', 'pivot').'</th>'
              .'<th data-field="daylight" data-sortable="true" class="text-center" scope="col">'.__('Day light', 'pivot').'</th>';
    // Check if attribute is present or not
    if(_multiKeyExists($rooms, 'superf')){
      $content .= '<th data-field="surface" data-sortable="true" class="text-center" scope="col">'.__('Surface', 'pivot').'</th>';
    }
    if(_multiKeyExists($rooms, 'captheat')){
      $content .= '<th data-field="theater" data-sortable="true" class="text-center" scope="col">'.__('Theater', 'pivot').'</th>';
    }
    if(_multiKeyExists($rooms, 'capcockt')){
      $content .= '<th data-field="reception"data-sortable="true"  class="text-center" scope="col">'.__('Reception', 'pivot').'</th>';
    }
    if(_multiKeyExists($rooms, 'capbanq')){
      $content .= '<th data-field="banquet" data-sortable="true" class="text-center" scope="col">'.__('Banquet', 'pivot').'</th>';
    }
    if(_multiKeyExists($rooms, 'capaudit')){
      $content .= '<th data-field="auditorium" data-sortable="true" class="text-center" scope="col">'.__('Auditorium', 'pivot').'</th>';
    }
    if(_multiKeyExists($rooms, 'capsemin')){
      $content .= '<th data-field="classroom" data-sortable="true" class="text-center" scope="col">'.__('Classroom', 'pivot').'</th>';
    }
    $content .= '</tr></thead><tbody>';

    foreach($rooms as $room){
      $content.= '<tr>';
      $content.= '<td class="name">'.$room['name'].'</td>';
      $content.= '<td class="lumjour text-center">'.(isset($room['lumjour'])?$room['lumjour']:'').'</td>';
      if(_multiKeyExists($rooms, 'superf')){
        $content .= '<td class="superf text-center">'.(isset($room['superf'])?$room['superf']:'').'</td>';
      }
      if(_multiKeyExists($rooms, 'captheat')){
        $content .= '<td class="captheat text-center">'.(isset($room['captheat'])?$room['captheat']:'').'</td>';
      }
      if(_multiKeyExists($rooms, 'capcockt')){
        $content .= '<td class="capcockt text-center">'.(isset($room['capcockt'])?$room['capcockt']:'').'</td>';
      }
      if(_multiKeyExists($rooms, 'capbanq')){
        $content .= '<td class="capbanq text-center">'.(isset($room['capbanq'])?$room['capbanq']:'').'</td>';
      }
      if(_multiKeyExists($rooms, 'capaudit')){
        $content .= '<td class="capaudit text-center">'.(isset($room['capaudit'])?$room['capaudit']:'').'</td>';
      }
      if(_multiKeyExists($rooms, 'capsemin')){
        $content .= '<td class="capsemin text-center">'.(isset($room['capsemin'])?$room['capsemin']:'').'</td>';
      }
      $content.= '</tr>';
    }
    return $open_balise.$content.$close_balise;
  }else{
    return $output;
  }
}

/**
 * Return HTML with date detail based on an activity Pivot offer
 * @param Object $offre complete offer object
 * @return string HTML with start and end dates
 */
function _add_section_event_dates($offre){
  $dates_output = '';
  
  foreach($offre->spec as $specification){
    if($specification->attributes()->urn->__toString() == 'urn:obj:date'){
      $dates_output .= '<div class="pivot-date-object">';
      foreach($specification->spec as $dateObj){
        $date = date("Y-m-d", strtotime(str_replace('/', '-', $dateObj->value->__toString())));
          if((strtotime($date) >= strtotime('now')) && (strtotime($date) <= strtotime('+6 month'))){
            $dates_output .= '<span class="time time-start">'
                            . '<span datetime="'.date("Y-M-D h:m", strtotime($date)).'">'
                            .   (($dateObj->attributes()->urn->__toString() == 'urn:fld:date:datefin')?' <i class="fas fa-angle-double-right"></i> ':'')
                            .   ' <span class="day">'.date('d', strtotime($date)).'</span>'
                            .   ' <span class="month">'.date('M', strtotime($date)).'</span>'
//                            .   ' <span class="year">'.date('Y', strtotime($date)).'</span>'
                            . '</span>'
                            .'</span>';
        }

      }
      $dates_output .= '</div>';
    }
  }
  
  return $dates_output;
}

function _add_itinerary_details($offre, $urnCat, $urnSubCat=0){
  $previousSubCat = '';
  // Get 2 letter language code
  $lang = substr(get_locale(), 0, 2 );
  // Define if sub category or category
  $cat_or_subcat = ($urnSubCat?'urnSubCat':'urnCat');
  // Init var, will be used to check if there is well content or not
  $content = '';

  foreach($offre->spec as $specification){
    // If iteration is on an URN of the cat or subcat we are looking for
    if($specification->$cat_or_subcat->__toString() == $urnCat){
      $urnValue = _get_urnValue_translated($offre, $specification);
      if(!empty($urnValue)){
        if($previousSubCat == $specification->urnSubCat->__toString()){
          // Case FR
          if($lang == 'fr' && 'urn' == substr($specification->attributes()->urn->__toString(), 0, 3)){
            $content .= ' || '
                     .      $urnValue;
          }else{
            // Case other language than french
            if($lang != 'fr'){
            $content .= ' || '
                     .      $urnValue;
             }
          }
        }else{
          // Case FR
          if($lang == 'fr' && 'urn' == substr($specification->attributes()->urn->__toString(), 0, 3)){
            $content .= '</p><p class="card-text">'
                     .      $urnValue;
          }else{
            // Case other language than french
            if($lang != 'fr'){
            $content .= '</p><p class="card-text">'
                     .      $urnValue;
             }
          }
        }
        
        $previousSubCat = $specification->urnSubCat->__toString();
      }
    }
  }

  return $content;
}

function _add_section_accessi($offre){
  $output = '';
  if(_get_urn_value($offre, 'urn:fld:accessi') == TRUE){
    $perfautroul = _get_urn_value($offre, 'urn:fld:accessi:perfautroul');
    $permardif = _get_urn_value($offre, 'urn:fld:accessi:permardif');
    $perave = _get_urn_value($offre, 'urn:fld:accessi:perave');
    $permalvoy = _get_urn_value($offre, 'urn:fld:accessi:permalvoy');
    $persou = _get_urn_value($offre, 'urn:fld:accessi:persou');
    $permalent = _get_urn_value($offre, 'urn:fld:accessi:permalent');
    $perdifcomp = _get_urn_value($offre, 'urn:fld:accessi:perdifcomp');
      
    $output .= '<h5 class="lis-font-weight-500">Access-I</h5>
                <section class="pivot-accessi card lis-brd-light mb-4">
                  <div class="card-body tetris__wrapper p-4">
                  <a alt="Access-I site" target="_blank" href="'._get_urn_value($offre, 'urn:fld:accessi:url').'">
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/label-i.png', dirname(__FILE__)).'" width="222" height="216" alt="Label Access-i">
                      <!-- Fauteuil roulant -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/wheelchair_'.substr($perfautroul, strrpos($perfautroul, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Une fiche informative est disponible pour l’accessibilité pour les personnes en fauteuil roulant">
                      <!-- Marchant difficilement -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/walking_'.substr($permardif, strrpos($permardif, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Accessible en autonomie aux personnes marchant difficilement">
                      <!-- Aveugle -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/blind_'.substr($perave, strrpos($perave, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Accessible en autonomie aux personnes aveugles">
                      <!-- Lowvision -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/lowvision_'.substr($permalvoy, strrpos($permalvoy, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Accessible avec un coup de main ponctuel aux personnes malvoyantes">
                      <!-- deaf -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/deaf_'.substr($persou, strrpos($persou, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Accessible avec un coup de main ponctuel aux personnes sourdes">
                      <!-- harthearing -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/harthearing_'.substr($permalent, strrpos($permalent, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Accessible en autonomie aux personnes malentendantes">
                      <!-- comprehension -->
                      <img class="tetrisImg" src="'.plugins_url('img/accessi/comprehension_'.substr($perdifcomp, strrpos($perdifcomp, ':') + 1).'.png', dirname(__FILE__)).'" width="222" height="216" alt="Accessible en autonomie aux personnes avec difficultés de compréhension">
                    </div>
                  </a>
                </section>';
  }
  
  return $output;
}

function _add_pivot_map($map, $nb_col){
  $output = '<div id="maparea" class="'.(($map==1)?'col-'.$nb_col.' d-none d-md-block':'').'">';
  // Include leaflet css for map
  $output .= ' <link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css"
                   integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
                   crossorigin=""/>';
  // Include leaflet js for map
  $output .= '<script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js"
                     integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg=="
                     crossorigin=""></script>';
  // Create Map element
  $output .= '<div id="mapid" style="height: 800px;width: 600px;z-index:0;"></div>';
  // Include map custom js
  $output .= '<script src="'.plugins_url('js/mapcardorientation.js', dirname(__FILE__)).'"></script>';
  $output .= '</div>';
  
  return $output;
}

function _set_nb_col($map, $nb_col){
  if($map==1){
    $output = 'col-12 ';
  }else{
    switch($nb_col){
      case 2:
        $output = 'col-xl-6 col-lg-6 ';
        break;
      case 3:
        $output = 'col-xl-4 col-lg-4 ';
        break;
      case 5:
        $output = 'w-xl-20 w-lg-20 ';
        break;
      case 6:
        $output = 'col-xl-2 col-lg-2 ';
        break;
      default:
        $output = 'col-xl-3 col-lg-4 ';
        break;
    }
    $output .= 'col-md-6 col-sm-6 col-xs-12 ';
  }

  return $output;
}

function _add_banner_image($image){
  if($image != NULL && $image != ''){
    $output = '<div class="row" style="background-image:url('.$image.');
                                       background-position: center center;
                                       background-repeat: no-repeat;
                                       background-size: cover;
                                       height: 400px;">
               </div>';
  }else{
    $output = '';
  }
  return $output;
}