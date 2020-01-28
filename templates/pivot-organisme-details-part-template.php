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
      <h6 class="title-header pt-1 pb-1 mb-1 card-header" <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'style="display:none"';?>><?php print $offerTitle; ?></h6>
      <div class="card-body pt-1 pb-2 <?php print ($offre->map!=1||wp_is_mobile()==1)?'':'col-7 pt-2 pb-0';?>">
        <i class="fas fa-map-marker-alt"></i>
        <span>
          <?php print $offre->adresse1->rue->__toString(); ?>, 
          <?php print $offre->adresse1->numero->__toString(); ?>
        </span>
        <br>
        <?php print $offre->adresse1->cp; ?> 
        <?php print $offre->adresse1->localite->value->__toString(); ?>
        <p class="card-text">
          <?php if(_get_urn_value($offre, 'urn:fld:urlweb')): ?>
            <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlweb;h=16"/>
            <a target="_blank" href="<?php print _get_urn_value($offre, 'urn:fld:urlweb'); ?>"><?php esc_html_e('Website', 'pivot');?></a>
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>
      
</div>
