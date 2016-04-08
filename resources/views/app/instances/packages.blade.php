@extends('layouts.main')
@include('layouts.partials.topmenu')

@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'instances'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances', 'title' => 'Packages'])

        @if(Session::has('flash_message'))
            <div class="alert {{ Session::get('flash_type') }}">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ Session::get('flash_message') }}
            </div>
        @endif

        <form class="form-horizontal instance-form" method="POST" enctype="multipart/form-data">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="form-group">
                <label for="package-list" class="col-md-2 control-label">{!! trans('common.package-list-label') !!}</label>

                <div class="col-md-8">
                    <select multiple="multiple"
                            name="package-list[]"
                            id="package-list"
                            size="5"
                            class="form-control @if(empty($packages)){{ 'disabled' }}@endif">
                        @if(!empty($packages))
                            @foreach($packages as $_index => $_package)
                                <option name="package-list-item-{{ $_index }}"
                                        @if(in_array($_package,$default_packages)) SELECTED @endif>{{ $_package }}</option>
                            @endforeach
                        @else
                            <option name="package-list-item-none" class="disabled">No packages</option>
                        @endif
                    </select>
                    {!! trans('common.package-list-help') !!}
                </div>
                <div class="col-md-2">
                    <button id="btn-make-default" type="submit" class="btn btn-primary btn-success btn-md" data-instance-action="make-default">
                        <i class="fa fa-fw {{ config('icons.star') }} fa-move-right"></i><span>{{ trans('common.make-default-button-text') }}</span>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="package-upload" class="col-md-2 control-label">{!! trans('common.package-upload-label') !!}</label>

                <div class="col-md-8">
                    <input type="file"
                           class="form-control"
                           name="package-upload"
                           id="package-upload">
                    {!! trans('common.package-upload-help') !!}
                </div>
                <div class="col-md-2">
                    <button id="btn-package-upload" type="submit" class="btn btn-primary btn-success btn-md" data-instance-action="package-upload">
                        <i class="fa fa-fw {{ config('icons.upload') }} fa-move-right"></i><span>{{ trans('common.package-upload-button-text') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop
