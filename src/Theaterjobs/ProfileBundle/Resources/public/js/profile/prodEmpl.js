$(function () {
    if (route.indexOf('productions') > -1) {

        $("#theaterjobs_profilebundle_productions_year").datepicker({
            format: "yyyy",
            minViewMode: 2,
            autoclose: true,
            endDate: '+1y'
        });

        $("#theaterjobs_profilebundle_productions_occupation").select2().on('select2-close', function () {

            if ($(this).val() !== null) {

                var isPerformanceCategory = $(this).select2('data').element[0].attributes['data-performance'].value;
                if (isPerformanceCategory === 'true') {
                    $("#theaterjobs_profilebundle_productions_occupationDescription_assistant").hide();
                    $("label[for='theaterjobs_profilebundle_productions_occupationDescription_assistant']").hide();
                    $("#theaterjobs_profilebundle_productions_occupationDescription_assistant").val('');
                    $("#theaterjobs_profilebundle_productions_occupationDescription_roleName").show();
                    $("#theaterjobs_profilebundle_productions_occupationDescription_roleName").removeClass('hidden');
                    $("#theaterjobs_profilebundle_productions_occupationDescription_roleName").prev().closest('label').show();
                    $("#rolediv").removeClass('hidden');
                }
                else if (isPerformanceCategory === 'false') {
                    $("#theaterjobs_profilebundle_productions_occupationDescription_roleName").hide();
                    $("#theaterjobs_profilebundle_productions_occupationDescription_roleName").prev().closest('label').hide();
                    $("#theaterjobs_profilebundle_productions_occupationDescription_roleName").val('');
                    $("#theaterjobs_profilebundle_productions_occupationDescription_assistant").show();
                    $("#theaterjobs_profilebundle_productions_occupationDescription_assistant").removeClass('hidden');
                    $("label[for='theaterjobs_profilebundle_productions_occupationDescription_assistant']").show();
                    $("#rolediv").removeClass('hidden');

                }
            }
        });

        $('.tag-orga-input').select2({
            minimumInputLength: 3,
            formatInputTooShort: function () {
                $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
            },
            maximumSelectionSize: 1,
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
                    text: $.trim(term) + '  (new organization)'
                };

            },
            ajax: {
                url: Routing.generate('tj_productions_organization', {'_locale': locale}),
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
                    })
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
                        text: this,
                    });
                });
                callback(data);
            },
            formatSelectionTooBig: function (limit) {
                return "Max organization limit is only one";
            }
        }).on("select2-close", function (e) {
            $('#tj_form_secondstep').css('visibility', 'visible');
        }).on("select2-removed", function (e) {
            $('#tj_form_secondstep').css('visibility', 'hidden');
        });

    }
    else if (route.indexOf('employments') > -1) {
        $("#theaterjobs_profilebundle_employments_yearFrom").datepicker({
            format: "dd.mm.yyyy",
            autoclose: true,
            endDate: '+1y'
        });

        $("#theaterjobs_profilebundle_employments_yearTo").datepicker({
            format: "dd.mm.yyyy",
            autoclose: true,
            endDate: '+1y'
        });

        $("#theaterjobs_profilebundle_employments_occupation").select2();
        $.ajax({
            type: 'GET',
            url: Routing.generate('tj_employments_organization', {'_locale': locale}),
            success: function (data) {

                $("#theaterjobs_profilebundle_employments_organization_helper").autocomplete({
                    source: data,
                    minLength: 3,
                    focus: function (event, ui) {

                        $("#theaterjobs_profilebundle_employments_organization_helper").val(ui.item.value);
                        return false;

                    },
                    select: function (event, ui) {

                        $("#theaterjobs_profilebundle_employments_organization_helper").val(ui.item.value);
                        $("#theaterjobs_profilebundle_employments_organization_helper").parent().append(ui.item.desc);
                        return false;

                    },
                    create: function () {
                        $(this).autocomplete("instance")._renderItem = function (ul, item) {

                            return $("<li>").append("<a>" + item.label + "<br>" + item.desc + "</a>").appendTo(ul);

                        }
                    }
                });
            }
        });
    }

    $("#theaterjobs_profilebundle_productions_start").datepicker({
        format: 'dd.mm.yyyy',
        minViewMode: 1,
        autoclose: true
    });

    $("#theaterjobs_profilebundle_productions_end").datepicker({
        format: 'dd.mm.yyyy',
        minViewMode: 1,
        autoclose: true
    });

});

function createPrototype(listnew, listinput, addnew, elementCount, maxElements) {

    if ($(listinput).length == maxElements) {
        $(addnew).addClass('hide');
    }

    $(addnew).click(function (e) {
        e.preventDefault();
        if ($(listinput).length <= maxElements + 1) {
            var elementList = $(listnew);

            // grab the prototype template
            var newWidget = elementList.attr('data-prototype');
            // replace the "__name__" used in the id and name of the prototype
            // with a number that's unique to your emails
            // end name attribute looks like name="contact[emails][2]"
            newWidget = newWidget.replace(/__name__/g, elementCount);
            elementCount++;

            // create a new list element and add it to the list
            var newLi = $('<li></li>').html(newWidget);
            newLi.appendTo(elementList);

            addTagFormDeleteLink(newLi, listinput, addnew, maxElements);

            if ($(listinput).length == maxElements + 2) {
                $(addnew).addClass('hide');
            }
            makeProtoAutosugg(newLi);
        }
    });
}

function addTagFormDeleteLink(tagFormLi, listinput, add, max) {
    var removeFormA = $('<a href="#">tj.label.delete</a>');
    tagFormLi.append(removeFormA);

    removeFormA.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // remove the li for the tag form
        tagFormLi.remove();
        if ($(listinput).length <= max + 2) {
            $(add).removeClass('hide');
        }

    });
}

function makeProtoAutosugg(newLi) {

    var divInput = newLi.children().find('input');
    var group = '';
    if (divInput.hasClass('tag-creator-input')) {
        group = 'creator';
    }
    else if (divInput.hasClass('tag-director-input')) {
        group = 'director';
    }
    else {
        return false;
    }

    divInput.select2({
        minimumInputLength: 3,
        formatInputTooShort: function () {
            $("#select2-drop").addClass('hidden'); //We hide the message "please enter 3 characters" by hiding the suggestion list.
        },
        maximumSelectionSize: 1,
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
                text: $.trim(term) + '  (new ' + group + ')'
            };

        },
        ajax: {

            url: Routing.generate('tj_productions_' + group, {'_locale': locale}),
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
                    text: this,
                });
            });
            callback(data);
        },
        formatSelectionTooBig: function (limit) {
            return "Max " + group + " limit is only one";
        }
    });
}