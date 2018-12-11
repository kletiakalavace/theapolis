//Run immediately after index page load
//Load threads
$.ajax({
    type: 'POST',
    url: Routing.generate('tj_message_search', {'_locale' : locale}),
    data: {q : ''},
    dataType: 'json',
    success: function (data) {
        if (data.success) {
            var showMore = $('#showMoreThreads');
            //If there are results show them
            if (data.result !== "") {
                //Show messenger content
                $('.content-messenger').show();

                // //Add new results to list
                var t = $('' + data.result).insertBefore(showMore.parent());
                t.each(function (e) {
                    if (!$(this).is('li')) {
                        t.splice(e, 1);
                    }
                });

                //If there are more than 5 results show show more link
                if (t.length > 4) {
                    showMore.show();
                }

                //Select the active thread and load the msgs
                if (isNaN(replyThread)) {
                    selectActiveThread(t);
                } else {
                    loadThread(replyThread);
                }
            } else {
                //Show no messages label
                $('.no-messages').show();
            }
        } else {
            bootbox.alert({
                title : false,
                message : data.error
            })
        }
    },
    error : function (xhr, status, error) {
        bootbox.alert({
            title : false,
            message : error
        });
        loading.hide();
    }
});