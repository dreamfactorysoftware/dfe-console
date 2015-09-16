<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models;
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
        $_table = trim(strtolower($this->argument('table')));

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
MYSQL
                , [':table_schema' => $_schema,]);
        } else {
            $_tables = [$_table];
        }

        foreach ($_tables as $_table) {
            $this->writeln('Table: ' . $_table);
            $this->writeln('SHOW FULL FIELDS FROM `' . $_schema . '`.`' . $_table . '`');

            $_columns = \DB::select('SHOW FULL FIELDS FROM `' . $_schema . '`.`' . $_table . '`');
            $_allowed = [];

            //  Filter out the ones we don't want
            foreach ($_columns as $_details) {
                if (array_key_exists($_details['Field'], $this->schemaColumns)) {
                    $_allowed[$_details['Field']] = json_encode($_details);
                }
            }

            $this->writeln($_allowed . PHP_EOL);
        }
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
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
        return array_merge(parent::getOptions(), [
            [
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The output format. Allowed values are: "text" or "json".',
                'text'
            ],
        ]);
    }
}
