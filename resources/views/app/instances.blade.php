@extends('layouts.main')
@include('layouts.partials.topmenu',['pageName' => 'Instances', 'prefix' => $prefix])
@section('content')

    <div class="col-xs-1 col-sm-2 col-md-2 df-sidebar-nav"></div>

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances', 'title' => 'Instance Manager'])


        <!-- Tool Bar -->
        <div class="row">
            <div class="col-xs-12">
                <div class="well well-sm">
                    <div class="btn-group btn-group pull-right">

                    </div>
                    <div class="btn-group btn-group">

                        <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-backward" id="_prev" style="width: 40px"></button>

                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <span id="currentPage">Page 1</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="tablePages">
                            </ul>
                        </div>

                        <button type="button" disabled="disabled" class="btn btn-default btn-sm fa fa-fw fa-forward" id="_next" style="width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <input id="instanceSearch" class="form-control input-sm" value="" type="text" placeholder="Search Instances...">
                    </div>
                    <div class="btn-group pull-right">
                        <button type="button" id="refresh" class="btn btn-default btn-sm fa fa-fw fa-refresh" title="Reset sorting" value="" style="width: 40px"></button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <table id="instanceTable" class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-instance">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Cluster</th>
                                    <th>Owner Email</th>
                                    <th>Policy</th>
                                    <th>Last Modified</th>
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
                                    <td> </td>
                                    <td>{{ $_instance->lmod_date }}</td>
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

