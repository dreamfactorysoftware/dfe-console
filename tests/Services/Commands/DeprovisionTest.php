<?php
namespace DreamFactory\Enterprise\Services\Tests\Commands;

use DreamFactory\Enterprise\Services\Commands\DeprovisionJob;
use DreamFactory\Enterprise\Services\Enums\GuestLocations;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

class DeprovisionTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests deprovision request
     */
    public function testDeprovision()
    {
        $_instanceId = 'dfe-test-case';

        $_payload = [
            'instance-id'        => $_instanceId,
            'owner-id'           => 1,
            'guest-location-nbr' => GuestLocations::RAVE_CLUSTER,
        ];

        $_job = new DeprovisionJob( $_instanceId, $_payload );

        $_result = \Queue::push( $_job );

    }
}