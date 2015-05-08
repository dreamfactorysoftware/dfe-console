$( document ).ready(function() {
    $("#servers tr").click(function (e) {
        var server_id = $("#servers tr:eq('" + this.rowIndex + "')").find('input[type="hidden"]').val();
        var cellId = $('td', this).index(e.target);

        if(cellId > 1)
            window.location = 'servers/' + server_id + '/edit';

        e.stopPropagation();
    });
});

var table = $('#serverTable').DataTable({
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

$('#serverTable tbody').on( 'click', 'td', function () {

    var rowId = table.cell( this ).index().row - (10 * table.page.info().page);
    var cellId = table.cell( this ).index().column;

    var user_id = $("#serverTable tr:eq('" + (rowId + 1) + "')").find('input[type="hidden"]').val();

    if(cellId > 1)
        window.location = 'servers/' + user_id + '/edit';

} );


var info = table.page.info();

$("div.toolbar").html('');

if($('#tableInfo').html() === '')
    $('#tableInfo').html('Showing Servers ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal);


$('#_next').on( 'click', function () {

    table.page( 'next' ).draw( false );

    if((table.page.info().page + 1) === table.page.info().pages){
        $('#_next').prop('disabled', true);
    }

    if(table.page.info().page > 0){
        $('#_prev').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing Servers ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
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
    $('#tableInfo').html('Showing Servers ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
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

    $('#tableInfo').html('Showing Servers ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
}

$( document ).ready(function() {
    if(info) {
        for (var i = 0; i < info.pages; i++) {
            $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
        }

        if (info.pages > 1)
            $('#_next').prop('disabled', false);

        $('#_prev').prop('disabled', true);
    }

    var selected = $('#server_type_select').val();

    if(selected){

        $("#server_type_select option").each(function()
        {
            var opt = $(this).val();

            if(opt !== ''){
                if(opt === selected)
                    $('#server_type_' + opt).show();
                else
                    $('#server_type_' + opt).hide();

            }
        });
    }



});





function removeServer(id, name) {
    if(confirm('Remove Server "' + name + '" ?')){
        $('#single_delete_' + id).submit();
        return true;
    }
    else
        return false;
}


$('#selectedServersRemove').click(function(){

    var deleteArray = [];

    $('input[type=checkbox]').each(function () {

        if(this.checked)
            deleteArray.push(this.value);
    });

    if(!deleteArray.length){
        alert('No Server(s) Selected!');
        return true;
    }

    $('#_selected').val(deleteArray);

    if(confirm('Remove Selected Servers?')){
        $('#multi_delete').submit();
        return true;
    }
    else
        return false;
});







/*
function confirmRemoveServer(id) {

    var state = $("#server_button_" + id).attr('value');


    if (state === 'delete') {
        $("#server_button_" + id).html('Confirm!');
        $("#server_button_" + id).attr('value', 'confirm');
        $("#server_button_" + id).attr('class', 'btn btn-danger btn-xs');
        $("#server_button_" + id).attr('style', 'width: 75px');
        $("#server_button_cancel_" + id).show();
        $("#server_checkbox_" + id).hide();
        $("#actionColumn").attr('width', '200px');
    }

    if (state === 'confirm') {
        $.ajax({
            url: "/v1/servers/" + id,
            type: "DELETE",
            success: function (data, textStatus, jqXHR) {
                window.location = 'servers'
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error');
            }
        });
    }
};


function cancelRemoveServer(id, admin) {

    $("#server_button_" + id).html('');
    $("#server_button_" + id).attr('value', 'delete');
    $("#server_button_" + id).attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
    $("#server_button_" + id).attr('style', 'width: 25px');
    $("#server_button_cancel_" + id).hide();//attr('display', 'none');
    $("#server_checkbox_" + id).show();
    $("#actionColumn").removeAttr('width');

};

function confirmRemoveSelectedServers () {

    var deleteArray = [];

    $('input[type=checkbox]').each(function () {

        if(this.checked)
            deleteArray.push(this.value);
    });

    if(deleteArray.length) {

        var state = $("#selectedServersRemove").attr('value');

        if (state === 'delete') {
            $("#selectedServersRemove").html('Confirm!');
            $("#selectedServersRemove").attr('value', 'confirm');
            $("#selectedServersRemove").attr('class', 'btn btn-danger btn-sm');
            $("#selectedServersRemove").attr('style', 'width: 75px');
            $("#selectedServersRemoveCancel").show();
        }

        if (state === 'confirm') {

            $( "#selectedServersRemove").html('');
            $( "#selectedServersRemove").attr('value', 'delete');
            $( "#selectedServersRemove").attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
            $("#selectedServersRemove").attr('style', 'width: 40px');
            $( "#selectedServersRemoveCancel").hide();

            $.ajax({
                url : "/v1/servers/" + deleteArray,
                type: "DELETE",
                success: function(data, textStatus, jqXHR)
                {
                    window.location = 'servers'
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log('error');
                }
            });
        }
    }
};

function cancelRemoveSelectedServers() {
    $("#selectedServersRemove").html('');
    $("#selectedServersRemove").attr('value', 'delete');
    $("#selectedServersRemove").attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
    $("#selectedServersRemove").attr('style', 'width: 40px');
    $("#selectedServersRemoveCancel").hide();//attr('display', 'none');
}


function saveEditServer(id){
    var formData = {
        server_name_text:           $('#server_name_text').val(),
        server_type_select:         $('#server_type_select').children(":selected").attr('id'),
        server_host_text:           $('#server_host_text').val(),
        db_port_text:               $('#db_port_text').val(),
        db_username_text:           $('#db_username_text').val(),
        db_password_text:           $('#db_password_text').val(),
        db_driver_text:             $('#db_driver_text').val(),
        db_default_db_name_text:    $('#db_default_db_name_text').val(),
        web_port_text:              $('#web_port_text').val(),
        web_scheme_text:            $('#web_scheme_text').val(),
        web_username_text:          $('#web_username_text').val(),
        web_password_text:          $('#web_password_text').val(),
        app_port_text:              $('#app_port_text').val(),
        app_scheme_text:            $('#app_scheme_text').val(),
        app_username_text:          $('#app_username_text').val(),
        app_password_text:          $('#app_password_text').val(),
        app_accesstoken_text:       $('#app_accesstoken_text').val()
    };


    $.ajax({
        url : "/v1/servers/" + id,
        type: "PUT",
        data : formData,
        success: function(data, textStatus, jqXHR)
        {
            if(data === 'OK'){
                window.location = '/v1/servers';
            }
            else
                console.log(data);
            //alert('Error: Email already exists');
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            //console.log(textStatus);
        }
    });

}



function saveCreateServer(){
    var formData = {
        server_name_text:           $('#server_name_text').val(),
        server_type_select:         $('#server_type_select').children(":selected").attr('id'),
        server_host_text:           $('#server_host_text').val(),
        db_port_text:               $('#db_port_text').val(),
        db_username_text:           $('#db_username_text').val(),
        db_password_text:           $('#db_password_text').val(),
        db_default_db_name_text:    $('#db_default_db_name_text').val(),
        //web_host_text:              $('#web_host_text').val(),
        web_port_text:              $('#web_port_text').val(),
        web_scheme_text:            $('#web_scheme_text').val(),
        web_username_text:          $('#web_username_text').val(),
        web_password_text:          $('#web_password_text').val(),
        //app_host_text:              $('#app_host_text').val(),
        app_port_text:              $('#app_port_text').val(),
        app_scheme_text:            $('#app_scheme_text').val(),
        app_username_text:          $('#app_username_text').val(),
        app_password_text:          $('#app_password_text').val(),
        app_accesstoken_text:       $('#app_accesstoken_text').val()



    };

    $.ajax({
        url : "/v1/servers",
        type: "POST",
        data : formData,
        success: function(data, textStatus, jqXHR)
        {
            if(data === 'OK'){
                window.location = '/v1/servers';
            }
            else
                console.log(data);
            //alert('Error: Email already exists');
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            console.log(textStatus);
        }
    });
}

 */


function cancelCreateServer(){

    window.location = '/v1/servers';
}

function cancelEditServer(){

    window.location = '/v1/servers';
}

