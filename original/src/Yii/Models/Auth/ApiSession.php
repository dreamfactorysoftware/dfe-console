<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "api_session_t"
 *
 * @property string  $id
 * @property integer $user_id
 * @property string  $api_token_text
 * @property string  $local_session_id_text
 * @property string  $remote_session_id_text
 * @property string  $session_name_text
 * @property string  $session_data_text
 * @property string  $expire_date
 * @property string  $create_date
 * @property string  $lmod_date
 */
class ApiSession extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return ApiSessionT the static model class
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
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'user_id, session_name_text, expire_date, ', 'required' ),
			array( 'user_id', 'numerical', 'integerOnly' => true ),
			array( 'api_token_text, local_session_id_text, remote_session_id_text', 'length', 'max' => 128 ),
			array( 'session_name_text', 'length', 'max' => 255 ),
			array( 'session_data_text', 'safe' ),
			array(
				'id, user_id, api_token_text, local_session_id_text, remote_session_id_text, session_name_text, session_data_text, expire_date, create_date, lmod_date',
				'safe',
				'on' => 'search'
			),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'user_id'                => 'User',
			'api_token_text'         => 'Api Token',
			'local_session_id_text'  => 'Local Session Id',
			'remote_session_id_text' => 'Remote Session Id',
			'session_name_text'      => 'Session Name',
			'session_data_text'      => 'Session Data',
			'expire_date'            => 'Expire Date',
			'create_date'            => 'Create Date',
			'lmod_date'              => 'Last Modified Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new \CDbCriteria();

		$_criteria->compare( 'id', $this->id, true );
		$_criteria->compare( 'user_id', $this->user_id );
		$_criteria->compare( 'api_token_text', $this->api_token_text, true );
		$_criteria->compare( 'local_session_id_text', $this->local_session_id_text, true );
		$_criteria->compare( 'remote_session_id_text', $this->remote_session_id_text, true );
		$_criteria->compare( 'session_name_text', $this->session_name_text, true );
		$_criteria->compare( 'session_data_text', $this->session_data_text, true );
		$_criteria->compare( 'expire_date', $this->expire_date, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new \CActiveDataProvider(
			$this,
			array(
				 'criteria' => $_criteria,
			)
		);
	}
}