@extends('layouts.main')

@section('content')
    @include('app._page-header',array('pageName' => 'Users', 'buttons' => array('new'=>array('icon'=>'plus','color'=>'success')) ) )

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped table-hover table-heading table-datatable nowrap"
                    data-resource="service-user"
                    id="dt-service-user">
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