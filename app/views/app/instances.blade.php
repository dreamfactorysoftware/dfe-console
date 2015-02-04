@extends('layouts.main')

@section('content')
    @include('app._page-header',array('pageName' => 'Instances', 'buttons' => array('new'=>array('icon'=>'plus','color'=>'success')) ) )

    <div class="row">
        <div class="col-md-12">
            <table class="table table-compact table-bordered table-striped table-hover table-select table-datatable nowrap"
                    data-resource="instance"
                    id="dt-instance">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Cluster</th>
                        <th>Created On</th>
                        <th>Owner Email</th>
                        <th>Last Visit</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop