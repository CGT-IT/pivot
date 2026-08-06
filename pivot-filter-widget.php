<?php

add_action('widgets_init', function () {
  register_widget('pivot_filter_widget');
});

// Register My_Widget
class pivot_filter_widget extends WP_Widget {

  // class constructor
  public function __construct() {
    $widget_ops = array(
      'classname' => 'filter_widget',
      'description' => 'A widget fo filter on offers from pivot',
    );
    parent::__construct(
      // Base ID of your widget
      'filter_widget',
      // Widget name will appear in UI
      'Filter Widget',
      // array of options
      $widget_ops
    );
  }

  // output the widget content on the front-end
  public function widget($args, $instance) {
    print pivot_display_widget($instance);
  }

  // output the option form field in admin Widgets screen
  public function form($instance) {
    $defaults = array('pageid' => '0');
    if (isset($instance['pageid'])) {
      $pageid = $instance['pageid'];
    }

    $pages = pivot_get_pages();

    // markup for form
    $output = '<div class="form-item form-type-textfield form-item-pivot-' . $this->get_field_id('pageid') . '">'
      . '<label for="' . $this->get_field_id('pageid') . '">' . esc_html__('Referenced Page ID', 'pivot') . '</label>'
      . '<select id="' . $this->get_field_id('pageid') . '" name="' . $this->get_field_name('pageid') . '">'
      . '<option selected value="">' . esc_html__('Choose a page', 'pivot') . '</option>';
    foreach ($pages as $page) {
      if ($pageid == $page->title) {
        $output .= '<option selected value="' . $page->title . '">' . $page->title . '</option>';
      } else {
        $output .= '<option value="' . $page->title . '">' . $page->title . '</option>';
      }
    }
    $output .= '</select></div>';

    echo $output;
  }

  // save options
  public function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['pageid'] = strip_tags($new_instance['pageid']);
    return $instance;
  }
}

function pivot_display_widget($instance = NULL) {
  if (isset($instance['pageid'])) {
    $pivot_page = pivot_get_page_path($instance['pageid']);
  }

  if (isset($pivot_page->id) && $pivot_page->id != null) {
    pivot_reset_filters($pivot_page->id);
    // Get filters attach to current page
    $filters = pivot_get_filters($pivot_page->id);
    if (empty($filters)) {
      return;
    }

    // Print head section and HTML Form
    $lang = substr(get_locale(), 0, 2);
    $output = '<section id="block-pivot-filters" class="block block-pivot block-pivot-filter clearfix">'
      . '<form action="' . get_site_url() . '/' . (($lang == 'fr') ? '' : $lang . '/') . $pivot_page->path . '" method="post" id="pivot-filter-form" accept-charset="UTF-8">';

    foreach ($filters as $filter) {
      // if not first iteration and filter is member of a group already inserted, we do not recreate this group
      if (isset($last_filter_group) && $last_filter_group == $filter->filter_group) {
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter);
      } else {
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter, $filter->filter_group);
      }
      // to remember filter_group of this iteration
      $last_filter_group = $filter->filter_group;
    }

    // Print footer section and close HTML form
    $output .= '<div><button type="submit" id="filter-submit" name="filter-submit" value="' . esc_html__('Search', 'pivot') . '"class="btn btn-lg form-submit" style="background-color:#f5f5f5;"><i class="fas fa-search"></i> ' . esc_html__('Search', 'pivot') . '</button></div>'
      . '</div>'
      . '</form>'
      . '</section>';

    return $output;
  }
}

function pivot_add_filters() {
  global $wp_query;
  if (isset($wp_query->query['pagename'])) {
    $query_page = $wp_query->query['pagename'];
  } else {
    if (isset($wp_query->query['name'])) {
      $query_page = $wp_query->query['name'];
    }
  }

  if (isset($query_page)) {
    $pivot_page = pivot_get_page_path($query_page);
  } else {
    $pivot_page = pivot_get_page_path(key($wp_query->query));
  }

  if (isset($pivot_page->id) && $pivot_page->id != null) {
    pivot_reset_filters($pivot_page->id);
    // Get filters attach to current page
    $filters = pivot_get_filters($pivot_page->id);
    if (empty($filters)) {
      return;
    }

    $lang = substr(get_locale(), 0, 2);
    // Print head section and HTML Form
    $output = '<section id="block-pivot-filters" class="block block-pivot block-pivot-filter clearfix">'
      . '<form action="' . get_site_url() . '/' . (($lang == 'fr') ? '' : $lang . '/') . $pivot_page->path . '" method="post" id="pivot-filter-form" accept-charset="UTF-8">'
      . '<div  id="edit-filter-body">';

    foreach ($filters as $filter) {
      // if not first iteration and filter is member of a group already inserted, we do not recreate this group
      if (isset($last_filter_group) && $last_filter_group == $filter->filter_group) {
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter);
      } else {
        $output .= pivot_add_filter_to_form($pivot_page->id, $filter, $filter->filter_group);
      }
      // to remember filter_group of this iteration
      $last_filter_group = $filter->filter_group;
    }

    // Print footer section and close HTML form
    $output .= '</div>'
      . '<div class="row mt-2 filter-buttons">'
      . '<div class="col-xl-7 col-12">'
      . '<button type="submit" id="filter-submit" name="filter-submit" value="' . esc_html__('Search', 'pivot') . '" class="btn btn-lg btn-block form-submit text-white" style="background-color:#555555;"><i class="fas fa-search"></i> ' . esc_html__('Search', 'pivot') . '</button>'
      . '</div>'
      . '<div class="col-xl-5 col-12">'
      . '<button type="submit" id="filter-reset" name="filter-reset" value="' . esc_html__('Reset', 'pivot') . '"class="btn text-dark btn-lg btn-block form-submit" style="background-color:#f5f5f5;"><i class="fas fa-redo-alt"></i> ' . esc_html__('Reset', 'pivot') . '</button>'
      . '</div>'
      . '</div>'
      . '</form>'
      . '</section>';

    return $output;
  }
}

function pivot_reset_filters($page_id) {
  // If filter form is well submited
  if (isset($_POST['filter-submit'])) {
    // Unset everything on filters
    pivot_state_reset_all_filters();

    $filters = array();
    // Loop on each parameters
    foreach ($_POST as $key => $value) {
      // Except 'op' and 'filter-submit' parameters
      if ($key === 'op' || $key === 'filter-submit' || $key === '_wpnonce' || $key === '_wp_http_referer') {
        continue;
      }
      if (empty($value)) {
        continue;
      }
      // Filter ids are the keys; anything else in the POST body is not a filter.
      if (!is_numeric($key)) {
        continue;
      }
      $value = is_array($value) ? array_map('sanitize_text_field', wp_unslash($value)) : sanitize_text_field(wp_unslash($value));
      // A checked checkbox posts "on"; store it as a boolean.
      $filters[absint($key)] = ($value === 'on') ? TRUE : $value;
    }

    pivot_state_set_filters($page_id, $filters);
  } else {
    if (isset($_POST['filter-reset'])) {
      pivot_state_set_filters($page_id, array());
    }
  }
}

/**
 *
 * @param string $filter_name Will be used in class or id in html content
 * @param string $filter_title Will be used in front-end
 * @param string $urn Pivot URN of the field.
 * @param string $operator exist/in
 * @return string HTML output (div containing filter)
 */
function pivot_add_filter_to_form($page_id, $filter, $group = NULL) {
  $output = '';

  if (isset($group) && !empty($group)) {
    $output .= '<div class="filter-group text-uppercase font-weight-bolder p-2 mb-2 mt-2 bg-light">' . esc_html(__($group, 'pivot')) . '</div>';
  }

  $title = pivot_get_filter_title($filter);
  $value = pivot_state_get_filter_value($page_id, $filter->id);

  // Booleans, Types, Values and the "online booking" flag all render the same
  // checkbox; they used to be four copies of the same markup.
  $renders_as_checkbox = in_array($filter->type, array('Boolean', 'Type', 'Value'), true)
    || ($filter->type === 'String' && $filter->urn === 'urn:fld:idorc');

  if ($renders_as_checkbox) {
    return $output . pivot_render_filter_checkbox($filter, $title, $value !== null);
  }

  if ($filter->type === 'String' && $filter->urn === 'urn:fld:adrcom') {
    return $output . pivot_render_filter_field(
      $filter,
      $title,
      '<select id="edit-' . esc_attr($filter->filter_name) . '" class="w-50" name="' . esc_attr($filter->id) . '">'
        . _get_commune_from_pivot('mdt', get_option('pivot_mdt'), $value)
        . '</select>',
      'form-type-select select'
    );
  }

  switch ($filter->type) {
    case 'Date':
      $input_type = 'date';
      break;
    case 'UInt':
      $input_type = 'number';
      break;
    default:
      $input_type = 'text';
      break;
  }

  $attributes = ($input_type === 'number') ? ' min="1" max="1000"' : '';
  $input = '<input type="' . $input_type . '" class="w-50" id="edit-' . esc_attr($filter->filter_name) . '"'
    . ' name="' . esc_attr($filter->id) . '"' . $attributes
    . ' placeholder="' . esc_attr($title) . '"'
    . ' value="' . esc_attr($value === null ? '' : $value) . '">';

  return $output . pivot_render_filter_field($filter, $title, $input);
}

/**
 * Title of a filter in the visitor's language.
 *
 * WPML string translation wins when the site defines one; otherwise the translation
 * columns filled from Pivot are used, and the thesaurus label is the last resort.
 *
 * @param Object $filter
 * @return string
 */
function pivot_get_filter_title($filter) {
  $lang = substr(get_locale(), 0, 2);

  if ($lang === 'fr') {
    return $filter->filter_title;
  }

  // Check if filter title is translated in WPML
  if ($filter->filter_title != __($filter->filter_title, 'pivot')) {
    return __($filter->filter_title, 'pivot');
  }

  $columns = array(
    'nl' => 'filter_title_nl',
    'en' => 'filter_title_en',
    'de' => 'filter_title_de',
  );
  $translated = isset($columns[$lang]) ? $filter->{$columns[$lang]} : $filter->filter_title;

  if (empty($translated)) {
    // If Pivot translation is empty, fall back on the thesaurus label
    return _get_urn_documentation($filter->urn);
  }

  return $translated;
}

/**
 * Checkbox rendering shared by the Boolean, Type, Value and idorc filters.
 *
 * @param Object $filter
 * @param string $title
 * @param bool $checked
 * @return string
 */
function pivot_render_filter_checkbox($filter, $title, $checked) {
  return '<div class="pl-2 form-item form-item-' . esc_attr($filter->filter_name) . '">'
    . '<label title="" data-toggle="tooltip" class="control-label" for="edit-' . esc_attr($filter->filter_name) . '"'
    . ' data-original-title="' . esc_attr(sprintf(__('Filter on %s', 'pivot'), $title)) . '">'
    . '<input type="checkbox" id="edit-' . esc_attr($filter->filter_name) . '" name="' . esc_attr($filter->id) . '" class="form-checkbox"'
    . checked($checked, true, false) . '> '
    . esc_html($title)
    . '</label>'
    . '</div>';
}

/**
 * Labelled field rendering shared by the text, number, date and select filters.
 *
 * @param Object $filter
 * @param string $title
 * @param string $input Already-escaped input markup.
 * @param string $extra_class
 * @return string
 */
function pivot_render_filter_field($filter, $title, $input, $extra_class = '') {
  return '<div class="pl-2 form-item form-item-' . esc_attr($filter->filter_name) . ' ' . esc_attr($extra_class) . '">'
    . '<label title="' . esc_attr($title) . '" data-toggle="tooltip" class="w-50 control-label" for="edit-' . esc_attr($filter->filter_name) . '"'
    . ' data-original-title="' . esc_attr(sprintf(__('Filter on %s', 'pivot'), $title)) . '">'
    . esc_html($title)
    . '</label>'
    . $input
    . '</div>';
}
