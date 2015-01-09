@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable nowrap" data-resource="user" id="dt-user">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email Address</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop