<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    public function fire()
    {
        parent::fire();

        switch ($_command = trim(strtolower($this->argument('operation')))) {
            case 'create':
            case 'update':
            case 'delete':
                return $this->{'_' . $_command . 'Cluster'}($this->argument('cluster-id'));

            case 'add':
            case 'remove':
                return $this->{'_' . $_command . 'Server'}($this->argument('cluster-id'));
        }

        throw new \InvalidArgumentException('The command "' . $_command . '" is invalid');
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'operation',
                    InputArgument::REQUIRED,
                    'The operation to perform: create, update, delete, add (server to cluster), or remove (server from cluster)',
                ],
                [
                    'cluster-id',
                    InputArgument::REQUIRED,
                    'The id of the cluster upon which to perform operation',
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
    protected function _createCluster($clusterId)
    {
        if (false === ($_data = $this->_prepareData($clusterId))) {
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
    protected function _updateCluster($clusterId)
    {
        try {
            $_cluster = $this->_findCluster($clusterId);

            if (false === ($_data = $this->_prepareData())) {
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
        } catch (\Exception $_ex) {
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
    protected function _deleteCluster($clusterId)
    {
        try {
            $_cluster = $this->_findCluster($clusterId);

            if ($_cluster->delete()) {
                $this->concat('cluster id ')->asComment($clusterId)->flush(' deleted.');

                return true;
            }

            $this->writeln('error deleting cluster id "' . $clusterId . '"', 'error');
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('cluster id "' . $clusterId . '" is not valid.', 'error');
        } catch (\Exception $_ex) {
            $this->writeln('error deleting cluster record: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * Adds "server-id" from cluster
     *
     * @param string|int $clusterId
     *
     * @return bool
     */
    protected function _addServer($clusterId)
    {
        try {
            $_cluster = $this->_findCluster($clusterId);
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('cluster-id "' . $clusterId . '" is not valid.', 'error');

            return false;
        }

        try {
            $_server = $this->_findServer($this->option('server-id'));
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('"server-id" is a required option for this operation.');

            return false;
        }

        return $_server->addToCluster($_cluster->id);
    }

    /**
     * Removes "server-id" from cluster
     *
     * @param string|int $clusterId
     *
     * @return bool
     */
    protected function _removeServer($clusterId)
    {
        try {
            $_cluster = $this->_findCluster($clusterId);
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('cluster-id "' . $clusterId . '" is not valid.', 'error');

            return false;
        }

        try {
            $_server = $this->_findServer($this->option('server-id'));
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('"server-id" is a required option for this operation.');

            return false;
        }

        return $_server->removeFromCluster($_cluster);
    }

    /**
     * @param bool|string $create If false, no data will be required. Pass $clusterId to have data be required and fill
     *                            cluster_id_text field
     *
     * @return array|bool
     */
    protected function _prepareData($create = false)
    {
        $_data = [];

        if (!is_bool($create)) {
            $_clusterId = trim($create);
            $create = true;

            try {
                $this->_findCluster($_clusterId);

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
