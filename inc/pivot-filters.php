<?php

/**
 * Get all the data from table wp_pivot_filter
 * @global Object $wpdb
 * @return Object
 */
function pivot_get_filters($page_id = NULL) {
  global $wpdb;
  if(!empty($page_id)){
    $filters = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."pivot_filter WHERE page_id ='".$page_id."'ORDER BY filter_group ASC, filter_title ASC");
  }else{
    $filters = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."pivot_filter ORDER BY filter_title ASC");
  }
  
  return $filters;
}

/**
 * Get a specific row from table wp_pivot_filter
 * @global Object $wpdb
 * @param string $id page id
 * @return string
 */
function pivot_get_filter($id) {
  global $wpdb;

  $filter = $wpdb->get_results("SELECT * FROM " .$wpdb->prefix ."pivot_filter WHERE id='".$id."'");

  if(!empty($filter[0])) {
    return $filter[0];
  }

  return;
}

function pivot_get_filter_groups($page_id) {
  global $wpdb;

  $groups = $wpdb->get_results("SELECT DISTINCT filter_group FROM " .$wpdb->prefix ."pivot_filter WHERE page_id='".$page_id."' AND filter_group IS NOT NULL");

  if(!empty($groups[0])) {
    return $groups;
  }

  return;
}

function pivot_filters_meta_box() {
    global $edit_page;
?>
  <div class="form-item form-type-textfield form-item-pivot-urn">
    <label for="edit-pivot-urn"><?php esc_html_e('URN', 'pivot')?> </label>
    <input type="text" id="edit-pivot-urn" name="urn" value="<?php if(isset($edit_page)) echo $edit_page->urn;?>" maxlength="128" class="form-text">
    <span><input id="load-urn-info" class="button" type="button" value="<?php esc_html_e('Load URN Infos', 'pivot')?>"> </button></span>
    <p class="description"><?php esc_html_e('URN or ID of the field you want to filter', 'pivot')?></p>
  </div>

  <div id="filter-urn-infos">
    <div class="form-item form-type-textfield form-item-pivot-filter-title">
      <label for="edit-pivot-filter-title"><?php esc_html_e('Filter title', 'pivot')?> </label>
      <input type="text" id="edit-pivot-filter-title" name="title" value="<?php if(isset($edit_page)) echo $edit_page->filter_title;?>" maxlength="128" class="form-text">
      <p class="description"><?php esc_html_e('Title used in frontend (to display to the user)', 'pivot')?></p>
    </div>
    <div class="form-item form-type-textfield form-item-pivot-operator">
      <label for="edit-pivot-operator"><?php esc_html_e('Operator', 'pivot')?> </label>
      <select id="edit-pivot-operator" name="operator">
        <option selected disabled hidden><?php esc_html_e('Choose an operator', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'exist') echo 'selected="selected"';?>value="exist"><?php esc_html_e('Exist', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'equal') echo 'selected="selected"';?>value="equal"><?php esc_html_e('Equal', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'like') echo 'selected="selected"';?>value="like"><?php esc_html_e('Like', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'greaterequal') echo 'selected="selected"';?>value="greaterequal"><?php esc_html_e('Greater or equal', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'between') echo 'selected="selected"';?>value="between"><?php esc_html_e('Between', 'pivot')?></option>
        <option <?php if(isset($edit_page) && $edit_page->operator == 'in') echo 'selected="selected"';?>value="in"><?php esc_html_e('in', 'pivot')?></option>
      </select>
      <p class="description"><?php esc_html_e('Type of comparison', 'pivot') ?></p>
    </div>
  </div>

  <?php if(!isset($edit_page->id)): ?>
    <br>
    <div class="form-item form-type-textfield form-item-pivot-allpages">
      <input type="checkbox" id="edit-pivot-allpages" name="allpages" class="form-checkbox">
      <label for="edit-pivot-allpages"><?php esc_html_e('Add this filter to all Pivot pages', 'pivot') ?> </label>
      <p class="description"><?php esc_html_e('If you want to add this filter to all other pages', 'pivot');?></p>
    </div>
  <?php endif; ?>
      
  <br>
  <h2 class="hndle"><b><span><?php esc_html_e('Grouping filters', 'pivot') ?></span></b></h2>
  <br>
  
  <div class="form-item form-type-textfield form-item-filter-group">
    <h4><?php esc_html_e('If you want to group filters', 'pivot')?></h4>
    <label for="edit-filter-group"><?php esc_html_e('Member of group', 'pivot')?> </label>
    <input type="text" id="edit-filter-group" name="filter_group" value="<?php if(isset($edit_page)) echo $edit_page->filter_group;?>" maxlength="128" class="form-text">
    <p class="description">
      <?php if(isset($edit_page->page_id)): ?>
        <?php $groups = pivot_get_filter_groups($edit_page->page_id); ?>
        <?php esc_html_e('Existing groups: ', 'pivot')?>
        <?php foreach($groups as $key => $group): ?>
          <?php end($groups); ?>
          <?php if($key === key($groups)): ?>
            <?php print '<strong>'.$group->filter_group.'</strong>'; ?>
          <?php else: ?>
            <?php print '<strong>'.$group->filter_group.'</strong>' . ', '; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </p>
  </div>
<?php 
}

/**
 * Define plugin options edit & delete VS add
 */    
function pivot_filters_settings(){
  // Manipulate data of the custom table
  pivot_filters_action();
  if (empty($_GET['edit'])) {
    // Display the data into the Dashboard
    pivot_manage_filter();
  } else {
    // Display a form to add or update the data
    pivot_add_filter();   
  }
}

/**
 * Define plugin actions
 * action of a CRUD
 * @global Object $wpdb
 */
function pivot_filters_action(){
  global $wpdb;
  // Delete the data if the variable "delete" is set
  if(isset($_GET['delete'])) {
    $wpdb->query("DELETE FROM " .$wpdb->prefix ."pivot_filter WHERE id=" .$_GET['delete']."");
  }

  // Process the changes in the custom table
  if(isset($_POST['pivot_add_filter']) && isset($_POST['title']) && isset($_POST['urn'])) {
    // Add new row in the custom table
    $urn = $_POST['urn'];
    $urnDoc= _get_urn_documentation_full_spec($urn);
    $type = $urnDoc->spec->type->__toString();
    switch($type){
      case 'Boolean':
        $operator = 'exist';
        break;
      case 'Type':
      case 'Value':
        $operator = 'in';
        break;
      default:
        $operator = $_POST['operator'];
        break;
    }

    $name = substr(strrchr($urn, ":"), 1);
    $title = $_POST['title'];
    $group = $_POST['filter_group'];

    if(empty($_POST['id'])) {
      // If we add the filter to all pages
      if(isset($_POST['allpages'])){
        $pages = pivot_get_pages();
        foreach($pages as $page){
          // Insert data
          $wpdb->query("INSERT INTO " .$wpdb->prefix ."pivot_filter (page_id,filter_name,filter_title,urn,operator,type,filter_group) VALUES('" .$page->id ."','" .$name ."','" .$title."','" .$urn."','" .$operator."','" .$type."','" .$group."');");
        }
      }else{
        // Insert data
        $wpdb->query("INSERT INTO " .$wpdb->prefix ."pivot_filter (page_id,filter_name,filter_title,urn,operator,type,filter_group) VALUES('" .$_POST['page_id'] ."','" .$name ."','" .$title."','" .$urn."','" .$operator."','" .$type."','" .$group."');");
      }
    } else {
      // Update data
      $id = $_POST['id'];
      $wpdb->query("UPDATE " .$wpdb->prefix. "pivot_filter SET page_id=".$_POST['page_id'].", filter_name='" .$name ."', filter_title='" .$title ."', urn='" .$urn ."', operator='" .$operator ."', type='" .$type ."', filter_group='" .$group ."' WHERE id='" .$id ."'");
    }
  }  
}

/**
 * Get global
 * @global type $edit_page
 */
function pivot_add_filter(){
  $id = '';
  if(isset($_GET['id'])) $id = absint($_GET['id']);

  // Get a specific row from the table wp_pivot
  global $edit_page;
  if ($id) $edit_page = pivot_get_filter($id);   

  // Create meta box
  add_meta_box('pivot-filter-meta', __('Pivot filter'), 'pivot_filters_meta_box', 'pivot', 'normal', 'core' );
?>

  <!--Display the form to add a new row-->
  <div class="wrap">
    <div id="faq-wrapper">
      <form method="post" action="?page=pivot-filters&page_id=<?php echo $_GET['page_id']?>">
        <h2><?php echo $tf_title = ($id == 0)?$tf_title = esc_attr__('Add filter', 'pivot') : $tf_title = esc_attr__('Edit filter', 'pivot');?></h2>
        <div id="poststuff" class="metabox-holder">
          <?php do_meta_boxes('pivot', 'normal','low'); ?>
        </div>
        <input type="hidden" name="page_id" value="<?php echo $_GET['page_id']?>" />
        <input type="hidden" name="id" value="<?php echo $id?>" />
        <input type="submit" value="<?php echo $tf_title;?>" name="pivot_add_filter" id="pivot_add_filter" class="button-secondary">
      </form>
    </div>
  </div>
<?php
}

function pivot_manage_filter(){
?>
<div class="wrap">
  <div class="icon32" id="icon-edit"><br></div>
  <h2><?php esc_html_e('Pivot Plugin filters', 'pivot')?></h2>
    
  <?php if(isset($_GET['page_id'])): ?>
    <?php if(isset($_POST['submit'])): ?>
      <?php pivot_filter_csv_import($_GET['page_id']); ?>
    <?php endif; ?>
    <div id="poststuff" class="postbox-container widefat page fixed">
      <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
        <div id="formatdiv" class="postbox ">
          <button type="button" class="handlediv" aria-expanded="true">
            <span class="screen-reader-text"><?php esc_html_e('Toggle panel: Import filters', 'pivot')?></span>
            <span class="toggle-indicator" aria-hidden="true"></span>
          </button>
          <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Import filters', 'pivot')?></h2>
          <div class="inside">
            <div id="import-filters-file">
              <fieldset>
                <form action="" method="post" enctype="multipart/form-data">
                  <input type="file" name="csv_file">
                  <input type="hidden" value="<?php echo $_GET['page_id']; ?>" name="page_id" />
                  <input type="submit" class="button" name="submit" value="<?php esc_html_e('Submit', 'pivot')?>">
                </form>
              </fieldset>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
    
  <form method="post" action="?page=pivot-filters" id="pivot_form_action">
    <?php if(isset($_GET['page_id'])): ?>
      <p><input type="button" class="button button-secondary" value="<?php esc_html_e('Add a filter', 'pivot')?>" onclick="window.location='?page=pivot-filters&amp;page_id=<?php echo $_GET['page_id']; ?>&amp;edit=true'" /></p>
    <?php endif; ?>
    <table class="widefat page fixed" cellpadding="0">
      <thead>
        <tr>
        <th id="cb" class="manage-column column-cb check-column" style="" scope="col">
          <input type="hidden"/>
        </th>
          <th class="manage-column"><?php esc_html_e('Filter name', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Filter title', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('URN', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Operator', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Type', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Linked to query', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('In group', 'pivot')?></th>
        </tr>
      </thead>

      <tbody>
        <?php
          $table = pivot_get_filters(isset($_GET['page_id'])?$_GET['page_id']:null);
          if($table){
           $i=0;
           foreach($table as $filter) { 
               $i++;
        ?>
      <tr class="<?php echo (ceil($i/2) == ($i/2)) ? "" : "alternate"; ?>">
        <th class="check-column" scope="row">
          <input type="hidden" value="<?php echo $filter->id; ?>" name="id" />
          <input type="hidden" value="<?php echo $filter->page_id; ?>" name="page_id" />
        </th>
          <td>
            <strong><?php echo $filter->filter_name?></strong>
            <div class="row-actions-visible">
              <span class="edit"><a href="?page=pivot-filters&amp;id=<?php echo $filter->id?>&amp;page_id=<?php echo $filter->page_id;?>&amp;edit=true"><?php esc_html_e('Edit', 'pivot')?></a> | </span>
              <span class="delete"><a href="?page=pivot-filters&amp;page_id=<?php echo $filter->page_id;?>&amp;delete=<?php echo $filter->id?>" onclick="return confirm(esc_attr('Are you sure you want to delete this filter?', 'pivot'));"><?php esc_html_e('Delete', 'pivot')?></a></span>
            </div>
          </td>
          <td><?php echo $filter->filter_title?></td>
          <td><?php echo $filter->urn?></td>
          <td><?php echo $filter->operator?></td>
          <td><?php echo $filter->type?></td>
          <td>
            <?php $page = pivot_get_page($filter->page_id)?>
            <?php echo $page->query?>
          </td>
          <td><?php echo $filter->filter_group?></td>
        </tr>
        <?php
           }
        }
        else{   
      ?>
        <tr><td colspan="4"><?php esc_html_e('There are no data.', 'pivot')?></td></tr>   
        <?php
      }
        ?>   
      </tbody>
    </table>
  </form>
  <?php if(isset($_GET['page_id']) && $table): ?>
  <br/><a class="button" href="?export=dump&amp;page_id=<?php echo $_GET['page_id']; ?>" target="_blank"><i class="fa fa-align-right fa-download"></i><?php esc_html_e('Export filters', 'pivot')?></a>
  <?php endif; ?>
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
    if ($key == 0){
      $check = pivot_filter_csv_control_header($value);
      if($check == 0){
        echo _show_admin_notice(__("The CSV file doesn't respect the format",'pivot'), 'error');
        break;
      }
    }else{
      // Check CSV quality
      $error = pivot_filter_csv_control($text, $key, $value);
      // If no error detected on first check
      if($error == false){
        $urn = $value[0];
        $urnDoc= _get_urn_documentation_full_spec($urn);
        $type = $urnDoc->spec->type->__toString();
        switch($type){
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
        $group = $value[3];
        
        // Insert data
        $wpdb->query("INSERT INTO " .$wpdb->prefix ."pivot_filter (page_id,filter_name,filter_title,urn,operator,type,filter_group) VALUES('" .$page_id."','" .$name ."','" .$title."','" .$urn."','" .$operator."','" .$type."','" .$group."');");

      }
    }
  }
  
  // If there is an error to show
  if($text != ''){
    echo _show_admin_notice($text, 'error');
  }
}

/**
 * Check first line (header) of the CSV file and return if error or not
 * @param array $value a row of the CSV file
 * @return boolean false if error true if OK
 */
function pivot_filter_csv_control_header($value){
  if($value[0] != 'urn'){
    return 0;
  }
  if($value[1] != 'operator'){
    return 0;
  }
  if($value[2] != 'filter_title'){
    return 0;
  }
  if($value[3] != 'filter_group'){
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
function pivot_filter_csv_control(&$text, $key, $value){
  $error = false;
  // First check on field is well an urn
  if(substr($value[0],0,4) !== 'urn:'){
    $text .= __('URN is invalid on line ', 'pivot');
    $text .= $key+1 .'</br>';
    $error = true;
  }

  // Check if operator is on allowed list
  if(!in_array($value[1], array('exist','equal','like','greaterequal','between','in'))){
    $text .= __('Operator is invalid on line ', 'pivot');
    $text .= $key+1 .'</br>';
    $error = true;
  }
  
  return $error;
}
