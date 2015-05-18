<?php namespace DreamFactory\Enterprise\Services\Utility;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Library\Fabric\Common\Utility\Json;
use DreamFactory\Enterprise\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use DreamFactory\Library\Utility\Inflector;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class InstanceMetadata implements Jsonable, Arrayable
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_instanceId;
    /**
     * @type string
     */
    protected $_storageKey;
    /**
     * @type int|string
     */
    protected $_clusterId;
    /**
     * @type int|string
     */
    protected $_dbServerId;
    /**
     * @type int|string
     */
    protected $_appServerId;
    /**
     * @type int|string
     */
    protected $_webServerId;
    /**
     * @type int
     */
    protected $_ownerId;
    /**
     * @type string
     */
    protected $_ownerEmailAddress;
    /**
     * @type string
     */
    protected $_ownerStorageKey;
    /**
     * @type array [:db-config]
     */
    protected $_db = [];
    /**
     * @type array [:zone-info]
     */
    protected $_storageMap = [];
    /**
     * @type array [:path-name => :relative-path]
     */
    protected $_paths = [];
    /** @type array ['key' => 'value...] of hosted environment information */
    protected $_env = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $instanceId
     * @param array  $values
     */
    public function __construct( $instanceId, array $values = [] )
    {
        $_instance = $this->_findInstance( $instanceId );
        $this->_instanceId = $_instance->instance_id_text;

        foreach ( $values as $_key => $_value )
        {
            if ( false !== strpos( $_key, '-' ) )
            {
                $_key = Inflector::deneutralize( $_key, true, '-' );
            }

            if ( property_exists( $this, $_key ) )
            {
                $this->{$_key} = $_value;
            }
            else if ( property_exists( $this, '_' . $_key ) )
            {
                $this->{'_' . $_key} = $_value;
            }
            else if ( method_exists( $this, 'set' . $_key ) )
            {
                $this->{'set' . $_key}( $_value );
            }
        }
    }

    /**
     * @param Instance $instance
     *
     * @return static
     */
    public static function createFromInstance( Instance $instance )
    {
        return new static( $instance->instance_id_text, $instance->getMetadata( false ) );

    }

    /** @inheritdoc */
    public function toArray()
    {
        return [
            'instance-id'         => $this->_instanceId,
            'storage-key'         => $this->_storageKey,
            'cluster-id'          => $this->_clusterId,
            'db-server-id'        => $this->_dbServerId,
            'app-server-id'       => $this->_appServerId,
            'web-server-id'       => $this->_webServerId,
            'owner-id'            => $this->_ownerId,
            'owner-email-address' => $this->_ownerEmailAddress,
            'owner-storage-key'   => $this->_ownerStorageKey,
            'storage-map'         => $this->_storageMap,
            'db'                  => $this->_db,
            'paths'               => $this->_paths,
            'env'                 => $this->_env,
        ];
    }

    /** @inheritdoc */
    public function toJson( $options = 0 )
    {
        return Json::encode( $this->toArray(), $options );
    }

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem $filesystem
     *
     * @return mixed
     */
    public function load( Filesystem $filesystem )
    {
        $_file =
            config( 'dfe.provisioning.private-path-name', ConsoleDefaults::PRIVATE_PATH_NAME ) . DIRECTORY_SEPARATOR . $this->_instanceId . '.json';

        if ( $filesystem->exists( $_file ) )
        {
            return Json::decode( $_json = $filesystem->get( $_file ) );
        }

        \Log::info( 'Building missing instance metadata: ' . $_file );

        return $this->save( $filesystem );
    }

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem $filesystem
     * @param array                                       $values Optional values to merge with metadata for writing
     *
     * @return
     */
    public function save( Filesystem $filesystem, array $values = null )
    {
        $_instance = $this->_findInstance( $this->_instanceId );

        $_map = IfSet::get( $_instance->instance_data_text, 'storage-map' );

        $_values = array_merge(
            $_instance->getMetadata(),
            [
                'storage-map' => $_map,
            ],
            $values ?: []
        );

        $_md = new static( $_instance->instance_id_text, $_values );
        $_file =
            rtrim( config( 'dfe.provisioning.private-path-name', ConsoleDefaults::PRIVATE_PATH_NAME ), ' ' . DIRECTORY_SEPARATOR ) .
            DIRECTORY_SEPARATOR .
            $_instance->instance_id_text .
            '.json';

        $filesystem->put( $_file, $_md->toJson() );

        return $_md->toArray();
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return $this->_instanceId;
    }

    /**
     * @param string $instanceId
     *
     * @return InstanceMetadata
     */
    public function setInstanceId( $instanceId )
    {
        $this->_instanceId = $instanceId;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getWebServerId()
    {
        return $this->_webServerId;
    }

    /**
     * @param int|string $webServerId
     *
     * @return InstanceMetadata
     */
    public function setWebServerId( $webServerId )
    {
        $this->_webServerId = $webServerId;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->_paths;
    }

    /**
     * @param array $paths
     *
     * @return InstanceMetadata
     */
    public function setPaths( $paths )
    {
        $this->_paths = $paths;

        return $this;
    }

    /**
     * @return int
     */
    public function getClusterId()
    {
        return $this->_clusterId;
    }

    /**
     * @param int $clusterId
     *
     * @return InstanceMetadata
     */
    public function setClusterId( $clusterId )
    {
        $this->_clusterId = $clusterId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDbServerId()
    {
        return $this->_dbServerId;
    }

    /**
     * @param int $dbServerId
     *
     * @return InstanceMetadata
     */
    public function setDbServerId( $dbServerId )
    {
        $this->_dbServerId = $dbServerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAppServerId()
    {
        return $this->_appServerId;
    }

    /**
     * @param int $appServerId
     *
     * @return InstanceMetadata
     */
    public function setAppServerId( $appServerId )
    {
        $this->_appServerId = $appServerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->_ownerId;
    }

    /**
     * @param int $ownerId
     *
     * @return InstanceMetadata
     */
    public function setOwnerId( $ownerId )
    {
        $this->_ownerId = $ownerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerEmailAddress()
    {
        return $this->_ownerEmailAddress;
    }

    /**
     * @param string $ownerEmailAddress
     *
     * @return InstanceMetadata
     */
    public function setOwnerEmailAddress( $ownerEmailAddress )
    {
        $this->_ownerEmailAddress = $ownerEmailAddress;

        return $this;
    }

    /**
     * @return array
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * @param array $db
     *
     * @return InstanceMetadata
     */
    public function setDb( $db )
    {
        $this->_db = $db;

        return $this;
    }

    /**
     * @return string
     */
    public function getStorageKey()
    {
        return $this->_storageKey;
    }

    /**
     * @param string $storageKey
     *
     * @return InstanceMetadata
     */
    public function setStorageKey( $storageKey )
    {
        $this->_storageKey = $storageKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerStorageKey()
    {
        return $this->_ownerStorageKey;
    }

    /**
     * @param string $ownerStorageKey
     *
     * @return InstanceMetadata
     */
    public function setOwnerStorageKey( $ownerStorageKey )
    {
        $this->_ownerStorageKey = $ownerStorageKey;

        return $this;
    }

    /**
     * @return array
     */
    public function getStorageMap()
    {
        return $this->_storageMap;
    }

    /**
     * @param array $storageMap
     *
     * @return InstanceMetadata
     */
    public function setStorageMap( $storageMap )
    {
        $this->_storageMap = $storageMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getEnv()
    {
        return $this->_env;
    }

    /**
     * @param array $env
     *
     * @return InstanceMetadata
     */
    public function setEnv( $env )
    {
        $this->_env = $env;

        return $this;
    }

}