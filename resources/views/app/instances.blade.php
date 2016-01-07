@extends('layouts.main')
@include('layouts.partials.topmenu',['pageName' => 'Instances', 'prefix' => $prefix])
@section('content')

    <div class="col-xs-1 col-sm-2 col-md-2 df-sidebar-nav"></div>

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances', 'title' => 'Manage Instances'])


                <!-- Tool Bar -->
        <div class="row">
            <div class="col-xs-12">
                <div class="well well-sm">
                    <div class="btn-group btn-group pull-right">

                    </div>
                    <div class="btn-group btn-group">

                        <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-backward"
                                id="_prev" style="height: 30px; width: 40px"></button>

                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown"
                                    aria-expanded="false">
                                <span id="currentPage">Page 1</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="tablePages">
                            </ul>
                        </div>

                        <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-forward"
                                id="_next" style="height: 30px; width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <input id="instanceSearch" class="form-control input-sm" value="" type="text"
                               placeholder="Search Instances...">
                    </div>
                    <div class="btn-group pull-right">
                        <button type="button" id="refresh" class="btn btn-default btn-sm fa fa-fw fa-refresh"
                                title="Reset sorting" value="" style="width: 40px"></button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <table id="instanceTable"
                               class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-instance"
                               style="table-layout: fixed; width: 100%; display:none">
                            <thead>
                            <tr>
                                <th></th>
                                <th style="min-width: 175px">Name</th>
                                <th style="min-width: 175px">Cluster</th>
                                <th style="min-width: 175px">Owner Email</th>
                                <th style="min-width: 100px">Last Modified</th>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($instances as $_instance)
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="hidden" id="instance_id" value="{{ $_instance->id }}">
                                        {{ $_instance->instance_id_text }}
                                    </td>
                                    <td>{{ $_instance->cluster->cluster_id_text }}</td>
                                    <!--td style="text-align: left; vertical-align: middle;">{{ $_instance->create_date }}</td-->

                                    <td>{{ $_instance->user->email_addr_text }}</td>
                                    <td style="width: 185px">{{ $_instance->lmod_date }}</td>
                                    <td>
                                        <form method="POST" action="/{{$prefix}}/limits/resetallcounters"
                                              id="reset_counter_{{ $_instance->instance_id }}">
                                            <input type="hidden" name="instance_id" id="instance_id"
                                                   value="{{ $_instance->instance_id }}">
                                            <input name="_method" type="hidden" value="DELETE">
                                            <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-bolt"
                                                    onclick="resetCounter('{{ $_instance->instance_id }}', '{{ $_instance->instance_id_text }}')"
                                                    value="reset"
                                                    style="width: 25px; display: inline; vertical-align: middle"
                                                    data-toggle="tooltip" data-placement="right" title="Reset counter">
                                                Reset All Limit Counters
                                            </button>
                                        </form>
                                    </td>
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
    </div>

    <script type="text/javascript" src="/js/blade-scripts/instances/instances.js"></script>
@stop

