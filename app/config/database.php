<?php
return array(
    /** PDO return data in arrays */
    'fetch'       => PDO::FETCH_ASSOC,
    /** Default database type is MySQL */
    'default'     => 'dfe-local',
    /** Migration table */
    'migrations'  => 'migration_t',
    /** Connections */
    'connections' => array(
        'fabric-deploy' => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => 3306,
            //            'database' => 'dfe_local',
            //            'username' => 'dfe_user',
            //            'password' => 'dfe_user',
            //            'host'      => 'cerberus.fabric.dreamfactory.com',
            'database'  => 'fabric_deploy',
            'username'  => 'deploy_user',
            'password'  => 'deploy_user',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
        'fabric-auth'   => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => 3306,
            //            'host'      => 'cerberus.fabric.dreamfactory.com',
            //            'port'      => 3306,
            //            'database' => 'dfe_local',
            //            'username' => 'dfe_user',
            //            'password' => 'dfe_user',
            'database'  => 'fabric_auth',
            'username'  => 'auth_user',
            'password'  => 'auth_user',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
        'dfe-local'     => array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'dfe_local',
            'username'  => 'dfe_user',
            'password'  => 'dfe_user',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
    ),
    /** Redis */
    'redis'       => array(
        'cluster' => false,
        'default' => array(
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 0,
        ),
    ),
);
