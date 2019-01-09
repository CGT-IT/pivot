<?php 

/**
 * Return a HTML section with fields inside a group of info (category or sub category) 
 * like urn:cat:eqpsrv or urn:cat:tarif or urn:cat:visite or urn:cat:accueil:langpar, ...
 * @param Object $offre
 * @param string $urnCat URN of type Category like urn:cat:eqpsrv or urn:cat:tarif or urn:cat:visite or urn:cat:accueil:langpar, ...
 * @param string $title H5 title  above the section
 * @param string $faIcon Font Awesome icon class (without .)
 * @param boolean $urnSubCat to true if you want to display field of a sub category or false (by default) if you want to display the entire category
 * @see https://fontawesome.bootstrapcheatsheets.com/
 * @return string
 */
function _add_section($offre, $urnCat, $title, $faIcon='', $urnSubCat=0){
  // Define if sub category or category
  $cat_or_subcat = ($urnSubCat?'urnSubCat':'urnCat');
  // Get 2 letter language code
  $lang = substr(get_locale(), 0, 2 );
  // Get cat name taking word after last :
  $cat = substr($urnCat, strrpos($urnCat, ':') + 1);
  // Init var, will be used to check if there is well content or not
  $content = '';

  $open_balise = '<h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 f0fc '.$faIcon.'"></i>'. __($title) .'</h5>'
           .'<section class="pivot-'.$cat.' card lis-brd-light mb-4">'
           .'<div class="card-body p-4">'
           .'<ul class="list-unstyled lis-line-height-2 mb-0">';
  foreach($offre->spec as $specification){
    // If iteration is on an URN of the cat or subcat we are looking for
    if($specification->$cat_or_subcat->__toString() == $urnCat && !empty(_get_urn_documentation($specification->attributes()->urn->__toString()))){
      // Case FR
      if($lang == 'fr' && 'urn' == substr($specification->attributes()->urn->__toString(), 0, 3)){
        $content .= '<li class="p-1 '. str_replace(":", "-", $specification->attributes()->urn->__toString()) .'">'
                .    '<span class="'.$cat.'-label">'. _get_urn_documentation($specification->attributes()->urn->__toString()) .'</span>'
                .    '<span class="'.$cat.'-value"> '
                .      _get_urnValue_translated($offre, $specification)
                .    '</span> '
                .    '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'. $specification->attributes()->urn->__toString() .';h=16"/>'
                .  '</li>';
      }else{
        // Case other language than french
        if($lang != 'fr'){
        $content .= '<li class="p-1 '. str_replace(":", "-", $specification->attributes()->urn->__toString()) .'">'
                .    '<span class="'.$cat.'-label">'. _get_urn_documentation($specification->attributes()->urn->__toString()) .'</span>'
                .    '<span class="'.$cat.'-value"> '
                .      _get_urnValue_translated($offre, $specification)
                .    '</span> '
                .    '<img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/'. $specification->attributes()->urn->__toString() .';h=16"/>'
                .  '</li>';
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

/**
 * Return a HTML section with facebook and twitter share link
 * @param Object $offre
 * @return string
 */
function _add_section_share($offre){
  $url_offer_details = get_bloginfo('wpurl').'/details/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString();
  $output = '<h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-share-square-o"></i>'.esc_html('Share', 'pivot').'</h5>'
          .   '<section class="pivot-share card lis-brd-light wow fadeInUp mb-4">'
          .     '<div class="card-body p-4">'
          .       '<span><a class="social-icon" href="https://www.facebook.com/sharer.php?u='.$url_offer_details.'&amp;t='._get_urn_value($offre, 'urn:fld:nomofr').'" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlfacebook;h=35" alt="Facebook Share button" title="Facebook Share button"/></a></span>'
          .       '<span><a class="social-icon" href="https://twitter.com/share?text='._get_urn_value($offre, 'urn:fld:nomofr').'&amp;url='.$url_offer_details.'" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urltwitter;h=35" alt="Twitter Share button" title="Twitter Share button"/></a></span>'
          .     '</div>'
          .   '</section>';
  
  return $output;
}

/**
 * Return a HTML section with all contact infos
 * @param Object $offre
 * @return string
 */
function _add_section_contact($offre){
  $output = '<h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-id-card-o"></i>'.esc_html('Contact', 'pivot').'</h5>'
          . '<section class="pivot-contacts card lis-brd-light wow fadeInUp mb-4">'
          .   '<div class="card-body p-4">'
          .     '<h6 class="pivo-title">'._get_urn_value($offre, 'urn:fld:nomofr').'</h6>'
          .     '<ul class="list-unstyled lis-line-height-2 mb-0">';
  foreach($offre->spec as $specification){
    if($specification->urnCat->__toString() == 'urn:cat:moycom'){
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
            
  $output .= '<ul class="adr list-unstyled lis-line-height-2 mb-0">'
          .    '<li class="street-address"><i class="fa fa-map-o"></i> '.$offre->adresse1->rue->__toString().', '.$offre->adresse1->numero->__toString().'</li>'
          .      '<span class="postal-code">'.$offre->adresse1->cp->__toString().'</span>'
          .      '<span class="locality">'.(isset($offre->adresse1->commune)?$offre->adresse1->commune->value->__toString():'').'</span>'
          .    '<li class="country-name">'.$offre->adresse1->pays->__toString().'</li>'
          .    '<li class="pivot-latitude d-none">'.$offre->adresse1->latitude->__toString().'</li>'
          .    '<li class="pivot-longitude d-none">'.$offre->adresse1->longitude->__toString().'</li>'
          .  '</ul></div></section>';
  
  return $output;
}