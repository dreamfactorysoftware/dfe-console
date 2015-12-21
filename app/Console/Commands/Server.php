<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Traits\ArtisanHelper;
use DreamFactory\Enterprise\Common\Traits\ArtisanOptionHelper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Server extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, ArtisanHelper, ArtisanOptionHelper;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:server';
    /** @inheritdoc */
    protected $description = 'Create, update, and delete servers';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'operation',
                    InputArgument::REQUIRED,
                    'The operation to perform: show, create, update, or delete',
                ],
                [
                    'server-id',
                    InputArgument::OPTIONAL,
                    'The id of the server upon which to perform operation',
                ],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                [
                    'server-type',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'The type of server: ' . implode(', ', ServerTypes::getDefinedConstants(true)),
                ],
                ['mount-id', 'm', InputOption::VALUE_REQUIRED, 'The id of the storage mount for this server'],
                ['host-name', 'a', InputOption::VALUE_REQUIRED, 'The host name of this server',],
                [
                    'config',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'JSON-encoded array of configuration data for this server',
                ],
            ]);
    }

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        parent::fire();

        switch ($_command = trim(strtolower($this->argument('operation')))) {
            case 'create':
            case 'update':
            case 'delete':
                if (empty($_serverId = $this->argument('server-id'))) {
                    throw new \InvalidArgumentException('No "server-id" provided.');
                }

                return $this->{'_' . $_command . 'Server'}($_serverId);

            case 'show':
                return $this->showServers();
        }

        throw new \InvalidArgumentException('The "' . $_command . '" operation is not valid');
    }

    /**
     * @return int
     */
    protected function showServers()
    {
        $_servers = Models\Server::orderBy('server_id_text')->get();

        if (empty($_servers)) {
            $this->info('** No servers found **');

            return 0;
        }

        $this->writeln('Registered servers (* denotes cluster assignment)');
        $this->writeln('-------------------------------------------------');

        foreach ($_servers as $_server) {
            $_used = (0 != Models\ClusterServer::where('server_id', $_server->id)->count());

            $this->writeln(($_used ? '*' : ' ') .
                '<info>' .
                $_server->server_id_text .
                "</info>\t" .
                '<comment>' .
                $_server->serverType->type_name_text .
                '@' .
                $_server->host_text .
                '</comment>');
        }

        return 0;
    }

    /**
     * Create a server
     *
     * @param $serverId
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Server
     */
    protected function _createServer($serverId)
    {
        if (false === ($_data = $this->_prepareData($serverId))) {
            return false;
        }

        $_server = Models\Server::create($_data);

        $this->concat('server id ')->asComment($serverId)->flush(' created.');

        return $_server;
    }

    /**
     * Update a server
     *
     * @param $serverId
     *
     * @return bool
     */
    protected function _updateServer($serverId)
    {
        try {
            if (false === ($_data = $this->_prepareData())) {
                return false;
            }

            if ($this->_findServer($serverId)->update($_data)) {
                $this->concat('server id ')->asComment($serverId)->flush(' updated.');

                return true;
            }

            $this->writeln('error updating server id "' . $serverId . '"', 'error');
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('server-id "' . $serverId . '" is not valid.', 'error');
        } catch (\Exception $_ex) {
            $this->writeln('error updating server record: ' . $_ex->getMessage(), 'error');
        }

        return false;
    }

    /**
     * Update a server
     *
     * @param $serverId
     *
     * @return bool
     */
    protected function _deleteServer($serverId)
    {
        try {
            $_server = $this->_findServer($serverId);

            if ($_server->delete()) {
                $this->concat('server id ')->asComment($serverId)->flush(' deleted.');

                return true;
            }

            $this->writeln('error deleting server id "' . $serverId . '"', 'error');

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('the server-id "' . $serverId . '" is not valid.', 'error');

            return false;
        } catch (\Exception $_ex) {
            $this->writeln('error deleting server record: ' . $_ex->getMessage(), 'error');

            return false;
        }
    }

    /**
     * @param bool|string $create If false, no data will be required. Pass $serverId to have data be required and fill
     *                            server_id_text field
     *
     * @return array|bool
     */
    protected function _prepareData($create = false)
    {
        $_data = [];

        if (!is_bool($create)) {
            $_serverId = trim($create);
            $create = true;

            try {
                $this->_findServer($_serverId);

                $this->writeln('the server-id "' . $_serverId . '" already exists.', 'error');

                return false;
            } catch (ModelNotFoundException $_ex) {
                //  This is what we want...
            }

            $_data['server_id_text'] = $_serverId;
        }

        //  Server type
        $_serverType = $this->option('server-type');

        try {
            $_type = ServerTypes::defines(trim(strtoupper($_serverType)), true);
            $_data['server_type_id'] = $_type;
        } catch (\Exception $_ex) {
            if ($create) {
                $this->writeln('the server-type "' . $_serverType . '" is not valid.', 'error');

                return false;
            }
        }

        //  Mount
        $_mountId = $this->option('mount-id');

        try {
            $_mount = $this->_findMount($_mountId);
            $_data['mount_id'] = $_mount->id;
        } catch (\Exception $_ex) {
            if ($create) {
                $this->writeln('the mount-id "' . $_mountId . '" does not exists.', 'error');

                return false;
            }
        }

        //  Host name
        if (!$this->optionString('host-name', 'host_text', $_data, $create)) {
            return false;
        }

        //  Config (optional)
        if (!$this->optionArray('config', 'config_text', $_data, $create)) {
            return false;
        }

        $_timestamp = new Carbon();

        if (!isset($_data['create_date']) || '0000-00-00 00:00:00' == $_data['create_date']) {
            $_data['create_date'] = $_timestamp;
        }

        if (!isset($_data['lmod_date']) || '0000-00-00 00:00:00' == $_data['lmod_date']) {
            $_data['lmod_date'] = $_timestamp;
        }

        return $_data;
    }

}
