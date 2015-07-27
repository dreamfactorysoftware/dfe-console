@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'instances'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances','title' => 'New Instance'])

    <form class="instance-form" method="POST" action="/{{$prefix}}/instances">
        <input name="_method" type="hidden" value="POST">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <input name="limit_period" id="limit_period" type="hidden" value="min">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cluster_id">Cluster</label>
                    <select class="form-control" id="cluster_id" name="cluster_id">
                        <option value="0">All Clusters</option>
                        @foreach ($clusters as $_cluster)
                            <option value="{{ $_cluster['cluster_id_text'] }}" {{ Input::old('cluster_id') == $_cluster['cluster_id_text'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>
                        @endforeach
                    </select>
                </div>



                <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Name</label>
                    <input id="instance_name_text" class="form-control" placeholder="Enter instance name." type="name">
                </div>
                <div class="form-group">
                    <label>Cluster</label>
                    <select class="form-control" id="instance_cluster_select">
                        <option value="" disabled selected>Select Cluster</option>
                        @foreach ($clusters as $cluster)
                            <option id="{{$cluster['id']}}">{{$cluster['cluster_id_text']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Policy</label>
                    <select class="form-control" id="instance_policy_select">
                        <option value="" disabled selected>Select Policy</option>
                    </select>
                </div>



                <div class="form-group">
                    <label>Owner</label>
                    <input id="instance_ownername_text" class="form-control" value="{{ Auth::user()->email_addr_text }}" type="owner" disabled>
                </div>





            </div>


            <div class="col-md-6">

            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <hr>
                <div class="form-group">
                    <div class="">

                        <button type="button" class="btn btn-primary" onclick="javascript:save();">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>
@stop
