@extends('layouts.main')

@section('breadcrumb-title')
    Clusters
@stop

@section('content')
    @include('app._page-header',array('pageName' => 'Clusters', 'buttons' => array('new'=>array('icon'=>'plus','color'=>'success')) ) )

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable nowrap"
                    data-resource="cluster"
                    id="dt-cluster">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Sub-Domain</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop