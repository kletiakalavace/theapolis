//On opening thread
function animateToMsg() {
    var lastMessage = $('.singleMessage').last().find('.body')[0];
    if(lastMessage !== undefined) {
        lastMessage.scrollIntoView();
    }
    $('form textarea')[0].focus();
}

//On new message arrive
function animateNewMsg() {
    var lastMessage = $('.singleMessage').last().find('.body')[0];
    if(lastMessage !== undefined) {
        lastMessage.scrollIntoView();
    }
    $('form[name="replyForm"] textarea')[0].focus();
}

//Set as active thread
function markThreadActive(self) {
    $('.list-messenger .active').removeClass('active');
    self.closest('li').addClass('active');
    self.find(".list-name-messenger").removeClass("unreaded");
}

//Remove number of unseen msgs from thread and global one
function removeUnseenNr(self) {
    //remove unseen nr
    var newMessagesNr = parseInt(self.find(".nrUnread").html());
    self.find(".nrUnread").remove();

    //Subtract to all unseen messages nr
    var allNewMsg = parseInt($('#allNewMsgs').html());
    if (!isNaN(newMessagesNr) && !isNaN(allNewMsg)) {
        allNewMsg -= newMessagesNr;
        if (allNewMsg === 0) {
            $('#allNewMsgs').remove()
        } else {
            $('#allNewMsgs').html(allNewMsg);
        }
    }
}

//Move thread conversation to Top
function moveThreadToTop(threadID) {
    var thread = $('.list-messenger #thread' + threadID);
    thread.remove();
    $('.list-messenger').prepend(thread);
}

//Select Active thread and load his messages
function selectActiveThread(threads) {
    var active = false;
    threads.each(function (el) {
        var self = $(this);
        if (!active && self.find('.unreaded').length === 0) {
            readThread(self.find('.singleThread'));
            active = true;
        }
    })
}

//Loads messages of a thread(existing on ui)
function readThread(self) {
    $.ajax({
        type: 'GET',
        url: self.attr("href"),
        dataType: 'json',
        success: function (data) {
            if (socket) {
                socket.close();
            }
            if (data.success) {
                markThreadActive(self);
                removeUnseenNr(self);

                $("#inboxMessages").html(data.result);
                $('#inboxMessages').addClass('inbox-active');
                $('#inboxMessages').prepend('<span class="back-list"><i class="fa fa-chevron-left"></i></span>');
                $('.back-list').click(function() {
                    $('#inboxMessages').removeClass('inbox-active');
                });
                animateToMsg();
            } else {
                bootbox.alert({
                    message: showErrors(data.message)
                })
            }
        },
        error: function (xhr, status, error) {
            bootbox.alert({
                title: false,
                message: error
            })
        }
    });
}

//Loads a thread from backend
//And reads it(readThread)
function loadThread(threadId) {
    var thread = $('#thread' + replyThread + ' .singleThread');
    if (thread.length) {
        readThread(thread);
        return;
    }
    $.ajax({
        type: 'GET',
        url: Routing.generate('tj_message_load_thread', {'_locale' : locale, 'id' : threadId}),
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                $('.list-messenger').prepend(data.result);
                readThread($('#thread' + threadId + ' .singleThread'));
            } else {
                bootbox.alert({
                    message: showErrors(data.message)
                })
            }
        },
        error: function (xhr, status, error) {
            bootbox.alert({
                title: false,
                message: error
            })
        }
    });
}

//####### JQuery events

//Load messages of all threads on click
$('.list-messenger').on('click', '.singleThread', function (e) {
    e.preventDefault();
    var self = $(this);
    readThread(self);
});

//Paginate threads
$('.content-messenger').on('click','#showMoreThreads', function (e) {

    var self = $(this);

    //Page Number
    var pageNr = self.attr('data-page');
    var data = {
        q: $('#search-input').val(),
        page: pageNr
    };
    self.hide();
    $.ajax({
        type: 'POST',
        url: Routing.generate('tj_message_search', {'_locale' : locale}),
        data: data,
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                if (data.result === "") {
                    self.hide();
                    self.off('click');
                    return;
                }
                // //Insert and load events
                var newEls = $('' + data.result).insertBefore(self.parent());
                if (newEls.length > 4) {
                    self.attr('data-page', ++pageNr);
                    self.show();
                }
            } else {
                bootbox.alert({
                    message: showErrors(data.message)
                })
            }
        },
        error : function (xhr, status, error) {
            bootbox.alert({
                title : false,
                message : error
            });
            self.show();
        }
    })
});

//Search by subject
$('#search-bar').click(function () {

    var value = $('#search-input').val();
    var loading = $('#searchLoading');
    loading.show();
    $.ajax({
        type: 'POST',
        url: Routing.generate('tj_message_search', {'_locale' : locale}),
        data: {q : value},
        dataType: 'json',
        success: function (data) {

            if (data.success) {
                var showMore = $('#showMoreThreads');
                //Remove current results
                $('.list-messenger li:not(.newThread)').remove();
                //Hide show more button
                showMore.hide();

                //If there are results show them
                if (data.result !== "") {
                    //Add new results
                    var t = $('' + data.result).insertBefore(showMore.parent());

                    //If there are more than 5 results show show more link
                    if (t.length > 4) {
                        showMore.show();
                    }
                    //Reset page
                    showMore.attr('data-page', 2);
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
        },
        complete: function () {
            loading.hide();
        }
    });
});

//Arild Custom JS
jQuery(function($){
    $('ul.list-messenger > li').click(function() {
        $('.page-message').addClass('inbox-active');
    });
});
