<?php
use Cerberus\Yii\Models\BaseFabricAuthModel;

/**
 * This is the model for table "global_provider_t".
 *
 * @property integer $id
 * @property string  $provider_name_text
 * @property string  $endpoint_text
 * @property string  $config_text
 * @property int     $enable_ind
 * @property string  $create_date
 * @property string  $lmod_date
 */
class GlobalProvider extends BaseFabricAuthModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'global_provider_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'provider_name_text, endpoint_text, enable_ind', 'required' ),
			array( 'enable_ind', 'numerical', 'integerOnly' => true ),
			array( 'config_text', 'safe' ),
		);
	}

	/**
	 * @return array
	 * @return array
	 */
	public function behaviors()
	{
		return array_merge(
			parent::behaviors(),
			array(
				 //	Secure JSON
				 'base_platform_model.secure_json' => array(
					 'class'            => 'SecureJson',
					 'salt'             => Yii::app()->params['auth.salt'],
					 'secureAttributes' => array(
						 'config_text',
					 )
				 ),
			)
		);
	}

	/**
	 * @param array $additionalLabels
	 *
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels( $additionalLabels = array() )
	{
		return array_merge(
			array(
				 'id'                 => 'ID',
				 'provider_name_text' => 'Provider Name',
				 'endpoint_text'      => 'Fabric API Name',
				 'config_text'        => 'Configuration',
				 'enable_ind'         => 'Enabled',
				 'create_date'        => 'Create Date',
				 'lmod_date'          => 'Lmod Date',
			),
			$additionalLabels
		);
	}
}
