const dataTableElement = $('#data-table');

dataTableInitialize = () => {
    table =
        dataTableElement.DataTable(
            {
                order: [],
                columnDefs: [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
                pageLength: 50,
                ajax: {
                    url: dataTableURL,
                    type: "GET",
                    dataSrc: 'data',
                    success: (response) => {
                        table.clear();
                        table.rows.add(response.data);
                        table.draw();
                    }
                }
            });

    dataTableElement.removeClass('hidden');
};


