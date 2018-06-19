const editFormAudio = $('#edit_audio_form');
const requiredCopyright = $('#theaterjobs_profilebundle_media_audio_copyrightText').val() !== ""; //If there was a value than an image was entered so the copyright is required.
addLoadingSvg(editFormAudio);
const loading1 = editFormAudio.find('#theaterjobs_profilebundle_media_audio_submit .submit-rolling-svg');

editFormAudio.validate({
    rules: {
        "theaterjobs_profilebundle_media_audio[title]": {
            maxlength: 250,
            required: true
        },
        "theaterjobs_profilebundle_media_audio[copyrightText]": {
            required: requiredCopyright
        }
    },
    messages: {
        "theaterjobs_profilebundle_media_audio[title]": {
            required: trans('profile.media.audio.insertTitle', locale)
        },
        "theaterjobs_profilebundle_media_audio[copyrightText]": {
            required: trans('profile.media.audio.insertCopyrightText', locale)
        }
    },
    errorPlacement: function (error, element) {
        toolTipError(error.text(), element);
    },
    unhighlight: function (element) {
        $(element).tooltip('destroy');
        $(element).parent().tooltip('destroy');
        $(element).closest('.has-error').removeClass('has-error');
    },
    submitHandler: function (element) {
        loading1.show();
        $.ajax({
            type: $(element).attr('method'),
            url: $(element).attr('action'),
            data: new FormData(element),
            processData: false,
            contentType: false,
            success: function (data) {
                loading1.hide();
                const previousChildrenNr = $('.display-slider-item').length;
                $('.slider-block').html(data.data);
                sliderInterval(previousChildrenNr);
                $('#myModal').modal('hide');
            },
            error: function () {
                loading1.hide();
                serverError();
            }
        });
    }
});

function customErrorPlacement(text) {
    $('#span-text-danger').text(text);
    $('.text-danger-custom').removeClass('hidden');
    $('#myModal').animate({scrollTop: $('.text-danger-custom').offset().top + 2000}, 100);
}

$('#editAudio').setWavesurfer();

const formAudioDelete = $('#mediaEditAudio form:eq(1)');
const loading2 = formAudioDelete.find('.submit-rolling-svg');
formAudioDelete.submit(function (e) {
    e.preventDefault();
    bootbox.confirm({
        message: trans('bootbox.wantToDelete', locale),
        buttons: {
            confirm: {
                label: trans('bootbox.button.yes', locale),
                className: 'btn-success'
            },
            cancel: {
                label: trans('bootbox.button.no', locale),
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (result) {
                loading2.show();
                $.ajax({
                    type: "DELETE",
                    url: formAudioDelete.attr('action'),
                    success: function (data) {
                        loading2.hide();
                        $('.slider-block').html(data);
                        const interval = setInterval(function () {
                            $(window).trigger("load");
                            clearInterval(interval);
                        }, 3000);
                        $('#myModal').modal('hide');
                    },
                    error: function () {
                        loading2.hide();
                        serverError();
                    }
                });
            }
        }
    });
});

