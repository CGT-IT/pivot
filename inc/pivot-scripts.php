<?php

// register jquery and style on initialization
add_action('init', 'pivot_register_script');

add_action('admin_enqueue_scripts', 'pivot_enqueue_admin_script');
add_action('wp_enqueue_scripts', 'pivot_enqueue_script');

/**
 * @deprecated 2.5.0 The plugin no longer uses PHP sessions. Starting one on every
 *   request sent a Set-Cookie and a no-cache header site-wide, which disabled page
 *   caching everywhere and required sticky sessions behind a load balancer.
 */
function pivot_start_session() {

}

/**
 * @deprecated 2.5.0 See pivot_start_session().
 */
function pivot_end_session() {

}

/**
 * Add script on Admin part (on condition)
 */
function pivot_enqueue_admin_script($hook) {
  $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
  $editing = isset($_GET['edit']) && $_GET['edit'] === 'true';

  // Add script only in this case
  // page is "pivot-filters" and "edit" is set to true
  if ($page === 'pivot-filters' && $editing) {
    wp_enqueue_script('pivot_filters_script', PIVOT_PLUGIN_URL . 'js/filters.js', array('jquery'), PIVOT_VERSION, true);
  }
  if ($page === 'pivot-shortcode') {
    wp_enqueue_script('clipboard_script', PIVOT_PLUGIN_URL . 'js/clipboard.min.js', array('jquery'), PIVOT_VERSION, true);
    wp_enqueue_script('pivot_shortcode_script', PIVOT_PLUGIN_URL . 'js/shortcode.js', array('jquery'), PIVOT_VERSION, true);
  }
  if ($page === 'pivot-shortcode-event') {
    wp_enqueue_script('clipboard_script', PIVOT_PLUGIN_URL . 'js/clipboard.min.js', array('jquery'), PIVOT_VERSION, true);
    wp_enqueue_script('pivot_shortcode_event_script', PIVOT_PLUGIN_URL . 'js/shortcodeevent.js', array('jquery'), PIVOT_VERSION, true);
    wp_enqueue_style('pivot_admin_css', PIVOT_PLUGIN_URL . 'css/pivot-admin.css', array(), PIVOT_VERSION, false);
  }
  if ($page === 'pivot-pages' && $editing) {
    wp_enqueue_script('pivot_pages_script', PIVOT_PLUGIN_URL . 'js/pages.js', array('jquery'), PIVOT_VERSION, true);
  }
  if ($page === 'pivot-offer-types' && $editing) {
    wp_enqueue_script('pivot_typeofr_script', PIVOT_PLUGIN_URL . 'js/typeofr.js', array('jquery'), PIVOT_VERSION, true);
  }
  if ($page === 'pivot-admin') {
    wp_enqueue_script('pivot_config_script', PIVOT_PLUGIN_URL . 'js/config.js', array('jquery'), PIVOT_VERSION, true);
  }
}

/**
 * Register Scripts
 *
 * Third-party assets are registered rather than printed inline. Leaflet and
 * bootstrap-table used to be echoed as raw <link>/<script> tags in the middle of the
 * body by _add_pivot_map() and _add_section_mice_rooms(): WordPress could not see
 * them, so they could not be dequeued, deduplicated, deferred or bundled, and they
 * were downloaded even on pages without a map.
 */
function pivot_register_script() {
  wp_register_style('lodging_style', PIVOT_PLUGIN_URL . 'css/pivot-lodging.css', array(), PIVOT_VERSION, false);
  wp_register_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/css/all.min.css', array(), '5.8.1', false);
  wp_register_style('bootstrapexternal', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', array(), '4.3.1', false);
  wp_register_script('poppermin', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', array(), '1.14.7', true);
  wp_register_script('bootstrapmin', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery', 'poppermin'), '4.3.1', true);
  wp_register_script('dataTablesmin', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array('jquery'), '1.10.19', true);
  wp_register_script('itinerary', PIVOT_PLUGIN_URL . 'js/itinerary.js', array(), PIVOT_VERSION, true);
  wp_register_script('pivot-listing', PIVOT_PLUGIN_URL . 'js/pivot-listing.js', array(), PIVOT_VERSION, true);
  wp_register_script('pivotshortcodecarousel', PIVOT_PLUGIN_URL . 'js/pivotshortcodecarousel.js', array(), PIVOT_VERSION, true);

  // Map assets, enqueued on demand by _add_pivot_map().
  wp_register_style('pivot-leaflet', 'https://unpkg.com/leaflet@1.4.0/dist/leaflet.css', array(), '1.4.0', false);
  wp_register_script('pivot-leaflet', 'https://unpkg.com/leaflet@1.4.0/dist/leaflet.js', array(), '1.4.0', true);
  wp_register_script('pivot-map-list', PIVOT_PLUGIN_URL . 'js/mapcardorientation.js', array('pivot-leaflet'), PIVOT_VERSION, true);
  wp_register_script('pivot-map-single', PIVOT_PLUGIN_URL . 'js/mapsingleoffer.js', array('pivot-leaflet'), PIVOT_VERSION, true);
  wp_register_script('pivot-map-orthodromic', PIVOT_PLUGIN_URL . 'js/maporthodromic.js', array('pivot-leaflet'), PIVOT_VERSION, true);

  // Sortable room table, enqueued on demand by _add_section_mice_rooms().
  wp_register_style('pivot-bootstrap-table', 'https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.css', array(), '1.15.5', false);
  wp_register_script('pivot-bootstrap-table', 'https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table.min.js', array('jquery'), '1.15.5', true);
  wp_register_script('pivot-bootstrap-table-locale', 'https://unpkg.com/bootstrap-table@1.15.5/dist/bootstrap-table-locale-all.min.js', array('pivot-bootstrap-table'), '1.15.5', true);

  // Itinerary elevation profile and GPX track, enqueued by the itinerary template.
  wp_register_script('pivot-d3', 'https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js', array(), '4.13.0', true);
  wp_register_script('pivot-leaflet-gpx', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.4.0/gpx.js', array('pivot-leaflet'), '1.4.0', true);
  wp_register_script('pivot-itinerary', PIVOT_PLUGIN_URL . 'js/itinerary.js', array('pivot-leaflet-gpx', 'pivot-d3'), PIVOT_VERSION, true);
}

/**
 * Enqueue the script that turns the filter form into an asynchronous search.
 *
 * Without it the form still works: it is a plain GET form pointing at the listing
 * page, which the server renders normally.
 *
 * @param Object $pivot_page
 */
function pivot_enqueue_listing_script($pivot_page) {
  if (!$pivot_page || empty($pivot_page->id)) {
    return;
  }

  wp_enqueue_script('pivot-listing');
  wp_localize_script('pivot-listing', 'pivotListing', array(
    'endpoint' => rest_url(PIVOT_REST_NAMESPACE . '/pages/' . absint($pivot_page->id) . '/offers'),
    'pageId' => absint($pivot_page->id),
    'pageUrl' => pivot_page_url($pivot_page),
    'param' => PIVOT_FILTER_PARAM,
    'perPage' => _define_nb_offers_per_page($pivot_page->nbcol),
    'i18n' => array(
      'loading' => __('Searching…', 'pivot'),
      'error' => __('The search failed, please try again.', 'pivot'),
      /* translators: %s: number of offers. */
      'countOne' => __('There is %s offer', 'pivot'),
      'countMany' => __('There are %s offers', 'pivot'),
    ),
  ));
}

/**
 * Enqueue the assets a Pivot map needs.
 *
 * @param string $variant list|single|orthodromic
 */
function pivot_enqueue_map_assets($variant = 'list') {
  wp_enqueue_style('pivot-leaflet');

  switch ($variant) {
    case 'single':
      wp_enqueue_script('pivot-map-single');
      break;
    case 'orthodromic':
      wp_enqueue_script('pivot-map-orthodromic');
      break;
    default:
      wp_enqueue_script('pivot-map-list');
      break;
  }
}

/**
 * Add the registered jquery and style above
 */
function pivot_enqueue_script() {
  wp_enqueue_style('lodging_style');
  // Add only if Boostrap is set.
  if (get_option('pivot_bootstrap') == 'on') {
    wp_enqueue_script('pivot_config_test', PIVOT_PLUGIN_URL . 'js/cgtvarious.js', array('jquery'), PIVOT_VERSION, true);
    wp_enqueue_style('fontawesome');
    wp_enqueue_style('bootstrapexternal');
    wp_enqueue_script('poppermin');
    wp_enqueue_script('bootstrapmin');
    wp_enqueue_script('dataTablesmin');
    wp_enqueue_script('pivotshortcodecarousel');
  }
}
