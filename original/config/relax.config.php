<?php
/**
 * relax.config.php
 * This file contains configuration information for Relax
 *
 * @link    http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author  Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
//	Return parameters array...
return array(
	//*************************************************************************
	//* CouchDB Settings
	//*************************************************************************

	'host'      => 'blob.fabric.dreamfactory.com',
	'port'      => 5984,
	'user'      => 'auth_user',
	'password'  => 'yu-qZQGie_JAzqT0VkU7qt8C',
	'namespace' => 'blob',
	'log_level' => 0,
	'log_file'  => \Kisma::get( 'app.log_file' ),
	'view_path' => __DIR__ . '/couchdb',
);
