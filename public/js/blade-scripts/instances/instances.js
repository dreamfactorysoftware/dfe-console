var table;
var info;
var instSearch;

$(function(){
    table = $('#instanceTable').DataTable({
        "dom": '<"toolbar">ti',
        "columns": [
            {
                "name": "instance_id_text",
                "data": null,
                "render": function(data){
                    var $input = $('<input/>').attr('type', 'hidden').prop('id', 'instance_id').val(data.id);
                    var $link  = $('<a/>')
                        .addClass('instance-link')
                        .attr('href', protocol + '://' + data.instance_id_text + '.' + data.default_domain)
                        .attr('target', '_blank')
                        .text(data.instance_id_text)
                        .prop('outerHTML');
                    return $input.prop('outerHTML') + $link;
                }
            },
            {
                "name": "email_addr_text",
                "data": "email_addr_text"
            },
            {
                "name": "cluster",
                "data": "cluster"
            },
            {
                "name": "create_date",
                "data": "create_date"
            },
            {
                "class": "details-control",
                "orderable":false,
                "data": null,
                "defaultContent": "",
                "render": function (data) {
                    var $template = $('.frm_template form').clone();
                    $template.attr('id', 'reset_counter_'+ data.id);
                    $template.find('input#instance_id').val(data.id);
                    $template.find('input#instance_id_text').val(data.instance_id_text);
                    return $template.prop('outerHTML');
                }
            }

        ],
        "order": [[0, 'asc']],
        "processing" : true,
        "serverSide" : true,
        "ajax": {
            "url": "instances/get_instances"
        },
        "pageLength": 25,
        "infoCallback": function( settings, start, end, max, total, pre ) {
            return "Showing " + start + " to " + end +" of " + total.toLocaleString() + " Instances";
        },
        language: {
            emptyTable: 'No instances found.'
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

    $('#instanceSearch').on( 'keyup', function () {
        $('#searchclear').show();
            window.clearTimeout(instSearch);
        if($('#instanceSearch').val().length >= 3){
            instSearch = setTimeout(function(){
                table.search($('#instanceSearch').val()).ajax.reload();
            }, 600);
        } else if($('#instanceSearch').val().length == 0){
            table.search('').ajax.reload();
            $('#searchclear').hide();
        }

    });

    $('#searchclear').on('click', function(){
        $('#instanceSearch').val('');
        $('#searchclear').hide();
        table.search('').ajax.reload();
    });

    $('#_next').on( 'click', function () {
        _nextPage();
    } );

    $('#_prev').on( 'click', function () {
        _prevPage();
    });

    $('#instanceTable').on('click', '.reset_counters', function(){
        var inst_id = $(this).closest('form').find('#instance_id').val();
        var inst_name = $(this).closest('form').find('#instance_id_text').val();
        resetCounter(inst_id, inst_name);
    });

    $('#instanceTable').on('click', '.delete_instance', function(){
        var inst_id = $(this).closest('form').find('#instance_id').val();
        var inst_name = $(this).closest('form').find('#instance_id_text').val();
        deleteInstance(inst_id, inst_name);
    });

    $('#refresh').click(function(){
        $('#searchclear').trigger('click');
        table.order([[0, 'asc']]);
        table.ajax.reload();
    });
}); //end ready()

function add_waiting(){
    $('#instanceTable tbody').empty().append(
        '<tr><td colspan="6" style="text-align: center;"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></td></tr>'
    );
}

function cancelEditInstance() {
    window.location = '/v1/instances';
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

