@extends('layouts.master')

@section('head')
    <title>DreamFactory Enterprise&trade; - Oops!</title>
@stop

@section('master.body')
    <body class="error-wrapper">

    @include('master.content_header')

    <div class="container-fluid">
        @yield('content')
    </div>

    <div id="footer">
        <div class="container-fluid">
            <div class="pull-left footer-copyright">
                <p class="footer-text">&copy; <a target="_blank"
                            href="https://www.dreamfactory.com">DreamFactory Software, Inc.</a> 2012-{{ date('Y') }}. All Rights Reserved.
                </p>
            </div>
            <div class="pull-right footer-version"><p class="footer-text">
                    <a href="https://github.com/dreamfactorysoftware/dsp-core/"
                            target="_blank">v{{ $_version }}</a>
                </p>
            </div>
        </div>
    </div>
    @include('master.content_footer')
    </body>
@overwrite
