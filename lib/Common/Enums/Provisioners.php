<?php
namespace DreamFactory\Enterprise\Common\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * The currently supported provisioners
 */
class Provisioners extends FactoryEnum
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var int
     */
    const AMAZON_EC2 = 0;
    /**
     * @var int
     */
    const DREAMFACTORY_ENTERPRISE = 1;
    /**
     * @var int
     */
    const MICROSOFT_AZURE = 2;

}