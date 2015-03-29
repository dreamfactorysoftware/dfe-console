<?php
/**
 * UserSshKey.php
 */
use DreamFactory\Yii\Models\BaseModel;

/**
 * This is the model class for table "user_ssh_key_t".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string  $key_name_text
 * @property string  $fingerprint_text
 * @property string  $ssh_key_text
 * @property string  $create_date
 * @property string  $lmod_date
 */
class UserSshKey extends BaseModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \UserSshKey the static model class
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
		return 'user_ssh_key_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'ssh_key_text, user_id, key_name_text, fingerprint_text', 'required' ),
			array( 'user_id', 'numerical', 'integerOnly' => true ),
			array( 'id, user_id, ssh_key_text, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'               => 'ID',
			'user_id'          => 'User ID',
			'ssh_key_text'     => 'SSH Key',
			'key_name_text'    => 'Name',
			'fingerprint_text' => 'Fingerprint',
			'create_date'      => 'Create Date',
			'lmod_date'        => 'Modified Date',
		);
	}

	/**
	 * Gets the fingerprint for an ssh key
	 */
	protected function _fingerprint( $key )
	{
		$_file = \tempnam( '/tmp', 'dfkc_' );
		\file_put_contents( $_file, trim( $key, ' ' . PHP_EOL ) );
		$_print = \shell_exec( '/usr/bin/ssh-keygen -lf ' . escapeshellarg( $_file ) . ' | awk \'{print $2}\'' );
		\unlink( $_file );

		return $_print;
	}

	/**
	 * Happens before we validate
	 */
	public function beforeValidate()
	{
		$this->ssh_key_text = str_replace( array( PHP_EOL, '\\n', '\\r' ), null, $this->ssh_key_text );

		$_value = trim( $this->_fingerprint( $this->ssh_key_text ), PHP_EOL );

		if ( empty( $_value ) || 47 != strlen( $_value ) )
		{
			$this->addError( 'ssh_key_text', 'Invalid public SSH key' );

			return false;
		}

		$this->fingerprint_text = $_value;

		if ( empty( $this->user_id ) )
		{
			$this->user_id = \DreamFactory\Yii\Utility\Pii::user()->getId();
		}

		return parent::beforeValidate();
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @return \CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$_criteria = new \CDbCriteria;

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'user_id', $this->user_id );
		$_criteria->compare( 'ssh_key_text', $this->ssh_key_text, true );
		$_criteria->compare( 'key_name_text', $this->key_name_text, true );
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