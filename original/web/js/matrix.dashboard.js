jQuery(document).ready(function() {
	var updateInterval = 5000, _chartData = [];
	var _chartOptions = {
		lines:  {
			show: true
		},
		points: {
			show: true
		},
		grid:   { hoverable: true, clickable: true },
		xaxis:  { mode: 'time', timezone: 'local', label: 'Day' }
	};

	function getData() {
		jQuery.ajax({
			url:      '/app/graylogData',
			type:     'POST',
			dataType: 'json',
			data:     {
				term:  'message.facility',
				value: 'dsp/',
				limit: 30
			},
			async:    false,
			success:  function( data ) {

				if ( data && data.data.length > 0 ) {
					_chartData = [];
					for ( var i = 0; i < data.data.length; ++i ) {
						_chartData.push(data.data[i]);
					}

					return _chartData;
				}
			}
		});
	}

	getData();
	var plot = jQuery.plot(jQuery('.chart'), [_chartData], _chartOptions);

	function update() {
		getData();

		plot.setData([_chartData]);
		plot.draw();
		setTimeout(update, updateInterval);
	}

	update();
});
