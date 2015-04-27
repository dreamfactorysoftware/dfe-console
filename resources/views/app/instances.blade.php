@include('layouts.partials.topmenu',array('pageName' => 'Instances', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/instances">Manage</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/instances">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
                    <h4 class="">Manage Instances</h4>
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
                        <button type="button" id="selectedInstancesRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected instances" value="delete" onclick="confirmRemoveSelectedInstances()" style="width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm" id="selectedInstanceRemoveCancel" onclick="cancelRemoveSelectedInstances()" value="delete" style="display: none">Cancel</button>
                    </div>
                    <div style="clear: both"></div>
                </div>
            </div>
        </div>




        <div class="">
            <div class="row">
                <div class="col-xs-12">
                    <table id="instanceTable" class="table table-responsive table-bordered table-striped table-hover table-condensed">
                        <thead>
                        <tr>
                            <th></th>
                            <th style="text-align: center; vertical-align: middle;"> </th>
                            <th class="" >
                                Name
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Cluster
                            </th>
                            <!--th class="" style="">
                                Created On
                            </th-->
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Owner Email
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Policy
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Last Modified
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">

                            </th>
                        </tr>

                        </thead>
                        <tbody>

                        @foreach($instances as $key => $value)
                            <tr>
                                <td>

                                </td>
                                <td style="text-align: center; vertical-align: middle; width: 80px;">
                                    <div class="btn-group">
                                        <input type="hidden" id="instance_id" value="{{ $value->id }}">
                                        <input type="checkbox" value="{{ $value->id }}">&nbsp;&nbsp;
                                        {!! Form::open(array('url' => 'v1/instances/' . $value->id, 'class' => 'pull-right', 'id' => 'instance_' . $value->id)) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {!! Form::button('<i class="fa fa-trash"></i>', array('id' => 'btn-save1', 'class' => 'btn btn-default btn-sm')) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </td>
                                <td style="text-align: left; vertical-align: middle;">{{ $value->instance_id_text }}</td>
                                <td style="text-align: left; vertical-align: middle;">{{ $value->cluster_id_text }}</td>
                                <!--td style="text-align: left; vertical-align: middle;">{{ $value->create_date }}</td-->


                                <td style="text-align: left; vertical-align: middle;">{{ $value->email_addr_text }}</td>
                                <td style="text-align: left; vertical-align: middle;"> </td>
                                <td style="text-align: center; vertical-align: middle;">{{ $value->lmod_date }}</td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input class="btn btn-default btn-xs" type="button" value="Backup">&nbsp;&nbsp;
                                    <input class="btn btn-default btn-xs" type="button" value="Restore">
                                </td>
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

    <script type="text/javascript" src="../js/blade-scripts/instances/instances.js"></script>
@stop

