/*!
 * Copyright (c) 2014 - ∞ DreamFactory Software, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
jQuery(function($) {
	var _validator = [];

	//	Login form
	_validator.push($('#login-form').validate($.extend({}, DefaultValidateOptions, {
		rules: {
			email_addr_text: {
				required: true,
				email:    true
			},
			password_text:   {
				required:  true,
				minlength: 5
			}
		}
	})));

	var $_body = $('body'), _of = $_body.css('overflow');

	$('#to-recover').on('click', function() {
		$('.alert-dismissible').hide();
		$_body.animate({overflow: 'hidden'}, 1, function() {
			$('#login-form').slideUp();
			$('#recover-form').fadeIn();
		}).done(function() {
			$_body.css({overflow: _of});
			$('.alert-dismissable').show();
		});
	});

	//	Recovery form
	_validator.push($('#recover-form').validate($.extend({}, DefaultValidateOptions, {
		rules: {
			email_addr_text: {
				required: true,
				email:    true
			}
		}
	})));

	$('#to-login').on('click', function() {
		$('.alert-dismissible').hide();
		$_body.animate({overflow: 'hidden'}, 1, function() {
			$('#recover-form').fadeOut();
			$('#login-form').slideDown();
		}).done(function() {
			$_body.css({overflow: _of});
			$('.alert-dismissable').show();
		});
	});
});
