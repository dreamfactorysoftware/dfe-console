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
                    <div style="clear: both"></div>
                </div>
            </div>
            </form>
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
                                Status
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

                                <td style="text-align: center; vertical-align: middle;" id="actionColumn" class="form-inline">
                                    <!--div-->
                                    <form method="POST" action="/{{$prefix}}/servers/{{$value->id}}" id="single_delete_{{ $value->id }}">
                                    <input type="hidden" id="server_id" value="{{ $value->id }}">

                                            <input name="_method" type="hidden" value="DELETE">
                                            <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                                        @if (array_key_exists('cluster_id', $value))
                                            <input type="checkbox" value="{{ $value->id }}" disabled id="server_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled onclick="removeServer({{ $value->id }}, '{{ $value->server_id_text }}')" value="delete" style="width: 25px" ></button>
                                        @else
                                            <input type="checkbox" value="{{ $value->id }}" id="server_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" onclick="removeServer({{ $value->id }}, '{{ $value->server_id_text }}')" value="delete" style="width: 25px" ></button>
                                        @endif


                                        </form>
                                    <!--/div-->
                                </td>

                                <td style="text-align: left; vertical-align: middle;">{{ $value->server_id_text }}</td>

                                @if ( $value->server_type_id == "1" )
                                    <td style="text-align: center; vertical-align: middle;"><span class="label label-primary">DB</span></td>
                                @elseif ( $value->server_type_id == "2" )
                                    <td style="text-align: center; vertical-align: middle;"><span class="label label-success">WEB</span></td>
                                @elseif ( $value->server_type_id == "3" )
                                    <td style="text-align: center; vertical-align: middle;"><span class="label label-warning">APP</span></td>
                                @endif

                                <td style="text-align: left; vertical-align: middle;">{{ $value->host_text }}</td>

                                <td style="text-align: center; vertical-align: middle;">
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
        </form>
    </div>

    <script type="text/javascript" src="../js/blade-scripts/servers/servers.js"></script>

    <script>


    </script>
@stop

