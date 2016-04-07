@extends('layouts.main')
@include('layouts.partials.topmenu')

@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'instances'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances', 'title' => 'Instance Settings'])

        <form class="form-horizontal instance-form" method="POST" action="/{{$prefix}}/instances/settings">
            <input name="_method" type="hidden" value="POST">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="form-group">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-fw fa-save"></i>Create</button>
                </div>
            </div>
        </form>
    </div>
@stop
