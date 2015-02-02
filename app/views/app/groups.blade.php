@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h3 class="page-header">Groups</h3>

            <div class="hr"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable nowrap"
                    data-resource="group"
                    id="dt-group">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Members</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop