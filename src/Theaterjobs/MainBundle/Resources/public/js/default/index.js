$(function () {
    var search_data = $('#search_data');
    var colum_eq = $('.column-eq');

    var heights = colum_eq.map(function () {
            return $(this).height();
        }).get(),
        maxHeight = Math.max.apply(null, heights);

    colum_eq.css({
        "min-height": maxHeight + "px"
    });

    $('.masonry-grid').masonry({
        itemSelector: '.masonry-grid-item',
        percentPosition: true,
        columnWidth: 255,
        gutter: 30
    });

    $("#resetpasswmodal").click(function () {
        $("#loginModal").hide();
        $(".modal-backdrop").hide();
        $("body").css({
            "padding-right": "0"
        });

    });

    $(".form-input.search").on('keyup', function () {
        if ($('.form-input.search').val() !== " ") {
            if (search_data.hasClass('hide')) {
                search_data.removeClass('hide');
            }

        } else {
            if (!search_data.hasClass('hide')) {
                search_data.addClass('hide');
            }
        }
    });

    if ($(window).width() <= 1024) {
        // Menu toggle
        var menu = $('#main-menu');
        var joinButton = $('#nav-join-free');
        $('#menu-toggle').on("click", function () {
            if (menu.attr('aria-opened') === "false") {
                menu.show();
                menu.addClass("animated fadeIn");
                joinButton.addClass('button-secondary-white');
                joinButton.removeClass('button-primary');
                menu.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                    menu.removeClass('animated fadeIn');
                });
                menu.attr('aria-opened', 'true');
            }

        });

        $('#close-icon').on("click", function () {
            menu.addClass('animated fadeOut');
            joinButton.removeClass('button-secondary-white');
            joinButton.addClass('button-primary');
            menu.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                menu.removeClass('animated fadeOut');
                menu.hide();
            });
            menu.attr('aria-opened', "false");
        });
    }

    //scroll the join for free on membership section homepage
    $('.nav-join-free').click(function () {
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top
        }, 1000);
        return false;
    });

    $(window).resize(function () {
        if ($(window).width() <= 1024) {
            // Menu toggle
            var menu = $('#main-menu');
            var joinButton = $('#nav-join-free');
            $('#menu-toggle').on("click", function () {
                if (menu.attr('aria-opened') === "false") {
                    menu.show();
                    menu.addClass("animated fadeIn");
                    joinButton.addClass('button-secondary-white');
                    joinButton.removeClass('button-primary');
                    menu.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                        menu.removeClass('animated fadeIn');
                    });
                    menu.attr('aria-opened', 'true');
                }

            });

            $('#close-icon').on("click", function () {
                menu.addClass('animated fadeOut');
                joinButton.removeClass('button-secondary-white');
                joinButton.addClass('button-primary');
                menu.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                    menu.removeClass('animated fadeOut');
                    menu.hide();
                });
                menu.attr('aria-opened', "false");

            });
        } else {
            $('#main-menu').show();
        }
    });
});