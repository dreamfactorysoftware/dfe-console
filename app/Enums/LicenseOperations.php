<?php namespace DreamFactory\Enterprise\Console\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * DFE console license server operations
 */
class LicenseOperations extends FactoryEnum
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string Upon installation of the system
     */
    const REGISTER_INSTALLATION = 'install';
    /**
     * @type string Upon registration of the first console admin
     */
    const REGISTER_ADMIN = 'admin';
    /**
     * @type string When instances are created
     */
    const REGISTER_INSTANCE = 'instance';
    /**
     * @type string Daily anonymous statistics
     */
    const REPORT_STATISTICS = 'usage';
}
