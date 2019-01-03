
<?php $offre = _get_offer_details(); ?>
<?php _add_meta_data($offre, 'details'); ?>
<?php get_header(); ?>


<article class="pivot-offer row m-3">
  <div class="col-xs-12 col-md-8">
    <div class="row">
      <div class="col-12">
        <!--Image carousel-->
        <div id="pivotCarousel" class="carousel slide" data-ride="carousel">
          <!-- Indicators -->
          <ol class="carousel-indicators">
          <?php $i = 0; ?>
          <?php foreach($offre->relOffre as $relation): ?>
            <!--if it's well an image-->
            <?php if($relation->offre->typeOffre->attributes()->idTypeOffre->__toString() == '268'): ?>
              <!--Add indicator and set active if first one-->
              <li data-target="#pivotCarousel" data-slide-to="<?php print $i;?>" class="<?php if($i == 0) {print 'active';}?>"></li>
              <?php $i++; ?>
            <?php endif; ?>
          <?php endforeach; ?>
          </ol>

          <!-- Images -->
          <div class="carousel-inner">
            <?php $i = 0; ?>
            <?php foreach($offre->relOffre as $relation): ?>
              <!--if it's well an image-->
              <?php if($relation->offre->typeOffre->attributes()->idTypeOffre->__toString() == '268'): ?>
                <div class="carousel-item <?php if($i == 0) {print 'active';}?>">
                  <?php foreach($relation as $specification): ?>
                    <?php if(strpos(_get_urn_value($specification, 'urn:fld:url'), 'pivotmedia')): ?>
                      <figure>
                        <img alt="<?php print _get_urn_value($specification, 'urn:fld:nomofr');?>" class="pivot-img pivot-img-details" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $relation->offre->attributes()->codeCgt->__toString(); ?>;w=1300;h=600"/>
                        <?php if($copyright = _get_urn_value($specification, 'urn:fld:copyr')): ?>
                          <footer><small><?php print _construct_media_copyright(_get_urn_value($specification, 'urn:fld:copyr'), _get_urn_value($specification, 'urn:fld:date'));?></small></footer>
                        <?php endif; ?>
                        <figcaption><?php print _get_urn_value($specification, 'urn:fld:nomofr');?></figcaption>
                      </figure>
                    <?php else: ?>
                      <figure>
                        <img alt="<?php print _get_urn_value($specification, 'urn:fld:nomofr');?>" class="pivot-img pivot-img-details" src="<?php print _get_urn_value($specification, 'urn:fld:url'); ?>"/>
                        <?php if($copyright = _get_urn_value($specification, 'urn:fld:copyr')): ?>
                          <footer><small><?php print _construct_media_copyright(_get_urn_value($specification, 'urn:fld:copyr'), _get_urn_value($specification, 'urn:fld:date'));?></small></footer>
                        <?php endif; ?>
                        <figcaption><?php print _get_urn_value($specification, 'urn:fld:nomofr');?></figcaption>
                      </figure>
                    <?php endif; ?>  
                  <?php endforeach; ?>
                </div>
                <?php $i++; ?>
              <?php endif; ?>
            <?php endforeach; ?>

            <!-- Left and right controls -->
            <a class="carousel-control-prev" href="#pivotCarousel" data-slide="prev">
              <span class="carousel-control-prev-icon"></span>
              <span class="sr-only"><?php esc_html_e('Previous')?></span>
            </a>
            <a class="carousel-control-next" href="#pivotCarousel" data-slide="next">
              <span class="carousel-control-next-icon"></span>
              <span class="sr-only"><?php esc_html_e('Next')?></span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-12">
        <h2 class="pivot-title"><?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?></h2>

        <!--<div class="tab-content card">-->
        <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
            <div class="card-body p-4">
                <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
            </div>
        </section>
        
        <?php print _add_section($offre,'urn:cat:accueil', 'Extra infos', 'fa-info'); ?>

      </div>
    </div>
  </div>

  <aside class="col-xs-12 col-md-3">
      
    <?php print _add_section_contact($offre); ?>
    
    <?php print _add_section($offre,'urn:cat:accueil:langpar', 'Language(s)', 'fa-language', 1); ?>
    <?php print _add_section($offre,'urn:cat:prod', 'Product(s)', 'fa-shopping-basket'); ?>
    <?php print _add_section($offre,'urn:cat:tarif', 'Price(s)', 'fa-eur'); ?>
    <?php print _add_section($offre,'urn:cat:visite', 'Visit', 'fa-map-signs'); ?>
    <?php print _add_section($offre,'urn:cat:eqpsrv', 'Equipments & services', 'fa-thumb-tack'); ?>
    <?php print _add_section($offre,'urn:cat:classlab', 'Themes', 'fa-list-alt'); ?>
    <?php print _add_section_share($offre); ?>

  </aside>

</article>


<?php get_footer();