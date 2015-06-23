@include('layouts.partials.topmenu',array('pageName' => 'Servers', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <div class="col-md-2 df-sidebar-nav">
        <div>
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a href="/{{$prefix}}/servers">Manage</a>
                </li>
                <li>
                    <a href="/{{$prefix}}/servers/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-md-10">
        <div>
            <div>
                <div class="nav nav-pills dfe-section-header">
                    <h4>Manage Servers</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <form method="POST" action="/{{$prefix}}/servers/multi" id="multi_delete">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                <input name="_selected" id="_selected" type="hidden" value="">
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
                                <button type="button" id="selectedServersRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected servers" value="delete" style="width: 40px"></button>
                        </div>
                        <div class="btn-group">
                            <input id="serverSearch" class="form-control input-sm" value="" type="text" placeholder="Search Servers...">
                        </div>
                        <div class="btn-group pull-right">
                            <button type="button" id="refresh" class="btn btn-default btn-sm fa fa-fw fa-refresh" title="Reset sorting" value="" style="width: 40px"></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div>

            @if(Session::has('flash_message'))
                <div class="alert {{ Session::get('flash_type') }}">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ Session::get('flash_message') }}
                </div>
            @endif

            <div class="row">
                <div class="col-xs-12">
                    <table id="serverTable" class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-server">
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Host</th>
                                <th>Status</th>
                                <th>Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>

                        @foreach($servers as $key => $value)
                            <tr>
                                <td> </td>
                                <td id="actionColumn">
                                    <form method="POST" action="/{{$prefix}}/servers/{{$value->id}}" id="single_delete_{{ $value->id }}">
                                        <input type="hidden" id="server_id" value="{{ $value->id }}">
                                        <input name="_method" type="hidden" value="DELETE">
                                        <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                                        @if (array_key_exists('cluster_id', $value))
                                            <div class="tooltip-wrapper"  data-title="Server In Use - Delete Disabled">
                                                <input type="checkbox" disabled>&nbsp;&nbsp;
                                                <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled style="width: 25px" ></button>
                                            </div>
                                        @else
                                            <input type="checkbox" value="{{ $value->id }}" id="server_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" onclick="removeServer({{ $value->id }}, '{{ $value->server_id_text }}')" value="delete" style="width: 25px" ></button>
                                        @endif
                                    </form>
                                </td>

                                <td>{{ $value->server_id_text }}</td>

                                @if ( $value->server_type_id == "1" )
                                    <td><span class="label label-primary">DB</span></td>
                                @elseif ( $value->server_type_id == "2" )
                                    <td><span class="label label-success">WEB</span></td>
                                @elseif ( $value->server_type_id == "3" )
                                    <td><span class="label label-warning">APP</span></td>
                                @endif

                                <td>{{ $value->host_text }}</td>

                                <td>
                                @if ( array_key_exists( 'cluster_id', $value ) && ( $value->server_type_id == "1" ) )
                                    @if ( array_key_exists( 'multi-assign', $value->config_text ) )
                                        <span class="label label-primary">Assignable</span>
                                    @else
                                        <span class="label label-warning">Assigned</span>
                                    @endif
                                @elseif( array_key_exists( 'cluster_id', $value ) && ( $value->server_type_id != "1" ))
                                    <span class="label label-warning">Assigned</span>
                                @else
                                    <span class="label label-success">Not Assigned</span>
                                @endif
                                </td>
                                <td>{{ $value->lmod_date }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    <span id="tableInfo"></span>
                    <br><br><br><br>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../js/blade-scripts/servers/servers.js"></script>

    <style>
        .tooltip-wrapper {
            display: inline-block;
        }

    </style>
@stop

