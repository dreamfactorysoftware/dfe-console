<?php
/**
 * database.db-east-1.config.php
 * This file contains the database configurations for the db-east-1 database
 *
 * @link   http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */

$_host = 'db-east-1.fabric.dreamfactory.com';
$_port = 3306;

return array(
    'class'              => 'CDbConnection',
    'autoConnect'        => true,
    'connectionString'   => 'mysql:host=' . $_host . ';port=' . $_port . ';',
    'username'           => 'cerberus',
    'password'           => 'KlL8ZF-E-rBFw_h9ygQZh3ZF',
    'emulatePrepare'     => true,
    'charset'            => 'utf8',
    'enableParamLogging' => true,
);
