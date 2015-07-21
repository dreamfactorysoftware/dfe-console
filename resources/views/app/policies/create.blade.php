@include('layouts.partials.topmenu',array('pageName' => 'Policies', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')




    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="ng-scope">
                        <div class="ng-scope">
                            <div class="col-md-2 df-sidebar-nav">
                                <df-sidebar-nav>
                                    <div class="">
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li class="">
                                                <a class="" href="{{ URL::action('Resources\\PolicyController@index') }}">Manage</a>
                                            </li>
                                            <li class="active">
                                                <a class="" href="{{ URL::action('Resources\\PolicyController@create') }}">Create</a>
                                            </li>
                                        </ul>
                                        <div class="hidden-lg hidden-md" id="sidebar-open">
                                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-fw fa-bars"></i></button>
                                        </div>

                                    </div>
                                </df-sidebar-nav>
                            </div>
                            <div class="col-md-10 df-section df-section-3-round" df-fs-height="">
                                <div>
                                    <div class="">
                                        <df-section-header class="" data-title="'Manage Servers'">
                                            <div class="nav nav-pills dfe-section-header">
                                                <h4 class="">Create Policy</h4>
                                            </div>
                                        </df-section-header>

                                        <form method="POST" action="/{{$prefix}}/policies">
                                            <input name="_method" type="hidden" value="POST">
                                            <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                            <input name="limit_period" id="limit_period" type="hidden" value="min">

                                            <!--form class="" name="create-user"-->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <!--div class="form-group">
                                                        <label>Name</label>
                                                        <input id="server_id_text" name="server_id_text" class="form-control" placeholder="Enter server name." type="name" required>
                                                    </div-->

                                                    <div class="form-group">
                                                        <label>Cluster</label>
                                                        <select class="form-control" id="cluster_select" name="cluster_select">
                                                            <option value="">Select cluster</option>
                                                            @foreach ($clusters as $cluster)
                                                                <option id="{{$cluster['id']}}" @if (Input::old('cluster_select') == $cluster['cluster_id_text']) selected="selected" @endif>{{$cluster['cluster_id_text']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Instance</label>
                                                        <select class="form-control" id="instance_select" name="instance_select">
                                                            <option value="">Select instance</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Services (optional)</label>
                                                        <select class="form-control" id="instance_select" name="instance_select">
                                                            <option value="">Select service</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Users (optional)</label>
                                                        <select class="form-control" id="instance_select" name="instance_select">
                                                            <option value="">Select user</option>
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Limits</label>
                                                        <div role="tabpanel">

                                                            <!-- Nav tabs -->
                                                            <ul class="nav nav-tabs" role="tablist" id="period">
                                                                <li role="presentation" class="active"><a href="#instance_limit_min" aria-controls="min" role="tab" data-toggle="tab" id="min">Minute</a></li>
                                                                <li role="presentation"><a href="#instance_limit_hour" aria-controls="hour" role="tab" data-toggle="tab" id="hour">Hour</a></li>
                                                                <li role="presentation"><a href="#instance_limit_day" aria-controls="day" role="tab" data-toggle="tab" id="day">Day</a></li>
                                                                <li role="presentation"><a href="#instance_limit_7day" aria-controls="week" role="tab" data-toggle="tab" id="week">7 Day</a></li>
                                                                <li role="presentation"><a href="#instance_limit_30day" aria-controls="month" role="tab" data-toggle="tab" id="month">30 Day</a></li>
                                                            </ul>

                                                            <div><br></div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    &nbsp;&nbsp;Maximum Requests:
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control" style="width: auto">
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>


                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <hr>
                                                    <div class="form-group">
                                                        <div class="">
                                                            <button type="button" class="btn btn-primary">Create</button>
                                                            &nbsp;&nbsp;
                                                            <button type="button" class="btn btn-default">
                                                                Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>



    $(document.body).on('change','#cluster_select',function() {

        var cluster_id = $(this).children(":selected").attr("id");

        $.get( "{!!   URL::action('Ops\\PolicyController@getInstances')!!}/" + cluster_id, function( data ) {

            $('#instance_select').empty();
            $('#instance_select').append("<option value=''>Select instance</option>");

            for ( var instance in data )
            {
                $('#instance_select').append("<option value='" + data[instance]['id'] + "'>" + data[instance]['instance_name_text'] + "</option>");
            }
        });


    });


    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('#limit_period').val(e.target.id);
    })


    </script>


@stop