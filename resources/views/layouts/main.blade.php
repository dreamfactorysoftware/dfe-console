<!DOCTYPE html >
<html lang="en">
<head>
    <meta charset="utf-8">
    <!--meta http-equiv="X-UA-Compatible" content="IE=edge"-->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ "DreamFactory Enterprise&trade;" }} | @yield('page-title','Welcome!')</title>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <!--link href="/css/stylesheets/styles.css" rel="stylesheet"-->
    <link href="/theme/standard/css/bootstrap.min.css" rel="stylesheet">
    <link href="/theme/common/css/common.css" rel="stylesheet">

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript" src="/theme/standard/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/js/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
<div>
    @include('layouts.partials.navbar')
    @yield('content')
</div>

</body>
</html>
