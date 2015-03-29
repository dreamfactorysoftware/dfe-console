<?php
/**
 * DreamServerWebApplication
 */
class DreamServerWebApplication extends CWebApplication
{
	/**
	 * Initialize
	 */
	protected function init()
	{
		parent::init();

		/** @noinspection PhpUndefinedFieldInspection */
		Yii::app()->onBeginRequest = array( $this, 'checkRequestMethod' );
	}

	/**
	 * Handles an OPTIONS request to the server to allow CORS
	 */
	public function checkRequestMethod()
	{
//		\Kisma\Core\Utility\Log::debug( 'Begin Request: ' . $_SERVER['REQUEST_METHOD'] );

		$_origin = \Kisma\Core\Utility\FilterInput::server( 'HTTP_ORIGIN' ) ? : '*';

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && \Kisma\Core\Enums\HttpMethod::Options == $_SERVER['REQUEST_METHOD'] )
		{
			header( 'HTTP/1.1 204' );
			header( 'content-length: 0' );
			header( 'content-type: text/plain' );
			header( 'access-control-allow-origin: ' . $_origin );
			header( 'access-control-allow-methods: GET, POST, PUT, DELETE, PATCH, COPY, OPTIONS' );
			header( 'access-control-allow-headers: content-type, accept' );
			header( 'access-control-max-age: 3600' );
			exit( 0 );
		}
	}
}