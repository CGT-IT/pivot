(function($) {

  $().ready(function(){

    // When URN is empty (In creation case)
    // It will be loaded when URN is known
    if($('#edit-pivot-urn').val().length === 0){
      // we hide "filter title" and "operator")
      $('#filter-urn-infos').hide();
    }
    
    // Default ajax call and actions on load
    pivot_ajax_call_on_load();
    
    // On click on "Load URN Infos" button
    $('#load-urn-info').click(function(){
        $('#edit-pivot-operator option').show();
      // Show URN Infos
      $('#filter-urn-infos').show();
      // Ajax call to Pivot
      pivot_ajax_call();
    });
    
  });

  function pivot_ajax_call(){
    $.ajax({
      type: "GET",
      // Thesaurus service to get infos of a specific URN
      url: "https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/thesaurus/urn/"+$('#edit-pivot-urn').val()+";pretty=true;fmt=json",
      dataType: "json",
      success: function(data) {
        for (i in data.spec[0].label) {
          if(data.spec[0].label[i].lang === 'fr'){
            // Fill "filter title" with value from Pivot
            $('#edit-pivot-filter-title').val(data.spec[0].label[i].value);
          }
        }
        switch(data.spec[0].type){
          case 'Boolean':
          case 'Type':
            $('#edit-pivot-operator').val('exist');
            $('#edit-pivot-operator').prop('disabled', true);
            break;
          case 'SFloat':
          case 'UFloat':
          case 'UInt':
            $('#edit-pivot-operator option[value=exist]').hide();
            $('#edit-pivot-operator option[value=like]').hide();
            $('#edit-pivot-operator option[value=between]').hide();
            break;
          case 'Date':
            $('#edit-pivot-operator option[value=exist]').hide();
            $('#edit-pivot-operator option[value=like]').hide();
            break;
          default:
            $('#edit-pivot-operator option[value=exist]').hide();
            $('#edit-pivot-operator option[value=greaterequal]').hide();
            $('#edit-pivot-operator option[value=between]').hide();
            break;
        }
      }
    });
  }
    
  function pivot_ajax_call_on_load(){
    $.ajax({
      type: "GET",
      // Thesaurus service to get infos of a specific URN
      url: "https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/thesaurus/urn/"+$('#edit-pivot-urn').val()+";pretty=true;fmt=json",
      dataType: "json",
      success: function(data) {
        switch(data.spec[0].type){
          case 'Boolean':
          case 'Type':
            $('#edit-pivot-operator').val('exist');
            $('#edit-pivot-operator').prop('disabled', true);
            break;
          case 'SFloat':
          case 'UFloat':
          case 'UInt':
            $('#edit-pivot-operator option[value=exist]').hide();
            $('#edit-pivot-operator option[value=like]').hide();
            $('#edit-pivot-operator option[value=between]').hide();
            break;
          case 'Date':
            $('#edit-pivot-operator option[value=exist]').hide();
            $('#edit-pivot-operator option[value=like]').hide();
            break;
          default:
            $('#edit-pivot-operator option[value=exist]').hide();
            $('#edit-pivot-operator option[value=greaterequal]').hide();
            $('#edit-pivot-operator option[value=between]').hide();
            break;
        }
      }
    });
  }

})(jQuery);