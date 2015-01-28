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
		dom:        'Cfrltip', //		deferRender: true,
		stateSave:  true,
		language:   {
			lengthMenu: '_MENU_ per page',
			search:     '<i class="fa fa-search"></i>'
		},
		classes:    {
			sLengthSelect: 'form-control',
			sFilterInput:  'form-control'
		},
		tableTools: {
			sRowSelect:   'os',
			sRowSelector: 'td:first-child',
			aButtons:     []
		},
		colVis:     {
			buttonText: '<i class="fa fa-eye"></i>',
			restore:    'Restore',
			showAll:    'Show All',
			showNone:   'Show None',
			align:      'right'
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
