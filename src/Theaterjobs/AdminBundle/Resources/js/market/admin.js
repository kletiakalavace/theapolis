var job;
$(document).ready(function() {
    $('#allUsers').prop('checked', true);
    $("#organization").select2();
    var input = $('#slug').val();
        $.ajax({
            type: 'GET',
            url: Routing.generate('tj_admin_market_data'),
            data: {slug: input},
            success: function(data) {
                showModal(data);
            }
        });
    
});
function showModal(data)
{
    $('#allUsers').prop('checked', true);
    $('#warning').addClass('hidden');
    job = data['job'];
    console.log(data);
   
    if (job[0].organization)
        var orga = job[0].organization.id;
    $('#organization').children().remove().end();
    $('#user').children().remove().end();
    $('#user').addClass('hidden');
    data['organization'].forEach(function(p) {

        var element = $("<option></option>");
        element.val(p.id).html(p.name);

        if (p.id === orga)
            element.prop('selected', true);
        $('#organization').append(element);

    });


  
    $("#organization").select2({width: 'resolve'});
  
    loadUsers();


}
$('input[name=userType]').change(function() {
    loadUsers();
    });
    
    function loadUsers(){
    $('#warning').addClass('hidden');
    $('#user').children().remove().end();
    $('#user').addClass('hidden');
    //   console.log($('input[type=radio]:checked').val());
    var type = $('input[name=userType]:checked').val();
    var input = $('#organization').val();
    $.ajax({
        type: 'GET',
        url: Routing.generate('tj_admin_get_market_users'),
        data: {id: input, type: type},
        success: function(data) {
            populateUsers(data);
        }
    });
}

function populateUsers(data)
{
    if (data.length == 0) {
        $('#warning').removeClass('hidden');
        $('input[name=userType]').prop('checked', false);
    }
    data.forEach(function(p) {

        var element = $("<option></option>");
        $text = 'Username :<strong>' + p.username + '</strong> First Name: <strong>' + p.firstName + '</strong> Last Name: ' + p.lastName;
        element.val(p.id).html($text);
        if (job[0].user)
            var usr = job[0].user.id;
        else
            usr = 0;
        if (p.id === usr)
            element.prop('selected', true);
        $('#user').append(element);
        $("#user").select2({width: 'resolve'});
        $('#user').removeClass('hidden');
    });
}
$('#organization').change(function() {
    $('#user').children().remove().end();
    $('#user').addClass('hidden');
    $('input[name=userType]').prop('checked', false);
});

 $('[data-toggle=offcanvas]').click(function() {
                    $(this).toggleClass('pull-right');
                    $(this).find('i').toggleClass('glyphicon-chevron-right glyphicon-chevron-left');
                    $('#customMenu').toggleClass('vis');
                   
                });

