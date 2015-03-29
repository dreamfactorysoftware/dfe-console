<?php
/**
 * InstanceStates.php
 */
namespace Cerberus\Enums;

use Kisma\Core\Enums\SeedEnum;

/**
 * InstanceStates
 */
class InstanceStates extends SeedEnum
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var int
     */
    const CREATED = 0;
    /**
     * @var int
     */
    const PROVISIONING = 1;
    /**
     * @var int
     */
    const PROVISIONED = 2;
    /**
     * @var int
     */
    const DEPROVISIONING = 3;
    /**
     * @var int
     */
    const DEPROVISIONED = 4;
    /**
     * @var int
     */
    const CREATION_ERROR = 10;
    /**
     * @var int
     */
    const PROVISIONING_ERROR = 12;
    /**
     * @var int
     */
    const DEPROVISIONING_ERROR = 14;
}
