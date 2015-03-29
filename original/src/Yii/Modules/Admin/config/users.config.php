<?php
/**
 * mysql.config.php
 * Configuration file for the MySql service
 *
 * @copyright Copyright (c) 2012 DreamFactory Software, Inc.
 * @link      http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @filesource
 */
use DreamFactory\Interfaces\SqlStorage;

/** @noinspection SpellCheckingInspection */
return array(
	SqlStorage::Key_DatabaseName => 'dreamserver',
	SqlStorage::Key_HostName     => 'df.local',
	SqlStorage::Key_HostPort     => 3306,
	SqlStorage::Key_UserName     => 'ds_user',
	SqlStorage::Key_Password     => 'ds_user',
);
