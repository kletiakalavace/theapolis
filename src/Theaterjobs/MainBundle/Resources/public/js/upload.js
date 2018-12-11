//Constants
const filtersImg = $(".instafilter");
const filtersId = $('#filters');
//All filters
const arrFilters = [
    'amaro',
    '_1977',
    'aden',
    'brannan',
    'brooklyn',
    'clarendon',
    'valencia',
    'hudson',
    'inkwell',
    'perpetua',
    'lark',
    'lofi',
    'mayfair',
    'moon',
    'nashville',
    'reyes',
    'toaster',
    'xpro2'
];

//JQuery Events

//Loads list of filters
$('#loadFilters').click(function () {
    if ($('#filters').children().length === 0) {
        addFilters($(this).attr('data-url'));
    }
});
//Loads a filter on profile picture
$(document).on("click", ".instafilter", function () {
    var filter;
    if ($('.cropper-view-box figure').length == 0) {
        filter = $('<figure></figure>');
        filter.attr('class', ($(this).parent().attr('class')));
        $('.cropper-view-box img').appendTo(filter);
        $('.cropper-view-box').append(filter);
    } else {
        $('.cropper-view-box img').parent().attr('class', ($(this).parent().attr('class')));
    }

    if ($('.cropper-canvas figure').length == 0) {
        filter = $('<figure></figure>');
        filter.attr('class', ($(this).parent().attr('class')));
        $('.cropper-canvas img').appendTo(filter);
        $('.cropper-canvas').append(filter);
    } else {
        $('.cropper-canvas img').parent().attr('class', ($(this).parent().attr('class')));
    }

    $('#profilePicture .filter').val($(this).parent().attr('class'));
    $('#mediaImage .filter').val($(this).parent().attr('class'));
});

//Functions

/**
 * Add filter to a selected profile picture
 * @param url
 */
function addFilters(url) {

    arrFilters.forEach(function (el) {
        var filter = $('<figure class="' + el + '"> <img class="instafilter" src="' + url + '"> </figure>');
        filtersId.append(filter);
    });
    filtersId.fadeIn('100');
    filtersId.removeClass('hidden');

    filtersImg.each(function (i) {
        setTimeout(function () {
            filtersImg.eq(i).css({
                "transform": "scale(1) translateX(0) rotateY(0deg)"
            });
        }, 150 * (i + 1));

    });
}