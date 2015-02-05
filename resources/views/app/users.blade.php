@extends('layouts.main')

@section('content')
    @include('app._page-header',array('pageName' => 'Users', 'buttons' => array('new'=>array('icon'=>'plus','color'=>'success')) ) )

    <div class="row">
        <div class="col-md-12">
            <table class="table table-compact table-bordered table-striped table-hover table-heading table-datatable nowrap"
                   data-resource="service-user"
                   id="dt-service-user">
                <thead>
                    <tr>
                        <th data-column-name="id">ID</th>
                        <th data-column-name="first_name_text">First Name</th>
                        <th data-column-name="last_name_text">Last Name</th>
                        <th data-column-name="email_addr_text">Email Address</th>
                        <th data-column-name="lmod_date">Last Modified</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop