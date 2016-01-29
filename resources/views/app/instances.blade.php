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
                    <div class="btn-group btn-group-sm">
                        <button type="button"
                                disabled="disabled"
                                class="btn btn-default"
                                id="_prev"
                                style="height: 30px;"><i class="fa fa-fw fa-backward"></i></button>

                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <span id="currentPage" style="margin-right: 10px;">Page 1</span><span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="tablePages"></ul>
                        </div>

                        <button type="button"
                                disabled="disabled"
                                class="btn btn-default"
                                id="_next"
                                style="height: 30px;"><i class="fa fa-fw fa-forward"></i></button>
                    </div>

                    <div class="btn-group btn-group-sm">
                        <input id="instanceSearch" class="form-control input-sm" value="" type="text" placeholder="Search Instances...">
                    </div>

                    <div class="btn-group btn-group-md pull-right">
                        <button type="button" id="refresh" class="btn btn-default btn-primary" title="Refresh Page"><i class=" fa fa-fw fa-refresh"></i>
                        </button>
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
                               class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-instance"
                               style="table-layout: fixed; width: 100%; display:none">
                            <thead>
                            <tr>
                                <th></th>
                                <th style="min-width: 100px">Name</th>
                                <th style="min-width: 100px">Cluster</th>
                                <th style="min-width: 150px">Owner Email</th>
                                <th style="min-width: 100px">Last Modified</th>
                                <th style="width: 50px">Actions</th>
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
                                    <td>{{ $_instance->cluster->cluster_id_text }}</td>
                                    <td>{{ $_instance->user->email_addr_text }}</td>
                                    <td style="width: 185px">{{ $_instance->lmod_date }}</td>
                                    <td>
                                        <form method="POST" action="/{{$prefix}}/limits/resetallcounters"
                                              id="reset_counter_{{ $_instance->id }}">
                                            <input type="hidden" name="instance_id" id="instance_id"
                                                   value="{{ $_instance->id }}">
                                            <input name="_method" type="hidden" value="DELETE">
                                            <input name="_token" type="hidden" value="{{ csrf_token() }}">

                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-default"
                                                        onclick="resetCounter('{{ $_instance->id }}', '{{ $_instance->instance_id_text }}')"
                                                        value="reset"
                                                        data-toggle="tooltip" data-placement="right"
                                                        title="Reset all limit counters for this instance"><i class="fa fa-fw fa-bolt"></i>
                                                </button>
                                                <button type="button" class="btn btn-default"
                                                        onclick="deleteInstance('{{ $_instance->id }}', '{{ $_instance->instance_id_text }}');"
                                                        data-toggle="tooltip" data-placement="right"
                                                        title="Deprovision"><i class="fa fa-fw fa-trash"></i>
                                                </button>
                                            </div>
                                        </form>

                                    </td>
                                </tr>

                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    <span id="tableInfo"></span>
                    <br><br><br><br>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="/js/blade-scripts/instances/instances.js"></script>
@stop

