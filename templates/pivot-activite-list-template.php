
<?php $pivot_page = pivot_get_page_path(_get_path());?>
<title><?php print $pivot_page->title .' - '. get_bloginfo('name');?></title>
<!--Include header-->
<?php get_header('pivot'); ?>
<!--Include sidebar-->
<?php // get_sidebar(); ?>

<!--Get filters-->
<?php $filters = pivot_add_filters(); ?>
<!--Get offers-->
<?php $offres = pivot_lodging_page($pivot_page->id); ?>
  
<div class="container-fluid pivot-list">
  <?php // if(stristr($_SERVER['HTTP_REFERER'], 'page=pivot-pages') != FALSE): ?>  
    <div class="row m-4">
      <div class="col-12"><h1 class="text-center"><?php print $pivot_page->title;?></h1></div>
    </div>
  <?php // endif;?>
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
        <div class="col-11 pt-3" style="background-color:#f5f5f5;">
          <h5><?php echo __('There are', 'pivot') .' '. $_SESSION['pivot'][$pivot_page->id]['nb_offres'] .' '.  __('offers', 'pivot'); ?></h5>
        </div>
        <div class="col-1" role="button">
          <i id="carte" class="float-right fas <?php print ($pivot_page->map==1)?'fa-list':'fa-map-marked-alt';?> fa-4x" role="button"></i>
        </div>
      </div>
      <div class="row">
        <div id="offers-area" class="<?php print ($pivot_page->map==1)?'col-3 pivot-offer-list':'col-12';?>">
          <div class="row">
            <?php foreach($offres as $offre): ?>
              <?php $name = 'pivot-'.$pivot_page->type.'-details-part-template'; ?>
              <?php $offre->path = $pivot_page->path; ?>
              <?php $offre->map = $pivot_page->map; ?>
              <?php print pivot_template($name, $offre); ?>
            <?php endforeach; ?>
          </div>
        </div>

        <div id="maparea" class="<?php print ($pivot_page->map==1)?'col-9':'';?>">
          <!--Include leaflet css for map-->
           <link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css"
                 integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
                 crossorigin=""/>
          <!--Include leaflet js for map-->
           <script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js"
                   integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg=="
                   crossorigin=""></script>

          <!--Create Map element-->  
          <div id="mapid" style="height: 600px;width: 600px;z-index:0;"></div>
          <!--Include map custom js-->
          <script src="<?php echo plugins_url('js/map.js', dirname(__FILE__)) ?>"></script>
        </div>
          
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
    
<!--Include footer-->
<?php get_footer('pivot');