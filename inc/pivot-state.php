<?php

/**
 * State of a Pivot listing: active filters, pagination token, total number of offers.
 *
 * This used to live in $_SESSION, which meant session_start() on every request of the
 * whole site — a Set-Cookie and a no-cache header on every page, so no page cache
 * (Varnish, WP Rocket, a CDN) could ever serve a Pivot site, and no multi-server setup
 * could work without shared session storage. Two visitors also shared one URL while
 * seeing different results, so a search could not be linked to or indexed.
 *
 * Filters now travel in the query string under pf[<filter id>], the pagination token
 * lives in a transient keyed by the search itself, and nothing is stored per visitor.
 */

defined('ABSPATH') or die('No script kiddies please!');

/** Query-string parameter carrying the active filters. */
const PIVOT_FILTER_PARAM = 'pf';

/**
 * Filters explicitly set for this request, per page id.
 *
 * The REST endpoint fills this from its own parameters instead of the query string.
 *
 * @return array
 */
function &pivot_state_filters_store() {
  static $filters = array();

  return $filters;
}

/**
 * Read one value of a page's state.
 *
 * Request-scoped only: state that must outlive the request is either derivable from
 * the page row or cached in a transient.
 *
 * @param int|string $page_id
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function pivot_state_get($page_id, $key, $default = null) {
  $store = &pivot_state_runtime_store();

  return isset($store[$page_id][$key]) ? $store[$page_id][$key] : $default;
}

/**
 * Store one value of a page's state for the duration of the request.
 *
 * @param int|string $page_id
 * @param string $key
 * @param mixed $value
 */
function pivot_state_set($page_id, $key, $value) {
  if ($page_id === null || $page_id === '') {
    return;
  }
  $store = &pivot_state_runtime_store();
  $store[$page_id][$key] = $value;
}

/**
 * @return array
 */
function &pivot_state_runtime_store() {
  static $store = array();

  return $store;
}

/**
 * Active filters of a page, as a map of filter id => submitted value.
 *
 * Read from the query string unless the caller (the REST endpoint, or a legacy POST
 * of the filter form) set them explicitly.
 *
 * @param int|string $page_id
 * @return array
 */
function pivot_state_get_filters($page_id) {
  $explicit = &pivot_state_filters_store();
  if (isset($explicit[$page_id])) {
    return $explicit[$page_id];
  }

  $raw = isset($_GET[PIVOT_FILTER_PARAM]) ? wp_unslash($_GET[PIVOT_FILTER_PARAM]) : array();

  return pivot_sanitize_filters($raw);
}

/**
 * Normalise a raw filter map coming from a request.
 *
 * Keys are filter ids, values are the submitted strings; a checked checkbox posts
 * "on" and is stored as a boolean.
 *
 * @param mixed $raw
 * @return array
 */
function pivot_sanitize_filters($raw) {
  if (!is_array($raw)) {
    return array();
  }

  $filters = array();
  foreach ($raw as $key => $value) {
    if (!is_numeric($key) || $value === '' || $value === null || is_array($value)) {
      continue;
    }
    $value = sanitize_text_field($value);
    if ($value === '') {
      continue;
    }
    $filters[absint($key)] = ($value === 'on') ? true : $value;
  }

  return $filters;
}

/**
 * Whether the visitor has at least one active filter on this page.
 *
 * @param int|string $page_id
 * @return bool
 */
function pivot_state_has_filters($page_id) {
  return count(pivot_state_get_filters($page_id)) > 0;
}

/**
 * Force the active filters of a page for this request.
 *
 * @param int|string $page_id
 * @param array $filters
 */
function pivot_state_set_filters($page_id, array $filters) {
  $explicit = &pivot_state_filters_store();
  $explicit[$page_id] = $filters;
}

/**
 * Drop any explicitly-set filters.
 */
function pivot_state_reset_all_filters() {
  $explicit = &pivot_state_filters_store();
  $explicit = array();
}

/**
 * Value submitted for one filter, or null.
 *
 * @param int|string $page_id
 * @param int|string $filter_id
 * @return mixed|null
 */
function pivot_state_get_filter_value($page_id, $filter_id) {
  $filters = pivot_state_get_filters($page_id);

  return isset($filters[$filter_id]) ? $filters[$filter_id] : null;
}

/**
 * Stable signature of the search currently applied to a page.
 *
 * Two visitors running the same search share one token, and therefore one set of
 * cached pages; changing a filter yields a different key instead of overwriting the
 * previous one.
 *
 * @param int|string $page_id
 * @return string
 */
function pivot_state_search_signature($page_id) {
  $filters = pivot_state_get_filters($page_id);
  if (empty($filters)) {
    return '';
  }
  ksort($filters);

  return substr(md5(wp_json_encode($filters) . '|' . substr(get_locale(), 0, 2)), 0, 12);
}

/**
 * Pagination token handed out by Pivot for the current search.
 *
 * @param int|string $page_id
 * @return string|false
 */
function pivot_state_get_shared_token($page_id) {
  return get_transient(pivot_state_token_key($page_id));
}

/**
 * @param int|string $page_id
 * @param string $token
 * @param int $ttl
 */
function pivot_state_set_shared_token($page_id, $token, $ttl) {
  set_transient(pivot_state_token_key($page_id), $token, $ttl);
}

/**
 * Total number of offers of the current search, cached alongside the token.
 *
 * @param int|string $page_id
 * @return string|false
 */
function pivot_state_get_shared_count($page_id) {
  return get_transient('nb' . pivot_state_token_key($page_id));
}

/**
 * @param int|string $page_id
 * @param string $count
 * @param int $ttl
 */
function pivot_state_set_shared_count($page_id, $count, $ttl) {
  set_transient('nb' . pivot_state_token_key($page_id), $count, $ttl);
}

/**
 * Transient key holding the pagination token of the current search.
 *
 * Numeric ids are listing pages; anything else is a shortcode, keyed by its query.
 *
 * @param int|string $page_id
 * @return string
 */
function pivot_state_token_key($page_id) {
  if (!is_numeric($page_id)) {
    return 'pivot_shortcode_token_' . md5((string) $page_id);
  }

  $signature = pivot_state_search_signature($page_id);

  // An unfiltered listing keeps the historical key, which the admin "clear cache"
  // action and _get_nb_offers_from_transient() both know about.
  return 'pivot_page_token_' . $page_id . ($signature === '' ? '' : '_' . $signature);
}

/**
 * Add the active filters to a URL.
 *
 * @param string $url
 * @param int|string $page_id
 * @return string
 */
function pivot_state_add_filters_to_url($url, $page_id) {
  $filters = pivot_state_get_filters($page_id);
  if (empty($filters)) {
    return $url;
  }

  $args = array();
  foreach ($filters as $id => $value) {
    $args[PIVOT_FILTER_PARAM . '[' . $id . ']'] = ($value === true) ? 'on' : $value;
  }

  return add_query_arg($args, $url);
}

/**
 * The active filters as arguments for paginate_links().
 *
 * @param int|string $page_id
 * @return array
 */
function pivot_state_pagination_args($page_id) {
  $filters = pivot_state_get_filters($page_id);
  if (empty($filters)) {
    return array();
  }

  $args = array();
  foreach ($filters as $id => $value) {
    $args[PIVOT_FILTER_PARAM][$id] = ($value === true) ? 'on' : $value;
  }

  return $args;
}
