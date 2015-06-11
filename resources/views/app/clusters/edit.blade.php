@include('layouts.partials.topmenu',array('pageName' => 'Clusters', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <script type='text/javascript'>

    $(document).ready(function() {
        updateServerList();

    });

    function cancel() {
        window.location = '/{{$prefix}}/clusters';
    }

    function updateServerList() {
        var table_data = $('#serverTable').DataTable().rows().data();
        //var out = [];
        var out = '';

        for (var i = 0; i < table_data.length; i++) {
            //out.push(table_data[i][0]);
            out += table_data[i][0] + ',';
        }

        out = out.replace(/(^,)|(,$)/g, "")

        $('#_server_list').val(out);
    }

    function save() {

        var table_data = $('#serverTable').DataTable().rows().data();
        //var out = [];
        var out = '';

        for (var i = 0; i < table_data.length; i++) {
            //out.push(table_data[i][0]);
            out += table_data[i][0] + ',';
        }

        out = out.replace(/(^,)|(,$)/g, "")

        var formData = {

            cluster_name_text:          $('#cluster_id_text').val(),
            cluster_subdomain_text:     $('#cluster_subdomain_text').val(),
            cluster_instancecount_text: $('#cluster_instancecount_text').val(),
            cluster_assigned_servers:   out

        };
        console.log(formData);
        /**/
        $.ajax({
            url:     "/{{$prefix}}/clusters/{{$cluster_id}}",
            type:    "PUT",
            data:    formData,
            success: function(data, textStatus, jqXHR) {
                //data - response from server
                //window.location = '/{{$prefix}}/clusters';
            },
            error:   function(jqXHR, textStatus, errorThrown) {
                //console.log(textStatus);
            }
        });

    }

    /*
     // $(document).ready(function() {
     $("#system_admin").click(function () {
     if ($('#system_admin').is(':checked')) {
     $('#instance_manage').removeAttr("disabled");
     $('#instance_policy').removeAttr("disabled");

     } else {
     $('#instance_manage').attr("disabled", true);
     $('#instance_policy').attr("disabled", true);
     }
     });

     $('#set_password').click(function () {
     if ($('#set_password').is(':checked')) {
     $('#set_password_form').show();

     } else {
     $('#set_password_form').hide();

     }
     });
     // });
     */
    </script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div>
                        <div>
                            <div class="col-md-2">
                                <div>
                                    <div>
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li class="active">
                                                <a href="/{{$prefix}}/clusters">Manage</a>
                                            </li>
                                            <li>
                                                <a href="/{{$prefix}}/clusters/create">Create</a>
                                            </li>
                                        </ul>
                                        <div class="hidden-lg hidden-md" id="sidebar-open">
                                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-fw fa-bars"></i></button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10">

                                <div>
                                    <div>
                                        <div>
                                            <div class="nav nav-pills dfe-section-header">
                                                <h4>Edit Cluster</h4>
                                            </div>
                                        </div>

                                        <form method="POST" action="/{{$prefix}}/clusters/{{$cluster_id}}">
                                            <input name="_method" type="hidden" value="PUT">
                                            <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                                            <input id="_server_list" name="_server_list" type="hidden" value="">
                                            <div class="row">

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="control-label" for="cluster_id_text">Name</label>
                                                        <input id="cluster_id_text"
                                                            name="cluster_id_text"
                                                            class="form-control"
                                                            value="{{$cluster->cluster_id_text}}"
                                                            type="text"
                                                            required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label" for="subdomain_text">Fixed DNS Subdomain</label>
                                                        <input id="subdomain_text"
                                                            name="subdomain_text"
                                                            class="form-control"
                                                            value="{{$cluster->subdomain_text}}"
                                                            type="text" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label" for="max_instances_nbr">Maximum Allowed Instances</label>
                                                        <input
                                                            id="max_instances_nbr"
                                                            name="max_instances_nbr"
                                                            class="form-control"
                                                            value="{{$cluster->max_instances_nbr}}"
                                                            type="number"
                                                            min="0"
                                                            max="1000">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <div class="col-xs-12">
                                                            <label>Assigned Servers</label>
                                                        </div>
                                                    </div>

                                                    <table cellpadding="0"
                                                        cellspacing="0"
                                                        border="0"
                                                        class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-cluster-servers"
                                                        id="serverTable">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th></th>
                                                                <th>Server Name</th>
                                                                <th>Type</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                    <div class="col-md-12">
                                                        <div class="form-inline">
                                                            <label>Assign Server&nbsp;&nbsp;&nbsp;</label>
                                                            <select class="form-control" id="server_select">
                                                                <option value="" disabled selected>Select Server...</option>
                                                                @foreach($server_dropdown as $key => $value)
                                                                    <option id="{{$value[1]}}">[{{$value[3]}}] {{$value[2]}}</option>
                                                                @endforeach
                                                            </select>&nbsp;&nbsp;
                                                            <button type="button" class="btn btn-primary" id="addserver">
                                                                Assign
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <hr>
                                                    <div class="form-group">
                                                        <div>
                                                            <button type="submit" class="btn btn-primary">
                                                                Update
                                                            </button>
                                                            &nbsp;&nbsp;
                                                            <button type="button" class="btn btn-default" onclick="cancel()">
                                                                Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .col_left {
            text-align: left;
        }

        .col_center {
            text-align:     center;
            vertical-align: middle;
            position:       relative;
        }
    </style>
    <script>

    var str = eval({!!$servers!!})
    ;

    var t = $('#serverTable').dataTable({
        "aoColumns":  [{sClass: "col_center"}, {sClass: "col_center"}, {sClass: "col_left"}, {sClass: "col_center"}],
        "columnDefs": [{
            "targets": [0],
            "visible": false
        }],
        "paging":     false,
        "ordering":   false,
        "info":       false,
        "bFilter":    false,
        "data":       str
    });

    $('#serverTable').on('draw.dt', function() {
        updateServerList();
    });

    $('#serverTable').on('init.dt', function() {
        updateServerList();
    });

    $('#addserver').on('click', function() {

        var id = $('#server_select').find('option:selected').attr('id');

        if (id !== undefined) {

            var servers = eval({!!$server_dropdown_all!!});

        var this_id = 0;

        for (var i = 0; i < servers.length; i++) {
            if (servers[i][1] == id) {
                this_id = i;
            }
        }

        $("#server_select option[id=" + id + "]").remove();

        var t = $('#serverTable').DataTable();
        t.row.add([servers[this_id][1], servers[this_id][5], servers[this_id][2], servers[this_id][4]]).draw();
    }
    })
    ;

    function removeServer(id) {

        var table = $('#serverTable').DataTable();

        var indexes = table.rows().eq(0).filter(function(rowIdx) {
            return table.cell(rowIdx, 0).data() == id ? true : false;
        });

        var deleted_row = table.row(indexes[0]).data();

        var type = '';

        if (deleted_row[3].indexOf('DB') > -1) {
            type = 'DB';
        }

        if (deleted_row[3].indexOf('WEB') > -1) {
            type = 'WEB';
        }

        if (deleted_row[3].indexOf('APP') > -1) {
            type = 'APP';
        }

        $('#server_select').append('<option id=' + deleted_row[0] + '>[' + type + '] ' + deleted_row[2] + '</option>');

        table.row(indexes[0]).remove().draw(false);
    }


    </script>

@stop


