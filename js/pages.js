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
    
    // Remove img from pivot pages
    $('#reset_img').click(function() {
      $('#imageUrl').val('');
      $('a.imageUrl').attr("href", '');
      $('a.imageUrl img').attr("src", '');
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