<?php /* Template Name: pivot-lodging-list-template */ ?>

<!--Include header-->
<?php get_header(); ?>
<!--Include sidebar-->
<?php // get_sidebar(); ?>
<?php global $base_url; ?>

<!--Get offers-->
<?php 
$page=pivot_get_page_path($_SESSION['pivot']['path']);
$offres = pivot_lodging_page($page->id); ?>


  
<div class="container-fluid pivot-list">
  <p><?php echo esc_html('There are', 'pivot') .' '. $_SESSION['pivot']['nb_offres'] .' '.  esc_html('offers', 'pivot'); ?></p>
  <div class="row row-eq-height pivot-row">
    <div class="col-12 col-lg-6 py-5 order-lg-1 order-2 left-sidebar z-index-99">
      <div class="row">  
        <?php foreach($offres as $offre): ?>
          <?php $name = 'pivot-lodging-details-part-template'; ?>
          <?php $offre->path = $_SESSION['pivot']['path']; ?>
          <?php print _template($name, $offre); ?>
        <?php endforeach; ?>
      </div>
      <?php echo _add_pagination($_SESSION['pivot']['nb_offres']); ?>
    </div>
    <div class="col-12 col-lg-6 order-lg-2 order-1 px-0">
      <!--Check if we want to show a map-->
      <?php if($_SESSION['pivot']['map'] == 1): ?>
        <!--Include leaflet css for map-->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.3/dist/leaflet.css"
          integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
          crossorigin=""/>
        <!--Include leaflet js for map-->
        <script src="https://unpkg.com/leaflet@1.3.3/dist/leaflet.js"
          integrity="sha512-tAGcCfR4Sc5ZP5ZoVz0quoZDYX5aCtEm/eu1KhSLj2c9eFrylXZknQYmxUssFaVJKvvc0dJQixhGjG2yXWiV9Q=="
          crossorigin=""></script>

        <!--Create Map element-->  
        <div id="mapid"></div>
        <!--Include map custom js-->
        <script src="<?php echo plugins_url('/map.js', __FILE__) ?>"></script>
      <?php endif; ?>
    </div>
  </div>
</div>

<!--Include footer-->
<?php get_footer();