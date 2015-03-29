<?php
/**
 * This is the model class for table "api_session_t".
 *
 * @property string $id
 * @property int    $user_id
 * @property string $api_token_text
 * @property string $local_session_id_text
 * @property string $remote_session_id_text
 * @property string $session_name_text
 * @property string $session_data_text
 * @property string $expire_date
 * @property string $create_date
 * @property string $lmod_date
 */
class ApiSession extends \DreamFactory\Yii\Models\BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ApiSession the static model class
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
		return 'api_session_t';
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'user' => array( static::BELONGS_TO, 'User', 'user_id' ),
		);
	}

}