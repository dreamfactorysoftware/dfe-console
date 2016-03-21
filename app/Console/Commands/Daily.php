<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Database\Models;
use Log;
use ReflectionClass;

class Daily extends ConsoleCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:daily';
    /** @inheritdoc */
    protected $description = 'Performs daily maintenance tasks';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function handle()
    {
        parent::handle();

        $_mirror = new ReflectionClass(get_called_class());

        foreach ($_mirror->getMethods() as $_method) {
            if (preg_match("/^do(.+)Tasks$/i", $_methodName = $_method->getShortName())) {
                $_which = str_slug(str_ireplace(['do', 'tasks'], null, $_methodName));

                try {
                    logger('[dfe.daily] executing daily task: "' . $_which . '"');
                    call_user_func([get_called_class(), $_methodName]);
                } catch (\Exception $_ex) {
                    Log::error('[dfe.daily] exception during "' . $_methodName . '": ' . $_ex->getMessage());
                }
            }
        }
    }

    protected function doDatabaseTasks()
    {
        $_results = [];
        $_tasks = config('tasks.daily.database', []);

        //  Process the configured tasks for this run
        foreach (array_get($_tasks, 'delete', []) as $_table => $_task) {
            $_sql = array_get($_task, 'sql');
            $_bindings = array_get($_task, 'bindings');
            $_label = array_get($_task, 'label', 'Execute "' . $_sql . '"');

            if (!empty($_sql)) {
                try {
                    $_results[$_table] = \DB::delete($_sql, $_bindings);
                    Log::info('[dfe.daily.database.' . $_table . '] ' . $_label);
                } catch (\Exception $_ex) {
                    Log::error('[dfe.daily.database.' . $_table . '] exception while deleting: ' . $_ex->getMessage());
                }
            }
        }
    }

    protected function doStorageTasks()
    {
    }

    protected function doInstanceTasks()
    {
    }
}
