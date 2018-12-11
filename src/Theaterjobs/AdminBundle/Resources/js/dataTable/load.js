let currentSortElement, adminSearchForm, dataTableURL, table;
let rowsNumber = 50;
let currentPage = 1;
let numberPages = 0;
const dataTableElement = $('#data-table');

dataTableInitialize = () => {
    table =
        dataTableElement.DataTable(
            {
                bFilter: false,
                bPaginate: false,
                bInfo: false,
                order: [],
                columnDefs: [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
                ajax: {
                    url: dataTableURL,
                    type: "GET",
                    dataSrc: 'data',
                    data: () => {
                        let params = {
                            page: currentPage,
                            rows: rowsNumber
                        };

                        return adminSearchForm.serialize().concat('&', $.param(params));
                    },
                    success: (response) => {
                        table.clear();
                        table.rows.add(response.data);
                        table.draw();
                        updatePagesSection(response);
                        numberPages = response.totalPages;
                        $('#data-table-page-number').val(response.page);
                        if (response.page > numberPages) {
                            paginateDataTable(response.page);
                        }
                    }
                }
            });


    dataTableElement.removeClass('hidden');

    /**
     * just in case
     */
    adminSearchForm.submit(function (e) {
        e.preventDefault();
    });

    paginateDataTable = (page) => {
        page = parseInt(page);
        if (page > 0 && page <= numberPages) {
            $('#data-table-page-number').val(page);
            $('#data-table-previous').attr('data-page', page - 1);
            $('#data-table-next').attr('data-page', page + 1);
            currentPage = page;
            table.ajax.reload();
        }
        if (page > numberPages) {
            $('#data-table-page-number').val(numberPages);
        }
    };

    updatePagesSection = (json) => {
        $('#data-table-pages-total').text(json.totalPages);
        $('#data-table-total').text(json.recordsTotal);
    };

    $('#data-table-page-number').change((event) => {
        const newPage = $(event.currentTarget).val();
        if (Math.floor(newPage) === newPage && $.isNumeric(newPage) && newPage !== '' || newPage !== 0) {
            paginateDataTable(newPage);
        } else {
            $(event.currentTarget).val('');
        }
    });

    $('.datatable-paginate-btn').click((e) => {
        paginateDataTable($(e.currentTarget).attr('data-page'));
    });

    $('#data-table-page-length').change((e) => {
        rowsNumber = $(e.currentTarget).val();
        table.ajax.reload();
    });

    $('#data-table-page-length').val(rowsNumber);

    table.on('order.dt', (event, ctx, sorting, columns) => {
        table.clear();
        let sortElement = sorting.pop();
        if (JSON.stringify(currentSortElement) !== JSON.stringify(sortElement)) {
            adminSearchForm.find("#orderCol").val($(event.currentTarget).find('th').eq(sortElement.src).data('name'));
            adminSearchForm.find("#order").val(sortElement.dir);
            currentSortElement = sortElement;
            table.ajax.reload();
        }

    });
};

dataTableResetCurrentPage = () => {
    currentPage = 1;
};

dataTableReload = () => {
    table.ajax.reload();
};

function dataTablePressEnter(e) {
    if (e.keyCode === 13) {
        dataTableResetCurrentPage();
        dataTableReload();
    }
}

function dataTableSearchResults(el, min = 3) {
    if ($(el).val().length === 0 || $(el).val().length >= min) {
        dataTableResetCurrentPage();
        dataTableReload();
    }
}


