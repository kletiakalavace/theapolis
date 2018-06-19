function animateToMsg() {
    var lastMessage = $('.singleMessage').last()[0];
    if(lastMessage !== undefined) {
        lastMessage.scrollIntoView({
            behavior: 'smooth'
        });
    }
    $('form textarea')[0].focus();
}

$('.content-messenger').on('click', '#newButton', function (e) {
    e.preventDefault();
    validFormInputs();
    var form = $('#newForm');
    var dt = form.serialize();

    if (form.valid()) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('tj_message_thread_new', {'_locale': locale}),
            data: dt,
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    window.location.href = data.route;
                    animateToMsg();
                } else {
                    bootbox.alert({
                        message: showErrors(data.message)
                    })
                }

            },
            error: function (xhr, status, error) {
                bootbox.alert({
                    title: false,
                    message: error
                })
            }
        });
    }
});


/*
 form validation
 */
$('#newForm').validate({
    errorPlacement: function (error, element) {
        toolTipError(error.text(), element);
        $('.text-danger').removeClass('hidden');
    }
});