/**
 * Created by Marlind Parllaku on 9/7/17.
 */

var textAreaId = $('.ckeditor').attr('id');
var contentCKE = CKEDITOR.instances[textAreaId];

let backgroundCKE='#FFFFFF';

contentCKE.on('change', function () {
    if (contentCKE.getData().length===0){
        backgroundCKE='#F0BCC3';
    }

    contentCKE.document.getBody().setStyle('background-color', backgroundCKE);
    contentCKE.updateElement();
});

var form = $('#application_track_form');

function customAlert(appendTo, status, text) {
    var elementBox = '<div class="message-info message-' + status + '">' + text + '<span class="close-message">' +
        '<svg class="icon-svg icon-svg-inverse" width="20" height="20">' +
        '<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'+closeIconSrc+'"></use></svg></span></div>';
    $(elementBox).insertBefore(appendTo)
    $('html, body').animate({scrollTop: '0px'}, 300);
}


function customErrorPlacement(text) {
    $('#span-text-danger').text(text);
    $('.text-danger-custom').removeClass('hidden');
}

$(document).on('click', '.close-message', function () {
    $(this).parent().fadeOut("400");
});

form.submit(function(e) {
    e.preventDefault();
}).validate({
    //To validate the fields tha are hidden (CKEditor textareas)
    ignore: [],
    rules: {
        'theaterjobs_inseratebundle_applicationtrack[email]': {
            required: true,
            email: true,
        }
        ,'theaterjobs_inseratebundle_applicationtrack[content]': {
            required: true,
        }
    },
    messages: {
        'theaterjobs_inseratebundle_applicationtrack[email]' : {
            email: trans('application.email.field.enterValidEmail', locale),
        }
    },
    invalidHandler: function(form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {
            customErrorPlacement(trans('application.form.checkForInvalidFields', locale));
            validator.errorList[0].element.focus();
        }
    },
    errorPlacement: function (error, element) {
        if (element.attr("id") !== textAreaId) {
            toolTipError(error.text(), element);
        }

    },
    // Highlight error inputs
    highlight: function (element) {
        if ($(element).attr("id") === textAreaId) {
            contentCKE.document.getBody().setStyle('background-color', '#F0BCC3');
        }
        else {
            $(element).closest('.form-group').addClass('has-error');
        }
    },
    // Revert the change done by highlight
    unhighlight: function (element) {

        if ($(element).attr("id") === textAreaId) {
            contentCKE.document.getBody().setStyle('background-color', '#FFFFFF');
        }
        else {
            $(element).tooltip('destroy');
            $(element).closest('.form-group').removeClass('has-error');
        }
    },
    submitHandler: function(form) {
        $.ajax({
            type: $(form).attr('method'),
            url: $(form).attr('action'),
            data: new FormData(form),
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.error) {
                    if(data.errors) {
                        for (var key in data.errors) {
                            var el = $(form).find('input[name="' + key + '"]');
                            el.parent().addClass('has-error');
                            toolTipError(data.errors[key], el);
                        }
                    }
                    else{
                        bootbox.alert({message: data.message});
                    }
                }
                else {
                    $('#applyButton').remove();
                    $('.apply-job').prepend(data.appliedInfo);
                    customAlert('.block-progress-job ', 'success', data.message);
                    $('#myModal').modal('hide');
                }
            }
        });
    }
});

initialize();