/**
 * cerberus.js
 * The file contains client-side functions that are global to the entire application.
 */

"use strict";

//******************************************************************************
//* Load plugins
//******************************************************************************

/**
 * If this is a data table, load it up...
 * @param [url]
 */
function _loadData(url) {
	var _type = $('.table-datatable').data('resource');

	if (_type && _type.length) {
		EnterpriseServer.populateTable(_type);
	}

	_setActiveItem();
}

/**
 * Adds the active class to the active menu item...
 * @private
 */
var _setActiveItem = function() {
	var _uri = window.top.location.hash.replace(/^#/, '');

	if (!_uri || !_uri.length) {
		_uri = '/app/dashboard';
	}

	$('.nav.partials-menu li').removeClass('active');
	$('.partials-menu a[href="' + _uri + '"]').parent('li').addClass('active');
};

//******************************************************************************
//* DocReady
//******************************************************************************

/**
 * document ready
 */
jQuery(function($) {
	var $_avatar = $('.avatar-image'), $_nano = $('.nano');

	if ($_avatar.length && $_avatar.data('hash').length) {
		$_avatar.html('<img class="gravatar-image" src="' + 'http://www.gravatar.com/avatar/' + $_avatar.data('hash') + '" alt="avatar" />');
	}

	if ($_nano && $_nano.length) {
		$_nano.nanoScroller();
	}

	$('button.btn-page-header').on('click', function(e) {
		e.preventDefault();
		var _rel = $(this).attr('rel'), _id = $(this).attr('id');

		if (_rel && _rel.length) {
			if ('header-bar-new' != _id) {
				var _rowId = $('.table-datatable tr.selected').attr('id');

				if (_rowId.length) {
					_rel = _rel.replace('{:id}', _rowId);
				}
			}

			window.top.location.href = _rel;
		}
	});

	//	Turn on page header tooltips
	$('[data-toggle="tooltip"]').tooltip({
		delay: {
			show: 500,
			hide: 100
		}
	});

	//	Load page data...
	_loadData();
});
