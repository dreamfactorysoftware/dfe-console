<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\ArtisanHelper;
use DreamFactory\Enterprise\Common\Traits\ArtisanOptionHelper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\MountTypes;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Mount extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, ArtisanOptionHelper, ArtisanHelper;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:mount';
    /** @inheritdoc */
    protected $description = 'Create, update, and delete mounts';

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
                    'mount-id',
                    InputArgument::OPTIONAL,
                    'The id of the mount upon which to perform operation',
                ],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                [
                    'mount-type',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'The type of mount: ' . implode(', ', MountTypes::getDefinedConstants(true)),
                ],
                ['owner-id', null, InputOption::VALUE_REQUIRED, 'The "owner-id" of this mount'],
                [
                    'owner-type',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'The type of owner: ' . implode(', ', OwnerTypes::getDefinedConstants(true)),
                ],
                ['root-path', 'p', InputOption::VALUE_REQUIRED, 'The "root-path" of the mount',],
                ['config', 'c', InputOption::VALUE_REQUIRED, 'JSON-encoded array of configuration data for this mount'],
            ]);
    }

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        switch ($_command = trim(strtolower($this->argument('operation')))) {
            case 'create':
            case 'update':
            case 'delete':
                if (empty($_mountId = $this->argument('mount-id'))) {
                    throw new InvalidArgumentException('No "mount-id" provided.');
                }

                return $this->{'_' . $_command . 'Mount'}($this->argument('mount-id'));

            case 'show':
                return $this->showMounts();
        }

        throw new InvalidArgumentException('The "' . $_command . '" operation is not valid');
    }

    /**
     * @return int
     */
    protected function showMounts()
    {
        $_mounts = Models\Mount::orderBy('mount_id_text')->get();

        if (empty($_mounts)) {
            $this->info('** No mounts found **');

            return 0;
        }

        foreach ($_mounts as $_mount) {
            $_used = (0 != Models\Server::where('mount_id', $_mount->id)->count());

            $this->writeln(($_used ? '*' : ' ') .
                '<info>' .
                $_mount->mount_id_text .
                "</info>\t" .
                '<comment>' .
                json_encode($_mount->config_text) .
                '</comment>');
        }

        return 0;
    }

    /**
     * Create a mount
     *
     * @param $mountId
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Mount
     */
    protected function _createMount($mountId)
    {
        if (false === ($_data = $this->_prepareData($mountId))) {
            return false;
        }

        $_mount = Models\Mount::create($_data);

        $this->concat('mount id ')->asComment($mountId)->flush(' created.');

        return $_mount;
    }

    /**
     * Update a mount
     *
     * @param $mountId
     *
     * @return bool
     */
    protected function _updateMount($mountId)
    {
        try {
            if (false === ($_data = $this->_prepareData())) {
                return false;
            }

            if ($this->_findMount($mountId)->update($_data)) {
                $this->concat('mount id ')->asComment($mountId)->flush(' updated.');

                return true;
            }

            $this->writeln('error updating mount id "' . $mountId . '"', 'error');
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('mount-id "' . $mountId . '" is not valid.', 'error');
        } catch (Exception $_ex) {
            $this->writeln('error updating mount record: ' . $_ex->getMessage(), 'error');
        }

        return false;
    }

    /**
     * Update a mount
     *
     * @param $mountId
     *
     * @return bool
     */
    protected function _deleteMount($mountId)
    {
        try {
            $_mount = $this->_findMount($mountId);

            if ($_mount->delete()) {
                $this->concat('mount id ')->asComment($mountId)->flush(' deleted.');

                return true;
            }

            $this->writeln('error deleting mount id "' . $mountId . '"', 'error');

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('the mount-id "' . $mountId . '" is not valid.', 'error');

            return false;
        } catch (Exception $_ex) {
            $this->writeln('error deleting mount record: ' . $_ex->getMessage(), 'error');

            return false;
        }
    }

    /**
     * @param bool|string $create If false, no data will be required. Pass $mountId to have data be required and fill
     *                            mount_id_text field
     *
     * @return array|bool
     */
    protected function _prepareData($create = false)
    {
        $_data = [];

        if (!is_bool($create)) {
            $_mountId = trim($create);
            $create = true;

            try {
                $this->_findMount($_mountId);

                $this->writeln('the mount-id "' . $_mountId . '" already exists.', 'error');

                return false;
            } catch (ModelNotFoundException $_ex) {
                //  This is what we want...
            }

            $_data['mount_id_text'] = $_mountId;
        }

        //  Mount type
        $_mountType = $this->option('mount-type');

        try {
            $_type = MountTypes::defines(trim(strtoupper($_mountType)), true);
            $_data['mount_type_nbr'] = $_type;
        } catch (Exception $_ex) {
            if ($create) {
                $this->writeln('the mount-type "' . $_mountType . '" is not valid.', 'error');

                return false;
            }
        }

        //  Owner
        if (!$this->optionOwner($_data, false)) {
            return false;
        }

        //  Root path
        if (!$this->optionString('root-path', 'root_path_text', $_data, false)) {
            return false;
        }

        //  Config (optional)
        if (!$this->optionArray('config', 'config_text', $_data, false)) {
            return false;
        }

        return $_data;
    }

}
