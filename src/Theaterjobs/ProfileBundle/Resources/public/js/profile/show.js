//Constants
const MAX_PROFILE_SIZE = 10971520;

/**
 * Unpublish profile first popup
 */
function alertPublish() {
    bootbox.alert({
        message: '<p>' + trans('profile.unpublished.first', locale) + '</p>'
    });
}

/**
 * Read more animation on profile bio
 * @param selector Content
 * @param toggleButton Read more button
 * @param height min height of content so button is shown
 */
function truncateText(selector, toggleButton, height) {
    const ellipsis = ' [...]';
    $(selector).dotdotdot({
        height: height,
        ellipsis: ellipsis,
        wrap: "word",
        callback: function (isTruncated, orgContent) {
            if ($(".bio-content").text().indexOf(ellipsis) !== -1) {
                $('#bio-more-toggle').show();
            } else {
                $('#bio-more-toggle').hide();
            }
            $(toggleButton).on("click", function (e) {
                e.preventDefault();
                if ($(this).attr('data-truncate') === "true") {
                    $(this).attr('data-truncate', "false");
                    $(selector).trigger("destroy");
                    $(this).html(trans('link.readLess', locale));
                } else if ($(this).attr('data-truncate') === "false") {
                    $(selector).dotdotdot({
                        height: height,
                        ellipsis: "<div>[ ... ]</div>",
                        wrap: "word"
                    });
                    $('html, body').animate({
                        scrollTop: $(selector).offset().top - 180
                    }, 700);
                    $(this).html(trans('link.readFullBio →', locale));
                    $(this).attr('data-truncate', "true");
                }
            });
        }
    });
}

/**
 * Uploads profile picture
 * @param $this
 */
function profileUpload($this) {
    var reader = new FileReader();
    //urlBase46, urlBlob;
    var file = $this.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg', 'svg']) === -1) {
        toastr.error("The uploaded file it isn't an image");
        $($this).val("");
        return;
    }
    else if (file.size >= MAX_PROFILE_SIZE) {
        bootbox.alert({
            message: '<p class="text-center">' + trans("max.upload.profile.pixc.size.is.10M", locale) + '</p>'
        });
        $(this).val("");
        return;
    }

    $(reader).load(function (e) {
        var urlBase46 = e.target.result;
        var urlBlob = base64toBlobUrl(urlBase46);
        resize_image(urlBlob, file, null, null, null, 0);
    });

    reader.readAsDataURL(file);
    $("form[name=theaterjobs_profilebundle_media_image]").get(0).reset();
}

/**
 * Crop uploaded profile picture
 * @param base64
 */
function crop(base64) {
    const filtersDiv = $('#filters');

    var uploadInputProfile = $('.imageSrc:input');
    const filter = $('#theaterjobs_profilebundle_media_image_filter');
    var formProfile = $('#profilePicture');
    var imgCrop = $('#image_crop');
    var cropModal = $('#imageCropModal');

    $('#loadFilters').attr('data-url', base64toBlobUrl(base64));
    imgCrop.attr('src', "");
    // reset current filter
    filter.val('');
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
        filtersDiv.html('');
        filtersDiv.addClass('hidden');
        $image.cropper('destroy');
    });

    $image.cropper(options);

    $('#button').on('click', function () {

        var validCopyright = validateCopyrightInput();
        if(! validCopyright){
            return;
        }

        //We get the value from the action-less form field to pass it to the the hidden field of the profile image form type.
        $('#theaterjobs_profilebundle_media_image_copyrightText').val($('#image_media_copyright_input').val());

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
            type: formProfile.attr('method'),
            url: formProfile.attr('action'),
            data: formProfile.serialize(),
            success: function (data) {

            },
            error: function () {
                cropModal.modal("hide");
                $('#loading-svg').remove();
                $('.uploadingSvg .docs-buttons').show();
            }
        }).done(function (data) {
            if (data.success) {
                // current el of profile photo in nav
                const modalImg = $('#modalProfileImg');
                // current el of profile photo in show
                const profileImg = $('#file_preview');

                modalImg.attr('src', base64toBlobUrl(upladSrc));
                modalImg.parent().attr('class', filter.val());
                profileImg.parent().attr('class', filter.val());
                profileImg.attr('src', base64toBlobUrl(upladSrc));
                $('#loading-svg').remove();
                $('.uploadingSvg .docs-buttons').show();
            }
            cropModal.modal("hide");
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

/**
 * Publish profile publicswith button
 */
function changeStatus(status, slug) {
    const loading = $('#profile-button-publish .submit-rolling-svg');
    if (loading.is(':visible')) {return;}

    const url = Routing.generate('tj_profile_user_publish', {'slug':slug, '_locale': locale});
    const data = {status};
    loading.show();
    $.ajax({
        url,
        type: 'PUT',
        data,
        dataType: 'json',
        success: function (data) {
            loading.hide();
            if (data.error) {
                bootbox.dialog({message: data.text});
                return;
            }
            const statistics = $('#statistics');
            const publish = $('#profile-button-publish');
            const unpublish = $('#profile-icon-unpublish');
            if (data.unpublish) {
                publicswitch = 0;
                statistics.addClass('hidden');
                unpublish.addClass('hide');
                publish.removeClass('hide');
                showFlashMessage(data.text, 'success', '.page-wrap');
            } else if (data.publish) {
                publicswitch = 1;
                statistics.removeClass('hidden');
                publish.addClass('hide');
                unpublish.removeClass('hide');
                showFlashMessage(data.text, 'success', '.page-wrap');
            }
        },
        error: function (err, data) {
            loading.hide();
            bootbox.alert({message: err.statusText});
        }
    });
}

/**
 * Ask for permissions to un publish profile
 * @param slug
 */
function askPermission(slug) {
    bootbox.confirm({
        message: trans('profile.unpublish.bootbox.confirmtext', locale) + "?",
        buttons: {
            confirm: {
                label: trans('profile.unpublish.bootbox.button.Yes', locale),
                className: 'btn-success'
            },
            cancel: {
                label: trans('profile.unpublish.bootbox.button.No', locale),
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (result) {
                changeStatus(0, slug);
            }
        }
    });
};

/**
 * Render flash message
 * @param message
 * @param type
 * @param element
 */
function showFlashMessage(message, type, element) {

    var msg = '<div class="flash-message message-info message-'+type+'">'+ message +
                 '<span class="close-message">' +
                    '<svg class="icon-svg icon-svg-inverse" width="20" height="20">' +
                       '<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'+smallCloseSvg+'"></use>' +
                    '</svg>' +
                 '</span>' +
             '</div>';
    $(element).prepend(msg);
    window.setTimeout(function () {
        $('.flash-message').last().remove();
    }, 2000);
};

/**
 * Prevent to add new pictures
 * Button for new picture in profile category
 */
$('.circle').click(function (e) {
    if (parseInt($(this).attr('data-count')) >= parseInt($(this).attr('data-limit'))) {
        e.preventDefault();
        e.stopPropagation();
        toastr.error('you have reached the limit of this media upload!');
    }
});

/**
 * Photo uploaded is not correct
 */
$('#addImageGalery').click(function (e) {
    if (typeof $('#file_preview').attr('src') === "undefined") {
        e.preventDefault();
        e.stopPropagation();
        bootbox.alert({
            message: '<p class="text-center">' + trans("profile.addPhoto", locale) + '</p>'
        });
    }
});

$(document).on('click', "#open-action-panel", function () {
    var panelAction = $(".action-panel-buttons");
    var circleButtons = $(".circle");
    var closeTime;

    if (panelAction.attr("data-action-open") === "true") {

        circleButtons.each(function (i) {
            closeTime = 30 * (i + 1);
            setTimeout(function () {
                circleButtons.eq(i).css({
                    "transform": "scale(0) translateX(-200px) rotateY(90deg)"
                });
            }, 10 * (i + 1));
        });

        panelAction.attr("data-action-open", "false");
        setTimeout(function () {
            panelAction.css({
                "opacity": "0"
            });
        }, closeTime);


    } else if (panelAction.attr("data-action-open") === "false") {
        panelAction.attr("data-action-open", "true");

        panelAction.css({
            "opacity": "1"
        });

        circleButtons.each(function (i) {
            setTimeout(function () {
                circleButtons.eq(i).css({
                    "transform": "scale(1) translateX(0) rotateY(0deg)"
                });
            }, 10 * (i + 1));
        });


    }


});

// Function Calls
//Short text for bio
truncateText(".bio-content", "#bio-more-toggle", 150);