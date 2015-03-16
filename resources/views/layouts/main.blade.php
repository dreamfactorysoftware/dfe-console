<?php
use DreamFactory\Enterprise\Console\Http\Controllers\FactoryController;

if ( !isset( $_user ) || !is_array( $_user ) )
{
    $_user = FactoryController::getUserInfo();
}
?>
<!DOCTYPE html >
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page-title','Welcome!') | {{ "DreamFactory Enterprise&trade;" }}</title>
    <!-- Bootstrap Core CSS -->
    <link href="/static/bootstrap-3.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
    {{--<link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/paper/bootstrap.min.css" rel="stylesheet">--}}
    <link href="/static/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/css/ripples.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/css/material-wfont.min.css" rel="stylesheet">
    <link href="//fezvrasta.github.io/snackbarjs/dist/snackbar.min.css" rel="stylesheet">

    @section('head-links')
        <!-- DFE Mods -->
        <link href="/css/grid-style.css" rel="stylesheet">
        <link href="/css/style.css" rel="stylesheet">
        <link rel="stylesheet" href="/css/nanoscroller.css">
    @show

    @section('head-scripts')
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries --><!-- WARNING: Respond.js doesn't work if you view the page via file:// --><!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
        <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]--><!-- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    @show
</head>
<body class="@yield('body-class')">

@section('body-content')
    @include('layouts.main.navbar')

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                @include('layouts.main.sidebar')
            </div>

            <div id="content" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                @yield('content')
            </div>
        </div>
    </div>

    <div id="loading-overlay" style="display: none;">Loading...</div>
    <div class="loading-content" style="display: none;"><img src="/img/img-loading.gif" class="loading-image" alt="Loading..." /></div>
@stop

@include('layouts.main.body-scripts')
</body>
</html>
