<?php
namespace DreamFactory\Enterprise\Services\Enums;

use DreamFactory\Library\Utility\Enums\FactoryEnum;

/**
 * Where DFE instances may reside. These values correspond to dfe-deploy:vendor_t.id
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
     * @var int DreamFactory Enterprise(tm) cluster
     */
    const RAVE_CLUSTER = 1;
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

    //******************************************************************************
    //* Members
    //******************************************************************************

    protected static $_tags = [
        self::DFE_CLUSTER     => 'rave',
        self::AMAZON_EC2      => 'amazon',
        self::MICROSOFT_AZURE => 'azure',
        self::RACKSPACE_CLOUD => 'rackspace',
        self::OPENSTACK       => 'openstack',
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param int $constant
     *
     * @return mixed
     */
    public static function resolve( $constant )
    {
        if ( is_numeric( $constant ) && isset( static::$_tags[$constant] ) )
        {
            return static::$_tags[$constant];
        }

        if ( !is_numeric( $constant ) && is_string( $constant ) )
        {
            return $constant;
        }

        throw new \InvalidArgumentException( 'The $constant "' . $constant . '" is invalid.' );
    }
}
