
<?php $offre = $args; ?>

<div class="offers-area-col <?php print _set_nb_col($offre->map, $offre->nb_per_row).'nb-col-'.$offre->nb_per_row; ?> mb-3">
  <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
  <?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
  <?php $lang = substr(get_locale(), 0, 2 ); ?>
  <?php $url = get_bloginfo('wpurl').(($lang=='fr')?'':'/'.$lang).'/'.$offre->path.'/'.$codeCGT.'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
  <div class="card text-left pivot-offer">
    <div class="card-orientation <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'card-horizontal';?>">
      <div class="container-img embed-responsive embed-responsive-16by9 <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-5 p-0 my-auto';?>">
        <img alt="<?php print $offerTitle; ?>" class="embed-responsive-item pivot-img card-img-top zoom pivot-img-list" src="<?php print _get_offer_default_image($offre); ?>"/>
      </div>
      <h6 class="title-header card-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'style="display:none"';?>><?php print $offerTitle; ?></h6>
      <div class="card-body <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-7 pt-2 pb-0';?>">
        <p class="h6 title-no-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'style="display:none"':'';?>><?php print $offerTitle; ?></p>
        <?php $phone = _get_urn_value($offre, 'urn:fld:phone1'); ?>
        <?php if($phone != ''): ?>
          <p class="card-text">
            <i class="fas fa-phone"></i>
            <?php print $phone; ?>
          </p>
        <?php else: ?>
          <?php $mobile = _get_urn_value($offre, 'urn:fld:mobi1'); ?>
          <?php if($mobile != ''): ?>
            <p class="card-text">
              <i class="fas fa-phone"></i>
              <?php print $mobile; ?>
            </p>
          <?php endif; ?>
        <?php endif; ?>
        <p class="card-text">
          <i class="fas fa-map-marker-alt"></i>
          <?php print $offre->adresse1->cp; ?> 
          <?php print $offre->adresse1->localite->value->__toString(); ?>
        </p>
        <a target="_blank" class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') .' '. $offerTitle; ?>" href="<?php print $url; ?>"></a>
        <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
        <span class="pivot-code-cgt d-none item"><?php print $codeCGT; ?></span>
        <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
        <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
      </div>
    </div>
  </div>

</div>