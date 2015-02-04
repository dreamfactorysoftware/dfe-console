/*!DreamFactory Enterprise(tm) Server*/
/**
 * Application support object
 *
 * Version: 1.0.0
 * Requires: jQuery v2.0+
 *
 * Copyright (c) 2012-2014 DreamFactory Software, Inc. All Rights Reserved
 */
"use strict";

var EnterpriseServer = {

	//******************************************************************************
	//* Members
	//******************************************************************************

	/**
	 * DataTable defaults
	 * @type {*}
	 */
	defaults:      {
		//	"<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md6'i><'col-md-6'p>>"
		//'<"wrapper"<"row"<"col-md-1"l><"col-md-offset-4 col-md-2"r><"col-md-5"f>><"row"<"col-md-12"t>><"row"<ip>>', //		deferRender: true,
		dom:       '<"wrapper"<"row"<"col-md-12"Clf>><"row"<"col-md-12"rt>><"row"<"col-md-12"ip>>>',
		stateSave: true,
		language:  {
			sLengthMenu: '_MENU_ per page',
			sSearch:     '<i class="fa fa-search"></i>'
		},
		classes:   {
			sLengthSelect: 'form-control',
			sFilterInput:  'form-control',
			sDataTable:    'table-compact'
		}
	},
	/**
	 * @type string
	 */
	dataType:      null,
	/**
	 * @type string
	 */
	dataUrl:       null,
	/**
	 * @type string
	 */
	tableId:       null,
	/**
	 * @type boolean
	 */
	initialized:   false,
	/**
	 * @type DataTable
	 */
	dt:            null,
	/**
	 * @type {*}
	 */
	cv:            null,
	/**
	 * @type $
	 */
	$_searchBoxes: null,
	/**
	 * @type $
	 */
	$_loader:      null,
	/**
	 * @type $
	 */
	$_dataLoader:  null,
	/**
	 * @type int
	 */
	minimumHeight: null,

	//******************************************************************************
	//* Functions
	//******************************************************************************

	/**
	 * Sets the various parts of the loader based on the data type (i.e. "user",
	 * "cluster", "server", etc.)
	 *
	 * @param dataType
	 * @param [dataUrl]
	 * @param [tableId]
	 */
	setDataType: function(dataType, dataUrl, tableId) {
		this.dataType = dataType + 's';
		this.dataUrl = dataUrl || ('/api/v1/' + this.dataType);
		this.tableId = tableId || ( '#dt-' + dataType );

		this.initialized = true;

		return this;
	},

	/**
	 * Fill up the table
	 * @param [dataType]
	 * @param [dataUrl]
	 * @param [tableId]
	 */
	populateTable: function(dataType, dataUrl, tableId) {
		if (dataType) {
			this.setDataType(dataType, dataUrl, tableId);
		}

		//	Create the data table
		if (this.initialized) {
			var _this = this, $_table = $(this.tableId);

			this.dt = $_table.DataTable($.extend(this.defaults, {
				ajax:       this.dataUrl,
				serverSide: true
			})).on('init', function(e) {
				_this.$_loader = $('#loading-content');
				_this.$_dataLoader = $('#loading-overlay')
			}).on('processing.dt', function(e, settings, processing) {
				_this.dataLoading(processing);
			}).on('click', 'tr', function(e) {
				$(this).toggleClass('selected');
			});

			var _ac, _name, $_search = $('.wrapper .dataTables_filter'), $_length = $('.wrapper .dataTables_length');

			if ($_search && $_search.length) {
				_ac = $_search.find('input[type="search"]').attr('aria-controls');
				$_search.html('<div class="form-group has-feedback"><input type="search" class="form-control" placeholder="filter" aria-controls="' +
							  _ac +
							  '"/><i class="fa fa-search form-control-feedback"></i></div>');
			}

			if ($_length.length) {
				var _options = $_length.find('select').html();

				_name = $('select', $_length).attr('name');
				_ac = $('select', $_length).attr('aria-controls');

				$_length.html('<select class="form-control" name="' +
							  _name +
							  '" aria-controls="' +
							  _ac +
							  '">' +
							  _options +
							  '</select><span class="help-block">per page</span>');
			}
		}

		return this;
	},

	/**
	 * Redraw the table
	 */
	redrawTable: function() {
		this.dt && this.dt.draw();

		return this;
	},

	/**
	 * Show/Hide the loading div
	 * @param showHide
	 */
	loading: function(showHide) {
		if (this.$_loader) {
			if (showHide) {
				this.$_loader.show();
			} else {
				this.$_loader.hide();
			}
		}
	},

	/**
	 * Show/Hide the loading div
	 * @param showHide
	 * @param [text]
	 */
	dataLoading: function(showHide, text) {
		if (this.$_dataLoader) {

			text = '<h1><i class="fa fa-cog fa-spin"></i>&nbsp;' + (text || 'Loading...') + '</h1>';

			if (showHide) {
				this.$_dataLoader.html(text).show();
			} else {
				this.$_dataLoader.hide().empty();
			}
		}
	}

};
