/*
function confirmRemoveCluster(id) {
    return;
    var state = $("#cluster_button_" + id).attr('value');

    if (state === 'delete') {
        $("#cluster_button_" + id).html('Confirm!');
        $("#cluster_button_" + id).attr('value', 'confirm');
        $("#cluster_button_" + id).attr('class', 'btn btn-danger btn-xs');
        $("#cluster_button_" + id).attr('style', 'width: 75px');
        $("#cluster_button_cancel_" + id).show();
        $("#cluster_checkbox_" + id).hide();
        $("#actionColumn").attr('width', '200px');
    }

    if (state === 'confirm') {
        $.ajax({
            url: "/{{$prefix}}/clusters/" + id,
            type: "DELETE",
            success: function (data, textStatus, jqXHR) {
                window.location = 'clusters'
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('error');
            }
        });
    }
};


function cancelRemoveCluster(id) {

    $( "#cluster_button_" + id).html('');
    $( "#cluster_button_" + id).attr('value', 'delete');
    $( "#cluster_button_" + id).attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
    $("#cluster_button_" + id).attr('style', 'width: 25px');
    $( "#cluster_button_cancel_" + id).hide();//attr('display', 'none');
    $("#cluster_checkbox_" + id).show();
    $("#actionColumn").removeAttr('width');

};

function cancelRemoveSelectedClusters() {
    $( "#selectedclustersRemove").html('');
    $( "#selectedclustersRemove").attr('value', 'delete');
    $( "#selectedclustersRemove").attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
    $("#selectedclustersRemove").attr('style', 'width: 40px');
    $( "#selectedclusterRemoveCancel").hide();//attr('display', 'none');
}


function confirmRemoveSelectedClusters () {
    return;
    var deleteArray = [];

    $('input[type=checkbox]').each(function () {

        if(this.checked)
            deleteArray.push(this.value);
    });

    if(deleteArray.length) {

        var state = $("#selectedclustersRemove").attr('value');

        if (state === 'delete') {
            $("#selectedclustersRemove").html('Confirm!');
            $("#selectedclustersRemove").attr('value', 'confirm');
            $("#selectedclustersRemove").attr('class', 'btn btn-danger btn-sm');
            $("#selectedclustersRemove").attr('style', 'width: 75px');
            $("#selectedclusterRemoveCancel").show();
        }

        if (state === 'confirm') {

            $( "#selectedclustersRemove").html('');
            $( "#selectedclustersRemove").attr('value', 'delete');
            $( "#selectedclustersRemove").attr('class', 'btn btn-default btn-sm fa fa-fw fa-trash');
            $("#selectedclustersRemove").attr('style', 'width: 40px');
            $( "#selectedclusterRemoveCancel").hide();

            $.ajax({
                url : "/{{$prefix}}/clusters/" + deleteArray,
                type: "DELETE",
                success: function(data, textStatus, jqXHR)
                {
                    window.location = 'clusters'
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



var table = $('#clusterTable').DataTable({
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

$('#clusterTable tbody').on( 'click', 'td', function () {

    var rowId = table.cell( this ).index().row - (10 * table.page.info().page);
    var cellId = table.cell( this ).index().column;

    var cluster_id = $("#clusterTable tr:eq('" + (rowId + 1) + "')").find('input[type="hidden"]').val();

    if(cellId > 1)
        window.location = 'clusters/' + cluster_id + '/edit';


} );


var info = table.page.info();

$("div.toolbar").html('');

if($('#tableInfo').html() === '')
    $('#tableInfo').html('Showing clusters ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal);


$('#_next').on( 'click', function () {

    table.page( 'next' ).draw( false );

    if((table.page.info().page + 1) === table.page.info().pages){
        $('#_next').prop('disabled', true);
    }

    if(table.page.info().page > 0){
        $('#_prev').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing clusters ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
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
    $('#tableInfo').html('Showing clusters ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
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

    $('#tableInfo').html('Showing clusters ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
}

function removeCluster(id, name) {
    if(confirm('Remove Cluster "' + name + '" ?')){
        $('#single_delete_' + id).submit();
        return true;
    }
    else
        return false;
}

$('#selectedClustersRemove').click(function(){

    var deleteArray = [];

    $('input[type=checkbox]').each(function () {

        if(this.checked)
            deleteArray.push(this.value);
    });

    if(!deleteArray.length){
        alert('No Cluster(s) Selected!');
        return true;
    }

    $('#_selected').val(deleteArray);

    if(confirm('Remove Selected Clusters?')){
        $('#multi_delete').submit();
        return true;
    }
    else
        return false;
});




$( document ).ready(function() {

    for(var i = 0; i < info.pages; i++){
        $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
    }

    if(info.pages > 1)
        $('#_next').prop('disabled', false);

    $('#_prev').prop('disabled', true);
});

