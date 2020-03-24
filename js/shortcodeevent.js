(function($) {

  $().ready(function(){
    
    // Init copy/paste (clipboard) button
    new ClipboardJS('#clipboard-btn');
    
    // we hide "filter value" and "operator")
    $('#value-date1-infos').hide();
    $('#value-date2-infos').hide();
    
    // Show linked fields if option change
    $('#edit-pivot-date1').change(function(){
      $('#value-date1-infos').show();
    });
    $('#edit-pivot-date2').change(function(){
      $('#value-date2-infos').show();
    });
    
    // Build shortcode when click on button
    $('#build-shortcode-event').click(function(){
      // Init vars
      var query = $('#edit-pivot-query');
      var nbOffers = $('#edit-pivot-nb-offers');
      var nbCol = $('#edit-pivot-nb-col');
      var date1 = $('#edit-pivot-date1');
      var operator1 = $('#edit-pivot-operator1');
      var value1 = $('#edit-pivot-value1');
      var format1 = $('#edit-pivot-format1');
      var date2 = $('#edit-pivot-date2');
      var operator2 = $('#edit-pivot-operator2');
      var value2 = $('#edit-pivot-value2');
      var format2 = $('#edit-pivot-format2');
      var sortMode = $('#edit-pivot-sortMode');
      var sortField = $('#edit-pivot-sortField');
      
      // Init shortcode string
      var shortcode = "[pivot_shortcode_event ";
      
      // If query is not empty
      if(query.val() != ''){
        // Set border to default
        query.css({"border": "1px solid #ddd"});
        // add query attribute to shortcode
        shortcode += "query='"+query.val()+"' ";
        if(nbOffers.val() != ''){
          shortcode += "nboffers='"+nbOffers.val()+"' ";
        }
        if(nbCol.val() != ''){
          shortcode += "nbcol='"+nbCol.val()+"' ";
        }
        if(date1.val() != ''){
          shortcode += "date1='"+date1.val()+"' ";
          if(operator1.val() != ''){
            shortcode += "operator1='"+operator1.val()+"' ";
          }
          if(value1.val() != '' && format1.val() != ''){
            shortcode += "value1='+"+value1.val()+" "+format1.val()+"' ";
          }
        }
        if(date2.val() != ''){
          shortcode += "date2='"+date2.val()+"' ";
          if(operator2.val() != ''){
            shortcode += "operator2='"+operator2.val()+"' ";
          }
          if(value2.val() != '' && format2.val() != ''){
            shortcode += "value2='+"+value2.val()+" "+format2.val()+"' ";
          }
        }
        if(sortMode.val() != ''){
          shortcode += "sortMode='"+sortMode.val()+"' ";
          if(sortMode.val() != 'shuffle'){
            if(sortField.val() != ''){
              shortcode += "sortField='"+sortField.val()+"' ";
            }
          }
        }
        shortcode += "]";
        $('#pivot-shortcode-insertion').val(shortcode);
      }else{
        // Set border of query field to red as it is empty
        query.css({"border": "1px solid red"});
      }
      
    });
    
  });

})(jQuery);