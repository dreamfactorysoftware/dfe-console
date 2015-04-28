<!DOCTYPE html >
<html lang="en">
    <head>
        <meta charset="utf-8">
        <!--meta http-equiv="X-UA-Compatible" content="IE=edge"-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ "DreamFactory Enterprise&trade;" }} | @yield('page-title','Welcome!')</title>

        <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
        <script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.10.5/jquery.dataTables.min.js"></script>

        <link href="/css/stylesheets/styles.css" rel="stylesheet">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/js/bootstrap-datepicker.min.js"></script>

    </head>
    <body>
        <div>
            @include('layouts.partials.navbar')
            @yield('content')
        </div>

    </body>
</html>
