;
(function ($, google) {
    "use strict";

    $(document).ready(function () {
        var $eventPage = $('#events'),
            lat = $eventPage.data('initial-lat'),
            long = $eventPage.data('initial-long');

        var location = new google.maps.LatLng(lat, long);
        var options = {
            zoom: 18,
            center: location,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map($('#map-canvas')[0], options);

        var marker = new google.maps.Marker({
            position: location,
            map: map,
            title: ''
        });

        // try reverse geocoding to detect location by given coordinates
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'latLng': location}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var bubble = new google.maps.InfoWindow()
                bubble.setContent(results[0].formatted_address);
                bubble.open(map, marker);
            }
        });
    });
})(jQuery, google);
