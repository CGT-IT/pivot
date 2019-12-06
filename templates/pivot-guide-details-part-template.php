
<?php $offre = $args; ?>
<tr>
  <td>
    <?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?>
  </td>
  <td><?php print (isset($offre->adresse1->commune->value)?$offre->adresse1->commune->value->__toString():''); ?></td>
  <td>
    <?php foreach($offre->spec as $specification): ?>
      <?php if($specification->urnCat->__toString() == 'urn:cat:moycom'): ?>
          <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
          <?php switch ($specification->type->__toString()): 
            case 'EMail': ?>
              <a class="<?php print $specification->type->__toString(); ?>" href="mailto:<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
              <?php break ?>
            <?php case 'URL': ?>
              <a class="<?php print $specification->type->__toString(); ?>" target="_blank" href="<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
              <?php break ?>
            <?php case 'GSM': ?>
              <a class="<?php print $specification->type->__toString(); ?>" href="tel:<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
              <?php break ?>
            <?php case 'Phone': ?>
              <a class="<?php print $specification->type->__toString(); ?>" href="tel:<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
              <?php break ?>
            <?php default: ?>
              <?php print $specification->value->__toString(); ?>
              <?php break ?>  
          <?php endswitch ?>
        </br>
      <?php endif; ?>
    <?php endforeach; ?>
  </td>
  <td>
    <div class="adr">
      <span class="street-address"><span class="glyphicon glyphicon-map-marker"></span> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></span></br>
      <span>
        <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
        <span class="locality"><?php print (isset($offre->adresse1->commune->value)?$offre->adresse1->commune->value->__toString():''); ?></span>
      </span></br>
      <span class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></span>
      <span class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></span>
      <span class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></span>
    </div>
  </td>
 <td>
    <?php foreach($offre->spec as $specification): ?>
      <?php if($specification->urnCat->__toString() == 'urn:cat:accueil' && $specification->urnSubCat->__toString() == 'urn:cat:accueil:langpar'): ?>
          <span class="pivot-acceuil <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
            <?php if($specification->type->__toString() == 'Boolean'): ?>
              <img class="pivot-picto" title="<?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?>" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
            <?php else: ?>
              : <span class="pivot-desc item"><?php print _get_urn_value($offre, $specification->attributes()->urn->__toString()) ;?></span>
            <?php endif ?>
          </span>
        </br>
      <?php endif ?>
    <?php endforeach ?>
  </td>
  <td><?php print $offre->attributes()->codeCgt->__toString(); ?></td>
</tr>