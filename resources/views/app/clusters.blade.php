@include('layouts.partials.topmenu',array('pageName' => 'Clusters', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')
    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/clusters">Manage</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/clusters/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
                    <h4>Manage Clusters</h4>
                </div>
            </div>
        </div>

        <!-- Tool Bar -->
        <div class="row">
            <form method="POST" action="/{{$prefix}}/clusters/multi" id="multi_delete">
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
                        <button type="button" id="selectedClustersRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected clusters" value="delete" style="width: 40px"></button>
                    </div>
                    <div style="clear: both"></div>
                </div>
            </div>
            </form>
        </div>

        <div class="">
            <div class="row">
                <div class="col-xs-12">
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive table-bordered table-striped table-hover table-condensed" id="clusterTable">
                        <thead>
                        <tr>
                            <th></th>
                            <th style="text-align: center; vertical-align: middle;"></th>
                            <th class="" >
                                Name
                            </th>
                            <th class="" style="">
                                Sub-Domain
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
                        @foreach($clusters as $key => $value)
                            <tr>
                                <td>
                                    <input type="hidden" id="cluster_id" value="{{ $value->id }}">
                                </td>
                                <td style="text-align: center; vertical-align: middle;" id="actionColumn">
                                    <div>
                                        <form method="POST" action="/{{$prefix}}/clusters/{{$value->id}}" id="single_delete_{{ $value->id }}">
                                            <input type="hidden" id="cluster_id" value="{{ $value->id }}">

                                            <input name="_method" type="hidden" value="DELETE">
                                            <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">

                                            @if (array_key_exists('cluster_id', $value))
                                                <input type="checkbox" value="{{ $value->id }}" disabled id="cluster_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                                <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled onclick="removeCluster({{ $value->id }}, '{{ $value->cluster_id_text }}')" value="delete" style="width: 25px" ></button>
                                            @else
                                                <input type="checkbox" value="{{ $value->id }}" id="cluster_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                                <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" onclick="removeCluster({{ $value->id }}, '{{ $value->cluster_id_text }}')" value="delete" style="width: 25px" ></button>
                                            @endif

                                        </form>
                                    </div>
                                </td>
                                <td style="text-align: left; vertical-align: middle;">{{ $value->cluster_id_text }}</td>
                                <td style="text-align: left; vertical-align: middle;">{{ $value->subdomain_text }}</td>

                                <td style="text-align: center; vertical-align: middle;">
                                    @if ( array_key_exists( 'cluster_id', $value ) )
                                        <span class="label label-warning">In Use</span>
                                    @else
                                        <span class="label label-success">Not In Use</span>
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
    </div>
    <script type="text/javascript" src="../js/blade-scripts/clusters/clusters.js"></script>
@stop