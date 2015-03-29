<?php
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\Inflector;

/**
 * RestException
 * Generic queue processing exception
 */
class RestException extends \CHttpException
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @InheritDoc
	 */
	public function __construct( $status, $message = null, $code = 0 )
	{
		if ( null === $message )
		{
			//	If no message was given and it's common, we can set it.
			if ( HttpResponse::defines( $status ) )
			{
				//	ka-jigger the constant name into some kinda english
				$message = ucfirst( strtolower( str_replace( '_', ' ', Inflector::neutralize( HttpResponse::nameOf( $status ) ) ) ) );
			}
		}

		parent::__construct( $status, $message, $code );
	}
}
