<?php
//  Convert custom CSS file to a <link> tag
if (null !== ($_customCssFile = config('dfe.common.custom-css-file'))) {
    $_customCssFile = '<link href="' . $_customCssFile . '" rel="stylesheet">';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{!! "DreamFactory Enterprise&trade;" !!} | @yield('page-title','Welcome!')</title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico"/>
    <link rel="icon" type="image/png" href="/public/img/apple-touch-icon.png">
    <link href="/static/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/font-awesome-4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="/static/bootstrap-datepicker-1.4.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/theme/common/css/common.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="/public/img/apple-touch-icon.png">
    <script type="text/javascript" src="/static/jquery-2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="/static/datatables-1.10.7/js/jquery.dataTables.min.js"></script>

    @section('user-scripts')
    @stop

    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    {!! $_customCssFile !!}
</head>
<body class="@yield('body-class')">
    @include('layouts.partials.navbar')

    <div id="page-content" class="container-fluid container-content">
        <div id="content" class="col-xs-12 col-sm-12 col-md-12 main">
            @yield('content')
        </div>
    </div>
<div class="clearfix"></div>
<script type="text/javascript" src="/static/bootstrap-3.3.6/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/static/bootstrap-datepicker-1.4.0/js/bootstrap-datepicker.min.js"></script>
</body>
</html>
