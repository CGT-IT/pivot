
<?php global $offre_meta_data; ?>
<!--if offer comes from url or by arguments-->
<?php if(isset($args)): ?>
  <?php $offre = $args; ?>
<?php else: ?>
  <?php $offre = _get_offer_details(); ?>
  <?php _add_meta_data($offre, 'details'); ?>
  <?php get_header('pivot'); ?>
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
                <h2 class="pivot-title"><?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?></h2>
              </div>    
              <div class="col-2">
                <?php print _add_section_share($offre); ?>
              </div>      
            </div>
          </div>
        </div>

        <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
          </div>
        </section>
        
        <?php print _add_section($offre,'urn:cat:accueil', __('Extra infos'), 'fa-info'); ?>
        <?php print _add_section_linked_offers($offre); ?>
      </div>
    </div>
  </div>

  <aside class="col-xs-12 col-md-3">
      
    <?php print _add_section_contact($offre); ?>

    <?php print _add_section($offre,'urn:cat:accueil:langpar', __('Language(s)'), 'fa-language', 1); ?>
    <?php print _add_section($offre,'urn:cat:prod', __('Product(s)'), 'fa-shopping-basket'); ?>
    <?php print _add_section($offre,'urn:cat:tarif', __('Price(s)'), 'fa-eur'); ?>
    <?php print _add_section($offre,'urn:cat:visite', __('Visit'), 'fa-map-signs'); ?>
    <?php print _add_section($offre,'urn:cat:eqpsrv', __('Equipments & services'), 'fa-thumb-tack'); ?>
    <?php print _add_section($offre,'urn:cat:classlab', __('Themes'), 'fa-list-alt'); ?>
  </aside>
    
  <?php // print pivot_template('map-orthodromic', $offre->adresse1->idIns); ?>

</article>

<?php if(!isset($args)): ?>
  <?php get_footer(); ?>
<?php endif;?>