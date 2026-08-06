<?php

/**
 * Per-visitor state of a Pivot listing page: active filters, pagination token, total
 * number of offers.
 *
 * Every read and write of that state goes through this file. It is still backed by
 * $_SESSION, but nothing outside of here needs to know that — which is what makes it
 * possible to move the state onto transients and the query string without touching
 * the callers.
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Read one value of a page's state.
 *
 * @param int|string $page_id
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function pivot_state_get($page_id, $key, $default = null) {
  if (!isset($_SESSION['pivot'][$page_id][$key])) {
    return $default;
  }

  return $_SESSION['pivot'][$page_id][$key];
}

/**
 * Store one value of a page's state.
 *
 * @param int|string $page_id
 * @param string $key
 * @param mixed $value
 */
function pivot_state_set($page_id, $key, $value) {
  if ($page_id === null || $page_id === '') {
    return;
  }
  $_SESSION['pivot'][$page_id][$key] = $value;
}

/**
 * Active filters of a page, as a map of filter id => submitted value.
 *
 * @param int|string $page_id
 * @return array
 */
function pivot_state_get_filters($page_id) {
  if (empty($_SESSION['pivot']['filters'][$page_id]) || !is_array($_SESSION['pivot']['filters'][$page_id])) {
    return array();
  }

  return $_SESSION['pivot']['filters'][$page_id];
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
 * Replace the active filters of a page.
 *
 * @param int|string $page_id
 * @param array $filters
 */
function pivot_state_set_filters($page_id, array $filters) {
  $_SESSION['pivot']['filters'][$page_id] = $filters;
}

/**
 * Drop the active filters of every page.
 */
function pivot_state_reset_all_filters() {
  unset($_SESSION['pivot']['filters']);
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
 * Pagination token handed out by Pivot for an unfiltered listing.
 *
 * Shared by every visitor of the page, hence a transient rather than session state.
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
 * Total number of offers of an unfiltered listing, cached alongside the token.
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
 * Transient key holding the shared pagination token.
 *
 * Numeric ids are listing pages; anything else is a shortcode, keyed by its query.
 *
 * @param int|string $page_id
 * @return string
 */
function pivot_state_token_key($page_id) {
  return is_numeric($page_id)
    ? 'pivot_page_token_' . $page_id
    : 'pivot_shortcode_token_' . md5((string) $page_id);
}
