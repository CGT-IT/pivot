<?php

add_action('init', 'pivot_start_session', 1);
add_action('end_session_action', 'pivot_end_session');

// register jquery and style on initialization
add_action('init', 'pivot_register_script');

add_action('admin_enqueue_scripts', 'pivot_enqueue_admin_script');
add_action('wp_enqueue_scripts', 'pivot_enqueue_script');

/**
 * Init Session
 */
function pivot_start_session() {
  if(!session_id()) {
    session_start();
  }
}

/**
 * End Session
 */
function pivot_end_session() {
  session_destroy ();
}

/**
 * Add script on Admin part (on condition)
 */
function pivot_enqueue_admin_script() {
//  wp_enqueue_script('my_custom_script_map', MY_PLUGIN_URL.'js/'.'map.js',array('jquery'), '2.0', true);
  // Add script only in this case
  // page is "pivot-filters" and "edit" is set to true
  if(isset($_GET['page']) && $_GET['page'] === "pivot-filters" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_filters_script', MY_PLUGIN_URL.'js/'.'filters.js',array('jquery'), '3.0', true);
  }
  if(isset($_GET['action']) && $_GET['action'] === 'edit'){
    wp_enqueue_script('clipboard_script', MY_PLUGIN_URL.'js/'.'clipboard.min.js',array('jquery'), '1.0', true);
    wp_enqueue_script('pivot_shortcode_script', MY_PLUGIN_URL.'js/'.'shortcode.js',array('jquery'), '1.9', true);
  }
  if(isset($_GET['page']) && $_GET['page'] === "pivot-pages" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_pages_script', MY_PLUGIN_URL.'js/'.'pages.js',array('jquery'), '3.0', true);
  }
  if(isset($_GET['page']) && $_GET['page'] === "pivot-offer-types" && isset($_GET['edit']) && $_GET['edit'] === 'true'){
    wp_enqueue_script('pivot_typeofr_script', MY_PLUGIN_URL.'js/'.'typeofr.js',array('jquery'), '3.0', true);
  }
  if(isset($_GET['page']) && $_GET['page'] === "pivot-admin"){
    wp_enqueue_script('pivot_config_script', MY_PLUGIN_URL.'js/'.'config.js',array('jquery'), '3.0', true);
  }
}

/**
 * Register Scripts
 */
function pivot_register_script() {
  wp_register_style('lodging_style', MY_PLUGIN_URL.'css/'.'pivot-lodging.css', array(), '3.2', false);
  wp_register_style('event_style', MY_PLUGIN_URL.'css/'.'pivot-event.css', array(), '2.0', false);
  wp_register_style('fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), '1.0.0', false);
  wp_register_style('bootstrapexternal', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css', array(), '1.0.0', false);
  wp_register_script('slimmin', 'https://code.jquery.com/jquery-3.3.1.min.js', array(), null, false);
  wp_register_script('poppermin', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), null, true);
  wp_register_script('bootstrapmin', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js', array(), null, true);
  wp_register_script('dataTablesmin', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array(), null, true);
}

/**
 * Add the registered jquery and style above
 */
function pivot_enqueue_script(){
  wp_enqueue_style('lodging_style');
  wp_enqueue_style('event_style');
  // Add only if Boostrap is set.
  if(get_option('pivot_bootstrap') == 'on'){
    wp_enqueue_script('pivot_config_test', MY_PLUGIN_URL.'js/'.'cgtvarious.js',array('jquery'), '1.0', true);
    wp_enqueue_style('fontawesome');
    wp_enqueue_style('bootstrapexternal');
    wp_enqueue_script('slimmin');
    wp_enqueue_script('poppermin');
    wp_enqueue_script('bootstrapmin');
    wp_enqueue_script('dataTablesmin');
  }
}