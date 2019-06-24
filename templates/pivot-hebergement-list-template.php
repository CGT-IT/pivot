
<?php $pivot_page = pivot_get_page_path(_get_path()); ?>
<title><?php print $_SESSION['pivot'][$pivot_page->id]['page_title'] .' - '. get_bloginfo('name');?></title>
<!--Include header-->
<?php get_header(); ?>
<!--Include sidebar-->
<?php // get_sidebar(); ?>


<!--Get offers-->
<?php $offres = pivot_lodging_page($pivot_page->id); ?>
  
<div class="container-fluid pivot-list">
  <div class="row m-4">
    <div class="col-12"><h1 class="text-center"><?php print $_SESSION['pivot'][$pivot_page->id]['page_title'];?></h1></div>
  </div>
  <div class="row">
    <?php $filters = pivot_add_filters(); ?>
    <?php if($filters !== 0): ?>
      <div class="col-xs-12 col-md-3">
        <?php print $filters; ?>
      </div>
      <div class="col-xs-12 col-md-9 bg-white border-left">
    <?php else: ?>
      <div class="col-xs-12 col-md-12 bg-white">
    <?php endif;?>
      <div class="row p-3">
        <div class="col-9 pt-3" style="background-color:#f5f5f5;">
          <h5><?php echo __('There are', 'pivot') .' '. $_SESSION['pivot'][$pivot_page->id]['nb_offres'] .' '.  __('offers', 'pivot'); ?></h5>
        </div>
        <div class="col-3" role="button">
          <i id="carte" class="float-right fas fa-map-marked-alt fa-4x" role="button"> Carte</i>
        </div>
      </div>
      <div class="row">
        <div id="offers-area" class="col-12">
          <div class="row">
            <?php foreach($offres as $offre): ?>
              <?php $name = 'pivot-'.$pivot_page->type.'-details-part-template'; ?>
              <?php $offre->path = $_SESSION['pivot'][$pivot_page->id]['path']; ?>
              <?php print pivot_template($name, $offre); ?>
            <?php endforeach; ?>
          </div>        
        </div>

        <div id="maparea" class="">
          <!--Include leaflet css for map-->
           <link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css"
                 integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
                 crossorigin=""/>
          <!--Include leaflet js for map-->
           <script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js"
                   integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg=="
                   crossorigin=""></script>

          <!--Create Map element-->  
          <div id="mapid" style="height: 600px;width: 600px;"></div>
          <!--Include map custom js-->
          <script src="<?php echo plugins_url('/map.js', __FILE__) ?>"></script>
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
<?php get_footer();