<?php

/**
 * Proxied download of media files hosted by Pivot (GPX tracks for itineraries).
 *
 * Replaces inc/external/gpxdownloader.php, a standalone PHP file — reachable directly
 * over HTTP, outside of the WordPress bootstrap — which did readfile($_GET['f']) with
 * no validation whatsoever (arbitrary local file read, SSRF) and injected $_GET['n']
 * raw into the Content-Disposition header.
 *
 * The remote URL is now only accepted when its host matches the configured Pivot
 * instance (or a host explicitly allowed through the 'pivot_download_allowed_hosts'
 * filter), which keeps the endpoint from being turned into an open proxy.
 */

defined('ABSPATH') or die('No script kiddies please!');

const PIVOT_DOWNLOAD_ACTION = 'pivot_download_media';

add_action('admin_post_' . PIVOT_DOWNLOAD_ACTION, 'pivot_download_media');
add_action('admin_post_nopriv_' . PIVOT_DOWNLOAD_ACTION, 'pivot_download_media');

/**
 * Build the download URL for a Pivot-hosted media file.
 *
 * @param string $file_url Remote URL of the media, as returned by Pivot.
 * @param string $filename Desired file name, without extension.
 * @param string $extension File extension, without the dot.
 * @return string Empty string when the media URL is not downloadable.
 */
function pivot_download_url($file_url, $filename, $extension = 'gpx') {
  if (!pivot_download_host_is_allowed($file_url)) {
    return '';
  }

  return add_query_arg(
    array(
      'action' => PIVOT_DOWNLOAD_ACTION,
      'file' => rawurlencode($file_url),
      'name' => rawurlencode($filename),
      'ext' => rawurlencode($extension),
    ),
    admin_url('admin-post.php')
  );
}

/**
 * Check that a URL points at the configured Pivot instance.
 *
 * @param string $url
 * @return bool
 */
function pivot_download_host_is_allowed($url) {
  $host = wp_parse_url($url, PHP_URL_HOST);
  $scheme = wp_parse_url($url, PHP_URL_SCHEME);

  if (empty($host) || !in_array($scheme, array('http', 'https'), true)) {
    return false;
  }

  $allowed_hosts = array();
  $pivot_host = wp_parse_url(get_option('pivot_uri'), PHP_URL_HOST);
  if (!empty($pivot_host)) {
    $allowed_hosts[] = strtolower($pivot_host);
  }

  /**
   * Hosts allowed to be proxied by the media download endpoint.
   *
   * @param array $allowed_hosts Lowercased host names.
   */
  $allowed_hosts = apply_filters('pivot_download_allowed_hosts', $allowed_hosts);

  return in_array(strtolower($host), $allowed_hosts, true);
}

/**
 * Stream a Pivot media file to the browser as an attachment.
 */
function pivot_download_media() {
  $file_url = isset($_GET['file']) ? esc_url_raw(wp_unslash($_GET['file'])) : '';

  if (!pivot_download_host_is_allowed($file_url)) {
    wp_die(esc_html__('This file cannot be downloaded.', 'pivot'), '', array('response' => 403));
  }

  $extension = isset($_GET['ext']) ? preg_replace('/[^a-z0-9]/i', '', wp_unslash($_GET['ext'])) : 'gpx';
  if ($extension === '') {
    $extension = 'gpx';
  }
  $name = isset($_GET['name']) ? sanitize_file_name(wp_unslash($_GET['name'])) : '';
  if ($name === '') {
    $name = 'pivot-media';
  }

  $response = pivot_http_get($file_url, array('timeout' => 20));
  if (is_wp_error($response)) {
    wp_die(esc_html__('The file could not be retrieved.', 'pivot'), '', array('response' => 502));
  }

  nocache_headers();
  header('Content-Type: ' . ($extension === 'gpx' ? 'application/gpx+xml' : 'application/octet-stream'));
  header('Content-Disposition: attachment; filename="' . sanitize_file_name($name . '.' . $extension) . '"');
  header('Content-Length: ' . strlen($response));
  header('X-Content-Type-Options: nosniff');

  echo $response; // phpcs:ignore WordPress.Security.EscapeOutput -- raw file payload.
  exit;
}
