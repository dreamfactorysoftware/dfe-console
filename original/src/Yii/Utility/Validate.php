<?php
/**
 * This file is part of the DreamFactory Services Platform(tm) (DSP)
 *
 * DreamFactory Services Platform(tm) <http://github.com/dreamfactorysoftware/dsp-core>
 * Copyright 2012-2013 DreamFactory Software, Inc. <developer-support@dreamfactory.com>
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
namespace Cerberus\Yii\Utility;

use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Option;
use DreamFactory\Interfaces\PageLocation;

/**
 * Validate.php
 * A jQuery Validation (http://docs.jquery.com/Plugins/Validation) helper.
 */
class Validate implements PageLocation
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string The CDN root
	 */
	const Cdn = '//ajax.aspnetcdn.com/ajax/jquery.validate';

	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	 * Registers the needed CSS and JavaScript.
	 *
	 * @param string       $selector
	 * @param string|array $options
	 *
	 * @return \CClientScript The current app's ClientScript object
	 */
	public static function register( $selector, $options = array() )
	{
		//	Don't screw with div formatting...
		if ( null === Option::get( $options, 'error_placement' ) )
		{
			$options['error_placement'] = 'function(error,element){error.appendTo(element.parent("div"));}';
		}

		//	Get the options...
		$_scriptOptions = is_string( $options ) ? $options : PiiScript::encodeOptions( $options );

		$_validate
			= <<<JS
var	_jqueryValidator;
if ( !_jQueryValidator ){ _jqueryValidator = $("{$selector}").validate({$_scriptOptions}); }
JS;

		//	Register the plugin
		Pii::scriptFile(
			array(
				 static::Cdn . '/1.11.1/jquery.validate.min.js',
				 static::Cdn . '/1.11.1/additional-methods.min.js',
			),
			static::End
		);

		//	Add to the page load
		return Pii::script( '#df-jquery-validator#', $_validate );
	}

	/**
	 * @param string $which
	 * @param string $message  If not specified, default for validator is used
	 * @param string $callback If not specified, default for validator is used
	 *
	 * @return bool
	 */
	public static function enableValidation( $which, $message = null, $callback = null )
	{
		$_message = $_callback = null;

		switch ( $which )
		{
			case 'postalCode':
				$_message = $message ? : 'Please specify a valid postal code';
				$_callback = $callback ? : 'function(postalcode, element) {return this.optional(element) || postalcode.match(/(^\d{5}(-\d{4})?$)|(^[ABCEGHJKLMNPRSTVXYabceghjklmnpstvxy]{1}\d{1}[A-Za-z]{1} ?\d{1}[A-Za-z]{1}\d{1})$/);}';
				break;

			default:
				return false;
		}

		return static::addMethod( $which, $_callback, $_message );
	}

	/**
	 * Adds a validation method to the page
	 *
	 * @param string $name
	 * @param string $callback
	 * @param string $message
	 *
	 * @return \CClientScript|null|string
	 */
	public static function addMethod( $name, $callback, $message )
	{
		$_method
			= <<<JS
jQuery.validator.addMethod(
	"{$name}",
	{$callback},
	"{$message}"
	);
JS;

		return Pii::script( '#df-jquery-validator::addMethod(' . $name . ')#', $_method );
	}
}