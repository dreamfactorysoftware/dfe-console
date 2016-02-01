var resourceName  = 'instance',
    table,
    info,
    pageCount,
    $_instanceTable,
    $_tableInfo,
    $_tablePages,
    $_instanceSearch,
    $_next        = $('#_next'),
    $_prev        = $('#_prev'),
    $_currentPage = $('#currentPage');

/**
 * @param start
 * @param end
 * @param count
 */
var setTableInfo = function(start, end, count) {
    start = start || info.start + 1;
    end = end || info.end;
    count = count || info.recordsTotal;

    if (!count) {
        return $_tableInfo.html('No instances found');
    }

    return $_tableInfo.html('Showing ' + resourceName + 's ' + start + ' to ' + end + ' of ' + count);
};

/**
 * @param page
 */
function selectPage(page) {
    table.page(page).draw(false);
    var nextPage = page + 1;

    $_currentPage.html('Page ' + nextPage);
    $_prev.prop('disabled', (0 == page || 1 == pageCount ));
    $_next.prop('disabled', (0 == page || nextPage > pageCount));

    setTableInfo();
}

var filterGlobal = function() {
    $('#instanceTable').DataTable().search($('#instanceSearch').val()).draw();
    updatePageDropdown();
    setTableInfo();
};

/**
 * @param id
 * @param name
 */
var removeInstances = function(id, name) {
    /*
     var r = confirm('Are you sure you want to delete ' + name + '?');

     if (r == true) {
     $( "#instance_" + id ).submit();
     }
     */
};

var deleteSelectedInstances = function() {
};

var cancelEditInstance = function() {
    window.location = '/v1/instances';
};

/** */
var updatePageDropdown = function() {
    $_tablePages.empty();

    for (var i = 0; i < pageCount; i++) {
        $_currentPage.text('Page 1');
        $_tablePages.append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
    }

    if (table.page.info().page === 0) {
        $_prev.prop('disabled', true);
    }

    if ((table.page.info().page + 1) < pageCount) {
        $_next.prop('disabled', false);
    }

    if (table.page.info().page > 0) {
        $_prev.prop('disabled', false);
    }

    if ((table.page.info().page + 1) === pageCount) {
        $_next.prop('disabled', true);
    }
};

/**
 * @param id
 * @param name
 * @returns {boolean}
 */
var resetCounter = function(id, name) {
    if (confirm('Reset all limit counters for instance "' + name + '" ?')) {
        $('#reset_counter_' + id).submit();
        return true;
    }

    return false;
};

jQuery(function($) {
    $_instanceTable = $('#instanceTable');
    $_instanceSearch = $('#instanceSearch');
    $_tableInfo = $('#tableInfo');

    table = $_instanceTable.DataTable({
        dom:          '<"toolbar">',
        aoColumnDefs: [{targets: [0], visible: false}],
        bStateSave:   true,
        fnStateSave:  function(oSettings, oData) {
            localStorage.setItem('Instances_' + window.location.pathname, JSON.stringify(oData));
        },
        fnStateLoad:  function(oSettings) {
            var data = localStorage.getItem('Instances_' + window.location.pathname);
            return JSON.parse(data);
        }
    });

    info = table.page.info();
    pageCount = info.pages;

    $_instanceTable.show();

    if (info) {
        for (var _i = 0; _i < pageCount; _i++) {
            $_tablePages.append('<li><a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a></li>')
        }

        if (info.pages > 1) {
            $_next.prop('disabled', false);
        }

        $_instanceSearch.on('keyup click', function() {
            filterGlobal();
        });

        updatePageDropdown();
        selectPage(info.page);

        $_instanceSearch.val(table.search());
    }

    $('div.toolbar').html('');

    setTableInfo(info.start + 1, info.end, info.recordsTotal);

    $('#refresh').click(function() {
        table.state.clear();
        localStorage.removeItem('Instances_' + window.location.pathname);
        window.location.reload();
    });

    var changePage = function(which) {
        var other = which == 'next' ? 'prev' : which, currentPage = info.page;
        table.page(which).draw(false);
        $('#_' + which).prop('disabled', ((currentPage + 1) == pageCount));
        $('#_' + other).prop('disabled', (currentPage > 1));

        table.page.info().page && $('#_' + alt).prop('disabled', false);

        $_currentPage.html('Page ' + (table.page.info().page + 1));
        setTableInfo(table.page.info().start + 1, table.page.info().end, table.page.info().recordsTotal);
    };

    $_next.on('click', function() {
        selectPage(info.page > 0 && info.page + 1 || 0);
    });

    $_prev.on('click', function() {
        selectPage(info.page > 0 && info.page - 1 || 0);
        table.page('previous').draw(false);
        !table.page.info().page && $_prev.prop('disabled', true);

        if (pageCount == (table.page.info().page + 1)) {
            $_next.prop('disabled', true);
        }

        if (pageCount > 1) {
            $_next.prop('disabled', false);
        }

        $_currentPage.html('Page ' + (table.page.info().page + 1));
        setTableInfo(table.page.info().start + 1, table.page.info().end, table.page.info().recordsTotal);
    });

    $_prev.prop('disabled', true);
});

