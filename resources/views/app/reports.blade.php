@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')

    <div class="col-xs-1 col-sm-2 col-md-2">
        <ul class="nav nav-pills nav-stacked visible-md visible-lg visible-sm">
            <li role="presentation" class="home-link active"><a href="/{{$prefix}}/reports">Reports</a></li>
            <li role="presentation" class="home-link"><a href="/{{$prefix}}/reports/quickstart">Quickstart</a></li>
        </ul>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="nav nav-pills dfe-section-header">
                    <h4 class="">Reports</h4>
                </div>
            </div>
        </div>

        <div class="">
            <div class="row">
                <div class="col-xs-12">
                    The ELK Stack (Elasticsearch, Logstash, Kibana) is pre-installed with DreamFactory Enterprise. The ELK system automatically logs API calls from each DreamFactory instance managed by DreamFactory Enterprise.
                    <br><br>
                    No configuration is required in DreamFactory Enterprise to log API calls. See the instructions <a href="http://wiki.dreamfactory.com/DFE/Reports" target="_blank">here</a> to configure Kibana and import a number of pre-defined API reports.
                    <br><br>
                    <button class="btn btn-default" onClick="window.open('/v1/reports/kibana');">Open Kibana</button>
                </div>
            </div>
        </div>
    </div>


@stop
