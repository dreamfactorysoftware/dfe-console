<?php
/**
 * chairlift.config.php
 * This file contains configuration information for chairlift
 *
 * @link    http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author  Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
//	Return parameters array...
return array(
	//*************************************************************************
	//* Job Queue Settings
	//*************************************************************************

	'host'             => 'couchdb.fabric.dreamfactory.com',
	'port'             => 5984,
	'user'             => 'dfadmin',
	'password'         => 'dfadmin',
	'track_job_status' => true,
	'dbname'           => 'work_queue',
	'worker_count'     => 2,
	'interval'         => 5,
	'queue'            => 'default',
	'namespace'        => 'com.dreamfactory.developer',
	'log_level'        => 0,
	'log_file'         => \Kisma::get( 'app.log_file' ),
	'view_path'        => dirname( __DIR__ ) . '/vendor/dreamfactory/lib-dfphp/src/DreamFactory/CouchDb/views',

);
