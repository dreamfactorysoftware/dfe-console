/**
 * cerberus.dashboard.js
 * Dashboard scripts
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 */

//*************************************************************************
//* Globals
//*************************************************************************

var _dashboardOptions = {

	timelineSize:    30,
	facility_filter: null,
	updateInterval:  30000,
	chart:           null
};

/**
 * @param name
 * @param x
 * @param y
 * @returns {string}
 * @private
 */
var _formatDate = function(name, x, y) {
	return '<b>' + name + '</b><br/>' + Highcharts.dateFormat('%A, %b %e, %Y', x) + '<br/>' + Highcharts.numberFormat(y, 0);
};

var _chart = function(selector, name, yAxisName, which, facility, size) {
	$(selector).highcharts({
							   chart:       {
								   type:     'spline',
								   zoomType: 'x'
							   },
							   title:       {
								   text: name
							   },
							   subtitle:    {
								   text: document.ontouchstart === undefined ? 'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
							   },
							   xAxis:       {
								   type: 'datetime'
							   },
							   yAxis:       {
								   title: {
									   text: yAxisName
								   }
							   },
							   plotLines:   [{
												 value: 0,
												 width: 1,
												 color: '#808080'
											 }],
							   tooltip:     {
								   formatter: function() {
									   return _formatDate(this.series.name, this.x, this.y);
								   }
							   },
							   legend:      {
								   enabled: false
							   },
							   exporting:   {
								   enabled: false
							   },
							   plotOptions: {
								   area: {
									   fillColor: {
										   linearGradient: {
											   x1: 0,
											   y1: 0,
											   x2: 0,
											   y2: 1
										   },
										   stops:          [[0,
															 Highcharts.getOptions().colors[0]],
															[1,
															 Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]]
									   },
									   marker:    {
										   radius: 2
									   },
									   lineWidth: 1,
									   states:    {
										   hover: {
											   lineWidth: 1
										   }
									   },
									   threshold: null
								   }
							   },
							   series:      [{
												 type:          'area',
												 pointInterval: 1000 * 60 * 60,
												 pointStart:    Date.UTC(2006, 0, 1),
												 name:          name,
												 data:          _getData(which, facility, size)
											 }]
						   });
};

/**
 * @param which
 * @param facility
 * @param size
 * @returns {Array}
 * @private
 */
var _getData = function(which, facility, size) {
	var _data = [];

	var _payload = {
		raw:      1,
		size:     size || 0,
		interval: 'day',
		facility: facility || null,
		which:    which || null
	};

	$.ajax({
			   url:      '/dashboard/logs',
			   type:     'POST',
			   data:     _payload,
			   dataType: 'json',
			   async:    false, //			processData: false,
			   success:  function(json, statusText, xhr) {
				   json.facets.published_on.entries.forEach(function(element, index, array) {
					   _data.push([element.time, element.count]);
				   });
			   },
			   error:    function(xhr, message, error) {
				   console.error("Error while loading data from server", message);
				   throw(error);
			   }
		   });

	return _data;
};

/**
 * DocReady
 */
$(function() {
	var _update = function() {
		$.ajax({
				   url:      '/dashboard/globalStats',
				   type:     'GET',
				   dataType: 'json',
				   success:  function(json, statusText, xhr) {
					   if (json && json.success) {
						   $('#db_user_count').html(json.details._users_total);
						   $('#db_dsp_count_live').html(json.details._active_total);
						   $('#db_dsp_count_dead').html(json.details._inactive_total);
						   $('#db_dsp_database_tables').html(json.details._database_tables_system);
						   $('#db_dsp_apps').html(json.details._apps_total - json.details._apps_system);
						   $('li#disk_usage .bar').css({width: (500 / (json.details.disk_usage.available / 1024000000) ) + '%'});
						   $('li#disk_usage .stat').html(json.details.disk_usage.available);
					   }
					   return true;
				   },
				   error:    function(xhr, message, error) {
					   console.error("Error while loading data from server", message);
					   throw(error);
				   }
			   });

		setTimeout(_update, _dashboardOptions.updateInterval);
	};

	$.getScript('/js/chart-theme.js', function() {
		// Apply the theme
		var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

		_chart('#timeline-chart', 'Live DSP API Calls', 'Calls');
		_chart('#timeline-chart-logins', 'DSP User Logins', 'Logins', 'logins');
		//_chart('#timeline-chart-activations', 'DSP User Activations', 'Activations', 'activations');
//		_chart('#timeline-chart-provision', 'Provisioning', 'Requests', '', 'fabric/queue/*');
//		_chart('#timeline-chart-fabric-api', 'Fabric API Calls', 'Calls', '', 'fabric/*');
//
//		_update();
	});
});
