(function($) {

  $().ready(function(){

    // URL of GPX file
    var gpx = 'https://pivotwebstg.tourismewallonie.be/PivotWeb-3.1/media/'+$('#gpx-file-id').text();

    // Init map
    var routeMap = L.map('gpx-map');

    // Set layer to the map
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
      attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
      maxZoom: 18,
      id: 'mapbox.streets',
      accessToken: window._pivot_mapbox_token
    }).addTo(routeMap);

    new L.GPX(gpx, {async: true,}).on('loaded', function(e) {
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

    routeMap.scrollWheelZoom.disable();

    // Remove markers
    $('#gpx-map .leaflet-marker-pane').hide();
    $('#gpx-map .leaflet-shadow-pane').hide();
    
  });
      
})(jQuery);