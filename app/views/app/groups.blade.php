@extends('layouts.main')

@section('content')
    @include('app._page-header',array('pageName' => 'Groups', 'buttons' => array('new'=>array('icon'=>'plus','color'=>'success')) ) )

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