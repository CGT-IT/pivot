<?php
if (!class_exists('WP_List_Table')) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Pivot_Filters_List extends WP_List_Table {

  /** Class constructor */
  public function __construct() {

    parent::__construct([
      'singular' => __('Filter'), //singular name of the listed records
      'plural' => __('Filters'), //plural name of the listed records
      'ajax' => false //does this table support ajax?
    ]);
  }

  /**
   * Retrieve filters data from the database
   * @param int $per_page
   * @param int $page_number
   * @return mixed
   */
  public static function get_filters($per_page = 20, $page_number = 1, $user_search_key = ' ', $page_id = NULL) {

    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}pivot_filter";
    if ($page_id != NULL) {
      $sql .= ' WHERE page_id = %d';
    }
    if ($page_id != NULL && $user_search_key != ' ') {
      $sql .= ' AND filter_title LIKE "%%%s%%"';
    }
    if ($page_id == NULL && $user_search_key != ' ') {
      $sql .= ' WHERE filter_title LIKE "%%%s%%"';
    }

    if (!empty($_REQUEST['orderby'])) {
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
    }

    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

    if ($page_id != NULL && $user_search_key != ' ') {
      return $result = $wpdb->get_results($wpdb->prepare($sql, $page_id, $user_search_key), 'ARRAY_A');
    } else {
      if ($page_id != NULL && $user_search_key == ' ') {
        return $result = $wpdb->get_results($wpdb->prepare($sql, $page_id), 'ARRAY_A');
      } else {
        return $result = $wpdb->get_results($wpdb->prepare($sql, $user_search_key), 'ARRAY_A');
      }
    }
  }

  /**
   * Delete a filter record.
   * @param int $id filter ID
   */
  public static function delete_filter($id) {
    global $wpdb;

    // IF WPML is active unregister title from translatable string
    if (is_plugin_active('wpml-string-translation/plugin.php')) {
      $filter_details = pivot_get_filter($id);
      icl_unregister_string('pivot', 'filter-title-' . $filter_details->urn . '-' . $filter_details->page_id);
    }

    $wpdb->delete($wpdb->prefix . 'pivot_filter', array('id' => $id), array('%d'));
  }

  /**
   * Returns the count of records in the database.
   * @return null|string
   */
  public static function record_count() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}pivot_filter";

    return $wpdb->get_var($sql);
  }

  /** Text displayed when no page data is available */
  public function no_items() {
    _e('No filter avaliable.', 'pivot');
  }

  /**
   * Render a column when no column specific method exist.
   * @param array $item
   * @param string $column_name
   * @return mixed
   */
  public function column_default($item, $column_name) {
    switch ($column_name) {
      case 'filter_title':
        return $item[$column_name];
      case 'filter_title_nl':
        return $item[$column_name];
      case 'filter_title_en':
        return $item[$column_name];
      case 'filter_title_de':
        return $item[$column_name];
      case 'urn':
        return $item[$column_name];
      case 'operator':
        return $item[$column_name];
      case 'type':
        return $item[$column_name];
      case 'page_id':
        $page = pivot_get_page($item[$column_name]);
        return $page->query;
      case 'filter_group':
        return $item[$column_name];
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
  function column_filter_name($item) {

    $delete_nonce = wp_create_nonce('pivot_delete_filters');

    $title = '<strong>' . $item['filter_name'] . '</strong>';

    $actions['edit'] = sprintf('<a href="?page=pivot-filters&id=%d&page_id=%d&edit=true">' . esc_html__('Edit') . '</a>', absint($item['id']), absint($item['page_id']));
    $actions['delete'] = sprintf('<a href="?page=%s&page_id=%d&action=%s&delete=%d&_wpnonce=%s">' . esc_html__('Delete') . '</a>', $_REQUEST['page'], absint($item['page_id']), 'bulk-delete', absint($item['id']), $delete_nonce);

    return $title . $this->row_actions($actions);
  }

  /**
   * Associative array of columns
   * @return array
   */
  function get_columns() {
    $columns = [
      'cb' => '<input type="checkbox" />',
      'filter_name' => __('Filter name', 'pivot'),
      'filter_title' => __('Filter title', 'pivot'),
      'urn' => __('URN', 'pivot'),
      'operator' => __('Operator', 'pivot'),
      'type' => __('Type', 'pivot'),
      'page_id' => __('Linked to query', 'pivot'),
      'filter_group' => __('In group', 'pivot'),
    ];
    return $columns;
  }

  /**
   * Columns to make sortable.
   * @return array
   */
  public function get_sortable_columns() {
    $sortable_columns = array(
      'filter_name' => array('filter_name', false),
      'filter_title' => array('filter_title', false),
      'urn' => array('urn', false),
      'operator' => array('operator', false),
      'type' => array('type', false),
      'page_id' => array('page_id', false),
      'filter_group' => array('filter_group', false)
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

    $per_page = $this->get_items_per_page('filters_per_page', 10);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page' => $per_page //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_filters($per_page, $current_page, $user_search_key, isset($_GET['page_id']) ? $_GET['page_id'] : null);
  }

  public function process_bulk_action() {
    //Detect when a bulk action is being triggered...
    if ('delete' === $this->current_action()) {
      // In our file that handles the request, verify the nonce.
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if (!wp_verify_nonce($nonce, 'pivot_delete_filters')) {
        die('Go get a life script kiddies');
      } else {
        self::delete_filter(absint($_GET['filters']));
      }
    }

    // If the delete bulk action is triggered
    if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')) {
      $delete_ids = esc_sql($_POST['bulk-delete']);

      // loop over the array of record IDs and delete them
      foreach ($delete_ids as $id) {
        self::delete_filter($id);
      }
    }
  }

  /**
   * Define plugin actions
   * action of a CRUD
   * @global Object $wpdb
   */
  public static function pivot_filters_action() {
    global $wpdb;
    // Delete the data if the variable "delete" is set
    if (isset($_GET['delete'])) {
      self::delete_filter($_GET['delete']);
    }

    // Process the changes in the custom table
    if (isset($_POST['pivot_add_filter']) && isset($_POST['title']) && isset($_POST['urn'])) {
      // Add new row in the custom table
      $urn = $_POST['urn'];
      $urnDoc = _get_urn_documentation_full_spec($urn);
      $type = $urnDoc->spec->type->__toString();
      switch ($type) {
        case 'Boolean':
          $operator = 'exist';
          break;
        case 'Type':
        case 'Value':
          $operator = 'in';
          break;
        default:
          if ($_POST['urn'] == 'urn:fld:idorc') {
            $operator = 'notempty';
          } else {
            $operator = $_POST['operator'];
          }
          break;
      }

      $name = substr(strrchr($urn, ":"), 1);
      $title = $_POST['title'];
      $title_nl = $_POST['title-nl'];
      $title_en = $_POST['title-en'];
      $title_de = $_POST['title-de'];
      $group = $_POST['filter_group'];

      if (empty($_POST['id'])) {
        // If we add the filter to all pages
        if (isset($_POST['allpages'])) {
          $pages = pivot_get_pages();
          foreach ($pages as $page) {
            // Insert data
            $inserted = $wpdb->insert(
              $wpdb->prefix . 'pivot_filter',
              array(
                'page_id' => $page->id,
                'filter_name' => $name,
                'filter_title' => $title,
                'filter_title_nl' => $title_nl,
                'filter_title_en' => $title_en,
                'filter_title_de' => $title_de,
                'urn' => $urn,
                'operator' => $operator,
                'type' => $type,
                'filter_group' => $group
              ),
              array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
          }
        } else {
          // Insert data
          $inserted = $wpdb->insert(
            $wpdb->prefix . 'pivot_filter',
            array(
              'page_id' => $_POST['page_id'],
              'filter_name' => $name,
              'filter_title' => $title,
              'filter_title_nl' => $title_nl,
              'filter_title_en' => $title_en,
              'filter_title_de' => $title_de,
              'urn' => $urn,
              'operator' => $operator,
              'type' => $type,
              'filter_group' => $group
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
          );
        }
      } else {
        // Update data
        $inserted = $wpdb->update(
          $wpdb->prefix . 'pivot_filter',
          array(
            'page_id' => $_POST['page_id'],
            'filter_name' => $name,
            'filter_title' => $title,
            'filter_title_nl' => $title_nl,
            'filter_title_en' => $title_en,
            'filter_title_de' => $title_de,
            'urn' => $urn,
            'operator' => $operator,
            'type' => $type,
            'filter_group' => $group
          ),
          array('id' => $_POST['id']),
          array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
          array('%d')
        );
      }
      // Inform user
      if ($inserted) {
        // IF WPML is active add title in translatable string
        if (is_plugin_active('wpml-string-translation/plugin.php')) {
          icl_register_string('pivot', 'filter-title-' . $urn . '-' . $_POST['page_id'], stripslashes($title), false, substr(get_locale(), 0, 2));
          icl_register_string('pivot', 'filter-group-' . preg_replace("/[^a-zA-Z]+/", "", $group), stripslashes($group), false, substr(get_locale(), 0, 2));
        }
        $message = __('Record was inserted / updated successfully', 'pivot');
        echo _show_admin_notice($message, 'info');
      } else {
        $message = __('Insertion / Update failed', 'pivot');
        echo _show_admin_notice($message);
      }
    }
  }

}

/**
 * Add "Wordpress" options to the page
 */
function pivot_filter_screen_option() {

  $option = 'per_page';
  $args = [
    'label' => __('Filters', 'pivot'),
    'default' => 10,
    'option' => 'filters_per_page'
  ];

  add_screen_option($option, $args);

  $table = new Pivot_Filters_List();
}

/**
 * Get a specific row from table wp_pivot_filter
 * @global Object $wpdb
 * @param string $id page id
 * @return string
 */
function pivot_get_filter($id) {
  global $wpdb;

  $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pivot_filter WHERE id = %d", $id);
  $filter = $wpdb->get_row($query);

  return $filter;
}

/**
 * Get all the data from table wp_pivot_filter
 * @global Object $wpdb
 * @return Object
 */
function pivot_get_filters($page_id = NULL) {
  global $wpdb;
  if (!empty($page_id)) {
    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pivot_filter WHERE page_id = %d ORDER BY filter_group ASC, filter_title ASC", $page_id);
  } else {
    $query = "SELECT * FROM {$wpdb->prefix}pivot_filter ORDER BY filter_title ASC";
  }
  $filters = $wpdb->get_results($query);

  return $filters;
}

function pivot_get_filter_groups($page_id) {
  global $wpdb;

  $query = $wpdb->prepare("SELECT DISTINCT filter_group FROM {$wpdb->prefix}pivot_filter WHERE page_id = %d AND filter_group IS NOT NULL ORDER BY filter_group ASC", $page_id);
  $groups = $wpdb->get_results($query);

  if (!empty($groups[0])) {
    return $groups;
  }

  return;
}

function pivot_get_filter_from_group($page_id, $group_id) {
  global $wpdb;

  $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}pivot_filter WHERE page_id = %d AND filter_group = %s ORDER BY filter_group ASC, filter_title ASC", $page_id, $group_id);
  $filters = $wpdb->get_results($query);

  if (!empty($filters[0])) {
    return $filters;
  }

  return;
}

function pivot_filters_meta_box() {
  global $edit_page;
  ?>
  <div class="form-item form-type-textfield form-item-pivot-urn">
      <label for="edit-pivot-urn"><?php esc_html_e('URN', 'pivot') ?> </label>
      <input type="text" id="edit-pivot-urn" name="urn" value="<?php if (isset($edit_page)) echo $edit_page->urn; ?>" maxlength="128" class="form-text">
      <span><input id="load-urn-info" class="button" type="button" value="<?php esc_html_e('Load URN Infos', 'pivot') ?>"></span>
      <p class="description"><?php esc_html_e('URN or ID of the field you want to filter', 'pivot') ?></p>
  </div>

  <div id="filter-urn-infos">
      <div class="form-item form-type-textfield form-item-pivot-filter-title">
          <label for="edit-pivot-filter-title"><?php esc_html_e('Filter title', 'pivot') ?> </label>
          <input type="text" id="edit-pivot-filter-title" name="title" value="<?php if (isset($edit_page)) echo $edit_page->filter_title; ?>" maxlength="128" class="form-text">
          <input type="text" id="edit-pivot-filter-title-nl" name="title-nl" placeholder="title-nl" value="<?php if (isset($edit_page)) echo $edit_page->filter_title_nl; ?>" maxlength="128" class="form-text">
          <input type="text" id="edit-pivot-filter-title-en" name="title-en" placeholder="title-en" value="<?php if (isset($edit_page)) echo $edit_page->filter_title_en; ?>" maxlength="128" class="form-text">
          <input type="text" id="edit-pivot-filter-title-de" name="title-de" placeholder="title-de" value="<?php if (isset($edit_page)) echo $edit_page->filter_title_de; ?>" maxlength="128" class="form-text">
          <p class="description"><?php esc_html_e('Title used in frontend (to display to the user)', 'pivot') ?></p>
      </div>
      <div class="form-item form-type-textfield form-item-pivot-operator">
          <label for="edit-pivot-operator"><?php esc_html_e('Operator', 'pivot') ?> </label>
          <select id="edit-pivot-operator" name="operator">
              <option selected disabled hidden><?php esc_html_e('Choose an operator', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'exist') echo 'selected="selected"'; ?>value="exist"><?php esc_html_e('Exist', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'equal') echo 'selected="selected"'; ?>value="equal"><?php esc_html_e('Equal', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'like') echo 'selected="selected"'; ?>value="like"><?php esc_html_e('Like', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'greaterequal') echo 'selected="selected"'; ?>value="greaterequal"><?php esc_html_e('Greater or equal', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'lesserequal') echo 'selected="selected"'; ?>value="lesserequal"><?php esc_html_e('Lesser or equal', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'between') echo 'selected="selected"'; ?>value="between"><?php esc_html_e('Between', 'pivot') ?></option>
              <option <?php if (isset($edit_page) && $edit_page->operator == 'in') echo 'selected="selected"'; ?>value="in"><?php esc_html_e('in', 'pivot') ?></option>
              <!--<option <?php // if(isset($edit_page) && $edit_page->operator == 'notempty') echo 'selected="selected"';                ?>value="notempty"><?php // esc_html_e('notempty', 'pivot')                ?></option>-->
          </select>
          <p class="description"><?php esc_html_e('Type of comparison', 'pivot') ?></p>
      </div>
  </div>

  <?php if (!isset($edit_page->id)): ?>
    <br>
    <div class="form-item form-type-textfield form-item-pivot-allpages">
        <input type="checkbox" id="edit-pivot-allpages" name="allpages" class="form-checkbox">
        <label for="edit-pivot-allpages"><?php esc_html_e('Add this filter to all Pivot pages', 'pivot') ?> </label>
        <p class="description"><?php esc_html_e('If you want to add this filter to all other pages', 'pivot'); ?></p>
    </div>
  <?php endif; ?>

  <br>
  <h2 class="hndle"><b><span><?php esc_html_e('Grouping filters', 'pivot') ?></span></b></h2>
  <br>

  <div class="form-item form-type-textfield form-item-filter-group">
      <h4><?php esc_html_e('If you want to group filters', 'pivot') ?></h4>
      <label for="edit-filter-group"><?php esc_html_e('Member of group', 'pivot') ?> </label>
      <input type="text" id="edit-filter-group" name="filter_group" value="<?php if (isset($edit_page)) echo $edit_page->filter_group; ?>" maxlength="128" class="form-text">
      <p class="description">
          <?php if (isset($edit_page->page_id)): ?>
            <?php $groups = pivot_get_filter_groups($edit_page->page_id); ?>
            <?php esc_html_e('Existing groups: ', 'pivot') ?>
            <?php foreach ($groups as $key => $group): ?>
              <?php end($groups); ?>
              <?php if ($key === key($groups)): ?>
                <?php print '<strong>' . $group->filter_group . '</strong>'; ?>
              <?php else: ?>
                <?php print '<strong>' . $group->filter_group . '</strong>, '; ?>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
      </p>
  </div>
  <?php
}

/**
 * Define options edit & delete VS add
 */
function pivot_filters_settings() {
  // Manipulate data of the custom table
  Pivot_Filters_List::pivot_filters_action();
  if (empty($_GET['edit'])) {
    // Display the data into the Dashboard
    ?>
    <div class="wrap">
        <h2><?php _e("Pivot Plugin filters", "pivot"); ?>
            <?php if (isset($_GET['page_id'])): ?>
              <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=pivot-filters&amp;page_id=<?php echo $_GET['page_id']; ?>&amp;edit=true" class="page-title-action"><?php _e('Add New'); ?></a>
            <?php endif; ?>
        </h2>
        <?php if (isset($_POST['submit'])): ?>
          <?php pivot_filter_csv_import($_GET['page_id']); ?>
        <?php endif; ?>
        <?php if (isset($_GET['page_id'])): ?>
          <div id="poststuff" class="postbox-container widefat page fixed">
              <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
                  <div id="formatdiv" class="postbox ">
                      <button type="button" class="handlediv" aria-expanded="true">
                          <span class="screen-reader-text"><?php esc_html_e('Toggle panel: Import filters', 'pivot') ?></span>
                          <span class="toggle-indicator" aria-hidden="true"></span>
                      </button>
                      <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Import filters', 'pivot') ?></h2>
                      <div class="inside">
                          <div id="import-filters-file">
                              <fieldset>
                                  <form action="" method="post" enctype="multipart/form-data">
                                      <input type="file" name="csv_file">
                                      <input type="hidden" value="<?php echo $_GET['page_id']; ?>" name="page_id" />
                                      <input type="submit" class="button" name="submit" value="<?php esc_html_e('Submit', 'pivot') ?>">
                                  </form>
                              </fieldset>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
        <?php endif; ?>

        <div id="poststuff">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        $table = new Pivot_Filters_List();
                        $table->prepare_items();
                        $table->search_box(__('Search'), 'search_id');
                        $table->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
        <?php if (isset($_GET['page_id']) && $table): ?>
          <a class="button" href="?export=dump&amp;page_id=<?php echo $_GET['page_id']; ?>" target="_blank"><i class="fa fa-align-right fa-download"></i><?php esc_html_e('Export filters', 'pivot') ?></a>
        <?php endif; ?>
    </div>
    </div>
    <?php
  } else {
    // Display a form to add or update the data
    pivot_add_filter();
  }
}

/**
 * Get global
 * @global type $edit_page
 */
function pivot_add_filter() {
  $id = '';
  if (isset($_GET['id']))
    $id = absint($_GET['id']);

  // Get a specific row from the table wp_pivot
  global $edit_page;
  if ($id)
    $edit_page = pivot_get_filter($id);

  // Create meta box
  add_meta_box('pivot-filter-meta', __('Pivot filter'), 'pivot_filters_meta_box', 'pivot', 'normal', 'core');
  ?>

  <!--Display the form to add a new row-->
  <div class="wrap">
      <div id="faq-wrapper">
          <form method="post" action="?page=pivot-filters&page_id=<?php echo $_GET['page_id'] ?>">
              <h2><?php echo $tf_title = ($id == 0) ? $tf_title = esc_attr__('Add filter', 'pivot') : $tf_title = esc_attr__('Edit filter', 'pivot'); ?></h2>
              <div id="poststuff" class="metabox-holder">
                  <?php do_meta_boxes('pivot', 'normal', 'low'); ?>
              </div>
              <input type="hidden" name="page_id" value="<?php echo $_GET['page_id'] ?>" />
              <input type="hidden" name="id" value="<?php echo $id ?>" />
              <input type="submit" value="<?php echo $tf_title; ?>" name="pivot_add_filter" id="pivot_add_filter" class="button-secondary">
          </form>
      </div>
  </div>
  <?php
}

/**
 * Import filters based on a CSV file
 * @global Object $wpdb
 * @param int $page_id
 */
function pivot_filter_csv_import($page_id) {
  global $wpdb;
  $text = '';

  $csv_file = $_FILES['csv_file'];
  $csv_to_array = array_map('str_getcsv', file($csv_file['tmp_name']));

  foreach ($csv_to_array as $key => $value) {
    // First line (Header)
    if ($key == 0) {
      $check = pivot_filter_csv_control_header($value);
      if ($check == 0) {
        echo _show_admin_notice(__("The CSV file doesn't respect the format", 'pivot'), 'error');
        break;
      }
    } else {
      // Check CSV quality
      $error = pivot_filter_csv_control($text, $key, $value);
      // If no error detected on first check
      if ($error == false) {
        $urn = $value[0];
        $urnDoc = _get_urn_documentation_full_spec($urn);
        $type = $urnDoc->spec->type->__toString();
        switch ($type) {
          case 'Boolean':
            $operator = 'exist';
            break;
          case 'Type':
          case 'Value':
            $operator = 'in';
            break;
          default:
            $operator = $value[1];
            break;
        }

        $name = substr(strrchr($urn, ":"), 1);
        $title = $value[2];
        $title_nl = $value[3];
        $title_en = $value[4];
        $title_de = $value[5];
        $group = $value[6];

        // Insert data
        $inserted = $wpdb->insert(
          $wpdb->prefix . 'pivot_filter',
          array(
            'page_id' => $page_id,
            'filter_name' => $name,
            'filter_title' => $title,
            'filter_title_nl' => $title_nl,
            'filter_title_en' => $title_en,
            'filter_title_de' => $title_de,
            'urn' => $urn,
            'operator' => $operator,
            'type' => $type,
            'filter_group' => $group
          ),
          array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
      }
      // IF WPML is active add title in translatable string
      if (is_plugin_active('wpml-string-translation/plugin.php')) {
        icl_register_string('pivot', 'filter-title-' . $urn . '-' . $page_id, $title, false, substr(get_locale(), 0, 2));
        icl_register_string('pivot', 'filter-group-' . preg_replace("/[^a-zA-Z]+/", "", $group), $group, false, substr(get_locale(), 0, 2));
      }
    }
  }

  // If there is an error to show
  if ($text != '') {
    echo _show_admin_notice($text, 'error');
  }
}

/**
 * Check first line (header) of the CSV file and return if error or not
 * @param array $value a row of the CSV file
 * @return boolean false if error true if OK
 */
function pivot_filter_csv_control_header($value) {
  if ($value[0] != 'urn') {
    return 0;
  }
  if ($value[1] != 'operator') {
    return 0;
  }
  if ($value[2] != 'filter_title') {
    return 0;
  }
  if ($value[3] != 'filter_title_nl') {
    return 0;
  }
  if ($value[4] != 'filter_title_en') {
    return 0;
  }
  if ($value[5] != 'filter_title_de') {
    return 0;
  }
  if ($value[6] != 'filter_group') {
    return 0;
  }

  return 1;
}

/**
 * Check quality of the first two column (which are mandatory)
 * @param String $text
 * @param int $key
 * @param String $value
 * @return boolean true if error otherwise false
 */
function pivot_filter_csv_control(&$text, $key, $value) {
  $error = false;
  // First check on field is well an urn
  if (substr($value[0], 0, 4) !== 'urn:') {
    $text .= __('URN is invalid on line ', 'pivot');
    $text .= $key + 1 . '</br>';
    $error = true;
  }

  // Check if operator is on allowed list
  if (!in_array($value[1], array('exist', 'equal', 'like', 'greaterequal', 'lesserequal', 'between', 'in'))) {
    $text .= __('Operator is invalid on line ', 'pivot');
    $text .= $key + 1 . '</br>';
    $error = true;
  }

  return $error;
}
