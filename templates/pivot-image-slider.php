<?php $offre = $args; ?>

<div class="col-12">
  <!--Image carousel-->
  <div id="pivotCarousel" class="carousel slide" data-ride="carousel">
    <!-- Indicators -->
    <ol class="carousel-indicators">
    <?php $i = 0; ?>
    <?php foreach($offre->relOffre as $relation): ?>
      <?php foreach($relation as $specification): ?>
        <?php foreach ($specification->spec as $spec): ?>
          <?php if($spec->attributes()->urn == 'urn:fld:typmed'): ?>
            <!--if it's well an image-->
            <?php if($spec->value->__toString() == "urn:val:typmed:photo"): ?>
              <!--Add indicator and set active if first one-->
              <li data-target="#pivotCarousel" data-slide-to="<?php print $i;?>" class="<?php if($i == 0) {print 'active';}?>"></li>
              <?php $i++; ?>
            <?php endif; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    <?php endforeach; ?>
    </ol>

    <!-- Images -->
    <div class="carousel-inner">
      <?php $i = 0; ?>
      <?php foreach($offre->relOffre as $relation): ?>
        <!--if it's well an image-->
        <?php foreach($relation as $specification): ?>
          <?php foreach ($specification->spec as $spec): ?>
            <?php if($spec->attributes()->urn == 'urn:fld:typmed'): ?>
              <!--if it's well an image-->
              <?php if($spec->value->__toString() == "urn:val:typmed:photo"): ?>
                <div class="carousel-item <?php if($i == 0) {print 'active';}?>">
                  <?php foreach($relation as $specification): ?>
                    <?php if(strpos(_get_urn_value($specification, 'urn:fld:url'), 'pivotmedia')): ?>
<!--                      <iframe width="1300" height="600" src="https://www.youtube.com/embed/Yc4JnybTqMw?rel=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>-->

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
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
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