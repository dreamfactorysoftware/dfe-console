var table;
var usrSearch;
var tableRowIndex = null;
var tableColIndex = null;

$(function() {

    table = $('#userTable').DataTable({
        "dom": '<"toolbar">ti',
        "columns": [
            {
                "class": "details-control",
                "orderable":false,
                "data": null,
                "defaultContent": "",
                "render": function (data) {
                    var userData = JSON.parse(data.original);
                    var $template = $('form.user_frm_template').clone();
                    var adminFlag = (userData.admin === true) ? 'admin' : 'user';
                    $template.attr('action', 'users/' + userData.id);
                    $template.prop('id', 'single_delete_' + userData.id + '_');
                    $template.find('input#edit_url').val('users/edit/'+ userData.id + '/' + adminFlag);
                    $template.find('input#user_name').val(userData.first_name_text + ' ' + userData.last_name_text);
                    $template.find('.user_checkbox').prop('id', 'user_checkbox_' + userData.id);
                    $template.find('input#user_id').val(userData.id);
                    if (userData.admin === true) {
                        $template.find('input#user_type').val('1');
                        $template.find('.user_checkbox, button.remove_user').remove();
                    }
                    return $template.prop('outerHTML');
                }
            },
            {
                "name": "create_date",
                "data": "create_date"
            },
            {
                "name": "first_name_text",
                "data": "first_name"
            },
            {
                "name": "last_name_text",
                "data": "last_name"
            },
            {
                "name": "email_addr_text",
                "data": "email"
            },
            {
                "name": "company_name_text",
                "data": "company"
            },
            {
                "name": "phone_text",
                "data": "phoneText"
            },
            {
                "class": "details-control",
                "orderable":false,
                "data": null,
                "defaultContent": "",
                "render": function (data) {
                    var userData = JSON.parse(data.original);
                    if(userData.admin === true){
                        var str = '<span class="label label-primary" id="user_type">System Admin</span><br/>';
                    } else {
                        var str = '<span class="label label-info" id="user_type">Instance Owner</span><br/>';
                    }
                    if(data.is_active == 1){
                        return str + ' <span class="label label-success">Active</span>';
                    } else {
                        return str + ' <span class="label label-warning">Not Active</span>';
                    }

                }
            }
        ],
        "order": [[1, 'desc']],
        "processing" : true,
        "serverSide" : true,
        "ajax": {
            "url": "/v1/users/get_users"
        },
        "pageLength": 50,
        "infoCallback": function( settings, start, end, max, total, pre ) {
            return "Showing " + start + " to " + end +" of " + total.toLocaleString() + " Users";
        }

    });

    /**
     * Main draw callback for datatable.
     */
    table.on('draw', function () {
        /**
         * Highlight feature for search terms
         */
        if(table.search){
            var body = $( table.table().body() );
            body.unhighlight();
            body.highlight( table.search() );
        }
        updatePageDropdown();
    });

    table.on('preXhr', function(){
        add_waiting();
    });

    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });

    $('#userSearch').on( 'keyup', function () {
        $('#searchclear').show();
        window.clearTimeout(usrSearch);
        if($('#userSearch').val().length >= 3){
            usrSearch = setTimeout(function(){
                    table.search($('#userSearch').val()).ajax.reload();
            }, 600);
        } else if($('#userSearch').val().length == 0){
            table.search('').ajax.reload();
            $('#searchclear').hide();
        }

    });

    $('#searchclear').on('click', function(){
        $('#userSearch').val('');
        $('#searchclear').hide();
        table.search('').ajax.reload();
    });

    $('#userTable').on('click', '.remove_user', function(){
        var uid = $(this).parent().find('#user_id').val();
        var name = $(this).parent().find('#user_name').val();
        removeUser(uid, name, '');

    });


    $("div.toolbar").html('');

    $('#_next').on( 'click', function () {
        _nextPage();
    } );

    $('#_prev').on( 'click', function () {
        _prevPage();
    });


    $("#new_password").keyup(checkPasswordMatch);
    $("#retype_new_password").keyup(checkPasswordMatch);


    $('#selectedUsersRemove').click(function(){

        var deleteArrayIds = [];
        var deleteArrayTypes = [];
        var deleteNames = '';

        $('input[type=checkbox]').each(function () {
            if($(this).is(':checked')){
                deleteNames += '"' + $(this).parent().find('input#user_name').val() + '", ';
                deleteArrayIds.push($(this).parent().find('input#user_id').val());
                deleteArrayTypes.push($(this).parent().find('input#user_type').val());
            }
        });

        deleteNames = deleteNames.substring(0, deleteNames.length - 2);

        if(!deleteArrayIds.length){
            alert('No User(s) Selected!');
            return true;
        }

        $('#_selectedIds').val(deleteArrayIds);
        $('#_selectedTypes').val(deleteArrayTypes);

        if(confirm('Remove Selected Users ' + deleteNames + ' ?')){
            $('#multi_delete').submit();
            return true;
        }
        else
            return false;
    });

    $('#refresh').click(function(){
        $('#searchclear').trigger('click');
        table.order([[1, 'asc']]);
        table.ajax.reload();
    });


    /* TODO:Make this a modal */
    $('#userTable tbody').on( 'click', 'tr', function (e) {
        if( ! $(e.target).is('input:checkbox, button.remove_user')) {
            window.location = $(this).find('form input#edit_url').val();
        }
    });

}); //end ready()

function cancelCreateUser(){
    window.location = '/v1/users';
}

function checkPasswordMatch() {
    var password = $("#new_password").val();
    var confirmPassword = $("#retype_new_password").val();

    if (password != confirmPassword) {
        $("#btnSubmitForm").prop("disabled",true);
    }
    else {
        $("#btnSubmitForm").prop("disabled",false);
    }
}

function cancelEditUser(){
    window.location = '/v1/users';
}

function removeUser(id, name, type) {

    if(confirm('Remove User "' + name + '" ? ')){
        $('#single_delete_' + id + '_' + type).submit();
        return true;
    } else {
        return false;
    }

}

function add_waiting(){
    $('#userTable tbody').empty().append(
        '<tr><td colspan="8"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></td></tr>'
    );
}