/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function($) {

  $().ready(function(){

    $('[id^=cgt-table-search-paging]').DataTable();

    var isInIFrame = (window.location != window.parent.location);
    if(isInIFrame==true){
        if($('.elementor-editor-active').length == 0){
            $("#header").hide();
            $(".footer-elementor").hide();
            $("#wpadminbar").hide();
            $("h1").hide();
        }
    }

  });

})(jQuery);