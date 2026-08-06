<?php

/**
 * Export of the filters of a Pivot page as a CSV file.
 *
 * Replaces the former inc/external/dump.php, which was executed on every single
 * request (it was picked up by the inc/external/*.php glob in pivot.php) and read
 * $_GET['page_id'] straight into an SQL query, with no capability check and no nonce.
 */

defined('ABSPATH') or die('No script kiddies please!');

const PIVOT_EXPORT_ACTION = 'pivot_export_filters';
const PIVOT_EXPORT_NONCE = 'pivot_export_filters';

add_action('admin_post_' . PIVOT_EXPORT_ACTION, 'pivot_export_filters');

/**
 * Build the URL of the filter export for a given page.
 *
 * @param int $page_id
 * @return string Nonced admin-post URL.
 */
function pivot_export_filters_url($page_id) {
  return wp_nonce_url(
    add_query_arg(
      array(
        'action' => PIVOT_EXPORT_ACTION,
        'page_id' => absint($page_id),
      ),
      admin_url('admin-post.php')
    ),
    PIVOT_EXPORT_NONCE
  );
}

/**
 * Stream the filters of a page as CSV.
 *
 * The CSV columns match what pivot_filter_csv_import() expects, so an export can be
 * re-imported as is.
 *
 * @global Object $wpdb
 */
function pivot_export_filters() {
  global $wpdb;

  if (!current_user_can('delete_others_pages')) {
    wp_die(esc_html__('You do not have sufficient permissions to export filters.', 'pivot'), '', array('response' => 403));
  }
  check_admin_referer(PIVOT_EXPORT_NONCE);

  $page_id = isset($_GET['page_id']) ? absint($_GET['page_id']) : 0;
  if (!$page_id) {
    wp_die(esc_html__('Missing page identifier.', 'pivot'), '', array('response' => 400));
  }

  $rows = $wpdb->get_results(
    $wpdb->prepare(
      "SELECT urn, operator, filter_title, filter_title_nl, filter_title_en, filter_title_de, filter_group
         FROM {$wpdb->prefix}pivot_filter
        WHERE page_id = %d
        ORDER BY filter_title ASC",
      $page_id
    ),
    ARRAY_A
  );

  if (empty($rows)) {
    wp_die(esc_html__('This page has no filter to export.', 'pivot'), '', array('response' => 404));
  }

  nocache_headers();
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="' . sanitize_file_name('filters_export_' . $page_id . '_' . date('Y-m-d') . '.csv') . '"');

  $handle = fopen('php://output', 'w');
  fputcsv($handle, array_keys($rows[0]));
  foreach ($rows as $row) {
    fputcsv($handle, array_map('pivot_csv_escape', $row));
  }
  fclose($handle);

  exit;
}

/**
 * Neutralise CSV injection: a leading =, +, - or @ makes spreadsheet software treat
 * the cell as a formula.
 *
 * @param string|null $value
 * @return string
 */
function pivot_csv_escape($value) {
  $value = (string) $value;
  if ($value !== '' && strpos("=+-@\t\r", $value[0]) !== false) {
    return "'" . $value;
  }
  return $value;
}
