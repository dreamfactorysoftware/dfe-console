@include('layouts.partials.topmenu',array('pageName' => 'Reports', 'prefix' => $prefix))

@extends('layouts.main')


@section('content')

    <script>

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

            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                drawChart(selected_id, 0, 0, '');
            else
                drawChart(selected_id, d1.toISOString(), d2.toISOString(), '');

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

            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            if((d1 == 'Invalid Date') || (d2 == 'Invalid Date'))
                drawChart(selected_id, 0, 0, '');
            else
                drawChart(selected_id, d1.toISOString(), d2.toISOString(), '');

        });

        $(document).on('click', 'a[id^="chart_type_"]', function (event) {
            var selected_tab = event.currentTarget.id;
            var clear_target = '';

            if(selected_tab == 'chart_type_clusters'){
                $("#chartCategory").val('clusters');
                $("#datepicker_from").val('Date From');
                $("#datepicker_to").val('Date To');
                clear_target = 'bandwidth_instances_';
            }


            if(selected_tab == 'chart_type_instances'){
                $("#chartCategory").val('instances');
                $("#datepicker_from").val('Date From');
                $("#datepicker_to").val('Date To');
                clear_target = 'bandwidth_clusters_';
            }

            $( "#chart_select  :input[id^=" + clear_target + "]" ).each(function( i ) {
                $('#' + this.id).attr('class', 'btn btn-default btn-sm');
            });
        });


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

            if((period === '') && (date_from !== 0) && (date_to !== 0)){
                _from = "'" + date_from + "'";
                _to = "'" + date_to + "'";
            }

            if(type === 'bandwidth_clusters_all')
            {
                _chart = "//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Clusters?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(field:content_length),schema:metric,type:sum),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'4',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";
            }

            if(type === 'bandwidth_instances_applications')
            {
                _chart = "//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Applications?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(field:content_length),schema:metric,type:sum),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:app_name,order:desc,orderBy:'1',size:15),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";
            }

            if(type === 'bandwidth_instances_endpoints')
            {
                _chart = "//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Endpoints?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(field:content_length),schema:metric,type:sum),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:path_info.raw,order:desc,orderBy:'1',size:5),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))"
            }

            if(type === 'bandwidth_instances_ids')
            {
                _chart = "//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Instance?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(field:content_length),schema:metric,type:sum),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:dfe.instance_id,order:desc,orderBy:'1',size:5),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";
            }

            if(type === 'bandwidth_instances_users')
            {
                _chart = "//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-User-Name?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(field:content_length),schema:metric,type:sum),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:user.email,order:desc,orderBy:'1',size:5),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";
            }

            if(type === 'bandwidth_instances_roles')
            {
                _chart = "//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-User-Roles?embed&_g=(time:(from:" + _from + ",mode:quick,to:" + _to + "))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(field:content_length),schema:metric,type:sum),(id:'2',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram),(id:'3',params:(field:user.session.public.role,order:desc,orderBy:'1',size:15),schema:group,type:terms)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))";
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
                <div class="nav nav-pills dfe-section-header">
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
                        </div>

                        <br>

                    </div>
                    <iframe id="iframe_chart" src="//kibana.fabric.dreamfactory.com:5601/#/visualize/edit/Bandwidth-by-Clusters?embed&_g=(time:(from:now%2Fy,mode:quick,to:now))&_a=(filters:!(),linked:!f,query:(query_string:(analyze_wildcard:!t,query:'_type:{{$type}}')),vis:(aggs:!((id:'1',params:(),schema:metric,type:count),(id:'2',params:(field:cluster.id,order:desc,orderBy:'1',size:5),schema:group,type:terms),(id:'3',params:(extended_bounds:(),field:'@timestamp',interval:auto,min_doc_count:1),schema:segment,type:date_histogram)),listeners:(),params:(addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t),type:histogram))" frameborder="0" width="100%" height="100%"></iframe>

                    <br><br>

                </div>
            </div>
        </div>
    </div>

    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <link href="../../static/plugins/pikaday/pikaday.css" rel="stylesheet">
    <script type="text/javascript" src="../../static/plugins/pikaday/pikaday.js"></script>
    <script type="text/javascript" src="../../static/plugins/pikaday/pikaday.jquery.js"></script>
    <script>
        var settings = {
            firstDay: 1
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

        $('#set_datespan').click(function(){

            var date_from = $('#datepicker_from').val();
            var date_to = $('#datepicker_to').val();

            var d1 = new Date(date_from);
            var d2 = new Date(date_to);

            drawChart(getChartId(), d1.toISOString(), d2.toISOString(), '');

        });
    </script>



@stop