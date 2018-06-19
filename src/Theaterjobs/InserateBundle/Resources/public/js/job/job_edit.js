/**
 * Created by marlind on 5/8/17.
 */

CKEDITOR.replace('tj_inserate_form_job_contact', {customConfig: CKContact});
CKEDITOR.replace('tj_inserate_form_job_description', {customConfig: CKDescription});

var formJob = $('form[name="tj_inserate_form_job"]');
addLoadingSvg(formJob);
const loading = formJob.find('.submit-rolling-svg');

CKEDITOR.instances['tj_inserate_form_job_description'].on('change', function () {
    if (CKEDITOR.instances['tj_inserate_form_job_description'].getData() === '')
        CKEDITOR.instances['tj_inserate_form_job_description'].document.getBody().setStyle('background-color', '#F0BCC3');
    else
        CKEDITOR.instances['tj_inserate_form_job_description'].document.getBody().setStyle('background-color', '#fff');

    CKEDITOR.instances['tj_inserate_form_job_description'].updateElement();
});

CKEDITOR.instances['tj_inserate_form_job_contact'].on('change', function () {
    if (CKEDITOR.instances['tj_inserate_form_job_contact'].getData() === '')
        CKEDITOR.instances['tj_inserate_form_job_contact'].document.getBody().setStyle('background-color', '#F0BCC3');
    else
        CKEDITOR.instances['tj_inserate_form_job_contact'].document.getBody().setStyle('background-color', '#fff');

    CKEDITOR.instances['tj_inserate_form_job_contact'].updateElement();
});

formJob.validate({
    errorPlacement: function (error, element) {
        if (element.attr("id") === 'tj_inserate_form_job_contact' || element.attr("id") === 'tj_inserate_form_job_description') {
            var textAreaId = element.attr("id");
            CKEDITOR.instances[textAreaId].document.getBody().setStyle('background-color', '#F0BCC3');
        } else {
            toolTipError(error.text(), element);
            $('.text-danger').removeClass('hidden');
        }
    },
    ignore: [],
    rules: {
        'tj_inserate_form_job[contact]': {
            required: true
        },
        'tj_inserate_form_job[description]': {
            required: true
        },
        'tj_inserate_form_job[categories][]': {
            required: true
        },
        'tj_inserate_form_job[email]': {
            email: true
        }
    }
});

formJob.submit(function (e) {
    e.preventDefault();
    if (loading.is(':visible')) {return;}
    validFormInputs();
    if ($(this).valid()) {
        loading.show();
        if (isJobPublished) {
            addHiddenFields(formJob);
        }
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (data) {
                loading.hide();
                if (data.error) {
                    $('#tj_inserate_form_job_submit').attr('type', 'submit');
                    for (var i = 0; i < data.errors.length; i++) {
                        var el;
                        if (data.errors[i].field === 'contact' || data.errors[i].field === 'description') {
                            el = $('.editor-job-' + data.errors[i].field);
                            toolTipError(data.errors[i].message, el);
                        } else {
                            showSingleError(data.errors[i], formJob);
                        }
                    }
                }
                else {
                    window.location.href = data.route;
                }
            },
            error: function () {
                loading.hide();
                $('#tj_inserate_form_job_submit').attr('type', 'submit');
                bootbox.alert({message: 'There was an error with your request.Please refresh and try again.'});
            }
        });
        if ($('.localization-radio').val() == 0) {
            $('.localized-fill').removeClass('hidden');
            $('#localization-group').addClass('hidden');
            $('#pac-input').removeClass('red-bg');
        }
        else {
            $('.localized-fill').addClass('hidden');
            $('#localization-group').removeClass('hidden');
            $('#pac-input').addClass('red-bg');
            initialize();
        }
    }
    

});


$("#tj_inserate_form_job_categories").select2({
    placeholder: "Keine Kategorie ausgewählt",
    maximumSelectionSize: 1
}).on("change", function (e) {
    var id = "#" + $(this).attr("id") + "-error";
    if ((e.val !== '') && ($(this).hasClass("error"))) {
        $(this).removeClass("error");
        $(id).hide();
    }
}).on("select2-removed", function (e) {
    var data = $(this).val();
    var idx = $.inArray(e.val, data);
    var id = "#" + $(this).attr("id") + "-error";
    if (idx !== -1) {
        data.splice(idx, 1);
    }
    if (data === null) {
        $(this).addClass("error");
        $(id).show();
    }
});

$('.localization-radio').change(function () {

    if ($(this).val() == 0) {
        $('#tj_inserate_form_job_geolocation').prop('disabled', true);
        $('#localization-group').addClass('hidden');
        $('.localized-fill').addClass('hidden');
        $('#pac-input').removeClass('red-bg');
    }
    else {
        $('#tj_inserate_form_job_geolocation').prop('disabled', false);
        $('#localization-group').removeClass('hidden');
        $('.localized-fill').removeClass('hidden');
        $('#pac-input').addClass('red-bg');
        initialize();
        
    }
});

$("#pac-input").on('change', function (e) {
    $(this).removeClass('red-bg');
});

var titleField = $('.tag-name-input');

titleField.typeahead({
    autoSelect: true,
    minLength: 3,
    delay: 400,
    source: function (query, process) {
        $.ajax({
            url: Routing.generate('tj_job_get_jobtitle_typeahead'),
            data: function (term, page) {
                return {
                    q: term
                };
            },
            dataType: 'json'
        })
            .done(function (response) {
                return process(response.data);
            });
    }
});

//Initialize the map
initialize();

$("#initialDate,#tj_inserate_form_job_engagementStart,#tj_inserate_form_job_engagementEnd,#tj_inserate_form_job_publicationEnd,#tj_inserate_form_job_applicationEnd").datetimepicker({
    viewMode: 'months',
    format: 'DD.MM.YYYY',
    showClear: true,
    showTodayButton: true,
    toolbarPlacement: 'top',
    useCurrent: false,
    locale: locale
}).on("dp.change", function (e) {
    if ($(this).valid()) {
        $(this).closest('.has-error').removeClass('has-error');
    }
}).on("dp.show", function (e) {
    $(this).data("DateTimePicker").viewMode('months').format('DD.MM.YYYY');
});

var currentDay = getDate('initialDate');
var currentPlus6Week = new Date;
currentPlus6Week.setDate(currentDay.getDate() + 42);
var currentPlus1Day = new Date;
currentPlus1Day.setDate(currentDay.getDate() + 1);
var minDateExtended = new Date;
minDateExtended.setDate(currentDay.getDate() - 12775);
var maxDateExtended = new Date;
maxDateExtended.setDate(currentDay.getDate() + 12775);

setMinDate('tj_inserate_form_job_engagementStart', currentDay);
setMinDate('tj_inserate_form_job_engagementEnd', currentDay);

setMinDate('tj_inserate_form_job_applicationEnd', getDate('tj_inserate_form_job_applicationEnd'));


if (hasValue('tj_inserate_form_job_engagementStart')) {
    setMaxDate('tj_inserate_form_job_applicationEnd', getDate('tj_inserate_form_job_engagementStart'));
}
if (!hasValue('tj_inserate_form_job_applicationEnd')) {
    setMaxDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
}
setMinDate('tj_inserate_form_job_publicationEnd', currentDay);


$("#tj_inserate_form_job_asap").change(function () {
    if (this.checked) {
        hideDate('tj_inserate_form_job_engagementStart');
        $('#tj_inserate_form_job_engagementEnd').parent().parent().removeClass('no-padding-left');
        expandDateField('tj_inserate_form_job_engagementEnd');
        disableDateAndClear('tj_inserate_form_job_applicationEnd');
        disableDateAndClear('tj_inserate_form_job_engagementStart');
    }
    else {
        handleAllDates();
        showDate('tj_inserate_form_job_engagementStart');
        $('#tj_inserate_form_job_engagementEnd').parent().parent().addClass('no-padding-left');
        narrowDateField('tj_inserate_form_job_engagementEnd');
        enableDate('tj_inserate_form_job_applicationEnd');
        enableDate('tj_inserate_form_job_engagementStart');
    }
    handleAllDates();
});

$("#tj_inserate_form_job_engagementStart").on("dp.change", function (e) {
    handleAllDates();
});

$("#tj_inserate_form_job_applicationEnd").on("dp.change", function (e) {
    handleAllDates();
});

$("#tj_inserate_form_job_engagementEnd").on("dp.change", function (e) {
    if ($(this).val() !== '') {
        handleAllDates();
    }
});

$('.education-radio').change(function () {

    if ($(this).val() == 0) {
        $('.gratification-edu').removeClass('hidden');
        $('.gratification-job').addClass('hidden');
        if ($('#tj_inserate_form_job_categories').val() !== null) {
            $('#tj_inserate_form_job_categories').val(null).trigger("change");
        }
        $('#tj_inserate_form_job_gratification_6').prop('checked', true);
        catOpt.empty();
        catOpt.append(lastCatoption);
    }
    else {
        $('.gratification-edu').addClass('hidden');
        $('.gratification-job').removeClass('hidden');
        $('#tj_inserate_form_job_categories').val(null).trigger("change");
        $("input[name='tj_inserate_form_job[gratification]']").prop('checked', false);
        $('#tj_inserate_form_job_gratification_3').prop('checked', true);
        catOpt.empty();
        catOpt.append(allOpt);
    }
});

function handleAllDates() {
    if (hasValue('tj_inserate_form_job_engagementStart')) {
        var startDateMinusOneDay = getDate('tj_inserate_form_job_engagementStart');
        startDateMinusOneDay.setDate(startDateMinusOneDay.getDate() - 1);
        var startDateMinus2Day = getDate('tj_inserate_form_job_engagementStart');
        startDateMinus2Day.setDate(startDateMinus2Day.getDate() - 2);
        removeMaxDate('tj_inserate_form_job_applicationEnd');
        setMaxDate('tj_inserate_form_job_applicationEnd', startDateMinusOneDay);

        if (hasValue('tj_inserate_form_job_engagementEnd')) {
            if (isBigger('tj_inserate_form_job_engagementStart', 'tj_inserate_form_job_engagementEnd')) {
                showPicker('tj_inserate_form_job_engagementEnd');
                removeMinDate('tj_inserate_form_job_engagementEnd');
                if ($('#tj_inserate_form_job_engagementEnd').val() !== '') {
                    setDate('tj_inserate_form_job_engagementEnd', getDate('tj_inserate_form_job_engagementStart'));
                }
                setMinDate('tj_inserate_form_job_engagementEnd', getDate('tj_inserate_form_job_engagementStart'));
            }
            else {
                removeMinDate('tj_inserate_form_job_engagementEnd');
                setMinDate('tj_inserate_form_job_engagementEnd', getDate('tj_inserate_form_job_engagementStart'));
            }
        }
        else {
            showPicker('tj_inserate_form_job_engagementEnd');
            removeMinDate('tj_inserate_form_job_engagementEnd');
            if ($('#tj_inserate_form_job_engagementEnd').val() !== '') {
                setDate('tj_inserate_form_job_engagementEnd', getDate('tj_inserate_form_job_engagementStart'));
            }
            setMinDate('tj_inserate_form_job_engagementEnd', getDate('tj_inserate_form_job_engagementStart'));
        }

        if (hasValue('tj_inserate_form_job_applicationEnd')) {
            disableDate('tj_inserate_form_job_publicationEnd');
            removeMaxDate('tj_inserate_form_job_publicationEnd');
            setDate('tj_inserate_form_job_publicationEnd', getDate('tj_inserate_form_job_applicationEnd'));
        }
        else {

            enableDate('tj_inserate_form_job_publicationEnd');
            if (isMoreThan6week('tj_inserate_form_job_engagementStart')) {
                removeMaxDate('tj_inserate_form_job_publicationEnd');
                setDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
                setMaxDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
            }
            else {
                removeMaxDate('tj_inserate_form_job_publicationEnd');
                setDate('tj_inserate_form_job_publicationEnd', startDateMinusOneDay);
                setMaxDate('tj_inserate_form_job_publicationEnd', startDateMinusOneDay);
            }
        }

    }
    else {
        removeMaxDate('tj_inserate_form_job_applicationEnd');
        removeMinDate('tj_inserate_form_job_engagementEnd');
        setMinDate('tj_inserate_form_job_engagementEnd', currentPlus1Day);
        if (hasValue('tj_inserate_form_job_applicationEnd')) {
            disableDate('tj_inserate_form_job_publicationEnd');
            if (hasValue('tj_inserate_form_job_engagementEnd')) {
                var new_application_end2 = getDate('tj_inserate_form_job_engagementEnd');
                new_application_end2.setDate(new_application_end2.getDate() - 1);
                setMaxDate('tj_inserate_form_job_applicationEnd', new_application_end2);
                removeMaxDate('tj_inserate_form_job_publicationEnd');
                setDate('tj_inserate_form_job_publicationEnd', getDate('tj_inserate_form_job_applicationEnd'));
            }
            else {
                removeMaxDate('tj_inserate_form_job_applicationEnd');
                removeMaxDate('tj_inserate_form_job_publicationEnd');
                setDate('tj_inserate_form_job_publicationEnd', getDate('tj_inserate_form_job_applicationEnd'));
            }
        }
        else {
            enableDate('tj_inserate_form_job_publicationEnd');
            if (hasValue('tj_inserate_form_job_engagementEnd')) {
                var new_application_end3 = getDate('tj_inserate_form_job_engagementEnd');
                new_application_end3.setDate(new_application_end3.getDate() - 1);
                setMaxDate('tj_inserate_form_job_applicationEnd', new_application_end3);
                if (isMoreThan6week('tj_inserate_form_job_engagementEnd')) {
                    removeMaxDate('tj_inserate_form_job_publicationEnd');
                    setDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
                    setMaxDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
                }
                else {
                    var new_application_end1 = getDate('tj_inserate_form_job_engagementEnd');
                    new_application_end1.setDate(new_application_end1.getDate() - 1);
                    removeMaxDate('tj_inserate_form_job_publicationEnd');
                    setDate('tj_inserate_form_job_publicationEnd', new_application_end1);
                    setMaxDate('tj_inserate_form_job_publicationEnd', new_application_end1);
                }
            }
            else {
                removeMaxDate('tj_inserate_form_job_applicationEnd');
                setDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
                setMaxDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
            }
        }
    }
}

function setMinDate(element, date) {
    $('#' + element).data("DateTimePicker").minDate(date);
}

function setMaxDate(element, date) {
    $('#' + element).data("DateTimePicker").maxDate(date);
}

function removeMaxDate(element) {
    $('#' + element).data("DateTimePicker").maxDate(maxDateExtended);
}

function removeMinDate(element) {
    $('#' + element).data("DateTimePicker").minDate(minDateExtended);
}

function setDate(element, date) {
    $('#' + element).data("DateTimePicker").date(date);
}

function getDate(element) {
    var selector = $('#' + element);
    return (selector.val() !== '') ? selector.data("DateTimePicker").date().toDate() : false;
}

function disableDate(element) {
    $('#' + element).prop('readonly', true);
}

function disableDateAndClear(element) {
    $('#' + element).prop('readonly', true).val('');
}

function enableDate(element) {
    $('#' + element).prop('readonly', false).prop('disabled', false);
}

function hideDate(element) {
    $('#' + element).parent().parent().hide();
}

function showDate(element) {
    $('#' + element).parent().parent().show();
}

function narrowDateField(element) {
    $('#' + element).parent().parent().removeClass('col-md-8').addClass('col-md-4 ');
}

function expandDateField(element) {
    $('#' + element).parent().parent().removeClass('col-md-4').addClass('col-md-8');
}

function showPicker(element) {
    $('#' + element).data("DateTimePicker").show();
}

function isMoreThan6week(element) {
    if (getDate(element).getTime() > currentPlus6Week.getTime()) {
        return true
    } else {
        return false;
    }
}

function isBigger(element1, element2) {
    if (getDate(element1).getTime() > getDate(element2).getTime()) {
        return true
    } else {
        return false;
    }
}

function hasValue(element) {
    if ($('#' + element).val() !== '') {
        return true
    } else {
        return false;
    }
}

CKEDITOR.config.height='400px';

// Disable job education selection if user is editing the job
if (isJobPublished) {
    $('input[name="radio-education"]').attr('disabled', true);
    $('#tj_inserate_form_job_categories').select2('enable', false);
}

/**
 * Load hidden fields from job form before submit
 * @param form
 */
function addHiddenFields(form) {
    const radioInput = $('input[name="radio-education"]');
    radioInput.after(
        '<input type="hidden" name="' + radioInput.attr('name') + '" value="' + radioInput.val() + '" />'
    );
    const categoryInput =  $('#tj_inserate_form_job_categories').select2('enable', false);
    categoryInput.after(
        '<input type="hidden" name=" ' + categoryInput.attr('name') + '" value="' + categoryInput.val()[0] + '" />'
    );
}