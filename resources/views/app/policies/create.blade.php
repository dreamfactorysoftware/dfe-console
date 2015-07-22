@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'servers'])

    <div class="col-md-10 df-section df-section-3-round">
        <div class="df-section-header" data-title="'Manage Servers'">
            <div class="nav nav-pills dfe-section-header">
                <h4 class="">Create Policy</h4>
            </div>
        </div>

        <form class="policy-form" method="POST" action="/{{$prefix}}/policies">
            <input name="_method" type="hidden" value="POST">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">
            <input name="limit_period" id="limit_period" type="hidden" value="min">

            <!--form class="" name="create-user"-->
            <div class="row">
                <div class="col-md-6">
                    <!--div class="form-group">
                        <label>Name</label>
                        <input id="server_id_text" name="server_id_text" class="form-control" placeholder="Enter server name." type="name" required>
                    </div-->

                    <div class="form-group">
                        <label for="cluster_id">Cluster</label>
                        <select class="form-control" id="cluster_id" name="cluster_id">
                            <option value>Select One...</option>
                            @foreach ($clusters as $_cluster)
                                <option value="{{ $_cluster['cluster_id_text'] }}" {{ Input::old('cluster_id') == $_cluster['cluster_id_text'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="instance_select">Instance</label>
                        <select class="form-control" id="instance_select" name="instance_select">
                            <option value>Select One...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Services (optional)</label>

                        <div class="row">
                            <div class="col-md-1" style="margin-top: 7px; text-align: center;">
                                <input id="" class="" type="checkbox" disabled>
                            </div>

                            <div class="col-md-11">
                                <select class="form-control" id="service_select" name="service_select" disabled>
                                    <option value="">Select user</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Users (optional)</label>

                        <div class="row">
                            <div class="col-md-1" style="margin-top: 7px; text-align: center;">
                                <input id="" class="" type="checkbox" disabled></td>
                            </div>

                            <div class="col-md-11">
                                <select class="form-control" id="user_select" name="user_select" disabled>
                                    <option value="">Select user</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Limits</label>

                        <div role="tabpanel">

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist" id="period">
                                <li role="presentation" class="active"><a href="#instance_limit_min"
                                                                          aria-controls="min"
                                                                          role="tab"
                                                                          data-toggle="tab"
                                                                          id="min">Minute</a></li>
                                <li role="presentation"><a href="#instance_limit_hour"
                                                           aria-controls="hour"
                                                           role="tab"
                                                           data-toggle="tab"
                                                           id="hour">Hour</a></li>
                                <li role="presentation"><a href="#instance_limit_day"
                                                           aria-controls="day"
                                                           role="tab"
                                                           data-toggle="tab"
                                                           id="day">Day</a></li>
                                <li role="presentation"><a href="#instance_limit_7day"
                                                           aria-controls="week"
                                                           role="tab"
                                                           data-toggle="tab"
                                                           id="week">7 Day</a></li>
                                <li role="presentation"><a href="#instance_limit_30day"
                                                           aria-controls="month"
                                                           role="tab"
                                                           data-toggle="tab"
                                                           id="month">30 Day</a></li>
                            </ul>

                            <div><br></div>
                            <div class="row">
                                <div class="col-md-4">
                                    &nbsp;&nbsp;Maximum Requests:
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" style="width: auto">
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <hr>
                    <div class="form-group">
                        <div class="">
                            <button type="button" class="btn btn-primary">Create</button>
                            &nbsp;&nbsp;
                            <button type="button" class="btn btn-default">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        jQuery(function ($) {
            $('.policy-form').on('change', '#cluster_id', function (e) {
                var $_select = $('#instance_select');
                var _clusterId = $('option:selected', this).val().toString();

                if (!_clusterId) {
                    alert('Invalid cluster selected.');
                    return false;
                }

                var _url = '/api/v1/ops/cluster/' + encodeURIComponent(_clusterId) + '/instances';

                $.get(_url).done(function (data) {
                    var _item;

                    $_select.empty().append('<option value>Select One...</option>');

                    $.each(data, function (item) {
                        var _id = ( item && item.hasOwnProperty('instance_name_text') ? item.instance_name_text : null );
                        $_select.append('<option value="' + _id + '">' + _id + '</option>');
                    });
                }).fail(function (xhr, status) {
                    $_select.empty().append('<option value>Reload Please!</option>');
                    alert('The current list of instances unavailable.\\n\\n' + '(' + status + ')');
                });
            });

            //@todo what is this doing?
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('#limit_period').val(e.target.id);
            });
        });
    </script>
@stop
