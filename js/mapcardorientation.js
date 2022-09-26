(function($) {

  $().ready(function(){

    if($('#maparea').length){
      if($("#carte").length){
        $("#carte").click(function() {
          let mapNbCol = "col-"+$('#maparea').data("nb-col");
          $('#maparea').toggleClass(mapNbCol);
          $("#carte").toggleClass("fa-map-marked-alt");
          $("#carte").toggleClass("fa-list");
          let offerNbCol = 12 - $('#maparea').data("nb-col");
          $('#offers-area').toggleClass("col-xs-12 col-sm-12 col-md-"+offerNbCol+" col-lg-"+offerNbCol+" col-xl-"+offerNbCol);
          $('#offers-area').toggleClass("col-12");
          $('#offers-area').toggleClass("pivot-offer-list");
          $('.offers-area-col').each(function(){
            if($(this).hasClass('nb-col-2')){
              $(this).toggleClass("col-12");
              $(this).toggleClass("col-xl-6 col-lg-6 col-md-6 col-sm-6 col-xs-12 col-12");  
            }
            if($(this).hasClass('nb-col-3')){
              $(this).toggleClass("col-12");
              $(this).toggleClass("col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12 col-12");  
            }
            if($(this).hasClass('nb-col-4')){
              $(this).toggleClass("col-12");
              $(this).toggleClass("col-xl-3 col-lg-4 col-md-6 col-sm-6 col-xs-12 col-12");  
            }
            if($(this).hasClass('nb-col-5')){
              $(this).toggleClass("col-12");
              $(this).toggleClass("w-xl-20 w-lg-20 w-md-20 w-sm-20 w-20 col-12");  
            }
            if($(this).hasClass('nb-col-6')){
              $(this).toggleClass("col-12");
              $(this).toggleClass("col-xl-2 col-lg-2 col-md-6 col-sm-6 col-xs-12 col-12");  
            }
            $(this).find(".card-orientation").toggleClass("card-horizontal");
            $(this).find(".container-img").toggleClass("col-5 p-0 my-auto");
            $(this).find(".card-body").toggleClass("col-7 pt-2 pb-0");
            $(this).find(".title-header").toggle();
            $(this).find(".title-no-header").toggle();
          });
          
          if($("#maparea[class^='col-']").length){
            map_call();
          }
        });
      }
      if($("#maparea[class^='col-']").length){
        map_call();
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
    accessToken: 'pk.eyJ1IjoibWRlZ2VtYmUiLCJhIjoiY2prMHY3Y2prMGE2NDNwazZuMWUxOWV5OCJ9.WkGG_tCifQuDbnVR9kniFw'
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
      var offerTitle = $(this).find('.title-header').text();
      var contentString = "<div class='card' style='width: 13rem;'>"
                +"<img src='https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/"+$(this).find('.pivot-code-cgt').text()+";w=193;h=128' class='card-img-top'>"
                +"<div class='card-body text-center'>"
                  +"<a target='_blank' href='"+$(this).find('a').attr("href")+"'><p class='card-title'>"+offerTitle+"</p></a>"
                +"</div>"
            +"</div>";

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