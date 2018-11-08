<?php /* Template Name: pivot-address-template */ ?>


<?php $offre = get_query_var('offre'); ?>
<?php if(isset($offre->adresse1->commune->value)): ?>
  <div class="adr">
    <dd class="street-address"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></dd>
      <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
      <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
      <dd class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></dd>
      <dd class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></dd>
      <dd class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></dd>
  </div>
<?php endif; ?>