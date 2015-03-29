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
		chart:     {
			type:        'spline',
			animation:   Highcharts.svg, // don't animate in old IE
			marginRight: 10,
			events:      {
				load: function() {
					var series = this.series[0];
					setInterval(_getData(which, facility, size), 1000 * 60 * 60);
				}
			}
		},
		title:     {
			text: name
		},
		xAxis:     {
			type:              'datetime',
			tickPixelInterval: 150
		},
		yAxis:     {
			title:     {
				text: yAxisName
			},
			plotLines: [
				{
					value: 0,
					width: 1,
					color: '#808080'
				}
			]
		},
		tooltip:   {
			formatter: function() {
				return _formatDate(this.series.name, this.x, this.y);
			}

		},
		legend:    {
			enabled: false
		},
		exporting: {
			enabled: false
		},
		series:    [
			{
				name: name,
				data: _getData(which, facility, size)
			}
		]
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
	var _data =
		[
		];

	var _payload = {
		raw:      1,
		size:     0,
		interval: 'hour'
	};

	if (which) {
		_payload.which = which;
	}

	if (facility) {
		_payload.facility = facility;
	}

	$.ajax({
		url:      '/dashboard/logs',
		type:     'POST',
		data:     _payload,
		dataType: 'json',
		async:    false,
		//			processData: false,
		success:  function(json, statusText, xhr) {
			json.facets.published_on.entries.forEach(function(element, index, array) {
				_data.push({
					x: element.time,
					y: element.count
				});
			});
		}, error: function(xhr, message, error) {
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
			}, error: function(xhr, message, error) {
				console.error("Error while loading data from server", message);
				throw(error);
			}
		});

		setTimeout(_update, _dashboardOptions.updateInterval);
	};

	/**
	 * Dark blue theme for Highcharts JS
	 * @author Torstein Hønsi
	 */

	Highcharts.theme = {
		global: {
			useUTC: false
		}
	};

	// Apply the theme
	var highchartsOptions = Highcharts.setOptions(Highcharts.theme);

	_chart('#timeline-chart', 'Live DSP API Calls', 'Calls');
	_chart('#timeline-chart-logins', 'DSP User Logins', 'Logins', 'logins');
	_chart('#timeline-chart-activations', 'DSP User Activations', 'Activations', 'activations');
	_chart('#timeline-chart-provision', 'Provisioning', 'Requests', '', 'fabric/queue/*');
	_chart('#timeline-chart-fabric-api', 'Fabric API Calls', 'Calls', '', 'fabric/*');

	_update();
});


