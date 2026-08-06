<?php

/**
 * REST endpoint backing the client-side search on listing pages.
 *
 * The offer thumbnails are still rendered server-side, with the same templates a theme
 * may have overridden — the endpoint returns markup, not raw offer data. That keeps
 * every cloned template working while letting the browser refresh the results without
 * a full page load.
 */

defined('ABSPATH') or die('No script kiddies please!');

const PIVOT_REST_NAMESPACE = 'pivot/v1';

add_action('rest_api_init', 'pivot_register_rest_routes');

function pivot_register_rest_routes() {
  register_rest_route(
    PIVOT_REST_NAMESPACE,
    '/pages/(?P<id>\d+)/offers',
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'pivot_rest_get_offers',
      // Listing pages are public; the endpoint exposes exactly what the page shows.
      'permission_callback' => '__return_true',
      'args' => array(
        'id' => array(
          'required' => true,
          'sanitize_callback' => 'absint',
        ),
        'paged' => array(
          'default' => 1,
          'sanitize_callback' => 'absint',
        ),
        'filters' => array(
          'default' => array(),
        ),
      ),
    )
  );
}

/**
 * Return the offers of a listing page as rendered markup.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function pivot_rest_get_offers(WP_REST_Request $request) {
  $page_id = absint($request['id']);
  $pivot_page = pivot_get_page($page_id);

  if (!$pivot_page) {
    return new WP_Error('pivot_unknown_page', __('Unknown Pivot page.', 'pivot'), array('status' => 404));
  }

  $filters = pivot_sanitize_filters($request['filters']);
  pivot_state_set_filters($page_id, $filters);

  // pivot_fetch_page_offers() reads the page number off the request URI, which for a
  // REST call is /wp-json/..., not the listing URL.
  $paged = max(1, absint($request['paged']));
  pivot_force_current_page($paged);

  $offres = pivot_lodging_page($page_id);
  $nb_offres = pivot_get_nb_offers($page_id);
  $per_page = _define_nb_offers_per_page($pivot_page->nbcol);

  return rest_ensure_response(array(
    'html' => pivot_render_offer_thumbnails($offres, $pivot_page),
    'total' => (int) $nb_offres,
    'page' => $paged,
    'pages' => (int) ceil($nb_offres / max(1, $per_page)),
    'empty' => __('No offer matches your search.', 'pivot'),
  ));
}

/**
 * Override the page number pivot_get_current_page() resolves for this request.
 *
 * @param int|null $paged Page number, or null to read the request URI again.
 * @return int|null Current override.
 */
function pivot_force_current_page($paged = null) {
  static $forced = null;

  if ($paged !== null) {
    $forced = max(1, absint($paged));
  }

  return $forced;
}
