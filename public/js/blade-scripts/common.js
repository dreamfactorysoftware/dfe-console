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

}
function updatePageDropdown(){

    $('#tablePages').empty();
    var pages = table.page.info().pages;
    $('#currentPage').text('Page '+ (parseInt(table.page.info().page)+1));

    for(var i = 0; i < pages; i++){
        var $li = $('<li/>');
        if(table.page.info().page == i){
            $li.addClass('active');
        }
        $li.html('<a href="javascript:selectPage(' + i + ');">' + (i + 1) + '</a>');
        $('#tablePages').append($li);
    }

    if(table.page.info().page === 0)
        $('#_prev').prop('disabled', true);

    if((table.page.info().page + 1) < table.page.info().pages)
        $('#_next').prop('disabled', false);

    if(table.page.info().page > 0)
        $('#_prev').prop('disabled', false);

    if((table.page.info().page + 1) === table.page.info().pages)
        $('#_next').prop('disabled', true);
}

function _nextPage(){
    table.page( 'next' ).draw( false );

    if((table.page.info().page + 1) === table.page.info().pages){
        $('#_next').prop('disabled', true);
    }

    if(table.page.info().page > 0){
        $('#_prev').prop('disabled', false);
    }

    $('#currentPage').html('Page ' + (table.page.info().page + 1));

}

function _prevPage(){
    table.page( 'previous' ).draw( false );

    if(table.page.info().page === 0)
        $('#_prev').prop('disabled', true);

    if((table.page.info().page + 1) === table.page.info().pages)
        $('#_next').prop('disabled', true);

    if(table.page.info().pages > 1)
        $('#_next').prop('disabled', false);

    $('#currentPage').html('Page ' + (table.page.info().page + 1));

}

function _gotoPage(page){
    selectPage(page);
}