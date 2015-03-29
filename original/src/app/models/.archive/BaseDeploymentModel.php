<?php

/**
 * BaseDeploymentModel.php
 */
use DreamFactory\Yii\Utility\Pii;

/**
 * BaseDeploymentModel
 * The base class for all deployment models.
 */
class BaseDeploymentModel extends \DreamFactory\Yii\Models\BaseModel
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