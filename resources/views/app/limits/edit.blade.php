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
                    @if(Session::has('flash_message'))
                        <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                    @endif
                    <div class="form-group">
                        <label for="label_text">Name</label>
                        <input type="text" class="form-control" id="label_text" name="label_text"
                        @if (Input::old('label_text')) value="{{ Input::old('label_text') }}" @else value="{{$limit['label_text'] or '' }}" @endif
                        >
                    </div>
                    <div class="form-group">
                        <label for="type_select">Type</label>
                        <select class="form-control" id="type_select" name="type_select">
                            <option value="cluster" {{ Input::old('type_select') == 'cluster' ? 'selected="selected"' : null }}>Cluster</option>
                            <option value="instance" {{ Input::old('type_select') == 'instance' ? 'selected="selected"' : null }}>Instance</option>
                            <option value="user" {{ Input::old('type_select') == 'user' ? 'selected="selected"' : null }}>User</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_cluster">
                        <label for="cluster_id">Cluster</label>
                        <select class="form-control" id="cluster_id" name="cluster_id">
                            @foreach ($clusters as $_cluster)
                                <option value="{{ $_cluster['id'] }}" {{ Input::old('cluster_id') == $limit['cluster_id'] ? 'selected="selected"' : null }} @if ($_cluster['id'] == $limit['cluster_id']) selected @endif>{{ $_cluster['cluster_id_text'] }}</option>
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
                                @foreach ($limitPeriods as $_periodName => $_period)
                                    <option value="{{ $_periodName }}" {{ Input::old('period_name') == $_periodName || $limit['period_name'] == $_periodName ? 'selected="selected"' : null }}>{{ $_periodName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="limit_nbr">Limit</label>
                            <input type="text" class="form-control" id="limit_nbr" name="limit_nbr" value="{{ $limit['limit_nbr'] }}">
                        </div>
                        <div>
                            <label for="active_ind">Active</label>&nbsp;&nbsp;
                            <input type="checkbox" id="active_ind" name="active_ind" @if ($limit['active_ind'] == '1') checked @endif>
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
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-default" onclick="closeCreate();">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>

        $(document.body).on('change', 'select', function (event) {

            var select = event.currentTarget.id;
            var _type = $('#type_select').val();

            if (select === 'type_select') {

                generateForm(_type);

                if ( _type === 'instance') {
                    var cluster_id = $('#cluster_id').val();
                    loadInstances(cluster_id, null);
                }

                if ( _type === 'user') {
                    var cluster_id = $('#cluster_id').val();
                    loadInstances(cluster_id, null);
                    var instance_id = $('#instance_id').val();
                    loadUsers(instance_id, null);
                }
            }

            if (select === 'cluster_id') {
                if (_type !== 'cluster') {
                    var cluster_id = $('#cluster_id').val();
                    loadInstances(cluster_id, null);
                }
            }

            if (select === 'instance_id') {
                if (_type !== 'instance') {
                    var instance_id = $('#instance_id').val();
                    loadUsers(instance_id, null);
                }
            }
        });


        $( document ).ready(function() {
            generateForm('{{$limit['type']}}');

            $('#type_select').val('{{$limit['type']}}');

            if ('{{$limit['type']}}' === 'instance') {
                loadInstances('{{$limit['cluster_id']}}', '{{$limit['instance_id']}}');
                $('#instance_id').val('{{$limit['instance_id']}}');
            }

            if ('{{$limit['type']}}' === 'user') {
                loadInstances('{{$limit['cluster_id']}}', '{{$limit['instance_id']}}');
                $('#instance_id').val('{{$limit['instance_id']}}');
                loadUsers('{{$limit['instance_id']}}', '{{$limit['user_id']}}');
            }

            $('#period_name').val('{{$limit['period_name']}}');
        });

        function generateForm(type) {
            var set_show = true;

            $('#type_select > option').each(function() {
                if (set_show === true) {
                    $('#select_' + this.value).show();
                    if (type === this.value) {
                        set_show = false;
                    }
                }
                else {
                    $('#select_' + this.value).hide();
                    $('#' + this.value + '_id').val($('#' + this.value + '_id option:first').val());
                }
            });
        }


        function loadInstances(clusterId, instanceId) {
            var $_spinner = $('.label-spinner');
            var $_select = $('#instance_id');
            var _clusterId = clusterId;

            if (!_clusterId || 0 == _clusterId) {
                $_select.empty();
                $_select.append('<option value="">Select Instance</option>');
                $_select.append('<option value="0">All Instances</option>');
                return false;
            }

            $_spinner.addClass('fa-spin').removeClass('hidden');

            $.get('/v1/cluster/' + encodeURIComponent(_clusterId) + '/instances').done(function (data) {
                $_select.empty();
                $_select.append('<option value="">Select Instance</option>');
                $_select.append('<option value="0">All Instances</option>');

                if ($.isArray(data)) {
                    $.each(data, function (index, item) {
                        var selected = '';
                        if (instanceId == item.id){
                            selected = 'selected';
                        }
                        $_select.append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
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


        function loadUsers(instanceId, userId) {
            var $_spinner = $('.label-spinner');
            var $_select = $('#user_id');
            var _instanceId = instanceId;

            if (!_instanceId || 0 == _instanceId) {
                $_select.empty();
                $_select.append('<option value="">Select User</option>');
                $_select.append('<option value="0">All Users</option>');
                return false;
            }

            $_spinner.addClass('fa-spin').removeClass('hidden');

            $.get('/v1/instance/' + encodeURIComponent(_instanceId) + '/users').done(function (data) {
                var $_select = $('#user_id');

                $_select.empty();
                $_select.append('<option value="">Select User</option>');
                $_select.append('<option value="0">All Users</option>');

                if ($.isArray(data)) {
                    $.each(data, function (index, item) {
                        var selected = '';
                        if (userId == item.id){
                            selected = 'selected';
                        }
                        $_select.append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
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

        function closeCreate(){
            window.location = '/{{$prefix}}/limits';
        }


    </script>
@stop
