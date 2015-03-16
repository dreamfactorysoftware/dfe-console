<?php
$_html = null;

if ( !empty( $messages ) )
{
    $_html = '<div class="alert alert-error alert-fixed fade in" data-alert="alert"><strong>Please check your entries.</strong>';

    foreach ( $messages->all( '<p>:message</p>') as $_errorMessage )
    {
        $_html .= $_errorMessage;
    }

    $_html .= '</div>';
}
?>
@extends('layouts.main')

@section('main-body')
<div class="container-fluid">
    <div class="row">
        <div id="content" class="col-sm-12 col-md-12 main">
            @include('app.login-body')
        </div>
    </div>
</div>
@overwrite

@section('title-section')
    <title>DreamFactory Enterprise&trade; - Login</title>
@overwrite

@section('head-links')
    <link href="/css/login.css" rel="stylesheet">
    <link href="/css/metro.css" rel="stylesheet">
@stop

@section('body-scripts')
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
    <script src="/js/login.js"></script>
@stop
