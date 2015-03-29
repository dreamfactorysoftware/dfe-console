<?php
use Cerberus\Enums\DSP;
use Cerberus\Services\Provisioning\BaseProvisioner;
use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\BaseFabricDeploymentModel;
use DreamFactory\Enums\Architectures;
use DreamFactory\Interfaces\InstanceStates;
use DreamFactory\Yii\Exceptions\RestException;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Exceptions\ServiceException;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;

/**
 * This is the model for table "instance_t"
 *
 * @property integer            $id
 * @property integer            $user_id
 * @property integer            $vendor_id
 * @property integer            $vendor_image_id
 * @property integer            $vendor_credentials_id
 * @property integer            $guest_location_nbr
 * @property string             $instance_id_text
 * @property string             $db_name_text
 * @property string             $db_user_text
 * @property string             $db_password_text
 * @property string             $storage_id_text
 * @property integer            $flavor_nbr
 * @property string             $base_image_text
 * @property string             $instance_name_text
 * @property string             $region_text
 * @property string             $availability_zone_text
 * @property string             $security_group_text
 * @property string             $ssh_key_text
 * @property integer            $root_device_type_nbr
 * @property string             $public_host_text
 * @property string             $public_ip_text
 * @property string             $private_host_text
 * @property string             $private_ip_text
 * @property string             $request_id_text
 * @property string             $request_date
 * @property integer            $deprovision_ind
 * @property integer            $provision_ind
 * @property integer            $trial_instance_ind
 * @property integer            $state_nbr
 * @property integer            $vendor_state_nbr
 * @property string             $vendor_state_text
 * @property integer            $environment_id
 * @property string             $start_date
 * @property string             $end_date
 * @property string             $terminate_date
 * @property string             $create_date
 * @property string             $lmod_date
 *
 * Relations:
 * @property Environment        $environment
 * @property VendorCredentials  $vendorCredentials
 * @property Vendor             $vendor
 * @property VendorImage        $vendorImage
 * @property User               $user
 */
class Instance extends BaseFabricDeploymentModel implements InstanceStates
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var int
	 */
	const AMAZON_HOSTED = 0;
	/**
	 * @var int
	 */
	const FABRIC_HOSTED = 1;
	/**
	 * @var int
	 */
	const AZURE_HOSTED = 2;
	/**
	 * @var int
	 */
	const RACKSPACE_HOSTED = 3;

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @param string $className active record class name.
	 *
	 * @return \Cerberus\Yii\Models\Deploy\Instance
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
		return 'instance_t';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array( 'user_id, vendor_id, vendor_credentials_id, storage_id_text', 'required' ),
			array(
				'user_id, vendor_id, vendor_image_id, vendor_credentials_id, guest_location_nbr, flavor_nbr, root_device_type_nbr, deprovision_ind, provision_ind, trial_instance_ind, state_nbr, vendor_state_nbr, environment_id',
				'numerical',
				'integerOnly' => true
			),
			array(
				'instance_id_text, db_name_text, db_user_text, db_password_text, storage_id_text, ssh_key_text, vendor_state_text',
				'length',
				'max' => 64
			),
			array( 'base_image_text, region_text, availability_zone_text', 'length', 'max' => 32 ),
			array( 'instance_name_text, request_id_text', 'length', 'max' => 128 ),
			array( 'security_group_text', 'length', 'max' => 1024 ),
			array( 'public_host_text, private_host_text', 'length', 'max' => 256 ),
			array( 'public_ip_text, private_ip_text', 'length', 'max' => 20 ),
			array( 'request_date, start_date, end_date, terminate_date', 'safe' ),
			array(
				'id, user_id, vendor_id, vendor_image_id, vendor_credentials_id, guest_location_nbr, instance_id_text, db_name_text, db_user_text, db_password_text, storage_id_text, flavor_nbr, base_image_text, instance_name_text, region_text, availability_zone_text, security_group_text, ssh_key_text, root_device_type_nbr, public_host_text, public_ip_text, private_host_text, private_ip_text, request_id_text, request_date, deprovision_ind, provision_ind, trial_instance_ind, state_nbr, vendor_state_nbr, vendor_state_text, environment_id, start_date, end_date, terminate_date, create_date, lmod_date',
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
			'environment' => array( self::BELONGS_TO, 'Environment', 'environment_id' ),
			'credentials' => array( self::BELONGS_TO, 'VendorCredentials', 'vendor_credentials_id' ),
			'vendor'      => array( self::BELONGS_TO, 'Vendor', 'vendor_id' ),
			'image'       => array( self::BELONGS_TO, 'VendorImage', 'vendor_image_id' ),
			'user'        => array( self::BELONGS_TO, '\\Cerberus\\Yii\\Models\\Auth\\User', 'user_id' ),
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
			'vendor_id'              => 'Vendor',
			'vendor_image_id'        => 'Vendor Image',
			'vendor_credentials_id'  => 'Vendor Credentials',
			'guest_location_nbr'     => 'Guest Location',
			'instance_id_text'       => 'Instance Id',
			'db_name_text'           => 'Db Name',
			'db_user_text'           => 'Db User',
			'db_password_text'       => 'Db Password',
			'storage_id_text'        => 'Storage Id',
			'flavor_nbr'             => 'Flavor',
			'base_image_text'        => 'Base Image',
			'instance_name_text'     => 'Instance Name',
			'region_text'            => 'Region',
			'availability_zone_text' => 'Availability Zone',
			'security_group_text'    => 'Security Group',
			'ssh_key_text'           => 'Ssh Key',
			'root_device_type_nbr'   => 'Root Device Type',
			'public_host_text'       => 'Public Host',
			'public_ip_text'         => 'Public Ip',
			'private_host_text'      => 'Private Host',
			'private_ip_text'        => 'Private Ip',
			'request_id_text'        => 'Request Id',
			'request_date'           => 'Request Date',
			'deprovision_ind'        => 'Deprovision',
			'provision_ind'          => 'Provision',
			'trial_instance_ind'     => 'Trial Instance',
			'state_nbr'              => 'State',
			'vendor_state_nbr'       => 'Vendor State',
			'vendor_state_text'      => 'Vendor State',
			'environment_id'         => 'Environment',
			'start_date'             => 'Start Date',
			'end_date'               => 'End Date',
			'terminate_date'         => 'Terminate Date',
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

		$_criteria->compare( 'id', $this->id );
		$_criteria->compare( 'user_id', $this->user_id );
		$_criteria->compare( 'vendor_id', $this->vendor_id );
		$_criteria->compare( 'vendor_image_id', $this->vendor_image_id );
		$_criteria->compare( 'vendor_credentials_id', $this->vendor_credentials_id );
		$_criteria->compare( 'guest_location_nbr', $this->guest_location_nbr );
		$_criteria->compare( 'instance_id_text', $this->instance_id_text, true );
		$_criteria->compare( 'db_name_text', $this->db_name_text, true );
		$_criteria->compare( 'db_user_text', $this->db_user_text, true );
		$_criteria->compare( 'db_password_text', $this->db_password_text, true );
		$_criteria->compare( 'storage_id_text', $this->storage_id_text, true );
		$_criteria->compare( 'flavor_nbr', $this->flavor_nbr );
		$_criteria->compare( 'base_image_text', $this->base_image_text, true );
		$_criteria->compare( 'instance_name_text', $this->instance_name_text, true );
		$_criteria->compare( 'region_text', $this->region_text, true );
		$_criteria->compare( 'availability_zone_text', $this->availability_zone_text, true );
		$_criteria->compare( 'security_group_text', $this->security_group_text, true );
		$_criteria->compare( 'ssh_key_text', $this->ssh_key_text, true );
		$_criteria->compare( 'root_device_type_nbr', $this->root_device_type_nbr );
		$_criteria->compare( 'public_host_text', $this->public_host_text, true );
		$_criteria->compare( 'public_ip_text', $this->public_ip_text, true );
		$_criteria->compare( 'private_host_text', $this->private_host_text, true );
		$_criteria->compare( 'private_ip_text', $this->private_ip_text, true );
		$_criteria->compare( 'request_id_text', $this->request_id_text, true );
		$_criteria->compare( 'request_date', $this->request_date, true );
		$_criteria->compare( 'deprovision_ind', $this->deprovision_ind );
		$_criteria->compare( 'provision_ind', $this->provision_ind );
		$_criteria->compare( 'trial_instance_ind', $this->trial_instance_ind );
		$_criteria->compare( 'state_nbr', $this->state_nbr );
		$_criteria->compare( 'vendor_state_nbr', $this->vendor_state_nbr );
		$_criteria->compare( 'vendor_state_text', $this->vendor_state_text, true );
		$_criteria->compare( 'environment_id', $this->environment_id );
		$_criteria->compare( 'start_date', $this->start_date, true );
		$_criteria->compare( 'end_date', $this->end_date, true );
		$_criteria->compare( 'terminate_date', $this->terminate_date, true );
		$_criteria->compare( 'create_date', $this->create_date, true );
		$_criteria->compare( 'lmod_date', $this->lmod_date, true );

		return new \CActiveDataProvider(
			$this,
			array(
				 'criteria' => $_criteria,
			)
		);
	}

	/**
	 * @param \CModelEvent $event
	 */
	public function onBeforeValidate( $event )
	{
		//	Insert user ID
		if ( empty( $this->user_id ) && Pii::user() )
		{
			$this->user_id = Pii::user()->getId();
		}

		parent::onBeforeValidate( $event );
	}

	/**
	 * @param \Cerberus\Yii\Models\Auth\User $owner
	 * @param string                         $name
	 * @param array                          $config
	 *
	 * @return bool
	 * @throws CDbException
	 */
	public static function launchTrial( $owner, $name, $config = array() )
	{
		return static::launch( $owner, $name, $config, true );
	}

	/**
	 * @param \Cerberus\Yii\Models\Auth\User $owner
	 * @param string                         $name
	 * @param array                          $config
	 * @param bool                           $trial
	 * @param bool                           $fabricHosted
	 *
	 * @throws \CDbException
	 * @return bool
	 */
	public static function launch( $owner, $name, $config = array(), $trial = false, $fabricHosted = true )
	{
		if ( true === ( $_restart = Option::get( $config, 'restart', false ) ) )
		{
			$_model = $config['instance'];
			$_model->state_nbr = static::Created;
			$_model->provision_ind = 1;
			$_model->deprovision_ind = 0;
		}
		else
		{
			$_model = new self;
			$_model->user_id = $owner->id;
			$_model->vendor_id = 2; //	DreamFactory
			$_model->vendor_image_id = 4647; //	Ubuntu server 12.04.1 i386
			$_model->vendor_credentials_id = 0; //	DreamFactory account
			$_model->state_nbr = static::Created;
			$_model->vendor_credentials_id = 0; //	DreamFactory account
			$_model->flavor_nbr = Architectures::i386;
			$_model->trial_instance_ind = ( $trial ? 1 : 0 );
			$_model->guest_location_nbr = ( true === $fabricHosted ? static::FABRIC_HOSTED : static::AMAZON_HOSTED );
			$_model->instance_name_text = $name;
			$_model->create_date = date( 'Y-m-d H:i:s' );
		}

		try
		{
			$_model->save();

			try
			{
				$_config = $config
					? : array(
						'cpu'  => 0,
						'disk' => 4,
						'blob' => 4,
					);

				$_model->provision( $_config );
			}
			catch ( ServiceException $_ex )
			{
				return false;
			}
		}
		catch ( \Exception $_ex )
		{
			//	Log it...
			Log::error( 'Error during save: ' . $_ex->getMessage() );

			throw new \CDbException( 'Error: ' . $_ex->getMessage() );
		}

		return true;
	}

	/**
	 * Queues up a provisioning request
	 */
	public function provision( $payload = array() )
	{
		if ( !empty( $this->instance_id_text ) )
		{
			throw new \InvalidArgumentException( 'This platform is already provisioned.' );
		}

		if ( empty( $payload ) )
		{
			$payload = array();
		}
		else if ( !is_array( $payload ) )
		{
			$payload = (array)$payload;
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$_id = Pii::controller()->queueWork( array_merge( $this->getAttributes(), $payload ), 'provision' );

		$this->update(
			array(
				 'provision_ind'     => 0,
				 'vendor_state_nbr'  => static::Provisioning,
				 'state_nbr'         => static::Provisioning,
				 'vendor_state_text' => 'pending',
				 'request_id_text'   => $_id,
				 'request_date'      => date( 'c' ),
			)
		);

		Log::info( 'Provision request queued: ' . $_id );

		return true;
	}

	/**
	 *
	 * Queues up a deprovisioning request
	 */
	public static function deprovision( $user, $instanceName )
	{
		/** @var $_instance \Instance */
		$_instance = null;

		if ( $user->instances )
		{
			foreach ( $user->instances as $_vm )
			{
				if ( $_vm->instance_name_text == $instanceName )
				{
					$_instance = $_vm;
					break;
				}

				unset( $_vm );
			}
		}

		if ( null === $_instance )
		{
			throw new RestException( HttpResponse::NotFound );
		}

		if ( null !== $_instance )
		{
			$_instance->update(
				array(
					 'state_nbr'         => static::Deprovisioning,
					 'vendor_state_nbr'  => static::Deprovisioning,
					 'vendor_state_text' => 'shutting-down',
				)
			);

			$_payload = $_instance->getAttributes();

			Log::debug( 'Destructor chosen -- Deprovision of "' . $_instance->id . '" request: ' . print_r( $_payload, true ) );

			/** @noinspection PhpUndefinedMethodInspection */
			$_id = Pii::controller()->queueWork( $_payload, 'deprovision' );
			Log::info( 'Deprovision of "' . $_instance->id . '" request queued: ' . $_id );
			$_instance->deprovision_ind = 1;

			return $_instance->update();
		}

		Log::error( 'Instance "' . $instanceName . '" not found to deprovision.' );

		return false;
	}

	/**
	 * @return array|void
	 */
	public function restMap()
	{
		return array(
			'id'                     => 'id',
			'user_id'                => 'userId',
			'vendor_id'              => 'vendorId',
			'vendor_image_id'        => 'vendorImageId',
			'vendor_credentials_id'  => 'vendorCredentialsId',
			'instance_id_text'       => 'instanceId',
			'instance_name_text'     => 'instanceName',
			'environment_name_text'  => 'environment',
			'flavor_nbr'             => 'flavor',
			'region_text'            => 'region',
			'availability_zone_text' => 'availabilityZone',
			'security_group_text'    => 'securityGroup',
			'ssh_key_text'           => 'sshKey',
			'root_device_type_nbr'   => 'rootDeviceType',
			'public_host_text'       => 'dnsName',
			'public_ip_text'         => 'publicIpAddress',
			'private_host_text'      => 'privateDnsName',
			'private_ip_text'        => 'privateIpAddress',
			'deprovision_ind'        => 'deprovisioned',
			'provision_ind'          => 'provisioned',
			'trial_instance_ind'     => 'trial',
			'state_nbr'              => 'instanceState',
			'vendor_state_nbr'       => 'vendorState',
			'vendor_state_text'      => 'vendorStateName',
			'start_date'             => 'startDate',
			'end_date'               => 'endDate',
			'terminate_date'         => 'terminationDate',
			'create_date'            => 'createDate',
			'lmod_date'              => 'lastModifiedDate',
		);
	}

	/**
	 * Changes just the state of the current instance
	 *
	 * @param int $state
	 *
	 * @return bool
	 */
	public function updateState( $state = self::Created )
	{
		if ( !$this->isNewRecord )
		{
			$this->state_nbr = $state;

			return $this->update( array( 'state_nbr' => $state ) );
		}

		return false;
	}

	/**
	 * @param string $name
	 *
	 * @throws RestException
	 * @return string
	 */
	public static function sanitizeInstanceName( $name )
	{
		static $_unavailableNames = array();

		$_clean = str_replace(
			array( ' ', '-' ),
			array( '_', '_' ),
			trim(
				str_replace(
					array( '--', '__' ),
					array( '_', '_' ),
					preg_replace( BaseProvisioner::CharacterPattern, '_', $name )
				),
				' -_'
			)
		);

		if ( null === $_unavailableNames )
		{
			$_unavailableNames = @include( \Kisma::get( 'app.config_path' ) . '/unavailable_names.config.php' );

			if ( empty( $_unavailableNames ) )
			{
				$_unavailableNames = array();
			}
		}

		//	Check host name
		if ( preg_match_all( BaseProvisioner::HostPattern, $_clean ) )
		{
			Log::debug( 'Possible attempt to register invalid DSP name: ' . $name . ' => ' . $_clean );
//			throw new RestException( HttpResponse::BadRequest, 'The name "' . $name . '" contains invalid characters. A-Z, 0-9 and - are allowed.' );
		}

		if ( in_array( $_clean, $_unavailableNames ) )
		{
			Log::warning( 'Attempt to register banned DSP name: ' . $name . ' => ' . $_clean );

			throw new RestException( HttpResponse::BadRequest, 'The name "' . $name . '" is not available.' );
		}

		return $_clean;
	}

	/**
	 * Scope to look up by name or ID
	 *
	 * @param string $name
	 * @param string $id
	 *
	 * @return $this
	 */
	public function byNameOrId( $name = null, $id = null )
	{
		$_params = $_condition = array();

		if ( null !== $name )
		{
			$_condition[] = 'instance_name_text = :instance_name_text';
			$_params[':instance_name_text'] = $name;
		}

		if ( null !== $id )
		{
			$_condition[] = 'instance_id_text = :instance_id_text';
			$_params[':instance_id_text'] = $id;
		}

		$this->getDbCriteria()->mergeWith(
			array(
				 'condition' => implode( ' OR ', $_condition ),
				 'params'    => $_params,
			)
		);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStoragePath()
	{
		return str_ireplace( static::FABRIC_STORAGE_KEY, $this->storage_id_text, DSP::FABRIC_BASE_STORAGE_PATH );
	}

	/**
	 * @return mixed
	 */
	public function getBlobStoragePath()
	{
		return str_ireplace( static::FABRIC_STORAGE_KEY, $this->storage_id_text, DSP::FABRIC_INSTANCE_BLOB_PATH );
	}

	/**
	 * @return mixed
	 */
	public function getSnapshotPath()
	{
		//	Snapshots are global to the user
		return $this->user->getSnapshotPath();
	}

}