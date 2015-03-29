<?php
namespace Cerberus\Services\Provisioning\DreamFactory;

use Cerberus\Enums\DSP;
use Cerberus\Enums\InstanceStates;
use Cerberus\Exceptions\DatabaseConfigurationMissingException;
use Cerberus\Services\Hosting\Instance\Snapshot;
use Cerberus\Services\Provisioning\BaseProvisioner;
use Cerberus\Services\Provisioning\Route53;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Exceptions\ProvisionerUnavailableException;
use DreamFactory\Exceptions\ProvisioningException;
use DreamFactory\Yii\Exceptions\RestException;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\Option;
use Kisma\Core\Utility\Sql;

/**
 * DreamFactory
 * DF Fabric provisioning service
 *
 * @author        Jerry Ablan <jerryablan@dreamfactory.com>
 */
class HostedInstance extends BaseProvisioner
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const SaltyGoodness = 'MA62HQ,PTx8oec~TQ;)Td*wc4(-{8WO*Mf-d+&p7*-d+D0P[6r?@ dW39<+H$~X>';

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Creates a hosted instance
     *
     * @param array $request The provisioning request. Valid $request options are:
     *                       storage_key => REQUIRED: The user's storage key
     *                       name => REQUIRED: the name of the new instance
     *                       db_name => if different from name
     *                       db_user => will be generated if not provided
     *                       db_password => if different from $db_name
     *                       zone => if different from DEFAULT_DSP_ZONE
     *                       server => if different from DEFAULT_DSP_HOST
     *
     * @param bool  $addDnsRecord
     *
     * @throws DatabaseConfigurationMissingException
     * @throws ProvisionerUnavailableException
     * @throws ProvisioningException
     * @throws RestException
     * @throws \Kisma\Core\Exceptions\StorageException
     * @return bool|\CFResponse|mixed
     */
    public function provision( $request, $addDnsRecord = false )
    {
        if ( null === ( $_name = Option::get( $request, 'name' ) ) )
        {
            throw new ProvisioningException( 'You must specify the "name" of the new instance.' );
        }

        //	Clean up that nasty name...
        $_name = Instance::sanitizeInstanceName( $_name );
        $_dbName = str_replace( '-', '_', Option::get( $request, 'db_name', $_name ) );

        /** @var $_instance Instance */
        if ( null === ( $_instance = Instance::model()->byNameOrId( $_name )->find() ) )
        {
            throw new RestException( HttpResponse::NotFound, 'The instance "' . $_name . '" was not found.' );
        }

        $_storageKey = $_instance->storage_id_text;
        $_dbConfigFile = $_instance->getPrivatePath() . '/' . $_name . '.database.config.php';

        //	Make sure the user name is kosher
        list( $_dbUser, $_dbPassword ) = $this->_generateDbUser( $_name );

        try
        {
            list( $_dbHost, $_dbPort, $_dbServer, $_clusterId ) =
                $this->_initDatabaseForInstance( $_instance );

            //	1. Create database
            if ( !$this->_createDatabase( $_dbName, $_dbUser, $_dbPassword ) )
            {
                $this->logError( '  ! Unable to create database "' . $_dbName . '"' );

                return false;
            }

            //	2. Grant privileges
            if ( !$this->_grantPrivileges( $_dbName, $_dbUser, $_dbPassword ) )
            {
                try
                {
                    //	Try and get rid of the database we created
                    $this->_dropDatabase( $_dbName );
                }
                catch ( \Exception $_ex )
                {
                    $this->logError( 'Exception dropping database: ' . $_ex->getMessage() );
                }

                return false;
            }
        }
        catch ( \CDbException $_ex )
        {
            throw new ProvisionerUnavailableException( $_ex->getMessage(), $_ex->getCode() );
        }

        //	3. Create storage area
        $this->logDebug( 'Provisioning storage...' );
        $_storageService = new Storage( $this );
        $_storageInfo = null;

        try
        {
            $_storageInfo = $_storageService->provision( $_instance );

            //	Create database config file...
            if ( isset( $_storageInfo, $_storageInfo['private_path'] ) )
            {
                $_date = date( 'c' );

                $_php = <<<PHP
<?php
/**
 * **** DO NOT MODIFY THIS FILE ****
 * **** CHANGES WILL BREAK YOUR DSP AND COULD BE OVERWRITTEN AT ANY TIME ****
 * @(#)\$Id: database.config.php; v1.8.2-{$_dbName} {$_date} \$
 */
return array(
	'connectionString' => 'mysql:host={$_dbHost};port={$_dbPort};dbname={$_dbName}',
	'username'         => '{$_dbUser}',
	'password'         => '{$_dbPassword}',
	'emulatePrepare'   => true,
	'charset'          => 'utf8',
	'schemaCachingDuration' => 3600,
);
PHP;

                if ( file_exists( $_dbConfigFile ) && !is_writeable( $_dbConfigFile ) )
                {
                    $_command =
                        'sudo ' .
                        dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) .
                        '/config/scripts/fix-perms.sh ' .
                        escapeshellarg( $_dbConfigFile );
                    $this->logDebug( 'PERMS FIX REQUIRED: ' . $_command );
                    exec( $_command );
                }

                if ( false === file_put_contents( $_dbConfigFile, $_php ) )
                {
                    $this->logError( 'Error writing database configuration file: ' . $_dbConfigFile );

                    return false;
                }
            }
        }
        catch ( \Exception $_ex )
        {
            $this->logError( 'Exception creating storage: ' . $_ex->getMessage() );
            $this->logInfo( 'Dropping database: ' . $_dbName );
            $this->_dropDatabase( $_dbName );

            $_path = $_storageInfo['storage_path'];
            $this->logInfo( 'Removing storage path: ' . $_path );
            $_result = `rm -rf $_path`;

            return false;
        }

        //	4. Create DNS entry
        if ( true === $addDnsRecord )
        {
            $this->logDebug( '  * Provisioning DNS record: ' . $_name );
            $_originalHost = $_instance->public_host_text;
            $_host = $this->_addRemoveDnsEntry( $_name, false, $_originalHost );
        }
        else
        {
            $this->logInfo( '  NOT Provisioning DNS for "' . $_name . '"' );
            $_host = $_name . '.' . DSP::DEFAULT_DSP_ZONE . DSP::DEFAULT_DSP_DOMAIN;
        }

        //	Update instance with new provision info
        $this->logDebug( '  * Updating instance record' );

        try
        {
            $_instance->setAttributes(
                array(
                    'guest_location_nbr' => Instance::FABRIC_HOSTED,
                    'instance_id_text'   => $_name,
                    'instance_name_text' => $_name,
                    'db_host_text'       => $_dbHost,
                    'db_port_nbr'        => $_dbPort,
                    'db_name_text'       => $_dbName,
                    'db_user_text'       => $_dbUser,
                    'db_password_text'   => $_dbPassword,
                    'base_image_text'    => 'fabric.standard',
                    'public_host_text'   => $_host,
                    'ready_state_nbr'    => 0, //   Admin Required
                    'state_nbr'          => InstanceStates::PROVISIONED,
                    'platform_state_nbr' => 0, //   Not Activated
                    'vendor_state_nbr'   => InstanceStates::PROVISIONED,
                    'vendor_state_text'  => 'running',
                    'start_date'         => date( 'c' ),
                    'end_date'           => null,
                    'terminate_date'     => null,
                    'provision_ind'      => 1,
                    'deprovision_ind'    => 0,
                    'cluster_id'         => $_clusterId,
                ),
                false
            );

            $_instance->save();
            $this->logDebug(
                '    * Instance row updated with new values: ' . print_r( $_instance->getRestAttributes(), true )
            );
        }
        catch ( \Exception $_ex )
        {
            $this->logError( '  ! Exception while storing new instance data: ' . $_ex->getMessage() );

            throw new RestException(
                HttpResponse::InternalServerError, 'There was a problem fulfilling your request.'
            );
        }

        return array(
            'host'                => $_host,
            'storage_key'         => $_storageKey,
            'blob_path'           => $_instance->getBlobStoragePath(),
            'storage_path'        => $_instance->getStoragePath(),
            'snapshot_path'       => $_instance->getSnapshotPath(),
            'private_path'        => $_instance->getPrivatePath(),
            'db_host'             => $_dbHost,
            'db_port'             => $_dbPort,
            'db_name'             => $_dbName,
            'db_user'             => $_dbUser,
            'db_password'         => $_dbPassword,
            'db_config_file_name' => $_dbConfigFile,
        );
    }

    /**
     * @param mixed $request
     * @param bool  $removeDnsRecord
     *
     * @throws ProvisioningException
     * @throws \CDbException
     * @return \CFResponse
     */
    public function deprovision( $request, $removeDnsRecord = false )
    {
        if ( is_string( $request ) )
        {
            $request = array('name' => $request);
        }

        if ( null === ( $_name = Option::get( $request, 'name', null, true ) ) )
        {
            throw new ProvisioningException(
                'You must specify the "name" of the instance that you wish to deprovision.'
            );
        }

        //	Clean up that nasty name...
        $_name = Instance::sanitizeInstanceName( $_name );

        /** @var $_instance Instance */
        if ( null === ( $_instance = Instance::model()->byNameOrId( $_name )->find() ) )
        {
            throw new ProvisioningException( 'Cannot locate instance named "' . $_name . '".' );
        }

        list( $_dbHost, $_dbPort, $_dbServer, $_clusterId ) =
            $this->_initDatabaseForInstance( $_instance );

        $_response = array(
            'name'        => $_name,
            'storage_key' => $_instance->storage_id_text,
        );

        //	1. Make a snapshot
        $_service = new Snapshot( $this );
        $_response['snapshot'] = $_service->create( $_name );

        //	2. Delete the database...
        if ( !$this->_dropDatabase( $_name ) )
        {
            $this->logError( 'Error removing database "' . $_name . '" from "' . $_dbServer );

            return false;
        }

        //	3. Delete the storage area
        $_storageService = new Storage( $this, $_instance );
        $_storageService->deprovision( $_instance );

        //	4. Kill the R53 record
        if ( true === $removeDnsRecord )
        {
            $this->_addRemoveDnsEntry( $_name, true );
        }

        //	Update instance with new provision info
        try
        {
            $_instance->setAttributes(
                array(
                    'guest_location_nbr' => 1,
                    'instance_id_text'   => null,
                    'db_name_text'       => null,
                    'db_user_text'       => null,
                    'db_password_text'   => null,
                    'base_image_text'    => null,
                    'public_host_text'   => null,
                    'storage_id_text'    => null,
                    'terminate_date'     => date( 'c' ),
                    'end_date'           => date( 'c' ),
                    'state_nbr'          => InstanceStates::DEPROVISIONED,
                    'vendor_state_nbr'   => InstanceStates::DEPROVISIONED,
                    'vendor_state_text'  => 'terminated',
                    'provision_ind'      => 0,
                    'deprovision_ind'    => 1,
                ),
                false
            );

            $_instance->save();
        }
        catch ( \CDbException $_ex )
        {
            $this->logError( 'Exception while storing new instance data: ' . $_ex->getMessage() );
        }

        //	5.	Delete the instance record...
        if ( !$_instance->delete() )
        {
            $this->logError( 'Error deleting instance row.' );
        }

        return true;
    }

    /**
     * @param mixed $request
     *
     * @return bool|\CFResponse|mixed
     */
    public function start( $request )
    {
        //	Ain't no such thing
        return true;
    }

    /**
     * @param mixed $request
     *
     * @return bool|\CFResponse|mixed
     */
    public function stop( $request )
    {
        //	Ain't no such thing
        return true;
    }

    /**
     * @param mixed $request
     *
     * @return bool|\CFResponse|mixed
     */
    public function terminate( $request )
    {
        return $this->deprovision( $request );
    }

    /**
     * @param string $name
     * @param string $user
     * @param string $password
     *
     * @return bool
     */
    protected function _createDatabase( $name, $user, $password )
    {
        $_result = Sql::execute(
            <<<MYSQL
CREATE DATABASE IF NOT EXISTS `{$name}`
MYSQL
        );

        if ( !$_result )
        {
            $this->logError(
                '  * Error creating new database "' . $name . '": ' . print_r( Sql::getConnection()->errorInfo(), true )
            );

            return false;
        }

        return true;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function _dropDatabase( $name )
    {
        $name = str_replace( '-', '_', $name );

        $_result = Sql::execute(
            <<<MYSQL
SET FOREIGN_KEY_CHECKS = 0; DROP DATABASE `{$name}`;
MYSQL
        );

        if ( !$_result )
        {
            $this->logError( '  ! Error dropping database: ' . $name );

            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param string $user
     * @param string $password
     *
     * @return bool
     */
    protected function _grantPrivileges( $name, $user, $password )
    {
        Sql::execute( 'CREATE USER \'' . $user . '\'@\'%\' IDENTIFIED BY \'' . $password . '\'' );

        $_sql = <<<MYSQL
GRANT ALL PRIVILEGES ON {$name}.* TO '{$user}'@'%'
MYSQL;

        $this->logDebug( 'Issuing GRANTS: [' . $_sql . ']' );

        //	Grants for DSP database
        $_result = Sql::execute( $_sql );

        $this->logDebug( '  * Grant result: ' . $_result . ' [' . $_sql . ']' );

        if ( false === $_result )
        {
            $this->logError( '  ! Error granting privileges on database: ' . $name );

            return false;
        }

        $this->logInfo( '  * Privileges granted to database "' . $name . '".' );

        return true;
    }

    /**
     * @param string $name
     * @param bool   $deleteRecord
     * @param string $originalHost
     * @param string $zone
     * @param string $server
     * @param string $recordType
     * @param int    $ttl
     * @param string $comment
     *
     * @throws \Kisma\Core\Exceptions\ServiceException
     * @return bool
     */
    protected function _addRemoveDnsEntry( $name, $deleteRecord = false, $originalHost = null, $zone = DSP::DEFAULT_DSP_ZONE, $server = DSP::DEFAULT_DSP_SERVER, $recordType = 'CNAME', $ttl = 3600, $comment = null )
    {
        try
        {
            $_newHost = rtrim( $name, '. ' ) . '.' . $zone . DSP::DEFAULT_DSP_DOMAIN;

            $_service = new Route53( $this );

            //	Delete the record if it exists
            $_service->changeResourceRecordsets(
                $zone,
                $_newHost,
                $originalHost,
                $recordType,
                $ttl,
                'Removed by ' . get_class( $this ) . ' by request',
                true
            );

            //	Add the new one
            $_result = $_service->changeResourceRecordsets(
                $zone,
                $_newHost,
                $server,
                $recordType,
                $ttl,
                $comment ?: 'Created by ' . get_class( $this ),
                $deleteRecord
            );

            if ( false === $_result )
            {
                $this->logError( 'Error creating host name entry. Check logs for specific error.' );
                //	@todo Revisit fatality of this error
            }

            return $_newHost;
        }
        catch ( \Exception $_ex )
        {
            $this->logError(
                'Exception ' . ( $deleteRecord ? 'deleting' : 'adding' ) . ' DNS entry: ' . $_ex->getMessage()
            );

            return false;
        }
    }

    /**
     * @param string $storageKey
     *
     * @return string
     */
    protected function _getStoragePath( $storageKey )
    {
        return DSP::FABRIC_BASE_STORAGE_PATH . '/' . $storageKey;
    }

    /**
     * Generates a unique user/pass for MySQL
     *
     * @param string $name
     *
     * @return array|string
     */
    protected function _generateDbUser( $name )
    {
        $_dbUser = $_dbPassword = null;

        Sql::setConnection( Pii::db( 'db.fabric_deploy' )->getPdoInstance() );

        while ( true )
        {
            $_baseHash = sha1( microtime( true ) . $name . static::SaltyGoodness );
            $_dbUser = substr( 'u' . $_baseHash, 0, 16 );

            if ( 0 == Sql::scalar(
                    'SELECT count(i.id) FROM fabric_deploy.instance_t i WHERE i.db_user_text = :user',
                    0,
                    array(':user' => $_dbUser)
                )
            )
            {
                break;
            }

            usleep( 500000 );
        }

        $_dbPassword = sha1( microtime( true ) . $name . $_dbUser . microtime( true ) );

        return array($_dbUser, $_dbPassword);
    }

    /**
     * @param Instance $instance
     *
     * @return array
     * @throws DatabaseConfigurationMissingException
     * @throws \Exception
     */
    protected function _initDatabaseForInstance( $instance )
    {
        $_dbServer = $_dbHost = $_dbPort = null;
        $_clusterId = 1;

        if ( $instance->dbServer )
        {
            $_dbServer = $instance->dbServer->server_id_text;

            if ( !empty( $_dbServer ) )
            {
                if ( 'cumulus' != $_dbServer )
                {
                    $_clusterId = 2;
                }

                $_dbServer = 'db.' . $_dbServer;
                $_dbConfig = $instance->dbServer->config_text;

                if ( empty( $_dbConfig ) )
                {
                    throw new DatabaseConfigurationMissingException( $_dbServer );
                }

                $_dbHost = Option::get( $_dbConfig, 'host' );
                $_dbPort = Option::get( $_dbConfig, 'port' );
            }
        }

        if ( empty( $_dbServer ) )
        {
            $_dbServer = 'db.cumulus';
            $_dbHost = 'localhost';
            $_dbPort = 3306;
        }

        $this->logDebug( '  * Using db-server-id "' . $_dbServer . '"' );

        try
        {
            Sql::setConnection( Pii::db( $_dbServer )->getPdoInstance() );
        }
        catch ( \Exception $_ex )
        {
            $this->logError( '  ! Unable to create pdo connection to ' . $_dbServer );
            throw $_ex;
        }

        //  Return an array of junk
        return array(
            $_dbHost,
            $_dbPort,
            $_dbServer,
            $_clusterId,
        );
    }
}
