<?php $offre = $args; ?>

<div class="offers-area-col <?php print ($offre->map==1)?'col-12':'col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12';?>  mb-3">
  <?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
  <?php $lang = substr(get_locale(), 0, 2 ); ?>
  <?php $url = get_bloginfo('wpurl').(($lang=='fr')?'':'/'.$lang).'/'.$offre->path.'/'.$codeCGT.'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
  <div class="card text-left pivot-offer">
    <div class="card-orientation <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'card-horizontal';?>">
      <div class="container-img <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-5 p-0 my-auto';?>">
        <img class="pivot-img card-img-top zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $codeCGT;?>;w=444;h=296"/>
      </div>
      <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
      <h6 class="title-header card-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'style="display:none"';?>><?php print $offerTitle; ?></h6>
      <div class="card-body <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-7 pt-2 pb-0';?>">
        <h6 class="title-no-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'style="display:none"':'';?>><?php print $offerTitle; ?></h6>
        <p class="card-text">
          <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:typ:8;h=18"> <?php print round(_get_urn_value($offre, 'urn:fld:dist'), 2); ?> km
          &nbsp;<img class="pivot-img" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print _get_urn_value($offre, 'urn:fld:signal') ;?>;w=20"/>
        </p>
        <?php print _add_itinerary_details($offre, 'urn:cat:accueil'); ?>
        <p class="card-text">
          <i class="fas fa-map-marker-alt"></i>
          <?php print $offre->adresse1->cp; ?> 
          <?php print $offre->adresse1->commune->value->__toString(); ?>
        </p>
        <a target="_blank" class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') .' '. $offerTitle; ?>" href="<?php print $url; ?>"></a>
        <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
        <span class="pivot-code-cgt d-none item"><?php print $offre->attributes()->codeCgt->__toString(); ?></span>
        <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
        <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
      </div>
    </div>
  </div>
</div>