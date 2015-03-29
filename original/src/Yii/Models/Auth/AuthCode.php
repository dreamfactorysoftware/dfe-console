<?php
namespace Cerberus\Yii\Models\Auth;

use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "auth_code_t"
 *
 * @property integer    $id
 * @property string     $code_text
 * @property integer    $owner_id
 * @property string     $scope_text
 * @property string     $client_id_text
 * @property string     $redirect_uri_text
 * @property integer    $expire_date
 * @property string     $create_date
 * @property string     $lmod_date
 *
 * Relations:
 * @property AuthOwnerT $owner
 */
class AuthCode extends BaseFabricAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return AuthCodeT the static model class
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
		return 'auth_code_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'code_text, owner_id, client_id_text, redirect_uri_text, expire_date, create_date, lmod_date', 'required' ),
			array( 'owner_id, expire_date', 'numerical', 'integerOnly' => true ),
			array( 'code_text', 'length', 'max' => 64 ),
			array( 'scope_text, client_id_text, redirect_uri_text', 'length', 'max' => 256 ),
			array(
				'id, code_text, owner_id, scope_text, client_id_text, redirect_uri_text, expire_date, create_date, lmod_date',
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
			'id'                => 'ID',
			'code_text'         => 'Code',
			'owner_id'          => 'Owner',
			'scope_text'        => 'Scope',
			'client_id_text'    => 'Client Id',
			'redirect_uri_text' => 'Redirect Uri',
			'expire_date'       => 'Expire Date',
			'create_date'       => 'Create Date',
			'lmod_date'         => 'Last Modified Date',
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
		$_criteria->compare( 'code_text', $this->code_text, true );
		$_criteria->compare( 'owner_id', $this->owner_id );
		$_criteria->compare( 'scope_text', $this->scope_text, true );
		$_criteria->compare( 'client_id_text', $this->client_id_text, true );
		$_criteria->compare( 'redirect_uri_text', $this->redirect_uri_text, true );
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