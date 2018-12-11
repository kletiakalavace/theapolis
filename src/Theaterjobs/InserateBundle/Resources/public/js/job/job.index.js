const jobForm = $('#jobs form');

const jobContent = $("#jobPartial");


autoSuggestion(jobForm, jobContent, 'jobs');
initialize();

jobForm.submit(function (e) {
    ajaxSubmit(jobForm, jobContent);
    $('#sort').removeClass('hidden');
    return false;
});

function removeFilterStatus() {
    resetPageNumber();
    $("input[name='status[]']", jobForm).val('');
    jobForm.submit();
}

function removeFilterGratification(id) {
    resetPageNumber();
    $('#gratification').find(":checkbox[value=" + id + "]").prop('checked', false);
    jobForm.submit();
}

function checkboxClick(el) {
    if (parseInt(el.attr('data-category')) === 1) {
        jobForm.attr('action', Routing.generate('tj_inserate_job_route_list', {
            category: el.val(),
            '_locale': locale
        }));
    }
    resetPageNumber();
    jobForm.submit();
}

function checkboxClickTeam(el) {
    if (parseInt(el.attr('data-category')) === 1) {
        jobForm.attr('action', Routing.generate('tj_inserate_job_route_list_team', {
            category: el.val(),
            '_locale': locale
        }));
    }
    resetPageNumber();
    jobForm.submit();
}

function checkboxClickMy(el) {
    if (parseInt(el.attr('data-category')) === 1) {
        var formAction = Routing.generate('tj_inserate_job_route_myjobs', {
            category: el.val(),
            '_locale': locale
        });
        jobForm.attr('action', formAction);
    }
    resetPageNumber();
    jobForm.submit();
}

jobResetFilters = (action) => {
    resetPageNumber();
    // remove category named route parameter
    jobForm.attr('action', Routing.generate(action, {category: null, '_locale': locale}));
    // clear the form input text values
    $(jobForm).get(0).reset();
    // remove all checkbox checked values
    $('input:checkbox', jobForm).removeAttr('checked');
    // remove all select selected values
    $('select option:selected', jobForm).removeAttr('selected');

    // set to default values the hidden fields
    $('#favorite', jobForm).val(0);
    $('#organization', jobForm).val('');

    $('#location', jobForm).val('');
    $('#pac-input', jobForm).val('');
    $('#searchPhrase', jobForm).val('');

    jobForm.submit();
};


/**
 * Remove a favorite job from the search list.
 */
removeFavorite = (slug) => {
    const url = Routing.generate('tj_inserate_job_favourite_remove', {slug: slug, '_locale': locale});

    $.get(url, function (data) {
        if (data.status === 'SUCCESS') {
            jobForm.submit();
        }
    })
};