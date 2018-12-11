/** Created by marlind on 6/9/17.*/


var formChangePassword = $('form[name="tj_user_form_change_password"]');

formChangePassword.validate({
    ignore: [],
    rules: {
        'tj_user_form_change_password[password]': {
            required: true,

        },
        'tj_user_form_change_password[plainPassword][first]': {
            required: true,
            minlength: 8
        },
        'tj_user_form_change_password[plainPassword][second]': {
            required: true,
            equalTo: '#tj_user_form_change_password_plainPassword_first'
        }
    },
    messages: {
        'tj_user_form_change_password[password]': {
            required: trans('tooltip.oldPasswordRequired', locale)
        },
        'tj_user_form_change_password[plainPassword][first]': {
            required: trans('tooltip.newPasswordRequired', locale),
            minlength: jQuery.validator.format(trans('tooltip.password.validate.Length', locale))
        },
        'tj_user_form_change_password[plainPassword][second]': {
            required: trans('tooltip.passwordconfirmationRequired', locale),
            equalTo: trans('tooltip.passwordconfirmationdontMatch', locale)
        }
    },
    errorPlacement: function (error, element) {
        toolTipError(error.text(), element);
        return false;
    }
});

var progressBar = $('#strength-bar');

$('#tj_user_form_change_password_plainPassword_first').complexify({}, function (valid, complexity) {

    if ($(this).val().length === 0) {
        progressBar.css({
            "background-color": '#EEEEEE !important',
            "border-color": '#a9a9a9',
            "color": "#a9a9a9"
        });

        progressBar.val("");
    }
    if (complexity < 35 && complexity > 0) {
        progressBar.css({
            "background-color": "#cd2036 !important",
            "border-color": "#a9a9a9",
            "color": "#ffffff",
            "font-size": "17px"
        });
        progressBar.val(trans('password.tooWeak', locale));

    } else if ((complexity > 35) && (complexity < 60)) {
        progressBar.css({
            "background-color": "#6d6d6d !important",
            "border-color": "#6d6d6d",
            "color": "#ffffff",
            "font-size": "17px"
        });
        progressBar.val(trans('password.weak', locale));

    } else if (complexity > 60) {
        progressBar.css({
            "background-color": "#62c39f !important",
            "border-color": "#62c39f",
            "color": "#ffffff",
            "font-size": "17px"
        });
        progressBar.val(trans('password.strong', locale));
    }

}).keyup(function () {
    if ($(this).val().length === 0) { //if password field is empty
        progressBar.css({
            "background-color": '#EEEEEE !important',
            "border-color": '#a9a9a9',
            "color": "#a9a9a9"
        });

        progressBar.val(trans('password.empty', locale));
    }
});

initialize();


