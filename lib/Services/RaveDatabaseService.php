<?php
namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\ComponentLookup;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RaveDatabaseService extends BaseService implements ResourceProvisioner
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use ComponentLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     * @throws ProvisioningException
     */
    public function provision( $request )
    {
        $_instance = $request->getInstance();
        $_serverId = $_instance->db_server_id;

        if ( empty( $_serverId ) )
        {
            throw new \InvalidArgumentException( 'Please assign the instance to a database server before provisioning database resources.' );
        }

        //  Get a connection to the instance's database server 
        $_db = $this->_getRootDatabaseConnection( $_instance );

        //  1. Create a random user and password for the instance
        $_creds = $this->_generateSchemaCredentials( $_instance );

        try
        {
            //	1. Create database
            if ( !$this->_createDatabase( $_db, $_creds ) )
            {
                try
                {
                    $request->setForced( true );
                    $this->deprovision( $request );

                }
                catch ( \Exception $_ex )
                {
                    $this->alert( 'Unable to remove remnants of failed provisioning for instance "' . $_instance->instance_id_text );
                }

                return false;
            }

            //	2. Grant privileges
            if ( !$this->_grantPrivileges( $_db, $_creds ) )
            {
                try
                {
                    //	Try and get rid of the database we created
                    $this->_dropDatabase( $_db, $_creds );
                }
                catch ( \Exception $_ex )
                {
                    $this->error( 'Exception dropping database: ' . $_ex->getMessage() );
                }

                return false;
            }
        }
        catch ( \Exception $_ex )
        {
            throw new ProvisioningException( $_ex->getMessage(), $_ex->getCode() );
        }

    }

    /**
     * @param ProvisioningRequest $request
     *
     * @return bool
     */
    public function deprovision( $request )
    {
        $_forced = $request->isForced();
    }

    /**
     * @param Instance $instance
     *
     * @return Connection
     */
    protected function _getRootDatabaseConnection( Instance $instance )
    {
        static $_skeleton = [
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'dfe_local',
            'username'  => 'dfe_user',
            'password'  => 'dfe_user',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ];

        //  Let's go!
        $_dbServerId = $instance->db_server_id;

        //  And stoopids (sic)
        if ( empty( $_dbServerId ) )
        {
            throw new \RuntimeException( 'Empty server id given during database resource provisioning for instance' );
        }

        try
        {
            $_server = $this->_lookupServer( $_dbServerId );
        }
        catch ( ModelNotFoundException $_ex )
        {
            throw new \RuntimeException( 'Database resource "' . $_dbServerId . '" not found.' );
        }

        //  Get the REAL server name
        $_dbServer = $_server->server_id_text;

        //  Build the config
        $_config =
            array_merge(
                is_scalar( $_server->config_text )
                    ? $this->_jsonDecode( $_server->config_text, true )
                    : (array)$_server->config_text,
                $_skeleton,
                ['db-server-id' => $_dbServer,]
            );

        //  Sanity Checks
        if ( empty( $_config ) )
        {
            throw new \LogicException( 'Configuration invalid for database resource during provisioning.' );
        }

        //  Add it to the connection list
        \Config::set( 'database.connections.' . $_server->server_id_text, $_config );

        $this->debug(
            'Creating root database connection on server-id "' . $_dbServer . '" for instance "' . $instance->instance_name_text . '".',
            ['service' => 'RAVE Database']
        );

        //  Create a connection and return. It's in Joe Pesce's hands now...
        return \DB::connection( $_dbServer );
    }

    /**
     * Generates a unique dbname/user/pass for MySQL for an instance
     *
     * @param Instance $instance
     *
     * @return array
     */
    protected function _generateSchemaCredentials( Instance $instance )
    {
        $_dbUser = $_dbPassword = null;
        $_dbName = $this->_generateDatabaseName( $instance );
        $_seed = $_dbName . env( 'APP_KEY' ) . $instance->instance_name_text;

        //  Make sure our user name is unique...
        while ( true )
        {
            $_baseHash = sha1( microtime( true ) . $_seed );
            $_dbUser = substr( 'u' . $_baseHash, 0, 16 );

            if ( Instance::where( 'db_user_text', '=', $_dbUser )->count() )
            {
                //  Make sure the database name is unique as well.
                $_names = \DB::select( 'SHOW DATABASES LIKE :schema', [':schema' => $_dbName] );

                if ( empty( $_names ) )
                {
                    break;
                }
            }

            //  Quick snoozy and we try again
            usleep( 500000 );
        }

        return [
            'database' => $_dbName,
            'username' => $_dbUser,
            'password' => sha1( microtime( true ) . $_seed . $_dbUser . microtime( true ) )
        ];
    }

    /**
     * @param Connection $db
     * @param array      $creds
     *
     * @return bool
     */
    protected function _createDatabase( $db, array $creds )
    {
        try
        {
            return $db->statement(
                <<<MYSQL
        CREATE DATABASE IF NOT EXISTS `{$creds['database']}`
MYSQL
            );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param Connection $db
     * @param array      $creds
     *
     * @return bool
     */
    protected function _dropDatabase( $db, $creds )
    {
        try
        {
            return $db->statement(
                <<<MYSQL
        SET FOREIGN_KEY_CHECKS = 0; DROP DATABASE {$creds['database']};
MYSQL
            );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * @param Connection $db
     * @param array      $creds
     *
     * @return bool
     */
    protected function _grantPrivileges( $db, $creds )
    {
        //  Create user
        $db->statement( 'CREATE USER \'' . $creds['username'] . '\'@\'%\' IDENTIFIED BY \'' . $creds['password'] . '\'' );

        $_sql = <<<MYSQL
GRANT ALL PRIVILEGES ON {$creds['database']}.* TO '{$creds['username']}'@'%'
MYSQL;

        try
        {
            //	Grants for instance database
            return $db->statement( $_sql );
        }
        catch ( \Exception $_ex )
        {
            return false;
        }
    }

    /**
     * Generates a database name for an instance.
     *
     * @param Instance $instance
     *
     * @return string
     */
    protected function _generateDatabaseName( Instance $instance )
    {
        return str_replace( '-', '_', $instance->instance_name_text );
    }
}
