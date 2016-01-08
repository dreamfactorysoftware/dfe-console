<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Info extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:info';
    /** @inheritdoc */
    protected $description = 'Extract information from the system';
    /**
     * @type array The supported entities
     */
    protected $entities = ['cluster', 'instance', 'metrics', 'mount', 'server', 'app-key',];

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

        $_format = $this->validateFormat();
        list($_entityType, $_entityId, $_all, $_studlyType) = $this->validateEntityType();

        try {
            if ($_all) {
                $_class = '\\DreamFactory\\Enterprise\\Database\\Models\\' . $_studlyType;
                /** @type Models\EnterpriseModel $_model */
                $_model = new $_class();
                $_info = $_model->all();
            } else {
                $_info = call_user_func([$this, 'find' . $_studlyType], $_entityId, $this->option('owner-type'));
            }
        } catch (ModelNotFoundException $_ex) {
            //  No results...
            $_info = [];
        }

        if (empty($_info) || empty($_data = $_info->toArray())) {
            $this->writeln('<comment>No results found</comment>');

            return 0;
        };

        $this->writeln($this->formatArray($_data, !$this->option('ugly'), $_entityType . ($_all ? 's' : null)));

        return 0;
    }

    /**
     * Find metrics by date
     *
     * @return Metrics[]|Metrics
     */
    protected function findMetrics()
    {
        if (!empty($_startDate = $this->option('start-date'))) {
            $_startDate = new Carbon($_startDate);
            $_endDate = $this->option('end-date') ?: Carbon::now();

            return Models\Metrics::where('create_date', '>=', $_startDate)->where('create_date', '<=', $_endDate)->get();
        }

        return Models\Metrics::orderBy('create_date', 'desc')->first();
    }

    /**
     * @param string $entity the entity name
     * @param array  $data   an array of info records
     *
     * @todo this is in progress
     */
    protected function showData($entity, $data = [])
    {
        $data = ('metrics' != $entity ? [0 => $data] : $data);

        if (empty($data) || empty($_first = current($data))) {
            return;
        }

        $_header = $_subheader = null;

        foreach (array_keys($_first) as $_key) {
            $_header .= $_key . "\t";
            $_subheader .= str_pad(null, strlen($_key . "\t"), '-') . "\t";
        }

        $this->writeln($_header);
        $this->writeln($_subheader);

        foreach ($data as $_row) {
            $_line = $_subline = null;

            foreach ($_row as $_key => $_value) {
                if (is_scalar($_value)) {
                    $_line .= $_value . "\t";
                } else {
                    $_line .= "[below]\t";
                    $_subline .= $_key . ': ' . json_encode($_value) . PHP_EOL;
                }
            }

            $this->writeln($_line);
            $this->writeln($_subline);
        }
    }

    /**
     * @param string|int $clusterId
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
            throw new \InvalidArgumentException('The cluster-id "' . $clusterId . '" is invalid.');
        }
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'entity-type',
                    InputArgument::REQUIRED,
                    'The type of entity information to retrieve: <info>mount</info>, <info>server</info>, <info>cluster</info>, <info>instance</info>, or <info>metrics</info>',
                ],
                [
                    'entity-id',
                    InputArgument::OPTIONAL,
                    'The id of the <info>entity-type</info>',
                ],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                [
                    'all',
                    'a',
                    InputOption::VALUE_NONE,
                    'Return all data, ignoring <comment>entity-id</comment>',
                ],
                [
                    'start-date',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'The start date for a range of <info>metrics</info> data',
                ],
                [
                    'end-date',
                    'e',
                    InputOption::VALUE_REQUIRED,
                    'The end date for a range of <info>metrics</info> data',
                ],
                [
                    'format',
                    'f',
                    InputOption::VALUE_OPTIONAL,
                    'The format in which to output the information. Available formats are: <info>json</info> or <info>xml</info>',
                ],
                [
                    'ugly',
                    'u',
                    InputOption::VALUE_NONE,
                    'For formatted output, does not <comment>pretty-print</comment> output',
                ],
                [
                    'escaped-slashes',
                    null,
                    InputOption::VALUE_NONE,
                    'For JSON formatted output, slashes will be escaped (default is that they are <comment>not</comment>)',
                    null,
                ],
                ['owner-type', 't', InputOption::VALUE_REQUIRED, 'The "owner-type" of the entity (required by "app-key")', OwnerTypes::USER,],
            ]);
    }

    /**
     * Validates the output format
     *
     * @return string
     */
    protected function validateFormat()
    {
        if (!empty($_format = trim(strtolower($this->option('format'))))) {
            switch ($_format) {
                case 'json':
                case 'xml':
                    //  Be validatin'
                    break;

                default:
                    throw new \InvalidArgumentException('The format "' . $_format . '" is invalid.');
            }
        }

        return $_format;
    }

    /**
     * Validate entity type and ID. Returns array of entity identifying info
     *
     * @return array
     */
    protected function validateEntityType()
    {
        $_entityType = strtolower(trim($this->argument('entity-type')));
        $_all = $this->option('all');

        if (empty($_entityId = $this->argument('entity-id')) && 'metrics' != $_entityType && !$_all) {
            throw new \InvalidArgumentException('The <comment>entity-id</comment> is required.');
        }

        if (!in_array($_entityType, $this->entities)) {
            throw new \InvalidArgumentException('The entity-type "' . $_entityType . '" is invalid.');
        }

        $_studlyType = studly_case($_entityType);

        if (!method_exists($this, 'find' . $_studlyType)) {
            throw new \RuntimeException('The entity-type "' . $_entityType . '" is supported but has no associated handler.');
        }

        return [$_entityType, $_entityId, $_all, $_studlyType];
    }
}
