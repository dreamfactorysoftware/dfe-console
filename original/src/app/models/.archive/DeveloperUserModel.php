<?php
/**
 * DeveloperUserModel.php
 */
use \Kisma\Core\Utility\Log;
use DreamFactory\Yii\Utility\Pii;

/**
 * DeveloperUserModel
 * Base class for tables in the "developer" database
 */
abstract class DeveloperUserModel extends \DreamFactory\Yii\Models\DreamUserModel
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @return \CDbConnection
	 */
	public function getDbConnection()
	{
		return \DreamFactory\Yii\Utility\Pii::db( 'db.fabric_auth' );
	}

}