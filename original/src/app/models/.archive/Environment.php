<?php
/**
 * Environment.php
 */
/**
 * This is the model class for table "environment_t".
 *
 * @property integer            $id
 * @property string             $environment_name_text
 * @property string             $create_date
 * @property string             $lmod_date
 */
class Environment extends BaseDeploymentModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \Environment
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
		return 'environment_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'id, environment_name_text', 'required' ),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                    => 'ID',
			'environment_name_text' => 'Name',
			'user_id'               => 'Owner',
			'create_date'           => 'Created',
			'lmod_date'             => 'Modified',
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'vendorCredentials' => array( self::HAS_MANY, 'VendorCredentials', 'environment_id' ),
		);
	}
}