
<?php /* Template Name: pivot-lodging-list-template */ ?>

<!--Include header-->
<?php get_header(); ?>
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
<!--<script defer src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">

<script>
$(document).ready(function() {
    $('#tourist-guide').DataTable();
} );
</script>
<!--Include sidebar-->
<?php // get_sidebar(); ?>
<?php global $base_url; ?>

<!--Get offers-->
<?php 
$page=pivot_get_page_path($_SESSION['pivot']['path']);
$offres = pivot_lodging_page($page->id); ?>

<p><?php echo esc_html('There are', 'pivot') .' '. $_SESSION['pivot']['nb_offres'] .' '.  esc_html('offers', 'pivot'); ?></p>
<div class="container">
  <?php add_filters(); ?>
  <div class="row">
    <div class="col-md-12 table-responsive-lg">
      <table id="cgt-table-search-pagin" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Province</th>
            <th>Contact</th>
            <th>Adresse</th>
            <th>Langues</th>
            <th>Thématiques</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($offres as $offre): ?>  
            <tr>
              <td>
                <?php print $offre->nom->__toString(); ?>
              </td>
              <td><?php print $offre->adresse1->commune->value->__toString(); ?></td>
              <td>
                <?php foreach($offre->spec as $specification): ?>
                  <?php if($specification->urnCat->__toString() == 'urn:cat:moycom'): ?>
                    <dd>
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
                    </dd>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
              <td>
                <div class="adr">
                  <dd class="street-address"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></dd>
                  <dd>
                    <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
                    <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
                  </dd>
                  <dd class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></dd>
                  <dd class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></dd>
                  <dd class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></dd>
                </div>
              </td>
              <td>
                <?php foreach($offre->spec as $specification): ?>
                  <?php if($specification->urnCat->__toString() == 'urn:cat:accueil' && $specification->urnSubCat->__toString() == 'urn:cat:accueil:langpar'): ?>
                    <dd class="pivot-acceuil <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                      <span>
                        <?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                        <?php if($specification->type->__toString() == 'Boolean'): ?>
                          <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
                        <?php else: ?>
                          <span class="pivot-desc item"><?php print _get_urn_value($offre, $specification->attributes()->urn->__toString()) ;?></span>
                        <?php endif ?>
                      </span>
                    </dd>
                  <?php endif ?>
                <?php endforeach ?>
              </td>
              <td>
                <?php foreach($offre->spec as $specification): ?>
                  <?php if($specification->urnSubCat->__toString() == 'urn:cat:classlab:themat'): ?>
                    <dd><?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?></dd>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php echo _add_pagination($_SESSION['pivot']['nb_offres']); ?>

<?php get_footer();