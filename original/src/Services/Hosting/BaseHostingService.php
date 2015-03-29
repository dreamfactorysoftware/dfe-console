<?php
namespace Cerberus\Services\Hosting;

use Cerberus\Services\Provisioning\DreamFactory;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Services\DreamService;
use Kisma\Core\Interfaces\ConsumerLike;

/**
 * BaseHostingService
 * Useful base class for hosting services
 *
 * @author        Jerry Ablan <jerryablan@dreamfactory.com>
 */
class BaseHostingService extends DreamService implements ConsumerLike
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const SNAPSHOT_FILE_SUFFIX = '.snapshot.tar.gz';
    /**
     * @var string
     */
    const STORAGE_FILE_SUFFIX = '.storage.sql.gz';
    /**
     * @var string
     */
    const MYSQLDUMP_FILE_SUFFIX = '.mysql.sql';
    /**
     * @var string
     */
    const SNAPSHOT_GLOB = '/*.*.snapshot.tar.gz';
    /**
     * @var string
     */
    const DEFAULT_SSH_OPTIONS = '-qi /etc/fabric/keys/dsp-snapshot.pem -o StrictHostKeyChecking=no';
    /**
     * @var string
     */
    const DFOPS_SSH_OPTIONS = '-qi ~/.ssh/dfops.pem -o StrictHostKeyChecking=no';
    /**
     * @var string
     */
    const DFADMIN_SSH_OPTIONS = '-qi ~/.ssh/dfadmin.pem -o StrictHostKeyChecking=no';

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var Instance
     */
    protected $_instance;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @return string
     */
    public function getSnapshotPath()
    {
        return $this->_instance->getSnapshotPath();
    }

    /**
     * @return string
     */
    public function getStoragePath()
    {
        return $this->_instance->getStoragePath();
    }

    /**
     * @return string
     */
    public function getBlobStoragePath()
    {
        return $this->_instance->getBlobStoragePath();
    }

    /**
     * @return string
     */
    public function getPrivatePath()
    {
        return $this->_instance->getPrivatePath();
    }

    /**
     * @param \Cerberus\Yii\Models\Deploy\Instance $instance
     *
     * @return BaseHostingService
     */
    public function setInstance( $instance )
    {
        $this->_instance = $instance;

        return $this;
    }

    /**
     * @return \Cerberus\Yii\Models\Deploy\Instance
     */
    public function getInstance()
    {
        return $this->_instance;
    }

    /**
     * @param string $instanceId
     *
     * @return Instance
     * @throws \InvalidArgumentException
     */
    protected function _validateInstance( $instanceId )
    {
        $this->logDebug( 'Validating instance "' . $instanceId . '"' );

        if ( !is_string( $instanceId ) )
        {
            $_instance = $instanceId;
        }
        else
        {
            $_instance = Instance::model()->byNameOrId( $instanceId )->find();
        }

        if ( empty( $_instance ) )
        {
            throw new \InvalidArgumentException( 'The instance "' . $instanceId . '" is invalid.' );
        }

        return $this->_validateRequest( $_instance );
    }

    /**
     * @param \Cerberus\Yii\Models\Deploy\Instance $instance
     *
     * @return \Cerberus\Yii\Models\Deploy\Instance
     * @throws \RuntimeException
     */
    protected function _validateRequest( Instance $instance )
    {
        $this->logDebug( 'Validating instance "' . $instance->instance_id_text . '" request' );

        if ( !isset( $instance->user ) )
        {
            throw new \RuntimeException( 'Invalid instance specified.' );
        }

        $this->_instance = $instance;

        $this->logDebug(
            'Request validated: ' .
            print_r(
                array(
                    'instance'      => $instance->getRestAttributes(),
                    'storage_path'  => $this->getStoragePath(),
                    'snapshot_path' => $this->getSnapshotPath(),
                    'private_path'  => $this->getPrivatePath(),
                ),
                true
            )
        );

        return $instance;
    }

    /**
     * @param string $path
     * @param bool   $force
     *
     * @return bool
     */
    protected function _rmdir( $path, $force = false )
    {
        $force = $force ? '-rf' : '-r';

        $_path = trim( $path, ' /' );

        if ( '/data' != substr( $_path, 0, 5 ) )
        {
            return false;
        }

        return ( 0 == `/bin/rm $force $path; echo $?` );
    }

    /**
     * @param string $which
     */
    protected function _killTempDirectory( $which )
    {
        if ( '/data/tmp/fabric.' == substr( $which, 0, 12 ) )
        {
            exec( 'rm -r ' . $which );
        }
    }

    /**
     * @param string $tempFileToo
     * @param array  $subs Options subdirectories to create as well.
     *
     * @throws \RuntimeException
     * @return string
     */
    protected function _makeTempDirectory( &$tempFileToo = null, $subs = array() )
    {
        $_tempPath = '/data/tmp';
        shell_exec( '[ ! -d ' . $_tempPath . ' ] && mkdir -p ' . $_tempPath . '; chmod 777 ' . $_tempPath );
        $tempFileToo = md5( microtime( true ) ) . microtime( true );

        if ( !is_dir( $_tempPath ) )
        {
            throw new \RuntimeException( 'Unable to create temporary working directory.' );
        }

        if ( !empty( $subs ) )
        {
            if ( !is_array( $subs ) )
            {
                $subs = array($subs);
            }

            foreach ( $subs as $_sub )
            {
                exec( 'mkdir -p ' . $_tempPath . '/' . trim( $_sub, ' ' . DIRECTORY_SEPARATOR ) );
            }
        }

        return $_tempPath;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function _buildTree( $path )
    {
        $_iterator = new \DirectoryIterator( $path );

        $_data = array();

        /** @var $_node \DirectoryIterator */
        foreach ( $_iterator as $_node )
        {
            if ( $_node->isDir() && !$_node->isDot() )
            {
                $_data[$_node->getFilename()] = $this->_buildTree( $_node->getPathname() );
            }
            else if ( $_node->isFile() )
            {
                $_data[] = $_node->getFilename();
            }
        }

        return $_data;
    }

    /**
     * Constructs a full snapshot path
     *
     * @param string $snapshotId
     *
     * @return string
     */
    protected function _snapshotPathFromId( $snapshotId )
    {
        return $this->getSnapshotPath() . '/' . $snapshotId . static::SNAPSHOT_FILE_SUFFIX;
    }
}
