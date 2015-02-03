@extends('layouts.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="pull-left"><h3 class="page-header">Roles</h3></div>

            <div class="page-header-toolbar pull-right">
                <button class="btn btn-info btn-sm" id="toolbar-new"><i class="fa fa-fw fa-plus"></i></button>
            </div>

            <div class="hr"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable nowrap"
                   data-resource="role"
                   id="dt-role">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Last Modified</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop