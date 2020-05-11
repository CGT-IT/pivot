(function($) {

  $().ready(function(){
    if($('#gpx-map').length){
      // URL of GPX file
      var gpx = 'https://pivotweb.tourismewallonie.be/PivotWeb-3.1/media/'+$('#gpx-file-id').text();

      // Init map
      var routeMap = L.map('gpx-map');
      // Init array of point (usefull to center the map)
      var arrayOfLatLngs = [];

      // Set layer to the map
      L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        maxZoom: 18,
        id: 'mapbox.streets',
        accessToken: window._pivot_mapbox_token
      }).addTo(routeMap);
      
      new L.GPX(gpx, {async: true, marker_options: {startIconUrl: '',endIconUrl: '',shadowUrl: ''}}).on('loaded', function(e) {
        // Auto center and zoom based on route
        routeMap.fitBounds(e.target.getBounds());

        var elevation = e.target.get_elevation_gain();

        if(elevation > 0){
          L.control.elevation({
            theme: "steelblue-theme", //default: lime-theme
            detachedView: true,
            responsiveView: true,
            width: 600,
            height: 300,
            margins: {
              top: 25,
              right: 20,
              bottom: 30,
              left: 50
            },
          }).loadGPX(routeMap, gpx);
        }else{
          $('#elevation-div').hide();
          e.target.addTo(routeMap);
        }
      });
      
      /**
       * For each offer in the list:
       * + set marker with own icon from pivot.
       * + set popup on market and offer's details in it
       */
      $('.pivot-rel-offer').each(function(){
        // Get latitude and longitude. Parse it to float as it is text at beginning and round to 6 decimal
        var latitude = parseFloat($(this).find('.pivot-latitude').text());
        var longitude = parseFloat($(this).find('.pivot-longitude').text());
        var idTypeOffre = $(this).find('.pivot-id-type-offre').text();
        var contentString = '';

        if(latitude && longitude && latitude != 0 && longitude != 0) {
          // Construction of a point
          var point = [latitude,longitude];
          // Get offer details
          var offerTitle = $(this).find('.offer-title').text();
          if(idTypeOffre === '10'){
            contentString = "<div class='card' style='width: 13rem;'>"
                    +"<span class='card-title text-center'><b>"+offerTitle+"</b></span>"
                    +"<div class='card-body'>"
                      +"<p>"+$(this).find('.offer-description').text()+"</p>"
                    +"</div>"
                +"</div>";
          }else{
            contentString = "<div class='card' style='width: 13rem;'>"
                    +"<img src='https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/"+$(this).find('.pivot-code-cgt').text()+";w=193;h=128' class='card-img-top'>"
                    +"<div class='card-body text-center'>"
                      +"<a target='_blank' href='"+$(this).find('a').attr("href")+"'><h6 class='card-title'>"+offerTitle+"</h6></a>"
                    +"</div>"
                +"</div>";
          }

          // Add the point to an array
          arrayOfLatLngs.push(point);

          // Get point icon from Pivot
          var image = 'https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/urn:typ:'+idTypeOffre+';modifier=pin;modifier=ori;w=30';
          var pointIcon = L.icon({
            iconUrl: image,
          });
          // Set marker
          var marker = L.marker(point, {icon: pointIcon}).bindTooltip(offerTitle).addTo(routeMap);
          // Set popup on marker with offer's details in it
          marker.bindPopup(contentString);
        }
      });

      new L.LatLngBounds(arrayOfLatLngs);

      routeMap.scrollWheelZoom.disable();
    }
    
  });
      
})(jQuery);