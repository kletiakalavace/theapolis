(function () {
    $(document).ready(function () {
        $('.fadeable-aside h3').each(function () {
            $(this).on('click', function () {
                $(this).siblings('aside').fadeToggle('slow', 'linear');
            });
        });
    });
}());
