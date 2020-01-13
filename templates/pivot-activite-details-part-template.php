<?php $offre = $args; ?>

<div class="offers-area-col <?php print _set_nb_col($offre->map, $offre->nb_per_row).'nb-col-'.$offre->nb_per_row; ?> mb-3">
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
      <div class="card-body">
        <span class="text-muted">
          <?php $content =''; ?>
          <?php foreach($offre->spec as $specification): ?>
            <?php if($specification->urnSubCat->__toString() == 'urn:cat:classlab:classif' && !empty(_get_urn_documentation($specification->attributes()->urn->__toString()))): ?>
              <?php $content .= _get_urnValue_translated($offre, $specification). ' / '; ?>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php print $content; ?>  
        </span>
        <h6 class="title-no-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'style="display:none"':'';?>><?php print $offerTitle; ?></h6>
        <div class="card-text text-uppercase dates"><?php print _add_section_event_dates($offre); ?></div>
        <p class="card-text text-muted city"><i class="fas fa-map-marker-alt"></i> <?php print $offre->adresse1->localite->value->__toString(); ?></p>
        <p class="card-text"><p class="pivot-desc item mb-0"><?php print wp_trim_words(_get_urn_value($offre, 'urn:fld:descmarket'), 20, '...' );?></p></p>
        <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
        <span class="pivot-code-cgt d-none item"><?php print $codeCGT; ?></span>
        <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
        <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>

      </div>
      <a class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') .' '. $offerTitle; ?>" href="<?php print $url; ?>"></a>
    </div>
  </div>
</div>