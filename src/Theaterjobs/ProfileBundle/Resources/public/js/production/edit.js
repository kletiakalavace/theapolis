// JQuery Dom
const orgaRelated = $('#theaterjobs_profilebundle_productionparticipations_production_organizationRelated');
const creators = $('#theaterjobs_profilebundle_productionparticipations_production_creators');
const directors = $('#theaterjobs_profilebundle_productionparticipations_production_directors');

const formProdEdit = $('#productionEdit form:eq(0)');
const loading1 = $('.btn-update-modal .submit-rolling-svg');
const formProdDelete = $('#productionEdit form:eq(1)');
addLoadingSvg(formProdDelete);
const loading2 = formProdDelete.find('.submit-rolling-svg');


/**
 * Functions
 */

/**
 * Formats the time of start/end Time from dd.mm.yyyy => mm.yyyy
 */
function formatTime() {
    //get time
    const startTime = startDate[0];
    const endTime = endDate[0];

    //get real input values
    const startTimeVal = document.getElementsByClassName('startDate')[0];
    const endTimeVal = document.getElementsByClassName('endDate')[0];

    //set them
    startTime.value = startTimeVal.value.match(/\d{2}.\d{4}/)[0];
    const reg = endTimeVal.value ? endTimeVal.value.match(/\d{2}.\d{4}/) : '';
    endTime.value = reg.length > 0 ? reg[0] : '';
}

/**
 *
 * @returns {*}
 */
function nrpublishedSections() {
    let countExperience = $('.experience-block').find('.timeline-item').data('count');
    let countProduction = $('.production-block').find('.timeline-item').data('count');
    let countEducation = $('.education-block').find('#eduList').children().length / 2;

    if (typeof countExperience == "undefined")
        countExperience = 0;

    if (typeof countProduction == "undefined")
        countProduction = 0;

    if (typeof countEducation == "undefined")
        countEducation = 0;

    return countEducation + countProduction + countExperience;
}

// Form validation
formProdEdit.validate({
        errorPlacement: function (error, element) {
            toolTipError(error.text(), element);
            $('.text-danger').removeClass('hidden');
            $('#myModal').animate({scrollTop: $('.login-error-content').offset().top + 2000}, 100);
        },
        ignore: [],
        rules: {
            'theaterjobs_profilebundle_productionparticipations_production_name': {
                required: true
            },
            'theaterjobs_profilebundle_productionparticipations_production_directors': {
                required: true
            }
        },
        unhighlight: function (element) {
            $(element).tooltip('destroy');
            $(element).parent().tooltip('destroy');
            $(element).closest('.has-error').removeClass('has-error');
        },
        submitHandler: function (form, e) {
            e.preventDefault();
            if (loading1.is(':visible')) {
                return;
            }
            var valid = false;
            loading1.show();
            const newForm = getNewForm($(form));
            $.ajax({
                type: newForm.attr('method'),
                url: newForm.attr('action'),
                data: newForm.serialize(),
                success: function (data) {
                    loading1.hide();
                    if (data.errors) {
                        renderFormErrors(data.errors, formProdEdit);
                    } else {
                        $(".production-block").html(data.productions);
                        $("#profileBoxes").html(data.boxes);
                        valid = true;
                    }
                },
                error: function () {
                    loading1.hide();
                    serverError();
                }
            }).done(function () {
                if (valid) {
                    renderBoxes();
                }
            });
        }
    }
);

formProdDelete.submit(function (e) {
    e.preventDefault();
    if (loading2.is(':visible')) {
        return;
    }
    if (publicswitch == 1) {
        if (nrpublishedSections() <= 1) {
            alertPublish();
            return;
        }
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
                    type: formProdDelete.attr('method'),
                    url: formProdDelete.attr('action'),
                    data: formProdDelete.serialize(),
                    dataType: 'json',
                    success: function (data) {
                        loading2.hide();
                        if (data.success) {
                            $(".production-block").html(data.data.productions);
                            $(".production-made").html(data.data.productionsMade);
                            $("#profileBoxes").html(data.data.boxes);
                        } else {
                            if (data.messages) {
                                const message = data.messages.reduce((acc, item) => {
                                    acc += item + '<br>';
                                    return acc;
                                }, "");
                                bootbox.alert({title: false, message});
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        loading2.hide();
                        serverError();
                    }
                }).done(renderBoxes);
            }
        }
    });
});

/**
 * Function calls
 */
$(document).ready(function () {
    // Make organization autosuggestion select2
    generalSelect2(
        '.tag-orga-input',
        trans("people.production.newOrganization", locale),
        Routing.generate('tj_productions_organization', {'_locale': locale}),
        trans('maxOrganization.limit.onlyOne', locale)
    ).on("select2-selecting", function (e) {
        orgaRelated.attr('readonly', '');
        $('#second').show();
    }).on("select2-removed", function (e) {
        $('#second').hide();
    }).on("select2-removing", function (e) {
        if (loading1.is(':visible')) {
            e.preventDefault();
        }
    });

    // Make production name autosuggestion select2
    createSelect2(
        '.tag-input-style',
        trans("profile.new.production.newProduction", locale),
        {
            url: Routing.generate('tj_profile_productions_autosuggestion', {'_locale': locale}),
            dataType: 'json',
            data: (q, page) => {
                return {
                    q,
                    organizationName: $('#theaterjobs_profilebundle_productionparticipations_production_organizationRelated').val(),
                    page,
                }
            },
            results: (results, page) => {
                const more = !results.length ? false : page * autosuggestion_pagination < results[0].total_count;
                return {results, more};
            }
        },
        trans("max.production.limit", locale)
    ).on("select2-close", function (e) {
        var prod = $('#theaterjobs_profilebundle_productionparticipations_production_name');
        if ($.isNumeric(prod.val()) === true) {
            var input = prod.val();
            var url = Routing.generate('tj_hidden_production', {'_locale': locale});
            $.ajax({
                type: "GET",
                url: url,
                data: {idprod: input},
                success: function (data) {
                    var year = (data[0].year);
                    $(".yearProduction").data("DateTimePicker").date(year);
                    creators.val(data[0].creators).change();
                    directors.val(data[0].directors).change();
                    $('.prod :input').each(function () {
                        $(this).attr('readonly', true);
                    });
                    $('.part2').hide();
                }
            });
        }
    }).on("select2-removed", function (e) {
        $('.part2').show();
        $('.prod :input').each(function () {
            $(this).attr('readonly', false);
        });
        creators.val('').change();
        directors.val('').change();
        yearProduction.val('').change();
    }).on("select2-removing", function (e) {
        if (loading1.is(':visible')) {
            e.preventDefault();
        }
    });

    // Make creator name autosuggestion select2
    generalSelect2(
        '.tag-creator-input',
        trans("people.production.newcreator", locale),
        Routing.generate('creators_autosuggestion', {'_locale': locale}),
        trans("people.production.maxcreator.is", locale),
        3
    );

    // Make creator name autosuggestion select2
    generalSelect2(
        '.tag-director-input',
        trans("people.production.newdirector", locale),
        Routing.generate('directors_autosuggestion', {'_locale': locale}),
        trans("people.production.maxdirector.is", locale),
        3
    ).on("select2-removing", function (e) {
        if (loading1.is(':visible')) {
            e.preventDefault();
        }
    });

    // Disable existing fields
    manager.attr('disabled', assistant.prop('checked'));
    assistant.attr('disabled', manager.prop('checked'));

    // Convert start/end date from dd.mm.yyyy to mm.yyyy
    formatTime();

    // Preselect existing data
    selectedOccupation($('#' + occupationId).select2('data').element[0]);

    // No idea
    if (!prodChecked && startDate.attr('readonly') === 'readonly') {
        startDate.datepicker('remove');
    }
});