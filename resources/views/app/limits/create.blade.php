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
                                <option value="{{ $_cluster['cluster_id_text'] }}" {{ Input::old('cluster_id') == $_cluster['cluster_id_text'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="instance_name_text">Instance</label>
                        <select class="form-control"
                                id="instance_name_text"
                                name="instance_name_text"
                                disabled="disabled">
                            <option value>All Instances</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Services (optional)</label>

                        <div class="row">
                            <div class="col-md-1" style="margin-top: 7px; text-align: center;">
                                <input id="" class="" type="checkbox" disabled>
                            </div>
                            <div class="col-md-10 df-section df-section-3-round" df-fs-height="">
                                <df-manage-users class=""><div>
                                        <div class="">
                                            <df-section-header class="" data-title="'Manage Servers'">
                                                <div class="nav nav-pills dfe-section-header">
                                                    <h4 class="">Create Limit</h4>
                                                </div>
                                            </df-section-header>

                            <div class="col-md-11">
                                <select class="form-control" id="service_select" name="service_select" disabled>
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

                            <div class="row">
                                <div class="col-md-4">Maximum Requests:</div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" style="width: auto">
                                </div>
                            </div>

                        </div>
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
            $('.policy-form').on('change', '#cluster_id', function (e) {
                var $_select = $('#instance_name_text');
                var $_spinner = $('.label-spinner');
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

            //@todo what is this doing?
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('#limit_period').val(e.target.id);
            });
        });
    </script>
@stop
