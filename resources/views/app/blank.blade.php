@extends('layouts.main')

{{-- @formatter:off --}}
@section('page-title')
The title
@overwrite
{{-- @formatter:on --}}

@section('content')
    The content
@stop

@section( 'after-body-scripts' )
    @parent
    any scripts to add
@stop
