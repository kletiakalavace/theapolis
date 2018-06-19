/**
 * Created by marlind on 5/8/17.
 */

CKEDITOR.replace('tj_inserate_form_job_contact', {customConfig: CKContact});
CKEDITOR.replace('tj_inserate_form_job_description', {customConfig: CKDescription});

var formJob = $('form[name="tj_inserate_form_job"]');
addLoadingSvg(formJob);
const loading = formJob.find('.submit-rolling-svg');

validateCKEDITOR = (el) => {
    if (el.getData() === '') {
        el.document.getBody().setStyle('background-color', '#F0BCC3');
    }
    else {
        el.document.getBody().setStyle('background-color', '#fff');
    }

    el.updateElement();
};

CKEDITOR.instances['tj_inserate_form_job_description'].on('change', function () {
    validateCKEDITOR(CKEDITOR.instances['tj_inserate_form_job_description']);
});

CKEDITOR.instances['tj_inserate_form_job_contact'].on('change', function () {
    validateCKEDITOR(CKEDITOR.instances['tj_inserate_form_job_contact']);
});

formJob.validate({
    errorPlacement: function (error, element) {
        if (element.attr("id") === 'tj_inserate_form_job_contact' || element.attr("id") === 'tj_inserate_form_job_description') {
            validateCKEDITOR(CKEDITOR.instances['tj_inserate_form_job_contact']);
            validateCKEDITOR(CKEDITOR.instances['tj_inserate_form_job_description']);
        } else {
            toolTipError(error.text(), element);
            $('.text-danger').removeClass('hidden');
        }
    },
    unhighlight: function (element) {
        if (!checkInputIsFile($(element))) {
            $(element).tooltip('destroy');
            $(element).closest('.form-group').removeClass('has-error');
        }
    },
    success: function (label) {
        $('.text-danger').addClass('hidden');
    },
    ignore: [],
    rules: {
        'tj_inserate_form_job[geolocation]': {
            required: true
        },
        'tj_inserate_form_job[contact]': {
            required: true
        },
        'tj_inserate_form_job[categories][]': {
            required: true
        },
        'tj_inserate_form_job[description]': {
            required: true
        },
        'tj_inserate_form_job[email]': {
            email: true
        }
    }
});

function checkInputIsFile(element) {
    return (element.attr('type') === 'file');
}

$('.localization-radio').change(function () {

    if ($(this).val() == 0) {
        $("#tj_inserate_form_job_geolocation").rules("remove", "required");
        $('#tj_inserate_form_job_geolocation').prop('disabled', true);
        $('#localization-group').addClass('hidden');
        $('.localized-fill').addClass('hidden');
        $('#pac-input').removeClass('red-bg');
    }
    else {
        $("#tj_inserate_form_job_geolocation").rules("add", "required");
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

$('#localization-choice').change(function () {
    if ($(this).val() === 0) {
        $('#tj_inserate_form_job_geolocation').prop('disabled', true);
    }
    else {
        $('#tj_inserate_form_job_geolocation').prop('disabled', false);
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

var currentPlus6Week = new Date();
currentPlus6Week.setDate(currentPlus6Week.getDate() + 42);
var currentPlus1Day = new Date();
currentPlus1Day.setDate(currentPlus1Day.getDate() + 1);
var currentPlus2Day = new Date();
currentPlus2Day.setDate(currentPlus1Day.getDate() + 1);
var currentDay = new Date();
currentDay.setDate(currentDay.getDate());
var minDateExtended = new Date();
minDateExtended.setDate(currentDay.getDate() - 12775);
var maxDateExtended = new Date();
maxDateExtended.setDate(currentDay.getDate() + 12775);

$("#tj_inserate_form_job_engagementStart,#tj_inserate_form_job_engagementEnd,#tj_inserate_form_job_publicationEnd,#tj_inserate_form_job_applicationEnd").datetimepicker({
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

setMinDate('tj_inserate_form_job_engagementStart', currentPlus1Day);
setMinDate('tj_inserate_form_job_engagementEnd', currentPlus1Day);
setMinDate('tj_inserate_form_job_applicationEnd', currentDay);
setMaxDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
setMinDate('tj_inserate_form_job_publicationEnd', currentDay);
setDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);

$("#tj_inserate_form_job_asap").change(function () {
    if (this.checked) {
        hideDate('tj_inserate_form_job_engagementStart');
        $('#tj_inserate_form_job_engagementEnd').parent().parent().removeClass('no-padding-left');
        expandDateField('tj_inserate_form_job_engagementEnd');
        disableDateAndClear('tj_inserate_form_job_applicationEnd');
        disableDateAndClear('tj_inserate_form_job_engagementStart');
        handleAllDates();
    }
    else {
        showDate('tj_inserate_form_job_engagementStart');
        $('#tj_inserate_form_job_engagementEnd').parent().parent().addClass('no-padding-left');
        narrowDateField('tj_inserate_form_job_engagementEnd');
        enableDate('tj_inserate_form_job_applicationEnd');
        enableDate('tj_inserate_form_job_engagementStart');
        handleAllDates();
    }

    triggerChangeEvent('tj_inserate_form_job_applicationEnd');
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
                removeMaxDate('tj_inserate_form_job_publicationEnd');
                setDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
                setMaxDate('tj_inserate_form_job_publicationEnd', currentPlus6Week);
            }
        }
    }
}

function setMinDate(element, date) {
    $('#' + element).data("DateTimePicker").minDate(date);
    $('#' + element).data("DateTimePicker").locale(locale);
}

function setMaxDate(element, date) {
    $('#' + element).data("DateTimePicker").maxDate(date);
    $('#' + element).data("DateTimePicker").locale(locale);
}

function removeMaxDate(element) {
    $('#' + element).data("DateTimePicker").maxDate(maxDateExtended);
    $('#' + element).data("DateTimePicker").locale(locale);
}

function removeMinDate(element) {
    $('#' + element).data("DateTimePicker").minDate(minDateExtended);
    $('#' + element).data("DateTimePicker").locale(locale);
}

function setDate(element, date) {
    $('#' + element).data("DateTimePicker").date(date);
    $('#' + element).data("DateTimePicker").locale(locale);
}

function getDate(element) {
    return $('#' + element).data("DateTimePicker").date().toDate();
}

function disableDate(element) {
    $('#' + element).prop('readonly', true);
}

function disableDateAndClear(element) {
    $('#' + element).prop('readonly', true).val('');
}

function enableDate(element) {
    $('#' + element).prop('readonly', false);
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

function triggerChangeEvent(element) {
    $('#' + element).change();
}

function isMoreThan6week(element) {
    var return_val = false;
    if (getDate(element).getTime() > currentPlus6Week.getTime()) {
        return_val = true;
    }
    return return_val;
}

function isBigger(element1, element2) {
    if (getDate(element1).getTime() > getDate(element2).getTime()) {
        return true;
    }
    return false;
}

function hasValue(element) {
    if ($('#' + element).val() !== '') {
        return true;
    }
    return false;
}

formJob.submit(function (e) {
    e.preventDefault();
    if (loading.is(':visible')) {
        return;
    }
    if ($(this).valid()) {
        loading.show();
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (data) {
                loading.hide();
                if (data.error) {
                    for (var i = 0; i < data.errors.length; i++) {
                        var el;
                        if (data.errors[i].field === 'contact' || data.errors[i].field === 'description') {
                            el = $('.editor-job-' + data.errors[i].field);
                        }
                        else {
                            el = $('[name=' + data.errors[i].field + ']');
                            el.parent().addClass('has-error');
                        }
                        toolTipError(data.errors[i].message, el);
                    }
                }
                else if (data.route) {
                    window.location.href = data.route;
                }
            },
            error: function () {
                loading.hide();
                popupCase(popupCase.error.popupCase_news_form_request_error);

            }
        });
    }
    if (typeof CKEDITOR.instances['tj_inserate_form_job_contact'] !== "undefined") {
        if (CKEDITOR.instances['tj_inserate_form_job_contact'].getData() === '') {
            CKEDITOR.instances['tj_inserate_form_job_contact'].document.getBody().setStyle('background-color', '#F0BCC3');
        }
    }
    if (CKEDITOR.instances['tj_inserate_form_job_description'].getData() === '') {
        CKEDITOR.instances['tj_inserate_form_job_description'].document.getBody().setStyle('background-color', '#F0BCC3');
        $('#myModal').animate(
            {
                scrollTop: $('.login-error-content').offset().top + 2000
            },
            100);
    }


    if ($('.localization-radio').val() == 0) {
        $('.localized-fill').removeClass('hidden');
        $('#pac-input').removeClass('red-bg');
    }
    else {
        $('.localized-fill').addClass('hidden');
        $('#pac-input').addClass('red-bg');
        initialize();
    }


});

$('.bootstrap-datetimepicker-widget').remove();

if (organizationField.val() !== undefined && organizationField.val() !== '') {
    var orgaId = organizationField.val();
    getOrgaEntity(orgaId);
}


function getOrgaEntity(id) {
    $.get(Routing.generate('tj_organization_data') + '/' + id, function (data) {
        $('#medianews').attr('src', data.url);

        if (data.latLng) {
            oldLatLng = data.latLng.split(',');
            initialize();
        }

        CKEDITOR.instances['tj_inserate_form_job_contact'].setData(data.contact)
    });
}

CKEDITOR.config.height = '400px';

initialize();