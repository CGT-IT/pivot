(function($) {

  $().ready(function(){
    
    // Get vars
    var sortMode = $('#edit-pivot-sortMode');
    var sortField = $('.form-item-pivot-sortField');
    
    // Action on load
    show_hide_sortField();
    
    // On change on "Sort mode" dropdown
    sortMode.change(function(){
      show_hide_sortField();
    });
    
    function show_hide_sortField(){
      if(sortMode.find(":selected").val() == 'ASC' || sortMode.find(":selected").val() == 'DESC'){
        sortField.show();
      }else{
        // Hide div and set input to empty
        sortField.hide();
        $('#edit-pivot-sortField').val('');
      }
    }
    
  });

})(jQuery);