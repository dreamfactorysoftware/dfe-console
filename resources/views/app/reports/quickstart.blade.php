@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')


    <div class="col-xs-1 col-sm-2 col-md-2">
        <ul class="nav nav-pills nav-stacked visible-md visible-lg visible-sm">
            <li role="presentation" class="home-link"><a href="/{{$prefix}}/reports">Reports</a></li>
            <li role="presentation" class="home-link active"><a href="/{{$prefix}}/reports/quickstart">Quickstart</a></li>
        </ul>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="nav nav-pills dfe-section-header">
                    <h4 class="">Quickstart</h4>
                </div>
            </div>
        </div>


        <div class="">
            <div class="row">
                <div class="col-xs-12">
                    <iframe id="iframe_quickstart" frameborder="0" width="100%" height="100%" src="//www.dreamfactory.com/in_enterprise_v1/reporting.html"></iframe>

                </div>
            </div>
        </div>
    </div>

    @stop