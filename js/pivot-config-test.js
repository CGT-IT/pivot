(function($) {

  $().ready(function(){

    // On click on "Load URN Infos" button
    $('#check-pivot-config').click(function(){
      
      $('#pivot-response').empty();
      var uri = $('#edit-pivot-uri').val();
      var key = $('#edit-pivot-key').val();
      
      if(uri == ''){
        alert("Base Uri can't be empty");
      }else{
        if(key == ''){
          alert("WS_KEY can't be empty");
        }else{

          $.ajax({
            type: "GET",
            headers: {'ws_key': key},
            url: uri+"offer/GIT-02-01W2-0001/exists;",
            dataType: "json",
            success: function(data){
              $('#pivot-response').append("<p style='color:green;'>Your config is correct</p>");
            },
            error: function(request, error){
              $('#pivot-response').append("<p style='color:red;'><strong>Error:</strong> something is wrong in your config </p>\n\
                                    <li><strong>Base Uri</strong> should be like <a href='https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/'>https://pivotweb.tourismewallonie.be:443/PivotWeb-3.1/</a></li>\n\
                                    <li><strong>WS_KEY</strong> please check if it's correct</li>");
            }
          });
        }
      }
        
    });
    
  });

})(jQuery);