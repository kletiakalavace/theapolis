$(document).ready(function () {
    $('.wysiwyg-editor').bind('paste', null, function (e) {
        e.preventDefault();
        var elem = $(this);
        OnPaste_StripFormatting(elem, e);
    });
    var _onPaste_StripFormatting_IEPaste = false;

    function OnPaste_StripFormatting(elem, e) {

        if (e.originalEvent && e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
            e.preventDefault();
            var text = e.originalEvent.clipboardData.getData('text/plain');
            window.document.execCommand('insertText', false, text.replace(/(<([^>]+)>)/ig, ""));
        } else if (e.clipboardData && e.clipboardData.getData) {
            e.preventDefault();
            var text = e.clipboardData.getData('text/plain');
            window.document.execCommand('insertText', false, text.replace(/(<([^>]+)>)/ig, ""));
        } else if (window.clipboardData && window.clipboardData.getData) {
            // Stop stack overflow
            if (!_onPaste_StripFormatting_IEPaste) {
                _onPaste_StripFormatting_IEPaste = true;
                e.preventDefault();
                window.document.execCommand('ms-pasteTextOnly', false);
            }
            _onPaste_StripFormatting_IEPaste = false;
        }

    }
   
    $('.wysiwyg-editor').each(function () {
        if ($('#' + $(this).attr('id')).length > 0) {
            if (($(this).attr('id') === 'siteInfo') || ($(this).attr('id') === 'category-editor')) {
                var image = {name: 'insertImage'};
            } else {
                var image = null;
            }
            $('#' + $(this).attr('id')).ace_wysiwyg({
                toolbar:
                        [
                            {name: 'bold', title: 'Custom tooltip'},
                            {name: 'italic', title: 'Custom tooltip'},
                            {name: 'strikethrough', title: 'Custom tooltip'},
                            {name: 'underline', title: 'Custom tooltip'},
                            null,
                            'insertunorderedlist',
                            'insertorderedlist',
                            'outdent',
                            'indent',
                            null,
                            {name: 'justifyleft'},
                            {name: 'justifycenter'},
                            {name: 'justifyright'},
                            {name: 'justifyfull'},
                            null,
                            {
                                name: 'createLink',
                                placeholder: 'URL (http://www.example.com)',
                                button_class: 'btn-primary',
                                button_text: 'Add'
                            },
                            {name: 'unlink'},
                            null,
                            image,
                            null,
                            {name: 'undo'},
                            {name: 'redo'},
                            null

                        ],
                //speech_button:false,//hide speech button on chrome

                'wysiwyg': {
                    hotKeys: {} //disable hotkeys
                }
            }).prev().addClass('wysiwyg-style2');
        }
    });
    
     $(".wysiwyg-toolbar").each(function(){
        $(this).append("<div class='btn-group clear-format-btn'>\n\
                        <a role=\"button\" class=\"btn btn-sm btn-default clear-format-btn\" data-edit=\"clearformat\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Clear format\">\n\
                            <i class=\"ace-icon fa fa-eraser\"></i>\n\
                        </a>\n\
                        </div>");
    });
    
    $(".clear-format-btn").each(function(){
        $(this).click(function(){
            $('.wysiwyg-editor').each(function () {
                $(this).html($(this).text());
            });
        });
    });

    $('button[type="submit"]').click(function (e) {

        var txt = $(".usefultextarea");
        var ed = $(".wysiwyg-editor");

        //a validation
        if ($(ed.length > 0)) {
            $("a", ed).attr('target', '_blank');
            $("a", ed).each(function (i, el) {
                var url = $(el).attr('href');
                if (!/^(f|ht)tps?:\/\//i.test(url)) {
                    url = "http://" + url;
                }
                $(el).attr('href', url);
            });
        }
        //news comment textarea
        if ($("form[name=tj_news_form_replies]").length > 0) {
            if ($("form[name=tj_news_form_replies]").valid()) {
                $('#tj_news_form_replies_comment').val($('#commentEditor').html());
                $('.modal-post-content').html($('#tj_news_form_replies_comment').val());
                if ($('#tj_news_form_replies_useForumAlias:checked').length > 0)
                    $('.user-preview').html($('.forum-alias').html());
                else
                    $('.user-preview').html($('.real-name').html());
                $('#comment-preview').modal('show');
            }
        }
        //insert editor value into the textarea
        if (($(this).attr('id') === 'tj_news_form_replies_submit')) {
            e.preventDefault();
        } else if (txt.length > 0 && txt.length == 1) {
            txt.val(ed.html());
        } else if (txt.length > 1) {
            txt.each(function () {
                if ($(this).hasClass('prime')) {
                    var ed1 = $(".wysiwyg-editor.prime");
                    $(this).val(ed1.html());

                }
                if ($(this).hasClass('bis')) {
                    var ed2 = $(".wysiwyg-editor.bis");
                    $(this).val(ed2.html());
                }
            });
        }
        
        if (($(this).attr('id') === 'tj_news_form_replies_submit')) {
            e.preventDefault();
            $("#" + $(this).attr('id')).parents('form').submit()
        }

    });

});

// education.js - wizard.js(profile) - forum.js - job.js - news.js