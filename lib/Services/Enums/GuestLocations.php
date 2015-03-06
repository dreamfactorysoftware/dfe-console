<?php
namespace DreamFactory\Enterprise\Services\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * Where DFE instances may reside
 */
class GuestLocations extends FactoryEnum
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var int DreamFactory Enterprise(tm) cluster
     */
    const DFE_CLUSTER = 1;
    /**
     * @var int Amazon EC2
     */
    const AMAZON_EC2 = 2;
    /**
     * @var int Microsoft Azure
     */
    const MICROSOFT_AZURE = 3;
    /**
     * @var int Rackspace cloud
     */
    const RACKSPACE_CLOUD = 4;
    /**
     * @var int Generic OpenStack
     */
    const OPENSTACK = 5;
}
