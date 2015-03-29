<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "auth_token_t"
 *
 * @property integer    $id
 * @property integer    $refresh_ind
 * @property string     $token_text
 * @property integer    $owner_id
 * @property integer    $client_id_text
 * @property string     $scope_text
 * @property integer    $expire_date
 * @property string     $create_date
 * @property string     $lmod_date
 *
 * Relations:
 * @property AuthOwnerT $owner
 */
class AuthToken extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthTokenT the static model class
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
		return 'auth_token_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'token_text, owner_id, client_id_text, expire_date, create_date, lmod_date', 'required' ),
			array( 'refresh_ind, owner_id, client_id_text, expire_date', 'numerical', 'integerOnly' => true ),
			array( 'token_text', 'length', 'max' => 200 ),
			array( 'scope_text', 'length', 'max' => 256 ),
			array(
				'id, refresh_ind, token_text, owner_id, client_id_text, scope_text, expire_date, create_date, lmod_date',
				'safe',
				'on' => 'search'
			),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'owner' => array( self::BELONGS_TO, 'Cerberus\\Yii\\Models\\Auth\\AuthOwner', 'owner_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'             => 'ID',
			'refresh_ind'    => 'Refresh',
			'token_text'     => 'Token',
			'owner_id'       => 'Owner',
			'client_id_text' => 'Client Id',
			'scope_text'     => 'Scope',
			'expire_date'    => 'Expire Date',
			'create_date'    => 'Create Date',
			'lmod_date'      => 'Last Modified Date',
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

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'refresh_ind', $this->refresh_ind );
		$_criteria->compare( 'token_text', $this->token_text, true );
		$_criteria->compare( 'owner_id', $this->owner_id );
		$_criteria->compare( 'client_id_text', $this->client_id_text );
		$_criteria->compare( 'scope_text', $this->scope_text, true );
		$_criteria->compare( 'expire_date', $this->expire_date );
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