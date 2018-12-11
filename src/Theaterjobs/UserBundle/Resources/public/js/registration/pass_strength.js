$(document).ready(function () {
//    $('#fos_user_registration_form_plainPassword_first').data('indicator', 'pwindicator');
//    $('#fos_user_registration_form_plainPassword_first').parent().append('<div id="pwindicator"><div class="bar"></div><div class="label"></div></div>');
    $("#first div").css("width","0");
    $("#first").addClass("progress-bar-success");
    jQuery(function ($) {
        $('#fos_user_registration_form_plainPassword_first').pwstrength();
    });
    
    $('#tj_user_form_change_password_plainPassword_first').data('indicator', 'pwindicator');
    $('#tj_user_form_change_password_plainPassword_first').parent().append('<div id="pwindicator"><div class="bar"></div><div class="label"></div></div>');
    jQuery(function ($) {
        $('#tj_user_form_change_password_plainPassword_first').pwstrength();
    });
    
    $('#fos_user_resetting_form_plainPassword_first').data('indicator', 'pwindicator');
    $('#fos_user_resetting_form_plainPassword_first').parent().append('<div id="pwindicator"><div class="bar"></div><div class="label"></div></div>');
    jQuery(function ($) {
        $('#fos_user_resetting_form_plainPassword_first').pwstrength();
    });
    
});