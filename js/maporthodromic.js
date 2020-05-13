(function($) {
  $().ready(function(){
    $('.tab-pane .pivot-offer').addClass('d-none');
    $('.tab-pane .offers-area-col').removeClass('mb-3');
    // Init map
    var pivotMap = L.map('mapid');
    // Init array of point (usefull to center the map)
    var arrayOfLatLngs = [];
    // Init MarkerGroup
    var mapMarkers = [];

    // Set layer to the map
    L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
      attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> <strong><a href="https://www.mapbox.com/map-feedback/" target="_blank">Improve this map</a></strong>',
      tileSize: 512,
      maxZoom: 18,
      zoomOffset: -1,
      id: 'mapbox/streets-v11',
      accessToken: 'pk.eyJ1IjoibWRlZ2VtYmUiLCJhIjoiY2prMHY3Y2prMGE2NDNwazZuMWUxOWV5OCJ9.WkGG_tCifQuDbnVR9kniFw'
    }).addTo(pivotMap);

    $('#tab-1').click(function(){
      for(var i = 0; i < mapMarkers.length; i++){
        pivotMap.removeLayer(mapMarkers[i]);
      }
      arrayOfLatLngs = [];
      mapMarkers = [];
      offersProcess(pivotMap, arrayOfLatLngs, mapMarkers, '#tab-panel-1 .pivot-offer');
    });
    $('#tab-2').click(function(){
      for(var i = 0; i < mapMarkers.length; i++){
        pivotMap.removeLayer(mapMarkers[i]);
      }
      arrayOfLatLngs = [];
      mapMarkers = [];
      offersProcess(pivotMap, arrayOfLatLngs, mapMarkers, '#tab-panel-2 .pivot-offer');
    });
    $('#tab-3').click(function(){
      for(var i = 0; i < mapMarkers.length; i++){
        pivotMap.removeLayer(mapMarkers[i]);
      }
      arrayOfLatLngs = [];
      mapMarkers = [];
      offersProcess(pivotMap, arrayOfLatLngs, mapMarkers, '#tab-panel-3 .pivot-offer');
    });
    $('#tab-4').click(function(){
      for(var i = 0; i < mapMarkers.length; i++){
        pivotMap.removeLayer(mapMarkers[i]);
      }
      arrayOfLatLngs = [];
      mapMarkers = [];
      offersProcess(pivotMap, arrayOfLatLngs, mapMarkers, '#tab-panel-4 .pivot-offer');
    });

    offersProcess(pivotMap, arrayOfLatLngs, mapMarkers, '#tab-panel-1 .pivot-offer');
  });
  
  /**
    * For each offer in the list:
    * + set marker with own icon from pivot.
    * + set popup on market and offer's details in it
    */
  function offersProcess(pivotMap, arrayOfLatLngs, mapMarkers, selector){
    $(selector).each(function(){
      // Get latitude and longitude. Parse it to float as it is text at beginning
      var latitude = parseFloat($(this).find('.pivot-latitude').text());
      var longitude = parseFloat($(this).find('.pivot-longitude').text());

      if(latitude && longitude && latitude != 0 && longitude != 0) {
        // Construction of a point
        var point = [latitude,longitude];
        // Get offer details
        var offerTitle = $(this).find('.card-header').text();
        var contentString = $(this).html();

        // Add the point to an array
        arrayOfLatLngs.push(point);

        // Get point icon from Pivot
        var image = 'https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/urn:typ:'+$(this).find('.pivot-id-type-offre').text()+';modifier=pin;modifier=ori;w=30';
        var pointIcon = L.icon({
          iconUrl: image,
        });
        // Set marker
        var marker = L.marker(point, {icon: pointIcon}).bindTooltip(offerTitle).addTo(pivotMap);

        mapMarkers.push(marker);
        // Set popup on marker with offer's details in it
        marker.bindPopup(contentString);
      }
    });
    var bounds = new L.LatLngBounds(arrayOfLatLngs);
    // Auto center and zoom based on all markers
    pivotMap.fitBounds(bounds);
    // Disable scrool zoom
    pivotMap.scrollWheelZoom.disable();
  }
})(jQuery);