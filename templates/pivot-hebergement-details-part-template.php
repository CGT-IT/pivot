
<?php $offre = $args; ?>

<div class="offers-area-col <?php print _set_nb_col($offre->map, $offre->nb_per_row); ?> mb-3">
  <?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
  <?php $offreName = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
  <?php $lang = substr(get_locale(), 0, 2 ); ?>
  <?php $url = get_bloginfo('wpurl').(($lang=='fr')?'':'/'.$lang).'/'.$offre->path.'/'.$codeCGT.'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
  <div class="card text-left pivot-offer">
    <div class="card-orientation <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'card-horizontal';?>">
      <div class="container-img <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-5 p-0 my-auto';?>">
        <img class="pivot-img card-img-top zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $codeCGT ;?>;w=428;h=284"/>
        <div class="p-3 position-absolute fixed-top">
          <?php print _search_specific_urn_img($offre, 'urn:fld:label:bvvelo', 40, null, true); ?>
        </div>
        <div class="p-3 position-absolute fixed-top text-right">
          <span class="item-services">
            <?php print _search_specific_urn_img($offre, 'urn:fld:eqpsrv:accwebwifi', 20, 'FFFFFF'); ?>
            <?php print _search_specific_urn_img($offre, 'urn:fld:pmr', 20, 'FFFFFF'); ?>
            <?php print _search_specific_urn_img($offre, 'urn:fld:animauxacc', 20, 'FFFFFF'); ?>
          </span>
        </div>
      </div>
      <h6 class="title-header card-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'style="display:none"';?>><?php print $offreName; ?></h6>
      <div class="card-body <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-7 pt-2 pb-0';?>">
        <h6 class="title-no-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'style="display:none"':'';?>><?php print $offreName; ?></h6>
        <p class="card-text">
          <?php print $offre->typeOffre->label->value->__toString().'  '._get_ranking_picto($offre); ?>
        </p>
        <?php $capbase = _get_urn_value($offre, 'urn:fld:capbase'); ?>
        <?php if(!empty($capbase) && $capbase != 0): ?>
          <p class="card-text">
            <i class="fas fas-align-right pr-2 fa-bed"></i><?php print $capbase; ?>
            <?php $capadd = _get_urn_value($offre, 'urn:fld:capadd'); ?>
            <?php if(!empty($capadd) && $capadd != 0): ?>
              <?php print ' '.__('à', 'pivot').' '.($capbase+$capadd); ?>
            <?php endif; ?>
          </p>
        <?php endif; ?>
        <?php $prixmin = _get_urn_value($offre, 'urn:fld:tarifind:pmin:webssais'); ?>
        <?php if(!empty($prixmin) && $prixmin != 0): ?>
          <p class="card-text">
            <i class="fas fas-align-right pr-2 fa-euro-sign"></i><?php print __('à partir de ','pivot').$prixmin.'€'; ?>
          </p>
        <?php endif; ?>
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
        <a target="_blank" class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') .' '. $offerName; ?>" href="<?php print $url; ?>"></a>
        <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
        <span class="pivot-code-cgt d-none item"><?php print $codeCGT; ?></span>
        <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
        <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
      </div>
    </div>
  </div>
      
</div>