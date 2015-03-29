<?php
/**
 * database.developer_site.config.php
 * This file contains the database configurations for the developer_site database
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

$_host = 'developer.dreamfactory.com';

return array(
	'class'              => 'CDbConnection',
	'autoConnect'        => true,
	'connectionString'   => 'mysql:host=' . $_host . ';dbname=developer_site;port=3306;',
	'username'           => 'site_user',
	'password'           => 'site_user',
	'emulatePrepare'     => true,
	'charset'            => 'utf8',
//	'schemaCachingDuration' => 3600,
	'enableParamLogging' => true,
);