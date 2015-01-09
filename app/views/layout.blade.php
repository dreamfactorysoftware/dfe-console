<!DOCTYPE html>
<html lang="en">
<head>
    @section('title-section')
        <title>DreamFactory Enterprise&trade; - Dashboard</title>
    @stop

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="//fonts.googleapis.com/css?family=Roboto+Condensed|Open+Sans" rel="stylesheet" type="text/css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdn.datatables.net/1.10.3/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
    <link href="//cdn.datatables.net/colvis/1.1.1/css/dataTables.colVis.css" rel="stylesheet" type="text/css">
    <link href="//cdn.datatables.net/tabletools/2.2.3/css/dataTables.tableTools.css" rel="stylesheet" type="text/css">
    <link href="/css/main.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->        <!--[if lt IE 9]>
    <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv-printshiv.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>    <![endif]-->
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar-logo">
                <a class="navbar-brand" href="/">DreamFactory Enterprise&trade;</a>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="main-navbar">
            <ul class="nav navbar-nav pull-right">
                <li class="hidden-xs navbar-icon hide">
                    <a href="#" class="modal-link"> <i class="fa fa-bell"></i></a>
                </li>
                <li class="hidden-xs navbar-icon hide">
                    <a href="/app/messages" class="ajax-link"> <i class="fa fa-envelope"></i></a>
                </li>
                <li class="dropdown avatar img-circle ">
                    <div class="avatar-image" data-hash="{{ md5( trim( strtolower( 'a user' ) ) ) }}"></div>
                </li>
                <li class="dropdown user-info">
                    <a href="#" class="dropdown-toggle account" data-toggle="dropdown">{{ 'a user' }}
                        <span class="caret"></span>
                    </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a class="ajax-link" href="/app/profile"> <i class="fa fa-user"></i>

                                <span>Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="/app/messages" class="ajax-link"> <i class="fa fa-envelope"></i>

                                <span>Messages</span>
                            </a>
                        </li>
                        <li>
                            <a class="ajax-link" href="/app/settings"> <i class="fa fa-cog"></i>

                                <span>Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="/web/logout"> <i class="fa fa-power-off"></i>

                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div id="main" class="container-fluid">

    <div class="row">

        <div id="sidebar-left" class="hidden-sm hidden-xs col-md-2">
            <ul class="nav main-menu">
                <li>
                    <a href="/app/dashboard" class="ajax-link"> <i class="fa fa-dashboard fa-2x"></i>

                        <span class="hidden-xs">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a class="ajax-link" href="/app/users"> <i class="fa fa-users fa-2x"></i>

                        <span class="hidden-xs">Users</span>
                    </a>
                </li>

                <li>
                    <a class="ajax-link" href="/app/servers"> <i class="fa fa-gear fa-2x"></i>

                        <span class="hidden-xs">Servers</span>
                    </a>
                </li>

                <li>
                    <a class="ajax-link" href="/app/clusters"> <i class="fa fa-gears fa-2x"></i>

                        <span class="hidden-xs">Clusters</span>
                    </a>
                </li>

                <li>
                    <a class="ajax-link" href="/app/instances"> <i class="fa fa-rocket fa-2x"></i>

                        <span class="hidden-xs">Instances</span>
                    </a>
                </li>

                <li>
                    <a class="ajax-link" href="/app/usage"> <i class="fa fa-bar-chart fa-2x"></i>

                        <span class="hidden-xs">Usage</span>
                    </a>
                </li>

                <li>
                    <a class="ajax-link" href="/app/settings"> <i class="fa fa-cog fa-2x"></i>

                        <span class="hidden-xs">Settings</span>
                    </a>
                </li>

            </ul>
        </div>

        <div id="content" class="col-xs-12 col-sm-12 col-md-10">
            <div class="loading-content" style="display: none;">
                <img src="/img/img-loading.gif" class="loading-image" alt="Loading..." />
            </div>

            <div id="ajax-content">
                <div class="row">
                    {{ $_trail }}
                </div>

                @yield('content')
            </div>
        </div>

    </div>

</div>

<div id="loading-overlay" style="display: none;">Loading...</div>

@section('footer-section')
    <script src="/static/plugins/jquery/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//cdn.datatables.net/1.10.3/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/colvis/1.1.1/js/dataTables.colVis.min.js"></script>
    <script src="//cdn.datatables.net/tabletools/2.2.3/js/dataTables.tableTools.min.js"></script>
    <script src="/static/highcharts/4.0.4/highcharts.min.js"></script>
    <script src="/static/highcharts/4.0.4/exporting.min.js"></script>
    <script src="/js/EnterpriseServer.js"></script>
    <script src="/js/cerberus.js"></script>
@stop
</body>
</html>
