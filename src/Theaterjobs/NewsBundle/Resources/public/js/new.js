//Constant vars
const FORM_NAME = 'form[name="news"]';
const FORM_DOM = $(FORM_NAME);

FORM_DOM.submit(function (e) {
    e.preventDefault();
    let valid = false;
    if (CKEDITOR.instances[textareaId].getData() === ''){
        CKEDITOR.instances[textareaId].document.getBody().setStyle('background-color', '#F0BCC3');
        return;
    }

    if ($(this).valid()) {
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.errors) {
                    for (let key in data.errors) {
                        let el = FORM_DOM.find('input[name="' + key + '"]');
                        el.parent().addClass('has-error');
                        toolTipError(data.errors[key], el);
                    }
                } else if (data.success === 1) {
                    valid = true;
                }
            }
        }).done(function (data) {
            if (valid) {
                window.location.replace(data.redirect);
            }
        });
    }
});
validFormInputs();