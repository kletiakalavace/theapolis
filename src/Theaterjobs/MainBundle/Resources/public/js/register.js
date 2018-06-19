$(function () {
    var terms = $("#terms");
    var modalBody = $('.modal .modal-body');
    var progressBar = $('#strength-bar');
    terms.removeClass('hidden');

    $('#fos_user_registration_form_plainPassword_first').complexify({}, function (valid, complexity) {

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

            progressBar.val(trans('password.register.empty', locale));
        }
    });
    var linkTerms = "<a id='termsTab'>" + terms.text() + "</a> " + trans('registration.label.and', locale) + " <a id='dataSecurityTab'>" + trans('registration.label.dataSecurity', locale) + "</a>";
    terms.html(terms.html().replace(terms.text(), trans('registration.label.readAccept', locale)));
    $('.checkbox').append(linkTerms);
    $("#termsTab").click(function () {
        var win = window.open(termsUrl, '_blank');
        if (win) {
            //Browser has allowed it to be opened
            win.focus();
        } else {
            //Browser has blocked it
            alert('Please allow popups for this website');
        }

    });
    $("#dataSecurityTab").click(function () {
        var win = window.open(privacyUrl, '_blank');
        if (win) {
            //Browser has allowed it to be opened
            win.focus();
        } else {
            //Browser has blocked it
            alert('Please allow popups for this website');
        }

    });
});