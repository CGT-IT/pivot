(function($) {

  $().ready(function(){
    // Get vars
    var typeofr = $('#edit-pivot-typeofr');
    
    // Action on load
    autocomplete_id_type();
    
    // On change on "Sort mode" dropdown
    typeofr.change(function(){
      autocomplete_id_type();
    });
    
    function autocomplete_id_type(){
      if($.isNumeric(typeofr.find(":selected").val())){
        $('#edit-pivot-type-id').val(typeofr.find(":selected").val());
        $('#edit-pivot-type').val(typeofr.find(":selected").text());
      }
    }
    
  });

})(jQuery);