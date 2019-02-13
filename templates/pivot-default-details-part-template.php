
<?php $offre = $args; ?>

<?php if($offre->map == 1): ?>
  <div class="col-4 col-lg-6 col-md-6 col-sm-6 col-xs-12">
<?php else: ?>
  <div class="col-3 col-lg-3 col-md-4 col-sm-6 col-xs-12">
<?php endif; ?>
  <article class="pivot-offer">
    <header>
      <?php $url = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
      <a class="text-dark" title="<?php echo __('Link to', 'pivot') .' '. _get_urn_value($offre, 'urn:fld:nomofr'); ?>" href="<?php print $url; ?>">
        <div class="container-img">
          <img class="pivot-img zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=428;h=284"/>
        </div>
        <h4 class="pivot-title pt-2 pl-3 pr-3">
          <?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?>
        </h4>
      </a>
    </header>

    <section class="pivot-summary p-3 pt-1">
      <?php if(!empty($offre->adresse1->commune->value) || !empty($offre->adresse1->numero) || !empty($offre->adresse1->rue)): ?>
      <p class="pivot-adr item">
        <span class="fa fa-map-o" aria-hidden="true">
          <?php print $offre->adresse1->rue->__toString(); ?>, 
          <?php print $offre->adresse1->numero->__toString(); ?>
          <?php print (isset($offre->adresse1->commune->value)?$offre->adresse1->commune->value->__toString():''); ?>
        </span>
        <span class="pivot-id-type-offre d-none item"><?php print $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?></span>
        <span class="pivot-code-cgt d-none item"><?php print $offre->attributes()->codeCgt->__toString(); ?></span>
        <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
        <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
      </p>
      <?php endif; ?>
      <?php if(_get_urn_value($offre, 'urn:fld:urlweb')): ?>
        <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlweb;h=16"/>
        <a target="_blank" href="<?php print _get_urn_value($offre, 'urn:fld:urlweb'); ?>"><?php esc_html_e('Website', 'pivot');?></a>
      <?php else: ?>
        <br>
      <?php endif; ?>
    </section>

  </article>
</div>