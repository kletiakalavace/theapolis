const url = Routing.generate('tj_news_ckeditor_upload',{'_locale': locale});
var textareaId = $('.ckeditor').attr('id');
const formNews = $('form[name="news"]');

CKEDITOR.replace(textareaId, {
    customConfig: ckConfig,
    filebrowserImageUploadUrl: url
});

CKEDITOR.instances[textareaId].on('change', function () {
    if (CKEDITOR.instances[textareaId].getData() == '')
        CKEDITOR.instances[textareaId].document.getBody().setStyle('background-color', '#F0BCC3');
    else
        CKEDITOR.instances[textareaId].document.getBody().setStyle('background-color', '#fff');

    CKEDITOR.instances[textareaId].updateElement();
});

formNews.validate({
    errorPlacement: function (error, element) {
        if (element.attr("id") == textareaId) {
            CKEDITOR.instances[textareaId].document.getBody().setStyle('background-color', '#F0BCC3');
        } else {
            toolTipError(error.text(), element);
        }
        validFormInputs();
        return false;
    },
    ignore: [],
    rules: {
        'news[description]': {
            required: true
        }
    }
});
//Initialize map on form
initialize();

usersAutocomplete($('#news_users'), inputTerm, initUsers);
