<?php
!isset($links) && $links =[];
$_uri = URL::getRequest()->getRequestUri();
$_active = ' active';
?>
@include('layouts.partials.topmenu',['pageName' => 'Home', 'prefix' => $prefix])
@extends('layouts.main')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 col-sm-2 col-xs-1">
                <ul class="nav nav-pills nav-stacked visible-md visible-lg visible-sm">
                    @foreach( $links as $_link)
                        <li role="presentation" class="home-link @if($_uri == $_link['href']){!! $_active !!}@endif"><a href="{{ $_link['href'] }}">{{ $_link['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="col-xs-11 col-sm-10 col-md-10">
                <div style="height:100%">
                    <iframe id="home-link-container" seamless src="" style="padding-bottom: 75px; height: 100%; width: 100%; border: none"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(function($){
            var $_links = $('.home-link');

            $_links.on('click','a', function(e) {
                e.preventDefault();
                var _href = $(this).attr('href');
                $_links.removeClass('active');
                $('#home-link-container').attr('src',_href);
                $(this).closest('li').addClass('active');
            });

            if ( $_links.length && !$('.active', $_links).length ) {
                $($_links[0]).find('a').trigger('click');
            }
        });
    </script>
@stop