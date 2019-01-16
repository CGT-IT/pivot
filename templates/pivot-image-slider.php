<?php $offre = $args; ?>

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