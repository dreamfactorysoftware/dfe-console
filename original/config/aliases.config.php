<?php
/**
 * aliases.config.php
 * This file contains any Yii imports or loading necessary
 *
 * @link    http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author  Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
use DreamFactory\Yii\Utility\Pii;

$_basePath = dirname( __DIR__ );
$_vendorPath = $_basePath . '/vendor';

Pii::alias( 'Cerberus', $_basePath . '/src' );
Pii::alias( 'Cerberus.*', $_basePath . '/src' );

Pii::alias( 'DreamFactory.*', $_vendorPath . '/dreamfactory/lib-dfphp/src/DreamFactory' );
Pii::alias( 'DreamFactory.Services.CouchDb.WorkQueue', $_vendorPath . '/dreamfactory/lib-dfphp/src/DreamFactory/Services/CouchDb/WorkQueue' );
Pii::alias( 'DreamFactory.Services.*', $_vendorPath . '/dreamfactory/lib-dfphp/src/DreamFactory/Services' );
Pii::alias( 'DreamFactory.Services.CouchDb.*', $_vendorPath . '/dreamfactory/lib-dfphp/src/DreamFactory/Services/CouchDb' );

if ( is_dir( $_vendorPath . '/dreamfactory/lib-php-common-yii/src/Actions' ) )
{
    //  PSR-4
    Pii::alias( 'DreamFactory.Yii.*', $_vendorPath . '/dreamfactory/lib-php-common-yii/src' );
    Pii::alias( 'DreamFactory.Yii.Logging.*', $_vendorPath . '/dreamfactory/lib-php-common-yii/src/Logging' );
}
else
{
    //  PSR-0
    Pii::alias( 'DreamFactory.Yii.*', $_vendorPath . '/dreamfactory/lib-php-common-yii/src/DreamFactory/Yii' );
    Pii::alias( 'DreamFactory.Yii.Logging.*', $_vendorPath . '/dreamfactory/lib-php-common-yii/src/DreamFactory/Yii/Logging' );
}

//Pii::alias( 'DreamFactory.*', $_vendorPath . '/dreamfactory/lib-dfphp/src/DreamFactory' );
Pii::alias( 'Swift', $_vendorPath . '/swiftmailer/swiftmailer/lib/classes' );

Pii::setPathOfAlias( 'vendor', $_vendorPath );

