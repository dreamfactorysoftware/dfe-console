@extends('layouts.main')

@section('page-title')
    oO!
@stop

@section('page-header')
    I've Failed You!
@stop

@section('breadcrumb-title')
    Page Not Found
@stop

@section('page-subheader')
    all your instances are belong to you
@stop

@section('content')
    <div class="row">
        <div class="error-content">
            <div class="error-heading">
                <h3 class="page-header">We're terribly sorry, but...</h3>

                <div class="hr"></div>
            </div>

            <div class="panel panel-danger">
                <div class="panel-heading">
                    <div class="panel-title">Page Not Found</div>
                </div>

                <div class="panel-body">
                    <p>Whatever you are looking for could not be found. At least not on this server. Please check your request and try again.</p>

                    <p><a href="/">Go Home</a></p>
                </div>
            </div>
        </div>
    </div>
@stop
