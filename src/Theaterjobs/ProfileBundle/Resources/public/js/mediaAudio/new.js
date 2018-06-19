const fileInputAudio = $('.uploadAudio :input');
const fileInputAudioImage = $('.uploadAudioImage :input');
const copyrightField = $('#theaterjobs_profilebundle_media_audio_copyrightText');
const newFormAudio = $('#new_audio_form');
const defaultImg = $('#medianews').attr('src');

addLoadingSvg(newFormAudio);
const loading = newFormAudio.find('.submit-rolling-svg');
// validFormInputs();

newFormAudio.validate({
    ignore: [], // validate all fields including form hidden input
    rules: {
        "theaterjobs_profilebundle_media_audio[title]": {
            maxlength: 250,
            required: true
        },
        "theaterjobs_profilebundle_media_audio[uploadFile][file]": {
            required: true,
            fileSize: 10, // mb
            fileType: ['ogg', 'mp3', 'wav', 'm4a']
        },
        "theaterjobs_profilebundle_media_audio[uploadFileImage][file]": {
            required: false,
        }
    },
    messages: {
        "theaterjobs_profilebundle_media_audio[title]": {
            required: trans('profile.media.audio.insertTitle', locale)
        },
        "theaterjobs_profilebundle_media_audio[uploadFile][file]": {
            required: trans('profile.media.audio.addAudio', locale)
        },
        "theaterjobs_profilebundle_media_audio[copyrightText]": {
            required: trans('profile.media.audio.insertCopyrightText', locale)
        }
    },
    errorPlacement: function (error, element) {
        debugger;
        if ($(element).attr('type') === 'file') {
            if ($('.text-danger-custom').hasClass('hidden')) {
                customErrorPlacement(error.text(), element.attr('id'));
            } else if (element[0].files.length) {
                customErrorPlacement(error.text(), element.attr('id'));
            }
        } else {
            toolTipError(error.text(), element);
        }
    },
    unhighlight: function (element) {
        debugger;
        if ($(element).attr('type') === 'file') {
            removeCustomError(element.id);
        } else {
            $(element).tooltip('destroy');
            $(element).parent().tooltip('destroy');
            $(element).closest('.has-error').removeClass('has-error');
        }
    },
    submitHandler: function (element, e) {
        if (!copyrightField.valid()) {
            // e.preventDefault();
            return;
        }
        if (loading.is(':visible')) {return;}
        loading.show();
        $.ajax({
            type: $(element).attr('method'),
            url: $(element).attr('action'),
            data: new FormData(element),
            processData: false,
            contentType: false,
            success: function (data) {
                loading.hide();
                if (data.error) {
                    if (data.errorMsg) {
                        customErrorPlacement(data.errorMsg);
                    } else {
                        renderFormErrors(data.errors, newFormAudio);
                    }
                    return;
                }
                var previousChildrenNr = $('.display-slider-item').length;
                $('.slider-block').html(data.data);
                sliderInterval(previousChildrenNr);
                $('#myModal').modal('hide');
            },
            error: function () {
                loading.hide();
                serverError();
            }
        });
    }
});

function customErrorPlacement(text, key) {
   const txtDanger = $('#span-text-danger');
    txtDanger.text(text);
    $('.text-danger-custom').removeClass('hidden');
    if (key) {
        txtDanger.data(key, text);
    }
}

function removeCustomError(id) {
    if (typeof id == 'undefined' || $('#span-text-danger').data(id) === $('#span-text-danger').html()) {
        $('#span-text-danger').data(id, "");
        $('.text-danger-custom').addClass('hidden');
    }
}

fileInputAudio.change(function ($e) {
    if (!$($e.target).valid()) {
        return;
    }
    const audioPlayer = $('#player');
    previewAudio($e.target, audioPlayer);

    const profileMediaSelector = $('#profile-media');
    const uploadLabel = profileMediaSelector.find("label.upload-block");

    const removeButt = '<label class="remove-image-button-news" id="remove-audio-button">' +
        '<svg class="icon-svg icon-svg-inverse icon-inline" width="20" height="20">' +
        '<use xlink:href="' + smallCloseSvg + '"></use></svg></label>';

    profileMediaSelector.append(removeButt);
    uploadLabel.hide();
    audioPlayer.css('display', 'block');

    $('#remove-audio-button').click(function () {
        removeCustomError(fileInputAudio.attr('id'));
        resetFile(fileInputAudio);
        $(this).remove();
        uploadLabel.show();
        audioPlayer[0].pause();
        audioPlayer[0].currentTime = 0;
        audioPlayer.css('display', 'none');
    });
    $(this).validate()
});

fileInputAudioImage.change(function ($this) {
    if (!$($this.target).valid()) {
        return;
    }
    previewImage($this.target, $('#medianews'));
    // Copyright required if there is an image
    copyrightField.rules("add", "required");
    fileInputAudioImage.rules("add", {
        fileSize: 2,
        fileType: ['gif', 'png', 'jpg', 'jpeg', 'svg']
    });
    copyrightField.prop('disabled', false);

    const imageSectionSelector = $('#audio-image');
    const uploadLabel = imageSectionSelector.find("label.upload-block");
    const removeButt = '<label class="remove-image-button-news" id="remove-button">' +
        '<svg class="icon-svg icon-svg-inverse icon-inline" width="20" height="20">' +
        '<use xlink:href=" ' + smallCloseSvg + ' "></use></svg></label>';

    imageSectionSelector.append(removeButt);
    uploadLabel.hide();

    // image remove
    $('#remove-button').click(function () {
        removeCustomError(fileInputAudioImage.attr('id'));
        //If the user removes the image for the audio thumbnail, we should make the copyright field non required
        //and validate in case it has thrown an error previously.
        copyrightField.rules("remove", "required");
        fileInputAudioImage.rules("remove", "fileSize fileType");
        copyrightField.prop('disabled', true);
        copyrightField.valid();

        resetFile(fileInputAudioImage);
        $('#medianews').attr('src', defaultImg);
        $(this).remove();
        uploadLabel.show();
    });
});