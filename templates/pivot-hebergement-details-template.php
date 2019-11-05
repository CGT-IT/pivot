
<?php global $offre_meta_data; ?>
<!--if offer comes from url or by arguments-->
<?php if($args): ?>
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
            <div class="row">
              <div class="col-10">
                <h2 class="pivot-title"><?php print _get_urn_value($offre, 'urn:fld:nomofr').' '._get_ranking_picto($offre); ?></h2>
              </div>    
              <div class="col-2">
                <?php print _add_section_share($offre); ?>
              </div>      
            </div>
          </div>
          <div class="row ml-auto mb-1">
            <div class="col-12">
              <?php print _add_section_themes($offre); ?>
            </div>
          </div>
        </div>

        <h5 class="lis-font-weight-500"><i class="fas fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
          </div>
        </section>

        <h5 class="lis-font-weight-500"><i class="fas fa-align-right pr-2 fa-thumb-tack"></i><?php esc_html_e('Equipments', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <div class="row">
              <div class="col-12">
                <ul id="pivot-equipments" class="list-unstyled lis-line-height-2 mb-0">
                  <?php foreach($offre->spec as $specification): ?>
                    <?php if($specification->urnCat->__toString() == 'urn:cat:eqpsrv'): ?>
                      <?php $urn_value = _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                      <li class="pivot-service <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                          <i class="pr-2"><img alt="<?php print $urn_value; ?>" title="<?php print $urn_value; ?>" class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/></i>
                        <?php print $urn_value; ?>
                      </li>
                    <?php endif ?>
                  <?php endforeach ?>
                </ul>
              </div>
            </div>
          </div>
        </section>

        <h5 class="lis-font-weight-500"><i class="fas fa-align-right pr-2 fa-info"></i><?php esc_html_e('Extra infos', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <ul id="pivot-extra-infos" class="list-unstyled lis-line-height-2 mb-0 ">
              <?php foreach($offre->spec as $specification): ?>
                <?php if($specification->urnCat->__toString() == 'urn:cat:accueil' && $specification->urnSubCat->__toString() != 'urn:cat:accueil:langpar' && $specification->attributes()->urn->__toString() != 'urn:fld:attestincend'): ?>
                  <li class="list-group-item list-group-item-action pivot-details <?php print str_replace(":", "-", $specification->attributes()->urn->__toString()); ?>">
                    <span>
                      <?php print _get_urn_documentation($specification->attributes()->urn->__toString()); ?>
                      <?php if($specification->type->__toString() == 'Boolean'): ?>
                        <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/<?php print $specification->attributes()->urn->__toString(); ?>;h=16"/>
                      <?php else: ?>
                        : <span class="pivot-desc item"><?php print _get_urn_value($offre, $specification->attributes()->urn->__toString()) ;?></span>
                      <?php endif ?>
                    </span>
                  </li>
                <?php endif ?>
              <?php endforeach ?>
            </ul>
          </div>
        </section>
        
        <?php print _add_section_linked_offers($offre); ?>
      </div>
    </div>
  </div>

  <aside class="col-xs-12 col-md-4">

    <?php print _add_section_contact($offre); ?>
    <?php print _add_section_booking($offre); ?>
    
    <?php print _add_section($offre, 'urn:cat:accueil:langpar', __('Language(s)'), 'fa-language', 1); ?>
    <?php print _add_section($offre, 'urn:cat:classlab', __('Themes'), 'fa-list-alt'); ?>

  </aside>
    
</article>

<?php get_footer();