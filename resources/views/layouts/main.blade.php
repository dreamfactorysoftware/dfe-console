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
    <title>{{ "DreamFactory Enterprise&trade;" }} | @yield('page-title','Welcome!')</title>
    <!-- Bootstrap Core CSS -->
    <link href="/static/bootstrap-3.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">
    {{--<link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/paper/bootstrap.min.css" rel="stylesheet">--}}
    <link href="/static/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/css/ripples.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/css/material-wfont.min.css" rel="stylesheet">
    <link href="//fezvrasta.github.io/snackbarjs/dist/snackbar.min.css" rel="stylesheet">
    <!-- DFE Mods -->
    <link href="/css/grid-style.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/nanoscroller.css">
    @section('head-links')
    @show
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries --><!-- WARNING: Respond.js doesn't work if you view the page via file:// --><!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
    <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]--><!-- jQuery -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    @section('head-scripts')
    @show
</head>
<body class="@yield('body-class')">
@include('layouts.main.body')
<div id="loading-overlay" style="display: none;">Loading...</div>
<div class="loading-content" style="display: none;"><img src="/img/img-loading.gif" class="loading-image" alt="Loading..." /></div>
@include('layouts.main.body-scripts')
</body>
</html>
