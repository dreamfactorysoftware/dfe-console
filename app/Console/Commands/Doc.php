<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Facades\DataShaper;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Doc extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:db-doc';
    /** @inheritdoc */
    protected $description = 'Dumps database information in a documentation format.';
    /**
     * @type array The schema columns we're interested in
     */
    protected $schemaColumns = [
        'Field',
        'Type',
        'Collation',
        'Null',
        'Key',
        'Default',
        'Extra',
        'Privileges',
        'Comment',
    ];

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

        $_schema = array_get(config('database.connections.' . config('database.default')), 'database');
        $_table = trim($this->argument('table'));
        $_format = $this->option('format');

        if (empty($_table)) {
            //  All tables...
            /** @noinspection SqlResolve */
            $_tables = \DB::select(<<<MYSQL
SELECT
  s.TABLE_NAME
FROM
  INFORMATION_SCHEMA.TABLES s
WHERE
  s.TABLE_SCHEMA = :table_schema
ORDER BY
  s.TABLE_NAME
MYSQL
                ,
                [':table_schema' => $_schema,]);
        } else {
            $_tables = [$_table];
        }

        $_output = [];

        foreach ($_tables as $_row) {
            $_data = [];
            $_table = is_string($_row) ? $_row : (string)$_row->TABLE_NAME;
            $_columns = \DB::select('SHOW FULL FIELDS FROM `' . $_schema . '`.`' . $_table . '`');

            //  Filter out the ones we don't want
            foreach ($_columns as $_details) {
                $_data[$_details->Field] = (array)$_details;
            }

            $_output[$_table] = DataShaper::transform($_data, $_format, ['class' => 'ddl-table']);
        }

        $this->output->writeln(print_r($_output, true));
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'table',
                    InputArgument::OPTIONAL,
                    'The table to document. If omitted, all tables are dumped.',
                ],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                [
                    'format',
                    'f',
                    InputOption::VALUE_OPTIONAL,
                    'The output format. Allowed values are: "raw", "json", or "mediawiki_table"',
                    'json',
                ],
            ]);
    }
}
