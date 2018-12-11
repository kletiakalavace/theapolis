// Global Constants
    let euCountries = {};
    const labelWarning = trans('tooltip.choosePaymentMethod', locale);
    const DE = "DE";
    const vatInput = $('#theaterjobs_membership_booking_type_profile_billingAddress_vatId');
    const ibanInput = $("#theaterjobs_membership_booking_type_debitAccount_iban");
    const countryInput = $('#theaterjobs_membership_booking_type_profile_billingAddress_country');
    const companyInput = $('#theaterjobs_membership_booking_type_profile_billingAddress_company');
    const ibanName = 'input[name="theaterjobs_membership_booking_type[debitAccount][iban]"]';
    const bookingForm = $('#membership_booking');
    const paymentMethod = 'input[name="theaterjobs_membership_booking_type[paymentmethod][title]"]';
    const page = $('.membership-modal');
// Functions
    /**
     * Makes Country validation that requires vat
     */
    function validateCountry() {
        let country = countryInput.val();
        let disabled = false, placeholder;

        if (country === null) {
            placeholder = vatInput.data('placeholder-empty');
            disabled = true;
        } else if (country === DE) {
            placeholder = vatInput.data('placeholder-de');
            disabled = true;
        } else if($.inArray(country, euCountries) === -1) {
            placeholder = vatInput.data('placeholder-no-company');
            disabled = true;
        } else {
            placeholder = vatInput.data('placeholder-enabled');
        }
        vatInput.attr("placeholder", placeholder);
        vatInput.prop("disabled", disabled);
    }
    /**
     * Get Order details
     */
    function calculateOrder() {
        const isPaypal = $(paymentMethod + ':checked').attr('short') === 'paypal';
        const country = countryInput.val();
        const membership = $("#membership-name").attr("data-membership");
        const vat_number = vatInput.val();
        const url = Routing.generate('tj_membership_calculate_payment',{'_locale' : locale});
        const data = {country, membership, vat_number, isPaypal};

        $.ajax({
            type: "GET",
            url,
            data,
            success: function (data) {
                $("#preCalculate").html(data);
            }
        });
    }

// Jquery Binds and Events
    //stick nav to top of page
    $(document).scroll(function () {
        const y = $(this).scrollTop();
        const navWrap = $('.bookingInfo').offset().top;
        if (y > navWrap) {
            $('#preCalculate').addClass('sticky');
        } else {
            $('#preCalculate').removeClass('sticky');
        }
    });
    //Validate Membership form
    bookingForm.validate({
        ignore: ":hidden",
        rules: {
            'theaterjobs_membership_booking_type[debitAccount][iban]': {
                required: true,
                remote: {
                    required: true,
                    url: Routing.generate('tj_membership_validate_iban'),
                    type: "GET",
                    data: {
                        iban: function () {
                            return ibanInput.val();
                        }
                    }
                }
            },
            'theaterjobs_membership_booking_type[profile][billingAddress][vatId]': {
                remote: {
                    url: Routing.generate('tj_membership_validate_vat'),
                    type: "GET",
                    data: {
                        vat: function () {
                            return vatInput.val();
                        }
                    }
                }
            },
            'theaterjobs_membership_booking_type[debitAccount][accountHolder]': {
                minlength: 3,
                required: true
            },
            'theaterjobs_membership_booking_type[profile][billingAddress][firstname]': {
                minlength: 3,
                required: true
            },
            'theaterjobs_membership_booking_type[profile][billingAddress][lastname]': {
                minlength: 3,
                required: true
            },
            'theaterjobs_membership_booking_type[profile][billingAddress][street]': {
                minlength: 3,
                required: true
            },
            'theaterjobs_membership_booking_type[profile][billingAddress][zip]': {
                required: true
            },
            'theaterjobs_membership_booking_type[profile][billingAddress][city]': {
                minlength: 3,
                required: true
            }
        },
        errorPlacement: function (error, element) {
            if(element.attr('id') === "theaterjobs_membership_booking_type_paymentmethod_title_2"){
                toolTipError(labelWarning, element);
            }else{
                toolTipError(error.text(), element);
            }
        }
    });
    euCountries = countryInput.data('eu-countries');

    // Update Order Details and validate country
    companyInput.on('change', function (el) {
        if($(this).val() === ''){
            vatInput.val('').change();
            vatInput.prop("disabled", true).change();
        }
    });
    // Update Order Details and validate Country
    vatInput.change(() => {
        calculateOrder();
        validateCountry();
    });
    countryInput.change(() => {
        validateCountry();
        vatInput.val('');
        calculateOrder();
    });
    // Displays bank name on input
    $(ibanName).on('input', function (el) {
        const url = Routing.generate('tj_membership_generate_bic');
        const iban = $(this).val().replace(/\s/g,'').trim();
        ibanInput.val(iban);
        $.ajax({
            type: "GET",
            url,
            data: {iban},
            success: function (data) {
                if (data.bic !== "XXX") {
                    $("#bank_name_updated").text(trans("membership.new.bankName", locale) + ": " + data.bankName);
                }
            }
        });
    });
    //Hides company info on click
    $('#hideCompanyInfo').on('click', function(e){
        companyInput.val('');
        vatInput.val('');
    });
    // Show Company and Vat, Vice Versa
    $('a.company_details').on('click', function () {
        var whatElementToShow = $(this).attr('data-show-on-click');
        $(whatElementToShow).show();
        var whatElementToHide = $(this).attr('data-hide-on-click');
        $(whatElementToHide).hide();
        $(this).hide();
        validateCountry();
    });
    // EventListener when changing the Payment Method
    $('#membership_booking').on("change", paymentMethod,  function (e) {
        const checkedValue = $(this).attr("short");
        switch (checkedValue) {
            case "direct":
                $('#debit-account').hide();$('#debit-account').show();
                break;
            case "paypal":
                $('#debit-account').hide();
                break;
            case "sofort":
                $('#debit-account').hide();
                break;
            default:
                $('#debit-account').hide();
        }
        $('#theaterjobs_membership_booking_type_paymentmethod_title .radio').removeClass('active_paymentmethod');
        $(this).parent().parent().addClass('active_paymentmethod');
        calculateOrder();
        e.stopImmediatePropagation();
    });
// Function Calls
//     validFormInputs();