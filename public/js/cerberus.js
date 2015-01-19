/**
 * cerberus.js
 * The file contains client-side functions that are global to the entire application.
 */

"use strict";

//******************************************************************************
//* Load plugins
//******************************************************************************

/**
 * Loads content into ajax content div
 * @param [url]
 */
function _loadContent(url) {
	var $_target = $('#ajax-content');

	url = url || location.hash.replace(/^#/, '');

	if (!url || url.length < 1) {
		url = '/app/dashboard';
	}

	EnterpriseServer.loading(true);
	$_target.hide().empty();

	$.ajax({
			   mimeType: 'text/html; charset=utf-8',
			   url:      url,
			   type:     'GET',
			   success:  function(data) {
				   $_target.removeClass('error-wrapper').html(data);
			   },
			   error:    function(jqXHR, textStatus, errorThrown) {
				   $_target.show();
				   EnterpriseServer.loading(false);

				   if (jqXHR.status == 500 || jqXHR.status == 404) {
					   $('#ajax-content').addClass('error-wrapper').html(jqXHR.responseText);
					   $('#content').css({
											 height:   '100%',
											 overflow: 'hidden'
										 });
				   } else {
					   alert(errorThrown);
				   }
			   },
			   dataType: 'html'
		   }).done(function() {
		$_target.show();
		EnterpriseServer.loading(false);

		var _type = $('.table-datatable').data('resource');

		if (_type && _type.length) {
			EnterpriseServer.setDataType(_type).populateTable();
		}

		$('.tab-link').on('click', function(e) {
			e.preventDefault();
			var _tab = $(this).data('toggle');

			$('#dashboard-tabs li, .tab-content').removeClass('active');
			$(this).parent('li').addClass('active');
			$(_tab).addClass('active');
		});

		_setActiveItem();
	});
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

	$('.nano').nanoScroller();

	$(document).on('ajaxStart', function() {
		$('.breadcrumb-loader').show();
		$('#main').css({cursor: 'wait'});
	}).on('ajaxComplete', function() {
		$('.breadcrumb-loader').hide();
		$('#main').css({cursor: 'initial'});
	});

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
	}).on('click', '.ajax-link', function(e) {
		e.preventDefault();

		var _href = $(this).attr('href').trim();

		if (_href && _href.length && _href != '#') {
			window.location.hash = _href;
			_loadContent(_href);
		}
	});

	_loadContent();
});
