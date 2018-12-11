CKEDITOR.editorConfig = function (config) {
    config.toolbarGroups = [
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph']},
        {name: 'styles', groups: ['styles']},
        {name: 'colors', groups: ['colors']},
        {name: 'about', groups: ['about']},
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing']},
        {name: 'links', groups: ['links']},
        {name: 'insert', groups: ['insert']},
        {name: 'forms', groups: ['forms']},
        {name: 'tools', groups: ['tools']},
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'others', groups: ['others']},
        '/',

    ];

    config.removeButtons = 'Subscript,Superscript,Paste,Cut,Undo,Redo,Image,Table,HorizontalRule,SpecialChar,Maximize,Source,Outdent,Indent,Styles,Format,About,PasteText,PasteFromWord,Copy,Scayt,Blockquote,Anchor';
    config.enterMode = CKEDITOR.ENTER_BR;
    config.skin = 'office2013';
    config.removePlugins = 'elementspath';
    config.autoGrow_onStartup = true;
};