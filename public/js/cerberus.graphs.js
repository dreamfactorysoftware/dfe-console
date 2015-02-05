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
	updateInterval:  3000000,
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
								   text: ''
							   },
							   subtitle:    {
								   text: document.ontouchstart ===
										 undefined
									   ? 'Click and drag in the plot area to zoom in'
									   : 'Pinch the chart to zoom in'
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
												 color: '#c0c0c0'
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
															 Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(.25).get('rgba')]]
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
				   json.response.aggregations.published_on.buckets.forEach(function(element, index, array) {
					   _data.push([element.key, element.doc_count]);
				   });
			   },
			   error:    function(xhr, message, error) {
				   console.error("Error while loading data from server", message);
				   throw(error);
			   }
		   });

	return _data;
};

var _update = function() {
	$.ajax({
			   url:      '/dashboard/global-stats',
			   type:     'GET',
			   dataType: 'json',
			   success:  function(json, statusText, xhr) {
				   if (json && json.success) {
					   $('#breadcrumb-activity-clusters').html(json.response.cluster_count);
					   $('#breadcrumb-activity-servers').html(json.response.server_count);
					   $('#breadcrumb-activity-users').html(json.response.user_count);
					   $('#breadcrumb-activity-provisioned').html(json.response.dsp_count.live);
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

/**
 * DocReady
 */
$(function() {
	// Apply the theme
	Highcharts.setOptions(Highcharts.theme);

	_chart('#timeline-chart', 'Live DSP API Calls', 'Calls');
	_chart('#timeline-chart-logins', 'DSP User Logins', 'Logins', 'logins');
	_chart('#timeline-chart-fabric-api', 'Fabric API Calls', 'Calls', '', 'fabric/*');

	_update();
});
