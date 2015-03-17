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

<div id="container-login" class="container-fluid">
    <div class="row">
        <div class="col-md-offset-3 col-md-6 col-md-offset-3">
            <div class="container-logo">
                <h3><img src="/img/logo-dfe.png" alt="" />
                    <small>DreamFactory Enterprise<span>v1.0.x-alpha</span></small>
                </h3>
            </div>

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Nope!</strong> The email address and password combination are invalid.<br><br>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="login-form" role="form" method="POST" action="/auth/login">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon bg_lg"><i class="fa fa-user"></i></span>

                        <input type="email"
                               class="form-control email required"
                               autofocus
                               name="email_addr_text"
                               placeholder="email address"
                               value="{{ old('email_addr_text') }}">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon bg_ly"><i class="fa fa-lock"></i></span>

                        <input class="form-control password required" placeholder="password" name="password_text" type="password" />
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember">
                            remember me
                        </label>
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

            <form id="recover-form" role="form" action="/password/email" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <h4 style="text-align: left;">We're sorry you have lost your password. Please enter your registered email address below and we will send you instructions on how to reset your password.</h4>

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon bg_lg"><i class="fa fa-user"></i></span>

                        <input type="email"
                               class="form-control email required"
                               autofocus
                               name="email_addr_text"
                               placeholder="email address">
                    </div>
                </div>

                <div class="form-actions">
                    <span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login">&laquo; Back to login</a></span>
                    <span class="pull-right"><button class="btn btn-info" type="submit">Recover</button></span>
                </div>

                <input type="hidden" name="recover" value="1">
            </form>
        </div>
    </div>
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
