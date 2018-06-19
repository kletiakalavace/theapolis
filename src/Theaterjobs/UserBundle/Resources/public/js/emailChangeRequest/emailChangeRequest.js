/** Created by marlind on 6/9/17.*/


var formChangePassword = $('form[name="theaterjobs_userbundle_emailchangerequest"]');

formChangePassword.validate({
    ignore: [],
    rules: {
        'theaterjobs_userbundle_emailchangerequest[newMail][first]': {
            required: true,
            email: true
        },
        'theaterjobs_userbundle_emailchangerequest[newMail][second]': {
            required: true,
            email: true,
            equalTo: '#theaterjobs_userbundle_emailchangerequest_newMail_first'
        }
    },
    messages: {
        'theaterjobs_userbundle_emailchangerequest[newMail][first]': {
            required: "New email is required.",
            email: "Please insert a valid email."
        },
        'theaterjobs_userbundle_emailchangerequest[newMail][second]': {
            required: "Email confirmation is required.",
            equalTo: "Email confirmation doesn't matches to new email."
        },
        "login[username]": {
            required: false
        }
    },
    errorPlacement: function (error, element) {
        // toolTipError(error.text(), element);
        // validFormInputs();
        // return false;
        toolTipError(error.text(), element);
        $('.text-danger').removeClass('hidden');
    }
});

formChangePassword.submit(function (e) {
    e.preventDefault();
    var valid = false;
    validFormInputs();
    if ($(this).valid()) {
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.error) {
                    for (var i = 0; i < data.errors.length; i++) {
                        var el = $(data.errors[i].field);
                        el.parent().addClass('has-error');
                        el.tooltip('destroy');
                        toolTipError(data.errors[i].message, el);
                    }
                } else {
                    valid = true;
                }
            },
            error: function () {
                customAlert('#accountSettingsBlock', 'error', 'There was an error with your request.Please refresh and try again.');
            }
        }).done(function (data) {
            if (valid) {
                $('#myModal').modal('hide');
                customAlert('#accountSettingsBlock', 'success', trans('accountSettings.emailchange.awaiting.approval', locale));
            }
        });
    }
});

/**
 * Marks email of user as fixed
 */
$('#emailFix').click(function (el) {
    el.preventDefault();
    $.ajax({
        url: Routing.generate('tj_user_email_change_fix', {'_locale': locale, slug}),
        dataType: "json",
        success: function (data) {
            if (data.error) {
                bootbox.alert({message: data.error})
            }
            $('#myModal').modal('hide');
            customAlert('#accountSettingsBlock', 'success', trans('accountSettings.email.fix.success', locale));
        }
    });
});