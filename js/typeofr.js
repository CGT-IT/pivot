(function($) {

  $().ready(function(){
    // Get vars
    var typeofr = $('#edit-pivot-typeofr');
    var category = $('#edit-pivot-parent');
    var submit_button = $('#pivot_add_type');
    
    // Hide submit button
    submit_button.hide();
    
    // Action on load
    autocomplete_id_type();
    
    // On change on "Sort mode" dropdown
    typeofr.change(function(){
      autocomplete_id_type();
    });

    // On change on "Sort mode" dropdown
    category.keyup(function(){
      if($(this).val() !== ''){
        submit_button.show();
      }else{
        submit_button.hide();      
      }
    });
    
    category.change(function(){
      $(this).val($(this).val().normalize('NFD').replace(/[\u0300-\u036f]/g, ""));
      $(this).val($(this).val().replace(/[^a-z]+/g, ""));
    });
    
    function autocomplete_id_type(){
      if($.isNumeric(typeofr.find(":selected").val())){
        $('#edit-pivot-type-id').val(typeofr.find(":selected").val());
        $('#edit-pivot-type').val(typeofr.find(":selected").text());
      }
    }
    
  });

})(jQuery);