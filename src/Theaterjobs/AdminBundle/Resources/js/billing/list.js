dataTableURL = Routing.generate('admin_invoices_load', {'_locale': locale});
adminSearchForm = $('#adminBillingSearchForm');
const billingCreationFrom = adminSearchForm.find('#' + 'billingCreationFrom');
const billingCreationTo = adminSearchForm.find('#' + 'billingCreationTo');
const select = adminSearchForm.find('#choices');
const to = adminSearchForm.find('#' + 'to');
let currentInput = adminSearchForm.find('#' + select.val());

$(() => {
    dataTableInitialize();
});

select.change((event) => {
    billingCreationFrom.addClass('hidden');
    billingCreationTo.addClass('hidden');
    to.addClass('hidden');

    billingCreationFrom.val('');
    billingCreationTo.val('');

    if ($(event.currentTarget).val() === 'billingCreation') {
        currentInput.addClass('hidden');
        to.removeClass('hidden');
        billingCreationFrom.removeClass('hidden');
        billingCreationTo.removeClass('hidden');
    } else {
        currentInput.addClass('hidden');
        const oldVal = currentInput.val();
        currentInput.val('');
        currentInput = adminSearchForm.find('#' + $(event.currentTarget).val());
        currentInput.removeClass('hidden');
        currentInput.val(oldVal);
    }

    if (currentInput.val().length > 0) {
        dataTableReload();
    }

});

adminSearchForm.validate({
    errorPlacement: (error, element) => {
    },
    ignore: []
});

if (element.length !== 0) {
    select.val(element);
}

if (searchPhrase.length !== 0) {
    const el = adminSearchForm.find('#' + element);
    el.val(searchPhrase);
}

adminSearchForm.find('#billingCreationFrom').datetimepicker({
    viewMode: 'days',
    format: 'DD/MM/YYYY',
    useCurrent: false,
    showClear: true,
    showTodayButton: true,
    toolbarPlacement: 'top',
    locale: locale
});

adminSearchForm.find('#billingCreationTo').datetimepicker({
    viewMode: 'days',
    format: 'DD/MM/YYYY',
    useCurrent: false,
    showClear: true,
    showTodayButton: true,
    toolbarPlacement: 'top',
    locale: locale
});

adminSearchForm.find('#billingCreationFrom').on("dp.change", (e) => {
    adminSearchForm.find('#billingCreationTo').data("DateTimePicker").minDate(e.date);
});

adminSearchForm.find('#billingCreationTo').on("dp.change", (e) => {
    adminSearchForm.find('#billingCreationFrom').data("DateTimePicker").maxDate(e.date);
    dataTableReload();
});