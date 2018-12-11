(function (threadID) {
//####### Globals
    //Prevent multiple requests on enter/send button
    var sendingMutex = 0;
    var socketUserID;

    var thread = $('#inboxMessages');
//####### jquery events

    // Catch click event
    thread.find('#replyButton').click(function (e) {
        e.preventDefault();
        if (!sendingMutex) {
            sendMessage();
        }
    });

    //Deletes a thread
    $('.content-msg').on('click', '.delSingleMsg', function (e) {
        e.preventDefault();
        var self = this;
        bootbox.confirm({
            message: "Are you sure do you really want to delete this message?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-success'
                }
            },
            callback: function (result) {
                var data = {
                    socketUserID: socketUserID
                };
                if(result) {
                    $.ajax({
                        type: 'POST',
                        url: $(self).attr('href'),
                        data: data,
                        dataType: 'json',
                        success: function (data) {
                            if (data.success) {
                                if (data.result) {
                                    var message = self.closest('.body');
                                    message.innerHTML = data.result;
                                }
                            } else {
                                bootbox.alert({
                                    message: showErrors(data.message)
                                });
                            }
                        },
                        error : function (xhr, status, error) {
                            bootbox.alert({
                                title : false,
                                message : error
                            })
                        }
                    })
                }
            }
        });
    });

    thread.find('#showMore').click(function (e) {

        var self = $(this);
        //Page Number
        var pageNr = self.attr('data-page');
        //Last message
        var lastMsg = thread.find('.singleMessage').first().attr('id');
        var lastMsgID = (lastMsg !== undefined) ? lastMsg: -1;
        self.hide();

        $.ajax({
            type: 'POST',
            url: Routing.generate('tj_message_show_more', {id : threadID, '_locale' : locale}),
            data: {page : pageNr, after : lastMsgID},
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    if (data.messages === "") {
                        self.hide();
                        self.off('click');
                        return;
                    }
                    $(data.messages).insertAfter(self.parent());
                    self.attr('data-page', ++pageNr);
                    self.show();
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
                })
            }
        })
    });


//####### js functions

    /**
     * Sends a message for current user
     */
    function sendMessage() {

        sendingMutex = 1;

        var replyForm = thread.find('form[name="replyForm"]').clone();

        if(replyForm.find('textarea[name="body"]').val() === "") {
            sendingMutex = 0;
            return;
        }

        //Disable reply button
        thread.find('#replyButton').prop('disabled', true);
        //Remove current message
        thread.find('form[name="replyForm"] textarea[name="body"]').val("");

        replyForm.validate();

        if (replyForm.valid()){

            var postData = {
                body : replyForm.find('textarea[name="body"]').val(),
                socketUserID: socketUserID
            };

            //Send the message
            $.ajax({
                type: replyForm.attr('method'),
                url: replyForm.attr("action"),
                data: postData,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        //In case error on publishing messages through node
                        if (data.message) {
                            pushMessage(data.message);
                        }
                    } else {
                        bootbox.alert({
                            message: showErrors(data.message)
                        });
                    }
                    $('#replyButton').prop('disabled', false);
                    sendingMutex = 0;
                },
                error : function (xhr, status, error) {
                    bootbox.alert({
                        title : false,
                        message : error
                    });
                    $('#replyButton').prop('disabled', false);
                    sendingMutex = 0;
                },
                complete: function() {
                    $('#replyButton').prop('disabled', false);
                    sendingMutex = 0;
                }
            });
        } else {
            sendingMutex = 0;
        }
    }

    /**
     * push a rendered template to messages
     * @param msg
     */
    function pushMessage(msg) {
        //Append to messages
        thread.find('.content-msg').append(msg);
        animateNewMsg();
        moveThreadToTop(threadID);
    }

    function emitSeen(socket, senderID) {
        if (socket !== undefined && senderID !== undefined) {
            socket.emit('seenMessage', senderID);
        }
    }

//####### Socket events

    //Connect with socket
    socket = io(nodeServer + '/singleThread');

    //When user connects with server
    socket.on('connect',function(e) {
        //Obtained socket id
        socketUserID = socket.io.engine.id;

        //Join to room
        socket.emit('joinRoom', {room : socketThreadID});
        emitSeen(socket, senderID);

    });

    //On error
    socket.on('connect_error', function (err) {
        console.log(err);
        socket.close();
    });

    /*    On disconect
        socket.on('disconnect', function(){
            socket.close();
        });*/

    //On Timeout
    socket.on('timeout', function(){
        socket.close();
    });

    //When a new message arrives
    socket.on('newMessage', function (data) {
        //Append to messages
        if(data.sender === senderID) {
            pushMessage(data.message.sender);
        } else {
            pushMessage(data.message.receiver);
        }

        if (data.sender !== senderID) {
            //Play sound
            sound.play();

            //mark as seen the message
            $.ajax({
                type: 'POST',
                url: Routing.generate('tj_message_seen', {'_locale' : locale, 'id' : threadID}),
                data: {},
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        emitSeen(socket, senderID);
                    } else {
                        bootbox.alert({
                            message: showErrors(data.message)
                        });
                    }
                },
                error : function (xhr, status, error) {
                    bootbox.alert({
                        title : false,
                        message : error
                    })
                }
            });
        }
    });

    //When a new message arrives
    socket.on('deleteMessage', function (data) {
        var message = thread.find('#' + data.message.messageID + ' .body');
        message.html(data.message.message)
    });

    //Emit an online status
    socket.on('online', function(){
        thread.find('#isActive').show();
        window.setTimeout(function(){
            thread.find('#isActive').hide();
        }, 50000);
    });

    //Appends the seen badge
    socket.on('messageSeen', function (data) {
        if (senderID !== data.sender) {
            var seenMsgs = $('.singleMessage .sender').filter(function() {
                return $(this).find('.seen').length === 0;
            });

            var seenBadge = '<i class="fa fa-check seen" aria-hidden="true"></i>\n' +
                '<i class="fa fa-check second seen" aria-hidden="true"></i>';

            seenMsgs.each(function (e) {
                $(this).find('.time-seen').append(seenBadge);
            })
        }
    })

})(threadID);
