<?php global $offre_meta_data; ?>
<?php if (isset($args->estActive)): // if offer comes by arguments, means included in an existing page            ?>
  <?php $offre = $args; ?>
<?php else: ?>
  <?php $current_template_name = basename(__FILE__, '.php'); ?>
  <?php $offre = _get_offer_details(null, 3, $current_template_name); ?>
  <?php if (get_option('pivot_transient') == 'on' && $offre['content']): ?>
    <?php
    $key = 'pivot_meta_' . $offre['offerid'];
    if (get_transient($key)) {
      $offre_meta_data = get_transient($key);
    }
    ?>
    <?php get_header(); ?>
    <?php print json_decode($offre['content']); ?>
  <?php else: ?>
    <?php _check_is_offer_active($offre); ?>
    <?php
    if (get_option('pivot_transient') == 'on') {
      $key = 'pivot_meta_' . $offre->attributes()->codeCgt->__toString();
      if (get_transient($key) === false) {
        $default_image = _get_offer_default_image($offre, null, null);
        $offre_meta_data = _add_meta_data($offre, 'details', $default_image);
        set_transient($key, $offre_meta_data, get_option('pivot_transient_time'));
      } else {
        $offre_meta_data = get_transient($key);
      }
    } else {
      $default_image = _get_offer_default_image($offre, null, null);
      $offre_meta_data = _add_meta_data($offre, 'details', $default_image);
    }
    ?>
    <?php get_header(); ?>
  <?php endif; ?>
<?php endif; ?>

<?php if (!isset($offre['content'])): ?>
  <article class="pivot-offer row m-3">
      <div class="col-xs-12 col-md-8">
          <div class="row">

              <?php print pivot_template('pivot-image-slider', $offre); ?>
              <div class="col-12">
                  <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
                  <h1 class="pivot-title"><?php print $offerTitle; ?></h1>

                  <p class="section-title h5 lis-font-weight-500"><i class="fas fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot') ?></p>
                  <section class="card lis-brd-light mb-4">
                      <div class="card-body p-4">
                          <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket'); ?></p>
                      </div>
                  </section>

                  <?php print _add_section($offre, 'urn:cat:accueil', __('Extra infos'), 'fa-info'); ?>
                  <?php print _add_section_linked_offers($offre); ?>
              </div>
          </div>

          <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js" charset="utf-8"></script>
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.2/leaflet.css" />
          <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.2/leaflet-src.js"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.4.0/gpx.js"></script>
          <?php print '<script src="' . MY_PLUGIN_URL . '/js/itinerary.js' . '"></script>'; ?>
          <?php foreach ($offre->relOffre as $relation): ?>
            <?php foreach ($relation as $specification): ?>
              <?php foreach ($specification->spec as $spec): ?>
                <?php if ($spec->attributes()->urn == 'urn:fld:typmed'): ?>
                  <!--if it's well a GPX-->
                  <?php if ($spec->value->__toString() == "urn:val:typmed:gpx"): ?>
                    <div id="gpx-file-id" class="d-none"><?php print $relation->offre->attributes()->codeCgt; ?></div>
                    <p><button class="btn"><i class="fa fa-download"></i>
                            <a id="gpx-file" href="<?php print MY_PLUGIN_URL; ?>inc/external/gpxdownloader.php?n=<?php print preg_replace('/[^A-Za-z0-9]/', "", $offerTitle); ?>&f=<?php print _get_urn_value($relation->offre, 'urn:fld:url'); ?>" download="gpxfile.gpx" target="_blank" type="application/octet-stream">
                                <?php _e('Download GPX file', 'pivot'); ?>
                            </a>
                        </button></p>
                    <div id="gpx-map" class="gpx" style="height: 500px"></div>

                  <?php endif; ?>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php endforeach; ?>

      </div>

      <aside class="col-xs-12 col-md-3">

          <?php print _add_section_contact($offre); ?>
          <?php print _add_section($offre, 'urn:cat:classlab', __('Themes'), 'fa-list-alt'); ?>
          <?php print _add_section($offre, 'urn:cat:desc', __('Route details'), 'fa-map-signs'); ?>
      </aside>

  </article>
<?php endif; ?>

<?php if (!isset($args->estActive) || $offre['content'] == true): ?>
  <?php get_footer(); ?>
<?php endif; ?>