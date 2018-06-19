let iban = $('#theaterjobs_membership_debit_account_type_iban');

/**
 * Functions
 */

/**
 * @param data
 */
function updateDebitAccount(data){
    if (data.bic !== "XXX") {
        //Add bank information
        if (data.bankName != null) {
            $("#bank_name_updated").text(trans('membership.new.bankName', locale) + ': ' + data.bankName);
        }

        //Remove tooltip error from input manually
        var self = $("#theaterjobs_membership_booking_type_debitAccount_iban");
        self.closest('.has-error').removeClass('has-error');
        if (typeof self.attr('aria-describedby') !== "undefined") {
            self.tooltip('destroy');
            self.removeAttr('aria-describedby');
        }
    } else {
        $("#bank_name_updated").text("");
    }
}

/**
 * Form validation
 */

form.validate({
    ignore: [],
    rules: {
        'theaterjobs_membership_debit_account_type[accountHolder]': {
            required: true
        },
        'theaterjobs_membership_debit_account_type[iban]': {
            required: true,
            remote: {
                required: true,
                url: Routing.generate('tj_membership_validate_iban'),
                type: "GET",
                data: {
                    iban: function () {
                        return iban.val();
                    }
                }
            }
        }
    },
    errorPlacement: function (error, element) {
        toolTipError(error.text(), element);
        $('.text-danger').removeClass('hidden');
        $('#myModal').animate({ scrollTop: $('.login-error-content').offset().top + 2000 }, 100);
    },
    unhighlight: function (element) {
        $(element).tooltip('destroy');
        $(element).parent().tooltip('destroy');
        $(element).closest('.has-error').removeClass('has-error');
    },
    submitHandler: function (form, e) {
        e.preventDefault();
        let valid = false;
        $.ajax({
            type: $(form).attr('method'),
            url: $(form).attr('action'),
            data: new FormData(form),
            processData: false,
            contentType: false,
            success: function (data) {
                if (!data.success) {
                    renderFormErrors(data.errors, $(form));
                } else {
                    customAlert('#accountSettingsBlock', 'success', data.message);
                    $('#bankingData').html(data.data);
                    valid = true;
                }
            }
        }).done(function () {
            if (valid) {
                renderBoxes();
            }
        });
    }
});


iban.focusout(function () {
    $.ajax({
        type: "GET",
        url: Routing.generate('tj_membership_generate_bic'),
        data: {iban: iban.val()},
        success: function (data) {
            updateDebitAccount(data);
        }
    });
});