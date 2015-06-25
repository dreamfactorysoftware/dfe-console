@include('layouts.partials.topmenu',array('pageName' => 'Instances', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <div class="col-md-2">
        <div>
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a href="/{{$prefix}}/instances">Manage</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-md-10">
        <div>
            <div>
                <div class="nav nav-pills dfe-section-header">
                    <h4>Manage Instances</h4>
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
                    <div style="clear: both"></div>
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
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                            @foreach($instances as $key => $value)
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="hidden" id="instance_id" value="{{ $value->id }}">
                                        {{ $value->instance_id_text }}
                                    </td>
                                    <td>{{ $value->cluster_id_text }}</td>
                                    <!--td style="text-align: left; vertical-align: middle;">{{ $value->create_date }}</td-->

                                    <td>{{ $value->email_addr_text }}</td>
                                    <td> </td>
                                    <td>{{ $value->lmod_date }}</td>
                                    <td>
                                        <input class="btn btn-default btn-xs" type="button" value="Backup">&nbsp;&nbsp;
                                        <input class="btn btn-default btn-xs" type="button" value="Restore">
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

    <script type="text/javascript" src="../js/blade-scripts/instances/instances.js"></script>
@stop

