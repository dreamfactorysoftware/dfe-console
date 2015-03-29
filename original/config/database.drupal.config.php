<?php
/**
 * database.drupal.config.php
 * This file contains the database configurations for the Drupal database
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
return array(
	'class'              => 'CDbConnection',
	'autoConnect'        => true,
	'connectionString'   => 'mysql:host=dfnew.piuid.com;dbname=dfcom_drupal;port=3306;',
	'username'           => 'mainsite_usr',
	'password'           => 'm@1ns1T3',
	'emulatePrepare'     => true,
	'charset'            => 'utf8',
	'enableParamLogging' => true,
);
