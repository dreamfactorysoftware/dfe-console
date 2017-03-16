<?php namespace DreamFactory\Enterprise\Console\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * DFE console operations
 */
class ConsoleOperations extends FactoryEnum
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const PROVISION = 'provision';
    /**
     * @type string
     */
    const DEPROVISION = 'deprovision';
    /**
     * @type string
     */
    const EXPORT = 'export';
    /**
     * @type string
     */
    const IMPORT = 'import';
    /**
     * @type string
     */
    const UPLOAD = 'upload';
    /**
     * @type string
     */
    const METRICS = 'metrics';

    /**
     * Reminder message that an instance is about to expire.
     * @type string
     */
    const REMINDER = 'reminder';
}
