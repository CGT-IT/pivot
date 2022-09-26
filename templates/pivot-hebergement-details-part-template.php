
<?php $offre = $args; ?>

<div class="offers-area-col <?php print _set_nb_col($offre->map, $offre->nb_per_row) . 'nb-col-' . $offre->nb_per_row; ?> mb-3">
    <?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
    <?php $idTypeOffre = $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
    <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
    <?php $lang = substr(get_locale(), 0, 2); ?>
    <?php $url = get_bloginfo('wpurl') . (($lang == 'fr') ? '' : '/' . $lang) . '/details/' . $codeCGT . '&amp;type=' . $idTypeOffre; ?>
    <div class="card text-left pivot-offer">
        <div class="card-orientation <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'card-horizontal'; ?>">
            <div class="container-img embed-responsive embed-responsive-16by9 <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'col-5 p-0 my-auto'; ?>" >
                <img alt="<?php print $offerTitle; ?>" class="embed-responsive-item pivot-img card-img-top zoom pivot-img-list" src="<?php print _get_offer_default_image($offre); ?>"/>
            </div>
            <p class="h6 title-header card-header" <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'style="display:none"'; ?>>
                <a target="_blank" class="text-dark" title="<?php echo __('Link to', 'pivot') . ' ' . $offerTitle; ?>" href="<?php print $url; ?>">
                    <?php print $offerTitle; ?>
                </a>
            </p>
            <div class="card-body <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'col-7 pt-2 pb-0'; ?>">
                <p class="h6 title-no-header" <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? 'style="display:none"' : ''; ?>>
                    <a target="_blank" class="text-dark" title="<?php echo __('Link to', 'pivot') . ' ' . $offerTitle; ?>" href="<?php print $url; ?>">
                        <?php print $offerTitle; ?>
                    </a>
                </p>
                <p class="card-text">
                    <?php print $offre->typeOffre->label->value->__toString() . '  ' . _get_ranking_picto($offre); ?>
                </p>
                <?php $capbase = _get_urn_value($offre, 'urn:fld:capbase'); ?>
                <?php if (!empty($capbase) && $capbase != 0): ?>
                  <p class="card-text">
                      <i class="fas fas-align-right pr-2 fa-bed"></i><?php print $capbase; ?>
                      <?php $capadd = _get_urn_value($offre, 'urn:fld:capadd'); ?>
                      <?php if (!empty($capadd) && $capadd != 0): ?>
                        <?php print ' ' . __('à', 'pivot') . ' ' . ($capbase + $capadd); ?>
                      <?php endif; ?>
                  </p>
                <?php endif; ?>
                <?php
                switch ($idTypeOffre) {
                  case 1:
                    $prixmin = _get_urn_value($offre, 'urn:fld:tarifind:pmin:dblptdej');
                    break;
                  case 2:
                  case 4:
                    $prixmin = _get_urn_value($offre, 'urn:fld:tarifind:pmin:webssais');
                    break;
                  case 3:
                    $prixmin = _get_urn_value($offre, 'urn:fld:tarifind:pmin:ch2pptdej');
                    break;
                  case 5:
                    $prixmin = _get_urn_value($offre, 'urn:fld:tarifind:pmin:emp2a2en');
                    break;
                  case 6:
                  case 7:
                    $prixmin = _get_urn_value($offre, 'urn:fld:tarifind:pmin');
                    break;
                }
                ?>
                <?php if (!empty($prixmin) && $prixmin != 0): ?>
                  <p class="card-text">
                      <i class="fas fas-align-right pr-2 fa-euro-sign"></i><?php print __('à partir de ', 'pivot') . $prixmin . '€'; ?>
                  </p>
                <?php endif; ?>
                <?php $phone = _get_urn_value($offre, 'urn:fld:phone1'); ?>
                <?php if ($phone != ''): ?>
                  <p class="card-text">
                      <i class="fas fa-phone"></i>
                      <a href="tel:<?php print $phone; ?>"><?php print $phone; ?></a>
                  </p>
                <?php else: ?>
                  <?php $mobile = _get_urn_value($offre, 'urn:fld:mobi1'); ?>
                  <?php if ($mobile != ''): ?>
                    <p class="card-text">
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php print $mobile; ?>"><?php print $mobile; ?></a>
                    </p>
                  <?php endif; ?>
                <?php endif; ?>
                <p class="card-text">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php print $offre->adresse1->cp; ?>
                    <?php print $offre->adresse1->localite->value->__toString(); ?>
                </p>
                <?php print pivot_template('booking-part', $offre); ?>
                <span class="pivot-id-type-offre d-none item"><?php print $idTypeOffre; ?></span>
                <span class="pivot-code-cgt d-none item"><?php print $codeCGT; ?></span>
                <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
                <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>
            </div>
        </div>
    </div>

</div>