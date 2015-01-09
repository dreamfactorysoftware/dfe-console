@extends('layout')

@section('content')
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
                        <th>Owner Email</th>
                        <th>Created On</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop