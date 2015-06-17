<?php namespace DreamFactory\Enterprise\Console\Tests\Services\Commands;

use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Models\Instance;

class ProvisionTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests provision request
     */
    public function testProvision()
    {
        $_instanceId = 'dfe-test-case';

        $_payload = [
            'instance-id'        => $_instanceId,
            'owner-id'           => 1,
            'guest-location-nbr' => GuestLocations::DFE_CLUSTER,
        ];

        if ( null !== ( $_instance = Instance::byNameOrId( $_instanceId )->first() ) )
        {
            $_instance->delete();
        }

        $_job = new ProvisionJob( $_instanceId, $_payload );

        $_result = \Queue::push( $_job );

    }
}