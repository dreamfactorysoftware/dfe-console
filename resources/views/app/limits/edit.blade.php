@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'limits'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'limits','title' => 'Edit Limit'])

        <form class="policy-form" method="POST" action="/{{$prefix}}/limits/{{$limit['id']}}">
            <input name="_method" type="hidden" value="PUT">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cluster_id">Cluster</label>
                        <select class="form-control" id="cluster_id" name="cluster_id">
                            <option value="0">All Clusters</option>
                            @foreach ($clusters as $_cluster)
                                <option value="{{ $_cluster['id'] }}" {{ Input::old('cluster_id') == $_cluster['id'] || $limit['cluster_id'] == $_cluster['id'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="instance_name_text">Instance</label>
                        <select class="form-control"
                                id="instance_id"
                                name="instance_id"
                                disabled="disabled">
                            <option value="0">All Instances</option>
                            @foreach($instances as $_instance)
                                <option value="{{ $_instance['id'] }}" {{ Input::old('instance_id') == $_instance['id'] || $limit['instance_id'] == $_instance['id'] ? 'selected="selected"' : null }}>{{ $_instance['instance_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_name">Service</label>
                        <select class="form-control"
                                id="service_name"
                                name="service_name"
                                disabled="disabled">
                            <option value="all">All Services</option>
                            @foreach($services as $_service)
                                <option value="{{ $_service['id'] }}" {{ Input::old('service_name') == $_service['id'] || $limit['service_name'] == $_service['id'] ? 'selected="selected"' : null }}>{{ $_service['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select class="form-control"
                                id="user_id"
                                name="user_id"
                                disabled="disabled">
                            <option value="0">All Users</option>
                            @foreach($users as $_user)
                                <option value="{{ $_user['id'] }}" {{ Input::old('user_id') == $_user['id'] || $limit['user_id'] == $_user['id'] ? 'selected="selected"' : null }}>{{ $_user['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <hr/>

                    <div class="form-group">
                        <label for="period_name">Time Period</label>
                        <select class="form-control"
                                id="period_name"
                                name="period_name">
                            @foreach ($limitPeriods as $_periodName => $_period)
                                <option value="{{ $_periodName }}" {{ Input::old('period_name') == $_periodName || $limit['period_name'] == $_periodName ? 'selected="selected"' : null }}>{{ $_periodName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="limit_nbr">Limit for Period</label>
                        <input type="number" class="form-control" id="limit_nbr" name="limit_nbr" value="{{ empty(Input::old('limit_nbr')) === false ? Input::old('limit_nbr') : $limit['limit_nbr'] }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <hr>
                    <div class="form-group">
                        <div class="">
                            <button type="button" class="btn btn-primary">Create</button>
                            <button type="button" class="btn btn-default">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
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

                if (!_instanceId || 0 == _instanceId) {
                    $_select.empty().append('<option value="all" selected="selected">All Services</option>').attr('disabled', 'disabled');
                    return false;
                }

                $_spinner.addClass('fa-spin').removeClass('hidden');

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

                $.get('/v1/instance/' + encodeURIComponent(_instanceId) + '/users').done(function (data) {
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
            });

            $_form.on('click', '.btn-primary', function (e) {
                $_form.submit();
            });
        });
    </script>
@stop
