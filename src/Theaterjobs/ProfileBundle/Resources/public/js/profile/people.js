var peopleForm = $('#people form');

const peopleContent = $("#peoplePartial");


autoSuggestion(peopleForm, peopleContent);
initialize();

peopleForm.submit(function (e) {
    ajaxSubmit(peopleForm, peopleContent);
    return false;
});

peopleRemoveCategory = () => {
    // remove category named route parameter
    peopleForm.attr('action', Routing.generate('tj_profile_profile_list', {category: null, '_locale': locale}));
    peopleForm.submit();
};

peopleResetFilters = () => {
    resetPageNumber();
    // remove category named route parameter
    peopleForm.attr('action', Routing.generate('tj_profile_profile_list', {category: null, '_locale': locale}));
    // clear the form input text values
    $(peopleForm).get(0).reset();
    // remove all checkbox checked values
    $('input:checkbox', peopleForm).removeAttr('checked');
    // remove all select selected values
    $('select option:selected', peopleForm).removeAttr('selected');

    // set to default values the hidden fields
    $('#favorite', peopleForm).val(0);
    $('#organization', peopleForm).val('');

    peopleForm.submit();
};

peopleCategoryCheckBox = (el) => {
    resetPageNumber();
    peopleForm.attr('action', Routing.generate('tj_profile_profile_list', {category: $(el).val(), '_locale': locale}));
    peopleForm.submit();
};

/**
 * Remove a favorite profile from the search list.
 */
removeFavorite = (slug) => {
    const url = Routing.generate('tj_profile_remove_favourite_root', {slug: slug, '_locale': locale});

    $.get(url, function (data) {
        if (data.status === 'SUCCESS') {
            peopleForm.submit();
        }
    })
};