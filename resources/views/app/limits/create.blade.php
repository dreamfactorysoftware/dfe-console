@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'limits'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'limits','title' => 'New Limit'])

        <form class="policy-form" method="POST" action="/{{$prefix}}/limits">
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
                                <option value="{{ $_cluster['id'] }}" {{ Input::old('cluster_id') == $_cluster['id'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="instance_name_text">Instance</label>
                        <select class="form-control"
                                id="instance_id"
                                name="instance_id"
                                disabled="disabled">
                            <option value>All Instances</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_id">Service</label>
                        <select class="form-control"
                                id="service_id"
                                name="service_id"
                                disabled="disabled">
                            <option value>All Services</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select class="form-control"
                                id="user_id"
                                name="user_id"
                                disabled="disabled">
                            <option value>All Users</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <hr/>

                    <div class="form-group">
                        <label for="period_nbr">Time Period</label>
                        <select class="form-control"
                                id="period_nbr"
                                name="period_nbr">
                            @foreach ($limitPeriods as $_periodName => $_period)
                                <option value="{{ $_period }}" {{ Input::old('period_nbr') == $_period ? 'selected="selected"' : null }}>{{ $_periodName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="limit_nbr">Limit for Period</label>
                        <input type="number" class="form-control" id="limit_nbr" name="limit_nbr">
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

                    $_select.empty();

                    if (!$.isArray(data)) {
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
                var $_select = $('#service_id');
                var _instanceId = $('option:selected', this).val().toString();

                if (!_instanceId || 0 == _instanceId) {
                    $_select.empty().append('<option value="0" selected="selected">All Services</option>').attr('disabled', 'disabled');
                    return false;
                }

                $_spinner.addClass('fa-spin').removeClass('hidden');

                $.get('/v1/instance/' + encodeURIComponent(_instanceId) + '/services').done(function (data) {
                    var _item;

                    $_select.empty();

                    if (!$.isArray(data)) {
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

                    $_select.empty();

                    if (!$.isArray(data)) {
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
        });
    </script>
@stop
