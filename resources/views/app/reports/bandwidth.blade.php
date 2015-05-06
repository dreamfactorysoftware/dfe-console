@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.0/js/bootstrap-datepicker.min.js"></script>

    <script>

        $(document).on('change', 'input:radio[id^="period_"]', function (event) {
            var period_id = event.currentTarget.id;
            drawChart(getChartId(), '', 0, 0, period_id);
        });


        $(document).on('click', 'button:button[id^="bandwidth_clusters_"]', function (event) {

            var selected_id = event.currentTarget.id;

            $( "#chart_select  :input[id^='bandwidth_clusters_']" ).each(function( i ) {
                if(this.id != selected_id)
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm');
                else
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm btn-info');
            });

            $('#chartType').val(selected_id);

            var selectedType = $('.selectedType').text();

            loadChartSelect(selected_id);

            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                drawChart(selected_id, '', selectedType, 0, 0, '');
            else
                drawChart(selected_id, '', selectedType, d1.toISOString(), d2.toISOString(), '');

        });


        $(document).on('click', 'button:button[id^="bandwidth_instances_"]', function (event) {

            var selected_id = event.currentTarget.id;

            $( "#chart_select  :input[id^='bandwidth_instances_']" ).each(function( i ) {
                if(this.id != selected_id)
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm');
                else
                    $('#' + this.id).attr('class', 'btn btn-default btn-sm btn-info');
            });

            $('#chartType').val(selected_id);

            var selectedType = $('.selectedType').text();

            loadChartSelect(selected_id);

            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                drawChart(selected_id, '', selectedType, 0, 0, '');
            else
                drawChart(selected_id, '', selectedType, d1.toISOString(), d2.toISOString(), '');

        });

        $(document).on('click', 'a[id^="chart_type_"]', function (event) {
            var selected_tab = event.currentTarget.id;
            var clear_target = '';

            if(selected_tab == 'chart_type_clusters'){
                $("#chartCategory").val('clusters');
                $('.selectedChart').text('Select Chart...');
                $("#datepicker_from").val('Date From');
                $("#datepicker_to").val('Date To');
                clear_target = 'bandwidth_instances_';
            }


            if(selected_tab == 'chart_type_instances'){
                $("#chartCategory").val('instances');
                $('.selectedChart').text('Select Chart...');
                $("#datepicker_from").val('Date From');
                $("#datepicker_to").val('Date To');
                clear_target = 'bandwidth_clusters_';
            }


            $( "#chart_select  :input[id^=" + clear_target + "]" ).each(function( i ) {
                $('#' + this.id).attr('class', 'btn btn-default btn-sm');
            });

        });

        $( document ).ready(function() {

            loadChartSelect('bandwidth_clusters_all');

            $('.dropdown-charts').delegate('li', 'click', function ()
            {
                var selected = $(this).text();
                var date_from = $('#datepicker_from').val();
                var date_to = $('#datepicker_to').val();

                var d1 = new Date(date_from);
                var d2 = new Date(date_to);

                $('.selectedChart').text(selected);// + ' <span class="caret"></span>');

                var chartType = $('#chartType').val();
                var selectedType = $('.selectedType').text();

                if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                    drawChart(chartType, selected, selectedType, 0, 0, '');
                else
                    drawChart(chartType, selected, selectedType, d1.toISOString(), d2.toISOString(), '');

            });


            $('.dropdown-charttypes').delegate('li', 'click', function ()
            {
                var selected = $(this).text();

                var date_from = $('#datepicker_from').val();
                var date_to = $('#datepicker_to').val();

                var d1 = new Date(date_from);
                var d2 = new Date(date_to);

                $('.selectedType').text(selected);// + ' <span class="caret"></span>');

                var chartType = $('#chartType').val();
                var selectedChart = $('.selectedChart').val();
                var selectedType = $('.selectedType').text();

                if(selectedChart == 'Select Chart...')
                    selectedChart = '';

                if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                    drawChart(chartType, selectedChart, selectedType, 0, 0, '');
                else
                    drawChart(chartType, selectedChart, selectedType, d1.toISOString(), d2.toISOString(), '');

            });

        });



        function loadChartSelect(id){

            var dd_array = [];

            if(id == 'bandwidth_clusters_all')
                dd_array = eval({!!$clusters!!});

        if(id == 'bandwidth_instances_endpoints')
            dd_array = eval({!!$endpoints!!});

        if(id == 'bandwidth_instances_roles')
            dd_array = eval({!!$roles!!});

        if(id == 'bandwidth_instances_ids')
            dd_array = eval({!!$instance_ids!!});

        if(id == 'bandwidth_instances_applications')
            dd_array = eval({!!$applications!!});

        if(id == 'bandwidth_instances_users')
            dd_array = eval({!!$users!!});

        $('.selectedChart').text('Select Chart...');

        var aList = $('ul#charts');
        aList.empty();

        $.each(dd_array, function(i)
        {
            aList.append('<li><a href=#>' + dd_array[i] + '</a></li>');
        });


        }

        function getPeriod(){

            var id = '';
            $( "#chart_period  input:radio[id^='period_']:checked" ).each(function( i ) {
                id = this.id;
            });

            return id;
        }


        function getChartId(){

            var chartId = '';

            $( "#chart_select  :input[id^='bandwidth_']" ).each(function( i ) {

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


        function drawChart(type, chart, charttypes, date_from, date_to, period){

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

            if((period === '') && (date_from !== 0) && (date_to !== 0)){
                _from = "'" + date_from + "'";
                _to = "'" + date_to + "'";
            }

            var ctype = charttypes.toLowerCase();//'histogram';

            if(type === 'bandwidth_clusters_all')
                if(chart)
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Clusters?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:%27cluster.id:%22" + chart + "%22%27,vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:1),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
                else
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Clusters?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";


            if(type === 'bandwidth_instances_applications'){
                if(chart)
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Applications?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:%27app_name:%22" + chart + "%22%27,vis:(aggs:!((id:'2',params:(),schema:metric,type:count),(id:'4',params:(field:app_name,order:desc,orderBy:'2',size:1),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
                else
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Applications?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'2',params:(),schema:metric,type:count),(id:'4',params:(field:app_name,order:desc,orderBy:'2',size:10),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
            }

            if(type === 'bandwidth_instances_endpoints'){
                if(chart)
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Endpoints?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:%27path_info.raw:%22" + chart + "%22%27,vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:path_info.raw,order:desc,orderBy:'1',size:1),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
                else
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Endpoints?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:path_info.raw,order:desc,orderBy:'1',size:10),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
            }

            if(type === 'bandwidth_instances_ids'){
                if(chart)
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Instance?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:%27dfe.instance_id:%22" + chart + "%22%27,vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:dfe.instance_id,order:desc,orderBy:'1',size:1),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
                else
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Instance?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:dfe.instance_id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
            }

            if(type === 'bandwidth_instances_users'){
                if(chart)
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-User-Name?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:%27user.email:%22" + chart + "%22%27,vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:user.email,order:desc,orderBy:'1',size:1),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
                else
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-User-Name?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:user.email,order:desc,orderBy:'1',size:5),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
            }

            if(type === 'bandwidth_instances_roles'){
                if(chart)
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-User-Roles?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:%27user.session.public.role:%22" + chart + "%22%27,vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:user.session.public.role,order:desc,orderBy:'1',size:1),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
                else
                    _chart = "http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-User-Roles?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:user.session.public.role,order:desc,orderBy:'1',size:10),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:" + ctype + "))";
            }

            $('#iframe_chart').attr('src', _chart);

        }


    </script>


    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="">
                    <a class="" href="/{{$prefix}}/reports">API Calls</a>
                </li>
                <li class="active">
                    <a class="" href="/{{$prefix}}/reports/bandwidth">Bandwith</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">
        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
                    <h4 class="">Bandwidth</h4>
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
                            <input type="hidden" id="chartType">
                            <input type="hidden" id="chartCategory">

                            <div role="tabpanel" class="tab-pane active" id="clusters">
                                <button id="bandwidth_clusters_all" type="button" class="btn btn-default btn-sm btn-info">All Clusters</button>&nbsp;&nbsp;
                            </div>
                            <div role="tabpanel" class="tab-pane" id="instances">
                                <button id="bandwidth_instances_endpoints" type="button" class="btn btn-default btn-sm">Endpoints</button>&nbsp;&nbsp;
                                <button id="bandwidth_instances_roles" type="button" class="btn btn-default btn-sm">Roles</button>&nbsp;&nbsp;
                                <button id="bandwidth_instances_ids" type="button" class="btn btn-default btn-sm">Instance Ids</button>&nbsp;&nbsp;
                                <button id="bandwidth_instances_applications" type="button" class="btn btn-default btn-sm">Applications</button>&nbsp;&nbsp;
                                <button id="bandwidth_instances_users" type="button" class="btn btn-default btn-sm">Users</button>&nbsp;&nbsp;
                            </div>
                        </div>

                        <br>

                        <div class="well well-sm" style="height: 50px">

                            <div class="btn-group pull-right" data-toggle="buttons" id="chart_period">

                            </div>

                            <div class="pull-left" role="group">
                                <div>
                                    <input type="text" id="datepicker_from" class="btn btn-default btn-sm" value="Date From">
                                    &nbsp;To&nbsp;
                                    <input type="text" id="datepicker_to" class="btn btn-default btn-sm" value="Date To">
                                    &nbsp;&nbsp;
                                    <button id="set_datespan" type="button" class="btn btn-default btn-sm">Go</button>
                                </div>
                            </div>

                            <div class="btn-group pull-left" role="group">
                                <div style="width: 50px">&nbsp; </div>
                            </div>

                            <div class="btn-group pull-left" role="group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle selectedChart" data-toggle="dropdown" aria-expanded="false">
                                    Select Chart...
                                </button>
                                <button class="btn btn-default btn-sm dropdown-toggle fa fa-caret-down" data-toggle="dropdown"></button>
                                <ul class="dropdown-menu dropdown-charts" role="menu" id="charts">

                                </ul>
                            </div>

                            <div class="btn-group pull-left" role="group">
                                <div style="width: 50px">&nbsp; </div>
                            </div>

                            <div class="btn-group pull-left" role="group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle selectedType" data-toggle="dropdown" aria-expanded="false" disabled="disabled">
                                    Histogram
                                </button>
                                <button class="btn btn-default btn-sm dropdown-toggle fa fa-caret-down" data-toggle="dropdown" disabled="disabled"></button>
                                <ul class="dropdown-menu dropdown-charttypes" role="menu">
                                    <li><a href="#">Histogram</a></li>
                                    <li><a href="#">Line</a></li>
                                </ul>
                            </div>
                        </div>

                        <br>

                    </div>
                    <iframe id="iframe_chart" src="http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Clusters?embed&_g=(time:(from:now%2Fy,mode:quick,to:now))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'*')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))" frameborder="0" width="100%" height="100%"></iframe>

                    <!--iframe src="{!! HTML::entities('http://kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Api-By-Clusters?embed&_g=%28time:%28from:now%2Fy,mode:quick,to:now%29%29&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27*%27%29%29,vis:%28aggs:!%28%28id:%271%27,params:%28%29,schema:metric,type:count%29,%28id:%272%27,params:%28field:path_info.raw,order:desc,orderBy:%271%27,size:10%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29') !!}" frameborder="0" width="100%" height="100%"></iframe-->
                    <br><br>

                </div>
            </div>
        </div>
    </div>

    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <link href="../../static/plugins/pikaday/pikaday.css" rel="stylesheet">
    <script type="text/javascript" src="../../static/plugins/pikaday/pikaday.js"></script>
    <script type="text/javascript" src="../../static/plugins/pikaday/pikaday.jquery.js"></script>
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

            var selectedChart = $('.selectedChart').text();
            var selectedType = $('.selectedType').text();

            if(selectedChart == 'Select Chart...')
                selectedChart = '';

            drawChart(getChartId(), selectedChart, selectedType, d1.toISOString(), d2.toISOString(), '');

        });

    </script>











@stop