@extends('layouts.dark-page')

@section('head-links')
    @parent
    <link href="/css/login.css" rel="stylesheet">
    <link href="/css/metro.css" rel="stylesheet">
@stop

@section( 'after-app-scripts' )
    @parent
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
    <script src="/js/validate.js"></script>
    <script src="/js/login.js"></script>
@show

@section('page-title')
    Login
@overwrite

@section('content')
    <div id="container-login" class="container-fluid">
        <div class="row">
            <div class="col-md-offset-4 col-md-4 col-md-offset-4">
                <div class="container-logo">
                    <h3><img src="/img/logo-dfe.png" alt="" />
                        <small>DreamFactory Enterprise
                            <span>v1.0.x-alpha</span>
                        </small>
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

                    <div class="alert alert-info" role="alert">
                        <p>We're sorry you have lost your password. Please enter your registered email address below and we will send you reset instructions.</p>
                    </div>

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
@stop