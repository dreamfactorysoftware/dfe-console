<?php
namespace DreamFactory\Enterprise\Services\Traits;

use DreamFactory\Enterprise\Services\Utility\RemoteInstance;
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
        try
        {
            if ( $instanceId instanceof Instance || $instanceId instanceof RemoteInstance )
            {
                return $instanceId;
            }

            return Instance::byNameOrId( $instanceId )->first();
        }
        catch ( \Exception $_ex )
        {
            throw new InstanceNotFoundException( $instanceId );
        }
    }

}