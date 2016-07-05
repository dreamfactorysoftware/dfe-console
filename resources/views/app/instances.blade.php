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
                        <span id="searchclear" class="glyphicon glyphicon-remove-circle" style="display:none;"></span>
                    </div>

                    <div class="btn-group btn-group-md pull-right">
                        <button type="button"
                                id="refresh"
                                class="btn btn-default"
                                title="Refresh and reset sorting"><i class="fa fa-refresh"></i></button>
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
                        <table id="instanceTable"
                               class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-instance" >
                            <thead style="width:100%">
                            <tr>
                                <th>Name</th>
                                <th>Owner Email</th>
                                <th>Cluster</th>
                                <th style="min-width: 150px;">Created</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" style="text-align: center;"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></td>
                                </tr>

                            </tbody>
                        </table>
                    <span id="tableInfo"></span>
                </div>
            </div>
        </div>
    </div>
    <div style="display:none" class="frm_template">
        <form method="POST" action="/v1/limits/resetallcounters" id="reset_counter_x">
            <input type="hidden" name="instance_id" id="instance_id" value="">
            <input type="hidden" name="instance_id_text" id="instance_id_text" value="">
            <input name="_method" type="hidden" value="DELETE">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-default reset_counters"
                        value="reset"
                        data-toggle="tooltip" data-placement="right"
                        title="Reset all limit counters for this instance"><i class="fa fa-fw fa-bolt"></i>
                </button>
                <button type="button" class="btn btn-default delete_instance"
                        data-toggle="tooltip" data-placement="right"
                        title="Deprovision"><i class="fa fa-fw fa-trash"></i>
                </button>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        var protocol = '<?= config('dfe.default-domain-protocol', \DreamFactory\Enterprise\Console\Enums\ConsoleDefaults::DEFAULT_DOMAIN_PROTOCOL); ?>';
    </script>
    <script type="text/javascript" src="/js/blade-scripts/common.js"></script>

    <script type="text/javascript" src="/js/blade-scripts/instances/instances.js"></script>
    <script type="text/javascript" src="/static/plugins/bartaz/jquery.highlight.js"></script>

@stop
