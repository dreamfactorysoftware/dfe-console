<?php
/**
 * services.config.php
 * This is the service definition configuration file for SLayer
 *
 * @link http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

return array(

	//*************************************************************************
	//* Citrix OAuth Service
	//*************************************************************************

	'citrix'     => require_once( __DIR__ . '/citrix.service.config.php' ),

	//*************************************************************************
	//* NetProspex Lookup Service
	//*************************************************************************

	'netProspex' => require_once( __DIR__ . '/netProspex.service.config.php' ),

	//*************************************************************************
	//* OAuth2 Server Service
	//*************************************************************************

	'oauth'      => require_once( __DIR__ . '/oauth.service.config.php' ),

	//*************************************************************************
	//* QR Code Generation
	//*************************************************************************

	'qrCode'     => require_once( __DIR__ . '/qrCode.service.config.php' ),

);
