<?php

/**
 * Get all the data from table wp_pivot
 * @global Object $wpdb
 * @return Object
 */
function pivot_get_pages() {
  global $wpdb;
  
  $pages = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."pivot_pages ORDER BY id ASC");
  
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
  
  $pivot_page = $wpdb->get_results("SELECT * FROM " .$wpdb->prefix ."pivot_pages WHERE path='".$path."'");
  if(!empty($pivot_page[0])) {
    return $pivot_page[0];
  }

  return;
}

/**
 * Get a specific row from table wp_pivot
 * @global Object $wpdb
 * @param string $id page id
 * @return string
 */
function pivot_get_page($id) {
  global $wpdb;

  $pivot_page = $wpdb->get_results("SELECT * FROM " .$wpdb->prefix ."pivot_pages WHERE id='".$id."'");
  if(!empty($pivot_page[0])) {
    return $pivot_page[0];
  }

  return;
}

function pivot_meta_box() {
    global $edit_page;
?>
  <div class="form-item form-type-textfield form-item-pivot-query">
    <label for="edit-pivot-query"><?php esc_html_e('Query', 'pivot') ?></label>
    <input type="text" id="edit-pivot-query" name="query" value="<?php if(isset($edit_page)) echo $edit_page->query;?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Pivot predefined query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-type">
    <label for="edit-pivot-type"><?php esc_html_e('Type', 'pivot') ?> </label>
    <select id="edit-pivot-type" name="type">
      <?php print _get_offer_types($edit_page); ?>
    </select>
    <p class="description"><?php esc_html_e('Type of query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-path">
    <label for="edit-pivot-path"><?php esc_html_e('Path', 'pivot') ?> </label>
    <input type="text" id="edit-pivot-path" name="path" value="<?php if(isset($edit_page)) echo $edit_page->path;?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Path to access results', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-title">
    <label for="edit-pivot-title"><?php esc_html_e('Title', 'pivot') ?> </label>
    <input type="text" id="edit-pivot-title" name="title" value="<?php if(isset($edit_page)) echo $edit_page->title;?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Page title', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-map">
    <input type="checkbox" id="edit-pivot-map" name="map" class="form-checkbox" <?php echo (isset($edit_page) && $edit_page->map == 1?'checked':'');?>>
    <label for="edit-pivot-map"><?php esc_html_e('Show map', 'pivot') ?> </label>
    <img class="pivot-picto" src="https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/img/urn:typ:269;modifier=orig;h=20"/>
    <p class="description"><?php esc_html_e('Define if you want to show a map on this page or not', 'pivot') ?></p>
  </div>

  <br>
  <h2 class="hndle"><b><span><?php esc_html_e('Query sorting', 'pivot') ?></span></b></h2>
  <br>
  
  <div class="form-item form-type-textfield form-item-pivot-sortMode">
    <label for="edit-pivot-sortMode"><?php esc_html_e('Sort mode', 'pivot') ?> </label>
    <select id="edit-pivot-sortMode" name="sortMode">
      <option selected value=""><?php esc_html_e('Choose an order', 'pivot') ?></option>
      <option <?php if(isset($edit_page) && $edit_page->sortMode == 'ASC') echo 'selected="selected"';?>value="ASC"><?php esc_html_e('Ascending', 'pivot') ?></option>
      <option <?php if(isset($edit_page) && $edit_page->sortMode == 'DESC') echo 'selected="selected"';?>value="DESC"><?php esc_html_e('Descending', 'pivot') ?></option>
      <option <?php if(isset($edit_page) && $edit_page->sortMode == 'shuffle') echo 'selected="selected"';?>value="shuffle"><?php esc_html_e('Shuffle', 'pivot') ?></option>
    </select>
    <p class="description"><?php esc_html_e('Choose the sort mode for the query', 'pivot') ?></p>
  </div>
  <div class="form-item form-type-textfield form-item-pivot-sortField">
    <label for="edit-pivot-sortField"><?php esc_html_e('Sort Field', 'pivot') ?> </label>
    <input type="text" id="edit-pivot-sortField" name="sortField" value="<?php if(isset($edit_page)) echo $edit_page->sortField;?>" size="60" maxlength="128" class="form-text">
    <p class="description"><?php esc_html_e('Define the field on which the sort mode will apply', 'pivot') ?></p>
  </div>
<?php 
}

/**
 * Define plugin options edit & delete VS add
 */    
function pivot_pages_settings(){
  // Manipulate data of the custom table
  pivot_action();
  if (empty($_GET['edit'])) {
    // Display the data into the Dashboard
    pivot_manage_page();
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
function pivot_action(){
  global $wpdb;

  // Delete the data if the variable "delete" is set
  if(isset($_GET['delete'])) {
      $_GET['delete'] = absint($_GET['delete']);
      // First delete dependencies (filters linked to this page)
      $wpdb->query("DELETE FROM " .$wpdb->prefix ."pivot_filter WHERE page_id='" .$_GET['delete']."'");
      // Delete the page
      $wpdb->query("DELETE FROM " .$wpdb->prefix ."pivot_pages WHERE id='" .$_GET['delete']."'");
  }

  // Process the changes in the custom table
  if(isset($_POST['pivot_add_page']) && $_POST['type'] != '' && $_POST['query'] != '' && $_POST['path'] != '' && $_POST['title'] != '') {
    // Add new row in the custom table
    $type = $_POST['type'];
    $query = preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['query']);
    //$query = $_POST['query'];
    $path = $_POST['path'];
    $title = $_POST['title'];
    $map = isset($_POST['map'])?1:0;
    $sortMode = $_POST['sortMode'];
    $sortField = $_POST['sortField'];
    
    // Check if path already exist in wordpress or not (to avoid duplicate and conflict)
    if(!$pivot_page = get_page_by_path($path)){
      if(empty($_POST['page_id'])){
        $wpdb->query("INSERT INTO " .$wpdb->prefix ."pivot_pages(type,query,path,title,map,sortMode,sortField) VALUES('" .$type ."','" .$query."','" .$path."','" .$title."','" .$map."','" .$sortMode."','" .$sortField."');");
      }else{
        // Update the data
        $page_id = $_POST['page_id'];
        $wpdb->query("UPDATE " .$wpdb->prefix. "pivot_pages SET type='" .$type ."', query='" .$query ."', path='" .$path ."', title='" .$title ."', map='" .$map ."', sortMode='" .$sortMode ."', sortField='" .$sortField ."' WHERE id='" .$page_id ."'");
      }
    }else{
      $text = esc_html('This path already exists', 'pivot').': <a href="'.get_permalink( $pivot_page->ID ).'">'.get_permalink( $pivot_page->ID ).'</a>';
      print _show_admin_notice($text);
    }
    flush_rewrite_rules();
  }else{
    if(isset($_POST['pivot_add_page']) && (!isset($_POST['query']) || $_POST['query'] == '')){
      $text = esc_html('Query is required', 'pivot');
      print _show_admin_notice($text);
    }
    if(isset($_POST['pivot_add_page']) && (!isset($_POST['type']) || $_POST['type'] == '')){
      $text = esc_html('Type is required', 'pivot');
      print _show_admin_notice($text);
    }
    if(isset($_POST['pivot_add_page']) && (!isset($_POST['path']) || $_POST['path'] == '')){
      $text = esc_html('Path is required', 'pivot');
      print _show_admin_notice($text);
    }
    if(isset($_POST['pivot_add_page']) && (!isset($_POST['title']) || $_POST['title'] == '')){
      $text = esc_html('Page title is required', 'pivot');
      print _show_admin_notice($text);
    }
    
  } 
}

/**
 * Get global
 * @global type $edit_page
 */
function pivot_add_page(){
  $page_id = 0;
  if(isset($_GET['id'])) $page_id = $_GET['id'];

  // Get an specific row from the table wp_pivot
  global $edit_page;
  if ($page_id) $edit_page = pivot_get_page($page_id);   

  // Create meta box
  add_meta_box('pivot-meta', 'Pivot Info', 'pivot_meta_box', 'pivot', 'normal', 'core' );
?>

  <!--Display the form to add a new row-->
  <div class="wrap">
    <div id="faq-wrapper">
      <form method="post" action="?page=pivot-pages">
          <h2><?php echo $tf_title = ($page_id == 0)?$tf_title = esc_attr('Add page', 'pivot') : $tf_title = esc_attr('Edit page', 'pivot');?></h2>
        <div id="poststuff" class="metabox-holder">
          <?php do_meta_boxes('pivot', 'normal','low'); ?>
        </div>
        <input type="hidden" name="page_id" value="<?php echo $page_id?>" />
        <input type="submit" value="<?php echo $tf_title;?>" name="pivot_add_page" id="pivot_add_page" class="button-secondary">
      </form>
    </div>
  </div>
<?php
}

function pivot_manage_page(){
?>
<div class="wrap">
  <div class="icon32" id="icon-edit"><br></div>
  <h2><?php esc_html_e('Pivot Plugin Pages', 'pivot') ?></h2>
  <form method="post" action="?page=pivot-pages" id="pivot_form_action">
    <p>
      <input type="button" class="button-secondary" value="<?php esc_attr_e('Add a new page', 'pivot')?>" onclick="window.location='?page=pivot-pages&amp;edit=true'" />
    </p>
    <table class="widefat page fixed" cellpadding="0">
      <thead>
        <tr>
        <th id="cb" class="manage-column column-cb check-column" style="" scope="col">
          <input type="hidden"/>
        </th>
          <th class="manage-column"><?php esc_html_e('Query', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Type', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Path', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Page title', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Map ?', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Sorting', 'pivot')?></th>
          <th class="manage-column"><?php esc_html_e('Filters', 'pivot')?></th>
        </tr>
      </thead>

      <tbody>
        <?php
          $table = pivot_get_pages();
          if($table){
           $i=0;
           foreach($table as $pivot_page) { 
               $i++;
        ?>
      <tr class="<?php echo (ceil($i/2) == ($i/2)) ? "" : "alternate"; ?>">
        <th class="check-column" scope="row">
          <input type="hidden" value="<?php echo $pivot_page->id?>" name="page_id[]" />
        </th>
          <td>
            <strong><?php echo $pivot_page->query?></strong>
            <div class="row-actions-visible">
              <span class="edit"><a href="?page=pivot-pages&amp;id=<?php echo $pivot_page->id?>&amp;edit=true"><?php esc_html_e('Edit')?></a> | </span>
              <span class="delete"><a href="?page=pivot-pages&amp;delete=<?php echo $pivot_page->id?>" onclick="return confirm('Are you sure you want to delete this page?');"><?php esc_html_e('Delete')?></a></span>
            </div>
          </td>
          <td><?php echo $pivot_page->type?></td>
          <td><?php echo '<a href="'.get_bloginfo('wpurl').'/'.$pivot_page->path.'">'.$pivot_page->path.'</a>';?></td>
          <td><?php echo $pivot_page->title?></td>
          <td><?php echo ($pivot_page->map == 1)?'&#10004;':'&#10008;';?></td>
          <td>
            <?php if($pivot_page->sortMode != ''){
                    echo $pivot_page->sortMode;
                    if($pivot_page->sortField != ''){
                      echo ' on '.$pivot_page->sortField;
                    }
                  }else{
                    echo 'none';
                  }
            ?>
          </td>
          <td class="manage-column">
            <input type="button" class="button-secondary" value="<?php esc_html_e('View filter(s)', 'pivot')?>" onclick="window.location='?page=pivot-filters&amp;page_id=<?php echo $pivot_page->id; ?>'" />
            <input type="button" class="button-secondary" value="<?php esc_html_e('Add a filter', 'pivot')?>" onclick="window.location='?page=pivot-filters&amp;page_id=<?php echo $pivot_page->id; ?>&amp;edit=true'" />
          </td>
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
