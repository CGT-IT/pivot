<?php /* Template Name: pivot-address-template */ ?>

<?php $offre = $args; ?>

<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
  <article class="pivot-offer">
    <header>
      <?php $url = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
      <a title="<?php echo esc_attr('Link to', 'pivot') .' '. $offre->nom->__toString(); ?>" href="<?php print $url; ?>">
        <div class="container-img">
          <img class="pivot-img zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=260;h=173"/>
          <div class="top-right-corner">
            <span class="item-services">
              <?php print _search_specific_urn($offre, 'urn:fld:eqpsrv:accwebwifi', 20, 'FFFFFF'); ?>
              <?php print _search_specific_urn($offre, 'urn:fld:pmr', 20, 'FFFFFF'); ?>
              <?php print _search_specific_urn($offre, 'urn:fld:animauxacc', 20, 'FFFFFF'); ?>
            </span>
          </div>
          <div class="bottom-left-corner">
            <span class="item-services">
              <?php print _search_specific_urn($offre, 'urn:fld:label:bvvelo', 40, null, true); ?>
            </span>
          </div>
        </div>
        <h4 class="pivot-title">
          <?php print $offre->nom->__toString(); ?>
        </h4>
      </a>
    </header>

    <section class="pivot-summary">
      <p class="pivot-commune item">
        <span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>
        <?php print $offre->adresse1->commune->value->__toString(); ?>
      </p>
      <p class="pivot-type-offre item">
        <?php print $offre->typeOffre->label->value->__toString()._get_ranking_picto($offre, 'urn:fld:class'); ?>
      </p>
      <?php if(_get_urn_value($offre, 'urn:fld:urlweb')): ?>
        <p class="pivot-external-link item">
          <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlweb;h=16"/>
          <a target="_blank" href="<?php print _get_urn_value($offre, 'urn:fld:urlweb'); ?>"><?php esc_html_e('Website', 'pivot');?></a>
        </p>
      <?php endif; ?>
      <p class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></p>
      <p class="pivot-code-cgt d-none item"><?php print $offre->attributes()->codeCgt->__toString(); ?></p>
      <p class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></p>
      <p class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></p>
    </section>

  </article>
</div>