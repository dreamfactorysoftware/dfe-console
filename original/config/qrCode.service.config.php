<?php
/**
 * qrCode.service.config.php
 * This is the service definition for the QRCode service
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

return array(

	//	The service's name
	'serviceName'               => 'qrCode',
	//	The controller's class path
	'class'                     => 'application.controllers.QrCodeController',
	//	Let each action decide what it needs
	'requiredParameters'        => null,
	//	Yep, but not now...
	'requireAuthentication'     => false,
	//	Allow header parameters
	'checkRequestForParameters' => true,
	//	Our header parameter prefix
	'headerParameterPrefix'     => 'X_DF_QR_',
	//	Acceptable header parameters
	'headerParameters'          => array(),

);