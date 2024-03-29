(function($) {

  $().ready(function(){

    if($('#mapid').length){
      map_call();
    }
  });
    
function map_call(){
  // Init map
  var pivotMap = L.map('mapid');
  // Init array of point (usefull to center the map)
  var arrayOfLatLngs = [];

  // Set layer to the map
  L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    tileSize: 512,
    maxZoom: 18,
    zoomOffset: -1,
    id: 'mapbox/streets-v11',
    accessToken: window._pivot_mapbox_token
  }).addTo(pivotMap);

  /**
   * For each offer in the list:
   * + set marker with own icon from pivot.
   * + set popup on market and offer's details in it
   */
  $('.pivot-offer').each(function(){
    // Get latitude and longitude. Parse it to float as it is text at beginning
    var latitude = parseFloat($(this).find('.pivot-latitude').text());
    var longitude = parseFloat($(this).find('.pivot-longitude').text());

    if(latitude && longitude && latitude != 0 && longitude != 0) {
      // Construction of a point
      var point = [latitude,longitude];
      // Get offer details
      var offerTitle = $(this).find('h1').text();

      // Add the point to an array
      arrayOfLatLngs.push(point);

      // Get point icon from Pivot
      var image = 'https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/urn:typ:'+$(this).find('.pivot-id-type-offre:first').text()+';modifier=pin;modifier=ori;w=30';
      var pointIcon = L.icon({
        iconUrl: image,
        iconSize: [32, 32],
        iconAnchor: [16, 32]
      });
      // Set marker
      L.marker(point, {icon: pointIcon}).bindTooltip(offerTitle).addTo(pivotMap);
    }
  });

  var bounds = new L.LatLngBounds(arrayOfLatLngs);
  // Auto center and zoom based on all markers
  pivotMap.fitBounds(bounds);

  // Disable scrool zoom
  pivotMap.scrollWheelZoom.disable();
  pivotMap.setZoom(12);
}

})(jQuery);