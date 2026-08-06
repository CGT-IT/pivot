<?php

/**
 * Single entry point for every outgoing call to the Pivot webservice.
 *
 * Before this, requests were made either with raw cURL handles or with
 * file_get_contents(), in both cases with certificate verification explicitly turned
 * off (CURLOPT_SSL_VERIFYPEER => 0 / 'verify_peer' => false) and with 120 to 160 second
 * timeouts. Going through the WordPress HTTP API gives us verified TLS by default,
 * sane timeouts, and a single place to cache, log and instrument the traffic.
 */

defined('ABSPATH') or die('No script kiddies please!');

/** Default total timeout, in seconds, for a Pivot call. */
const PIVOT_HTTP_TIMEOUT = 15;

/** How long a thesaurus / reference-data response stays cached. */
const PIVOT_HTTP_CACHE_TTL = WEEK_IN_SECONDS;

/**
 * Perform a GET request and return the response body.
 *
 * @param string $url
 * @param array $args Extra arguments passed to wp_remote_request().
 * @return string|WP_Error Response body, or WP_Error on transport/HTTP error.
 */
function pivot_http_get($url, $args = array()) {
  return pivot_http_request($url, array_merge(array('method' => 'GET'), $args));
}

/**
 * Perform a request against Pivot and return the response body.
 *
 * @param string $url
 * @param array $args Arguments for wp_remote_request(). 'ws_key' => true adds the
 *                    configured Pivot webservice key to the headers.
 * @return string|WP_Error
 */
function pivot_http_request($url, $args = array()) {
  $defaults = array(
    'method' => 'GET',
    'timeout' => PIVOT_HTTP_TIMEOUT,
    'redirection' => 3,
    'headers' => array(),
    'user-agent' => 'WordPress/Pivot ' . PIVOT_VERSION . '; ' . home_url('/'),
  );

  $args = array_merge($defaults, $args);

  if (!empty($args['ws_key'])) {
    $args['headers'] = array_merge(
      array(
        'WS_KEY' => get_option('pivot_key'),
        'Content-type' => 'application/xml',
        'Accept' => 'application/xml',
      ),
      $args['headers']
    );
  }
  unset($args['ws_key']);

  /**
   * Filter the arguments of a Pivot HTTP request.
   *
   * @param array $args
   * @param string $url
   */
  $args = apply_filters('pivot_http_request_args', $args, $url);

  $response = wp_remote_request($url, $args);

  if (is_wp_error($response)) {
    pivot_http_log($url, $response->get_error_message());
    return $response;
  }

  $code = wp_remote_retrieve_response_code($response);
  if ($code < 200 || $code >= 300) {
    pivot_http_log($url, 'HTTP ' . $code);
    return new WP_Error('pivot_http_status', sprintf(
      /* translators: %d: HTTP status code returned by Pivot. */
      __('Pivot answered with HTTP %d', 'pivot'),
      $code
    ), array('status' => $code));
  }

  return wp_remote_retrieve_body($response);
}

/**
 * GET a URL, caching the response body in a transient.
 *
 * Reference data (thesaurus entries, list of "maisons du tourisme", communes, offer
 * types) barely ever changes but used to be fetched on every single page render — the
 * thesaurus in particular was queried once per URN, inside rendering loops.
 *
 * On a failed request the previous value is served when we still have it, so a Pivot
 * outage degrades into stale content instead of an empty page.
 *
 * @param string $url
 * @param int $ttl Cache lifetime in seconds.
 * @param array $args Extra arguments for pivot_http_request().
 * @return string|WP_Error
 */
function pivot_http_get_cached($url, $ttl = PIVOT_HTTP_CACHE_TTL, $args = array()) {
  $key = 'pivot_http_' . md5($url . wp_json_encode($args));
  $stale_key = $key . '_stale';

  $cached = get_transient($key);
  if ($cached !== false) {
    return $cached;
  }

  $body = pivot_http_get($url, $args);

  if (is_wp_error($body)) {
    $stale = get_transient($stale_key);
    return ($stale !== false) ? $stale : $body;
  }

  set_transient($key, $body, $ttl);
  // Kept around longer than the fresh copy, to survive a Pivot outage.
  set_transient($stale_key, $body, $ttl * 4);

  return $body;
}

/**
 * Load an XML response into a SimpleXML object.
 *
 * @param string $body
 * @return SimpleXMLElement|false
 */
function pivot_http_parse_xml($body) {
  if (!is_string($body) || strpos($body, '<?xml') === false) {
    return false;
  }

  $previous = libxml_use_internal_errors(true);
  $xml = simplexml_load_string($body);
  libxml_clear_errors();
  libxml_use_internal_errors($previous);

  return $xml;
}

/**
 * Log a failed Pivot call when WP_DEBUG is on.
 *
 * @param string $url
 * @param string $message
 */
function pivot_http_log($url, $message) {
  if (defined('WP_DEBUG') && WP_DEBUG) {
    // The URL can carry a query id but never the WS key, which travels in a header.
    error_log(sprintf('[pivot] request failed (%s): %s', $message, $url));
  }
}
