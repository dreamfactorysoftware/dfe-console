@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'limits'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'limits','title' => 'New Limit'])

        <form class="policy-form" method="POST" action="/{{$prefix}}/limits">
            <input name="_method" type="hidden" value="POST">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="row">
                <div class="col-md-6">
                    @if(Session::has('flash_message'))
                        <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                    @endif
                    <div class="form-group">
                        <label for="label_text">Name</label>
                        <input type="text" class="form-control" id="label_text" name="label_text" value="{{ Input::old('label_text') }}">
                    </div>
                    <div class="form-group">
                        <label for="type_select">Type</label>
                        <select class="form-control" id="type_select" name="type_select">
                            <option value="">Select type</option>
                            <option value="cluster" {{ Input::old('type_select') == 'cluster' ? 'selected' : '' }}>Cluster</option>
                            <option value="instance" {{ Input::old('type_select') == 'instance' ? 'selected' : '' }}>Instance</option>
                            <option value="user" {{ Input::old('type_select') == 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_cluster" style="display: none;">
                        <label for="cluster_id">Cluster</label>
                        <select class="form-control" id="cluster_id" name="cluster_id">
                            <option value="">Select Cluster</option>
                            @foreach ($clusters as $_cluster)
                                <option value="{{ $_cluster['id'] }}" {{ Input::old('cluster_id') == $_cluster['id'] ? 'selected' : '' }}>{{ $_cluster['cluster_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="select_instance" style="display: none;">
                        <label for="instance_id">Instance</label>
                        <select class="form-control" id="instance_id" name="instance_id">
                            <option value="0">Select Instance</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_user" style="display: none;">
                        <label for="user_id">User</label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">Select User</option>
                            <option value="0">All User</option>
                        </select>
                    </div>
                    <div id="limit_settings">
                        <div class="form-group" id="select_period">
                            <label for="period_name">Period</label>
                            <select class="form-control" id="period_name" name="period_name">
                                <option value="">Select Period</option>
                                <option value="Minute" {{ Input::old('period_name') == 'Minute' ? 'selected' : '' }}>Minute</option>
                                <option value="Hour" {{ Input::old('period_name') == 'Hour' ? 'selected' : '' }}>Hour</option>
                                <option value="Day" {{ Input::old('period_name') == 'Day' ? 'selected' : '' }}>Day</option>
                                <option value="7 Days" {{ Input::old('period_name') == '7 Days' ? 'selected' : '' }}>7 Days</option>
                                <option value="30 Days" {{ Input::old('period_name') == '30 Days' ? 'selected' : '' }}>30 Days</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="limit_nbr">Limit</label>
                            <input type="text" class="form-control" id="limit_nbr" name="limit_nbr" value="{{ Input::old('limit_nbr') }}">
                        </div>
                        <div>
                            <label for="is_active">Active</label>&nbsp;&nbsp;
                            <input type="checkbox" id="is_active" name="is_active">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">

                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <hr>
                    <div class="form-group">
                        <div class="">
                            <button type="submit" class="btn btn-primary">Create</button>
                            <button type="button" class="btn btn-default" onclick="closeCreate();">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>

        $( document ).ready(function() {

            var _type = '{{ Input::old('type_select') }}';

            generateForm(_type);



        });


        function generateForm(type) {

            if (type === '') {
                $('#select_cluster').hide();
                $('#select_instance').hide();
                $('#select_user').hide();
            }

            if (type === 'cluster') {
                $('#select_cluster').show();
                $('#select_instance').hide();
                $('#select_user').hide();
            }

            if (type === 'instance') {
                $('#select_cluster').show();
                $('#select_instance').show();
                $('#select_user').hide();
            }

            if (type === 'user') {
                $('#select_cluster').show();
                $('#select_instance').show();
                $('#select_user').show();
            }
        }


        $('#type_select').on('change', function(){

            var selected = $('#type_select').val();

            if (selected === '') {
                $('#select_cluster').hide();
                $('#select_instance').hide();
                $('#select_user').hide();
            }

            if (selected === 'cluster') {
                $('#select_cluster').show();
                $('#select_instance').hide();
                $('#select_user').hide();
            }

            if (selected === 'instance') {
                $('#select_cluster').show();
                $('#select_instance').show();
                $('#select_user').hide();
            }

            if (selected === 'user') {
                $('#select_cluster').show();
                $('#select_instance').show();
                $('#select_user').show();
            }

        });

        jQuery(function ($) {
            var $_form = $('.policy-form');
            var $_spinner = $('.label-spinner');

            //  Cluster selection
            $_form.on('change', '#cluster_id', function (e) {
                var $_select = $('#instance_id');
                var _clusterId = $('option:selected', this).val().toString();

                if (!_clusterId || 0 == _clusterId) {
                    $_select.empty().append('<option value="0" selected="selected">All Instances</option>').attr('disabled', 'disabled');
                    return false;
                }

                $_spinner.addClass('fa-spin').removeClass('hidden');

                $.get('/v1/cluster/' + encodeURIComponent(_clusterId) + '/instances').done(function (data) {
                    var _item;

                    if (!$.isArray(data)) {
                        $_select.empty();
                        $_select.append('<option value="" selected="selected">No Instances</option>').attr('disabled', 'disabled');
                    } else {
                        $.each(data, function (index, item) {
                            $_select.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });

                        $_select.removeAttr('disabled').focus();
                    }
                }).fail(function (xhr, status) {
                    $_select.append('<option value="" selected="selected">Please Reload Page</option>').attr('disabled', 'disabled');
                    alert('The current list of instances unavailable.\n\n' + '(' + status + ')');
                }).always(function () {
                    $_spinner.removeClass('fa-spin').addClass('hidden');
                });
            });

            //  Instance selection
            $_form.on('change', '#instance_id', function (e) {
                var $_select = $('#service_name');
                var _instanceId = $('option:selected', this).val().toString();
                var _type = $('#type_select option:selected').val().toString();

                $_spinner.addClass('fa-spin').removeClass('hidden');
/*

                if (!_instanceId || 0 == _instanceId) {
                    $_select.empty().append('<option value="all" selected="selected">All Services</option>').attr('disabled', 'disabled');
                    return false;
                }

                $.get('/v1/instance/' + encodeURIComponent(_instanceId) + '/services').done(function (data) {
                    var _item;

                    if (!$.isArray(data)) {
                        $_select.empty();
                        $_select.append('<option value="" selected="selected">No Services</option>').attr('disabled', 'disabled');
                    } else {
                        $.each(data, function (index, item) {
                            $_select.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });

                        $_select.removeAttr('disabled').focus();
                    }
                }).fail(function (xhr, status) {
                    $_select.append('<option value="" selected="selected">Please Reload Page</option>').attr('disabled', 'disabled');
                    alert('The current list of services is not available.\n\n' + '(' + status + ')');
                }).always(function () {
                    $_spinner.removeClass('fa-spin').addClass('hidden');
                });
*/
                if (_type === 'user') {

                    $.get('/v1/instance/' + encodeURIComponent(_instanceId) + '/users').done(function (data) {
                        //if()

                        var _item, $_select = $('#user_id');

                        if (!$.isArray(data)) {
                            $_select.empty();
                            $_select.append('<option value="" selected="selected">No Users</option>').attr('disabled', 'disabled');
                        } else {
                            $.each(data, function (index, item) {
                                $_select.append('<option value="' + item.id + '">' + item.name + '</option>');
                            });

                            $_select.removeAttr('disabled').focus();
                        }
                    }).fail(function (xhr, status) {
                        $_select.append('<option value="" selected="selected">Please Reload Page</option>').attr('disabled', 'disabled');
                        alert('The current list of users is not available.\n\n' + '(' + status + ')');
                    }).always(function () {
                        $_spinner.removeClass('fa-spin').addClass('hidden');
                    });
                }
            });
/*
            $_form.on('click', '.btn-primary', function (e) {
                $_form.submit();
            });
*/
        });

        function closeCreate(){
            window.location = '/{{$prefix}}/limits';
        }
    </script>
@stop
