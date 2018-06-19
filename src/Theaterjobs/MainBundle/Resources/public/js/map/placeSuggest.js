function initialize() {
    let input = document.getElementById('pac-input');
    let autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener('place_changed', function () {
        let geo = autocomplete.getPlace().geometry;

        if (typeof encodeGeoHash !== 'undefined') {
            let lat = geo.location.lat();
            let lng = geo.location.lng();
            $('#location').val(encodeGeoHash(lat, lng));
        }
    });
}