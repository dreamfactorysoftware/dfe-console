<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Library\Fabric\Database\Enums\GuestLocations;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Utility\IfSet;
use DreamFactory\Library\Utility\Inflector;
use Illuminate\Filesystem\FilesystemAdapter;

/**
 * Instance storage services
 */
class InstanceStorageService extends BaseService
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const CHARACTER_PATTERN = '/[^a-zA-Z0-9]/';
    /**
     * @type string
     */
    const HOST_NAME_PATTERN = "/^([a-zA-Z0-9])+$/";

    /**
     * @type string
     */
    const SNAPSHOT_FILE_SUFFIX = '.snapshot.zip';
    /**
     * @type string
     */
    const STORAGE_FILE_SUFFIX = '.storage.zip';
    /**
     * @type string
     */
    const SQL_FILE_SUFFIX = '.sql';
    /**
     * @type string
     */
    const SQL_ZIP_SUFFIX = '.sql.gz';
    /**
     * @type string The snapshot ID prefix
     */
    const SNAPSHOT_ID_PREFIX = 'ess';

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
    protected $_privatePathName = ConsoleDefaults::PRIVATE_PATH_NAME;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Init
     */
    public function boot()
    {
        $this->_privatePathName = config( 'dfe.provisioning.private-path-base', ConsoleDefaults::PRIVATE_PATH_NAME );
    }

    /**
     * @param Instance $instance
     *
     * @return string
     */
    protected function _getStorageRoot( Instance $instance )
    {
        return $this->buildStoragePath( $instance );
    }

    /**
     * @param Instance $instance
     *
     * @return string
     */
    public function getStoragePath( Instance $instance )
    {
        return $this->_getStorageRoot( $instance ) . DIRECTORY_SEPARATOR . $instance->instance_id_text;
    }

    /**
     * @param Instance $instance
     *
     * @return string
     */
    public function getSnapshotPath( Instance $instance )
    {
        return $this->_getStorageRoot( $instance ) . DIRECTORY_SEPARATOR . $this->_privatePathName .
        DIRECTORY_SEPARATOR . config( 'dfe.provisioning.snapshot-path', ConsoleDefaults::SNAPSHOT_PATH_NAME );
    }

    /**
     * We want the private path of the instance to point to the user's area. Instances have no "private path" per se.
     *
     * @param Instance $instance
     *
     * @return mixed
     */
    public function getPrivatePath( Instance $instance )
    {
        return $this->getStoragePath( $instance ) . DIRECTORY_SEPARATOR . $this->_privatePathName;
    }

    /**
     * @param Instance $instance
     *
     * @return array
     */
    public function getStorageMap( Instance $instance )
    {
        if ( !isset( $instance->instance_data_text ) || null === ( $_map = IfSet::get( $instance->instance_data_text, 'storage-map' ) ) )
        {
            if ( empty( $instance->instance_data_text ) )
            {
                $instance->instance_data_text = [];
            }

            //  Non-hosted has no structure, just storage
            if ( GuestLocations::LOCAL == $instance->guest_location_nbr || 'localhost' == $instance->db_host_text )
            {
                $_map = [
                    'zone'      => null,
                    'partition' => null,
                    'root-hash' => null,
                ];
            }
            else
            {
                $_rootHash = $instance->user->getHash();
                $_partition = substr( $_rootHash, 0, 2 );

                $_zone = null;

                switch ( config( 'dfe.provisioning.storage-zone-type' ) )
                {
                    case 'dynamic':
                        switch ( $instance->guest_location_nbr )
                        {
                            case GuestLocations::AMAZON_EC2:
                            case GuestLocations::DFE_CLUSTER:
                                if ( file_exists( '/usr/bin/ec2metadata' ) )
                                {
                                    $_zone = str_replace( 'availability-zone: ', null, `/usr/bin/ec2metadata | grep zone` );
                                }
                                break;

                            default:
                                //  All others get name of guest
                                $_zone = Inflector::neutralize( GuestLocations::nameOf( $instance->guest_location_nbr ) );
                                break;
                        }
                        break;

                    case 'static':
                        $_zone = config( 'dfe.provisioning.static-zone-name' );
                        break;
                }

                if ( empty( $_zone ) || empty( $_partition ) )
                {
                    throw new \RuntimeException( 'Zone and/or partition unknown. Cannot provision storage.' );
                }

                $_map = [
                    'zone'      => $_zone,
                    'partition' => $_partition,
                    'root-hash' => $_rootHash,
                ];
            }

            $instance->instance_data_text = array_merge( $instance->instance_data_text, ['storage-map' => $_map] );
        }

        return $_map;
    }

    /**
     * Returns the relative root directory of this instance's storage
     *
     * @param string $path
     * @param string $tag
     *
     * @return FilesystemAdapter
     */
    public function getRootStorageMount( $path = null, $tag = null )
    {
        if ( !$this->webServer )
        {
            throw new InstanceException( 'No configured web server for instance.' );
        }

        $_mount = $this->webServer->mount;

        return $_mount->getFilesystem( $path ?: $this->buildStoragePath(), $tag ?: 'root-storage-mount' );
    }

    /**
     * Returns the relative root directory of this instance's storage
     *
     * @param string $append
     * @param string $tag
     *
     * @return FilesystemAdapter
     */
    public function getSnapshotMount( $append = null, $tag = null )
    {
        $_path = $this->buildStoragePath()
        $_path = $this->getOwnerPrivateStorageMount(
            config( 'dfe.provisioning.snapshot-path', ConsoleDefaults::SNAPSHOT_PATH_NAME )
        );

        return $_path;
    }

    /**
     * @param string $append
     * @param string $tag
     *
     * @return FilesystemAdapter
     */
    public function getStorageMount( $append = null, $tag = null )
    {
        $_path = $this->buildStoragePath(
            $this->instance_id_text .
            ( $append ? ltrim( $append, ' ' . DIRECTORY_SEPARATOR ) : null )
        );

        $_mount = $this->getRootStorageMount(
            $_path,
            ( $tag ?: 'storage-mount' )
        );

        return $_mount;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getPrivateStorageMount()
    {
        $_path = $this->getStorageMount(
            $this->_privatePathName,
            'private-storage'
        );

        return $_path;
    }

    /**
     * @param string $append
     *
     * @return FilesystemAdapter
     */
    public function getOwnerPrivateStorageMount( $append = null )
    {
        $_path =
            $this->_privatePathName . ( $append ? DIRECTORY_SEPARATOR . ltrim( $append, ' ' . DIRECTORY_SEPARATOR ) : $append );

        $_mount = $this->getRootStorageMount( $_path, 'owner-private-storage' );

        return $_mount;
    }

    /**
     * @param Instance $instance
     * @param string   $append If supplied, appended to path name
     *
     * @return string
     */
    public function buildStoragePath( Instance $instance, $append = null )
    {
        $_map = $this->getStorageMap( $instance );

        $_base =
            GuestLocations::LOCAL !== $instance->guest_location_nbr
                ? implode( DIRECTORY_SEPARATOR, [$_map['zone'], $_map['partition'], $_map['root-hash']] )
                : storage_path();

        return $_base . ( $append ? DIRECTORY_SEPARATOR . ltrim( $append, ' ' . DIRECTORY_SEPARATOR ) : $append );
    }
}