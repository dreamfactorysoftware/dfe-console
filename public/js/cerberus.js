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

	$('.nav.main-menu li').removeClass('active');
	$('.main-menu a[href="' + _uri + '"]').parent('li').addClass('active');
};

/**
 * document ready
 */
jQuery(function($) {
	var $_avatar = $('.avatar-image');

	if ($_avatar.data('hash').length) {
		$_avatar.html('<img class="gravatar-image" src="' + 'http://www.gravatar.com/avatar/' + $_avatar.data('hash') + '" alt="avatar" />');
	}

	//	Bind clickers
	$('#main').on('click', '.show-sidebar', function(e) {
		e.preventDefault();

		var $_body = $('body'), $_sidebar = $('#sidebar-left'), $_content = $('#content'), _flow = $_body.css('overflow');

		$_body.css({overflow: 'hidden'});

		if ($_content.hasClass('col-md-10')) {
			//	Shown, so hide
			$_sidebar.hide('fast').promise().done(function() {
				$_content.removeClass('col-md-10').promise().done(function() {
					$_content.addClass('col-md-12');
				});
			});
		} else {
			+//	Hidden, so show
				$_content.removeClass('col-md-12').promise().done(function() {
					$_content.addClass('col-md-10').promise().done(function() {
						$_sidebar.show('fast');
					});
				});
		}

		//	Redraw data table when animations complete
		$($_sidebar, $_content).promise().done(function() {
			$_body.css({overflow: _flow || 'initial'});
			EnterpriseServer.redrawTable();
		});
	});

	_loadData();

	$('.nano').nanoScroller();
});
