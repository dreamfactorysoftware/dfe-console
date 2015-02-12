<?php
namespace Cerberus\Enums;

use Kisma\Core\Enums\SeedEnum;

/**
 * MailTemplates.php
 */
class MailTemplates extends SeedEnum
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var int
     */
    const SYSTEM_NOTIFICATION = -1;
    /**
     * @var int
     */
    const WELCOME = 0;
    /**
     * @var int
     */
    const RESET_PASSWORD = 1;
    /**
     * @var int
     */
    const NOTIFICATION = 2;
    /**
     * @var int
     */
    const RESEND_CONFIRMATION = 3;
    /**
     * @var int
     */
    const STATUS = 4;
    /**
     * @var int
     */
    const PASSWORD_CHANGED = 5;
    /**
     * @var int
     */
    const PROVISIONING_COMPLETE = 6;
    /**
     * @var int
     */
    const DEPROVISIONING_COMPLETE = 6;
    /**
     * @var int
     */
    const GENERIC = 100;
}
