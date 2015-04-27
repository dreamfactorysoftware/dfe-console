
@include('layouts.partials.topmenu',array('pageName' => 'Clusters', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <script type='text/javascript'>

        $( document ).ready(function() {
            //$('#')

        });


        function cancel(){
            window.location = '/{{$prefix}}/clusters';
        }



        function save(){

            var table_data = $('#serverTable').DataTable().rows().data();
            //var out = [];
            var out = '';

            for(var i = 0; i < table_data.length; i++){
                //out.push(table_data[i][0]);
                out += table_data[i][0] + ',';
            }

            out = out.replace(/(^,)|(,$)/g, "")

            var  formData = {

                cluster_name_text: $('#cluster_name_text').val(),
                cluster_subdomain_text: $('#cluster_subdomain_text').val(),
                cluster_instancecount_text: $('#cluster_instancecount_text').val(),
                cluster_assigned_servers: out

            };
            //console.log(formData);
            /**/
            $.ajax({
                url : "/{{$prefix}}/clusters/{{$cluster_id}}",
                type: "PUT",
                data : formData,
                success: function(data, textStatus, jqXHR)
                {
                    //data - response from server
                    window.location = '/{{$prefix}}/clusters';
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
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
                    <div class="ng-scope">
                        <div class="ng-scope">
                            <div class="col-md-2 df-sidebar-nav">
                                <df-sidebar-nav>
                                    <div class="">
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li class="active">
                                                <a class="" href="/{{$prefix}}/clusters">Manage</a>
                                            </li>
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/clusters/create">Create</a>
                                            </li>
                                        </ul>
                                        <div class="hidden-lg hidden-md" id="sidebar-open">
                                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-fw fa-bars"></i></button>
                                        </div>

                                    </div>
                                </df-sidebar-nav>
                            </div>
                            <div class="col-md-10 df-section df-section-3-round" df-fs-height="">
                                <df-manage-users class=""><div>
                                        <div class="">
                                            <df-section-header class="" data-title="'Manage Servers'">
                                                <div class="df-section-header df-section-all-round">
                                                    <h4 class="">Edit Cluster</h4>
                                                </div>
                                            </df-section-header>


                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <form class="" name="create-user">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input id="cluster_name_text" class="form-control" value="{{$cluster->cluster_id_text}}" type="name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Sub-Domain</label>
                                                            <input id="cluster_subdomain_text" class="form-control" value="{{$cluster->subdomain_text}}" type="subdomain">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Max number of instances</label>
                                                            <input id="cluster_instancecount_text" class="form-control" value="" type="instancecount">
                                                        </div>
                                                        </form>

                                                    </div>


                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <div class="col-xs-12">
                                                                <label>Assigned Servers</label>
                                                                <!--div class="well well-sm">
                                                                    <div class="btn-group btn-group pull-right">

                                                                    </div>
                                                                    <div class="btn-group btn-group">

                                                                        <button type="button" disabled="true" class="btn btn-default btn-sm fa fa-fw fa-backward" id="_prev" style="width: 40px"></button>

                                                                        <div class="btn-group">
                                                                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                                                                <span id="currentPage">Page 1</span> <span class="caret"></span>
                                                                            </button>
                                                                            <ul class="dropdown-menu" role="menu" id="tablePages">
                                                                            </ul>
                                                                        </div>

                                                                        <button type="button" disabled="true" class="btn btn-default btn-sm fa fa-fw fa-forward" id="_next" style="width: 40px"></button>
                                                                    </div>

                                                                    <div class="btn-group">

                                                                        <input id="userSearch" class="form-control input-sm" value="" type="text" placeholder="Search Servers...">

                                                                    </div>
                                                                    <div class="btn-group pull-right">
                                                                        <button type="button" id="selectedUsersRemove" class="btn btn-default btn-sm glyphicon glyphicon-plus" title="Assign Server" value="Assign" onclick="" style="width: 40px"></button>
                                                                    </div>
                                                                </div-->
                                                            </div>
                                                        </div>

                                                        <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive table-bordered table-striped table-hover table-condensed" id="serverTable">
                                                            <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th style="text-align: center; vertical-align: middle;"></th>
                                                                <th class="" style="text-align: left; vertical-align: middle;">
                                                                    Server Name
                                                                </th>
                                                                <th style="text-align: center; vertical-align: middle;">
                                                                    Type
                                                                </th>
                                                            </tr>
                                                            </thead>

                                                        </table>

                                                        <div class="col-md-12">


                                                            <form class="form-inline">
                                                                <label>Assign Server&nbsp;&nbsp;&nbsp;</label>
                                                                <select class="form-control" id="server_select">
                                                                    <option value="" disabled selected>Select Server...</option>
                                                                    @foreach($server_dropdown as $key => $value)
                                                                        <option id="{{$value[0]}}">[{{$value[3]}}] {{$value[2]}}</option>
                                                                    @endforeach
                                                                </select>&nbsp;&nbsp;
                                                                <button type="button" class="btn btn-primary" id="addserver">
                                                                    Assign
                                                                </button>

                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-xs-12">
                                                        <hr>
                                                        <div class="form-group">
                                                            <div class="">

                                                                <button type="button" class="btn btn-primary" onclick="javascript:save();">
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
                                            <!--/form-->
                                        </div>


                                        </df-user-details>
                                    </div>

                                </df-manage-users>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .col_left{
            text-align: left;
        }
        .col_center{
            text-align: center;
            vertical-align: middle;
            position: relative;
        }
    </style>
    <script>

        var str = eval({!!$servers!!});
        
        var t = $('#serverTable').dataTable( {
            "aoColumns" : [
                { sClass: "col_center" },
                { sClass: "col_center" },
                { sClass: "col_left" },
                { sClass: "col_center" }
            ],
            "columnDefs": [
                {
                    "targets": [ 0 ],
                    "visible": false
                }
            ],
            "paging":   false,
            "ordering": false,
            "info":     false,
            "bFilter":     false,
            "data": str
        } );

        $('#addserver').on('click', function(){

            var id = $('#server_select').find('option:selected').attr('id');

            if(id !== undefined){
                var servers = eval({!!$server_dropdown_str!!});
                $("#server_select option[id=" + id + "]").remove();

                var t = $('#serverTable').DataTable();
                t.row.add( [
                    servers[id][1],
                    servers[id][5],
                    servers[id][2],
                    servers[id][4]
                ] ).draw();
            }
        });


        function removeServer(id){

            var table = $('#serverTable').DataTable();

            var indexes = table.rows().eq( 0 ).filter( function (rowIdx) {
                return table.cell( rowIdx, 0 ).data() == id ? true : false;
            } );

            //console.log(id + ' / ' + indexes[0]);
            //console.log(indexes[0]);
            //console.log(table.row(indexes[0]).data());

            var deleted_row = table.row(indexes[0]).data();

            var type = '';

            if(deleted_row[3].indexOf('DB') > -1)
                type = 'DB';

            if(deleted_row[3].indexOf('WEB') > -1)
                type = 'WEB';

            if(deleted_row[3].indexOf('APP') > -1)
                type = 'APP';

            //$('#server_select').append('<option id=' + deleted_row[0] + '>[' + type + '] ' + deleted_row[2] + '</option>');

            table.row(indexes[0]).remove().draw( false );
        }

    </script>

@stop


