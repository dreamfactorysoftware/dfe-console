<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\MySqlAdmin;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MoveInstance extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, MySqlAdmin;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:move-instance';
    /** @inheritdoc */
    protected $description = 'Moves an instance from one server to another.';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        parent::handle();

        $_instanceId = $this->argument('instance-id');
        $_serverId = $this->argument('server-id');
        $_all = $this->option('all');

        if (empty($_serverId)) {
            throw new \InvalidArgumentException('You must specify a destination "server-id".');
        }

        if (empty($_instanceId) && !$_all) {
            throw new \InvalidArgumentException('You must specify an "instance-id" or use the "--all" option.');
        }

        try {
            $_server = $this->findServer($_serverId);
        } catch (ModelNotFoundException $_ex) {
            throw new \InvalidArgumentException('The server-id "' . $_serverId . '" is not a valid destination server.');
        }

        $this->setOutputPrefix('[' . $this->name . ']');

        if ($this->option('all')) {
            $_results = $this->moveAllInstances($_server);
        } else {
            $_results = [$_instanceId => $this->moveSingleInstance($_instanceId, $_server)];
        }

        $_file = storage_path(date('YmdHis') . '-move-instance.json');

        if (false === JsonFile::encodeFile($_file, $_results)) {
            $this->error('Error storing results to file.');
        }

        return 0;
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['server-id', InputArgument::REQUIRED, 'The destination server of the move',],
                ['instance-id', InputArgument::OPTIONAL, 'The instance to move',],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                ['all', 'a', InputOption::VALUE_NONE, 'Move *all* instances',],
                ['purge', 'p', InputOption::VALUE_NONE, 'Purge existing credentials from database. Only used with database server moves.'],
                [
                    'cluster-id',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'If specified with "--all", only the instances managed by "cluster-id" will be moved.',
                ],
            ]);
    }

    /**
     * Moves all instances known, or in a cluster
     *
     * @param \DreamFactory\Enterprise\Database\Models\Server $server
     *
     * @return array
     */
    protected function moveAllInstances($server)
    {
        $this->info('Moving <comment>all</comment> instances to <comment>' . $server->server_id_text . '</comment>');

        /** @type Instance[] $_instances */
        if (null !== ($_clusterId = $this->option('cluster-id'))) {
            $_instances = $this->findClusterInstances($_clusterId, ['instance_id_text']);
        } else {
            $_instances = Instance::orderBy('instance_id_text')->get(['instance_id_text']);
        }

        $_results = [];
        $_count = $_errors = 0;

        if (!empty($_instances)) {
            foreach ($_instances as $_instance) {
                $_id = $_instance->instance_id_text;

                if ($_instance->db_host_text == $server->host_text) {
                    continue;
                }

                try {
                    if (false !== ($_results[$_id] = $this->moveSingleInstance($_id, $server, true))) {
                        $_count++;
                    }
                } catch (\Exception $_ex) {
                    $_errors++;
                    $_results[$_id] =
                        ['success' => false, 'output' => $_ex->getMessage(), 'exit_code' => $_ex->getCode()];
                }
            }
        }

        $this->line(PHP_EOL . 'Moved ' . number_format($_count, 0) . ' instance(s) with ' . number_format($_errors, 0) . ' error(s).');

        return $_results;
    }

    /**
     * @param string                                          $instanceId
     * @param \DreamFactory\Enterprise\Database\Models\Server $server
     * @param bool                                            $mute If true, no message will be displayed
     *
     * @return bool
     */
    protected function moveSingleInstance($instanceId, $server, $mute = false)
    {
        !$mute && $this->info('Moving instance "<comment>' .
            $instanceId .
            '</comment>" to "<comment>' .
            $server->server_id_text .
            '</comment>"');

        try {
            //  1.  Get the full instance row
            $_instance = $this->findInstance($instanceId);

            //  2. Change instance database pointers
            switch ($server->server_type_id) {
                case ServerTypes::DATABASE:
                    $this->updateInstanceConfig($_instance, $server);
                    break;

                case ServerTypes::WEB_APPS:
                    $_instance->web_server_id = $server->id;
                    $_config = $_instance->instance_data_text;
                    array_set($_config, 'audit.web-server-id', $server->server_id_text);
                    $_instance->instance_data_text = $_config;
                    break;

                case ServerTypes::APPS:
                    $_instance->app_server_id = $server->id;
                    $_config = $_instance->instance_data_text;
                    array_set($_config, 'audit.app-server-id', $server->server_id_text);
                    $_instance->instance_data_text = $_config;
                    break;
            }

            if (!$_instance->save()) {
                \Log::error('Error saving modified instance row. Quandary!');
            }

            $this->info('* <comment>' . $instanceId . ':</comment> <info>success</info>');

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->info('* <comment>' . $instanceId . ':</comment> <error>failure</error> - not found');
        } catch (DatabaseException $_ex) {
            $this->info('* <comment>' . $instanceId . ':</comment> <error>failure</error> - ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return array
     * @throws \DreamFactory\Enterprise\Database\Exceptions\DatabaseException
     */
    protected function findSuitableCredentials($instance)
    {
        $_generic = false;

        //  Get all the user entries for this instance owner
        $_users =
            \DB::select('SELECT * FROM mysql.user WHERE User = :User AND Host = :Host',
                [':User' => $instance->db_user_text, ':Host' => $instance->webServer->host_text]);

        if (empty($_users)) {
            $_users =
                \DB::select('SELECT * FROM mysql.user WHERE User = :User AND ( Host = :Host OR Host = :percent_sign )',
                    [':User' => $instance->db_user_text, ':Host' => 'localhost', ':percent_sign' => '%']);

            if (empty($_users)) {
                throw new DatabaseException('no database credentials for user "' . $instance->db_user_text . '", unable to move.');
            }

            $_generic = true;
        }

        //  Find the current host matching row
        foreach ($_users as $_user) {
            if ($_generic || $instance->webServer->host_text == $_user->Host) {
                return (array)$_user;
            }
        }

        if (empty($_row)) {
            throw new DatabaseException('no suitable database user found for "' . $instance->db_user_text . '", unable to move.');
        }
    }

    /**
     * @param Instance                                        $instance
     * @param \DreamFactory\Enterprise\Database\Models\Server $server
     * @param string                                          $dbHost The host name of the database server, if any
     *
     * @return bool
     * @throws \DreamFactory\Enterprise\Database\Exceptions\DatabaseException
     */
    protected function updateInstanceConfig(&$instance, $server, $dbHost = null)
    {
        $_config = $instance->instance_data_text;
        $_serverId = $server->server_id_text;
        $_instanceId = $instance->instance_id_text;

        if ($server->isServerType(ServerTypes::DATABASE)) {
            try {
                $this->mysqlSetConnection('dfe-remote');

                //  Grant new privileges on remote database
                $this->mysqlGrantPrivileges(
                    $instance->db_user_text,
                    $instance->db_password_text,
                    $instance->db_name_text,
                    $instance->webServer->host_text);

                //  Purge existing if desired
                $this->option('purge') && $this->mysqlDropUser($instance->db_user_text);

                //  Update the instance row
                $instance->db_server_id = $server->id;
                $instance->db_host_text = $dbHost ?: $server->host_text;

                if (empty($_db = array_get($_config, 'db.' . $_instanceId))) {
                    return false;
                }

                array_set($_config, 'db.' . $_instanceId . '.id', $_serverId);
                array_set($_config, 'db.' . $_instanceId . '.db-server-id', $_serverId);
                array_set($_config, 'audit.db-server-id', $_serverId);
            }
            finally {
                $this->mysqlDisconnect();
            }
        }

        $instance->instance_data_text = $_config;

        return true;
    }
}
