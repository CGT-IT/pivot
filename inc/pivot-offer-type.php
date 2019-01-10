<?php


/**
 * Get all the data from table wp_pivot
 * @global Object $wpdb
 * @return Object
 */
function pivot_get_offer_type($id = NULL, $type = NULL) {
  global $wpdb;

  $sql_request = "SELECT * FROM ".$wpdb->prefix."pivot_offer_type";

  if(!is_null($id)){
    $sql_request .= " WHERE id='".$id."'";
    if(!is_null($type)){
      $sql_request .= " type='".$type."'";
    }
  }

  if(!is_null($type)){
    $sql_request .= " WHERE type='".$type."'";
  }
  
  $sql_request .= " ORDER BY id ASC";
  
  $offer_type = $wpdb->get_results($sql_request);
  
  if(!isset($offer_type[1])){
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
  
  $sql = "SELECT DISTINCT parent FROM " .$wpdb->prefix ."pivot_offer_type";
  if($type){
    $sql .= " WHERE parent = '".$type."'";
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
      <?php print _get_list_typeofr($edit_type->id); ?>
    </select>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-type-id">
    <label for="edit-pivot-type-id"><?php esc_html_e('ID', 'pivot') ?></label>
    <input type="text" readonly="readonly" id="edit-pivot-type-id" name="id" value="<?php if(isset($edit_type)) echo $edit_type->id;?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot ID of the offer type', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-type">
    <label for="edit-pivot-type"><?php esc_html_e('Type', 'pivot') ?> </label>
    <input type="text" readonly="readonly" id="edit-pivot-type" name="type" value="<?php if(isset($edit_type)) echo $edit_type->type;?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot Name of the offer type', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-parent">
    <label for="edit-pivot-parent"><?php esc_html_e('Parent category', 'pivot') ?> </label>
    <input type="text" id="edit-pivot-parent" name="parent" value="<?php if(isset($edit_type)) echo $edit_type->parent;?>" size="60" maxlength="128" class="form-text">
    <p class="description">
      <?php $categories = pivot_get_offer_type_categories(); ?>
      <?php esc_html_e('Existing categories: ', 'pivot')?>
      <?php foreach($categories as $key => $category): ?>
        <?php end($categories); ?>
        <?php if($key === key($categories)): ?>
          <?php print '<strong>'.$category->parent.'</strong>'; ?>
        <?php else: ?>
          <?php print '<strong>'.$category->parent.'</strong>' . ', '; ?>
        <?php endif; ?>
      <?php endforeach; ?>
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

  // Delete the data if the variable "delete" is set
  if(isset($_GET['delete'])) {
      $_GET['delete'] = absint($_GET['delete']);
      // First delete dependencies (filters linked to this page)
      $wpdb->query("DELETE FROM " .$wpdb->prefix ."pivot_offer_type WHERE id='" .$_GET['delete']."'");
  }
  // Process the changes in the custom table
  if(isset($_POST['pivot_add_type']) && $_POST['pivot_typeofr'] != '' && $_POST['parent'] != '') {
    // Add new row in the custom table
    if(empty($_POST['type_id'])) {
      if(empty(pivot_get_offer_type($_POST['id']))){
        $sql = "INSERT INTO " .$wpdb->prefix ."pivot_offer_type(id,type,parent) VALUES('" .$_POST['id'] ."','" .$_POST['type']."','" .$_POST['parent']."');";
        $wpdb->query($sql);
      }else{
        print _show_admin_notice('This type has already been set', 'error');
      }
    } else {
      // Update the data
      $wpdb->query("UPDATE " .$wpdb->prefix. "pivot_offer_type SET parent='" .$_POST['parent']."' WHERE id='" .$_POST['type_id'] ."'");
    }
  }else{
    if(isset($_POST['pivot_add_type']) && $_POST['parent'] == '') {
      $text = esc_html('Category is required', 'pivot');
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
  if(isset($_GET['id'])) $type_id = $_GET['id'];

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
          <h2><?php echo $tf_title = ($type_id == 0)?$tf_title = esc_attr('Add type', 'pivot') : $tf_title = esc_attr('Edit type', 'pivot');?></h2>
        <div id="poststuff" class="metabox-holder">
          <?php do_meta_boxes('pivot', 'normal','low'); ?>
        </div>
        <input type="hidden" name="type_id" value="<?php echo $type_id?>" />
        <input type="submit" value="<?php echo $tf_title;?>" name="pivot_add_type" id="pivot_add_type" class="button-secondary">
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
          <input type="hidden" value="<?php echo $type->id?>" name="type_id[]" />
        </th>
          <td>
            <strong><?php echo $type->type?></strong>
            <div class="row-actions-visible">
              <span class="edit"><a href="?page=pivot-offer-types&amp;id=<?php echo $type->id?>&amp;edit=true"><?php esc_html_e('Edit')?></a> | </span>
              <span class="delete"><a href="?page=pivot-offer-types&amp;delete=<?php echo $type->id?>" onclick="return confirm('Are you sure you want to delete this type?');"><?php esc_html_e('Delete')?></a></span>
            </div>
          </td>
          <td><?php echo $type->parent?></td>
          <td><?php echo $type->id?></td>
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
