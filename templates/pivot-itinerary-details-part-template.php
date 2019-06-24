<?php $offre = $args; ?>

<div class="offers-area-col col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
  
  <?php $url = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
  <div class="card text-left pivot-offer">
    <div class="container-img">
      <img class="pivot-img card-img-top zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=428;h=284"/>
    </div>
    <h5 class="card-header">
      <?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?>
    </h5>
    <div class="card-body">
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
      <a class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') .' '. _get_urn_value($offre, 'urn:fld:nomofr'); ?>" href="<?php print $url; ?>"></a>
      <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
      <span class="pivot-code-cgt d-none item"><?php print $offre->attributes()->codeCgt->__toString(); ?></span>
      <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
      <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
    </div>
    
  </div>
      
</div>