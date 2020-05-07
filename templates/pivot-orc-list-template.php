
<?php $pivot_page = pivot_get_page_path(_get_path());?>
<title><?php print __($pivot_page->title, 'pivot') .' - '. get_bloginfo('name');?></title>
<!--Include header-->
<?php get_header(); ?>

<!--Get filters-->
<?php $filters = pivot_add_filters(); ?>
<!--Get offers-->
<?php $offres = pivot_lodging_page($pivot_page->id,2,1000); ?>
  
<div class="container-fluid pivot-list">
  <?php print _add_banner_image($pivot_page->image); ?>
  <div class="row m-4">
    <div class="col-12">
      <h1 class="text-center"><?php _e($pivot_page->title, 'pivot');?></h1>
      <div id="pivot-page-description" class="text-center"><?php _e($pivot_page->description, 'pivot');?></div>
    </div>
  </div>
  <div class="row">
    <?php if(!(empty($filters))): ?>
      <div class="col-xs-12 col-md-3">
        <?php print $filters; ?>
      </div>
      <div class="col-xs-12 col-md-9 bg-white border-left">
    <?php else: ?>
      <div class="col-xs-12 col-md-12 bg-white">
    <?php endif;?>
        <div class="row p-4">
          <link href="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.css" rel="stylesheet">
          <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table.min.js"></script>
          <script src="https://unpkg.com/bootstrap-table@1.16.0/dist/bootstrap-table-locale-all.min.js"></script>
          <script>
            function copyFunction(linkID) {
              var copyText = document.getElementById(linkID);
              copyText.select();
              copyText.setSelectionRange(0, 99999)
              document.execCommand("copy");
              alert("Lien FR copié: " + copyText.value);
            }
          </script>
          <div class="table-responsive-xl">

            <table id="orctable" data-show-fullscreen="true" data-show-columns="true" data-show-columns-toggle-all="true" data-show-toggle="true" data-locale="fr-FR" data-toggle="table" data-sort-name="nom" data-sort-order="asc" data-pagination="true" data-page-size="25" data-toggle="table" data-search="true"class="table table-striped">
              <thead>
                <tr>
                  <th data-field="nom" data-sortable="true">Nom</th>
                  <th data-field="id-pivot" data-sortable="true" class="text-center" scope="col">ID Pivot</th>
                  <th data-field="code-postal" data-sortable="true" class="text-center" scope="col">Code postal</th>
                  <th data-field="commune" data-sortable="true" class="text-center" scope="col">Commune</th>
                  <th data-field="lien-orc-fr" class="text-center" data-visible="false" scope="col">Lien ORC (FR)</th>
                  <th data-field="lien-orc-nl" class="text-center" data-visible="false" scope="col">Lien ORC (NL)</th>
                  <th data-field="lien-orc-en" class="text-center" data-visible="false" scope="col">Lien ORC (EN)</th>
                  <th data-field="lien-orc-de" class="text-center" data-visible="false" scope="col">Lien ORC (DE)</th>
                  <th data-field="details-offre" scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 0; ?>
                <?php foreach($offres as $offre): ?>
                  <?php $offerTitle = _get_urn_value($offre, 'urn:fld:nomofr'); ?>
                  <?php $codeCGT = $offre->attributes()->codeCgt->__toString(); ?>
                  <?php $typeOffer = $offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
                  <?php $url = get_bloginfo('wpurl').'/details/'.$codeCGT.'&type='.$typeOffer; ?>
                  <?php $orcLink = _get_urn_value($offre, 'urn:fld:orc'); ?>
                  <tr>
                    <td class="nom"><?php print $offerTitle; ?></td>
                    <td class="id-pivot"><?php print $codeCGT; ?></td>
                    <td class="code-postal"><?php print $offre->adresse1->cp; ?></td>
                    <td class="commune"><?php print $offre->adresse1->localite->value->__toString(); ?></td>
                    <td id="lien-orc-fr-<?php print $i;?>" class="lien-orc-fr"><?php print _get_urn_value($offre, 'urn:fld:orc'); ?></td>
                    <td id="lien-orc-nl-<?php print $i;?>" class="lien-orc-nl"><?php print _get_urn_value($offre, 'nl:urn:fld:orc'); ?></td>
                    <td id="lien-orc-en-<?php print $i;?>" class="lien-orc-en"><?php print _get_urn_value($offre, 'en:urn:fld:orc'); ?></td>
                    <td id="lien-orc-de-<?php print $i;?>" class="lien-orc-de"><?php print _get_urn_value($offre, 'de:urn:fld:orc'); ?></td>
                    <input id="link-<?php print $i;?>" type="text" class="d-none" value="<?php print _get_urn_value($offre, 'urn:fld:orc'); ?>"/>
                    <td class="details-offre">
                      <button class="button btn-sm" onclick="copyFunction('link-<?php print $i;?>')"><i class="fa fa-copy"></i> Copier</button>
                       // 
                      <a href="<?php print $url;?>" target="_blank" title="<?php print __('Link to', 'pivot') .' '. $offerTitle; ?>"><i class="fa fa-eye"> Pivot</i></a>
                       // 
                      <a href="<?php print $orcLink;?>" target="_blank" title="<?php print __('Link to', 'pivot') .' la page ORC'; ?>"><i class="fa fa-credit-card"> ORC</i></a>
                    </td>
                  </tr>
                  <?php $i++; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
  </div>
</div>
    
<!--Include footer-->
<?php get_footer();