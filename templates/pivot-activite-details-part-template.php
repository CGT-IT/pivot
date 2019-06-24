
<?php $offre = $args; ?>

<div class="offers-area-col col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12 mb-3">
  
  <?php $url = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
  <div class="card text-left pivot-offer">
    <div class="container-img">
      <img class="pivot-img card-img-top zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=428;h=284"/>
      <div class="p-3 position-absolute fixed-top text-right">
        <?php print _add_section_event_dates($offre); ?>
      </div>
      <div class="p-3 position-absolute fixed-bottom text-white" style="background: rgba(0, 0, 0, 0.5);">
        <i class="fas fa-map-marker-alt"></i>
        <?php print $offre->adresse1->cp; ?> 
        <?php print $offre->adresse1->commune->value->__toString(); ?>
      </div>
    </div>
    <!--<div class="card-body">-->
      <a class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') .' '. _get_urn_value($offre, 'urn:fld:nomofr'); ?>" href="<?php print $url; ?>"></a>
    <!--</div>-->
  </div>
      
</div>