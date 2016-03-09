<?php

use DreamFactory\Enterprise\Common\Enums\ServerTypes;

?>

@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'servers'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header', ['resource'=>'servers', 'title' => 'Manage Servers'])

        <div class="row">
            <form method="POST" action="/{{$prefix}}/servers/multi" id="multi_delete">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="<?php use DreamFactory\Enterprise\Database\Models\Server;echo csrf_token(); ?>">
                <input name="_selected" id="_selected" type="hidden" value="">

                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="well well-sm">
                        <div class="btn-group" role="group">
                            <button type="button" disabled="disabled"
                                    class="btn btn-default btn-sm fa fa-fw fa-backward" id="_prev"
                                    style="height: 30px; width: 40px"></button>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-expanded="false" aria-haspopup="true"><span
                                        id="currentPage">Page 1</span>&nbsp;<span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu" id="tablePages"></ul>
                            </div>
                            <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-forward"
                                    id="_next" style="height: 30px; width: 40px"></button>
                        </div>

                        <div class="btn-group">
                            <button type="button" id="selectedServersRemove" class="btn btn-default btn-sm"
                                    title="Delete selected servers" value="delete"><i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>

                        <div class="btn-group">
                            <input id="serverSearch" class="form-control input-sm" type="text" placeholder="search">
                        </div>

                        <div class="btn-group pull-right">
                            <button type="button" id="refresh" class="btn btn-default btn-sm" title="Reset sorting"><i
                                    class="fa fa-fw fa-refresh"></i></button>
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
                    <table id="serverTable"
                           class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-server"
                        {{--style="table-layout: fixed; width: 100%; display:none"--}}>
                        <thead>
                        <tr>
                            <th style="width: 0 !important;"></th>
                            <th>Actions</th>
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
                                <td style="width: 0 !important;"></td>
                                <td id="actionColumn">
                                    <form method="POST" action="/{{$prefix}}/servers/{{$value->id}}"
                                          id="single_delete_{{ $value->id }}">
                                        <input name="server_id" id="server_id" type="hidden" value="{{ $value->id }}">
                                        <input name="_method" type="hidden" value="DELETE">
                                        <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                                        <div class="form-group tooltip-wrapper" data-placement="left" data-title="Server In Use - Delete Disabled">
                                            @if (!empty($value->cluster_id))
                                                <input type="checkbox" disabled style="margin-top: auto; vertical-align: middle">
                                                <button type="button" style="margin-top: auto; vertical-align: middle"
                                                        class="btn btn-default btn-sm"
                                                        disabled><i class="fa fa-fw fa-trash"></i></button>
                                        </div>
                                        @else
                                            <input type="checkbox"
                                                   value="{{ $value->id }}"
                                                   id="server_checkbox_{{ $value->id }}"
                                                   name="{{ $value->server_id_text }}" style="margin-top: auto; vertical-align: middle">&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-sm" style="margin-top: auto; vertical-align: middle"
                                                    onclick="removeServer('{{ $value->id }}', '{{ $value->server_id_text }}')"
                                                    value="delete"><i class="fa fa-fw fa-trash"></i></button>
                                        @endif
                                    </form>
                                </td>

                                <td>{{ $value->server_id_text }}</td>

                                <td>
                                    <div class="tooltip-wrapper" data-title="Server In Use - Delete Disabled">
                                        <i class="text-primary fa fa-fw {{ config('icons.server-types.'.$value->server_type_id,'fa-server') }}"></i>&nbsp;{{ ServerTypes::nameOf($value->server_type_id, false, true) }}

                                </td>

                                <td>{{ $value->host_text }}</td>

                                <td>
                                    @if(empty($value->cluster_id))
                                        <span class="label label-success">Available</span>
                                    @else
                                        @if ( !empty(data_get($value->config_text, 'multi-assign')) )
                                            <span class="label label-success">Available</span>
                                        @else
                                            <span class="label label-warning">In Use</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $value->lmod_date }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
                <span id="tableInfo"></span>
                <br><br><br><br>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/js/blade-scripts/servers/servers.js"></script>
@stop
