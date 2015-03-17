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
        <link href="/css/login.css" rel="stylesheet">
        <link href="/css/metro.css" rel="stylesheet">
    @show

    @section('head-scripts')
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries --><!-- WARNING: Respond.js doesn't work if you view the page via file:// --><!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
        <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script><![endif]--><!-- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    @show
</head>
<body class="@yield('body-class')">

<div id="container-login" class="container">
    <div class="container-logo">
        <h3><img src="/img/logo-dfe.png" alt="" />
            <small>DreamFactory Enterprise<span>v1.0.0</span></small>
        </h3>
    </div>

    {!! $_html !!}

    <form id="login-form" role="form" method="POST">
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon bg_lg"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control email required" autofocus id="email_addr_text"
                       name="email_addr_text" placeholder="email address">
            </div>
        </div>

        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon bg_ly"><i class="fa fa-lock"></i></span>

                <input class="form-control password required" id="password_text" placeholder="password"
                       name="password_text" type="password" />
            </div>
        </div>

        <label class="control-label sr-only" for="_email_addr_text">Email Address</label>
        <label class="control-label sr-only" for="_password_text">Password</label>

        <div class="form-actions">
            <span class="pull-left"><a href="#" class="flip-link btn btn-info" id="to-recover">Lost password?</a></span>

            <span class="pull-right"><button type="submit" class="btn btn-success">Login</button></span>
        </div>

        <input type="hidden" name="recover" value="0">
    </form>

    <form id="recover-form" role="form" action="/app/recover" method="POST">
        <h4>Enter your email address and password reset instructions will be emailed to you.</h4>

        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon bg_lg"><i class="fa fa-user"></i></span>

                <input type="text" class="form-control email required" autofocus id="email_addr_text" name="email_addr_text" placeholder="email address">
            </div>
        </div>

        <div class="form-actions">
            <span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login">&laquo; Back to login</a></span> <span
                    class="pull-right"><button class="btn btn-info" type="submit"> Recover</button></span>
        </div>

        <input type="hidden" name="recover" value="1">
    </form>
</div>

@section( 'before-body-scripts' )
@show

<script src="/static/bootstrap-3.3.2/js/bootstrap.min.js"></script>
<script src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/js/material.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/js/ripples.min.js"></script>
<script>
jQuery(function($) {
    //	Enable MD effects on doc-ready
    $.material.init();
});
</script>

@section( 'before-local-body-scripts' )
@show

<script src="/js/EnterpriseServer.js"></script>
<script src="/js/cerberus.js"></script>

@section( 'after-body-scripts' )
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
    <script src="/js/validate.js"></script>
    <script src="/js/login.js"></script>
@show
</body>
</html>
