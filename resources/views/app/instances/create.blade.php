@extends('layouts.main')
@include('layouts.partials.topmenu')

@section('content')
    @include('layouts.partials.sidebar-menu', ['resource'=>'instances'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances','title' => 'New Instance'])

        <form class="form-horizontal instance-form" method="POST" action="/{{$prefix}}/instances">
            <input name="_method" type="hidden" value="POST">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="form-group">
                <label class="control-label col-md-2" for="owner_email">Instance Owner</label>
                <div class="col-md-8">
                    <input id="owner_email"
                           name="owner_email"
                           class="form-control"
                           value="{{ \Input::old('owner_email') }}"
                           type="email"
                           required
                           maxlength="128"
                           placeholder="owner email address">
                    <p class="help-block">The email address of the user who will own the new instance</p>
                </div>
            </div>

            {{--<div class="form-group">--}}
            {{--<label class="control-label col-md-2" for="cluster_id">Provisioning Cluster</label>--}}
            {{--<div class="col-md-8">--}}
            {{--<select class="form-control" id="cluster_id" name="cluster_id" required>--}}
            {{--@foreach ($clusters as $_cluster)--}}
            {{--<option value="{{ $_cluster['cluster_id_text'] }}" {{ Input::old('cluster_id') == $_cluster['cluster_id_text'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>--}}
            {{--@endforeach--}}
            {{--</select>--}}
            {{--<p class="help-block">The cluster on which to provision this instance.</p>--}}
            {{--</div>--}}
            {{--</div>--}}

            <div class="form-group">
                <label class="control-label col-md-2" for="instance_name_text">Instance Name</label>
                <div class="col-md-8">
                    <input id="instance_name_text"
                           name="instance_name_text"
                           class="form-control"
                           placeholder="Instance Name"
                           type="text"
                           required
                           maxlength="64">
                    <p class="help-block">Instance names may contain only letters, numbers, and underscores.</p>
                </div>
            </div>

            <hr />
            <div class="form-group">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i>Create</button>
                </div>
            </div>
        </form>
    </div>
@stop
