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
      L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
        attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> <strong><a href="https://www.mapbox.com/map-feedback/" target="_blank">Improve this map</a></strong>',
        tileSize: 512,
        maxZoom: 18,
        zoomOffset: -1,
        id: 'mdegembe/ckvmftkrgn4ap14o8kfgv6z2z',
        accessToken: window._pivot_mapbox_token
      }).addTo(routeMap);

      var colors = ['red', 'purple'];
      new L.GPX(gpx, {async: true, 
          marker_options: {
            wptIconUrls: null,
            startIconUrl: 'https://www.destinationcondroz.be/wp-content/uploads/2021/02/pin-icon-start.png',
            endIconUrl: null,
            shadowUrl: null
          },
          polyline_options: { 
            color: 'black' 
          }
        }).on('loaded', function(e) {
        // Auto center and zoom based on route
        routeMap.fitBounds(e.target.getBounds());
        
          e.target.addTo(routeMap);
      });
      
      new L.LatLngBounds(arrayOfLatLngs);

      routeMap.scrollWheelZoom.disable();
    }
    
  });
      
})(jQuery);