// Sample
/*
$.validator.addMethod(
    'rule',
    () => {
        // logic here
    },
    'ErrorMsg' //Or trans('error.desc %key%', locale, {'%key%' : 'value'}) || cb
);
*/

/**
 * File size validation param in mb
 */
$.validator.addMethod(
    'fileSize',
    function (value, element, param) {
        if ($(element).attr('type') !== 'file') {
            throw Error("File type is not file");
        }
        // If blank and not required
        return element.files.length && element.files[0].size / 1024 / 1024 <= parseFloat(param);
    },
    trans('file.max.size.is {0}', locale)
);

/**
 * File type validation
 */
$.validator.addMethod(
    'fileType',
    function (value, element, param) {
        const file = element.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        return !($.inArray(ext, param) === -1);
    },
    trans('file.type.invalid', locale)
);

/**
 * @TODO Find the meaning
 */
$.validator.addMethod(
    'customMeters',
    function (value, element) {
        return this.optional(element) || numbersValidation(value) && between(value, $(element).attr('data-min'), $(element).attr('data-max'));
    },
    (value, element) => {
    return trans('organization.stage.onlyDigits {min} {max} {example}', locale, { '{min}': $(element).attr('data-min').toLocaleString(locale), '{max}': $(element).attr('data-max').toLocaleString(locale), '{example}': 12345.67.toLocaleString(locale) })
    }
);

/**
 * @TODO Find the meaning
 */
$.validator.addMethod(
    'numbersOnly',
    function (value, element) {
        return this.optional(element) || numbersValidation(value) && between(parseInt(value), $(element).attr('min'), $(element).attr('max'));
    },
    (value, element) => {
        return trans('organization.visitors.onlyDigits {min} {max}', locale, { '{min}': $(element).attr('min'),  '{max}': $(element).attr('max') })
    }
);

/**
 * @TODO Find the meaning
 */
$.validator.addMethod(
    "currency",
    function (value, element) {
        return this.optional(element) || numbersValidationSplit(value);
    },
    "Please enter a correct currency format, ex." + 12345.67.toLocaleString(locale)
);

/**
 * Validate through regex
 */
$.validator.addMethod(
    "regex",
    function (value, element, regexp) {
        return this.optional(element) || regexp.test(value);
    },
    trans("Invalid.Format", locale)
);