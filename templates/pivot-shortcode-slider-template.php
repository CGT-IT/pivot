<?php $offre = $args; ?>

<?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
<?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
<?php $lang = substr(get_locale(), 0, 2); ?>
<?php $url = get_bloginfo('wpurl') . (($lang == 'fr') ? '' : '/' . $lang) . '/details/' . $codeCGT . '&type=' . $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>


<div class="carousel-item pivot-slide <?php print _set_slider_col($offre->nb_per_row); ?> <?php print (($offre->first) ? 'active' : ''); ?>">
    <div class="card bg-dark text-white">
        <img class="card-img" src="<?php print _get_offer_default_image($offre); ?>"/>
        <div class="card-img-overlay">
            <h5 class="card-title"><?php print $offerTitle; ?></h5>
            <a class="text-dark stretched-link" title="<?php echo __('Link to', 'pivot') . ' ' . $offerTitle; ?>" href="<?php print $url; ?>"></a>
        </div>
    </div>
</div>