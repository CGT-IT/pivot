<?php $offre = $args; ?>

<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">

  <div class="card text-center">
    <h5 class="card-header"><?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?></h5>
    <div class="card-body">
      <p class="card-text">
        <i class="fas fa-map-marker-alt"></i>
        <span>
          <?php print $offre->adresse1->rue->__toString(); ?>, 
          <?php print $offre->adresse1->numero->__toString(); ?>
        </span>
        <br>
        <?php print $offre->adresse1->cp; ?> 
        <?php print $offre->adresse1->commune->value->__toString(); ?>
      </p>
      <p class="card-text" style="height: 25px;">
        <?php if(_get_urn_value($offre, 'urn:fld:urlweb')): ?>
          <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlweb;h=16"/>
          <a target="_blank" href="<?php print _get_urn_value($offre, 'urn:fld:urlweb'); ?>"><?php esc_html_e('Website', 'pivot');?></a>
        <?php endif; ?>
      </p>
    </div>
  </div>
      
</div>