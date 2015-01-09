@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable nowrap" data-resource="server" id="dt-server">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Host</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop