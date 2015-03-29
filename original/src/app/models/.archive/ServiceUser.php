<?php
/**
 * This is the model class for table "service_user_t".
 *
 * @property integer         $id
 * @property string          $first_name_text
 * @property string          $last_name_text
 * @property string          $email_addr_text
 * @property string          $password_text
 * @property string          $last_login_date
 * @property string          $create_date
 * @property string          $lmod_date
 *
 * The followings are the available model relations:
 * @property ServiceToken[]  $tokens
 */
class ServiceUser extends \DreamFactory\Yii\Models\DreamUserModel
{
	/**
	 * Returns the model for the class
	 *
	 * @param string $className
	 *
	 * @return \ServiceUser
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
		return 'service_user_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'first_name_text, last_name_text, email_addr_text, password_text', 'required' ),
			array( 'first_name_text, last_name_text, password_text', 'length', 'max' => 64 ),
			array( 'email_addr_text', 'length', 'max' => 320 ),
			array( 'last_login_date', 'safe' ),
			array(
				'id, first_name_text, last_name_text, email_addr_text, last_login_date, create_date, lmod_date',
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
			'tokens' => array( self::HAS_MANY, 'ServiceToken', 'user_id' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'              => 'ID',
			'first_name_text' => 'First Name',
			'last_name_text'  => 'Last Name',
			'email_addr_text' => 'Email Address',
			'password_text'   => 'Password',
			'last_login_date' => 'Last Login Date',
			'create_date'     => 'Create Date',
			'lmod_date'       => 'Modified Date',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new CDbCriteria;

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'first_name_text', $this->first_name_text, true );
		$_criteria->compare( 'last_name_text', $this->last_name_text, true );
		$_criteria->compare( 'email_addr_text', $this->email_addr_text, true );
		$_criteria->compare( 'password_text', $this->password_text, true );
		$_criteria->compare( 'last_login_date', $this->last_login_date, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new CActiveDataProvider( $this, array(
				'criteria' => $_criteria,
			)
		);
	}

	/**
	 * User lookup/authentication
	 *
	 * @param string $userName
	 * @param string $password
	 *
	 * @return bool|int The user ID or false when not found
	 */
	public static function authenticate( $userName, $password )
	{
		$userName = trim( strtolower( $userName ) );

		$_sql = <<<SQL
SELECT
	id
FROM
	service_user_t
WHERE
	email_addr_text = :email_addr_text AND
	password_text = sha2(:password_text,512)
SQL;

		$_userId = \Kisma\Core\Utility\Sql::scalar(
			$_sql,
			0,
			array(
				':email_addr_text' => $userName,
				':password_text'   => $password,
			),
			\Yii::app()->db->getPdoInstance()
		);

		if ( empty( $_userId ) )
		{
			return false;
		}

		$_result = ServiceUser::model()->updateByPk( $_userId,
			array(
				'last_login_date'    => time(),
				'last_login_ip_text' => $_SERVER['REMOTE_ADDR'],
			)
		);

		if ( empty( $_result ) )
		{
			\Kisma\Core\Utility\Log::warning( 'Error updating last_login information in ServiceUser record.' );
		}

		\Kisma\Core\Utility\Log::debug( 'User login: ' . $userName );

		return $_userId;
	}
}