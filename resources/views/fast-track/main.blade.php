<?php
/**
 * @var string $launchButtonText
 * @var string $endpoint
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DreamFactory Enterprise&trade; FastTrack</title>
    <link href="/static/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/font-awesome-4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="/css/fast-track.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
    <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Welcome</a>
        </div>
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
                <li>
                    <a href="https://www.dreamfactory.com/company" target="_blank">About</a>
                </li>
                <li>
                    <a href="https://www.dreamfactory.com/features" target="_blank">Products</a>
                </li>
                <li>
                    <a href="https://www.dreamfactory.com/support" target="_blank">Support</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="image-bg-fluid-height">
    <img class="img-responsive img-center" src="/img/header-fast-track.png" alt="DreamFactory">
    <img class="img-responsive img-center uplifted" src="/img/img-fast-track.png">
</header>

<section style="padding-bottom: 5px;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <p class="lead section-paragraph">You are <strong><em>one</em></strong> step away from having your very own DreamFactory instance! Just fill out
                    the form below
                    and press
                    the
                    <strong>{{ $launchButtonText }}</strong> button.</p>

                <div id="error-alert" class="alert alert-danger alert-dismissible fade in hidden" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 id="error-header">There was a problem...</h4>
                    <div id="error-body"></div>
                </div>

                <form id="ft-register">
                    <input type="hidden" id="nickname" name="nickname" value="">
                    <input type="hidden" id="redirect" name="redirect" value="true">

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="sr-only" for="first-name">First Name</label>
                                <input class="form-control"
                                       type="text"
                                       minlength="3"
                                       maxlength="32"
                                       id="first-name"
                                       name="first-name"
                                       required
                                       placeholder="First Name" value="{{ \Input::old('first-name') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="sr-only" for="last-name">Last Name</label>
                                <input class="form-control"
                                       type="text"
                                       minlength="3"
                                       maxlength="32"
                                       id="last-name"
                                       name="last-name"
                                       required
                                       placeholder="Last Name" value="{{ \Input::old('last-name') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="sr-only" for="email">Email Address</label>
                                <input class="form-control"
                                       type="text"
                                       minlength="5"
                                       maxlength="128"
                                       id="email"
                                       name="email"
                                       required
                                       placeholder="Email Address" value="{{ \Input::old('email') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="sr-only" for="phone">Phone Number</label>
                                <input class="form-control"
                                       type="text"
                                       maxlength="40"
                                       id="phone"
                                       name="phone"
                                       placeholder="Phone" value="{{ \Input::old('phone') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="sr-only" for="company">Company Name</label>
                                <input class="form-control"
                                       type="text"
                                       maxlength="64"
                                       id="company"
                                       name="company"
                                       placeholder="Company" value="{{ \Input::old('company') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="sr-only" for="password">Password</label>
                                <input class="form-control"
                                       type="password"
                                       minlength="3"
                                       maxlength="16"
                                       id="password"
                                       name="password"
                                       placeholder="Password"
                                       required>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6">
                                    <label class="sr-only" for="password_confirmation">Confirm Password</label>
                                    <input class="form-control"
                                           type="password"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           maxlength="128"
                                           required
                                           placeholder="Confirm password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button id="btn-launch" type="submit" class="btn btn-warning">{{ $launchButtonText }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="social-links pull-right">
                    <ul class="list-inline">
                        <li>
                            <a target="_blank"
                               href="https://github.com/dreamfactorysoftware/"><i class="fa fa-github-square fa-2x"></i></a>
                        </li>
                        <li>
                            <a target="_blank"
                               href="https://facebook.com/dfsoftwareinc/"><i class="fa fa fa-facebook-square fa-2x"></i></a>
                        </li>
                        <li>
                            <a target="_blank"
                               href="https://twitter.com/dfsoftwareinc/"><i class="fa fa-twitter-square fa-2x"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="clearfix"></div>
                <p><span class="pull-left hidden-xs">DreamFactory Enterprise&trade; FastTrack
                        <small style="margin-left: 5px;font-size: 9px;">({!! config('dfe.common.display-version') !!})</small>
                        </span> <span class="pull-right">{!! config('dfe.common.display-copyright') !!}</span>
                </p>
            </div>
        </div>
    </div>
</footer>

<div class="please-wait hidden">
    <div class="loading-text">Preparing your instance&#8230;</div>
    <div class="loading-icon"><i class="fa fa-fw fa-circle-o-notch fa-spin"></i></div>
</div>

<script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="/static/bootstrap-3.3.6/js/bootstrap.min.js"></script>
<script src="/js/fast-track.js"></script>
<script>
    var fast_track_endpoint = '{{ $endpoint }}';
</script>
</body>
</html>
