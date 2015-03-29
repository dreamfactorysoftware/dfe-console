<?php
namespace Cerberus\Yii\Models\Deploy;

use Cerberus\Enums\ProvisionerFeatures;
use Cerberus\Yii\Models\BaseFabricDeploymentModel;
use Kisma\Core\Utility\Option;

/**
 * This is the model for table "vendor_t"
 *
 * @property integer              $id
 * @property string               $vendor_name_text
 * @property string               $features_text
 * @property string               $create_date
 * @property string               $lmod_date
 *
 * Relations:
 * @property Instance[]           $instances
 * @property VendorCredentials[]  $vendorCredentials
 * @property VendorImage[]        $vendorImages
 */
class Vendor extends BaseFabricDeploymentModel
{
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return Vendor the static model class
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
		return 'vendor_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'vendor_name_text, create_date, lmod_date', 'required' ),
			array( 'vendor_name_text', 'length', 'max' => 48 ),
			array( 'id, vendor_name_text, create_date, lmod_date', 'safe', 'on' => 'search' ),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'instances'         => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\Instance', 'vendor_id' ),
			'vendorCredentials' => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\VendorCredentials', 'vendor_id' ),
			'vendorImages'      => array( self::HAS_MANY, 'Cerberus\\Yii\\Models\\Deploy\\VendorImage', 'vendor_id' ),
		);
	}

	/**
	 * @param int $feature
	 *
	 * @throws \InvalidArgumentException
	 * @return array|\stdClass
	 */
	public function hasFeature( $feature )
	{
		if ( !ProvisionerFeatures::contains( $feature ) )
		{
			throw new \InvalidArgumentException( 'The feature "' . $feature . '" is invalid.' );
		}

		return Option::get( $this->feature_text, $feature, false );
	}

	/**
	 * {@InheritDoc}
	 */
	protected function beforeSave()
	{
		if ( !empty( $this->features_text ) && !is_string( $this->features_text ) )
		{
			$this->features_text = serialize( $this->features_text );
		}

		return parent::beforeSave();
	}

	/**
	 * {@InheritDoc}
	 */
	protected function afterFind()
	{
		if ( !empty( $this->features_text ) && is_string( $this->features_text ) )
		{
			$this->features_text = unserialize( $this->features_text );
		}

		parent::afterFind();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'               => 'ID',
			'vendor_name_text' => 'Vendor Name',
			'features_text'    => 'Features',
			'create_date'      => 'Create Date',
			'lmod_date'        => 'Last Modified Date',
		);
	}

}