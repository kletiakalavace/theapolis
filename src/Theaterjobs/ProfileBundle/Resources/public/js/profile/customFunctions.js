var locale = window.location.pathname.split("/");
function getProfileType() {
    if (catIds) {
        $.get(Routing.generate('tj_profile_profile_getCategoryType', {categoryId: catIds,'_locale': locale}), function (data) {
            if (($.inArray("actor", data.type) >= 0) || ($.inArray("dancer", data.type) >= 0))
                $('#actordata').removeClass('hidden');
            else {
                $('#actordata').addClass('hidden');
                $('#actordata :input').each(function () {
                    $(this).val("");
                });
            }
            if (($.inArray("voice", data.type) >= 0) || ($.inArray("singer", data.type) >= 0))
                $('#voicedata').removeClass('hidden');
            else {
                $('#voicedata').addClass('hidden');
                //$("#theaterjobs_profile_qualification_form_singerSection_voiceCategories").select2("val", "");
            }
            if (($.inArray("voice", data.type) >= 0) || ($.inArray("actor", data.type) >= 0)) {
                $('#actorSingerData').removeClass('hidden');
            } else {
                $('#actorSingerData').addClass('hidden');
            }
        });
    }
    else {
        $('#voicedata').addClass('hidden');
        $('#actordata').addClass('hidden');
    }
}

function createTypeahead(typeaheadField) {
    typeaheadField = typeaheadField || $('.typeahead');
    var organization_query_url = decodeURIComponent(Routing.generate('tj_main_organization_query', {query: '%QUERY'}));


    var numbers = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.name);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: organization_query_url
    });

    numbers.initialize();
    $(typeaheadField).typeahead({minLength: 3}, {
        displayKey: 'name',
        source: numbers.ttAdapter(),
        templates: {
            suggestion: Handlebars.compile(['<p class="repo-name"><img src="{{logo}}" width="25" height="25"/> {{name}}</p>',
            ].join(''))
        }
    });

    // Gets the selected value from the theater
    // typeahead.
}

function createTypeaheadJob(typeaheadField) {
    if (typeaheadField === undefined)
        typeaheadField = typeaheadField || $('.job_title');

    var organization_query_url = decodeURIComponent(Routing.generate('tj_inserate_job_route_autocomplete', {query: '%QUERY'}));


    var numbers = new Bloodhound({
        datumTokenizer: function (d) {
            return Bloodhound.tokenizers.whitespace(d.title);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: organization_query_url,
    });

    numbers.initialize();

    $(typeaheadField).typeahead({minLength: 3}, {
        displayKey: 'title',
        source: numbers.ttAdapter(),
        templates: {
            suggestion: Handlebars.compile(['<p class="repo-name">{{title}}</p>',
            ].join(''))
        }
    });

}

function renderFunction(preID, afterPreID, type) {
    $collectionHolder = $('table.' + type);
    $collectionHolder.data('index', $collectionHolder.find(':input').length);
    if (type == 'photo' && $collectionHolder.data('limit')) {
        if ($('table.' + type + ' tr').length > 0) {
            alert(profile.media.member.add_images['' + locale[1] + '']);
            return;
        }
    }
    if (type == 'videos' && $collectionHolder.data('limit')) {
        if ($('table.' + type + ' tr').length > 0) {
            alert(profile.media.member.add_video['' + locale[1] + '']);
            return;
        }
    }
    if (type === 'qualification') {
        var profileInstitution = [];
        $('.profileInstitution').each(function () {
            if (typeof $(this).attr('id') != "undefined") {
                profileInstitution.push($(this).val());
            }
        });

        var startDate = [];
        $('.startDate').each(function () {
            startDate.push($(this).val());
        });

        var profileProfession = [];
        $('.profileProfession').each(function () {
            if (typeof $(this).attr('id') != "undefined") {
                profileProfession.push($(this).val());
            }
        });

        if (($.inArray("", profileInstitution) === -1) && ($.inArray("", startDate) === -1) && ($.inArray("", profileProfession) === -1)) {
            addTagForm(preID, afterPreID, $collectionHolder, $newLinkLi, type);
        } else {
            $(".qualification [required]").valid();
            $("span#profileQualifications").remove();
            $('.add_tag_link[data-type="qualification"]').parent().parent().append("<span id='profileQualifications' class='error'>" + profile.media.required_field['' + locale[1] + ''] + "</a>");
            $("#profileQualifications").fadeOut(5000);
        }
    } else {
        addTagForm(preID, afterPreID, $collectionHolder, $newLinkLi, type);
    }
}

function editQualification(row, preID, afterPreID) {
    var a = row.data('row');
    var index = a.substring(a.length - 1);
    $('.' + a + '.display').addClass('hidden');
    $('.' + a + '.edit').removeClass('hidden');

    var qualificationChoice = '#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + '_qualificationChoice';
    var startDate = "#" + preID + afterPreID + "qualificationSection_qualifications_" + index + "_startDate";
    var endDate = "#" + preID + afterPreID + "qualificationSection_qualifications_" + index + "_endDate";
    var educationChoice = '#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + '_educationChoice';
    var category = "#" + preID + afterPreID + "qualificationSection_qualifications_" + index + "_categories";
    var finished = "#" + preID + afterPreID + "qualificationSection_qualifications_" + index + "_finished";

    if ($(qualificationChoice).val() == "") {
        $(qualificationChoice).parent().hide();
    }

    $('.startDate').each(function () {
        $(this).rules("add", {
            lowerThan: endDate
        });
    });

    $('.endDate').each(function () {
        $(this).rules("add", {
            required: function () {
                if ($(finished).is(":checked")) {
                    return true;
                } else {
                    return false;
                }
            },
            greaterThan: startDate
        });
    });

    $('.qualificationChoice').each(function () {
        var select = $(this).is(":visible");
        $(this).rules("add", {
            required: function () {
                if (select) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    });

    $(category).select2({
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

    $(category).change(function () {
        catIds = [];
        $("select.qualificationSectionCategory").each(function () {
            catIds.push($(this).val());
        });
        getProfileType();

    });


    $(educationChoice).change(function () {
        if ($(this).val() == 1) {
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group').show();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(2)').hide();
            $('#' + preID + afterPreID + "qualificationSection_qualifications_" + index + ' div#message-' + index).remove();

            $("#save_form").attr("disabled", false);
        } else {
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group').hide();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(0)').show();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(1)').show();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(2)').show();
            $("#" + preID + afterPreID + "qualificationSection_qualifications_" + index + ' div#message-' + index).remove();

            $("#save_form").attr("disabled", true);
        }
    });

    $(qualificationChoice).change(function () {
        if ($(this).val() == 1) {
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group').show();
            $("#" + preID + afterPreID + "qualificationSection_qualifications_" + index + ' div#message-' + index).remove();

            $(".btn-next").attr("disabled", false);
            $("#save_form").attr("disabled", false);
        } else {
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group').hide();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(0)').show();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(1)').show();
            $('#' + preID + afterPreID + 'qualificationSection_qualifications_' + index + ' div.form-group:eq(2)').show();

            //message
            var catText = $("#" + preID + afterPreID + "qualificationSection_qualifications_" + index + "_categories option:selected").text();
            var html = "<div id='message-" + index + "' class='alert alert-danger'>" + profile.edit_wizard.cant_publish_profile['' + locale[1] + ''] + " '" + catText + "',";
            html += " " + profile.edit_wizard.cant_publish_profile_reason['' + locale[1] + ''] + "</div>";
            $("#" + preID + afterPreID + "_qualificationSection_qualifications_" + index).append(html);

            $(".btn-next").attr("disabled", true);
            $("#save_form").attr("disabled", true);
        }
    });

}