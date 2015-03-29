<?php
/**
 * oauth.service.config.php
 * This is the service definition for the oauth2 service
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

return array(

	//	The service's name
	'serviceName'               => 'oauth',
	//	The controller's class path
	'class'                     => 'application.controllers.OAuthController',
	//	Let each action decide what it needs
	'requiredParameters'        => null,
	//	Yep, but not now...
	'requireAuthentication'     => false,
	//	Allow header parameters
	'checkRequestForParameters' => true,
	//	Our header parameter prefix
	'headerParameterPrefix'     => 'X_DF_OAUTH_',
	//	Acceptable header parameters
	'headerParameters'          => array(),
	/**
	 * If these are listed as header parameters as well,
	 * then they will be accepted in both methods.
	 *
	 * @var array Acceptable configuration options
	 */
	'configOptions'             => array(),
);