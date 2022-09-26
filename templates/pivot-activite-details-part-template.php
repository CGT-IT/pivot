<?php $offre = $args; ?>

<div class="offers-area-col <?php print _set_nb_col($offre->map, $offre->nb_per_row) . 'nb-col-' . $offre->nb_per_row; ?> mb-3">
    <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
    <?php $idTypeOffre = $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
    <?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
    <?php $lang = substr(get_locale(), 0, 2); ?>
    <?php $url = get_bloginfo('wpurl') . (($lang == 'fr') ? '' : '/' . $lang) . '/details/' . $codeCGT . '&type=' . $idTypeOffre; ?>
    <div class="card text-left pivot-offer">
        <div class="card-orientation <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'card-horizontal'; ?>">
            <div class="container-img embed-responsive embed-responsive-16by9 <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'col-5 p-0 my-auto'; ?>" >
                <img alt="<?php print $offerTitle; ?>" class="embed-responsive-item pivot-img card-img-top zoom pivot-img-list" src="<?php print _get_offer_default_image($offre); ?>"/>
            </div>
            <p class="h6 title-header card-header" <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? '' : 'style="display:none"'; ?>><?php print $offerTitle; ?></p>
            <div class="card-body">
                <span class="text-muted">
                    <?php $content = ''; ?>
                    <?php foreach ($offre->spec as $specification): ?>
                      <?php if ($specification->urnSubCat->__toString() == 'urn:cat:classlab:classif' && !empty(_get_urn_documentation($specification->attributes()->urn->__toString()))): ?>
                        <?php $content .= _get_urnValue_translated($offre, $specification) . ' / '; ?>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    <?php print $content; ?>
                </span>
                <p class="h6 title-no-header" <?php print ($offre->map != 1 || wp_is_mobile() == 1) ? 'style="display:none"' : ''; ?>><?php print $offerTitle; ?></p>
                <div class="card-text text-uppercase dates"><?php print _add_section_event_dates($offre); ?></div>
                <p class="card-text text-muted city"><i class="fas fa-map-marker-alt"></i> <?php print $offre->adresse1->localite->value->__toString(); ?></p>
                <p class="card-text"><p class="pivot-desc item mb-0"><?php print wp_trim_words(_get_urn_value($offre, 'urn:fld:descmarket'), 20, '...'); ?></p></p>
                <span class="pivot-id-type-offre d-none item"><?php print $idTypeOffre; ?></span>
                <span class="pivot-code-cgt d-none item"><?php print $codeCGT; ?></span>
                <span class="pivot-latitude d-none item"><?php print $offre->adresse1->latitude->__toString(); ?></span>
                <span class="pivot-longitude d-none item"><?php print $offre->adresse1->longitude->__toString(); ?></span>

            </div>
            <a class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') . ' ' . $offerTitle; ?>" href="<?php print $url; ?>"></a>
        </div>
    </div>
</div>