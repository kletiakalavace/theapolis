// O masterpiece
// 1. Under the .page-wrap class in the future may be added other forms
// 2. Master.js is loaded on every page and this is only for job searches
var listSearchForm = $('.page-wrap form').last();

var html, modalErorr = function (msg) {
    $('.errorModal').remove();
    html = '<div class="alert bg-white errorModal">'
        + '<a href="#" class="close" data-dismiss="alert" aria-label="close"> &times;</a>'
        + '<strong class="text-warning" style="font-size: 18px;font-family: \'Kepler Std\'">'
        + '<i class="fa fa-info" aria-hidden="true"></i> ' + msg + '</strong></div>';
    return html;
};

function resetFile(e) {
    e.wrap('<form>').closest('form').get(0).reset();
    e.unwrap();
}

function getRoundedCanvas(sourceCanvas) {
    var canvas = document.createElement('canvas');
    var context = canvas.getContext('2d');
    var width = sourceCanvas.width;
    var height = sourceCanvas.height;
    canvas.width = width;
    canvas.height = height;
    context.beginPath();
    context.arc(width / 2, height / 2, Math.min(width, height) / 2, 0, 2 * Math.PI);
    context.strokeStyle = 'rgba(0,0,0,0)';
    context.stroke();
    context.clip();
    context.drawImage(sourceCanvas, 0, 0, width, height);
    return canvas;
}

var select2Tags = $('#tags');
var searchPhrase = $('#searchPhrase');
var locationInput = $('#pac-input');
var locationHash = $('#location');
var area = $('#area');

function ajaxSubmit(form, content, splitVal, $this, process) {
    var formUrl;
    $.ajax({
        type: form.attr('method'),
        url: form.attr('action'),
        data: form.serialize(),
        beforeSend: function () {
            formUrl = this.url;
        },
        success: function (data) {
            var dataContent = {
                content: data.html,
                search: searchPhrase.val(),
                location: locationInput.val(),
                locationHash: locationHash.val(),
                area: area.val()
            };
            updateContent(dataContent, searchPhrase, content);
            // Add an item to the history log
            history.pushState(dataContent, document.title, formUrl);
        }
    });
}

function autoSuggestion(form, content, splitVal) {
    searchPhrase.typeahead({
        minLength: 3,
        delay: 1000,
        source: function (query, process) {
            $('#page').val(1);
            ajaxSubmit(form, content, splitVal, this, process);
        }
        , updater: function (item) {
            searchPhrase.val(item);
            form.submit();
            return item;
        },
        matcher: function () {
            return true;
        }
    });

    // Store the initial content so we can revisit it later
    history.replaceState(
        {
            content: content.html(),
            search: searchPhrase.val(),
            location: locationInput.val(),
            locationHash: locationHash.val(),
            area: area.val()
        }
        , document.title, document.location.href);

    // back browser event for pushState
    window.addEventListener('popstate', function (event) {
        updateContent(event.state, searchPhrase, content);
    });
}


function updateContent(data, searchVal, htmlContent) {
    if (data === null)
        return;

    htmlContent.html(data.content);
    searchVal.val(data.search);
    $('body').goTo();
    if (typeof locationHash.val() !== "undefined") {
        locationInput.val(data.location);
        locationHash.val(data.locationHash);
        latlng = decodeGeoHash(locationHash.val());
        filterLocation(latlng.latitude[0], latlng.longitude[0], listSearchForm.selector, data.area);
    }
}

var inArray = function (needle) {
    // Per spec, the way to identify NaN is that it is not equal to itself
    var findNaN = needle !== needle;
    var indexOf;

    if (!findNaN && typeof Array.prototype.indexOf === 'function') {
        indexOf = Array.prototype.indexOf;
    } else {
        indexOf = function (needle) {
            var i = -1, index = -1;

            for (i = 0; i < this.length; i++) {
                var item = this[i];

                if ((findNaN && item !== item) || item === needle) {
                    index = i;
                    break;
                }
            }

            return index;
        };
    }

    return indexOf.call(this, needle) > -1;
};

function between(x, min, max) {
    x = parseFloat(x.toString().replace(/[,]/g, '.'));
    return x >= min && x <= max;
}

function numbersValidation(value) {
    value = value.replace(/\s+/g, '');
    return /^\d+([\.,]\d+)?$/.test(value);
}

function numbersValidationSplit(value) {
    value = value.toString().replace(/\s+/g, '');
    value = value.toString().replace(/[,]/g, '.');
    var fields = value.split('.');
    var returnValue = true;

    for (var key in fields) {
        if (!$.isNumeric(fields[key])) {
            returnValue = false;
            break;
        }
    }
    return returnValue;
}

(function ($) {
    $.fn.goTo = function () {
        $('html, body').animate({
            scrollTop: $(this).offset().top + 'px'
        }, 'fast');
        return this; // for chaining...
    }
})(jQuery);

function createCookie(name, value, hours) {
    if (hours) {
        var date = new Date();
        date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    } else
        var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function tagsAutocomplete(el, inputTerm) {
    el.select2({
        minimumInputLength: 3,
        formatInputTooShort: function () {
            $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
        },
        tags: true,
        cache: true,
        quietMillis: 250,
        tokenSeparators: [','],
        createSearchChoice: function (term) {
            $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
        },
        formatNoMatches: function () {
            return '';
        },
        ajax: {
            url: Routing.generate('tj_organization_get_tags', {'_locale': locale}),
            dataType: 'json',
            data: function (term, page) {
                inputTerm = term;
                return {
                    q: term,
                    page: page // page number
                };
            },
            results: function (data, page) {
                var existingTag;
                // check if the term matches in the data result from the db
                var validCheck = false;
                var more = false;
                data.forEach(function (element) {
                    more = (page * autosuggestion_pagination) < element.total_count;
                });

                el.select2('data').forEach(function (item, key, mapObj) {
                    if (item.id.toString() === inputTerm)
                        existingTag = inputTerm;
                });

                data.forEach(function (item, key, mapObj) {
                    if (item.text.toString() === inputTerm) {
                        validCheck = true;
                    }

                    if (!validCheck) {
                        el.select2('data').forEach(function (item, key, mapObj) {
                            data.splice(data.indexOf(item), 1);
                        });
                    }

                    if (item.text.toString() === existingTag) {
                        data.splice(data.indexOf(item), 1);
                    }
                });

                // if not add at the top of the array new tag
                if (!validCheck) {
                    data.unshift({
                        id: $.trim(inputTerm),
                        text: $.trim(inputTerm) + ' (new tag)'
                    });
                }
                return {
                    results: data,
                    more: more
                };
            }
        },
        // Take default tags from the input value
        initSelection: function (element, callback) {
            var data = [];

            $(splitVal(element.val(), ",")).each(function () {
                data.push({
                    id: this,
                    text: this
                });
            });
            callback(data);
        },
        // Some nice improvements:

        // override message for max tags
        formatSelectionTooBig: function (limit) {
            return inserate.organization.tags['' + locale[1] + ''] + " " + limit;
        }
    });
}

function tagsNewsAutocomplete(el, inputTerm) {
    el.select2({
        minimumInputLength: 0,
        tags: true,
        tokenSeparators: [','],
        cache: true,
        quietMillis: 250,
        maximumSelectionLength: 5,
        createSearchChoice: function (term) {
        },
        formatNoMatches: function () {
            return '';
        },
        ajax: {
            url: Routing.generate('tj_news_get_tags', {'_locale': locale}),
            dataType: 'json',
            data: function (term, page) {
                inputTerm = term;
                return {
                    q: term,
                    page: page // page number
                };
            },
            results: function (data, page) {
                var more = false;
                data.forEach(function (element) {
                    more = (page * autosuggestion_pagination) < element.total_count;
                });
                var existingTagsArray = [];
                // check if the term matches in the data result from the db
                var validCheck = false;

                el.select2('data').forEach(function (item, key, mapObj) {
                    existingTagsArray.push(item.id.toString());
                });

                data.forEach(function (item, key, mapObj) {
                    if (item.text.toString() === inputTerm) {
                        validCheck = true;
                    }

                    if (!validCheck && inputTerm.length > 0) {
                        el.select2('data').forEach(function (item, key, mapObj) {
                            data.splice(data.indexOf(item), 1);
                        });
                    }

                    // if (item.text.toString() === existingTag || $.inArray(item.text.toString(), existingTag) > -1) {
                    if ($.inArray(inputTerm, existingTagsArray) > -1 || $.inArray(item.text.toString(), existingTagsArray) > -1) {
                        data.splice(data.indexOf(item), 1);
                    }
                });

                // if not add at the top of the array new tag
                if (!validCheck && inputTerm.length > 2) {
                    data.unshift({
                        id: $.trim(inputTerm),
                        text: $.trim(inputTerm) + ' (new tag)'
                    });
                }
                return {
                    results: data,
                    more: more
                };
            }
        },
        // Take default tags from the input value
        initSelection: function (element, callback) {
            var data = [];

            $(splitVal(element.val(), ",")).each(function () {
                data.push({
                    id: this,
                    text: this
                });
            });
            callback(data);
        },
        // Some nice improvements:

        // override message for max tags
        formatSelectionTooBig: function (limit) {
            return trans("max.tags.limit", locale);
        }
    });
}

function organizationAutocomplete(el, inputTerm) {

    el.select2({
        tags: true,
        minimumInputLength: 3,
        formatInputTooShort: function () {
            $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
        },
        createSearchChoice: function (term, page) {
            $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
        },
        tokenSeparators: [','],
        cache: true,
        quietMillis: 250,
        ajax: {
            url: Routing.generate('tj_news_get_organizations', {'_locale': locale}),
            dataType: 'json',
            data: function (term, page) {
                inputTerm = term;
                return {
                    q: term,
                    page: page // page number
                };
            },
            results: function (data, page) {
                var existingOrga;
                var validCheck = false;
                var more = false;

                el.select2('data').forEach(function (item, key, mapObj) {
                    console.log(item.id.toString());
                    if (item.id.toString() === inputTerm)
                        existingOrga = inputTerm;
                });

                data.forEach(function (item, key, mapObj) {
                    more = (page * autosuggestion_pagination) < item.total_count;
                    if (item.text.toString() === inputTerm) {
                        validCheck = true;
                    }

                    el.select2('data').forEach(function (item1, key, mapObj) {
                        if (item1.id.toString() == item.text.toString()) {
                            data.splice(data.indexOf(item), 1);
                        }
                    });

                    if (!validCheck) {
                        el.select2('data').forEach(function (item, key, mapObj) {
                            data.splice(data.indexOf(item), 1);
                        });
                    }

                    if (item.text.toString() === existingOrga) {
                        data.splice(data.indexOf(item), 1);
                    }
                });

                return {
                    results: data,
                    more: more
                };
            }
        },
        initSelection: function (element, callback) {
            var data = [];

            $(splitVal(element.val(), ",")).each(function () {
                data.push({
                    id: this,
                    text: this
                });
            });
            callback(data);
        }
    });
}

function usersAutocomplete(el, inputTerm, initSelection) {
    el.select2({
        tags: true,
        minimumInputLength: 3,
        formatInputTooShort: function () {
            $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
        },
        createSearchChoice: function (term, page) {
            $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
        },
        tokenSeparators: [','],
        cache: true,
        quietMillis: 250,
        ajax: {
            url: Routing.generate('tj_news_get_users', {'_locale': locale}),
            dataType: 'json',
            data: function (term, page) {
                inputTerm = term;
                return {
                    q: term,
                    page: page // page number
                };
            },
            results: function (data, page) {
                var more = false;
                data.forEach(function (item) {
                    more = (page * autosuggestion_pagination) < item.total_count;
                });
                var existingOrga;
                var firstInputFound = false;

                el.select2('data').forEach(function (item, key, mapObj) {
                    if (item.text.toString() === inputTerm)
                        existingOrga = inputTerm;
                });

                for (var i = data.length; i--;) {
                    var resultText = data[i].text.toString();

                    if (resultText === inputTerm) {
                        firstInputFound = true;
                    }
                    el.select2('data').forEach(function (existingTag, keyExistingTag, mapObjExistingTags) {
                        var tagFullText = existingTag.text.toString();
                        if (tagFullText === resultText) {
                            data.splice(data[i], 1);
                        }
                    });
                    if (firstInputFound) {
                        el.select2('data').forEach(function (item, key, mapObj) {
                            data.splice(data[i], 1);
                        });
                    }
                    if (resultText === existingOrga) {
                        data.splice(data[i], 1);
                    }
                }

                return {
                    results: data,
                    more: more
                };
            }
        },
        initSelection: function (element, callback) {
            const result = [];
            const data = JSON.parse(initSelection);
            for (let choice of data) {
                result.push({
                    id: choice.id,
                    text: choice.text
                })
            }
            callback(result);
        }
    });
}

function detectBrowser() {
    var matched, browser;
    jQuery.uaMatch = function (ua) {
        ua = ua.toLowerCase();
        var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
            /(webkit)[ \/]([\w.]+)/.exec(ua) ||
            /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
            /(msie) ([\w.]+)/.exec(ua) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
            [];
        return {
            browser: match[1] || "",
            version: match[2] || "0"
        };
    };
    matched = jQuery.uaMatch(navigator.userAgent);
    browser = {};
    if (matched.browser) {
        browser[matched.browser] = true;
        browser.version = matched.version;
    }
    // Chrome is Webkit, but Webkit is also Safari.
    if (browser.chrome) {
        browser.webkit = true;
    } else if (browser.webkit) {
        browser.safari = true;
    }
    return browser;
}

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[rel="tooltip"]').tooltip();
});
var tmpImg = "";


// Read more read, less toggle functionality
$(document).ready(function () {
    $('#instagram-lightgallery').lightGallery({
        thumbnail: true
    });
    $('#profile-image-lightgallery').lightGallery({
        thumbnail: false
    });
    var readToggle = $(".read-toggle");

    readToggle.each(function () {
        $(this).on("click", function (e) {
            var cardBody = $(this).parent().prev(".card-body-faded");
            e.preventDefault();
            if (cardBody.hasClass('is-closed')) {
                cardBody.removeClass('is-closed');
                cardBody.css({
                    "opacity": "1",
                    "min-height": "200px",
                    "transform": "rotateX(0deg)"
                });
                $(this).text("Show less");
            } else {
                cardBody.addClass('is-closed');
                cardBody.css({
                    "opacity": "0",
                    "min-height": "0",
                    "transform": "rotateX(90deg)"
                });
                $(this).text("Read more");
            }
        });
    });
});

function validFormInputs() {
    console.log('aaa');
    var el = $("form input, form select, form textarea");
    el.unbind('change');
    el.each(function () {
        $(this).on("change", function () {
            if ($(this).valid()) {
                $(this).closest('.has-error').removeClass('has-error');
                if (typeof $(this).attr('aria-describedby') !== "undefined") {
                    $(this).tooltip('destroy');
                    $(this).removeAttr('aria-describedby');
                }
            }
        });
    });
    tootltipFIrstelement = 0;
}

function sliderInterval(previousChildrenNr) {
    var interval = setInterval(function () {
        if (previousChildrenNr !== $('.display-slider-item').length) {
            $(window).trigger("load");
            clearInterval(interval);
        }
    }, 3000);
}

$.fn.modal.Constructor.prototype.enforceFocus = function () {
    modal_this = this;
    $(document).on('focusin.modal', function (e) {
        if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
            // add whatever conditions you need here:
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
            modal_this.$element.focus();
            $(".cke_dialog_ui_input_select option[value='_blank']").prop("selected", true);
        }
    });
};
$(document).on('hidden.bs.modal', function (e) {
    if ($(e.target).attr('data-case') != 1 && !$(e.target).hasClass('bootbox.modal')) {
        $(e.target).removeData('bs.modal').find('.modal-content').empty();
        $(e.target).find('.modal-content').html('<div class="container"> <div class="modal-body"> ' +
            '<center> <img src=' + loadingSvg + '> </center></div></div>');
    }
    $(e.target).css("background-color", '');
    history.pushState("", document.title, window.location.pathname);

}).on("hidden.bs.modal", ".bootbox.modal", function (e) {
    e.stopPropagation();
    if ($('#myModal').hasClass('in')) {
        $('body').addClass('modal-open');
    }
    return false;
}).on('show.bs.modal', function (e) {
    var closeTime = 0;
    $('.circle').each(function (i) {
        closeTime = 150 * (i + 1);
        setTimeout(function () {
            $(".circle").eq(i).css({
                "transform": "scale(0) translateX(-200px) rotateY(90deg)"
            });
        }, 150 * (i + 1));
    });
    $('.action-panel-buttons').attr("data-action-open", "false");
    setTimeout(function () {
        $('.action-panel-buttons').css({
            "opacity": "0"
        });
    }, closeTime);
}).ready(function () { //on page all
    $('body').css('pointer-events', 'all'); //activate all pointer-events on body
}).on("click", "*[data-toggle='modal']", function () {
    var _self = $(this);
    var backgroundColor = "#CD2036";
    var targetModal = $(_self.attr('data-target'));

    if (_self.attr('id') == 'statistics' || _self.attr('id') == 'team-members')
        $('.modal-header').find('button').addClass('btn-close-blue');
    else
        $('.modal-header').find('button').removeClass('btn-close-blue');

    if (_self.attr('data-color')) {
        backgroundColor = _self.attr("data-color");
    } else {
        backgroundColor = "#CD2036";
    }

    targetModal.css({
        "background-color": backgroundColor
    });
    if (typeof _self.attr("data-hash") !== "undefined") {
        location.hash = _self.attr("data-hash");
    }


});
tootltipFIrstelement = 0;

function toolTipError(error, element) {
    if (!element.is(':checkbox')) {
        var isSelect2 = (element.prev().hasClass('select2-container')) ? element.parent() : false;
        if (isSelect2 === false) {
            element.parent().addClass('has-error');
        } else {
            element.parent().addClass('has-error');
            element = isSelect2;
        }
        element.tooltip({
            'trigger': 'focus',
            'title': error,
            'placement': 'top'
        });


        if (tootltipFIrstelement === 0) {
            element.focus();
            tootltipFIrstelement = 1;
        }
    }
    else {

        element.parent().tooltip({
            'trigger': 'focus',
            'title': error,
            'placement': 'top'
        });

        if (tootltipFIrstelement === 0) {
            element.focus();
            tootltipFIrstelement = 1;
        }
    }
    return false;
}

$(function () {
    var interval = setInterval(function () {
        var hash = window.location.hash.substring(1);
        if (hash.length !== 0) {

            if (($('#myModal').hasClass('in') || $('#myUser').hasClass('in'))) {
                clearInterval(interval);
            } else {

                if (hash == 'login') {
                    $(".nav *[data-hash='" + window.location.hash.substring(1) + "']").trigger('click');
                } else {
                    $("*[data-hash='" + window.location.hash.substring(1) + "']").trigger('click');
                }

            }
        } else {
            clearInterval(interval);
        }
    }, 3000);

});

function filterLocation(latitude, longitude, formName, area) {
    getPlaceNameByLatLng(latitude, longitude, function (err, city, country) {
        if (!err) {
            var el = ' <li class="tags-search">'
                + ' <div>' + area + '</div>'
                + '<a href="javascript:;" onclick="removeFilterSearchArea()" class="select2-search-choice-close" tabindex="-1">'
                + '</a> </li>'
                + ' <li class="tags-search mapapi-location">'
                + ' <div>' + city + ', ' + country + '</div>'
                + '<a href="javascript:;" onclick="removeLocation()" class="select2-search-choice-close" tabindex="-1">'
                + '</a> </li>';
            $('.content-filters').find('ul').prepend(el);
        }
    })
}

/**
 * General function to get city and Country
 * @param lat
 * @param lng
 * @param cb
 */
function getPlaceNameByLatLng(lat, lng, cb) {
    const googleApiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?';

    $.getJSON(googleApiUrl + 'latlng=' + lat + ',' + lng + '&sensor=true&key=' + GOOGLEMAPSAPIKEY).then(function (geo) {
        // Api Limit
        if (geo.status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
            return cb(true, trans('api.limit', locale));
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
            return cb(true, trans('Address.not.found', locale));
        }
        cb(false, city, country);
    });
}


function resetPageNumber() {
    $('#page', listSearchForm).val(1);
}

removeLocation = () => {
    $('#location', listSearchForm).val('');
    $('#pac-input', listSearchForm).val('');
    $('#area option:selected', listSearchForm).removeAttr('selected');
    listSearchForm.submit();
};

function removeSubCategory(id) {
    resetPageNumber();
    $('#category', listSearchForm).find("input[value='" + id + "']").trigger('click');
}

function removeCategory(action) {
    resetPageNumber();
    listSearchForm.attr('action', Routing.generate(action, {category: null, '_locale': locale}));
    $('#category input:checked', listSearchForm).removeAttr('checked');
    listSearchForm.submit();
}

function removeFilterSearchPhrase() {
    resetPageNumber();
    $('#searchPhrase', listSearchForm).val('');
    listSearchForm.submit();
}

removeFilterSearchArea = () => {
    $('#area option:selected', listSearchForm).removeAttr('selected');
    listSearchForm.submit();
};

function removeFilterSearchYears() {
    resetPageNumber();
    $('#years', listSearchForm)[0].selectedIndex = 0;
    listSearchForm.submit();
}

function removeFavourite() {
    resetPageNumber();
    $('#favorite', listSearchForm).val(0);
    listSearchForm.submit();
}

/**
 * Submit listSearchForm  when a filter is selected
 */
filterListSearchForm = () => {
    // reset page nr
    resetPageNumber();
    listSearchForm.submit();
};

function removeTag() {
    resetPageNumber();
    $('#tags', listSearchForm).val('');
    listSearchForm.submit();
}

function removeApplications() {
    resetPageNumber();
    $('#applications', listSearchForm).val(0);
    listSearchForm.submit();
}

function removeOrganization() {
    resetPageNumber();
    $('#organization', listSearchForm).val('');
    listSearchForm.submit();
}

function removeFilterTags(name) {
    resetPageNumber();
    var tagsValue = select2Tags.val();
    if (tagsValue.indexOf(',' + name) !== -1) {
        select2Tags.val(tagsValue.replace(',' + name, ''));
    }
    else {
        select2Tags.val(tagsValue.replace(name, ''));
    }
    listSearchForm.submit();
    select2Tags.trigger('change');
}

function resetFilter(url) {
    resetPageNumber();
    listSearchForm.get(0).reset();
    if (select2Tags.val()) {
        removeFilterTags(select2Tags.val());
    }
    if ($('#location', listSearchForm).length > 0) {
        removeFilterSearchArea();
        removeLocation();
    }
    removeFilterSearchPhrase();
    listSearchForm.attr('action', Routing.generate(url, {'_locale': locale}).split("?")[0]);
    $("input[name^='organization']").val('');
    $(':checkbox').prop('checked', false);
    //listSearchForm.submit();
    select2Tags.trigger('change');
}


areaChange = (el) => {
    const locationLength = $('#location', listSearchForm).val().length;
    if (locationLength === 0) {
        // set back the default value
        $(el).val($('option:first', el).val());
    } else {
        listSearchForm.submit();
    }
};

function locationChange() {
    $(this).delay(500).queue(function () {
        resetPageNumber();
        listSearchForm.submit();
        $(this).dequeue();
    });
}

$(document).on('click', '#save-search', function (e) {
    e.preventDefault();
    const entity = $(this).data('entity');
    const categorySlug = $(this).data('category');
    const params = JSON.stringify(urlParamsAsJSON(window.location.href));
    const routeName = $(this).data('routename');
    const data = {params, entity, routeName, categorySlug};

    $.post(Routing.generate('tj_main_save_search', {_locale: locale}),
        data,
        function (data) {
            if (data.success) {
                bootbox.alert({title: false, message: data.data});
            } else {
                bootbox.alert({title: false, message: showErrors(Object.values(data.errors))})
            }
        }
    );
});

/**
 * @JAMANANA Do me m'tregu mu ti
 * Build json structure from query url parameters
 * @param query
 */
function urlParamsAsJSON(query) {
    query = decodeURIComponent(query);
    // No params
    let params = query.split('?')[1];
    if (typeof params == 'undefined') {
        return {};
    }

    // Remove [], [number] on param keys
    params = params.split('&');
    params.forEach((el, i) => {
        params[i] = params[i].replace(/\[\d*\]/g, '');
    });

    // Build a json structure with params
    return params.reduce((acc, item) => {
        // Split params on '=' Ex. 'page=1'
        const [key, value] = item.split('=');
        if (value.trim() === "") return acc;

        // If the key already exists (in array cases like status)
        if (acc[key]) {
            // If already array and not duplicated then push
            if (Array.isArray(acc[key]) && $.inArray(value, acc[key]) === -1) {
                acc[key].push(value);
                // Create an array and push both existing and new
            } else if (value !== acc[key]) {
                acc[key] = [acc[key], value];
            }
            // Equalize for the first time
        } else {
            acc[key] = value;
        }
        return acc;
    }, {});
}

function bootboxAfterFavoriteSuccesful() {
    url = Routing.generate('tj_main_default_user_modal', {'_locale': locale});
    bootbox.dialog(
        {
            message: "<div id='bootboxMsg' class='text-center'><p> "
            + trans('bootbox.addFavorite', locale)
            + ' <a class="favorite-bootbox-link" data-hash="user" data-toggle="modal" data-target="#myUser" href="' + url + '"> '
            + trans('bootbox.addfavorite.Here', locale)
            + '</a>.</p></div>'
        });
    $('.favorite-bootbox-link').on('click', function () {
        $(this).closest('.bootbox').modal('hide');
    });
}

jQuery(function ($) {
    $(document).ready(function () {


        $('.topbar-mobile-menu').click(function () {
            $('.large-top-menu').addClass('top-active');
            $('.navbar').addClass('stop-transform');
        });
        $('.topbar-close-menu').click(function () {
            $('.large-top-menu').removeClass('top-active');
            $('.navbar').removeClass('stop-transform');
        });

        var logNumber = $('.activity-job-lines .log-line').length;
        var collectLog = $('.activity-job-lines .log-line:nth-child(n+2)');
        if (logNumber > 1) {
            collectLog.addClass('remove-logs');
        }
        $('.expand-log').click(function () {
            collectLog.toggleClass('remove-logs');
            $(this).toggleClass('change-text');
            if ($(this).hasClass('change-text')) {
                $(this).text(trans('link.seeLess', locale));
            } else {
                $(this).text(trans('link.seeMore', locale));
            }
        });

        $('#action_favorite').click(function () {
            if ($('#action_favorite').hasClass('active')) {
                $(this).prop('title', 'Add to favourites');
            } else {
                $(this).prop('title', 'Remove from favourites');
            }
            ;
        })

    });
});


/**
 * Renders messages from backend
 *
 * @param errors
 * @returns {string}
 */
function showErrors(errors) {
    var str = "";
    if (Array.isArray(errors)) {
        for (let err of errors) {
            str += err + "<br>"
        }
    } else {
        str = errors;
    }
    return str;
}

// nav admin search drop-down

var mainAdminSelect = $('.adminChoices');

function searchAdmin() {
    var className = mainAdminSelect.children(":selected").attr("data-class");
    var select = $('.' + className + 'Choices').val();
    var searchPhrase = $('#' + select).val();
    window.location.href = mainAdminSelect.val() + '?element=' + select + '&searchPhrase=' + searchPhrase;
}

$('#searchIconPeople').click(function () {
    searchAdmin();
});
$('#searchIconBilling').click(function () {
    searchAdmin();
});

function runScript(e) {
    if (e.keyCode == 13) {
        searchAdmin();
    }
}

// Validate the copyright input in image modals.
function validateCopyrightInput() {

    // We have created a blank form element as parent ,so we can properly validate it with JQuery validator plugin.
    var form = $("#image_media_copyright_form");

    form.validate({
        rules: {
            image_media_copyright_input: {
                required: true
            }
        },
        errorPlacement: function (error, element) {
            toolTipError(error.text(), element);
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error');
            $(element).tooltip('destroy');
        }
    });

    return form.valid();
}


$(function () {

    var currentDivHidden = mainAdminSelect.children(":selected").attr("data-class");
    mainAdminSelect.change(function () {
        var className = $(this).children(":selected").attr("data-class");
        $('.' + currentDivHidden).addClass('hidden');
        $('.' + className).removeClass('hidden');
        currentDivHidden = className;
    });

    var selectPeople = $('.peopleChoices');
    var currentInputPeople = $('#' + selectPeople.val());
    selectPeople.change(function () {
        currentInputPeople.addClass('hidden');
        currentInputPeople.val('');
        currentInputPeople = $('#' + $(this).val());
        currentInputPeople.removeClass('hidden');
    });


    var selectBilling = $('.billingChoices');
    var currentInputBilling = $('#' + selectBilling.val());

    $('.billingChoices option[value="billingCreation"]').addClass('hidden');
    selectBilling.change(function () {
        if (currentInputBilling.selector == '#input') {
            currentInputBilling = $('.billing #input');
        }
        currentInputBilling.addClass('hidden');
        currentInputBilling.val('');
        currentInputBilling = $('#' + $(this).val());
        if (currentInputBilling.selector == '#input') {
            currentInputBilling = $('.billing #input');
        }
        currentInputBilling.removeClass('hidden');
    });


    $('.userLastLogin, .profileRegistration').datetimepicker({
        viewMode: 'days',
        format: 'DD/MM/YYYY',
        useCurrent: false,
        showClear: true,
        showTodayButton: true,
        toolbarPlacement: 'top',
        locale: locale
    });

});

jQuery(function ($) {
    $(document).ready(function () {
        $('.toggle-admin').click(function () {
            $(this).parent().toggleClass('submenu-ac');
            if ($(this).parent().hasClass('submenu-ac')) {
                $(this).text('-');
            } else {
                $(this).text('+');
            }
        });
    });
});

$(window).load(function () {
    // For each table within the content area...
    $('table').each(function (t) {
        // Add a unique id if one doesn't exist.
        if (!this.id) {
            this.id = 'table_' + t;
        }
        // Prepare empty variables.
        var headertext = [],
            theads = document.querySelectorAll('#' + this.id + ' thead'),
            headers = document.querySelectorAll('#' + this.id + ' th'),
            tablerows = document.querySelectorAll('#' + this.id + ' th'),
            tablebody = document.querySelector('#' + this.id + ' tbody');
        // For tables with theads...
        for (var i = 0; i < theads.length; i++) {
            // If they have more than 2 columns...
            if (headers.length > 2) {
                // Add a responsive class.
                this.classList.add('responsive');
                this.classList.remove('nowrap');
                // Get the content of the appropriate th.
                for (var i = 0; i < headers.length; i++) {
                    var current = headers[i];
                    headertext.push(current.textContent.replace(/\r?\n|\r/, ''));
                }
                // Apply that as a data-th attribute on the corresponding cells.
                for (var i = 0, row; row = tablebody.rows[i]; i++) {
                    for (var j = 0, col; col = row.cells[j]; j++) {
                        col.setAttribute('data-th', headertext[j]);
                    }
                }
            }
        }
    });

});

$(document).on('click', '.close-message', function (e) {
    $(this).closest('.message-info').remove();
});

// list pagination prevent event on a click
$(document).on('click', '.navigation .pagination a', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $('#page', listSearchForm).val($(this).attr('data-page'));
    listSearchForm.submit();
});

/**
 * Appends svg span to submit button
 * @param form
 */
function addLoadingSvg(form) {
    const btnSubmit = form.find('button[type="submit"]');
    if (btnSubmit.length) {
        btnSubmit.append('<span class="submit-rolling-svg" style="display:none;"> </span>')
    } else {
        form.find('input[type="submit"]').append('<span class="submit-rolling-svg" style="display: none"></span>');
    }
}

/**
 * Render errors on 500 case
 * @TODO customize each ajax request on 500
 */
function serverError() {
    bootbox.alert({message: trans('error.occurred.pleaseReload', locale)});
}

/**
 * load image from blob image
 * @param input
 */
function previewImage(input, $el = null) {
    if (!input.files.length) {
        return;
    }
    const reader = new FileReader();
    const file = input.files[0];
    $(reader).load(function (e) {
        if ($el) {
            $el.attr('src', base64toBlobUrl(e.target.result));
        } else {
            resize_image(base64toBlobUrl(e.target.result), file, null, null, null, 1);
        }
    });
    reader.readAsDataURL(file);
}

/**
 * Load audio buttons on player div
 * @param input
 * @param player
 */
function previewAudio(input, player) {
    if (!input.files.length) {
        return;
    }
    const reader = new FileReader();
    const file = input.files[0];
    $(reader).load(function (e) {
        const sourceUrl = base64toBlobUrl(e.target.result);
        player.children().each(function () {
            $(this).attr("src", sourceUrl);
        });
        player[0].load();
        player.removeClass('hidden');
    });

    reader.readAsDataURL(file);
}

/**
 * Get errors [inputKeyName => Error] and renders to form inputs
 * @param errors
 * @param form
 */
function renderFormErrors(errors, form) {
    for (let err of errors) {
        showSingleError(err, form);
    }
}

/**
 * In order to use same way in other forms where this function is implemented partially
 * @param err
 * @param form
 */
function showSingleError(err, form) {
    err.field = err.field.replace('\\', '');
    const el = form.find('input[name="' + err.field + '"]');
    el.parent().addClass('has-error');
    toolTipError(err.message, el);
}

/**
 * Populate select2 input with saved tags
 * @param string
 * @param separator
 */
splitVal = (string, separator) => {
    let val, i, l;
    if (string === null || string.length < 1) {
        return [];
    }

    val = string.split(separator);
    for (i = 0, l = val.length; i < l; i = i + 1) {
        val[i] = $.trim(val[i]);
    }

    return val;
};

/**
 * General function to create select2
 * @param input
 * @param searchChoiceTrans
 * @param url
 * @param formatSelectionTooBig
 * @param maximumSelectionSize
 */
generalSelect2 = (input, searchChoiceTrans, url, formatSelectionTooBig, maximumSelectionSize = null) => {
    return createSelect2(
        input,
        searchChoiceTrans,
        {
            url,
            dataType: 'json',
            data: (q, page) => {
                return {q, page}
            },
            results: (results, page) => {
                const more = !results.length ? false : page * autosuggestion_pagination < results[0].total_count;
                return {results, more};
            }
        },
        formatSelectionTooBig,
        maximumSelectionSize
    )
};

/**
 * General function for creating autoselect
 * @param input
 * @param searchChoiceTrans
 * @param ajax object
 * @param formatSelectionTooBig callback
 * @param maximumSelectionSize
 * @returns {*|jQuery}
 */
createSelect2 = (input, searchChoiceTrans, ajax, formatSelectionTooBig, maximumSelectionSize = 1) => {
    return $(input).select2({
        minimumInputLength: 3,
        formatInputTooShort: function () {
            $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
        },
        maximumSelectionSize,
        cache: true,
        quietMillis: 250,
        tags: true,
        tokenSeparators: [';'],
        createSearchChoice: (term, page) => {
            $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
            const exists = item => item.text.toLowerCase() === term.toLowerCase();
            if (page.some(exists)) return;
            return {
                id: $.trim(term),
                text: $.trim(term) + '  (' + searchChoiceTrans + ')'
            };
        },
        ajax,
        initSelection: function (element, callback) {
            const data = splitVal(element.val(), ",").reduce((acc, item) => {
                acc.push({id: item, text: item});
                return acc;
            }, []);
            callback(data);
        },
        formatSelectionTooBig
    });
};

/**
 * Render profile data boxes
 */
function renderBoxes() {
    $('#myModal').modal('hide');
    // Set up masonry grids
    const grids = $("[data-masonry]");
    grids.each(function () {
        $(this).masonry({itemSelector: "[data-masonry-item]"});
        $(this).masonry("on", "layoutComplete", function () {
            $("[data-masonry-item] .panel").each(function () {
                $(this).bleedPanel();
            });
        });
        $(this).masonry();
    });
}

function popupCase(text) {
    if (text.trim().length === 0) {
        text = 'Please specify a text for the placeholder!';
    }
    bootbox.alert({
        message: text
    });
}