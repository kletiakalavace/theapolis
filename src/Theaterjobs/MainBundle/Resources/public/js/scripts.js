// Variables
var searchToggle = $("#search-toggle");
var navSearch = $("#nav-search");
var searchWindow = $(".search-window");
var searchClose = $(".search-close");
var homepageHeader = $(".header-homepage");
var topMenu = $("#top-menu");

// main header
if ($(window).width() >= 960) {
    var joinButton = $("#join-button");
    var lastScrollTop = 0;
    var slow = 1;
    var mh_header_h = topMenu.outerHeight();
    if ($(window).scrollTop() > mh_header_h * slow) {
        topMenu.css('top', mh_header_h * slow * (-1));
        topMenu.addClass('small-menu');
        joinButton.css({
            "display": "inline-block"
        });
    }
    $(window).scroll(function () {
        if ($(window).width() >= 960) {
            var mh_st = ($(this).scrollTop() < 0) ? 0 : $(this).scrollTop();
            mh_header_h = topMenu.outerHeight();
            var mh_header_top = parseFloat(topMenu.css('top'));
            var mh_top = mh_header_top + ((lastScrollTop - mh_st) / slow);
            if (mh_st > lastScrollTop) {
                if (mh_top < (mh_header_h * (-1))) {
                    mh_top = mh_header_h * (-1);
                }
            } else {
                if (mh_top > 0) {
                    mh_top = 0;
                }
            }
            if ($('body').length) {
                if ($(this).scrollTop() > mh_header_h * slow) {
                    topMenu.addClass('small-menu');
                    topMenu.removeClass('animate');
                    joinButton.css({
                        "display": "inline-block"
                    });
                } else {
                    if (topMenu.hasClass('small-menu')) {
                        if (!topMenu.hasClass('animate')) {
                            topMenu.addClass('animate');
                            topMenu.animate({
                                top: -140
                            }, 100, function () {
                                topMenu.removeClass('small-menu');
                                if (navSearch.attr("aria-opened") === "true") {
                                    topMenu.animate({top: "100px"}, 200);
                                } else {
                                    topMenu.animate({top: 0}, 200);
                                }
                                joinButton.hide();
                            });
                        }
                    }
                }
            }
            if (navSearch.attr("aria-opened") === "true") {
                topMenu.css('top', "100px");
            } else {
                topMenu.css('top', mh_top);
            }

            lastScrollTop = mh_st;
        } else {
            topMenu.css('top', 0);
        }
    });
}

// Search toggle
// Open the search bar
searchToggle.on("click", function (e) {
    e.preventDefault();
    navSearch.attr("aria-opened", "true");
    navSearch.animate({top: 0}, 500);
    topMenu.animate({top: "100px"}, 500);
    homepageHeader.animate({marginTop: "160px"}, 500);
});

// Close the search bar
searchClose.on("click", function (e) {
    e.preventDefault();
    navSearch.attr("aria-opened", "false");
    navSearch.animate({top: "-100px"}, 500);
    searchWindow.fadeOut();
    topMenu.animate({top: 0}, 500);
    homepageHeader.animate({marginTop: 0}, 500);
});

// Carousel
$('.tj-carousel').slick({
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    centerMode: true,
    variableWidth: true
});

// Masonry grid profile
$('.masonry-grid-profile-info').masonry({
    itemSelector: '.masonry-item-profile-info',
    percentPosition: true,
    gutter: 20
});

window.onload = function () {
    // Masonry instagram
    $('.masonry-grid').masonry({
        itemSelector: '.masonry-grid-item',
        percentPosition: true,
        columnWidth: 270,
        gutter: 30
    });
};


// Custom modal
$('.tj-modal-toggle').click(function (e) {
    e.preventDefault();
    var dataDismissable;
    if ($(this).attr('data-dismissable')) {
        dataDismissable = $(this).attr('data-dismissable');
        $(dataDismissable).removeClass("is-modal-visible");
        if ($(dataDismissable).attr('style')) {
            $(dataDismissable).attr('style', function (i, style) {
                return style.replace(/display[^;]+;?/g, '');
            });
        }
        // $('body').removeClass("is-modal-open");
    }
    var modalId = $($(this).attr('data-id'));
    if ($(modalId).attr('style')) {
        $(modalId).attr('style', function (i, style) {
            return style.replace(/display[^;]+;?/g, '');
        });
    }
    modalId.addClass("is-modal-visible");
    $('body').addClass("is-modal-open");
});
$('.cs-modal-header .close').each(function () {
    $(this).click(function () {
        if ($('.cs-modal-body .alert')) {
            $('.cs-modal-body .alert').remove();
        }
        $(this).parent().closest('.cs-modal').removeClass('is-modal-visible');
        $('body').removeClass('is-modal-open');
        $(this).parent().closest('.cs-modal').find('form')[0].reset();

    });
});

var idTerms = $('#terms');

idTerms.click(function () {
    $('#fos_user_registration_form_terms_and_trades')
});

// Gallery linking
$(function () {
    var photoLink = $("#galleryPhotoLink");
    var videoLink = $("#galleryVideoLink");
    var audioLink = $("#galleryAudioLink");
    var images = $(".image-box:not('.slick-cloned')");
    var audios = $(".audio-box:not('.slick-cloned')");
    var videos = $(".video-box:not('.slick-cloned')");

    photoLink.click(function (e) {
        e.preventDefault();
        var slideIndex = images.first().attr('data-slick-index');
        $('.tj-carousel').slick('slickGoTo', parseInt(slideIndex));
    });

    audioLink.click(function (e) {
        e.preventDefault();
        var sliderIndex = audios.first().attr('data-slick-index');
        $('.tj-carousel').slick('slickGoTo', parseInt(sliderIndex));
    });

    videoLink.click(function (e) {
        e.preventDefault();
        var sliderIndex = videos.first().attr('data-slick-index');
        $('.tj-carousel').slick('slickGoTo', parseInt(sliderIndex));
    });

    var loginForm = $('.login-block form');
    var resetModal = $("#resetModal form");
    var registerModal = $("#fos_user_registration_register");

    loginForm.validate({
        rules: {
            _username: {
                required: true,
                minlength: 2
            },
            _password: {
                required: true,
            }
        },
        onsubmit: true,
        onkeyup: function (element) {
            $(element).valid()
        },
        onfocusout: function (element) {
            this.element(element);
        }
    });

    resetModal.validate({
        rules: {
            username: {
                required: true,
                email: true
            }
        },
        onsubmit: false,
        onkeyup: function (element) {
            $(element).valid()
        },
        onfocusout: function (element) {
            this.element(element);
        }
    });

    registerModal.validate({
        rules: {
            "fos_user_registration_form[profile][firstName]": {
                required: true,
                minlength: 2
            },
            "fos_user_registration_form[profile][lastName]": {
                required: true,
                minlength: 2
            },
            "fos_user_registration_form[email]": {
                email: true,
                required: true
            },
            "fos_user_registration_form[plainPassword][first]": {
                required: true,
                minlength: 8
            },
            "fos_user_registration_form[plainPassword][second]": {
                required: true,
                equalTo: "#fos_user_registration_form_plainPassword_first"
            },
            "fos_user_registration_form[terms_and_trades]": {
                required: false
            }
        },
        onsubmit: false,
        onkeyup: function (element) {
            $(element).valid()
        },
        onfocusout: function (element) {
            this.element(element);
        }
    });
});



