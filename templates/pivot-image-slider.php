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
    <div class="carousel-inner" style="height:auto;max-height: 600px;">
      <?php $i = 0;?>
      <?php foreach($offre->relOffre as $relation): ?>
        <!--if it's well an image-->
        <?php foreach($relation as $specification): ?>
          <?php foreach ($specification->spec as $spec): ?>
            <?php if($spec->attributes()->urn == 'urn:fld:typmed'): ?>
              <!--if it's well an image-->
              <?php if($spec->value->__toString() == "urn:val:typmed:photo"): ?>
                <div class="carousel-item <?php if($i == 0) {print 'active';}?>">
                  <?php foreach($relation as $specification): ?>
                    <?php $media_name = _get_urn_value($specification, 'urn:fld:nomofr'); ?>
                    <?php if(strpos(_get_urn_value($specification, 'urn:fld:url'), 'pivotmedia')): ?>
                      <figure>
                        <img alt="<?php print $media_name;?>" class="pivot-img pivot-img-details" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $relation->offre->attributes()->codeCgt->__toString(); ?>;h=600"/>
                        <?php if($copyright = _get_urn_value($specification, 'urn:fld:copyr')): ?>
                          <footer><small><?php print _construct_media_copyright($copyright, _get_urn_value($specification, 'urn:fld:date'));?></small></footer>
                        <?php endif; ?>
                        <figcaption><?php print $media_name;?></figcaption>
                      </figure>
                    <?php else: ?>
                      <figure>
                        <img alt="<?php print $media_name;?>" class="pivot-img pivot-img-details" src="<?php print _get_urn_value($specification, 'urn:fld:url'); ?>"/>
                        <?php if($copyright = _get_urn_value($specification, 'urn:fld:copyr')): ?>
                          <footer><small><?php print _construct_media_copyright($copyright, _get_urn_value($specification, 'urn:fld:date'));?></small></footer>
                        <?php endif; ?>
                        <figcaption><?php print $media_name;?></figcaption>
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

      <?php if($i > 1): ?>
        <!-- Left and right controls -->
        <a class="carousel-control-prev" href="#pivotCarousel" data-slide="prev">
          <span class="carousel-control-prev-icon"></span>
          <span class="sr-only"><?php esc_html_e('Previous', 'pivot')?></span>
        </a>
        <a class="carousel-control-next" href="#pivotCarousel" data-slide="next">
          <span class="carousel-control-next-icon"></span>
          <span class="sr-only"><?php esc_html_e('Next', 'pivot')?></span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>