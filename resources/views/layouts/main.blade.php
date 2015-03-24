<!DOCTYPE html>
<html lang="en">
<head>
    @section('head-master')
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('page-title', 'Welcome!') | DreamFactory Enterprise&trade;</title>
        <link href="/vendor/dfe-common/static/bootswatch-3.3.4/{{ config('dfe.common.themes.page', 'darkly') }}.min.css" rel="stylesheet">
        <link href="/static/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="/css/style.css" rel="stylesheet">
        <!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
        <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    @show

    @section('head-links')
    @show

    @section('head-scripts')
    @show
</head>
<body class="@yield('body-class')">

@include('layouts.partials.navbar')

<div id="page-content" class="container-fluid container-content">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            @include('layouts.partials.otf-sidebar')
        </div>

        <div id="content" class="col-sm-9 col-md-10 main">
            @yield('content')
        </div>
    </div>
</div>

@section('before-body-scripts')
@show

<script src="/static/jquery-2.1.3/jquery.min.js"></script>
<script src="/static/bootstrap-3.3.4/js/bootstrap.min.js"></script>
<script src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script src="/js/EnterpriseServer.js"></script>
<script src="/js/cerberus.js"></script>

@section('after-body-scripts')
@show
</body>
</html>
