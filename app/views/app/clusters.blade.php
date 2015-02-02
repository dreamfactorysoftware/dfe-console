@extends('layouts.main')

@section('breadcrumb-title')
    Clusters
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-header">Clusters</h3>

            <div class="hr"></div>
        </div>
    </div>

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