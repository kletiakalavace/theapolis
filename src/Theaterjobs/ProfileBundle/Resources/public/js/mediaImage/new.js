const fileInputImage = $('#mediaImage .profileUpload:input[type="file"]');
const uploadInputImage = $('#mediaImage .imageSrc:input');
var formImgImage = $('#mediaImage form');
const defaultImg = $('#mediaBackground').attr('src');
addLoadingSvg(formImgImage);
const loading = formImgImage.find('.submit-rolling-svg');

formImgImage.validate({
    ignore: [],
    rules: {
        'theaterjobs_profilebundle_media_image[path]': {
            required: true,
            fileSize: 10, // mb
            fileType: ['gif', 'png', 'jpg', 'jpeg', 'svg']
        }
    },
    messages: {
        'theaterjobs_profilebundle_media_image[path]': {
            required: trans('profile.mediaImage.photo.required', locale)
        }
    },
    errorPlacement: function (error, element) {
        debugger
        if ($(element).attr('type') === 'file') {
            customErrorPlacement(error.text());
        } else {
            toolTipError(error.text(), element);
        }
    },
    unhighlight: function (element) {
        if ($(element).attr('type') === 'file') {
            removeCustomError();
        } else {
            $(element).tooltip('destroy');
            $(element).parent().tooltip('destroy');
            $(element).closest('.has-error').removeClass('has-error');
        }
    },
    submitHandler: function (form, event) {
        event.preventDefault();
        if (loading.is(':visible')) {return;}
        loading.show();
        $.ajax({
            type: $(form).attr('method'),
            url: $(form).attr('action'),
            data: $(form).serialize(),
            success: function (data) {
                loading.hide();
                if (data.error) {
                    if (data.errorMsg) {
                        customErrorPlacement(data.errorMsg);
                    } else {
                        renderFormErrors(data.errors, formImgImage);
                    }
                    return;
                }
                const previousChildrenNr = $('.display-slider-item').length;
                $('.slider-block').html(data.data);
                sliderInterval(previousChildrenNr);
                clearModal();
            },
            error: function () {
                loading.hide();
                serverError();
            }
        });
    }
});

fileInputImage.change(function ($this) {
    if(!$($this.target).valid()) {
        return;
    }
    previewImage($this.target);
    const profileMediaSelector = $('#profile-media');
    const uploadLabel = profileMediaSelector.find("label.upload-block");
    uploadLabel.hide();
    const removeButt = '<label class="remove-image-button-news" id="remove-button">' +
        '<svg class="icon-svg icon-svg-inverse icon-inline" width="20" height="20">' +
        '<use xlink:href=" ' + smallCloseSvg + ' "></use></svg></label>';
    profileMediaSelector.append(removeButt);

    // image remove
    $('#remove-button').click(function () {
        removeCustomError();
        resetFile($('#theaterjobs_profilebundle_media_image_path'));
        $('#theaterjobs_profilebundle_media_image_uploadFile').val('');
        $('#mediaBackground').attr('src', defaultImg);
        $(this).remove();
        uploadLabel.show();
    });
});

//Remove loading bar,modal
function clearModal() {
    $('#myModal').modal('hide');
    fileInputImage.val("");
    uploadInputImage.val("");
}

function customErrorPlacement(text) {
    $('#span-text-danger').text(text);
    $('.text-danger-custom').removeClass('hidden');
}

function removeCustomError() {
    $('.text-danger-custom').addClass('hidden');
}

function addImage(url) {
    uploadInputImage.val(url);
    $('#mediaBackground').attr('src', base64toBlobUrl(url));
}