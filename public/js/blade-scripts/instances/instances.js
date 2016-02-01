var table = $('#instanceTable').DataTable({
    "dom":            '<"toolbar">', "aoColumnDefs": [{
        "targets": [0], "visible": false
    }], "bStateSave": true, "fnStateSave": function(oSettings, oData) {
        localStorage.setItem('Instances_' + window.location.pathname, JSON.stringify(oData));
    }, "fnStateLoad": function(oSettings) {
        var data = localStorage.getItem('Instances_' + window.location.pathname);
        return JSON.parse(data);
    },
    language:         {
        emptyTable: 'No instances found'
    }
});

var info = table.page.info();

function selectPage(page) {
    table.page(page).draw(false);
    $('#currentPage').html('Page ' + (page + 1));

    if (page == 0) {
        $('#_prev').prop('disabled', true);
    }

    if ((page + 1) < info.pages) {
        $('#_next').prop('disabled', false);
    }

    if (page > 0) {
        $('#_prev').prop('disabled', false);
    }

    if ((page + 1) == info.pages) {
        $('#_next').prop('disabled', true);
    }

    setTableInfo();
}

function filterGlobal() {
    $('#instanceTable').DataTable().search($('#instanceSearch').val()).draw();

    setTableInfo();
}

function cancelEditInstance() {
    window.location = '/v1/instances';
}

function updatePageDropdown() {
    var $_pages = $('#tablePages');
    $_pages.empty();

    for (var i = 0; i < info.pages; i++) {
        $_pages.append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
    }

    if (info.page == 0) {
        $('#_prev').prop('disabled', true);
    }

    if ((info.page + 1) < info.pages) {
        $('#_next').prop('disabled', false);
    }

    if (info.page > 0) {
        $('#_prev').prop('disabled', false);
    }

    if ((info.page + 1) == info.pages) {
        $('#_next').prop('disabled', true);
    }

    setTableInfo();
}

function setTableInfo() {
    $('#currentPage').html('Page ' + (info.page + 1));
    $('#tableInfo').html(
        info.recordsDisplay < 2
            ? ''
            : 'Showing ' + info.end + ' of ' + info.recordsTotal + ' instances'
    );
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
        return $('#reset_counter_' + id).attr('action', '/v1/instance/' + id + '/delete').submit();
    }

    return false;
}

jQuery(function($) {
    $('#instanceTable').show();

    if (info) {
        for (var i = 0; i < info.pages; i++) {
            $('#tablePages').append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
        }

        $('#instanceSearch').on('keyup click', function() {
            filterGlobal();
        }).val(table.search());

        updatePageDropdown();
        selectPage(info.page);
    }

    //$('div.toolbar').empty();

    $('#_next').on('click', function() {
        table.page('next').draw(false);

        if ((info.page + 1) == info.pages) {
            $('#_next').prop('disabled', true);
        }

        if (info.page > 0) {
            $('#_prev').prop('disabled', false);
        }

        setTableInfo();
    }).prop('disabled', (info.pages <= 1));

    $('#_prev').on('click', function() {
        table.page('previous').draw(false);

        $('#_prev').prop('disabled', (0 == info.page));

        if ((info.page + 1) == info.pages) {
            $('#_next').prop('disabled', true);
        }

        if (info.pages > 1) {
            $('#_next').prop('disabled', false);
        }

        setTableInfo();
    }).prop('disabled', (0 == info.page));

    $('#refresh').click(function() {
        table.state.clear();
        localStorage.removeItem('Instances_' + window.location.pathname);
        window.location.reload();
    });

    setTableInfo();
});
