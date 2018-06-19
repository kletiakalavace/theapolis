removeSaveSearch = (id) => {
    bootbox.confirm({
        title: false,
        message: trans("bootbox.wantToDelete", locale),
        buttons: {
            'confirm': {
                label: trans('bootbox.button.ok', locale)
            },
            'cancel': {
                label: trans('bootbox.button.cancel', locale)
            }
        },
        callback: function (result) {
            if (result) {
                $.ajax({
                    type: 'GET',
                    url: Routing.generate('tj_main_remove_search', {
                        id: id,
                        '_locale': locale,
                        page
                    }),
                    success: function (data) {
                        if (data.success) {
                            $("#saveSearch").html(data);
                            loadLocations();
                        } else {
                            serverError();
                        }
                    },
                });
            }
        }
    })
};

/**
 * Load location name for each saved search
 */
function loadLocations() {
    $('.location').each(function () {
        var latlng = decodeGeoHash($(this).find('span').text());
        const self = $(this);
        getPlaceNameByLatLng(latlng.latitude[0], latlng.longitude[0], function (err, city, country) {
            if (err) {
                self.find('span').text(trans('location.not.found', locale));
                return;
            }
            self.find('span').text(city + ', ' + country);
        });
    });
}


function notifyMeDaily(checkGroupElement) {

    var isChecked = $(checkGroupElement).find("input").is("[checked]");
    var id =  $(checkGroupElement).find("input").data('hash');
    var route  = isChecked ? 'tj_saved_searches_list_uncheck_notify' : 'tj_saved_searches_check_notify';

    $.ajax({
        type: 'GET',
        url: Routing.generate(route, {
            id: id,
            '_locale': locale
        }),
        success: function (data) {
            if (data.success) {

            } else {

            }
        },
    });
}

loadLocations();