@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')

    <div class="col-xs-1 col-sm-2 col-md-2">
        <ul class="nav nav-pills nav-stacked visible-md visible-lg visible-sm">
            <li role="presentation" class="home-link active"><a href="/{{$prefix}}/reports">Quickstart</a></li>
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
                    <iframe id="iframe_quickstart" frameborder="0" width="100%" src="//www.dreamfactory.com/in_enterprise_v1/reporting.html"></iframe>
                    <br><br>
                    <button class="btn btn-default" onClick="window.open('/v1/reports/kibana');">Launch Kibana</button>
                </div>
            </div>
        </div>
    </div>


@stop
