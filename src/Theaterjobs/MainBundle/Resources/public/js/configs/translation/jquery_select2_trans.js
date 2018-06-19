
var lang = {
    de : {
        formatNoMatches: function () { return "Keine Übereinstimmungen gefunden"; },
        formatInputTooShort: function (input, min) { var n = min - input.length; return "Bitte " + n + " Zeichen mehr eingeben"; },
        formatInputTooLong: function (input, max) { var n = input.length - max; return "Bitte " + n + " Zeichen weniger eingeben"; },
        formatSelectionTooBig: function (limit) { return "Du kannst nur " + limit + " Eintr" + (limit === 1 ? "ag" : "äge") + " auswählen"; },
        formatLoadMore: function (pageNumber) { return "Lade mehr Ergebnisse…"; },
        formatSearching: function () { return "Suche…"; },
        formatMatches: function (matches) { return matches + " Ergebnis " + (matches > 1 ? "se" : "") + " verfügbar, zum Navigieren die Hoch-/Runter-Pfeiltasten verwenden."; }
    }
};

if(locale in lang){
    $.extend($.fn.select2.defaults, lang[locale]);
}