@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-header">Instances</h3>

            <div class="hr"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable display nowrap"
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