<?php
namespace Cerberus\Yii\Components;

use Cerberus\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpMethod;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Option;

/**
 * CerberusWebApplication
 */
class CerberusWebApplication extends \CWebApplication
{
	//*************************************************************************
	//	Methods
	//*************************************************************************

	/**
	 * Initialize
	 */
	protected function init()
	{
		parent::init();

		Pii::app()->attachEventHandler( 'onError', array( $this, '_handleError' ) );
		Pii::app()->attachEventHandler( 'onException', array( $this, '_handleException' ) );
	}

	protected function _handleError( $event )
	{
		Log::error( 'Error: ' . print_r( $event, true ) );
	}

	protected function _handleException( $event )
	{
		Log::error( 'Exception: ' . print_r( $event, true ) );
	}

	/**
	 * Handles an OPTIONS request to the server to allow CORS and optionally sends the CORS headers
	 *
	 * @param \CEvent $event
	 */
	public function checkRequestMethod( \CEvent $event )
	{
		//	Answer an options call...
		if ( HttpMethod::Options == FilterInput::server( 'REQUEST_METHOD' ) )
		{
			header( 'HTTP/1.1 204' );
			header( 'content-length: 0' );
			header( 'content-type: text/plain' );

			$this->addCorsHeaders();

			Pii::end();
		}

		//	Auto-add the CORS headers...
		if ( $this->_autoAddHeaders )
		{
			if ( !$this->addCorsHeaders() )
			{
				$event->handled = true;
			}
		}
	}

	/**
	 * @param array|bool $whitelist Set to "false" to reset the internal method cache.
	 *
	 * @return bool
	 */
	public function addCorsHeaders( $whitelist = array() )
	{
		static $_cache = array();

		//	Reset the cache before processing...
		if ( false === $whitelist )
		{
			$_cache = array();

			return true;
		}

		$_origin = Option::get( $_SERVER, 'HTTP_ORIGIN' );
		$_requestSource = $_SERVER['SERVER_NAME'];

		//	Not in cache, check it out...
		if ( $_origin && !in_array( $_origin, $_cache ) )
		{
			if ( $this->_allowedOrigin( $_origin, array( $_requestSource ) ) )
			{
				$_cache[] = $_origin;
			}
			else
			{
				/**
				 * No sir, I didn't like it.
				 *
				 * @link http://www.youtube.com/watch?v=VRaoHi_xcWk
				 */
				header( 'HTTP/1.1 403 Forbidden' );

				Pii::end();

				//	If end fails for some unknown impossible reason...
				return false;
			}
		}

		header( 'Access-Control-Allow-Origin: ' . $_origin );
		header( 'Access-Control-Allow-Headers: ' . static::CORS_ALLOWED_HEADERS );
		header( 'Access-Control-Allow-Methods: ' . static::CORS_ALLOWED_METHODS );
		header( 'Access-Control-Max-Age: ' . static::CORS_DEFAULT_MAX_AGE );

		if ( $this->_extendedHeaders )
		{
			header( 'X-DreamFactory-Source: ' . $_requestSource );

			if ( $_origin )
			{
				if ( !empty( $this->_corsWhitelist ) )
				{
					header( 'X-DreamFactory-Full-Whitelist: ' . implode( ', ', $this->_corsWhitelist ) );
				}

				header( 'X-DreamFactory-Origin-Whitelisted: ' . preg_match( '/^([\w_-]+\.)*' . $_requestSource . '$/', $_origin ) );
			}
		}

		return true;
	}

	/**
	 * @param string $origin     The requesting origin
	 * @param array  $additional Additional origins to allow
	 *
	 * @return bool
	 */
	protected function _allowedOrigin( $origin, $additional = array() )
	{
		if ( !is_array( $additional ) )
		{
			$additional = array( $additional );
		}

		foreach ( array_merge( $this->_corsWhitelist, $additional ) as $_whiteGuy )
		{
			if ( preg_match( '/^([\w_-]+\.)*' . $_whiteGuy . '$/', $origin ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array $corsWhitelist
	 *
	 * @return CerberusWebApplication
	 */
	public function setCorsWhitelist( $corsWhitelist )
	{
		$this->_corsWhitelist = $corsWhitelist;

		//	Reset the header cache
		$this->addCorsHeaders( false );

		return $this;
	}

	/**
	 * @return array
	 */
	public function getCorsWhitelist()
	{
		return $this->_corsWhitelist;
	}

	/**
	 * @param boolean $autoAddHeaders
	 *
	 * @return CerberusWebApplication
	 */
	public function setAutoAddHeaders( $autoAddHeaders = true )
	{
		$this->_autoAddHeaders = $autoAddHeaders;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getAutoAddHeaders()
	{
		return $this->_autoAddHeaders;
	}

	/**
	 * @param boolean $extendedHeaders
	 *
	 * @return CerberusWebApplication
	 */
	public function setExtendedHeaders( $extendedHeaders = true )
	{
		$this->_extendedHeaders = $extendedHeaders;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getExtendedHeaders()
	{
		return $this->_extendedHeaders;
	}
}