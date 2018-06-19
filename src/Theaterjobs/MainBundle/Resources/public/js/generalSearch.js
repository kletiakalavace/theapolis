const searchResults = $('.search-results');
const searchField = $('.search-field');

generalSearch = (query) => {
    if (!query) {
        query = searchField.val();
    }
    if (query) {
        $.ajax({
            url: Routing.generate('tj_main_search', {'_locale': locale}),
            type: 'GET',
            data: {
                search: query
            },
            success: function (data) {
                if (data) {
                    searchResults.html(data);
                    searchResults.addClass('show');
                }

            }
        });
    }
};

generalSearch();

searchField.typeahead({
    minLength: 3,
    delay: 1000,
    source: (query, process) => {
        this.generalSearch(query);
    },
    updater: (item) => {
        return item;
    },
    matcher: () => {
        return true;
    }
}).keyup(() => {
    if (searchField.val().length < 3) {
        searchResults.removeClass('show');
    }
});