<?php
/**
 * This is the model class for table "auth_client_t".
 *
 * The followings are the available columns
 *
 * @property integer    $id
 * @property integer    $owner_id
 * @property string     $client_id_text
 * @property string     $client_secret_text
 * @property string     $redirect_uri_text
 * @property string     $create_date
 * @property string     $lmod_date
 *
 * The followings are the available model relations:
 * @property AuthOwner  $owner
 */
class AuthClient extends BaseAuthModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \AuthClient the static model class
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
		return 'auth_client_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'owner_id, client_id_text, client_secret_text, redirect_uri_text, create_date, lmod_date', 'required' ),
			array( 'owner_id', 'numerical', 'integerOnly' => true ),
			array( 'client_id_text, client_secret_text', 'length', 'max' => 256 ),
			array( 'id, owner_id, client_id_text, client_secret_text, redirect_uri_text, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'owner' => array( self::BELONGS_TO, 'AuthOwner', 'owner_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                 => 'ID',
			'owner_id'           => 'Owner',
			'client_id_text'     => 'Client Id',
			'client_secret_text' => 'Client Secret',
			'redirect_uri_text'  => 'Redirect URI',
			'create_date'        => 'Create Date',
			'lmod_date'          => 'Modified Date',
		);
	}

	/**
	 * Scope to return by client ID
	 *
	 * @param string $clientId
	 *
	 * @return $this
	 */
	public function byClientId( $clientId )
	{
		$this->getDbCriteria()->mergeWith(
			array(
				'condition' => 'client_id_text = :client_id_text',
				'params'    => array(
					':client_id_text' => $clientId,
				),
			)
		);

		return $this;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria();

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'owner_id', $this->owner_id );
		$_criteria->compare( 'client_id_text', $this->client_id_text, true );
		$_criteria->compare( 'client_secret_text', $this->client_secret_text, true );
		$_criteria->compare( 'redirect_uri_text', $this->redirect_uri_text, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider(
			$this,
			array(
				'criteria' => $_criteria,
			)
		);
	}
}