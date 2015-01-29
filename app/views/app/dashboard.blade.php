@extends('layouts.main')

@section('page-title')
    Dashboard
@stop

@section('page-header')
    Dashboard
@stop

@section('breadcrumb-title')
    Dashboard
@stop

@section('page-subheader')
    all your instances are belong to you
@stop

@section('content')
    <div class="dashboard-content">
        <div class="dashboard-heading">
            <h3 class="page-header">Overview</h3>
        </div>

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">API Calls</div>
                </div>

                <div class="panel-body">
                    <div class="chart" id="timeline-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">API Calls (Back-end)</div>
                </div>

                <div class="panel-body">
                    <div class="chart" id="timeline-chart-fabric-api"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">Logins</div>
                </div>

                <div class="panel-body">
                    <div class="chart" id="timeline-chart-logins"></div>
                </div>
            </div>
        </div>

    </div>
@stop
