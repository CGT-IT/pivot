<?php


/**
 * Get all the data from table wp_pivot
 * @global Object $wpdb
 * @return Object
 */
function pivot_get_offer_type($id = NULL, $type = NULL) {
  global $wpdb;

  $sql_request = "SELECT * FROM {$wpdb->prefix}pivot_offer_type";

  // Both clauses used to be concatenated raw, and the id reaching this function comes
  // straight from the request URI (see pivot_template_include()) — unauthenticated
  // SQL injection. The AND between the two conditions was missing as well.
  $where = array();
  $values = array();

  if (!is_null($id)) {
    $where[] = 'id = %d';
    $values[] = absint($id);
  }
  if (!is_null($type)) {
    $where[] = 'type = %s';
    $values[] = (string) $type;
  }

  if (!empty($where)) {
    $sql_request .= ' WHERE ' . implode(' AND ', $where);
  }

  $sql_request .= " ORDER BY id ASC";

  $offer_type = empty($values)
    ? $wpdb->get_results($sql_request)
    : $wpdb->get_results($wpdb->prepare($sql_request, $values));

  if(!isset($offer_type[1]) && isset($offer_type[0])){
    return $offer_type[0];
  }

  return $offer_type;
}

/**
 * SQL query to get all categories or to check if a category exist if param "category"is set
 * @global Object $wpdb
 * @param string $type Type / Category name
 * @return string
 */
function pivot_get_offer_type_categories($type = NULL) {
  global $wpdb;
  
  $sql = "SELECT DISTINCT parent FROM {$wpdb->prefix}pivot_offer_type";
  if ($type) {
    $sql = $wpdb->prepare($sql . ' WHERE parent = %s', $type);
  }
  $categories = $wpdb->get_results($sql);

  if(!empty($categories[0])) {
    return $categories;
  }

  return;
}

/**
 * 
 * @global type $edit_type
 */
function pivot_offer_type_meta_box() {
    global $edit_type;
?>
  <div class="form-item form-type-textfield form-item-pivot-typeofr">
    <label for="edit-pivot-typeofr"><?php esc_html_e('Type of offer', 'pivot')?></label>
    <select id="edit-pivot-typeofr" name="pivot_typeofr">
      <option selected disabled hidden><?php esc_html_e('Choose a type of offer', 'pivot')?></option>
      <?php print _get_list_typeofr(isset($edit_type->id) ? $edit_type->id : null); ?>
    </select>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-type-id">
    <label for="edit-pivot-type-id"><?php esc_html_e('ID', 'pivot') ?></label>
    <input type="text" readonly="readonly" id="edit-pivot-type-id" name="id" value="<?php if(isset($edit_type)) echo esc_attr($edit_type->id);?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot ID of the offer type', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-type">
    <label for="edit-pivot-type"><?php esc_html_e('Type', 'pivot') ?> </label>
    <input type="text" readonly="readonly" id="edit-pivot-type" name="type" value="<?php if(isset($edit_type)) echo esc_attr($edit_type->type);?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot Name of the offer type', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-parent">
    <label for="edit-pivot-parent"><?php esc_html_e('Parent category', 'pivot') ?> </label>
    <input type="text" id="edit-pivot-parent" name="parent" value="<?php if(isset($edit_type)) echo esc_attr($edit_type->parent);?>" size="60" maxlength="128" class="form-text">
    <p class="description">
      <?php $categories = pivot_get_offer_type_categories(); ?>
      <?php esc_html_e('Existing categories: ', 'pivot')?>
      <?php if (!empty($categories)): ?>
        <?php
        $names = array();
        foreach ($categories as $category) {
          $names[] = '<strong>' . esc_html($category->parent) . '</strong>';
        }
        print implode(', ', $names);
        ?>
      <?php endif; ?>
    </p>
  </div>
<?php
}

/**
 * Define plugin options edit & delete VS add
 */    
function pivot_offer_type_settings(){
  // Manipulate data of the custom table
  pivot_offer_type_action();
  if (empty($_GET['edit'])) {
    // Display the data into the Dashboard
    pivot_manage_offer_type();
  } else {
    // Display a form to add or update the data
    pivot_add_offer_type();   
  }
}

/**
 * Define plugin actions
 * action of a CRUD
 * @global Object $wpdb
 */
function pivot_offer_type_action(){
  global $wpdb;

  // Offer types drive which template renders an offer: writing to this table used to
  // require neither a capability nor a nonce.
  if (!current_user_can('manage_options')) {
    return;
  }

  // Delete the data if the variable "delete" is set
  if(isset($_GET['delete'])) {
    $delete_id = absint($_GET['delete']);
    check_admin_referer('pivot_delete_offer_type_' . $delete_id);
    // First delete dependencies (filters linked to this page)
    $wpdb->delete($wpdb->prefix.'pivot_offer_type', array('id' => $delete_id), array('%d'));
  }

  if (isset($_POST['pivot_add_type'])) {
    check_admin_referer('pivot_save_offer_type');
  }

  $parent = isset($_POST['parent']) ? sanitize_text_field(wp_unslash($_POST['parent'])) : '';

  // Process the changes in the custom table
  if(isset($_POST['pivot_add_type']) && $parent != '') {
    $posted_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $posted_type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
    // Add new row in the custom table
    if(empty($_POST['type_id'])) {
      if(empty(pivot_get_offer_type($posted_id))){
        $wpdb->insert(
          $wpdb->prefix.'pivot_offer_type',
          array(
            'id' => (!empty($posted_id) ? $posted_id : time()),
            'type' => (!empty($posted_type) ? $posted_type : $parent),
            'parent' => $parent
          ),
          array('%d','%s','%s')
        );
      }else{
        print _show_admin_notice(esc_html__('This type has already been set', 'pivot'), 'error');
      }
    } else {
      // Update the data
      $wpdb->update(
        $wpdb->prefix.'pivot_offer_type',
        array(
          'parent' => $parent
        ),
        array('id' => absint($_POST['type_id'])),
        array('%s'),
        array('%d')
      );
    }
  }else{
    if(isset($_POST['pivot_add_type']) && $parent == '') {
      $text = esc_html__('Category is required', 'pivot');
      print _show_admin_notice($text);
    }
  }
}

/**
 * Get global
 * @global type $edit_type
 */
function pivot_add_offer_type(){
  $type_id = 0;
  if(isset($_GET['id'])) $type_id = absint($_GET['id']);

  // Get an specific row from the table wp_pivot
  global $edit_type;
  if ($type_id) $edit_type = pivot_get_offer_type($type_id);   

  // Create meta box
  add_meta_box('pivot-meta', 'Pivot Info', 'pivot_offer_type_meta_box', 'pivot', 'normal', 'core' );
?>

  <!--Display the form to add a new row-->
  <div class="wrap">
    <div id="faq-wrapper">
      <form method="post" action="?page=pivot-offer-types">
        <?php wp_nonce_field('pivot_save_offer_type'); ?>
          <h2><?php echo $tf_title = ($type_id == 0) ? esc_html__('Add type', 'pivot') : esc_html__('Edit type', 'pivot');?></h2>
        <div id="poststuff" class="metabox-holder">
          <?php do_meta_boxes('pivot', 'normal','low'); ?>
        </div>
        <input type="hidden" name="type_id" value="<?php echo esc_attr($type_id)?>" />
        <input type="submit" value="<?php echo esc_attr($tf_title);?>" name="pivot_add_type" id="pivot_add_type" class="button-secondary">
      </form>
    </div>
  </div>
<?php
}

function pivot_manage_offer_type(){
?>
<div class="wrap">
  <div class="icon32" id="icon-edit"><br></div>
  <h2><?php esc_html_e('Pivot Offer Types', 'pivot') ?></h2>
  <form method="post" action="?page=pivot-offer-types" id="pivot_form_action">
    <p>
      <input type="button" class="button-secondary" value="<?php esc_attr_e('Add type', 'pivot')?>" onclick="window.location='?page=pivot-offer-types&amp;edit=true'" />
    </p>
    <table class="widefat page fixed" cellpadding="0">
      <thead>
        <tr>
        <th id="cb" class="manage-column column-cb check-column" style="" scope="col">
          <input type="hidden"/>
        </th>
          <th class="manage-column"><?php esc_html_e('Type', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Parent Category', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('ID', 'pivot')?></th>
        </tr>
      </thead>

      <tbody>
        <?php
          $table = pivot_get_offer_type();
          if($table){
           $i=0;
           foreach($table as $type) { 
               $i++;
        ?>
      <tr class="<?php echo (ceil($i/2) == ($i/2)) ? "" : "alternate"; ?>">
        <th class="check-column" scope="row">
          <input type="hidden" value="<?php echo esc_attr($type->id)?>" name="type_id[]" />
        </th>
          <td>
            <strong><?php echo esc_html($type->type)?></strong>
            <div class="row-actions-visible">
              <span class="edit"><a href="<?php echo esc_url(admin_url('admin.php?page=pivot-offer-types&id=' . absint($type->id) . '&edit=true'))?>"><?php esc_html_e('Edit')?></a> | </span>
              <span class="delete"><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=pivot-offer-types&delete=' . absint($type->id)), 'pivot_delete_offer_type_' . absint($type->id)))?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this type?', 'pivot'))?>');"><?php esc_html_e('Delete')?></a></span>
            </div>
          </td>
          <td><?php echo esc_html($type->parent)?></td>
          <td><?php echo esc_html($type->id)?></td>
        </tr>
        <?php
           }
        }
        else{   
      ?>
        <tr><td colspan="4"><?php esc_html_e('There is no data.', 'pivot')?></td></tr>   
        <?php
      }
        ?>   
      </tbody>
    </table>


  </form>
</div>
<?php
}
