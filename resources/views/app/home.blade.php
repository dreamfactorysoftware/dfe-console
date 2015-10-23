@extends('layouts.main')
<?php
!isset($links) && $links = [];
$_uri = URL::getRequest()->getRequestUri();
$_active = ' active';
?>
@include('layouts.partials.topmenu',['pageName' => 'Home', 'prefix' => $prefix])
@section('content')
    <div class="col-xs-1 col-sm-2 col-md-2">
        <ul class="nav nav-pills nav-stacked visible-md visible-lg visible-sm">
            @foreach( $links as $_link)
                <li role="presentation" class="home-link @if($_uri == $_link['href']){!! $_active !!}@endif"><a
                            href="{{ $_link['href'] }}">{{ $_link['name'] }}</a></li>
            @endforeach
        </ul>
    </div>

    <div class="col-xs-11 col-sm-10 col-md-10">
        <iframe id="home-link-container" seamless="seamless"></iframe>
        {{ $formatted }}
    </div>

    <script>
        jQuery(function ($) {
            var $_links = $('.home-link');

            $_links.on('click', 'a', function (e) {
                e.preventDefault();
                var _href = $(this).attr('href');
                $_links.removeClass('active');
                $('#home-link-container').attr('src', _href);
                $(this).closest('li').addClass('active');
            });

            if ($_links.length && !$('.active', $_links).length) {
                $($_links[0]).find('a').trigger('click');
            }
        });
    </script>
@stop