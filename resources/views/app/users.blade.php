@include('layouts.partials.topmenu',array('pageName' => 'Users', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')
    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/users">Manage</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/users/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
                    <h4>Manage Users</h4>
                </div>
            </div>
        </div>

                <!-- Tool Bar -->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="well well-sm">
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
                                <button type="button" id="selectedUsersRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected users" value="delete" onclick="confirmRemoveSelectedUsers()" style="width: 40px"></button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm" id="selectedUserRemoveCancel" onclick="cancelRemoveSelectedUsers()" value="delete" style="display: none">Cancel</button>
                            </div>
                            <!--div style="clear: both"></div-->
                            <div class="btn-group">

                                    <input id="userSearch" class="form-control input-sm" value="" type="text" placeholder="Search Users...">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="">
                    <div class="row">
                        <div class="col-xs-12">
                            <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive table-bordered table-striped table-hover table-condensed" id="userTable">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th style="text-align: center; vertical-align: middle;"></th>
                                    <th class="" >
                                        Name
                                    </th>
                                    <th class="" >
                                        Display Name
                                    </th>
                                    <th class="" style="">
                                        Email
                                    </th>
                                    <th class="" style="text-align: center; vertical-align: middle;">
                                        Role
                                    </th>
                                    <th style="text-align: center; vertical-align: middle;">
                                        Status
                                    </th>
                                </tr>
                                </thead>

                    </table>
                    <span id="tableInfo"></span>
                    <br><br><br><br>

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
        }
    </style>

    <script>
        var str = eval({!!$users!!});

        var table = $('#userTable').dataTable( {
            "dom": '<"toolbar">',
            "aoColumns" : [
                { sClass: "col_center" },
                { sClass: "col_center" },
                { sClass: "col_left" },
                { sClass: "col_left" },
                { sClass: "col_left" },
                { sClass: "col_center" },
                { sClass: "col_center" }
            ],
            "aoColumnDefs": [
                {
                    "bSortable": false,
                    "aTargets": [1]
                },
                {
                    "targets": [0],
                    "visible": false
                }
            ],
            "columnDefs": [
                {
                    "targets": [ 0 ],
                    "visible": false
                }
            ],
            "data": str
        } );
    </script>

    <script type="text/javascript" src="../../../js/blade-scripts/users/users.js"></script>
@stop

