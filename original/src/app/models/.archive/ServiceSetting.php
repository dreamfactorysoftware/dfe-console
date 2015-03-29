<?php
/**
 * This is the model class for table "service_setting_t".
 *
 * The followings are the available columns in table 'service_setting_t':
 *
 * @property integer $id
 * @property integer $service_id
 * @property string  $user_id_text
 * @property string  $account_id_text
 * @property string  $token_text
 * @property string  $token_secret_text
 * @property string  $create_date
 * @property string  $lmod_date
 *
 * @property Service $service
 */
class ServiceSetting extends \DreamFactory\Yii\Models\BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param null|string $className
	 *
	 * @return \BaseModel|\CActiveRecord|\ServiceSetting the static model class
	 */
	public static function model( $className = __CLASS__ )
	{
		return parent::model( $className );
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'service_setting_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'service_id', 'required' ),
			array(
				'service_id',
				'numerical',
				'integerOnly'=> true
			),
			array(
				'user_id_text, account_id_text, token_text, token_secret_text',
				'length',
				'max'=> 100
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'service' => array( self::BELONGS_TO, 'Service', 'service_id' ),
		);
	}

}
