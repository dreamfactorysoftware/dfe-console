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
                        <label for="label_text">Name</label>
                        <input type="text" class="form-control" id="label_text" name="label_text" value="{{ $limit['label_text'] }}">
                    </div>
                    <div class="form-group">
                        <label for="type_select">Type</label>
                        <select class="form-control" id="type_select" name="type_select">
                            <option value="cluster" >Cluster</option>
                            <option value="instance" >Instance</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_cluster" style="display: none;">
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
                            <option value="0">Select Instance</option>
                        </select>
                    </div>
                    <div class="form-group" id="select_user" style="display: none;">
                        <label for="user_id">User</label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">Select User</option>
                            <option value="">All User</option>
                        </select>
                    </div>
                    <div id="limit_settings">
                        <div class="form-group" id="select_period">
                            <label for="period_name">Period</label>
                            <select class="form-control" id="period_name" name="period_name">
                                <option value="Minute">Minute</option>
                                <option value="Hour">Hour</option>
                                <option value="Day">Day</option>
                                <option value="7 Days">7 Days</option>
                                <option value="30 Days">30 Days</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="limit_nbr">Limit</label>
                            <input type="number" class="form-control" id="limit_nbr" name="limit_nbr" value="{{ $limit['limit_nbr'] }}">
                        </div>
                        <div>
                            <label for="is_active">Active</label>&nbsp;&nbsp;
                            <input type="checkbox" id="is_active" name="is_active" @if ($limit['is_active'] == '1') checked @endif>
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
                            <button type="button" class="btn btn-default">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>




        $('#type_select').on('change', function(){

            var selected = $('#type_select').val();
            console.log(selected);

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


        if ('{{$limit['type']}}' === 'cluster') {
            $('#select_cluster').show();
            $('#select_instance').hide();
            $('#select_user').hide();
        }

        if ('{{$limit['type']}}' === 'instance') {
            $('#select_cluster').show();
            $('#select_instance').show();
            $('#select_user').hide();
            loadInstances('{{$limit['cluster_id']}}', '{{$limit['instance_id']}}');
            //$('#instance_id').val('{{$limit['instance_id']}}');
        }

        if ('{{$limit['type']}}' === 'user') {
            $('#select_cluster').show();
            $('#select_instance').show();
            $('#select_user').show();
            loadUsers('{{$limit['cluster_id']}}', '{{$limit['instance_id']}}', '{{$limit['instance_id']}}');
        }


        $( document ).ready(function() {

            $('#type_select').val('{{$limit['type']}}');
            $('#instance_id').val('{{$limit['instance_id']}}');
            $('#period_name').val('{{$limit['period_name']}}');

        });


        function loadInstances(clusterId, instanceId) {
            var $_spinner = $('.label-spinner');
            var $_select = $('#instance_id');
            var _clusterId = clusterId;//$('option:selected', this).val().toString();

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
                        var selected = '';
                        if (instanceId === item.id){
                            selected = 'selected';
                        }
                        $_select.append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                        console.log('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
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


        function loadUsers(clusterId, instanceId) {
            var $_spinner = $('.label-spinner');
            var $_select = $('#instance_id');
            var _clusterId = clusterId;//$('option:selected', this).val().toString();

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
                        var selected = '';
                        if (instanceId === item.id){
                            selected = 'selected';
                        }
                        $_select.append('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
                        console.log('<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>');
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

    </script>
@stop
