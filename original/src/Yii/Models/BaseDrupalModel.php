<?php
namespace Cerberus\Yii\Models;

use DreamFactory\Yii\Utility\Pii;

/**
 * BaseDrupalModel
 * The base class for all drupal models
 */
class BaseDrupalModel extends BaseFabricModel
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @return CDbConnection
	 */
	public function getDbConnection()
	{
		return static::$db = Pii::component( 'db.drupal' );
	}
}
