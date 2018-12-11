var orgContent = $("#organizationPartial");
$(document).on("click", ".underTitle i, .add-undertitle", function () {
    $('.underTitle').addClass('hidden');
    $('.add-undertitle').addClass('hidden');
    $('.editUnderTitle').removeClass('hidden');
    $('.editUnderTitle input[type="text"]').focus();
    $('.editUnderTitle :input').val($('.underTitle').find('span').text());
}).on("click", ".editUnderTitle #underTitleClose", function () {
    $('.editUnderTitle').addClass('hidden');
    $('.underTitle').removeClass('hidden');
    $('.add-undertitle').removeClass('hidden');
}).on("focus", '.editUnderTitle input[type="text"]', function (e) {
    var el = this;
    el.selectionStart = el.selectionEnd = 10000;
}).on("submit", "form[name='tj_inserate_form_organization_name']", function (e) {
    e.preventDefault();
    let loading = $(this).find('.submit-rolling-svg');
    if (loading.length === 0) {
        addLoadingSvg($(this));
        loading = $(this).find('.submit-rolling-svg');
    }
    if (loading.is(':visible')) {
        return;
    }
    loading.show();
    $.ajax({
        type: $(this).attr('method'),
        url: $(this).attr('action'),
        data: $(this).serialize(),
        success: function (data) {
            loading.hide();
            if (data.errors) {
                for (var key in data.errors) {
                    var el = $("form[name='tj_inserate_form_organization_name']").find('input[name="' + data.errors[key]['field'] + '"]');
                    el.parent().addClass('has-error');
                    toolTipError(data.errors[key]['message'], el);
                }
            } else {
                if (data) {
                    var dataContent = {
                        content: data.html
                    };
                    updateContent(dataContent);
                    // Add an item to the history log
                    history.pushState(
                        {
                            content: data.html
                        }
                        , document.title, data.url);
                }
            }
        },
        error: function () {
            loading.hide();
            serverError();
        }
    });
    return false;
});


(function ($) {
    truncateText(".bio-content", "#bio-more-toggle", 150);
    truncateText(".app-content", "#app-more-toggle", 150);
})(jQuery);

function truncateText(selector, toggleButton, height) {
    $(selector).dotdotdot({
        height: height,
        ellipsis: " [ ... ]",
        wrap: "word",
        callback: function (isTruncated, orgContent) {
            if ($(selector).height() >= 140) {
                $(toggleButton).show();
            } else {
                $(toggleButton).hide();
            }
            $(toggleButton).on("click", function (e) {
                e.preventDefault();
                if ($(this).attr('data-truncate') === "true") {
                    $(this).attr('data-truncate', "false");
                    $(selector).trigger("destroy");
                    $(this).html(trans('organization.show.block.desc.link.readLessDesc', locale));
                } else if ($(this).attr('data-truncate') === "false") {
                    $(selector).dotdotdot({
                        height: height,
                        ellipsis: " [ ... ]",
                        wrap: "word"
                    });
                    $('html, body').animate({
                        scrollTop: $(selector).offset().top - 180
                    }, 700);
                    $(this).html(trans('organization.show.block.desc.link.readFullDesc', locale) + " →");
                    $(this).attr('data-truncate', "true");
                }
            });
        }
    });
}

var fileInputOrg = $('.imgUpload:input[type="file"]');

$(document).on('change', '#tj_inserate_form_organization_logo_path', function () {
    var reader = new FileReader(), urlBase46, urlBlob;
    var file = this.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg', 'svg']) === -1) {
        toastr.error("The uploaded file it isn't an image");
        $(this).val("");
        return;
    }
    else if (file.size === 10971520) {
        toastr.error("The max upload size is 10M");
        $(this).val("");
        return;
    }

    $(reader).load(function (e) {
        urlBase46 = e.target.result;
        urlBlob = base64toBlobUrl(urlBase46);
        resize_image(urlBlob, file, null, null, null, 0);
    });

    reader.readAsDataURL(file);
    $('form').get(0).reset();
});

function crop(base64) {
    var uploadInputProfile = $('#tj_inserate_form_organization_logo_uploadFile');
    var formOrg = $('#organizationLogo form');
    var imgCrop = $('#image_crop');
    var cropModal = $('#imageCropModal');

    imgCrop.attr('src', "");
    imgCrop.attr('src', base64);
    var $image = imgCrop;
    var $dataX = $('#dataX');
    var $dataY = $('#dataY');
    var $dataHeight = $('#dataHeight');
    var $dataWidth = $('#dataWidth');
    var $dataRotate = $('#dataRotate');
    var $dataScaleX = $('#dataScaleX');
    var $dataScaleY = $('#dataScaleY');
    var croppable = false;

    if ($(window).width() < 1024 && $(window).width() > 768) {
        var options = {
            preview: '.img-preview',
            minContainerWidth: 600,
            minContainerHeight: 400,
            reset: true,
            aspectRatio: 1,
            viewMode: 1,
            ready: function () {
                croppable = true;
            },
            crop: function (e) {
                $dataX.val(Math.round(e.x));
                $dataY.val(Math.round(e.y));
                $dataHeight.val(Math.round(e.height));
                $dataWidth.val(Math.round(e.width));
                $dataRotate.val(e.rotate);
                $dataScaleX.val(e.scaleX);
                $dataScaleY.val(e.scaleY);
            }
        };
    } else if ($(window).width() < 767 && $(window).width() > 576) {
        var crop_width = $(window).width() - 20;
        var options = {
            preview: '.img-preview',
            minContainerWidth: 600,
            minContainerHeight: 450,
            reset: true,
            aspectRatio: 1,
            viewMode: 1,
            ready: function () {
                croppable = true;
            },
            crop: function (e) {
                $dataX.val(Math.round(e.x));
                $dataY.val(Math.round(e.y));
                $dataHeight.val(Math.round(e.height));
                $dataWidth.val(Math.round(e.width));
                $dataRotate.val(e.rotate);
                $dataScaleX.val(e.scaleX);
                $dataScaleY.val(e.scaleY);
            }
        };
    } else if ($(window).width() < 575 && $(window).width() > 374) {
        var crop_width = $(window).width() - 20;
        var options = {
            preview: '.img-preview',
            minContainerWidth: 300,
            minContainerHeight: 500,
            reset: true,
            aspectRatio: 1,
            viewMode: 1,
            ready: function () {
                croppable = true;
            },
            crop: function (e) {
                $dataX.val(Math.round(e.x));
                $dataY.val(Math.round(e.y));
                $dataHeight.val(Math.round(e.height));
                $dataWidth.val(Math.round(e.width));
                $dataRotate.val(e.rotate);
                $dataScaleX.val(e.scaleX);
                $dataScaleY.val(e.scaleY);
            }
        };
    } else if ($(window).width() < 374 && $(window).width() > 300) {
        var crop_width = $(window).width() - 20;
        var options = {
            preview: '.img-preview',
            minContainerWidth: 300,
            minContainerHeight: 360,
            reset: true,
            aspectRatio: 1,
            viewMode: 1,
            ready: function () {
                croppable = true;
            },
            crop: function (e) {
                $dataX.val(Math.round(e.x));
                $dataY.val(Math.round(e.y));
                $dataHeight.val(Math.round(e.height));
                $dataWidth.val(Math.round(e.width));
                $dataRotate.val(e.rotate);
                $dataScaleX.val(e.scaleX);
                $dataScaleY.val(e.scaleY);
            }
        };
    } else {
        var options = {
            preview: '.img-preview',
            minContainerWidth: 700,
            minContainerHeight: 490,
            reset: true,
            aspectRatio: 1,
            viewMode: 1,
            ready: function () {
                croppable = true;
            },
            crop: function (e) {
                $dataX.val(Math.round(e.x));
                $dataY.val(Math.round(e.y));
                $dataHeight.val(Math.round(e.height));
                $dataWidth.val(Math.round(e.width));
                $dataRotate.val(e.rotate);
                $dataScaleX.val(e.scaleX);
                $dataScaleY.val(e.scaleY);
            }
        };
    }

    cropModal.on('hidden.bs.modal', function () {
        uploadInputProfile.val('');
        $('#button').unbind('click');
        $('.docs-buttons').unbind('click');
        $('.docs-toggles').unbind('change');
        $image.cropper('destroy');
    });

    $image.cropper(options);

    $('#button').on('click', function () {
        var croppedCanvas;
        var roundedCanvas;
        if (!croppable) {
            return;
        }
        // Crop
        croppedCanvas = $image.cropper('getCroppedCanvas');
        // Round
        roundedCanvas = getRoundedCanvas(croppedCanvas);
        var upladSrc = roundedCanvas.toDataURL();

        uploadInputProfile.val(upladSrc);

        $('.uploadingSvg .docs-buttons').hide();
        $('.uploadingSvg').append('<div class="col-md-12" id="loading-svg"><center> <img src=' + loadingSvg + '> </center></div>');

        $.ajax({
            type: formOrg.attr('method'),
            url: formOrg.attr('action'),
            data: formOrg.serialize(),
            success: function (data) {
                $('#logo-block').html(data);
            }
        }).done(function () {
            cropModal.modal("hide");
            $('#loading-svg').remove();
            $('.uploadingSvg .docs-buttons').show();
        });
    });

    if (typeof document.createElement('cropper').style.transition === 'undefined') {
        $('button[data-method="rotate"]').prop('disabled', true);
        $('button[data-method="scale"]').prop('disabled', true);
    }

    // Options
    $('.docs-toggles').on('change', 'input', function () {
        var $this = $(this);
        var name = $this.attr('name');
        var type = $this.prop('type');

        if (!$image.data('cropper')) {
            return;
        }

        if (type === 'radio') {
            options[name] = $this.val();
        }

        $image.cropper('destroy').cropper(options);
    });

    // Methods
    $('.docs-buttons').on('click', '[data-method]', function () {
        var $this = $(this);
        var data = $this.data();
        var $target;
        var result;

        if ($this.prop('disabled') || $this.hasClass('disabled')) {
            return;
        }

        if ($image.data('cropper') && data.method) {
            data = $.extend({}, data); // Clone a new one

            if (typeof data.target !== 'undefined') {
                $target = $(data.target);

                if (typeof data.option === 'undefined') {
                    try {
                        data.option = JSON.parse($target.val());
                    } catch (e) {
                        //console.log(e.message);
                    }
                }
            }

            result = $image.cropper(data.method, data.option, data.secondOption);

            switch (data.method) {
                case 'scaleX':
                case 'scaleY':
                    $(this).data('option', -data.option);
                    break;
            }

            if ($.isPlainObject(result) && $target) {
                try {
                    $target.val(JSON.stringify(result));
                } catch (e) {
                    //console.log(e.message);
                }
            }
        }
    });

    // Keyboard
    $(document.body).on('keydown', function (e) {

        if (!$image.data('cropper') || this.scrollTop > 300) {
            return;
        }

        switch (e.which) {
            case 37:
                e.preventDefault();
                $image.cropper('move', -1, 0);
                break;

            case 38:
                e.preventDefault();
                $image.cropper('move', 0, -1);
                break;

            case 39:
                e.preventDefault();
                $image.cropper('move', 1, 0);
                break;

            case 40:
                e.preventDefault();
                $image.cropper('move', 0, 1);
                break;
        }
    });

    cropModal.modal("show");
}

function updateContent(data) {
    if (data === null)
        return;

    orgContent.html(data.content);
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
}

window.addEventListener('popstate', function (event) {
    updateContent(event.state);
});

// Store the initial content so we can revisit it later
history.replaceState({content: orgContent.html()}, document.title, document.location.href);
