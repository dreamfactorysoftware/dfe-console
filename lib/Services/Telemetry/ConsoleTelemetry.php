<?php namespace DreamFactory\Enterprise\Services\Telemetry;

use DreamFactory\Enterprise\Database\Models\Telemetry;

class ConsoleTelemetry extends BaseTelemetryProvider
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = 'console';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array All tables
     */
    protected $tables = [];
    /**
     * @type array Only these tables
     */
    protected $only = ['user_t', 'mount_t', 'server_t', 'cluster_t', 'limit_t', 'instance_t', 'service_user_t',];
    /**
     * @type array All but these tables
     */
    protected $except = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * ClusterTelemetry constructor.
     *
     * @param array|null  $only           Only these tables
     * @param array|null  $except         All but these tables
     * @param null|string $connectionName The connection. Default is used otherwise
     */
    public function __construct(array $only = null, array $except = null, $connectionName = null)
    {
        $only && $this->only = $only;
        $except && $this->except = $except;

        $this->tables = $this->discoverTables($connectionName);
    }

    /** @inheritdoc */
    public function getTelemetry($options = [])
    {
        $_connectionName = array_get($options, 'connection-name');
        $_only = array_get($options, 'only', $this->only);
        $_except = array_get($options, 'except', $this->except);

        $_tables = [];

        //  Determine the list of tables we're going to scan
        foreach ($this->tables as $_table) {
            //  Only check
            if (!empty($_only) && !in_array($_table, $_only)) {
                continue;
            }

            //  Excepts
            if (!empty($_except) && in_array($_table, $_except)) {
                continue;
            }

            //  It's a table we want!
            $_tables[] = $_table;
        }

        //  Get telemetry from those tables
        $_db = \DB::connection($_connectionName);
        $_telemetry = [];

        foreach ($_tables as $_table) {
            //  Strip off the domain identifier
            $_index =
                ('_t' == substr($_table, -2))
                    ? substr($_table, 0, -2)
                    : $_table;

            $_telemetry[$_index] = $_db->table($_table)->count();
        }

        //  Drop a row in the table
        try {
            Telemetry::storeTelemetry($this->getProviderId(), $_telemetry);
        } catch (\Exception $_ex) {
            //  No worries, just throw it in the log...
            \Log::error('[telemetry.console] unable to save telemetry data: ' . $_ex->getMessage(), $_telemetry);
        }

        return $_telemetry;
    }

    /**
     * Returns the list of tables that this service reports on
     *
     * @param string|null $name The connection name if other than default
     *
     * @return array
     */
    protected function discoverTables($name = null)
    {
        try {
            $_tables = \DB::connection($name)->getDoctrineConnection()->getSchemaManager()->listTables();
        } catch (\Exception $_ex) {
            //  Try the old-fashioned way...
            $_tables = \DB::connection($name)->statement('SHOW TABLES');
        }

        return $_tables;
    }
}
