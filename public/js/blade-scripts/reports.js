/**
 * reports.js
 * scripts for the Reports tab
 */

jQuery(function ($) {
    var $_body = $(document.body);

    var changeDropdown = function($element) {
        var dropdown_span = '#selected_' + event.currentTarget.parentElement.id.replace('select_type_list_', '');
        var dropdown_text = event.currentTarget.textContent;

        $(dropdown_span).html(dropdown_text);
        $(dropdown_span).attr('value', event.currentTarget.id);
    };

    //$_body.on('change', '#select_time_period', function () {
    //    var selected = $('#select_time_period').val();
    //    console.log(selected);
    //});

    $_body.on('click', 'ul[id^="select_type_list_"] li', function (event) {
        var dropdown_span = '#selected_' + event.currentTarget.parentElement.id.replace('select_type_list_', '');
        var dropdown_text = event.currentTarget.textContent;

        $(dropdown_span).html(dropdown_text);
        $(dropdown_span).attr('value', event.currentTarget.id);
    });

    $_body.on('click', 'button[id^="instance_type_"]', function (event) {

        var selected_id = event.currentTarget.id;

        $("#chart_select  :button[id^='instance_type_']").each(function (i) {
            if (this.id != selected_id)
                $('#' + this.id).attr('class', 'btn btn-default btn-sm');
            else
                $('#' + this.id).attr('class', 'btn btn-default btn-sm btn-info');
        });

        $("#submit_instance").prop("disabled", false);
    });


    $_body.on('click', 'button[id^="submit_"]', function (event) {

        var id = event.currentTarget.id;
        var type = id.replace('submit_', '');

        var selected = $('#selected_' + type).html();
        var search_val = $('#selected_' + type).attr('value');
        var search_string = '';

        var period = $('#current_period_' + type).html();
        var date_start = $('#datepicker_from_' + type).val();
        var date_end = $('#datepicker_to_' + type).val();

        var timeperiod = convertPeriod(period, date_start, date_end);

        var search_type = '';
        var search_param = '';
        var search_field = '';
        var no_select = '';
        var search_additional = ''

        if (type === 'cluster') {
            search_type = 'API-Calls-Clusters';
            search_param = 'dfe.cluster_id';
            search_field = 'dfe.instance_id';
            no_select = 'Select Cluster and click Submit again.';
        }
        else if (type === 'instanceowner') {
            search_type = 'API-Calls-Instance-Owners';
            search_param = 'dfe.instance_owner_id';
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
                        search_field = 'dfe.instance_owner_id';
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
                alert('Select Chart Type (e.g. Endpoints)')
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

        var _chart = "//kibana.fabric.dreamfactory.com/#/visualize/edit/" + search_type + "?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:{{$type}}" + search_string + "%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:" + search_field + ",order:desc,orderBy:%272%27,size:15%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,index:%27logstash-*%27,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&" + timeperiod;

        // Good one - keep it
        //var _chart = "http://kibana.fabric.dreamfactory.com/#/visualize/edit/" + search_type + "?embed&_a=%28filters:!%28%29,linked:!f,query:%28query_string:%28analyze_wildcard:!t,query:%27_type:{{$type}}" + search_string + "%27%29%29,vis:%28aggs:!%28%28id:%272%27,params:%28%29,schema:metric,type:count%29,%28id:%274%27,params:%28field:" + search_field + ",order:desc,orderBy:%272%27,size:15%29,schema:group,type:terms%29,%28id:%273%27,params:%28extended_bounds:%28%29,index:%27logstash-*%27,field:%27@timestamp%27,interval:auto,min_doc_count:1%29,schema:segment,type:date_histogram%29%29,listeners:%28%29,params:%28addLegend:!t,addTooltip:!t,defaultYExtents:!f,mode:stacked,shareYAxis:!t%29,type:histogram%29%29&" + timeperiod;

        $('#iframe_chart').attr('src', _chart);
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
                $('#datepickers_' + type).show();
                //$('#datepicker_spacer_' + type).show();
                break;
            case 1:
                $('#current_period_' + type).html('Today');
                $('#datepickers_' + type).hide();
                //$('#datepicker_spacer_' + type).hide();
                break;
            case 7:
                $('#current_period_' + type).html('This Week');
                $('#datepickers_' + type).hide();
                //$('#datepicker_spacer_' + type).hide();
                break;
            case 30:
                $('#current_period_' + type).html('This Month');
                $('#datepickers_' + type).hide();
                //$('#datepicker_spacer_' + type).hide();
                break;
            case 365:
                $('#current_period_' + type).html('This Year');
                $('#datepickers_' + type).hide();
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

    var datepicker_from_instanceowner = $('#datepicker_from_instanceowner').pikaday(settings);

    $('#datepicker_from_instanceowner').click(function () {
        datepicker_from_instanceowner.pikaday('show');//.pikaday('nextMonth');
    });

    var datepicker_to_instanceowner = $('#datepicker_to_instanceowner').pikaday(settings);

    $('#datepicker_to_instanceowner').click(function () {
        datepicker_to_instanceowner.pikaday('show');//.pikaday('nextMonth');
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
});
