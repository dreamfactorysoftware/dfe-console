<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\PortableData;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Services\Exceptions\ProvisioningException;
use DreamFactory\Enterprise\Services\Exceptions\SchemaExistsException;
use DreamFactory\Enterprise\Services\Provisioners\BaseDatabaseProvisioner;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DatabaseProvisioner extends BaseDatabaseProvisioner implements PortableData
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use Archivist, EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    protected function doProvision($request)
    {
        $_instance = $request->getInstance();
        $_serverId = $_instance->db_server_id;

        if (empty($_serverId)) {
            throw new \InvalidArgumentException('Please assign the instance to a database server before provisioning database resources.');
        }

        //  Get a connection to the instance's database server
        list($_db, $_rootConfig, $_rootServer) = $this->getRootDatabaseConnection($_instance);

        //  1. Create a random user and password for the instance
        $_creds = $this->generateSchemaCredentials($_instance);

        $this->debug('>>> provisioning database "' . $_creds['database'] . '"');

        try {
            //	1. Create database
            if (false === $this->createDatabase($_db, $_creds)) {
                try {
                    $this->deprovision($request);
                } catch (\Exception $_ex) {
                    $this->notice('Unable to eradicate Klingons from planet "' .
                        $_creds['database'] .
                        '" after deprovisioning.');
                }

                return false;
            }

            //	2. Grant privileges
            $_result = $this->grantPrivileges($_db, $_creds, $_instance->webServer->host_text);

            if (false === $_result) {
                try {
                    //	Try and get rid of the database we created
                    $this->dropDatabase($_db, $_creds['database']);
                } catch (\Exception $_ex) {
                    //  Ignored, what can we do?
                }

                $this->debug('<<< provisioning database "' . $_creds['database'] . '" FAILURE');

                return false;
            }
        } catch (ProvisioningException $_ex) {
            throw $_ex;
        } catch (\Exception $_ex) {
            throw new ProvisioningException($_ex->getMessage(), $_ex->getCode());
        }

        $this->info('<<< provisioning database "' . $_creds['database'] . '" SUCCESS');

        return array_merge($_rootConfig, $_creds);
    }

    /** @inheritdoc */
    protected function doDeprovision($request)
    {
        $_instance = $request->getInstance();

        $this->debug('>>> deprovisioning database "' . $_instance->db_name_text . '"');

        //  Get a connection to the instance's database server
        list($_db, $_rootConfig, $_rootServer) = $this->getRootDatabaseConnection($_instance);

        try {
            //	Try and get rid of the database we created
            if (!$this->dropDatabase($_db, $_instance->db_name_text)) {
                throw new ProvisioningException('Unable to delete database "' . $_instance->db_name_text . '".');
            }
        } catch (\Exception $_ex) {
            $this->error('<<< deprovisioning database "' .
                $_instance->db_name_text .
                '" FAILURE: ' .
                $_ex->getMessage());

            return false;
        }

        $this->info('<<< deprovisioning database "' . $_instance->db_name_text . '" SUCCESS');

        return true;
    }

    /** @inheritdoc */
    public function import($request)
    {
        $_instance = $request->getInstance();

        if (!file_exists($_from = $request->getTarget())) {
            throw new \InvalidArgumentException('$from file "' . $_from . '" missing or unreadable.');
        }

        /** @type Connection $_db */
        list($_db, ,) = $this->getRootDatabaseConnection($_instance);

        $this->dropDatabase($_db, $_instance->db_name_text);
        $this->createDatabase($_db, $_instance->db_name_text);

        return $_db->statement('source ' . $_from);
    }

    /** @inheritdoc */
    public function export($request)
    {
        $_instance = $request->getInstance();
        $_tag = date('YmdHis') . '.' . $_instance->instance_id_text;
        $_workPath = $this->getWorkPath($_tag, true);

        //  Add file extension if missing
        if (null === ($_target = $request->getTarget()) || !is_string($_target)) {
            $_target = $_tag . '.database.sql';
        }

        $_target = static::ensureFileSuffix('.sql', $_target);

        $_command = str_replace(PHP_EOL, null, `which mysqldump`);
        $_template =
            $_command . ' --compress --delayed-insert {options} >' . ($_workPath . DIRECTORY_SEPARATOR . $_target);
        $_port = $_instance->db_port_nbr;
        $_name = $_instance->db_name_text;

        $_options = [
            '--host=' . escapeshellarg($_instance->db_host_text),
            '--user=' . escapeshellarg($_instance->db_user_text),
            '--password=' . escapeshellarg($_instance->db_password_text),
            '--databases ' . escapeshellarg($_name),
        ];

        if (!empty($_port)) {
            $_options[] = '--port=' . $_port;
        }

        $_command = str_replace('{options}', implode(' ', $_options), $_template);
        exec($_command, $_output, $_return);

        if (0 != $_return) {
            $this->error('Error while dumping database of instance id "' . $_instance->instance_id_text . '".');

            return false;
        }

        //  Copy it over to the snapshot area
        $this->writeStream($_instance->getSnapshotMount(), $_workPath . DIRECTORY_SEPARATOR . $_target, $_target);
        $this->deleteWorkPath($_tag);

        //  The name of the file in the snapshot mount
        return $_target;
    }

    /**
     * @param Instance $instance
     *
     * @return Connection
     */
    protected function getRootDatabaseConnection(Instance $instance)
    {
        static $_skeleton = [
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'dfe_local',
            'username'  => 'dfe_user',
            'password'  => 'dfe_user',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ];

        //  Let's go!
        $_dbServerId = $instance->db_server_id;

        //  And stoopids (sic)
        if (empty($_dbServerId)) {
            throw new \RuntimeException('Empty server id given during database resource provisioning for instance');
        }

        try {
            $_server = $this->_findServer($_dbServerId);
        } catch (ModelNotFoundException $_ex) {
            throw new \RuntimeException('Database resource "' . $_dbServerId . '" not found.');
        }

        //  Get the REAL server name
        $_dbServer = $_server->server_id_text;

        //  Build the config
        $_config =
            array_merge(
                is_scalar($_server->config_text)
                    ? Json::decode($_server->config_text, true)
                    : (array)$_server->config_text,
                $_skeleton,
                ['db-server-id' => $_dbServer,]
            );

        //  Sanity Checks
        if (empty($_config)) {
            throw new \LogicException('Configuration invalid for database resource during provisioning.');
        }

        //  Add it to the connection list
        config(['database.connections.' . $_server->server_id_text => $_config]);

        //  Create a connection and return. It's in Joe Pesce's hands now...
        return [\DB::connection($_dbServer), $_config, $_server];
    }

    /**
     * Generates a unique dbname/user/pass for MySQL for an instance
     *
     * @param Instance $instance
     *
     * @return array
     * @throws SchemaExistsException
     */
    protected function generateSchemaCredentials(Instance $instance)
    {
        $_tries = 0;

        $_dbUser = null;
        $_dbName = $this->generateDatabaseName($instance);
        $_seed = $_dbName . env('APP_KEY') . $instance->instance_name_text;

        //  Make sure our user name is unique...
        while (true) {
            $_baseHash = sha1(microtime(true) . $_seed);
            $_dbUser = substr('u' . $_baseHash, 0, 16);

            if (0 == Instance::where('db_user_text', '=', $_dbUser)->count()) {
                $_sql = 'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :schema_name';

                //  Make sure the database name is unique as well.
                $_names = \DB::select($_sql, [':schema_name' => $_dbName]);

                if (!empty($_names)) {
                    throw new SchemaExistsException('The schema "' . $_dbName . '" already exists.');
                }

                break;
            }

            if (++$_tries > 10) {
                throw new \LogicException('Unable to locate a non-unique database user name after ' .
                    $_tries .
                    ' attempts.');
            }

            //  Quick snoozy and we try again
            usleep(500000);
        }

        $_creds = [
            'database' => $_dbName,
            'username' => $_dbUser,
            'password' => sha1(microtime(true) . $_seed . $_dbUser . microtime(true)),
        ];

        return $_creds;
    }

    /**
     * @param Connection $db
     * @param array      $creds
     *
     * @return bool
     */
    protected function createDatabase($db, array $creds)
    {
        try {
            $_dbName = $creds['database'];

            return $db->statement(
                <<<MYSQL
CREATE DATABASE IF NOT EXISTS `{$_dbName}`
MYSQL
            );
        } catch (\Exception $_ex) {
            $this->error('* create database - failure: ' . $_ex->getMessage());

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
    protected function dropDatabase($db, $databaseToDrop)
    {
        try {
            if (empty($databaseToDrop)) {
                return true;
            }

            $this->debug('dropping database "' . $databaseToDrop . '"');

            return $db->transaction(
                function () use ($db, $databaseToDrop) {
                    $_result = $db->statement('SET FOREIGN_KEY_CHECKS = 0');
                    $_result && $_result = $db->statement('DROP DATABASE `' . $databaseToDrop . '`');
                    $_result && $db->statement('SET FOREIGN_KEY_CHECKS = 1');
                    $this->debug('database "' . $databaseToDrop . '" dropped.');

                    return $_result;
                }
            );
        } catch (\Exception $_ex) {
            $_message = $_ex->getMessage();

            //  If the database is already gone, don't cause an error, but note it.
            if (false !== stripos($_message, 'general error: 1008')) {
                $this->info('* drop database - not performed. database does not exist.');

                return true;
            }

            $this->error('* drop database - failure: ' . $_message);

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
    protected function grantPrivileges($db, $creds, $fromServer)
    {
        return $db->transaction(
            function () use ($db, $creds, $fromServer) {
                //  Create users
                $_users = $this->getDatabaseUsers($creds, $fromServer);

                try {
                    foreach ($_users as $_user) {
                        $db->statement(
                            'GRANT ALL PRIVILEGES ON ' .
                            $creds['database'] .
                            '.* TO ' .
                            $_user .
                            ' IDENTIFIED BY \'' .
                            $creds['password'] .
                            '\''
                        );
                    }

                    //	Grants for instance database
                    return true;
                } catch (\Exception $_ex) {
                    $this->error('* issue grants - failure: ' . $_ex->getMessage());

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
    protected function revokePrivileges($db, $creds, $fromServer)
    {
        return $db->transaction(
            function () use ($db, $creds, $fromServer) {
                //  Create users
                $_users = $this->getDatabaseUsers($creds, $fromServer);

                try {
                    foreach ($_users as $_user) {
                        //	Grants for instance database
                        if (!($_result =
                            $db->statement('REVOKE ALL PRIVILEGES ON ' . $creds['database'] . '.* FROM ' . $_user))
                        ) {
                            $this->error('* error revoking privileges from "' . $_user . '"');
                            continue;
                        }

                        $this->debug('grants revoked - complete');

                        if (!($_result = $db->statement('DROP USER ' . $_user))) {
                            $this->error('* error dropping user "' . $_user . '"');
                        }

                        $_result && $this->debug('users dropped > ', $_users);
                    }

                    return true;
                } catch (\Exception $_ex) {
                    $this->error('revoke grants - failure: ' . $_ex->getMessage());

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
    protected function generateDatabaseName(Instance $instance)
    {
        return str_replace('-', '_', $instance->instance_name_text);
    }

    /**
     * Generates a list of users to de/provision
     *
     * @param array  $creds
     * @param string $fromServer
     *
     * @return array
     */
    protected function getDatabaseUsers($creds, $fromServer)
    {
        return [
            '\'' . $creds['username'] . '\'@\'' . $fromServer . '\'',
            '\'' . $creds['username'] . '\'@\'localhost\'',
        ];
    }
}
