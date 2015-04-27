@include('layouts.partials.topmenu',array('pageName' => 'Servers', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/servers">Manage</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/servers/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
                    <h4 class="">Manage Servers</h4>
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
                        <button type="button" id="selectedServersRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected servers" value="delete" onclick="confirmRemoveSelectedServers()" style="width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm" id="selectedServersRemoveCancel" onclick="cancelRemoveSelectedServers()" value="delete" style="display: none">Cancel</button>
                    </div>
                    <div style="clear: both"></div>
                </div>
            </div>
        </div>

        <div class="">
            <div class="row">
                <div class="col-xs-12">
                    <table id="serverTable" class="table table-responsive table-bordered table-striped table-hover table-condensed">
                        <thead>
                        <tr>
                            <th></th>
                            <th style="text-align: center; vertical-align: middle;"> </th>
                            <th class="" >
                                Name
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Type
                            </th>
                            <th class="" style="">
                                Host
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Last Modified
                            </th>
                        </tr>

                        </thead>
                        <tbody>

                        @foreach($servers as $key => $value)
                            <tr>
                                <td>

                                </td>

                                <td style="text-align: center; vertical-align: middle;" id="actionColumn">
                                    <div>
                                        <input type="hidden" id="server_id" value="{{ $value->id }}">


                                            <input type="checkbox" value="{{ $value->id }}" id="server_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="server_button_{{$value->id}}" onclick="confirmRemoveServer({{$value->id}})" value="delete" style="width: 25px"></button>&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-xs" id="server_button_cancel_{{$value->id}}" onclick="cancelRemoveServer({{$value->id}})" value="delete" style="display: none">Cancel</button>




                                    </div>
                                </td>

                                <!--td style="text-align: center; vertical-align: middle; width: 80px;">
                                    <div class="btn-group">
                                        <input type="hidden" id="server_id" value="{{ $value->id }}">
                                        <input type="checkbox" value="{{ $value->id }}">&nbsp;&nbsp;
                                        {!! Form::open(array('url' => 'v1/servers/' . $value->id, 'class' => 'pull-right', 'id' => 'server_' . $value->id)) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {!! Form::button('<i class="fa fa-trash"></i>', array('id' => 'btn-save1', 'class' => 'btn btn-default btn-sm')) !!}
                                        {!! Form::close() !!}

                                    </div>
                                </td-->
                                <td style="text-align: left; vertical-align: middle;">{{ $value->server_id_text }}</td>

                                @if ( $value->server_type_id == "1" )
                                    <td style="text-align: center; vertical-align: middle;"><span class="label label-primary">DB</span></td>
                                @elseif ( $value->server_type_id == "2" )
                                    <td style="text-align: center; vertical-align: middle;"><span class="label label-success">WEB</span></td>
                                @elseif ( $value->server_type_id == "3" )
                                    <td style="text-align: center; vertical-align: middle;"><span class="label label-warning">APP</span></td>
                                @endif


                                <td style="text-align: left; vertical-align: middle;">{{ $value->host_text }}</td>
                                <td style="text-align: center; vertical-align: middle;">{{ $value->lmod_date }}</td>
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
@stop

