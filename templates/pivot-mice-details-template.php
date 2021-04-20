<?php global $offre_meta_data; ?>
<?php if(isset($args->estActive)): // if offer comes by arguments, means included in an existing page ?>
   <?php $offre = $args; ?>
<?php else: ?>
  <?php $current_template_name = basename(__FILE__, '.php'); ?>
  <?php $offre = _get_offer_details(null, 3, $current_template_name); ?>
  <?php if(get_option('pivot_transient') == 'on' && $offre['content']): ?>
    <?php $key = 'pivot_meta_'.$offre['offerid'];
        if (get_transient($key)){
          $offre_meta_data = get_transient($key);
        } 
    ?>
    <?php get_header(); ?>
    <?php print json_decode($offre['content']); ?>
  <?php else: ?>
    <?php _check_is_offer_active($offre); ?>
    <?php
      if(get_option('pivot_transient') == 'on'){
        $key = 'pivot_meta_'.$offre->attributes()->codeCgt->__toString();
        if (get_transient($key) === false){
          $default_image = _get_offer_default_image($offre, null, null);
          $offre_meta_data = _add_meta_data($offre, 'details', $default_image); 
          set_transient($key, $offre_meta_data, get_option('pivot_transient_time'));
        }else{
          $offre_meta_data = get_transient($key);
        }
      }else{
        $default_image = _get_offer_default_image($offre, null, null);
        $offre_meta_data = _add_meta_data($offre, 'details', $default_image); 
      }
    ?>
    <?php get_header(); ?>
  <?php endif;?>
<?php endif;?>

<?php if(!isset($offre['content'])): ?>
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

        <p class="section-title h5 lis-font-weight-500"><i class="fa fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></p>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
          </div>
        </section>

        <?php print _add_section($offre,'urn:cat:accueil', __('Extra infos'), 'fa-info'); ?>
        <?php print _add_section_mice_rooms($offre, __('Rooms', 'pivot'), 'fa-building'); ?>

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
<?php endif;?>

<?php if(!isset($args->estActive) || $offre['content'] == true): ?>
  <?php get_footer(); ?>
<?php endif; ?>