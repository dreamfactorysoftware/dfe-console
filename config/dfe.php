<?php
//******************************************************************************
//* Master DFE Console Settings
//******************************************************************************

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;

return [
    //******************************************************************************
    //* General
    //******************************************************************************
    //  The id of THIS cluster
    'cluster-id'       => 'cluster-east-2',
    //  A string to be pre-pended to instance names for non-admin users
    'instance-prefix'  => env( 'DFE_DEFAULT_INSTANCE_PREFIX' ),
    'signature-method' => env( 'DFE_SIGNATURE_METHOD', EnterpriseDefaults::DEFAULT_SIGNATURE_METHOD ),
];
