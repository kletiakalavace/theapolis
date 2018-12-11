CKEDITOR.editorConfig = function( config ) {
    config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
        { name: 'styles', groups: [ 'styles' ] },
        { name: 'colors', groups: [ 'colors' ] },
        { name: 'about', groups: [ 'about' ] },
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
        { name: 'links', groups: [ 'links' ] },
        { name: 'insert', groups: [ 'insert' ] },
        { name: 'forms', groups: [ 'forms' ] },
        { name: 'tools', groups: [ 'tools' ] },
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others', groups: [ 'others' ] }

    ];

    config.removeButtons = 'Subscript,Superscript,Cut,Copy,Paste,PasteText,Undo,Redo,Scayt,Anchor,Image,Table,HorizontalRule,SpecialChar,Maximize,Source,Strike,NumberedList,Outdent,Indent,Blockquote,Styles,About,Format,BulletedList,PasteFromWord';
    config.enterMode = CKEDITOR.ENTER_BR;
    config.skin = 'office2013';
    config.removePlugins = 'blockquote,save,flash,iframe,tabletools,pagebreak,templates,about,showblocks,newpage,language,print,div,elementspath';
    config.autoGrow_onStartup = true;

};