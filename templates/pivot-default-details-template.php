
<?php $offre = _get_offer_details(); ?>
<?php _add_meta_data($offre, 'details'); ?>
<?php get_header(); ?>


<article class="pivot-offer row m-3">
  <div class="col-xs-12 col-md-8">
    <div class="row">
        
      <?php print pivot_template('pivot-image-slider', $offre); ?>
        
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
        <?php print _add_section_linked_offers($offre); ?>
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