@extends('layouts.main')
@include('layouts.partials.topmenu')

@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'instances'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances', 'title' => 'Manage Instances'])

            <!-- Tool Bar -->
        <div class="row">
            <div class="col-md-12">
                <div class="well well-sm">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" disabled="disabled" class="btn btn-default" id="_prev"><i class="fa fa-fw fa-backward"></i></button>

                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                                <span id="currentPage">Page 1</span>&nbsp;<span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="tablePages">

                            </ul>
                        </div>

                        <button type="button" disabled="disabled" class="btn btn-default btn-sm" id="_next"><i class="fa fa-fw fa-forward"></i></button>
                    </div>

                    <div class="btn-group btn-group-sm">
                        <input id="instanceSearch" class="form-control input-sm" value="" type="text" placeholder="search term">
                    </div>

                    <div class="btn-group btn-group-sm pull-right">
                        <button type="button"
                                id="refresh"
                                class="btn btn-default"
                                title="Reset sorting"><i class="fa fa-fw fa-refresh"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            @if(Session::has('flash_message'))
                <div class="alert {{ Session::get('flash_type') }}">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ Session::get('flash_message') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <table id="instanceTable"
                               class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-instance">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Owner Email</th>
                                <th>Cluster</th>
                                <th style="min-width: 150px;">Created</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($instances as $_instance)
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="hidden" id="instance_id" value="{{ $_instance->id }}">
                                        <a class="instance-link"
                                           target="_blank"
                                           href="{{ config('dfe.default-domain-protocol', \DreamFactory\Enterprise\Console\Enums\ConsoleDefaults::DEFAULT_DOMAIN_PROTOCOL) . '://' . $_instance->instance_id_text . '.' . data_get($_instance->instance_data_text,'env.default-domain') }}">
                                            {{ $_instance->instance_id_text }}
                                        </a>
                                    </td>
                                    <td>{{ $_instance->user->email_addr_text }}</td>
                                    <td>{{ $_instance->cluster->cluster_id_text }}</td>
                                    <td>{{ $_instance->create_date }}</td>
                                    <td>
                                        <form method="POST" action="/{{$prefix}}/limits/resetallcounters"
                                              id="reset_counter_{{ $_instance->id }}">
                                            <input type="hidden" name="instance_id" id="instance_id"
                                                   value="{{ $_instance->id }}">
                                            <input name="_method" type="hidden" value="DELETE">
                                            <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-bolt"
                                                    onclick="resetCounter('{{ $_instance->id }}', '{{ $_instance->instance_id_text }}')"
                                                    value="reset"
                                                    style="width: 25px; display: inline; vertical-align: middle"
                                                    data-toggle="tooltip" data-placement="right"
                                                    title="Reset all limit counters for this instance">
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <span id="tableInfo"></span>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/js/blade-scripts/instances/instances.js"></script>
@stop

