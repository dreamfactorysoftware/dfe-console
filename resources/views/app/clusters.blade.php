@include('layouts.partials.topmenu', ['pageName' => 'Clusters'])

@extends('layouts.main')

@section('content')
    <div class="col-md-2">
        <div>
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a href="/{{$prefix}}/clusters">Manage</a>
                </li>
                <li>
                    <a href="/{{$prefix}}/clusters/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-md-10">
        <div>
            <div>
                <div class="nav nav-pills dfe-section-header">
                    <h4>Manage Clusters</h4>
                </div>
            </div>
        </div>

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
                        <div class="btn-group">
                            <input id="clusterSearch" class="form-control input-sm" value="" type="text" placeholder="Search Clusters...">
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
                    <div class="panel panel-default">
                        <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-cluster" id="clusterTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th style="max-width: 100px"></th>
                                    <th style="min-width: 175px">Name</th>
                                    <th style="min-width: 175px">Sub-Domain</th>
                                    <th style="min-width: 125px">Status</th>
                                    <th style="min-width: 175px">Last Modified</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($clusters as $key => $value)
                                <tr>
                                    <td>
                                        <input type="hidden" id="cluster_id" value="{{ $value->id }}">
                                    </td>
                                    <td id="actionColumn">
                                        <div>
                                            <form method="POST" action="/{{$prefix}}/clusters/{{$value->id}}" id="single_delete_{{ $value->id }}">
                                                <input type="hidden" id="cluster_id" value="{{ $value->id }}">

                                                <input name="_method" type="hidden" value="DELETE">
                                                <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">

                                                @if (array_key_exists('cluster_id', $value))
                                                    <div class="tooltip-wrapper"  data-title="Cluster In Use - Delete Disabled">
                                                        <input type="checkbox" disabled>&nbsp;&nbsp;
                                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled style="width: 25px" ></button>
                                                    </div>
                                                @else
                                                    <input type="checkbox" value="{{ $value->id }}" id="cluster_checkbox_{{ $value->id }}" name="{{ $value->cluster_id_text }}">&nbsp;&nbsp;
                                                    <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" onclick="removeCluster({{ $value->id }}, '{{ $value->cluster_id_text }}')" value="delete" style="width: 25px" ></button>
                                                @endif

                                            </form>
                                        </div>
                                    </td>
                                    <td>{{ $value->cluster_id_text }}</td>
                                    <td>{{ $value->subdomain_text }}</td>

                                    <td>
                                        @if ( array_key_exists( 'cluster_id', $value ) )
                                            <span class="label label-warning">In Use</span>
                                        @else
                                            <span class="label label-success">Not In Use</span>
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
    </div>
    <script type="text/javascript" src="../js/blade-scripts/clusters/clusters.js"></script>

    <style>
        .tooltip-wrapper {
            display: inline-block;
        }

    </style>
@stop