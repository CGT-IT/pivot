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
            <?php if($spec->value->__toString() == "urn:val:typmed:photo"): ?>
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
      <?php $i = 0;?>
      <?php foreach($offre->relOffre as $relation): ?>
        <?php foreach($relation as $specification): ?>
          <?php foreach ($specification->spec as $spec): ?>
            <?php if($spec->attributes()->urn == 'urn:fld:typmed'): ?>
              <?php if($spec->value->__toString() == "urn:val:typmed:photo"): ?>
                <div class="carousel-item <?php if($i == 0) {print 'active';}?>">
                  <?php foreach($relation as $specification): ?>
                    <?php $media_name = _get_urn_value($specification, 'urn:fld:nomofr'); ?>
                    <?php if(strpos(_get_urn_value($specification, 'urn:fld:url'), 'pivotmedia')): ?>
                      <figure class="embed-responsive embed-responsive-16by9">
                        <img alt="<?php print $specification->nom->__toString();?>" class="embed-responsive-item pivot-img pivot-img-details" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $relation->offre->attributes()->codeCgt->__toString(); ?>;w=1920"/>
                        <?php if($copyright = _get_urn_value($specification, 'urn:fld:copyr')): ?>
                          <figcaption><small><?php print _construct_media_copyright($copyright, _get_urn_value($specification, 'urn:fld:date'));?></small></figcaption>
                        <?php endif; ?>
                      </figure>
                    <?php else: ?>
                      <figure class="embed-responsive embed-responsive-16by9">
                        <img alt="<?php print $specification->nom->__toString();?>" class="embed-responsive-item pivot-img pivot-img-details" src="<?php print _get_urn_value($specification, 'urn:fld:url'); ?>"/>
                        <?php if($copyright = _get_urn_value($specification, 'urn:fld:copyr')): ?>
                          <figcaption><small><?php print _construct_media_copyright($copyright, _get_urn_value($specification, 'urn:fld:date'));?></small></figcaption>
                        <?php endif; ?>
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