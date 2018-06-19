// JQuery Dom
const orgaRelated = $('#theaterjobs_profilebundle_productionparticipations_production_organizationRelated');
const creators = $('#theaterjobs_profilebundle_productionparticipations_production_creators');
const directors = $('#theaterjobs_profilebundle_productionparticipations_production_directors');

const productionForm = $('form[name="theaterjobs_profilebundle_productionparticipations"]');
addLoadingSvg(productionForm);
const loading = productionForm.find('.submit-rolling-svg');

// Form validation
productionForm.validate({
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
        if (loading.is(':visible')) {
            return;
        }
        var valid = false;
        loading.show();
        const newForm = getNewForm($(form));
        $.ajax({
            type: newForm.attr('method'),
            url: newForm.attr('action'),
            data: newForm.serialize(),
            success: function (data) {
                loading.hide();
                if (data.errors) {
                    renderFormErrors(data.errors, productionForm);
                } else {
                    $(".production-block").html(data.productions);
                    $("#profileBoxes").html(data.boxes);
                    valid = true;
                }
            },
            error: function () {
                loading.hide();
                serverError();
            }
        }).done(function () {
            if (valid) {
                renderBoxes();
            }
        });
    }
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
        if (loading.is(':visible')) {
            e.preventDefault();
        }
    });

    createSelect2(
        '.tag-input-style',
        trans("people.production.newProduction", locale),
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
        $(".yearProduction").val('').change();
    }).on("select2-removing", function (e) {
        if (loading.is(':visible')) {
            e.preventDefault();
        }
    });

    // Make creator name autosuggestion select2
    generalSelect2(
        '.tag-creator-input',
        trans("people.production.newcreator", locale),
        Routing.generate('creators_autosuggestion', {'_locale': locale}),
        trans("people.production.maxcreator.is", locale)
    ).on("select2-removing", function (e) {
        if (loading.is(':visible')) {
            e.preventDefault();
        }
    });

    // Make creator name autosuggestion select2
    generalSelect2(
        '.tag-director-input',
        trans("people.production.newdirector", locale),
        Routing.generate('directors_autosuggestion', {'_locale': locale}),
        trans("people.production.maxdirector.is", locale)
    ).on("select2-removing", function (e) {
        if (loading.is(':visible')) {
            e.preventDefault();
        }
    });
});

