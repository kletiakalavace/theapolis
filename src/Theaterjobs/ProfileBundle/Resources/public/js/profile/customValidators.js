var locale = window.location.pathname.split("/");


$.validator.addMethod('le', function (value, element, param) {
    if ($(param).val() == "") {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
    else if (parseInt(value) > parseInt($(param).val())) {
        return false;
    } else {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
}, profile.custom_validators.le[''+locale[1]+'']);


$.validator.addMethod('ge', function (value, element, param) {
    if ($(param).val() == "") {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
    else if (parseInt(value) < parseInt($(param).val())) {
        return false;
    } else {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
}, profile.custom_validators.ge[''+locale[1]+'']);