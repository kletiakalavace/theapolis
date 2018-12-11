// ========================================
// Main scripts
// ========================================

// When document ready
$(document).ready(function () {

    // We have JavaScript
    $(document.body).addClass("js");
});

// When everything is ready
$(window).bind("load", function () {

    // Initiate display slider
    var profileMedia = $("#profile-media");
    if (profileMedia.length) {
        profileMedia.initDisplaySlider();
    }

    $(window).resize(function () {
        if (profileMedia.length) {
            profileMedia.updateDisplaySlider();
        }
    });

    // Set up masonry grids
    var grids = $("[data-masonry]");
    grids.each(function () {
        $(this).masonry({
            itemSelector: "[data-masonry-item]"
        });

        $(this).masonry("on", "layoutComplete", function () {
            $("[data-masonry-item] .panel").each(function () {
                $(this).bleedPanel();
            });
        });

        $(this).masonry();
    });

    // Video play buttons
    $(".display-slider-play").each(function () {
        $(this).click(function () {
            $(this).playEmbed();
        });
    });

    // Manage scrolling
    var prevScrollTop = $(window).scrollTop();
    $(window).scroll(function () {
        $(".navbar").updateHeader($(window).scrollTop(), prevScrollTop);
        prevScrollTop = $(window).scrollTop();
    });

    // Toggles
    $(".toggle").each(function () {
        $(this).toggle();
    });

    // Search
    $("#search").setSearch();
    $("#add-new").setAddNew();

    // Mobile menu
    $("#mobile-menu").setMobileMenu();

    // Filter group mobile toggle
    $(".filter-group-mobile-toggle").click(function () {
        $(this).parent().toggleClass("active");
    });

    // Filter group mobile toggle
    $(".display-slider-mobile-toggle a").click(function () {
        $(this).parent().parent().toggleClass("mobile-expand");
    });

    // Panel expand mobile
    $(".panel-head").each(function () {
        $(this).click(function () {
            $(this).parent().toggleClass("mobile-expand");
        });
    });

    // Audio player

    $(".audio-player").each(function () {
        $(this).setWavesurfer();
    });

    // Video player
    var players = plyr.setup();
    var url;
    $('video').each(function (index, value) {
        url = $(value).data('url');
        videoPlayer(players[index], url);
    });
});

function videoPlayer(el, url) {
    el.source({
        type: 'video',
        title: 'Video',
        sources: [{
            src: url,
            type: url.indexOf('youtu') !== -1 ? 'youtube' : url.indexOf('vimeo') !== -1 ? 'vimeo' : 'video/mp4'
        }]
    });
}


// Wavesurfer
// ========================================

$.fn.setWavesurfer = function () {
    var _this = this;
    var waveform = _this.find(".audio-player-waveform");
    var playPauseButton = _this.find(".audio-player-button");
    var remainingTime = _this.find(".audio-player-remain");

    var wavesurfer = WaveSurfer.create({
        container: "#" + waveform.attr("id"),
        barWidth: 3,
        cursorWidth: 3,
        cursorColor: "#87162D",
        progressColor: "#CD2036",
        waveColor: "#fff"
    });

    // Load file
    wavesurfer.load(waveform.attr("data-audio"));

    // Set up button
    playPauseButton.click(function () {
        wavesurfer.playPause();
        if (wavesurfer.isPlaying()) {
            $(this).addClass("playing");
        } else {
            $(this).removeClass("playing");
        }
    });

    // Set up remaining time
    wavesurfer.on("audioprocess", function () {
        var current = wavesurfer.getCurrentTime();
        var duration = wavesurfer.getDuration();
        remainingTime.html("-" + secToTime(duration - current));
    });

    wavesurfer.on("finish", function () {
        playPauseButton.removeClass("playing");
    });
};

// Seconds to timestamp
function secToTime(sec) {
    var minutes = Math.floor(sec / 60);
    var seconds = Math.floor(sec - minutes * 60);
    return minutes + ":" + ("0" + seconds).slice(-2);
}

// Mobile menu
// ========================================
$.fn.setMobileMenu = function () {
    var _this = this;
    var toggle = $("#mobile-menu-toggle");
    var close = _this.find(".mobile-menu-close");

    toggle.click(function () {
        _this.addClass("active");
        $(document.body).addClass("modal-open");
    });

    close.click(function () {
        _this.removeClass("active");
        $(document.body).removeClass("modal-open");
    });
};


// Search bar
// ========================================
$.fn.setSearch = function () {
    var _this = this;
    var open = $("#search-toggle");
    var close = $("#search-close");
    var field = _this.find(".search-field");

    open.click(function () {
        _this.addClass("visible");
        _this.addClass("active");
        $(document.body).addClass("modal-open");
        field.focus();
    });

    close.click(function () {
        _this.removeClass("visible");
        _this.removeClass("active");
        $(document.body).removeClass("modal-open");
    });

    _this.click(function (e) {
        if (e.target !== this) {
            return;
        }

        _this.removeClass("visible");
        _this.removeClass("active");
        $(document.body).removeClass("modal-open");
    });

    field.on("focus", function () {
        _this.toggleSearchResults();
    });

    field.on("blur", function () {
        _this.toggleSearchResults();
    });
};

$.fn.toggleSearchResults = function () {
    var _this = this;
    var results = _this.find(".search-results");

   // results.toggleClass("show");
};

// Add new bar
// ========================================
$.fn.setAddNew = function () {
    var _this = this;
    var open = $("#add-new-btn-primary");
    var close = $("#addnew-close");
    var clickClose = $(".click-close");
    /*var field = _this.find(".add-new-btn-primary");*/

    open.click(function () {
        _this.addClass("visible");
        _this.addClass("active");
        $(document.body).addClass("modal-open");
    });

    close.click(function () {
        _this.removeClass("visible");
        _this.removeClass("active");
        $(document.body).removeClass("modal-open");
    });
    
    clickClose.click(function () {
        _this.removeClass("visible");
        _this.removeClass("active");
        $(document.body).removeClass("modal-open");
    });

    _this.click(function (e) {
        if (e.target !== this) {
            return;
        }

        _this.removeClass("visible");
        _this.removeClass("active");
        $(document.body).removeClass("modal-open");
    });

  /*  field.on("focus", function () {
        _this.toggleSearchResults();
    });

    field.on("blur", function () {
        _this.toggleSearchResults();
    });*/
};

/*
$.fn.toggleSearchResults = function () {
    var _this = this;
    var results = _this.find(".search-results");
*/

    // results.toggleClass("show");
/*
};
*/


// Toggles
// ========================================
$.fn.toggle = function () {
    var _this = this;

    _this.click(function () {
        $(this).toggleClass("on");
    });
};


// Header transition
// ========================================
$.fn.updateHeader = function (offset, prevOffset) {
    var _this = this;

    if ($(document.body).hasClass("fixed-header-large")) {
        if (offset > 200) {
            _this.addClass("fixed");
            _this.addClass("out");
            _this.removeClass("large");
        } else if (offset < 200) {
            _this.removeClass("fixed");
            _this.removeClass("out");
            _this.addClass("large");
        }

        if (offset > 300) {
            _this.addClass("slide");
        } else if (offset < 300) {
            _this.removeClass("slide");
        }

        if (offset > 400) {
            if (prevOffset - offset > 10) {
                _this.addClass("in");
            }

            if (prevOffset - offset < -10) {
                _this.removeClass("in");
            }
        } else if (offset < 400) {
            _this.removeClass("in");
        }
    } else {
        if (offset > 400) {
            if (prevOffset - offset > 10) {
                _this.addClass("in");
            }

            if (prevOffset - offset < -10) {
                _this.removeClass("in");
            }
        } else if (offset < 400) {
            _this.addClass("in");
        }
    }

    _this.prevOffset = offset;
};

// Video play/pause
// ========================================
$.fn.playEmbed = function () {
    var _this = this;
    var item = _this.parent();
    var target = item.find("iframe");
    var scr = target.attr("scr");

    // Vimeo
    var player = $f(target[0]);
    player.api("play");

    // YouTube
    target[0].contentWindow.postMessage('{"event":"command","func":"' + 'playVideo' + '","args":""}', '*');

    item.addClass("playing");
};

// Masonry bleed panels
// ========================================
$.fn.bleedPanel = function () {
    var _this = this;

    if (_this.parent().position().left > 0) {
        _this.addClass("panel-bleed-right");
        _this.removeClass("panel-bleed-left");
    } else {
        _this.addClass("panel-bleed-left");
        _this.removeClass("panel-bleed-right");
    }

    if ($(window).width() < 768) {
        _this.parent().css("position", "static");
        _this.parent().parent().css("height", "auto");
    }
};

// Display slider
// ========================================
$.fn.updateDisplaySlider = function () {
    var _this = this;
    _this.translate = 0;
    _this.sideOffset = $(".navbar-brand").first().offset().left;

    // Measure and set min-width
    _this.list.children(".display-slider-item").each(function (index) {
        _this.listWidth += $(this).outerWidth() + 30;
    });
    _this.listWidth -= 30;

    if ($(document).width() > 768) {
        _this.list.css("min-width", _this.listWidth);
    } else {
        _this.list.css("min-width", _this.listWidth);
    }

    _this.list.css("transform", "translateX(0px)");
};

$.fn.updateDisplayButtons = function () {
    var _this = this;

    if (_this.translate == 0) {
        _this.prev.removeClass("visible");
        _this.next.addClass("visible");

    } else if (_this.translate > 0 && _this.translate < _this.listWidth - $(window).width()) {
        _this.prev.addClass("visible");
        _this.next.addClass("visible");

    } else if (_this.translate == _this.listWidth - $(window).width()) {
        _this.next.removeClass("visible");
        _this.prev.addClass("visible");
    }
};

$.fn.transformDisplay = function (target) {
    _this = this;
    // Get translate distance
    _this.translate = target.position().left - _this.sideOffset;

    // Check first element
    if (target.is(":first-child")) {
        _this.translate = 0;
    }

    // Check the end
    if (_this.translate - _this.listWidth + $(window).width() > 0) {
        _this.translate -= _this.translate - _this.listWidth + $(window).width();
    }

    // Set transform
    _this.list.css("transform", "translateX(-" + _this.translate + "px)");

    // Update classes
    _this.active.removeClass("active");
    target.addClass("active");
    _this.active = target;
};

$.fn.nextDisplay = function () {
    var _this = this;
    var next = _this.active.next();

    if (!next.length) {
        next = _this.active;
    }

    _this.transformDisplay(next);
    _this.updateDisplayButtons();
};

$.fn.prevDisplay = function () {
    var _this = this;
    var prev = _this.active.prev();

    if (!prev.length) {
        prev = _this.active;
    }

    _this.transformDisplay(prev);
    _this.updateDisplayButtons();
};

$.fn.selectDisplay = function (target) {
    var _this = this;
    var next = $(target);

    _this.transformDisplay(next);
    _this.updateDisplayButtons();
};

$.fn.initDisplaySlider = function () {
    var _this = this;
    _this.list = _this.find(".display-slider-list");
    _this.next = _this.find(".display-slider-next");
    _this.prev = _this.find(".display-slider-prev");
    _this.active = _this.find(".display-slider-item.active");
    _this.anchorTargets = _this.find("[data-display-target]");
    _this.listWidth = 0;
    _this.translate = 0;
    _this.sideOffset = $(".navbar-brand").first().offset().left;

    _this.updateDisplayButtons();

    // Make sure stuff exists
    if (!_this.list.length) {
        console.log("There is no list element in display slider!");
        return 0;
    }

    if (!_this.next.length) {
        console.log("There is no next element in display slider!");
        return 0;
    }

    if (!_this.prev.length) {
        console.log("There is no prev element in display slider!");
        return 0;
    }

    if (!_this.active.length) {
        console.log("There is no initial active slider item!");
        return 0;
    }

    // Measure and set min-width
    _this.list.children(".display-slider-item").each(function (index) {
        _this.listWidth += $(this).outerWidth() + 30;
    });
    _this.listWidth -= 30;

    if ($(document).width() > 768) {
        _this.list.css("min-width", _this.listWidth);
    } else {
        _this.list.css("min-width", 0);
    }

    // Attach event listeners
    _this.next.click(function () {
        _this.nextDisplay()
    });
    _this.prev.click(function () {
        _this.prevDisplay()
    });

    // Click on image
    _this.list.find(".display-slider-item").each(function () {
        $(this).click(function () {
            _this.selectDisplay(this)
        });
    });

    // Set up anchors
    _this.anchorTargets.each(function () {
        $(this).click(function () {
            _this.selectDisplay($($(this).attr("data-display-target")));
        });
    });
};