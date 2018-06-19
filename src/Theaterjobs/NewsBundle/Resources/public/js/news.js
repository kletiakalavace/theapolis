var newsForm = $('#news-block form');

$(function () {
    if ($('#news_publishAt').length > 0) {
        $('#news_publishAt').datetimepicker({
            format: 'DD.MM.YYYY HH:mm',
            locale: locale
        });
        var CurrentDate = moment().format('DD.MM.YYYY HH:mm');
        $('#news_publishAt').val(CurrentDate);
    }
    $("#comment").click(function () {
        $('html, body').animate({
            scrollTop: parseInt($("#commentEditor").offset().top)
        }, 2000);
        $("#commentEditor").focus();
    });

    $('.comment-total').click(function () {
        $('html, body').animate({
            scrollTop: parseInt($(".comments").offset().top)
        }, 2000);
    });
    $('#postAlias').click(function () {
        var alias = $('#forumAlias').val();
        var url = Routing.generate('tj_forum_update_forum_alias', {'_locale': locale});
        $.ajax({
            type: "POST",
            url: url,
            data: {alias: alias}
        }).done(function (data) {
            $('.alert-danger').remove();
            $('#myModal').modal('hide');
            $('#forumAliasModal').modal('hide');
            $('.forum-alias').html(alias);
            $('#replies_useForumAlias').removeAttr("disabled");

        });
    });


    $("#news_submit").click(function () {
        $("#news_imageData").remove();
    });


    if ($('#removePhoto').length > 0) {
        $('#hiddenFile').hide();
    }

    $('#deleteLogo').click(function () {
        bootbox.dialog({
            message: "Do you really want to delete the image?",
            buttons: {
                success: {
                    label: "<i class='fa fa-check' aria-hidden='true'></i> Ok",
                    className: "btn-success",
                    callback: function () {
                        var input = $('#deleteLogo').data('char');
                        var url = Routing.generate('tj_news_delete_logo', {slug: input, '_locale': locale});
                        $.ajax({
                            type: 'GET',
                            url: url,
                            success: function (data) {
                                if (data.success) {
                                    $('#news_imageDescription').val("");
                                    $('.uploadAudioImage :input').val('');
                                    $(this).remove();
                                    $('#medianews').attr('src', '');
                                    $('#deleteLogo').remove();
                                }

                            }
                        });
                    }
                },
                danger: {
                    label: "<i class='fa fa-times' aria-hidden='true'></i> Cancel",
                    className: "btn-danger",
                    callback: function () {
                    }
                }

            }
        });
    });

    $('#post-comment').click(function () {
        $('form[name="replies"]').submit();
    });
    if ($("form[name=replies]").length > 0)
        $("form[name=replies]").validate();

    $('#replies_useForumAlias').change(function () {
        $('.real-name').toggleClass('hidden');
        $('.forum-alias').toggleClass('hidden');
    });

    var inputTerm;

    tagsNewsAutocomplete($('#news_tags_helper'), inputTerm);
    organizationAutocomplete($('#news_organizations_helper'), inputTerm);
    // usersAutocomplete($('#news_users'), inputTerm, initUsers);

    $('#news_imageData').change(function () {
        if ($('#tj_inserate_form_job_description').val() !== "") {
            $('#news_imageDescription').attr('required', "required");
            $('#news_imageDescription').removeClass('hidden');
        }
        else {
            $('#news_imageDescription').removeAttr("required");
            $('#news_imageDescription').addClass('hidden');
        }

    });

    const newsContent = $("#news-list");
    autoSuggestion(newsForm, newsContent, 'news');

    newsForm.submit(function (e) {
        ajaxSubmit(newsForm, newsContent);
        return false
    });


    $('#tags, #years, #sortChoices').change(function () {
        $('#news-block form').submit();
    });

    newsResetFilters = () => {
        resetPageNumber();

        // clear the form input text values
        $(newsForm).get(0).reset();

        // remove all select selected values
        $('select option:selected', newsForm).removeAttr('selected');
        // clear tags value
        $('#tags').val('');
        // clear tags autosuggestion input
        $('.select2-search-choice').remove();

        // set to default values the hidden fields
        $('#favorite', newsForm).val(0);
        $('#organization', newsForm).val('');
        newsForm.submit();
    };

});


/**
 * Remove a favorite news from the search list.
 */
removeFavorite = (slug) => {
    const url = Routing.generate('tj_news_favourite_remove', {slug: slug, '_locale': locale});
    const newsForm = $('#news-block form');

    $.get(url, function (data) {
        if (data.status === 'SUCCESS') {
            newsForm.submit();
        }
    })
};