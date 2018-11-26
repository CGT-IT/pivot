
<?php /* Template Name: pivot-lodging-list-template */ ?>

<!--<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
<script defer src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css">-->
<?php $page = pivot_get_page_path(_get_path()); ?>
<title><?php print $_SESSION['pivot'][$page->id]['page_title'] ?> - CGT</title>
<!--Include header-->
<?php get_header(); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.css">

<!--Include sidebar-->
<?php if(is_active_sidebar('et_pb_widget_area_12')): ?>
  <aside id="secondary" class="widget-area" role="complementary">
    <?php dynamic_sidebar('et_pb_widget_area_12'); ?>
  </aside><!-- #primary-sidebar -->
<?php endif; ?>

<!--Get offers-->
<?php $offres = pivot_lodging_page($page->id); ?>
<div class="container-fluid custom-margin-top">
  <?php // add_filters(); ?>
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
                    <div>
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
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
              <td>
                <div class="adr">
                  <div class="street-address"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></div>
                  <div>
                    <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
                    <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
                  </div>
                  <div class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></div>
                  <dd class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></dd>
                  <dd class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></dd>
                </div>
              </td>
              <td>
                <?php foreach($offre->spec as $specification): ?>
                  <?php if($specification->urnCat->__toString() == 'urn:cat:accueil' && $specification->urnSubCat->__toString() == 'urn:cat:accueil:langpar'): ?>
                    <div class="pivot-acceuil <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                      <span>
                        <?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                        <?php if($specification->type->__toString() == 'Boolean'): ?>
                          <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
                        <?php else: ?>
                          <span class="pivot-desc item"><?php print _get_urn_value($offre, $specification->attributes()->urn->__toString()) ;?></span>
                        <?php endif ?>
                      </span>
                    </div>
                  <?php endif ?>
                <?php endforeach ?>
              </td>
              <td>
                <?php foreach($offre->spec as $specification): ?>
                  <?php if($specification->urnSubCat->__toString() == 'urn:cat:classlab:themat'): ?>
                    <div><?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?></div>
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