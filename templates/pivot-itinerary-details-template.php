
<?php $offre = _get_offer_details(); ?>
<?php _add_meta_data($offre, 'details'); ?>
<?php get_header(); ?>


<article class="pivot-offer row m-3">
  <div class="col-xs-12 col-md-8">
    <div class="row">
        
      <?php print pivot_template('pivot-image-slider', $offre); ?>
      <div class="col-12">
        <div class="row">
          <div class="col-12">
            <div class="row mb-2">
              <div class="col-10">
                <h2 class="pivot-title"><?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?></h2>
              </div>    
              <div class="col-2">
                <?php print _add_section_share($offre); ?>
              </div>      
            </div>
          </div>
        </div>  

        <h5 class="lis-font-weight-500"><i class="fas fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
          </div>
        </section>
        
        <?php print _add_section($offre,'urn:cat:accueil', __('Extra infos'), 'fa-info'); ?>
        <?php print _add_section_linked_offers($offre); ?>
      </div>
    </div>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js" charset="utf-8"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.2/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.2/leaflet-src.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.4.0/gpx.js"></script>
    <link rel="stylesheet" href="https://raruto.github.io/cdn/leaflet-elevation/0.0.5/leaflet-elevation.css" />
    <script src="https://raruto.github.io/cdn/leaflet-elevation/0.0.5/leaflet-elevation.js"></script>

    <?php foreach($offre->relOffre as $relation): ?>
      <?php foreach($relation as $specification): ?>
        <?php foreach ($specification->spec as $spec): ?>
          <?php if($spec->attributes()->urn == 'urn:fld:typmed'): ?>
            <!--if it's well a GPX-->
            <?php if($spec->value->__toString() == "urn:val:typmed:gpx"): ?>
              <div id="gpx-file-id" class="d-none"><?php print $relation->offre->attributes()->codeCgt; ?></div>
              <p><button class="btn"><i class="fa fa-download"></i>
                <a id="gpx-file" href="<?php print _get_urn_value($relation->offre, 'urn:fld:url'); ?>" download target="_blank" type="application/octet-stream">
                  <?php _e('Download GPX file','pivot'); ?>
                </a>
              </button></p>
              <div id="gpx-map" class="gpx" style="height: 500px"></div>
              <div id="elevation-div" class="gpx" style="height: 30%; width: 100%; margin-top: 20px;"></div>

            <?php endif; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    <?php endforeach; ?>

  </div>

  <aside class="col-xs-12 col-md-3">
      
    <?php print _add_section_contact($offre); ?>
    <?php print _add_section($offre,'urn:cat:classlab', __('Themes'), 'fa-list-alt'); ?>
    <?php print _add_section($offre,'urn:cat:desc', __('Route details'), 'fa-map-signs'); ?>
  </aside>
</article>

<?php get_footer();