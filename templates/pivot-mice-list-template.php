
<?php $pivot_page = pivot_get_page_path(_get_path());?>
<title><?php print __($pivot_page->title, 'pivot') .' - '. get_bloginfo('name');?></title>
<!--Include header-->
<?php get_header(); ?>

<!--Get filters-->
<?php $filters = pivot_add_filters(); ?>
<!--Get offers-->
<?php $offres = pivot_lodging_page($pivot_page->id); ?>
  
<div class="container-fluid pivot-list">
  <div class="row m-4">
    <div class="col-12"><h1 class="text-center"><?php _e($pivot_page->title, 'pivot');?></h1></div>
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
        <div class="row p-3">
          <div class="col-xs-12 col-sm-12 col-md-11 col-lg-11 col-xl-11 pt-3" style="background-color:#f5f5f5;">
            <h5><?php echo __('There are', 'pivot') .' '. $_SESSION['pivot'][$pivot_page->id]['nb_offres'] .' '.  __('offers', 'pivot'); ?></h5>
          </div>
          <div class="d-none d-md-block col-1" role="button">
            <i id="carte" class="float-right fas <?php print ($pivot_page->map==1)?'fa-list':'fa-map-marked-alt';?> fa-2x" role="button"></i>
          </div>
        </div>
        <div class="row">
          <div id="offers-area" class="<?php print ($pivot_page->map==1)?'col-xs-12 col-sm-12 col-md-5 col-lg-5 col-xl-5 pivot-offer-list':'col-12';?>">
            <div class="row d-flex flex-wrap">
              <?php foreach($offres as $offre): ?>
                <?php $name = 'pivot-'.$pivot_page->type.'-details-part-template'; ?>
                <?php $offre->path = $pivot_page->path; ?>
                <?php $offre->map = $pivot_page->map; ?>
                <?php $offre->nb_per_row = $pivot_page->nbcol; ?>
                <?php print pivot_template($name, $offre); ?>
              <?php endforeach; ?>
            </div>
          </div>

          <?php print _add_pivot_map($pivot_page->map, 7); ?>

        </div>
        <div class="row mt-3">
          <div class="col-12">
            <div class="float-right">
              <?php echo _add_pagination($_SESSION['pivot'][$pivot_page->id]['nb_offres']); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    
<!--Include footer-->
<?php get_footer();