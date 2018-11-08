<?php
/*
Plugin Name: Pivot
Description: Un plugin pour l'affichage et la recherche (via webservice) des offres disponibles dans la DB Pivot
Version: 0.1
Author: Maxime Degembe
License: GPL2
*/

// Define global variable
$pivot_offer_type = array(
  array('id' => 1,
        'type' => 'Hôtel',
        'parent' => 'hebergement'
  ),
  array('id' => 2,
        'type' => '	Gîte',
        'parent' => 'hebergement'
  ),
  array('id' => 3,
        'type' => '	Chambre d\'hôtes',
        'parent' => 'hebergement'
  ),
  array('id' => 9,
        'type' => 'Evénement',
        'parent' => 'activite'
  ),
);

require_once(plugin_dir_path( __FILE__ ). '/pivot-filters.php');
require_once(plugin_dir_path( __FILE__ ). '/pivot-pages.php');
require_once(plugin_dir_path( __FILE__ ). '/pivot-offer-type.php');
//require_once(plugin_dir_path( __FILE__ ). '/bitly.php');

//$bitly_params = array();
//$bitly_params['access_token'] = get_option('pivot_bitly');
//$bitly_params['domain'] = 'bit.ly';

register_activation_hook( __FILE__, 'pivot_install_plugin_create_table' );
add_action('init', 'init');
add_action('admin_menu', 'pivot_menu');
add_action('admin_init', 'pivot_settings');

function pivot_install_plugin_create_table() {
  // Create an instance of the database class
  global $wpdb;

  // Set the custom table name with the wp prefix "pivot"
  $table_name = $wpdb->prefix . "pivot";
  
  // Execute the sql statement to create or update the custom table
  $sql = "CREATE TABLE ".$table_name." (
            id int(9) NOT NULL AUTO_INCREMENT,
            type varchar(100) NOT NULL,
            query varchar(100) NOT NULL,
            path varchar(100) NOT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
  
function pivot_menu() {
  add_menu_page('Pivot administration', 'Pivot', 'manage_options', 'pivot-admin', 'pivot_options');
  add_submenu_page('pivot-admin', 'Pivot administration', 'Pivot', 'manage_options', 'pivot-admin');
  add_submenu_page('pivot-admin', 'Offer types', 'Manage offer type', 'manage_options', 'pivot-offer-types', 'pivot_offer_type_settings');
  add_submenu_page('pivot-admin', 'Pages', 'Manage pages', 'manage_options', 'pivot-pages', 'pivot_pages_settings');
  add_submenu_page('pivot-admin', 'Filters', 'Manage filters', 'manage_options', 'pivot-filters', 'pivot_filters_settings');
}


function init() {
}

function pivot_settings(){
  register_setting('pivot_settings', 'pivot_uri');
  register_setting('pivot_settings', 'pivot_key');
  register_setting('pivot_settings', 'pivot_mdt');
  register_setting('pivot_settings', 'pivot_bootstrap');
//  register_setting('pivot_settings', 'pivot_bitly');
}

function pivot_options() {
	if (!current_user_can('manage_options')) {
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
          }//aa0f76f6-1994-49a0-9802-40109a5eb9b4
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
        <input type="text" id="edit-pivot-key" name="pivot_key" value="<?php echo get_option('pivot_key')?>" size="60" maxlength="128" class="form-text required">
        <p class="description"><?php _e('Personnal Key to access Pivot webservices, take contact with <a href="http://pivot.tourismewallonie.be/index.php/2015-05-05-10-23-26/flux-de-donnees-3-1" target="_blank">Pivot</a>', 'pivot')?></p>
      </div>
        
      <span><button id="check-pivot-config" type="button"><?php esc_html_e('Check Pivot config', 'pivot')?> </button></span>
      
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
<!--      <div class="form-item form-type-textfield form-item-pivot-bitly">
        <label for="edit-pivot-bitly">Bitly access token <span class="form-required" title="<?php // esc_html_e('This field is required')?>">*</span></label>
        <input type="text" id="edit-pivot-bitly" name="pivot_bitly" value="<?php // echo get_option('pivot_bitly')?>" size="60" maxlength="128" class="form-text required">
        <p class="description"><?php // _e('Personnal Key to access bitly webservices, signin <a href="https://bitly.com/" target="_blank">bitly.com</a> and get you access token', 'pivot')?></p>
      </div>  -->
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
    $shuffle = '';
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
      //$pivot_url .= $params['type'].'/'.$_SESSION['pivot']['query'] . '/paginated;itemsperpage='.$params['items_per_page'].';content=' . $detail;
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
      global $wp_query;
      $wp_query->set_404();
      status_header( 404 );
      get_template_part( 404 ); exit();
    }
  }
}

function _xml_query_construction($query_id, $field_params = NULL){
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

  $criteriaQueryElement = $domDocument->createElement('CriteriaQuery');
  $queryValueElement = $domDocument->createElement('value', $query_id);

  $criteriaQueryElement->appendChild($queryValueElement);
  $criteriaGroupElement->appendChild($criteriaQueryElement);

  if(isset($field_params['filters'])){
    foreach ($field_params['filters'] as $filter){
      $criteriaFieldElement = _create_dom_criteria_field_element($domDocument, $filter);
      $criteriaGroupElement->appendChild($criteriaFieldElement);
    }
  }

  $queryElement->appendChild($criteriaGroupElement);
  $domDocument->appendChild($queryElement);
  $domDocument->save('/var/www/html/wordpress/test/test1.xml');

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
 * 
 * @param string $offre_id Offer ID
 * @return array
 */
function pivot_lodging_detail_page($offre_id){
  $params['offer_code'] = $offre_id;
  $params['type'] = 'offer';
  $xml_object = _pivot_request('offer-details', 3, $params);
  $offre = $xml_object->offre;
  
  return theme('pivot_lodging_detail_page',array('offre' => $offre));
}