var map;
var marker;
// var lat = 52.52000659999999;
// var lng = 13.404953999999975;
const googleApiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?';

/**
 * Sets an existing map or a new map with location in berlin
 */
function initialize() {
    let input = document.getElementById('pac-input');
    // Prevent submits when present in forms
    $(input).keydown(function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
        }
    });

    let position = null;
    // new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
    //If showing existing location
    if (oldLatLng) {
        position = new google.maps.LatLng(parseFloat(oldLatLng[0]), parseFloat(oldLatLng[1]));
    }
    let opt = {
        zoom: 15,
        center: position,
        scrollwheel: false
    };

    map = new google.maps.Map(document.getElementById("map"), opt);

    //If showing existing location
    if (oldLatLng) {
        setMarker(position);
        updateInput(position)
    }

    $("#currentLocation").click(function () {
        navigator.geolocation.getCurrentPosition(locationFound, locationNotFound);
    });

    let autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);

    autocomplete.addListener('place_changed', function () {
        if (marker) {
            marker.setVisible(false);
        }
        let place = autocomplete.getPlace();
        if (!place.geometry) {
            bootbox({message: trans('bootbox.address.not.found', locale)});
            return;
        }

        // If the place has a geometry, then present it on a map.
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);  // Why 17? Because it looks good.
        }
        setMarker(place.geometry.location);
        updateInput(place.geometry.location);
        marker.setVisible(true);
    });


    function locationNotFound() {
        bootbox.alert({
            message: trans('Address.not.found', locale)
        });
    }

    function locationFound(position) {
        var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        setMarker(location);
        updateInput(location);
    }
}

/**
 * get Info from api
 * updates .map-input, #pac-input, #fullAddress
 * @param location
 */
function updateInput(location) {
    let lat = location.lat();
    let lng = location.lng();

    $.getJSON(googleApiUrl + 'latlng=' + lat + ',' + lng + '&sensor=true&key=' + GOOGLEMAPSAPIKEY).then(function (geo) {
        if (geo.status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
            bootbox.alert({message: 'api.limit'});
            return;
        }
        let city = null, country = null;
        for (let i of geo.results) {
            for (let y of i.address_components) {
                if (y.types[0] === "locality") {
                    city = y.long_name;
                }
                if (y.types[0] === "country") {
                    country = y.long_name;
                }
            }
        }
        if (!city || !country) {
            bootbox.alert({
                message: trans('Address.not.found', locale)
            });
            return;
        }
        $(".map-input").val(lat + ',' + lng);
        $(".map-input-country").val(country);
        $(".map-input-city").val(city);
        $('#pac-input').val(city + ", " + country);
        $('#fullAddress').text(city + ", " + country);
    });

}

/**
 * Sets a marker and update inputs
 * @param location
 */
function setMarker(location) {
    map.setCenter(location);
    if (marker) {
        marker.setMap(null);
    }
    marker = new google.maps.Marker({
        position: location,
        map: map,
        draggable: true,
        title: trans("map.marker.Here", locale)
    });
    google.maps.event.addListener(marker, 'dragend', function () {
        updateInput(this.position);
    });
}

//?
function geoCodeAddress(address) {
    let geocoder = new google.maps.Geocoder();
    geocoder.geocode({'address': address}, function (results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            let latitude = results[0].geometry.location.lat();
            let longitude = results[0].geometry.location.lng();
            let loc = new google.maps.LatLng(parseFloat(latitude), parseFloat(longitude));
            setMarker(loc);
        } else {
            //alert("Request failed.");
        }
    });
}