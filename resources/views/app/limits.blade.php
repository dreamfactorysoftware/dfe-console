@extends('layouts.main')

@include('layouts.partials.topmenu')

@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'limits'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'policies','title' => 'Manage Limits'])

    <!-- Tool Bar -->
    <div class="row">
        <form method="POST" action="/{{$prefix}}/limits/multi" id="multi_delete">
            <input name="_method" type="hidden" value="DELETE">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">
            <input name="_selected" id="_selected" type="hidden" value="">

            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="well well-sm">
                    <div class="btn-group">
                        <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-backward" id="_prev" style="height: 30px; width: 40px"></button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <span id="currentPage">Page 1</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="tablePages"></ul>
                        </div>
                        <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-forward" id="_next" style="height: 30px; width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <button type="button" id="selectedLimitsRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected limits" value="delete" style="width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <input id="limitsSearch" class="form-control input-sm" value="" type="text" placeholder="Search Limits...">
                    </div>
                    <div class="btn-group pull-right">
                        <button type="button" id="refresh" class="btn btn-default btn-sm"><i class="fa fa-fw fa-refresh"></i></button>
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
            <div class="col-xs-12 col-sm-12 col-md-12">
                <table id="limitTable" class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-policy" style="table-layout: fixed; width: 100%; display:none">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Cluster</th>
                        <th>Instance</th>
                        <th>User</th>
                        <th>Limit</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                    </thead>
                    <tbody>

                        @foreach($limits as $value)
                            <tr>
                                <td></td>
                                <td id="actionColumn" class="" style="text-align: center; vertical-align: middle;">
                                    <form method="POST" action="/{{$prefix}}/limits/{{$value['id']}}" id="single_delete_{{ $value['id'] }}">
                                        <input type="hidden" id="limit_id" value="{{ $value['id'] }}">
                                        <input name="_method" type="hidden" value="DELETE">
                                        <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                        <input type="checkbox" value="{{ $value['id'] }}" id="server_checkbox_{{ $value['id'] }}" name="{{ $value['label_text'] }}">&nbsp;&nbsp;
                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" onclick="removeLimit('{{ $value['id'] }}', '{{ $value['label_text'] }}')" value="delete" style="width: 25px" ></button>
                                    </form>
                                </td>
                                <td>{{ $value['label_text'] }}</td>
                                <td>{{ $value['limit_type_text'] }}</td>
                                <td>{{ $value['cluster_id_text'] }}</td>
                                <td>@if(empty($value['instance_id_text']))<em>All</em>@else{{ $value['instance_id_text'] }}@endif</td>
                                <td>@if(empty($value['user_name']))<em>All</em>@else{{ $value['user_name'] }}@endif</td>
                                <td style="text-align: right;">{{ number_format($value['limit_nbr'],0) }} / {{ strtolower($value['period_name']) }}</td>
                                <td style="text-align: center;">@if ($value['active_ind'] == 1) <span class="label label-success">Active</span> @else <span class="label label-warning">Not Active</span> @endif</td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <span id="tableInfo"></span>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript" src="/js/blade-scripts/limits/limits.js"></script>

@stop
