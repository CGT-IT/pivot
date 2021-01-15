
<?php global $offre_meta_data; ?>
<!--if offer comes from url or by arguments-->
<?php if(isset($args->estActive)): ?>
  <?php $offre = $args; ?>
<?php else: ?>
  <?php $offre = _get_offer_details(); ?>
  <?php _check_is_offer_active($offre); ?>
  <?php _add_meta_data($offre, 'details'); ?>
  <?php get_header(); ?>
<?php endif;?>

<article class="pivot-offer row m-3">
  <div class="col-xs-12 col-md-8">
    <div class="row">
      
      <?php print pivot_template('pivot-image-slider', $offre); ?>
        
      <div class="col-12">
        <div class="row">
          <div class="col-12">
            <div class="row mb-2 mt-2">
              <div class="col-10">
                <h1 class="pivot-title"><?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?></h1>
              </div>    
              <div class="col-2">
                <?php print _add_section_share($offre); ?>
              </div>      
            </div>
          </div>
        </div>

        <p class="section-title h5 lis-font-weight-500"><i class="fas fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></p>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
          </div>
        </section>

        <p class="section-title h5 lis-font-weight-500"><i class="fas fa-align-right pr-2 fa-thumb-tack"></i><?php esc_html_e('Equipments', 'pivot')?></p>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <div class="row">
              <div class="col-12">
                <ul id="pivot-equipments" class="list-unstyled lis-line-height-2 mb-0">
                  <?php foreach($offre->spec as $specification): ?>
                    <?php if($specification->urnCat->__toString() == 'urn:cat:eqpsrv'): ?>
                      <?php $urn_value = _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                      <li class="pivot-service <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                          <i class="pr-2"><img alt="<?php print $urn_value; ?>" title="<?php print $urn_value; ?>" class="pivot-picto" src="<?php print get_option('pivot_uri').'img/'.$specification->attributes()->urn->__toString(); ?>;h=16"/></i>
                        <?php print $urn_value; ?>
                      </li>
                    <?php endif ?>
                  <?php endforeach ?>
                </ul>
              </div>
            </div>
          </div>
        </section>
        
        <?php print _add_section_linked_offers($offre); ?>
      </div>
    </div>
  </div>

  <aside class="col-xs-12 col-md-4">
    <?php print _add_section_contact($offre); ?>
    <?php print _add_section_booking($offre); ?>
    <?php print _add_section($offre, 'urn:cat:accueil', __('Extra infos'), 'fa-info'); ?>
  </aside>
    
  <?php // print pivot_template('map-orthodromic', $offre->adresse1->idIns); ?>
    
</article>

<?php if(!isset($args->estActive)): ?>
  <?php get_footer(); ?>
<?php endif;?>