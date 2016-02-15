<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DreamFactory Enterprise&trade; Soft Launch</title>
    <link href="/static/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/soft-launch.css" rel="stylesheet">
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
    <img class="img-responsive img-center" src="/img/logo-soft-launch.png" alt="DreamFactory">
</header>

<section>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="section-heading">Congratulations!</h1>
                <p class="lead section-lead">DreamFactory Enterprise&trade; has been successfully installed.</p>
                <p class="section-paragraph">[NEED COPY] Below are some additional setup steps you make take. These options will quickly get your started
                    working with
                    DreamFactory&trade;.</p>
            </div>
        </div>
    </div>
</section>

<aside class="image-bg-fixed-height"></aside>

<section>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="section-heading">Create Your Dream!</h1>
                <p class="section-paragraph">[NEED COPY] Simply fill out the form below and press <strong>Launch</strong>.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <form class="form-horizontal">
                    <div class="form-group">
                        <div class="col-lg-2">
                            <label class="control-label" for="email-address">Email Address</label>
                        </div>

                        <div class="col-lg-6">
                            <input class="form-control" type="email" id="email-address" name="email-address" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-2">
                            <label class="control-label" for="password">Password</label>
                        </div>
                        <div class="col-lg-6">
                            <input class="form-control" type="password" id="password" name="password" required>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <p>Copyright &copy; {{ date('Y') }} <a href="https://www.dreamfactory.com/" target="_blank">DreamFactory Software, Inc.</a> All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="/static/jquery-2.1.4/jquery.min.js"></script>
<script src="/static/bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
