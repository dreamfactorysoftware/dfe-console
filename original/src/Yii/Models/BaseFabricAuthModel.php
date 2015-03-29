<?php
namespace Cerberus\Yii\Models;

use DreamFactory\Yii\Models\BaseModel;
use DreamFactory\Yii\Utility\Pii;

/**
 * BaseFabricAuthModel
 * The base class for all authentication models.
 */
class BaseFabricAuthModel extends BaseFabricModel
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * @return CDbConnection
	 */
	public function getDbConnection()
	{
		return static::$db = Pii::component( 'db.fabric_auth' );
	}
}
