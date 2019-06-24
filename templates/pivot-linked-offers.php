<?php $offre = $args; ?>

<h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-paperclip"></i><?php _e('Linked offers', 'pivot') ?></h5>
<div class="carousel slide" data-ride="carousel" id="quote-carousel">
  <!-- Carousel Slides -->
  <div class="carousel-inner">
    <?php $i= 0; ?>
    <?php foreach($offre->relOffre as $relation): ?>
      <!--The linked offer shouldn't be a contact or a media-->
      <?php if(!(in_array($relation->offre->typeOffre->attributes()->idTypeOffre->__toString(), array('268', '23')))): ?>
        <!--The linked offer type should exist in "pivot offer type" otherwise no template will be used-->
        <?php if(pivot_get_offer_type($relation->offre->typeOffre->attributes()->idTypeOffre->__toString())): ?>
          <?php $url = get_bloginfo('wpurl').'/details/'.$relation->offre->attributes()->codeCgt->__toString().'&type='.$relation->offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
          <div class="carousel-item <?php print ($i++ == 0)?"active":"" ?>">
            <blockquote>
              <a class="text-dark" title="<?php echo esc_attr('Link to', 'pivot') .' '. _get_urn_value($relation->offre, 'urn:fld:nomofr'); ?>" href="<?php print $url; ?>">
                <div class="row">
                  <div class="col-sm-3 text-center">
                    <img class="pivot-img zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $relation->offre->attributes()->codeCgt->__toString() ;?>;w=256;h=170"/>
                  </div>
                  <div class="col-sm-9">
                    <p><?php print _get_urn_value($relation->offre, 'urn:fld:descmarket'); ?></p>
                    <small><?php print _get_urn_value($relation->offre, 'urn:fld:nomofr'); ?></small>
                  </div>
                </div>
              </a>
            </blockquote>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>

    <!-- Bottom Carousel Indicators -->
    <ol class="carousel-indicators">  
    <?php for($x = 0; $x <= $i; $x++): ?>
      <li data-target="#quote-carousel" data-slide-to="<?php print $x; ?>" <?php print ($x == 0)?'class="active"':''; ?>></li>
    <?php endfor; ?>
    </ol>
  </div>

  <!-- Carousel Buttons Next/Prev -->
  <a class="carousel-control-prev" href="#quote-carousel" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#quote-carousel" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>