const organizationFormSearch = $('#organizationSearch form');
const orgContent = $("#organizationPartial");
autoSuggestion(organizationFormSearch, orgContent);

initialize();

organizationFormSearch.validate({
    errorPlacement: function (error, element) {
        element.parent().addClass('has-error');
        return false;
    },
    ignore: [],
    rules: {}
});

organizationFormSearch.submit(function (e) {
    ajaxSubmit(organizationFormSearch, orgContent);
    return false;
});

removeFilterKind = (id) => {
    resetPageNumber();
    $("#organizationKind_" + id).attr('checked', false);
    organizationFormSearch.submit();
};

removeFilterSection = (id) => {
    resetPageNumber();
    $("#organizationSection_" + id).attr('checked', false);
    organizationFormSearch.submit();
};

removeFilterStatus = (id) => {
    resetPageNumber();
    $('#status').find(":checkbox[value=" + id + "]").prop('checked', false);
    organizationFormSearch.submit();
};

removeFilterMyOrganization = () => {
    resetPageNumber();
    $('#organization', organizationFormSearch).val(0);
    listSearchForm.submit();
};


organizationResetFilters = () => {
    resetPageNumber();
    // clear the form input text values
    $(organizationFormSearch).get(0).reset();

    // remove all checkbox checked values
    $('input:checkbox', organizationFormSearch).removeAttr('checked');

    // remove all select selected values
    $('select option:selected', organizationFormSearch).removeAttr('selected');

    // set to default values the hidden fields
    $('#favorite', organizationFormSearch).val(0);
    $('#organization', organizationFormSearch).val(0);
    organizationFormSearch.submit();
};

/**
 * Remove a favorite organization from the search list.
 */
removeFavorite = (slug) => {
    const url = Routing.generate('tj_organization_favourite_remove', {slug: slug, '_locale': locale});

    $.get(url, function (data) {
        if (data.status === 'SUCCESS') {
            organizationFormSearch.submit();
        }
    })
};