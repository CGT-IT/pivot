<?php /* Template Name: pivot-address-template */ ?>

<?php $offre = $args; ?>

<div class="col-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
  <article class="pivot-offer">
    <header>
      <?php $url = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
      <a class="text-dark" title="<?php echo esc_attr('Link to', 'pivot') .' '. $offre->nom->__toString(); ?>" href="<?php print $url; ?>">
        <div class="container-img">
          <img class="pivot-img zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=260;h=173"/>
          <div class="top-right-corner p-1">
            <span class="item-services">
              <?php print _search_specific_urn_img($offre, 'urn:fld:eqpsrv:accwebwifi', 20, 'FFFFFF'); ?>
              <?php print _search_specific_urn_img($offre, 'urn:fld:pmr', 20, 'FFFFFF'); ?>
              <?php print _search_specific_urn_img($offre, 'urn:fld:animauxacc', 20, 'FFFFFF'); ?>
            </span>
          </div>
        </div>
        <h4 class="pivot-title pt-2 pl-3 pr-3">
          <?php print $offre->nom->__toString(); ?>
        </h4>
      </a>
    </header>

    <section class="pivot-summary p-3 pt-1">
      <p class="pivot-adr item">
        <span class="fa fa-map-o" aria-hidden="true">
          <?php print $offre->adresse1->rue->__toString(); ?>, 
          <?php print $offre->adresse1->numero->__toString(); ?>
          <?php print $offre->adresse1->commune->value->__toString(); ?>
        </span>
        <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
        <span class="pivot-code-cgt d-none item"><?php print $offre->attributes()->codeCgt->__toString(); ?></span>
        <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
        <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
      </p>
      <?php if(_get_urn_value($offre, 'urn:fld:urlweb')): ?>
        <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlweb;h=16"/>
        <a target="_blank" href="<?php print _get_urn_value($offre, 'urn:fld:urlweb'); ?>"><?php esc_html_e('Website', 'pivot');?></a>
      <?php else: ?>
        <br>
      <?php endif; ?>
    </section>
      
    <div class="row text-center pl-3 pr-3">
      <div class="col-4 pt-3 pb-3 border-top border-right"><i class="fa fa-bed"></i> <?php print _search_specific_urn($offre, 'urn:fld:capbase'); ?></div>
      <?php if(_search_specific_urn($offre, 'urn:fld:label:bvvelo') != ''): ?>
        <div class="col-6 pt-3 pb-3 border-top">
          <?php print $offre->typeOffre->label->value->__toString().'  '._get_ranking_picto($offre, 'urn:fld:class'); ?>
        </div>
        <div class="col-2 pt-2 border-top border-left item-services">
          <?php print _search_specific_urn_img($offre, 'urn:fld:label:bvvelo', 40, null, true); ?>
        </div>
      <?php else: ?>
        <div class="col-8 pt-3 pb-3 border-top">
          <?php print $offre->typeOffre->label->value->__toString().'  '._get_ranking_picto($offre, 'urn:fld:class'); ?>
        </div>
      <?php endif; ?>
    </div>

  </article>
</div>