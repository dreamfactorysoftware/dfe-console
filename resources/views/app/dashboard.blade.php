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

@section('layouts.main.body-content')
    <div class="row">
        <div class="col-md-12 dashboard-content">
            <div class="dashboard-heading">
                <h3 class="page-header">Overview</h3>

                <div class="hr"></div>
            </div>
        </div>
    </div>

    <div class="row">
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

@section('before-local-body-scripts')
    <script src="/static/highcharts/4.0.4/highcharts.min.js"></script>
    <script src="/js/chart-theme.js"></script>
    <script src="/js/cerberus.graphs.js"></script>
@stop