<?php
/*
 * Plugin Name: Pivot
 * Description: Un plugin pour l'affichage et la recherche (via webservice) des offres touristiques disponibles dans la DB Pivot
 * Version: 1.3.6
 * Author: Maxime Degembe
 * License: GPL2
 * Text Domain: pivot
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');
// define
define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));

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

add_action( 'wpseo_opengraph', 'change_yoast_seo_og_meta' );

/**
* Function to add hooks and filter out the Yoast SEO Open Graph Meta Tags
*/
function change_yoast_seo_og_meta() {
  add_filter( 'wpseo_opengraph_title', 'change_title');
//  add_filter( 'wpseo_locale', 'change_locale' );
  add_filter( 'wpseo_opengraph_type', 'change_type' );
  add_filter( 'wpseo_opengraph_desc', 'change_desc' );
  add_filter( 'wpseo_opengraph_url', 'change_url' );
//  add_action( 'wpseo_add_opengraph_images', 'add_images' );
}
function change_title($title){
  global $offre_meta_data;
  if(!is_singular()){
    $title = $offre_meta_data['title'];
  }
  return $title;
}
function change_type($type){
  global $offre_meta_data;
  if(!is_singular()){
    $type = $offre_meta_data['type'];
  }
  return $type;
}
function change_desc($desc){
  global $offre_meta_data;
  if(!is_singular()){
    $desc = $offre_meta_data['description'];
  }
  return $desc;
}
function change_url($url){
  global $offre_meta_data;
  if(!is_singular()){
    $url = $offre_meta_data['url'];
  }
  return $url;
}

// Include all files
foreach (glob(MY_PLUGIN_PATH. "inc/*.php") as $file) {
  require_once $file;
}
// Include all external files
foreach (glob(MY_PLUGIN_PATH. "inc/external/*.php") as $file) {
  require_once $file;
}

require_once(MY_PLUGIN_PATH. 'pivot-filter-widget.php');
require_once(MY_PLUGIN_PATH. 'pivot-shortcode.php');

$bitly_params = array();
$bitly_params['access_token'] = get_option('pivot_bitly');
$bitly_params['domain'] = 'bit.ly';

register_activation_hook(__FILE__, 'pivot_install');
register_activation_hook(__FILE__, 'pivot_install_data');
register_deactivation_hook(__FILE__, 'pivot_deactivation');
register_uninstall_hook(__FILE__, 'pivot_uninstall');

add_action('init', 'init');
add_action('admin_menu', 'pivot_menu');
add_action('admin_init', 'pivot_settings');
add_action('init', 'pivot_load_textdomain');
function pivot_load_textdomain() {
	load_plugin_textdomain('pivot', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

function set_plugin_meta($links, $file){
	$plugin = plugin_basename(__FILE__);
	
  // create link
	if($file == $plugin){
		return array_merge(
			$links,
			array(sprintf( '<a target="_blank" href="https://github.com/CGT-IT/pivot">GitHub</a>' ),
            sprintf( '<a target="_blank" href="https://github.com/CGT-IT/pivot/wiki">Documentation</a>' ))
    );
  }
  
	return $links;
}
add_filter( 'plugin_row_meta', 'set_plugin_meta', 10, 2 );

/**
 * Creation of Pivot Tables to store configuration settings
 * @global Object $wpdb
 */
function pivot_install() {
  // Create an instance of the database class
  global $wpdb;
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  $charset_collate = $wpdb->get_charset_collate();

  // Set the custom table name with the wp prefix "pivot"
  $table_name = $wpdb->prefix . "pivot_pages";
  // Define sql statement to create the table
  $sql = "CREATE TABLE ".$table_name." (
            id int(11) NOT NULL AUTO_INCREMENT,
            type varchar(100) NOT NULL,
            query varchar(100) NOT NULL,
            path varchar(100) NOT NULL,
            title varchar(128) NOT NULL,
            map tinyint(1) NOT NULL,
            sortMode varchar(50) DEFAULT NULL,
            sortField varchar(100) DEFAULT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
  // Execute the sql statement to create the custom table
  dbDelta($sql);
  
  // Set the custom table name with the wp prefix "pivot"
  $table_name = $wpdb->prefix . "pivot_filter";
  $sql = "CREATE TABLE ".$table_name." (
            id int(11) NOT NULL AUTO_INCREMENT,
            page_id int(11) NOT NULL,
            filter_name varchar(200) NOT NULL,
            filter_title varchar(200) NOT NULL,
            urn varchar(200) NOT NULL,
            operator varchar(200) NOT NULL,
            type varchar(100) NOT NULL,
            filter_group varchar(200) DEFAULT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
  // Execute the sql statement to create the custom table
  dbDelta($sql);
  
  // Set the custom table name with the wp prefix "pivot"
  $table_name = $wpdb->prefix . "pivot_offer_type";
  $sql = "CREATE TABLE ".$table_name." (
            id int(11) NOT NULL,
            type varchar(200) NOT NULL,
            parent varchar(200) NOT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
  // Execute the sql statement to create the custom table
  dbDelta($sql);
}

/**
 * Insert default datas in table pivot_offer_type
 * @global Object $wpdb
 */
function pivot_install_data() {
  global $wpdb;
  $table_name = $wpdb->prefix . "pivot_offer_type";
  // Set default offer types
  $data_set[0]= array("id" => 1, "type" => "Hôtel", "parent" => "hebergement");
  $data_set[1]= array("id" => 2, "type" => "Gîte", "parent" => "hebergement");
  $data_set[2]= array("id" => 3, "type" => "Chambre d'hôtes", "parent" => "hebergement");
  $data_set[3]= array("id" => 4, "type" => "Meublé", "parent" => "hebergement");
  $data_set[4]= array("id" => 5, "type" => "Camping", "parent" => "hebergement");
  $data_set[5]= array("id" => 6, "type" => "Budget Holiday", "parent" => "hebergement");
  $data_set[6]= array("id" => 7, "type" => "Village de vacances", "parent" => "hebergement");
  $data_set[7]= array("id" => 9, "type" => "Evénement", "parent" => "activite");
  $data_set[8]= array("id" => 11, "type" => "Découverte et Divertissement", "parent" => "default");
  $data_set[9]= array("id" => 258, "type" => "Producteur", "parent" => "default");
  $data_set[10]= array("id" => 259, "type" => "Artisan", "parent" => "default");
  $data_set[11]= array("id" => 269, "type" => "Point d'intérêt", "parent" => "default");
  $data_set[12]= array("id" => 8, "type" => "Itinéraire", "parent" => "itinerary");
  $data_set[13]= array("id" => 261, "type" => "Restauration", "parent" => "restauration");
  // Execute the sql statement to insert datas
  wp_insert_rows($data_set,$table_name);
}

function plugin_upgrade() {
  if(get_option('pivot_db_version') < 200){
    pivot_upgrade_200();
  }
}
add_action('plugins_loaded', 'plugin_upgrade');

function pivot_upgrade_200() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'pivot_pages';

    $wpdb->query(
        "ALTER TABLE $table_name
         ADD COLUMN `nbcol` SMALLINT(1) DEFAULT '4'
        ");

    update_option('pivot_db_version', 200);
}

function pivot_deactivation(){
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
  add_action( "load-$pivot_page_submenu", 'pivot_page_screen_option');
  $pivot_filters_submenu = add_submenu_page('pivot-admin', 'Filters', 'Manage filters', 'delete_others_pages', 'pivot-filters', 'pivot_filters_settings');
  add_action( "load-$pivot_filters_submenu", 'pivot_filter_screen_option');
  add_submenu_page('pivot-admin', 'Shortcode', 'Shortcode', 'manage_options', 'pivot-shortcode', 'pivot_build_shortcode_box_html');
}

function init() {
}

function pivot_global_js_vars() {
    echo '<script type="text/javascript">var _pivot_mapbox_token = '.wp_json_encode(get_option('pivot_mapbox')).";</script>\n";
}
add_action( 'wp_head', 'pivot_global_js_vars' );


/**
 * Define main pivot settings
 */
function pivot_settings(){
  register_setting('pivot_settings', 'pivot_uri');
  register_setting('pivot_settings', 'pivot_key');
  register_setting('pivot_settings', 'pivot_mdt');
  register_setting('pivot_settings', 'pivot_bootstrap');
  register_setting('pivot_settings', 'pivot_bitly');
  register_setting('pivot_settings', 'pivot_mapbox');
}

function pivot_options() {
	if(!(current_user_can('editor')) && !(current_user_can('administrator'))){
		wp_die(__( 'You do not have sufficient permissions to access this page.'));
	}
  echo '<h1>'.get_admin_page_title().'</h1>';
  ?>

  <form method="post"  id="pivot-settings-form" accept-charset="UTF-8" action="options.php">
    <?php settings_fields('pivot_settings') ?>
    <div>
      <?php
        if(empty(get_option('pivot_uri'))){
          print _show_admin_notice("Base Uri is required");
        }else{
          if(substr(get_option('pivot_uri'), -1) !== '/'){
            print _show_admin_notice("Base Uri should end with /");
          }
        }
        if(empty(get_option('pivot_key'))){
          print _show_admin_notice("WS_KEY is required");
        }
        ?>
      <div class="form-item form-type-textfield form-item-pivot-uri">
        <label for="edit-pivot-uri"><?php esc_html_e('Base Uri', 'pivot')?> <span class="form-required" title="<?php esc_html_e('This field is required')?>">*</span></label>
        <input type="text" id="edit-pivot-uri" name="pivot_uri" value="<?php echo (get_option('pivot_uri') == ''?"https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/":esc_url(get_option('pivot_uri')))?>" size="60" maxlength="128" class="form-text required">
        <p class="description"><?php esc_html_e('Uri to access Pivot Database.', 'pivot')?> <strong><?php esc_html_e('End / is required !', 'pivot')?></strong></p>
      </div>
      <div class="form-item form-type-textfield form-item-pivot-key">
        <label for="edit-pivot-key">WS_KEY <span class="form-required" title="<?php esc_html_e('This field is required')?>">*</span></label>
        <input type="password" id="edit-pivot-key" name="pivot_key" value="<?php echo get_option('pivot_key')?>" size="60" maxlength="128" class="form-text required">
        <p class="description"><?php _e('Personnal Key to access Pivot webservices, take contact with <a href="http://pivot.tourismewallonie.be/index.php/2015-05-05-10-23-26/flux-de-donnees-3-1" target="_blank">Pivot</a>', 'pivot')?></p>
      </div>

      <span><input id="check-pivot-config" class="button" type="button" value="<?php esc_html_e('Check Pivot config', 'pivot')?>"> </button></span>

      <div id="pivot-response"></div>

      <div class="form-item form-type-textfield form-item-pivot-mdt">
        <label for="edit-pivot-mdt"><?php esc_html_e('Votre maison de tourisme', 'pivot')?></label>
        <select id="edit-pivot-mdt" name="pivot_mdt">
          <option selected disabled hidden><?php esc_html_e('Choisir une maison de tourisme', 'pivot')?></option>
          <?php print _get_list_mdt(); ?>
        </select>
      </div>
      <div class="form-item form-type-textfield form-item-pivot-bootsrap">
        <input type="checkbox" id="edit-pivot-bootsrap" name="pivot_bootstrap" class="form-checkbox" <?php echo (get_option('pivot_bootstrap') == 'on'?'checked':'');?>>
        <label for="edit-pivot-bootsrap"><?php esc_html_e('Include Bootstrap', 'pivot') ?> </label>
        <p class="description"><a href="https://getbootstrap.com/">Bootstrap </a>(<?php esc_html_e('required for default templates', 'pivot');?>)</p>
      </div>
      <div class="form-item form-type-textfield form-item-pivot-bitly">
        <label for="edit-pivot-bitly">Bitly access token <span class="form-required" title="<?php esc_html_e('This field is required')?>">*</span></label>
        <input type="text" id="edit-pivot-bitly" name="pivot_bitly" value="<?php echo get_option('pivot_bitly')?>" size="60" maxlength="128" class="form-text required">
        <p class="description"><?php _e('Personnal Key to access bitly webservices, signin <a href="https://bitly.com/" target="_blank">bitly.com</a> and get you access token', 'pivot')?></p>
      </div>
      <div class="form-item form-type-textfield form-item-pivot-mapbox">
        <label for="edit-pivot-bitly"><?php esc_html_e('MapBox access token', 'pivot')?> <span class="form-required" title="<?php esc_html_e('This field is required')?>">*</span></label>
        <input type="text" id="edit-pivot-mapbox" name="pivot_mapbox" value="<?php echo get_option('pivot_mapbox')?>" size="60" maxlength="128" class="form-text required">
        <p class="description"><?php _e('Personnal Key to access MapBox services, signin <a href="https://account.mapbox.com/" target="_blank">mapbox.com</a> and get you access token', 'pivot')?></p>
      </div>
      <?php submit_button(); ?>
    </div>
  </form>
<?php
  flush_rewrite_rules();
}


/**
 * Construct and send Webservice query based on param and return XML in an Object
 * @param string $type Define type of query (Lodging, ...)
 * @param int $detail Define level of detail @see Pivot documentation to get levels of detail
 * @param array $params
 * @return Object The XML Webservice response load in a php Object
 */
function _pivot_request($type, $detail, $params = NULL, $postfields = NULL){
  if(isset($params['shuffle']) && $params['shuffle'] == TRUE){
    $shuffle = ';shuffle=true'; 
  }else{
    $shuffle = ';shuffle=false';  
  }
  // Get Pivot Base URI
  $pivot_url = esc_url(get_option('pivot_uri'));
  // Get Pivot Personnal Key for Webservices
  $pivot_key = get_option('pivot_key');

  // Construct URL depending on query type
  switch($type) {
    case 'shortcode':
      $pivot_url .= $params['type'].';content='.$detail.$shuffle;
    case 'offer-init-list':
      $pivot_url .= $params['type'].'/paginated;itemsperpage='.$params['items_per_page'].';content='.$detail.$shuffle;
      break;
    case 'offer-pager':
      $pivot_url .= $params['type'].'/paginated'.$params['token'].';content='.$detail.$shuffle;
      break;
    case 'offer-search':
      $pivot_url .= $params['type'].'/paginated;itemsperpage='.$params['items_per_page'].';content='.$detail.$shuffle;
      break;  
    case 'offer-details':
      $pivot_url .= $params['type'].'/'.$params['offer_code'].';content='.$detail;
      break;
    case 'thesaurus':
      $pivot_url .= 'thesaurus/urn/'.$params['urn_name'].';fmt=xml';
      break;
  }

  // Define Headers with Pivot Personnal Key and set format as XML
  $headers = array(
    'WS_KEY: '.$pivot_key,
    'Content-type: application/xml',
    'Accept: application/xml');

  $request = curl_init();
  if ($request){
    curl_setopt($request, CURLOPT_URL, $pivot_url);
    curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 160);
    curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    if($postfields != NULL){
      curl_setopt($request, CURLOPT_POST, 1);  
      curl_setopt($request, CURLOPT_POSTFIELDS, $postfields);
    }

    $response = curl_exec($request);

    if (curl_errno($request)) {
      echo 'Error:' . curl_error($request);
    }

    curl_close($request);
    /* Check if the response is well an XML file.
     * Could be an error like "[CCM006] Results not found (token and/or page number are incorrect)"
     */
    if(strpos($response, '<?xml') !== FALSE){
      // Load XML response in an Object
      $xml_object = simplexml_load_string($response); 

      return $xml_object;
    }else{
      print _show_warning($response, 'warning');

      global $wp_query;
      $wp_query->set_404();
      status_header( 404 );
      get_template_part( 404 );
      exit();
    }
  }
}

/**
 * Construction of the xml query with query id and filters
 * @param string $query_id
 * @param array $field_params
 * @return xml file
 */
function _xml_query_construction($query_id = NULL, $field_params = NULL){
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
  if(isset($field_params['sortField']) && isset($field_params['sortMode'])){
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
  
  if(isset($field_params['criterafield']) && $field_params['criterafield'] == TRUE){
    if(isset($field_params['filters'])){
      foreach ($field_params['filters'] as $filter){
        $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
        $criteriaGroupElement->appendChild($criteriaFieldElement);
      }
    }
  }
  
  if(isset($field_params['radius'])){
    $criteriaOrthodromicElement = $domDocument->createElement('CriteriaOrthodromic');
    $idInsValueElement = $domDocument->createElement('idIns', $field_params['idIns']);
    $criteriaOrthodromicElement->appendChild($idInsValueElement);
    $radiusValueElement = $domDocument->createElement('radius', $field_params['radius']);
    $criteriaOrthodromicElement->appendChild($radiusValueElement);
    
    $criteriaGroupElement->appendChild($criteriaOrthodromicElement);
  }

  if(isset($query_id)){
    $criteriaQueryElement = $domDocument->createElement('CriteriaQuery');
    $queryValueElement = $domDocument->createElement('value', $query_id);
    $criteriaQueryElement->appendChild($queryValueElement);
    $criteriaGroupElement->appendChild($criteriaQueryElement);
    
    // For event check only those where "date fin publication" is between now and +6month
    if(isset($field_params['page_type']) && $field_params['page_type'] == 'activite'){
      $field_params['filters']['datefinmax']['name'] = 'urn:fld:datefinvalid';
      $field_params['filters']['datefinmax']['operator'] = 'lesserequal';
      $field_params['filters']['datefinmax']['searched_value'][] = date("d/m/Y", strtotime('+6 month'));
      $field_params['filters']['datefinmin']['name'] = 'urn:fld:datefinvalid';
      $field_params['filters']['datefinmin']['operator'] = 'greaterequal';
      $field_params['filters']['datefinmin']['searched_value'][] = date("d/m/Y", strtotime('now'));
    }
    
    if(isset($field_params['filters'])){
      foreach ($field_params['filters'] as $filter){
        $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
        $criteriaGroupElement->appendChild($criteriaFieldElement);
      }
    }
  }
  
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
  if(isset($filter['searched_value'])){
    foreach($filter['searched_value'] as $filter_value){
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
function pivot_lodging_page($page_id) {
  $field_params = array();
  
  // Get current page details
  $pivot_page = pivot_get_page_path($_SESSION['pivot'][$page_id]['path']);
  if($pivot_page){
    $field_params['page_type'] = $pivot_page->type;

    // Define how many offers per page
    $offers_per_page = _define_nb_offers_per_page($pivot_page->nbcol);
  }
  
  // Check if there if a sort is defined
  if(isset($pivot_page->sortMode) && $pivot_page->sortMode != NULL && $pivot_page->sortMode != ''){
    $field_params['sortField'] = $pivot_page->sortField;
    $field_params['sortMode'] = $pivot_page->sortMode;
  }
  
  // Check if there is at least ONE active filter
  if(isset($_SESSION['pivot']['filters'][$page_id]) && count($_SESSION['pivot']['filters'][$page_id]) > 0){
    foreach($_SESSION['pivot']['filters'][$page_id] as $key => $value){
      // Get details of filter based on his ID
      $filter = pivot_get_filter($key);
      
      $field_params = _construct_filters_array($field_params, $filter, $key, $page_id);
      
      // Reset var
      $parent_urn = '';
    }
  }

  $xml_query = _xml_query_construction($_SESSION['pivot'][$page_id]['query'], $field_params);

  $offers = pivot_construct_output('offer-search', $offers_per_page, $xml_query, $page_id);

  return $offers;
}

/**
 * 
 * @param string $case Define request case
 * @param int $offers_per_page Number of offers per page to display
 * @param Object $xml_query XML file with request to Pivot (filter on specific fields)
 * @return string part of HTML to display
 */
function pivot_construct_output($case, $offers_per_page, $xml_query = NULL, $page_id = NULL, $details = 2){
  // Get current page number (start with 0)
  if(($pos = strpos($_SERVER['REQUEST_URI'], "paged=")) !== FALSE){ 
    $page_number = substr($_SERVER['REQUEST_URI'], $pos+6); 
    $current_page =  (int) filter_var($page_number, FILTER_SANITIZE_NUMBER_INT);
  }else{
    $current_page = 0;
  }
  // Define query type
  $params['type'] = 'query';
//  $params['shuffle'] = TRUE;
  
  // Check current page.
  // If 0 we need to define params to get all offers (depending on filters)
  if($current_page == 0){
    // Define number of offers per page
    $params['items_per_page'] = $offers_per_page;
    // Define content details we want to receive from Pivot
    $params['content_details'] = ';content=2';
    $page = pivot_get_page($page_id);
    if(isset($page->sortMode) && $page->sortMode == 'shuffle'){
      $params['shuffle'] = TRUE;
    }
    // Get offers
    $xml_object = _pivot_request($case, $details, $params, $xml_query);
    // Store number of offers
    $_SESSION['pivot'][$page_id]['nb_offres'] = str_replace(',', '', $xml_object->attributes()->count->__toString());
    // Store the token to get next x items
    $_SESSION['pivot'][$page_id]['token'] = $xml_object->attributes()->token->__toString();

    $offres = $xml_object->offre;
  }else{
    // Get token + current page (set +1 to current page as it start with 0 but with 1 in Pivot)
    $params['token'] = '/'.$_SESSION['pivot'][$page_id]['token'].'/'.$current_page;

    // Get offers
    $xml_object = _pivot_request('offer-pager', $details, $params);
    $offres = $xml_object->offre;
  }
  
  return $offres;
}