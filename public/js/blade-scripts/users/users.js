
function validateCreateUser(){
    // Email exists
    if($('#email_addr_text').val() === ''){
        alert('Email is missing');
        return false;
    }
    // Email validation
    if($('#email_addr_text').val() !== ''){
        var email = $('#email_addr_text').val().toLowerCase();
        var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;

        if(!re.test(email)){
            alert('Email is invalid');
            return false;
        }
    }
    // First Name exists
    if($('#first_name_text').val() === ''){
        alert('First Name is missing');
        return false;
    }
    // First Name validation
    if($('#first_name_text').val() !== ''){
        var firstName = $('#first_name_text').val().toLowerCase();
        var re = /^\w+$/;

        if(!re.test(firstName)){
            alert('First Name is invalid');
            return false;
        }
    }
    // Last Name exists
    if($('#last_name_text').val() === ''){
        alert('Last Name is missing');
        return false;
    }
    // Last Name validation
    if($('#last_name_text').val() !== ''){
        var lastName = $('#last_name_text').val().toLowerCase();
        var re = /^\w+$/;

        if(!re.test(lastName)){
            alert('Last Name is invalid');
            return false;
        }
    }
    // Display Name exists
    if($('#nickname_text').val() === ''){
        alert('Nickname is missing');
        return false;
    }
    // Last Name validation
    if($('#nickname_text').val() !== ''){
        var displayName = $('#last_name_text').val().toLowerCase();
        var re = /^\w+$/;

        if(!re.test(displayName)){
            alert('Nickname is invalid');
            return false;
        }
    }
    // Validate password
    if($('#set_password').is(':checked')){
        if($('#new_password').val() !== $('#retype_new_password').val()){
            alert('Password and Re-enter password are not identical');
            return false;
        }

        if($('#new_password').val() === ''){
            alert('Password not entered');
            return false;
        }
    }

    return true;
}

function createUser() {

    if (validateCreateUser()) {

        submitCreateUser();
    };
}

function submitCreateUser(){
    var formData = {
        email_addr_text:    $('#email_addr_text').val(),
        first_name_text:    $('#first_name_text').val(),
        last_name_text:     $('#last_name_text').val(),
        nickname_text:  $('#nickname_text').val(),
        system_admin:       $('#system_admin').is(':checked'),
        active:             $('#active').is(':checked'),
        instance_manage:    $('#instance_manage').val(),
        instance_policy:    $('#instance_policy').val(),
        set_password:       $('#set_password').is(':checked'),
        password_text:      $('#new_password').val()
    };

    $.ajax({
        url : "/v1/users",
        type: "POST",
        data : formData,
        success: function(data, textStatus, jqXHR)
        {
            if(data === 'OK'){
                window.location = '/v1/users';
            }
            else
                alert('Error: Email already exists');
        },
        error: function (jqXHR, textStatus, errorThrown)
        {

        }
    });
}



function cancelCreateUser(){

    window.location = '/v1/users';
}


function submitForm(){

    if($('#set_password').is(':checked')){

        var passwordEq = function(){
            if($('#new_password').val() !== $('#retype_new_password').val())
                return false;
            else
                return true;
        }

        var passwordEmpty = function(){
            if($('#new_password').val() === '')
                return false;
            else
                return true;
        }

        if(passwordEq() && passwordEmpty()) {
            $( "#user_form" ).submit();
        }
        else{
            if(!passwordEmpty())
            {
                alert('Password not entered');
                return;
            }

            if(!passwordEq())
            {
                alert('Password and Re-enter password are not identical');
                return;
            }
        }
    }
    else
    {
        $( "#user_form" ).submit();
    }
}







function validateEditUser(){
    // Email exists
    if($('#email_addr_text').val() === ''){
        alert('Email is missing');
        return false;
    }
    // Email validation
    if($('#email_addr_text').val() !== ''){
        var email = $('#email_addr_text').val().toLowerCase();
        var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;

        if(!re.test(email)){
            alert('Email is invalid');
            return false;
        }
    }
    // First Name exists
    if($('#first_name_text').val() === ''){
        alert('First Name is missing');
        return false;
    }
    // First Name validation
    if($('#first_name_text').val() !== ''){
        var firstName = $('#first_name_text').val().toLowerCase();
        var re = /^\w+$/;

        if(!re.test(firstName)){
            alert('First Name is invalid');
            return false;
        }
    }
    // Last Name exists
    if($('#last_name_text').val() === ''){
        alert('Last Name is missing');
        return false;
    }
    // Last Name validation
    if($('#last_name_text').val() !== ''){
        var lastName = $('#last_name_text').val().toLowerCase();
        var re = /^\w+$/;

        if(!re.test(lastName)){
            alert('Last Name is invalid');
            return false;
        }
    }
    // Display Name exists
    if($('#nickname_text').val() === ''){
        alert('Last Name is missing');
        return false;
    }
    // Last Name validation
    if($('#nickname_text').val() !== ''){
        var displayName = $('#last_name_text').val().toLowerCase();
        var re = /^\w+$/;

        if(!re.test(displayName)){
            alert('Last Name is invalid');
            return false;
        }
    }
    // Validate password
    if($('#set_password').is(':checked')){
        if($('#new_password').val() !== $('#retype_new_password').val()){
            alert('Password and Re-enter password are not identical');
            return false;
        }

        if($('#new_password').val() === ''){
            alert('Password not entered');
            return false;
        }
    }


    return true;
}

function editUser(id) {

    if (validateEditUser()) {

        submitEditUser(id);
    }
}

function submitEditUser(id){
    var  formData = {
        user_id: id,
        email_addr_text: $('#email_addr_text').val(),
        first_name_text: $('#first_name_text').val(),
        last_name_text: $('#last_name_text').val(),
        nickname_text: $('#nickname_text').val(),
        system_admin: $('#system_admin').is(':checked'),
        active: $('#active').is(':checked'),
        instance_manage: $('#instance_manage').val(),
        instance_policy: $('#instance_policy').val(),
        set_password: $('#new_password').val(),
        password_text: $('#new_password').val()
    };

    $.ajax({
        url : "/v1/users/" + id,
        type: "PUT",
        data : formData,
        success: function(data, textStatus, jqXHR)
        {
            if(data === 'OK'){
                window.location = '/v1/users';
            }
            else
                alert('Error: Email already exists');
        },
        error: function (jqXHR, textStatus, errorThrown)
        {

        }
    });
}

function cancelEditUser(){
    window.location = '/v1/users';
}


function initUserEditSet(status){

    if(status)
        $("#advancedUserOptions").show();
    else
        $("#advancedUserOptions").hide();

}











function systemAdminClick(){

    var status = $("#system_admin").is(':checked');

    if(Boolean(status))
        $("#advancedUserOptions").hide();
    else
        $("#advancedUserOptions").show();
}






function removeUser(id, name, type) {

    //console.log(id +', ' + name +', ' + type);

    if(confirm('Remove User "' + name + '" ? ')){
        $('#single_delete_' + id + '_' + type).submit();
        return true;
    }
    else
        return false;

}


$('#selectedUsersRemove').click(function(){

    var deleteArrayIds = [];
    var deleteArrayTypes = [];

    $('input[type=checkbox]').each(function () {

        var val = this.value.split(',');

        if(this.checked){
            deleteArrayIds.push(val[0]);
            deleteArrayTypes.push(val[1]);
        }
    });

    if(!deleteArrayIds.length){
        alert('No User(s) Selected!');
        return true;
    }

    $('#_selectedIds').val(deleteArrayIds);
    $('#_selectedTypes').val(deleteArrayTypes);
    console.log(deleteArrayIds);
    console.log(deleteArrayTypes);

    if(confirm('Remove Selected Users?')){
        $('#multi_delete').submit();
        return true;
    }
    else
        return false;
});



/*
function confirmRemoveUser(id, admin) {

    var state = null;
    var level = '';

    if(admin){
        level = '_admin';
        state = $("#user_button_" + id + '_admin').attr('value');
    }
    else
        state = $("#user_button_" + id).attr('value');


    if (state === 'delete') {
        $("#user_button_" + id + level).html('Confirm!');
        $("#user_button_" + id + level).attr('value', 'confirm');
        $("#user_button_" + id + level).attr('class', 'btn btn-danger btn-xs');
        $("#user_button_" + id + level).attr('style', 'width: 75px');
        $("#user_button_cancel_" + id + level).show();
        $("#user_checkbox_" + id + level).hide();
        $("#actionColumn").attr('width', '200px');
    }

    if (state === 'confirm') {
        $.ajax({
            url: "/v1/users/" + id + level,
            type: "DELETE",
            success: function (data, textStatus, jqXHR) {
                window.location = 'users'
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error');
            }
        });
    }
};


function cancelRemoveUser(id, admin) {

    var level = '';

    if(admin){
        level = '_admin';
    }

    $( "#user_button_" + id + level).html('');
    $( "#user_button_" + id + level).attr('value', 'delete');
    $( "#user_button_" + id + level).attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
    $("#user_button_" + id + level).attr('style', 'width: 25px');
    $( "#user_button_cancel_" + id + level).hide();//attr('display', 'none');
    $("#user_checkbox_" + id + level).show();
    $("#actionColumn").removeAttr('width');

};

function cancelRemoveSelectedUsers() {
    $( "#selectedUsersRemove").html('');
    $( "#selectedUsersRemove").attr('value', 'delete');
    $( "#selectedUsersRemove").attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
    $("#selectedUsersRemove").attr('style', 'width: 40px');
    $( "#selectedUserRemoveCancel").hide();//attr('display', 'none');
}


function confirmRemoveSelectedUsers () {

    var deleteArray = [];

    $('input[type=checkbox]').each(function () {

        if(this.checked)
            deleteArray.push(this.value);
    });

    if(deleteArray.length) {

        var state = $("#selectedUsersRemove").attr('value');

        if (state === 'delete') {
            $("#selectedUsersRemove").html('Confirm!');
            $("#selectedUsersRemove").attr('value', 'confirm');
            $("#selectedUsersRemove").attr('class', 'btn btn-danger btn-sm');
            $("#selectedUsersRemove").attr('style', 'width: 75px');
            $("#selectedUserRemoveCancel").show();
        }

        if (state === 'confirm') {
            $( "#selectedUsersRemove").html('');
            $( "#selectedUsersRemove").attr('value', 'delete');
            $( "#selectedUsersRemove").attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
            $("#selectedUsersRemove").attr('style', 'width: 40px');
            $( "#selectedUserRemoveCancel").hide();

            $.ajax({
                url : "/v1/users/" + deleteArray,
                type: "DELETE",
                success: function(data, textStatus, jqXHR)
                {
                    window.location = 'users'
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log('error');
                }
            });
        }
    }
};
*/


var tableRowIndex = null;
var tableColIndex = null;



var table = $('#userTable').DataTable({
    "dom": '<"toolbar">',
    "aoColumnDefs": [
        {
            "bSortable": false,
            "aTargets": [1]
        },
        {
            "targets": [0],
            "visible": false
        }
    ]
});

/*
$('#userTable tbody').on( 'click', 'td', function () {

    var rowId = table.cell( this ).index().row - (10 * table.page.info().page);
    var cellId = table.cell( this ).index().column;

    var user_id = $("#userTable tr:eq('" + (rowId + 1) + "')").find('input[type="hidden"]').val();

    if(cellId > 1)
        window.location = 'users/' + user_id + '/edit';


} );
*/


/**/
$('#userTable tbody').on( 'click', 'tr', function () {

    tableRowIndex = null;

    var $tr = $(this);

    while(tableColIndex === null){
        //wait
    }

    var user_id = $tr.find('input[type="hidden"][id="user_id"]').val();
    var user_admin = $tr.find('input[type="hidden"][id="user_type"]').val();

    tableRowIndex = user_id;

    var user_type = null;

    if(tableColIndex !== null){

        if(user_admin)
            user_type = 'admin';
        else
            user_type = 'user';

        console.log(user_admin);

         if(tableColIndex > 1)
             window.location = 'users/' + user_id + '/edit?user_type=' + user_type;
    }
} );


$('#userTable tbody').on( 'click', 'td', function () {

    tableColIndex = null;

    var cellId = table.cell( this ).index().column;

    tableColIndex = cellId;
});



var table = $('#userTable').DataTable();
var info = table.page.info();

$("div.toolbar").html('');

if($('#tableInfo').html() === '')
    $('#tableInfo').html('Showing Users ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal);


$('#_next').on( 'click', function () {

    table.page( 'next' ).draw( false );

    if((table.page.info().page + 1) === table.page.info().pages){
        $('#_next').prop('disabled', true);
    }

    if(table.page.info().page > 0){
        $('#_prev').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing Users ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
} );

$('#_prev').on( 'click', function () {

    table.page( 'previous' ).draw( false );

    if(table.page.info().page === 0)
        $('#_prev').prop('disabled', true);

    if((table.page.info().page + 1) === table.page.info().pages)
        $('#_next').prop('disabled', true);

    if(table.page.info().pages > 1)
        $('#_next').prop('disabled', false);

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing Users ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
});

function selectPage(page) {

    table.page( page ).draw( false );
    $('#currentPage').html('Page ' + (page + 1));

    if(page === 0)
        $('#_prev').prop('disabled', true);

    if((page + 1) < table.page.info().pages)
        $('#_next').prop('disabled', false);

    if(page > 0)
        $('#_prev').prop('disabled', false);

    if((page + 1) === table.page.info().pages)
        $('#_next').prop('disabled', true);

    $('#tableInfo').html('Showing Users ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
}

function filterGlobal () {
    $('#userTable').DataTable().search(
        $('#userSearch').val()
    ).draw();
}

$( document ).ready(function() {

    if(info){
        for(var i = 0; i < info.pages; i++){
            $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
        }

        if(info.pages > 1)
            $('#_next').prop('disabled', false);

        $('#_prev').prop('disabled', true);

        $('#userSearch').on( 'keyup click', function () {
            filterGlobal();
        } );
    }
});

