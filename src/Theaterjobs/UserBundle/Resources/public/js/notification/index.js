/**
 * Delete NI notification
 * @param id
 */
function deleteNotification(id){
    $.ajax({
        type: 'DELETE',
        url: Routing.generate('tj_user_notification_delete', {'_locale': locale, id}),
        success: function (data) {
            if (data.success) {
                var totalCounter = $('#totalNotificationCount');
                var unseenCounter = $('#unseenNotificationCount');
                var redNotificationCounter = $('#redNotificationCount');
                var totalCount = parseInt(totalCounter.text());
                var unseenCount = parseInt(unseenCounter.text());
                var redNotificationsCount = parseInt(redNotificationCounter.text());

                totalCounter.text((totalCount === 0) ? 0 : totalCount - 1);
                unseenCounter.text((unseenCount === 0) ? 0 : unseenCount - 1);
                redNotificationCounter.text((redNotificationsCount === 0) ? 0 : redNotificationsCount - 1);

                $('#notification-' + id).remove();
            }
        }
    });
}
