@extends('layouts.main')

@section('content')
    <div class="scrollable-content">
        <div id="dashboard-header" class="row">
            <div class="col-md-6 no-padding-left">
                <h3>dashboard</h3>
            </div>
            <div class="col-md-2">
                <div class="dashboard-header-info">{{ $_active['instances'] }}
                    <span>active instances</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-header-info">
                    {{ $_active['clusters'] }}
                    <span>active clusters</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-header-info">
                    {{ $_active['users'] }}
                    <span>active users</span>
                </div>
            </div>
        </div>

        <div class="row" style="margin: 25px 0 0 0;">
            <div id="dashboard-tabs" class="col-md-2 pull-right">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="#" class="tab-link" data-toggle="#dashboard-overview">Overview</a></li>
                    <li><a href="#" class="tab-link" data-toggle="#dashboard-users">Users</a></li>
                    <li><a href="#" class="tab-link" data-toggle="#dashboard-statistics">Statistics</a></li>
                    <li><a href="#" class="tab-link" data-toggle="#dashboard-servers">Servers</a></li>
                </ul>
            </div>

            <div class="col-md-10 no-padding-left">
                <div id="dashboard-overview" class="row panel-row tab-content active">
                    <h4 class="page-header">Overview</h4>

                    <div class="panel panel-default db-overview-content">
                        <div class="panel-heading bg_lg">
                            <i class="fa fa-signal"></i>&nbsp;API Calls (hosted DSPs only, calls per day)
                        </div>

                        <div class="panel-body">
                            <div class="col-md-9">
                                <div class="chart" id="timeline-chart"></div>
                            </div>

                            <div class="col-md-3">
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
                        <div class="col-md-6 no-padding-left">
                            <div class="panel panel-default">
                                <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-logins">
                                    DSP User Logins
                                </div>

                                <div class="panel-body no-padding in collapse" id="collapse-tc-logins" style="height: auto;">
                                    <div class="chart" id="timeline-chart-logins"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 no-padding-right">
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
                        <div class="col-md-6 no-padding-left">
                            <div class="panel panel-default">
                                <div class="panel-heading bg_lo" data-toggle="collapse" href="#collapse-tc-provision">
                                    <h3 class="panel-title">Provision Requests</h3>
                                </div>

                                <div class="panel-body no-padding in collapse" id="collapse-tc-provision" style="height: auto;">
                                    <div class="chart" id="timeline-chart-provision"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 no-padding-right">
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
                    <div class="col-md-12 no-padding-left">
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
                    <div class="col-md-12 no-padding-left">
                        <h4 class="page-header">Statistics</h4>

                        <div class="panel-body no-padding in collapse">
                            Statistics go here
                        </div>
                    </div>
                </div>

                <div id="dashboard-servers" class="row panel-row tab-content">
                    <div class="col-md-12 no-padding-left">
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
