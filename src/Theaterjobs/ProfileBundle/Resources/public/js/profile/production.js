/**
 * Created on 10/28/16.
 */

/*
 make organization autosuggestion select2
 */
$('.tag-orga-input').select2({
    minimumInputLength: 3,
    formatInputTooShort: function () {
        $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
    },
    maximumSelectionSize: 1,
    tags: true,
    tokenSeparators: [';'],
    createSearchChoice: function (term, page) {
        $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
        if (page.some(function (item) {
                return item.text.toLowerCase() === term.toLowerCase();
            })) {
            return;
        }

        return {
            id: $.trim(term),
            text: $.trim(term) + '  (new organization)'
        };

    },
    ajax: {
        url: Routing.generate('tj_productions_organization',{'_locale': locale}),
        dataType: 'json',
        data: function (term, page) {
            return {
                q: term
            };
        },
        results: function (data, page) {
            $.each(data, function () {
                if (this.desc !== '') {
                    this.text = this.text + "  (" + this.desc + ")";
                }
            });
            return {
                results: data
            };
        }
    },
    // Take default tags from the input value
    initSelection: function (element, callback) {
        var data = [];

        function splitVal(string, separator) {
            var val, i, l;
            if (string === null || string.length < 1)
                return [];
            val = string.split(separator);
            for (i = 0, l = val.length; i < l; i = i + 1)
                val[i] = $.trim(val[i]);
            return val;
        }

        $(splitVal(element.val(), ";")).each(function () {
            data.push({
                id: this,
                text: this
            });
        });
        callback(data);
    },
    formatSelectionTooBig: function (limit) {
        return "Max organization limit is only one";
    }
}).on("select2-selecting", function (e) {
    $('#second').css('visibility', 'visible');
}).on("select2-removed", function (e) {
    $('#second').css('visibility', 'hidden');
});


/*
 make production name autosuggestion select2
 */
$('.tag-input-style').select2({
    minimumInputLength: 3,
    formatInputTooShort: function () {
        $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
    },
    maximumSelectionSize: 1,
    tags: true,
    tokenSeparators: [';'],
    createSearchChoice: function (term, page) {
        $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
        if (page.some(function (item) {
                return item.text.toLowerCase() === term.toLowerCase();
            })) {
            return;
        }

        return {
            id: $.trim(term),
            text: $.trim(term) + ' (new production)',
        };

    },
    ajax: {
        url: Routing.generate('tj_profile_productions_autosuggestion',{'_locale': locale}),
        dataType: 'json',
        data: function (term, page) {
            return {
                q: term,
                org: $('#theaterjobs_profilebundle_productionparticipations_production_organizationRelated').val()

            };
        },
        results: function (data, page) {
            return {
                results: data
            };
        }
    },
    // Take default tags from the input value
    initSelection: function (element, callback) {
        var data = [];

        function splitVal(string, separator) {
            var val, i, l;
            if (string === null || string.length < 1)
                return [];
            val = string.split(separator);
            for (i = 0, l = val.length; i < l; i = i + 1)
                val[i] = $.trim(val[i]);
            return val;
        }

        $(splitVal(element.val(), ";")).each(function () {
            data.push({
                id: this,
                text: this
            });
        });
        callback(data);
    },
    formatSelectionTooBig: function (limit) {
        return "Max production limit is only one";
    }
}).on("select2-close", function (e) {
    var prod = $('#theaterjobs_profilebundle_productionparticipations_production_name');
    if ($.isNumeric(prod.val()) === true) {
        var input = prod.val();
        var url = Routing.generate('tj_hidden_production',{'_locale': locale});
        $.ajax({
            type: "GET",
            url: url,
            data: {idprod: input},
            success: function (data) {
                var year = (data[0].year);
                var yr = $('#theaterjobs_profilebundle_productionparticipations_production_year');
                yr.datepicker('setDate', new Date(year));
                yr.datepicker('remove');

                var creatorsValue = (data[0].creators);
                var directorsValue = (data[0].directors);
                var creators = $('#theaterjobs_profilebundle_productionparticipations_production_creators');
                var directors = $('#theaterjobs_profilebundle_productionparticipations_production_directors');
                creators.val(creatorsValue).change();
                directors.val(directorsValue).change();

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
        $(this).val('');
    });

    var creators = $('#theaterjobs_profilebundle_productionparticipations_production_creators');
    var directors = $('#theaterjobs_profilebundle_productionparticipations_production_directors');
    creators.val('').change();
    directors.val('').change();

    $(".yearProduction").datepicker({
        format: "yyyy",
        minViewMode: 2,
        autoclose: true,
        endDate: '+1y',
        locale: locale
    });
});

/*
 make occupation change the description fields
 */
$("#theaterjobs_profilebundle_productionparticipations_occupation").select2().on('select2-close', function () {
    if ($(this).val() !== null) {
        var isPerformanceCategory = $(this).select2('data').element[0].attributes['data-performance'].value;
        if (isPerformanceCategory === 'true') {
            $("#rolediv").removeClass('hidden');

            $("#rolediv input[name*='roleName']").each(function () {
                $(this).removeClass('hidden');
                $(this).prev().closest('label').show();
            });
            $("#rolediv input:checkbox").each(function () {
                $(this).hide();
                $(this).parent().hide();
            });
        }
        else if (isPerformanceCategory === 'false') {
            $("#rolediv").removeClass('hidden');

            $("#rolediv input[name*='roleName']").each(function () {
                $(this).addClass('hidden');
                $(this).prev().closest('label').hide();
            });
            $("#rolediv input:checkbox").each(function () {
                $(this).show();
                $(this).parent().show();
            });
        }
    }
});

/*
 call datepicker for the date fields
 */
$(".yearProduction").datepicker({
    format: "yyyy",
    minViewMode: 2,
    autoclose: true,
    endDate: '+1y',
    locale: locale
});

$("#theaterjobs_profilebundle_productionparticipations_start").datepicker({
    format: 'dd.mm.yyyy',
    minViewMode: 1,
    autoclose: true,
    locale: locale
});

$("#theaterjobs_profilebundle_productionparticipations_end").datepicker({
    format: 'dd.mm.yyyy',
    minViewMode: 1,
    autoclose: true,
    locale: locale
});

/*
 make creator/director name autosuggestion select2
 */
var creator = $('.tag-creator-input');
var director = $('.tag-director-input');

autoCreaDire(creator, 'creator');
autoCreaDire(director, 'director');

function autoCreaDire(input, group) {
    input.select2({
        minimumInputLength: 3,
        formatInputTooShort: function () {
            $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
        },
        maximumSelectionSize: 3,
        tags: true,
        tokenSeparators: [','],
        createSearchChoice: function (term, page) {
            $("#select2-drop").removeClass('hidden'); //We show the suggestion list because user has now entered more than 3 characters.
            if (page.some(function (item) {
                    return item.text.toLowerCase() === term.toLowerCase();
                })) {
                return;
            }

            return {
                id: $.trim(term),
                text: $.trim(term) + ' (new ' + group + ')',
                check: true
            };

        },
        ajax: {
            url: Routing.generate('tj_productions_' + group,{'_locale': locale}),
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term
                };
            },
            results: function (data, page) {
                return {
                    results: data
                };
            }
        },
        // Take default tags from the input value
        initSelection: function (element, callback) {
            var data = [];

            function splitVal(string, separator) {
                var val, i, l;
                if (string === null || string.length < 1)
                    return [];
                val = string.split(separator);
                for (i = 0, l = val.length; i < l; i = i + 1)
                    val[i] = $.trim(val[i]);
                return val;
            }

            $(splitVal(element.val(), ",")).each(function () {
                data.push({
                    id: this,
                    text: this
                });
            });
            callback(data);
        },
        formatSelectionTooBig: function (limit) {
            return 'Max ' + group + ' limit is three';
        }
    });
}

/*
 form validation
 */
$('form[name="theaterjobs_profilebundle_productionparticipations"]').validate({
    ignore: [],
    rules: {
        'theaterjobs_profilebundle_productionparticipations_production_name': {
            required: true
        },
        'theaterjobs_profilebundle_productionparticipations_production_directors': {
            required: true
        },
        'theaterjobs_profilebundle_productionparticipations[start]': {
            lessThan: '#theaterjobs_profilebundle_productionparticipations_end'
        },
        'theaterjobs_profilebundle_productionparticipations[end]': {
            greaterThan: '#theaterjobs_profilebundle_productionparticipations_start'
        }
    }
});

$.validator.addMethod('lessThan', function (value, element, param) {

    if ($(param).val().length === 0) {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
    else if ($(element).val().length === 0) {
        return true;
    }
    else if (moment(value, "DD.MM.YYYY").unix() > moment($(param).val(), "DD.MM.YYYY").unix()) {
        return false;
    } else {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
}, 'Must be less than field participation end');

$.validator.addMethod('greaterThan', function (value, element, param) {
    if ($(param).val().length === 0) {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    } else if ($(element).val().length === 0) {
        return true;
    }
    else if (moment(value, "DD.MM.YYYY").unix() < moment($(param).val(), "DD.MM.YYYY").unix()) {
        return false;
    } else {
        $('#' + $(element).attr("id") + '-error').remove();
        $(element).removeClass('error');
        $('#' + $(param).attr("id") + '-error').remove();
        $(param).removeClass('error');
        return true;
    }
}, 'Must be greater than field participation start');

