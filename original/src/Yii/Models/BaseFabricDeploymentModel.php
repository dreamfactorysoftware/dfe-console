<?php
namespace Cerberus\Yii\Models;

use DreamFactory\Yii\Models\BaseModel;
use DreamFactory\Yii\Utility\Pii;

/**
 * BaseFabricDeploymentModel
 * The base class for all deployment models.
 */
class BaseFabricDeploymentModel extends BaseFabricModel
{
	//********************************************************************************
	//* Methods
	//********************************************************************************

	/**
	 * @return \CDbConnection
	 */
	public function getDbConnection()
	{
		return self::$db = Pii::component( 'db.fabric_deploy' );
	}
}