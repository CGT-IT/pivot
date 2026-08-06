<?php
/*
 * Plugin Name: Pivot
 * Description: Un plugin pour l'affichage et la recherche (via webservice) des offres touristiques disponibles dans la DB Pivot
 * Version: 2.5.0
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Maxime Degembe
 * License: GPL2
 * Text Domain: pivot
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

define('PIVOT_VERSION', '2.5.0');
define('PIVOT_DB_VERSION', 240);
define('PIVOT_CACHE_GROUP', 'pivot');
define('PIVOT_PLUGIN_FILE', __FILE__);
define('PIVOT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PIVOT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Legacy aliases: templates cloned into client themes reference these names.
define('MY_PLUGIN_PATH', PIVOT_PLUGIN_PATH);
define('MY_PLUGIN_URL', PIVOT_PLUGIN_URL);

/*
 * Helper to check if there is an update for the plugin.
 * Will allow user to update this plugin via the "wordpress way"
 */
require_once('inc/external/plugin-update-checker/plugin-update-checker.php');
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
  'https://github.com/CGT-IT/pivot/',
  __FILE__,
  'pivot'
);

// Include all files
foreach (glob(PIVOT_PLUGIN_PATH . "inc/*.php") as $file) {
  require_once $file;
}
// Include all external files.
// These are libraries only: no file in inc/external/ may run code at include time.
foreach (glob(PIVOT_PLUGIN_PATH . "inc/external/*.php") as $file) {
  require_once $file;
}

require_once(PIVOT_PLUGIN_PATH . 'pivot-filter-widget.php');
require_once(PIVOT_PLUGIN_PATH . 'pivot-shortcode.php');

$bitly_params = array();
$bitly_params['access_token'] = get_option('pivot_bitly');
$bitly_params['domain'] = 'bit.ly';

register_activation_hook(__FILE__, 'pivot_install');
register_activation_hook(__FILE__, 'pivot_install_data');
register_deactivation_hook(__FILE__, 'pivot_deactivation');
register_uninstall_hook(__FILE__, 'pivot_uninstall');

// Configuration rows are memoised for the duration of a request only: a persistent
// object cache would otherwise keep serving pages that an editor just changed.
wp_cache_add_non_persistent_groups(array(PIVOT_CACHE_GROUP));

add_action('admin_menu', 'pivot_menu');
add_action('admin_init', 'pivot_settings');
add_action('init', 'pivot_load_textdomain');

function pivot_load_textdomain() {
  load_plugin_textdomain('pivot', false, basename(dirname(__FILE__)) . '/languages');
}

function set_plugin_meta($links, $file) {
  $plugin = plugin_basename(__FILE__);

  // Create link
  if ($file == $plugin) {
    return array_merge(
      $links,
      array(sprintf('<a target="_blank" href="https://github.com/CGT-IT/pivot">GitHub</a>'),
        sprintf('<a target="_blank" href="https://github.com/CGT-IT/pivot/wiki">Documentation</a>'))
    );
  }
  return $links;
}

add_filter('plugin_row_meta', 'set_plugin_meta', 10, 2);

function add_clear_pivot_cache_menu_item($wp_admin_bar) {
  if (!current_user_can('edit_others_pages')) {
    return;
  }
  global $wp_query;
  // If well on a pivot fake page
  if (isset($wp_query->post) && $wp_query->post->ID === 0) {
    $pivot_page = pivot_get_page_path(_get_path());
    // case listing page
    if (isset($pivot_page->id)) {
      $args = array(
        'id' => 'clear-pivot-cache',
        'parent' => null,
        'group' => null,
        'title' => __('Clear current page Pivot cache', 'pivot'),
        'href' => wp_nonce_url(
          admin_url('admin.php?page=pivot-pages&clear-pivot-cache=' . absint($pivot_page->id)),
          'pivot_clear_cache_' . absint($pivot_page->id)
        ),
      );
      $wp_admin_bar->add_node($args);
    }
    // case offer details page
    if (get_option('pivot_transient') == 'on' && isset($wp_query->post->pivot_id)) {
      $offer_id = sanitize_text_field($wp_query->post->pivot_id);
      $args = array(
        'id' => 'clear-pivot-cache',
        'parent' => null,
        'group' => null,
        'title' => __('Clear current page Pivot cache', 'pivot'),
        'href' => wp_nonce_url(
          admin_url('admin.php?page=pivot-pages&clear-pivot-offer-cache=' . rawurlencode($offer_id)),
          'pivot_clear_offer_cache_' . $offer_id
        ),
      );
      $wp_admin_bar->add_node($args);
    }
  }
}

add_action('admin_bar_menu', 'add_clear_pivot_cache_menu_item');

/**
 * Creation of Pivot Tables to store configuration settings
 * @global Object $wpdb
 */
function pivot_install() {
  // Create an instance of the database class
  global $wpdb;
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  $charset_collate = $wpdb->get_charset_collate();

  // The schema below is the full, current one. It used to describe the tables as they
  // were several releases ago, so a fresh install was missing every column added by
  // the migrations — which then replayed their ALTER TABLE statements and failed.
  $table_name = $wpdb->prefix . "pivot_pages";
  // Define sql statement to create the table
  $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            type varchar(100) NOT NULL,
            query varchar(100) NOT NULL,
            path varchar(100) NOT NULL,
            title varchar(128) NOT NULL,
            map tinyint(1) NOT NULL,
            sortMode varchar(50) DEFAULT NULL,
            sortField varchar(100) DEFAULT NULL,
            nbcol smallint(1) DEFAULT '4',
            description longtext DEFAULT NULL,
            image varchar(255) DEFAULT NULL,
            shortcode varchar(255) DEFAULT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
  // Execute the sql statement to create the custom table
  dbDelta($sql);

  // Set the custom table name with the wp prefix "pivot"
  $table_name = $wpdb->prefix . "pivot_filter";
  $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            page_id int(11) NOT NULL,
            filter_name varchar(200) NOT NULL,
            filter_title varchar(200) NOT NULL,
            filter_title_nl varchar(200) NOT NULL DEFAULT '',
            filter_title_en varchar(200) NOT NULL DEFAULT '',
            filter_title_de varchar(200) NOT NULL DEFAULT '',
            urn varchar(200) NOT NULL,
            operator varchar(200) NOT NULL,
            type varchar(100) NOT NULL,
            filter_group varchar(200) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY page_id (page_id)
          ) $charset_collate;";
  // Execute the sql statement to create the custom table
  dbDelta($sql);

  // Set the custom table name with the wp prefix "pivot"
  $table_name = $wpdb->prefix . "pivot_offer_type";
  $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL,
            type varchar(200) NOT NULL,
            parent varchar(200) NOT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
  // Execute the sql statement to create the custom table
  dbDelta($sql);

  // A fresh install is already at the latest schema: record it so plugin_upgrade()
  // does not replay the migrations below.
  update_option('pivot_db_version', PIVOT_DB_VERSION);
}

/**
 * Insert default datas in table pivot_offer_type
 * @global Object $wpdb
 */
function pivot_install_data() {
  global $wpdb;
  $table_name = $wpdb->prefix . "pivot_offer_type";
  // Set default offer types
  $data_set[0] = array("id" => 1, "type" => "Hôtel", "parent" => "hebergement");
  $data_set[1] = array("id" => 2, "type" => "Gîte", "parent" => "hebergement");
  $data_set[2] = array("id" => 3, "type" => "Chambre d'hôtes", "parent" => "hebergement");
  $data_set[3] = array("id" => 4, "type" => "Meublé", "parent" => "hebergement");
  $data_set[4] = array("id" => 5, "type" => "Camping", "parent" => "hebergement");
  $data_set[5] = array("id" => 6, "type" => "Budget Holiday", "parent" => "hebergement");
  $data_set[6] = array("id" => 7, "type" => "Village de vacances", "parent" => "hebergement");
  $data_set[7] = array("id" => 9, "type" => "Evénement", "parent" => "activite");
  $data_set[8] = array("id" => 11, "type" => "Découverte et Divertissement", "parent" => "default");
  $data_set[9] = array("id" => 258, "type" => "Producteur", "parent" => "default");
  $data_set[10] = array("id" => 259, "type" => "Artisan", "parent" => "default");
  $data_set[11] = array("id" => 269, "type" => "Point d'intérêt", "parent" => "default");
  $data_set[12] = array("id" => 8, "type" => "Itinéraire", "parent" => "itinerary");
  $data_set[13] = array("id" => 261, "type" => "Restauration", "parent" => "restauration");
  // Execute the sql statement to insert datas
  wp_insert_rows($data_set, $table_name);
}

function pivot_plugin_upgrade() {
  $installed = (int) get_option('pivot_db_version');

  // Already current: nothing to do, and above all no ALTER TABLE to replay.
  if ($installed >= PIVOT_DB_VERSION) {
    return;
  }

  if ($installed < 200) {
    pivot_upgrade_200();
  }
  if ($installed < 210) {
    pivot_upgrade_210();
  }
  if ($installed < 220) {
    pivot_upgrade_220();
  }
  if ($installed < 230) {
    pivot_upgrade_230();
  }

  update_option('pivot_db_version', PIVOT_DB_VERSION);
}

add_action('plugins_loaded', 'pivot_plugin_upgrade');

/**
 * @deprecated 2.5.0 Renamed to pivot_plugin_upgrade(); "plugin_upgrade" was a global
 *   name generic enough to collide with a theme or another plugin.
 */
function plugin_upgrade() {
  pivot_plugin_upgrade();
}

function pivot_upgrade_200() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'pivot_pages';

  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `nbcol` SMALLINT(1) DEFAULT '4'
    ");

  update_option('pivot_db_version', 200);
}

function pivot_upgrade_210() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'pivot_pages';

  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `description` longtext DEFAULT NULL
    ");
  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `image` varchar(255) DEFAULT NULL
    ");

  update_option('pivot_db_version', 210);
}

function pivot_upgrade_220() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'pivot_filter';

  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `filter_title_nl` varchar(200) NOT NULL
    ");
  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `filter_title_en` varchar(200) NOT NULL
    ");
  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `filter_title_de` varchar(200) NOT NULL
    ");

  update_option('pivot_db_version', 220);
}

function pivot_upgrade_230() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'pivot_pages';

  $wpdb->query(
    "ALTER TABLE $table_name
     ADD COLUMN `shortcode` varchar(255) DEFAULT NULL
    ");

  update_option('pivot_db_version', 230);
}

function pivot_deactivation() {
  flush_rewrite_rules();
  wp_cache_flush();
}

/**
 * Drop Pivot tables on plugin uninstall
 * @global Object $wpdb
 */
function pivot_uninstall() {
  global $wpdb;
  $table_name = $wpdb->prefix . "pivot_filter";
  $sql = "DROP TABLE IF EXISTS $table_name;";
  $wpdb->query($sql);

  $table_name = $wpdb->prefix . "pivot_pages";
  $sql = "DROP TABLE IF EXISTS $table_name;";
  $wpdb->query($sql);

  $table_name = $wpdb->prefix . "pivot_offer_type";
  $sql = "DROP TABLE IF EXISTS $table_name;";
  $wpdb->query($sql);

  delete_option('pivot_uri');
  delete_option('pivot_key');
  delete_option('pivot_mdt');
  delete_option('pivot_bootstrap');
  delete_option('pivot_bitly');
  delete_option('pivot_db_version');
  delete_option('pivot_transient');
  delete_option('pivot_transient_time');

  flush_rewrite_rules();
}

/**
 * Add Pivot Menu and submenu
 */
function pivot_menu() {
  add_menu_page('Pivot administration', 'Pivot', 'delete_others_pages', 'pivot-admin', 'pivot_options');
  add_submenu_page('pivot-admin', 'Pivot administration', 'Pivot', 'delete_others_pages', 'pivot-admin');
  add_submenu_page('pivot-admin', 'Offer types', 'Manage offer type', 'manage_options', 'pivot-offer-types', 'pivot_offer_type_settings');
  $pivot_page_submenu = add_submenu_page('pivot-admin', 'Pages', 'Manage pages', 'delete_others_pages', 'pivot-pages', 'pivot_pages_settings');
  add_action("load-$pivot_page_submenu", 'pivot_page_screen_option');
  $pivot_filters_submenu = add_submenu_page('pivot-admin', 'Filters', 'Manage filters', 'delete_others_pages', 'pivot-filters', 'pivot_filters_settings');
  add_action("load-$pivot_filters_submenu", 'pivot_filter_screen_option');
  add_submenu_page('pivot-admin', 'Shortcode', 'Shortcode', 'delete_others_pages', 'pivot-shortcode', 'pivot_build_shortcode_box_html');
  add_submenu_page('pivot-admin', 'Shortcode Event', 'Shortcode Event', 'manage_options', 'pivot-shortcode-event', 'pivot_build_shortcode_event_box_html');
}

function pivot_global_js_vars() {
  echo '<script type="text/javascript">var _pivot_mapbox_token = ' . wp_json_encode(get_option('pivot_mapbox')) . ";</script>\n";
}

add_action('wp_head', 'pivot_global_js_vars');

/**
 * Define main pivot settings
 */
function pivot_settings() {
  register_setting('pivot_settings', 'pivot_uri');
  register_setting('pivot_settings', 'pivot_key');
  register_setting('pivot_settings', 'pivot_mdt');
  register_setting('pivot_settings', 'pivot_bootstrap');
  register_setting('pivot_settings', 'pivot_bitly');
  register_setting('pivot_settings', 'pivot_mapbox');
  register_setting('pivot_settings', 'pivot_transient');
  register_setting('pivot_settings', 'pivot_transient_time');
}

function pivot_options() {
  if (!(current_user_can('editor')) && !(current_user_can('administrator'))) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  echo '<h1>' . get_admin_page_title() . '</h1>';
  ?>

  <form method="post"  id="pivot-settings-form" accept-charset="UTF-8" action="options.php">
      <?php settings_fields('pivot_settings') ?>
      <div>
          <?php
          if (empty(get_option('pivot_uri'))) {
            print _show_admin_notice("Base Uri is required");
          } else {
            if (substr(get_option('pivot_uri'), -1) !== '/') {
              print _show_admin_notice("Base Uri should end with /");
            }
          }
          if (empty(get_option('pivot_key'))) {
            print _show_admin_notice("WS_KEY is required");
          }
          ?>
          <div class="form-item form-type-textfield form-item-pivot-uri">
              <label for="edit-pivot-uri"><?php esc_html_e('Base Uri', 'pivot') ?> <span class="form-required" title="<?php esc_html_e('This field is required') ?>">*</span></label>
              <input type="text" id="edit-pivot-uri" name="pivot_uri" value="<?php echo (get_option('pivot_uri') == '' ? "https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/" : esc_url(get_option('pivot_uri'))) ?>" size="60" maxlength="128" class="form-text required">
              <p class="description"><?php esc_html_e('Uri to access Pivot Database.', 'pivot') ?> <strong><?php esc_html_e('End / is required !', 'pivot') ?></strong></p>
          </div>
          <div class="form-item form-type-textfield form-item-pivot-key">
              <label for="edit-pivot-key">WS_KEY <span class="form-required" title="<?php esc_html_e('This field is required') ?>">*</span></label>
              <input type="password" id="edit-pivot-key" name="pivot_key" value="<?php echo get_option('pivot_key') ?>" size="60" maxlength="128" class="form-text required">
              <p class="description"><?php _e('Personnal Key to access Pivot webservices, take contact with <a href="http://pivot.tourismewallonie.be/index.php/2015-05-05-10-23-26/flux-de-donnees-3-1" target="_blank">Pivot</a>', 'pivot') ?></p>
          </div>

          <span><input id="check-pivot-config" class="button" type="button" value="<?php esc_html_e('Check Pivot config', 'pivot') ?>"> </button></span>

          <div id="pivot-response"></div>

          <div class="form-item form-type-textfield form-item-pivot-mdt">
              <label for="edit-pivot-mdt"><?php esc_html_e('Votre maison de tourisme', 'pivot') ?></label>
              <select id="edit-pivot-mdt" name="pivot_mdt">
                  <option selected disabled hidden><?php esc_html_e('Choisir une maison de tourisme', 'pivot') ?></option>
                  <?php print _get_list_mdt(); ?>
              </select>
          </div>
          <div class="form-item form-type-textfield form-item-pivot-bootsrap">
              <input type="checkbox" id="edit-pivot-bootsrap" name="pivot_bootstrap" class="form-checkbox" <?php echo (get_option('pivot_bootstrap') == 'on' ? 'checked' : ''); ?>>
              <label for="edit-pivot-bootsrap"><?php esc_html_e('Include Bootstrap', 'pivot') ?> </label>
              <p class="description"><a href="https://getbootstrap.com/">Bootstrap </a>(<?php esc_html_e('required for default templates', 'pivot'); ?>)</p>
          </div>
          <div class="form-item form-type-textfield form-item-pivot-transient">
              <input type="checkbox" id="edit-pivot-transient" name="pivot_transient" class="form-checkbox" <?php echo (get_option('pivot_transient') == 'on' ? 'checked' : ''); ?>>
              <label for="edit-pivot-transient"><?php esc_html_e('Active Wordpress Transients for Pivot content', 'pivot') ?> </label>
              <p class="description"><?php esc_html_e('Use Wordpress transients to optimize page speed. Do not use for development purpose', 'pivot') ?></p>
          </div>
          <div class="form-item form-type-textfield form-item-pivot-transient-time">
              <label for="edit-pivot-transient-time"><?php esc_html_e('Define transient validity', 'pivot') ?></label>
              <select id="edit-pivot-transient-time" name="pivot_transient_time">
                  <option selected disabled hidden value="172800"><?php esc_html_e('Choose expiration time of transients', 'pivot') ?></option>
                  <option <?php print (get_option('pivot_transient_time') == "3600" ? 'selected="selected"' : ''); ?> value="3600">1H</option>
                  <option <?php print (get_option('pivot_transient_time') == "43200" ? 'selected="selected"' : ''); ?> value="43200">12H</option>
                  <option <?php print (get_option('pivot_transient_time') == "86400" ? 'selected="selected"' : ''); ?> value="86400">1 <?php esc_html_e('Day'); ?></option>
                  <option <?php print (get_option('pivot_transient_time') == "172800" ? 'selected="selected"' : ''); ?> value="172800">2 <?php esc_html_e('Days'); ?></option>
                  <option <?php print (get_option('pivot_transient_time') == "432000" ? 'selected="selected"' : ''); ?> value="432000">5 <?php esc_html_e('Days'); ?></option>
                  <option <?php print (get_option('pivot_transient_time') == "864000" ? 'selected="selected"' : ''); ?> value="864000">10 <?php esc_html_e('Days'); ?></option>
              </select>
          </div>
          <div class="form-item form-type-textfield form-item-pivot-bitly">
              <label for="edit-pivot-bitly">Bitly access token <span class="form-required" title="<?php esc_html_e('This field is required') ?>">*</span></label>
              <input type="text" id="edit-pivot-bitly" name="pivot_bitly" value="<?php echo get_option('pivot_bitly') ?>" size="60" maxlength="128" class="form-text required">
              <p class="description"><?php _e('Personnal Key to access bitly webservices, signin <a href="https://bitly.com/" target="_blank">bitly.com</a> and get you access token', 'pivot') ?></p>
          </div>
          <div class="form-item form-type-textfield form-item-pivot-mapbox">
              <label for="edit-pivot-mapbox"><?php esc_html_e('MapBox access token', 'pivot') ?> <span class="form-required" title="<?php esc_html_e('This field is required') ?>">*</span></label>
              <input type="text" id="edit-pivot-mapbox" name="pivot_mapbox" value="<?php echo get_option('pivot_mapbox') ?>" size="60" maxlength="128" class="form-text required">
              <p class="description"><?php _e('Personnal Key to access MapBox services, signin <a href="https://account.mapbox.com/" target="_blank">mapbox.com</a> and get you access token', 'pivot') ?></p>
          </div>
          <?php submit_button(); ?>
      </div>
  </form>
  <?php
}

/**
 * Construct and send Webservice query based on param and return XML in an Object
 * @param string $type Define type of query (Lodging, ...)
 * @param int $detail Define level of detail @see Pivot documentation to get levels of detail
 * @param array $params
 * @return Object The XML Webservice response load in a php Object
 */
function _pivot_request($type, $detail, $params = NULL, $postfields = NULL) {
  if (isset($params['shuffle']) && $params['shuffle'] == TRUE) {
    $shuffle = ';shuffle=true';
  } else {
    $shuffle = ';shuffle=false';
  }
  // Get Pivot Base URI
  $pivot_url = esc_url(get_option('pivot_uri'));

  // Construct URL depending on query type
  switch ($type) {
    case 'shortcode':
      $pivot_url .= $params['type'] . ';limit=' . $params['items_per_page'] . ';content=' . $detail . $shuffle . ';info=true;infolvl=10';
      break;
    case 'offer-init-list':
      $pivot_url .= $params['type'] . '/paginated;itemsperpage=' . $params['items_per_page'] . ';content=' . $detail . $shuffle . ';info=true;infolvl=10';
      break;
    case 'offer-pager':
      $pivot_url .= $params['type'] . '/paginated' . $params['token'] . ';content=' . $detail . $shuffle . ';info=true;infolvl=10';
      break;
    case 'offer-search':
      $pivot_url .= $params['type'] . '/paginated;itemsperpage=' . $params['items_per_page'] . ';content=' . $detail . $shuffle . ';info=true;infolvl=10';
      break;
    case 'offer-details':
      $pivot_url .= $params['type'] . '/' . $params['offer_code'] . ';content=' . $detail . ';info=true';
      break;
    case 'thesaurus':
      $pivot_url .= 'thesaurus/urn/' . $params['urn_name'] . ';fmt=xml';
      break;
    case 'full':
      $pivot_url .= $params['type'] . ';content=0';
      break;
  }

  $request_args = array('ws_key' => true);
  if ($postfields != NULL) {
    $request_args['method'] = 'POST';
    $request_args['body'] = $postfields;
  }

  $response = pivot_http_request($pivot_url, $request_args);

  if (is_wp_error($response)) {
    $response = $response->get_error_message();
  }

  /* Check if the response is well an XML file.
   * Could be an error like "[CCM006] Results not found (token and/or page number are incorrect)"
   */
  $xml_object = pivot_http_parse_xml($response);
  if ($xml_object !== false) {
    // Check type of response
    if (($xml_object->attributes()) !== null) {
      // Check if case there is no result
      if (isset($xml_object->attributes()->count) && $xml_object->attributes()->count->__toString() == 0) {
        $error = __('No offer at this time ! Come back later ...', 'pivot');
        print _show_warning($error);
      }
    }
    return $xml_object;
  }

  if (isset($params['page_id']) || isset($params['shortcode'])) {
    if (isset($params['page_id'])) {
      // If it is event, show a specific error message
      $page = pivot_get_page($params['page_id']);
      if ($page && $page->type == 'activite') {
        $error = __('Too bad, no event planned at this time ! Come back later ...', 'pivot');
      } else {
        $error = __('No offer at this time ! Come back later ...', 'pivot');
      }
      $output = '<div class="container">'
        . '<div class="row">'
        . '<div class="col mx-auto my-5">'
        . _show_warning($error)
        . '<form>'
        . '<input class="btn btn-outline-dark btn-lg btn-block btn-filter shadow py-3" type="button" value="' . esc_attr__('Go back!') . '" onclick="history.back()">'
        . '</form>'
        . '</div></div></div>';
      print $output;
      get_footer();
    }
    // case shortcode and error, avoid page construction errors
    if ((isset($params['shortcode']) && $params['shortcode'] == true)) {
      $error = __('No offer at this time ! Come back later ...', 'pivot');
      $output = '<div class="container">'
        . '<div class="row">'
        . '<div class="col mx-auto my-5">'
        . _show_warning($error)
        . '</div></div></div>';
      return $output;
    }
  } else {
    print pivot_template('pivot-problem-template', $response);
  }
  exit();
}

/**
 * Construction of the xml query with query id and filters
 * @param string $query_id
 * @param array $field_params
 * @return xml file
 */
function _xml_query_construction($query_id = NULL, $field_params = NULL) {
  // Init XML document
  $domDocument = new DOMDocument('1.0', "UTF-8");
  $queryElement = $domDocument->createElement('Query');

  // Creation of an attribute
  $xmlnsAttribute = $domDocument->createAttribute('xmlns');
  // Value for the created attribute
  $xmlnsAttribute->value = 'http://pivot.tourismewallonie.be/files/xsd/pivot/3.1';
  // Append it to the element
  $queryElement->appendChild($xmlnsAttribute);

  $xsiAttribute = $domDocument->createAttribute('xmlns:xsi');
  $xsiAttribute->value = 'http://www.w3.org/2001/XMLSchema-instance';
  $queryElement->appendChild($xsiAttribute);

  $schemaLocationAttribute = $domDocument->createAttribute('xsi:schemaLocation');
  $schemaLocationAttribute->value = 'http://pivot.tourismewallonie.be/files/xsd/pivot/3.1 http://pivot.tourismewallonie.be/files/xsd/pivot/3.1/pivot310-import-query.xsd';
  $queryElement->appendChild($schemaLocationAttribute);

  // Add sorting if needed
  if (isset($field_params['sortField']) && isset($field_params['sortMode'])) {
    // Add sortField
    $sortField = $domDocument->createAttribute('sortField');
    $sortField->value = $field_params['sortField'];
    $queryElement->appendChild($sortField);
    // Add sortMode
    $sortMode = $domDocument->createAttribute('sortMode');
    $sortMode->value = $field_params['sortMode'];
    $queryElement->appendChild($sortMode);
  }

  $criteriaGroupElement = $domDocument->createElement('CriteriaGroup');

  $typeAttribute = $domDocument->createAttribute('type');
  $typeAttribute->value = 'and';
  $criteriaGroupElement->appendChild($typeAttribute);

  $criteriaGroupSubElement = $domDocument->createElement('CriteriaGroup');

  $typeAttribute = $domDocument->createAttribute('type');
  $typeAttribute->value = 'and';
  $criteriaGroupSubElement->appendChild($typeAttribute);

  if (isset($field_params['criterafield']) && $field_params['criterafield'] == TRUE) {
    if (isset($field_params['filters'])) {
      foreach ($field_params['filters'] as $filter) {
        $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
        $criteriaGroupSubElement->appendChild($criteriaFieldElement);
      }
    }
  }

  if (isset($field_params['radius'])) {
    $criteriaOrthodromicElement = $domDocument->createElement('CriteriaOrthodromic');
    $idInsValueElement = $domDocument->createElement('idIns', $field_params['idIns']);
    $criteriaOrthodromicElement->appendChild($idInsValueElement);
    $radiusValueElement = $domDocument->createElement('radius', $field_params['radius']);
    $criteriaOrthodromicElement->appendChild($radiusValueElement);

    $criteriaGroupSubElement->appendChild($criteriaOrthodromicElement);
  }

  if (isset($query_id)) {
    $criteriaQueryElement = $domDocument->createElement('CriteriaQuery');
    $queryValueElement = $domDocument->createElement('value', $query_id);
    $criteriaQueryElement->appendChild($queryValueElement);
    $criteriaGroupSubElement->appendChild($criteriaQueryElement);

    // For event check only those where "date fin publication" is between now and +6month
    if (isset($field_params['page_type']) && $field_params['page_type'] == 'activite') {
      $field_params['filters']['datefinmin']['name'] = 'urn:fld:date:datefin';
      $field_params['filters']['datefinmin']['operator'] = 'greaterequal';
      $field_params['filters']['datefinmin']['searched_value'][] = date("d/m/Y", strtotime('today'));
      $field_params['filters']['datefinvalid']['name'] = 'urn:fld:datefinvalid';
      $field_params['filters']['datefinvalid']['operator'] = 'greaterequal';
      $field_params['filters']['datefinvalid']['searched_value'][] = date("d/m/Y", strtotime('today'));
      $field_params['filters']['datedebvalid']['name'] = 'urn:fld:datedebvalid';
      $field_params['filters']['datedebvalid']['operator'] = 'lesserequal';
      $field_params['filters']['datedebvalid']['searched_value'][] = date("d/m/Y", strtotime('today'));
    }

    if (isset($field_params['filters'])) {
      foreach ($field_params['filters'] as $filter) {
        $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
        $criteriaGroupSubElement->appendChild($criteriaFieldElement);
      }
    }
  }

  $criteriaGroupElement->appendChild($criteriaGroupSubElement);

  // Add OR for date comparison
  if (isset($field_params['or'])) {
    $criteriaGroupSubElement = $domDocument->createElement('CriteriaGroup');

    $typeAttribute = $domDocument->createAttribute('type');
    $typeAttribute->value = 'or';
    $criteriaGroupSubElement->appendChild($typeAttribute);

    if (isset($field_params['or'][1])) {
      $criteriaGroupSubSubElement = $domDocument->createElement('CriteriaGroup');

      $typeAttribute = $domDocument->createAttribute('type');
      $typeAttribute->value = 'and';
      $criteriaGroupSubSubElement->appendChild($typeAttribute);

      foreach ($field_params['or'][1] as $filter) {
        $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
        $criteriaGroupSubSubElement->appendChild($criteriaFieldElement);
      }
      $criteriaGroupSubElement->appendChild($criteriaGroupSubSubElement);
    }

    if (isset($field_params['or'][2])) {
      $criteriaGroupSubSubElement = $domDocument->createElement('CriteriaGroup');

      $typeAttribute = $domDocument->createAttribute('type');
      $typeAttribute->value = 'and';
      $criteriaGroupSubSubElement->appendChild($typeAttribute);

      foreach ($field_params['or'][2] as $filter) {
        $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
        $criteriaGroupSubSubElement->appendChild($criteriaFieldElement);
      }
      $criteriaGroupSubElement->appendChild($criteriaGroupSubSubElement);
    }

    $criteriaGroupElement->appendChild($criteriaGroupSubElement);
  }// End OR

  $queryElement->appendChild($criteriaGroupElement);

  $domDocument->appendChild($queryElement);

  return $domDocument->saveXML();
}

function _create_dom_criteria_field_element($domDocument, $filter) {
  // Creation of a field element
  $criteriaFieldElement = $domDocument->createElement('CriteriaField');

  // Creation of an attribute
  $fieldAttribute = $domDocument->createAttribute('field');
  // Value for the created attribute 'urn:fld:typeofr'
  $fieldAttribute->value = $filter['name'];
  // Append it to the element
  $criteriaFieldElement->appendChild($fieldAttribute);

  $operatorAttribute = $domDocument->createAttribute('operator');
  $operatorAttribute->value = $filter['operator'];
  $criteriaFieldElement->appendChild($operatorAttribute);

  $targetAttribute = $domDocument->createAttribute('target');
  $targetAttribute->value = 'value';
  $criteriaFieldElement->appendChild($targetAttribute);

  // Not always set. In accordance to the "operator" type
  if (isset($filter['searched_value'])) {
    foreach ($filter['searched_value'] as $filter_value) {
      $criteriaValueElement = $domDocument->createElement('value', $filter_value);
      $criteriaFieldElement->appendChild($criteriaValueElement);
    }
  }

  return $criteriaFieldElement;
}

/**
 * Return offers resulting of the XML query
 * @param int $page_id
 * @return Object all offers resulting of the XML query
 */
function pivot_lodging_page($page_id, $details = 2, $offers_per_page = null) {
  $field_params = array();
  // Get current page details
  $pivot_page = pivot_get_page($page_id);
  if ($pivot_page) {
    $field_params['page_type'] = $pivot_page->type;
    if ($offers_per_page == null) {
      // Define how many offers per page
      $offers_per_page = _define_nb_offers_per_page($pivot_page->nbcol);
    }
  }

  // Check if there if a sort is defined
  if (isset($pivot_page->sortMode) && $pivot_page->sortMode != NULL && $pivot_page->sortMode != '') {
    $field_params['sortField'] = $pivot_page->sortField;
    $field_params['sortMode'] = $pivot_page->sortMode;
  }

  // Check if there is at least ONE active filter
  if (pivot_state_has_filters($page_id)) {
    $between = array();
    foreach (pivot_state_get_filters($page_id) as $key => $value) {
      // Get details of filter based on his ID
      $filter = pivot_get_filter($key);
      if (!$filter) {
        continue;
      }

      $field_params = _construct_filters_array($field_params, $filter, $key, $page_id);

      // If dateDeb or dateFin has been set, we save it to check what to do at the end of the foreach
      if ($filter->urn == 'urn:fld:date:datedeb' || $filter->urn == 'urn:fld:date:datefin') {
        $between[$key] = $filter->urn;
      }
    }

    // If dateDeb and dateFin has been set we remove them from classic filters
    // They are in a OR condition
    if (count($between) == 2) {
      foreach ($between as $key => $date) {
        unset($field_params['filters'][$key]);
      }
    } else {
      // If dateDeb and dateFin has not both been set we remove them from the OR condition
      unset($field_params['or']);
    }
  }

  $xml_query = _xml_query_construction($pivot_page ? $pivot_page->query : null, $field_params);

  // define call case depending there is a filter or not
  if (isset($field_params['filters'])) {
    $offers = pivot_construct_output('offer-search', $offers_per_page, $xml_query, $page_id, $details);
  } else {
    $offers = pivot_construct_output('offer-pager', $offers_per_page, $xml_query, $page_id, $details);
  }

  return $offers;
}

/**
 *
 * @param string $case Define request case
 * @param int $offers_per_page Number of offers per page to display
 * @param Object $xml_query XML file with request to Pivot (filter on specific fields)
 * @param int $page_id to be able to retrieve the page attributes or to know if it's a shortcode
 * @param int $details level of details ask, 2 by default
 * @return string part of HTML to display
 */
function pivot_construct_output($case, $offers_per_page, $xml_query = NULL, $page_id = NULL, $details = 2) {
  $context = pivot_build_request_context($case, $offers_per_page, $page_id);

  if ($context['is_shortcode']) {
    $xml_object = pivot_fetch_shortcode_offers($context, $xml_query, $details);
  } else {
    $xml_object = pivot_fetch_page_offers($context, $xml_query, $details);
  }

  // On failure _pivot_request() returns either an error string or nothing at all;
  // callers test is_object() to tell the two apart.
  return is_object($xml_object) ? $xml_object->offre : $xml_object;
}

/**
 * Gather everything a Pivot listing request depends on.
 *
 * A numeric $page_id is a listing page from wp_pivot_pages; anything else is a
 * shortcode, identified by its query.
 *
 * @param string $case
 * @param int $offers_per_page
 * @param int|string|null $page_id
 * @return array
 */
function pivot_build_request_context($case, $offers_per_page, $page_id) {
  $is_shortcode = ($page_id !== NULL && !is_numeric($page_id));
  $page = (!$is_shortcode && $page_id !== NULL) ? pivot_get_page($page_id) : null;

  $params = array(
    'type' => 'query',
    'items_per_page' => $offers_per_page,
  );
  if ($is_shortcode) {
    $params['shortcode'] = true;
  } elseif ($page_id !== NULL) {
    $params['page_id'] = $page_id;
  }

  // $page was read before it was loaded, so shuffle never reached Pivot on a
  // listing page.
  if ($page && $page->sortMode === 'shuffle') {
    $params['shuffle'] = TRUE;
  }

  return array(
    'case' => $is_shortcode ? 'shortcode' : $case,
    'page_id' => $page_id,
    'page' => $page,
    'is_shortcode' => $is_shortcode,
    // A shortcode always renders its first page, whatever ?paged= says: it can sit
    // inside a paginated listing page.
    'current_page' => $is_shortcode ? 1 : pivot_get_current_page(),
    'has_filters' => ($page_id !== NULL) && pivot_state_has_filters($page_id),
    'params' => $params,
  );
}

/**
 * Offers of a shortcode: one stateless request, no pagination.
 *
 * @param array $context
 * @param string $xml_query
 * @param int $details
 * @return SimpleXMLElement|string
 */
function pivot_fetch_shortcode_offers($context, $xml_query, $details) {
  return _pivot_request('shortcode', $details, $context['params'], $xml_query);
}

/**
 * Offers of a listing page, with pagination.
 *
 * Pivot paginates with a token returned by the first request. Unfiltered listings
 * are identical for every visitor, so their token is shared through a transient;
 * a filtered search is specific to one visitor and its token lives in their state.
 *
 * @param array $context
 * @param string $xml_query
 * @param int $details
 * @return SimpleXMLElement|string
 */
function pivot_fetch_page_offers($context, $xml_query, $details) {
  $page_id = $context['page_id'];
  $params = $context['params'];
  $current_page = $context['current_page'];

  // The transient key embeds a signature of the active filters, so a filtered search
  // now has a shared token too: two visitors running the same search reuse it, and it
  // survives across requests instead of dying with the session.
  $token = pivot_state_get_shared_token($page_id);

  // Past the first page we can only ask Pivot for more of an existing result set.
  if ($current_page > 1 && !empty($token)) {
    $params['token'] = '/' . $token . '/' . $current_page;

    return _pivot_request('offer-pager', $details, $params);
  }

  $xml_object = _pivot_request('offer-init-list', $details, $params, $xml_query);

  if (!is_object($xml_object)) {
    return $xml_object;
  }

  pivot_store_pagination_state($context, $xml_object);

  // Deep link straight to page N with no token cached — now that searches live in
  // shareable URLs this is a normal entry point, not an expired session. Replay the
  // request with the token we just obtained instead of silently showing page 1.
  if ($current_page > 1) {
    $fresh_token = pivot_state_get_shared_token($page_id);
    if (!empty($fresh_token)) {
      $params['token'] = '/' . $fresh_token . '/' . $current_page;

      return _pivot_request('offer-pager', $details, $params);
    }
  }

  return $xml_object;
}

/**
 * Remember the token and the offer count returned by an initial listing request.
 *
 * @param array $context
 * @param SimpleXMLElement $xml_object
 */
function pivot_store_pagination_state($context, $xml_object) {
  $page_id = $context['page_id'];
  if ($page_id === NULL) {
    return;
  }

  $attributes = $xml_object->attributes();
  $token = isset($attributes->token) ? $attributes->token->__toString() : '';
  $count = isset($attributes->count) ? str_replace(', ', '', $attributes->count->__toString()) : '0';

  pivot_state_set($page_id, 'nb_offres', $count);

  // Events churn faster than the rest of the catalogue, so their token expires sooner.
  $page = $context['page'];
  $ttl = ($page && $page->type === 'activite') ? 12 * HOUR_IN_SECONDS : DAY_IN_SECONDS;
  // A filtered search is one visitor's query: keep it around long enough to page
  // through the results, not for a day.
  if ($context['has_filters']) {
    $ttl = HOUR_IN_SECONDS;
  }

  pivot_state_set_shared_token($page_id, $token, $ttl);
  pivot_state_set_shared_count($page_id, $count, $ttl);
}
