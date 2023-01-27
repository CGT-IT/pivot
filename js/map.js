(function($) {

  $().ready(function(){

    if($('#maparea').length){
      if($("#carte").length){
        $("#carte").click(function() {
          $('#maparea').toggleClass("col-9");
          $("#carte").toggleClass("fa-map-marked-alt");
          $("#carte").toggleClass("fa-list");
          $('#offers-area').toggleClass("col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-3");
          $('#offers-area').toggleClass("col-12");
          $('#offers-area').toggleClass("pivot-offer-list");
          $('.offers-area-col').each(function(){
            $(this).toggleClass("col-12");
            $(this).toggleClass("col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12");
          });
          
          if($("#maparea.col-9").length){
            map_call();
          }
        });
        if($("#maparea.col-9").length){
          map_call();
        }
      }
    }
  });
    
function map_call(){
  // Init map
  var pivotMap = L.map('mapid');
  // Init array of point (usefull to center the map)
  var arrayOfLatLngs = [];

  // Set layer to the map
  L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> <strong><a href="https://www.mapbox.com/map-feedback/" target="_blank">Improve this map</a></strong>',
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
    // Get latitude and longitude. Parse it to float as it is text at beginning and round to 2 decimal
    var latitude = parseFloat($(this).find('.pivot-latitude').text()).toFixed(2);
    var longitude = parseFloat($(this).find('.pivot-longitude').text()).toFixed(2);

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