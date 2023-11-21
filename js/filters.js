(function($) {

  $().ready(function(){

    // When URN is empty (In creation case)
    // It will be loaded when URN is known
    if($('#edit-pivot-urn').val().length === 0){
      // we hide "filter title" and "operator")
      $('#filter-urn-infos').hide();
    }
    
    // Default ajax call and actions on load
    $('#form-item-pivot-filter-typeofr').hide();
    pivot_ajax_call_on_load();
    
    // On click on "Load URN Infos" button
    $('#load-urn-info').click(function(){
      $('#edit-pivot-operator option').show();
      // Show URN Infos
      $('#filter-urn-infos').show();
      $('#form-item-pivot-filter-typeofr').hide();
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
            if(data.spec[0].label[i].value !== 'Accueil'){
                switch(data.spec[0].label[i].lang){
                    case 'fr':
                        $('#edit-pivot-filter-title').val(data.spec[0].label[i].value);
                        break;
                    case 'nl':
                        $('#edit-pivot-filter-title-nl').val(data.spec[0].label[i].value);
                        break;
                    case 'en':
                        $('#edit-pivot-filter-title-en').val(data.spec[0].label[i].value);
                        break;
                    case 'de':
                        $('#edit-pivot-filter-title-de').val(data.spec[0].label[i].value);
                        break;
                }
                $('#filter-urn-infos').show();
            }
        }
        switch_decision(data);
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
        switch_decision(data);
      }
    });
  }
  
  function switch_decision(data){
    switch(data.spec[0].type){
      case 'Boolean':
        $('#edit-pivot-operator').val('exist');
        $('#edit-pivot-operator').prop('disabled', true);
        break;
      case 'Type':
      case 'Value':
        $('#edit-pivot-operator').val('in');
        $('#edit-pivot-operator').prop('disabled', true);
        break;  
      case 'SFloat':
      case 'UFloat':
      case 'UInt':
        $('#edit-pivot-operator option[value=exist]').hide();
        $('#edit-pivot-operator option[value=like]').hide();
        $('#edit-pivot-operator option[value=between]').hide();
        $('#edit-pivot-operator option[value=in]').hide();
        break;
      case 'Date':
        $('#edit-pivot-operator option[value=exist]').hide();
        $('#edit-pivot-operator option[value=like]').hide();
        $('#edit-pivot-operator option[value=in]').hide();
        break;
      case 'Choice':
      case 'MultiChoice':
      case 'Object':
      case 'Panel':
      case 'Type de champ':
      case 'HMultiChoice':
      case 'Subcategory':
      case 'Category':
        $('#filter-urn-infos').hide();
        $('#edit-pivot-urn').val('');
        $('#edit-pivot-filter-title').val('');
        alert("Il n'est pas possible d'ajouter les URN de ce type: "+data.spec[0].type);
        break;
      default:
        $('#edit-pivot-operator option[value=exist]').hide();
        $('#edit-pivot-operator option[value=greaterequal]').hide();
        $('#edit-pivot-operator option[value=between]').hide();
        $('#edit-pivot-operator option[value=in]').hide();
        break;
    }
  }

})(jQuery);