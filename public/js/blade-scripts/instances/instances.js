jQuery(function($) {
    $('#instanceTable').show();
});

var table = $('#instanceTable').DataTable({
    "dom":            '<"toolbar">', "aoColumnDefs": [{
        "targets": [0], "visible": false
    }], "bStateSave": true, "fnStateSave": function(oSettings, oData) {
        localStorage.setItem('Instances_' + window.location.pathname, JSON.stringify(oData));
    }, "fnStateLoad": function(oSettings) {
        var data = localStorage.getItem('Instances_' + window.location.pathname);
        return JSON.parse(data);
    }
});

var info = table.page.info();

$("div.toolbar").html('');

if (!$('#tableInfo').html().length) {
    $('#tableInfo').html('Showing Instances ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal);
}

$('#_next').on('click', function() {
    table.page('next').draw(false);

    if ((table.page.info().page + 1) === table.page.info().pages) {
        $('#_next').prop('disabled', true);
    }

    if (table.page.info().page > 0) {
        $('#_prev').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
});

$('#_prev').on('click', function() {
    table.page('previous').draw(false);

    if (table.page.info().page === 0) {
        $('#_prev').prop('disabled', true);
    }

    if ((table.page.info().page + 1) === table.page.info().pages) {
        $('#_next').prop('disabled', true);
    }

    if (table.page.info().pages > 1) {
        $('#_next').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));
    $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
});

function selectPage(page) {
    table.page(page).draw(false);
    $('#currentPage').html('Page ' + (page + 1));

    if (page === 0) {
        $('#_prev').prop('disabled', true);
    }

    if ((page + 1) < table.page.info().pages) {
        $('#_next').prop('disabled', false);
    }

    if (page > 0) {
        $('#_prev').prop('disabled', false);
    }

    if ((page + 1) === table.page.info().pages) {
        $('#_next').prop('disabled', true);
    }

    $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsTotal);
}

$(document).ready(function() {
    if (info) {
        for (var i = 0; i < info.pages; i++) {
            $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
        }

        if (info.pages > 1) {
            $('#_next').prop('disabled', false);
        }

        $('#instanceSearch').on('keyup click', function() {
            filterGlobal();
        });

        updatePageDropdown();
        selectPage(info.page);
        $('#instanceSearch').val(table.search());
    }

    $('#_prev').prop('disabled', true);
});

function filterGlobal() {
    $('#instanceTable').DataTable().search($('#instanceSearch').val()).draw();

    updatePageDropdown();
    setTableInfo();
}

function removeInstances(id, name) {
    /*
     var r = confirm('Are you sure you want to delete ' + name + '?');

     if (r == true) {
     $( "#instance_" + id ).submit();
     }
     */
};

function deleteSelectedInstances() {
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

function cancelEditInstance() {

    window.location = '/v1/instances';
}

function updatePageDropdown() {

    $('#tablePages').empty();

    for (var i = 0; i < table.page.info().pages; i++) {
        $('#currentPage').text('Page 1');
        $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
    }

    if (table.page.info().page === 0) {
        $('#_prev').prop('disabled', true);
    }

    if ((table.page.info().page + 1) < table.page.info().pages) {
        $('#_next').prop('disabled', false);
    }

    if (table.page.info().page > 0) {
        $('#_prev').prop('disabled', false);
    }

    if ((table.page.info().page + 1) === table.page.info().pages) {
        $('#_next').prop('disabled', true);
    }
}

function setTableInfo() {
    if (table.page.info().recordsDisplay === 0) {
        $('#tableInfo').html('Showing Instances 0 to 0 of 0');
    } else {
        $('#tableInfo').html('Showing Instances ' + (table.page.info().start + 1) + ' to ' + table.page.info().end + ' of ' + table.page.info().recordsDisplay);
    }
}

function resetCounter(id, name) {
    if (confirm('Reset all limit counters for instance "' + name + '" ?')) {
        $('#reset_counter_' + id).submit();
        return true;
    } else {
        return false;
    }
}

function deleteInstance(id, name) {
    if (confirm('Really deprovision instance "' + name + '"?')) {
        return $('#reset_counter_' + id).attr('action', '/v1/instance/delete/' + id).submit();
    }

    return false;
}

$('#refresh').click(function () {
    table.state.clear();
    localStorage.removeItem('Instances_' + window.location.pathname);
    window.location.reload();
});
