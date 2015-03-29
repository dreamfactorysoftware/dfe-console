<?php
/**
 * BaseAuthModel.php
 */
use DreamFactory\Yii\Utility\Pii;

/**
 * BaseAuthModel
 * The base class for all authentication models.
 */
class BaseAuthModel extends \DreamFactory\Yii\Models\BaseModel
{
	//********************************************************************************
	//* Public Methods
	//********************************************************************************

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className
	 *
	 * @return BaseAuthModel
	 */
	public static function model( $className = __CLASS__ )
	{
		return parent::model( $className );
	}

	/**
	 * @return CDbConnection
	 */
	public function getDbConnection()
	{
		return self::$db = Pii::component( 'db.fabric_auth' );
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->getAttributes( true );
	}

}
