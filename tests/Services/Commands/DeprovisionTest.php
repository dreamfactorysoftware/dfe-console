<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Services\Jobs\DeprovisionJob;

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
        $_instanceId = 'leela';

        $_payload = [
            'instance-id'        => $_instanceId,
            'owner-id'           => 22,
            'guest-location-nbr' => GuestLocations::DFE_CLUSTER,
        ];

        $_job = new DeprovisionJob($_instanceId, $_payload);

        $_result = \Queue::push($_job);
    }
}