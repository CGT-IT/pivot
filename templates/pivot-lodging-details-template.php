
<?php /* Template Name: pivot-lodging-list-template */ ?>

<?php $offre = _get_offer_details(); ?>
<?php _add_meta_data($offre, 'details'); ?>
<?php get_header(); ?>

<article class="pivot-offer row">
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
        <h2 class="pivot-title"><?php print $offre->nom->__toString(); ?></h2>

        <!--<div class="tab-content card">-->
        <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></h5>
        <section class="card lis-brd-light wow fadeInUp mb-4" style="visibility: visible; animation-name: fadeInUp;">
            <div class="card-body p-4">
                <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
            </div>
        </section>

        <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-thumb-tack"></i><?php esc_html_e('Equipments', 'pivot')?></h5>
        <section class="card lis-brd-light wow fadeInUp mb-4" style="visibility: visible; animation-name: fadeInUp;">
          <div class="card-body p-4">
            <div class="row">
              <div class="col-12">
                <ul id="pivot-equipments" class="list-unstyled lis-line-height-2 mb-0">
                  <?php foreach($offre->spec as $specification): ?>
                    <?php if($specification->urnCat->__toString() == 'urn:cat:eqpsrv'): ?>
                      <?php $urn_value = _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                      <li class="pivot-service <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                          <i class="pr-2"><img alt="<?php print $urn_value; ?>" title="<?php print $urn_value; ?>" class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/></i>
                        <?php print $urn_value; ?>
                      </li>
                    <?php endif ?>
                  <?php endforeach ?>
                </ul>
              </div>
            </div>
          </div>
        </section>

        <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-info"></i><?php esc_html_e('Extra infos', 'pivot')?></h5>
        <section class="card lis-brd-light wow fadeInUp mb-4" style="visibility: visible; animation-name: fadeInUp;">
          <div class="card-body p-4">
            <ul id="pivot-extra-infos" class="list-unstyled lis-line-height-2 mb-0 ">
              <?php foreach($offre->spec as $specification): ?>
                <?php if($specification->urnCat->__toString() == 'urn:cat:accueil' && $specification->urnSubCat->__toString() != 'urn:cat:accueil:langpar'): ?>
                  <li class="list-group-item list-group-item-action pivot-details <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                    <span>
                      <?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                      <?php if($specification->type->__toString() == 'Boolean'): ?>
                        <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
                      <?php else: ?>
                        : <span class="pivot-desc item"><?php print _get_urn_value($offre, $specification->attributes()->urn->__toString()) ;?></span>
                      <?php endif ?>
                    </span>
                  </li>
                <?php endif ?>
              <?php endforeach ?>
            </ul>
          </div>
        </section>

      </div>
    </div>
  </div>

  <aside class="col-xs-12 col-md-3">

    <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-id-card-o"></i><?php esc_html_e('Contact', 'pivot')?></h5>
    <section class="pivot-contacts card lis-brd-light wow fadeInUp mb-4">
      <div class="card-body p-4">
        <h6 class="pivo-title"><?php print $offre->nom->__toString(); ?></h6>
        <ul class="list-unstyled lis-line-height-2 mb-0">
        <?php foreach($offre->spec as $specification): ?>
          <?php if($specification->urnCat->__toString() == 'urn:cat:moycom'): ?>
            <li>
              <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
              <?php switch ($specification->type->__toString()): 
                case 'EMail': ?>
                  <a class="<?php print $specification->type->__toString(); ?>" href="mailto:<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
                  <?php break ?>
                <?php case 'URL': ?>
                  <a class="<?php print $specification->type->__toString(); ?>" target="_blank" href="<?php print esc_url($specification->value->__toString()); ?>"><?php print esc_url($specification->value->__toString()); ?></a>
                  <?php break ?>
                <?php case 'GSM': ?>
                  <a class="<?php print $specification->type->__toString(); ?>" href="tel:<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
                  <?php break ?>
                <?php case 'Phone': ?>
                  <a class="<?php print $specification->type->__toString(); ?>" href="tel:<?php print $specification->value->__toString(); ?>"><?php print $specification->value->__toString(); ?></a>
                  <?php break ?>
                <?php default: ?>
                  <?php if (esc_url($specification->value->__toString())): ?>
                    <a class="<?php print $specification->type->__toString(); ?>"  target="_blank" href="<?php print esc_url($specification->value->__toString()); ?>"><?php print esc_url($specification->value->__toString()); ?></a>
                  <?php else: ?>
                    <?php print $specification->value->__toString(); ?>
                  <?php endif; ?>
                  <?php break ?>  
              <?php endswitch ?>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
        </ul>
            
        <ul class="adr list-unstyled lis-line-height-2 mb-0">
          <li class="street-address"><i class="fa fa-map-o"></i> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></li>
          <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
          <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
          <li class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></li>
          <li class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></li>
          <li class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></li>
        </ul>
            
      </div>
    </section>
    
    <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-language"></i><?php esc_html_e('Language(s)', 'pivot')?></h5>
    <section class="pivot-language card lis-brd-light wow fadeInUp mb-4">
      <div class="card-body p-4">
      <ul class="list-unstyled lis-line-height-2 mb-0">
      <?php foreach($offre->spec as $specification): ?>
        <?php if($specification->urnSubCat->__toString() == 'urn:cat:accueil:langpar' && !empty(_get_urn_documentation($specification->attributes()->urn->__toString()))): ?>
          <li class="p-1 <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
            <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
            <span class="ranking-label"><?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?></span>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
      </div>
      </ul>
    </section>
    
    <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-list-ol"></i><?php esc_html_e('Ranking', 'pivot')?></h5>
    <section class="pivot-ranking card lis-brd-light wow fadeInUp mb-4">
      <div class="card-body p-4">
      <ul class="list-unstyled lis-line-height-2 mb-0">
      <?php foreach($offre->spec as $specification): ?>
        <?php if($specification->urnCat->__toString() == 'urn:cat:classlab' && !empty(_get_urn_documentation($specification->attributes()->urn->__toString()))): ?>
          <li class="p-1 <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
            <span class="ranking-label"><?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?>: </span>
            <?php if(strpos($specification->value->__toString(),'urn:') !== FALSE): ?>
              <span class="ranking-value"><?php print _get_urn_documentation($specification->value->__toString()); ?></span>
            <?php endif; ?>
            <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
      </ul>
      </div>
    </section>
    
    <?php $url = get_bloginfo('wpurl').'/details/'.$offre->attributes()->codeCgt->__toString(); ?>
    <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-share-square-o"></i><?php esc_html_e('Share', 'pivot')?></h5>
    <section class="pivot-share card lis-brd-light wow fadeInUp mb-4">
      <div class="card-body p-4">
        <span><a class="social-icon" href="https://www.facebook.com/sharer.php?u=<?php print $url;?>&amp;t=<?php print $offre->nom->__toString();?>" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urlfacebook;h=35" alt="Facebook Share button" title="Facebook Share button"/></a></span>
        <span><a class="social-icon" href="https://twitter.com/share?text=<?php print $offre->nom->__toString();?>&amp;url=<?php print $url;?>" target="_blank"><img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:fld:urltwitter;h=35" alt="Twitter Share button" title="Twitter Share button"/></a></span>
      </div>
    </section>

  </aside>

</article>


<?php get_footer();