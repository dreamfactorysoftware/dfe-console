@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.js"></script>

    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/reports">API Calls</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/reports/bandwidth">Bandwith</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="nav nav-pills dfe-section-header">
                    <h4 class="">API Calls</h4>
                </div>
            </div>
        </div>


        <div class="">
            <div class="row">
                <div class="col-xs-12">


                    <div role="tabpanel">

                        <ul class="nav nav-tabs" role="tablist" id="chart_type_tabs">
                            <li role="presentation" class="active"><a href="#clusters" aria-controls="clusters" role="tab" data-toggle="tab" id="chart_type_clusters">Clusters</a></li>
                            <li role="presentation"><a href="#instance_owners" aria-controls="instance_owners" role="tab" data-toggle="tab" id="chart_type_instances">Instance Owners</a></li>
                            <li role="presentation"><a href="#instances" aria-controls="instances" role="tab" data-toggle="tab" id="chart_type_instances">Instances</a></li>
                        </ul>

                        <div class="tab-content" id="chart_select">
                            <br>
                            <input type="hidden" id="chartType">
                            <input type="hidden" id="chartCategory">

                            <div role="tabpanel" class="tab-pane active" id="clusters">
                                <div class="well well-sm" style="height: 50px">

                                    <div class="pull-left" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="select_type_cluster" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <span id="selected_cluster" value="*">Select Cluster</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select_type_cluster" id="select_type_list_cluster">
                                                <li id="*"><a href="#">All Clusters</a></li>

                                                @foreach($clusters as $i => $cluster)
                                                    <li id="{{$cluster->id}}"><a href="#">{{$cluster->cluster_id_text}}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pull-left" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle btn-sm" type="button" id="select_time_period_clusters" data-toggle="dropdown" aria-expanded="false" style="width: 130px">
                                                <span id="current_period_cluster">Select Time Period</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select_time_period_clusters">
                                                <li><a href="javascript:selectPeriod(1, 'cluster')">Today</a></li>
                                                <li><a href="javascript:selectPeriod(7, 'cluster')">This Week</a></li>
                                                <li><a href="javascript:selectPeriod(30, 'cluster')">This Month</a></li>
                                                <li><a href="javascript:selectPeriod(365, 'cluster')">This Year</a></li>
                                                <li><a href="javascript:selectPeriod(0, 'cluster')">Custom Range</a></li>
                                            </ul>
                                        </div>
                                    </div>


                                    <div class="pull-left" id="datepicker_spacer_clusters"></div>

                                    <div id="datepickers_cluster" hidden="hidden">
                                        <div class="pull-left" id="datepicker_spacer1" style="width: 25px">&nbsp;</div>

                                        <div class="pull-left" role="group" id="datepickers">

                                            <div>
                                                <input type="text" id="datepicker_from_cluster" class="btn btn-default btn-sm" value="Date From">
                                                &nbsp;
                                                <input type="text" id="datepicker_to_cluster" class="btn btn-default btn-sm" value="Date To">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pull-left" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left">
                                        <button id="submit_cluster" type="button" class="btn btn-primary btn-sm">Submit</button>
                                        <!--button id="set_datespan" type="button" class="btn btn-primary btn-sm">Submit</button-->
                                    </div>

                                </div>

                                <!--button id="api_clusters_all" type="button" class="btn btn-default btn-sm btn-info">All Clusters</button-->
                            </div>
                            <div role="tabpanel" class="tab-pane" id="instance_owners">

                                <div class="well well-sm" style="height: 50px">

                                    <div class="pull-left" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="select_type_instanceowner" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <span id="selected_instanceowner">Select Instance Owner</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select_type_instanceowner" id="select_type_list_instanceowner">
                                                <!--li><a href="#">All Instance Owners</a></li-->

                                                @foreach($users as $i => $user)
                                                    <li><a href="#">{{$user->first_name_text}} {{$user->last_name_text}}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pull-left" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle btn-sm" type="button" id="select_time_period_instanceowners" data-toggle="dropdown" aria-expanded="false" style="width: 130px">
                                                <span id="current_period_instanceowners">Select Time Period</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select_time_period_instanceowners">
                                                <li><a href="javascript:selectPeriod(1, 'instanceowners')">Today</a></li>
                                                <li><a href="javascript:selectPeriod(7, 'instanceowners')">This Week</a></li>
                                                <li><a href="javascript:selectPeriod(30, 'instanceowners')">This Month</a></li>
                                                <li><a href="javascript:selectPeriod(365, 'instanceowners')">This Year</a></li>
                                                <li><a href="javascript:selectPeriod(0, 'instanceowners')">Custom Range</a></li>
                                            </ul>
                                        </div>
                                    </div>


                                    <div class="pull-left" id="datepicker_spacer_instanceowners"></div>

                                    <div id="datepickers_instanceowners" hidden="hidden">
                                        <div class="pull-left" id="datepicker_spacer1" style="width: 25px">&nbsp;</div>

                                        <div class="pull-left" role="group" id="datepickers">

                                            <div>
                                                <input type="text" id="datepicker_from" class="btn btn-default btn-sm" value="Date From">
                                                &nbsp;
                                                <input type="text" id="datepicker_to" class="btn btn-default btn-sm" value="Date To">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pull-left" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left">
                                        <button id="submit_instanceowner" type="button" class="btn btn-primary btn-sm">Submit</button>
                                        <!--button id="set_datespan" type="button" class="btn btn-primary btn-sm">Submit</button-->
                                    </div>
                                </div>

                                <!--button id="api_clusters_all" type="button" class="btn btn-default btn-sm btn-info">All Clusters</button-->
                            </div>
                            <div role="tabpanel" class="tab-pane" id="instances">
                                <button id="api_instances_endpoints" type="button" class="btn btn-default btn-sm">Endpoints</button>&nbsp;&nbsp;
                                <button id="api_instances_roles" type="button" class="btn btn-default btn-sm">Roles</button>&nbsp;&nbsp;
                                <button id="api_instances_ids" type="button" class="btn btn-default btn-sm">Instance Ids</button>&nbsp;&nbsp;
                                <button id="api_instances_applications" type="button" class="btn btn-default btn-sm">Applications</button>&nbsp;&nbsp;
                                <button id="api_instances_users" type="button" class="btn btn-default btn-sm">Users</button>&nbsp;&nbsp;

                                <div>
                                    <br>
                                </div>

                                <div class="well well-sm" style="height: 50px">


                                    <div class="pull-left" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="select_type_instance" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <span id="selected_instance">Select Instance</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select_type_instance" id="select_type_list_instance">
                                                <!--li><a href="#">All Clusters</a></li-->

                                                @foreach($instances as $i => $instance)
                                                    <li><a href="#">{{$instance->instance_id_text}}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pull-left" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left" role="group">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle btn-sm" type="button" id="select_time_period_instances" data-toggle="dropdown" aria-expanded="false" style="width: 130px">
                                                <span id="current_period_instances">Select Time Period</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select_time_period_instances">
                                                <li><a href="javascript:selectPeriod(1, 'instances')">Today</a></li>
                                                <li><a href="javascript:selectPeriod(7, 'instances')">This Week</a></li>
                                                <li><a href="javascript:selectPeriod(30, 'instances')">This Month</a></li>
                                                <li><a href="javascript:selectPeriod(365, 'instances')">This Year</a></li>
                                                <li><a href="javascript:selectPeriod(0, 'instances')">Custom Range</a></li>
                                            </ul>
                                        </div>
                                    </div>


                                    <div class="pull-left" id="datepicker_spacer_instances"></div>

                                    <div id="datepickers_instances" hidden="hidden">
                                        <div class="pull-left" id="datepicker_spacer1" style="width: 25px">&nbsp;</div>

                                        <div class="pull-left" role="group" id="datepickers">

                                            <div>
                                                <input type="text" id="datepicker_from" class="btn btn-default btn-sm" value="Date From">
                                                &nbsp;
                                                <input type="text" id="datepicker_to" class="btn btn-default btn-sm" value="Date To">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pull-left" style="width: 25px">&nbsp;</div>

                                    <div class="pull-left">
                                        <button id="submit_instance" type="button" class="btn btn-primary btn-sm">Submit</button>

                                    </div>


                                </div>


                            </div>

                        </div>

                    </div>
                    <iframe id="iframe_chart" frameborder="0" width="100%" height="100%"></iframe>
                    <!--iframe id="iframe_chart" src="http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-By-Clusters?embed&_g=(time:(from:now%2Fy,mode:quick,to:now))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))" frameborder="0" width="100%" height="100%"></iframe-->
                    <br><br>
                </div>
            </div>
        </div>
    </div>


    <link href="../static/plugins/pikaday/pikaday.css" rel="stylesheet">
    <script type="text/javascript" src="../static/plugins/pikaday/pikaday.js"></script>
    <script type="text/javascript" src="../static/plugins/pikaday/pikaday.jquery.js"></script>
    <script>

        $(document.body).on('change','#select_time_period',function(){

            var selected = $('#select_time_period').val();
            console.log(selected);

        });

        //http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-By-Clusters?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:fabric%20AND%20dfe.cluster_id:12%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:host,order:desc,orderBy:%272%27,size:10%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&_g=%28time:%28from:now%2Fy,mode:quick,to:now%29%29

        $(document.body).on('click', 'ul[id^="select_type_list_"] li', function (event) {

            var dropdown_span = '#selected_' + event.currentTarget.parentElement.id.replace('select_type_list_', '');
            var dropdown_text = event.currentTarget.textContent;

            $(dropdown_span).html(dropdown_text);
            $(dropdown_span).attr('value', event.currentTarget.id);

            //console.log(dropdown_span + ' = ' + dropdown_text);
            //console.log(event.currentTarget.parentElement.id + ' = ' + event.currentTarget.textContent);
        });

        $(document.body).on('click', 'button[id^="submit_"]', function (event) {

            var id = event.currentTarget.id;
            var type = id.replace('submit_', '');

            var selected = $('#selected_' + type).html();
            var search_val = $('#selected_' + type).attr('value');

            var period = $('#current_period_' + type).html();

            var date_start = $('#datepicker_from_' + type).val();
            var date_end = $('#datepicker_to_' + type).val();

            var timeperiod = convertPeriod(period, date_start, date_end);

            var search_type = '';
            var search_param = '';

            if (type === 'cluster')
            {
                search_type = 'Api-By-Clusters';
                search_param = 'dfe.cluster_id';
            }
            else
                return;

            var _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/" + search_type + "?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:{{$type}}%20AND%20" + search_param + ":" + search_val + "%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:host,order:desc,orderBy:%272%27,size:10%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&" + timeperiod;//_g=%28time:%28from:now%2Fy,mode:quick,to:now%29%29";
            $('#iframe_chart').attr('src', _chart);
        });


        function convertPeriod(period, date_from, date_to){

            var _from = 'now-30d';
            var _to = 'now';

            if(period === 'Today'){
                _from = moment().startOf('day').format();
                _to = moment().endOf('day').format();
            }

            if(period === 'This Week'){
                _from = moment().startOf('week').format();
                _to = moment().endOf('day').format();
            }

            if(period === 'This Month'){
                _from = moment().startOf('month').format();
                _to = moment().endOf('day').format();
            }

            if(period === 'This Year'){
                _from = moment().startOf('year').format();
                _to = moment().endOf('day').format();
            }

            if(period === 'Custom Range'){
                _from = moment(date_from, 'MM/DD/YYYY').startOf('day').format();
                _to = moment(date_to, 'MM/DD/YYYY').endOf('day').format();
            }

            return "_g=(time:(from:'" + _from + "',mode:quick,to:'" + _to + "'))";
        }


        function selectPeriod(period, type) {

            switch (period) {

                case 0:
                    $('#current_period_' + type).html('Custom Range');
                    $('#datepickers_' + type).show();
                    $('#datepicker_spacer_' + type).show();
                    break;
                case 1:
                    $('#current_period_' + type).html('Today');
                    $('#datepickers_' + type).hide();
                    $('#datepicker_spacer_' + type).hide();
                    break;
                case 7:
                    $('#current_period_' + type).html('This Week');
                    $('#datepickers_' + type).hide();
                    $('#datepicker_spacer_' + type).hide();
                    break;
                case 30:
                    $('#current_period_' + type).html('This Month');
                    $('#datepickers_' + type).hide();
                    $('#datepicker_spacer_' + type).hide();
                    break;
                case 365:
                    $('#current_period_' + type).html('This Year');
                    $('#datepickers_' + type).hide();
                    $('#datepicker_spacer_' + type).hide();
                    break;
                default:
                    break;

            }
        }

        var settings = {
            firstDay: 1,
            format: 'MM/DD/YYYY'
            //minDate: new Date('2000-01-01'),
            //maxDate: new Date('2020-12-31'),
            //yearRange: [2000,2020],
            //format: 'DD-MM-YY'
        };

        var datepicker_from = $('#datepicker_from').pikaday(settings);

        $('#datepicker_from').click(function(){
            datepicker_from.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_to = $('#datepicker_to').pikaday(settings);

        $('#datepicker_to').click(function(){
            datepicker_to.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_from_cluster = $('#datepicker_from_cluster').pikaday(settings);

        $('#datepicker_from_cluster').click(function(){
            datepicker_from_cluster.pikaday('show');//.pikaday('nextMonth');
        });

        var datepicker_to_cluster = $('#datepicker_to_cluster').pikaday(settings);

        $('#datepicker_to_cluster').click(function(){
            datepicker_to_cluster.pikaday('show');//.pikaday('nextMonth');
        });

        $('#set_datespan').click(function()
        {
            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                drawChart(getChartId(), 0, 0, '');
            else
                drawChart(getChartId(), d1.toISOString(), d2.toISOString(), '');
        });


        $('input[name="options"]').click( function() {
            alert($(this).val())
        })

    </script>


@stop