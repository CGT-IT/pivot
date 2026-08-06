<?php

add_filter('template_include', 'pivot_template_include');

/**
 *
 * @global Object $wp_query
 * @param type $template
 * @return type
 */
function pivot_template_include($template) {
  // Get wp_query
  global $wp_query;
  // Init var to nothing as we can check if it's empty or not
  $new_template = '';

  if (isset($wp_query->query['pagename'])) {
    $query_page = $wp_query->query['pagename'];
  } else {
    if (isset($wp_query->query['name'])) {
      $query_page = $wp_query->query['name'];
    }
  }

  if (isset($query_page)) {
    $pivot_page = pivot_get_page_path($query_page);

    /*
     * Basic case
     * = we are listing offers
     */
    if (isset($pivot_page->path) && $query_page == $pivot_page->path) {
      // Search template file in plugins folder depending on query type
      $new_template = pivot_locate_template('pivot-' . $pivot_page->type . '-list-template.php');
    }
  }

  /*
   * Paged case
   * = we are listing offers but url contains parameter for page number
   */
  if ($new_template == '') {
    reset($wp_query->query_vars);
    $path = key($wp_query->query_vars);

    if ($path == 'details') {
      if (($pos = strpos($_SERVER['REQUEST_URI'], "&type=")) !== FALSE) {
        $pivot_page = new stdClass();
        // Offer type id, straight out of the URL: cast it before it reaches any query.
        $type_id = absint(substr($_SERVER['REQUEST_URI'], $pos + strlen("&type=")));
        $type = $type_id ? pivot_get_offer_type($type_id) : null;
        if (isset($type->parent)) {
          $pivot_page->type = $type->parent;
          $pivot_page->path = 'details';
        } else {
          $pivot_page->type = 'error-in-type';
        }
      }
    } else {
      $pivot_page = pivot_get_page_path($path);
    }
  }
  if ($new_template == '') {
    if (isset($pivot_page->path) && strpos($wp_query->query[$pivot_page->path], 'paged') !== false) {
      $current_page = pivot_get_current_page();
      $wp_query->set('paged', $current_page);
      // Search template file in plugins folder depending on query type
      $new_template = pivot_locate_template('pivot-' . $pivot_page->type . '-list-template.php');
    }
  }

  /*
   * Details case
   * = we want to show an entire offer in its single page
   */
  if ($new_template == '') {
    if (isset($pivot_page->type)) {
      $new_template = pivot_locate_template('pivot-' . $pivot_page->type . '-details-template.php');
    }
  }
  // map / path / query / page_title used to be copied into the session here. They are
  // columns of the page row, so templates read them straight off pivot_get_page().

  if ($new_template != '') {
    return $new_template;
  } else {
    // This is not a virtualpage, so return initial template
    return $template;
  }
}

/**
 * Locate template.
 *
 * @param string $template_name	Template to load.
 * @param string $template_path	Path to templates.
 * @param string $default_path Default path to template files.
 * @return string	Path to the template file.
 */
function pivot_locate_template($template_name, $template_path = '', $default_path = '') {
  // Set variable to search in pivot-plugin-templates folder of theme.
  if (!$template_path) {
    $template_path = 'pivot/';
  }
  // Set default plugin templates path.
  if (!$default_path) {
    // Path to the template folder
    $default_path = plugin_dir_path(__DIR__) . 'templates/';
  }
  // Search template file in theme folder.
  $template = locate_template(array($template_path . $template_name, $template_name));

  // Get plugins template file.
  if (!$template) {
    $template = $default_path . $template_name;
  }

  // Fall back on the generic listing template. Most categories render an identical
  // list, so they no longer need a file of their own — a category-specific template,
  // in the theme or in the plugin, still takes precedence.
  if (!file_exists($template) && substr($template_name, -18) === '-list-template.php') {
    $generic = 'pivot-list-template.php';
    $theme_generic = locate_template(array($template_path . $generic, $generic));
    $template = $theme_generic ? $theme_generic : $default_path . $generic;
  }

  // Ensure the file exists
  if (!file_exists($template)) {
    $text = __('The required template', 'pivot');
    $text .= ' ' . $template_name . ' ';
    $text .= __('was not found!', 'pivot');
    print _show_warning($text, 'warning');
    return '';
  }

  return apply_filters('pivot_locate_template', $template, $template_name, $template_path, $default_path);
}

/**
 * Simple Templating function
 *
 * @param $name   - Name of the template file.
 * @param $args   - Associative array of variables to pass to the template file.
 * @return string - Output of the template file. Likely HTML.
 */
function pivot_template($name, $args) {
  // Search template file in theme folder.
  $template = locate_template($name . '.php');
  // Get plugins template file.
  if (!$template) {
    $template = MY_PLUGIN_PATH . 'templates/' . $name . '.php';
  }

  // Ensure the file exists
  if (!file_exists($template)) {
    $text = __('The required template', 'pivot');
    $text .= ' ' . $name . ' ';
    $text .= __('was not found!', 'pivot');
    print _show_warning($text, 'warning');
    return '';
  }

  // buffer the output (including the file is "output")
  ob_start();
  include $template;

  return ob_get_clean();
}

/**
 * Add redirects to point desired virtual page paths to the new
 * index.php?virtualpage=name destination.
 *
 * After this code is updated, the permalink settings in the administration
 * interface must be saved before they will take effect. This can be done
 * programmatically as well, using flush_rewrite_rules() triggered on theme
 * or plugin install, update, or removal.
 */
function pivot_add_rewrite_rules() {
  add_rewrite_tag('%' . 'details' . '%', '([^&]+)');
  add_rewrite_rule(
    'details' . '/([^/]*&type=\d+)/?$',
    'index.php?' . 'details' . '=$matches[1]',
    'top'
  );

  $pages = pivot_get_pages();

  foreach ($pages as $pivot_page) {
    add_rewrite_tag('%' . $pivot_page->path . '%', '([^&]+)');
    add_rewrite_rule(
      '^' . $pivot_page->path . '$',
      'index.php?pagename=' . $pivot_page->path,
      'top'
    );
    add_rewrite_rule(
      $pivot_page->path . '/([^/]*)/?$',
      'index.php?' . $pivot_page->path . '=$matches[1]',
      'top'
    );
  }
}

add_action('init', 'pivot_add_rewrite_rules');

// Check if we are in custom page, to override bypass 404 status
add_filter('pre_handle_404', function ($preempt, $wp_query) {
  global $wp;
  $customPages = pivot_get_pages_path();
  $customPages[] = array('path' => 'details');
  // To be sure paged case are also treated
  $request_path = rtrim(strtok($wp->request, '&'), '/');
  $key = array_search($request_path, array_column($customPages, 'path'));
  // If Session has been too long or token is lost, reload first page
  if (strpos($wp->request, '&paged=')) {
    $page_id = $customPages[$key]['id'];
    $stored_token = pivot_state_get_shared_token($page_id);
    if (pivot_state_get($page_id, 'token') === null && $stored_token === false) {
      $pos = strpos($_SERVER['REQUEST_URI'], "&paged=");
      $url = substr($_SERVER['REQUEST_URI'], 0, $pos);
      header('Location:' . $url);
    }
  }
  if (isset($key) && is_int($key)) {
    pivot_create_fake_post($customPages[$key]['title'], $customPages[$key]['path'], $customPages[$key]['description']);
    $preempt = true;
  }

  return $preempt;
}, 10, 2);

/**
 * Create a fake post
 * @global Object $wp_query
 * @global Object $wp
 * @param string $title
 * @param string $path
 */
function pivot_create_fake_post($title, $path, $description, $post_type = 'page', $offer_id = null) {
  global $wp_query;
  global $wp;
  // negative ID, to avoid clash with a valid post
  $post_id = 0;

  $post = new stdClass();
  $post->ID = $post_id;
  $post->pivot_id = $offer_id;
  $post->post_author = 1;
  $post->post_date = current_time('mysql');
  $post->post_date_gmt = current_time('mysql', 1);
  $post->post_title = $title;
  $post->post_excerpt = $description;
  $post->post_content = '';
  $post->post_status = 'publish';
  $post->comment_status = 'closed';
  $post->ping_status = 'closed';
  // append random number to avoid clash
  $post->post_name = $path;
  $post->post_type = $post_type;
  // important!
  $post->filter = 'raw';

  $wp_post = new WP_Post($post);
//  wp_cache_add($post_id, $wp_post, 'posts');
  // Update the main query
  $wp_query->post = $wp_post;
  $wp_query->posts = array($wp_post);
  $wp_query->queried_object = $wp_post;
  $wp_query->queried_object_id = $post_id;
  $wp_query->found_posts = 1;
  $wp_query->post_count = 1;
  $wp_query->max_num_pages = 1;
  $wp_query->is_page = ($post_type == 'page') ? true : false;
  $wp_query->is_singular = true;
  $wp_query->is_single = false;
  $wp_query->is_attachment = false;
  $wp_query->is_archive = false;
  $wp_query->is_category = false;
  $wp_query->is_tag = false;
  $wp_query->is_tax = false;
  $wp_query->is_author = false;
  $wp_query->is_date = false;
  $wp_query->is_year = false;
  $wp_query->is_month = false;
  $wp_query->is_day = false;
  $wp_query->is_time = false;
  $wp_query->is_search = false;
  $wp_query->is_feed = false;
  $wp_query->is_comment_feed = false;
  $wp_query->is_trackback = false;
  $wp_query->is_home = false;
  $wp_query->is_embed = false;
  $wp_query->is_404 = false;
  $wp_query->is_paged = false;
  $wp_query->is_admin = false;
  $wp_query->is_preview = false;
  $wp_query->is_robots = false;
  $wp_query->is_posts_page = false;
  $wp_query->is_post_type_archive = false;
  $GLOBALS['wp_query'] = $wp_query;
  $wp->register_globals();
}

/*
 * Function to get active page number used for pagination
 * @return int current page number for pagination
 */

function pivot_get_current_page() {
  // The page number used to be parsed in two places with different conventions: here,
  // returning a raw substring starting at 0, and again in pivot_construct_output(),
  // returning an int starting at 1. This is now the single implementation.
  if (($pos = strpos($_SERVER['REQUEST_URI'], "paged=")) === FALSE) {
    return 1;
  }

  $current_page = absint(substr($_SERVER['REQUEST_URI'], $pos + strlen("paged=")));

  return max(1, $current_page);
}
