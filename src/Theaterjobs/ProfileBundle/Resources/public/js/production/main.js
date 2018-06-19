// JQuery Dom
var yearProduction = $(".yearProduction");
const FORM_NAME = 'theaterjobs_profilebundle_productionparticipations';
const occupationId = 'theaterjobs_profilebundle_productionparticipations_occupation';
const assistant = $("#theaterjobs_profilebundle_productionparticipations_occupationDescription_assistant");
const manager = $("#theaterjobs_profilebundle_productionparticipations_occupationDescription_management");
const startDate = $('.startDate');
const endDate = $('.endDate');
const usedName = $('.used-name input').val();

/**
 * Functions
 */

/**
 * @param string
 * @param separator
 * @returns {Array}
 */
function splitVal(string, separator) {
    if (string === null || string.length < 1)
        return [];
    return string.split(separator).map(item => $.trim(item));
};

/**
 * Clone form and transforms date from format mm.yyy to 01.mm.yyyy
 * @param oldForm
 */
function getNewForm(oldForm) {
    // Clone form and modify fields
    const newForm = oldForm.clone();
    const _endDate = newForm.find('input[name="' + FORM_NAME + '[end]"]').val();
    const _startDate = newForm.find('input[name="' + FORM_NAME + '[start]"]').val();

    newForm.find('input[name="' + FORM_NAME + '[start]"]').val('01.' + _startDate);
    newForm.find('input[name="' + FORM_NAME + '[end]"]').val('01.' + _endDate);

    const newOccupation = newForm.find('#' + occupationId);
    const oldOccupation = oldForm.find('#' + occupationId);
    newOccupation.val(oldOccupation.val());
    return newForm;
}

/**
 * Change occupation based on data attribute on the element
 * @param selected
 */
function selectedOccupation(selected) {
    const roleDiv = $("#rolediv");
    const roleCheckBox = roleDiv.find("input:checkbox");
    const roleName = roleDiv.find("input[name*='roleName']");
    const dataPerformance = selected.attributes['data-performance'];
    // If nothing is selected, hide
    if (!dataPerformance) {
        roleDiv.hide();
        roleCheckBox.each(function () {
            $(this).hide();
            $(this).parent().hide();
        });
        roleName.each(function () {
            $(this).val('');
            $(this).addClass('hidden');
            $(this).prev().closest('label').hide();
        });
        return;
    }
    const isPerfCat = dataPerformance.value === "true";
    // If user selected sth
    roleDiv.show();
    roleCheckBox.each(function () {
        isPerfCat ? $(this).hide() : $(this).show();
        isPerfCat ? $(this).parent().hide() : $(this).parent().show();
    });
    roleName.each(function () {
        isPerfCat ? $(this).removeClass('hidden') : $(this).addClass('hidden');
        isPerfCat ? $(this).prev().closest('label').show() : $(this).prev().closest('label').hide();
        isPerfCat ? $(this).val('') : '';
    });
}


/**
 * JQuery Selectors
 */

// Make occupation change the description fields
$('#' + occupationId).select2().on('select2-selecting', function (el) {
    selectedOccupation(el.choice.element[0]);
});

// Call datepicker for the date fields
yearProduction.datetimepicker({
    viewMode: 'years',
    format: 'YYYY',
    showClear: true,
    showTodayButton: true,
    toolbarPlacement: 'top',
    useCurrent: false,
    locale: locale,
    maxDate: moment().add(1, 'year').toDate()
});

// On year selection => preselct startDate and endDate
yearProduction.on('dp.change', function (e) {
    startDate.val('');
    endDate.val('');
    if (!e.date) return;
    // Shortest Possible solution (Exceeding events don't allow dates to cross limits unless we put them twice)
    // @TODO We can keep track of current date of yearProduction and check if new date is bigger and apply the events accordingly
    endDate.data("DateTimePicker").date(e.date.endOf('year'));
    endDate.val('');
    startDate.data("DateTimePicker").date(e.date.startOf('year'));
    startDate.val('');
    endDate.data("DateTimePicker").date(e.date.endOf('year'));
    endDate.val('');
    startDate.data("DateTimePicker").date(e.date.startOf('year'));
    startDate.val('');

});

// Init Start/End Date
$(".startDate, .endDate").datetimepicker({
    viewMode: 'months',
    format: 'MM.YYYY',
    showClear: true,
    showTodayButton: true,
    toolbarPlacement: 'top',
    useCurrent: false,
    locale: locale,
    widgetPositioning: {
        horizontal: 'left',
        vertical: 'bottom'
    }
});

// @TODO Find the meaning
$('#theaterjobs_profilebundle_productionparticipations_production_year, #theaterjobs_profilebundle_productionparticipations_start, #theaterjobs_profilebundle_productionparticipations_end').attr("autocomplete", "off");

// Make Sure dates don't exceed limit
startDate.on("dp.change", function (e) {
    endDate.data("DateTimePicker").minDate(e.date);
    if (e.date > endDate.data("DateTimePicker").date()) {
        endDate.data("DateTimePicker").date(e.date);
    }
});
// Make Sure dates don't exceed limit
endDate.on("dp.change", function (e) {
    startDate.data("DateTimePicker").maxDate(e.date);
    if (e.date < startDate.data("DateTimePicker").date()) {
        startDate.data("DateTimePicker").date(e.date);
    }
});

// Disable end date when ongoing
$("#theaterjobs_profilebundle_productionparticipations_ongoing").change(function () {
    if ($(this).prop('checked') === true) {
        endDate.val(' ');
        endDate.data("DateTimePicker").disable();

    } else if ($(this).prop('checked') === false) {
        endDate.data("DateTimePicker").enable();
    }
});

$('.username-newProd .checkbox input').change(function () {
    if ($(this).is(":checked")) {
        $('.used-name').removeClass('hidden');
    } else {
        $('.used-name').addClass('hidden');
        $('.used-name input').val(usedName);
    }
});

// Datepicker loads fully on the screen when selected
startDate.click(() => {$("#myModal").animate({ scrollTop: $(document).height() }, "fast");});
endDate.click(() => {$("#myModal").animate({ scrollTop: $(document).height() }, "fast");});

// Prevents to checkbox click at the same time
assistant.change(() =>  {manager.attr('disabled', $(this).prop('checked'))});
manager.change(() =>  {assistant.attr('disabled', $(this).prop('checked'))});
