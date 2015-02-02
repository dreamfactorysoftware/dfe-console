@extends('layouts.main')

@section('body-class'){{ "body-404" }}@stop

@section('content')
    <div class="text-center page-404 error-wrapper">
        <img src="/img/bg-planet.png" />

        <br /><br />

        <h1>Error 404</h1>

        <h2>P a g e&nbsp; n o t &nbsp; f o u n d</h2>

        <p>Whatever you are looking for could not be found. At least not on this server. Please check your request and try again.</p>

        <p><a href="/">Go Home</a></p>
    </div>
@stop