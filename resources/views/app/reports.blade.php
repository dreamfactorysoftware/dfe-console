@extends('layouts.main')
@include('layouts.partials.topmenu',['pageName' => 'Reports', 'prefix' => $prefix])

@section('user-scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.js"></script>
@show

@section('content')
    <div class="col-md-2 df-sidebar-nav">
        <!--div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/reports">API Calls</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/reports/bandwidth">Bandwith</a>
                </li>
            </ul>
        </div-->
    </div>

    <div class="col-md-10">
        <div class="nav nav-pills dfe-section-header">
            <h4 class="">{{ $report_title }}</h4>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div role="tabpanel">
                    <ul class="nav nav-tabs" role="tablist" id="chart_type_tabs">
                        <li role="presentation" class="active"><a href="#clusters" aria-controls="clusters" role="tab"
                                                                  data-toggle="tab"
                                                                  id="chart_type_clusters">Clusters</a></li>
                        <li role="presentation"><a href="#instance_owners" aria-controls="instance_owners" role="tab"
                                                   data-toggle="tab" id="chart_type_instances">Users</a></li>
                        <li role="presentation"><a href="#instances" aria-controls="instances" role="tab"
                                                   data-toggle="tab" id="chart_type_instances">Instances</a></li>
                    </ul>

                    <div class="tab-content" id="chart_select">
                        <br>
                        <input type="hidden" id="chartType">
                        <input type="hidden" id="chartCategory">

                        <div role="tabpanel" class="tab-pane active" id="clusters">
                            <div class="well well-menu">
                                <div class="pull-left" role="group">
                                    <div class="dropdown">
                                        <button class="btn btn-default btn-sm dropdown-toggle" type="button"
                                                id="select_type_cluster" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="true">
                                            <span id="selected_cluster" value="*">Select Cluster</span>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="select_type_cluster"
                                            id="select_type_list_cluster">
                                            @foreach($clusters as $i => $cluster)
                                                <li id="{{$cluster->cluster_id_text}}"><a
                                                            href="#">{{$cluster->cluster_id_text}}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="pull-left" role="group">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle btn-sm" type="button"
                                                id="select_time_period_clusters" data-toggle="dropdown"
                                                aria-expanded="false" style="width: 130px">
                                            <span id="current_period_cluster">Select Time Period</span>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="select_time_period_clusters">
                                            <li><a href="javascript:selectPeriod(1, 'cluster')" data-period="1"
                                                   data-type="cluster">Today</a></li>
                                            <li><a href="javascript:selectPeriod(7, 'cluster')" data-period="7"
                                                   data-type="cluster">This Week</a></li>
                                            <li><a href="javascript:selectPeriod(30, 'cluster')" data-period="30"
                                                   data-type="cluster">This Month</a></li>
                                            <li><a href="javascript:selectPeriod(365, 'cluster')" data-period="365"
                                                   data-type="cluster">This Year</a></li>
                                            <li><a href="javascript:selectPeriod(0, 'cluster')" data-period="0"
                                                   data-type="cluster">Custom Range</a></li>
                                        </ul>
                                    </div>
                                </div>


                                <div id="datepicker_cluster" hidden="hidden">
                                    <div class="pull-left" role="group" id="datepicker">
                                        <div>
                                            <input type="text" id="datepicker_from_cluster"
                                                   class="btn btn-default btn-sm" value="Date From">
                                            &nbsp;
                                            <input type="text" id="datepicker_to_cluster" class="btn btn-default btn-sm"
                                                   value="Date To">
                                        </div>
                                    </div>
                                </div>

                                <div class="pull-left">
                                    <button id="submit_cluster" type="button" class="btn btn-primary btn-sm">Submit
                                    </button>
                                </div>

                            </div>

                        </div>
                        <div role="tabpanel" class="tab-pane" id="instance_owners">

                            <div class="well well-sm" style="height: 50px">

                                <div class="pull-left" role="group">
                                    <div class="dropdown">
                                        <button class="btn btn-default btn-sm dropdown-toggle" type="button"
                                                id="select_type_user" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="true">
                                            <span id="selected_user" value="*">Select Instance Owner</span>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="select_type_user"
                                            id="select_type_list_user">

                                            @foreach($users as $i => $user)
                                                <li id="{{$user->email_addr_text}}"><a
                                                            href="#">{{$user->first_name_text}} {{$user->last_name_text}}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="pull-left" style="width: 25px">&nbsp;</div>

                                <div class="pull-left" role="group">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle btn-sm" type="button"
                                                id="select_time_period_users" data-toggle="dropdown"
                                                aria-expanded="false" style="width: 130px">
                                            <span id="current_period_user">Select Time Period</span>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="select_time_period_users">
                                            <li><a href="javascript:selectPeriod(1, 'user')">Today</a></li>
                                            <li><a href="javascript:selectPeriod(7, 'user')">This Week</a></li>
                                            <li><a href="javascript:selectPeriod(30, 'user')">This Month</a>
                                            </li>
                                            <li><a href="javascript:selectPeriod(365, 'user')">This Year</a>
                                            </li>
                                            <li><a href="javascript:selectPeriod(0, 'user')">Custom Range</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div id="datepicker_user" hidden="hidden">
                                    <div class="pull-left" id="datepicker_spacer1" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left" role="group" id="datepicker">

                                        <div>
                                            <input type="text" id="datepicker_from_user"
                                                   class="btn btn-default btn-sm" value="Date From">
                                            &nbsp;
                                            <input type="text" id="datepicker_to_user"
                                                   class="btn btn-default btn-sm" value="Date To">
                                        </div>
                                    </div>
                                </div>

                                <div class="pull-left" style="width: 25px">&nbsp;</div>

                                <div class="pull-left">
                                    <button id="submit_user" type="button" class="btn btn-primary btn-sm">
                                        Submit
                                    </button>
                                </div>
                            </div>

                        </div>
                        <div role="tabpanel" class="tab-pane" id="instances">
                            <button id="instance_type_endpoints" type="button" class="btn btn-default btn-sm btn-info">
                                Endpoints
                            </button>
                            &nbsp;&nbsp;
                            <!--button id="instance_type_roles" type="button" class="btn btn-default btn-sm" disabled>Roles</button>&nbsp;&nbsp;-->
                            <button id="instance_type_applications" type="button" class="btn btn-default btn-sm">
                                Applications
                            </button>
                            &nbsp;&nbsp;
                            <button id="instance_type_users" type="button" class="btn btn-default btn-sm">Users</button>
                            &nbsp;&nbsp;

                            <div>
                                <br>
                            </div>

                            <div class="well well-sm" style="height: 50px">

                                <div class="pull-left" role="group">
                                    <div class="dropdown">
                                        <button class="btn btn-default btn-sm dropdown-toggle" type="button"
                                                id="select_type_instance" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="true">
                                            <span id="selected_instance" value="*">Select Instance</span>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="select_type_instance"
                                            id="select_type_list_instance">

                                            @foreach($instances as $i => $instance)
                                                <li id="{{$instance->instance_id_text}}"><a
                                                            href="#">{{$instance->instance_id_text}}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="pull-left" style="width: 25px">&nbsp;</div>

                                <div class="pull-left" role="group">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle btn-sm" type="button"
                                                id="select_time_period_instances" data-toggle="dropdown"
                                                aria-expanded="false" style="width: 130px">
                                            <span id="current_period_instance">Select Time Period</span>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="select_time_period_instances">
                                            <li><a href="javascript:selectPeriod(1, 'instance')">Today</a></li>
                                            <li><a href="javascript:selectPeriod(7, 'instance')">This Week</a></li>
                                            <li><a href="javascript:selectPeriod(30, 'instance')">This Month</a></li>
                                            <li><a href="javascript:selectPeriod(365, 'instance')">This Year</a></li>
                                            <li><a href="javascript:selectPeriod(0, 'instance')">Custom Range</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div id="datepicker_instance" hidden="hidden">
                                    <div class="pull-left" id="datepicker_spacer1" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left" role="group" id="datepicker">

                                        <div>
                                            <input type="text" id="datepicker_from_instance"
                                                   class="btn btn-default btn-sm" value="Date From">
                                            &nbsp;
                                            <input type="text" id="datepicker_to_instance"
                                                   class="btn btn-default btn-sm" value="Date To">
                                        </div>
                                    </div>
                                </div>

                                <div class="pull-left" style="width: 25px">&nbsp;</div>

                                <div class="pull-left">
                                    <button id="submit_instance" type="button" class="btn btn-primary btn-sm">Submit
                                    </button>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>
                <iframe id="iframe_chart" seamless="seamless" frameborder="0" width="100%" height="60%"></iframe>
                <!--iframe id="iframe_chart" src="http://kibana.enterprise.dreamfactory.com:/#/visualize/edit/Api-By-Clusters?embed&_g=(time:(from:now%2Fy,mode:quick,to:now))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$report_index_type}}')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))" frameborder="0" width="100%" height="100%"></iframe-->
                <br><br>
            </div>
        </div>
    </div>


    <link href="/static/plugins/pikaday/pikaday.css" rel="stylesheet">
    <script type="text/javascript" src="/static/plugins/pikaday/pikaday.js"></script>
    <script type="text/javascript" src="/static/plugins/pikaday/pikaday.jquery.js"></script>
    <script>
        $(document.body).on('change', '#select_time_period', function () {
            var selected = $('#select_time_period').val();
            console.log(selected);
        }).on('click', 'ul[id^="select_type_list_"] li', function (event) {
            var dropdown_span = '#selected_' + event.currentTarget.parentElement.id.replace('select_type_list_', '');
            var dropdown_text = event.currentTarget.textContent;

            $(dropdown_span).html(dropdown_text);
            $(dropdown_span).attr('value', event.currentTarget.id);
        }).on('click', 'button[id^="instance_type_"]', function (event) {
            var selected_id = event.currentTarget.id;

            $("#chart_select").find(":button[id^='instance_type_']").each(function (i) {
                if (this.id != selected_id)
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm');
                else
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm btn-info');
            });

            $("#submit_instance").prop("disabled", false);
        }).on('click', 'button[id^="submit_"]', function (event) {

            var id = event.currentTarget.id;
            var type = id.replace('submit_', '');
            var $selectedType = $('#selected_' + type);

            var selected = $selectedType.html();
            var search_val = $selectedType.attr('value');
            var search_string = '';

            var period = $('#current_period_' + type).html();
            var date_start = $('#datepicker_from_' + type).val();
            var date_end = $('#datepicker_to_' + type).val();

            var timeperiod = convertPeriod(period, date_start, date_end);

            var search_type = '';
            var search_param = '';
            var search_field = '';
            var no_select = '';
            var search_additional = '';

            if (type === 'cluster') {
                search_type = 'API-Calls-Clusters';
                search_param = 'dfe.cluster_id';
                search_field = 'dfe.instance_id';
                no_select = 'Select Cluster and click Submit again.';
            }
            else if (type === 'user') {
                search_type = 'API-Calls-Instance-Owners';
                search_param = 'dfe.owner_email_address';
                search_field = 'app_name';
                no_select = 'Select Instance Owner and click Submit again.';
            }
            else if (type === 'instance') {
                var chart_type = get_selected_instance_type();

                if (chart_type !== '') {
                    switch (chart_type) {
                        case 'instance_type_endpoints':
                            search_type = 'API-Calls-Endpoints';
                            search_param = 'dfe.instance_id';
                            search_field = 'path_info.raw';
                            break;
                        case 'instance_type_users':
                            search_type = 'API-Calls-Users';
                            search_param = 'dfe.instance_id';
                            search_field = 'dfe.owner_email_address';
                            break;
                        case 'instance_type_applications':
                            search_type = 'API-Calls-Applications';
                            search_param = 'dfe.instance_id';
                            search_field = 'app_name';
                            search_additional = '%20NOT%20app_name:admin';
                            break;
                        case 'instance_type_roles':
                            search_type = 'Api-Calls-by-User-Roles';
                            search_param = 'dfe.instance_id';
                            search_field = 'user.cached.role.name';
                            break;
                        default:
                            break;
                    }

                    no_select = 'Select Instance and click Submit again.';
                }
                else {
                    alert('Select Chart Type (e.g. Endpoints)');
                    return;
                }
            }
            else
                return;


            if (search_val !== '*') {
                search_string = "%20AND%20" + search_param + ":" + search_val + search_additional;
            }
            else {
                alert(no_select);
                return;
            }

            var _uri = "{!! $report_host !!}{!! $report_query !!}";

            _uri = _uri
                    .replace('{search_type}', search_type)
                    .replace('{search_string}', search_string)
                    .replace('{search_field}', search_field) + '&' + timeperiod;

            //kibana.enterprise.dreamfactory.com/#/visualize/edit/" + search_type + "?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:{{$report_index_type}}" + search_string + "%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:" + search_field + ",order:desc,orderBy:%272%27,size:15%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,index:%27logstash-*%27,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&" + timeperiod;
            // Good one - keep it
            //var _chart = "http://kibana.enterprise.dreamfactory.com/#/visualize/edit/" + search_type + "?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:{{$report_index_type}}" + search_string + "%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:" + search_field + ",order:desc,orderBy:%272%27,size:15%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,index:%27logstash-*%27,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&" + timeperiod;
            $('#iframe_chart').attr('src', _uri);
        });

        function convertPeriod(period, date_from, date_to) {

            var _from = 'now-30d';
            var _to = 'now';

            if (period === 'Today') {
                _from = moment().startOf('day').format();
                _to = moment().endOf('day').format();
            }

            if (period === 'This Week') {
                _from = moment().startOf('week').format();
                _to = moment().endOf('day').format();
            }

            if (period === 'This Month') {
                _from = moment().startOf('month').format();
                _to = moment().endOf('day').format();
            }

            if (period === 'This Year') {
                _from = moment().startOf('year').format();
                _to = moment().endOf('day').format();
            }

            if (period === 'Custom Range') {
                _from = moment(date_from, 'MM/DD/YYYY').startOf('day').format();
                _to = moment(date_to, 'MM/DD/YYYY').endOf('day').format();
            }

            if (_from === 'now-30d') {
                return "_g=(time:(from:'" + _from + "',mode:quick,to:'" + _to + "'))";
            }
            else {
                return "_g=(time:(from:'" + _from + "',mode:absolute,to:'" + _to + "'))";
            }

        }

        function selectPeriod(period, type) {

            switch (period) {

                case 0:
                    $('#current_period_' + type).html('Custom Range');
                    $('#datepicker_' + type).show();
                    //$('#datepicker_spacer_' + type).show();
                    break;
                case 1:
                    $('#current_period_' + type).html('Today');
                    $('#datepicker_' + type).hide();
                    //$('#datepicker_spacer_' + type).hide();
                    break;
                case 7:
                    $('#current_period_' + type).html('This Week');
                    $('#datepicker_' + type).hide();
                    //$('#datepicker_spacer_' + type).hide();
                    break;
                case 30:
                    $('#current_period_' + type).html('This Month');
                    $('#datepicker_' + type).hide();
                    //$('#datepicker_spacer_' + type).hide();
                    break;
                case 365:
                    $('#current_period_' + type).html('This Year');
                    $('#datepicker_' + type).hide();
                    //$('#datepicker_spacer_' + type).hide();
                    break;
                default:
                    break;

            }
        }

        var settings = {
            firstDay: 1,
            format:   'MM/DD/YYYY'
            //minDate: new Date('2000-01-01'),
            //maxDate: new Date('2020-12-31'),
            //yearRange: [2000,2020],
            //format: 'DD-MM-YY'
        };

        var datepicker_from = $('#datepicker_from').pikaday(settings);

        $('#datepicker_from').click(function () {
            datepicker_from.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_to = $('#datepicker_to').pikaday(settings);

        $('#datepicker_to').click(function () {
            datepicker_to.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_from_cluster = $('#datepicker_from_cluster').pikaday(settings);

        $('#datepicker_from_cluster').click(function () {
            datepicker_from_cluster.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_to_cluster = $('#datepicker_to_cluster').pikaday(settings);

        $('#datepicker_to_cluster').click(function () {
            datepicker_to_cluster.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_from_user = $('#datepicker_from_user').pikaday(settings);

        $('#datepicker_from_user').click(function () {
            datepicker_from_user.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_to_user = $('#datepicker_to_user').pikaday(settings);

        $('#datepicker_to_user').click(function () {
            datepicker_to_user.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_from_instance = $('#datepicker_from_instance').pikaday(settings);

        $('#datepicker_from_instance').click(function () {
            datepicker_from_instance.pikaday('show');
        });

        var datepicker_to_instance = $('#datepicker_to_instance').pikaday(settings);

        $('#datepicker_to_instance').click(function () {
            datepicker_to_instance.pikaday('show');
        });


        function get_selected_instance_type() {
            var result = '';
            $("#chart_select  :button[id^='instance_type_']").each(function (i) {

                var asd = $('#' + this.id).attr('class');

                if (asd.indexOf('btn-info') > -1) {
                    result = this.id;
                }
            });

            return result;
        }


    </script>

@stop
