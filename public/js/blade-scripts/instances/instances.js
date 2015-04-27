$( document ).ready(function() {
    $("#instances tr").click(function (e) {
        var instance_id = $("#instances tr:eq('" + this.rowIndex + "')").find('input[type="hidden"]').val();
        var cellId = $('td', this).index(e.target);

        //if(cellId > 0)
        //    window.location = 'instances/' + instance_id + '/edit';

        e.stopPropagation();
    });
});

var table = $('#instanceTable').DataTable({
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

$('#instanceTable tbody').on( 'click', 'td', function () {

    var rowId = table.cell( this ).index().row - (10 * table.page.info().page);
    var cellId = table.cell( this ).index().column;

    var user_id = $("#instanceTable tr:eq('" + (rowId + 1) + "')").find('input[type="hidden"]').val();

    if(cellId > 0)
        window.location = 'instances/' + user_id + '/edit';


} );


var info = table.page.info();

$("div.toolbar").html('');

if($('#tableInfo').html() === '')
    $('#tableInfo').html('Showing Instances ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal);


$('#_next').on( 'click', function () {

    table.page( 'next' ).draw( false );

    if((table.page.info().page + 1) === table.page.info().pages){
        $('#_next').prop('disabled', true);
    }

    if(table.page.info().page > 0){
        $('#_prev').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
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
    $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
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

    $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
}

$( document ).ready(function() {

    for(var i = 0; i < info.pages; i++){
        $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
    }

    if(info.pages > 1)
        $('#_next').prop('disabled', false);

    $('#_prev').prop('disabled', true);
});

function removeInstances(id, name) {
    /*
     var r = confirm('Are you sure you want to delete ' + name + '?');

     if (r == true) {
     $( "#instance_" + id ).submit();
     }
     */
};


function deleteSelectedInstances () {
    /*
     var deleteArray = [];

     $('input[type=checkbox]').each(function () {

     if(this.checked)
     deleteArray.push(this.value);
     });

     if(deleteArray.length) {

     var r = confirm('Are you sure you want to delete selected instance(s)?');
     if (r == true) {

     $.ajax({
     url : "/{{$prefix}}/instances/" + deleteArray,
     type: "DELETE",
     success: function(data, textStatus, jqXHR)
     {
     window.location = 'instances'
     },
     error: function (jqXHR, textStatus, errorThrown)
     {
     console.log('error');
     }
     });
     }
     }
     else
     alert('No instances selected');
     */
};