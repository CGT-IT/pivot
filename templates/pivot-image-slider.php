<?php

$offre = $args;

//Open balise for carousel
$carousel_open = '<div class="col-xl-8 col-lg-10 col-12 mx-auto mb-2 p-0">'
  . '<div id="pivotCarousel" class="carousel slide" data-ride="carousel">'
  . '<div class="carousel-inner">';

//Indicators open balise
$carousel_items = '';
$i = 0;
foreach ($offre->relOffre as $relation) {
  foreach ($relation as $specification) {
    foreach ($specification->spec as $spec) {
      if ($spec->attributes()->urn == 'urn:fld:typmed') {
        if ($spec->value->__toString() == "urn:val:typmed:photo") {
          $img = '';
          $media_name = _get_urn_value($specification, 'urn:fld:nomofr');
          // If media is on Pivot
          $img_url = get_option('pivot_uri') . 'img/' . $relation->offre->attributes()->codeCgt->__toString() . ';w=970';
          list($width, $height, $type, $attr) = getimagesize($img_url);
          $img .= '<figure class="">';
          $img .= '<img alt="' . $specification->nom->__toString() . '" class="pivot-img pivot-img-details ' . (($height >= $width) ? 'portrait' : 'landscape') . '" src="' . get_option('pivot_uri') . 'img/' . $relation->offre->attributes()->codeCgt->__toString() . ';w=970"/>';
          if ($copyright = _get_urn_value($specification, 'urn:fld:copyr')) {
            $img .= '<figcaption><small>' . _construct_media_copyright($copyright, _get_urn_value($specification, 'urn:fld:date')) . '</small></figcaption>';
          }
          $img .= '</figure>';
          if ($img != '') {
            // Set active if first image
            $carousel_items .= '<div class="carousel-item ' . (($i == 0) ? 'active' : '') . '">' . $img . '</div>';
            $i++;
          }
        }
      }
    }
  }
}

$controls = '';
//Left and right controls
if ($i > 1) {
  $controls = '<a class="carousel-control-prev" href="#pivotCarousel" data-slide="prev">'
    . '<span class="carousel-control-prev-icon"></span>'
    . '<span class="sr-only">' . esc_html__('Previous', 'pivot') . '</span>'
    . '</a>'
    . '<a class="carousel-control-next" href="#pivotCarousel" data-slide="next">'
    . '<span class="carousel-control-next-icon"></span>'
    . '<span class="sr-only">' . esc_html__('Next', 'pivot') . '</span>'
    . '</a>';
}
$carousel_close = '</div></div></div>';
if ($carousel_items != '') {
  print $carousel_open . $carousel_items . $controls . $carousel_close;
} else {
  return;
}