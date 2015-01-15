@extends('layouts.main')

@section('page-title')
    Dashboard
@stop

@section('page-header')
    Dashboard
@stop

@section('page-subheader')
    all your instances are belong to you
@stop

@section('content')
    <div class="scrollable-content dashboard-content">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h3 class="page-header">Overview</h3>
            </div>

            <div class="panel-body row panel-row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading bg_lo db-overview-content" data-toggle="collapse" href="#collapse-tc-api-calls">
                            <h3 class="panel-title"><i class="fa fa-signal"></i>&nbsp;API Calls (hosted DSPs only, calls per day)</h3>
                        </div>

                        <div class="panel-body" id="collapse-tc-api-calls">
                            <div class="col-lg-9">
                                <div class="chart" id="timeline-chart"></div>
                            </div>

                            <div class="col-lg-3">
                                <ul class="site-stats">
                                    <li class="bg_lg"><i class="fa fa-sitemap"></i><strong>
                                            <span id="db_dsp_count_live"></span>
                                        </strong>
                                        <small>Activated DSPs</small>
                                    </li>
                                    <li class="bg_lo"><i class="fa fa-ambulance"></i><strong>
                                            <span id="db_dsp_count_dead"></span>
                                        </strong>
                                        <small>Non-Activated DSPs</small>
                                    </li>
                                    <li class="bg_dy"><i class="fa fa-user"></i><strong>
                                            <span id="db_user_count"></span>
                                        </strong>
                                        <small>Total DSP Users</small>
                                    </li>
                                    <li class="bg_lh"><i class="fa fa-flask"></i><strong>
                                            <span id="db_dsp_database_tables"></span>
                                        </strong>
                                        <small>Total Database Tables</small>
                                    </li>
                                    <li class="bg_lh"><i class="fa fa-sitemap"></i><strong>
                                            <span id="db_dsp_apps"></span>
                                        </strong>
                                        <small>Total Apps (non-system)</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row panel-row">
                        <div class="col-lg-6 no-padding-left">
                            <div class="panel panel-default">
                                <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-logins">
                                    DSP User Logins
                                </div>

                                <div class="panel-body no-padding in collapse" id="collapse-tc-logins" style="height: auto;">
                                    <div class="chart" id="timeline-chart-logins"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 no-padding-right">
                            <div class="panel panel-default">
                                <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-activations">
                                    DSP Activations
                                </div>
                                <div class="panel-body no-padding in collapse" id="collapse-tc-activations" style="height: auto;">
                                    <div class="chart" id="timeline-chart-activations"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row panel-row">
                        <div class="col-lg-6 no-padding-left">
                            <div class="panel panel-default">
                                <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-provision">
                                    <h3 class="panel-title">Provision Requests</h3>
                                </div>

                                <div class="panel-body no-padding in collapse" id="collapse-tc-provision" style="height: auto;">
                                    <div class="chart" id="timeline-chart-provision"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 no-padding-right">
                            <div class="panel panel-default">
                                <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-fabric-api">
                                    <h3 class="panel-title">Fabric API</h3>
                                </div>
                                <div class="panel-body no-padding in collapse" id="collapse-tc-fabric-api" style="height: auto;">
                                    <div class="chart" id="timeline-chart-fabric-api"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="dashboard-users" class="row panel-row tab-content">
                    <div class="col-lg-12 no-padding-left">
                        <h4 class="page-header">Users</h4>

                        <div class="panel panel-default">
                            <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-fabric-api">
                                <h3 class="panel-title">User Info #1</h3>
                            </div>
                            <div class="panel-body no-padding in collapse" id="collapse-tc-fabric-api" style="height: auto;">
                                <div class="chart" id="timeline-chart-fabric-api"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="dashboard-statistics" class="row panel-row tab-content">
                    <div class="col-lg-12 no-padding-left">
                        <h4 class="page-header">Statistics</h4>

                        <div class="panel-body no-padding in collapse">
                            Statistics go here
                        </div>
                    </div>
                </div>

                <div id="dashboard-servers" class="row panel-row tab-content">
                    <div class="col-lg-12 no-padding-left">
                        <h4 class="page-header">Servers</h4>

                        <div class="panel-body no-padding in collapse">
                            Server info here
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
