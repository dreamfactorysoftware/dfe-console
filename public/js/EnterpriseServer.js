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
	defaults:        {

		//'<"wrapper"<"row"<"col-md-1"l><"col-md-offset-4 col-md-2"r><"col-md-5"f>><"row"<"col-md-12"t>><"row"<ip>>', //		deferRender: true,
		//'<"wrapper"<"row"<"col-md-12"Clf>><"row"<"col-md-12"rt>><"row"<"col-md-12"ip>>>',
		dom:      "<'row'<'col-md-4 col-sm-3'l><'col-md-offset-4 col-md-4 col-sm-offset-6 col-sm-3'f>r><'row'<'col-sm-12 col-md-12't>><'row'<'col-sm-4 col-md-4'i><'col-sm-8 col-md-8'p>>",
		language: {
			sLengthMenu: '_MENU_ per page',
			sSearch:     '<i class="fa fa-search"></i>',
			sEmptyTable: 'No entries found'
		},
		classes:  {
			sLengthSelect: 'form-control',
			sFilterInput:  'form-control'
		}
	},
	/**
	 * @type string
	 */
	dataType:        null,
	/**
	 * @type string
	 */
	dataUrl:         null,
	/**
	 * @type string
	 */
	tableId:         null,
	/**
	 * @type boolean
	 */
	initialized:     false,
	/** @type string **/
	lastTitle:       null,
	/**
	 * @type DataTable
	 */
	dt:              null,
	/**
	 * @type {*}
	 */
	cv:              null,
	/**
	 * @type $
	 */
	$_searchBoxes:   null,
	/**
	 * @type $
	 */
	$_loader:        null,
	/**
	 * @type $
	 */
	$_dataLoader:    null,
	/**
	 * @type int
	 */
	minimumHeight:   null,
	/** @type $ **/
	$_headerToolbar: null,
	/**
	 * @type bool
	 */
	searchFixed:     false,

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
		this.columns = this._getTableColumns($(this.tableId));

		this.initialized = true;

		return this;
	},

	/**
	 * Pulls the column name out of the table header "data-*"" fields
	 *
	 * @param $table
	 * @returns {{columns: Array}}
	 * @private
	 */
	_getTableColumns: function($table) {
		var _columns = {columns: []};

		$.each($table.find('thead th'), function(index, th) {
			var _name = $(th).data('columnName');

			if (_name && _name.length) {
				var _point = {data: _name};
				_columns.columns.push(_point);
			}
		});

		return _columns;
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

			var _options = $.extend(this.defaults, {
				ajax:       this.dataUrl,
				serverSide: true,
				processing: false,
				pagingType: 'full_numbers',
				columns:    this.columns.columns
			});

			this.$_headerToolbar = $('.page-header-toolbar');

			if ($_table.length) {
				this.dt = $_table.DataTable(_options);

				if (this.dt) {
					this.dt.on('init', function(e) {
						_this.$_loader = $('#loading-content');
						_this.$_dataLoader = $('#loading-data')
					}).on('processing.dt', function(e, settings, processing) {
						_this.dataLoading(processing);
					}).on('draw.dt', function(e, settings, processing) {
						if (!_this.searchFixed) {
							var _ac, _name, $_search = $('.dataTables_filter');

							if ($_search) {
								_ac = $_search.find('input[type="search"]').attr('aria-controls');
								$_search.html('<div class="form-group has-feedback"><input type="search" class="form-control" placeholder="filter" aria-controls="' +
											  _ac +
											  '"/><i class="fa fa-fw fa-search form-control-feedback"></i></div>');

								$_search.find('input').css({height: 'auto'});
							}

							_this.searchFixed = true;
						}
					}).on('click', 'tr', function(e) {
						$('tr.selected', $_table).removeClass('selected');
						$(this).toggleClass('selected');

						var $_edit = $('#header-bar-edit, #header-bar-delete', _this.$_headerToolbar);

						if ($_edit.length && $(this).hasClass('selected')) {
							$_edit.show();
						} else {
							$_edit.hide();
						}
					}).on('dblclick', 'tbody tr', function(e) {
						e.preventDefault();
						var _id = $(this).attr('id');
						window.top.location.href = _this.dataUrl + '/' + _id + '/edit';
					});
				}
			}
		}

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
			if (showHide) {
				this.$_dataLoader.show();
			} else {
				this.$_dataLoader.hide();
			}
		}
	}

};
