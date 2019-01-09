(function($) {

  $().ready(function(){
    
    new ClipboardJS('#clipboard-btn');
    
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
    
    $('#build-shortcode').click(function(){
      var query = $('#edit-pivot-query');
      var type = $('#edit-pivot-type');
      var nbOffers = $('#edit-pivot-nb-offers');
      var urn = $('#edit-pivot-urn');
      var operator = $('#edit-pivot-operator');
      var value = $('#edit-pivot-filter-value');
      var shortcode = "[pivot_shortcode ";
      if(query.val() != ''){
        query.css({"border": "1px solid #ddd"});
        shortcode += "query='"+query.val()+"' ";
        if(type.val() != null){
          type.css({"border": "1px solid #ddd"});
          shortcode += "type='"+type.val()+"' ";
          if(nbOffers.val() != ''){
            shortcode += "nboffers='"+nbOffers.val()+"' ";
          }
          if(urn.val() != ''){
            shortcode += "filterurn='"+urn.val()+"' ";
            if(operator.val() != ''){
              shortcode += "operator='"+operator.val()+"' ";
            }
            if(value.val() != ''){
              shortcode += "filtervalue='"+value.val()+"' ";
            }
          }
          shortcode += "]";
          $('#pivot-shortcode-insertion').val(shortcode);
        }else{
          type.css({"border": "1px solid red"});
        }
      }else{
        query.css({"border": "1px solid red"});
      }
      
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
          if(data.spec[0].label[i].lang === 'fr' && data.spec[0].label[i].value !== 'Accueil'){
            // Fill "filter title" with value from Pivot
            $('#edit-pivot-filter-title').val(data.spec[0].label[i].value);
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
        $('.form-item-pivot-filter-value').hide();
        break;
      case 'Type':
      case 'Value':
        $('#edit-pivot-operator').val('in');
        $('#edit-pivot-operator').prop('disabled', true);
        $('.form-item-pivot-filter-value').hide();
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
        $('#filter-urn-infos').hide();
        $('#edit-pivot-urn').val('');
        $('.form-item-pivot-filter-value').val('');
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