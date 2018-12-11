
var lang = {
    de : {
        required: "Dieses Feld ist ein Pflichtfeld.",
        maxlength: $.validator.format( "Gib bitte maximal {0} Zeichen ein." ),
        minlength: $.validator.format( "Gib bitte mindestens {0} Zeichen ein." ),
        rangelength: $.validator.format( "Gib bitte mindestens {0} und maximal {1} Zeichen ein." ),
        email: "Gib bitte eine gültige E-Mail Adresse ein.",
        url: "Gib bitte eine gültige URL ein.",
        date: "Gib bitte ein gültiges Datum ein.",
        number: "Gib bitte eine Nummer ein.",
        digits: "Gib bitte nur Ziffern ein.",
        equalTo: "Bitte denselben Wert wiederholen.",
        range: $.validator.format( "Gib bitte einen Wert zwischen {0} und {1} ein." ),
        max: $.validator.format( "Gib bitte einen Wert kleiner oder gleich {0} ein." ),
        min: $.validator.format( "Gib bitte einen Wert größer oder gleich {0} ein." ),
        creditcard: "Gib bitte eine gültige Kreditkarten-Nummer ein.",
        remote:"Nicht korrekte Nummer"
    }
};

if(locale in lang){
    $.extend( $.validator.messages,  lang[locale]);
}