
<?php $offre = $args; ?>

<div class="offers-area-col <?php print ($offre->map==1)?'col-12':'col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12';?>  mb-3">
  
  <?php $url = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
  <div class="card text-left pivot-offer">
    <div class="container-img">
      <img class="pivot-img card-img-top zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=428;h=284"/>
    </div>
    <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
    <h5 class="card-header"><?php print $offerTitle; ?></h5>
    <div class="card-body">
      <?php $phone = _get_urn_value($offre, 'urn:fld:phone1'); ?>
      <?php if($phone != ''): ?>
        <p class="card-text">
          <i class="fas fa-phone"></i>
          <?php print $phone; ?>
        </p>
      <?php endif; ?>
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