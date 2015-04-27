@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/js/bootstrap-datepicker.min.js"></script>

    <script>

        $(document).on('change', 'input:radio[id^="period_"]', function (event) {
            var period_id = event.currentTarget.id;
            drawChart(getChartId(), 0, 0, period_id);
        });


        $(document).on('click', 'button:button[id^="api_clusters_"]', function (event) {

            var selected_id = event.currentTarget.id;

            $( "#chart_select  :input[id^='api_clusters_']" ).each(function( i ) {
                if(this.id != selected_id)
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm');
                else
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm btn-info');
            });

            drawChart(selected_id, 0, 0, getPeriod());
        });


        $(document).on('click', 'button:button[id^="api_instances_"]', function (event) {

            var selected_id = event.currentTarget.id;

            $( "#chart_select  :input[id^='api_instances_']" ).each(function( i ) {
                if(this.id != selected_id)
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm');
                else
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm btn-info');
            });

            drawChart(selected_id, 0, 0, getPeriod());

        });

        $(document).on('click', 'a[id^="chart_type_"]', function (event) {
            var selected_tab = event.currentTarget.id;
            var clear_target = '';

            if(selected_tab == 'chart_type_clusters')
                clear_target = 'api_instances_';

            if(selected_tab == 'chart_type_instances')
                clear_target = 'api_clusters_';

            $( "#chart_select  :input[id^=" + clear_target + "]" ).each(function( i ) {
                $('#' + this.id).attr('class', 'btn btn-default btn-sm');
            });


            //console.log(selected_id);
        });


        function getPeriod(){

            var id = '';
            $( "#chart_period  input:radio[id^='period_']:checked" ).each(function( i ) {
                id = this.id;
            });

            return id;
        }


        function getChartId(){

            var chartId = '';

            $( "#chart_select  :input[id^='api_']" ).each(function( i ) {

                var _class = $('#' + this.id).attr('class');

                if(_class.indexOf('btn-info') > -1){
                    chartId = this.id;
                }
            });

            return chartId;
        }


        function getChartType(){

            var tab = $('#chart_type_tabs .active').text();
            var chart_type = 'chart_type_' + tab.toLowerCase();

            return chart_type;
        }


        function drawChart(type, date_from, date_to, period){

            var _from = 'now/y';
            var _to = 'now';
            var _chart = '';

            if(period === 'period_day'){
                _from = 'now-24h';
                _to = 'now';
            }

            if(period === 'period_week'){
                _from = 'now-7d';
                _to = 'now';
            }

            if(period === 'period_month'){
                _from = 'now-1M';
                _to = 'now';
            }

            if(period === 'period_year'){
                _from = 'now/y';
                _to = 'now';
            }

            if(period === ''){
                _from = "'" + date_from + "'";
                _to = "'" + date_to + "'";
            }

            console.log(_from + ' - ' + _to);

            if(type === 'api_clusters_all')
                _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-By-Clusters?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";


            if(type === 'api_instances_applications')
                _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-calls-by-Applications?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'2',params:(),schema:metric,type:count),(id:'4',params:(field:app_name,order:desc,orderBy:'2',size:10),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";

            if(type === 'api_instances_endpoints')
                _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-calls-by-endpoints?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:path_info.raw,order:desc,orderBy:'1',size:10),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";



            if(type === 'api_instances_ids')
                _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-Calls-by-Instance-Id?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:dfe.instance_id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";


            if(type === 'api_instances_users')
                _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-Calls-by-User-Name?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:user.email,order:desc,orderBy:'1',size:5),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";

            if(type === 'api_instances_roles')
                _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-Calls-by-User-Roles?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:user.session.public.role,order:desc,orderBy:'1',size:10),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";

            $('#iframe_chart').attr('src', _chart);

        }


        //var picker = new Pikaday({ field: $('#datepicker')[0] });

    </script>


    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/reports">API Calls</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/reports">Bandwith</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
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
                            <li role="presentation"><a href="#instances" aria-controls="instances" role="tab" data-toggle="tab" id="chart_type_instances">Instances</a></li>
                        </ul>

                        <div class="tab-content" id="chart_select">
                            <br>
                            <div role="tabpanel" class="tab-pane active" id="clusters">
                                <button id="api_clusters_all" type="button" class="btn btn-default btn-sm btn-info">All Clusters</button>&nbsp;&nbsp;
                                <button id="api_clusters_1" type="button" class="btn btn-default btn-sm" disabled="disabled">Cluster One</button>&nbsp;&nbsp;
                                <button id="api_clusters_2" type="button" class="btn btn-default btn-sm" disabled="disabled">Cluster Two</button>&nbsp;&nbsp;
                            </div>
                            <div role="tabpanel" class="tab-pane" id="instances">
                                <button id="api_instances_endpoints" type="button" class="btn btn-default btn-sm">Endpoints</button>&nbsp;&nbsp;
                                <button id="api_instances_roles" type="button" class="btn btn-default btn-sm">Roles</button>&nbsp;&nbsp;
                                <button id="api_instances_ids" type="button" class="btn btn-default btn-sm">Instance Ids</button>&nbsp;&nbsp;
                                <button id="api_instances_applications" type="button" class="btn btn-default btn-sm">Applications</button>&nbsp;&nbsp;
                                <button id="api_instances_users" type="button" class="btn btn-default btn-sm">Users</button>&nbsp;&nbsp;
                                <button id="api_instances_owner" type="button" class="btn btn-default btn-sm" disabled="disabled">Owner</button>&nbsp;&nbsp;
                                <button id="api_instances_userendpoints" type="button" class="btn btn-default btn-sm" disabled="disabled">User Endpoints</button>&nbsp;&nbsp;
                                <button id="api_instances_userapplications" type="button" class="btn btn-default btn-sm" disabled="disabled">User Applications</button>&nbsp;&nbsp;

                            </div>
                        </div>

                        <br>

                                <div class="well well-sm">
                                    <div class="btn-group pull-right" data-toggle="buttons" id="chart_period">
                                        <!--label class="btn btn-default btn-sm">
                                            <input type="radio" name="options" id="period_day" autocomplete="off"> Day
                                        </label>
                                        <label class="btn btn-default btn-sm">
                                            <input type="radio" name="options" id="period_week" autocomplete="off"> Week
                                        </label>
                                        <label class="btn btn-default btn-sm">
                                            <input type="radio" name="options" id="period_month" autocomplete="off"> Month
                                        </label>
                                        <label class="btn btn-default btn-sm active">
                                            <input type="radio" name="options" id="period_year" autocomplete="off"> Year
                                        </label-->
                                    </div>



                                    <input type="text" id="datepicker_from" class="btn btn-default btn-sm" value="Date From">
                                    &nbsp;To&nbsp;
                                    <input type="text" id="datepicker_to" class="btn btn-default btn-sm" value="Date To">
                                    &nbsp;&nbsp;
                                    <button id="set_datespan" type="button" class="btn btn-default btn-sm">Go</button>


                                    <!--div class="btn-group" role="group">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            Chart Type
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="">Histogram</a></li>
                                            <li><a href="">Line Chart</a></li>
                                        </ul>
                                    </div-->
                                </div>

                        <br>











                    </div>
                    <iframe id="iframe_chart" src="http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-By-Clusters?embed&_g=(time:(from:now%2Fy,mode:quick,to:now))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))" frameborder="0" width="100%" height="100%"></iframe>

                    <!--iframe src="{!! HTML::entities('http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-By-Clusters?embed&_g=%28time:%28from:now%2Fy,mode:quick,to:now%29%29&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27*%27%29%29,vis:%28aggs:!%28%28id:%271%27,params:%28%29,schema:metric,type:count%29,%28id:%272%27,params:%28field:path_info.raw,order:desc,orderBy:%271%27,size:10%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29') !!}" frameborder="0" width="100%" height="100%"></iframe-->
                    <br><br>

                </div>
            </div>
        </div>
    </div>

    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <link href="../static/plugins/pikaday/pikaday.css" rel="stylesheet">
    <script type="text/javascript" src="../static/plugins/pikaday/pikaday.js"></script>
    <script type="text/javascript" src="../static/plugins/pikaday/pikaday.jquery.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
    <script>
        var settings = {
            firstDay: 1,
            minDate: new Date('2000-01-01'),
            maxDate: new Date('2020-12-31'),
            yearRange: [2000,2020],
            format: 'DD-MM-YY'
        };

        var $datepicker_from = $('#datepicker_from').pikaday(settings);

        $('#datepicker_from').click(function(){
            $datepicker_from.pikaday('show').pikaday('nextMonth');
        });

        var $datepicker_to = $('#datepicker_to').pikaday(settings);

        $('#datepicker_to').click(function(){
            $datepicker_to.pikaday('show').pikaday('nextMonth');
        });

        $('#set_datespan').click(function(){

            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            var diff = Math.abs(d2 -d1);


            diff = diff / (1000 * 60 * 60 * 24);

            console.log(diff + ' - ' + getChartId());

            drawChart(getChartId(), d1.toISOString(), d2.toISOString(), '');
            //var n = d1.toISOString();

        });

    </script>











@stop