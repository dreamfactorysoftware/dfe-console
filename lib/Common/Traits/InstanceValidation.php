<?php
namespace DreamFactory\Enterprise\Common\Traits;

use DreamFactory\Enterprise\Common\Contracts\InstanceContainer;
use DreamFactory\Library\Fabric\Common\Exceptions\InstanceNotFoundException;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;

/**
 * A trait for validating instances
 */
trait InstanceValidation
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param string|Instance $instanceId
     *
     * @return Instance
     */
    protected function _validateInstance( $instanceId )
    {
        if ( $instanceId instanceof Instance )
        {
            return $instanceId;
        }

        if ( $instanceId instanceOf InstanceContainer )
        {
            return $instanceId->getInstance();
        }

        if ( !is_string( $instanceId ) )
        {
            throw new InstanceNotFoundException( $instanceId );
        }

        try
        {
            $instanceId = Instance::sanitizeName( $instanceId );

            return Instance::byNameOrId( $instanceId )->firstOrFail();
        }
        catch ( \Exception $_ex )
        {
            throw new InstanceNotFoundException( $instanceId );
        }
    }

}