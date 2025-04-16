<?php
if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Pivot_Pages_List extends WP_List_Table {

  /** Class constructor */
  public function __construct() {

    parent::__construct([
      'singular' => __('Page'), //singular name of the listed records
      'plural' => __('Pages'), //plural name of the listed records
      'ajax' => false //does this table support ajax?
    ]);
  }

  /**
   * Retrieve pages data from the database
   * @param int $per_page
   * @param int $page_number
   * @return mixed
   */
  public static function get_pages($per_page = 20, $page_number = 1, $user_search_key = ' ') {

    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}pivot_pages";
    if ($user_search_key != ' ') {
      $sql .= ' WHERE title LIKE "%%%s%%"';
    }

    if (!empty($_REQUEST['orderby'])) {
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
    }

    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

    $result = $wpdb->get_results($wpdb->prepare($sql, $user_search_key), 'ARRAY_A');

    return $result;
  }

  /**
   * Delete a page record.
   *
   * @param int $id page ID
   */
  public static function delete_page($id) {
    global $wpdb;

    $wpdb->delete(
      "{$wpdb->prefix}pivot_pages",
      ['id' => $id],
      ['%d']
    );
  }

  /**
   * Returns the count of records in the database.
   *
   * @return null|string
   */
  public static function record_count() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}pivot_pages";

    return $wpdb->get_var($sql);
  }

  /** Text displayed when no page data is available */
  public function no_items() {
    _e('No page avaliable.', 'sp');
  }

  /**
   * Render a column when no column specific method exist.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default($item, $column_name) {
    switch ($column_name) {
      case 'query':
        return $item[$column_name];
      case 'type':
        return $item[$column_name];
      case 'nbcol':
        return $item[$column_name];
      case 'path':
        return '<a target="_blank" href="' . get_bloginfo('wpurl') . '/' . $item[$column_name] . '">' . $item[$column_name] . '</a>';
      case 'title':
        return stripslashes($item[$column_name]);
      case 'map':
        return ($item[$column_name] == 1) ? '&#10004;' : '&#10008;';
      case 'sortMode':
        if ($item[$column_name] != '') {
          $r = $item[$column_name];
          if ($item['sortField'] != '') {
            $r .= ' on ' . $item['sortField'];
          }
        } else {
          $r = '-';
        }
        return $r;
      case 'filters':
        $val = '<input type="button" class="button-secondary" value="' . esc_html__('View filter(s)', 'pivot') . '" onclick="window.location=\'?page=pivot-filters&amp;page_id=' . $item['id'] . '\'"/>';
        $val .= '<input type="button" class="button-secondary" value="' . esc_html__('Add a filter', 'pivot') . '" onclick="window.location=\'?page=pivot-filters&amp;page_id=' . $item['id'] . '&amp;edit=true\'" />';
        return $val;
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }

  /**
   * Render the bulk edit checkbox
   *
   * @param array $item
   *
   * @return string
   */
  function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
    );
  }

  /**
   * Method for name column
   *
   * @param array $item an array of DB data
   *
   * @return string
   */
  function column_query($item) {

    $delete_nonce = wp_create_nonce('pivot_delete_pages');

    $title = '<strong>' . $item['query'] . '</strong>';

    $actions['edit'] = sprintf('<a href="?page=pivot-pages&amp;id=%d&amp;edit=true">' . esc_html__('Edit') . '</a>', absint($item['id']));
    $actions['delete'] = sprintf('<a href="?page=pivot-pages&amp;delete=%d">' . esc_html__('Delete') . '</a>', absint($item['id']));
    $actions['clear-pivot-cache'] = sprintf('<a href="?page=pivot-pages&amp;clear-pivot-cache=%d">' . esc_html__('Clear Pivot cache') . '</a>', absint($item['id']));

    return $title . $this->row_actions($actions);
  }

  /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns() {
    $columns = [
      'cb' => '<input type="checkbox" />',
      'query' => __('Query', 'pivot'),
      'type' => __('Type', 'pivot'),
      'nbcol' => __('Nb Col', 'pivot'),
      'path' => __('Path', 'pivot'),
      'title' => __('Page Title', 'pivot'),
      'map' => __('Map ?', 'pivot'),
      'sortMode' => __('Sorting', 'pivot'),
      'filters' => __('Filters', 'pivot'),
    ];

    return $columns;
  }

  /**
   * Columns to make sortable.
   *
   * @return array
   */
  public function get_sortable_columns() {
    $sortable_columns = array(
      'query' => array('query', true),
      'type' => array('type', false),
      'nbcol' => array('nbcol', false),
      'path' => array('path', false),
      'title' => array('title', false),
      'map' => array('map', false),
      'sortMode' => array('sortMode', false)
    );

    return $sortable_columns;
  }

  /**
   * Returns an associative array containing the bulk action
   *
   * @return array
   */
  public function get_bulk_actions() {
    $actions = [
      'bulk-delete' => __('Delete')
    ];

    return $actions;
  }

  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items() {
    // check if a search was performed.
    $user_search_key = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = array($columns, $hidden, $sortable);

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page = $this->get_items_per_page('pages_per_page', 5);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page' => $per_page //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_pages($per_page, $current_page, $user_search_key);
  }

  public function process_bulk_action() {

    //Detect when a bulk action is being triggered...
    if ('delete' === $this->current_action()) {

      // In our file that handles the request, verify the nonce.
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if (!wp_verify_nonce($nonce, 'pivot_delete_pages')) {
        die('Go get a life script kiddies');
      } else {
        self::delete_page(absint($_GET['pages']));

        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
        // add_query_arg() return the current url
        wp_redirect(esc_url_raw(add_query_arg()));
        exit;
      }
    }

    // If the delete bulk action is triggered
    if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
    ) {

      $delete_ids = esc_sql($_POST['bulk-delete']);

      // loop over the array of record IDs and delete them
      foreach ($delete_ids as $id) {
        self::delete_page($id);
      }

      // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
      // add_query_arg() return the current url
      wp_redirect(esc_url_raw(add_query_arg()));
      exit;
    }
  }
}

/**
 * Get all the data from table wp_pivot
 * @global Object $wpdb
 * @return Object
 */
function pivot_get_pages() {
  global $wpdb;
  $query = "SELECT * FROM {$wpdb->prefix}pivot_pages ORDER BY id ASC";
  $pages = $wpdb->get_results($query);

  return $pages;
}

/**
 * Get a specific row from table wp_pivot based on path
 * @global Object $wpdb
 * @param string $path
 * @return Object
 */
function pivot_get_page_path($path) {
  global $wpdb;
  $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pivot_pages WHERE path= %s", $path);
  $pivot_page = $wpdb->get_row($query);
  if (isset($pivot_page)) {
    // Unscape String
    foreach ($pivot_page as &$field) {
      if (is_string($field))
        $field = stripslashes($field);
    }
  }

  return $pivot_page;
}

/**
 * Get a specific row from table wp_pivot based on path
 * @global Object $wpdb
 * @param string $path
 * @return Object
 */
function pivot_get_pages_path() {
  global $wpdb;
  $query = "SELECT * FROM {$wpdb->prefix}pivot_pages";
  $pivot_pages_path = $wpdb->get_results($query, ARRAY_A);

  // Unscape String
  foreach ($pivot_pages_path as &$pivot_page_path) {
    foreach ($pivot_page_path as &$field) {
      if (is_string($field))
        $field = stripslashes($field);
    }
  }

  return $pivot_pages_path;
}

/**
 * Get a specific row from table wp_pivot
 * @global Object $wpdb
 * @param string $id page id
 * @return string
 */
function pivot_get_page($id) {
  global $wpdb;
  $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pivot_pages WHERE id= %d", $id);
  $pivot_page = $wpdb->get_row($query);

  // Unscape String
  foreach ($pivot_page as &$field) {
    if (is_string($field))
      $field = stripslashes($field);
  }

  return $pivot_page;
}

function pivot_meta_box() {
  global $edit_page;
  ?>
  <div class="form-item form-type-textfield form-item-pivot-query">
      <label for="edit-pivot-query"><strong><?php esc_html_e('Query', 'pivot') ?></strong></label>
      <input type="text" id="edit-pivot-query" name="query" value="<?php if (isset($edit_page)) echo $edit_page->query; ?>" size="60" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Pivot predefined query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-type">
      <label for="edit-pivot-type"><strong><?php esc_html_e('Type', 'pivot') ?></strong> </label>
      <select id="edit-pivot-type" name="type">
          <?php print _get_offer_types($edit_page); ?>
      </select>
      <p class="description"><?php esc_html_e('Type of query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-path">
      <label for="edit-pivot-path"><strong><?php esc_html_e('Path', 'pivot') ?></strong> </label>
      <input type="text" id="edit-pivot-path" name="path" value="<?php if (isset($edit_page)) echo $edit_page->path; ?>" size="60" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Path to access results', 'pivot') ?></p>
  </div>

  <br>
  <h2 class="hndle"><b><span><?php esc_html_e('Visuel') ?></span></b></h2>
  <br>

  <div class="form-item form-item-pivot-nb-col">
      <label for="edit-pivot-nb-col"><strong><?php esc_html_e('Define number of offers per line', 'pivot') ?></strong> </label>
      <input type="number" id="edit-pivot-nb-col" name="nbcol" min="2" max="6" value="<?php echo (isset($edit_page)) ? $edit_page->nbcol : '4'; ?>">
      <p class="description"><?php esc_html_e('It will be 4 by default', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-map">
      <input type="checkbox" id="edit-pivot-map" name="map" class="form-checkbox" <?php echo (isset($edit_page) && $edit_page->map == 1 ? 'checked' : ''); ?>>
      <label for="edit-pivot-map"><strong><?php esc_html_e('Show map', 'pivot') ?></strong> </label>
      <img class="pivot-picto" src="<?php print get_option('pivot_uri'); ?>img/urn:typ:269;modifier=orig;h=20"/>
      <p class="description"><?php esc_html_e('Define if you want to show a map on this page or not', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-title">
      <label for="edit-pivot-title"><strong><?php esc_html_e('Title', 'pivot') ?></strong> </label>
      <input type="text" id="edit-pivot-title" name="title" value="<?php if (isset($edit_page)) echo $edit_page->title; ?>" size="60" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Page title', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-description">
      <label for="edit-pivot-description"><strong><?php esc_html_e('Description') ?></strong></label>
      <?php $content = ''; ?>
      <?php if (isset($edit_page)): ?>
        <?php $content = $edit_page->description; ?>
      <?php endif; ?>
      <?php wp_editor($content, 'edit-pivot-description', array('textarea_rows' => 10, 'media_buttons' => 0, 'tinymce' => 1, 'quicktags' => array('buttons' => 'strong,em,ul,ol,li,close'))); ?>
      <p class="description"><?php esc_html_e('Small text to display above the page under the title', 'pivot') ?></p>
  </div><br>
  <div class="form-item form-type-textfield form-item-pivot-shortcode">
      <label for="shortcode"><strong><?php esc_html_e('Shortcode', 'pivot') ?></strong> </label>
      <input type="text" id="shortcode" name="shortcode" value="<?php if (isset($edit_page)) echo esc_attr($edit_page->shortcode); ?>" size="60" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('To insert custom presentation with a shortcode, from Elementor for example', 'pivot') ?></p>
      <p class="description"><?php esc_html_e('Need to be implemented in your templates to be usable (not active by default) !', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-file form-item-pivot-image">
      <label><strong><?php esc_html_e('Choose an image', 'pivot'); ?></strong></label>
      <input type="file" name="my_image_upload" id="my_image_upload"  multiple="false" />
      <?php wp_nonce_field('my_image_upload', 'my_image_upload_nonce'); ?>
      <p class="description"><?php esc_html_e('This image will be displayed full width between menu and page title', 'pivot') ?></p>
      <p class="description"><b><?php esc_html_e('Perfect format would be 1920x400 px', 'pivot') ?></b></p>
      <p><label><strong><?php esc_html_e('Current image or link', 'pivot'); ?></strong></label></p>
      <input type="url" id="imageUrl" name="imageUrl" value="<?php if (isset($edit_page)) echo $edit_page->image; ?>">
      <a class="imageUrl" target="_blank" href="<?php if (isset($edit_page)) echo $edit_page->image; ?>">
          <img width="300px" src="<?php if (isset($edit_page)) echo $edit_page->image; ?>"/>
      </a>
      <button class="ed_button button button-small" type='reset' id='reset_img'/><?php _e('Remove'); ?> image</button>
  </div>

  <br>
  <h2 class="hndle"><b><span><?php esc_html_e('Query sorting', 'pivot') ?></span></b></h2>
  <br>

  <div class="form-item form-type-textfield form-item-pivot-sortMode">
      <label for="edit-pivot-sortMode"><strong><?php esc_html_e('Sort mode', 'pivot') ?></strong> </label>
      <select id="edit-pivot-sortMode" name="sortMode">
          <option selected value=""><?php esc_html_e('Choose an order', 'pivot') ?></option>
          <option <?php if (isset($edit_page) && $edit_page->sortMode == 'ASC') echo 'selected="selected"'; ?>value="ASC"><?php esc_html_e('Ascending', 'pivot') ?></option>
          <option <?php if (isset($edit_page) && $edit_page->sortMode == 'DESC') echo 'selected="selected"'; ?>value="DESC"><?php esc_html_e('Descending', 'pivot') ?></option>
          <option <?php if (isset($edit_page) && $edit_page->sortMode == 'shuffle') echo 'selected="selected"'; ?>value="shuffle"><?php esc_html_e('Shuffle', 'pivot') ?></option>
      </select>
      <p class="description"><?php esc_html_e('Choose the sort mode for the query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-sortField">
      <label for="edit-pivot-sortField"><strong><?php esc_html_e('Sort Field', 'pivot') ?></strong> </label>
      <input type="text" id="edit-pivot-sortField" name="sortField" value="<?php if (isset($edit_page)) echo $edit_page->sortField; ?>" size="60" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Define the field on which the sort mode will apply', 'pivot') ?></p>
  </div>
  <?php
}

/**
 * Add "Wordpress" options to the page
 */
function pivot_page_screen_option() {

  $option = 'per_page';
  $args = [
    'label' => 'Pages',
    'default' => 10,
    'option' => 'pages_per_page'
  ];

  add_screen_option($option, $args);

  $table = new Pivot_Pages_List();
}

/**
 * Define plugin options edit & delete VS add
 */
function pivot_pages_settings() {
  // Manipulate data of the custom table
  pivot_action();
  if (empty($_GET['edit'])) {
    // Display the data into the Dashboard
    ?>
    <div class="wrap">
        <h2><?php _e("Pivot Plugin Pages", "pivot"); ?>
            <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=pivot-pages&edit=true" class="page-title-action"><?php _e('Add New'); ?></a>
        </h2>

        <div id="poststuff">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        $table = new Pivot_Pages_List();
                        $table->prepare_items();
                        $table->search_box(__('Search'), 'search_id');
                        $table->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
    </div>
    <?php
  } else {
    // Display a form to add or update the data
    pivot_add_page();
  }
}

/**
 * Define plugin actions
 * action of a CRUD
 * @global Object $wpdb
 */
function pivot_action() {
  global $wpdb;

  // Delete the transient of the specified page
  if (isset($_GET['clear-pivot-cache'])) {
    $page_id = absint($_GET['clear-pivot-cache']);
    $key = 'pivot_page_token_' . $page_id;
    $response = delete_transient($key);
    if ($response === true) {
      print _show_admin_notice("Cache Pivot cleared for the page : " . $page_id, "success");
    } else {
      print _show_admin_notice("No Pivot cache for the page : " . $page_id);
    }
  }
  // Delete the transient of the specified page
  if (isset($_GET['clear-pivot-offer-cache'])) {
    $offer_id = $_GET['clear-pivot-offer-cache'];
    // Get all transients concerning this offer
    $keys = _get_pivot_transients($offer_id);
    foreach ($keys as $key) {
      // remove part of option name to get correct transient name
      $key = str_replace('_transient_', '', $key->option_name);
      $response = delete_transient($key);
    }
    if ($response === true) {
      print _show_admin_notice("Cache Pivot cleared for the offer : " . $offer_id, "success");
    } else {
      print _show_admin_notice("No Pivot cache for the offer : " . $offer_id);
    }
  }

  // Delete the data if the variable "delete" is set
  if (isset($_GET['delete'])) {
    $_GET['delete'] = absint($_GET['delete']);
    // IF WPML is active unregister title from translatable string
    if (is_plugin_active('wpml-string-translation/plugin.php')) {
      $page_details = pivot_get_page($_GET['delete']);
      icl_unregister_string('pivot', 'title-for-' . $page_details->query);
      icl_unregister_string('pivot', 'description-for-' . $page_details->query);
    }
    // First delete dependencies (filters linked to this page)
    $wpdb->delete($wpdb->prefix . 'pivot_filter', array('page_id' => $_GET['delete']), array('%d'));
    // Delete the page
    $wpdb->delete($wpdb->prefix . 'pivot_pages', array('id' => $_GET['delete']), array('%d'));
  }

  // Process the changes in the custom table
  if (isset($_POST['pivot_add_page']) && $_POST['type'] != '' && $_POST['query'] != '' && $_POST['path'] != '' && $_POST['title'] != '') {
    // Add new row in the custom table
    $type = $_POST['type'];
    $query = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['query']);
    $nbcol = $_POST['nbcol'];
    $path = $_POST['path'];
    $title = $_POST['title'];
    $description = $_POST['edit-pivot-description'];
    $shortcode = isset($_POST['shortcode']) ? wp_unslash($_POST['shortcode']) : '';

    // Check that the nonce is valid, and the user can edit this post.
    if (isset($_POST['my_image_upload_nonce']) && wp_verify_nonce($_POST['my_image_upload_nonce'], 'my_image_upload')) {
      // Allowed image types
      $allowed_image_types = array('image/jpeg', 'image/png');
      // Check if there's an image
      if (isset($_FILES['my_image_upload']['size']) && $_FILES['my_image_upload']['size'] > 0) {
        // Check conditions
        if (in_array($_FILES['my_image_upload']['type'], $allowed_image_types)) {
          // These files need to be included as dependencies when on the front end.
          require_once(ABSPATH . 'wp-admin/includes/image.php');
          require_once(ABSPATH . 'wp-admin/includes/file.php');
          require_once(ABSPATH . 'wp-admin/includes/media.php');

          // Let WordPress handle the upload.
          // Remember, 'my_image_upload' is the name of our file input in our form above.
          $attachment_id = media_handle_upload('my_image_upload', 0);
          $image_url = wp_get_attachment_url($attachment_id);
          if (is_wp_error($attachment_id)) {
            print _show_admin_notice('There was an error uploading the image.');
          }
        } else {
          print _show_admin_notice('image/jpeg and image/png only allowed');
        }
      }
    } else {
      print _show_admin_notice('There was an error uploading the image.');
    }

    $map = isset($_POST['map']) ? 1 : 0;
    $sortMode = $_POST['sortMode'];
    $sortField = $_POST['sortField'];

    // Check if path already exist in wordpress or not (to avoid duplicate and conflict)
    if (!$pivot_page = get_page_by_path($path)) {
      if (empty($_POST['page_id'])) {
        $inserted = $wpdb->insert(
          $wpdb->prefix . 'pivot_pages',
          array(
            'type' => $type,
            'query' => $query,
            'path' => $path,
            'title' => $title,
            'map' => $map,
            'sortMode' => $sortMode,
            'sortField' => $sortField,
            'nbcol' => $nbcol,
            'description' => $description,
            'shortcode' => $shortcode,
            'image' => (isset($image_url) ? $image_url : $_POST['imageUrl'])
          ),
          array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s')
        );
      } else {
        // Update the data
        $inserted = $wpdb->update(
          $wpdb->prefix . 'pivot_pages',
          array(
            'type' => $type,
            'query' => $query,
            'path' => $path,
            'title' => $title,
            'map' => $map,
            'sortMode' => $sortMode,
            'sortField' => $sortField,
            'nbcol' => $nbcol,
            'description' => $description,
            'shortcode' => $shortcode,
            'image' => (isset($image_url) ? $image_url : $_POST['imageUrl'])
          ),
          array('id' => $_POST['page_id']),
          array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s'),
          array('%d')
        );
      }
      // IF WPML is active add title in translatable string
      if (is_plugin_active('wpml-string-translation/plugin.php')) {
        icl_register_string('pivot', 'title-for-' . $query, stripslashes($title), false, substr(get_locale(), 0, 2));
        icl_register_string('pivot', 'description-for-' . $query, stripslashes($description), false, substr(get_locale(), 0, 2));
      }
    } else {
      $text = esc_html__('This path already exists', 'pivot') . ': <a href="' . get_permalink($pivot_page->ID) . '">' . get_permalink($pivot_page->ID) . '</a>';
      print _show_admin_notice($text);
    }
    flush_rewrite_rules();
  } else {
    if (isset($_POST['pivot_add_page']) && (!isset($_POST['query']) || $_POST['query'] == '')) {
      $text = esc_html__('Query is required', 'pivot');
      print _show_admin_notice($text);
    }
    if (isset($_POST['pivot_add_page']) && (!isset($_POST['type']) || $_POST['type'] == '')) {
      $text = esc_html__('Type is required', 'pivot');
      print _show_admin_notice($text);
    }
    if (isset($_POST['pivot_add_page']) && (!isset($_POST['path']) || $_POST['path'] == '')) {
      $text = esc_html__('Path is required', 'pivot');
      print _show_admin_notice($text);
    }
    if (isset($_POST['pivot_add_page']) && (!isset($_POST['title']) || $_POST['title'] == '')) {
      $text = esc_html__('Page title is required', 'pivot');
      print _show_admin_notice($text);
    }
  }
}

/**
 * Get global
 * @global type $edit_page
 */
function pivot_add_page() {
  $page_id = 0;
  if (isset($_GET['id']))
    $page_id = $_GET['id'];

  // Get an specific row from the table wp_pivot
  global $edit_page;
  if ($page_id)
    $edit_page = pivot_get_page($page_id);

  // Create meta box
  add_meta_box('pivot-meta', 'Pivot Info', 'pivot_meta_box', 'pivot', 'normal', 'core');
  ?>

  <!--Display the form to add a new row-->
  <div class="wrap">
      <div id="faq-wrapper">
          <form method="post" enctype="multipart/form-data" action="?page=pivot-pages">
              <h2><?php echo $tf_title = ($page_id == 0) ? $tf_title = esc_attr('Add page', 'pivot') : $tf_title = esc_attr('Edit page', 'pivot'); ?></h2>
              <div id="poststuff" class="metabox-holder">
                  <?php do_meta_boxes('pivot', 'normal', 'low'); ?>
              </div>
              <input type="hidden" name="page_id" value="<?php echo $page_id ?>" />
              <input type="submit" value="<?php echo $tf_title; ?>" name="pivot_add_page" id="pivot_add_page" class="button-secondary">
          </form>
      </div>
  </div>
  <?php
}
