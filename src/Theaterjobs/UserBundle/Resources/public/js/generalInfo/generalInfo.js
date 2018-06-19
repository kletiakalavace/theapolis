/** Created by marlind on 6/9/17.*/


var formChangePassword = $('form[name="tj_user_form_master_data"]');

formChangePassword.validate({
    ignore: [],
    rules: {
        'tj_user_form_master_data[firstName]': {
            required: true
        },
        'tj_user_form_master_data_lastName': {
            required: true
        },
        'tj_user_form_master_data_billingAddress_street': {
            required: true
        },
        'tj_user_form_master_data_billingAddress_zip': {
            required: true
        },
        'tj_user_form_master_data_billingAddress_city': {
            required: true
        },
        'tj_user_form_master_data_billingAddress_country': {
            required: true
        }
    },
    errorPlacement: function (error, element) {
        toolTipError(error.text(), element);
        validFormInputs();
        return false;
    }
});


