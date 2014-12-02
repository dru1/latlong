<?php

$address = trim(filter_input(INPUT_POST, "address"));

// [-90, 90]
$lat = filter_input(INPUT_POST, "lat", FILTER_SANITIZE_NUMBER_FLOAT);
if (!$lat) {
    $lat = rand(-90, 90);
}

// [-180, 180] East or West
$long = filter_input(INPUT_POST, "lng", FILTER_SANITIZE_NUMBER_FLOAT);
if (!$long) {
    $long = rand(-180, 180);
}
?>
<html>
    <head>
        <title>whats-on-the-other-side.org</title>
        <script src="//maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script>
        <style type="text/css">
            body, input, form, button, select { font-family: sans-serif; font-size: 1em; }
            #map_canvas { width: 800px; height: 600px; float: left; margin: 0 20px 0 }
        </style>
    </head>
    <body>
         <div id="map_canvas"></div>
        <form method="post">
            Address: <input type="text" id="address" name="address" value="<?= $address ?>" /><br/>
            <input type="button" onclick="codeAddress()" value="Get me the coords!"/><br/><br/>
            Lat: <input type="text"  id="lat" name="lat" value="<?= $lat ?>" /><br/>
            Long: <input type="text"  id="lng" name="lng" value="<?= $long ?>" /><br/>
            <input type="button" onclick="inverseCodeLatLng()" value="Get me to the other SIDE!"/>
        </form>
       
        <script type="text/javascript">
            var geocoder;
            var map;
            var infowindow = new google.maps.InfoWindow();
            var marker;
            
            function inverseCodeLatLng() {
                var coords = [ parseFloat(document.getElementById("lat").value), parseFloat(document.getElementById("lng").value) ];
                var invcoords = inverseLatLng(coords[0], coords[1]);
                document.getElementById("lat").value = invcoords[0];
                document.getElementById("lng").value = invcoords[1];
                codeLatLng(invcoords[0], invcoords[1]);
            }
            
            function inverseLatLng(lat, lng) {
                var inv_lat = lat > 0 ? lat - 90 : lat + 90;
                var inv_lng = lng > 0 ? lng - 180 : lng + 180;
                return [ inv_lat, inv_lng ];
            }
            
            function initialize() {
                geocoder = new google.maps.Geocoder();
                var coords = [ parseFloat(document.getElementById("lat").value), parseFloat(document.getElementById("lng").value) ];
                var latlng = new google.maps.LatLng(coords[0], coords[1]);
                var mapOptions = {
                    zoom: 7,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
                marker = new google.maps.Marker({
                    position: latlng,
                    map: map
                });
                codeLatLng(coords[0], coords[1]);
                
                google.maps.event.addListener(map, 'click', function(event) {
                    var newcenter = event.latLng;
                    document.getElementById("lat").value = newcenter.lat();
                    document.getElementById("lng").value = newcenter.lng();
                    codeLatLng(newcenter.lat(), newcenter.lng());
                });
                
                google.maps.event.addListener(map, 'center_changed', function() {
                    var newcenter = map.getCenter();
                    document.getElementById("lat").value = newcenter.lat();
                    document.getElementById("lng").value = newcenter.lng();
                });
            
                google.maps.event.addListener(marker, 'click', function() {
                    map.setZoom(8);
                    map.setCenter(marker.getPosition());
                });
            
            
            }
            
            function codeAddress() {
                var address = document.getElementById("address").value;
                geocoder.geocode({'address': address}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var newcenter = results[0].geometry.location;
                        document.getElementById("lat").value = newcenter.lat();
                        document.getElementById("lng").value = newcenter.lng();
                        codeLatLng(newcenter.lat(), newcenter.lng());
                    }
                });
            }
            
            function codeLatLng(lat, lng) {
                var latlng = new google.maps.LatLng(lat, lng);
                geocoder.geocode({'latLng': latlng}, function(results, status) {
                    map.setCenter(latlng);
                    marker.setPosition(latlng);
                    
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[1]) {
                            infowindow.setContent(results[1].formatted_address);
                        }
                    } else {
                        infowindow.setContent("Damn, there is nothing here...");
                    }
                    infowindow.open(map, marker);
                });
            }
            
            google.maps.event.addDomListener(window, 'load', initialize);
            
        </script>
    </body>
</html>