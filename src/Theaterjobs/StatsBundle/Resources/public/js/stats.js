$(function () {
    $('#graphContainer').highcharts({
        data: {
            table: 'datatable'
        },
        chart: {
            type: 'line'
        },
        title: {
            text: $("#form_entity option:selected").text()
        },
        yAxis: {
            allowDecimals: false,
            tickInterval: 1,
            title: {
                text: 'Units'
            }
        },
    });

    $("button[type='button']").click(function () {
        var type = $(this).attr('data-type');
        var start, end;
        if (type === 'day') {
            start = moment().format('DD.MM.YYYY');
            end = moment().format('DD.MM.YYYY');
        } else if (type === 'week') {
            start = moment().subtract(7, "days").format('DD.MM.YYYY');
            end = moment().format('DD.MM.YYYY');
        } else if (type === 'month') {
            start = moment().subtract(30, "days").format('DD.MM.YYYY');
            end = moment().format('DD.MM.YYYY');
        }

        $("#form_dateFrom").val(start);
        $("#form_dateTo").val(end);
        $("#form_save").trigger('click');
    });

    var a = moment($("#form_dateFrom").val(), 'DD.MM.YYYY');
    var b = moment($("#form_dateTo").val(), 'DD.MM.YYYY');
    var days = b.diff(a, 'days');
    $('button.time').removeClass('btn-success');
    if(days == 0){        
        $('button[data-type="day"]').addClass('btn-success');
    }else if(days == 7){
        $('button[data-type="week"]').addClass('btn-success');                
    }else if(days == 30){
        $('button[data-type="month"]').addClass('btn-success');                
    }


    if ($("#form_entity").val() !== 'user') {
        $("#membersType").addClass('hidden');
        $("#paymentsTime").addClass('hidden');
        $("#paymentsType").addClass('hidden');
        $("#membershipsType").addClass("hidden");
        $("#registeredType").addClass("hidden");
    } else {
        if ($("#form_members").val() === 'payments') {

            $("#membershipsType").addClass("hidden");
            $("#registeredType").addClass("hidden");

            $("#membersType").removeClass('hidden');
            $("#paymentsTime").removeClass('hidden');
            $("#paymentsType").removeClass('hidden');
        } else if ($("#form_members").val() === 'memberships') {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#registeredType").addClass("hidden");

            $("#membershipsType").removeClass("hidden");
        } else if ($("#form_members").val() === 'registered') {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#membershipsType").addClass("hidden");

            $("#registeredType").removeClass("hidden");
        }

        if (($("#form_members").val() === 'payingProfile') || ($("#form_members").val() === 'deleted')) {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#membershipsType").addClass("hidden");
            $("#registeredType").addClass("hidden");
        }

        if ($("#form_registeredType").val() === 'members') {
            $("#membersType").removeClass('hidden');
            $("#paymentsTime").removeClass('hidden');
            $("#paymentsType").removeClass('hidden');
        } else {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
        }
    }

    if ($("#form_entity").val() === 'user') {
        $("#members").removeClass('hidden');
    } else if ($("#form_entity").val() === 'job') {
        $("#status").removeClass('hidden');
        $("#users").removeClass('hidden');
    } else {
        $("#status").removeClass('hidden');
    }

    $("#form_entity").change(function () {
        $("#members").addClass('hidden');
        $("#status").addClass('hidden');
        $("#users").addClass('hidden');

        if ($(this).val() === 'user') {
            $("#members").removeClass('hidden');
        } else if ($(this).val() === 'job') {
            $("#status").removeClass('hidden');
            $("#users").removeClass('hidden');
        } else {
            $("#status").removeClass('hidden');
        }

        if ($(this).val() !== 'user') {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#membershipsType").addClass("hidden");
            $("#registeredType").addClass("hidden");
        }
    });

    $("#form_members").change(function () {
        if ($(this).val() === 'payments') {

            $("#membershipsType").addClass("hidden");
            $("#registeredType").addClass("hidden");

            $("#membersType").removeClass('hidden');
            $("#paymentsTime").removeClass('hidden');
            $("#paymentsType").removeClass('hidden');
        } else if ($(this).val() === 'memberships') {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#registeredType").addClass("hidden");

            $("#membershipsType").removeClass("hidden");
        } else if ($(this).val() === 'registered') {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#membershipsType").addClass("hidden");

            $("#registeredType").removeClass("hidden");
        }

        if (($(this).val() === 'payingProfile') || ($(this).val() === 'deleted')) {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
            $("#membershipsType").addClass("hidden");
            $("#registeredType").addClass("hidden");
        }
    });

    $("#form_registeredType").change(function () {
        if ($(this).val() === 'members') {
            $("#membersType").removeClass('hidden');
            $("#paymentsTime").removeClass('hidden');
            $("#paymentsType").removeClass('hidden');
        } else {
            $("#membersType").addClass('hidden');
            $("#paymentsTime").addClass('hidden');
            $("#paymentsType").addClass('hidden');
        }
    });

});

// Datepicker script
$('#form_dateFrom').datepicker({
    autoclose: true,
    format: 'dd.mm.yyyy',
    locale: locale
});
$('#form_dateTo').datepicker({
    autoclose: true,
    format: 'dd.mm.yyyy',
    locale: locale
});