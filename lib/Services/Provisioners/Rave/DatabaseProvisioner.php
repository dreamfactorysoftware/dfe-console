<?php
namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Exceptions\SchemaExistsException;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DatabaseProvisioner extends BaseService implements ResourceProvisioner
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return bool
     * @throws ProvisioningException
     */
    public function provision( $request, $options = [] )
    {
        $this->debug( '    * rave: provision database - begin' );

        $_instance = $request->getInstance();
        $_serverId = $_instance->db_server_id;

        if ( empty( $_serverId ) )
        {
            throw new \InvalidArgumentException( 'Please assign the instance to a database server before provisioning database resources.' );
        }

        //  Get a connection to the instance's database server 
        list( $_db, $_rootConfig, $_rootServer ) = $this->_getRootDatabaseConnection( $_instance );

        //  1. Create a random user and password for the instance
        $_creds = $this->_generateSchemaCredentials( $_instance );

        try
        {
            //	1. Create database
            if ( false === $this->_createDatabase( $_db, $_creds ) )
            {
                try
                {
                    $this->deprovision( $request );
                }
                catch ( \Exception $_ex )
                {
                    $this->notice( 'Unable to remove remnants of failed provisioning for instance "' . $_instance->instance_id_text );
                }

                return false;
            }

            //	2. Grant privileges
            $_result = $this->_grantPrivileges( $_db, $_creds, $_instance->webServer->host_text );

            if ( false === $_result )
            {
                try
                {
                    //	Try and get rid of the database we created
                    $this->_dropDatabase( $_db, $_creds['database'] );
                }
                catch ( \Exception $_ex )
                {
                }

                $this->error( '    * rave: provision database - incomplete/fail' );

                return false;
            }
        }
        catch ( ProvisioningException $_ex )
        {
            throw $_ex;
        }
        catch ( \Exception $_ex )
        {
            throw new ProvisioningException( $_ex->getMessage(), $_ex->getCode() );
        }

        $this->debug( '    * rave: provision database - complete' );

        return array_merge( $_rootConfig, $_creds );
    }

    /**
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return bool
     * @throws ProvisioningException
     * @throws SchemaExistsException
     */
    public function deprovision( $request, $options = [] )
    {
        $_instance = $request->getInstance();

        //  Get a connection to the instance's database server
        list( $_db, $_rootConfig, $_rootServer ) = $this->_getRootDatabaseConnection( $_instance );

        try
        {
            //	Try and get rid of the database we created
            if ( !$this->_dropDatabase( $_db, $_instance->db_name_text ) )
            {
                throw new ProvisioningException( 'Unable to delete database "' . $_instance->db_name_text . '".' );
            }
        }
        catch ( \Exception $_ex )
        {
            $this->error( '    * rave: deprovision database > incomplete/fail: ' . $_ex->getMessage() );

            return false;
        }

        $this->debug( '    * rave: deprovision database > dropped "' . $_instance->db_name_text . '".' );

        return true;
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
            $_server = $this->_findServer( $_dbServerId );
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

        //  Create a connection and return. It's in Joe Pesce's hands now...
        return [\DB::connection( $_dbServer ), $_config, $_server];
    }

    /**
     * Generates a unique dbname/user/pass for MySQL for an instance
     *
     * @param Instance $instance
     *
     * @return array
     * @throws SchemaExistsException
     */
    protected function _generateSchemaCredentials( Instance $instance )
    {
        $_tries = 0;

        $_dbUser = null;
        $_dbName = $this->_generateDatabaseName( $instance );
        $_seed = $_dbName . env( 'APP_KEY' ) . $instance->instance_name_text;

        //  Make sure our user name is unique...
        while ( true )
        {
            $_baseHash = sha1( microtime( true ) . $_seed );
            $_dbUser = substr( 'u' . $_baseHash, 0, 16 );

            if ( 0 == Instance::where( 'db_user_text', '=', $_dbUser )->count() )
            {
                $_sql = 'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :schema_name';

                //  Make sure the database name is unique as well.
                $_names = \DB::select( $_sql, [':schema_name' => $_dbName] );

                if ( !empty( $_names ) )
                {
                    throw new SchemaExistsException( 'The schema "' . $_dbName . '" already exists.' );
                }

                break;
            }

            if ( ++$_tries > 10 )
            {
                throw new \LogicException( 'Unable to locate a non-unique database user name after ' . $_tries . ' attempts.' );
            }

            //  Quick snoozy and we try again
            usleep( 500000 );
        }

        $_creds = [
            'database' => $_dbName,
            'username' => $_dbUser,
            'password' => sha1( microtime( true ) . $_seed . $_dbUser . microtime( true ) )
        ];

        return $_creds;
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
            $_dbName = $creds['database'];

            return $db->statement(
                <<<MYSQL
CREATE DATABASE IF NOT EXISTS `{$_dbName}`
MYSQL
            );
        }
        catch ( \Exception $_ex )
        {
            $this->error( '    * provisioner: create database - failure: ' . $_ex->getMessage() );

            return false;
        }
    }

    /**
     * @param Connection $db
     * @param string     $databaseToDrop
     *
     * @return bool
     *
     */
    protected function _dropDatabase( $db, $databaseToDrop )
    {
        try
        {
            if ( empty( $databaseToDrop ) )
            {
                return true;
            }

            return $db->transaction(
                function () use ( $db, $databaseToDrop )
                {
                    $_result = $db->statement( 'SET FOREIGN_KEY_CHECKS = 0' );
                    $_result && $_result = $db->statement( 'DROP DATABASE `' . $databaseToDrop . '`' );
                    $_result && $db->statement( 'SET FOREIGN_KEY_CHECKS = 1' );
                    $this->debug( '    * provisioner: database dropped > ' . $databaseToDrop );

                    return $_result;
                }
            );
        }
        catch ( \Exception $_ex )
        {
            $_message = $_ex->getMessage();

            //  If the database is already gone, don't cause an error, but note it.
            if ( false !== stripos( $_message, 'general error: 1008' ) )
            {
                $this->info( '    * provisioner: drop database - semi-successful: ' . $_message );

                return true;
            }

            $this->error( '    * provisioner: drop database - failure: ' . $_message );

            return false;
        }
    }

    /**
     * @param Connection $db
     * @param array      $creds
     * @param string     $fromServer
     *
     * @return bool
     */
    protected function _grantPrivileges( $db, $creds, $fromServer )
    {
        return $db->transaction(
            function () use ( $db, $creds, $fromServer )
            {
                //  Create users
                $_users = $this->_getDatabaseUsers( $creds, $fromServer );

                try
                {
                    foreach ( $_users as $_user )
                    {
                        $db->statement(
                            'GRANT ALL PRIVILEGES ON ' . $creds['database'] . '.* TO ' . $_user . ' IDENTIFIED BY \'' . $creds['password'] . '\''
                        );
                    }

                    //	Grants for instance database
                    return true;
                }
                catch ( \Exception $_ex )
                {
                    $this->error( '    * provisioner: issue grants - failure: ' . $_ex->getMessage() );

                    return false;
                }
            }
        );
    }

    /**
     * @param Connection $db
     * @param array      $creds
     * @param string     $fromServer
     *
     * @return bool
     */
    protected function _revokePrivileges( $db, $creds, $fromServer )
    {
        return $db->transaction(
            function () use ( $db, $creds, $fromServer )
            {
                //  Create users
                $_users = $this->_getDatabaseUsers( $creds, $fromServer );

                try
                {
                    foreach ( $_users as $_user )
                    {
                        //	Grants for instance database
                        if ( !( $_result = $db->statement( 'REVOKE ALL PRIVILEGES ON ' . $creds['database'] . '.* FROM ' . $_user ) ) )
                        {
                            \Log::error( '    * provisioner: error revoking privileges from "' . $_user . '"' );
                            continue;
                        }

                        $this->debug( '    * provisioner: grants revoked - complete' );

                        if ( !( $_result = $db->statement( 'DROP USER ' . $_user ) ) )
                        {
                            \Log::error( '    * provisioner: error dropping user "' . $_user . '"' );
                        }

                        $_result && $this->debug( '    * provisioner: users dropped > ', $_users );
                    }

                    return true;
                }
                catch ( \Exception $_ex )
                {
                    $this->error( '    * provisioner: revoke grants - failure: ' . $_ex->getMessage() );

                    return false;
                }
            }
        );
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

    /**
     * Generates a list of users to de/provision
     *
     * @param array  $creds
     * @param string $fromServer
     *
     * @return array
     */
    protected function _getDatabaseUsers( $creds, $fromServer )
    {
        return [
            '\'' . $creds['username'] . '\'@\'' . $fromServer . '\'',
            '\'' . $creds['username'] . '\'@\'localhost\'',
        ];
    }
}
