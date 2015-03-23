<!DOCTYPE html >
<html lang="en">
<head>
    @include('layouts.partials.head-master')

    @section('head-links')
    @show

    @section('head-scripts')
    @show
</head>
<body class="@yield('body-class')">

@include('layouts.partials.navbar')

<div class="container-fluid">
    <div class="row">
		<div class="col-sm-3 col-md-2 sidebar">
			@include('layouts.partials.otf-sidebar')
        </div>

		<div id="content" class="col-sm-9 col-md-10 main">
            @yield('content')
        </div>
    </div>
</div>

<div class="loading-content" style="display: none;"><img src="/img/img-loading.gif" class="loading-image" alt="Loading..." /></div>

@section('before-body-scripts')
@show

@include('layouts.partials.body-scripts')

@section('before-app-scripts')
@show

<script src="/js/EnterpriseServer.js"></script>
<script src="/js/cerberus.js"></script>

@section('after-app-scripts')
@show

</body>
</html>
