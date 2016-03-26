<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Cluster extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:cluster';
    /** @inheritdoc */
    protected $description = 'Create, update, delete, and manage clusters';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $_clusterId = $this->argument('cluster-id');

        switch ($_command = trim(strtolower($this->argument('operation')))) {
            case 'create':
            case 'update':
            case 'delete':
                return $this->{$_command . 'Cluster'}($_clusterId);

            case 'add':
            case 'remove':
                return $this->{$_command . 'Server'}($_clusterId, $this->option('server-id'));

            case 'show':
                return $this->showServers($_clusterId);
        }

        throw new InvalidArgumentException('The command "' . $_command . '" is invalid');
    }

    /**
     * @param string|int $clusterId
     *
     * @return int
     */
    protected function showServers($clusterId)
    {
        try {
            $_cluster = $this->findCluster($clusterId);

            $this->writeln('Assigned to cluster-id "' . $clusterId . '":');
            $this->writeln('-------------------------------------------------');

            foreach ($_cluster->assignedServers() as $_server) {
                $this->writeln('<info>' .
                    $_server->server->server_id_text .
                    "</info>\t<comment>" .
                    $_server->server->serverType->type_name_text .
                    '</comment>');
            }
        } catch (ModelNotFoundException $_ex) {
            throw new InvalidArgumentException('The cluster-id "' . $clusterId . '" is invalid.');
        }

        return 0;
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'operation',
                    InputArgument::REQUIRED,
                    'The operation to perform: show, create, update, delete, add (server to cluster), or remove (server from cluster)',
                ],
                [
                    'cluster-id',
                    InputArgument::OPTIONAL,
                    'The id of the cluster upon which to perform operation',
                    config('dfe.cluster-id'),
                ],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                //  Create/Update/Delete
                ['owner-id', null, InputOption::VALUE_REQUIRED, 'The "owner-id" of this cluster'],
                [
                    'owner-type',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'The type of owner: ' . implode(', ', OwnerTypes::getDefinedConstants(true)),
                ],
                ['subdomain', null, InputOption::VALUE_REQUIRED, 'The subdomain in which this cluster resides'],
                ['max-instances', 'm', InputOption::VALUE_REQUIRED, 'The maximum number of instances allowed, if any.'],
                //  Add/Remove
                ['server-id', null, InputOption::VALUE_REQUIRED, 'The "server-id" to "add" or "remove"'],
            ]);
    }

    /**
     * Create a cluster
     *
     * @param $clusterId
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Cluster
     */
    protected function createCluster($clusterId)
    {
        if (false === ($_data = $this->prepareData($clusterId))) {
            return false;
        }

        $_cluster = Models\Cluster::create($_data);

        $this->concat('cluster id ')->asComment($clusterId)->flush(' created.');

        return $_cluster;
    }

    /**
     * Update a cluster
     *
     * @param $clusterId
     *
     * @return bool
     */
    protected function updateCluster($clusterId)
    {
        try {
            $_cluster = $this->findCluster($clusterId);

            if (false === ($_data = $this->prepareData())) {
                return false;
            }

            if ($_cluster->update($_data)) {
                $this->concat('cluster id ')->asComment($clusterId)->flush(' updated.');

                return true;
            }

            $this->writeln('error updating cluster id "' . $clusterId . '"', 'error');

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('cluster-id "' . $clusterId . '" is not valid.', 'error');

            return false;
        } catch (Exception $_ex) {
            $this->writeln('error updating cluster record: ' . $_ex->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Update a cluster
     *
     * @param $clusterId
     *
     * @return bool
     */
    protected function deleteCluster($clusterId)
    {
        try {
            $_cluster = $this->findCluster($clusterId);

            if ($_cluster->delete()) {
                $this->concat('cluster id ')->asComment($clusterId)->flush(' deleted.');

                return true;
            }

            $this->writeln('error deleting cluster id "' . $clusterId . '"', 'error');
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('cluster id "' . $clusterId . '" is not valid.', 'error');
        } catch (Exception $_ex) {
            $this->writeln('error deleting cluster record: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * Adds "server-id" from cluster
     *
     * @param string|int $clusterId
     * @param string|int $serverId
     *
     * @return bool
     */
    protected function addServer($clusterId, $serverId)
    {
        try {
            $_server = $this->findServer($serverId);
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('"server-id" is a required option for this operation.');

            return false;
        }

        $this->writeln('Adding server-id "' . $_server->server_id_text . '" to cluster-id "' . $clusterId . '"');

        return $_server->addToCluster($clusterId);
    }

    /**
     * Removes "server-id" from cluster
     *
     * @param string|int $clusterId
     * @param string|int $serverId
     *
     * @return bool
     */
    protected function removeServer($clusterId, $serverId)
    {
        try {
            $_server = $this->findServer($serverId);
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('server-id "' . $serverId . '" was not found.');

            return false;
        } catch (Exception $_ex) {
            $this->writeln('Error adding server: ' . $_ex->getMessage());

            return false;
        }

        $this->writeln('Removing server-id "' . $_server->server_id_text . '" from cluster-id "' . $clusterId . '"');

        return $_server->removeFromCluster($clusterId);
    }

    /**
     * @param bool|string $create If false, no data will be required. Pass $clusterId to have data be required and fill
     *                            cluster_id_text field
     *
     * @return array|bool
     */
    protected function prepareData($create = false)
    {
        $_data = [];

        if (!is_bool($create)) {
            $_clusterId = trim($create);
            $create = true;

            try {
                $this->findCluster($_clusterId);

                $this->writeln('dfe: The cluster-id "' . $_clusterId . '" already exists.', 'error');

                return false;
            } catch (ModelNotFoundException $_ex) {
                //  This is what we want...
            }

            $_data['cluster_id_text'] = $_clusterId;
        }

        //  Owner
        if (!$this->optionOwner($_data, false)) {
            return false;
        }

        //  Subdomain
        if (!$this->optionString('subdomain', 'subdomain_text', $_data, $create)) {
            return false;
        }

        return $_data;
    }

}
