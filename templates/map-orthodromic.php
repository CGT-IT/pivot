<?php $field_params['radius'] = 10.0; ?>
<?php $field_params['idIns'] = $args; ?>
<?php $field_params['criterafield'] = TRUE; ?>
<?php $field_params['filters']['status']['name'] = 'urn:fld:etatedit'; ?>
<?php $field_params['filters']['status']['operator'] = 'equal'; ?>
<?php $field_params['filters']['status']['searched_value'][] = 'urn:val:etatedit:30'; ?>

<!--Include leaflet css for map-->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css"
      integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA=="
      crossorigin=""/>
<!--Include leaflet js for map-->
<script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js"
        integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg=="
crossorigin=""></script>

<div class="col-12 d-none d-md-block">
    <ul class="nav nav-pills nav-justified bg-secondary m-0" id="myTab" role="tablist">
        <li class="nav-item"><a class="nav-link active p-3 text-white" id="tab-1" data-toggle="tab" href="#tab-panel-1" role="tab" aria-controls="tab-panel-1" aria-selected="true"><?php echo __('Se loger', 'pivot'); ?></a></li>
        <li class="nav-item"><a class="nav-link p-3 text-white" id="tab-2" data-toggle="tab" href="#tab-panel-2" role="tab" aria-controls="tab-panel-2" aria-selected="false"><?php echo __('Se balader', 'pivot'); ?></a></li>
        <li class="nav-item"><a class="nav-link p-3 text-white" id="tab-3" data-toggle="tab" href="#tab-panel-3" role="tab" aria-controls="tab-panel-3" aria-selected="false"><?php echo __('Découvrir', 'pivot'); ?></a></li>
        <li class="nav-item"><a class="nav-link p-3 text-white" id="tab-4" data-toggle="tab" href="#tab-panel-4" role="tab" aria-controls="tab-panel-4" aria-selected="false"><?php echo __('Savourer', 'pivot'); ?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active " id="tab-panel-1" role="tabpanel" aria-labelledby="tab-1">
            <?php $field_params['filters']['typeofr'] = array(); ?>
            <?php $field_params['filters']['typeofr']['name'] = 'urn:fld:typeofr'; ?>
            <?php $field_params['filters']['typeofr']['operator'] = 'in'; ?>
            <?php $field_params['filters']['typeofr']['searched_value'] = array(1, 2, 3, 4, 5, 6, 7, 270); ?>
            <?php $xml_query = _xml_query_construction(NULL, $field_params); ?>
            <?php $offers = pivot_construct_output('shortcode', 15, $xml_query, null, 1); ?>
            <?php foreach ($offers as $offre_radius): ?>
              <?php $type = pivot_get_offer_type(null, $offre_radius->typeOffre->label->value->__toString()); ?>
              <?php $template_name = 'pivot-' . $type->parent . '-details-part-template'; ?>
              <?php $offre_radius->path = 'details'; ?>
              <?php print pivot_template($template_name, $offre_radius); ?>
            <?php endforeach; ?>
        </div>
        <div class="tab-pane" id="tab-panel-2" role="tabpanel" aria-labelledby="tab-2">
            <?php $field_params['filters']['typeofr'] = array(); ?>
            <?php $field_params['filters']['typeofr']['name'] = 'urn:fld:typeofr'; ?>
            <?php $field_params['filters']['typeofr']['operator'] = 'in'; ?>
            <?php $field_params['filters']['typeofr']['searched_value'][] = 8; ?>
            <?php $xml_query = _xml_query_construction(NULL, $field_params); ?>
            <?php $offers = pivot_construct_output('shortcode', 15, $xml_query, null, 1); ?>
            <?php foreach ($offers as $offre_radius): ?>
              <?php $type = pivot_get_offer_type(null, $offre_radius->typeOffre->label->value->__toString()); ?>
              <?php $template_name = 'pivot-' . $type->parent . '-details-part-template'; ?>
              <?php $offre_radius->path = 'details'; ?>
              <?php print pivot_template($template_name, $offre_radius); ?>
            <?php endforeach; ?>
        </div>

        <div class="tab-pane" id="tab-panel-3" role="tabpanel" aria-labelledby="tab-3">
            <?php $field_params['filters']['typeofr'] = array(); ?>
            <?php $field_params['filters']['typeofr']['name'] = 'urn:fld:typeofr'; ?>
            <?php $field_params['filters']['typeofr']['operator'] = 'in'; ?>
            <?php $field_params['filters']['typeofr']['searched_value'] = array(11, 269); ?>
            <?php $xml_query = _xml_query_construction(NULL, $field_params); ?>
            <?php $offers = pivot_construct_output('shortcode', 15, $xml_query, null, 1); ?>
            <?php foreach ($offers as $offre_radius): ?>
              <?php $type = pivot_get_offer_type(null, $offre_radius->typeOffre->label->value->__toString()); ?>
              <?php $template_name = 'pivot-' . $type->parent . '-details-part-template'; ?>
              <?php $offre_radius->path = 'details'; ?>
              <?php print pivot_template($template_name, $offre_radius); ?>
            <?php endforeach; ?>
        </div>

        <div class="tab-pane" id="tab-panel-4" role="tabpanel" aria-labelledby="tab-4">
            <?php $field_params['filters']['typeofr'] = array(); ?>
            <?php $field_params['filters']['typeofr']['name'] = 'urn:fld:typeofr'; ?>
            <?php $field_params['filters']['typeofr']['operator'] = 'in'; ?>
            <?php $field_params['filters']['typeofr']['searched_value'][] = 261; ?>
            <?php $xml_query = _xml_query_construction(NULL, $field_params); ?>
            <?php $offers = pivot_construct_output('shortcode', 15, $xml_query, null, 1); ?>
            <?php foreach ($offers as $offre_radius): ?>
              <?php $type = pivot_get_offer_type(null, $offre_radius->typeOffre->label->value->__toString()); ?>
              <?php $template_name = 'pivot-' . $type->parent . '-details-part-template'; ?>
              <?php $offre_radius->path = 'details'; ?>
              <?php print pivot_template($template_name, $offre_radius); ?>
            <?php endforeach; ?>
        </div>

    </div>
    <!--Create Map element-->
    <div id="mapid" style="height: 500px; width: 100%;"></div>
    <script src=<?php print MY_PLUGIN_URL . "js/maporthodromic.js" ?>></script>

</div>