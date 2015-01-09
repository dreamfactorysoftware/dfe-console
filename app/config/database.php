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
            'host'      => 'cerberus.fabric.dreamfactory.com',
            'port'      => 3306,
            'database'  => 'fabric_deploy',
            'username'  => 'deploy_user',
            'password'  => '3hgc9nKuhh658_MJ-_D-PDqpkVEyta',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),
        'fabric-auth'   => array(
            'driver'    => 'mysql',
            'host'      => 'cerberus.fabric.dreamfactory.com',
            'port'      => 3306,
            'database'  => 'fabric_auth',
            'username'  => 'auth_user',
            'password'  => 'yu-qZQGie_JAzqT0VkU7qt8Cf',
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
