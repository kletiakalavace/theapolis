var formImgImageEdit = $('#mediaImageEdit form:eq(0)');
addLoadingSvg(formImgImageEdit);
const loading1 = formImgImageEdit.find('#theaterjobs_profilebundle_media_image_submit .submit-rolling-svg');

formImgImageEdit.validate({
    errorPlacement: function (error, element) {
        toolTipError(error.text(), element);
    }
});
formImgImageEdit.submit(function (e) {
    e.preventDefault();
    if (loading1.is(':visible')) {return;}
    if ($(this).valid()) {
        loading1.show();
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (data) {
                $('.slider-block').html(data);
                var interval = setInterval(function () {
                    $(window).trigger("load");
                    clearInterval(interval);
                    clearModal();
                }, 3000);
            },
            error: function () {
                loading1.hide();
                serverError();
            }
        });
    }
});

var formImgImageDelete = $('#mediaImageEdit form:eq(1)');
const loading2 = formImgImageDelete.find('.submit-rolling-svg');

formImgImageDelete.submit(function (e) {
    e.preventDefault();
    if (loading2.is(':visible')) {
        return;
    }
    bootbox.confirm({
        message: trans('bootbox.wantToDelete', locale),
        buttons: {
            confirm: {
                label: trans('bootbox.button.yes', locale),
                className: 'btn-success'
            },
            cancel: {
                label: trans('bootbox.button.no', locale),
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (result) {
                loading2.show();
                $.ajax({
                    type: "DELETE",
                    url: formImgImageDelete.attr('action'),
                    success: function (data) {
                        var previousChildrenNr = $('.display-slider-item').length;
                        $('.slider-block').html(data);
                        sliderInterval(previousChildrenNr);
                        clearModal();
                    },
                    error: function () {
                        loading2.hide();
                        serverError();
                    }
                });
            }
        }
    });
});

//Remove loading bar,modal
function clearModal() {
    loading1.hide();
    loading2.hide();
    $('#myModal').modal('hide');
}