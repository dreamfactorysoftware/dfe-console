@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'limits'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'limits','title' => 'Create Limit'])

        <form class="policy-form" method="POST" action="/{{$prefix}}/limits">
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
                            <option value={{ \DreamFactory\Library\Utility\Enums\Limits::CLUSTER }} {{ Input::old('type_select') == \DreamFactory\Library\Utility\Enums\Limits::CLUSTER ? 'selected' : '' }}>Cluster</option>
                            <option value={{ \DreamFactory\Library\Utility\Enums\Limits::INSTANCE }} {{ Input::old('type_select') == \DreamFactory\Library\Utility\Enums\Limits::INSTANCE ? 'selected' : '' }}>Instance</option>
                            <option value={{ \DreamFactory\Library\Utility\Enums\Limits::USER }} {{ Input::old('type_select') == \DreamFactory\Library\Utility\Enums\Limits::USER ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_cluster" style="display: none;">
                        <label for="cluster_id">Cluster</label>
                        <select class="form-control" id="cluster_id" name="cluster_id">
                            <option value="">Select Cluster</option>
                            @foreach ($clusters as $_cluster)
                                <option value="{{ $_cluster['id'] }}">{{ $_cluster['cluster_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="select_instance" style="display: none;">
                        <label for="instance_id">Instance</label>
                        <select class="form-control" id="instance_id" name="instance_id">
                            <option value="">Select Instance</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_user" style="display: none;">
                        <label for="user_id">User</label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">Select User</option>
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
                            <label for="active_ind">Active</label>&nbsp;&nbsp;
                            <input type="checkbox" id="active_ind" name="active_ind" {{ Input::old('active_ind', false) ? 'checked' : null }}>
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
                            <button type="button" class="btn btn-default">Close</button>
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

            var cluster_id = '{{ Input::old('cluster_id') }}';
            var instance_id = '{{ Input::old('instance_id') }}';
            var user_id = '{{ Input::old('user_id') }}';

            if (_type !== '') {
                $("#type_select option:eq(" + _type + ")").prop('selected', true);
                $("#cluster_id option:eq(" + cluster_id + ")").prop('selected', true);
                $("#cluster_id").trigger('change');

                updateInstances(cluster_id, instance_id);
                updateUser(instance_id, user_id);
            }
        });


        jQuery(function ($) {
            var $_form = $('.policy-form');
            var $_spinner = $('.label-spinner');

            var cluster_id = '{{ Input::old('cluster_id') }}';
            var instance_id = '{{ Input::old('instance_id') }}';
            var user_id = '{{ Input::old('user_id') }}';

            //  Cluster selection
            $_form.on('change', '#cluster_id', function (e) {
                var _clusterId = $('option:selected', this).val().toString();
                updateInstances(_clusterId);
            });

            //  Instance selection
            $_form.on('change', '#instance_id', function (e) {
                if (({{ \DreamFactory\Library\Utility\Enums\Limits::USER }} == $('#type_select').find(':selected').val()) ||
                        ('{{ \DreamFactory\Library\Utility\Enums\Limits::USER }}' == '{{ Input::old('user_id') }}')) {
                        var _instanceId = $(':selected', this).val();
                        updateUser(_instanceId);
                }
            });
        });

        $('#type_select').on('change', function(){
            var selected = $('#type_select').val();
            generateForm(selected);
        });


        function updateInstances(cluster_id, instance_id) {
            var $_form = $('.policy-form');
            var $_spinner = $('.label-spinner');

            var $_select = $('#instance_id');
            var _clusterId = cluster_id;

            instance_id = instance_id || '';

            if ($('#type_select').val().toString() == '{{ \DreamFactory\Library\Utility\Enums\Limits::CLUSTER }}') {
                return false;
            }

            if (!_clusterId || 0 == _clusterId) {
                $_select.empty();
                $_select.append('<option value="">Select Instance</option>');
                $_select.append('<option value="0">Each Instance</option>');
                return false;
            }

            $_spinner.addClass('fa-spin').removeClass('hidden');

            $.get('/v1/cluster/' + encodeURIComponent(_clusterId) + '/instances').done(function (data) {
                $_select.empty();
                $_select.append('<option value="">Select Instance</option>');

                if(instance_id === '0') {
                    $_select.append('<option value="0" selected>Each Instance</option>');
                }
                else {
                    $_select.append('<option value="0">Each Instance</option>');
                }


                if ($.isArray(data) || data.length) {
                    $.each(data, function (index, item) {
                        if (instance_id == item.id) {
                            $_select.append('<option value="' + item.id + '" selected>' + item.name + '</option>');
                        }
                        else {
                            $_select.append('<option value="' + item.id + '">' + item.name + '</option>');
                        }
                    });

                    $_select.removeAttr('disabled').focus();
                }

            }).fail(function (xhr, status) {
                $_select.append('<option value="" selected="selected">Please Reload Page</option>').attr('disabled', 'disabled');
                alert('The current list of instances unavailable.\n\n' + '(' + status + ')');
            }).always(function () {
                $_spinner.removeClass('fa-spin').addClass('hidden');
            });
        }


        function updateUser(instance_id, user_id) {
            var $_form = $('.policy-form');
            var $_spinner = $('.label-spinner');
            var _instanceId = instance_id;

            user_id = user_id || '';

            if ($('#type_select').val().toString() == '{{ \DreamFactory\Library\Utility\Enums\Limits::INSTANCE }}') {
                return false;
            }

            if (!_instanceId || 0 == _instanceId) {
                $('#user_id').empty();
                $('#user_id').append('<option>Select User</option>');
                $('#user_id').append('<option value="0">Each User</option>');
                return false;
            }

            $_spinner.addClass('fa-spin').removeClass('hidden');

            $.get('/v1/instance/' + encodeURIComponent(_instanceId) + '/users').done(function (data) {
                var $_select = $('#user_id');
                $_select.empty();
                $_select.append('<option value="">Select User</option>');

                if(user_id === '0') {
                    $_select.append('<option value="0" selected>Each User</option>');
                }
                else {
                    $_select.append('<option value="0">Each User</option>');
                }

                if ($.isArray(data) || data.length) {
                    $.each(data, function (index, item) {
                        if (user_id == item.id) {
                                $_select.append('<option value="' + item.id + '" selected>' + item.name + '</option>');
                        }
                        else {
                                $_select.append('<option value="' + item.id + '">' + item.name + '</option>');
                        }
                    });

                    $_select.removeAttr('disabled').focus();
                }
            }).fail(function (xhr, status) {
                var $_select = $('#user_id');
                $_select.append('<option value="" selected="selected">Please Reload Page</option>').attr('disabled', 'disabled');
                alert('The current list of users is not available.\n\n' + '(' + status + ')');
            }).always(function () {
                $_spinner.removeClass('fa-spin').addClass('hidden');
            });
        }

        function generateForm(type) {
            var set_show = true;
            var types = [];
            types[{{ \DreamFactory\Library\Utility\Enums\Limits::CLUSTER }}] = 'cluster';
            types[{{ \DreamFactory\Library\Utility\Enums\Limits::INSTANCE }}] = 'instance';
            types[{{ \DreamFactory\Library\Utility\Enums\Limits::USER }}] = 'user';

            $('#type_select').find('> option').each(function() {
                if (set_show === true) {
                    $('#select_' + types[this.value]).show();

                    if (type == this.value) {
                        set_show = false;
                    }
                    $('#' + types[this.value] + '_id').trigger('change');
                }
                else {
                    $('#select_' + types[this.value]).hide();
                    $('#' + types[this.value] + '_id').val($('#' + types[this.value] + '_id option:first').val()).trigger('change');
                }
            });
        }

    </script>
@stop















