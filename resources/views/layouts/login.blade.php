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

<div class="container-fluid">
    <div class="row">
        <div id="content" class="col-sm-12 col-md-12 main">
            <div id="loginbox">

                <form id="loginform" class="form-vertical" method="POST">
                    <input type="hidden" name="recover" value="0">
                    <div class="control-group normal_text logo-container"><h3><img src="/img/logo-cerberus-256x256.png" alt="" /></h3></div>
                    {!! $_html !!}
                    <div class="control-group">
                        <div class="controls">
                            <div class="main_input_box">
                                <span class="add-on bg_lg"><i class="icon-user"></i></span>
                                <input class="email required"
                                        autofocus
                                        type="text"
                                        id="email_addr_text"
                                        name="email_addr_text"
                                        placeholder="Email Address" />
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <div class="main_input_box">
                                <span class="add-on bg_ly"><i class="icon-lock"></i></span>
                                <input class="password required" id="password_text" placeholder="Password"
                                        name="password_text" type="password" />
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <span class="pull-left"><a href="#" class="flip-link btn btn-info" id="to-recover">Lost password?</a></span>
                        <span class="pull-right"><button type="submit" class="btn btn-success"> Login</button></span>
                    </div>
                </form>

                <form id="recoverform" class="form-vertical" action="/app/recover" method="POST">
                    <input type="hidden" name="recover" value="1">
                    <p class="normal_text">Enter your email address below and we will send you instructions how to recover a password.</p>

                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lo"><i class="icon-envelope"></i></span>
                            <input class="email required"
                                    autofocus
                                    type="text"
                                    id="email_addr_text"
                                    name="email_addr_text"
                                    placeholder="Email Address" />
                        </div>
                    </div>

                    <div class="form-actions">
                        <span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login">&laquo; Back to login</a></span>
                        <span class="pull-right"><button class="btn btn-info" type="submit">Recover</button></span>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@include('layouts.main.body-scripts')
</body>
</html>
